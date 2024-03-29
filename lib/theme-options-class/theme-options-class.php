<?php
if ( !defined( 'ABSPATH' ) ) {
    die( 'Direct access is forbidden.' );
}
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
if ( ! class_exists( 'AT_ThemeSettings') ) :

    /**
     * All Types Meta Box class.
     *
     * @package All Types Meta Box
     * @since 1.0
     *
     * @todo Nothing.
     */
    class AT_ThemeSettings {

        /**
         * Holds meta box object
         *
         * @var object
         * @access protected
         */
        protected $settings_boxes;
        protected $_meta_box;

        /**
         * Holds meta box fields.
         *
         * @var array
         * @access protected
         */
        protected $_prefix;

        /**
         * Holds Prefix for meta box fields.
         *
         * @var array
         * @access protected
         */
        protected $_fields;

        /**
         * Use local images.
         *
         * @var bool
         * @access protected
         */
        protected $_Local_images;

        /**
         * SelfPath to allow themes as well as plugins.
         *
         * @var string
         * @access protected
         * @since 1.6
         */
        protected $SelfPath;

        /**
         * $field_types  holds used field types
         * @var array
         * @access public
         * @since 2.9.7
         */
        public $field_types = array();

        /**
         * $inGroup  holds groupping boolean
         * @var boolean
         * @access public
         * @since 2.9.8
         */
        public $inGroup = false;

        /**
         * Constructor
         *
         * @since 1.0
         * @access public
         *
         * @param array $meta_box
         */
        public function __construct ( $meta_boxes ) {

	        $config = array(
		        'title'          => 'Demo box',
		        'pages'          => array(),
		        'context'        => 'normal',
		        'fields'         => array(),
		        'local_images'   => false,
		        'use_with_theme' => false
	        );
	        $meta_box = array_merge( $config, $meta_boxes );
            // If we are not in admin area exit.
            if ( ! is_admin() )
                return;
            add_action( 'add_metabox_theme_settings', array( $this, 'add_metabox_theme_settings' ),10,5 );
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            //load translation
            add_filter('init', array($this,'load_textdomain'));
            $dirname = 'theme-options-class';
            // Assign meta box values to local variables and add it's missed values.
            $this->_meta_box = $meta_box;
            $this->_prefix = (isset($meta_box['prefix'])) ? $meta_box['prefix'] : '';
            $this->_fields = $this->_meta_box['fields'];
            $this->_Local_images = (isset($meta_box['local_images'])) ? true : false;
            $this->add_missed_values();
            if (isset($meta_box['use_with_theme']))
                if ($meta_box['use_with_theme'] === true){
                    $this->SelfPath = get_stylesheet_directory_uri() . '/'.$dirname;
                }elseif($meta_box['use_with_theme'] === false){
                    $this->SelfPath = plugins_url( $dirname, plugin_basename( dirname( __FILE__ ) ) );
                }else{
                    $this->SelfPath = $meta_box['use_with_theme'];
                }
            else{
                $this->SelfPath = plugins_url( $dirname, plugin_basename( dirname( __FILE__ ) ) );
            }

            // Add metaboxes
            add_action( 'add_theme_options_boxes', array( $this, 'add' ), 10, 2 );
            //add_action( 'wp_insert_post', array( $this, 'save' ) );
            add_action( 'save_post_theme_options', array( $this, 'save' ) );
            // Load common js, css files
            // Must enqueue for all pages as we need js for the media upload, too.
            add_action( 'admin_print_styles', array( $this, 'load_scripts_styles' ) );
            //limit File type at upload
            add_filter('wp_handle_upload_prefilter', array($this,'Validate_upload_file_type'));
			add_action( 'wp_ajax_update_theme_options', array($this,'update_theme_options') );
        }
		function update_theme_options(){
        	$come_data = $_POST;
        	if(isset($come_data['_wp_http_referer'])){
        		unset($come_data['_wp_http_referer']);
			}
        	if(isset($come_data['at_meta_box_nonce'])){
        		unset($come_data['at_meta_box_nonce']);
			}
			foreach ($come_data as $key => $value){
				update_option($key,stripslashes($value));
			}
		}
        /**
         * add theme options page
         *
         * @since 1.0
         * @access public
         */

        public function add_plugin_page() {
            add_options_page(
                $this->_meta_box['title'],
                $this->_meta_box['title'],
                'manage_options',
                $this->_meta_box['id'],
                array( $this, 'display_admin_page' )
            );
        }

        public function display_fields(){
            foreach ($this->settings_boxes as $settings_boxe){
//                var_dump($settings_boxe['callback']);
                call_user_func_array($settings_boxe['callback'],array());
            }
        }
        public function display_admin_page()
        {
            $this->options = get_option( 'themesettings' );
            wp_enqueue_media();
            echo '<div class="theme-options-ont">';
            echo '<h1>'.$this->_meta_box['title'].'</h1>';
            echo '<form method="post" action="options.php"  enctype="multipart/form-data" encoding="multipart/form-data">';
            do_action('add_theme_options_boxes');
            $this->display_fields();
            submit_button("Submit");
            echo '</form>';
            echo '</div>';
        }
        /**
         * Load all Javascript and CSS
         *
         * @since 1.0
         * @access public
         */

        public function load_scripts_styles() {

            // Get Plugin Path
            $plugin_path = $this->SelfPath;


            //only load styles and js when needed
            /*
             * since 1.8
             */
            global $typenow;
            if ($this->is_edit_page()){
                // Enqueue Meta Box Style
                wp_enqueue_style( 'at-theme-settings', $plugin_path . '/css/meta-box.css' );

                // Enqueue Meta Box Scripts
                wp_enqueue_script( 'at-sweetalert2-theme', '//cdn.jsdelivr.net/npm/sweetalert2@9', array( 'jquery' ), null, true );
                wp_enqueue_script( 'at-theme-settings', $plugin_path . '/js/theme-settings.js', array( 'jquery' ), null, true );

                // Make upload feature work event when custom post type doesn't support 'editor'
                if ($this->has_field('image') || $this->has_field('file')){
                    wp_enqueue_script( 'media-upload' );
                    add_thickbox();
                    wp_enqueue_script( 'jquery-ui-core' );
                    wp_enqueue_script( 'jquery-ui-sortable' );
                }
                // Check for special fields and add needed actions for them.

                //this replaces the ugly check fields methods calls
                foreach (array('upload','color','date','time','code','select','posts') as $type) {
                    call_user_func ( array( $this, 'check_field_' . $type ));
                }
            }

        }

        /**
         * Check the Field select, Add needed Actions
         *
         * @since 2.9.8
         * @access public
         */
        public function check_field_select() {

            // Check if the field is an image or file. If not, return.
            if ( ! $this->has_field( 'select' ))
                return;
            $plugin_path = $this->SelfPath;
            // Enqueu JQuery UI, use proper version.

            // Enqueu JQuery select2 library, use proper version.
	        wp_enqueue_style('at-multiselect-select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css', array(), null);
	        wp_enqueue_script('at-multiselect-select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', array('jquery'), false, true);
        }
        public function check_field_posts() {

            // Check if the field is an image or file. If not, return.
            if ( ! $this->has_field( 'posts' ))
                return;
            $plugin_path = $this->SelfPath;
            // Enqueu JQuery UI, use proper version.

            // Enqueu JQuery select2 library, use proper version.
	        wp_enqueue_style('at-multiselect-select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css', array(), null);
	        wp_enqueue_script('at-multiselect-select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', array('jquery'), false, true);
        }

        /**
         * Check the Field Upload, Add needed Actions
         *
         * @since 1.0
         * @access public
         */
        public function check_field_upload() {

            // Check if the field is an image or file. If not, return.
            if ( ! $this->has_field( 'image' ) && ! $this->has_field( 'file' ) )
                return;

            // Add data encoding type for file uploading.
//            add_action( 'post_edit_form_tag', array( $this, 'add_enctype' ) );

        }

        /**
         * Add data encoding type for file uploading
         *
         * @since 1.0
         * @access public
         */
        public function add_enctype () {
            printf(' enctype="multipart/form-data" encoding="multipart/form-data" ');
        }

        /**
         * Check Field Color
         *
         * @since 1.0
         * @access public
         */
        public function check_field_color() {

            if ( $this->has_field( 'color' ) && $this->is_edit_page() ) {
                wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_script( 'wp-color-picker' );
            }
        }

        /**
         * Check Field Date
         *
         * @since 1.0
         * @access public
         */
        public function check_field_date() {

            if ( $this->has_field( 'date' ) && $this->is_edit_page() ) {
                // Enqueu JQuery UI, use proper version.
                $plugin_path = $this->SelfPath;
                wp_enqueue_style( 'at-jquery-ui-css', $plugin_path .'/js/jquery-ui/jquery-ui.css' );
                wp_enqueue_script( 'jquery-ui');
                wp_enqueue_script( 'jquery-ui-datepicker');
            }
        }

        /**
         * Check Field Time
         *
         * @since 1.0
         * @access public
         */
        public function check_field_time() {

            if ( $this->has_field( 'time' ) && $this->is_edit_page() ) {
                $plugin_path = $this->SelfPath;
                // Enqueu JQuery UI, use proper version.
                wp_enqueue_style( 'at-jquery-ui-css', $plugin_path .'/js/jquery-ui/jquery-ui.css' );
                wp_enqueue_script( 'jquery-ui');
                wp_enqueue_script( 'at-timepicker', $plugin_path .'/js/jquery-ui/jquery-ui-timepicker-addon.js', array( 'jquery-ui-slider','jquery-ui-datepicker' ),false,true );
            }
        }

        /**
         * Check Field code editor
         *
         * @since 2.1
         * @access public
         */
        public function check_field_code() {

            if ( $this->has_field( 'code' ) && $this->is_edit_page() ) {
                $plugin_path = $this->SelfPath;
                // Enqueu codemirror js and css
                wp_enqueue_style( 'at-code-css', $plugin_path .'/js/codemirror/codemirror.css',array(),null);
                wp_enqueue_style( 'at-code-css-dark', $plugin_path .'/js/codemirror/solarizedDark.css',array(),null);
                wp_enqueue_style( 'at-code-css-light', $plugin_path .'/js/codemirror/solarizedLight.css',array(),null);
                wp_enqueue_script('at-code-js',$plugin_path .'/js/codemirror/codemirror.js',array('jquery'),false,true);
                wp_enqueue_script('at-code-js-xml',$plugin_path .'/js/codemirror/xml.js',array('jquery'),false,true);
                wp_enqueue_script('at-code-js-javascript',$plugin_path .'/js/codemirror/javascript.js',array('jquery'),false,true);
                wp_enqueue_script('at-code-js-css',$plugin_path .'/js/codemirror/css.js',array('jquery'),false,true);
                wp_enqueue_script('at-code-js-clike',$plugin_path .'/js/codemirror/clike.js',array('jquery'),false,true);
                wp_enqueue_script('at-code-js-php',$plugin_path .'/js/codemirror/php.js',array('jquery'),false,true);

            }
        }
        public function add_metabox_theme_settings($id,$title,$callback,$context,$priority){
            if($this->settings_boxes === null){
                $this->settings_boxes = array();
            }
            if(!isset($this->settings_boxes[$id])){
                $this->settings_boxes[$id] = array(
                    'id' => $id,
                    'title' => $title,
                    'callback' => $callback,
                    'context' => $context,
                    'priority' => $priority
                );
            }
        }
        /**
         * Add Meta Box for multiple post types.
         *
         * @since 1.0
         * @access public
         */
        public function add() {
            $display = true;
            if($display){
                do_action('add_metabox_theme_settings', $this->_meta_box['id'], $this->_meta_box['title'], array( $this, 'show' ), $this->_meta_box['context'], $this->_meta_box['priority'] );
            }
        }

        /**
         * Callback function to show fields in meta box.
         *
         * @since 1.0
         * @access public
         */
        public function show() {
            $this->inGroup = false;
            $display = true;
            if($display){
                wp_nonce_field( basename(__FILE__), 'at_meta_box_nonce' );
                echo '<div class="form-table">';
//                var_dump($this->_fields);
                foreach ( $this->_fields as $field ) {
                    $field['multiple'] = isset($field['multiple']) ? $field['multiple'] : false;
                    $meta = get_option(  $field['id'] );
                    $meta = ( $meta !== '' ) ? $meta : @$field['std'];

                    if (!in_array($field['type'], array('image', 'repeater','file')))
                        $meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );

                    if ($this->inGroup !== true)
                        $dashname = sanitize_title_with_dashes($field["name"]);
                    echo '<div class="tr-'.$dashname;
                    if (isset($field['group']) && $field['group'] == 'start'){
                        echo " form-table-gr";
                    }
                    echo '">';

                    if (isset($field['group']) && $field['group'] == 'start'){
                        $this->inGroup = true;
                        echo '<div class="group-form-table"><div class="form-table">';
                        $dashname = sanitize_title_with_dashes($field["name"]);
                        echo '<div class="tr-'.$dashname.' ">';
                    }

                    // Call Separated methods for displaying each type of field.
                    call_user_func ( array( $this, 'show_field_' . $field['type'] ), $field, $meta );

                    if ($this->inGroup === true){
                        if(isset($field['group']) && $field['group'] == 'end'){
                            echo '</div></div></div></div>';
                            $this->inGroup = false;
                        }
                    }else{
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
        }

        /**
         * Show Repeater Fields.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_repeater( $field, $meta ) {
            global $post;
            // Get Plugin Path
            $plugin_path = $this->SelfPath;
            $this->show_field_begin( $field, $meta );
            $class = '';
            if ($field['sortable'])
                $class = " repeater-sortable";
            echo "<div class='at-repeat".$class."' id='{$field['id']}'>";

            $c = 0;
            $meta = get_option($field['id']);

            if (is_array($meta) && count($meta) > 0 ){
                foreach ($meta as $me){
                    //for labling toggles
                    $mmm =  isset($me[$field['fields'][0]['id']])? $me[$field['fields'][0]['id']]: "";
                    if ( in_array( $field['fields'][0]['type'], array('image','file') ) )
                        $mmm = $c +1 ;
                    if($field['fields'][0]['type'] == 'posts'){
                        $mmm = get_the_title( $mmm );
                    }
                    echo '<div class="at-repater-block"><span class="input-info">'.$mmm.'</span><br/><div class="repeater-table" style="display: none;">';
                    if ($field['inline']){
                        echo '<div class="at-inline" VALIGN="top">';
                    }
                    foreach ($field['fields'] as $f){
                        //reset var $id for repeater
                        $id = '';
                        $id = $field['id'].'['.$c.']['.$f['id'].']';
                        $m = isset($me[$f['id']]) ? $me[$f['id']]: '';
                        $m = ( $m !== '' ) ? $m : $f['std'];
                        if ('image' != $f['type'] && $f['type'] != 'repeater')
                            $m = is_array( $m) ? array_map( 'esc_attr', $m ) : esc_attr( $m);
                        //set new id for field in array format
                        $f['id'] = $id;
                        if (!$field['inline']){
                            echo '<div>';
                        }
                        call_user_func ( array( $this, 'show_field_' . $f['type'] ), $f, $m);
                        if (!$field['inline']){
                            echo '</div>';
                        }
                    }
                    if ($field['inline']){
                        echo '</div>';
                    }
                    echo '</div>';
                    if ($field['sortable'])
                        echo '<span class="re-control-move "><div class="at_re_sort_handle" href=""><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 512.568 512.568" style="enable-background:new 0 0 512.568 512.568;" xml:space="preserve" width="512" height="512"><path d="M254.284,325.284c-38.598,0-70-31.402-70-70s31.402-70,70-70s70,31.402,70,70S292.882,325.284,254.284,325.284z   M254.284,225.284c-16.542,0-30,13.458-30,30s13.458,30,30,30s30-13.458,30-30S270.826,225.284,254.284,225.284z M360.427,407.426  l-28.285-28.284L255.284,456l-73.857-73.858l-28.285,28.284l102.143,102.142L360.427,407.426z M360.427,105.142L255.284,0  L152.142,103.142l28.285,28.284l74.857-74.858l76.857,76.858L360.427,105.142z"/></svg></div></span>';

                    echo'
        <span class="re-control at-re-toggle"><a href="#edit-'.$field['id'].'"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="32px" height="32px" viewBox="0 0 32 32" style="enable-background:new 0 0 32 32;" xml:space="preserve">
<g><g id="Pencil"><g><path d="M30.276,1.722C29.168,0.611,27.69,0,26.121,0s-3.045,0.61-4.154,1.72L4.294,19.291c-0.105,0.104-0.185,0.229-0.235,0.367     l-4,11c-0.129,0.355-0.046,0.756,0.215,1.031C0.466,31.891,0.729,32,1,32c0.098,0,0.196-0.014,0.293-0.044l9.949-3.052     c0.156-0.047,0.298-0.133,0.414-0.248l18.621-18.621C31.389,8.926,32,7.448,32,5.878C31.999,4.309,31.389,2.832,30.276,1.722z      M10.092,27.165l-3.724,1.144c-0.217-0.637-0.555-1.201-1.016-1.662c-0.401-0.399-0.866-0.709-1.356-0.961L5.7,21H8v2     c0,0.553,0.447,1,1,1h1.765L10.092,27.165z M24.812,12.671L12.628,24.855l0.35-1.647c0.062-0.296-0.012-0.603-0.202-0.837     C12.586,22.136,12.301,22,12,22h-2v-2c0-0.552-0.448-1-1-1H7.422L19.315,7.175l0.012,0.011c0.732-0.733,1.707-1.136,2.742-1.136     s2.011,0.403,2.742,1.136s1.138,1.707,1.138,2.743C25.949,10.965,25.546,11.938,24.812,12.671z M28.862,8.621L27.93,9.554     c-0.09-1.429-0.683-2.761-1.703-3.782c-1.021-1.022-2.354-1.614-3.787-1.703l0.938-0.931l0.002-0.002     C24.11,2.403,25.085,2,26.121,2s2.01,0.403,2.741,1.136C29.596,3.869,30,4.843,30,5.878C30,6.915,29.598,7.889,28.862,8.621z      M22.293,8.293l-10,10c-0.391,0.391-0.391,1.023,0,1.414C12.487,19.902,12.744,20,13,20s0.511-0.098,0.707-0.293l10-10     c0.391-0.391,0.391-1.023,0-1.414C23.315,7.902,22.684,7.902,22.293,8.293z"/></g></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></a></span> 
        <span class="re-control re-control-remove"><a class="remove-l remove-'.$field['id'].'" id="remove-'.$field['id'].'" href="#remove-'.$field['id'].'"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
   width="774.266px" height="774.266px" viewBox="0 0 774.266 774.266" style="enable-background:new 0 0 774.266 774.266;"
   xml:space="preserve"><g><g><path d="M640.35,91.169H536.971V23.991C536.971,10.469,526.064,0,512.543,0c-1.312,0-2.187,0.438-2.614,0.875 C509.491,0.438,508.616,0,508.179,0H265.212h-1.74h-1.75c-13.521,0-23.99,10.469-23.99,23.991v67.179H133.916 c-29.667,0-52.783,23.116-52.783,52.783v38.387v47.981h45.803v491.6c0,29.668,22.679,52.346,52.346,52.346h415.703 c29.667,0,52.782-22.678,52.782-52.346v-491.6h45.366v-47.981v-38.387C693.133,114.286,670.008,91.169,640.35,91.169z M285.713,47.981h202.84v43.188h-202.84V47.981z M599.349,721.922c0,3.061-1.312,4.363-4.364,4.363H179.282 c-3.052,0-4.364-1.303-4.364-4.363V230.32h424.431V721.922z M644.715,182.339H129.551v-38.387c0-3.053,1.312-4.802,4.364-4.802 H640.35c3.053,0,4.365,1.749,4.365,4.802V182.339z"/><rect x="475.031" y="286.593" width="48.418" height="396.942"/><rect x="363.361" y="286.593" width="48.418" height="396.942"/><rect x="251.69" y="286.593" width="48.418" height="396.942"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></a></span>
        <span class="re-control-clear"></span></div>';
                    $c = $c + 1;
                }
            }

            echo '<a id="add-'.$field['id'].'" href="#add-'.$field['id'].'">+ Add</a><br/></div>';

            //create all fields once more for js function and catch with object buffer
            ob_start();
            echo '<div class="at-repater-block"><div class="repeater-table">';
            if ($field['inline']){
                echo '<div class="at-inline" VALIGN="top">';
            }
            foreach ($field['fields'] as $f){
                //reset var $id for repeater
                $id = '';
                $id = $field['id'].'[CurrentCounter]['.$f['id'].']';
                $f['id'] = $id;
                if (!$field['inline']){
                    echo '<div>';
                }
                if ($f['type'] != 'wysiwyg')
                    call_user_func ( array( $this, 'show_field_' . $f['type'] ), $f, '');
                else
                    call_user_func ( array( $this, 'show_field_' . $f['type'] ), $f, '',true);
                if (!$field['inline']){
                    echo '</div>';
                }
            }
            if ($field['inline']){
                echo '</div>';
            }
            echo '</div><a id="remove-'.$field['id'].'" href="#remove-'.$field['id'].'"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
   width="774.266px" height="774.266px" viewBox="0 0 774.266 774.266" style="enable-background:new 0 0 774.266 774.266;"
   xml:space="preserve"><g><g><path d="M640.35,91.169H536.971V23.991C536.971,10.469,526.064,0,512.543,0c-1.312,0-2.187,0.438-2.614,0.875 C509.491,0.438,508.616,0,508.179,0H265.212h-1.74h-1.75c-13.521,0-23.99,10.469-23.99,23.991v67.179H133.916 c-29.667,0-52.783,23.116-52.783,52.783v38.387v47.981h45.803v491.6c0,29.668,22.679,52.346,52.346,52.346h415.703 c29.667,0,52.782-22.678,52.782-52.346v-491.6h45.366v-47.981v-38.387C693.133,114.286,670.008,91.169,640.35,91.169z M285.713,47.981h202.84v43.188h-202.84V47.981z M599.349,721.922c0,3.061-1.312,4.363-4.364,4.363H179.282 c-3.052,0-4.364-1.303-4.364-4.363V230.32h424.431V721.922z M644.715,182.339H129.551v-38.387c0-3.053,1.312-4.802,4.364-4.802 H640.35c3.053,0,4.365,1.749,4.365,4.802V182.339z"/><rect x="475.031" y="286.593" width="48.418" height="396.942"/><rect x="363.361" y="286.593" width="48.418" height="396.942"/><rect x="251.69" y="286.593" width="48.418" height="396.942"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></a></div>';
            $counter = 'countadd_'.$field['id'];
            $js_code = ob_get_clean ();
            $js_code = str_replace("\n","",$js_code);
            $js_code = str_replace("\r","",$js_code);
            $js_code = str_replace("'","\"",$js_code);
            $js_code = str_replace("CurrentCounter","' + ".$counter." + '",$js_code);
            echo '<script>
        jQuery(document).ready(function() {
          var '.$counter.' = '.$c.';
          jQuery("#add-'.$field['id'].'").live(\'click\', function() {
            '.$counter.' = '.$counter.' + 1;
            jQuery(this).before(\''.$js_code.'\');            
            update_repeater_fields();
            return false;
          });
              jQuery("#remove-'.$field['id'].'").live(\'click\', function() {
                  if (jQuery(this).parent().hasClass("re-control"))
                    jQuery(this).parent().parent().remove();
                  else
                    jQuery(this).parent().remove();
                  return false;
              });
          });
        </script>';
            echo '<br/><style>
</style>';
            $this->show_field_end($field, $meta);
        }

        /**
         * Begin Field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_begin( $field, $meta) {
            echo "<div class='at-field ".( isset($field['class']) ? $field['class'] : '' )."'".(($this->inGroup === true)? " valign='top'": "").">";
            if ( $field['name'] != '' || $field['name'] != FALSE ) {
                echo "<div class='at-label'>";
                echo "<label for='{$field['id']}'>{$field['name']}</label>";
                echo "</div>";
            }
        }

        /**
         * End Field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_end( $field, $meta=NULL ,$group = false) {
            //print description
            if ( isset($field['desc']) && $field['desc'] != '' )
                echo "<div class='desc-field'>{$field['desc']}</div>";
            echo "</div>";
        }

        /**
         * Show Field Text.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_text( $field, $meta) {
            $this->show_field_begin( $field, $meta );
            echo "<input type='text' class='at-field at-text".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}' value='{$meta}' size='30' ".( isset($field['style'])? "style='{$field['style']}'" : '' )."/>";
            $this->show_field_end( $field, $meta );
        }

        /**
         * Show Field number.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_number( $field, $meta) {
            $this->show_field_begin( $field, $meta );
            $step = (isset($field['step']) || $field['step'] != '1')? "step='".$field['step']."' ": '';
            $min = isset($field['min'])? "min='".$field['min']."' ": '';
            $max = isset($field['max'])? "max='".$field['max']."' ": '';
            echo "<input type='number' class='at-field at-number".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}' value='{$meta}' size='30' ".$step.$min.$max.( isset($field['style'])? "style='{$field['style']}'" : '' )."/>";
            $this->show_field_end( $field, $meta );
        }

        /**
         * Show Field code editor.
         *
         * @param string $field
         * @author Ohad Raz
         * @param string $meta
         * @since 2.1
         * @access public
         */
        public function show_field_code( $field, $meta) {
            $this->show_field_begin( $field, $meta );
            echo "<textarea class='code_text".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}' data-lang='{$field['syntax']}' ".( isset($field['style'])? "style='{$field['style']}'" : '' )." data-theme='{$field['theme']}'>{$meta}</textarea>";
            $this->show_field_end( $field, $meta );
        }


        /**
         * Show Field hidden.
         *
         * @param string $field
         * @param string|mixed $meta
         * @since 0.1.3
         * @access public
         */
        public function show_field_hidden( $field, $meta) {
            //$this->show_field_begin( $field, $meta );
            echo "<input type='hidden' ".( isset($field['style'])? "style='{$field['style']}' " : '' )."class='at-text".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}' value='{$meta}'/>";
            //$this->show_field_end( $field, $meta );
        }

        /**
         * Show Field Paragraph.
         *
         * @param string $field
         * @since 0.1.3
         * @access public
         */
        public function show_field_paragraph( $field) {
            //$this->show_field_begin( $field, $meta );
            echo '<p>'.$field['value'].'</p>';
            //$this->show_field_end( $field, $meta );
        }

        /**
         * Show Field Textarea.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_textarea( $field, $meta ) {
            $this->show_field_begin( $field, $meta );
            echo "<textarea class='at-textarea large-text".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}' ".( isset($field['style'])? "style='{$field['style']}' " : '' )." cols='60' rows='10'>{$meta}</textarea>";
            $this->show_field_end( $field, $meta );
        }

        /**
         * Show Field Select.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_select( $field, $meta ) {

            if ( ! is_array( $meta ) )
                $meta = (array) $meta;

            $this->show_field_begin( $field, $meta );
            echo "<select ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='at-select".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}" . ( $field['multiple'] ? "[]' id='{$field['id']}' multiple='multiple'" : "'" ) . ">";
            foreach ( $field['options'] as $key => $value ) {
                echo "<option value='{$key}'" . selected( in_array( $key, $meta ), true, false ) . ">{$value}</option>";
            }
            echo "</select>";
            $this->show_field_end( $field, $meta );

        }

        /**
         * Show Radio Field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_radio( $field, $meta ) {

            if ( ! is_array( $meta ) )
                $meta = (array) $meta;

            $this->show_field_begin( $field, $meta );
            foreach ( $field['options'] as $key => $value ) {
                echo "<input type='radio' ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='at-radio".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> <span class='at-radio-label'>{$value}</span>";
            }
            $this->show_field_end( $field, $meta );
        }

        /**
         * Show Checkbox Field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_checkbox( $field, $meta ) {


            $this->show_field_begin($field, $meta);
            echo "<input type='checkbox' ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='rw-checkbox".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}'" . checked(!empty($meta), true, false) . " />";
            $this->show_field_end( $field, $meta );

        }

        /**
         * Show Wysiwig Field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_wysiwyg( $field, $meta,$in_repeater = false ) {
            $this->show_field_begin( $field, $meta );

            if ( $in_repeater )
                echo "<textarea class='at-wysiwyg theEditor large-text".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>{$meta}</textarea>";
            else{
                // Use new wp_editor() since WP 3.3
                $n=10;
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $randomString = '';
                for ($i = 0; $i < $n; $i++) {
                    $index = rand(0, strlen($characters) - 1);
                    $randomString .= $characters[$index];
                }
                $settings = ( isset($field['settings']) && is_array($field['settings'])? $field['settings']: array() );
                $settings['editor_class'] = 'at-wysiwyg'.( isset($field['class'])? ' ' . $field['class'] : '' );
                $id = strtolower( $field['id'] );
                wp_editor( html_entity_decode($meta), $id, $settings);
            }
            $this->show_field_end( $field, $meta );
        }

        /**
         * Show File Field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_file( $field, $meta ) {
            wp_enqueue_media();
            $this->show_field_begin( $field, $meta );

            $std      = isset($field['std'])? $field['std'] : array('id' => '', 'url' => '');
            $multiple = isset($field['multiple'])? $field['multiple'] : false;
            $multiple = ($multiple)? "multiFile '" : "";
            $name     = esc_attr( $field['id'] );
            $value    = isset($meta['id']) ? $meta : $std;
            $has_file = (empty($value['url']))? false : true;
            $type     = isset($field['mime_type'])? $field['mime_type'] : '';
            $ext      = isset($field['ext'])? $field['ext'] : '';
            $type     = (is_array($type)? implode("|",$type) : $type);
            $ext      = (is_array($ext)? implode("|",$ext) : $ext);
            $id       = $field['id'];
            $li       = ($has_file)? "<li><a href='{$value['url']}' target='_blank'>{$value['url']}</a></li>": "";

            echo "<span class='simplePanelfilePreview'><ul>{$li}</ul></span>";
            echo "<input type='hidden' name='{$name}[id]' value='{$value['id']}'/>";
            echo "<input type='hidden' name='{$name}[url]' value='{$value['url']}'/>";
            if ($has_file)
                echo "<input type='button' class='{$multiple} button simplePanelfileUploadclear' id='{$id}' value='Remove File' data-mime_type='{$type}' data-ext='{$ext}'/>";
            else
                echo "<input type='button' class='{$multiple} button simplePanelfileUpload' id='{$id}' value='Upload File' data-mime_type='{$type}' data-ext='{$ext}'/>";

            $this->show_field_end( $field, $meta );
        }

        /**
         * Show Image Field.
         *
         * @param array $field
         * @param array $meta
         * @since 1.0
         * @access public
         */
        public function show_field_image( $field, $meta ) {
            wp_enqueue_media();
            $this->show_field_begin( $field, $meta );

            $std          = isset($field['std'])? $field['std'] : array('id' => '', 'url' => '');
            $name         = esc_attr( $field['id'] );
            $value        = isset($meta['id']) ? $meta : $std;

            $value['url'] = isset($meta['src'])? $meta['src'] : $value['url']; //backwords capability
            $has_image    = empty($value['url'])? false : true;
            $w            = isset($field['width'])? $field['width'] : 'auto';
            $h            = isset($field['height'])? $field['height'] : 'auto';
            $PreviewStyle = "style='width: $w; height: $h;". ( (!$has_image)? "display: none;'": "'");
            $id           = $field['id'];
            $multiple     = isset($field['multiple'])? $field['multiple'] : false;
            $multiple     = ($multiple)? "multiFile " : "";

            echo "<span class='simplePanelImagePreview'><img {$PreviewStyle} src='{$value['url']}'><br/></span>";
            echo "<input type='hidden' name='{$name}[id]' value='{$value['id']}'/>";
            echo "<input type='hidden' name='{$name}[url]' value='{$value['url']}'/>";
            if ($has_image)
                echo "<input class='{$multiple} button  simplePanelimageUploadclear' id='{$id}' value='Remove Image' type='button'/>";
            else
                echo "<input class='{$multiple} button simplePanelimageUpload' id='{$id}' value='Upload Image' type='button'/>";
            $this->show_field_end( $field, $meta );
        }

        /**
         * Show Color Field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_color( $field, $meta ) {

            if ( empty( $meta ) )
                $meta = '#';

            $this->show_field_begin( $field, $meta );
            if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //iris color picker since 3.5
                echo "<input class='at-color-iris".(isset($field['class'])? " {$field['class']}": "")."' type='text' name='{$field['id']}' id='{$field['id']}' value='{$meta}' size='8' />";
            }else{
                echo "<input class='at-color".(isset($field['class'])? " {$field['class']}": "")."' type='text' name='{$field['id']}' id='{$field['id']}' value='{$meta}' size='8' />";
                echo "<input type='button' class='at-color-select button' rel='{$field['id']}' value='" . __( 'Select a color' ,'apc') . "'/>";
                echo "<div style='display:none' class='at-color-picker' rel='{$field['id']}'></div>";
            }
            $this->show_field_end($field, $meta);

        }

        /**
         * Show Checkbox List Field
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_checkbox_list( $field, $meta ) {

            if ( ! is_array( $meta ) )
                $meta = (array) $meta;

            $this->show_field_begin($field, $meta);

            $html = array();

            foreach ($field['options'] as $key => $value) {
                $html[] = "<input type='checkbox' ".( isset($field['style'])? "style='{$field['style']}' " : '' )."  class='at-checkbox_list".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}[]' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> {$value}";
            }

            echo implode( '<br />' , $html );

            $this->show_field_end($field, $meta);

        }

        /**
         * Show Date Field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_date( $field, $meta ) {
            $this->show_field_begin( $field, $meta );
            echo "<input type='text'  ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='at-field at-date".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}' rel='{$field['format']}' value='{$meta}' size='30' />";
            $this->show_field_end( $field, $meta );
        }

        /**
         * Show time field.
         *
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_time( $field, $meta ) {
            $this->show_field_begin( $field, $meta );
            $ampm = ($field['ampm'])? 'true' : 'false';
            echo "<input type='text'  ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='at-field at-time".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}' id='{$field['id']}' data-ampm='{$ampm}' rel='{$field['format']}' value='{$meta}' size='30' />";
            $this->show_field_end( $field, $meta );
        }

        /**
         * Show Posts field.
         * used creating a posts/pages/custom types checkboxlist or a select dropdown
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         */
        public function show_field_posts($field, $meta) {
            global $post;

            if (!is_array($meta)) $meta = (array) $meta;
            $this->show_field_begin($field, $meta);
            $options = $field['options'];
            $posts = get_posts($options['args']);
            // checkbox_list
            if ('checkbox_list' == $options['type']) {
                foreach ($posts as $p) {
                    echo "<input type='checkbox' ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='at-posts-checkbox".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}[]' value='$p->ID'" . checked(in_array($p->ID, $meta), true, false) . " /> $p->post_title<br/>";
                }
            }
            // select
            else {
                echo "<select ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='at-posts-select".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}" . ($field['multiple'] ? "[]' multiple='multiple' style='height:auto'" : "'") . ">";
                if (isset($field['emptylabel']))
                    echo '<option value="-1">'.(isset($field['emptylabel'])? $field['emptylabel']: __('Select ...','mmb')).'</option>';
                foreach ($posts as $p) {
                    echo "<option value='$p->ID'" . selected(in_array($p->ID, $meta), true, false) . ">$p->post_title</option>";
                }
                echo "</select>";
            }

            $this->show_field_end($field, $meta);
        }

        /**
         * Show Taxonomy field.
         * used creating a category/tags/custom taxonomy checkboxlist or a select dropdown
         * @param string $field
         * @param string $meta
         * @since 1.0
         * @access public
         *
         * @uses get_terms()
         */
        public function show_field_taxonomy($field, $meta) {
            global $post;

            if (!is_array($meta)) $meta = (array) $meta;
            $this->show_field_begin($field, $meta);
            $options = $field['options'];

            // checkbox_list
            if ('checkbox_list' == $options['type']) {
                $terms = get_terms($options['taxonomy'], $options['args']);
                foreach ($terms as $term) {
                    echo "<input type='checkbox' ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='at-tax-checkbox".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}[]' value='$term->slug'" . checked(in_array($term->slug, $meta), true, false) . " /> $term->name<br/>";
                }
            }
            // select
            else {
                $display_terms = false;
                if(!is_array($options['taxonomy'])){
                    $terms = get_terms($options['taxonomy'], $options['args']);
                }else{
                    $display_terms = true;
                }
                echo "<select ".( isset($field['style'])? "style='{$field['style']}' " : '' )." class='at-tax-select".( isset($field['class'])? ' ' . $field['class'] : '' )."' name='{$field['id']}" . ($field['multiple'] ? "[]' multiple='multiple' style='height:auto'" : "'") . ">";
                if($display_terms == false){
                    if(isset($options['std'])){
                        echo "<option value=''>".$options['std']."</option>";
                    }
                    foreach ($terms as $term) {
                        echo "<option value='$term->slug'" . selected(in_array($term->slug, $meta), true, false) . ">$term->name</option>";
                    }
                }else{
                    foreach ($options['taxonomy'] as $taxonomy) {
                        $terms = get_terms($taxonomy, $options['args']);
                        echo '<optgroup label="'.$taxonomy.'">';
                        foreach ($terms as $term) {
                            echo "<option value='$term->term_id'" . selected(in_array($term->term_id, $meta), true, false) . ">$term->name</option>";
                        }
                        echo '</optgroup>';
                    }
                }
                echo "</select>";
            }

            $this->show_field_end($field, $meta);
        }

        /**
         * Show conditinal Checkbox Field.
         *
         * @param string $field
         * @param string $meta
         * @since 2.9.9
         * @access public
         */
        public function show_field_cond( $field, $meta ) {

            $this->show_field_begin($field, $meta);
            $checked = false;
            if (is_array($meta) && isset($meta['enabled']) && $meta['enabled'] == 'on'){
                $checked = true;
            }
            echo "<input type='checkbox' class='conditinal_control' name='{$field['id']}[enabled]' id='{$field['id']}'" . checked($checked, true, false) . " />";
            //start showing the fields
            $display = ($checked)? '' :  ' style="display: none;"';

            echo '<div class="conditinal_container"'.$display.'><div>';
            foreach ((array)$field['fields'] as $f){
                //reset var $id for cond
                $id = '';
                $id = $field['id'].'['.$f['id'].']';
                $m = '';
                $m = (isset($meta[$f['id']])) ? $meta[$f['id']]: '';
                $m = ( $m !== '' ) ? $m : (isset($f['std'])? $f['std'] : '');
                if ('image' != $f['type'] && $f['type'] != 'repeater')
                    $m = is_array( $m) ? array_map( 'esc_attr', $m ) : esc_attr( $m);
                //set new id for field in array format
                $f['id'] = $id;
                $dashname = sanitize_title_with_dashes($field["name"]);
                echo '<div class="'.$dashname.'">';
                call_user_func ( array( $this, 'show_field_' . $f['type'] ), $f, $m);
                echo '</div>';
            }
            echo '</div></div>';
            $this->show_field_end( $field, $meta );
        }

        /**
         * Save Data from Metabox
         *
         * @since 1.0
         * @access public
         */
        public function save() {
            $display = true;
            if($display){
                foreach ( $this->_fields as $field ) {

                    $name = $field['id'];
                    $type = $field['type'];
                    $old = apply_filters( 'get_theme_option', $name, ! $field['multiple'] );
                    $new = ( isset( $_POST[$name] ) ) ? $_POST[$name] : ( ( $field['multiple'] ) ? array() : '' );

                    //skip on Paragraph field
                    if ($type != "paragraph"){

                        // Call defined method to save meta value, if there's no methods, call common one.
                        $save_func = 'save_field_' . $type;
                        if ( method_exists( $this, $save_func ) ) {
                            call_user_func( array( $this, 'save_field_' . $type ), $field, $old, $new );
                        } else {
                            $this->save_field( $field, $old, $new );
                        }
                    }

                } // End foreach
            }
        }

        /**
         * Common function for saving fields.
         *
         * @param string $field
         * @param string $old
         * @param string|mixed $new
         * @since 1.0
         * @access public
         */
        public function save_field( $field, $old, $new ) {
            $name = $field['id'];
            apply_filters( 'delete_theme_option', $name );
            if ( $new === '' || $new === array() )
                return;
            if ( $field['multiple'] ) {
                foreach ( $new as $add_new ) {
                    apply_filters( 'save_theme_option_array', $name, $add_new );
                }
            } else {
                apply_filters( 'save_theme_option', $name, $new );
            }
        }

        /**
         * function for saving image field.
         *
         * @param string $field
         * @param string $old
         * @param string|mixed $new
         * @since 1.7
         * @access public
         */
        public function save_field_image( $field, $old, $new ) {
            $name = $field['id'];
            apply_filters( 'delete_theme_option', $name );
            if ( $new === '' || $new === array() || $new['id'] == '' || $new['url'] == '')
                return;

            apply_filters( 'save_theme_option', $name, $new );
        }

        /*
         * Save Wysiwyg Field.
         *
         * @param string $field
         * @param string $old
         * @param string $new
         * @since 1.0
         * @access public
         */
        public function save_field_wysiwyg( $field, $old, $new ) {
            $id = strtolower( $field['id'] );
            $new = ( isset( $_POST[$id] ) ) ? $_POST[$id] : ( ( $field['multiple'] ) ? array() : '' );
            $this->save_field( $field, $old, $new );
        }

        /**
         * Save repeater Fields.
         *
         * @param string $field
         * @param string|mixed $old
         * @param string|mixed $new
         * @since 1.0
         * @access public
         */
        public function save_field_repeater( $field, $old, $new ) {
            if (is_array($new) && count($new) > 0){
                foreach ($new as $n){
                    foreach ( $field['fields'] as $f ) {
                        $type = $f['type'];
                        switch($type) {
                            case 'wysiwyg':
                                $n[$f['id']] = wpautop( $n[$f['id']] );
                                break;
                            default:
                                break;
                        }
                    }
                    if(!$this->is_array_empty($n))
                        $temp[] = $n;
                }
                if (isset($temp) && count($temp) > 0 && !$this->is_array_empty($temp)){
                    apply_filters( 'save_theme_option', $field['id'], $temp );
                }else{
                    //  remove old meta if exists
                    apply_filters( 'delete_theme_option', $field['id'] );
                }
            }else{
                apply_filters( 'delete_theme_option', $field['id'] );
                //  remove old meta if exists
            }
        }

        /**
         * Save File Field.
         *
         * @param string $field
         * @param string $old
         * @param string $new
         * @since 1.0
         * @access public
         */
        public function save_field_file( $field, $old, $new ) {

            $name = $field['id'];
            apply_filters( 'delete_theme_option', $name );
            if ( $new === '' || $new === array() || $new['id'] == '' || $new['url'] == '')
                return;

            apply_filters( 'save_theme_option',$name, $new );
        }

        /**
         * Save repeater File Field.
         * @param string $field
         * @param string $old
         * @param string $new
         * @since 1.0
         * @access public
         * @deprecated 3.0.7
         */
        public function save_field_file_repeater( $field, $old, $new ) {}

        /**
         * Add missed values for meta box.
         *
         * @since 1.0
         * @access public
         */
        public function add_missed_values() {

            // Default values for meta box
            $this->_meta_box = array_merge( array( 'context' => 'normal', 'priority' => 'high', 'pages' => array( 'post' ) ), (array)$this->_meta_box );

            // Default values for fields
            foreach ( $this->_fields as &$field ) {

                $multiple = in_array( $field['type'], array( 'checkbox_list', 'file', 'image' ) );
                $std = $multiple ? array() : '';
                $format = 'date' == $field['type'] ? 'yy-mm-dd' : ( 'time' == $field['type'] ? 'hh:mm' : '' );

                $field = array_merge( array( 'multiple' => $multiple, 'std' => $std, 'desc' => '', 'format' => $format, 'validate_func' => '' ), $field );

            } // End foreach

        }

        /**
         * Check if field with $type exists.
         *
         * @param string $type
         * @since 1.0
         * @access public
         */
        public function has_field( $type ) {
            //faster search in single dimention array.
            if (count($this->field_types) > 0){
                return in_array($type, $this->field_types);
            }

            //run once over all fields and store the types in a local array
            $temp = array();
            foreach ($this->_fields as $field) {
                $temp[] = $field['type'];
                if ('repeater' == $field['type']  || 'cond' == $field['type']){
                    foreach((array)$field["fields"] as $repeater_field) {
                        $temp[] = $repeater_field["type"];
                    }
                }
            }

            //remove duplicates
            $this->field_types = array_unique($temp);
            //call this function one more time now that we have an array of field types
            return $this->has_field($type);
        }

        /**
         * Check if current page is edit page.
         *
         * @since 1.0
         * @access public
         */
        public function is_edit_page() {
            global $pagenow;
            return in_array( $pagenow, array( 'options-general.php' ) );
        }

        /**
         * Fixes the odd indexing of multiple file uploads.
         *
         * Goes from the format:
         * $_FILES['field']['key']['index']
         * to
         * The More standard and appropriate:
         * $_FILES['field']['index']['key']
         *
         * @param string $files
         * @since 1.0
         * @access public
         */
        public function fix_file_array( &$files ) {

            $output = array();

            foreach ( $files as $key => $list ) {
                foreach ( $list as $index => $value ) {
                    $output[$index][$key] = $value;
                }
            }

            return $output;

        }

        /**
         * Get proper JQuery UI version.
         *
         * Used in order to not conflict with WP Admin Scripts.
         *
         * @since 1.0
         * @access public
         */
        public function get_jqueryui_ver() {

            global $wp_version;

            if ( version_compare( $wp_version, '3.1', '>=') ) {
                return '1.8.10';
            }

            return '1.7.3';

        }

        /**
         *  Add Field to meta box (generic function)
         *  @author Ohad Raz
         *  @since 1.2
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         */
        public function addField($id,$args){
            $new_field = array('id'=> $id,'std' => '','desc' => '','style' =>'');
            $new_field = array_merge($new_field, $args);
            $this->_fields[] = $new_field;
        }

        /**
         *  Add Text Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'style' =>   // custom style for field, string optional
         *    'validate_func' => // validate function, string optional
         *   @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addText($id,$args,$repeater=false){
            $new_field = array('type' => 'text','id'=> $id,'std' => '','desc' => '','style' =>'','name' => 'Text Field');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Number Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'style' =>   // custom style for field, string optional
         *    'validate_func' => // validate function, string optional
         *   @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addNumber($id,$args,$repeater=false){
            $new_field = array('type' => 'number','id'=> $id,'std' => '0','desc' => '','style' =>'','name' => 'Number Field','step' => '1','min' => '0');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add code Editor to meta box
         *  @author Ohad Raz
         *  @since 2.1
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'style' =>   // custom style for field, string optional
         *    'syntax' =>   // syntax language to use in editor (php,javascript,css,html)
         *    'validate_func' => // validate function, string optional
         *   @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addCode($id,$args,$repeater=false){
            $new_field = array('type' => 'code','id'=> $id,'std' => '','desc' => '','style' =>'','name' => 'Code Editor Field','syntax' => 'php','theme' => 'defualt');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Hidden Field to meta box
         *  @author Ohad Raz
         *  @since 0.1.3
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'style' =>   // custom style for field, string optional
         *    'validate_func' => // validate function, string optional
         *   @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addHidden($id,$args,$repeater=false){
            $new_field = array('type' => 'hidden','id'=> $id,'std' => '','desc' => '','style' =>'','name' => 'Text Field');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Paragraph to meta box
         *  @author Ohad Raz
         *  @since 0.1.3
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $value  paragraph html
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addParagraph($id,$args,$repeater=false){
            $new_field = array('type' => 'paragraph','id'=> $id,'value' => '');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Checkbox Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addCheckbox($id,$args,$repeater=false){
            $new_field = array('type' => 'checkbox','id'=> $id,'std' => '','desc' => '','style' =>'','name' => 'Checkbox Field');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add CheckboxList Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $options (array)  array of key => value pairs for select options
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         *
         *   @return : remember to call: $checkbox_list = get_post_meta(get_the_ID(), 'meta_name', false);
         *   which means the last param as false to get the values in an array
         */
        public function addCheckboxList($id,$options,$args,$repeater=false){
            $new_field = array('type' => 'checkbox_list','id'=> $id,'std' => '','desc' => '','style' =>'','name' => 'Checkbox List Field','options' =>$options,'multiple' => true,);
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Textarea Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'style' =>   // custom style for field, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addTextarea($id,$args,$repeater=false){
            $new_field = array('type' => 'textarea','id'=> $id,'std' => '','desc' => '','style' =>'','name' => 'Textarea Field');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Select Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string field id, i.e. the meta key
         *  @param $options (array)  array of key => value pairs for select options
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, (array) optional
         *    'multiple' => // select multiple values, optional. Default is false.
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addSelect($id,$options,$args,$repeater=false){
            $new_field = array('type' => 'select','id'=> $id,'std' => array(),'desc' => '','style' =>'','name' => 'Select Field','multiple' => false,'options' => $options);
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }
        public function addSelectgform($id,$args,$repeater=false){
        	global $wpdb;
	        $forms = $wpdb->get_results( "SELECT `id`,`title` FROM {$wpdb->prefix}gf_form", OBJECT );
	        $options = array();
	        if($forms){
		        foreach ($forms as $form) {
			        $options[$form->id] = $form->title;
		        }
	        }
	        $args['options'] = $options;
	        $new_field = array('type' => 'select','id'=> $id,'std' => array(),'desc' => '','style' =>'','name' => 'Select Field','multiple' => false,'options' => $options);
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }


        /**
         *  Add Radio Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string field id, i.e. the meta key
         *  @param $options (array)  array of key => value pairs for radio options
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addRadio($id,$options,$args,$repeater=false){
            $new_field = array('type' => 'radio','id'=> $id,'std' => array(),'desc' => '','style' =>'','name' => 'Radio Field','options' => $options);
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Date Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *    'format' => // date format, default yy-mm-dd. Optional. Default "'d MM, yy'"  See more formats here: http://goo.gl/Wcwxn
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addDate($id,$args,$repeater=false){
            $new_field = array('type' => 'date','id'=> $id,'std' => '','desc' => '','format'=>'d MM, yy','name' => 'Date Field');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Time Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string- field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *    'format' => // time format, default hh:mm. Optional. See more formats here: http://goo.gl/83woX
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addTime($id,$args,$repeater=false){
            $new_field = array('type' => 'time','id'=> $id,'std' => '','desc' => '','format'=>'hh:mm','name' => 'Time Field', 'ampm' => false);
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Color Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addColor($id,$args,$repeater=false){
            $new_field = array('type' => 'color','id'=> $id,'std' => '','desc' => '','name' => 'ColorPicker Field');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Image Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addImage($id,$args,$repeater=false){
            $new_field = array('type' => 'image','id'=> $id,'desc' => '','name' => 'Image Field','std' => array('id' => '', 'url' => ''),'multiple' => false);
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add File Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addFile($id,$args,$repeater=false){
            $new_field = array('type' => 'file','id'=> $id,'desc' => '','name' => 'File Field','multiple' => false,'std' => array('id' => '', 'url' => ''));
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add WYSIWYG Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'style' =>   // custom style for field, string optional Default 'width: 300px; height: 400px'
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addWysiwyg($id,$args,$repeater=false){
            $new_field = array('type' => 'wysiwyg','id'=> $id,'std' => '','desc' => '','style' =>'width: 300px; height: 400px','name' => 'WYSIWYG Editor Field');
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add Taxonomy Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $options mixed|array options of taxonomy field
         *    'taxonomy' =>    // taxonomy name can be category,post_tag or any custom taxonomy default is category
         *    'type' =>  // how to show taxonomy? 'select' (default) or 'checkbox_list'
         *    'args' =>  // arguments to query taxonomy, see http://goo.gl/uAANN default ('hide_empty' => false)
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addTaxonomy($id,$options,$args,$repeater=false){
            // echo "<pre>";
            // var_dump($options['tax']);
            // echo "</pre>";
            $temp = array(
                'args' => array('hide_empty' => 0),
                'tax' => 'category',
                'type' => 'select');
            $options = array_merge($temp,$options);
            // echo "<pre>";
            // var_dump($options['tax']);
            // echo "</pre>";
            $new_field = array('type' => 'taxonomy','id'=> $id,'desc' => '','name' => 'Taxonomy Field','options'=> $options);
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add posts Field to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $options mixed|array options of taxonomy field
         *    'post_type' =>    // post type name, 'post' (default) 'page' or any custom post type
         *    'type' =>  // how to show posts? 'select' (default) or 'checkbox_list'
         *    'args' =>  // arguments to query posts, see http://goo.gl/is0yK default ('posts_per_page' => -1)
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addPosts($id,$options,$args,$repeater=false){
            $post_type = isset($options['post_type'])? $options['post_type']: (isset($args['post_type']) ? $args['post_type']: 'post');
            $type = isset($options['type'])? $options['type']: 'select';
            $q = array('posts_per_page' => -1, 'post_type' => $post_type);
            if (isset($options['args']) )
                $q = array_merge($q,(array)$options['args']);
            $options = array('post_type' =>$post_type,'type'=>$type,'args'=>$q);
            $new_field = array('type' => 'posts','id'=> $id,'desc' => '','name' => 'Posts Field','options'=> $options,'multiple' => false);
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }

        /**
         *  Add repeater Field Block to meta box
         *  @author Ohad Raz
         *  @since 1.0
         *  @access public
         *  @param $id string  field id, i.e. the meta key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'style' =>   // custom style for field, string optional
         *    'validate_func' => // validate function, string optional
         *    'fields' => //fields to repeater
         */
        public function addRepeaterBlock($id,$args){
            $new_field = array(
                'type'     => 'repeater',
                'id'       => $id,
                'name'     => 'Reapeater Field',
                'fields'   => array(),
                'inline'   => false,
                'sortable' => false
            );
            $new_field = array_merge($new_field, $args);
            $this->_fields[] = $new_field;
        }

        /**
         *  Add Checkbox conditional Field to Page
         *  @author Ohad Raz
         *  @since 2.9.9
         *  @access public
         *  @param $id string  field id, i.e. the key
         *  @param $args mixed|array
         *    'name' => // field name/label string optional
         *    'desc' => // field description, string optional
         *    'std' => // default value, string optional
         *    'validate_func' => // validate function, string optional
         *    'fields' => list of fields to show conditionally.
         *  @param $repeater bool  is this a field inside a repeatr? true|false(default)
         */
        public function addCondition($id,$args,$repeater=false){
            $new_field = array(
                'type'   => 'cond',
                'id'     => $id,
                'std'    => '',
                'desc'   => '',
                'style'  =>'',
                'name'   => 'Conditional Field',
                'fields' => array()
            );
            $new_field = array_merge($new_field, $args);
            if(false === $repeater){
                $this->_fields[] = $new_field;
            }else{
                return $new_field;
            }
        }


        /**
         * Finish Declaration of Meta Box
         * @author Ohad Raz
         * @since 1.0
         * @access public
         */
        public function Finish() {
            $this->add_missed_values();
        }

        /**
         * Helper function to check for empty arrays
         * @author Ohad Raz
         * @since 1.5
         * @access public
         * @param $args mixed|array
         */
        public function is_array_empty($array){
            if (!is_array($array))
                return true;

            foreach ($array as $a){
                if (is_array($a)){
                    foreach ($a as $sub_a){
                        if (!empty($sub_a) && $sub_a != '')
                            return false;
                    }
                }else{
                    if (!empty($a) && $a != '')
                        return false;
                }
            }
            return true;
        }

        /**
         * Validate_upload_file_type
         *
         * Checks if the uploaded file is of the expected format
         *
         * @author Ohad Raz <admin@bainternet.info>
         * @since 3.0.7
         * @access public
         * @uses get_allowed_mime_types() to check allowed types
         * @param array $file uploaded file
         * @return array file with error on mismatch
         */
        function Validate_upload_file_type($file) {
            if (isset($_POST['uploadeType']) && !empty($_POST['uploadeType']) && isset($_POST['uploadeType']) && $_POST['uploadeType'] == 'my_meta_box'){
                $allowed = explode("|", $_POST['uploadeType']);
                $ext =  substr(strrchr($file['name'],'.'),1);

                if (!in_array($ext, (array)$allowed)){
                    $file['error'] = __("Sorry, you cannot upload this file type for this field.");
                    return $file;
                }

                foreach (get_allowed_mime_types() as $key => $value) {
                    if (strpos($key, $ext) || $key == $ext)
                        return $file;
                }
                $file['error'] = __("Sorry, you cannot upload this file type for this field.");
            }
            return $file;
        }

        /**
         * function to sanitize field id
         *
         * @author Ohad Raz <admin@bainternet.info>
         * @since 3.0.7
         * @access public
         * @param  string $str string to sanitize
         * @return string      sanitized string
         */
        public function idfy($str){
            return str_replace(" ", "_", $str);

        }

        /**
         * stripNumeric Strip number form string
         *
         * @author Ohad Raz <admin@bainternet.info>
         * @since 3.0.7
         * @access public
         * @param  string $str
         * @return string number less string
         */
        public function stripNumeric($str){
            return trim(str_replace(range(0,9), '', $str) );
        }


        /**
         * load_textdomain
         * @author Ohad Raz
         * @since 2.9.4
         * @return void
         */
        public function load_textdomain(){
            //In themes/plugins/mu-plugins directory
            load_textdomain( 'mmb', dirname(__FILE__) . '/lang/' . get_locale() .'.mo' );
        }
    } // End Class
endif; // End Check Class Exists
