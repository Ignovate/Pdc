<?php

class Dever_Retailstore_Block_Adminhtml_Retailstore_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTitle(Mage::helper('dever_retailstore')
            ->__('Supplier Information'));
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Dever_Retailstore_Block_Adminhtml_Retailstore_Edit_Form
     */
    protected function _prepareForm()
    {
        $retailstore = Mage::registry('current_retailstore');
        $helper = Mage::helper('dever_retailstore');

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

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => $helper->__('Name'),
            'title'     => $helper->__('Name'),
            'required'  => true,
        ));

        $fieldset->addField('code', 'text', array(
            'name'      => 'code',
            'label'     => $helper->__('Identifier'),
            'title'     => $helper->__('Identifier'),
            'required'  => true,
        ));

        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => $helper->__('Email'),
            'title'     => $helper->__('Email'),
            'required'  => true,
        ));

        $fieldset->addField('phone', 'text', array(
            'name'      => 'phone',
            'label'     => $helper->__('Mobile'),
            'title'     => $helper->__('Mobile'),
            'required'  => true,
        ));

        $fieldset->addField('address', 'textarea', array(
            'name'      => 'address',
            'label'     => $helper->__('Address'),
            'title'     => $helper->__('Address'),
            'required'  => false,
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => $helper->__('Active'),
            'title'     => $helper->__('Active'),
            'required'  => false,
            'options'       => array(
                '1'     => 'Yes',
                '0'     => 'No',
            ),
        ));

        if ($retailstore && $retailstore->getId()) {
            $form->setValues($retailstore->getData());
            $form->setDataObject($retailstore);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}