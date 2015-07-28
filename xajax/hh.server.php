<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/hh.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/hh.php");
session_start();
get_uid(false);

/**
 * @see hh::addHHSpecProf()
 */
function addHHSpecProf($hh_field, $hh_spec_id, $prof_id) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
    $objResponse = new xajaxResponse();
    $hh = new hh();
    $hh_field   = intval($hh_field);
    $hh_spec_id = intval($hh_spec_id);
    $prof_id    = intval($prof_id);
    ob_start();
    $pname = professions::GetProfNameWP($prof_id, '::');
    if(!$hh->addHHSpecProf($hh_field, $hh_spec_id, $prof_id))
        $err = "—пециализаци€ '{$pname}' уже прив€зана к данному разделу.";
    ob_end_clean();
    $objResponse->call("addHHSpecProf", $pname, $err);
    return $objResponse;
}

/**
 * @see hh::delHHSpec()
 */
function delHHSpec($hh_spec_id) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
    $objResponse = new xajaxResponse();
    $hh = new hh();
    $hh_spec_id = intval($hh_spec_id);
    $hh->delHHSpec($hh_spec_id);
    $objResponse->call("delHHSpec", NULL, $hh_spec_id);
    return $objResponse;
}

/**
 * @see hh::delProf()
 */
function delProf($hh_field, $prof_id, $hh_spec_id) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
    $objResponse = new xajaxResponse();
    $hh = new hh();
    $hh_field   = intval($hh_field);
    $hh_spec_id = intval($hh_spec_id);
    $prof_id    = intval($prof_id);
    $hh->delProf($hh_field, $prof_id, $hh_spec_id);
    $objResponse->call("delProf", NULL, $hh_field, $prof_id, $hh_spec_id);
    return $objResponse;
}


$xajax->processRequest();

?>
