<?php

require_once '../abstract.php';
require_once '../simplexlsx.class.php';
class Dever_Shell_Bulk_Orders extends Mage_Shell_Abstract
{
    protected $_processData = null;
    public function _construct()
    {
        parent::_construct();
        $datafile = Mage::getBaseDir('var') . DS . 'import' . DS . 'cancelorder.xlsx';
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
                        $orderData[] = array_combine($csvHeaders, $lines);
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
            Mage::log("Job Started - " . date('Y-m-d H:i:s'), null, 'bulkcancelstatus.log');
            try {
                foreach ($orderData as $orders)
                {
                    /** @var Mage_Sales_Model_Order $order */
                   $order = Mage::getModel('sales/order');
                    $order = $order->loadByIncrementId($orders['ordernumber']);
                   // print_r($order);
                    $items = $order->getAllItems();
                    if($order['status'] == "pending"){
                           if($order->canCancel()){
                                echo "Inside IF Cancancel";echo "\n";
                                $order->setStatus('Canceled')
                                    ->save();
                           }  
                        foreach ($items as $item)
                        {
                            echo "Sku : "; echo $item['sku'];echo "\n";
                            echo "Sku Ordered : "; echo $item['qty_ordered'];echo "\n";
                            $item->setQtyCanceled($item['qty_ordered'])
                                 ->save();
                        }
                        echo "{$order->getIncrementId()} \n";
                        Mage::log("Order - " . $order->getIncrementId() . " is Canceled", null, 'bulkcancelstatus.log');
                       
                    }
                }
            } catch (Exception $e){
                Mage::log("Exception - " . $e, null, 'bulkcancelstatus.log');
            }
            $filepath = Mage::getBaseDir('var') . DS . 'import' . DS ;
            $file = "cancelorder.xlsx";
            $ext = pathinfo($filepath.$file, PATHINFO_EXTENSION); //getting image extension
            $newfilename = "cancelorder_".date('Y-m-d H:i:s').".".$ext;
            $newpath = $filepath.$newfilename;
            rename($filepath.$file,$newpath);
            Mage::log("Job End - " . date('Y-m-d H:i:s'), null, 'bulkcancelstatus.log');
        }
    }
}
$obj = new Dever_Shell_Bulk_Orders();
$obj->run();