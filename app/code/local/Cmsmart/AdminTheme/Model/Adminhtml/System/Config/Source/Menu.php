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
class Cmsmart_AdminTheme_Model_Adminhtml_System_Config_Source_Menu
{
  public function toOptionArray()
  {
    return array(
        array('value' => 'horizontal', 'label' =>'Horizontal'),
        array('value' => 'vertical', 'label' =>'Vertical')
    );
  }
}
