<?php

/**
 * Plugin Name: NAB Form Builder
 * Description: A form builder plugin that will allow users to create forms for their website.
 * Version: 1.0.0
 * Author: \
 * Text Domain: nab_form_builder
 */

//Soft security to exit if accessed directly
if (!defined('ABSPATH')) {
   exit;
}



// Include the main plugin class
require_once plugin_dir_path(__FILE__) . 'includes/class-nab-form-builder.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-nab-form-api.php';

// Runs plugin
function run_nab_form_builder()
{
   $plugin = new Nab_Form_Builder();
   $plugin->run();
}
run_nab_form_builder();
