<?php
defined('INDEX_AUTH') or die('Direct access is not allowed!');

ob_start();

// create new instance
$form = new simbio_form_table_AJAX('mainForm', pluginUrl(['section' => 'add_schema']), 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

$form->addAnything('File Skema', <<<HTML
<input type="file" name="schema"/>
<input type="hidden" name="import[raw_json]"/>
<input type="hidden" name="action" value="import_schema"/>
HTML);

echo $form->printOut();

echo <<<HTML
<script>
    document.querySelector('input[name="schema"]').addEventListener('change', (event) => {
        const file = event.target.files[0]
        const reader = new FileReader()
        reader.onload = function() {
            const contents = reader.result
            document.querySelector('input[name="import[raw_json]"]').value = contents
        }

        reader.readAsText(file);
    })
</script>
HTML;

/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';