<?php

/**
 * Asodiabetes Donations
 *
 * @package     Asodiabetes Donations
 * @author      Paul Osinga
 * @copyright   2020 Asodiabetes Donations
 * @license     GPL-2.0
 *
 * Plugin Name: Asodiabetes Donations
 * Description: Plugin complementario del plugin de PayU para woocommerce que permite generar un boton de pago para docaciones. (Requiere PayU para wordpress. Las transaciones no se registran en woocommerce)
 * Version:     0.1.0
 * Author:      Paul Osinga
 * Text Domain: asodiabetes-donations
 * License:     GPL-2.0
 *
 */

// Block direct access to file
defined( 'ABSPATH' ) or die( 'Not Authorized!' );

// Plugin Defines
define( "ASODIABETES_DONATIONS_FILE", __FILE__ );
define( "ASODIABETES_DONATIONS_DIR", dirname(__FILE__) );
define( "ASODIABETES_DONATIONS_INCLUDE_DIR", dirname(__FILE__) . '/include' );
define( "ASODIABETES_DONATIONS_DIR_BASENAME", plugin_basename( __FILE__ ) );
define( "ASODIABETES_DONATIONS_DIR_PATH", plugin_dir_path( __FILE__ ) );
define( "ASODIABETES_DONATIONS_DIR_URL", plugins_url( null, __FILE__ ) );

// Require the main class file
require_once( dirname(__FILE__) . '/include/class-main.php' );
