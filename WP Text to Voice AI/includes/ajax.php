<?php
if (!defined('ABSPATH')) {
    exit; 
}

add_action('wp_ajax_wp_text_to_voice_ai_generate_audio_bulk', 'wp_text_to_voice_ai_generate_audio_bulk');
function wp_text_to_voice_ai_generate_audio_bulk() {
    check_ajax_referer('wp_text_to_voice_ai_nonce', 'security'); // Pour la sécurité

    $post_id = intval($_POST['post_id']);
    $api_key = get_option('wp_text_to_voice_ai_api_key');
    $post_content = wp_text_to_voice_ai_clean_post_content(get_post_field('post_content', $post_id)); // Cleaner le contenu du post

    if (empty($api_key)) {
        wp_send_json_error(['message' => 'API key is missing']);
    }

    if (empty($post_content)) {
        wp_send_json_error(['message' => 'Post content is empty']);
    }

    $segments = wp_text_to_voice_ai_split_text($post_content, 4000);

    if (empty($segments)) {
        wp_send_json_error(['message' => 'Failed to split content into segments']);
    }

    $audio_files = [];

    foreach ($segments as $index => $segment) {
        $response = wp_remote_post('https://api.openai.com/v1/audio/speech', [
            'headers' => [
                'Authorization' => 'Bearer ' . sanitize_text_field($api_key),
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => 'tts-1',
                'input' => sanitize_textarea_field($segment),
                'voice' => 'alloy',
                'response_format' => 'mp3'
            ]),
            'timeout' => 60
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Error from API: ' . $response->get_error_message()]);
        }

        $body = wp_remote_retrieve_body($response);

        if (empty($body)) {
            wp_send_json_error(['message' => 'Empty response from API for segment ' . ($index + 1)]);
        }

        $audio_files[] = $body;
    }

    if (empty($audio_files)) {
        wp_send_json_error(['message' => 'No audio files generated']);
    }

    $final_audio = wp_text_to_voice_ai_concatenate_audio($audio_files, $post_id);

    $upload = wp_upload_bits("audio_{$post_id}.mp3", null, $final_audio);

    if (!empty($upload['error'])) {
        wp_send_json_error(['message' => 'Audio upload failed: ' . $upload['error']]);
    }

    $filename = $upload['file'];
    $filetype = wp_check_filetype(basename($filename), null);

    $attachment = array(
        'guid' => $upload['url'],
        'post_mime_type' => $filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
    if (is_wp_error($attach_id)) {
        wp_send_json_error(['message' => 'Failed to insert attachment: ' . $attach_id->get_error_message()]);
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // Attach the audio to the post
    update_post_meta($post_id, '_attached_audio', $attach_id);

    wp_send_json_success(['message' => 'Audio successfully generated and attached.']);
}

// Cleaner le contenu du post
function wp_text_to_voice_ai_clean_post_content($content) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); 
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // extraire uniquement le texte des balises spécifiques
    $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'ul', 'li'];
    $textContent = '';

    // extraire le texte uniquement des balises autorisées
    foreach ($dom->getElementsByTagName('*') as $element) {
        if (in_array($element->nodeName, $allowed_tags)) {
            
            $textContent .= trim($element->textContent) . ' '; 
        }
    }

    // Supprimer les entités HTML
    $textContent = html_entity_decode($textContent, ENT_QUOTES);   // Convertir les entités HTML en caractères

    // Remplacer les sauts de lignes par un espace et supprimer les espaces multiples
    $textContent = preg_replace("/\r|\n/", " ", $textContent);     // Remplacer les saut de lignes par un espace
    $textContent = preg_replace('/\s+/', ' ', $textContent);       // Remplacer les multiples espaces par un seul

    return trim($textContent);                                     // Enlever les espaces en début et fin de chaîne
}


function wp_text_to_voice_ai_split_text($text, $length) {
    $segments = [];
    $text_length = strlen($text);
    for ($start = 0; $start < $text_length; $start += $length) {
        $segments[] = substr($text, $start, $length);
    }
    return $segments;
}


function wp_text_to_voice_ai_concatenate_audio($audio_segments, $post_id) {
    $final_audio = '';
    foreach ($audio_segments as $index => $audio_segment) {
        $final_audio .= $audio_segment;
    }
    return $final_audio;
}

// Ajax handler pour supprimer les transcriptions audio en bulk
add_action('wp_ajax_wp_text_to_voice_ai_delete_audio_bulk', 'wp_text_to_voice_ai_delete_audio_bulk');
function wp_text_to_voice_ai_delete_audio_bulk() {
    check_ajax_referer('wp_text_to_voice_ai_nonce', 'security'); // Pour la sécurité

    $post_id = intval($_POST['post_id']);
    $audio_id = get_post_meta($post_id, '_attached_audio', true);

    if ($audio_id) {
        wp_delete_attachment($audio_id, true);
        delete_post_meta($post_id, '_attached_audio');
        wp_send_json_success(['message' => 'Audio successfully deleted and detached.']);
    } else {
        wp_send_json_error(['message' => 'No audio attached for post ID ' . $post_id]);
    }
}