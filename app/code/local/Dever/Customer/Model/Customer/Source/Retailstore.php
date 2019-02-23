<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 02/07/17
 * Time: 12:07 PM
 */

class Dever_Customer_Model_Customer_Source_Retailstore extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    protected $_options;

    public function getAllOptions()
    {
        if (!$this->_options) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $groupSql = $readConnection->select()
                ->from(
                    array('t1' => 'retailstores'),
                    array(
                        'value' => 't1.id',
                        'label' => 't1.name',
                    )
                );
            $this->_options = $readConnection->query($groupSql)->fetchAll();
            array_unshift($this->_options, array('value'=>'', 'label'=>Mage::helper('customer')->__('-- Please Select --')));
        }

        $arr = array();
        foreach ($this->_options as $options) {
            $arr[$options['value']] = $options['label'];
        }

        return $arr;
    }
}