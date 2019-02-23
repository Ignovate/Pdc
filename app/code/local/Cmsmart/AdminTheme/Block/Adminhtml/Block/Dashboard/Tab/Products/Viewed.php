<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml dashboard most viewed products grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Cmsmart_AdminTheme_Block_Adminhtml_Block_Dashboard_Tab_Products_Viewed extends Mage_Adminhtml_Block_Dashboard_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productsReviewedGrid');
    }

    protected function _prepareCollection()
    {
        if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else {
            $storeId = (int)$this->getParam('store');
        }
        $collection = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect('*')
            ->addViewsCount('')
            ->setStoreId($storeId)
            ->addStoreFilter($storeId);

        $collection->getSelect()->columns('MAX(report_table_views.logged_at) as logged_at');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'sortable'  => false,
            'index'     =>'name',
            'width'     =>'30%',
        ));

        $this->addColumn('views', array(
            'header'    =>Mage::helper('reports')->__('Number of Views'),
            'width'     =>'10%',
            'align'     =>'right',
            'sortable'  => false,
            'index'     =>'views'
        ));
        $this->addColumn('price', array(
            'header'    =>Mage::helper('reports')->__('Price'),
            'width'     =>'10%',
            'type'      =>'currency',
            'currency_code' => (string) Mage::app()->getStore((int)$this->getParam('store'))->getBaseCurrencyCode(),
            'sortable'  => false,
            'index'     =>'price'
        ));

        //Netbase Add
        $this->addColumn('logged_at', array(
            'header'    =>Mage::helper('reports')->__('Last Review'),
            'width'     =>'20%',
            'sortable'  =>false,
            'type'      =>'datetime',
            'index'     =>'logged_at'
        ));

        $this->addColumn('action', array(
            'header'    => $this->__('Action'),
            'sortable'  => false,
            'renderer'   => 'Cmsmart_AdminTheme_Block_Adminhtml_Block_Dashboard_Orders_Iconeye',
            'width'     => '5%'
        ));


        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        $params = array('id'=>$row->getId());
        if ($this->getRequest()->getParam('store')) {
            $params['store'] = $this->getRequest()->getParam('store');
        }
        return $this->getUrl('*/catalog_product/edit', $params);
    }
}
