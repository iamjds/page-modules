<?php
   /*
   Plugin Name: CPI Page Modules
   Plugin URI: https://cpisecurity.com/
   description: WordPress Content plugin which allows CPI Marketing team to make regular updates to specific sections on the cpisecurity.com website
   Version: 1.0
   Author: Jake Schaap
   Author URI: https://www.linkedin.com/in/jakeschaap/
   License: GPL2
   */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Currently plugin version.
 */
define( 'CPI_PAGE_MODULE_VERSION', '1.0.0' );


/**
 * The code that runs during plugin activation.
 */
function activate_cpi_page_module() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cpi-page-module-activator.php';
	CPI_Page_Module_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_cpi_page_module() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cpi-page-module-deactivator.php';
	CPI_Page_Module_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cpi_page_module' );
register_deactivation_hook( __FILE__, 'deactivate_cpi_page_module' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cpi-page-module.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cpi_page_module() {

	$plugin = new CPI_Page_Module();
	$plugin->run();

}
run_cpi_page_module();