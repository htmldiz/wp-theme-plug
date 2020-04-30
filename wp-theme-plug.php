<?php 
/*
Plugin Name: WP theme plug
Plugin URI: 
Description: 
Version: 1.0
Author: Markus {code}
Author URI:
*/
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
require_once("lib/meta-box-class/my-meta-box-class.php");
require_once("lib/tax-meta-class/Tax-meta-class.php");
require_once("lib/CPT.php");
require_once("lib/gallery/gallery.php");
class ThemeSettingsCL
{
	
	function __construct()
	{
		
	}
}
new ThemeSettingsCL();