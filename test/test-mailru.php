<?
require_once("../classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");



$mail = new smail();

// 1
$f_user_admin = users::GetUid($err,"admin");

$user['uname'] = "вас€";
$user['usurname'] = "ѕупкин";
$user['login'] = "vp";
$user['email'] = "vishna-v-sahare@mail.ru";
$prof['name'] = "nnnn";
$prof['id'] = 10;
$prof['cost'] = 15;
$days = 2;

$mail->subject = "Ќедостаточно средств дл€ автоматического продлени€ на Free-lance.ru";  
$mail->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>"; 
	        
	        $html = "";
	           $prof_name  = $prof['name'];
    	       if($prof['id'] == 0)  $prof_name  = "¬се фрилансеры";
    	       
	           $html .= "-&nbsp;<a href=\"{$GLOBALS['host']}/firstpage/?prof={$prof['id']}\">{$prof_name}</a> ({$prof['cost']} FM)<br/>";

	        
	        $dev  = 111;
                $date_dest = strtotime('+'.$days.' days');
                $date = date('d '.monthtostr(date('m', $date_dest)).' Y года', $date_dest);
	        $body = "ƒо активации функции автопродлени€ ".ending($days, "осталс€", "осталось", "осталось")." ".number2string($days, 1)." ".ending($days, "день", "дн€", "дней").". „ерез $days ".ending($days, "день", "дн€", "дней").", {$date}, должно быть автоматически продлено размещение в следующих разделах сайта Free-lance.ru:<br/>
{$html}
¬сего с вашего счета должно быть списано {$val['sum_cost']} FM.<br/>
—ейчас на вашем Ћичном счету {$val['sum']} FM. ƒл€ срабатывани€ автоматического продлени€ недостаточно средств.<br/><br/>
Ќапоминаем вам, что автоматическое продление происходит в случае, когда на вашем личном счету достаточно средств дл€ оплаты продлени€ всех указанных разделов.<br/> 
ѕожалуйста, пополните счет или измените настройки автоматического продлени€.<br/>
<br/>
—чет можно пополнить на следующей странице: <a href=\"{$GLOBALS['host']}/bill/\">{$GLOBALS['host']}/bill/</a><br/>
‘ункцию автопродлени€ можно настроить или отключить здесь: <a href=\"{$GLOBALS['host']}/firstpage/\">{$GLOBALS['host']}/firstpage/</a>";
	        
	        $mail->message = $mail->GetHtml($user['uname'], $body, 'simple');
echo $mail->message;
	        $mail->SmtpMail('text/html');



?>
