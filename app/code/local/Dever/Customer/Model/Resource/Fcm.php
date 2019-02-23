<?php
class Dever_Customer_Model_Resource_Fcm extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('dever_customer/fcm', 'id');
    }
} 