<?php

class Dever_Retailstore_Model_Resource_Retailstore extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('dever_retailstore/retailstore', 'id');
    }
}