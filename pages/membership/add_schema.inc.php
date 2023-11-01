<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', pluginUrl(reset: true), 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

$form->addTextField('text', 'name', '<strong>Nama*</strong>', '', 'rows="1" class="form-control"');

$form->addAnything('<strong>Informasi</strong>', <<<HTML
<div class="d-flex flex-column">
    <label><strong>Nama Formulir</strong></label>
    <input type="text" class="form-control col-3"/>
    <label><strong>Lain-lain</strong></label>
    <p>Pemberitahuan mengenai prasayrat, informasi lanjutan pra/pasca pendaftaran</p>
    <input type="text" class="form-control col-6"/>
</div>
HTML);
$form->addAnything('<strong>Struktur</strong>', <<<HTML
<div class="d-flex flex-column">
    <label><strong>Nama Formulir</strong></label>
    <input type="text" class="form-control col-3"/>
    <label><strong>Lain-lain</strong></label>
    <p>Pemberitahuan mengenai prasayrat, informasi lanjutan pra/pasca pendaftaran</p>
    <input type="text" class="form-control col-6"/>
</div>
HTML);
echo $form->printOut();