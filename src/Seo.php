<?php 
namespace Boyo\WPBang;

if (!defined('ABSPATH')) die;

class Seo {
	
	/** @var The single instance of the class */
	private static $_instance = null;	
	
	// Don't load more than one instance of the class
	public static function instance() {
		if ( null == self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }	

	public function __construct() {
		
		$post_meta_fields = [
			'all' => [ 
				'seo_metabox' => [ 
					'metabox_title' => 'SEO',
					'metabox_intro' => '', 
					'sections' => [
						[
							'type'		=> 'single',
							'meta_name' => 'noindexnofollow',
							'meta_type' => 'checkbox',
							'label'		=> 'No index, no follow',
							'placeholder'	=> 'No index, no follow',								
						],
						[
							'type'		=> 'single',
							'meta_name' => 'seo_description',
							'meta_type' => 'textarea',
							'label'		=> 'Custom meta description',
							'placeholder'	=> 'Custom meta description',								
						],				
					],
				],
			],	
		];
		$post_meta = \Boyo\WPBangMeta\PostMeta::instance();
		$post_meta->setFields($post_meta_fields,19);
		
		add_action('admin_init', [$this,'adminOptions']); 

		add_action('wp_head',[$this,'head']);
		//add_filter( 'document_title_parts', [$this,'titleFilter'], 10, 1 );   		
	}

	public function adminOptions() {
	
	    add_settings_section(  
	        'wp5bang_seo', // Section ID 
	        'SEO Information', // Section Title
	        [$this,'sectionSettingCallback'], // Callback
	        'general' // What Page?  This makes the section show up on the General Settings Page
	    );
	
	    add_settings_field( // Option 1
	        'site_ogimage', // Option ID
	        'OG Image default', // Label
	        [$this,'textSettingCallback'], // !important - This is where the args go!
	        'general', // Page it will be displayed (General Settings)
	        'wp5bang_seo', // Name of our section
	        array( // The $args
	            'site_ogimage' // Should match Option ID
	        )  
	    );
	
	    register_setting('general','site_ogimage', 'esc_attr');
	
	}
	
	public function sectionSettingCallback() {
		
	}
	
	public function textSettingCallback($args) {
		echo '<input type="text" id="'. $args[0] .'" name="'. $args[0] .'" value="'.stripslashes(esc_attr( get_option($args[0]) )).'" class="regular-text" />';	
	}

	
	public function head() {
		
		// set up defaults		
		$meta = [
			'description' => get_bloginfo('description'),
			'site_name' => get_bloginfo('name'),
			'app_name' => get_bloginfo('name'),
			'ogtitle' => get_bloginfo('name'),
			'ogtype' => 'website',
			'ogimg'	=> '',
			'noindex' => false,
		];
		
		$ogimage = get_option('site_ogimage');
		
		if (!empty($ogimage)) {
			$meta['ogimg'] = $seo_options['ogimage'];
		}
			
		// if single post/page
		if ( is_single() OR is_page() ) {
			
			global $post;
			
			$meta['noindex'] = get_post_meta($post->ID,'noindexnofollow',true);
			$description = get_post_meta($post->ID,'seo_description',true);
			
			if (!empty($description)) {
				$meta['description'] = stripslashes($description);
			} else {
				$meta['description'] = strip_tags(get_the_excerpt());			
				// if front page use default site image
				if (!is_front_page()) {
					if ( has_post_thumbnail() ) { 
						$meta['ogimg'] = wp_get_attachment_url( get_post_thumbnail_id() ); 
					}	
				}		
			}
					
			$meta['ogtitle'] = get_the_title();
	
		// if tag - tag description
		} elseif ( is_tag() ) { 
			$meta['description'] = strip_tags(tag_description());
			$meta['ogtitle'] = single_term_title('', false);	
		// if category page, use category description as meta description
		} elseif ( is_category() ) { 
			$meta['description'] = strip_tags(category_description());
			$meta['ogtitle'] = single_term_title('', false);	
		// if tax page, use tax description as meta description
		} elseif ( is_tax() ) { 
			$meta['description'] = strip_tags(term_description());
			$meta['ogtitle'] = single_term_title('', false);
		// if archive 
		} elseif ( is_archive() ) {
			$meta['description'] = __('Archive','tablank').' - '.get_bloginfo('name'); 
			$meta['noindex'] = true;
		// if search
		} elseif ( is_search() ) { 
			$meta['noindex'] = true;
		// if attachment
		} elseif ( is_404() ) {	
			$meta['noindex'] = true;
		// if 404
		} elseif ( is_attachment() ) {	
			$meta['noindex'] = true;
		}
		
		if (is_paged()) {
			$meta['noindex'] = true;
		}
		
		$meta['current_url'] = 'http://'.$_SERVER['HTTP_HOST'];
		$path = explode( '?', $_SERVER['REQUEST_URI'] ); // Blow up URI
		$meta['current_url'] .= $path[0]; // Only use the rest of URL - before any parameters
		
		// apply filter
		$meta = apply_filters( 'wp5bang_page_meta', $meta );
		
		if ($meta['noindex']) { 
			?><meta name="robots" content="noindex, nofollow"><?php 
		} 
		if (!empty($meta['description'])) { 
			?>
			<meta name="Description" content="<?php echo $meta['description']; ?>">
			<meta property="og:description" content="<?php echo $meta['description']; ?>" />
			<?php 
		} 
		if (!empty($meta['ogimg'])) { 
			?><meta property="og:image" content="<?php echo $meta['ogimg']; ?>" /><?php 
		}
		
		?>
		<meta property="og:url" content="<?php echo $meta['current_url']; ?>" />
		<meta property="og:site_name" content="<?php echo $meta['site_name']; ?>" />	
		<meta property="og:type" content="<?php echo $meta['ogtype']; ?>" />
		<meta property="og:title" content="<?php echo $meta['ogtitle']; ?>" />			
		<meta name="apple-mobile-web-app-title" content="<?php echo $meta['app_name']; ?>">
		<?php 
	
	}
		

	public function titleFilter( $title ) {
		
		if (is_search()) {
	    	$title['title'] = __('Search for', 'tablank') . ' &quot;'.get_search_query().'&quot;'; 
	    } 
	    global $page, $paged;
	  	if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
	  		$title['page'] = ' ('.__('page', 'tablank').' '. $page.')'; 
		}
		
		return $title;
	}
	
}