<?php 
namespace Boyo\WPBang\Admin;


if (!defined('ABSPATH')) die;

class Lock {
	
	/** @var The single instance of the class */
	private static $_instance = null;
	
	private $plugins_lock = [];	
	
	// Don't load more than one instance of the class
	public static function instance() {
		if ( null == self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }	
    
    public function __construct() {
	    
	    // add link to generate lock file
	    add_action('pre_current_active_plugins', [$this,'readLock'], 10 , 1);
	    
        // add link to generate lock file
	    add_action('pre_current_active_plugins', [$this,'addLink'], 10 , 1);
			    
	    // add column to plugins table
	    add_filter( 'manage_plugins_columns', [$this,'addColumn'], 10, 1 );
    	add_action( 'manage_plugins_custom_column', [$this,'addColumnContent'], 10, 3);
			
		if ( is_multisite() ) {
			add_filter( 'manage_plugins-network_columns', [$this,'addColumn'], 10, 1 );
			add_action( 'manage_plugins-network_custom_column', [$this,'addColumnContent'], 10, 3);		
		} else {
			
		}
		
		add_filter( 'plugin_row_meta',[$this,'addPluginRowMeta'],10, 4 );
		add_action( 'after_plugin_row', [$this,'addPluginAfter'], 10 , 3);
	}
	
	public function addLink($plugins) {

		echo '<div><a href="#">'.__('Generate lock file','wp5-bang').'</a></div>';
		
	}
	
	public function saveLock() {
		
	}
	
	public function readLock($plugins) {
		
		$lockfile_path = PROJECT_DIR . '/plugins.lock';
		if (file_exists($lockfile_path)) {
			
			$lockfile = file_get_contents($lockfile_path);
			$this->plugins_lock = json_decode($lockfile);
			
		}
		
	}
	
	/*
	 * Read the lock file and compare with the plugins array
	 */
	public function checkLock($plugins) {
		
	}
	
	public function addColumn($columns) {
		var_dump($columns);
		$columns['lock'] = __('Status');
		return $columns;
		
	}

	public function addColumnContent($column_name, $plugin_file, $plugin_data ) {
		if ($column_name=='lock') {
			echo '<span style="color:green;font-weight:bold;">ok</span>';
		}
	}

	function addPluginRowMeta($plugin_meta, $plugin_file, $plugin_data, $status) {
		$plugin_meta[] = '<span style="color:red;font-weight:bold;">test</span>';
		$plugin_meta[] = '<span style="color:green;font-weight:bold;">ok</span>';
		return $plugin_meta;
	}

	function addPluginAfter($plugin_file, $plugin_data, $status) {
		echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>hujnq</p></div></td></tr>';
		echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p>hujnq</p></div></td></tr>';
		echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-alt updated-message notice-success"><p>hujnq</p></div></td></tr>';
	}

	
}


//$all_plugins = apply_filters( 'all_plugins', get_plugins() );



// add_filter( 'plugin_action_links','trest',10, 4 );
function trest($actions, $plugin_file, $plugin_data, $context) {

	// is_plugin_active($plugin_file)
	
	if ($context=='all') {
		
		// check if plugin file exists in lock $plugin_file
		
		// yes
		
		// no 
			
			
		
	}
	
	// 	var_dump($actions, $plugin_file, $plugin_data, $context);
	
	return $actions;
}



//do_action( "in_plugin_update_message-{$file}", $plugin_data, $response );
/*

array(1) { 
	["deactivate"]=> string(182) "Deactivate" 
} 
string(21) "wp5-bang/wp5-bang.php" 
array(11) { 
	["Name"] => string(4) "Bang" 
	["PluginURI"]=> string(39) "https://github.com/boyanraichev/wp5-env" 
	["Version"]=> string(3) "1.0" 
	["Description"]=> string(39) "Wordress custom development boilerplate" 
	["Author"]=> string(13) "Boyan Raichev" 
	["AuthorURI"]=> string(32) "https://github.com/boyanraichev/" 
	["TextDomain"]=> string(8) "wp5-bang" 
	["DomainPath"]=> string(10) "/languages" 
	["Network"]=> bool(false) 
	["Title"]=> string(4) "Bang" 
	["AuthorName"]=> string(13) "Boyan Raichev" 
} 
string(3) "all" 

array(2) { 
	["activate"]=> string(226) "Activate" 
	["delete"]=> string(239) "Delete" 
} 
string(43) "bulglish-permalinks/bulglish-permalinks.php" 
array(20) { 
	["id"]=> string(33) "w.org/plugins/bulglish-permalinks" 
	["slug"]=> string(19) "bulglish-permalinks" 
	["plugin"]=> string(43) "bulglish-permalinks/bulglish-permalinks.php" 
	["new_version"]=> string(5) "1.4.2" 
	["url"]=> string(50) "https://wordpress.org/plugins/bulglish-permalinks/" 
	["package"]=> string(68) "https://downloads.wordpress.org/plugin/bulglish-permalinks.1.4.2.zip" 
	["icons"]=> array(1) { ["default"]=> string(70) "https://s.w.org/plugins/geopattern-icon/bulglish-permalinks_80c8ff.svg" } 
	["banners"]=> array(1) { ["1x"]=> string(74) "https://ps.w.org/bulglish-permalinks/assets/banner-772x250.png?rev=1012075" } 
	["banners_rtl"]=> array(0) { } 
	["Name"]=> string(19) "Bulglish permalinks" 
	["PluginURI"]=> string(55) "https://github.com/talkingaboutthis/bulglish-permalinks" 
	["Version"]=> string(5) "1.4.2" 
	["Description"]=> string(67) "This plugins transliterates cyrillic URL slugs to latin characters." 
	["Author"]=> string(13) "Boyan Raichev" 
	["AuthorURI"]=> string(27) "http://talkingaboutthis.eu/" 
	["TextDomain"]=> string(19) "bulglish-permalinks" 
	["DomainPath"]=> string(0) "" 
	["Network"]=> bool(false) 
	["Title"]=> string(19) "Bulglish permalinks" 
	["AuthorName"]=> string(13) "Boyan Raichev"
} 
string(3) "all"
	 
	
*/