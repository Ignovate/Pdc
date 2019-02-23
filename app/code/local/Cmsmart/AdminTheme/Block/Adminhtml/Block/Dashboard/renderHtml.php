<?php 
class Cmsmart_AdminTheme_Block_Adminhtml_Block_Dashboard_Render_Html extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
     
    public function render(Varien_Object $row)
    {
        $html = '<img ';
        $html .= 'id="' . $this->getColumn()->getId() . '" ';
        $html .= 'src="' . $row->getData($this->getColumn()->getIndex()) . '"';
        $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '"/>';
        $html .= '<br/><p>'.$row->getData($this->getColumn()->getIndex()).'</p>';
        return $html;
    }
}
?>
