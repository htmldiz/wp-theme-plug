My Wordpress Meta box Class
=======================
Contributors: bainternet
Requires at least: 3.1
Tested up to: 3.7.1

# How add a WordPress MetaBox
```php
/*
* configure meta box
*/
$config = array(
	'id' => 'demo_meta_box',             // meta box id, unique per meta box
	'title' => 'Demo Meta Box',      // meta box title
	'pages' => array('post', 'page'),    // post types, accept custom post types as well, default is array('post'); optional
	'context' => 'normal',               // where the meta box appear: normal (default), advanced, side; optional
	'priority' => 'high',                // order of meta box: high (default), low; optional
	'page_template' => array('front-page.php'), // use page template for example front-page.php
	'fields' => array(),                 // list of meta fields (can be added by field arrays) or using the class's functions
	'local_images' => false,             // Use local or hosted images (meta box images for add/remove)
	'use_with_theme' => false            //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
);
$config = array(
	'id' => 'demo_meta_box',
	'title' => 'Demo Meta Box',
	'pages' => array('post', 'page'),
	'context' => 'normal',
	'priority' => 'high',
	'page_template' => array('front-page.php')
	'fields' => array(),
	'local_images' => false,
	'use_with_theme' => false
);
/*
* Initiate your meta box
*/
$my_meta = new AT_Meta_Box($config);
/*
* Add fields to your meta box
*/

//text field
$my_meta->addText('text_field_id',array('name'=> 'My Text '));
//textarea field
$my_meta->addTextarea('textarea_field_id',array('name'=> 'My Textarea '));
//checkbox field
$my_meta->addCheckbox('checkbox_field_id',array('name'=> 'My Checkbox '));
//select field
$my_meta->addSelect('select_field_id',array('selectkey1'=>'Select Value1','selectkey2'=>'Select Value2'),array('name'=> 'My select ', 'std'=> array('selectkey2')));
//radio field
$my_meta->addRadio('radio_field_id',array('radiokey1'=>'Radio Value1','radiokey2'=>'Radio Value2'),array('name'=> 'My Radio Filed', 'std'=> array('radionkey2')));
//date field
$my_meta->addDate('date_field_id',array('name'=> 'My Date '));
//Time field
$my_meta->addTime('time_field_id',array('name'=> 'My Time '));
//Color field
$my_meta->addColor('color_field_id',array('name'=> 'My Color '));
//Image field
$my_meta->addImage('image_field_id',array('name'=> 'My Image '));
//file upload field
$my_meta->addFile('file_field_id',array('name'=> 'My File '));
//wysiwyg field
$my_meta->addWysiwyg('wysiwyg_field_id',array('name'=> 'My wysiwyg Editor '));
//taxonomy field
$my_meta->addTaxonomy('taxonomy_field_id',array('taxonomy' => 'category'),array('name'=> 'My Taxonomy '));
//posts field
$my_meta->addPosts('posts_field_id',array('post_type' => 'post'),array('name'=> 'My Posts '));

/*
* To Create a reapeater Block first create an array of fields
* use the same functions as above but add true as a last param
*/

$repeater_fields[] = $my_meta->addText('re_text_field_id',array('name'=> 'My Text '),true);
$repeater_fields[] = $my_meta->addTextarea('re_textarea_field_id',array('name'=> 'My Textarea '),true);
$repeater_fields[] = $my_meta->addCheckbox('re_checkbox_field_id',array('name'=> 'My Checkbox '),true);
$repeater_fields[] = $my_meta->addImage('image_field_id',array('name'=> 'My Image '),true);

/*
* Then just add the fields to the repeater block
*/
//repeater block

$my_meta->addRepeaterBlock('re_',array('inline' => true, 'name' => 'This is a Repeater Block','fields' => $repeater_fields));

/*
* Don't Forget to Close up the meta box deceleration
*/
//Finish Meta Box Deceleration

$my_meta->Finish();
```
# WP Custom Post Type Class v1.4
```php
$people = new CPT(array(
	'post_type_name' => 'person',
	'singular' => 'Person',
	'plural' => 'People',
	'slug' => 'people'
));
```
The Class uses the WordPress defaults where possible.

