<?php

class Dever_Retailstore_Adminhtml_RetailstoreController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_initRetailstore();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        $debug = true;
        $retailstore = $this->_initRetailstore();
        try {

            $data = $this->getRequest()->getParams();
            if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                /* Starting upload */
                $uploader = new Varien_File_Uploader('filename');
                // Any extention would work
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png', 'pdf', 'doc', 'docx'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                // We set media as the upload dir
                $path = Mage::getBaseDir('media') . DS . 'supplier' . DS . 'docs';
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
            $retailstore->addData($data)->save();
            $this->_getSession()->addSuccess(
                $this->__("Retailstore save success")
            );
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }


    public function newAction()
    {
        $this->_forward('edit');
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model = Mage::getModel('dever_retailstore/retailstore')->load($id);
            $model->delete();
            $this->_getSession()->addSuccess(
                $this->__("Retailstore delete success")
            );
            $this->_redirect('*/*/');
        }
    }

    protected function _initRetailstore()
    {
        $retailstore = Mage::getModel('dever_retailstore/retailstore');
        if ($id = $this->getRequest()->getParam('id')) {
            $retailstore->load($id);
            if ($retailstore->getId()) {
                Mage::register('current_retailstore', $retailstore);
            }
        }

        return $retailstore;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('dever_retailstore');
    }
}