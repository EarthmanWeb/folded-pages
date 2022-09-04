<?php
/**
 * Plugin Name:     Folded Pages
 * Description:     Lightweight plugin that nests child pages for efficient wp-admin page management
 * Author:          Earthman Media - Terrance Orletsky
 * Author URI:      earthmanmedia.com
 * Text Domain:     folded_pages
 * Version:         1.0.0
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
