<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 27/11/17
 * Time: 11:51 PM
 */
require_once 'abstract.php';

class Dever_Shell_Reports extends Mage_Shell_Abstract
{
    public function run()
    {
        try {
            $this->orderReport();
        } catch (Exception $e) {
            echo (string)$e->getMessage();
        }
    }

    public function orderReport()
    {
        /** @var Dever_Sales_Model_Cron $model */
        $model = Mage::getModel('dever_sales/cron');
        $model->generateOrderReport();
    }
}

$obj = new Dever_Shell_Reports();
$obj->run();
