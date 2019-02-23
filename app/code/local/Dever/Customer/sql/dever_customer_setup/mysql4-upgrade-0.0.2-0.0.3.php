<?php
$debug = true;
/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();
$tbl = $this->getTable('dever_customer/fcm');
if ($this->getConnection()->isTableExists($tbl)) {
    $this->getConnection()->dropTable($tbl);
}
$_tbl = $this->getConnection()
    ->newTable($tbl)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        'unsigned'  => true,
    ), 'ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'nullable'  => false,
    ), 'Customer ID')
    ->addColumn('fcm_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable'  => false,
    ), 'FCM Id')
    ->setComment('Retailstore list')
;
$this->getConnection()->createTable($_tbl);
$this->endSetup();