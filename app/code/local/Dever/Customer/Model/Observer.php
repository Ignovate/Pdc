<?php
/**
 * Created by PhpStorm.
 * User: prabugoodhope
 * Date: 22/09/18
 * Time: 7:59 AM
 */

class Dever_Customer_Model_Observer
{
    public function validateRetailstore($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        /** @var Mage_Customer_Model_Resource_Customer_Collection $collection */
        $collection = Mage::getModel('customer/customer')->getCollection();
        $attrCount = $collection->addAttributeToFilter('retailstores', array('eq' => $customer->getRetailstores()))
            ->count();

        if ($attrCount >= 2) {
            Mage::throwException("Retailstore can be linked to only 2 customers");
        }

    }
}