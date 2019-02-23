<?php
class Dever_Sales_Model_Bulk extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('dever_sales/bulk');
    }
}