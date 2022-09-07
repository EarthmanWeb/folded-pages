<?php
/**
 * Plugin Name:     Folded Pages
 * Plugin URI:      https://github.com/EarthmanWeb/folded-pages
 * Description:     A lightweight WordPress plugin to view hierarchical pages more efficiently in the WP-Admin page listing
 * Author:          Earthman Media - Terrance Orletsky
 * Author URI:      earthmanmedia.com
 * Text Domain:     folded-pages
 * Version:         2.0.5
 *
 * @package         Folded_Pages
 */

namespace EMMEDIA;

/**
 * Import plugin class and initialize
 */
require_once __DIR__ . '/classes/class-folded-pages.php';
// initialize the hooks...
add_action( 'init', array( new Folded_Pages(), 'init' ) );

/**
 * Import updater class and initialize updater
 */
require_once __DIR__ . '/classes/class-folded-pages-updater.php';
$folded_pages_updater = new Folded_Pages_Updater( __FILE__ ); // instantiate our class.
$folded_pages_updater->set_username( 'EarthmanWeb' ); // set username.
$folded_pages_updater->set_repository( 'folded-pages' ); // set repo.
$folded_pages_updater->initialize();
