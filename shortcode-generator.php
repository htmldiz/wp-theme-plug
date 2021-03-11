<?php
class ListProducts{
    function __construct(){
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'wp_ajax_get_list_products', array($this,'get_list_products') );
        add_action( 'wp_ajax_nopriv_get_list_products', array($this,'get_list_products') );
        add_action( 'wp_ajax_get_list_categores', array($this,'get_list_categores') );
        add_action( 'wp_ajax_nopriv_get_list_categores', array($this,'get_list_categores') );
        add_shortcode('listproducts',array($this,'listproducts'));
        add_action( 'wp_ajax_get_list_pharmacy', array($this,'get_list_pharmacy') );
        add_action( 'wp_ajax_nopriv_get_list_pharmacy', array($this,'get_list_pharmacy') );
        add_action( 'wp_ajax_get_list_strain', array($this,'get_list_strain') );
        add_action( 'wp_ajax_nopriv_get_list_strain', array($this,'get_list_strain') );
        add_action( 'wp_ajax_get_list_brand', array($this,'get_list_brand') );
        add_action( 'wp_ajax_nopriv_get_list_brand', array($this,'get_list_brand') );
    }
    function listproducts($args){
        $args = shortcode_atts(array(
            'items'       => "",
            'limit'       => -1,
            'title'       => "",
            'typeof_post' => "post",
            'typedisplay' => "latest",
        ),$args);
        $args_post = array(
            'posts_per_page'   => -1,
            'offset'           => 0,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'post_type'        => $args['typeof_post'],
            'post_status'      => 'publish',
            'suppress_filters' => true,
            'fields'           => 'ids',
        );
        $args['items'] = explode(',',$args['items']);
        foreach ($args['items'] as $key => $item){
            $args['items'][$key] = (int)$item;
        }
        switch ($args['typedisplay']){
            case 'latest':
                $args_post['posts_per_page'] = $args['limit'];
                break;
            case 'categores':
                $args_post['posts_per_page'] = $args['limit'];
                $args_post['tax_query'] = array(
                    array(
                        'taxonomy' => 'category',
                        'field'    => 'id',
                        'terms'    => $args['items']
                    )
                );
                break;
            default:
                $args_post['include']        = $args['items'];
                break;
        }
        $products = get_posts( $args_post );
        $outhtml   = '<div class="list-produts-in-cat">';
        $outhtml   .= '<div class="row">';
            $outhtml  .= '<div class="col-12"><div class="breaker"></div></div>';
            $outhtml  .= '<div class="text-section text-center col-12"><h2 class="h2">'.$args['title'].'</h2></div>';
        $outhtml  .= '</div>';
        $outhtml  .= '<div class="row single-product justify-content-center">';
        if(count($products)){
            foreach ($products as $product_id) {
                $htm = "";
                $htm  = apply_filters('display_product_item_loop_html',$htm,$product_id);
                $outhtml .= $htm;
            }
        }
//        $outhtml  .= apply_filters('display_product_item_loop_html',$product_id);
        $outhtml .= '</div>';
        $outhtml .= '</div>';
//        require_once dirname(__FILE__)."/template-listproducts.php";
        return $outhtml;
    }
    function get_list_categores(){
        $args = array(
                'taxonomy'=>'category',
                'hide_empty'=>1
        );
        $terms = get_terms($args);
        $out_rems = array();
        foreach ($terms as $term){
            $out_rems[] = array(
                'id'    => $term->term_id,
                'title' => $term->name
            );
        }
        echo wp_json_encode($out_rems);
        exit();
    }
    function get_posts_by_type($type){
        $args = array(
            'posts_per_page'   => -1,
            'offset'           => 0,
            'category'         => '',
            'category_name'    => '',
            'orderby'          => 'date',
            'order'            => 'DESC',
            'post_type'        => $type,
            'author_name'	   => '',
            'post_status'      => 'publish',
            'suppress_filters' => true,
            'fields'           => 'ids',
        );
        $products = get_posts( $args );
        $out_array = array();
        if(count($products)){
            foreach ($products as $product_id) {
                $out_array[] = array(
                    'id'    => $product_id,
                    'title' => get_the_title($product_id),
                );
            }
        }
        echo wp_json_encode($out_array);
    }
    function get_list_products(){
        $this->get_posts_by_type('post');
        exit();
    }
    function add_plugin_page(){
        add_options_page(
            'List products generator',
            'List products shortcode generator',
            'manage_options',
            'lpsg',
            array( $this, 'create_admin_page' )
        );
    }
    function create_admin_page(){
        add_thickbox();
        ?>
        <style>.callJsshortcodegen{display:none}.form-group .label-text{font-weight:500;font-size:18px;line-height:1.5}.form-group,.form-group .label-text{display:block}.form-generator-shrtcode h1{margin:0;margin-bottom:10px;line-height:1.5}.form-generator-shrtcode{background:#fff;margin-top:10px;padding:10px}.limits:not(.active),.selection_items:not(.active){display:none}.form-field{width:100%;max-width:unset!important}.form-generator-shrtcode{width:100%;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto}@media (min-width:576px){.form-generator-shrtcode{max-width:540px}}@media (min-width:768px){.form-generator-shrtcode{max-width:720px}}@media (min-width:992px){.form-generator-shrtcode{max-width:800px}}@media (min-width:1200px){.form-generator-shrtcode{max-width:800px}}</style>
        <div id="shortcodegen" style="display:none;">
            <p></p>
                <div class="form-group shortcodegen-input" >
                    <label for="shortcodegen-input">
                        <span class="label-text">Click here and copy shortcode from field</span>
                        <br/>
                        <input type="text" class="form-field text-copy" id="shortcodegen-input" readonly  placeholder="Click here and copy text for field" />
                    </label>
                </div>
        </div>
        <a href="#TB_inline?&width=600&height=150&inlineId=shortcodegen" class="thickbox callJsshortcodegen"></a>

        <form class="form-generator-shrtcode">
            <h1>List items generator</h1>
            <div class="form-group title_input" >
                <label for="title_input">
                    <span class="label-text">Title</span>
                    <input type="text" id="title_input" name="title" class="form-field"  placeholder="Title" />
                </label>
            </div>
            <div class="form-group typeof_post" >
                <label for="typeof_post">
                    <span class="label-text">Type of post</span>
                    <select name="typeof_post" class="form-field"  id="typeof_post">
                        <option value="">Select post type</option>
                        <option value="post">Post</option>
                    </select>
                </label>
            </div>
            <div class="form-group typedisplay" >
                <label for="typedisplay">
                    <span class="label-text">Type of display</span>
                    <select name="typedisplay" class="form-field"  id="typedisplay">
                        <option displayon="post" value="latest">Latest</option>
                        <option displayon="post" value="categores">Posts in category</option>
                    </select>
                </label>
            </div>
            <div class="form-group limits active">
                <label for="limit">
                <span class="label-text">Limit <small>(-1 no limits)</small></span>
                <input type="text" id="limit" class="form-field" name="limit" placeholder="Limit" value="-1" />
            </label>
            </div>
            <div class="form-group selection_items" >
                <label for="selection_items">
                    <span class="label-text">Select items</span>
                <select name="items" class="form-field"  id="selection_items" multiple def-option-val="" def-option-label="Select items"></select>
            </label>
            </div>
            <?php submit_button('Generate'); ?>
        </form>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
        <script>
            jQuery(function($) {
                var shortcodename = 'listproducts';
                function copytoclipboard(element) {
                    var copyText = element.get(0);
                    copyText.select();
                    copyText.setSelectionRange(0, 99999);
                    document.execCommand("copy");
                }
                $('body').on('click', '.text-copy', function(event) {
                    var href = $(this).val();
                    copytoclipboard($('body').find('#shortcodegen-input'));
                    event.preventDefault();
                    return false;
                });
                $("[name='items']").select2();
                $(document).on('change','[name="typeof_post"]',function (event) {
                   var option_type = $(this).val();
                    $('[name="typedisplay"] [displayon]').css('display','none');
                    $('[name="typedisplay"] [displayon~="'+option_type+'"]').attr('style','');

                });
                $(document).on('click','[type="submit"]',function (event) {
                    regen_shortcode();
                    var title = $('[name="title"]').val();
                    var limit = $('[name="limit"]').val();
                    var items = $('[name="items"]').val();
                    var typeof_post = $('[name="typeof_post"]').val();
                    if(title.trim() == ''){
                        alert('Enter title of section please');
                        return false;
                    }
                    if(typeof_post.trim() == ''){
                        alert('Select type of post');
                        return false;
                    }
                    if($('[name="typedisplay"]').val() != 'latest'){
                        if(items == null){
                            alert('Select items first');
                            return false;
                        }
                    }
                    if($('[name="typedisplay"]').val() == 'latest' || $('[name="typedisplay"]').val() == 'categores'){
                        if(limit.trim() == ''){
                            alert('Enter limit of items');
                            return false;
                        }
                    }
                    document.querySelector('.callJsshortcodegen').click();
                    return false;
                });
                function regen_shortcode(){
                    var info = $('.form-generator-shrtcode').serializeArray();
                    var out_val  = '['+shortcodename;
                    var ob_out = {};
                        $.each(info,function(index,element){
                            if(ob_out[element.name]){
                                if(typeof ob_out[element.name] =='array'){
                                    ob_out[element.name].push(element.value);
                                }else{
                                    var item = ob_out[element.name];
                                    ob_out[element.name] = [item, element.value];
                                }
                            }else{
                                ob_out[element.name] = element.value;
                            }
                        });
                        $.each(ob_out,function(index,element){
                            if(typeof element != 'array'){
                                out_val += " "+index+'="'+element+'"';
                            }
                        });
                        out_val += ']';
                    $('#shortcodegen-input').val(out_val);

                }
                $(document).on('change','[name="typedisplay"]',function (event) {
                    var val = $(this).val();
                    $(".limits").removeClass('active');
                    $(".selection_items").removeClass('active');
                    $("[name='items']").html('');
                        switch(true){
                            case val != 'latest':
                                $.ajax({
                                	url: '/wp-admin/admin-ajax.php',
                                	type: 'POST',
                                	data: {
                                	    'action': 'get_list_'+val
                                    },
                                	dataType: 'json',
                                }).done(function(data) {

                                    $.each(data,function (index, item) {
                                        $("[name='items']")
                                            .append('<option value="'+item.id+'">'+item.title+'</option>');
                                    });
                                    if(data.length > 0){
                                        $(".selection_items").addClass('active');
                                        $("[name='items']").select2('destroy');
                                        $("[name='items']").select2();
                                    }
                                })
                                .fail(function() {
                                	console.log("error");
                                })
                                .always(function() {
                                	console.log("complete");
                                });
                                break;
                            case val == 'latest':
                                regen_shortcode();
                                break;
                        }
                    if(val != 'products'){
                        $(".limits").addClass('active');
                    }
                });
            });
        </script>
    <?php
    }
}
new ListProducts();