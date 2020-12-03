<?php 
namespace Boyo\WPBang\Fields;

use Boyo\WPBang\Fields\PostMeta;
use Boyo\WPBang\Fields\TermMeta;

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
	
		$config_post_meta = config('post_meta');
		
		if(!empty($config_post_meta)) {
			
			$post_meta = PostMeta::instance();
			$post_meta->setFields($config_post_meta);
			
		}
		
		$config_term_meta = config('term_meta');
		
		if(!empty($config_term_meta)) {
			
			$term_meta = TermMeta::instance();
			$term_meta->setFields($config_term_meta);
			
		}

		add_action( 'admin_enqueue_scripts', [$this,'bangMetaScripts'] );

	}
	
	public function bangMetaScripts() 
	{
	
	    wp_enqueue_media();
	    
	    wp_enqueue_script( 'jquery-ui-sortable' );
	
		wp_register_script( 'tat-admin',  get_template_directory_uri() . '/assets/vendor/wp5-bang/js/bang.meta.js', ['jquery'], WP5BANG_VERSION, true );      
		wp_enqueue_script('tat-admin');

	}
}
