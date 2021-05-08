<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2019-11-01 05:42:50
 * @modify date 2021-05-08 05:26:16
 * @desc [description]
 */

defined('DR_INDEX_AUTH') OR exit('No direct script access allowed');

function startAttrForm($_card_title)
{
	echo '<div class="card card-primary">';
  	echo '<div class="card-header">';
    echo '<h2 class="card-title">'.$_card_title.'</h2>';
  	echo '</div>';
}

function createForm($_array_main_attr)
{
	if (count($_array_main_attr) < 1) {
		exit('Error creating form!. Please fill attribute');
	}

	foreach ($_array_main_attr as $key => $value) {
		$_main_attr = '';
		foreach ($_array_main_attr as $key => $value) {
			$_main_attr .= strip_tags($key).' = "'.strip_tags($value).'" ';
		}
	}

	echo '<form role="form" '.$_main_attr.'>';
	echo '<div class="card-body">';
}

function createSeparator($name, $hidden = true)
{	$hidden = ($hidden)?'d-none':'d-block';
	echo '<div class="separator sep-'.$name.' '.$hidden.'">';
}

function createHiddenContent($_name, $_value)
{
	$id  = str_replace(array('[',']','KEY'), '', $_name);
	echo '<input type="hidden" name="'.$_name.'" id="'.$id.'" value="'.$_value.'" />';
}

function createFormContent($_label, $_type, $_name, $_place_holder = '', $_is_required = true, $_default_value = '', $_autofocus = '')
{
	$_value = '';
	// set default value in edit mode
	if (!empty($_default_value)) {
		$_value = strip_tags($_default_value);
		if ($_type == 'text') {
			$_value = ' value="'.strip_tags($_default_value).'"';
		}
	}
	// set tag
	echo '<div class="form-group">';
    echo '<label for="exampleInputEmail1">'.$_label.'</label>';

    $required = '';
    if ($_is_required)
    {
        $required = 'required';
    }
   
	switch ($_type) {
		case 'textarea':
			echo '<textarea class="form-control" name="'.$_name.'" rows="3" placeholder="'.$_place_holder.'" required>'.$_value.'</textarea>';
			break;
		
		case 'password':
			echo '<input type="'.$_type.'" name="'.$_name.'" class="form-control" '.$_value.' id="exampleInputEmail1" placeholder="'.$_place_holder.'" required '.$_autofocus.'/>';
			break;
		
		default:
		echo '<input type="'.$_type.'" name="'.$_name.'" class="form-control" '.$_value.' id="exampleInputEmail1" placeholder="'.$_place_holder.'" '.$required.' '.$_autofocus.'/>';
			break;
	}

  	echo '</div>';
}

function createUploadArea($_label, $_name, $_edit = false)
{
	$_required = ' require="true"';
	if ($_edit) {
		$_required = '';
	}

	echo '<div class="form-group">';
	echo '<label for="exampleInputFile">'.$_label.'</label>';
	echo '<div class="input-group">';
	echo '<div class="custom-file">';
	echo '<input type="file" class="custom-file-input" name="'.$_name.'" id="exampleInputFile" '.$_required.'>';
	echo '<label class="custom-file-label" for="exampleInputFile">Pilih File - besar file maksimal 1 MB</label>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}

function createSelect($_label, $_name, $_option, $_attr = '', $_default_value = '')
{
	echo '<div class="form-group">';
	echo '<label>'.$_label.'</label>';
	echo '<select class="form-control '.$_attr.'" name="'.$_name.'">';
	echo '<option value="0">Pilih</option>';
	foreach ($_option as $value) {
		if (!empty($_default_value) AND ($_default_value == $value['value'])) {
			echo '<option value="'.$value['value'].'" selected>'.$value['label'].'</option>';	
		} else {
			echo '<option value="'.$value['value'].'">'.$value['label'].'</option>';	
		}
	}
	echo '</select>';
	echo '</div>';
}

function createSelect2($_label, $_name, $_option, $_default_value = '', $_addtional_attr = '', $_addtional_class = '')
{
	echo '<div class="form-group">';
	echo '<label>'.$_label.'</label>';
	echo '<select class="js-select-two '.$_addtional_class.'" name="'.$_name.'">';
	foreach ($_option as $value) {
		if (!empty($_default_value) AND ($_default_value == $value['value'])) {
			echo '<option value="'.$value['value'].'" selected>'.$value['label'].'</option>';	
		} else {
			echo '<option value="'.$value['value'].'">'.$value['label'].'</option>';	
		}
	}
	echo '</select>';
	echo '</div>';
}

function createFormButton($_label, $_type, $_name, $_opt_class = '')
{
	echo '<div class="card-footer">';
    echo '<button type="'.$_type.'" name="'.$_name.'" class="btn btn-primary float-right '.$_opt_class.'">'.$_label.'</button>';
    echo '</div>';
}

function createAlert($_str_msg)
{
	echo '<div class="form-group">';
	echo '<div class="alert alert-success" role="alert">';
  	echo strip_tags($_str_msg);
	echo '</div>';
	echo '</div>';
}

function createBlindIframe($name, $hidden = true)
{
	$hidden = ($hidden)?'class="d-none" ':'class="d-block w-100" ';
	echo '<iframe name="'.$name.'" '.$hidden.'></iframe>';
}

function interactiveIframe($name)
{
	echo '<iframe name="'.$name.'" class="w-100 mt-5" style="border: 1px solid #3e3e3e; height: 500px"></iframe>';
}

function createAnything($_label, $_str_tag)
{
	// set tag
	echo '<div class="form-group">';
    echo '<label for="exampleInputEmail1" class="d-block w-100">'.strip_tags($_label).'</label>';
	echo $_str_tag;
	echo '</div>';
}

function closeTag($_str_tag_name)
{
	echo '</'.$_str_tag_name.'>';
}

function jsAlert($_msg)
{
	echo '<script>';
	echo 'alert("'.strip_tags($_msg).'")';
	echo '</script>';
}

function simbioRedirect($url, $_position = 'top.', $_type = 'DIRECT')
{
	echo '<script>';
	if ($_type == 'POST') {
		$_data = '';
		foreach ($_POST as $key => $value) {
			$_data .= '&'.strip_tags($key).'=\''.strip_tags($value).'\'';
		}
		echo $_position."$('#mainContent').simbioAJAX('".$url."', {method: 'post', addData: ".$_data."})";
	} else {
		echo $_position."$('#mainContent').simbioAJAX('".$url."')";
	}
	echo '</script>';
}

function createJSArea($_str_js)
{
	echo '<script>'."\n";
	echo $_str_js;
	echo '</script>'."\n";
}