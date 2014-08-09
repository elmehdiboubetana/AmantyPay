<?php
class Amanty_Pay_Model_System_Config_Source_Order_Status
{
    // set null to enable all possible
    protected $_stateStatuses = array();

    public function toOptionArray()
    {
        if ($this->_stateStatuses) {
            $statuses = Mage::getSingleton('sales/order_config')->getStateStatuses($this->_stateStatuses);
        } else {
            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );
        $options[] = array(
            'value' => 'tashilate',
            'label' => Mage::helper('adminhtml')->__('en attent Tashilat')
        );
        foreach ($statuses as $code => $label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }
        return $options;
    }
}
