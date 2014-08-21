<?php
// Plugin constants
global $wpdb;
if(!defined('GGPR_VER')) define('GGPR_VER', 1.0 );

if(!defined('GGPR_VER_OPTNAME')) define('GGPR_VER_OPTNAME', 'ggpr_version');
if(!defined('GGPR_OPTION_NAME')) define ('GGPR_OPTION_NAME', 'ggpr_options' );

if(!defined('GGPR_ADMIN_MENU_SLUG')) define ('GGPR_ADMIN_MENU_SLUG', 'gg-product-registration' );
//if(!defined('GGPR_ADMIN_SETTING_SLUG')) define ('GGPR_ADMIN_SETTING_SLUG', 'ggpr_settings' );


if(!defined('GGPR_TABLE_NAME'))		define ('GGPR_TABLE_NAME', $wpdb->prefix . 'registration_codes' );

if(!defined('GGPR_PATH'))	define('GGPR_PATH', plugin_dir_path(  __FILE__ ) );
if(!defined('GGPR_LIB'))	define('GGPR_LIB', trailingslashit (GGPR_PATH) . 'lib/');
if(!defined('GGPR_URL'))	define('GGPR_URL', plugin_dir_url( __FILE__ ));
