<?php 
	
use Boyo\WPBang\Config;
use Boyo\WPBang\Init;

if (! function_exists('bang')) {
	
	function bang() {
		
		$bang = Init::instance();
		
		return $bang;
		
	}
	
}

if (! function_exists('config')) {
	
	function config($key) {
		
		$config = Config::instance();
		
		return $config->get($key);
		
	}
	
}

if (! function_exists('template')) {
	
	function template(string $name) {

		$template = [ $name.'.php' ];
		
		locate_template($template, true, false);
		
	}
	
}

if (! function_exists('asset')) {
	
	function asset(string $name) {
		
		return get_stylesheet_directory_uri() . '/assets';
		
	}
	
}