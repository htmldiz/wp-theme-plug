<?php
class ThemeSettingsRequests{
	public function __construct()
	{
		add_filter('remove_last_shash_in_url', array($this,'remove_last_shash_in_url'), 10, 1);
//		add_filter('term_link', array($this,'term_link'), 10, 3);
//		add_filter('post_link', array($this,'post_link'), 10, 3);
//		add_filter('post_type_link', array($this,'post_type_link'), 10, 2);
//		add_filter( 'request', array($this,'request'),10,1 );
	}
	function remove_last_shash_in_url( $url ){
		if(substr($url, -1) == '/') {
			$url = substr($url, 0, -1);
		}
		return $url;
	}
	function post_link( $url, $post ){
		return $url;
	}
	function post_type_link( $url, $post ){
		return $url;
	}
	function request($query){
		return $query;
	}
	function term_link_parents($slug, $term){
		if(!empty($term->parent)){
			$parent_term = get_term($term->parent);
			$slug = $this->term_link_parents($slug, $parent_term);
		}
		$slug = $slug."/".$term->slug;
		return $slug;
	}
	function term_link($url, $term, $taxonomy){
		return $url;
	}
}
new ThemeSettingsRequests();