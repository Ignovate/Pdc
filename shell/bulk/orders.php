<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 20/11/17
 * Time: 5:55 PM
 */

require_once '../abstract.php';

require_once '../simplexlsx.class.php';

class Dever_Shell_Bulk_Orders extends Mage_Shell_Abstract
{
    protected $_processData = null;

    const DEFAULT_WEBSITE = 2;

    const DEFAULT_STORE = 2;

    public function _construct()
    {
        parent::_construct();
        $datafile = Mage::getBaseDir('var') . DS . 'import' . DS . 'ordersnew.xlsx';

        //echo "Loading {$datafile}. \n";
        $xlsx = @(new SimpleXLSX($datafile));
        $rows =  $xlsx->rows();
        $total = count($rows);
        //echo "Loaded {$total} rows. \n";

        $this->_processData = $rows;
    }

    public function run()
    {
        ini_set('memory_limit', '2G');

        $orderData = $this->prepareData();
        $this->createOrder($orderData);
    }

    /**
     * Create Sales Order
     *
     * @param $orderData
     */
    public function prepareData()
    {
        $orders = array();
        try {
            if ($this->_processData) {
                $csvHeaders = array();
                foreach ($this->_processData as $key => $lines) {
                    if ($key == 0) {
                        $csvHeaders = $lines;
                    } else {
                        $orders[] = array_combine($csvHeaders, $lines);
                    }
                }
            }
        } catch (Exception $e) {
            echo (string)$e->getMessage();
        }

        return $orders;
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
                if (empty($orderData['email'])) {
                    Mage::log("\t Row #{$i} Skip Row - Customer Email is empty", null, "bulkimport.log");
                    continue;
                }

                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(self::DEFAULT_WEBSITE)
                    ->loadByEmail($orderData['email']);

                if (empty($customer->getId())) {
                    //Mage::throwException('Customer does not Exists - Skip Row');
                    Mage::log("\t Row #{$i} Customer Email " . $orderData['email'] . " does not exists", null, "bulkimport.log");
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
                    'items' => $this->_buildItems($orderData['items']),
                    'order' => array (
                        'currency' => 'AED',
                        'shipping_address' => array (
                            'firstname' => $orderData['firstname'],
                            'lastname' => $orderData['lastname'],
                            'street' => $orderData['address'],
                            'country_id' => $orderData['country'],
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

                    //Unset Rule Data for every record
                    Mage::unregister('rule_data');

                    echo "{$order->getIncrementId()} \n";
                    Mage::log("\t Row #{$i} Order Success - " . $order->getIncrementId(), null, 'bulkimport.log');

                    $this->_getSession()->clear();

                } catch (Exception $e){
                    Mage::logException($e);
                }
                $i++;
            }

            Mage::log("Job End - " . date('Y-m-d H:i:s'), null, 'bulkimport.log');
			rename("Mage::getBaseDir('var') . DS . 'import' . DS . 'ordersnew.xlsx'", "Mage::getBaseDir('var') . DS . 'import' . DS . 'ordersnew_'".date('Y-m-d H:i:s')."'.xlsx'");
        }
    }

    protected function _buildItems($items)
    {
        $itemArr = array();
        $splitItems = explode(',', $items);
        foreach ($splitItems as $split)
        {
            $val = explode(':', $split);
            $productId = $this->_getProduct()->getIdBySku($val[0]);
            if (isset($productId) && !empty($productId)) {
                $itemArr[$productId] = $val[1];
            }
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