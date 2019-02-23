<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 04/09/16
 * Time: 12:09 PM
 */
class Dever_Retailstore_Block_Adminhtml_Retailstore extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'dever_retailstore';
        $this->_controller = 'adminhtml_retailstore';
        $this->_headerText = Mage::helper('dever_retailstore')->__('Manage Retailstores');

        parent::__construct();
    }
}