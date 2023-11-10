<?php

if (isset($_POST['saveData'])) {

    exit;
}

$data = $activeSchema->fetchObject();
$option = json_decode($data->option??'');

// create new instance
$form = new simbio_form_table_AJAX('mainForm', pluginUrl(), 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

$form->addHidden('schema_id', $data->id);
$form->addSelectList('image', '<strong>Unggah Foto Profil?</strong>', [[0, __('Disable')],[1, __('Enable')]], $option?->image??'', 'rows="1" class="form-control col-2"');
$form->addSelectList('captcha', '<strong>Menggunakan Re-Captcha?</strong>', [[0, __('Disable')],[1, __('Enable')]], $option?->captcha??'', 'rows="1" class="form-control col-2"');

echo $form->printOut();