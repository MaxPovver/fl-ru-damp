<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/num_to_word.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pskb.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/odt2pdf.php");

echo "<pre>";

$UID      = 237958;
$STAGE_ID = 3524;
$DATE     = '27.09.2012';

$sbr = sbr_meta::getInstanceLocal($UID);
$error = '';
$stage = $sbr->initFromStage($STAGE_ID);
$doc_file = $stage->generateFrlActPdrd($error, $DATE);
$doc = array(
    'stage_id' => $stage->id, 
    'file_id' => $doc_file->id, 
    'status' => sbr::DOCS_STATUS_PUBL, 
    'access_role' => sbr::DOCS_ACCESS_FRL,
    'owner_role' => 0, 
    'type' => sbr::DOCS_TYPE_ACT
);

$stage->sbr->addDocR($doc);

$doc_file = $stage->generateTzPdrd($error, $DATE);

$doc = array(
    'stage_id' => $stage->id, 
    'file_id' => $doc_file->id, 
    'status' => sbr::DOCS_STATUS_PUBL, 
    'access_role' => sbr::DOCS_ACCESS_FRL,
    'owner_role' => 0, 
    'type' => sbr::DOCS_TYPE_TZ_PDRD
);
$stage->sbr->addDocR($doc);

exit;