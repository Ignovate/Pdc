<?php

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
        $datafile = Mage::getBaseDir('var') . DS . 'import' . DS . 'orders.xlsx';
        
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
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
        $orders = $this->prepareData();
        print_r($orders);
        foreach ($orders as $orderData){
            $q = '"';
            $customer_email = $q.$orderData['email'].$q;
            $store_id = self::DEFAULT_STORE;
            $method = '"cashondelivery"';
            $items =$q.$orderData['items'].$q;
            $currency = '"AED"';
            $firstname = $q.$orderData['firstname'].$q;
            $lastname = $q.$orderData['lastname'].$q;
            $street = $q.$orderData['address'].$q;
            $country_id = $q.$orderData['country'].$q;
            $city = $q.$orderData['city'].$q;
            $postcode = $q.$orderData['postcode'].$q;
            $telephone = $q.$orderData['telephone'].$q;
            $shipping_method = '"freeshipping_freeshipping"';
            $comment = '"Order Created using sheet"';
            $import_dtime = $q.date("Y-m-d H:i:s").$q;
            $status = '"Pending"';
            $resource = Mage::getSingleton('core/resource');
            $writeAdapter = $resource->getConnection('core_write');
            print_r($customer_email);
            $query = "INSERT INTO custom_bulk_order (`customer_email`,`store_id`,`method`,`items`,`currency`,`firstname`,`lastname`,`street`,`country_id`,`city`,`postcode`,`telephone`,`shipping_method`,`comment`,`status`, `import_dtime`) VALUES ($customer_email, $store_id, $method, $items, $currency,$firstname, $lastname, $street, $country_id, $city,$postcode, $telephone, $shipping_method, $comment, $status, $import_dtime);";
            print_r($query);
            $writeAdapter->query($query);
            print_r("after insert");
        }
        $filepath = Mage::getBaseDir('var') . DS . 'import' . DS ;
        $file = "orders.xlsx";
        $ext = pathinfo($filepath.$file, PATHINFO_EXTENSION); //getting image extension
        $newfilename = "orders_".date('Y-m-d H:i:s').".".$ext;
        $newpath = $filepath.$newfilename;
        rename($filepath.$file,$newpath);
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

}

$obj = new Dever_Shell_Bulk_Orders();
$obj->run();