<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 08/11/17
 * Time: 12:41 PM
 */
class Dever_App_Model_Api2_Customer_Abstract extends Dever_Api2_Model_Resource
{
    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Customer authorization
     *
     * @param   string $username
     * @param   string $password
     * @return  bool
     */
    public function login($username, $password)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(self::DEFAULT_WEBSITE);

        if ($customer->authenticate($username, $password)) {
            return true;
        }
        return false;
    }
}