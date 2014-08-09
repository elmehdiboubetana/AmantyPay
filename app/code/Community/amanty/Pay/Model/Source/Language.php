<?php

class Amanty_Pay_Model_Source_Language
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'EN', 'label' => Mage::helper('Pay')->__('English')),
            array('value' => 'FR', 'label' => Mage::helper('Pay')->__('French')),
        );
    }
}



