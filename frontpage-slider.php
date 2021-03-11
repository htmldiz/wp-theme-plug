<?php 
class ThemeSlider
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		if( is_admin() ){
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
			add_action( 'wp_ajax_save_front_slider', array( $this, 'save_front_slider' ) );
		}
		add_filter( 'get_frontpageslider', array($this,'get_frontpageslider'), 10, 1 );
	}
	function get_frontpageslider($options){
		$options = get_option( 'frontpageslider' );
		if(empty($options)){
			$options = array();
		}
		return $options;
	}
	function save_front_slider(){
		$slides = $_POST['slide'];
		if(!empty($slides)){
			$slides_array = array($slides);
			$i = 0;
			foreach ($slides as $slide) {
				$slides_array[$i] = $slide;
				$i++;
			}
			update_option( 'frontpageslider', $slides_array );
		}
	}

	public function add_plugin_page()
	{
		add_options_page(
			'Slider', 
			'Slider', 
			'manage_options', 
			'frontpageslider', 
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		$this->options = get_option( 'frontpageslider' );
		if(empty($this->options)){
			$this->options = array();
		}
		wp_enqueue_media();
		wp_enqueue_script('jquery-ui-sortable');
		?>
		<style>@-webkit-keyframes spin {0% { -webkit-transform: rotate(0deg); }100% { -webkit-transform: rotate(360deg); }}@keyframes spin {0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }}.image-element{display:block;text-align:center;margin-bottom:20px}.button.button-danger{color:#c00!important}.frontpageslider li .slide-elements{display:none}.frontpageslider li.active .slide-elements{display:block}.frontpageslider li .actions{margin-left:auto}[dir="rtl"] .frontpageslider li .actions{margin-right:auto;margin-left:0px}.frontpageslider li img{max-width:100%}.frontpageslider li .slide-elements{-webkit-box-flex:0;-webkit-flex:0 0 100%;-ms-flex:0 0 100%;flex:0 0 100%;max-width:100%}.frontpageslider li{margin:0;padding:10px 25px;background-color:#fff;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-webkit-align-items:center;-ms-flex-align:center;align-items:center;-webkit-flex-wrap:wrap;-ms-flex-wrap:wrap;flex-wrap:wrap;box-shadow:0 1px 1px rgba(0,0,0,.04);margin-top:10px;border:1px solid #e5e5e5}.frontpageslider input,.frontpageslider textarea{width:100%}.frontpageslider textarea{height:250px}.frontpageslider label,.frontpageslider .text,.frontpageslider .title{display:block;margin-bottom:10px}.frontpageslider{list-style:none;margin:0;padding:0}.toggle-buttons .button{display:none}.toggle-buttons .button.active{display:inline-block;box-shadow:unset}.frontpageslider h5{margin-bottom:5px}.load:after {content: "";display: inline-block;background: url(/wp-includes/images/admin-bar-sprite-2x.png);-webkit-animation: spin 2s linear infinite;animation: spin 2s linear infinite;background-position: 0px 47px;width: 15px;height: 15px;background-size: 100%;margin-top: 2px;margin-bottom: -3px;margin-right: 5px;margin-left: 5px;}</style>
		<div class="wrap">
			<h1>Slider</h1>
			<a href="#" class="button button-primary button-add">Add slide</a>
			<a href="#" class="button button-primary button-save">Save slide</a>
			<ul class="frontpageslider"></ul>
		</div>
		<script type="text/html" id="slider-item">
			<li data-slideitemid="slidernum-{slidernum}">
				<h2 class="title-slide">Title of slide</h2>
				<span class="actions">
					<a href="#" class="button edit-slide button-primary" toggle-text="Save">Edit</a>
					<span class="toggle-buttons">
						<a href="#" class="button remove-slide button-danger active">Remove</a>
						<a href="#" class="button cancel-slide">Cancel</a>
					</span>
				</span>
				<span class="slide-elements">
					<span class="title">
						<label>
							<h5>Title of slide</h5>
							<input type="text" data-valid="title" name="slide[{slidernum}][title]" placeholder="Title of slide" />
						</label>
					</span>
					<span class="title">
						<label>
							<h5>Title of slide down</h5>
							<input type="text" data-valid="titlesmall" name="slide[{slidernum}][titlesmall]" placeholder="Title of slide down" />
						</label>
					</span>
					<span class="title">
						<label>
							<h5>Button 1</h5>
							<input type="text" data-valid="button_link_url_1" name="slide[{slidernum}][button_link_url_1]" placeholder="Button link url" />
							<input type="text" data-valid="button_link_text_1" name="slide[{slidernum}][button_link_text_1]" placeholder="Button link text" />
						</label>
					</span>
					<span class="title">
						<label>
							<h5>Button 2</h5>
							<input type="text" data-valid="button_link_url_2" name="slide[{slidernum}][button_link_url_2]" placeholder="Button link url" />
							<input type="text" data-valid="button_link_text_2" name="slide[{slidernum}][button_link_text_2]" placeholder="Button link text" />
						</label>
					</span>
					<span class="text">
						<label>
							<h5>Text</h5>
							<textarea data-valid="text" name="slide[{slidernum}][text]"></textarea>
						</label>
					</span>
					<span class="full-image-element image-element">
						<img src="/wp-content/plugins/cpt-proj/images/slide_1.jpg" class="full-image">
						<div class="image-controls">
							<a href="#" class="button button-edit-fullimage button-primary">Edit full image</a>
							<input type="hidden" data-valid="fullimage" name="slide[{slidernum}][fullimage]" class="image-selection" />
						</div>
					</span>
				</span>
			</li>
		</script>
		<script>
			jQuery(function($) {
				var slider = <?php echo json_encode( $this->options ); ?>;
				function getRndInteger(min, max) {
					return Math.floor(Math.random() * (max - min) ) + min;
				}
				function init_sort(){
					jQuery('.frontpageslider').sortable({
						stop: function( event, ui ) {
							save_slider();
						}
					});
				}
				function init_slider_items(){
					$.each(slider,function(index, el) {
						var item = $('#slider-item').html();
						item = item.replace(new RegExp('{slidernum}','g'),index);
						$('.frontpageslider').append(item);
						$.each(el,function(ind, elem) {
							$('body').find('[data-slideitemid="slidernum-'+index+'"]').find('[data-valid="'+ind+'"]').attr('value',elem);
						});
						var title = $('body').find('[data-slideitemid="slidernum-'+index+'"]').find('[data-valid="title"]').val();
						var fullimage = $('body').find('[data-slideitemid="slidernum-'+index+'"]').find('[data-valid="fullimage"]').val();
						if(title.trim() != ''){
							$('body').find('[data-slideitemid="slidernum-'+index+'"] .title-slide').html(title);
						}
						if(fullimage.trim() != ''){
							$('body').find('[data-slideitemid="slidernum-'+index+'"] .full-image-element img').attr('src',fullimage);
						}
					});
				}
				init_slider_items();
				function save_slider(){
					var data = $('body').find('.frontpageslider input, .frontpageslider select, .frontpageslider textarea').serialize();
						data = data+"&action=save_front_slider";
						$('.button-save, .edit-slide').addClass('load');
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							dataType: 'json',
							data: data,
						}).done(function(){
							$('.load').removeClass('load');
						});
				}
				var element_image_for;
				frame = wp.media({
					title: 'Select or upload media of slider',
					button: {
						text: 'Select image for slider'
					},
					multiple: false
				});
				frame.on( 'select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					element_image_for.find('.image-selection').val( attachment.url );
					element_image_for.find('img').attr('src',attachment.url);
				});
				$('body').on('click', '.button-edit-fullimage', function(event) {
					element_image_for = $(this).closest('.image-element');
					frame.open();
					return false;
				});
				$('body').on('input change keyup keydown', '[data-valid="title"]', function(event) {
					var text = $(this).val();
					$(this).closest('li').find('.title-slide').html(text);
				});
				$('body').on('click', '.button-save', function(event) {
					save_slider();
					return false;
				});
				$('body').on('click', '.button-add', function(event) {
					var item = $('#slider-item').html();
					var rand_id = getRndInteger($('.frontpageslider li').length, 1000000);
					item = item.replace(new RegExp('{slidernum}','g'),rand_id);
					$('.frontpageslider').append(item);
					init_sort();
					save_slider();
					return false;
				});
				$('body').on('click', '.remove-slide', function(event) {
					$(this).closest('li').remove();
					save_slider();
					init_sort();
					return false;
				});
				$('body').on('click', '.cancel-slide', function(event) {
					$(this).closest('li').find('.toggle-buttons .button').toggleClass('active');
					$(this).closest('li').toggleClass('active');
					var old_text_elenemt = $(this).closest('li').find('.edit-slide');
					var old_text = old_text_elenemt.text();
					old_text_elenemt.text(old_text_elenemt.text() == old_text_elenemt.attr('toggle-text') ? old_text : old_text_elenemt.attr('toggle-text'));
					old_text_elenemt.attr('toggle-text',old_text);
					return false;
				});
				$('body').on('click', '.edit-slide', function(event) {
					if($(this).closest('li').hasClass('active')){
						save_slider();
					}
					$(this).closest('li').find('.toggle-buttons .button').toggleClass('active');
					$(this).closest('li').toggleClass('active');
					var old_text = $(this).text();
					$(this).text($(this).text() == $(this).attr('toggle-text') ? old_text : $(this).attr('toggle-text'));
					$(this).attr('toggle-text',old_text);
					return false;
				});
				init_sort();
			});
		</script>
	<?php
	}

	public function page_init()
	{
	}

}

$my_settings_page = new ThemeSlider();