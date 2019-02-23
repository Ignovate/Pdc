<?php

class Dever_App_Model_Api2_Homescreen_Rest_Admin_V2
    extends Dever_App_Model_Api2_Homescreen_Abstract
{
    public function _retrieveCollection()
    {
        $storeCode = $this->getRequest()->getParam('store');
        $storeId = Mage::app()->getStore($storeCode)->getId();

        $limit = $this->getRequest()->getParam('limit');

        $t1['bestsellers'] = $this->bestSellers($storeId, $limit);
        $t2['categories'] = $this->topCategories($storeId);

        $temp = array_merge($t1, $t2);

        return $temp;
    }

    protected function bestSellers($storeId, $limit)
    {
        $readAdapter = Mage::getSingleton('core/resource')
            ->getConnection('core_read');

        $collectionSelect = $readAdapter->select()
            ->from(
                array('product' => 'catalog_product_flat_' . $storeId),
                array(
                    'product_id'        => 'product.entity_id',
                    'name'              => 'product.name',
                    'url_path'          => 'product.url_path',
                    'url_key'           => 'product.url_key',
                    'sku'               => 'product.sku',
                )
            );

        $collectionSelect
            ->where(
                'mode_value = ?', 'BestSeller'
            )
            ->order(
                'RAND()'
            )
            ->limit($limit);

        $response = $readAdapter->query($collectionSelect)->fetchAll();

        return $response;
    }

    public function topCategories($storeId)
    {
        $readAdapter = Mage::getSingleton('core/resource')
            ->getConnection('core_read');

        $collectionSelect = $readAdapter->select()
            ->from(
                array('category' => 'catalog_category_flat_store_' . $storeId),
                array(
                    'category_id'        => 'category.entity_id',
                    'name'              => 'category.name',
                    'image'             => 'category.image',
                    'url_path'          => 'category.url_path',
                    'url_key'           => 'category.url_key'
                )
            );

        $collectionSelect
            ->where(
                'is_active = 1'
            )
            ->where(
                'level = 2'
            );

        $response = $readAdapter->query($collectionSelect)->fetchAll();

        return $response;
    }
}