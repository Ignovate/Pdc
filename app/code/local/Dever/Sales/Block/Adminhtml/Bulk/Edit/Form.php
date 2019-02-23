<?php
class Dever_Sales_Block_Adminhtml_Bulk_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTitle(Mage::helper('dever_sales')->__('Upload File'));
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return Dever_Retailstore_Block_Adminhtml_Retailstore_Edit_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('dever_sales');
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('_current' => true)),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));
        $form->setUseContainer(true);
        $fieldset = $form->addFieldset('editForm', array(
            'legend'    => $helper->__("General Information"),
        ));
        $this->_addElementTypes($fieldset);
        $fieldset->addField('filename', 'image', array(
            'label'     => $helper->__('File'),
            'required'  => false,
            'name'      => 'filename',
        ));
        $this->setForm($form);
        return parent::_prepareForm();
    }
} 