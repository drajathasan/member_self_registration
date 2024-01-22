<?php
use SLiMS\Url;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

$schema = getActiveSchemaData();
if ($schema === null) throw new Exception("Tidak ada Skema yang aktif.");

$action = $_POST['action']??$_GET['actuion']??null;

if ($action !== null) action('save', ['schema' => $schema]);

if (!isset($opac)) $opac = $this;

$path = str_replace(['\'', '"'], '', strip_tags($_GET['p']));
echo formGenerator($schema, actionUrl: Url::getSelf(fn($self) => $self . '?p=' . $path), opac: $opac);