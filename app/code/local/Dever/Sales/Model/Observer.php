<?php

class Dever_Sales_Model_Observer
{
    public function triggerFcm($observer)
    {
        $debug = true;
        $order = $observer->getEvent()->getOrder();
        $status = $order->getStatus();
        $originalData = $order->getOrigData();
        $previousStatus = $originalData['status'];
        if ($status == $previousStatus) {
            return;
        }
        
        /** @var Dever_Sms_Helper_Fcm $helper */
        $helper = Mage::helper('dever_sms/fcm');
        $customerId =$order->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $message = "Dear {$customer->getName()}, Your Order {$order->getIncrementId()} is currently in {$status} status.";
        //Multi Fcm logic starts here
        $notificationList = array();
        /** @var Dever_Customer_Model_Fcm $model */
        $model = Mage::getModel('dever_customer/fcm');
        $fcmIds = $model->filterByCustomer($customerId);
       
        foreach ($fcmIds as $each)
        {
            $helper->sendSms($each, $message);
            //Trigger Notification Event to log messages
            $notificationList[] = array(
                'fcm_id' => $each,
                'customer_id' => $customerId,
                'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'email' => $customer->getEmail(),
                'message' => $message,
                'created_date' => date('Y-m-d H:i:s', strtotime($order->getCreatedAt(). ' + 240 mins'))
            );
        }
        Mage::dispatchEvent('log_notification_messages', array('notification' => $notificationList));
        //Multi Fcm logic ends here
    }
}