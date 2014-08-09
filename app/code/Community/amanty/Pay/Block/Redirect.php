<?php

class Amanty_Pay_Block_Redirect extends Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
        $standard = Mage::getModel('Pay/payment');
        $html = '<html><body>';
        $html.='<form method="post" id="form" action="https://www.amanty.ma/ProxiPay/index.jsp">';
        foreach ($standard->setOrder($this->getOrder())->getStandardCheckoutFormFields() as $field => $value) {
            $html.='<input type="hidden" name="'.$field.'"  id="'.$field. '"  value="'.$value.'"/>';
        }
        $html.= $this->__('Vous serez redirigé vers Amanty en quelques secondes.');
        $html.='</form>';
        $html.= '<script type="text/javascript">document.getElementById("form").submit();</script>';
        $html.= '<form></body></html>';
        if ($standard->getConfigData('debug_flag')) {
            Mage::getModel('Pay/api_debug')
                    ->setRequestBody($html)
                    ->save();
        }
        return $html;
    }
}