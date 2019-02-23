<?php
class Dever_Sales_Model_Resource_Bulk extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('dever_sales/bulk', 'id');
    }
} 
