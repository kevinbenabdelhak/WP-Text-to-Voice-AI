<?php


if (!defined('ABSPATH')) {
    exit; 
}

add_action('admin_menu', 'wp_text_to_voice_ai_menu');
function wp_text_to_voice_ai_menu() {
    add_options_page('WP Text to Voice AI', 'WP Text to Voice AI', 'manage_options', 'wp-text-to-voice-ai', 'wp_text_to_voice_ai_options_page');
}


function wp_text_to_voice_ai_options_page() {
    ?>
    <div class="wrap">

        <h1>WP Text to Voice AI</h1>
        <form method="post" action="options.php">

            <?php
            settings_fields('wp_text_to_voice_ai_options_group');

            do_settings_sections('wp_text_to_voice_ai');

            submit_button();

            ?>

        </form>
    </div>
    <?php

}

add_action('admin_init', 'wp_text_to_voice_ai_settings');




function wp_text_to_voice_ai_settings() {
    register_setting('wp_text_to_voice_ai_options_group', 'wp_text_to_voice_ai_api_key');
    add_settings_section('wp_text_to_voice_ai_main_section', null, null, 'wp_text_to_voice_ai');

    add_settings_field('wp_text_to_voice_ai_api_key', 'Clé API OpenAI', 'wp_text_to_voice_ai_api_key_callback', 'wp_text_to_voice_ai', 'wp_text_to_voice_ai_main_section');

    // paramètre pour le style du lecteur 

    register_setting('wp_text_to_voice_ai_options_group', 'wp_text_to_voice_ai_audio_style');

    add_settings_field('wp_text_to_voice_ai_audio_style', 'Style du lecteur audio', 'wp_text_to_voice_ai_audio_style_callback', 'wp_text_to_voice_ai', 'wp_text_to_voice_ai_main_section');


}





function wp_text_to_voice_ai_api_key_callback() {
    $api_key = get_option('wp_text_to_voice_ai_api_key');
    echo '<input type="text" name="wp_text_to_voice_ai_api_key" value="' . esc_attr($api_key) . '" size="50" />';

}





function wp_text_to_voice_ai_audio_style_callback() {
    $audio_style = get_option('wp_text_to_voice_ai_audio_style');
    echo '<select name="wp_text_to_voice_ai_audio_style">
            <option value="default" ' . selected($audio_style, 'default', false) . '>Lecteur natif</option>
            <option value="plyr" ' . selected($audio_style, 'plyr', false) . '>Plyr</option>
          </select>';
}