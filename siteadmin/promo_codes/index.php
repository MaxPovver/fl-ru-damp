<?
define( 'IS_SITE_ADMIN', 1 );

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/template.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/PromoCodes.php");
session_start();

$no_banner = 1;
$rpath = "../../";

$uid = get_uid();
//if(!hasPermissions('sbr') && !hasPermissions('sbr_finance'))
//    header_location_exit('/404.php');	
    
$promoCodes = new PromoCodes();

$content = "../content2.php";
$template = "template2.php";

$services = array(
    10 => "ПРО",
    15 => "платные опции в проектах",
    20 => "публикация конкурса",
    25 => "публикация вакансии",
    30 => "закрепление профиля",
    35 => "закрепление услуг",
    40 => "карусель",
    45 => "предложения фрилансеров",
    55 => "автоответы",
    60 => "рассылка по каталогу"
);

$action = __paramInit('string', 'action', null, 'add');
$id = __paramInit('int', 'id');
$card = null;

switch ($action) {
    case 'edit':
    case 'add':
        $form = __paramInit('bool', null, 'formname');
        if ($form) {
            $error = '';
            $code = trim(__paramInit('string', null, 'code'));
            if (!$code) {
                $error .= 'Не указан код<br />';
            }
            $date_s = __paramInit('string', null, 'date_s_eng_format');
            $date = new DateTime($date_s);
            $date_start = $date->format("Y-m-d H:i:s");
             
            $date_e = __paramInit('string', null, 'date_e_eng_format');
            $date = new DateTime($date_e);
            $date_end = $date->format("Y-m-d H:i:s");
            
            $discount = __paramInit('int', null, 'discount');
            if ($discount <= 0) {
                $error .= 'Скидка некорректна<br />';
            }
            $is_percent = __paramInit('bool', null, 'is_percent');
            $count = __paramInit('int', null, 'count');
            if ($count <= 0) {
                $error .= 'Количество использований некорректно<br />';
            }
            $post_services = __paramInit('array', null, 'service');
            if (count($post_services) == 0) {
                $error .= 'Не выбрана ни одна услуга';
            }

            if (!$error) {
                if ($id) {
                    $promoCodes->edit($id, array(
                        'code' => $code,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'discount_percent' => ($is_percent ? $discount : 0),
                        'discount_price' => (!$is_percent ? $discount : 0),
                        'count' => $count
                    ), $post_services);
                    header_location_exit('/siteadmin/promo_codes/');
                } else {
                    $promoCodes->add(array(
                        'code' => $code,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'discount_percent' => ($is_percent ? $discount : 0),
                        'discount_price' => (!$is_percent ? $discount : 0),
                        'count' => $count
                    ), $post_services);
                    header_location_exit('/siteadmin/promo_codes/');
                }
                
            }
        }
        $card = $promoCodes->getById($id);
        
        break;  
    case 'delete':
        $promoCodes->delete($id);
        header_location_exit('/siteadmin/promo_codes/');
        break;
    default:
        
        break;
}

$codesArray = $promoCodes->getList();
foreach ($codesArray as $key => $code) {
    $codesArray[$key]['service_string'] = "";
    foreach ($code['services'] as $k => $value) {
        if ($k > 0) $codesArray[$key]['service_string'] .= ", ";
        $codesArray[$key]['service_string'] .= $services[$value];
    }
}
$list = Template::render('list.php', array(
    'data' => $codesArray
));
        
$css_file = array( 'moderation.css', 'new-admin.css', 'nav.css' );
$inner_page = "content.php";
$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath.$template);

?>
