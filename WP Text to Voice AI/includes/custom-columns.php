<?php


if (!defined('ABSPATH')) {
    exit; 
}


add_filter('manage_edit-post_columns', 'wp_text_to_voice_ai_add_custom_column');
add_filter('manage_edit-page_columns', 'wp_text_to_voice_ai_add_custom_column');

add_action('init', function() {
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $post_type) {
        add_filter("manage_edit-{$post_type}_columns", 'wp_text_to_voice_ai_add_custom_column');
        add_action("manage_{$post_type}_custom_column", 'wp_text_to_voice_ai_custom_column_content', 10, 2);
    }

});




function wp_text_to_voice_ai_add_custom_column($columns) {
    $columns['attached_media'] = 'MP3 attaché';
    return $columns;
}



// Ajouter le contenu de la colonne pour les fichiers audio attachés (mp3) à tous les types de contenus
add_action('manage_posts_custom_column', 'wp_text_to_voice_ai_custom_column_content', 10, 2);
add_action('manage_pages_custom_column', 'wp_text_to_voice_ai_custom_column_content', 10, 2);



function wp_text_to_voice_ai_custom_column_content($column_name, $post_id) {
    if ($column_name == 'attached_media') {
        $audio_id = get_post_meta($post_id, '_attached_audio', true);
        if ($audio_id) {
            $mime_type = get_post_mime_type($audio_id);
            $file_path = get_attached_file($audio_id);
            if ($mime_type === 'audio/mpeg' || $mime_type === 'audio/mp3' && file_exists($file_path)) {
                echo '✔️';
            } else {
                echo '❌'; 
            }
        } 
        else {
            echo '❌'; 
        }

    }

}




// colonne activable/désactivable via les options d'écran
add_filter('manage_edit-post_hidden_columns', 'wp_text_to_voice_ai_default_hidden_columns');
add_filter('manage_edit-page_hidden_columns', 'wp_text_to_voice_ai_default_hidden_columns');



function wp_text_to_voice_ai_default_hidden_columns($hidden) {
    if (!is_array($hidden)) {
        $hidden = array();
    }

    $hidden[] = 'attached_media';
    return $hidden;
}




// colonnes CPT activables/désactivables 
function wp_text_to_voice_ai_manage_custom_columns($post_type) {
    add_filter("manage_edit-{$post_type}_columns", 'wp_text_to_voice_ai_add_custom_column');
    add_action("manage_{$post_type}_custom_column", 'wp_text_to_voice_ai_custom_column_content', 10, 2);
    add_filter("manage_edit-{$post_type}_hidden_columns", 'wp_text_to_voice_ai_default_hidden_columns');
}



add_action('init', function() {
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $post_type) {
        wp_text_to_voice_ai_manage_custom_columns($post_type);
    }
});