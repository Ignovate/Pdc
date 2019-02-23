<?php
/**
 * Created by PhpStorm.
 * User: thillai.rajendran
 * Date: 6/22/16
 * Time: 11:02 AM
 */
class Dever_Import_Helper_Import extends Mage_Core_Helper_Abstract
{
    public function saveAttributeOptions($code, $value)
    {
        try {
            $attrModel = Mage::getModel('catalog/resource_eav_attribute');
            $attr = $attrModel->loadByCode('catalog_product', $code);
            $attrId = $attr->getAttributeId();
            $checkAttr = $this->attributeValueExists($code, $value);
            if (empty($checkAttr)) {
                $option = array(
                    'attribute_id' => $attrId,
                    'value' => array(
                        array(
                            0 => $value
                        )
                    )
                );
                $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
                $optionId = $setup->addAttributeOption($option);
                if ($optionId) {
                    echo "\tProduct Option - {$code} / {$value} created\n";
                    return $optionId;
                }
            }

        } catch (Exception $e) {
            echo (string)$e->getMessage();
        }
    }

    public function attributeValueExists($attr, $val)
    {
        $attributeModel = Mage::getModel('eav/entity_attribute');
        $attributeOptionsModel = Mage::getModel('eav/entity_attribute_source_table');
        $attribute_code = $attributeModel->getIdByCode('catalog_product', $attr);
        $attribute = $attributeModel->load($attribute_code);
        $attributeTable = $attributeOptionsModel->setAttribute($attribute);
        $options = $attributeOptionsModel->getAllOptions(false);
        foreach ($options as $option) {
            if ($val == $option['label']) {
                return $option['value'];
            }
        }
        return null;
    }

    public function metaTitle($value)
    {
        $mTitle = $value . ' at Lowest Price in Dubai, UAE';
        return $mTitle;
    }

    public function metaDescription($value)
    {
        $mDesc = $value . " in Dubai. Buy " . $value . " at the Lowest Price in UAE - Free Shipping - Cash on Delivery.";
        return $mDesc;
    }

    public function descTemplate($name, $supplier)
    {
        $template = "<p>The best deals of {$name} in UAE.Large choice of {$supplier} {$name} available in stock. </p>";
        return $template;
    }

    public function categoryArrayBinding($lines)
    {
        if ($lines['level2']) {
            return array(
                array(
                    'name' => $lines['root'],  // root
                    'children' => array(
                        array(
                            'name' => $lines['level1'],  // level 1
                            'children' => array(
                                array(
                                    'name' => $lines['level2']  //level 2
                                )
                            )
                        )
                    )
                )
            );
        } else {
            return array(
                array(
                    'name' => $lines['root'],  // root
                    'children' => array(
                        array(
                            'name' => $lines['level1'],  // level 1
                        )
                    )
                )
            );
        }
    }

    public function checkDefaultAttributeType($code)
    {
        $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $code);
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
        if ($attribute->getBackendType() == 'int'
            && $attribute->getFrontendInput() == 'select'
            && $attribute->getIsUserDefined() == 1
        ) {
           return true;
        } else {
           return false;
        }
    }

    public function getFilterAttributesCollection()
    {
        /** @var Mage_Eav_Model_Resource_Entity_Attribute_Collection $collection */
        $collection = Mage::getModel('eav/entity_attribute')->getCollection();

        $collection->getSelect()->joinleft(
            array('eav' => 'eav_attribute'),
            "main_table.attribute_id = eav.attribute_id",
            array()
        );

        $collection->getSelect()
            ->where(
                'eav.backend_type = ?', 'int'
            )
            ->where(
                'eav.frontend_input = ?', 'select'
            )
            ->where(
                'eav.is_user_defined = 1'
            )
            ->where(
                'eav.entity_type_id = 4'
            );

        $ids = array();

        /*$excludeAttributes = array(
            'brand',
            'filter_color',
            'filter_powerbank_capacity',
            'filter_harddisk_type',
            'filter_capacity'
        );*/

        foreach ($collection as $each)
        {
            /*if (in_array($each->getAttributeCode(), $excludeAttributes)) {
                continue;
            }*/

            if (strpos($each->getAttributeCode(), 'filter') !== false) {
                $attributeCode = str_replace('filter_', '', $each->getAttributeCode());
                $ids[] = Mage::getResourceModel('eav/entity_attribute')
                    ->getIdByCode('catalog_product', $attributeCode);
                //$data[$id] = $attributeCode;

            }
        }
        //return $data;
        return $ids;
    }

    public function getOptionId($product, $code, $value)
    {
        $attr = $product->getResource()->getAttribute($code);
        if ($attr->usesSource()) {
            $optionId = $attr->getSource()->getOptionId($value);
            return $optionId;
        }

        return null;
    }

    public function determineCategoryLevels($count)
    {
        switch ($count) {
            case 1:
                $categoryLevels = array ('root');
                break;
            case 2:
                $categoryLevels = array ('root','level1');
                break;
            case 3:
                $categoryLevels = array ('root','level1','level2');
                break;
            default:
                // Do nothing
        }

        return $categoryLevels;
    }

    public function getAttributeSetId($attributeSet)
    {
        $entityTypeId = Mage::getModel('eav/entity')
            ->setType('catalog_product')
            ->getTypeId();
        $attributeSetId = Mage::getModel('eav/entity_attribute_set')
            ->getCollection()
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('attribute_set_name', $attributeSet)
            ->getFirstItem()
            ->getAttributeSetId();

        return $attributeSetId;
    }

    public function logMessages($message)
    {
        Mage::log($message, null, 'dataimport.log');
    }
}