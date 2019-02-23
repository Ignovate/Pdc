<?php

class Dever_Sales_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function triggerFcm($order)
    {
        /** @var Dever_Sms_Helper_Fcm $helper */
        $helper = Mage::helper('dever_sms/fcm');
        $customerId = $order->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $message = "Dear {$customer->getName()}, Thanks for your Order. Your Order {$order->getIncrementId()} is submitted with PDC team. 
                We will get back to you shortly.";
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