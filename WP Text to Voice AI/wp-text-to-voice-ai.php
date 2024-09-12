<?php
/*
 * Plugin Name: WP Text to Voice AI
 * Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-text-to-voice-ai/
 * Description: Transformez vos textes en Audio via OpenAI et ajoutez le shortcode [transcription_ia] pour afficher une barre de lecture audio sur vos pages
 * Version: 1.0
 * Author: Kevin Benabdelhak
 * Author URI: https://kevin-benabdelhak.fr/
 * Contributors: kevinbenabdelhak
*/



if (!defined('ABSPATH')) {
    exit; 
}

require_once(plugin_dir_path(__FILE__) . 'includes/enqueue.php');
require_once(plugin_dir_path(__FILE__) . 'includes/ajax.php');
require_once(plugin_dir_path(__FILE__) . 'includes/settings.php');
require_once(plugin_dir_path(__FILE__) . 'includes/shortcode.php');
require_once(plugin_dir_path(__FILE__) . 'includes/custom-columns.php');
require_once(plugin_dir_path(__FILE__) . 'includes/bulk-actions.php');