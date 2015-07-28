<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
session_start();
get_uid();
  
if (!(hasPermissions('adm') && hasPermissions('payservices'))) { header ("Location: /404.php"); exit; }
$action = __paramInit('string', NULL, 'action');
$error = NULL;
$sError = NULL;

switch($action)
{
    case 'oth_plus' :
        $login      = __paramInit('string', NULL, 'login');
        $o_oth_plus = __paramInit('string', NULL, 'o_oth_plus');
        $o_oth_plus = str_replace( ',', '.', $o_oth_plus );
        $user = new users();
        if($error = $user->GetUser($login))
          break;
        if(!$user->uid) {
          $error = "Ошибка, пользователь не найден.";
          break;
        }
        
        if ( !is_numeric($o_oth_plus) ) {
            $error = "Ошибка, рейтинг должен быть числом.";
            break;
        }
        
        $rating = new rating($user->uid, $user->is_pro, $user->is_verify, $user->is_profi);
        $upd = array(
         'o_oth_factor'  => $rating->data['o_oth_factor'] + floatval($o_oth_plus)
        );
        
        if(rating::Update($user->uid, $upd)) {
            // пишем лог админских действий: манипуляции с рейтингом
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
            
            $sObjName = $user->uname. ' ' . $user->usurname . ' [' . $user->login . ']';
            $sObjLink = '/users/' . $user->login;
            $sReason  = 'Рейтинг ' . ( floatval($o_oth_plus) < 0 ? 'уменьшен' : 'увеличен' ) . ' на ' . abs( $o_oth_plus );
            
            admin_log::addLog( 
        	   admin_log::OBJ_CODE_USER, admin_log::ACT_ID_USR_CH_RATING, 
        	   $user->uid, $user->uid, $sObjName, $sObjLink, 0, '', 0, $sReason 
        	);
        	//-----------------------------------
            
            header ("Location: /siteadmin/rating/?result=success");
            exit;
        }
        
        $error = "Ошибка.";
    break;
    case "setpwd":
        $login = __paramInit('string', NULL, 'login');
        $pwd   = stripslashes($_POST['pwd']); //__paramInit('string', NULL, 'pwd');
        
        require_once(ABS_PATH . "/classes/users.php");
        require_once(ABS_PATH . "/classes/codes.php");
        $codes = new codes();
	    $user = new users();
	    $uid = $user->GetUid($error, $login);
	    $user->passwd = $pwd;
	    $err = $user->Update($uid,$res);
	    $codes->DelByUT($uid, 1);
        
        // Пишем в лог смены паролей
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/restorepass_log.php");
        restorepass_log::SaveToLog($uid, getRemoteIP(), 4, get_uid(false));   
        
        header ("Location: /siteadmin/rating/?result=success");
    break;
    case "addserv":
        $login = __paramInit( 'string', NULL, 'login' );
        $days  = __paramInit( 'string', NULL, 'days' );
        $type  = __paramInit( 'int', NULL, 'type' );
        $where = __paramInit( 'int', NULL, 'where' );
        $fid   = users::GetUid( $err,$login );
        
        if ( $fid && $login ) {
            if ( preg_match('#^[1-9]+[\d]*$#', $days) ) {
                require_once( ABS_PATH . '/classes/account.php' );
                
                $nDays = intval( $days );
                $trsn_id = account::start_transaction( $fid );

                switch ( $type ) {
                    case 1:
                        require_once( ABS_PATH . '/classes/payed.php' );
                        $pro = new payed();
                        $pro->AdminAddPRO( $fid, $trsn_id, $nDays . ' days' );
                    break;
                }

                header ( 'Location: /siteadmin/rating/?result=success' );
            }
            else {
                $sError = 'Ошибка, Кол-во дней должно быть целым числом.';
            }
        }
        else {
            $sError = 'Ошибка, пользователь не найден.';
        }
    break;
}
$prfs = new professions();
$profs = $prfs->GetAllProfessions("",0, 0);
$css_file = array('moderation.css','nav.css' );
$content = "../content.php";
$inner_page = "inner_index.php";
$header = $rpath."header.php";
$footer = $rpath."footer.html";
include ($rpath."template.php");
?>
