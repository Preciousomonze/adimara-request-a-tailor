<?php
/**
 * Plugin Name: Adimara Request a Tailor
 * Plugin URI: 
 * Description: A WordPress Plugin Add-on for the Adimara measurement plugin, which helps in the creation of a button called "Request Tailor Measurement" placed beneath the "enter measurement". The RTM button will enable the customer skip the process of entering their measurements for the item(s). <br><strong>Note: This plugin will not work without "Adimara Measurement Extension" plugin</strong>.
 * Author: Precious Omonze (CodeXplorer 🤓🦜)
 * Author URI: https://codeexplorer.ninja
 * Version: 1.1.0
 * Requires at least: 4.9
 * Tested up to: 5.3
 * WC requires at least: 3.0
 * WC tested up to: 3.9
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}
//make sure you update the version values when necessary
define('ADM_RAT_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define('ADM_RAT_PLUGIN_FILE', __FILE__ );
define('ADM_RAT_TEXT_DOMAIN', 'adimara');
define('ADM_RAT_PLUGIN_VERSION','1.1.0');


// Include the main class.
if(!class_exists('ADM_RAT')){
    include_once dirname(__FILE__) . '/includes/class-adm-rat.php';
}
function adm_rat(){
    return ADM_RAT::instance();
}

add_action( 'adm_pk_init', 'adm_rat');

$GLOBALS['adm_rat'] = adm_rat();
