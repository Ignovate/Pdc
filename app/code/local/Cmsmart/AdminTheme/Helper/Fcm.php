<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 05/10/16
 * Time: 3:17 PM
 */
class Ignovate_Sms_Helper_Fcm extends Mage_Core_Helper_Abstract
{
    protected $_url;
    protected $_apiKey;
    protected $_senderId;

    public function __construct()
    {
        $this->_apiKey = 'AIzaSyAHPG9ibkDceJUVXez5xr9iIuLZgBm7ruI';

        return $this;
    }

    public function sendSms($order)
    {
        //Get Customer Fcm
        $customerId = $order->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $fcmId = $customer->getFcmId();

        // prep the bundle
        $message = "Dear Customer, The status of your AgentX Order {$order->getIncrementId()} is {$order->getStatusLabel()}";
        $msg = array (
            'body' 	=> $message,
            'title'		=> 'AgentX Order Delivery',
           // 'subtitle'	=> 'This is a subtitle. subtitle',
          //  'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
            'vibrate'	=> 1,
            'sound'		=> 1,
            'largeIcon'	=> 'large_icon',
            'smallIcon'	=> 'small_icon'
        );
        $fields = array (
            'to' 	=> $fcmId,
         // 'to' => 'fi44PJYsCk4:APA91bEZJqyBzMR4vHgjjxZ9LAQssisLSDUSOy1FYf3niU6rHJa9oPERzqOZqUWbTIbs7IbAC7cnw6NylqabpQFigKmumjg_AbaDtzVsnFOAehvphzTan0Zl2qvZeeDl76rpOQKLZBkE',
            'notification'			=> $msg
            //'data'			=> $msg
        );

        $headers = array(
            'Authorization: key=' . $this->_apiKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        //curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
    }

}