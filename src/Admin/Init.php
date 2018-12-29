<?php 
namespace Boyo\WPBang\Admin;

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
	    
		add_action('wp_dashboard_setup', [$this,'removeDashboardWidgets'] );
		
		add_filter('admin_footer_text', [$this,'footerText']);
		
	}
	
	// remove dashboard widgets
	public function removeDashboardWidgets() {

		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		
	} 

	// remove footer wordpress link - in admin
	public function footerText() {
    	
    	return config('theme.description');
    	
	}

	
}