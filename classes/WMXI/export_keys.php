<?php
require_once('WMSigner.php');

$wmid = '123456789012';
$key = array(
 'pass' => 'zzzzzzzzzz',
 'file' => 'C:\path\to\285045456218.kwm '
);
$wms = new WMSigner($wmid, $key);
print_r($wms->ExportKeys());