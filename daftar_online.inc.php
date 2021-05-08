<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-05-08 09:15:31
 * @modify date 2021-05-08 09:15:31
 * @desc [description]
 */

// set meta
$meta = $sysconf['selfRegistration'];

// set page title
$page_title = $meta['title'];

// Attribute
$attr = [
    'action' => $_SERVER['PHP_SELF'] .'?p=daftar_online',
    'method' => 'POST',
];

// require helper
require SB.'plugins'.DS.'member_self_registration'.DS.'helper.php';

if (isset($_POST['memberName']))
{
    saveRegister();
}

// check dep
if (!file_exists(SB.'plugins/member_self_registration/bs4formmaker.inc.php'))
{
    echo '<div class="bg-danger p-2 text-white">';
    echo 'Folder <b>plugins/member_self_registration/</b> tidak ada. Pastikan folder itu tersedia.';
    echo '</div>';
}
else
{
    // set key
    define('DR_INDEX_AUTH', '1');

    // require helper
    require SB.'plugins/member_self_registration/bs4formmaker.inc.php';

    // create form
    createForm($attr);

    // CSRF Token
    echo \Volnix\CSRF\CSRF::getHiddenInputString();

    // Member name
    createFormContent(__('Member Name'), 'text', 'memberName', 'Isikan nama anda', true, '', true);

    // Birth Date
    createFormContent(__('Birth Date'), 'date', 'memberBirth');

    // Institution
    createFormContent(__('Institution'), 'text', 'memberInst', 'Isikan institusi anda');

    // Jenis Kelamin
    createSelect(__('Sex'), 'memberSex', [['label' => __('Male'), 'value' => 1],['label' => __('Female'), 'value' => 0]]);

    // Member Address
    createFormContent(__('Address'), 'textarea', 'memberAddress', 'Isikan alamat anda');

    // Member Mail Address
    createFormContent(__('Phone Number'), 'text', 'memberPhone', 'Isikan Nomor Telepon/HP anda');

    // Member Mail Address
    createFormContent(__('E-mail'), 'text', 'memberEmail', 'Isikan email/surel anda');

    // Member Password
    createFormContent('Password', 'password', 'memberPassword1', 'Isikan Password anda');
    createFormContent('Tulis ulang password', 'password', 'memberPassword2', 'Isikan Password anda');

    // captcha
    if ((int)$meta['useRecaptcha'] === 1 && $sysconf['captcha']['member']['enable'])
    {
        // require captcha
        require_once LIB . $sysconf['captcha']['member']['folder'] . '/' . $sysconf['captcha']['member']['incfile'];

        // public key
        $publickey = $sysconf['captcha']['member']['publickey'];

        createAnything('Tekan Saya Bukan Robot', '<div class="captchaMember">'.recaptcha_get_html($publickey).'</div>');
    }

    // Button
    createFormButton('Daftar', 'submit', 'register');

    // Iframe
    createBlindIframe('blindIframe');

    // close tag
    closeTag('div');
    closeTag('form');
}