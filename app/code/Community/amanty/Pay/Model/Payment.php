<?php

class Amanty_Pay_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'Pay_payment';
    protected $_formBlockType = 'Pay/form';

    // Pay return codes of payment
    const RETURN_CODE_ACCEPTED      = 'paiement';
    const RETURN_CODE_TEST_ACCEPTED = 'payetest';
    const RETURN_CODE_ERROR         = 'Annulation';

    // Payment configuration
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;

    // Order instance
    protected $_order = null;

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function getPayUrl()
    {
        $url = $this->getConfigData('actionslkprod');
        return $url;
    }


    /**
     *  Return URL for Pay success response
     *
     *  @return	  string URL
     */
    protected function getSuccessURL()
    {
        return Mage::getUrl('Pay/payment/success', array('_secure' => true));
    }

    
    /**
     *  Return URL for Pay notify response
     *
     *  @return	  string URL
     */
    protected function getNotifyURL()
    {
        return Mage::getUrl('Pay/payment/notify', array('_secure' => true));
    }

    /**
     * Get quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        if (!$this->_quote) {
            $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
            $this->_quote = Mage::getModel('sales/quote')->load($quoteId);
        }
        return $this->_quote;
    }

    /**
     * Get real order ids
     *
     * @return string
     */
    public function getOrderid()
    {
        $orders = Mage::getModel('sales/order')->getCollection()
                ->setOrder('created_at','DESC')
                ->setPageSize(1)
                ->setCurPage(1);
        
        $orderId = $orders->getFirstItem()->getEntityId();
        return '1000000'.$orderId;
    }

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
                ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @param   Varien_Object $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote->setCustomerNoteNotify(false);
        parent::validate();
    }

    /**
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('Pay/form_payment', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());
        return $block;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        Mage::getSingleton('checkout/session')->setIsMultishipping(false);
        return Mage::getUrl('Pay/payment/redirect');
    }

    public function getAmount() {
        if ($this->getQuote()->getIsMultiShipping()) {
            $amount = $this->getQuote()->getBaseGrandTotal();
        } else {
            $amount = $this->getOrder()->getBaseGrandTotal();
        }
        return sprintf('%.2f', $amount);
    }

    /**
     *  Return Standard Checkout Form Fields for request to Pay
     *
     *  @return	  array Array of hidden form fields
     */
    public function getStandardCheckoutFormFields()
    {
        $session = Mage::getSingleton('checkout/session');

        $order = $this->getOrder();
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
        }

        $billingAddress = $order->getBillingAddress();
        $datetx=rand(10,100000);
        $fields = array();
        $fields['idpartner']= $this->getConfigData('storeidprod');
        $fields['numaudit'] = $datetx;
		$fields['fnameclt'] = $billingAddress->getFirstname();
        $fields['lnameclt'] = $billingAddress->getLastname();
        $fields['emailclt'] = $order->getCustomerEmail();
        $fields['numcmd']=$this->getOrderid();
        $fields['mntcmd']=$this->getAmount();
        $fields['descriptcmd']='Commande Sofiatech';
        $fields['datecmd']=$order->getCreatedAt();
        $fields['url']='votre url de reponse';
        $secretkey = 'votre clé';
        $var = $fields['idpartner'].$fields['numaudit'].$fields['numcmd'].$fields['mntcmd'].$fields['url'];
        
        $fields['mac']=md5($var.$secretkey);
        return $fields;
    }

}