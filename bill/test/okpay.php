<?php
// Тестовое оплата услуг через Qiwi
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) exit;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");


$account = new account;

if(isset($_POST['cancel'])) {
    $back_url = $_SESSION['referer'];
    unset($_SESSION['referer']);
    header("Location: {$back_url}");
    exit;
} elseif(isset($_POST['success']) ) {
    $sum = $_SESSION['post_payment']['sum'];
    $account = new account();
    $account->GetInfo( $_SESSION['post_payment']['ok_f_uid'] );

    //$descr = "OKPAY #".$_SESSION['post_payment']['ok_txn_id']." на кошелек ".$_SESSION['post_payment']['ok_receiver_wallet']." OKPAYID: ".$_SESSION['post_payment']['ok_payer_id']." сумма - ".$_SESSION['post_payment']['ok_item_1_price'].",";
    //$descr .= " обработан ".$_SESSION['post_payment']['ok_txn_datetime'].", счет - ".$_SESSION['post_payment']['ok_f_bill_id'];

    $descr = "OKPAY #11 на кошелек OK460571733 OKPAYID: 1111 сумма - ".$_SESSION['post_payment']['ok_item_1_price'].",";
    $descr .= " обработан ".date("Y-m-d H:i:s").", счет - ".$_SESSION['post_payment']['ok_f_bill_id'];

    $account->deposit($op_id, $_SESSION['post_payment']['ok_f_bill_id'], $_SESSION['post_payment']['ok_item_1_price'], $descr, 14, $_SESSION['post_payment']['ok_item_1_price'], 12);

    header("Location: /bill/");
    exit;
}

$_SESSION['post_payment'] = $_POST;
$_SESSION['referer']      = $_SERVER['HTTP_REFERER'];

?>

<h2>Тестовая оплата OKPAY</h2>
<p>
Оплата услуг аккаунт #<?= get_uid(false);?>, сумма оплаты <?= to_money($_POST['ok_item_1_price'],2)?> рублей
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