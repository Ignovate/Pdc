<?php 
class Cmsmart_AdminTheme_Block_Adminhtml_Block_Dashboard_Orders_Iconeye extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
     
    public function render(Varien_Object $row)
    {
        $html = '<span class="ti-eye"></span>';
        return $html;
    }
}
?>
