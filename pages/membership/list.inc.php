<?php
defined('INDEX_AUTH') or die('Direct access is not allowed!');

if ($schemas->rowCount() < 1) {
?>
    <div class="w-full d-flex flex-column align-items-center justify-content-center p-5" style="background-color: whitesmoke">
        <img src="<?= MSWB ?>static/images/notfound.png"/ style="width: 250px">
        <h4 class="font-weight-bold mt-2">Yah</h4>
        <p>Belum ada skema</p>
        <a href="<?= pluginUrl(['section' => 'add_schema']) ?>" class="btn btn-outline-primary">Buat Yuk</a>
    </div>
<?php
} else {
    $addUrl = pluginUrl(['section' => 'add_schema']);
    echo '<div id="schemas" class="my-5 mx-3 d-flex flex-wrap">';
    while ($result = $schemas->fetchObject()) {
        $bgColor = substr(md5($result->name), 0,6);
        $fnColor = textColor($bgColor);
        $info = json_decode($result->info);
        $info->desc = substr(strip_tags($info->desc), 0,100);

        $checked = '';
        if ($result->status == 1) $checked = 'checked';

        $result->status = $result->status == 0 ? 'Aktifkan' : 'Non-Aktifkan';
        $previewUrl = pluginUrl(['headless' => 'yes', 'schema_id' => $result->id, 'section' => 'form_preview']);
        echo <<<HTML
        <div class="card col-4">
            <div class="card-img-top rounded-lg" style="background-color: #{$bgColor}; color: #{$fnColor}; height: 20px"></div>
            <div class="card-body">
                <h5 class="card-title font-weight-bold">{$result->name}</h5>
                <p class="card-text d-flex flex-column">
                    <label><strong>Judul Form</strong></label>
                    {$info->title}
                    <label><strong>Deskripsi</strong></label>
                    {$info->desc}
                </p>
                <div class="d-flex flex-row justify-content-between">
                    <div class="custom-control custom-switch">
                        <input onchange="enable({$result->id})" type="checkbox" class="custom-control-input" data-uid="{$result->id}" id="checkbox{$result->id}" {$checked}>
                        <label class="custom-control-label" for="checkbox{$result->id}">{$result->status}</label>
                    </div>
                    <a href="{$previewUrl}" class="btn btn-outline-primary notAJAX openPopUp" height="500px" title="Pratinjau">Pratinjau Formulir</a>
                </div>
            </div>
        </div>
        HTML;
    }
    echo '</div>';
    $actionUrl = pluginUrl(reset: true);
    $url = pluginUrl();
    echo <<<HTML
    <script>
        function enable(id)
        {
            let el = '';
            $('#schemas').find('input[type="checkbox"]').each(function() {
                if(id != $(this).data('uid') && this.checked) {
                    $(this).trigger('click')
                } else if (id == $(this).data('uid')) {
                    $(this).trigger('click')
                }
            })

            setTimeout(() => {
                $.post('{$actionUrl}', {schema_id:id}, function() {    
                        $('#mainContent').simbioAJAX('{$url}') 
                })
            }, 3500);
        }
    </script>
    HTML;
}