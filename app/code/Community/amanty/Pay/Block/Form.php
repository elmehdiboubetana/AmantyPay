<?php


class Amanty_Pay_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('Pay/form.phtml');
        parent::_construct();
    }
}
