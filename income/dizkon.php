<?php

define('NO_CSRF', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php';

$action = (empty($_POST['action']) || !in_array($_POST['action'], array('role', 'info')))? '': $_POST['action'];
$email  = (empty($_POST['email']) || !preg_match('/.+\@.+/', $_POST['email']))? '': $_POST['email'];
$login  = (empty($_POST['login']) || strlen($_POST['login']) < 3 || strlen($_POST['login']) > 15)? '': $_POST['login'];
$pass   = (empty($_POST['password']) || strlen($_POST['password']) < 6 || strlen($_POST['password']) > 24)? '': $_POST['password'];
$result = array('result' => 'no');

if ( !$action || (!$email && !$login) || ($action == 'info' && !$pass) ) {
    echo json_encode($result);
    exit(1);
}

$mem = new memBuff;
$key = md5("dizkon:{$action}_{$login}_{$email}_{$pass}");

if ( ($res = $mem->get($key)) !== false ) {
    echo json_encode($res);
    exit(0);
}

if ( $email ) {
    $user = new users;
    $user = $user->getUserBySocialEmail($email);
    if ( empty($user['login']) ) {
        $action = '';
    } else {
        $login = $user['login'];
    }
}

if ( $login ) {
    $user = new users;
    if ( $action == 'info' ) {
        $user->GetUserByLoginPasswd($login, $user->hashPasswd($pass));
    } else {
        $user->GetUser($login);
    }
    if ( empty($user->login) ) {
        $action = '';
    }
}

switch ( $action ) {
    
    case 'role': {
        $result = array( 'result' => 'ok', 'role' => is_emp($user->role)? 1: 2 );
        break;
    }
    
    case 'info': {
        $sbr    = sbr_meta::getUserReqvs($user->uid);
        $result = array(
            'result' => 'ok',
            'user'   => array(
                'login'     => $user->login,
                'email'     => $user->email,
                'phone'     => ($sbr[$sbr['form_type']]['mob_phone'] != '')? $sbr[$sbr['form_type']]['mob_phone']: '',
                'firstname' => $user->uname?  iconv('CP1251', 'UTF-8', $user->uname): '',
                'lastname'  => $user->usurname?  iconv('CP1251', 'UTF-8', $user->usurname): '',
                'role'      => is_emp($user->role)? 1: 2,
                'avatar'    => ($user->photo != '')? WDCPREFIX . '/users/' . $user->login . '/foto/' . $user->photo: ''
            )
        );
        break;
    }
    
}

$mem->set($key, $result, 600);
echo json_encode($result);
