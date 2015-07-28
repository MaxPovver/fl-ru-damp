<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
session_start();
get_uid(false);
$header = ABS_PATH."/header.php";
$footer = ABS_PATH."/footer.html";
$content = ABS_PATH."/unsubscribe/inner.php";
$page_title = "Отписаться от рассылки";

$captcha = null;
$captchanum = uniqid('', true);

$action = __paramInit("string", null, "action");
$ukey   = __paramInit("string", "ukey");
$type   = __paramInit("string", "type");
$type   = in_array($type, array('new_projects', 'mailer'))?$type:NULL;

$info = users::GetUserInfoByUnsubscribeKey($ukey);

//Если ключ устарел
if(!count($info)){
    include ABS_PATH . '/404.php';
    exit;    
}

$email = $info["email"]; 

//Если тип отписки не соответствует роли то 404
//@todo: пока в этом нет необходимости
/*
if(in_array($type, array('new_projects')) && $info["role"][0] != 0){
    include ABS_PATH . '/404.php';
    exit;
}
*/

$alert = "";
//TODO: Избавиться от этого сообщения
if (!$ukey) {
    $alert = "Не удалось найти пользователя";
}
if ($action == "unsubscribe") {    
    $num = __paramInit('string', null, 'rndnum');
    $captchanum = __paramInit("string", null, "captchanum");
    $captcha = new captcha($captchanum);
    if (!$captcha->checkNumber($num)) {
        $alert = 'Введены неверные символы';            
    }
    if (!$alert) {
    	$class = 'users';
    	$vacancy = 0;
        
        if ($info["role"][0] == 0) {
            $class = 'freelancer';
            $vacancy = array();
        }
        
        $user = new $class();
        global $DB;
        
        if($type == 'new_projects') {
            
            if($info["role"][0] == 1){
                
                //@todo: UpdateSubscr2 пока только для работодателей
                $info['subscr'][12] = 0;
                $user->UpdateSubscr2($info["uid"],$info['subscr']);
                
            }else{
                
                //@todo: жуть медот :)    
                $user->UpdateSubscr(
                    $info["uid"], 
                    $info['subscr'][0], 
                    $vacancy, 
                    $info['subscr'][2], $info['subscr'][3], $info['subscr'][4], 
                    $info['subscr'][5], $info['subscr'][6], $info['subscr'][7], 
                    $info['subscr'][8], $info['subscr'][9], $info['subscr'][10], 
                    $info['subscr'][11], $info['subscr'][12], $info['subscr'][13],
                    $info['subscr'][14], 0, $info['subscr'][15]);
                
            }
        } elseif ($type == 'mailer') {
            
            $info['subscr'][7] = 0;
            $user->UpdateSubscr2($info["uid"],$info['subscr']);

        } else {
            
            $user->UpdateSubscr($info["uid"], 0, $vacancy, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            commune::clearSubscription($info["uid"]);
            
        }
        
        $content = ABS_PATH."/unsubscribe/success.php"; 
    }
}

if (!$captcha) {
    $captcha = new captcha($captchanum);
}
$css_file = "/css/block/b-captcha/b-captcha.css";

include ABS_PATH."/template3.php";