<?php
class Dever_App_Model_Api2_Order_Cancel_Rest_Admin_V2 extends Dever_App_Model_Api2_Order_Abstract
{
    /**
     * Order Cancelation API
     *
     * @param array $orderData
     * @return array
     * @throws Exception
     */
    public function _update($request)
    {
        if (empty($request)) {
            $this->_critical(Dever_Api2_Model_Resource::RESOURCE_REQUEST_DATA_INVALID);
        }
        // Validate if consumer key is set in request and if it exists
        $consumer = Mage::getModel('oauth/consumer');
        if (empty($request['api_key'])) {
            Mage::throwException('Consumer key is not specified');
        }
        $consumer->load($request['api_key'], 'key');
        if (!$consumer->getId()) {
            Mage::throwException('Consumer key is incorrect');
        }
        $orderId = $this->getRequest()->getParam('id');
        if (empty($orderId)) {
            $this->_critical(Dever_Api2_Model_Resource::RESOURCE_REQUEST_DATA_INVALID);
        }
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if ($order->canCancel()) {
            try {
                $order->cancel();
                $history = $order->addStatusHistoryComment('Order marked as cancelled.', false);
                $history->setIsCustomerNotified(false);
                $order->save();
                $response = array (
                    'status' => "success",
                    'message' => "Order {$order->getIncrementId()} Created Successfully"
                );
            } catch (Exception $e) {
                $response = array (
                    'status' => "error",
                    'message' => (string)$e->getMessage()
                );
            }
        }
        return $response;
    }
}