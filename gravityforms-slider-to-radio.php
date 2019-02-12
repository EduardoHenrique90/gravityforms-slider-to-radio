<?php
/*
Plugin Name: GravityForms - Set Radio to Slider 
Description: For the administrative area it is possible to format a radio field for slider
Author: Eduardo Henrique Machado de Araujo
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// If Gravity Forms cannot be found, exit.
if ( ! class_exists( 'GFForms' ) ) {
    echo 'Gravity forms não está instalado, antes de ativar esse plugin instale o Gravityforms e tente novamente';
	die();
}

/**
 * Adds according to the position specified in if, an html 
 * snippet in the field editor of Gravity forms
 */
add_action( 'gform_field_standard_settings', 'my_standard_settings', 10, 2 );
function my_standard_settings( $position, $form_id ) {
    if ( $position == 25 ) {
        ?>
        <li class="encrypt_setting field_setting">
            <label for="field_admin_label">
                <?php esc_html_e( 'Sliders', 'gravityforms' ); ?>
                <?php gform_tooltip( 'some_new_feature' ) ?>
            </label>
            <input type="checkbox" id="field_encrypt_value" onclick="SetFieldProperty('isSlider', this.checked);" /> Exibir como Slider
        </li>
        <?php
    }
}

/**
 * Function that performs an event in this case javascript while holding the form creator
 * with the custom chekbox to transform radio into slider
 */
add_action( 'gform_editor_js', 'editor_script' );
function editor_script(){
    ?>
    <script type='text/javascript'>
        //adding setting to fields of type "text"
        fieldSettings.radio += ', .encrypt_setting';
        //binding to the load field settings event to initialize the checkbox
        jQuery(document).on('gform_load_field_settings', function(event, field, form){
            // alert('sdd');
            jQuery('#field_encrypt_value').attr('checked', field.encryptField == true);
        });
    </script>
    <?php
}

/**
 * Tooltip chekbox slider to radio 
 */
add_filter( 'gform_tooltips', 'add_encryption_tooltips' );
function add_encryption_tooltips( $tooltips ) {
    $tooltips['some_new_feature'] = "<h6>Slider</h6>use esse campo para transformar radio em Slider";
    return $tooltips;
}   

/**
 * Function takes values ​​from the radio form that has been marked to be a slider and transforms
 * into a slice of html slider cusotmizado and inserts inside the html from where the radio should appear
 */
