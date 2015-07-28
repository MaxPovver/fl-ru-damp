<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_const.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/functions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_phone.php");

session_start();

//$g_page_id = "0|102";
$rpath="../";

$stretch_page = true;
$showMainDiv  = true;

// Формируем JS внизу страницы
define('JS_BOTTOM', true);

$css_file = array( '/css/block/b-horizontal-line/b-horizontal-line.css', '/css/block/b-button/_mini/b-button_mini.css', '/css/block/b-button/__icon/b-button__icon.css' );
//$js_file[] = "/tservices_categories_js.php";
$js_file[] = "mootools-form-validator.js";
$js_file[] = "tservices/fineuploader.js";
$js_file[] = "tservices/tservices.js";

$content = "content.php";
$header = "../header.php";
$footer = "../footer.html";

$solt = '26bFRs2mgwuX_';

//------------------------------------------------------------------------------

$uid = get_uid(false);

if(!$uid) 
{
    header("Location: /404.php"); 
    exit;
}

//------------------------------------------------------------------------------

$is_adm = hasPermissions('tservices');

if(!$is_adm && is_emp()) 
{
    header("Location: /404.php"); 
    exit;
}

//------------------------------------------------------------------------------

$action = __paramInit('string','action');
$name   = trim($_GET['user']);

$user_obj = new users();
$user_obj->GetUser($name);
$is_owner = $user_obj->uid == $uid;




if(!$user_obj->uid || !($is_owner || $is_adm)) 
{
    header("Location: /404.php"); 
    exit;
}

//------------------------------------------------------------------------------

$tservice = new tservices($user_obj->uid);
$errors = array();
$is_exist_feedbacks = 0;

switch($action)
{
    case 'edit':
        $tuid = __paramInit('int','tuid',NULL,0);
        
        if($tuid <= 0 || !$tservice->getByID($tuid))
        {
            header("Location: /404.php"); 
            exit; 
        }

        if($is_adm) $tservice->is_angry = FALSE;
        
        $action = __paramInit('string',NULL,'action','');
        
        if($action == 'save' && $tuid == __paramInit('int',NULL,'id',0))
        {
            $is_exist_feedbacks = $tservice->isExistFeedbacks($tuid);
            $errors = tu_validation($tservice,$is_exist_feedbacks);
            if(count($errors) == 0)
            {
                if($tservice->update($tuid))
                {
                    $sess_p = __paramInit('string', NULL, 'preview_sess', NULL);
                    if($sess_p)
                    {
                        $tservice->addAttachedFiles($sess_p, $tuid, true);
                    }
                    
                    $sess = __paramInit('string', NULL, 'uploader_sess', NULL);
                    if($sess)
                    {
                        $tservice->addAttachedFiles($sess, $tuid);
                    }
                    
                    //message?
                    $msg_type = ($tservice->active === 't')?'update_publish':'update';
                    tservices_helper::setFlashMessageFromConstWithTitle($msg_type,$tservice->title);

                    $tu_card_uri = sprintf('/tu/%d/%s.html',$tuid,tservices_helper::translit($tservice->title));
                    header("Location: {$tu_card_uri}");
                    exit;
                }
            }
        }        

        //SEO
        $page_title = 'Редактирование типовой услуги на FL.ru';
        //$page_descr = ''
        //$page_keyw = '';
        
        break;
        
//------------------------------------------------------------------------------  
    
   //Пока нет необходимости в этом экшене 
   //поскольку снимаем и публикуем через редактирование     
        
    /*
    case 'close':
        
        $tuid = __paramInit('int',NULL,'id',0);
        
        if($tuid <= 0 || !($result = $tservice->isExists($tuid)))
        {
            header("Location: /404.php"); 
            exit; 
        }
        
        $tservice->switchActive($tuid);
        
        //message? $result['title']
        $msg_type = ($result['active'] == 't')?'hide':'show';
        tservices_helper::setFlashMessageFromConstWithTitle($msg_type,$result['title']);
        
        $tu_list_uri = sprintf('/users/%s/tu/',$user_obj->login);
        header("Location: {$tu_list_uri}");
        exit;        
        
        break;
    */
//------------------------------------------------------------------------------        
        
    case 'delete':
        
        $tuid = __paramInit('int',NULL,'id',0);
        
        if($tuid <= 0 || !($result = $tservice->isExists($tuid)) || (@$result['minus_feedbacks'] > 0 && !$is_adm))
        {
            header("Location: /404.php"); 
            exit; 
        }
        
        $tservice->deleteByID($tuid);
        
        //message? $result['title']
        tservices_helper::setFlashMessageFromConstWithTitle('deleted',$result['title']);
        
        $tu_list_uri = sprintf('/users/%s/tu/',$user_obj->login);
        header("Location: {$tu_list_uri}");
        exit;
                
        break;
        
//------------------------------------------------------------------------------    
    
    default:
        $action = __paramInit('string',NULL,'action','');
        if($action == 'save')
        {
            $errors = tu_validation($tservice);
            if(count($errors) == 0)
            {
                $id = $tservice->create();
                
                $sess = __paramInit('string', NULL, 'uploader_sess', NULL);
                if($sess) {
                    $tservice->addAttachedFiles($sess, $id);
                }
                
                $sess_p = __paramInit('string', NULL, 'preview_sess', NULL);
                if($sess_p) {
                    $tservice->addAttachedFiles($sess_p, $id);
                }
                
                //message ?
                $msg_type = ($tservice->active === 't')?'new_saved_publish':'new_saved';
                tservices_helper::setFlashMessageFromConstWithTitle($msg_type,$tservice->title);
                
                $tu_card_uri = sprintf('/tu/%d/%s.html',$id,tservices_helper::translit($tservice->title));
                header("Location: {$tu_card_uri}");
                exit;
            }
            
        }
        
        //SEO
        $page_title = 'Добавление типовой услуги на FL.ru';
        //$page_descr = ''
        //$page_keyw = '';
                
}



