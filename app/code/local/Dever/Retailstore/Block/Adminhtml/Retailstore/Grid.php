<?php

class Dever_Retailstore_Block_Adminhtml_Retailstore_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('dever_retailstore_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('dever_retailstore/retailstore')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('dever_retailstore');

        $this->addColumn('id',
            array(
                'header'=> $helper->__('Id'),
                'type' => 'text',
                'index' => 'id',
                'width' => '50px'
            ));

        $this->addColumn('name',
            array(
                'header'=> $helper->__('Name'),
                'type'  => 'text',
                'index' => 'name'
            ));

        $this->addColumn('code',
            array(
                'header'=> $helper->__('Code'),
                'type'  => 'text',
                'index' => 'code'
            ));

        $this->addColumn('email',
            array(
                'header'=> $helper->__('Email'),
                'type'  => 'text',
                'index' => 'email'
            ));

        $this->addColumn('phone',
            array(
                'header'=> $helper->__('Phone'),
                'type'  => 'number',
                'index' => 'phone'
            ));

        $this->addColumn('address',
            array(
                'header'=> $helper->__('Address'),
                'type'  => 'text',
                'index' => 'address'
            ));

        $this->addColumn('status', array(
            'header'    => $helper->__('Active'),
            'width'     => '50px',
            'align'     => 'left',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                0      => $helper->__('No'),
                1     => $helper->__('Yes'),
            ),
        ));

        $this->addColumn('action',
            array(
                'header'    =>  $helper->__('Action'),
                'width'     => '50',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => $helper->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }
}