<?php

class Dever_App_Model_Api2_Customer_Wishlist_Rest_Admin_V2
    extends Dever_App_Model_Api2_Customer_Wishlist_Abstract
{
    /**
     * Retrieve all wishlist items for customer id
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $items = $this->_getItems(
            $customerId,
            self::DEFAULT_STORE
        );
        return $items;
    }

    /**
     * New wishlist item
     */
    protected function _create(array $data)
    {
        $debug = true;
        $customerId = (int)$this->getRequest()->getParam('customer_id');
        $storeId = self::DEFAULT_STORE;

        $productId  = isset($data['product_id']) ? $data['product_id'] : false;
        try {
            if (!$productId) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product->getId() || !$product->isVisibleInCatalog()) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }

            /** @var Mage_Wishlist_Model_Wishlist $wishlist */
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
            $wishlist->save();

            $item = Mage::getModel('wishlist/item');
            $item->setProductId($product->getId())
                ->setWishlistId($wishlist->getId())
                ->setAddedAt(now())
                ->setStoreId($storeId)
                ->setQty(1)
                ->save();
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message'   => $e->getMessage()
            );
        }

        return array(
            'status' => "success",
            'message'   => "Item {$product->getName()} added to wishlist"
        );
    }

    /**
     * Delete wishlist item for customer id
     */
    protected function _delete()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $productId     = (int)$this->getRequest()->getParam('product_id');
        $storeId     = self::DEFAULT_STORE;

        $response = array();
        try {
            /** @var Mage_Wishlist_Model_Wishlist $wishlist */
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer($customerId);
            if (null !== $wishlist->getId()) {
                /** @var Mage_Wishlist_Model_Resource_Item_Collection $collection */
                $collection = Mage::getResourceModel('wishlist/item_collection');
                $collection->addWishlistFilter($wishlist)
                    ->addStoreFilter($storeId)
                    ->setVisibilityFilter();
                $item  = $collection->getItemByColumnValue('product_id', $productId);
                $product = Mage::getModel('catalog/product')->load($productId);
                if ($item) {
                    $item->delete();
                    $response = array(
                        'status' => 'success',
                        'message'   => "Item {$product->getName()} removed from wishlist"
                    );
                }
            }
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message'   => $e->getMessage()
            );
        }

        return $response;
    }

    /**
     * Get wishlist items prepared for rest response
     *
     * @return array
     */
    protected function _getItems($customerId, $storeId)
    {
        $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId);

        if (null === $wishlist->getId()) {
            return array();
        }

        /** @var Mage_Wishlist_Model_Resource_Item_Collection $collection */
        $collection = Mage::getResourceModel('wishlist/item_collection');
        $collection->addWishlistFilter($wishlist)
            ->addStoreFilter($storeId)
            ->setVisibilityFilter();

        //$collection = $wishlist->getItemCollection();
        $productIds = array();
        foreach ($collection as $item) {
            $productIds[] = $item->getProductId();
        }

        $collectionSelect = $this->getAdapter()->select()
            ->from(
                array('product' => 'catalog_product_flat_' . $storeId),
                array(
                    'product_id'        => 'product.entity_id',
                    'name'              => 'product.name',
                    'url_path'          => 'product.url_path',
                    'url_key'           => 'product.url_key',
                    'sku'               => 'product.sku',
                    'price'             => 'product.price',
                    'special_price'     => 'product.special_price'
                )
            );

        $collectionSelect->joinLeft(
            array ('cat' => 'catalog_category_product'),
            'cat.product_id = product.entity_id'
        );

        $collectionSelect->where(
            'product.entity_id IN (?)', $productIds
        );

        $str = (string)$collectionSelect;

        $indexData = $this->getAdapter()->query($collectionSelect)->fetchAll();
        $final = array();
        //Dummy flag to maintain structure
        foreach ($indexData as $key => $data) {
            $data['is_wishlist'] = 1;
            $final[] = $data;
        }

        return $final;
    }

}