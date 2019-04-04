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
        $this->_apiKey = 'AIzaSyCS15paXe3_5yW82GuBsnLEDqPeTLQQddo';

        return $this;
    }

    public function sendSms($fcmId, $message)
    {
         $msg = array (
            
            'body' 	=> $message,
            'title'		=> 'PDC Order Update',
            'click_action' => 'notification',
            'vibrate'	=> 1,
            'sound'		=> 1,
            'largeIcon'	=> 'https://www.pdcorders.com/appimages/logo.pn',
            'smallIcon'	=> 'https://www.pdcorders.com/appimages/logo.pn',
            'icon'	=> 'https://www.pdcorders.com/appimages/logo.pn',
            'priority' => 'high',
            'show_in_foreground' => true
        );
        $fields = array (
            'to' 	=> $fcmId,
            'notification'=> array(
                        'title' => "PDC Order Update",
                        'body'=> $message,
                        'sound'=> "default",
                        'priority' => "high",
                        'show_in_foreground'=> true,
                        'targetScreen'=> "detail",
                        'click_action' => 'notification',
                        'channel' => 'default'
                    ),
            'priority' => 10
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