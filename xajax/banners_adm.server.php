<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/banners_adm.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");

function checkStatic($from, $to) {
    $objResponse = new xajaxResponse();

    $banners = new banners;
    $stat_bans = $banners->GetBannersByDate($from, $to, true);

    if ($stat_bans) {
        $objResponse->alert("Внимание! На этот период уже есть статические размещения!");
        $objResponse->assign("stat_info", 'innerHTML',
            "<a href=\"#\" class=\"blue\" onclick=\"window.showModalDialog('actbanners.php?from=" . $from . "&to=" . $to . "&stat=1', null, 'dialogHeight: 300px; dialogWidth: 500px; edge: Raised; center: Yes; help: No; resizable: No; scroll: Vertical; status: No;')\">Статические размещения на этот период</a>"
        );
    }
    else
        $objResponse->assign("stat_info", 'innerHTML', "");
    return $objResponse;
}

function updateCitys($country) {
    $objResponse = new xajaxResponse();

    $banners = new banners;
    $citys = $banners->GetCitys($country);

    if ($citys) {
        $str .= "document.getElementById('scity').options.length=0; document.getElementById('scity').options[0] = new Option( 'ВСЕ', '0' );";
        foreach ($citys as $city) {
            $str .= "document.getElementById('scity').options[document.getElementById('scity').options.length] = new Option( '" . $city['cname'] . "', '" . $city['id'] . "' );";
        }
        $objResponse->script($str . "document.getElementById('scity').disabled=false");
    }
    return $objResponse;
}


function GetCities($country) {
    $objResponse = new xajaxResponse();

    $banners = new banners;
    $citys = $banners->GetCitys($country);

//    var_dump($citys);

    $objResponse->call('GetCities', null, $citys);
    return $objResponse;
}

function AddClient($data) {
    $objResponse = new xajaxResponse();

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
    session_start();
    get_uid(false);
    if (!hasPermissions('banners')) {
        return $objResponse;
        exit;
    }

    if (!$_SESSION['uid']) {
        return $objResponse;
        exit;
    }

    $ban_obj = new banners;

    $name = trim($data['name']);
    $adr = trim($data['adr']);
    $phone = trim($data['phone']);
    $cont = trim($data['cont']);
    $email = trim($data['email']);
    $notes = trim($data['notes']);

    $id = trim($data['id']);
    if (!$id) {
        $action = 'add';
    } else {
        $action = 'update';
    }

    if ($action == "add" && $name) {
        $res = $ban_obj->AddCompany($name, $adr, $phone, $cont, $email, $notes);
        if(intval($res)) {
            $newid = $res;
        } else {
            $error = $res;
        }
    }

    if ($action == "update" && $name && $id) {
        $error = $ban_obj->EditCompany($id, $name, $adr, $phone, $cont, $email, $notes);
    }
    if ($newid)
        $company = $ban_obj->GetCompany($newid, $error);

    if($error) {
        $objResponse->alert($error);
        return $objResponse;
    }

    if(!$newid) {
        $objResponse->alert('Сохранить не получилось. Может что-то не заполнили?');
        return $objResponse;
    }

    $objResponse->call('AddClient', $company);
    
    return $objResponse;
}


function CheckLogin($login) {
    $objResponse = new xajaxResponse();

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    session_start();

    if(!get_uid(false)) {
        return $objResponse;
        exit;
    }

    $login = change_q_x($login, TRUE);

    $user = new users();
    $user->GetUser($login);

    if(!$user->uid) {
        $objResponse->call('CheckLogin', array(
            'error' => 'Пользователь не найден'
        ));
        return $objResponse;
    }

    $user_data = array(
        'uid' => $user->uid,
        'login' => $user->login,
    );
    $objResponse->call('CheckLogin', $user_data);
    
    return $objResponse;
}


function GetClientBanners($cid) {
    $objResponse = new xajaxResponse();

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
    session_start();

    if(!get_uid(false)) {
        return $objResponse;
        exit;
    }

    $cid = intval($cid);

    $ban_obj = new banners;
    $clients = $ban_obj->GetBannersByClient($cid, -1, $error);

    $objResponse->call('GetClientBanners', null, $clients);

    return $objResponse;
}

$xajax->processRequest();
?>
