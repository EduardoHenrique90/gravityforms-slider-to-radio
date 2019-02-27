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
            <label for="field_init_label">
                <?php esc_html_e( 'Label Inicial', 'gravityforms' ); ?>
                <?php gform_tooltip( 'some_init_label' ) ?>
            </label>
            <input type="text" id="field_initial_value" onblur="SetFieldProperty('initLabel', this.value)" /> 
            <label for="field_end_label">
                <?php esc_html_e( 'Label Final', 'gravityforms' ); ?>
                <?php gform_tooltip( 'some_end_label' ) ?>
            </label>
            <input type="text" id="field_end_value" onblur="SetFieldProperty('endLabel', this.value)" /> 
        </li>
        <li class="encrypt_setting field_setting">
            <label for="field_admin_label">
                <?php esc_html_e( 'Sliders', 'gravityforms' ); ?>
                <?php gform_tooltip( 'some_new_feature' ) ?>
            </label>
            <input type="checkbox" id="field_encrypt_value" onclick="SetFieldProperty('isSlider', this.checked == true ? 1 : 0);" /> Exibir como Slider
        </li>
        <li class="encrypt_setting field_setting">
            <label for="field_admin_label">
                <?php esc_html_e( 'Opções em coluna', 'gravityforms' ); ?>
                <?php gform_tooltip( 'some_line_feature' ) ?>
            </label>
            <input type="checkbox" id="field_inline_value" onclick="SetFieldProperty('isInline', this.checked == true ? 1 : 0);" /> Exibir em coluna
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
        //adding setting to fields of type "checkbox"
        fieldSettings.radio += ', .encrypt_setting';
        //binding to the load field settings event to initialize the checkbox
        jQuery(document).on('gform_load_field_settings', function(event, field, form){
            console.log(field);
            if(field.initLabel != ''){
                jQuery('#field_initial_value').val(field.initLabel)
            }
            if(field.endLabel != ''){
                jQuery('#field_end_value').val(field.endLabel)
            }
            if(field.isSlider == 1){
                jQuery('#field_encrypt_value').attr('checked', field.encryptField == true);
                jQuery('#field_encrypt_value').prop('checked', true);
            }
            if(field.isInline == 1){
                jQuery('#field_inline_value').attr('checked', field.encryptField == true);
                jQuery('#field_inline_value').prop('checked', true);
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
    $tooltips['some_init_label'] = "<h6>Label inicial</h6>se a opção slider estiver marcada esse sera o label inicial do seu campo";
    $tooltips['some_end_label'] = "<h6>Label final</h6>se a opção slider estiver marcada esse sera o label final do seu campo";
    $tooltips['some_new_feature'] = "<h6>Slider</h6>use esse campo para transformar radio em Slider";
    $tooltips['some_line_feature'] = "<h6>Inline</h6>Use esse campo para deixar suas opções de radio em linha";
    return $tooltips;
}   

/**
 * Function takes values ​​from the radio form that has been marked to be a slider and transforms
 * into a slice of html slider cusotmizado and inserts inside the html from where the radio should appear
 */
add_filter( 'gform_field_container', 'gform_format', 10, 6 );
function gform_format( $field_container, $field, $form, $css_class, $style, $field_content ) {
    if( $field['isSlider'] == 1 && !is_admin() && wp_is_mobile()){
        $lis = '';
        foreach($field['choices'] as $f){
            $lis .= '<li></li>';
        }
        $labelSlider = $field['label'];
        $initLabel;    
        $endLabel;    
            if($field['initLabel'] != ''){
                $initLabel = $field['initLabel'];
            }else{
                $initLabel = $field['choices'][0]['text'];
            }

            if($field['endLabel'] != ''){
                $endLabel = $field['endLabel'];
            }else{
                $endLabel = $field['choices'][end(array_keys($field['choices']))]['text'];
            }
        
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
                <div class="range-slider-block">
                <input type="range" id="range-slider_'.$field['id'].'" value="0.0" min="0" max="'.(count($field['choices'])-1).'" step="1" />
                <div class = "label_range">
                <span>'. $initLabel . '</span> <span>'. $endLabel .'</span>
                </div>
                <ul class="range-labels">
                    '.$lis.'
                </ul>
            </div>
            <div id="slider_count-m"><span id="sliderPrice_'.$field['id'].'">Nenhuma opção selecionada</span></div>

                <input type="hidden" name="input_'.$field['id'].'" id="hidden_value_'.$field['id'].'" value="'.$_POST['input_'.$field['id']].'" >
                <div class="gfield_description validation_message custom-error" >Pelo menos um campo deve ser preenchido</div>
                ';
            }else{
                $html = '
                <div class="product-range-wrapper">
                    <b>'. $labelSlider. '</b>
                    <div id="slider_count-m"><span id="sliderPrice_'.$field['id'].'">Nenhuma opção selecionada</span></div>
                    <div class="range-slider-block">
                        <input class="slider" type="range" name="range-slider_'.$field['id'].'" id="range-slider_'.$field['id'].'" value="0.0" min="0" max="'.(count($field['choices'])-1).'" step="1"  />
                        <div class = "label_range">
                        <span>'. $initLabel . '</span> <span>'. $endLabel .'</span>
                        </div>
                        <ul class="range-labels">
                        '.$lis.'
                        </ul>
                    </div>
                </div>

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
add_action( 'gform_register_init_scripts', 'radio_to_slider',20 );
function radio_to_slider($form) {
    $script;
    $choices;
    $values;
    foreach($form['fields'] as $field){
        if( $field['isSlider'] == 1 && !is_admin() && wp_is_mobile()){
        $labelSlider = $field['label'];            // echo json_encode($field);
            $index = 0;
            $choices .= '[';
            $values .= '[';
            foreach($field['choices'] as $f){
                if($index == 0){
                    $choices .=  '"'.$f['text'].'"';                
                    $values .=  '"'.$f['value'].'"';       
                }else{
                    $choices .=  ','.'"'.$f['text'].'"';                
                    $values .=  ','.'"'.$f['value'].'"';   
                }
                $index ++;             
            }
            $choices .= ']';
            $values .= ']';
            $script .= 'radioToSlider('.$form['id'].','.$field['id'].','.$choices.','.$values.');';
        }
        $choices = '';
        $values = '';
    
    }
    GFFormDisplay::add_init_script( $form['id'], 'slider', GFFormDisplay::ON_PAGE_RENDER, $script );
}

/**
 * Load scripts from custom style slider
 */
add_action('wp_enqueue_scripts','slider_scripts');
function slider_scripts( ) {
    wp_enqueue_style( "slider-css",plugins_url()."/gravityforms-slider-to-radio/gravity_slider/css.css" );
    wp_enqueue_script( "slider-js",plugins_url()."/gravityforms-slider-to-radio/gravity_slider/main.js", array('jquery') );
    wp_enqueue_script( "chart-bundle-js",plugins_url()."/gravityforms-slider-to-radio/gravity_slider/Chart.bundle.js", array('jquery') );
    wp_enqueue_script( "radom-color-js",plugins_url()."/gravityforms-slider-to-radio/gravity_slider/randomColor.js", array('jquery') );
    
}

add_action( 'gform_post_process', 'post_process_actions', 1, 3 );
function post_process_actions( $form, $v, $source_page_number ){

    //   echo $form['pagination']['pages'][$source_page_number];

}


add_filter( 'gform_confirmation', 'alert_user', 10, 4 );

function alert_user( $confirmation, $form, $entry, $ajax ) {
        $html = '';
        $tips = '';
        $page = array();
        $results = array();
        // echo json_encode($form['fields']);
        // die();
        foreach($form['fields'] as $field ){
            // $page[$field['pageNumber']]['pagenumber'] = $field['pageNumber'];
            if( count((array)$field['choices']) >= 1){
        
                if(is_numeric($_POST['input_'.$field['id']]) && $field['pageNumber'] >= 2 && $field['pageNumber'] <= 10){
                    switch ($field['pageNumber']) {
                        case 3:
                        $page[0]['pagenumber'] = 3;
                            $page[0]['result'] =  $page[0]['result'] + (float)$_POST['input_'.$field['id']];
                            $results[0] = $results[0] + (float)$_POST['input_'.$field['id']];
                        break;
                        case 4:
                        $page[1]['pagenumber'] = 4;
                            $page[1]['result'] = $page[1]['result'] + (float)$_POST['input_'.$field['id']];
                            $results[1] = $results[1] + (float)$_POST['input_'.$field['id']];
                        break;
                        case 5:
                        $page[2]['pagenumber'] = 5;
                            $page[2]['result'] = $page[2]['result'] + (float)$_POST['input_'.$field['id']];
                            $results[2] = $results[2] + (float)$_POST['input_'.$field['id']];
                        break;
                        case 6:
                        $page[3]['pagenumber'] = 6;
                            $page[3]['result'] = $page[3]['result'] + (float)$_POST['input_'.$field['id']];
                            $results[3] = $results[3] + (float)$_POST['input_'.$field['id']];
                        break;
                        case 7:
                        $page[4]['pagenumber'] = 7;
                            $page[4]['result'] = $page[4]['result'] + (float)$_POST['input_'.$field['id']];
                            $results[4] = $results[4] + (float)$_POST['input_'.$field['id']];
                        break;
                        case 8:
                        $page[5]['pagenumber'] = 8;
                            $page[5]['result'] = $page[5]['result'] + (float)$_POST['input_'.$field['id']];
                            $results[5] = $results[5] + (float)$_POST['input_'.$field['id']];
                        break;
                    }
                }
            }
        }
        ?>
            <script>
                var results = [0, 0, 0, 0, 0, 0];
            </script>
        <?php
        $tips = '
        
            <div class="tip-container">
                <h1>
                Agradecemos pelo preenchimento  do seu Histórico de Saúde.
                Ele será de muita utilidade para orientar as futuras ações de qualidade de vida do Programa VIVAZ.
                </h1>
            </div>
        ';
        foreach($page as $p){
            switch ($p['pagenumber']) {
                case 3:
                $tips .= '
                <li> 
                <h2>Saúde do Homem e Mulher</h2>';
                    if ($p['result'] <= 5) {
                        $p['situation'] = "Muito Bom";
                        $tips .= '
                            <span>Muito Bom: </span>
                            Parabéns! Você se preocupa com sua saúde e previne o risco de doenças. Continue assim.
                            Os exames preventivos é a melhor forma de diagnosticar precocemente algumas doenças, aumentando significativamente as chances de cura quando detectadas logo no início. Conheça os principais exames preventivos:
                            - PSA e toque retal para pesquisa de câncer de próstata. Recomendado a todos os homens acima de 50 anos, anualmente.
                            - Exame de sangue oculto nas fezes para pesquisa de Câncer colorretal. Indicado 1 vez ao ano.
                            </li>';
                    }
                    if ($p['result'] >= 6 && $p['result'] <= 7) {
                        $p['situation'] = "Bom";
                        $tips .= '
                        <li> 
                        <span>Bom: </span>
                        Está quase lá, falta só o último passo. Pesquise mais sobre os temas de saúde que te deixam inseguro.
                        Os exames preventivos é a melhor forma de diagnosticar precocemente algumas doenças, aumentando significativamente as chances de cura quando detectadas logo no início. Conheça os principais exames preventivos:
                        - PSA e toque retal para pesquisa de câncer de próstata. Recomendado a todos os homens acima de 50 anos, anualmente.
                        - Exame de sangue oculto nas fezes para pesquisa de Câncer colorretal. Indicado 1 vez ao ano.
                        </li>';

                    }
                    if ($p['result'] >= 8 && $p['result'] <= 9) {
                        $p['situation'] = "Regular";
                        $tips .= '
                        <li> 
                        <span>Regular: </span>
                        Você está no caminho certo, mas ainda precisa melhorar.
 
                        Os exames preventivos é a melhor forma de diagnosticar precocemente algumas doenças, aumentando significativamente as chances de cura quando detectadas logo no início. Conheça os principais exames preventivos:
                        - PSA e toque retal para pesquisa de câncer de próstata. Recomendado a todos os homens acima de 50 anos, anualmente.
                        - Exame de sangue oculto nas fezes para pesquisa de Câncer colorretal. Indicado 1 vez ao ano.
                        </li>';

                    }
                    if ($p['result'] >= 10 && $p['result'] <= 11) {
                        $p['situation'] = "Ruim";
                        $tips .= '
                        <li> 
                        <span>Ruim: </span>
                        Os exames preventivos é a melhor forma de diagnosticar precocemente algumas doenças, aumentando significativamente as chances de cura quando detectadas logo no início. Conheça os principais exames preventivos:
                            - PSA e toque retal para pesquisa de câncer de próstata. Recomendado a todos os homens acima de 50 anos, anualmente.
                            - Exame de sangue oculto nas fezes para pesquisa de Câncer colorretal. Indicado 1 vez ao ano.
                        </li>';
                    }
                    if ($p['result'] >= 12) {
                        $p['situation'] = "Muito Ruim";
                        $tips .= '
                        <li> 
                        <span>Muito Ruim:</span>
                        Os exames preventivos é a melhor forma de diagnosticar precocemente algumas doenças, aumentando significativamente as chances de cura quando detectadas logo no início. Conheça os principais exames preventivos:
                            - PSA e toque retal para pesquisa de câncer de próstata. Recomendado a todos os homens acima de 50 anos, anualmente.
                            - Exame de sangue oculto nas fezes para pesquisa de Câncer colorretal. Indicado 1 vez ao ano.
                        </li>';
                    }
                    ?><script>
                        results[0] = <?php echo $p['result'] ?>;
                        console.log(results)
                    </script><?php
                    break;
                case 4:
                $tips .= '
                <li> 
                <h2>Saúde Bucal</h2>';
                    if ($p['result'] <= 2 ) {
                        $p['situation'] = "Muito Bom";
                        $tips .= '
     
                        <span>Muito Bom: </span>
                        A sua higienização oral está perfeita. Continue assim, seu sorriso agradecerá. Basta continuar nesse ritmo que, dificilmente, terá algum tipo de problema oral como cárie e doença gengival.
                        </li>';
                    }
                    if ($p['result'] >= 3 && $p['result'] <= 4) {
                        $p['situation'] = "Bom";
                        $tips .= '
                        <li> 
                        <span>Bom: </span>

                        Muita gente não liga para a saúde dos dentes, mas ela é tão essencial quanto a do organismo. A boca é porta de entrada para bactérias que podem provocar doenças, daí a importância de conservá-la limpa. Coloque na agenda as visitas semestrais ao dentista e desligue o piloto automático na hora de escovar os dentes, pois a tarefa precisa ser feita com calma.
                        </li>';
                    }
                    if ($p['result'] >= 5 && $p['result'] <= 6) {
                        $p['situation'] = "Regular";
                        $tips .= '
                        <li> 
                        <span> Regular: </span>

                        Você se preocupa com a saúde bucal, mas ainda tem espaço para melhorar. Lembre-se sempre de escovar os dentes após todas as refeições. Não adianta escovar só de manhã ou à noite.  Além disso, não esqueça o fio dental, e fique atento à saúde de sua gengiva.
                        </li>';
                    }
                    if ($p['result'] >= 7 && $p['result'] <= 8) {
                        $p['situation'] = "Ruim";
                        $tips .= '
                        <li> 
                        <span> Ruim: </span>
                        Sua preocupação com a saúde bucal está deixando a desejar, alguns hábitos devem ser mudados imediatamente. Você já está em risco de ter cáries e gengivite. Se tiver dúvidas, procure um dentista.
                        </li>';
                    }
                    if ($p['result'] >= 9) {
                        $p['situation'] = "Muito Ruim";
                        $tips .= '
                        <li> 
                        <span> Muito Ruim: </span>
                        Sua saúde bucal pode estar em risco. Você deve procurar o quanto antes um profissional especializado para a realização de uma orientação mais detalhada, além, da utilização de produtos mais adequados para cada caso como escovas dentais ultramacias e com uma grande quantidade de cerdas.
                        </li>';
                    }
                    ?><script>
                        results[1] = <?php echo $p['result'] ?>;
                        console.log(results)
                    </script><?php
                    break;
                case 5:
                $tips .= '
                <li> 
                <h2>Saúde do Coração</h2>';
                    if ($p['result'] <= 6) {
                        $p['situation'] = "Muito Bom";
                        $tips .= '
    
                        <span>Muito Bom: </span>
                        Parabéns! Você se leva à sério a saúde do seu coração: evita fumar e beber, e possui um histórico de saúde invejável.
                        </li>';
                    }
                    if ($p['result'] >= 7 && $p['result'] <= 9) {
                        $p['situation'] = "Bom";
                        $tips .= '
                        <li> 
                        <span>Bom: </span>
                        Você se preocupa com a saúde do seu coração e leva uma vida saudável. Só às vezes se deixa levar pelo mau hábito, seja o fumo, a bebida ou sedentarismo. Se segure um pouco melhor nestas horas e seu coração ficará mais forte ainda.
                        </li>';
                    }
                    if ($p['result'] >= 10 && $p['result'] <= 12) {
                        $p['situation'] = "Regular";
                        $tips .= '
                        <li> 
                        <span> Regular: </span>
                        Você está em cima do muro com relação aos seus hábitos de saúde do coração. Seja pelo fumo, pela bebida, pelo sedentarismo, é importante você perceber qual área você está mais negativo e focar em estratégias para mudá-la.
                        </li>';
                    }
                    if ($p['result'] >= 13 && $p['result'] <= 15) {
                        $p['situation'] = "Ruim";
                        $tips .= '
                        <li> 
                        <span> Ruim: </span>

                        Seus hábitos no dia-a-dia, combinados com seu histórico de saúde, podem resultar em problemas mais sérios no futuro para seu coração. Uma força de vontade maior é importante no seu caso para voltar-se a uma vida mais saudável.
                        </li>';
                    }
                    if ($p['result'] >= 15) {
                        $p['situation'] = "Muito Ruim";
                        $tips .= '
                        <li> 
                        <span> Muito Ruim: </span>

                        Você pode estar em uma situação de risco, se não hoje, talvez no futuro. Não fumar, não beber, praticar atividades físicas, tudo isso ajuda a saúde do seu coração e não é nenhum segredo. Tente levar uma vida mais ativa e saudável, senão as consequências poderão ser gravíssimas. Busque ajuda de um profissional.
                        </li>';                        
                    }
                    ?><script>
                        results[2] = <?php echo $p['result'] ?>;
                        console.log(results)
                    </script><?php
                    break;
                case 6:
                $tips .= '
                <li> 
                <h2>Nutrição</h2>';
                    if ($p['result'] <= 15) {
                        $p['situation'] = "Muito Bom";
                        $tips .= '
                        <span>Muito Bom: </span>

                        Continue assim! Você está a par do que é considerado uma alimentação saudável e segue essas recomendações.
                        </li>';    
                    }
                    if ($p['result'] >= 16 && $p['result'] <= 22) {
                        $p['situation'] = "Bom";
                        $tips .= '
                        <li> 
                        <span>Bom: </span>

                        Quase lá! Falta pouco para conseguir um excelente hábito de alimentação. Faça uma autoanálise de sua alimentação e foque no hábito que ainda precisa melhorar.
                        </li>';    
                    }
                    if ($p['result'] >= 23 && $p['result'] <= 29) {
                        $p['situation'] = "Regular";
                        $tips .= '
                        <li> 
                        <span> Regular: </span>

                        Sua alimentação tem pontos bons e ruins. Verifique no questionário qual é o ponto mais grave e foque no hábito que ainda precisa melhorar.
                        </li>';  
                    }
                    if ($p['result'] >= 30 && $p['result'] <= 36) {
                        $p['situation'] = "Ruim";
                        $tips .= '
                        <li> 
                        <span> Ruim: </span>

                        Você não está se alimentando muito bem. Não há perigo em comer o que gosta de vez em quando, o que não pode é extrapolar todos os dias. Lembre-se de comer frutas, verduras e legumes, prefira carnes magras, e evite exagerar nos doces. Além disso, lembre-se de comer de 3 em 3 horas e não se esqueça de beber, em média, 2 litros de água por dia.
                        </li>';
                    }
                    if ($p['result'] >= 37) {
                        $p['situation'] = "Muito Ruim";
                        $tips .= '
                        <li> 
                        <span> Muito Ruim: </span>

                        Sua alimentação não é a das melhores e isto pode trazer consequências graves para sua saúde. Veja se está se alimentando somente de fast foods, comidas prontas e frituras. Beba bastante água, evite refrigerantes e sucos industrializados. Busque a ajuda de um nutricionista para melhorar sua alimentação.
                        </li>';
                    }
                    ?><script>
                        results[3] = <?php echo $p['result'] ?>;
                        console.log(results)
                    </script><?php
                    break;
                case 7:
                $tips .= '
                <li> 
                <h2>Qualidade do Sono</h2>';
                    if ($p['result'] <= 14) {
                        $p['situation'] = "Muito Bom";
                        $tips .= '
                        <span>Muito Bom: </span>
                        Você está certo. Muitos esquecem de levar em consideração o sono no dia-a-dia, e acabam sendo afetados negativamente por isso, mas não você. Seu corpo agradece.
                        </li>';
                    }
                    if ($p['result'] >= 15 && $p['result'] <= 18) {
                        $p['situation'] = "Bom";
                        $tips .= '
                        <li> 
                        <span>Bom: </span>
                        De vez em quando você deixa faltar algumas horas de descanso e seu corpo pode estar querendo te avisar isso. Faça um esforço a mais, vá dormir um pouco mais cedo e relaxe. Lembre-se que a qualidade do sono é tão importante quanto a duração.
                        </li>';
                    }
                    if ($p['result'] >= 19 && $p['result'] <= 22) {
                        $p['situation'] = "Regular";
                        $tips .= '
                        <li> 
                        <span> Regular: </span>
                        A qualidade do seu sono está um pouco a desejar. O seu corpo deve estar bem cansado e está te avisando isso de diversas maneiras: aumento do estresse, sonolência durante o dia, dificuldade de concentração, etc. Se programe para aumentar o número de horas dormidas e trate o tempo de sono como um tempo sagrado: apague as luzes, silêncio, e nada de dormir picadinho. Relaxe. Senão os problemas de saúde vão se acumulando.
                        </li>';
                    }
                    if ($p['result'] >= 23 && $p['result'] <= 26) {
                        $p['situation'] = "Ruim";
                        $tips .= '
                        <li> 
                        <span> Ruim: </span>
                        Uma boa noite de sono é tão importante para saúde quanto a prática de atividades físicas e alimentação saudável. Procure sempre dormir na mesma hora, escureça o máximo que puder o quarto, reduza os ruídos, evite trabalhar ou praticar atividades estressantes no mesmo local onde você costuma dormir. Desta forma, seu sono poderá ser mais prazeroso e contribuir para uma maior qualidade de vida.
                        </li>';
                    }
                    if ($p['result'] >= 27) {
                        $p['situation'] = "Muito Ruim";
                        $tips .= '
                        <li> 
                        <span> Muito Ruim: </span>
                        Que tal uma consulta com um médico para melhorar o seu descanso noturno e render mais nas atividades cotidianas?  Melhorar o sono é fundamental para não ter problemas futuros.
                        </li>';
                    }
                    ?><script>
                    results[4] = <?php echo $p['result'] ?>;
                    console.log(results)
                </script><?php
                    break;
                case 8:
                $tips .= '
                <li> 
                <h2>Saúde Emocional</h2>';
                    if ($p['result'] <= 5) {
                        $p['situation'] = "Muito Bom";
                        $tips .= '

                        <span>Muito Bom: </span>

                        O estresse não está te afetando, parabéns! Não quer dizer que não tem problemas no dia-a-dia, mas sim que está sabendo leva-los com sabedoria e tranquilidade para resolvê-los.
                        </li>';
                    }
                    if ($p['result'] >= 6 && $p['result'] <= 7) {
                        $p['situation'] = "Bom";
                        $tips .= '
                        <li>
                        <span>Bom: </span>
 
                        Você consegue levar a maioria das dificuldades do dia-a-dia com tranquilidade. Falta alguma coisa a mais. Não esqueça de reservar um tempo para você, afinal sua saúde é muito importante. Mantenha um hobby, descanse.
                        </li>';
                    }
                    if ($p['result'] >= 8 && $p['result'] <= 9) {
                        $p['situation'] = "Regular";
                        $tips .= '
                        <li> 
                        <span> Regular: </span>

                        A qualidade do seu sono está um pouco a desejar. Ou você não tem dormido durante um tempo suficiente, ou algo está atrapalhando o seu sono. Conforme esta defasagem for continuando seu corpo vai começar a reclamar mais e mais, já que o cansaço vai se acumulando. Reserve um tempo maior para seu descanso.
                        </li>';
                    }
                    if ($p['result'] >= 10 && $p['result'] <= 11) {
                        $p['situation'] = "Ruim";
                        $tips .= '
                        <li> 
                        <span> Ruim: </span>

                        Os problemas do dia-a-dia estão tendo um impacto negativo na sua qualidade de vida, o que pode acabar por piorar ainda mais a situação já que o estresse piora o caso. É difícil separar um dia para relaxar, mas tente separar pelo menos uns minutos, e não esqueça da importância de pedir ajuda.
                        </li>';
                    }
                    if ($p['result'] >= 12) {
                        $p['situation'] = "Muito Ruim";
                        $tips .= '
                        <li> 
                        <span> Muito Ruim: </span>

                        Você está se sentindo sobrecarregado com as dificuldades do dia-a-dia e isto está afetando sua saúde. Pare um pouco para ver o que está acontecendo, qual o motivo maior do estresse. Às vezes surgem problemas enormes que não temos como evitar, mas o importante é sempre lembrar que ficar se preocupando não vai ajudar em nada. Respire fundo e procure reservar um tempo para si mesmo e recuperar sua disposição. E, não a nada de errado em pedir ajuda, se precisar. Uma hora o problema passa.
                        </li>';
                    }
                    ?><script>
                    results[5] = <?php echo $p['result'] ?>;
                    console.log(results)
                </script><?php
                    break;
            }
        }
        $max =  max($results);

        $html = '<div class="row final_section">
            <div id="chart-container" class="col-lg-7">
                <canvas id="chart" ></canvas>
                <div class="legend">
                    <ul>
                    <li>SHM - Saude Homem Mulher</li>
                    <li>SB - Saúde Bucal</li>
                    <li>RC - Risco Cardiovascular</li>
                    <li>NT - Nutrição</li>
                    <li>QS - Qualidade do Sono</li>
                    <li>SM - Saúde Mental/ Stress</li>
                    </ul>
                </div>
            </div>
            
            <ul class="col-tips col-lg-5">
            '.$tips.'
            <a class="gform_save_link" data-toggle="modal" href="#finalizar_modal">Entendi! Vamos prosseguir ></a>
            </ul>
            </div>
        ';
    ?>
    <script>

// jQuery( document ).ready(function() {
        setTimeout(function(){
        var canvas = document.getElementById('chart');
        console.log(canvas);
        var context = canvas.getContext('2d');
        var gradientFill = context.createLinearGradient(30, 300, 150, 30);
        gradientFill.addColorStop(0, "rgba(53, 32, 110, 1)");
        gradientFill.addColorStop(1, 'rgba(184, 0, 110, 1)');

        var myChart = new Chart("chart", {
            type: 'radar',
            data: {
                labels: [
                    "SHM " + results[0]+"/21",
                    "SB " + results[1]+"/16",
                    "RC " + results[2]+"/42",
                    "NT " + results[3]+"/30",
                    "QS " + results[4]+"/12",
                    "SM " + results[5]+"/37"
                ],
                datasets: [{
                        label: 'Máxima pontuação',
                        data: [ 
                            42,
                            42,
                            42,
                            42,
                            42,
                            42
                        ],
                        fill: true,
                        backgroundColor: 'f7f6fc',
                        borderWidth: 0,
                        borderColor: "rgba(200,0,0,0)",
                        pointBorderColor: "rgba(0,0,0,0)",
                        pointBackgroundColor: "red",


                    },
                    {
                        label: 'Pontuação',
                        data: results,
                        fill: true,
                        backgroundColor: '#f5de3d',
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                responsiveAnimationDuration: 0,
                aspectRatio: 1,
                legend: {
                    display: false,
                    labels: {
                fontColor: 'rgb(255, 99, 132)'
            }
                },
                // tooltips: {
                //   enabled: true
                // },
                layout: {
                    padding: {
                        left: 15,
                        right: 15,
                        top: 15,
                        bottom: 15
                    }
                },
                scale: {
                    gridLines: {
                        color: 'rgba(77,77,77, 0.7)',
                        lineWidth: 0,
                        drawBorder: false,
                        drawOnChartArea: false,
                        offsetGridLines: true
                    },
                    pointLabels: {
                        fontSize: 13,
                        fontStyle: 'bold',
                        fontColor: '#333'
                    },
                    angleLines: {
                        color: 'rgba(77,77,77, 0.7)',
                        lineWidth: 0.5
                    },
                    pointStyle: 'star',
                    pointBackgroundColor:{
                        color:'#f00'
                    },
                    ticks: {
                        backdropColor: 'rgba(0, 0, 0, 0)',
                        fontColor: 'rgba(77,77,77, 0.7)',
                        display: false,
                        mirror: true,
                        min: 0,
                        max: 42 ,
                        stepSize: 10,
                        callback: function (value, index, values) {
                            return '           ' + value + '%';
                        }
                    }
                } 
            }
            });
                context.globalCompositeOperation = 'destination-over';
                setTimeout(() => {
                    myChart.update();
                }, 2000);
            }, 3000);
            // });
    </script>
    <?php
    // echo $tips;
    // die;
    $confirmation = $html;
    return $confirmation; 
}

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */

function my_login_redirect( $redirect_to, $request, $user ) {
    //is there a user to check?
    if (isset($user->roles) && is_array($user->roles)) {
        //check for subscribers
        if (in_array('subscriber', $user->roles)) {
            // redirect them to another URL, in this case, the homepage 
            $redirect_to =  home_url();
        }
    }
    return $redirect_to;
}

add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );