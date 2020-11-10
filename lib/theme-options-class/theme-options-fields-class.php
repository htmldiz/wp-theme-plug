<?php
class ThemeOptionsFieldsClass{
    public $returnHTML = '';
    function __construct(){
        add_action('display_theme_option_finish',array($this,'display_theme_option'),10);
        add_filter('theme_option_get_value',array($this,'theme_option_get_value'),10,2);
        add_filter('theme_option_get_label',array($this,'theme_option_get_label'),10,3);
        add_action('theme_option_field_text',array($this,'display_field_text'),10,1);
        add_action('theme_option_field_textarea',array($this,'display_field_textarea'),10,1);
//        add_action('display_theme_option_field_select',array($this,'display_field_select'),10,2);
    }
    function display_theme_option(){
        echo $this->returnHTML;
    }
    function theme_option_get_value( $return, $data){
        $value = (isset($data["value"]) ? $data["value"] : (isset($data["std"]) ? $data["std"] : ''));
        return !empty($value) ? $value : $return;
    }

    function theme_option_get_label($return, $name, $data){
        $label_gen         = apply_filters( 'get_themeoption_label', $name );
        $label = (isset($data['label']) ? $data['label'] : $label_gen);
        return !empty($label) ? $label : $return;
    }
    function display_field_text($data){
        $label  = $data['name'];
        $name   = $data['id'];
        $value  = apply_filters('theme_option_get_value','',$data);
        $id_gen = 'themesettings-'.$name;
        $label  = apply_filters('theme_option_get_label','',$name,$data);
        $returnHTML = '';
        $returnHTML .= '<label for="'.$id_gen.'"><span class="label">'.$label.'</span>';
        $returnHTML .= '<input type="text" id="'.$id_gen.'" name="themesettings['.$name.']" placeholder="'.$data["placeholder"].'"  value="'.$value.'" />';
        $returnHTML .= '</label>';
        $this->returnHTML .= $returnHTML;
    }
    function display_field_textarea($data){
        $label  = $data['name'];
        $name   = $data['id'];
        $value  = apply_filters('theme_option_get_value','',$data);
        $id_gen = 'themesettings-'.$name;
        $label  = apply_filters('theme_option_get_label','',$name,$data);
        $returnHTML = '';
        $returnHTML .= '<label for="'.$id_gen.'"><span class="label">'.$label.'</span>';
        $returnHTML .= '<textarea type="text" id="'.$id_gen.'" name="themesettings['.$name.']" placeholder="'.$data["placeholder"].'"  >'.$value.'</textarea>';
        $returnHTML .= '</label>';
        $this->returnHTML .= $returnHTML;
    }

}

