<?php
 /**
  * Gallery_meta_theme_pl
  */
 class Gallery_meta_theme_pl
 {
	private $types;
	private $page_template;
	function __construct( $props )
	{
		if(!$props){
			return;
		}
		$defaultprops = array(
			'post_types'    => array(),
			'page_template' => array()
		);
		$props = array_merge($defaultprops,$props);
		if(!is_array($props['post_types'])){
			$props['post_types'] = array($props['post_types']);
		}
		if(!is_array($props['page_template'])){
			$props['page_template'] = array($props['page_template']);
			foreach ($props['page_template'] as $key => $page_templatename) {
				$page_template = str_replace('.php','',$page_templatename);
				$page_template = $page_template.'.php';
				$props['page_template'][$key] = $page_template;
			}
		}
		$this->types         = $props['post_types'];
		$this->page_template = $props['page_template'];
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes'       , array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post'            , array( $this, 'save_post' ) );
	}
	function admin_enqueue_scripts($hook) {
		if ( 'post.php' == $hook || 'post-new.php' == $hook ) {
			wp_enqueue_script('gallery-metabox', plugins_url('/js/gallery-metabox.js',__FILE__), array('jquery', 'jquery-ui-sortable'));
			wp_enqueue_style('gallery-metabox', plugins_url('/css/gallery-metabox.css',__FILE__));
		}
	}
	function add_meta_boxes($post_type,$post) {
		$type_default = array();
		if(is_array($this->types) && !empty($this->types)){
			$type_default = array_merge( $this->types, $type_default );
		}
		if (!empty($type_default)) {
			if (in_array($post_type, $type_default)) {
				$display_metabox = false;
				$post_id = $post->ID;
				if(!empty($this->page_template)){
					if(in_array(get_page_template_slug($post_id),$this->page_template)){
						$display_metabox = true;
					}
					$page_on_front = get_option('page_on_front',0);
					if(in_array('front-page.php',$this->page_template)){
						if((int)$page_on_front == (int)$post_id){
							$display_metabox = true;
						}
					}
				}else{
					$display_metabox = true;
				}
				if($display_metabox == true){
					add_meta_box(
						'gallery-metabox',
						'Gallery',
						array($this,'gallery_meta_callback'),
						$post_type,
						'normal',
						'high'
					);
				}
			}
		}
	}
	function gallery_meta_callback($post) {
		wp_nonce_field( basename(__FILE__), 'gallery_meta_nonce' );
		$ids = get_post_meta($post->ID, 'gallery', true); ?>
		<table class="form-table">
			<tr>
				<td>
					<a class="gallery-add button" href="#" data-uploader-title="Добавить изображение" data-uploader-button-text="Добавить изображение">Добавить изображение</a>
					<ul id="gallery-metabox-list">
						<?php if ($ids): ?>
							<?php  foreach ($ids as $key => $value) : $image = wp_get_attachment_image_src($value); ?>
							<li>
								<input type="hidden" name="gallery[<?php echo $key; ?>]" value="<?php echo $value; ?>">
								<img class="image-preview" src="<?php echo $image[0]; ?>">
								<a class="change-image button button-small" href="#" data-uploader-title="Изменить" data-uploader-button-text="Изменить">Изменить</a><br>
								<small><a class="remove-image" href="#">Удалить</a></small>
							</li>
						<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</td>
			</tr>
		</table>
	<?php }

	function save_post($post_id) {
		if (!isset($_POST['gallery_meta_nonce']) || !wp_verify_nonce($_POST['gallery_meta_nonce'], basename(__FILE__))) return;
		if (!current_user_can('edit_post', $post_id)) return;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if(isset($_POST['gallery'])) {
			update_post_meta($post_id, 'gallery', $_POST['gallery']);
		} else {
			delete_post_meta($post_id, 'gallery');
		}
	}
 }
