<?php
$g_page_id = "0|4";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
session_start();
$uid = get_uid();
    
$email = isset($_POST['email'])?trim($_POST['email']):'';
$success = isset($_GET['success'])?intval($_GET['success']):0;

$form_error = false;

if (!$email && $uid) {
    $error = '';
    $email = users::GetField($uid, $error, 'email');
}

if (isset($_POST['email'])) { 
    $sql = "SELECT 1 FROM partners_become WHERE email = ?";
    if ($DB->val($sql, $email) == 1) {
        header('Location: ./?success=1');
        exit();
    }

    // Проверка правильности ввода email-адреса
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = true;
    }

    if (!$form_error) {
        $sql = 'INSERT INTO partners_become (email, user_id) VALUES (?, ?)';
        if ($GLOBALS['DB']->query($sql, $email, get_uid(false))) {
            header('Location: ./?success=1');
            exit();
        }
    }
}


$content = "content.php";
$header = "../header.php";
$footer = "../footer.html";

include ("../template.php");
