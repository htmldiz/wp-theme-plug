<?php
class ThemeSettingsAjax{
	function __construct(){
		add_action( 'wp_ajax_search_post_by_type', array($this,'search_post_by_type') );
		add_action( 'wp_ajax_nopriv_search_post_by_type', array($this,'search_post_by_type') );
	}
	function search_post_by_type() {
		$searsh = $_GET['q'];
		$post_type = $_GET['post_type'];
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => $post_type,
			's'	               => $searsh,
			'post_status'      => 'publish',
			'suppress_filters' => true,
			'fields'           => 'ids',
		);
		$indexs = get_posts( $args );
		$array_results = array('results'=>array());
		if(count($indexs)){
			foreach ($indexs as $index_id) {
				$array_results['results'][] = array(
					'id'   => $index_id,
					'name' => get_the_title($index_id),
				);
			}
		}
		echo json_encode($array_results);
		exit();
	}
}
new ThemeSettingsAjax();