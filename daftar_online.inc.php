<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-05-08 09:15:31
 * @modify date 2022-03-28 13:30:19
 * @desc [description]
 */ 

// set meta
$meta = $sysconf['selfRegistration']??[];

if ((int)($meta['selfRegistrationActive']??0) === 1)
{

    // set page title
    $page_title = $meta['title'];

    // Attribute
    $attr = [
        'action' => $_SERVER['PHP_SELF'] .'?p=daftar_online',
        'method' => 'POST',
        'enctype' => 'multipart/form-data'
    ];

    // require helper
    require __DIR__ . DS . 'helper.php';

    if (isset($_POST['memberName']))
    {
        saveRegister();
    }

    // check dep
    if (!file_exists(SB.'plugins'.DS.'member_self_registration'.DS.'bs4formmaker.inc.php'))
    {
        echo '<div class="bg-danger p-2 text-white">';
        echo 'Folder <b>'.SB.'plugins'.DS.'member_self_registration'.DS.'bs4formmaker.inc.php</b> tidak ada. Pastikan folder itu tersedia.';
        echo '</div>';
    }
    else
    {
        // set key
        define('DR_INDEX_AUTH', '1');

        // require helper
        require SB.'plugins'.DS.'member_self_registration'.DS.'bs4formmaker.inc.php';

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

        // Member type
        $list = [];
        foreach (membershipApi::getMembershipType($dbs) as $id => $data) {
            $list[] = [
                'label' => $data['member_type_name'],
                'value' => $id,
            ];
        }

        createSelect(__('Member Type'), 'memberType', $list);

        // Jenis Kelamin
        createSelect(__('Sex'), 'memberSex', [['label' => __('Male'), 'value' => 1],['label' => __('Female'), 'value' => 0]]);

        // Member Address
        createFormContent(__('Address'), 'textarea', 'memberAddress', 'Isikan alamat anda');

        // Member Mail Address
        createFormContent(__('Phone Number'), 'text', 'memberPhone', 'Isikan Nomor Telepon/HP anda');

        // Photo Profile
        if (isset($meta['withImage']) && (bool)$meta['withImage'] === true)
            createUploadArea('Foto Profil', 'photoprofil', 'Pilih file - <strong class="text-danger">Dilarang menggunakan foto selfie, gunakan foto rapih dan sopan.</strong>');

        // Member Mail Address
        createFormContent(__('E-mail'), 'text', 'memberEmail', 'Isikan email/surel anda');

        // Member Password
        createPasswordShow([
            'Password',
            'Tulis ulang password'
        ], ['memberPassword1', 'memberPassword2'], function(){
            echo <<<HTML
                <input type="checkbox" id="showPassword"/>  <label class="fa fa-eye"></label> Tampilkan Password
                <script>
                    document.querySelector('#showPassword').onclick = function () {
                        if(document.querySelector('#showPassword').checked) {
                            document.querySelectorAll('input[name="memberPassword1"], input[name="memberPassword2"]').forEach(el => {
                                    el.setAttribute('type', 'text');
                            })
                        } else {
                            document.querySelectorAll('input[name="memberPassword1"], input[name="memberPassword2"]').forEach(el => {
                                el.setAttribute('type', 'password');
                            })
                        }
                    }
                </script>
            HTML;
        });

        // captcha
        if ((int)$meta['useRecaptcha'] === 1 && $sysconf['captcha']['member']['enable'])
        {
            // require captcha
            require_once LIB . $sysconf['captcha']['member']['folder'] . DS . $sysconf['captcha']['member']['incfile'];

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
}
else
{
    echo '<div class="bg-danger p-2 text-white">';
    echo 'Form sedang tidak aktif, silahkan hubungi petugas untuk mengaktifkannya.';
    echo '</div>';
}
