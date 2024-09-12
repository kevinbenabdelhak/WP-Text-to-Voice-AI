<?php

if (!defined('ABSPATH')) {
    exit; 
}


// Enqueue le lecteur de Plyr 
add_action('wp_enqueue_scripts', 'wp_text_to_voice_ai_enqueue_plyr');
function wp_text_to_voice_ai_enqueue_plyr() {
    $audio_style = get_option('wp_text_to_voice_ai_audio_style');
    if ($audio_style === 'plyr') {
        wp_enqueue_style('plyr-css', plugin_dir_url(__FILE__) . '../assets/css/plyr.css', [], '3.6.8');
        wp_enqueue_script('plyr-js', plugin_dir_url(__FILE__) . '../assets/js/plyr.polyfilled.js', [], '3.6.8', true);
        wp_add_inline_script('plyr-js', 'const players = Plyr.setup("audio");');
    }

}