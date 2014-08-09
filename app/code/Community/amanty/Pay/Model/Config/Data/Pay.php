<?php

class Amanty_Pay_Model_Config_Data_Pay extends Mage_Core_Model_Config_Data
{
    public function  _beforeSave() {
        $filename = BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Mage' . DS . 'Pay' . DS . 'etc' . DS . 'config.xml';

        if (file_exists($filename)) {
            Mage::getSingleton('adminhtml/session')->addError('Warning: the module Mage_Pay must be uninstalled.');
        }

        return $this;
    }
}
