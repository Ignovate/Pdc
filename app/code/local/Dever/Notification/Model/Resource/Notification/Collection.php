<?php

class Dever_Notification_Model_Resource_Notification_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('dever_notification/notification');
    }
}