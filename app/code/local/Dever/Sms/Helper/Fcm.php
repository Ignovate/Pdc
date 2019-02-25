<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 05/10/16
 * Time: 3:17 PM
 */
class Dever_Sms_Helper_Fcm extends Mage_Core_Helper_Abstract
{
    protected $_url;
    protected $_apiKey;
    protected $_senderId;

    public function __construct()
    {
        $this->_apiKey = 'AIzaSyATlqcsgc6mPyKkWZWdYqaphrir85j_2hg';

        return $this;
    }

    public function sendSms($fcmId, $message)
    {
        $msg = array (
            'body' 	=> $message,
            'title'		=> 'PDC Order Update',
            'vibrate'	=> 1,
            'sound'		=> 1,
            'largeIcon'	=> 'https://www.pdcorders.com/appimages/logo.png',
            'smallIcon'	=> 'https://www.pdcorders.com/appimages/logo.png',
            'icon'	=> 'https://www.pdcorders.com/appimages/logo.png',
            'priority' => 'high',
            'show_in_foreground' => true
        );
        $fields = array (
            'to' 	=> $fcmId,
            'notification'  => $msg
        );

        $headers = array(
            'Authorization: key=' . $this->_apiKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
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