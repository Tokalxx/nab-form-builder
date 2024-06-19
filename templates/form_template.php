<div class="wrap">
    <h1>Create Your Form</h1>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="nab_save_form">
        <?php wp_nonce_field( 'nab_save_form', 'nab_form_nonce' ); ?>
        <label for="form-name">Form Name:</label>
        <input type="text" name="form_name" id="form-name" required>
        <div class="rootlayout">
            <div id="items">
                <div id="input" class="draggable field-template" draggable="true">Input Field</div>
                <div id="paragraph" class="draggable field-template" draggable="true">Paragraph</div>
                <div id="textfield" class="draggable field-template" draggable="true">Button</div>
            </div>
            <div id="dropzone" class="dropzone"></div>
        </div>
        <textarea name="form_structure" id="form-structure" hidden></textarea>
        <button type="submit" id="save-form-button">Save Form</button>
    </form>
</div>
