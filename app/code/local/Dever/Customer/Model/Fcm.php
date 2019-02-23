<?php
class Dever_Customer_Model_Fcm extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('dever_customer/fcm');
    }
    public function deleteFcm($id)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('fcm_id', $id)
            ->load();
        foreach ($collection as $data) {
            $model = $this->load($data->getId());
            $model->delete();
        }
    }
    public function filterByCustomer($id)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('customer_id', $id)
            ->load();
        $ids = array();
        foreach ($collection as $data) {
            $model = $this->load($data->getId());
            $ids[] = $model->getFcmId();
        }
        return $ids;
    }
} 