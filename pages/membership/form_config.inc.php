<?php
$data = $activeSchema->fetchObject();
$option = json_decode($data->option??'');

// create new instance
$form = new simbio_form_table_AJAX('mainForm', pluginUrl(reset: true), 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

$form->addHidden('schema_id', $data?->id??'');

$list = [];
$list[] = [0, 'Pilih'];
while ($schemaData = $schemas->fetchObject()) {
    $list[] = [$schemaData->id, $schemaData->name];
}

$form->addSelectList('form_config[image]', '<strong>Unggah Foto Profil?</strong>', [[0, __('Disable')],[1, __('Enable')]], $option?->image??'', 'rows="1" class="imageWarning form-control col-2"');
$form->addSelectList('form_config[captcha]', '<strong>Menggunakan Re-Captcha?</strong>', [[0, __('Disable')],[1, __('Enable')]], $option?->captcha??'', 'rows="1" class="form-control col-2"');
$form->addTextField('textarea', 'form_config[message_after_save]', '<strong>Pesean Setelah Registrasi</strong>', $option?->message_after_save??'', 'rows="1" style="height: 80px" class="form-control"');

echo $form->printOut();
?>
<script>
    $('.imageWarning').change(function() {
        if ($(this).val() == 1) {
            let ask = confirm('Mengaktifkan fitur memungkinkan sistem anda menjadi rentan terhadap serangan oleh hacker. Apakah anda yakin?')

            if (!ask) {
                $(this).val(0)
                return 
            }
        }
    })
</script>