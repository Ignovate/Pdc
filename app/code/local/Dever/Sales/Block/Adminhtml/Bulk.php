<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 04/09/16
 * Time: 12:09 PM
 */
class Dever_Sales_Block_Adminhtml_Bulk extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'dever_sales';
        $this->_controller = 'adminhtml_bulk';
        $this->_headerText = Mage::helper('dever_sales')->__('Upload File');
        parent::__construct();
    }
} 