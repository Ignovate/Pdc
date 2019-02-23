<?php
class Dever_Sales_Model_Resource_Bulk_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('dever_sales/bulk');
    }
}