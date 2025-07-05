<?php


if (!defined('ABSPATH')) {
    exit; 
}


add_shortcode('transcription_ia', 'wp_text_to_voice_ai_shortcode');
function wp_text_to_voice_ai_shortcode() {
    global $post;
    if (!isset($post->ID)) {
        return ''; 
    }

    
    $audio_id = get_post_meta($post->ID, '_attached_audio', true);
    $audio_style = get_option('wp_text_to_voice_ai_audio_style', 'default');

    if ($audio_id) {

        $audio_url = wp_get_attachment_url($audio_id);
        if ($audio_url) {
            if ($audio_style === 'plyr') {

                return '<audio id="audio-player" controls><source src="' . esc_url($audio_url) . '" type="audio/mpeg">Votre navigateur ne supporte pas la balise audio.</audio>';

            } else {

                return '<audio controls><source src="' . esc_url($audio_url) . '" type="audio/mpeg">Votre navigateur ne supporte pas la balise audio.</audio>';

            }

        }

    }
    return '';

}