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
    public function _construct()
    {
        parent::_construct();
        $datafile = Mage::getBaseDir('var') . DS . 'import' . DS . 'orderstatus.xlsx';
        echo "Loading {$datafile}. \n";
        $xlsx = @(new SimpleXLSX($datafile));
        $rows =  $xlsx->rows();
        $total = count($rows);
        echo "Loaded {$total} rows. \n";
        $this->_processData = $rows;
    }
    public function run()
    {
        ini_set('memory_limit', '2G');
        $orderData = $this->prepareData();
        $this->updateOrder($orderData);
    }
    /**
     * Create Sales Order
     *
     * @param $orderData
     */
    public function prepareData()
    {
        $orderData = array();
        try {
            if ($this->_processData) {
                $csvHeaders = array();
                echo "--Prepare Save ...\n";
                foreach ($this->_processData as $key => $lines) {
                    if ($key == 0) {
                        $csvHeaders = $lines;
                    } else {
                        $orderData = array_combine($csvHeaders, $lines);
                    }
                }
                echo "--End Save ...\n";
            }
        } catch (Exception $e) {
            echo (string)$e->getMessage();
        }
        return $orderData;
    }
    public function updateOrder($orderData)
    {
        if (!empty($orderData)) {
            try {
                foreach ($orderData as $orders)
                {
                    /** @var Mage_Sales_Model_Order $order */
                    $order = Mage::getModel('sales/order');
                    $order = $order->loadByIncrementId($orders['id']);
                    $order->setStatus($orders['status'])
                        ->save();
                    echo "{$order->getIncrementId()} \n";
                }
            } catch (Exception $e){
                echo (string)$e->getMessage();
            }
        }
    }
}
$obj = new Dever_Shell_Bulk_Orders();
$obj->run(); 