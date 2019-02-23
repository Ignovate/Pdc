<?php

class Dever_Retailstore_Block_Adminhtml_Retailstore_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'dever_retailstore';
        $this->_controller = 'adminhtml_retailstore';

        parent::__construct();
        //$this->_removeButton('delete');
        $this->_removeButton('reset');
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }
    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $supplier = Mage::registry('current_retailstore');
        if ($supplier) {
            return Mage::helper('dever_retailstore')
                ->__("Edit Retailstore " . $supplier->getName());
        }

        return Mage::helper('dever_retailstore')->__("New Retailstore");
    }
}
