<?php 
namespace Boyo\WPBang\Fields;
	
if (!defined('ABSPATH')) die;

class TermMeta extends CustomMeta {
	
    /**
     * Constructor
     */
    public function __construct() 
    {
	    
	    add_action('wp_loaded',[$this,'register']);
	    
	}
	
	public function register() 
    {
		
		foreach ($this->fields as $taxonomy => $fields) {
 		
 			add_action( $taxonomy.'_add_form_fields', [ $this, 'createTaxonomy' ], 10, 1 );
			add_action( 'created_'.$taxonomy, [ $this, 'saveTaxonomy' ], 10, 2 );
			add_action( $taxonomy.'_edit_form_fields', [ $this, 'editTaxonomy' ], 10, 2 );
 			add_action( 'edited_'.$taxonomy, [ $this, 'updateTaxonomy' ], 10, 2 );
 			
 		}
		
	}
	
	public function createTaxonomy($taxonomy) 
    {
    	
    	if (isset($this->fields[$taxonomy])) {
    	
    		foreach ($this->fields[$taxonomy] as $field) {
    			
    			if (!isset($field['create']) OR $field['create']==true) {
    				
    				switch($field['type']) {
    					case 'text':
    						?>
    						<div class="form-field term-group">
						        <label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
						        <input type="text" class="" id="<?php echo $field['name']; ?>" name="<?php echo $field['name']; ?>" value="">
						    </div>
						    <?php
    						break;
    					case 'image':
    					
    						break;
    						
    				}
    			
    			}
    		
    		}
    	
    	}
    }
    
    public function editTaxonomy($term, $taxonomy) 
    {

    	if (isset($this->fields[$taxonomy])) {
    	
    		foreach ($this->fields[$taxonomy] as $field) {
    		
    			$saved = get_term_meta( $term->term_id, $field['name'], true );
    			
    			switch($field['type']) {
    				case 'text':
    					?>
    					<tr class="form-field term-group-wrap">
					        <th scope="row">
					        	<label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
					        </th>
					        <td>
					        	<input type="text" class="<?php echo (!empty($field['options']['translate'])?' multilang':''); ?>" id="<?php echo $field['name']; ?>" name="<?php echo $field['name']; ?>" value="<?php echo esc_attr($saved); ?>">
					        </td>
					    </tr>
					    <?php
					    break;
    				case 'image':
    					?>
    					<tr class="form-field term-group-wrap">
					        <th scope="row">
					        	<label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
					        </th>
					        <td>
					        	<?php
					        	$upload_link = esc_url( get_upload_iframe_src( 'image', null ) );
								$icon_src = false;
								if (!empty($saved)) {
									$icon_src = wp_get_attachment_image_src( $saved, 'medium' );
								}
								?>
								<div class="meta-img-field" style="margin: 1px;" data-postid="">
									<div class="meta-img-container" style=" display: inline-block; vertical-align: middle; ">
										<?php
										if ( $icon_src ) {
									        echo '<img src="'.$icon_src[0].'" alt="" style="max-height:50px; width: auto; margin-right: 8px;" />';
										}
										?>
									</div>
									<a class="js-upload-meta-img <?php echo ( $icon_src ? 'hidden' : ''); ?>" href="<?php echo $upload_link; ?>"><?php _e('Add image','wp5-bang'); ?></a>
					    			<a class="js-delete-meta-img <?php echo ( !$icon_src ? 'hidden' : ''); ?>" href="#"><?php _e('Remove image','wp5-bang'); ?></a>
					    			<input class="meta-img-id" name="<?php echo $field['name']; ?>" type="hidden" value="<?php echo esc_attr( $saved ); ?>" />
				    			</div>
					        </td>
					    </tr>
					    <?php
    					break;
    			}	
    			
    		}
    		
    	}
    	
	    
    }
    
    public function saveTaxonomy($term_id, $tt_id) 
    {
 	    
 	    $term = get_term( $term_id );
    	    
    	if ($term AND isset($this->fields[$term->taxonomy])) {
    	
    		foreach ($this->fields[$term->taxonomy] as $field) {
    	
    			if( !empty( $_POST[$field['name']] ) ) {
    				switch($field['type']) {
    					case 'text':
    						$value = sanitize_text_field( $_POST[$field['name']] );
    						break;
    					case 'image':
    						$value = intval( $_POST[$field['name']] );
    						break;
    					default:
    						$value = '';
    						break;
    				}		
			        add_term_meta( $term_id, $field['name'], $value, true );
			    }
			
			}
			
	    }
    
    }
    
    public function updateTaxonomy($term_id, $tt_id) 
    {
    	
    	$term = get_term( $term_id );
    	    
    	if ($term AND isset($this->fields[$term->taxonomy])) {

    		foreach ($this->fields[$term->taxonomy] as $field) {

    			if( isset( $_POST[$field['name']] ) ) {
    				switch($field['type']) {
    					case 'text': 
    						$value = sanitize_text_field( $_POST[$field['name']] );
    						break;
    					case 'image':
    						$value = intval( $_POST[$field['name']] );
    						if ($value==0) {
    							$value = '';	
    						}
    						break;
    					default:
    						$value = '';
    						break;
    				}	
    					
			    } else {
			    
    				$value = '';
			    
			    }
			    
				if (empty($value)) {
					delete_term_meta( $term_id, $field['name']);
				} else {
				    update_term_meta( $term_id, $field['name'], $value );
				}
			}
			
	    }
	    
    }
}
	