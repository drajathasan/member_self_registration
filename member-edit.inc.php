<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-05-08 09:16:04
 * @modify date 2021-05-08 09:16:04
 * @desc [description]
 */
// Attribute
$attribute = (isset($meta['separateTable']) && (int)$meta['separateTable'] == 1) 
                ? 
                ["SELECT * FROM member_online WHERE id='{itemID}'"] 
                : 
                ["SELECT * FROM member WHERE member_id='{itemID}'"];

// Is read only?
$readonly = (isset($meta['editableData']) && (int)$meta['editableData'] !== 1) ?? 'readonly';

/* RECORD FORM */
// $itemID
$itemID = $dbs->escape_string(trim(isset($_POST['itemID'])?$_POST['itemID']:'')); 
$rec_q = $dbs->query(str_replace('{itemID}', $itemID, $attribute[0]));
$rec_d = $rec_q->fetch_assoc();

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
$form->submit_button_attr = 'name="saveDataMember" value="'.__('Save').'" class="s-btn btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// edit mode flag set
if ($rec_q->num_rows > 0) {
    $form->edit_mode = true;
    // record ID for delete process
    $form->record_id = $itemID;
    // form record title
    $form->record_title = $rec_d['member_name'];
    // submit button attribute
    $form->submit_button_attr = 'name="saveDataMember" value="'.__('Save').'" class="s-btn btn btn-primary"';
}

// member code
$str_input  = '<div class="container">';
$str_input .= '<div class="row">';
$str_input .= simbio_form_element::textField('text', 'memberID', $rec_d['member_id']??'', 'id="memberID" onblur="ajaxCheckID(\''.SWB.'admin/AJAX_check_id.php\', \'member\', \'member_id\', \'msgBox\', \'memberID\')" class="form-control col-4"');
$str_input .= '<div id="msgBox" class="col mt-2"></div>';
$str_input .= '</div>';
$str_input .= '</div>';
$form->addAnything(__('Member ID').'*', $str_input);

// member name
$form->addTextField('text', 'memberName', __('Member Name').'*', $rec_d['member_name']??'', 'class="form-control" style="width: 50%;"');

// member institution
$form->addTextField('text', 'instName', __('Institution'), $rec_d['inst_name']??'', 'class="form-control" style="width: 100%;"');

// member birth date
$form->addDateField('birthDate', __('Birth Date').'*', $rec_d['birth_date']??'','class="form-control"');

// member gender
$gender_chbox[0] = array('1', __('Male'));
$gender_chbox[1] = array('0', __('Female'));
$form->addRadio('memberSex', __('Sex'), $gender_chbox, !empty($rec_d['gender'])?$rec_d['gender']:'0');

// member address
$form->addTextField('textarea', 'memberAddress', __('Address'), $rec_d['member_address']??'', 'rows="2" class="form-control" style="width: 100%;"');

// member phone
$form->addTextField('text', 'memberPhone', __('Phone Number'), $rec_d['member_phone']??'', 'class="form-control" style="width: 50%;"');

// member is_pending
$form->addCheckBox('isPending', 'Aktifkan Member', array( array('1', __('Yes')) ), '');

// member email
$form->addTextField('text', 'memberEmail', __('E-mail'), $rec_d['member_email']??'', 'class="form-control" style="width: 40%;" class="form-control"');

// print out the form object
echo $form->printOut();

if ((int)$meta['editableData'] === 0) 
{
?>
<script>
    document.querySelectorAll('input, textarea').forEach((elem,index) => {
        if (elem.getAttribute('name') !== 'memberID')
        {
            elem.readOnly = true;
        }
    });
</script>
<?php
}