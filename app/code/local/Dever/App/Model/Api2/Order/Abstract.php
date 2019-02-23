<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 08/11/17
 * Time: 12:41 PM
 */
class Dever_App_Model_Api2_Order_Abstract extends Dever_Api2_Model_Resource
{
    /**
     * Initialize order creation session data
     *
     * @param $data
     * @return $this
     */
    protected function _initSession($data)
    {
        /* Get/identify customer */
        if (!empty($data['customer_id'])) {
            $this->_getSession()->setCustomerId((int) $data['customer_id']);
        }
        /* Get/identify store */
        $this->_getSession()->setStoreId(self::DEFAULT_STORE);
        /* Get/identify store */
        if (!empty($data['quote_id'])) {
            $this->_getSession()->setQuoteId((int) $data['quote_id']);
        }
        return $this;
    }
    /**
     * Retrieve order create model
     *
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }
    /**
     * Retrieve session object
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }
    /**
     * Retrieve Product Model
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::getModel('catalog/product');
    }
    /**
     * Prepare Order Response Data
     *
     * @param $order
     * @return array
     */
    protected function _buildOrderData($order)
    {
        $date = date('Y-m-d H:i:s', strtotime($order->getCreatedAt(). ' + 240 mins'));
        $orderData = array (
            'order_number' => $order->getIncrementId(),
            'grand_total' => $order->getGrandTotal(),
            'ordered_date' => $date,
            'status_label' => Mage::helper('core')->__($order->getStatusLabel()),
            'tax_amount'    => $order->getTaxAmount()
        );
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $orderData['customer'] = array (
            'customer_id' => $customer->getId(),
            'customer_email' => $customer->getEmail(),
            'name'  => $customer->getFirstname() . ' ' . $customer->getLastname()
        );
        //Build Order item details
        $productIds = array();
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getProductType() == 'configurable') {
                continue;
            }
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            //Remove decimal in qty
            $qty = floatval($item->getQtyOrdered());
            $qtyAccepted = floatval($item->getQtyInvoiced());
            $itemData = array(
                'item_id' => $item->getItemId(),
                'product_id' => $item->getProductId(),
                'product_sku' => $item->getSku(),
                'product_name' => $item->getName(),
                'qty' => $qty,
                'price' => $item->getPrice(),
                'base_price' => $item->getBasePrice(),
                'row_total' => $item->getRowTotal(),
                'supplier' => $product->getAttributeText('supplier'),
                'qty_ordered' => $item->getQtyOrdered(),
                'qty_invoiced' => $qtyAccepted,
                'qty_shipped' => $item->getQtyShipped(),
                'qty_canceled' => $item->getQtyCanceled(),
            );
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($order->getCustomerId(), true);
            $collection = Mage::getModel('wishlist/item')->getCollection()
                ->addFieldToFilter('store_id', $order->getStoreId())
                ->addFieldToFilter('wishlist_id', $wishlist->getId())
                ->addFieldToFilter('product_id', $item->getProductId());
            $item = $collection->getFirstItem();
            $isWishlist = 0;
            if ($item->getId()) {
                $isWishlist = 1;
            }
            $itemData['is_wishlist'] = $isWishlist;
            $orderData['items'][] = $itemData;
        }
        // Order Address details
        foreach ($customer->getAddresses() as $address) {
            $customerAddress[] = $address->toArray();
        }
        $address = $order->getBillingAddress();
        if ($address && $address->getId()) {
            $orderData['billing'] = $address->getData();
        }
        $address = $order->getShippingAddress();
        if ($address && $address->getId()) {
            $orderData['shipping'] = $address->getData();
        }
        $orderData['shipping_method'] = array (
            'value' => $order->getShippingAmount(),
            'code' => $order->getShippingMethod(),
            'label' => $order->getShippingDescription()
        );
        $payment = $order->getPayment();
        if ($payment && $payment->getId()) {
            $method = $payment->getMethod();
            $paymentTitle = Mage::getStoreConfig('payment/'.$method.'/title');
            $orderData['payment_method'] = array (
                'code' => $payment->getMethod(),
                'label' => $paymentTitle
            );
        }
        return $orderData;
    }
}