<?php
/**
 * Plugin Name: Forms
 * Plugin URI: https://www.madeit.be/producten/wordpress/forms-plugin/
 * Description: Build cool, easy and flexible forms with Forms.
 * Author: Made I.T.
 * Author URI: https://www.madeit.be
 * Version: 1.5.2
 * Text Domain: forms-by-made-it
 * Domain Path: /languages
 * License: GPLv2.
 */
// Defines
if (!defined('MADEIT_FORM_DIR')) {
    define('MADEIT_FORM_DIR', dirname(__FILE__)); // Plugin Dir
}
if (!defined('MADEIT_FORM_URL')) {
    define('MADEIT_FORM_URL', plugin_dir_url(__FILE__)); // Plugin URL
}
if (!defined('MADEIT_FORM_ADMIN')) {
    define('MADEIT_FORM_ADMIN', MADEIT_FORM_DIR.'/admin'); // Admin Dir
}
if (!defined('MADEIT_FORM_FRONT')) {
    define('MADEIT_FORM_FRONT', MADEIT_FORM_DIR.'/front'); // Admin Dir
}
require_once MADEIT_FORM_DIR.'/vendor/autoload.php';

function wp_forms_by_madeit_load_plugin_textdomain()
{
    load_plugin_textdomain('forms-by-made-it', false, basename(dirname(__FILE__)).'/languages/');
}
add_action('plugins_loaded', 'wp_forms_by_madeit_load_plugin_textdomain');

require_once MADEIT_FORM_DIR.'/WP_MadeIT_Form_Settings.php';
$wp_madeit_form_settings = new WP_MadeIT_Form_Settings();

require_once MADEIT_FORM_DIR.'/actions/Email.php';
$a = new WP_MADEIT_FORM_Email();
require_once MADEIT_FORM_DIR.'/actions/Mailchimp.php';
$a = new WP_MADEIT_FORM_Mailchimp();
require_once MADEIT_FORM_DIR.'/actions/Mailpoet.php';
$a = new WP_MADEIT_FORM_Mailpoet();
require_once MADEIT_FORM_DIR.'/actions/GAEvent.php';
$a = new WP_MADEIT_FORM_GAEvent();
require_once MADEIT_FORM_DIR.'/actions/GAAdsEvent.php';
$a = new WP_MADEIT_FORM_GAAdsEvent();
require_once MADEIT_FORM_DIR.'/actions/Download.php';
$a = new WP_MADEIT_FORM_Download();

require_once MADEIT_FORM_DIR.'/modules/Text.php';
require_once MADEIT_FORM_DIR.'/modules/Checkbox.php';
require_once MADEIT_FORM_DIR.'/modules/Textarea.php';
require_once MADEIT_FORM_DIR.'/modules/Submit.php';
require_once MADEIT_FORM_DIR.'/modules/Select.php';
require_once MADEIT_FORM_DIR.'/modules/Number.php';
$t = new WP_MADEIT_FORM_Module_Text();
$t = new WP_MADEIT_FORM_Module_Checkbox();
$t = new WP_MADEIT_FORM_Module_Textarea();
$t = new WP_MADEIT_FORM_Module_Select();
$t = new WP_MADEIT_FORM_Module_Number();
$t = new WP_MADEIT_FORM_Module_Submit($wp_madeit_form_settings);

require_once MADEIT_FORM_DIR.'/DatabaseInit.php';
$wp_plugin_dbinit = new DatabaseInit();
$wp_plugin_dbinit->addHooks();

require_once MADEIT_FORM_DIR.'/admin/WP_MADEIT_FORM_admin.php';
$wp_madeit_form_admin = new WP_MADEIT_FORM_admin($wp_madeit_form_settings);
$wp_madeit_form_admin->addHooks();

require_once MADEIT_FORM_DIR.'/front/WP_Form_front.php';
$wp_NBD_front = new WP_Form_front($wp_madeit_form_settings);
$wp_NBD_front->addHooks();