# WP Custom Post Type Class that hidden for public
```php
$people = new CPT(
array(
	'post_type_name' => 'person',
	'singular' => 'Person',
	'plural' => 'People',
	'slug' => ''
),
array(
	'publicly_queryable'  => false,
	'show_in_nav_menus' => false,
	'exclude_from_search' => true,
));
```
The Class uses the WordPress defaults where possible.

To override the default options simply pass an array of options as the second parameter. Not all options have to be passed just the ones you want to add/override like so:

```php
$person = new CPT(array(
	'post_type_name' => 'person',
	'singular' => 'Person',
	'plural' => 'People',
	'slug' => 'people'
), array(
	'supports' => array('title', 'editor', 'thumbnail', 'comments')
));
$person->register_taxonomy(array(
	'taxonomy_name' => 'genre',
	'singular' => 'Genre',
	'plural' => 'Genres',
	'slug' => 'genre'
));
$person->menu_icon("dashicons-book-alt");
```

# How add a Gallery

```php
new Gallery_meta_theme_pl(array(
	'post_types'    => array('post'),
	'page_template' => array('front-page.php')
)); // add Gallery to all posts post_type "post"
```


# Tax Meta Class

```php
$config = array(
	'id' => 'demo_meta_box',          // meta box id, unique per meta box
	'title' => 'Demo Meta Box',          // meta box title
	'pages' => array('category'),        // taxonomy name, accept categories, post_tag and custom taxonomies
	'context' => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
	'fields' => array(),            // list of meta fields (can be added by field arrays)
	'local_images' => false,          // Use local or hosted images (meta box images for add/remove)
	'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
);
$config = array(
	'id' => 'demo_meta_box',
	'title' => 'Demo Meta Box',
	'pages' => array('category'),
	'context' => 'normal',
	'fields' => array(),
	'local_images' => false,
	'use_with_theme' => false
);
$my_meta =  new Tax_Meta_Class($config);

  //text field
  $my_meta->addText('text_field_id',array('name'=> __('My Text ','tax-meta'),'desc' => 'this is a field desription'));
  //textarea field
  $my_meta->addTextarea('textarea_field_id',array('name'=> __('My Textarea ','tax-meta')));
  //checkbox field
  $my_meta->addCheckbox('checkbox_field_id',array('name'=> __('My Checkbox ','tax-meta')));
  //select field
  $my_meta->addSelect('select_field_id',array('selectkey1'=>'Select Value1','selectkey2'=>'Select Value2'),array('name'=> __('My select ','tax-meta'), 'std'=> array('selectkey2')));
  //radio field
  $my_meta->addRadio('radio_field_id',array('radiokey1'=>'Radio Value1','radiokey2'=>'Radio Value2'),array('name'=> __('My Radio Filed','tax-meta'), 'std'=> array('radionkey2')));
  //date field
  $my_meta->addDate('date_field_id',array('name'=> __('My Date ','tax-meta')));
  //Time field
  $my_meta->addTime('time_field_id',array('name'=> __('My Time ','tax-meta')));
  //Color field
  $my_meta->addColor('color_field_id',array('name'=> __('My Color ','tax-meta')));
  //Image field
  $my_meta->addImage('image_field_id',array('name'=> __('My Image ','tax-meta')));
  //file upload field
  $my_meta->addFile('file_field_id',array('name'=> __('My File ','tax-meta')));
  //wysiwyg field
  $my_meta->addWysiwyg('wysiwyg_field_id',array('name'=> __('My wysiwyg Editor ','tax-meta')));
  //taxonomy field
  // std is default value
  $my_meta->addTaxonomy('taxonomy_field_id',array('taxonomy' => 'category'),array('name'=> __('My Taxonomy '),'std'=>'Select option'));
  //posts field
  $my_meta->addPosts('posts_field_id',array('args' => array('post_type' => 'page')),array('name'=> __('My Posts ','tax-meta')));
  
  /*
   * To Create a reapeater Block first create an array of fields
   * use the same functions as above but add true as a last param
   */
  
  $repeater_fields[] = $my_meta->addText('re_text_field_id',array('name'=> __('My Text ','tax-meta')),true);
  $repeater_fields[] = $my_meta->addTextarea('re_textarea_field_id',array('name'=> __('My Textarea ','tax-meta')),true);
  $repeater_fields[] = $my_meta->addCheckbox('re_checkbox_field_id',array('name'=> __('My Checkbox ','tax-meta')),true);
  $repeater_fields[] = $my_meta->addImage('image_field_id',array('name'=> __('My Image ','tax-meta')),true);
  
  /*
   * Then just add the fields to the repeater block
   */
  //repeater block
  $my_meta->addRepeaterBlock('re_',array('inline' => true, 'name' => __('This is a Repeater Block','tax-meta'),'fields' => $repeater_fields));
  /*
   * Don't Forget to Close up the meta box decleration
   */
  //Finish Meta Box Decleration
  $my_meta->Finish();
```
# Theme options Class

