<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/autoresponse.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupAutoresponse.php');

//require_once(dirname(__FILE__).'/autoresponse.form.php');
        
session_start();

// Раздел доступен только для авторизованных фрилансеров
if (is_emp() || !get_uid()) {
    header("Location: /frl_only.php\r\n");
    exit();
}

$stretch_page = true;
$showMainDiv  = true;

$stop_words = new stop_words();

// БД с данными
autoresponse::$db = $GLOBALS['DB'];

/*
$form = new AutoresponseForm();

// Создание нового автоответа
if (isset($_POST) && sizeof($_POST) > 0) {

    if ($form->isValid($_POST)) {
        $data = $form->getValues();
        $data['user_id'] = get_uid();
        $data['is_pro'] = is_pro(); // есть ли у пользователя ПРО аккаунт на момент покупки автоответа
        $data['filter_category_id'] = $_POST['filter_category_columns'][0];
        $data['filter_subcategory_id'] = $_POST['filter_category_columns'][1];

        $data['filter_budget'] = $form->getElement('filter_budget')->getValue('budget');
        $data['filter_budget_currency'] = $form->getElement('filter_budget')->getValue('currency_db_id');
        $data['filter_budget_priceby'] = $form->getElement('filter_budget')->getValue('priceby_db_id');

        if ($ar = autoresponse::create($data)) {
            // Сохраняем проект и вызываем JavaScript метод для оплаты
            echo "<script>";
            echo "window.parent.autoresponseShowPayModal({$ar->data['id']});";
            echo "</script>";
            exit();
        }
    }
    exit();
}
else {
    $form->setDefaults(array('total' => autoresponse::$config['default_quantity']));
}
*/

// Получить список автоответов пользователя
$autoresponse_list = autoresponse::findForUser(get_uid());

// Инициализация попапа оплаты
//quickPaymentPopupAutoresponse::getInstance()->init();

// Добавляем скрипт для работы с автоответами 
//$GLOBALS['js_file']['autoresponse'] = 'autoresponse.js';

$content = "content.php";
$header = "../header.php";
$footer = "../footer.html";

include ("../template3.php");