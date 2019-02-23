<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 08/11/17
 * Time: 12:41 PM
 */
class Dever_App_Model_Api2_Customer_Wishlist_Abstract extends Dever_Api2_Model_Resource
{
    /**
     * Load customer by id
     *
     * @param int $id
     * @throws Mage_Api2_Exception
     * @return Mage_Customer_Model_Customer
     */
    protected function _loadCustomerById($id)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->load($id);
        if (!$customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $customer;
    }
}