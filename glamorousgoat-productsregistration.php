<?php
/**
 * Plugin Name: Glamorousgoat Products Registration
 * Description: Glamorousgoat Products Registration is a personal plugin.
 * Version: 1.0
 * Author: glamorousgoat
 * License: GPLv2 or later
 *License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

require_once( trailingslashit(plugin_dir_path(__FILE__)) . '/constants.php' );	
require_once( GGPR_LIB . 'classes/gg-product-registration.php' );
require_once( GGPR_LIB . 'classes/ggpr-entry.php' );
require_once( GGPR_LIB . 'functions.php' );
if(  is_admin()){
    require_once( GGPR_LIB . 'classes/ggpr-admin-view.php' );
    require_once( GGPR_LIB . 'classes/ggpr-list-table.php' );
    require_once( GGPR_LIB . 'classes/ggpr-leads-list-table.php' );
}

global $gg_product_registration;
$gg_product_registration = new GG_Product_Registration(__FILE__);