//------------------------------------------------------------------------------

//Превью

$preview_field = array();
if(count($tservice->preview)) {
    foreach($tservice->preview as $key => $image) {
        $preview_field = array(
            'hash' => md5( $solt . $image['id'] . $tuid . $uid ),
            'qquuid' => $image['id'],
            'src' => WDCPREFIX . '/' . $image['path'] . $image['fname'] 
         );
    }
}

$sess_p = __paramInit('string', NULL, 'preview_sess', NULL);

if($sess_p)
{
    $files = uploader::sgetFiles($sess_p);

    if (count($files)) 
    {
        foreach ($files as $file) 
        {
            if (strpos($file['fname'], 'tiny_') === FALSE)
                continue;

            $preview_field = array(
                'qquuid' => $file['id'],
                'src' => WDCPREFIX . '/' . $file['path'] . $file['fname']
            );
        }
    }              
}
else
{
    $sess_p = uploader::createResource('tupreview');
}



//------------------------------------------------------------------------------

//Прикрепленные файлы
$uploader_field_element = array();
if(count($tservice->images)) {
    foreach($tservice->images as $key => $image) {
        $uploader_field_element[] = array(
            'hash' => md5( $solt . $image['id'] . $tuid . $uid ),
            'qquuid' => $image['id'],
            'src' => WDCPREFIX . '/' . $image['path'] . $image['fname'] 
         );
    }
}

$sess = __paramInit('string', NULL, 'uploader_sess', NULL);

if($sess)
{
    $files = uploader::sgetFiles($sess);

    if (count($files)) 
    {
        foreach ($files as $file) 
        {
            if (strpos($file['fname'], 'tiny_') === FALSE)
                continue;

            $uploader_field_element[] = array(
                'qquuid' => $file['id'],
                'src' => WDCPREFIX . '/' . $file['path'] . $file['fname']
            );
        }
    }              
}
else
{
    $sess = uploader::createResource('tservices');
}

//------------------------------------------------------------------------------


$city = new city();
if(!$tservice->city) $tservice->city = ($user_obj->city > 0)?$user_obj->city:1;
$location_value = $city->getCountryName($tservice->city) . ": " . $city->getCityName($tservice->city);


//------------------------------------------------------------------------------

$user_phone_tservice = user_phone::getInstance()->render(user_phone::PLACE_TSERVICE);



$is_bg = true;
$inner = 'tpl.form.php';
include ("../template3.php");