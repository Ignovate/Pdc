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
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                // We set media as the upload dir
                $path = Mage::getBaseDir('var') . DS . 'import';
                
                $UploadFileName = $_FILES['filename']['name'];
                if(file_exists($path ."/". $UploadFileName)){
					$this->_getSession()->addError("Previous file is under progress, Please try after sometime.");
				}else{
					$uploader->save($path, $_FILES['filename']['name'] );
					$fileName = str_replace(" ","_",$_FILES['filename']['name']);
					//this way the name is saved in DB
					$data['filename'] = $fileName;
					 $bulk->addData($data)->save();
					$this->_getSession()->addSuccess(
						$this->__("Sheet uploaded successfully")
					);
				}
            } else {
                if(isset($data['filename']['delete']) && $data['filename']['delete'] == 1) {
                    $data['filename'] = '';
                } else {
                    unset($data['filename']);
                }
            }
            //$data['updated_at'] = date ('Y-m-d H:i:s');
            if(!file_exists($path ."/". $UploadFileName)){
                $bulk->addData($data)->save();
                $this->_getSession()->addSuccess(
                    $this->__("Sheet uploaded successfully")
                );
           // $newfilename = "ordersnew.xlsx";
            // rename($path."/".$fileName,$path."/".$newfilename);
			}
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