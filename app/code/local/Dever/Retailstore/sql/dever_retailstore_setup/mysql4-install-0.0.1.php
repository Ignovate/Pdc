<?php
$debug = true;
/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$tbl = $this->getTable('dever_retailstore/retailstore');
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
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable'  => false,
    ), 'Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable'  => false,
    ), 'Code')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable'  => true,
    ), 'Name')
    ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'nullable'  => true,
    ), 'Phone')
    ->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, NULL, array(
        'nullable'  => true,
    ), 'Address')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'nullable'  => true,
        'default'   => 0
    ), 'status')
    ->setComment('Retailstore list')
;
$this->getConnection()->createTable($_tbl);

$this->endSetup();
