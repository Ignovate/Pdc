<?php

class Dever_Api2_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_source;

    protected $_objectData;

    protected $_attributes;

    /**
     * Add Api flagged attributes to object response
     *
     * @param Varien_Object $obj
     * @param array $additionalFields
     * @return array
     */
    public function getDataForApi(Varien_Object $obj, $additionalFields = array())
    {
        $result = array();
        array_unshift($additionalFields, 'id');

        if ($obj->getTypeId()) {
            array_unshift($additionalFields, 'type_id');
        }

        foreach ($additionalFields as $field) {
            $method = $this->fieldToMethodName($field);
            $result[$field] = $obj->$method();
        }

        $attributes = $this->getAttributesByObject($obj);
        foreach ($attributes as $attribute) {
            // pass only api flagged attributes
            if ($attribute->getIsApiDisplay()) {
                $code = $attribute->getAttributeCode();
                $backend = $attribute->getBackend();
                if (($attribute->getFrontendInput() == 'select'
                    || $attribute->getFrontendInput() == 'multiselect')
                ) {
                    $result[$code] = $backend->getValueForApi($obj, $code);
                } else {
                    $result[$code] = $obj->getData($code);
                }
            }
        }

        return $result;
    }

    /**
     * Convert field name to getter method name
     *
     * @param string $field
     * @return string
     */
    protected function fieldToMethodName($field)
    {
        $parts = explode('_', $field);
        $parts = array_map('ucfirst', $parts);
        return 'get' . implode('', $parts);
    }

    /**
     * Get collection of attributes for given EAV object
     *
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Collection
     */
    protected function getAttributesByObject(Varien_Object $obj)
    {
        return Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($obj->getEntityTypeId())
            ;
    }

    /**
     * Set Object Source
     *
     * @param $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->_source = $source;

        return $this;
    }

    /**
     * Return Source data
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getSource()
    {
        if (empty($this->_source)) {
            Mage::throwException('Source is empty');
        }

        return $this->_source;
    }

    /**
     * Filter Source data
     *
     * @param $obj
     * @return array
     */
    public function filterSource($obj)
    {
        foreach ($this->getSource() as $key => $value) {
            $fValue = $value;
            $findOptionId = $this->_findListAttributes($key, $value, $obj);
            if ($findOptionId) {
                $fValue = $findOptionId;
            }
            $this->_objectData[$key] = $fValue;
        }

        return $this->_objectData;
    }

    /**
     * Find list attribute option id
     *
     * @param $code
     * @param $value
     * @param $obj
     * @return bool|int
     */
    protected function _findListAttributes($code, $value, $obj)
    {
        $attribute = $this->getAttribute($code, $obj);
        if ($attribute && in_array($attribute->getFrontendInput(), array(
                'select', 'multiselect'
            ))) {
            return $this->_findOptionId($attribute, $value);
        }

        return false;
    }

    /**
     * Get attribute object by code
     *
     * @param $code
     * @param $obj
     * @return bool
     */
    public function getAttribute($code, $obj)
    {
        if (empty($this->_attributes)) {
            $attributes = $this->getAttributesByObject($obj);
            foreach ($attributes as $attr) {
                $this->_attributes[$attr->getAttributeCode()] = $attr;
            }
        }
        if (isset($this->_attributes[$code])) {
            return $this->_attributes[$code];
        }

        return false;
    }

    /**
     * Load attribute option id by label.
     * @param $attribute
     * @param $value
     * @return mixed
     */
    protected function _findOptionId($attribute, $value)
    {
        $sourceModel = $attribute->getSource();
        $sourceOptions = $sourceModel->getAllOptions(false);
        $optionList = array();
        foreach ($sourceOptions as $option) {
            $optionList[$option['value']] = $option['label'];
        }
        $optionId = array_search($value, $optionList);

        return $optionId;
    }

}
