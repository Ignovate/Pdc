<?php

class Cmsmart_AdminTheme_Block_Adminhtml_Themes_Select extends Mage_Adminhtml_Block_Html_Select
{
    protected function _construct()
    {
        $this->setName('theme')
            ->setId('interface_theme')
            ->setTitle($this->helper('cmsmart_admintheme')->__('Current Admin Theme'))
            ->setValue(Mage::getStoreConfig('cmsmart_admintheme/config/theme'))
            ->setOptions($this->_getSelectOptions());
    }

    protected function _getSelectOptions()
    {
        return Mage::getModel('cmsmart_admintheme/adminhtml_system_config_source_themes')->toOptionArray();
    }
}