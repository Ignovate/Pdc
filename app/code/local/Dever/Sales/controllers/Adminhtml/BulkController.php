<?php
class Dever_Sales_Adminhtml_BulkController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_forward('edit');
    }
    public function editAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function saveAction()
    {
        $debug = true;
        $bulk = Mage::getModel('dever_sales/bulk');
        try {
            $data = $this->getRequest()->getParams();
            if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                /* Starting upload */
                $uploader = new Varien_File_Uploader('filename');
                // Any extention would work
                $uploader->setAllowedExtensions(array('xlsx'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                // We set media as the upload dir
                $path = Mage::getBaseDir('var') . DS . 'import';
                $uploader->save($path, $_FILES['filename']['name'] );
                $fileName = str_replace(" ","_",$_FILES['filename']['name']);
                //this way the name is saved in DB
                $data['filename'] = $fileName;
            } else {
                if(isset($data['filename']['delete']) && $data['filename']['delete'] == 1) {
                    $data['filename'] = '';
                } else {
                    unset($data['filename']);
                }
            }
            //$data['updated_at'] = date ('Y-m-d H:i:s');
            $bulk->addData($data)->save();
            $this->_getSession()->addSuccess(
                $this->__("Sheet uploaded successfully")
            );
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('dever_sales');
    }
}