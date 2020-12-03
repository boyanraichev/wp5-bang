<?php 
namespace Boyo\WPBang\Fields;
	
if (!defined('ABSPATH')) die;

class PostMeta extends CustomMeta {
	
    /**
     * Constructor
     */
    public function __construct() 
	{
	    
	    $this->register();
		
	}
	
	public function register() 
	{
		
		add_action( 'add_meta_boxes', [ $this, 'addMetaBoxes' ], 10, 1);
        add_action( 'save_post',      [ $this, 'saveMetaBoxes' ] );
		add_action( 'admin_head', [ $this, 'addStyles' ], 100);
		
	}
	
	public function addStyles() 
	{
		echo "<style>
			.tat_metabox_wrap label { font-weight: bold; margin-top: 10px; margin-bottom: 5px; display: inline-block; }
			.tat_metabox_wrap .wp-editor-wrap { width: 100%; max-width: 900px; }
			#postcustomstuff table input.button, #postcustomstuff table input[type=checkbox], #postcustomstuff table input[type=radio]  { width: auto; }
			#postcustomstuff table label { margin-left: 8px; }
			#postcustomstuff .wp-editor-wrap { width:96%; margin: 8px; }
			#postcustomstuff .wp-editor-wrap textarea { width:100%; margin: 0px; }
			.input-wrap { margin-bottom: 10px; }
			.tat_meta_section { padding: 10px 0; }
			.tat-input { width:100%; max-width: 900px; }
			.tat-table { width:100%; max-width: 900px; margin-top: 20px; margin-bottom: 10px; }
			.tat-table td { border-top: 1px solid #ddd; }
			.tat-table td.row-control { vertical-align: middle; text-align:right; padding: 8px; }
			.tat-table td.row-content { }
			.tat-table td.row-content .tat-td-wrap { display:none; }
			.tat-table td.row-content.opened .tat-td-wrap { display: block; }
			.tat-table h4 .dashicons { vertical-align: middle; }
			.tat-table h4.toggled .dashicons-arrow-right:before { content: \"\\f140\"; } 
			.tat-table .tat-td-wrap { padding-left: 22px; }
		</style>";
    }
    
	/*
	* adds the meta boxes
	*
	* @function runs the add_meta_box WP function
	*/
	
	
 	public function addMetaBoxes($post) 
	{
	
		if (!empty($this->fields)) {
	
			if (isset($_GET['post'])) { 
				$this->post_id = intval($_GET['post']);
			} elseif (isset($_POST['post_ID'])) {
				$this->post_id = intval($_POST['post_ID']);
			} else { 
				$this->post_id = ''; 
			}
			
			if (isset($_GET['post_type'])) {
				$post_type = sanitize_text_field($_GET['post_type']);
			} else {
				$post_type = get_post_type();
			}

			// is_page_template( $template = '' ) 
			$template_file = get_post_meta($this->post_id,'_wp_page_template',TRUE);
			// is_front_page
			$front_page = get_option( 'page_on_front');
			
			foreach ($this->fields as $template_page => $tat_page) {

				$addmeta = false;

				if ( $template_file == $template_page OR ( $template_page=='page_on_front' AND $front_page > 0 AND $front_page == $this->post_id ) ) {
					$addmeta = true;
					$metato = 'page';
				} elseif ( ( $template_page == 'cpt_'.$post_type ) OR ( $template_page == 'all' ) ) {
					$addmeta = true;
					$metato = $post_type;					
				}
				
				if ( $addmeta AND !empty($tat_page) AND is_array($tat_page) ) {

					foreach ($tat_page as $metabox_id => $metabox) { 
						add_meta_box( 
					        $metabox_id,
					        $metabox['metabox_title'],
					        [ $this,'renderMetaBoxes' ],
					        $metato,
					        'normal',
					        'high',
					        $metabox
					    );
					}
				}
				
			}
			 
		}
	}	
	
	/*
	* renders the meta box
	*
	* @html prints html
	*/
	
	public function renderMetaBoxes( $post, $args ) 
	{
	
		$this->ID = $post->ID;
		$metabox = $args['args'];
		
		echo '<div class="tat_metabox_wrap">';
				
		// Use nonce for verification
		wp_nonce_field( 'tatnonce', 'tat_metaboxes' );
		
		// echo intro, if present
		if (!empty($metabox['metabox_intro'])) {
			echo '<p>'.$metabox['metabox_intro'].'</p>';
		}
		
		if (!empty($metabox['sections'])) {
			foreach ($metabox['sections'] as $section) {
				if (!empty($section['post_id'])) { $post_id = intval($section['post_id']); } else { $post_id = $post->ID; }
				switch ($section['type']) {
					// single meta field
					case 'text':
						echo '<div class="tat_meta_section">';
							if (!empty($section['title'])) { 
								echo '<h3>'.stripslashes($section['title']).'</h3>';
							}
							if (!empty($section['text'])) { 
								echo '<p>'.stripslashes($section['text']).'</p>';
							}
						echo '</div>';
						break;
					// single meta field
					case 'single':
						echo '<div class="tat_meta_section">';
							$saved = get_post_meta( $post_id, $section['meta_name'], true );
							$options = ( isset($section['options']) ? $section['options'] : null );
							echo $this->printField($post_id,$section['meta_name'],$section['meta_type'],$section['label'],$section['placeholder'],$saved,$options);
						echo '</div>';
						break;
					// multiple fields with single meta name
					case 'multiple':
						$saved = get_post_meta( $post_id, $section['meta_name'], true );
						echo '<div class="tat_meta_section">';
							if (!empty($section['title'])) {
								echo '<h3>'.$section['title'].'</h3>';
							}
							foreach ($section['rows'] as $row) {
								$name = $section['meta_name'].'['.$row['name'].']';
								$id = $section['meta_name'].'__'.$row['name'];
								$options = ( isset($row['options']) ? $row['options'] : null );
								$value = ( isset( $saved[$row['name']] ) ? $saved[$row['name']] : '' );
								echo $this->printField($post_id,$name,$row['meta_type'],$row['label'],$row['placeholder'],$value,$options,$id);
							}
						echo '</div>';
						break;
					// dynamic table	
					case 'table':
						if (!empty($section['title'])) {
							echo '<h4>'.$section['title'].'</h4>';
						}
						if (!empty($section['text'])) { 
							echo '<p>'.stripslashes($section['text']).'</p>';
						}
						$saved = get_post_meta( $post_id, $section['meta_name'], true );
						?>
						<div id="postcustomstuff">
						<table id="<?php echo $section['table']; ?>" class="tat-table js-sortable">
							<thead><tr>
								<th style="width:90%; "><?php _e('Content','wp5-bang'); ?></th>
								<th style="width:10%;"><?php _e('Actions','wp5-bang'); ?></th>
							</tr></thead>
							<tbody id="<?php echo $section['table']; ?>-tbody">
							<?php 
							if (is_array($saved)) {
							foreach ($saved as $key => $single) {
								?>
								<tr class="row" style="vertical-align: middle; background-color: #f9f9f9;" data-key="<?php echo $key; ?>" >
									<td id="<?php echo $section['table']; ?>-row-<?php echo $key; ?>" class="row-content">
										<h4 class="js-toggle" data-toggle="<?php echo $section['table']; ?>-row-<?php echo $key; ?>" style="cursor:pointer; margin: 8px;">
											<span class="dashicons dashicons-arrow-right" ></span>
											<?php 
											if (!empty($single['logo'])) {
												$your_img_src = wp_get_attachment_image_src( $single['logo'], 'thumb' );
											} elseif (!empty($single['photo'])) {
												$your_img_src = wp_get_attachment_image_src( $single['photo'], 'thumb' );
											} elseif (!empty($single['icon'])) {
												$your_img_src = wp_get_attachment_image_src( $single['icon'], 'thumb' );
											} else { 
												$your_img_src = null;
											}
											if ( $your_img_src ) {
												echo '<img src="'.$your_img_src[0].'" alt="" style="max-height:30px; max-width:50px; height: auto; width: auto; vertical-align: middle; margin-right: 8px; " />';
											}
											if (!empty($single['title'])) {
												echo '<span class="i18n-multilingual-display">'.$single['title'].'</span>';
											} else {
												echo ( isset($section[$single['row']]) ? $section[$single['row']] : $single['row'] ); 
											} ?>
											</h4>
										<div class="tat-td-wrap">
										<?php
										if (isset($single['row']) AND isset($section['rows'][$single['row']])) {
											echo '<input type="hidden" name="'.$section['meta_name'].'['.$key.'][row]" value="'.$single['row'].'" />';
											foreach ($section['rows'][$single['row']] as $field) {
												$name = $section['meta_name'].'['.$key.']['.$field['name'].']';
												$value = ( isset( $single[$field['name']] ) ? $single[$field['name']] : '' );
												$label = ( !empty( $field['label'] ) ? $field['label'] : '' );
												$options = ( isset($field['options']) ? $field['options'] : null );
												echo $this->printField($post_id, $name,$field['meta_type'],$label,$field['title'],$value,$options);
											}
										}					
										?>
										</div>
									</td>
									<td class="row-control"><span class="dashicons dashicons-sort handle" style="cursor:pointer;"></span> <span class="js-del-row dashicons dashicons-trash" style="cursor:pointer;"></span></td>
								</tr>
								<?php
							}
							}
							?>
							</tbody>
						</table>

						<?php 
						foreach ($section['rows'] as $row => $row_fields) {
							ob_start();
							?>
								<tr class="<?php echo $row; ?> row" style="vertical-align: middle; background-color: #f9f9f9;" data-key="__key__">
									<td id="<?php echo $section['table']; ?>-row-__key__" class="row-content opened" ><input type="hidden" name="<?php echo $section['meta_name'].'[__key__][row]'; ?>" value="<?php echo $row; ?>" />
										<h4 class="js-toggle toggled" data-toggleid="<?php echo $section['table']; ?>-row-__key__" style="margin: 8px; cursor:pointer;">
											<span class="dashicons dashicons-arrow-right" ></span>
											<?php echo ( isset($section[$row]) ? $section[$row] : $row ); ?>
										</h4>
										<div class="tat-td-wrap">
										<?php 
										foreach ($row_fields as $field) {
											$name = $section['meta_name'].'[__key__]['.$field['name'].']';
											$label = ( !empty( $field['label'] ) ? $field['label'] : '' );
											$options = ( isset($field['options']) ? $field['options'] : null );
											echo $this->printField($post_id,$name,$field['meta_type'],$label,$field['title'],'',$options);
										}
										?>
										</div>
									</td>
									<td class="row-control" style="text-align:right; padding-top: 8px; padding-right: 8px;"><span class="dashicons dashicons-sort handle" style="cursor:pointer;"></span> <span class="js-del-row dashicons dashicons-trash" style="cursor:pointer;"></span></td>
								</tr>
							<?php 
							$prototype = ob_get_clean();
							?>
							<button class="js-add-row button-secondary button" data-table="<?php echo $section['table']; ?>-tbody" data-callback="metaBoxImgListeners" data-prototype="<?php echo htmlspecialchars($prototype,ENT_QUOTES); ?>"><span class="dashicons dashicons-plus" style="margin-top:4px; "></span> <?php echo ( isset($section[$row]) ? $section[$row] : $row ); ?></button> 
							<?php 
						}
						?>
						</div><br />						
						<?php
						break; 
				}
			}
		}	
		
		echo '</div>';	
	}
	
	public function printField($post_id, $name, $type='text', $label, $placeholder, $value=null, $options=null, $id=null) 
	{
		
		if (!$id) {
			$id = $name;
		}
		
		ob_start();
		switch ($type) {
			case 'text':
				if ($label) { echo '<label for="'.$id.'">'.$label.'</label><br />'; }
				echo '<input type="text" name="'.$name.'" id="'.$id.'" placeholder="'.$placeholder.'" value="'.esc_attr($value).'" class="tat-input tat-meta'.(!empty($options['translate'])?' multilang':'').'"><br>';
				break;
			case 'textarea':
				if ($label) { echo '<label for="'.$id.'">'.$label.'</label><br />'; }
				echo '<textarea name="'.$name.'" id="'.$id.'" placeholder="'.$placeholder.'" rows="5" class="tat-input tat-meta'.(!empty($options['translate'])?' multilang':'').'">'.$value.'</textarea><br>';
				break;
			case 'textarea-xl':
				$id = str_replace('_', '', $id); $id = str_replace('_', '', $id);
				if ($label) { echo '<label for="'.$id.'">'.$label.'</label><br>'; }
				$args = array (
					'media_buttons' => false,
					'textarea_name' => $name,
			        'tinymce' => false,
			    	'quicktags' => true,
			    	'textarea_rows' => 8,
				);
				if (!empty($options['translate'])) { $args['editor_class'] = 'multilang'; }
				wp_editor( $value, $id, $args );
				break;	
			case 'image':
			case 'video':
				if ($label) { echo '<label for="'.$name.'">'.$label.'</label><br />'; }
				$upload_link = esc_url( get_upload_iframe_src( 'image', $this->post_id ) );
				$has_image = false;
				if (!empty($value)) {
					if ($type=='video') {
						$your_img_src = wp_get_attachment_url($value);
						$has_image = ( !empty($your_img_src) ?? false );
					} else {	
						$your_img_src = wp_get_attachment_image_src( $value, 'medium' );
						$has_image = is_array( $your_img_src );
					}
				}
				echo '<div class="meta-img-field" style="margin: 1px;" data-postid="'.$post_id.'" data-type="'.$type.'">';
					echo '<div class="meta-img-container" style=" display: inline-block; vertical-align: middle; ">';
					if ( $has_image ) {
				        	if ($type=='video') {
							echo '<video src="'.$your_img_src.'" style="max-height:50px; width: auto; margin-right: 8px;" controls></video>';								
						} else {
							echo '<img src="'.$your_img_src[0].'" style="max-height:50px; width: auto; margin-right: 8px;" />';							
						}
					}
				echo '</div>';
				$file_type = ( !empty($label) ?  $label : __('image','wp5-bang') );
				echo '<a class="js-upload-meta-img '.( $has_image ? 'hidden' : '').'" href="'.$upload_link.'">'.__('Add','wp5-bang').' '.$file_type.'</a>';
	    			echo '<a class="js-delete-meta-img '.( !$has_image ? 'hidden' : '').'" href="#">'.__('Remove','wp5-bang').' '.$file_type.'</a>';
	    			echo '<input class="meta-img-id" name="'.$name.'" type="hidden" value="'.esc_attr( $value ).'" />';
    			echo '</div>';
				break;
			case 'select':
				if (!empty($options) AND is_array($options)) {
					if ($label) { echo '<label for="'.$id.'">'.$label.'</label><br />'; } elseif ($placeholder) { echo '<label for="'.$id.'">'.$placeholder.'</label><br />'; }
					echo '<select name="'.$name.'" id="'.$id.'">'; 
						foreach ($options as $option_value => $option_title) {
							echo '<option value="'.$option_value.'" '.selected($option_value,$value,false).'>'.$option_title.'</option>';
						}
					echo '</select><br />';
					break;
				}
				break;
			case 'checkbox':
				echo '<label for="'.$id.'"><input type="checkbox" name="'.$name.'" id="'.$id.'" value="1" '.checked('1',$value,false).' /> '.$label.'</label><br />';
				break;
			case 'radio':
				if (!empty($options) AND is_array($options)) {
					echo '<div class="input-wrap">';
						if ($label) { echo '<label for="'.$name.'">'.$label.'</label><br>'; } elseif ($placeholder) { echo '<label for="'.$name.'">'.$placeholder.'</label><br />'; } 
						foreach ($options as $option_value => $option_title) {
							echo '<label for="'.$id.'_'.$option_value.'"><input id="'.$id.'_'.$option_value.'" name="'.$name.'" type="radio" value="'.$option_value.'" '.checked($option_value,$value,false).' />'.$option_title.'</label><br />';
						}
					echo '</div>';
					break;
				}
				break;		
			case 'dropdown_posts':
				if ($label) { echo '<label for="'.$name.'">'.$label.'</label><br />'; }
				if (!$value) { $value=0; }
				if (empty($options['post_type'])) {
					$options['post_type'] = 'post';
				}
				$args = array( 
					'post_type' => $options['post_type'],
					'posts_per_page' => 20,
					'post_status' => 'publish',
				); 
				$posts = new \WP_Query($args);
				if ($posts->have_posts()) {
					echo '<select name="'.$name.'" id="'.$id.'">';
						echo '<option value="">'.__('None','wp5-bang').'</option>';
						while ($posts->have_posts()) {
							$posts->the_post();
							echo '<option value="'.$posts->post->ID.'" '.selected($posts->post->ID,$value,false).'>'.get_the_title().'</option>';
						}
						wp_reset_postdata();
					echo '</select>';
				}
				break; 
			case 'dropdown_pages':
				if ($label) { echo '<label for="'.$name.'">'.$label.'</label><br />'; }
				if (!$value) { $value=0; }
				$args = array(
				    'selected'              => $value,
				    'name'                  => $name,
				    'class'                 => '', // string
				    'show_option_none'      => __('None','wp5-bang'), // string
				    'option_none_value'     => '0',
				    'post_type'				=> $options['post_type']
				); 
				wp_dropdown_pages( $args ); echo '<br>';
				break;
			case 'dropdown_cats':
				if ($label) { echo '<label for="'.$name.'">'.$label.'</label><br>'; }
				if (!$value) { $value=0; }
				$args = array(
				    'selected'              => $value,
				    'name'                  => $name,
				    'class'                 => '', // string
				    'show_option_none'      => __('None','wp5-bang'), // string
				    'option_none_value'     => '0',
				    'hide_empty'			=> 0,
				    'taxonomy'				=> $options['taxonomy'],
				); 
				wp_dropdown_categories( $args ); echo '<br />';
				
				break;									
		}
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}
	
	public function saveMetaBoxes( $post_id ) 
	{
	 	// verify if this is an auto save routine. 
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		
		// verify nonce
		if ( !isset($_POST['tat_metaboxes'])) { return; }
		if ( !wp_verify_nonce( $_POST['tat_metaboxes'], 'tatnonce' ) ) {  return; }

		if (isset($_GET['post_type'])) {
			$post_type = sanitize_text_field($_GET['post_type']);
		} else {
			$post_type = get_post_type();
		}

		// is_page_template( $template = '' ) 
		$template_file = get_post_meta($post_id,'_wp_page_template',TRUE);
		// is_front_page
		$front_page = get_option( 'page_on_front');

		foreach ($this->fields as $template_page => $pagetemplate) {

			$addmeta = false;

			if ( $template_file == $template_page OR ( $template_page=='page_on_front' AND $front_page > 0 AND $front_page == $post_id ) OR ( $template_page == 'cpt_'.$post_type ) OR ( $template_page == 'all' ) ) {
				$addmeta = true;
			}
			
			if ( $addmeta AND !empty($pagetemplate) AND is_array($pagetemplate) ) {

				foreach ($pagetemplate as $meta_box) { 
					
					foreach ($meta_box['sections'] as $section) {
					
						if (!empty($section['post_id'])) { $post_id_save = intval($section['post_id']); } else { $post_id_save = $post_id; }
						
						switch ($section['type']) {
							case 'single':
								switch ($section['meta_type']) {
									case 'image':
									case 'video':
									case 'number':
									case 'dropdown_pages':
									case 'dropdown_posts':
									case 'dropdown_cats':
										if (isset($_POST[$section['meta_name']])) { 
											$post_data = intval($_POST[$section['meta_name']]);
											update_post_meta($post_id_save, $section['meta_name'], $post_data);
										}
										break;
									case 'checkbox':
										if (isset($_POST[$section['meta_name']]) AND $_POST[$section['meta_name']]=='1') {
											$post_data = 1;
										} else {
											$post_data = 0;
										}
										update_post_meta($post_id_save, $section['meta_name'], $post_data);
									case 'text':
									case 'select':
									case 'radio':
										if (isset($_POST[$section['meta_name']])) { 
											$post_data = sanitize_text_field($_POST[$section['meta_name']]);
											update_post_meta($post_id_save, $section['meta_name'], $post_data);
										}
										break;  
									case 'textarea':
										if (isset($_POST[$section['meta_name']])) { 
											$post_data = wp_kses($_POST[$section['meta_name']],array('br'=>array(), 'strong'=>array(), 'a'=>array( 'href' => array(), 'title' => array(), 'iframe' => array(), 'class' => array(), 'data-modal' => array())));
											update_post_meta($post_id_save, $section['meta_name'], $post_data);
										}
										break;
									case 'textarea-xl':
										if (isset($_POST[$section['meta_name']])) { 
											$post_data = $_POST[$section['meta_name']];
											update_post_meta($post_id_save, $section['meta_name'], $post_data);
										}
								}
								break;
							case 'multiple':
								if (isset($_POST[$section['meta_name']])) { 
									$post_data = $_POST[$section['meta_name']];
									update_post_meta($post_id_save, $section['meta_name'], $post_data);
								}
								break;
							case 'table':
								if (isset($_POST[$section['meta_name']])) { 
									$post_data = $_POST[$section['meta_name']]; 
									if (isset($post_data['bahur'])) { unset($post_data['bahur']); }
									$tosave = array(); 
									foreach ($post_data as $single) {
										$tosave[] = $single;
									}
									update_post_meta($post_id_save, $section['meta_name'], $tosave);
								}
								break;
						}
					}
				}
			}
		}

	}

}
