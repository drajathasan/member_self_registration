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
    $iterateAt = 0;
    $checked = '';
    $activeId = $activeSchema->rowCount() ? $activeSchema->fetchObject()->id : 0;

    echo '<div id="schemas" class="my-5 mx-3 d-flex flex-wrap" schema-active="' . $activeId . '">';
    while ($result = $schemas->fetchObject()) {
        $iterateAt++;
        $bgColor = substr(md5($result->name), 0,6);
        $fnColor = textColor($bgColor);
        $info = json_decode($result->info);
        $info->desc = substr(strip_tags($info->desc), 0,100);

        if ($result->status == 1) $checked = 'checked';

        $result->status = $result->status == 0 ? 'Aktifkan' : 'Non-Aktifkan';
        $previewUrl = pluginUrl(['headless' => 'yes', 'schema_id' => $result->id, 'section' => 'form_preview']);
        $deleteUrl = pluginUrl(['headless' => 'yes', 'section' => 'list']);
        $exportUrl = pluginUrl(['action' => 'export', 'schema_id' => $result->id]);
        echo <<<HTML
        <div class="card col-4">
            <div class="card-img-top rounded-lg" style="background-color: #{$bgColor}; color: #{$fnColor}; height: 20px"></div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title font-weight-bold">{$result->name}</h5>
                    <a href="{$exportUrl}" target="blindSubmit" title="Ekspor skema" class="btn btn-outline-info">Ekspor</a>

                </div>
                <p class="card-text d-flex flex-column">
                    <label><strong>Judul Form</strong></label>
                    {$info->title}
                    <label><strong>Deskripsi</strong></label>
                    {$info->desc}
                </p>
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input checkbox" data-uid="{$result->id}" id="checkbox{$result->id}" {$checked}>
                        <label class="custom-control-label" for="checkbox{$result->id}">{$result->status}</label>
                    </div>
                    <div>
                        <a href="{$deleteUrl}" data-uid="{$result->id}" class="schemaDelete btn btn-outline-danger">Hapus</a>
                        <a href="{$previewUrl}" class="btn btn-outline-primary notAJAX openPopUp" height="500px" title="Pratinjau">Pratinjau Formulir</a>
                    </div>
                </div>
            </div>
        </div>
        HTML;
        $checked = '';
    }
    echo '</div>';
    $actionUrl = pluginUrl(reset: true);
    $url = pluginUrl();
    echo <<<HTML
    <script>
        $('input[type="checkbox"]').change(function(){
            let activeSchema = $('#schemas').attr('schema-active')
            
            if (activeSchema != 0 && activeSchema != $(this).data('uid')) {
                console.log(activeSchema)
                $(`#checkbox\${activeSchema}`).trigger('click')
            }

            let uid = $(this).data('uid')
            if (this.checked === false) uid = 0

            $.post('{$actionUrl}', {schema_id:uid, action: 'active_schema'}, function(){
                setTimeout(() => {
                    $('#mainContent').simbioAJAX('{$url}')
                }, 1000);
            })
        })

        $('.schemaDelete').click(function(e) {
            e.preventDefault()
            let ask = confirm('Menghapus skema juga akan menghapus data pendaftaran yang sudah ada. Apakah anda yakin?')

            if (!ask) {
                return
            }

            $.post('{$actionUrl}', {schema_id: $(this).data('uid'), action: 'drop_schema'}, function() {
                $('#mainContent').simbioAJAX('{$url}')
            })
        })
    </script>
    HTML;
}