<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 20/11/17
 * Time: 5:55 PM
 */

require_once '../abstract.php';

require_once '../simplexlsx.class.php';

class Dever_Shell_Bulk_Import extends Mage_Shell_Abstract
{
    protected $_processData = null;

    protected $_importData = null;

    public function _construct()
    {
        parent::_construct();
        $datafile = Mage::getBaseDir('var') . DS . 'import' . DS . $this->getArg('sheet') .'.xlsx';

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
        $this->saveProduct();
    }

    public function saveProduct()
    {
        /** @var Dever_Import_Model_Import $model */
        $model = Mage::getModel('dever_import/import');
        try {
            if ($this->_processData) {
                $csvHeaders = array();
                $importData = array();
                echo "--Prepare Product Save ...\n";
                $i = 1;
                foreach ($this->_processData as $key => $lines)
                {
                    //print_r($lines);
                    if ($key == 0) {
                        $csvHeaders = $lines;
                    } else {
                        //print_r($csvHeaders);
                        $arrayCombined = array_combine($csvHeaders, $lines);
                        $data = $model->prepareDataForImport($arrayCombined);
                        $model->saveProduct($data, $this->getArg('mediaDir'));
                    }
                }
                echo "--End Product Save ...\n";
                //exit;
                $this->_importData = $importData;
            }
        } catch (Exception $e) {
            echo (string)$e->getMessage();
        }
    }
}

$obj = new Dever_Shell_Bulk_Import();
$obj->run();