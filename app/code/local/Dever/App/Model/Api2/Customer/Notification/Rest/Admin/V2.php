<?php

class Dever_App_Model_Api2_Customer_Notification_Rest_Admin_V2
    extends Dever_App_Model_Api2_Customer_Notification_Abstract
{
    /**
     * Retrieve notification by customer
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $customerId = $this->getRequest()->getParam('customerid');
        if (empty($customerId)) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        try {
            /** @var Dever_Notification_Model_Notification $model */
            $model = Mage::getModel('dever_notification/notification');
            $collection = $model->loadByCustomer($customerId);
            $response = array(
                'status' => 'success',
                'response'   => $collection
            );

        } catch (Exception $e) {
            $response = array(
                'status' => 'error',
                'response'   => $e->getMessage()
            );
        }
        return $response;
    }
}