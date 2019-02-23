<?php
$debug = true;
/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$tbl = $this->getTable('dever_notification/notification');
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
    ->addColumn('fcm_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true,
    ), 'FCM Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'nullable'  => true,
    ), 'Customer Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable'  => false,
    ), 'Name')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable'  => true,
    ), 'Email')
    ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, NULL, array(
        'nullable'  => true,
    ), 'Message')
    ->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => true
    ), 'Created Date')
    ->setComment('Notification List')
;
$this->getConnection()->createTable($_tbl);

$this->endSetup();
