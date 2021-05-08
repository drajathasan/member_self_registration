<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-05-08 09:15:43
 * @modify date 2021-05-08 09:15:43
 * @desc [description]
 */

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// Aktifkan Daftar Online
$form->addSelectList('selfRegistrationActive', 'Aktifkan Daftar Online?', [['0','Tidak'],['1','Ya']], $meta['selfRegistrationActive'] ?? '', 'class="select2"', 'Aktifkan atau tidak');

// form title
$form->addTextField('text', 'title', 'Judul Form' . '*', $meta['title'] ?? '', 'rows="1" class="form-control"', 'Judul Form');

// Auto Active?
$form->addSelectList('autoActive', 'Keanggotaan Otomatis Aktif?', [['0','Tidak'],['1','Ya']], $meta['autoActive'] ?? '', 'class="select2"', 'Aktifkan atau tidak');

// Memisahkan antara anggota aktif dengan anggota online?
$form->addSelectList('separateTable', 'Memisahkan tabel member aktif dan member online?', [['1','Ya'],['0','Tidak']], $meta['separateTable'] ?? '', 'class="select2"', 'Dengan memisahkan, setidaknya ketika member sudah terdaftar tidak otomatis bisa login dan dapat meminjam buku.');

// Memisahkan antara anggota aktif dengan anggota online?
$form->addSelectList('editableData', 'Apakah data yang sudah registrasi dapat di edit?', [['0','Tidak'], ['1','Ya']], $meta['editableData'] ?? '', 'class="select2"', 'Edit data ketika sudah terdaftar namun ketika belum aktif.');

// Menggunakan Re-Captcha Active?
$form->addSelectList('useRecaptcha', 'Menggunakan Google Re-Captcha?', [['0','Tidak'],['1','Ya']], $meta['useRecaptcha'] ?? '', 'class="select2"', 'Menggunakan recaptcha untuk mengurangi serangan spam');

// Info setelah registrasi
$form->addTextField('textarea', 'regisInfo', 'Informasi dan Kontak terkait pendaftaran.' . '*', $meta['regisInfo'] ?? '', 'rows="1" class="form-control" style="margin-top: 0px; margin-bottom: 0px; height: 122px;"', 'Informasi dan Kontak');

// print out the form object
echo $form->printOut();