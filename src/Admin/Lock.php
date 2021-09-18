<?php 
namespace Boyo\WPBang\Admin;


if (!defined('ABSPATH')) die;

class Lock {
	
	/** @var The single instance of the class */
	private static $_instance = null;
	
	private $plugins_lock = false;	
	
	// Don't load more than one instance of the class
	public static function instance() 
	{
		if ( null == self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}	
	
	// add hooks on plugins page
	public function __construct() 
	{
		add_action('init', [$this,'init'], 10 , 1);
	}
	
	public function init() 
	{	
		global $pagenow;
		
		if ( $pagenow == 'plugins.php' && current_user_can('install_plugins') ) { 
			
			// read saved lock file if any
			add_action('admin_init', [$this,'readLock'], 10 , 1);
			
			if (( ! is_multisite() || is_network_admin() )) {
				
				// add link to generate lock file
				add_action('pre_current_active_plugins', [$this,'addLink'], 10 , 1);
				
				// save new lock file if needed 
				add_action( 'admin_init', [$this,'saveLock'], 10, 1 );

			}
			
			if (is_multisite() && is_network_admin()) {
				
				add_filter( 'manage_plugins-network_columns', [$this,'addColumn'], 10, 1 );
				// add_action( 'manage_plugins_network_custom_column', [$this,'addColumnContent'], 10, 3);		
				add_action( 'manage_plugins_custom_column', [$this,'addColumnContent'], 10, 3);	
				
			}
			
			if (!is_multisite()) {
				
				add_filter( 'manage_plugins_columns', [$this,'addColumn'], 10, 1 );
				add_action( 'manage_plugins_custom_column', [$this,'addColumnContent'], 10, 3);	

			}
			
			add_filter( 'plugin_row_meta',[$this,'addPluginRowMeta'],10, 4 );
			add_action( 'after_plugin_row', [$this,'addPluginAfter'], 10 , 3);
		
		}
	}
	
	public function addLink(array $plugins) 
	{
		$url = is_multisite() ? network_admin_url('plugins.php?generate_lock=true') : admin_url('plugins.php?generate_lock=true');
		echo '<div><a href="'.$url.'">'.__('Generate lock file','wp5-bang').'</a></div>';
		
	}
	
	public function saveLock($all_plugins) 
	{
		
		if (!empty($_GET['generate_lock'])) {
			
			$plugins = get_plugins();
			
			$lock = [
				'about' => 'This is an automatically generated lock file of the currently installed WP plugins.',
				'date' => (new \DateTime())->format('Y-m-d H:i'),
				'timestamp' => time(),
				'plugins' => [],
			];
			
			foreach ($plugins as $key => $plugin) {
				
				$lock['plugins'][$key] = [
					'name' => $plugin['Name'],
					'version' => $plugin['Version'],
					'network' => $plugin['Network'],	
				];
				
			}
			
			$lockfile_path = PROJECT_DIR . '/plugins.lock';
			file_put_contents($lockfile_path, json_encode($lock,JSON_PRETTY_PRINT));
			
			if ( is_multisite() ) {
				
				add_action( 'network_admin_notices', [$this,'lockSavedNotice'] );
				
			} else {
				
				add_action( 'admin_notices', [$this,'lockSavedNotice'] );
				
			}
			
			
		}
	}
	
	public function lockSavedNotice() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Lock file generated!', 'wp5-bang' ); ?></p>
		</div>
		<?php
	}
	
	public function readLock() 
	{
		
		$lockfile_path = PROJECT_DIR . '/plugins.lock';
		if (file_exists($lockfile_path)) {
			
			$lockfile = file_get_contents($lockfile_path);
			$this->plugins_lock = json_decode($lockfile);
			
			$plugins = get_plugins();
			
			$this->missing = [];
			
			foreach($this->plugins_lock->plugins as $slug => $plugin) {
				
				if (!isset($plugins[$slug])) {
					$this->missing[] = $plugin;
				}
				
			} 
			
			if (!empty($this->missing)) {
				
				if ( is_multisite() ) {
					
					add_action( 'network_admin_notices', [$this,'missingPluginsNotice'] );
					
				} else {
					
					add_action( 'admin_notices', [$this,'missingPluginsNotice'] );
					
				}
				
			}
			
		}
		
	}
	
