<?php
defined('INDEX_AUTH') or die('Direct access is not allowed!');

use SLiMS\DB;

$schemas = DB::getInstance()->query('select * from self_registartion_schemas');

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
    echo '<div class="my-3 mx-5">';
    echo <<<HTML
    <div class="d-flex flex-row-reverse my-2">
        <a href="{$addUrl}" class="btn btn-secondary">Buat Skema Baru</a>
    </div>
    HTML;
    while ($result = $schemas->fetchObject()) {
        $bgColor = substr(md5($result->name), 0,6);
        $fnColor = bestTextColor($bgColor);

        echo <<<HTML
        <div class="card" style="width: 18rem;">
            <div class="card-img-top rounded-lg" style="background-color: #{$bgColor}; color: #{$fnColor}; height: 100px"></div>
            <div class="card-body">
                <h5 class="card-title">{$result->name}</h5>
                <p class="card-text">{$result->info}</p>
            </div>
        </div>
        HTML;
    }
    echo '</div>';
}