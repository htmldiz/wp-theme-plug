<?php
class ThemeSettingsInitMetaboxes{
	function __construct(){
		if (is_admin()) {
			$config = array(
				'title' => 'Example',
				'pages' => array('post','page'),
			);
			$my_meta = new AT_Meta_Box($config);
			$my_meta->addPosts('posts',array('post_type' => 'post'),array('name'=> 'Brand','emptylabel'=>'Select brand'));
			$my_meta->Finish();
		}
	}
}
new ThemeSettingsInitMetaboxes();