<?php
use SLiMS\Url;
$schema = getActiveSchemaData();

if (isset($_POST['form'])) {
    dd($_POST);
}

if (!isset($opac)) $opac = $this;

$path = str_replace(['\'', '"'], '', strip_tags($_GET['p']));
echo formGenerator($schema, actionUrl: Url::getSelf(fn($self) => $self . '?p=' . $path), opac: $opac);