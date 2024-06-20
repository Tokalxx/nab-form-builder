<?php

if (!defined('ABSPATH')) {
    exit; //Exit if accessed directly
}

class Nab_Form_API {

    //Constructor to hook into the REST API initialization action.
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    //Register the REST API routes for saving and getting forms.
    public function register_routes() {
        register_rest_route('nab-form-builder/v1', '/save-form', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_form'),
            'permission_callback' => array($this, 'permissions_check'),
        ));

        register_rest_route('nab-form-builder/v1', '/get-forms', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_forms'),
            'permission_callback' => array($this, 'permissions_check'),
        ));
    }

    //Checks if the user has proper permissions
    public function permissions_check() {
        return current_user_can('manage_options');
    }

    //function to save form
    public function save_form(WP_REST_Request $request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nab_form_builder';

        $form_name = sanitize_text_field($request->get_param('form_name'));
        $form_structure = wp_kses_post($request->get_param('form_structure'));

        $result = $wpdb->insert(
            $table_name,
            array(
                'form_name' => $form_name,
                'form_structure' => $form_structure,
            )
        );

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Could not insert form into the database', array('status' => 500));
        }

        return new WP_REST_Response('Form saved successfully', 200);
    }

    //function that gets all forms
    public function get_forms(WP_REST_Request $request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nab_form_builder';

        $forms = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        if (empty($forms)) {
            return new WP_Error('no_forms', 'No forms found', array('status' => 404));
        }

        return new WP_REST_Response($forms, 200);
    }
}

new Nab_Form_API();
