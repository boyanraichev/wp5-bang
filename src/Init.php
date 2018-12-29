<?php 
namespace Boyo\WPBang;

use Boyo\WPBang\Admin\Init as AdminInit;

if (!defined('ABSPATH')) die;

class Init {
	
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
	    
		if (!defined('THEME_DIR')) {
			define('THEME_DIR',dirname(__DIR__,3));
		}    
		
		if (!defined('THEME_VERSION')) {
			$ver = config('theme.version');
			define('THEME_VERSION',$ver);
		}
		
		// setup theme
    	add_action( 'after_setup_theme', [ $this, 'themeSetup' ] ); 
		add_action( 'init', [ $this, 'themeInit' ] );
		
		// change templates folder
		add_filter( 'index_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( '404_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'archive_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'author_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'category_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'tag_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'date_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'embed_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'home_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'frontpage_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'page_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'paged_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'search_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'single_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'singular_template_hierarchy', [$this,'templatesFolder'] );
		add_filter( 'attachment_template_hierarchy', [$this,'templatesFolder'] );
		
		// remove unneeded things in <head>
		// add_action('init', [$this,'removeHeadLinks'] ); 
		
		// remove admin bar logo
		// add_action('wp_before_admin_bar_render', [$this,'removeBarLogo'], 0);

		if (is_admin()) {
			$admin = AdminInit::instance();
		}
    }
    
    public function themeSetup() {
	    
	    add_theme_support( 'post-thumbnails' );		
		add_theme_support( 'title-tag' ); 	
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
	    
	    $locale = get_locale();
		$locale_file = get_template_directory_uri() ."/languages/$locale.php";
		if ( is_readable($locale_file) ) {
			require_once($locale_file);
		}
    }
    
    public function themeInit() {

		// excerpt for pages
    	add_post_type_support( 'page', 'excerpt' );

    }
    
    public function templatesFolder( $templates ){

	    if(is_array($templates)) {
		    foreach($templates as $key => $template) {
			    $templates[$key] = 'views/'.$template;
		    }
	    }           
		
		return $templates;
		
	}
    
    // Clean up the <head>
	public function removeHeadLinks() {
		
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		remove_action('wp_head', 'feed_links', 2 );
		remove_action('wp_head', 'feed_links_extra', 3 );
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		// remove emoji crap
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		
	}
	
	// remove admin bar logo
	public function removeBarLogo() {
		
	    global $wp_admin_bar;
	    $wp_admin_bar->remove_menu('wp-logo');
	    
	}

}