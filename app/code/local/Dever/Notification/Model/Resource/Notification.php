<?php

class Dever_Notification_Model_Resource_Notification extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('dever_notification/notification', 'id');
    }
}