<?php
/*____________________________________________________________________
* Name Themes: Magento responsive admin template
* Author: The Cmsmart Development Team 
* Date Created: 2015
* Websites: http://cmsmart.net
* Technical Support: Forum - http://cmsmart.net/support
* GNU General Public License v3 (http://opensource.org/licenses/GPL-3.0)
* Copyright © 2011-2015 Cmsmart.net. All Rights Reserved.
______________________________________________________________________*/
?>
<?php
class Cmsmart_AdminTheme_Model_Adminhtml_System_Config_Source_Color
{
  public function toOptionArray()
  {
    return array(
        array('value' => '25bce9', 'label' =>'Main color 1 (#25bce9)'),
        array('value' => 'dad67d', 'label' =>'Main color 2 (#dad67d)'),
        array('value' => 'f99700', 'label' =>'Main color 3 (#f99700)'),
        array('value' => '71d3c6', 'label' =>'Main color 4 (#71d3c6)'),
        array('value' => '868cda', 'label' =>'Main color 5 (#868cda)')
    );
  }
}
