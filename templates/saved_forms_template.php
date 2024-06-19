<div class="wrap">
    <h1>Saved Forms</h1>
    <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'nab_forms';
    $forms = $wpdb->get_results("SELECT * FROM $table_name");

    if ($forms) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Form Name</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($forms as $form) {
            echo '<tr>';
            echo '<td>' . esc_html($form->id) . '</td>';
            echo '<td>' . esc_html($form->form_name) . '</td>';
            echo '<td>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=nab-form-builder&form_id=' . $form->id)) . '">View</a> | ';
            echo '<a href="' . esc_url(admin_url('admin.php?action=nab_delete_form&form_id=' . $form->id)) . '">Delete</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No forms found.</p>';
    }
    ?>
</div>
