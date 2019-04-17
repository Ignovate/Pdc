<?php


require_once '../abstract.php';

class Dever_Shell_Bulk_Orders extends Mage_Shell_Abstract
{
    protected $_processData = null;

    const DEFAULT_WEBSITE = 2;

    const DEFAULT_STORE = 2;


    public function run()
    {
        
        ini_set('memory_limit', '2G');
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
        $query1 = "select * from custom_bulk_order where status = 'Pending' limit 1";
        $dataselect = $writeAdapter->fetchAll($query1);
        $this->createOrder($dataselect);
        
    }
    
    public function createOrder($orders)
    {
        if (!empty($orders) && isset($orders)) {
                    
            //print_r($orders);
            //exit;
            Mage::log("Job Started - " . date('Y-m-d H:i:s'), null, 'bulkimport.log');
            $i = 1;
            foreach ($orders as $orderData)
            {
                $q = '"';
                $orderId = $orderData['id'];
                $timestamp = $q.date("Y-m-d H:i:s").$q;
                    $resource = Mage::getSingleton('core/resource');
                    $writeAdapter = $resource->getConnection('core_write');
                    $initiatequery = "UPDATE custom_bulk_order SET status = 'initiate', timestamp = ".$timestamp." where id = ".$orderId;
                    $dataselect = $writeAdapter->query($initiatequery);
                
                 if (empty($orderData['customer_email'])) {
                    Mage::log("\t Row #{$i} Skip Row - Customer Email is empty", null, "bulkimport.log");
                    $resource = Mage::getSingleton('core/resource');
                    $writeAdapter = $resource->getConnection('core_write');
                    $emptyquery = "UPDATE custom_bulk_order SET status = 'error', timestamp = ".$timestamp.", message = 'Customer Email is empty' where id = ".$orderId;
                    $dataselect = $writeAdapter->query($emptyquery);
                    continue;
                }
 
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(self::DEFAULT_WEBSITE)
                    ->loadByEmail($orderData['customer_email']);
                
                if (empty($customer->getId())) {
                    
                    Mage::log("\t Row #{$i} Customer Email " . $orderData['customer_email'] . " does not exists", null, "bulkimport.log");
                    $resource = Mage::getSingleton('core/resource');
                    $writeAdapter = $resource->getConnection('core_write');
                    $existquery = "UPDATE custom_bulk_order SET status = 'error', message = 'Customer Email does not exists', timestamp = ".$timestamp." where id = ".$orderId;
                    $dataselect = $writeAdapter->query($existquery);
                    continue;
                }
                //Prepare from Sheet Data as accepted Order array
                $orderData = array (
                    'session' => array (
                        'customer_id' => $customer->getId(),
                        'store_id' => self::DEFAULT_STORE
                    ),
                    'payment' => array (
                        'method' => 'cashondelivery'
                    ),
                    'items' => $this->_buildItems($orderData['items'], $orderId),
                    'order' => array (
                        'currency' => 'AED',
                        'shipping_address' => array (
                            'firstname' => $orderData['firstname'],
                            'lastname' => $orderData['lastname'],
                            'street' => $orderData['street'],
                            'country_id' => $orderData['country_id'],
                            'city' => $orderData['city'],
                            'postcode' => $orderData['postcode'],
                            'telephone' => $orderData['telephone']
                        ),
                        'billing_address' => array (
                            'firstname' => $orderData['firstname'],
                            'lastname' => $orderData['lastname'],
                            'street' => $orderData['street'],
                            'country_id' => $orderData['country_id'],
                            'city' => $orderData['city'],
                            'postcode' => $orderData['postcode'],
                            'telephone' => $orderData['telephone']
                        )
                    ),
                    'shipping_method' => 'freeshipping_freeshipping',
                    'comment' => 'Order Created using sheet'
                );
                
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
                  
                        if(empty($order->getIncrementId())){
                            echo "run create"; echo $orderId; 
                        }else{
                            echo "inside if \n";
                            $resource = Mage::getSingleton('core/resource');
                            $writeAdapter = $resource->getConnection('core_write');
                            $query1 = "UPDATE custom_bulk_order SET status = 'complete',customer_id = ".$customer->getId().", order_id = '".$order->getIncrementId()."', timestamp = ".$timestamp." where id = ".$orderId;
                            $dataselect = $writeAdapter->query($query1);
                        }
                        //Unset Rule Data for every record
                        Mage::unregister('rule_data');
                        echo "{$order->getIncrementId()} \n";
                        Mage::log("\t Row #{$i} Order Success - " . $order->getIncrementId(), null, 'bulkimport.log');
                        $this->_getSession()->clear();
                        
                } catch (Exception $e){
                    Mage::logException($e);
                    $resource = Mage::getSingleton('core/resource');
                    $writeAdapter = $resource->getConnection('core_write');
                    $exceptionquery = "UPDATE custom_bulk_order SET status = 'error',customer_id = ".$customer->getId().", message = 'SKUs Might Be Wrong / Missing OR Data Error', timestamp = ".$timestamp." where id = ".$orderId;
                    $dataselect = $writeAdapter->query($exceptionquery);
                }
                echo"Session Cleared ";echo "\n";
                $i++;
            }
            
            Mage::log("Job End - " . date('Y-m-d H:i:s'), null, 'bulkimport.log');
                                
        }
    }

    protected function _buildItems($items, $id)
    {
        $itemArr = array();
		$skippedItemArr = array();
        $splitItems = explode(',', $items);
		$q = '"';
        $timestamp = $q.date("Y-m-d H:i:s").$q;
        foreach ($splitItems as $split)
        {
            $val = explode(':', $split);
            $productId = $this->_getProduct()->getIdBySku($val[0]);
            if (isset($productId) && !empty($productId)) {
                $x = $val[1];
                $itemArr[$productId] = (int)$x;
            }else{
				$skippedItemArr[] = $val[0];
			}
        }
        if(!empty($skippedItemArr) && isset($skippedItemArr)){
           $skippedItemList = implode(', ', $skippedItemArr);
		   $resource = Mage::getSingleton('core/resource');
           $writeAdapter = $resource->getConnection('core_write');
           $exceptionskippedquery = "UPDATE custom_bulk_order SET skippedSku = '".$skippedItemList."' where id = ".$id;
           $dataselect = $writeAdapter->query($exceptionskippedquery);
		}
        if(empty($itemArr)){
           $resource = Mage::getSingleton('core/resource');
           $writeAdapter = $resource->getConnection('core_write');
           $exceptionquery1 = "UPDATE custom_bulk_order SET status = 'error', message = 'SKUs Might Be Wrong / Missing OR Data Error', timestamp = ".$timestamp." where id = ".$id;
           $skippeddataselect = $writeAdapter->query($exceptionquery1);
        }
        
        return $itemArr;
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
            $this->_getOrderCreateModel()->addProducts($data['items']);
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

}

$obj = new Dever_Shell_Bulk_Orders();
$obj->run();