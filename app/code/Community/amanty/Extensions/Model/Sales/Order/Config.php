<?php
class Amanty_Extensions_Model_Sales_Order_Config extends Mage_Sales_Model_Order_Config
{
    public function getStateStatuses($state, $addLabels = true)
    {
        $version = Mage::getVersion();
        $version = substr($version, 0, 5);
        $version = str_replace('.', '', $version);
        while (strlen($version) < 3) {
            $version .= "0";
        }

        if (((int)$version) < 150) {
            return parent::getStateStatuses($state, $addLabels);
        }

        if (is_array($state)) {
            $key = implode('', $state) . $addLabels;
        } else {
            $key = $state . $addLabels;
        }

        if (isset($this->_stateStatuses[$key])) {
            return $this->_stateStatuses[$key];
        }
        $statuses = array();
        if (empty($state) || !is_array($state)) {
            $state = array($state);
        }
        foreach ($state as $_state) {
            if ($stateNode = $this->_getState($_state)) {
                $collection = Mage::getResourceModel('sales/order_status_collection')
                    ->addStateFilter($_state)
                    ->orderByLabel();
                foreach ($collection as $status) {
                    $code = $status->getStatus();
                    if ($addLabels) {
                        $statuses[$code] = $status->getStoreLabel();
                    } else {
                        $statuses[] = $code;
                    }
                }
            }
        }
        $this->_stateStatuses[$key] = $statuses;
        return $statuses;
    }
}