add_filter( 'gform_field_container', 'gform_format', 10, 6 );
function gform_format( $field_container, $field, $form, $css_class, $style, $field_content ) {
    
    if($field['isSlider'] == 1 && !is_admin()){
        ?>
        <script type='text/javascript'>
            var sizeRange<?php echo $field['id']?> = [
                <?php  
                    $labelSlider = $field['label'];
                    if($field['type'] = 'radio'){
                        foreach($field['choices'] as $f){
                            echo '"'.$f['text'].'"'.",";                
                        }
                    }
                ?>
            ]   
            jQuery('#sliderPrice_<?php echo $field['id']?>').html( sizeRange<?php echo $field['id']?> );
            
            jQuery(document).on('input change', '#range-slider_<?php echo $field['id']?>', function() { 
                var v = jQuery(this).val();
                console.log(v);
                jQuery('#choice_<?php echo $form_id.'_'.$field['id'].'_';?>'+v).prop('checked', true);
                jQuery('#hidden_value_<?php echo $field['id']?>').val(sizeRange<?php echo $field['id']?>[v])
                jQuery('#sliderStatus_<?php echo $field['id']?>').html( jQuery(this).val() );
                jQuery('#sliderPrice_<?php echo $field['id']?>').html( sizeRange<?php echo $field['id']?>[v] );
            });
            
            jQuery("#range-slider_<?php echo $field['id']?>").on("mousedown", function() {
                jQuery(this).removeClass().addClass("thumb-down");
                jQuery(this).addClass("hover-ring");
            });
            
            jQuery("#range-slider_<?php echo $field['id']?>").on("mouseup", function() {  
                jQuery(this).addClass("thumb-up");
                jQuery(this).addClass("hover-ring-out");
            });
        
        </script>
        <?php    
            if($field['failed_validation']  ){
                $html = '
                <div class="custom-error product-range-wrapper " style="color: #790000!important;
                background-color: rgba(255,223,224,.25);
                margin-bottom: 6px!important;
                border-top: 1px solid #C89797;
                border-bottom: 1px solid #C89797;
                padding-bottom: 6px;
                padding-top: 8px;
                ">
                <b>'. $labelSlider. '</b>
                <div id="slider_count-m"><span id="sliderPrice_'.$field['id'].'">Nenhuma opção selecionada</span></div>
                <div class="range-slider-block">
                    <input type="range" id="range-slider_'.$field['id'].'" value="0.0" min="0" max="'.(count($field['choices'])-1).'" step="1" />
                </div>
                </div>
                <div id="slider_count"><span id="sliderPrice_'.$field['id'].'"></span></div>
                <input type="hidden" name="input_'.$field['id'].'" id="hidden_value_'.$field['id'].'" value="" >
                <div class="gfield_description validation_message custom-error" >Pelo menos um campo deve ser preenchido</div>
                ';
            }else{
                $html = '
                <div class="product-range-wrapper">
                <b>'. $labelSlider. '</b>
                <div id="slider_count-m"><span id="sliderPrice_'.$field['id'].'">Nenhuma opção selecionada</span></div>
                <div class="range-slider-block">
                    <input type="range" id="range-slider_'.$field['id'].'" value="0.0" min="0" max="'.(count($field['choices'])-1).'" step="1" />
                </div>
                </div>
                <div id="slider_count"><span id="sliderPrice_'.$field['id'].'"></span></div>
                <input type="hidden" name="input_'.$field['id'].'" id="hidden_value_'.$field['id'].'" value="" >
                ';
            }
            

            return $html;      

    }else{
        return $field_container;
    }
                
}



/**
 * Function hidden radio options in front view
 */
add_action( 'gform_register_init_scripts', 'radio_to_slider',10 );
function radio_to_slider($form) {
    $script;
    foreach($form['fields'] as $field){
        if($field['isSlider'] == 1){
            $script .= '
                jQuery("#field_'.$form['id'].'_'.$field['id'].'").css({"visibility":"visible","position":"absolute","z-index":"-1"}); 
            ';
        }
    }
    GFFormDisplay::add_init_script( $form['id'], 'format_money', GFFormDisplay::ON_PAGE_RENDER, $script );
}

/**
 * Load scripts from custom style slider
 */
add_action('wp_enqueue_scripts','slider_scripts');
function slider_scripts( ) {
    wp_enqueue_style( "slider-css",plugins_url()."/gravityforms-slider-to-radio/gravity_slider/css.css" );
    wp_enqueue_script( "slider-js",plugins_url()."/gravityforms-slider-to-radio/gravity_slider/main.js", array('jquery') );
}

// add_filter( 'gform_validation', 'custom_validation' );
// function custom_validation( $validation_result ) {
    
//     $form = $validation_result['form'];

//     foreach($form['fields'] as &$field){
//         $current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' . $form['id'] ) : 1;
//         $field_page = $field->pageNumber;
//         if ( $field_page != $current_page  ) {
//             continue;
//         }
//         $field_value = rgpost( "input_{$field['id']}" );
//         $is_valid = is_vin( $field_value );
//         echo $is_valid;
//         die();
//         if ( $is_valid ) {
//             echo 'cu';
//             die();
//             continue;
//         }else{
//             $validation_result['is_valid'] = false;
//             $field->failed_validation = true;
//             $field->validation_message = 'The VIN number you have entered is not valid.';
//             
//             $validation_result['form'] = $form;
//         }


        
//     }
//     return $validation_result;  
	
// }

