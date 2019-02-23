<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 08/11/17
 * Time: 12:41 PM
 */
class Dever_App_Model_Api2_Cart_Abstract extends Dever_Api2_Model_Resource
{
    protected function _buildQuote($quote)
    {
        $quoteData = array();

        $quoteData = array_merge($quoteData, array(
            'quote_id'                      => $quote->getId(),
            'subtotal'                      => $quote->getShippingAddress()->getSubtotal(),
            'subtotal_with_discount'        => $quote->getShippingAddress()->getSubtotalWithDiscount(),
            'grand_total'                   => $quote->getShippingAddress()->getGrandTotal(),
            'currency_code'                 => $quote->getQuoteCurrencyCode(),
            'cod_fee'                       => $quote->getShippingAddress()->getCodFee(),
            'shipping_fee'                  => $quote->getShippingAddress()->getShippingAmount(),
            'discount_amount'               => $quote->getShippingAddress()->getDiscountAmount(),
            'tax_amount'                    => $quote->getShippingAddress()->getTaxAmount()
        ));

        if (empty($quote->getAllVisibleItems()) || $quote->getAllVisibleItems() == '') {
            $quoteData['items'] = array();
        } else {
            foreach ($quote->getAllVisibleItems() as $item) {

                $product = Mage::getModel('catalog/product')->load($item->getProductId());

                $itemData = array(
                    'item_id'        => $item->getItemId(),
                    'product_id'     => $item->getProductId(),
                    'product_sku'    => $item->getSku(),
                    'product_name'   => $item->getName(),
                    'qty'            => $item->getQty(),
                    'price'          => $item->getPrice(),
                    'base_price'     => $item->getBasePrice(),
                    'row_total'      => $item->getRowTotal(),
                    'thumbnail'      => $product->getThumbnail(),
                    'small_image'    => $product->getSmallImage(),
                    'supplier'      => $product->getAttributeText('supplier')
                );

                $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($quote->getCustomerId(), true);
                $collection = Mage::getModel('wishlist/item')->getCollection()
                    ->addFieldToFilter('store_id', $quote->getStoreId())
                    ->addFieldToFilter('wishlist_id', $wishlist->getId())
                    ->addFieldToFilter('product_id', $item->getProductId());
                $item = $collection->getFirstItem();
                $isWishlist = 0;
                if ($item->getId()) {
                    $isWishlist = 1;
                }
                $itemData['is_wishlist'] = $isWishlist;

                $quoteData['items'][] = $itemData;
            }
        }


        // Add address info into quote data
        $quoteData['customer'] = array (
            'customer_id'   => $quote->getCustomerId(),
            'customer_email' => $quote->getCustomerEmail()
        );
        $customerAddress = array();
        $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
        foreach ($customer->getAddresses() as $address) {
            $customerAddress[] = $address->toArray();
        }
        $quoteData['customer']['address'] = $customerAddress;
        $quoteData['shipping'] = $this->getShippingMethods();

        $quoteData['payment'] = $this->getPaymentMethods();

        return $quoteData;
    }

    public function getShippingMethods($isMultiSelect = false)
    {
        $debug = true;
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $options = array();
        foreach($methods as $_code => $_method)
        {
            if(!$_title = Mage::getStoreConfig("carriers/$_code/title"))
                $_title = $_code;
            $options[] = array(
                'value' => $_code . '_' . $_code,
                'label' => $_title,
                'rate'  => Mage::getStoreConfig("carriers/$_code/price")
            );
        }
        if($isMultiSelect) {
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }
        return $options;
    }

    public function getPaymentMethods()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();

        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            if ($paymentCode == 'paypal_billing_agreement') {
                continue;
            }
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            $methods[] = array(
                'label'   => $paymentTitle,
                'value' => $paymentCode,
            );
        }
        return $methods;
    }

    protected function _getFinalPrice($product)
    {
        //Look for special price
        if ($product->getSpecialPrice() > 0) {
            $price = $product->getSpecialPrice();
        } else {
            $price = $product->getPrice();
        }

        return $price;
    }
}