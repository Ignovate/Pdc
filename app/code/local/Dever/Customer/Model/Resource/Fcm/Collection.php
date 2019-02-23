<?php
class Dever_Customer_Model_Resource_Fcm_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('dever_customer/fcm');
    }
} 