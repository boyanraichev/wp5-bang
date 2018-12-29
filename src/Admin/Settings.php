<?php
namespace Boyo\WPBang;

if (!defined('ABSPATH')) die;

class Settings {

    /**
     * Holds the values to be used in the fields callbacks
     */
    public $options = array();

	/** @var The single instance of the class */
	protected static $_instance = null;	
	
	// Don't load more than one instance of the class
	public static function instance() {
		if ( !isset(static::$_instance) ) {
            static::$_instance = new static;
        }
        return static::$_instance;
    }

    /**
     * Construct
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_theme_menu' ] );
        add_action( 'admin_init', [ $this, 'page_init' ] );
    }

    /**
     * Add options page
     */
    public function add_theme_menu() {
        add_theme_page(
        	__('Design & Marketing Settings', 'tablank'), 
        	__('Theme Settings', 'tablank'), 
        	'manage_options', 
        	'tat_options', 
        	[ $this, 'create_theme_page']
        );
    }

    /**
     * Options page callback
     */
    public function create_theme_page() {

        ?>
        <div class="wrap">
			<h2><?php _e('Theme Settings', 'tablank'); ?></h2>
			<!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
			<?php settings_errors(); ?>
			<?php 	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'options'; ?>
			<h2 class="nav-tab-wrapper">
				<a href="themes.php?page=tat_options&tab=options" class="nav-tab <?php echo $active_tab == 'options' ? 'nav-tab-active' : ''; ?>"><?php _e('Configuration', 'tablank'); ?></a>
				<a href="themes.php?page=tat_options&tab=design_options" class="nav-tab <?php echo $active_tab == 'design_options' ? 'nav-tab-active' : ''; ?>"><?php _e('Frameworks', 'tablank'); ?></a>
				<a href="themes.php?page=tat_options&tab=social_options" class="nav-tab <?php echo $active_tab == 'social_options' ? 'nav-tab-active' : ''; ?>"><?php _e('Social Media', 'tablank'); ?></a>
				<a href="themes.php?page=tat_options&tab=seo_options" class="nav-tab <?php echo $active_tab == 'seo_options' ? 'nav-tab-active' : ''; ?>"><?php _e('SEO', 'tablank'); ?></a>
				<?php do_action('tat_settings_tabs',$active_tab); ?>
			</h2>
			<!-- Create the form that will be used to render our options -->
			<form method="post" action="options.php">
			 	<?php
			 	switch($active_tab) {
				case 'options':
					$this->options['tat_general_options'] = get_option( 'tat_general_options' );
					settings_fields( 'tat_general_options_group' );
					do_settings_sections( 'tat_general_options' );
					break;
				case 'design_options':
					$this->options['tat_design_options'] = get_option( 'tat_design_options' );				
					settings_fields( 'tat_design_options_group' );
					do_settings_sections( 'tat_design_options' );
					break;
				case 'social_options':		
					$this->options['tat_social_options'] = get_option( 'tat_social_options' );
					settings_fields( 'tat_social_options_group' );
					do_settings_sections( 'tat_social_options' );			
					break;
				case 'seo_options':		
					$this->options['tat_seo'] = get_option( 'tat_seo' );
					settings_fields( 'tat_seo_options_group' );
					do_settings_sections( 'tat_seo_options' );			
					break;
				}
				do_action('tat_settings_form',$active_tab);
				submit_button();
				?>
			</form>
		</div><!-- /.wrap -->
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {    
    	// register OPTIONS for wp_options   
        register_setting(
			'tat_general_options_group',
			'tat_general_options',
			[ $this, 'sanitize' ]
		);
		register_setting(
			'tat_social_options_group',
			'tat_social_options',
			[ $this, 'sanitize' ]
		);
		register_setting(
			'tat_design_options_group',
			'tat_design_options',
			[ $this, 'sanitize' ]
		);
		register_setting(
			'tat_seo_options_group',
			'tat_seo',
			[ $this, 'sanitize' ]
		);
		
        
		// register SECTIONS per page  
        add_settings_section(
			'tat_settings_general',			// ID 
			__('Basic site settings', 'tablank'), // Title 
			[ $this, 'general_options_callback' ],	// Callback 
			'tat_general_options'	// Page 
		);
		
		add_settings_section(
			'tat_settings_design',			
			__('Frameworks', 'tablank'),
			[ $this, 'design_options_callback' ],	
			'tat_design_options'			
		);
		
		add_settings_section(
			'tat_settings_google',			
			__('Web services', 'tablank'),
			[ $this, 'google_options_callback' ],	
			'tat_social_options'			
		);
		
		add_settings_section(
			'tat_settings_social',			
			__('Social media', 'tablank'),
			[ $this, 'social_options_callback' ],	
			'tat_social_options'			
		);
		
		add_settings_section(
			'tat_settings_seo',			
			__('Social media', 'tablank'),
			[ $this, 'seo_options_callback' ],	
			'tat_seo_options'			
		);
		
		// register settings FIELDS per section and page
              
        // CONFIGURATION options 
		add_settings_field( 
			'tat_maintanance_mode',	 // ID
			__('Maintenance', 'tablank'),	 // Title
			[ $this, 'input_checkbox' ],	// Callback
			'tat_general_options',	 // Page
			'tat_settings_general',	 // Section	
			[
				'descr' => __('Check to temporarily hide the website', 'tablank'),
				'name'	=> 'tat_general_options',
				'key'	=> 'maintenance',
			]
		 ); 
		 add_settings_field( 
			'tat_maintanance_mode_text',	 
			__('Maintenance text', 'tablank'),	 
			[ $this, 'input_textarea' ],	 
			'tat_general_options',	
			'tat_settings_general',	 	
			[
				'descr' => __('Maintenance text', 'tablank'),
				'name'	=> 'tat_general_options',
				'key'	=> '503',
			]
		 ); 
		 add_settings_field( 
			'tat_login',	 
			__('Email login', 'tablank'),	
			array( $this, 'input_checkbox' ),	
			'tat_general_options',	
			'tat_settings_general',	 	
			[
				'descr' => __('Login with email address', 'tablank'),
				'name'	=> 'tat_general_options',
				'key'	=> 'email_login',
			]	 
		 );
		 add_settings_field( 
			'tat_blocklogin',	 
			__('Block wp-login', 'tablank'),	
			[ $this, 'input_checkbox' ],	
			'tat_general_options',	
			'tat_settings_general',	 	
			[
				'descr' => __('Block access to wp-login.php (security)', 'tablank'),
				'name'	=> 'tat_general_options',
				'key'	=> 'blockwplogin',
			]	 
		 ); 		
		 add_settings_field( 
			'tat_register',	 
			__('Registration', 'tablank'),	
			[ $this, 'input_checkbox' ],	
			'tat_general_options',	
			'tat_settings_general',	 	
			[
				'descr' => __('Activate [TAT_REGISTER] shortcode', 'tablank'),
				'name'	=> 'tat_general_options',
				'key'	=> 'register',
			]	 
		 );
		 add_settings_field( 
			'tat_lostpass',	 
			__('Lost Password', 'tablank'),	
			[ $this, 'input_checkbox' ],	
			'tat_general_options',	
			'tat_settings_general',	 	
			[
				'descr' => __('Activate [TAT_LOST_PASSWORD] shortcode', 'tablank'),
				'name'	=> 'tat_general_options',
				'key'	=> 'lostpass',
			]	 
		 );
		
		 add_settings_field( 
			'tat_option_cookielaw',	 
			__('Cookie law', 'tablank'),	 
			[ $this, 'input_checkbox' ],
			'tat_design_options',	 
			'tat_settings_design',	 
			[
				'descr' => __('Show cookie warning', 'tablank'),
				'name'	=> 'tat_design_options',
				'key'	=> 'cookielaw',
			]
		 );
		
		// SOCIAL AND GOOGLE

		add_settings_field( 
			'google_verification',	 
			__('Google verification meta', 'tablank'),	 
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_google',	 
			[
				'descr' => __('META for Google Search Console verification', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'google_verification',
			]	 
		 ); 
		add_settings_field( 
			'google_analytics',	
			__('Analytics UI', 'tablank'),	
			[ $this, 'input_text' ],
			'tat_social_options',	
			'tat_settings_google',	
			[
				'descr' => __('Code for Google Analytics', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'google_analytics',
			]	 
		 );
		 add_settings_field( 
			'google_maps',	
			__('Maps API key', 'tablank'),	 
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_google',	 
			[
				'descr' => __('Code for Google Maps API', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'google_maps',
			]
		 );	 
		 add_settings_field( 
			'google_maps_incl',	
			__('Maps Inclusion', 'tablank'),	 
			[ $this, 'input_checkbox' ],
			'tat_social_options',	 
			'tat_settings_google',	 
			[
				'descr' => __('Check to include maps JS on every page', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'maps_incl',
			]
		 );		
		add_settings_field( 
			'tat_facebook_page',
			__('Facebook page', 'tablank'),	
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('Full url', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'facebook_page',
			]
		 );
		 add_settings_field( 
			'tat_facebook_pageid',	
			__('Facebook ID', 'tablank'),	
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('Page ID', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'facebook_pageid',
			]
		 );
		 add_settings_field( 
			'tat_facebook_appid',	 
			__('Facebook App ID', 'tablank'),
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('Facebook App ID (required for FB plugins)', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'facebook_appid',
			]	
		 );
		 add_settings_field( 
			'tat_twitter_page',
			__('Twitter page', 'tablank'),
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('Full address', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'twitter_page',
			]
		 );
		 add_settings_field( 
			'tat_google_page',	
			__('Google + page', 'tablank'),
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('Full address', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'google_page',
			]	
		 );
		 add_settings_field( 
			'tat_linkedin_page',	
			__('LinkedIn page', 'tablank'),
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('Full address', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'linkedin_page',
			]
		 );
		 add_settings_field( 
			'tat_youtube_page',	
			__('YouTube page', 'tablank'),
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('Full address', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'youtube_page',
			]
		 );	
		 add_settings_field( 
			'tat_instagram_page',	
			__('Instagram page', 'tablank'),
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('Full address', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'instagram_page',
			]
		 );	
		 add_settings_field( 
			'tat_apple_app',	
			__('iOS App', 'tablank'),
			[ $this, 'input_text' ],
			'tat_social_options',	 
			'tat_settings_social',	 
			[
				'descr' => __('ID', 'tablank'),
				'name'	=> 'tat_social_options',
				'key'	=> 'apple_app',
			]
		 ); 	
		 
		 // SEO 	 
         
		 add_settings_field( 
			'tat_option_seo',	 
			__('SEO', 'tablank'),
			[ $this, 'input_checkbox' ],
			'tat_seo_options',	 
			'tat_settings_seo',	 
			[
				'descr' => __('Use SEO framework', 'tablank'),
				'name'	=> 'tat_seo',
				'key'	=> 'seo',
			]	
		 ); 
		 add_settings_field( 
			'tat_option_seobox',	 
			__('SEO box', 'tablank'),
			[ $this, 'input_checkbox' ],
			'tat_seo_options',	 
			'tat_settings_seo',	 
			[
				'descr' => __('Use SEO meta box', 'tablank'),
				'name'	=> 'tat_seo',
				'key'	=> 'meta',
			]
		 );
		 add_settings_field( 
			'tat_option_ogsite',	 
			__('OG sitename', 'tablank'),
			[ $this, 'input_text' ],
			'tat_seo_options',	 
			'tat_settings_seo',	 
			[
				'name'	=> 'tat_seo',
				'key'	=> 'ogsite',
			]	 
		 );
		 add_settings_field( 
			'tat_option_ogimg',	 
			__('OG img', 'tablank'),
			[ $this, 'input_text' ],
			'tat_seo_options',	 
			'tat_settings_seo',	 
			[
				'descr' => __('Photo URL for default OG tag', 'tablank'),
				'name'	=> 'tat_seo',
				'key'	=> 'ogimage',
			]
		 ); 
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) { 
        $new_input = [];
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        // return $new_input;
        return $input;
    }

    /** 
     * Print the Section text
     */
    public function empty_section_info() {
        echo '';
    }
	public function general_options_callback() {
		echo '<p>'._e('Common site configuration', 'tablank').'</p>';
	}
	public function design_options_callback() {
		echo '<p>'._e('Different frameworks to use in page development', 'tablank').'</p>'; 
	}
	public function google_options_callback() {
		echo '<p>'._e('Setup different Google services here', 'tablank').'</p>';
	}  
	public function social_options_callback() {
		echo '<p>'._e('Used for social media integration', 'tablank').'</p>';
	}  
	public function seo_options_callback() {
		echo '<p>'._e('Activate different SEO functions. By turning SEO on the framework will be setting your title, meta description and OG tags. The default is to use the post title & excerpt, but they can be overriden with custom fields when you turn on the meta box.', 'tablank').'</p>';
	} 
    /** 
     * Prints input type=text options
     */
    public function input_text($args) {
    	$option = $this->extract_option_data($args);
		// Render the output
		echo '<input type="text" id="'. $option['id'] .'" name="'. $option['name'] .'" value="'.stripslashes(esc_attr( $option['value'] )).'" class="regular-text" />';
		echo ( isset($args['descr']) ? '<br><small><label for="'.$option['id'].'">'. $args['descr'] .'</label></small>' : '' ); 
    }
    
    /** 
     * Prints textarea options
     */
	public function input_textarea($args) {
		$option = $this->extract_option_data($args);
		// Render the output
		echo '<textarea id="'. $option['id'] .'" name="'. $option['name'] .'" rows="6" cols="45" >'. stripslashes(esc_textarea($option['value'])) .'</textarea>';
		echo ( isset($args['descr']) ? '<br><small><label for="'.$option['id'].'">'. $args['descr'] .'</label></small>' : '' );
	}
	
    /** 
     * Prints input type=text options
     */
    public function input_number($args) {
    	$option = $this->extract_option_data($args);
    	$class = ( ( !isset($args['max']) OR $args['max'] > 99999 ) ? 'regular-text' : 'small-text' );
		// Render the output
		echo '<input type="number" id="'. $option['id'] .'" name="'. $option['name'] .'" value="'.stripslashes(esc_attr( $option['value'] )).'" min="'.$args['min'].'" max="'.$args['max'].'" step="'.$args['step'].'" class="'.$class.'" />';
		echo ( isset($args['descr']) ? '<br><small><label for="'.$option['id'].'">'. $args['descr'] .'</label></small>' : '' );
    }

    /** 
     * Prints checkbox options
     */
    public function input_checkbox($args) {
    	$option = $this->extract_option_data($args);
		// Render the output
		echo '<label for="'. $option['id'] .'"><input type="checkbox" id="'. $option['id'] .'" name="'. $option['name'] .'" size="20" value="1" '.checked($option['value'],'1',false).' />'. $args['descr'] . '</label>';
    }
    
    /** 
     * Prints radio options
     */
    public function input_radio($args) {
		$option = $this->extract_option_data($args);
		$radio_array = $args['options'];
		// Render the output
		foreach ( $radio_array as $value => $name) {
			echo '<label for="'. $option['id'].'-'.$value .'"><input type="radio" id="'. $option['id'].'-'.$value .'" name='. $option['name'] .' value="'. $value .'" '.checked($option['value'],$value,false).' /> '. $name . '</label><br />'; 
		}  	
		echo ( isset($args['descr']) ? '<small><label for="'.$option['id'].'">'. $args['descr'] .'</label></small>' : '' );
	}
	
	/** 
     * Prints dropdown pages select
     */
    public function input_dropdown_page($args) {
    	$option = $this->extract_option_data($args);
		// Render the output
		$post_type = ( isset($args['post_type']) ? $args['post_type'] : 'page');
		$args = array(
		   'selected'              => $option['value'],
		   'name'                  => $option['name'],
		   'id'					   => $option['id'],
		   'class'                 => '', // string
		   'show_option_none'      => __('None','tablank'), // string
		   'option_none_value'     => '0',
		   'post_type'				=> $post_type
		); 
		wp_dropdown_pages( $args );
		echo ( isset($args['descr']) ? '<br><small><label for="'.$option['id'].'">'. $args['descr'] .'</label></small>' : '' );
    }
    
    /** 
     * Prepares an $option array with name, id and value for this option
     */
    public function extract_option_data($args) {
		$option = array();
		if ( !empty($args['key']) ) { 
			$option['name'] = $args['name'] . '[' . $args['key'] . ']'; 
			$option['id'] = $args['name'].'-'.$args['key']; 
			$settings = get_option($args['name'],'');
			$option['value'] = ( isset($this->options[$args['name']][$args['key']]) ? $this->options[$args['name']][$args['key']] : '');
		} else { 
			$option['name'] = $args['name']; 
			$option['id'] = $args['name'];
			$option['value'] = ( isset( $this->options[$args['name']] ) ? $this->options[$args['name']] : '');
		}
		return $option;
	}
	

	
	public function validate_options( $input ) {
		// Create our array for storing the validated options
		$output = array();
		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {
			// Check to see if the current option has a value. If so, process it.
			if( isset( $input[$key] ) ) {
				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
			} // end if
		} // end foreach
		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'kosher_validate_options', $output, $input );
	}

}