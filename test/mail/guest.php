<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/guest/models/GuestSmail.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/guest/models/GuestConst.php';

$guestSmail = new GuestSmail();

$data = array(
    'email' => 'danil@onyanov.ru',
    'type' => GuestConst::TYPE_VACANCY
);

$user = null;
$code = 'jkndfvkbxnfvkxn';

$sent = $guestSmail->sendActivation($data['email'], $code, $user, $data['type']);

print_r($sent);exit;
