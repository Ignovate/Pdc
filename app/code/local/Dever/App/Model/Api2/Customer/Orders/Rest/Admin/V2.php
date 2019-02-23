<?php

class Dever_App_Model_Api2_Customer_Orders_Rest_Admin_V2
    extends Dever_App_Model_Api2_Customer_Orders_Abstract
{

    public function _retrieve()
    {
        $customer = $this->_loadCustomerById(
            $this->getRequest()->getParam('id')
        );

        return $this->buildCustomerOrderObj(
            $customer,
            self::DEFAULT_STORE
        );
    }
}