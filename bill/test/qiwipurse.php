<?php
// Тестовое оплата услуг через Qiwi
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) exit;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/qiwipay.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
$qiwipay = new qiwipay(get_uid(false));

if(isset($_POST['cancel'])) {
    $back_url = $_SESSION['referer'];
    unset($_SESSION['referer']);
    header("Location: {$back_url}");
    exit;
} elseif(isset($_POST['success']) ) {
    $sum = $_SESSION['post_payment']['sum'];
    $account = new account();
    $account->GetInfo( $qiwipay->uid, true );
    
    $bill = $DB->row("SELECT * FROM qiwi_account WHERE account_id = ? order by id desc LIMIT 1", $account->id);
   
    $error =  $qiwipay->completeBill($error, $bill, $sum);
    var_dump($error); var_dump($DB->sql); exit;
    header("Location: /bill/");
    exit;
}
$bill = new billing(get_uid(false));
$bill->test = true;
$bill->setPaymentMethod('qiwipurse');
$created = $bill->error;

$_SESSION['post_payment'] = $_POST;
$_SESSION['referer']      = $_SERVER['HTTP_REFERER'];

?>

<h2>Тестовая оплата QIWI.Purse</h2>
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