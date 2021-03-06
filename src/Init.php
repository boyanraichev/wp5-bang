<?php 
namespace Boyo\WPBang;

use Boyo\WPBang\Admin\Init as AdminInit;
use Boyo\WPBang\Fields\Init as Fields;

if (!defined('ABSPATH')) die;

class Init {
	
	/** @var The single instance of the class */
	private static $_instance = null;	
	
	// Don't load more than one instance of the class
	public static function instance() 
	{
		if ( null == self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }	
    
    public function __construct() 
	{
		
		define('WP5BANG_VERSION','1.0');
	    
		if (!defined('PROJECT_DIR')) {
			define('PROJECT_DIR',dirname(__DIR__,4));
		}    
		
		// make custom theme folder outside content work		
		add_filter( 'theme_root_uri', function($theme_root) { return WP_HOME . '/themes'; } );
		add_filter( 'theme_root', function($theme_root) { return PROJECT_DIR . '/web/' . 'themes'; } );
		
		// setup theme
		add_action( 'after_setup_theme', [ $this, 'themeSetup' ] ); 
		add_action( 'init', [ $this, 'themeInit' ] );
		
		// remove unneeded things in <head>
		add_action('init', [$this,'removeHeadLinks'] ); 
		
		// remove admin bar logo
		add_action('wp_before_admin_bar_render', [$this,'removeBarLogo'], 0);
		
		// email login (config)
		add_action('wp_authenticate',[$this,'emailLogin'],10,1);
		
		// redirect wp-login.php (config)		
		$this->redirectLogin();
	
		// add browser classes
		add_filter('body_class',[$this,'browserClass']);	

		if (config('theme.custom_fields')) {
			$fields = Fields::instance();
		}
		
		if (config('theme.seo')) {
			$seo = Seo::instance();
		}
		
		if (is_admin()) {
			$admin = AdminInit::instance();
		}
		
    }
    
    public function themeSetup() 
	{
	    
	    add_theme_support( 'post-thumbnails' );		
		add_theme_support( 'title-tag' ); 	
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
	    
    }
    
    public function themeInit() 
	{
		
		// check for maintenance mode
		$this->maintenanceMode();
		
		// excerpt for pages
		add_post_type_support( 'page', 'excerpt' );

    }
    
    /* 
	*  Clean up the <head>
	*/
	public function removeHeadLinks() 
	{
		
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		remove_action('wp_head', 'feed_links', 2 );
		remove_action('wp_head', 'feed_links_extra', 3 );
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		// remove emoji
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		
	}
	
	/* 
	*
	* remove admin bar logo
	*
	*/
	public function removeBarLogo() 
	{
		
	    global $wp_admin_bar;
	    $wp_admin_bar->remove_menu('wp-logo');
	    
	}
	
	/* 
	* MAINTENANCE MODE 
	*/
	public function maintenanceMode() 
	{
	  	if ( !current_user_can( 'administrator' ) ) {
	  		$mode_config = config('theme.maintenance_mode');
			$mode_text = config('theme.maintenance_mode_text');
	  		$mode_option = get_option('wp5bang_maintenance');
			if (!empty($mode_config) OR !empty($mode_option) ) {
			    $protocol = "HTTP/1.0";
				if ( "HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"] )
				$protocol = "HTTP/1.1";
				header( "$protocol 503 Service Unavailable", true, 503 );
				header( 'Content-Type: text/html; charset=utf-8' );
				header( 'Retry-After: 600' );	
				?>
				<!DOCTYPE html>
				<html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>
				body {
					width:100%;
					height:100%;
					background:#3F3F3F;
				}
				h1 {
					max-width: 600px;
					margin: 20px auto;
					font-size: 30px;
					line-height: 60px;
					font-weight: 700;
					font-family: Hevetica,Arial,sans-serif;
					color: white;
				}
				</style>
				</head>
				<body>
					<h1><?php if (!empty($mode_text)) { echo $mode_text; } else { _e( 'Site is being updated. Please come back in a few minutes.', 'wp5-bang' ); } ?></h1>
				</body>
				</html>
				<?php
				die();
			}
		}
	}
	
	/* 
	* Login with Email
	*/
	public function emailLogin(&$username) 
	{
		if (config('theme.email_login')) {
			$user = get_user_by('email',$username);
			if(!empty($user->user_login)) {
				$username = $user->user_login;
			}
		}
	}
	
	/* 
	* Redirect wp-login.php
	*/
	public function redirectLogin() 
	{		

		if ( config('theme.block_login') ) {
			add_action( 'login_form_login', [$this,'redirectHome'] );
			add_action( 'login_form_register', [$this,'redirectHome'] );
			add_action( 'login_form_rp', [$this,'redirectHome'] );
			add_action( 'login_form_resetpass', [$this,'redirectHome'] );
		}
	
	}
	
	
	/* 
	* Redirect to home page
	*/
	public function redirectHome() 
	{
		wp_redirect( home_url( '' ) );
		exit(); 
	}
		
	
	/* 
	* Add browser classes
	*/
	public function browserClass(array $classes) : array
	{
		
		global $is_safari; global $is_gecko; global $is_IE; global $is_chrome; global $is_opera; global $is_iphone;
		
		if ($is_safari) { $classes[] = 'safari'; $classes[] = 'webkit'; }
		if ($is_gecko) { $classes[] = 'mozilla'; }
		if ($is_IE) { $classes[] = 'ie'; }
		if ($is_chrome) { $classes[] = 'chrome'; $classes[] = 'webkit'; }	
		if ($is_opera) { $classes[] = 'opera'; }
		if ($is_iphone) { $classes[] = 'iphone'; }	
		if (wp_is_mobile()) { $classes[] = 'mobile'; }						

		return $classes;

	}

}