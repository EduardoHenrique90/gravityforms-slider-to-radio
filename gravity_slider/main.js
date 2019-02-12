var width = jQuery(window).width();
if (width < 720) {
    jQuery("#slider_count-m").show();
    jQuery("#slider_count").hide();
} else {
    jQuery("#slider_count-m").hide();
    jQuery("#slider_count").show();
}