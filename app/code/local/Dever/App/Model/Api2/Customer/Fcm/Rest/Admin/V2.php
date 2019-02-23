<?php

class Dever_App_Model_Api2_Customer_Fcm_Rest_Admin_V2
    extends Dever_App_Model_Api2_Customer_Fcm_Abstract
{
    protected function _delete()
    {
        $fcmId = $this->getRequest()->getParam('fcmid');
        if (empty($fcmId)) {
            Mage::throwException('Fcm ID is not specified');
        }

        try {
            
                /** @var Dever_Customer_Model_Fcm $fcm */
            $fcm = Mage::getModel('dever_customer/fcm');
            $fcm->deleteFcm($fcmId);
            
        } catch (Exception $e) {
            throw new Mage_Api2_Exception(
                $e->getMessage(),
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR
            );
        }
    }
}