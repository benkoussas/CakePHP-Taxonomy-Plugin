<?php
/**
 * Taxonomy View Helper
 *
 * PHP version 5.3
 * CakePHP 2.2+
 *
 * @package  Taxonomy.Taxonomy.View.Helper
 * @version  1.0
 * @author   Bridn - (based on Grafikart beta plugin https://github.com/Grafikart/CakePHP-Taxonomy)
 * @date 	 August 2012
 */

class TaxonomyHelper extends AppHelper {

	public $helpers = array('Html','Form');
	public $javascript = false; 

	public function input($type, $options = array() ){
		$data = $this->data;
		
		//Edit function
		if(!empty($data)){
		$ref = key($data);
		
		$this->javascriptAdd($ref, $data[$ref]['id']); 
		$clear = '<div class="clear"></div>';
		$html = ''; 
		
		if(!empty($data['Taxonomy'][$type])){
			foreach($data['Taxonomy'][$type] as $value){
				$url = Router::url( array( 'controller' => 'Terms', 'action' => 'delete', 'plugin' => 'taxonomy', 'admin'=>true, $value['TermR']['id']));
				$html .= '<span class="tag no-shadow">'.$value['Term'][Configure::read('Taxonomy.field')].' <a href="'.$url.'" class="del-taxonomy">x</a></span>';
			}
		}

		$options['id'] = $type;
		$options['class'] = 'add-taxonomy';
		$options['value'] = ''; 
		return $this->Form->input('Taxonomy.'.$type, $options).$clear.$html; 
		
		}	else   {
		
		//Add function
		

		}
	}

	private function javascriptAdd($ref = null, $ref_id = null){
		
		if($this->javascriptAdd){ return false; }
		$this->javascriptAdd = true; 
		
		$url = Router::url(array( 'admin' => true, 'plugin' => 'taxonomy', 'controller' => 'terms', 'action'=>'add', $ref, $ref_id));
		$urlList = Router::url(array( 'admin' => true, 'plugin' => 'taxonomy', 'controller' => 'terms', 'action' => 'search' ));
		$this->Html->scriptStart(array('inline' => false));?>
		jQuery(function($){
			$('.del-taxonomy').live('click',function(e){
				var a = $(this);
				$.get(a.attr('href'));
				a.parent().fadeOut(); 
				return false; 
			});
			$('.add-taxonomy').each(function(){
				var input = $(this);
				var cache = {},lastXhr;
				var type = input.attr('id'); 

				input.autocomplete({
					minLength:2,
					source: function( request, response ) {
						request.type = input.attr('id');
						var term = request.term;
						if ( term in cache ) {
							response( cache[ term ] );
							return;
						}
						lastXhr = $.getJSON( "<?php echo $urlList; ?>", request, function( data, status, xhr ) {
							cache[ term ] = data;
							if ( xhr === lastXhr ) {
								response( data );
							}
						});
					},
					select : function(event, ui){
						$.get('<?php echo $url; ?>',{id:ui.item.id,name:ui.item.label,type:type},function(data){
							input.parent().after(data);
							input.val('');  
						});
					}
				});

				input.keypress(function(e){
					if( e.keyCode == 13 ){
						var val = input.val(); 
						input.val(''); 
						$.get("<?php echo $url; ?>",{name:val,type:type},function(data){
							input.parent().after(data); 
						});
						return false;
					}
				});
			});
		})
		<?php $this->Html->scriptEnd(); ?>
		<?php
	}

}