<?php
/*
 * Plugin Name: WordPress Cookie Law Plugin
 * Version: 0.0
 * Plugin URI: http://www.triplette.it/
 * Description: Cookie EU Law Plugin. Based on http://sitebeam.net/cookieconsent/
 * Author: Henry Triplette
 * Author URI: http://www.triplette.it/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: triplette-cookie-law
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Henry Triplette
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-triplette-cookie-law.php' );
require_once( 'includes/class-triplette-cookie-law-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-triplette-cookie-law-admin-api.php' );
require_once( 'includes/lib/class-triplette-cookie-law-post-type.php' );
require_once( 'includes/lib/class-triplette-cookie-law-taxonomy.php' );

/**
 * Returns the main instance of Triplette_Cookie_Law to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Triplette_Cookie_Law
 */
function Triplette_Cookie_Law () {
	$instance = Triplette_Cookie_Law::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Triplette_Cookie_Law_Settings::instance( $instance );
	}

	return $instance;
}

Triplette_Cookie_Law();