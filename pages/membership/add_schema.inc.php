<?php
use SLiMS\Table\Schema;

$columns = implode('', array_merge(array_map(function($item) {
    return '<option value="' . $item . '">' . $item . '</option>';
}, array_values(array_filter(Schema::table('member')->columns(), function($column) {
    if (!preg_match('/(expire|regis|since|notes|input|last_|is_)/', $column)) return true;
}))), ['<option value="advance">Ruas Mahir</option>']));



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
    <label><strong>Ruas</strong></label>
    <p>Tentukan ruas-ruas apa saja yang akan dijadikan isian pada formulir perndaftaran nanti</p>
    <hr>
    <div id="editableArea">
        <div class="d-flex flex-column col-12">
            <label id="label-1"><strong>Ruas <b id="rowName1"></b></strong></label>
            <div class="d-flex flex-row">
                <input type="text" class="form-control col-4 noAutoFocus" name="row[1]" placeholder="Label yang akan muncul di formulir"/>
                <select class="form-control col-4 noAutoFocus" name="field[1]" data-row="1">
                    <option value="">Pilih</option>
                    {$columns}
                </select>
            </div>
            <div id="advForm1" class="d-none flex-column my-3">
                <div class="d-block">
                    <label><strong>Ruas Mahir</strong></label>
                </div>
                <div class="d-flex flex-row">
                    <input type="text" class="form-control col-6 noAutoFocus" name="advrow[1]" placeholder="Nama kolom pada database"/>
                    <select class="form-control col-4 noAutoFocus" name="advrowtype[1]">
                        <option value="">Pilih</option>
                        <option value="int">Angka</option>
                        <option value="varchar">Teks Singkat</option>
                        <option value="text">Teks Paragraf</option>
                        <option value="enum">Daftar</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <button row="1" class="addRow notAJAX btn btn-secondary btn-sm col-2 my-3">Tambah Selanjutnya</button>
</div>
HTML);
echo $form->printOut();
?>
<script>
    let area = $('#editableArea')
    let addRow = $('.addRow')
    let template = `
    <div id="detailrow{row}" class="d-flex flex-column col-12">
        <label id="label-1"><strong>Ruas <b id="rowName{row}"></b></strong></label>
        <div class="d-flex flex-row">
            <input type="text" class="form-control col-4 noAutoFocus" name="row[{row}]" placeholder="Label yang akan muncul di formulir"/>
            <select class="form-control col-4 noAutoFocus" name="field[{row}]" data-row="{row}">
                <option value="">Pilih</option>
                <?= $columns ?>
            </select>
            <button class="deleteRow notAJAX btn btn-danger" data-remove="{row}"><i class="fa fa-trash"></i></button>
        </div>
        <div id="advForm{row}" class="d-none flex-column my-3">
            <div class="d-block">
                <span><strong>Ruas Mahir</strong></span>
            </div>
            <div class="d-flex flex-row">
                <input type="text" class="form-control col-6 noAutoFocus" name="advrow[{row}]" placeholder="Nama kolom pada database"/>
                <select class="form-control col-4 noAutoFocus" name="advrowtype[{row}]">
                    <option value="">Pilih</option>
                    <option value="int">Angka</option>
                    <option value="varchar">Teks Singkat</option>
                    <option value="text">Teks Paragraf</option>
                    <option value="enum">Daftar</option>
                </select>
            </div>
        </div>
    </div>`

    addRow.click(function() {
        let nextNumber = parseInt($(this).attr('row')) + 1
        area.append(template.replace(/\{row\}/g, nextNumber))
        $(this).attr('row', nextNumber)
    })

    area.on('change', 'select', function(){
        let row = $(this).data('row')

        if ($(this).val() === 'advance') {
            $(`#advForm${row}`).addClass('d-flex')
        } else {
            $(`#advForm${row}`).removeClass('d-flex')
            $(`input[name="advrow[${row}]"]`).val('')
            $(`select[name="advrowtype[${row}]"]`).val('')
        }
    })

    area.on('click', '.deleteRow', function(){
        let row = $(this).data('remove')
        $(`#detailrow${row}`).remove()
    })

    area.on('click', 'input,select', function(e){
        e.preventDefault()
    })
</script>