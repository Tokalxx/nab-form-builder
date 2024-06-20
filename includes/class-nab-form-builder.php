<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Nab_Form_Builder
{

    public function __construct()
    {
        // Constructor content can go here if needed
    }

    public function run()
    {
        $this->define_hooks();
    }

    //Hooks for WordPress functions
    private function define_hooks()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        register_activation_hook(__FILE__, array($this, 'create_custom_table'));
        add_action('admin_post_nab_save_form', array($this, 'handle_form_submission'));
        add_action('admin_post_nab_delete_form', array($this, 'handle_form_deletion'));

        add_action('wp_ajax_nab_get_form_names', array($this, 'handle_form_getFormNames'));
        add_action('wp_ajax_nab_get_form', array($this, 'get_form'));
        add_action('wp_ajax_nab_get_form_names', array($this, 'handle_form_getFormNames'));
    }

    //Enqueue admin styles and scripts
    public function enqueue_admin_scripts()
    {
        wp_enqueue_style('nab_form_builder_styles', plugin_dir_url(__FILE__) . '../assets/css/styles.css');
        wp_enqueue_script('nab_form_builder_scripts', plugin_dir_url(__FILE__) . '../assets/js/form_builder.js', array('jquery'), null, true);

        wp_localize_script('nab_form_builder_scripts', 'nabFormBuilder', array(
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    //Add the plugin's menu and submenu pages in the admin dashboard
    public function add_admin_menu()
    {
        add_menu_page(
            'NAB Form Builder',
            'Form Builder',
            'manage_options',
            'nab-form-builder',
            array($this, 'display_admin_page'),
            'dashicons-forms',
            20
        );
        add_submenu_page(
            'nab-form-builder',
            'Saved Forms',
            'Saved Forms',
            'manage_options',
            'nab-saved-forms',
            array($this, 'display_saved_forms_page')
        );
    }

    //Function to display the main admin page
    public function display_admin_page()
    {
        require_once plugin_dir_path(__FILE__) . '../templates/form_template.php';
    }

    //Function to display the saved forms page
    public function display_saved_forms_page()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . '/templates/saved_forms_template.php';
    }

    //Create the custom database table on plugin activation
    public function create_custom_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nab_form_builder';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE `{$wp_nab_form_builder}` (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_name varchar(255) NOT NULL,
            form_structure longtext NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    //Handle form submission and save it to the database
    public function handle_form_submission()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('nab_save_form', 'nab_form_nonce');

        $form_name = isset($_POST['form_name']) ? sanitize_text_field($_POST['form_name']) : '';
        $form_structure = isset($_POST['form_structure']) ? wp_kses_post(wp_unslash($_POST['form_structure'])) : '';

        global $wpdb;
        $table_name = $wpdb->prefix . 'nab_form_builder'; // Use the correct table name

        $wpdb->insert(
            $table_name,
            array(
                'form_name' => $form_name,
                'form_structure' => $form_structure,
            )
        );

        wp_redirect(admin_url('admin.php?page=nab-saved-forms'));
        exit;
    }

    //Handle AJAX request to get form names
    public function handle_form_getFormNames()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'nab_form_builder';

        $page_number = isset($_GET['page_number']) ? intval($_GET['page_number']) : 1;
        $page_size = isset($_GET['page_size']) ? intval($_GET['page_size']) : 10;
        $offset = ($page_number - 1) * $page_size;

        $form_names = $wpdb->get_results($wpdb->prepare(
            "SELECT id, form_name FROM $table_name LIMIT %d OFFSET %d",
            $page_size,
            $offset
        ), ARRAY_A);

        if (empty($form_names)) {
            wp_send_json_error('No forms found');
        } else {
            wp_send_json_success($form_names);
        }
    }

    //Get a specific form based on ID or name
    public function get_form($formID = null, $formName = null)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'nab_form_builder';

        if ($formID) {
            $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $formID), ARRAY_A);
        } elseif ($formName) {
            $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE form_name = %s", $formName), ARRAY_A);
        } else {
            wp_send_json_error('No form ID or name provided');
        }

        if (empty($form)) {
            wp_send_json_error('Form not found');
        } else {
            wp_send_json_success($form);
        }
    }

    //Handle AJAX request to get form names with pagination
    public function handle_form_getFormNames()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'nab_form_builder';
        $forms = $wpdb->get_results("SELECT form_name FROM $table_name", ARRAY_A);

        if (empty($forms)) {
            wp_send_json_error('No forms found');
        } else {
            wp_send_json_success($forms);
        }
    }

    //Handle form deletion
    public function handle_form_deletion()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;

        if ($form_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nab_forms';
            $wpdb->delete($table_name, array('id' => $form_id));
        }

        wp_redirect(admin_url('admin.php?page=nab-saved-forms'));
        exit;
    }
}

// Initialize the plugin
$nab_form_builder = new Nab_Form_Builder();
$nab_form_builder->run();
