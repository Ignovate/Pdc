<?php
class Dever_Sales_Block_Adminhtml_Bulk_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'dever_sales';
        $this->_controller = 'adminhtml_bulk';
        parent::__construct();
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
        return Mage::helper('dever_sales')->__("Upload File");
    }
}