<?php
class Dever_Sales_Block_Adminhtml_Bulk_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'dever_sales';
        $this->_controller = 'adminhtml_bulk';
		$url = str_replace('index.php','',Mage::getBaseUrl('web', true));
        $downloadurl = $url.'customReport/orderUploadLog.php';
        $this->_addButton('add_new', array(
        'label'   => "Download Log",
        'onclick' => "setLocation('$downloadurl')"
    ));
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