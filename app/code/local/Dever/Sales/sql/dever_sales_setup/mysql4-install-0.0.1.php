<?php
$debug = true;
/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();
$tbl = $this->getTable('dever_sales/bulk');
if ($this->getConnection()->isTableExists($tbl)) {
    $this->getConnection()->dropTable($tbl);
}
$_tbl = $this->getConnection()
    ->newTable($tbl)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        'unsigned'  => true,
    ), 'ID')
    ->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => true
    ), 'Created Date')
    ->setComment('Retailstore list')
;
$this->getConnection()->createTable($_tbl);
$this->endSetup();