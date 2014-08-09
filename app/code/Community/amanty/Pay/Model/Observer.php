<?php

class Amanty_Pay_Model_Observer
{
    /**
     *  Can redirect to Pay payment
     */
    public function initRedirect(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('checkout/session')->setCanRedirect(true);
    }

    /**
     *  Return Orders Redirect URL
     *
     *  @return	  string Orders Redirect URL
     */
    public function multishippingRedirectUrl(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('checkout/session')->getCanRedirect()) {
            $orderIds = Mage::getSingleton('core/session')->getOrderIds();
            $orderIdsTmp = $orderIds;
            $key = array_pop($orderIdsTmp);
            $order = Mage::getModel('sales/order')->loadByIncrementId($key);

            if (!(strpos($order->getPayment()->getMethod(), 'Pay') === false)) {
                Mage::getSingleton('checkout/session')->setRealOrderIds(implode(',', $orderIds));
                Mage::app()->getResponse()->setRedirect(Mage::getUrl('Pay/payment/redirect'));
            }
        } else {
            Mage::getSingleton('checkout/session')->unsRealOrderIds();
        }

        return $this;
    }

    /**
     *  Disables sending email after the order creation
     *
     *  @return	  updated order
     */
    public function disableEmailForMultishipping(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();

        if (!(strpos($order->getPayment()->getMethod(), 'Pay') === false)) {
            $order->setCanSendNewEmailFlag(false)->save();
        }

        return $this;
    }

}
