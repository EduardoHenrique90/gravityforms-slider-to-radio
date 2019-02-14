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
            <input type="checkbox" id="field_encrypt_value" onclick="SetFieldProperty('isSlider', this.checked == true ? 1 : 0);" /> Exibir como Slider
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
             console.log("sxsssssssssssss", field);
            jQuery('#field_encrypt_value').attr('checked', field.encryptField == true);
            if(field.isSlider == 1){
                jQuery('#field_encrypt_value').prop('checked', true);
            }
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
    if(  $field['isSlider'] == 1 && !is_admin()){
        ?>
        <script type='text/javascript'>
            var sizeRange<?php echo $field['id']?> = [
                <?php  
                    $labelSlider = $field['label'];
                    if($field['type'] = 'radio'){
                        foreach($field['choices'] as $f){
                            echo '
                            "'.$f['text'].',"
                            '.",";                
                        }
                    }
                ?>
            ]
            var valueRange<?php echo $field['id']?> = [
                <?php  
                    $labelSlider = $field['label'];
                    if($field['type'] = 'radio'){
                        foreach($field['choices'] as $f){
                            echo '
                            "'.$f['value'].'"
                            '.",";                
                        }
                    }
                ?>
            ]    

            
            jQuery('#sliderPrice_<?php echo $field['id']?>').html( sizeRange<?php echo $field['id']?> );
            
            jQuery(document).on('input change', '#range-slider_<?php echo $field['id']?>', function() { 
                var v = jQuery(this).val();
                console.log(v);
                console.log('#choice_<?php echo $form['id'].'_'.$field['id'].'_';?>'+v)
                jQuery('#choice_<?php echo $form['id'].'_'.$field['id'].'_';?>'+v).prop('checked', true);
                jQuery('#hidden_value_<?php echo $field['id']?>').val(valueRange<?php echo $field['id']?>[v])
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
                <input type="hidden" name="input_'.$field['id'].'" id="hidden_value_'.$field['id'].'" value="'.$_POST['input_'.$field['id']].'" >
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
                <input type="hidden" name="input_'.$field['id'].'" id="hidden_value_'.$field['id'].'" value="'.$_POST['input_'.$field['id']].'" >
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
        $script = '';
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

add_action( 'gform_post_process', 'post_process_actions', 5, 3 );
function post_process_actions( $form, $v, $source_page_number ){
    // // if($source_page_number == 1){
    //     $_POST['input_1'] = $_POST['input_1'];
    // // }
}


add_filter( 'gform_confirmation', 'alert_user', 10, 4 );

function alert_user( $confirmation, $form, $entry, $ajax ) {
        $feedback = 0;
        $html = '';
        $page = array();
        foreach($form['fields'] as $field ){
            if( count((array)$field['choices']) >= 1){
                foreach((array)$field['choices'] as $choice){
                    if($_POST['input_'.$field['id']] && is_numeric($_POST['input_'.$field['id']])){
                        $feedback = $feedback + $_POST['input_'.$field['id']];
                    }
                    $page[$field['pageNumber']]['result'] = $feedback;
                    $page[$field['pageNumber']]['pagenumber'] = $field['pageNumber'];
                    // $page[$field['pageNumber']]['pagenumber'] = $field['pageNumber'];
                }
            }
        }
        foreach($page as $p){
            switch ($p['pagenumber']) {
                case 2:
                    if ($p['result'] <= 5) {
                        $p['situation'] = "Muito Bom";
                    }
                    if ($p['result'] <= 6 && $p['result'] <= 7) {
                        $p['situation'] = "Bom";
                    }
                    if ($p['result'] <= 8 && $p['result'] <= 9) {
                        $p['situation'] = "Regular";
                    }
                    if ($p['result'] <= 10 && $p['result'] <= 11) {
                        $p['situation'] = "Ruim";
                    }
                    if ($p['result'] >= 12) {
                        $p['situation'] = "Muito Ruim";
                    }
                    $html .= '
                    <table class="flat-table">
                        <tbody>
                            <tr>
                                <th> Questionario - ' .$p['pagenumber'].'</th>
                            </tr>
                            <tr>
                                <td>'. $p['result'] .'</td>
                            </tr>
                            <tr>
                                <td>'. $p['situation'] .'</td>
                            </tr>
                        </tbody>
                            </table>
                    ';
                    break;
                case 3:
                    if ($p['result'] <= 2 ) {
                        $p['situation'] = "Muito Bom";
                    }
                    if ($p['result'] <= 3 && $p['result'] <= 4) {
                        $p['situation'] = "Bom";
                    }
                    if ($p['result'] <= 5 && $p['result'] <= 6) {
                        $p['situation'] = "Regular";
                    }
                    if ($p['result'] <= 7 && $p['result'] <= 8) {
                        $p['situation'] = "Ruim";
                    }
                    if ($p['result'] >= 9) {
                        $p['situation'] = "Muito Ruim";
                    }
                    $html .= '
                    <table class="flat-table">
                        <tbody>
                            <tr>
                                <th> Questionario - ' .$p['pagenumber'].'</th>
                            </tr>
                            <tr>
                                <td>'. $p['result'] .'</td>
                            </tr>
                            <tr>
                                <td>'. $p['situation'] .'</td>
                            </tr>
                        </tbody>
                            </table>
                    ';
                    break;
                case 4:
                    if ($p['result'] <= 6) {
                        $p['situation'] = "Muito Bom";
                    }
                    if ($p['result'] <= 7 && $p['result'] <= 9) {
                        $p['situation'] = "Bom";
                    }
                    if ($p['result'] <= 10 && $p['result'] <= 12) {
                        $p['situation'] = "Regular";
                    }
                    if ($p['result'] <= 13 && $p['result'] <= 15) {
                        $p['situation'] = "Ruim";
                    }
                    if ($p['result'] >= 15) {
                        $p['situation'] = "Muito Ruim";
                    }
                    $html .= '
                    <table class="flat-table">
                        <tbody>
                            <tr>
                                <th> Questionario - ' .$p['pagenumber'].'</th>
                            </tr>
                            <tr>
                                <td>'. $p['result'] .'</td>
                            </tr>
                            <tr>
                                <td>'. $p['situation'] .'</td>
                            </tr>
                        </tbody>
                            </table>
                    ';
                    break;
                case 5:
                    if ($p['result'] <= 15) {
                        $p['situation'] = "Muito Bom";
                    }
                    if ($p['result'] <= 16 && $p['result'] <= 22) {
                        $p['situation'] = "Bom";
                    }
                    if ($p['result'] <= 23 && $p['result'] <= 29) {
                        $p['situation'] = "Regular";
                    }
                    if ($p['result'] <= 30 && $p['result'] <= 36) {
                        $p['situation'] = "Ruim";
                    }
                    if ($p['result'] >= 37) {
                        $p['situation'] = "Muito Ruim";
                    }
                    $html .= '
                    <table class="flat-table">
                        <tbody>
                            <tr>
                                <th> Questionario - ' .$p['pagenumber'].'</th>
                            </tr>
                            <tr>
                                <td>'. $p['result'] .'</td>
                            </tr>
                            <tr>
                                <td>'. $p['situation'] .'</td>
                            </tr>
                        </tbody>
                            </table>
                    ';
                    break;
                case 6:
                    if ($p['result'] <= 14) {
                        $p['situation'] = "Muito Bom";
                    }
                    if ($p['result'] <= 15 && $p['result'] <= 18) {
                        $p['situation'] = "Bom";
                    }
                    if ($p['result'] <= 19 && $p['result'] <= 22) {
                        $p['situation'] = "Regular";
                    }
                    if ($p['result'] <= 23 && $p['result'] <= 26) {
                        $p['situation'] = "Ruim";
                    }
                    if ($p['result'] >= 27) {
                        $p['situation'] = "Muito Ruim";
                    }
                    $html .= '
                    <table class="flat-table">
                        <tbody>
                            <tr>
                                <th> Questionario - ' .$p['pagenumber'].'</th>
                            </tr>
                            <tr>
                                <td>'. $p['result'] .'</td>
                            </tr>
                            <tr>
                                <td>'. $p['situation'] .'</td>
                            </tr>
                        </tbody>
                            </table>
                    ';
                    break;
                case 7:
                    if ($p['result'] <= 5) {
                        $p['situation'] = "Muito Bom";
                    }
                    if ($p['result'] <= 6 && $p['result'] <= 7) {
                        $p['situation'] = "Bom";
                    }
                    if ($p['result'] <= 8 && $p['result'] <= 9) {
                        $p['situation'] = "Regular";
                    }
                    if ($p['result'] <= 10 && $p['result'] <= 11) {
                        $p['situation'] = "Ruim";
                    }
                    if ($p['result'] >= 12) {
                        $p['situation'] = "Muito Ruim";
                    }
                    $html .= '
                    <table class="flat-table">
                        <tbody>
                            <tr>
                                <th> Questionario - ' .$p['pagenumber'].'</th>
                            </tr>
                            <tr>
                                <td>'. $p['result'] .'</td>
                            </tr>
                            <tr>
                                <td>'. $p['situation'] .'</td>
                            </tr>
                        </tbody>
                            </table>
                    ';
                    break;
            }
            

        }

    $confirmation = $html;
    return $confirmation; 
}


