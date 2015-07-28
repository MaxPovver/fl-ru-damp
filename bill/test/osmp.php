<?php
// Тестовое оплата услуг через Qiwi
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) exit;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/osmppay.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");

if(isset($_POST['cancel'])) {
    $back_url = $_SESSION['referer'];
    unset($_SESSION['referer']);
    header("Location: {$back_url}");
    exit;
} elseif(isset($_POST['success']) ) {
    $sum = $_SESSION['post_payment']['sum'];
    $bill = new billing(get_uid(false));
    $account = new osmppay;
    $error = $account->checkdeposit($op_id, $result, $sum, $bill->user['login'], rand(1, 999999999), date('YmdHis'));
    header("Location: /bill/");
    exit;
}
$bill = new billing(get_uid(false));
$bill->test = true;
$bill->setPaymentMethod('qiwi');
$created = $bill->error;

$_SESSION['post_payment'] = $_POST;
$_SESSION['referer']      = $_SERVER['HTTP_REFERER'];

?>

<h2>Тестовая оплата Терминалы OSMP</h2>
<p>
Оплата услуг аккаунт #<?= get_uid(false);?>, сумма оплаты <?= to_money($_POST['sum'],2)?> рублей
</p>

<? if ($created) { ?>
Ошибка:
<pre>
<?var_dump($created);?>
</pre>
<? } else { //if?>
<form method="POST" />
    <input type="submit" name="success" value="Оплатить" />
    <input type="submit" name="cancel" value="Отмена" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>
<? }//if?>