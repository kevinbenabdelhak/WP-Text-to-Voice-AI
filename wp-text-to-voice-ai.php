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




if ( !class_exists( 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
}
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$monUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/kevinbenabdelhak/WP-Text-to-Voice-AI/', 
    __FILE__,
    'wp-text-to-voice-ai' 
);

$monUpdateChecker->setBranch('main');





require_once(plugin_dir_path(__FILE__) . 'includes/enqueue.php');
require_once(plugin_dir_path(__FILE__) . 'includes/ajax.php');
require_once(plugin_dir_path(__FILE__) . 'includes/settings.php');
require_once(plugin_dir_path(__FILE__) . 'includes/shortcode.php');
require_once(plugin_dir_path(__FILE__) . 'includes/custom-columns.php');
require_once(plugin_dir_path(__FILE__) . 'includes/bulk-actions.php');