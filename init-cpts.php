<?php
if(!class_exists('CPT')){
	return;
}
class ThemeSettingsInitCpts{
	function __construct(){
		$video = new CPT(array(
			'post_type_name' => 'example',
			'singular' => 'Example',
			'plural' => 'Examples',
			'slug' => 'example'
		), array(
			'supports' => array('title', 'editor', 'thumbnail')
		));
		$video->register_taxonomy(array(
			'taxonomy_name' => 'exampletax',
			'singular' => 'Example taxonomy',
			'plural' => 'Example taxonomies',
			'slug' => 'exampletax'
		));
		$video->menu_icon("dashicons-format-video");
	}
}
new ThemeSettingsInitCpts();