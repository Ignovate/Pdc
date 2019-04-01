<?php

require_once '../abstract.php';
require_once '../simplexlsx.class.php';
class Dever_Shell_Bulk_Orders extends Mage_Shell_Abstract
{
    protected $_processData = null;
    public function _construct()
    {
        parent::_construct();
        $datafile = Mage::getBaseDir('var') . DS . 'import' . DS . 'orderstatus.xlsx';
        $xlsx = @(new SimpleXLSX($datafile));
        $rows = $xlsx->rows();
        $total = count($rows);
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
                foreach ($this->_processData as $key => $lines) {
                    if ($key == 0) {
                        $csvHeaders = $lines;
                    } else {
                        $orderData[] = array_combine($csvHeaders, $lines);
                    }
                }
            }
        } catch (Exception $e) {
            echo (string)$e->getMessage();
        }
        return $orderData;
    }
    public function updateOrder($orderData)
    {
        if (!empty($orderData)) {
            Mage::log("Job Started - " . date('Y-m-d H:i:s'), null, 'bulkstatus.log');
            try {
                foreach ($orderData as $data) {
                    /** @var Mage_Sales_Model_Order $order */
                    $order = Mage::getModel('sales/order');
                    $order = $order->loadByIncrementId($data['ordernumber']);
                    switch ($data['status']) {
                        case 'Accepted':
                            //Create Invoice
                            $this->_createInvoice($order, $data['sku'], $data['invoice_qty']);
                            break;
                        case 'Complete':
                            //Create Shipment
                            $this->_createShipment($order, $data['sku'], $data['shipped_qty']);
                            break;
                        case 'Canceled':
                            //Create Canceled items
                            $this->_createCancel($order, $data['sku'], $data['canceled_qty']);
                            break;
                        default:
                            //Do nothing
                    }
                }
            } catch (Exception $e) {
                echo (string)$e->getMessage();
            }
            $filepath = Mage::getBaseDir('var') . DS . 'import' . DS ;
            $file = "orderstatus.xlsx";
            $ext = pathinfo($filepath.$file, PATHINFO_EXTENSION); //getting image extension
            $newfilename = "orderstatus_".date('Y-m-d H:i:s').".".$ext;
            $newpath = $filepath.$newfilename;
            rename($filepath.$file,$newpath);
            Mage::log("Job End - " . date('Y-m-d H:i:s'), null, 'bulkstatus.log');
        }
    }
    protected function _createInvoice(/** @var Mage_Sales_Model_Order $order */ $order, $sku, $iQty)
    {
        if ($order->canInvoice()) {
            Mage::log("Process Order - {$order->getIncrementId()}", null, 'bulkstatus.log');
            $items = $order->getAllItems();
            foreach ($items as $item) {
                if ($item->getSku() == $sku) {
                    $itemsarray[$item->getId()] = $iQty;
                    $invoice = $order->prepareInvoice($itemsarray);
                    $invoice->register();
                    $invoice->getOrder()->setIsInProcess(true);
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                    Mage::log("***** Invoice - {$item->getSku()} - {$iQty}", null, 'bulkstatus.log');
                }
            }
        }
    }
    protected function _createShipment(/** @var Mage_Sales_Model_Order $order */ $order, $sku, $sQty)
    {
        if ($order->canShip()) {
            Mage::log("Process Order - {$order->getIncrementId()}", null, 'bulkstatus.log');
            $items = $order->getAllItems();
            foreach ($items as $item) {
                if ($item->getSku() == $sku) {
                    $itemsarray[$item->getId()] = $sQty;
                    $shipment = $order->prepareShipment($itemsarray);
                    $shipment->register();
                    $shipment->getOrder()->setIsInProcess(true);
                    Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder())
                        ->save();
                    Mage::log("***** Shipment - {$item->getSku()} - {$sQty}", null, 'bulkstatus.log');
                }
            }
        }
    }
    public function _createCancel(/** @var Mage_Sales_Model_Order $order */ $order, $sku, $cQty)
    {
        if ($order->canCancel()) {
            Mage::log("Process Order - {$order->getIncrementId()}", null, 'bulkstatus.log');
            $items = $order->getAllItems();
            foreach ($items as $item)
            {
                if ($item['sku'] == $sku) {
                    echo $sku;
                    $item->setQtyCanceled($cQty)
                        ->save();
                    Mage::log("***** Cancel - {$item['sku']} - {$cQty}", null, 'bulkstatus.log');
                }
            }
        }
    }
}
$obj = new Dever_Shell_Bulk_Orders();
$obj->run();