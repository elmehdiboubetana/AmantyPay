<?php


class Amanty_Pay_Model_System_Config_Source_Order_Status_Canceled extends Amanty_Pay_Model_System_Config_Source_Order_Status
{
    // set null to enable all possible
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_HOLDED,
        Mage_Sales_Model_Order::STATE_CANCELED
    );
}
