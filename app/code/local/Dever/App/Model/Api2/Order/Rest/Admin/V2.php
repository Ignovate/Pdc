<?php


class Dever_App_Model_Api2_Order_Rest_Admin_V2
    extends Dever_App_Model_Api2_Order_Abstract
{
    public function _create(array $request)
    {
        $debug = true;
        if (empty($request)) {
            $this->_critical(Dever_Api2_Model_Resource::RESOURCE_REQUEST_DATA_INVALID);
        }

        // Validate if consumer key is set in request and if it exists
        $consumer = Mage::getModel('oauth/consumer');
        if (empty($request['api_key'])) {
            Mage::throwException('Consumer key is not specified');
        }
        $consumer->load($request['api_key'], 'key');
        if (!$consumer->getId()) {
            Mage::throwException('Consumer key is incorrect');
        }

        $response = $this->createOrder($request);

        return $response;
    }

    /**
     * Create Sales Order
     *
     * @param $orderData
     */
    public function createOrder($orderData)
    {
        $debug = true;
        if (!empty($orderData)) {

            $this->_initSession($orderData['session']);

            try {
                $this->_processQuote($orderData);
                if (!empty($orderData['payment'])) {
                    $this->_getOrderCreateModel()->setPaymentData($orderData['payment']);
                    $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($orderData['payment']);
                }

                $order = $this->_getOrderCreateModel()
                    ->importPostData($orderData['order'])
                    ->createOrder();

                $this->_getSession()->clear();

                //Delete Quote
                if ($order->getIncrementId()) {
                    $quote = Mage::getModel("sales/quote")
                        ->setStoreId(self::DEFAULT_STORE)
                        ->load($orderData['quote_id'])
                    ;
                    $quote->delete();

                }

                //Trigger Fcm
                /** @var Dever_Sales_Helper_Data $helper */
                $helper = Mage::helper('dever_sales');
                $helper->triggerFcm($order);

                $response = array (
                    'status' => "success",
                    'message' => "Order {$order->getIncrementId()} Created Successfully"
                );

            } catch (Exception $e){

                $response = array (
                    'status' => "error",
                    'message' => (string)$e->getMessage()
                );
            }

            return $response;
        }
    }

    /**
     * Prepare and Process Quote for Sales Order Creation
     *
     * @param array $data
     * @return $this
     */
    protected function _processQuote($data = array())
    {
        /* Saving order data */
        if (!empty($data['order'])) {
            $this->_getOrderCreateModel()->importPostData($data['order']);
        }

        $this->_getOrderCreateModel()->getBillingAddress();
        $this->_getOrderCreateModel()->setShippingAsBilling(true);

        /* Add Product */
        if (!empty($data['items'])) {
            $itemArr = array();
            foreach ($data['items'] as $productId => $qty) {
                $itemArr[$productId] = array ('qty' => $qty);
            }
            $this->_getOrderCreateModel()->addProducts($itemArr);
        }

        /* Collect shipping rates */
        $this->_getOrderCreateModel()->collectShippingRates();

        /* Add payment data */
        if (!empty($data['payment'])) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($data['payment']);
        }

        $this->_getOrderCreateModel()
            ->initRuleData()
            ->saveQuote();

        if (!empty($data['payment'])) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($data['payment']);
        }

        return $this;
    }

    public function _retrieve()
    {
        $orderId = $this->getRequest()->getParam('id');
        if (empty($orderId)) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        try {
            $order = Mage::getModel('sales/order')->load($orderId);
            return $this->_buildOrderData($order);
        } catch (Exception $e) {
            throw new Mage_Api2_Exception(
                $e->getMessage(),
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR
            );
        }
    }

    /**
     * Attempt to edit existing order items
     *
     * @param array $orderData
     * @return array|void
     * @throws Exception
     */
    public function _update($orderData)
    {
        $orderId = $this->getRequest()->getParam('id');
        if (!empty($orderData)) {
            try {
                //Load order by incrementid
                /** @var Mage_Sales_Model_Order $order */
                $order = Mage::getModel('sales/order')->load($orderId);
                foreach($order->getAllItems() as $item) {
                    $item->delete();
                }

                $total = 0;
                foreach ($orderData['items'] as $id => $qty)
                {
                    $product = Mage::getModel('catalog/product')->load($id);
                    $rowTotal = ($product->getPrice() * $qty);
                    /** @var Mage_Sales_Model_Order_Item $orderItem */
                    $orderItem = Mage::getModel('sales/order_item');
                    $orderItem->setStoreId(self::DEFAULT_STORE)
                        ->setQuoteItemId(NULL)
                        ->setQuoteParentItemId(NULL)
                        ->setProductId($product->getId())
                        ->setProductType($product->getTypeId())
                        ->setQtyBackordered(NULL)
                        ->setTotalQtyOrdered($qty)
                        ->setQtyOrdered($qty)
                        ->setName($product->getName())
                        ->setSku($product->getSku())
                        ->setPrice($product->getPrice())
                        ->setBasePrice($product->getPrice())
                        ->setOriginalPrice($product->getPrice())
                        ->setRowTotal($rowTotal)
                        ->setBaseRowTotal($rowTotal)
                        ->setOrder($order);
                    $orderItem->save();
                    $total += $rowTotal;
                }

                $order->setSubtotal($total)
                    ->setBaseSubtotal($total)
                    ->setGrandTotal($total)
                    ->setBaseGrandTotal($total);

                if ($order->save()) {
                    $response = array (
                        'status' => "success",
                        'message' => "Order {$order->getIncrementId()} Edit Success"
                    );
                }

            } catch (Exception $e){

                $response = array (
                    'status' => "error",
                    'message' => (string)$e->getMessage()
                );
            }

            return $response;
        }
    }
}