<?php
use SLiMS\Table\Schema;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

$columns = implode('', array_merge(array_map(function($item) {
    return '<option value="' . $item . '">' . $item . '</option>';
}, array_values(array_filter(Schema::table('member')->columns(), function($column) {
    if (!preg_match('/(expire|regis|since|notes|input|last_|is_)/', $column)) return true;
}))), ['<option value="advance">Ruas Mahir</option>']));

// create new instance
$form = new simbio_form_table_AJAX('mainForm', pluginUrl(['section' => 'add_schema']), 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

$form->addHidden('action', 'create_schema');
$form->addTextField('text', 'name', '<strong>Nama*</strong>', '', 'rows="1" class="form-control"');

$form->addAnything('<strong>Informasi</strong>', <<<HTML
<div class="d-flex flex-column">
    <label><strong>Judul Formulir</strong></label>
    <input type="text" name="info[title]" class="form-control col-3"/>
    <label><strong>Lain-lain</strong></label>
    <p>Pemberitahuan mengenai prasayrat, informasi lanjutan pra/pasca pendaftaran</p>
    <div id="editor" class="col-8">
        <div id="toolbarContainer"></div>
        <div id="contentDesc" class="rounded-lg px-3 noAutoFocus" style="background-color: white; min-height: 200px"></div>
    </div>
    <label><strong>Letak</strong></label>
    <select class="form-control col-2" name="info[position]">
        <option value="top">Atas</option>
        <option value="bottom">Bawah</option>
    </select>
</div>
HTML);

$form->addAnything('<strong>Struktur</strong>', <<<HTML
<div class="d-flex flex-column">
    <label><strong>Ruas</strong></label>
    <p>Tentukan ruas-ruas apa saja yang akan dijadikan isian pada formulir perndaftaran nanti</p>
    <hr>
    <div id="editableArea">
        <div class="d-flex flex-column col-12">
            <label id="label-1"><strong>Ruas <b id="columnName1"></b></strong></label>
            <div class="d-flex flex-row">
                <input type="text" class="columnName form-control col-4 noAutoFocus" data-label="1" name="column[1][name]" placeholder="Label yang akan muncul di formulir"/>
                <select class="form-control col-1 noAutoFocus" name="column[1][is_required]">
                    <option value="1">Wajib Diisi</option>
                    <option value="0">Opsional</option>
                </select>
                <select class="form-control col-3 noAutoFocus" name="column[1][field]" data-row="1">
                    <option value="">Pilih Kolom Database</option>
                    {$columns}
                </select>
            </div>
            <div id="advForm1" class="d-none flex-column my-3">
                <div class="d-block">
                    <label><strong>Ruas Mahir</strong></label>
                </div>
                <div class="d-flex flex-row">
                    <input type="text" class="form-control col-6 noAutoFocus" name="column[1][advfield]" placeholder="Nama kolom pada database"/>
                    <select class="form-control col-4 noAutoFocus" name="column[1][advfieldtype]">
                        <option value="">Pilih</option>
                        <option value="int">Angka</option>
                        <option value="varchar">Teks Singkat</option>
                        <option value="text">Teks Paragraf</option>
                        <option value="enum">Daftar</option>
                        <option value="enum_radio">Daftar Radio</option>
                        <option value="text_multiple">Pilihan Ganda</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <button row="1" class="addRow notAJAX btn btn-success btn-sm col-2 my-3">Tambah Selanjutnya</button>
</div>
HTML);

echo $form->printOut();
?>
<script>
    let area = $('#editableArea')
    let addRow = $('.addRow')
    let template = `
    <div id="detailrow{column}" class="d-flex flex-column col-12">
        <label id="label-1"><strong>Ruas <b id="columnName{column}"></b></strong></label>
        <div class="d-flex flex-row">
            <input type="text" class="columnName form-control col-4 noAutoFocus" data-label="{column}" name="column[{column}][name]" placeholder="Label yang akan muncul di formulir"/>
            <select class="form-control col-1 noAutoFocus" name="column[{column}][is_required]">
                <option value="1">Wajib Diisi</option>
                <option value="0">Opsional</option>
            </select>
            <select class="form-control col-3 noAutoFocus" name="column[{column}][field]" data-row="{column}">
                <option value="">Pilih Kolom Database</option>
                <?= $columns ?>
            </select>
            <button class="deleteRow notAJAX btn btn-danger" data-remove="{column}"><i class="fa fa-trash"></i></button>
        </div>
        <div id="advForm{column}" class="d-none flex-column my-3">
            <div class="d-block">
                <span><strong>Ruas Mahir</strong></span>
            </div>
            <div class="d-flex flex-row">
                <input type="text" class="form-control col-6 noAutoFocus" name="column[{column}][advfield]" placeholder="Nama kolom pada database"/>
                <select class="form-control col-4 noAutoFocus" name="column[{column}][advfieldtype]">
                    <option value="">Pilih</option>
                    <option value="int">Angka</option>
                    <option value="varchar">Teks Singkat</option>
                    <option value="text">Teks Paragraf</option>
                    <option value="enum">Daftar</option>
                    <option value="enum_radio">Daftar Radio</option>
                        <option value="text_multiple">Pilihan Ganda</option>
                </select>
            </div>
        </div>
    </div>`

    addRow.click(function(e) {
        e.preventDefault()
        let nextNumber = parseInt($(this).attr('row')) + 1
        area.append(template.replace(/\{column\}/g, nextNumber))
        $(this).attr('row', nextNumber)
    })

    area.on('keyup', '.columnName', function(){
        let labelRow = $(this).data('label')
        $(`#columnName${labelRow}`).html($(this).val())
    })

    area.on('blur', '.columnName', function(){
        let labelRow = $(this).data('label')
        $(`#columnName${labelRow}`).html($(this).val())
    })

    area.on('change', 'select', function(){
        let column = $(this).data('row')

        if ($(this).val() === 'advance') {
            $(`#advForm${column}`).addClass('d-flex')
        } else {
            $(`#advForm${column}`).removeClass('d-flex')
            $(`input[name="column[${column}][advfield]"]`).val('')
            $(`select[name="column[${column}][advfieldtype]"]`).val('')
        }
    })

    area.on('click', '.deleteRow', function(){
        let column = $(this).data('remove')
        $(`#detailrow${column}`).remove()
    })

    area.on('click', 'input,select', function(e){
        e.preventDefault()
    })

    $(document).ready(function(){
        let editorInstance = '';

        $('#mainForm').submit(function() {
            top.toastr.info('Tunggu hingga proses selesai','Info'); 
        })

        DecoupledEditor
            .create(document.querySelector('#contentDesc'),{  
                toolbar: ['heading','bold','italic','link','numberedList','bulletedList']

            })
            .then( editor => {
                const toolbarContainer = document.querySelector('#toolbarContainer');
                toolbarContainer.appendChild( editor.ui.view.toolbar.element );
                editorInstance = editor
            })
            .catch( error => {
                console.log(error);
            });

        // when form submited retrive content
        // and put into hidden textarea
        $('#mainForm').submit(function(){
            $(this).append('<textarea name="info[desc]" class="d-none">' + editorInstance.getData() + '</textarea>');
        })

        $('#dataList > tbody').prepend(`
        <tr>
            <td colspan="3">
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">Peringatan</h4>
                    <p>Skema yang sudah dibuat tidak dapat diubah. Pastikan semua telah terisi dengan benar.</p>
                </div>
            </td>
        </tr>`) 
    })
</script>