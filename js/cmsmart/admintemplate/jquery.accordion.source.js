/**
*	@name							Accordion
*	@descripton						This Jquery plugin makes creating accordions pain free
*	@version						1.3
*	@requires						Jquery 1.2.6+
*
*	@author							Jan Jarfalk
*	@author-email					jan.jarfalk@unwrongest.com
*	@author-website					http://www.unwrongest.com
*
*	@licens							MIT License - http://www.opensource.org/licenses/mit-license.php
*/

(function(jQuery){
     jQuery.fn.extend({  
         accordion: function() {       
            return this.each(function() {
            	
            	var $ul = jQuery(this);
            	
				if($ul.data('accordiated'))
					return false;
													
				jQuery.each($ul.find('ul, li>div'), function(){
					jQuery(this).data('accordiated', true);
					jQuery(this).hide();
				});
				
				jQuery.each($ul.find('a'), function(){
					jQuery(this).click(function(e){
						activate(this);
						return void(0);
					});
				});
				
				var active = (location.hash)?jQuery(this).find('a[href=' + location.hash + ']')[0]:'';

				if(active){
					activate(active, 'toggle');
					jQuery(active).parents().show();
				}
				
				function activate(el,effect){
					jQuery(el).parent('li').toggleClass('active').siblings().removeClass('active').children('ul, div').slideUp('fast');
					jQuery(el).siblings('ul, div')[(effect || 'slideToggle')]((!effect)?'fast':null);
				}
				
            });
        } 
    }); 
})(jQuery);