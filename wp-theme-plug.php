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
//require_once("shortcode-generator.php");
//require_once("frontpage-slider.php");
//require_once("requests.php");
require_once("lib/tgm-plugin-activation/example.php");
require_once("lib/theme-options-class/theme-options-class.php");
require_once("lib/meta-box-class/my-meta-box-class.php");
require_once("lib/tax-meta-class/Tax-meta-class.php");
require_once("lib/CPT.php");
require_once("lib/gallery/gallery.php");
require_once("init-cpts.php");
require_once("init-metaboxes.php");
require_once("ajax.php");
class ThemeSettingsCL
{
	public $options = array();
	function __construct()
	{
		add_filter('display_post_states', array( $this,'display_post_states' ),10,2);
		add_filter( 'get_theme_option_cst', array($this, 'get_theme_option_cst'), 10, 2 );
		if(is_admin()){
			$config = array(
				'id' => 'theme_settings',
				'title' => 'Theme settings',
				'fields' => array(),
				'local_images' => false,
				'use_with_theme' => false
			);
			$ThemeSettingsPage =  new AT_ThemeSettings($config);
			$ThemeSettingsPage->addText('currency',array('name'=> __('Currency sumbol')));
			$ThemeSettingsPage->addSelect('display_price_side',array(
				'left'=>'left',
				'right'=>'Right',
				'left_space'=>'Left with space',
				'right_space'=>'Right with space'
			),array('name'=> 'Currency sumbol position', 'std'=> array('left'),'multiple'=>true));
			$ThemeSettingsPage->addSelect('display_price_side',array(
				'left'=>'left',
				'right'=>'Right',
				'left_space'=>'Left with space',
				'right_space'=>'Right with space'
			),array('name'=> 'Currency sumbol position', 'std'=> array('left')));
			$ThemeSettingsPage->Finish();
		}
	}
	function get_theme_option_cst($return = "", $option_name = ""){
		$this->options[$option_name] = get_option( $option_name, $return );
		$this->options[$option_name] = apply_filters( 'get_default_theme_option', $this->options[$option_name] );
		return $this->options[$option_name];
	}
	function display_post_states( $states, $post ) {
		$templatename = get_page_template_slug( $post->ID );
		if ( strpos($templatename,'template-') !== false ) {
			$templateSlug = str_replace('template-','',$templatename);
			$templateSlug = str_replace('.php','',$templateSlug);
			$states[] = sprintf(__('Main %s'),$templateSlug);
		}
		return $states;
	}
}
new ThemeSettingsCL();