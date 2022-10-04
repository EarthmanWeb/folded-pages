<?php
/**
 * Plugin Name:     Folded Pages
 * Plugin URI:      https://github.com/EarthmanWeb/folded-pages
 * Description:     A lightweight WordPress plugin to view hierarchical pages more efficiently in the WP-Admin page listing
 * Author:          Earthman Media - Terrance Orletsky
 * Author URI:      earthmanmedia.com
 * Text Domain:     folded-pages
 * Version:         2.1.1
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