	public function missingPluginsNotice() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'The following plugins are missing on this installation:', 'wp5-bang' ); ?></p>
			<?php 
			foreach($this->missing as $missing) {
				?><div><?php echo "{$missing->name} ({$missing->version})"; ?></div><?php
			}
			?>
		</div>
		<?php
	}
	
	/*
	 * Read the lock file and compare with the plugins array
	 */
	public function checkLock(array $plugins) 
	{
		
	}
	
	public function addColumn(array $columns) : array
	{
		if ($this->plugins_lock) {
			$columns['lock'] = __('Lock Status', 'wp5-bang');
		}
		return $columns;
		
	}

	public function addColumnContent($column_name, $plugin_file, $plugin_data ) 
	{
		if ($this->plugins_lock && $column_name=='lock') {
			
			if ( isset($this->plugins_lock->plugins->{$plugin_file}) && $this->plugins_lock->plugins->{$plugin_file}->version == $plugin_data['Version']) {
				
				echo '<span class="dashicons dashicons-saved"></span>';
				
			} else {
				
				echo '<span class="dashicons dashicons-warning"></span>';
				
			}
			
			
		}
	}

	function addPluginRowMeta($plugin_meta, $plugin_file, $plugin_data, $status) 
	{
		if ($this->plugins_lock && isset($this->plugins_lock->plugins->{$plugin_file}) ) {
			
			$plugin_meta[] = '<span>'.__('Recommended version:','wp5-bang').' '. $this->plugins_lock->plugins->{$plugin_file}->version.'</span>';
			
		}
		return $plugin_meta;
	}

	function addPluginAfter($plugin_file, $plugin_data, $status) 
	{
		// status - 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', 'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'
		
		if ($this->plugins_lock) {
			
			// plugin should be deleted 
			if (!isset($this->plugins_lock->plugins->{$plugin_file})) {
				
				echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p>'.__('Site administrator: Removal recommended','wp5-bang').'</p></div></td></tr>';
				
			} else {
				
				if (isset($plugin_data['new_version']) && $plugin_data['new_version'] != $plugin_data['Version']) {
					
					if ( $plugin_data['new_version'] != $this->plugins_lock->plugins->{$plugin_file}->version) {
						
						echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>'.__('Site administrator: This update has not been tested','wp5-bang').'</p></div></td></tr>';
						$version_alert = true;
						
					} else { 
						
						echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-alt updated-message notice-success"><p>'.__('Site administrator: Please update now','wp5-bang').'</p></div></td></tr>';
						$version_alert = true;
						
					}
					
				} 
					
				if (!isset($version_alert) && $plugin_data['Version'] != $this->plugins_lock->plugins->{$plugin_file}->version) {
					
					echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>'.__('Site administrator: A different version of this plugin is recommended','wp5-bang').'</p></div></td></tr>';
					
				}
				
					
				if (is_network_admin()) {
					
					if ($plugin_data['Network'] && !$this->plugins_lock->plugins->{$plugin_file}->network) {
						
						echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>'.__('Site administrator: Please deactivate for network and activate on required sites only.','wp5-bang').'</p></div></td></tr>';
						
					} else if (!$plugin_data['Network'] && $this->plugins_lock->plugins->{$plugin_file}->network) {
						
						echo '<tr class="plugin-update-tr"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>'.__('Site administrator: Please activate for network','wp5-bang').'</p></div></td></tr>';
						
					}
				}
				
			}
			
		}
		
	}

	
}

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
