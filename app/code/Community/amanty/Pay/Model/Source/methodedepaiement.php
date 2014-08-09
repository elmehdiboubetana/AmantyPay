<?php

class Amanty_Pay_Model_methodedepaiement
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'cb', 'label' => 'Carte Bancaire'),
            array('value' => 'mb', 'label' => 'Mobicash'),
            array('value' => 'bi', 'label' => 'Binga (WafaCash)')
        );
    }
}