```php
$config = array(
	'id' => 'demo_meta_box',          // meta box id, unique per meta box
	'title' => 'Demo Meta Box',          // meta box title
	'pages' => array('category'),        // taxonomy name, accept categories, post_tag and custom taxonomies
	'context' => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
	'fields' => array(),            // list of meta fields (can be added by field arrays)
	'local_images' => false,          // Use local or hosted images (meta box images for add/remove)
	'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
);
$config = array(
    'id' => 'theme_settings',
    'title' => 'Theme settings',
    'fields' => array(),
    'local_images' => false,
    'use_with_theme' => false
);
$my_meta =  new AT_ThemeSettings($config);

  //text field
  $my_meta->addText('text_field_id',array('name'=> __('My Text ','tax-meta'),'desc' => 'this is a field desription'));
  //textarea field
  $my_meta->addTextarea('textarea_field_id',array('name'=> __('My Textarea ','tax-meta')));
  //checkbox field
  $my_meta->addCheckbox('checkbox_field_id',array('name'=> __('My Checkbox ','tax-meta')));
  //select field
  $my_meta->addSelect('select_field_id',array('selectkey1'=>'Select Value1','selectkey2'=>'Select Value2'),array('name'=> __('My select ','tax-meta'), 'std'=> array('selectkey2')));
  //radio field
  $my_meta->addRadio('radio_field_id',array('radiokey1'=>'Radio Value1','radiokey2'=>'Radio Value2'),array('name'=> __('My Radio Filed','tax-meta'), 'std'=> array('radionkey2')));
  //date field
  $my_meta->addDate('date_field_id',array('name'=> __('My Date ','tax-meta')));
  //Time field
  $my_meta->addTime('time_field_id',array('name'=> __('My Time ','tax-meta')));
  //Color field
  $my_meta->addColor('color_field_id',array('name'=> __('My Color ','tax-meta')));
  //Image field
  $my_meta->addImage('image_field_id',array('name'=> __('My Image ','tax-meta')));
  //file upload field
  $my_meta->addFile('file_field_id',array('name'=> __('My File ','tax-meta')));
  //wysiwyg field
  $my_meta->addWysiwyg('wysiwyg_field_id',array('name'=> __('My wysiwyg Editor ','tax-meta')));
  //taxonomy field
  // std is default value
  $my_meta->addTaxonomy('taxonomy_field_id',array('taxonomy' => 'category'),array('name'=> __('My Taxonomy '),'std'=>'Select option'));
  //posts field
  $my_meta->addPosts('posts_field_id',array('args' => array('post_type' => 'page')),array('name'=> __('My Posts ','tax-meta')));
  
  /*
   * To Create a reapeater Block first create an array of fields
   * use the same functions as above but add true as a last param
   */
  
  $repeater_fields[] = $my_meta->addText('re_text_field_id',array('name'=> __('My Text ','tax-meta')),true);
  $repeater_fields[] = $my_meta->addTextarea('re_textarea_field_id',array('name'=> __('My Textarea ','tax-meta')),true);
  $repeater_fields[] = $my_meta->addCheckbox('re_checkbox_field_id',array('name'=> __('My Checkbox ','tax-meta')),true);
  $repeater_fields[] = $my_meta->addImage('image_field_id',array('name'=> __('My Image ','tax-meta')),true);
  
  /*
   * Then just add the fields to the repeater block
   */
  //repeater block
  $my_meta->addRepeaterBlock('re_',array('inline' => true, 'name' => __('This is a Repeater Block','tax-meta'),'fields' => $repeater_fields));
  /*
   * Don't Forget to Close up the meta box decleration
   */
  //Finish Meta Box Decleration
  $my_meta->Finish();
```
