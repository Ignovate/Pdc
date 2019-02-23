<?php

class Dever_Oauth_Model_Token extends Mage_Oauth_Model_Token
{
    protected $_consumerId = null;

    public function loadByCustomer($customer)
    {
        $customerId = $this->_getCustomerId($customer);
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');

        $select = $adapter->select()
            ->from(
                array('token' => $resource->getTableName(
                    'oauth/token'
                ))
            )->where(
                'token.customer_id = ?', $customerId
            )->where(
                'token.revoked = 0'
            )->where(
                'token.authorized = 1'
            );
        $token = $adapter->query($select)->fetch();

        if (!empty($token)) {
            $this->setData($token);
        }

        return $this;
    }

    /**
     * Build token object based on given customer ID
     *
     * @param $customer
     * @return Ignovate_Oauth_Model_Token
     */
    public function createFromCustomer($customer)
    {
        $customerId = $this->_getCustomerId($customer);
        $helper = Mage::helper('oauth');
        $this->setData(array(
            'consumer_id'   => $this->getConsumerId(),
            'customer_id'   => $customerId,
            'type'          => Mage_Oauth_Model_Token::TYPE_ACCESS,
            'token'         => $helper->generateToken(),
            'secret'        => $helper->generateTokenSecret(),
            'authorized'    => 1,
            'callback_url'  => Mage::getBaseUrl(),
        ));

        return $this;
    }


    public function setConsumerId($consumerId)
    {
        $this->_consumerId = $consumerId;

        return $this;
    }

    public function getConsumerId()
    {
        if (is_null($this->_consumerId)) {
            Mage::throwException('Consumer key is not specified');
        }

        return $this->_consumerId;
    }

    /**
     * Validate customer value and convert it to the customer ID
     * according to the type of value
     *
     * @param int|Mage_Customer_Model_Customer $customer
     * @return int|mixed|string
     */
    protected function _getCustomerId($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            if (!$customer->getId()) {
                Mage::throwException(
                    'Customer has to be loaded to fetch active token'
                );
            }
            $customer = $customer->getId();
        } elseif (!is_numeric($customer)) {
            Mage::throwException(
                'Customer has to be and object or numeric to load token'
            );
        }

        return $customer;
    }
}