<?php
class Amanty_Pay_PaymentController extends Mage_Core_Controller_Front_Action
{

    protected $_PayResponse = null;
    protected $_realOrderIds;
    protected $_quote;

    /**
     * Get quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $session = Mage::getSingleton('checkout/session');
            $this->_quote = Mage::getModel('sales/quote')->load($session->getPayPaymentQuoteId());

            if (!$this->_quote->getId()) {
                $realOrderIds = $this->getRealOrderIds();
                if (count($realOrderIds)) {
                    $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderIds[0]);
                    $this->_quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                }
            }
        }
        return $this->_quote;
    }

    /**
     * Get real order ids
     *
     * @return array
     */
    public function getRealOrderIds()
    {
        $this->_realOrderIds = $this->_PayResponse['cartId'];
        return $this->_realOrderIds;
    }


    /**
     * seting response after returning from MTC
     *
     * @param array $response
     * @return object $this
     */
    protected function setPayResponse($response)
    {
        if (count($response)) {
            $this->_PayResponse = $response;
        }
        return $this;
    }

    /**
     * When a customer chooses Amanty on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setPayPaymentQuoteId($session->getLastQuoteId());

        if ($this->getQuote()->getIsMultiShipping()) {
            $realOrderIds = explode(',', $session->getRealOrderIds());
            $session->setPayRealOrderIds($session->getRealOrderIds());
        } else {
            $realOrderIds = array($session->getLastRealOrderId());
            $session->setPayRealOrderIds($session->getLastRealOrderId());
        }

        foreach ($realOrderIds as $realOrderId) {
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($realOrderId);

            if (!$order->getId()) {
                $this->norouteAction();
                return;
            }

            $order->addStatusToHistory(
                $order->getStatus(), Mage::helper('Pay')->__('Customer was redirected to Pay')
            );
            $order->save();
        }

        $this->getResponse()
             ->setBody($this->getLayout()
                ->createBlock('Pay/redirect')
                ->setOrder($order)
                ->toHtml());

        $session->unsQuoteId();
    }

    /**
     *  Pay response router
     *
     *  @param    none
     *  @return	  void
     */
    public function notifyAction()
    {
        $model = Mage::getModel('Pay/payment');

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $method = 'post';
        } else {
            $model->generateErrorResponse();
        }
		
		
        $this->setPayResponse($postData);

      
        $checksum = $postData['checksum'];
        $correctchecksum = $model->getcorrectchecksumbookurl($postData);

		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getRealOrderIds());

		if (!$order->getId()) {
			$model->generateErrorResponse();
		}

        if ($checksum == $correctchecksum)
		{

			$order = Mage::getModel('sales/order')->loadByIncrementId($this->getRealOrderIds());

			// Déblocage de la commande si nécessaire
			
			if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
				$order->unhold();
			}

			if (!$status = $model->getConfigData('order_status_payment_accepted')) {
				$status = $order->getStatus();
			}

		  
			$message = $model->getSuccessfulPaymentMessage($postData);

			if ($status == Mage_Sales_Model_Order::STATE_PROCESSING) {
				$order->setState(
					Mage_Sales_Model_Order::STATE_PROCESSING, $status, $message
				);
			} else if ($status == Mage_Sales_Model_Order::STATE_COMPLETE) {
				$order->setState(
					Mage_Sales_Model_Order::STATE_COMPLETE, $status, $message, null, false
				);
			} else {
				$order->addStatusToHistory(
					$status, $message, true
				);
			}

			if (!$order->getEmailSent()) {
				$order->sendNewOrderEmail();
			}

			if ($model->getConfigData('invoice_create')) {
				$this->saveInvoice($order);
			}

			$order->save();

            if ($method == 'post') {
                $model->generateSuccessResponse($this->getRealOrderIds());
            } else if ($method == 'get') {
                return;
            }
        } else {
            foreach ($this->getRealOrderIds() as $realOrderId) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);
                $order->addStatusToHistory(
                    $order->getStatus(), Mage::helper('Pay')->__('Returned Checksum is invalid. Order cancelled.')
                );
                if ($order->canCancel())
                    $order->cancel();
                $order->save();
            }
            $model->generateErrorResponse();
        }
    }

    /**
     *  Save invoice for order
     *
     *  @param    Mage_Sales_Model_Order $order
     *  @return	  boolean Can save invoice or not
     */
    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {

            $version = Mage::getVersion();
            $version = substr($version, 0, 5);
            $version = str_replace('.', '', $version);
            while (strlen($version) < 3) {
                $version .= "0";
            }

            if (((int) $version) < 111) {
                $convertor = Mage::getModel('sales/convert_order');
                $invoice = $convertor->toInvoice($order);
                foreach ($order->getAllItems() as $orderItem) {
                    if (!$orderItem->getQtyToInvoice()) {
                        continue;
                    }
                    $item = $convertor->itemToInvoiceItem($orderItem);
                    $item->setQty($orderItem->getQtyToInvoice());
                    $invoice->addItem($item);
                }
                $invoice->collectTotals();
            } else {
                $invoice = $order->prepareInvoice();
            }

            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
            return true;
        }

        return false;
    }

    /**
     *  Success payment page
     *
     *  @param    none
     *  @return	  void
     */
    public function successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPayPaymentQuoteId());
        $session->unsPayPaymentQuoteId();
        $session->setCanRedirect(false);

        $session->setIsMultishipping(false);

        if ($this->getQuote()->getIsMultiShipping())
            $orderIds = array();

        foreach ($this->getRealOrderIds() as $realOrderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);

            if (!$order->getId()) {
                $this->norouteAction();
                return;
            }

            $order->addStatusToHistory(
                $order->getStatus(), Mage::helper('Pay')->__('Customer successfully returned from Pay')
            );

            $order->save();

            if ($this->getQuote()->getIsMultiShipping())
                $orderIds[$order->getId()] = $realOrderId;
        }

        if ($this->getQuote()->getIsMultiShipping()) {
            Mage::getSingleton('checkout/type_multishipping')
                ->getCheckoutSession()
                ->setDisplaySuccess(true)
                ->setPayboxResponseCode('success');

            Mage::getSingleton('core/session')->setOrderIds($orderIds);
            Mage::getSingleton('checkout/session')->setIsMultishipping(true);
        }

        $this->_redirect($this->_getSuccessRedirect());
    }

    /**
     *  Failure payment page
     *
     *  @param    none
     *  @return	  void
     */
    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $model = Mage::getModel('Pay/payment');

        $session->setIsMultishipping(false);

        if ($this->getQuote()->getIsMultiShipping())
            $orderIds = array();

        foreach ($this->getRealOrderIds() as $realOrderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);

            if (!$order->getId()) {

                //$this->_redirect('checkout/onepage/error');
                //return;
            } else if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
                /* $order->addStatusToHistory(
                  $model->getConfigData('order_status_payment_canceled'),
                  Mage::helper('Pay')->__('Customer returned from Pay.')
                  );

                  if ($model->getConfigData('order_status_payment_canceled') == Mage_Sales_Model_Order::STATE_CANCELED) {
                  $order->cancel();
                  } */

                if (!$status = $model->getConfigData('order_status_payment_canceled')) {
                    $status = $order->getStatus();
                }

                $order->addStatusToHistory(
                    $status, $this->__('Order was canceled by customer')
                );

                if ($status == Mage_Sales_Model_Order::STATE_HOLDED && $order->canHold()) {
                    $order->hold();
                } else if ($status == Mage_Sales_Model_Order::STATE_CANCELED && $order->canCancel()) {
                    $order->cancel();
                }

                $order->save();
            }
        }

        if (!$model->getConfigData('empty_cart')) {
            $this->_reorder();
        }

        $this->_redirect($this->_getErrorRedirect());
    }

    protected function _reorder()
    {
        $cart = Mage::getSingleton('checkout/cart');
        $cartTruncated = false;
        /* @var $cart Mage_Checkout_Model_Cart */

        foreach ($this->getRealOrderIds() as $realOrderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);

            if ($order->getId()) {
                $items = $order->getItemsCollection();
                foreach ($items as $item) {
                    try {
                        $cart->addOrderItem($item);
                    } catch (Mage_Core_Exception $e){
                        if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                            Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                        }
                        else {
                            Mage::getSingleton('checkout/session')->addError($e->getMessage());
                        }
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addException($e,
                            Mage::helper('checkout')->__('Cannot add the item to shopping cart.')
                        );
                    }
                }
            }
        }

        $cart->save();
    }

    protected function _getSuccessRedirect()
    {
        if ($this->getQuote()->getIsMultiShipping())
            return 'checkout/multishipping/success';
        else
            return 'checkout/onepage/success';
    }

    protected function _getErrorRedirect()
    {
        if ($this->getQuote()->getIsMultiShipping()) {
            return 'checkout/cart';
        } else {
            return 'checkout/onepage/failure';
        }
    }
}
