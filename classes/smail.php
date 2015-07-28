<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smtp.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/statistic/StatisticFactory.php');



/**
 * Класс для отправки писем
 *
 */
class smail extends SMTP {
    
    /**
    * Отправляет email с заявкой на создание нового промо сообщества
    *
    * @param     string    $name    Имя и фамилия
    * @param     string    $msg     Текст предложения
    * @return    string             возможная ошибка
    */
    function NewPromoCommune($name, $msg) {
        if (!$this->Connect())
            return "Невозможно соеденится с SMTP сервером";

        $this->subject = "Предложение нового промо сообщества";
        $this->recipient = "adv@FL.ru";
        $msg_text = "Имя и фамилия:<br>".htmlspecialchars(stripslashes($name), ENT_COMPAT | ENT_HTML401, 'cp1251')."<br><br>Предложение:<br>".htmlspecialchars(stripslashes($msg), ENT_COMPAT | ENT_HTML401, 'cp1251');
        $this->message = $this->GetHtml('', $msg_text, array('header' => 'none', 'footer' => 'none'));
        $this->SmtpMail('text/html');
    }
    /**
     * Отправляет сообщение от администрации группе юзеров, определенных в модуле /siteadmin/admin/. Вызвается из hourly.php.
     *
     * Чтобы сообщение было отправлено нужно его занести в таблицу messages с полем to_id равным 0 и,
     * по необходимости, определить какому виду пользователей нужно отправить сообщение.
     * Кроме того, далее необходимо зарегистрировать данное сообщение в таблице переменных variables, переменной
     * с именем 'admin_message_id' со значением идентификатором отправляемого сообщения.
     * Отправляет уведомление о новом сообщении в личке ("Мои контакты").
	 *
	 * @return   string   возможная ошибка
     */
	function SendAdminMessage()
	{
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
		
		if (!($message_id = spam::GetAdminMessageID()))
			return "Не зарегистрировано ни одного сообщения от администрации (таблица 'variables', имя переменной 'admin_message_id').";

		if (!($message = messages::GetMessage($message_id)))
			return "Тело сообщения отсутствует.";

		$this->subject = "Новое сообщение от Команды FL.ru";

		$msg_text = reformat2($message['msg_text'], 100);
		$attaches = array();
		if ($message['attach']) {
			foreach($message['attach'] as $a) {
				$attaches[] = new CFile($a['path'].$a['fname']);
			}
			$attaches = $this->CreateAttach($attaches);
		}

		if (!$this->Connect())
			return "Невозможно соеденится с SMTP сервером";

		for ($i=0; $users = messages::GetZeroMessageUsers($message['from_id'], $message_id, 1000, $i * 1000); $i++) {
			foreach ($users as $ikey=>$user) {
				if (!$user['email'] || substr($user['subscr'], 7, 1) == '0') continue;
				$this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
				$this->message = $this->GetHtml($user['uname'], $msg_text, array('header' => 'none', 'footer' => 'none'));
				$this->SmtpMail('text/html', $attaches);
			}
		}
		return '';
	}
	
    
    /**
     * Отправляет сообщение от администрации группе юзеров, определенных в модуле /siteadmin/admin/. Вызвается из hourly.php.
     *
     * Чтобы сообщение было отправлено нужно его занести в таблицу messages с полем to_id равным 0 и,
     * по необходимости, определить какому виду пользователей нужно отправить сообщение.
     * Кроме того, далее необходимо зарегистрировать данное сообщение в таблице переменных variables, переменной
     * с именем 'admin_message_id' со значением идентификатором отправляемого сообщения.
     * Отправляет уведомление о новом сообщении в личке ("Мои контакты").
	 *
	 * @return   string   возможная ошибка
     */
	function SendMasssending()
	{
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
		
		if (!($message_id = spam::GetMasssendingMessageID()))
			return "Не зарегистрировано ни одного сообщения от администрации (таблица 'variables', имя переменной 'admin_message_id').";

		if (!($message = messages::GetMessage($message_id)))
			return "Тело сообщения отсутствует.";

		$this->subject = "Новое сообщение на FL.ru";
		$msg_text = "
<a href='{$GLOBALS['host']}/users/{$message['from_login']}{$this->_addUrlParams('b')}'>{$message['from_uname']} {$message['from_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$message['from_login']}{$this->_addUrlParams('b')}'>{$message['from_login']}</a>]
направил(а) вам новое сообщение на сайте FL.ru.<br />
<br />
---------- 
<br />
".$this->ToHtml(LenghtFormatEx(strip_tags($message['msg_text']), 300))."
<br />
<br />
<br />
<a href='{$GLOBALS['host']}/contacts/?from={$message['from_login']}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/contacts/?from={$message['from_login']}</a>
<br />
<br />
------------
";
		// атачи по идее не нужны в письме в массовых рассылках, пусть читают на сайте :) но если вдруг... то включить это
		$attaches = array();
		/*if ($message['attach']) {
			foreach($message['attach'] as $a) {
				$attaches[] = new CFile($a['path'].$a['fname']);
			}
			$attaches = $this->CreateAttach($attaches);
		}*/

		if (!$this->Connect())
			return "Невозможно соеденится с SMTP сервером";

		for ($i=0; $users = messages::GetZeroMessageUsers($message['from_id'], $message_id, 1000, $i * 1000, FALSE); $i++) {
			foreach ($users as $ikey=>$user) {
       
				if ($user['email'] && (substr($user['subscr'], 12, 1) == '1')) {
					$this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
					$this->message = $this->GetHtml($user['uname'], $msg_text, array('header' => 'default', 'footer' => 'default'), array('login'=>$user['login']));
					$this->SmtpMail('text/html', $attaches);
				}
			}
		}
		
		// отправляем сообщение автору рассылки
		$this->subject = "Ваша заявка на рассылку прошла модерацию";
		$this->recipient = $message['from_uname']." ".$message['from_usurname']." [".$message['from_login']."] <".$message['from_email'].">";
		$attaches = '';
		if ($message['attach']) {
			foreach ($message['attach'] as $a) {
				$attaches .= ", <a href='".WDCPREFIX."/{$a['path']}{$a['fname']}{$this->_addUrlParams('b')}'>{$a['fname']}</a>";
			}
		}
		$msg_text = $this->ToHtml($message['msg_text']);
        $body = 
        "Ваша заявка на рассылку была рассмотрена и одобрена модераторами сайта FL.ru (3). 
         Фрилансерам выбранных вами специализаций будет отправлено сообщение следующего содержания:</br>
         ---<br/>
         {$msg_text}<br/>
         ---<br/>";
		$this->message = $this->GetHtml($message['from_uname'], $body, array('header'=>'default', 'footer'=>'simple'));
		$this->SmtpMail('text/html');
		
		return '';
	}
	

    /**
     * Отправляет консультантам FL.ru сообщение от юзера из обратной связи.
     *
     * @param  string   $login  имя запросившего
     * @param  string   $email  e-mail запросившего
     * @param  int      $kind   тип запроса
     * @param  string   $msg    текст сообщения.
     * @param  int      $fid    id из таблицы feedback
     * @return integer          количетво отправленных писем.
     */
	function FeedbackPost( $login, $email, $kind, $msg, $ucode = '', $fid = 0 ) {
	    $nRet = 0;
	    
	    if ( !empty($GLOBALS['aFeedbackPost'][$kind]) ) {
	        $login = stripslashes(htmlspecialchars_decode($login, ENT_QUOTES));
            $msg   = stripslashes($msg);
            
    	    $this->recipient = $GLOBALS['aFeedbackPost'][$kind]['email'];
            $this->subject   = $GLOBALS['aFeedbackPost'][$kind]['subj'];
            $this->message   = $msg . ( ($ucode && $fid) ? "\n".'[[UCODE::{'.$ucode.'},FID::{'.$fid.'}]]' : "" );
    		$this->from      = "$login <$email>";
    		
            $this->SmtpMail( 'text/plain' );
            
            $nRet = $this->sended;
	    }
        
        return $nRet;
	}
	

    /**
     * Отправляет уведомление автору сообщества о новой заявке на вступление.
     *
     * @param  int    $user_id   users.id подавшего заявку юзера.
     * @param  array  $comm      массив с информацией о сообществе, в которое хотят вступить.
     */
    function CommuneJoinAction($user_id, $comm)
    {
        if(!$comm['author_email'] || $comm['author_subscr'][5] != '1')
            return NULL;
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        $user = new users();
        $user->GetUserByUID($user_id);
        $this->recipient = $comm['author_uname'].' '.$comm['author_usurname'].' ['.$comm['author_login'].'] <'.$comm['author_email'].'>';
        $this->subject .= 'Новая заявка на вступление в сообщество «'.$comm['name'].'»';
        $body = 
"Пользователь <a href=\"{$GLOBALS['host']}/users/{$user->login}{$this->_addUrlParams('b')}\">{$user->uname} {$user->usurname}</a> [<a href=\"{$GLOBALS['host']}/users/{$user->login}{$this->_addUrlParams('b')}\">{$user->login}</a>] 
хочет вступить в сообщество «<a href=\"{$GLOBALS['host']}/commune/?id={$comm['id']}{$this->_addUrlParams('b', '&')}\">".$this->ToHtml($comm['name'], 1)."</a>». 
Вы можете <a href=\"{$GLOBALS['host']}/commune/?id={$comm['id']}&site=Admin.members&mode=Asked{$this->_addUrlParams('b', '&')}\">отклонить или принять</a> его заявку.";
        $this->message = $this->GetHtml($comm['author_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comm['author_login']));
        $this->SmtpMail('text/html');
    }
	

    /**
     * Отправляет уведомление члену сообщества, что его забанили, удалили, приняли заявку и т.д.
     *
     * @param int $user_id   users.id подавшего заявку юзера.
     * @param string $action   тип уведомления.
     * @param array $comm   массив с информацией о сообществе, в состоит юзер.
     */
    function CommuneMemberAction($user_id, $action, $comm)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$user = new users();
        $user->GetUserByUID($user_id);
        $comm_link = $GLOBALS['host'].'/commune/?id='.$comm['id'];
        

        if(!$user->email || substr($user->subscr, 5, 1) != '1' || $user->is_banned == '1')
            return NULL;
        
        switch($action)
        {
            case 'do.Kill.member'     : $this->subject = 'Вас удалили из сообщества '; break;
            case 'do.Accept.member'   : $this->subject = 'Вас приняли в сообщество '; break;
            case 'do.Unaccept.member' : 
                $body = "Ваша заявка на вступление в сообщество ";
                $this->subject = 'Заявка на вступление в сообщество '; 
                break;
            case 'do.Add.admin'       : $this->subject = 'Вас добавили в администраторы сообщества '; break;
            case 'do.Remove.admin'    : $this->subject = 'Вас удалили из администрации сообщества '; break;
            case 'UnBanMember'        : $this->subject = 'Вас разблокировали в сообществе '; break;
            case 'BanMember'          : $this->subject = 'Вас заблокировали в сообществе '; break;
            case 'WarnMember'         : $this->subject = 'Вам сделали предупреждение в сообществе '; break;
        }
        
        $body = ( $body ? $body : $this->subject ).' «<a href="'.$comm_link.$this->_addUrlParams('b', '&').'">'.$this->ToHtml($comm['name'], 1).'</a>»';
        $comm['name'] = $comm['name'];
        $this->subject .= '«'.$comm['name'].'»';
        if($action=='do.Unaccept.member') {
            $this->subject .= ' отклонена';
            $body .= ' отклонена';
        }
        $body .= '. ';
        
        switch($action) {
            case 'BanMember':   $body .= "К сожалению, теперь вы не можете создавать новые темы и оставлять комментарии в сообществе."; break;
            case 'UnBanMember': $body .= "Теперь вы снова можете создавать новые темы и оставлять комментарии в сообществе. "; break;
        }

        $this->recipient = $user->uname.' '.$user->usurname.' ['.$user->login.'] <'.$user->email.'>';
        $this->message = $this->GetHtml($user->uname, $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$user->login));
        $this->SmtpMail('text/html');
    }

    /**
     * Отправляет уведомления о новых темах в сообществе. Вызывается из hourly.php раз в час.
     */
    function CommuneNewTopic()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/commune.php';
		
		if(!($topics = commune::GetTopic4Sending('ALL', true)))
            return NULL;

        foreach($topics as $top)
        {
            if(!($recs = commune::GetTopicSubscribers($top['commune_id'])))
                continue;

            $this->subject = 'Новая тема в сообществе «'.$top['commune_name'].'»';
            $domain = $GLOBALS['host'];
            $body = 
"<a href=\"$domain/users/{$top['user_login']}\">{$top['user_uname']} {$top['user_usurname']}</a> [<a href=\"$domain/users/{$top['user_login']}\">{$top['user_login']}</a>] создал(-а) <a href=\"{$GLOBALS['host']}/commune/?id={$top['commune_id']}&site=Topic&post={$top['id']}{$this->_addUrlParams('b', '&')}\">новую тему</a> в сообществе «<a href=\"{$GLOBALS['host']}/commune/?id={$top['commune_id']}{$this->_addUrlParams('b', '&')}\">".$this->ToHtml($top['commune_name'], 1)."</a>».
<br/><br/>
--------
<br/>".$top['title']."
<br/>".reformat(LenghtFormatEx(strip_tags($top['msgtext'], "<br><p>"), 300))."
<br/>
<br/>
--------";

            if(commune::SetTopicIsSent($top['theme_id'])) {
				if (!$this->Connect()) {
					return "Невозможно соеденится с SMTP сервером";
				}
                foreach($recs as $r) {
                    if($top['user_login']!=$r['login']) {
                        $this->recipient = $r['uname']." ".$r['usurname']." [".$r['login']."] <".$r['email'].">";
                        if (!$r['unsubscribe_key']) {
                            $r['unsubscribe_key'] = users::GetUnsubscribeKey($r['login']);
                        }
                        $this->message = $this->GetHtml($r['uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$r['login'], 'UNSUBSCRIBE_KEY'=>$r['unsubscribe_key']));
                        $this->SmtpMail('text/html');
                    }
                }
            }
        }
    }

	
    /**
     * Новогоднее поздравление 2009 от сервиса.
     * @deprecated
     */
    function NY2009()
    {
        if($GLOBALS['host']!='http://www.FL.ru')
            return;

        return;

        $t_user = new users();
        $this->subject = 'Счастливого Нового Года!';
        $attach = self::CreateAttach(array(0=>array('path'=>$_SERVER['DOCUMENT_ROOT'].'/images/', 'name'=>'otkrytka.jpg', 'content_type'=>'image/jpeg')));
        $i=0;
        do
        {
            $users = $t_user->GetAll($size, "is_banned = '0'", "uid", 1000,($i*1000));
            if ($users) {
                foreach ($users as $ikey=>$user){
                    if (!$user['email']) continue;
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    if(!is_emp($user['role'])) {
                        $body = 
'<p>
Дорогие наши самые лучшие пользователи во Вселенной!
</p>
<p>
    Мы хотим сказать вам большое спасибо.
</p>
<p>
    Спасибо за то, что вы есть.<br/>
    За ваше живое участие в развитии нашего сайта. За каждый ваш комментарий, за каждую идею.
    За то, что вы, не жалея своего времени и сил, тестируете вместе с нами каждый сервис.<br/>
    За всю вашу полезную критику, которая нам очень нужна и помогает делать сайт ещё удобнее и интереснее.<br/>
    За тот позитив и вкус к жизни, который вы дарите в своих работах.<br/>
    За легкость, с который вы делитесь своим уникальным опытом в сообществах и блогах.<br/>
    За ваш профессионализм и ответственность. Профессионализм и ответственность, благодаря которым мы можем гордо и откровенно заявлять о том, что наши фрилансеры и работодатели лучшие.<br/>
    За вашу помощь и взаимовыручку, а мы-то знаем &mdash; что вы это делаете.<br/>
    За ваше уважение к нам, искренность, открытость и терпение.
</p>
<p>
    За то, что благодаря вам есть фри-ланс.ру (ну, куда без патетики и громких слов в поздравлении, но, правда ведь :)
</p>
<p>
    Позвольте сказать вам стотысячное спасибо и пожелать нового, чистого и светлого в наступающем году. Тепла вам и улыбок.
</p>
<p>
    Мы находимся в разных городах и странах, делаем такую разную работу, живём столь непохожими жизнями, но нас всех объединяет одно &mdash; мы выбрали этот яркий и не всегда простой путь свободы и независимости. Мы фрилансеры.
</p>
<p>
    Пусть солнце освещает ваш путь, а ноутбуки ломаются реже, заказчики присылают четкие и неизменные ТЗ, увеличивают срок выполнения проекта и гонорар по первой вашей просьбе. Или даже без вашей просьбы, по их личной доброй инициативе.
</p>
<p>
    Желаем вам настоящей жизни. Во всей её полноте и прелести.
</p>
<p>
    Счастливого Нового Года!
</p>
<p>
    Ёлка фри-ланс.ру: <a href="http://www.FL.ru/newyear2009/">http://www.FL.ru/newyear2009/</a>
</p>';
                    }
                    else    {
                        $body = 
'<p>
    Дорогие наши самые лучшие пользователи во Вселенной!
</p>
<p>
    Мы хотим сказать вам большое спасибо.
</p>
<p>
    За ваше живое участие в развитии нашего сайта. За каждый ваш комментарий, за каждую идею.<br/>
    За все ваши проекты и конкурсы.<br/>
    За всю вашу полезную критику, которая нам очень нужна и помогает делать сайт ещё удобнее и интереснее.<br/>
    За легкость, с который вы делитесь своим уникальным опытом в сообществах и блогах.<br/>
    За ваш профессионализм и ответственность. Профессионализм и ответственность, благодаря которым мы можем гордо и откровенно заявлять о том, что наши фрилансеры и работодатели лучшие.<br/>
    За ваше уважение к нам, искренность, открытость и терпение.<br/>
    За то, что благодаря вам есть фри-ланс.ру (ну, куда без патетики и громких слов в поздравлении, но, правда ведь :)<br/>
</p>
<p>
    Позвольте сказать вам стотысячное спасибо и пожелать нового, чистого и светлого в наступающем году. Тепла вам и улыбок.
</p>
<p>
    Пусть теплый ветер кружит неподалеку, а фрилансеры без единого уточняющего вопроса, в большинстве случаев бесплатно, но очень качественно выполняют задания. Узнают о проекте задолго до его публикации. Договариваются между собой, кто лучше сможет сделать. И присылают вам готовую работу. Телепатических вам фрилансеров. Магов и волшебников.
</p>
<p>
    Желаем вам настоящей жизни. Во всей её полноте и прелести.
</p>
<p>
    Счастливого Нового Года!
</p>';
                    }
                    $this->message = self::GetHtml($user->uname, $body, array('header'=>'no', 'footer'=>'no'));
                    $error = $this->SmtpMail(true,0,'text/html',$attach);
                }
            }
            $i++;
        } while (sizeof($users) == 1000);
    }


    /**
     * Приглашение на банкет 2009 от сервиса.
     * @deprecated
     */
    function BD2009()
    {
        return;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/birthday.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");

        $bd = new birthday(2009);
        $users = $bd->getAll(false);

        $this->subject = '';
        $smtp = 0;
        $admin_id = users::GetUid($error, 'admin');
        foreach ($users as $ikey=>$user) {
            if (!$user['email']) continue;
            $email_msg = // сообщение для е-майл рассылки.
'';
            $lichka_msg = // сообщение для рассылки по личкам.
'';
            $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
            $this->message = self::GetHtml($user['uname'], $email_msg); // если приветствия не надо, то добавить 3-й параметр: array('header'=>'no', 'footer'=>'no'));
            $smtp = $this->SmtpMail(false, $smtp, 'text/html');
            $error = messages::Add($admin_id, $user['login'], $lichka_msg, '');
        }
        self::SmtpClose($smtp);
    }
	

//------------------------------------------------------------------------------
    
    
    
    /**
     * Рассылка Заказчикам о новых проектах
     * 
     * @param int  сколько выбирать проектов
     * @param int  сколько выбирать юзеров за запрос
     * @return int всего отправлено писем адресатам
     */
    public function EmpNewProj($show_limit = 10, $min_users = 200)
    {
        //$show_limit = 10;//выбирать проектов
        //$min_users = 200;//выбирать юзеров за запрос
        
        $projects = projects::GetNewProjectsPreviousDay($error, false, $show_limit, true);
        
        $projects_count = count($projects);
        if(!$projects_count) return FALSE;
        
        $page  = 0;
        $count = 0;         
        $message = '';
        
        $current_date = time();
        $current_date_sufix = '_' . date('dmy',$current_date); //format:_270314
        
        foreach ($projects as $prj) {
            
            $message .= Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/emp_new_projects/project.tpl.php', 
                    array(
                            'url'   => $GLOBALS['host'] . getFriendlyURL('project', array('id' => $prj['id'], 'name' => $prj['name'])),
                            'name'  => ($prj['name'] ? reformat($prj['name'], 50, 0, 1) : ''),
                            'descr' => $prj['descr'],
                            'host' => $GLOBALS['host'],
                            'project_kind' => $prj['kind'],
                            'project_pro_only' => ($prj['pro_only'] == 't'),
                            'project_verify_only' => ($prj['verify_only'] == 't'),
                            'project_urgent' => ($prj['urgent'] == 't'),
                            'price' => ($prj['cost'])? CurToChar($prj['cost'], $prj['currency']) . getPricebyProject($prj['priceby']) : NULL,
                            'end_date' => $prj['end_date'],
                            'create_date' => $prj['create_date'],
                            'utm_param' => $this->_addUtmUrlParams('email', 'emp%UTM_CONTENT%', 'day_projects' . $current_date_sufix)
                    )
           );
        }
        
        
        //Подтягиваем баннер для работодателей           
        $settings = new settings();
        $banner_file = $settings->GetVariable('newsletter', 'emp_banner_file');
        $banner_link = $settings->GetVariable('newsletter', 'emp_banner_link');            

        
        $this->subject = 'Новые проекты на FL.ru';
        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/emp_new_projects/project_layout.tpl.php', 
                array(
                    'projects' => $message,
                    'host' => $GLOBALS['host'],
                    'projects_cnt' => $projects_count,
                    'date' => strtotime('- 1 day'),
                    'join_url' => $GLOBALS['host'] . '/public/?step=1&kind=1',
                    'unsubscribe_url' => '%UNSUBSCRIBE_URL%',
                    'track_url' => '%TRACK_URL%',
                    
                    'banner_file' => $banner_file,
                    'banner_link' => $banner_link                    
                )
        );
        $this->recipient = '';
        $massId = $this->send('text/html');
        
        $statistics = array();
        
        while ( $users = employer::GetPrjRecps($error, ++$page, $min_users) ) {
            
            $this->recipient = array();
            
            foreach ( $users as $user ) {
                
                if (!$user['unsubscribe_key']) {
                    $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                }
                
                
                if($user['last_years_ago'] > 0){
                   $utm_content = ($user['last_years_ago'] > 3)?'_3y':'_1-3y';
                }else{
                   $utm_content = ($user['reg_days_ago'] > 7)?'_1y':'_new';
                }
                $utm = $this->_addUtmUrlParams('email', 'emp' . $utm_content, 'unsubscr_day_projects' . $current_date_sufix);
                
                
                //Накапливаем статистику
                $stat_idx = (($user['reg_days_ago'] > 7)?$user['reg_date_year']:'new');
                $statistics[$stat_idx]++; 
                
                
                $this->recipient[] = array(
                    'email' => sprintf('%s %s [%s] <%s>', $user['uname'], $user['usurname'], $user['login'], $user['email']),
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login'],
                        'UTM_CONTENT'       => ($user['reg_days_ago'] > 7)?$user['reg_date_year']:'_new',
                        'UNSUBSCRIBE_URL'   => "/unsubscribe/?type=new_projects&ukey={$user['unsubscribe_key']}" . $utm,
                                
                        'TRACK_URL'         => $GLOBALS['host'] . StatisticHelper::track_url(1, $stat_idx, $current_date, $user['login'] . $user['uid'])
                    )
                );
                
                        
       
                        
                $count++;
            }
            
            
            $this->bind($massId, true);  

        }
        
        
        //Собранную статистику отправляем в GA
        $statistics['total'] = $count;
        $ga = StatisticFactory::getInstance('GA');
        $ga->newsletterNewProjectsEmp($statistics, $current_date);
        
        return $count;
    }

    


//------------------------------------------------------------------------------




    /**
     * @todo: замена NewProj
     * 
     * Рассылка о новых проектах за предыдущий день. Вызывается раз в день из hourly.php
     * 
     * @param array $uids - массив идентификаторов пользователей, которых нужно исключить
     * @return integer   количество получивших рассылку
     */
    public function NewProj2($uids = array())
    {
        $show_pro_limit = 25;
        $show_limit = 25;
        
        $projects = projects::GetNewProjectsPreviousDay($error, true);
        $groups   = professions::GetAllGroupsLite(true);
        
        $page  = 0;
        $count = 0;        
        
        $projects_count = count($projects);
        if(!$projects_count) return FALSE;
        
        //Получаем баннеры
        $settings = new settings();
        $banner_file = $settings->GetVariable('newsletter', 'banner_file');
        $banner_link = $settings->GetVariable('newsletter', 'banner_link');
        
        
        $this->subject = 'Новые проекты на FL.ru';

        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/new_projects/project_layout.tpl.php', 
                array(
                    'projects' => '%MESSAGE%',
                    'host' => $GLOBALS['host'],
                    'title' => '%TITLE%',
                    'unsubscribe_url' => '%UNSUBSCRIBE_URL%',
                    'date' => strtotime('- 1 day'),
                    'track_url' => '%TRACK_URL%'
                )
        );
        $this->recipient = '';
        $massId = $this->send('text/html');
        
        $project_ids = array();
        
        foreach ($projects as $i => $prj) {
            
            $descr = $prj['descr'];
            $descr = htmlspecialchars($descr, ENT_QUOTES, 'CP1251', false);
            $descr = reformat(LenghtFormatEx($descr,180), 50, 0, 1);

            $price = ($prj['cost'])? CurToChar($prj['cost'], $prj['currency']) . getPricebyProject($prj['priceby']) : NULL;
            

            $projects[$i]['html'] = Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/new_projects/project.tpl.php', 
                    array(
                            'url'   => $GLOBALS['host'] . getFriendlyURL('project', array('id' => $prj['id'], 'name' => $prj['name'])),
                            'name'  => ($prj['name'] ? reformat(htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false), 50, 0, 1) : ''),
                            'descr' => $descr,//LenghtFormatEx(reformat($prj['descr'], 100, 0, 1),250),
                            'host' => $GLOBALS['host'],
                            'project_kind' => $prj['kind'],
                            'project_pro_only' => ($prj['pro_only'] == 't'),
                            'project_verify_only' => ($prj['verify_only'] == 't'),
                            'project_urgent' => ($prj['urgent'] == 't'),
                            'price' => $price,
                            'end_date' => $prj['end_date'],
                            'create_date' => $prj['create_date'],
                            
                            'utm_param' => '%UTM_PARAM%'
                    )
           );
            
            
           $project_ids[] =  $prj['id'];
        }
 
        //Собираем юзеров у которых есть ответы на новые проекты
        $offers_exist = array();
        $offers = projects_offers::AllFrlOffersByProjectIDs($project_ids);

        if(count($offers)){
            foreach($offers as $offer)
            {
                if(!isset($offers_exist[$offer['project_id']])) $offers_exist[$offer['project_id']] = array();
                $offers_exist[$offer['project_id']][$offer['user_id']] = TRUE;
            }
        }
        
        
        $strtotime_3y_ago = strtotime('- 3 year');
        $strtotime_1y_ago = strtotime('- 1 year');
        $strtotime_1w_ago = strtotime('- 1 week');
        $current_date = time();
        $current_date_sufix = '_' . date('dmy',$current_date); //format:_270314
        
        $statistics = array();
        
        while ( $users = freelancer::GetPrjRecps($error, ++$page, 200, $uids) ) {

            $this->recipient = array();
            
            foreach ( $users as $user ) {
                
                //Если ли у фрилансера уточнение по категориям
                $is_mailer_str = (strlen($user['mailer_str']) > 0);
                
                $subj = array();
                if($is_mailer_str){
                    foreach ( $groups as $group ) {
                        if( freelancer::isSubmited($user['mailer_str'], array( array('category_id' => $group['id'])) ) ) {
                            $subj[$group['id']] = $group['name'];
                        }
                    }
                }
                
                $message_pro  = '';
                $cnt_pro = 0;
                $message = '';
                $cnt = 0;
                $cnt_submited = 0;
                $cnt_user_submited = 0;
                
                
                foreach ( $projects as $prj ) {
                    
                    //Подписан ли фрилансер на специализацию к которой относится проект
                    if ($is_mailer_str && !freelancer::isSubmited($user['mailer_str'], $prj['specs']) ) {
                        continue;
                    }
                    
                    //Считаем все проекты по выбранным специализациям
                    $cnt_submited++;
                    
                    //Условия не попадания в письмо
                    if(($prj['is_blocked'] == 't') || 
                       ($prj['closed'] == 't') || 
                       ($prj['state'] == projects::STATE_MOVED_TO_VACANCY) || 
                       ($prj['kind'] == projects::KIND_PERSONAL)) {
                        continue;
                    }
                    
                    
                    //Если у фрилансера ответ на проект то не добавляем его в рассылку
                    if(isset($offers_exist[$prj['id']][$user['uid']])){
                        continue;
                    }
                    
 
                    if($prj['pro_only'] == 't'){
                        if($cnt_pro < $show_pro_limit) {
                            $message_pro .= $prj['html'];
                            $cnt_pro ++;
                        }
                    }else{
                        if($cnt < $show_limit) {
                            $message .= $prj['html'];
                            $cnt ++;
                        }
                    }
                    
                    $cnt_user_submited++;
                }
                
                $message = $message_pro . $message;
                
                if ( empty($message) ) {
                    continue;
                }
                
                if ($cnt_user_submited <= ($show_pro_limit + $show_limit)) {
                    $cnt_submited = $cnt_user_submited;
                }
                
                //Формирует UTM метки аналитики
                $reg_date = strtotime($user['reg_date']);
                $reg_year = date('Y',$reg_date);
                $utm_content = ($reg_date >= $strtotime_1w_ago)?'_new':$reg_year;
                //$utm_content = ($user['reg_days_ago'] > 7)?$user['reg_date_year']:'_new';
                $utm_param = $this->_addUtmUrlParams('email', 'free' . $utm_content, 'day_projects' . $current_date_sufix);
                $message = str_replace('%UTM_PARAM%', $utm_param, $message);

                
                //Собираем шаблон
                $message = Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/new_projects/project_list.tpl.php', 
                    array(
                        'projects' => $message,
                        'spec_list' => implode(" / ", $subj),
                        'setup_url' => $GLOBALS['host'] . "/users/{$user['login']}/setup/mailer/",
                        'other_count' => $cnt_submited - $cnt_pro - $cnt,
                        'more_url' => $GLOBALS['host'] . $utm_param,
                        'banner_file' => $banner_file,
                        'banner_link' => $banner_link        
                    )
                );
                
                           
                if (!$user['unsubscribe_key']) {
                    $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                }
                
                
                /*
                $date = strtotime($projects[0]['post_date']);
                $date = date( 'j', $date ) . ' ' . monthtostr(date('n', $date),true);
                */
                
                $projects_count_txt = $cnt_submited . ' ' . plural_form($cnt_submited, array('новый', 'новых', 'новых')) . ' ' . 
                                                            plural_form($cnt_submited, array('проект', 'проекта', 'проектов'));

                //$title = "{$projects_count_txt} за {$date}";
                

                $last_time = strtotime($user['last_time']);
                if($last_time < $strtotime_3y_ago){
                    $utm_content = '_3y';
                }elseif(($last_time >= $strtotime_3y_ago) && ($last_time <= $strtotime_1y_ago)){
                    $utm_content = '_1-3y';
                }elseif($reg_date < $strtotime_1w_ago){
                    $utm_content = '_1y';
                }

                /*
                 * @todo: EXTRACT медленней
                 
                if($user['last_years_ago'] > 0){
                   $utm_content = ($user['last_years_ago'] > 3)?'_3y':'_1-3y';
                }else{
                   $utm_content = ($user['reg_days_ago'] > 7)?'_1y':'_new';
                }
                */
                
                
                //Накапливаем статистику
                $stat_idx = (($reg_date >= $strtotime_1w_ago)?'new':$reg_year);
                $statistics[$stat_idx]++;
                
                
                $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login'],
                        'MESSAGE'           => $message,
                        'UNSUBSCRIBE_URL'   => "/unsubscribe/?type=new_projects&ukey={$user['unsubscribe_key']}" . $this->_addUtmUrlParams('email', 'free' . $utm_content, 'unsubscr_day_projects' . $current_date_sufix),
                        'TITLE'             => $projects_count_txt,//$title
                        
                        'TRACK_URL'         => $GLOBALS['host'] . StatisticHelper::track_url(0, $stat_idx, $current_date, $user['login'] . $user['uid'])
                    )
                );
                   
                $count++;
            }
            
            $this->bind($massId, true);            
        }
        
        
        //Собранную статистику отправляем в GA
        $statistics['total'] = $count;
        $ga = StatisticFactory::getInstance('GA');
        $ga->newsletterNewProjectsFrl($statistics, $current_date);

        
        return $count;
    }

    



    /**
     * @todo НЕ ИСПОЛЬЗУЕТСЯ
     * @deprecated since 0026073
     * 
     * Рассылка о новых проектах. Вызывается раз в день из hourly.php
     * @param array $uids - массив идентификаторов пользователей, которым уже отправленя новая рассылка (см. NewProjForMissingMoreThan24h)
     * @return integer   количество получивших рассылку
     */
    public function NewProj($uids) {
        $projects = projects::GetNewProjects($error, true, 600, 50);
        $groups   = professions::GetAllGroupsLite(true);
        $page  = 0;
        $count = 0;

        if ( empty($projects) ) {
            return 0;
        }

        $this->subject = 'Новые проекты на FL.ru';
        $message = 
'<p>
Данное письмо отправлено почтовым роботом сервера FL.ru и не требует ответа.
</p>
<p>
На сайте <a href="' . $GLOBALS['host'] . $this->_addUrlParams('f') . '">' . $GLOBALS['host'] . '</a> опубликованы новые Проекты
</p>
%MESSAGE%
<p>
Если вы хотите оперативно получать информацию об опубликованных на FL.ru проектах, 
скачайте и установите бесплатное приложение-информер <a href="'. $GLOBALS['host'] . '/promo/freetray/' . $this->_addUrlParams('f') . '">Free-tray</a>. 
</p>';
        $this->message = $this->GetHtml(
            '%USER_NAME%', 
            $message, 
            array('header' => 'default', 'footer' => 'default'), 
            array('target_footer' => true)
        );
        $this->recipient = '';
        $massId = $this->send('text/html');
        
        foreach ( $projects as $i=>$prj ) {
            $url = $GLOBALS['host'] . getFriendlyURL("project", $projects[$i]['id']);
            $projects[$i]['html'] = array(
                'post_date' => date("d.m.y", strtotimeEx($prj['post_date'])),
                'name'      => ($prj['name']? reformat($prj['name'], 100, 0, 1): ''),
                'descr'     => reformat($prj['descr'], 100, 0, 1),
                'url'       => "<a href='{$url}{$this->_addUrlParams('f')}'>{$url}</a>",
            );
        }
        
        while ( $users = freelancer::GetPrjRecps($error, ++$page, 50, $uids) ) {
            $this->recipient = array();
            foreach ( $users as $user ) {
                if ( empty($user['mailer']) ) {
                    continue;
                }
                $subj = array();
                foreach ( $groups as $group ) {
                    if( freelancer::isSubmited($user['mailer_str'], array( array('category_id' => $group['id'])) ) ) {
                        $subj[$group['id']] = $group['name'];
                    }
                }
                $lastKind = 0;
                $message  = '';
                foreach ( $projects as $prj ) {
                    if ( !freelancer::isSubmited($user['mailer_str'], $prj['specs']) ) {
                        continue;
                    }
                    if ( $lastKind != $prj['kind'] ) {
                        $kindName = '';
                        switch ( $prj['kind'] ) {
                            case 1: {
                                $kindName = 'Попроектно';
                                break;
                            }
                            case 2: {
                                $kindName = 'Конкурсы';
                                break;
                            }
                            case 3: {
                                $kindName = 'На зарплату';
                                break;
                            }
                            case 4: {
                                $kindName = 'В офис';
                                break;
                            }
                            case 7: {
                                $kindName = 'Конкурсы';
                                break;
                            }
                        }
                        $message .= "\n";
                        $message .= "<div>-----------------------------------------------------------------------------------</div>\n";
                        $message .= "<div>{$kindName}</div>\n";
                        $message .= "<div>-----------------------------------------------------------------------------------</div>\n";
                        $lastKind = $prj['kind'];
                    }
                    $message .= "\n<div>&nbsp;</div><div>-----</div>\n";
                    $message .= "<div>{$prj['html']['post_date']}</div>\n";
                    $message .= "<div>{$prj['html']['name']}</div>\n";
                    $message .= "<div>-----</div>\n";
                    $message .= "<div>{$prj['html']['descr']}</div>\n";
                    $message .= "<div>{$prj['html']['url']}</div>\n";
                    $message .= "<div>-----------------------------------</div>\n";
                }
                
                if ( empty($message) ) {
                    continue;
                }
                
                $message = '<div>(' . implode("/", $subj) . ')</div><div>&nbsp;</div>' . $message;
                
                if (!$user['unsubscribe_key']) {
                    $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                }
                $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'    => $user['uname'],
                        'USER_SURNAME' => $user['usurname'],
                        'USER_LOGIN'   => $user['login'],
                        'MESSAGE'      => $message,
                        'UNSUBSCRIBE_KEY' => $user['unsubscribe_key']
                    )
                );
                
                $count++;
            }
            
            $this->bind($massId, true);
            
        }
        
        return $count;
        
    }


    /**
     * Услуга восстановления пароля. Отправляет инструкцию на указанный e-mail.
     * @param  string  $mail  e-mail.
     *
     * @return string         возможная ошибка.
     */
    function Remind($mail) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $uuid = $t_user->Remind($mail, $error);
        if ($uuid && !$error) {
            $msg = 
			
			"Вы получили это письмо, т.к. ваш e-mail адрес был указан на сайте FL.ru при попытке восстановить доступ к аккаунту {$t_user->login}. Для восстановления доступа, пожалуйста, перейдите по ссылке <a href='{$GLOBALS['host']}/changepwd.php?c={$uuid}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/changepwd.php?c={$uuid}</a> или скопируйте ее в адресную строку браузера.<br/><br/>Если вы не заказывали услугу на сайте FL.ru ине указывали свой e-mail – просто проигнорируйте письмо. Вероятно, один из наших пользователей ошибся адресом.";
            $this->message = $this->GetHtml($t_user->uname, $msg, array('header'=>'simple', 'footer'=>'simple'));
            $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
            $this->subject = "Параметры учетной записи на FL.ru";
            $this->SmtpMail('text/html');
        }
        return $error;
    }
	
	
    /**
     * Отправляет сообщение зарегистрировавшемуся юзеру с инструкцией по активации аккаунта.
     * @param string  $login    логин юзера.
     * @param string  $passwd   пароль юзера.
     * @param string  $code     код активации.
     * @param string  $masterId id пользователя в мастере регистрации
     * @param string  $uType    тип пользователя (frl, emp) для мастера регистрации
     *
     * @return string   возможная ошибка.
     */
    function NewUser($login, $passwd = false, $code = false, $masterId = false, $uType = false){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUser($login, false);
        
        $subject = "Вы успешно зарегистрировались на FL.ru";
        
        $search_factor = is_emp($t_user->role) ? "исполнителей" : "и выполнения работы";

        $message .= "Поздравляем вас с успешной регистрацией на сайте <a href='https://fl.ru/' target='_blank'>FL.ru</a>.";
        if($code) $message .= "<br/>Осталось лишь активировать аккаунт по ссылке <a href='".$GLOBALS['host']."/registration/activate.php?code=$code".($masterId? "&m={$masterId}": "").($uType? "&u={$uType}": "")."{$this->_addUrlParams('b', '&')}' target='_blank'>".$GLOBALS['host']."/registration/activate.php?code=$code".($masterId? "&m={$masterId}": "").($uType? "&u={$uType}": "")."</a>";
        $message .= "<br/><br/><strong>Ваши учетные данные:</strong><br/><br/>Логин: {$t_user->login}<br/>Пароль: {$passwd}<br/>Пожалуйста, сохраните их и не передавайте третьим лицам.<br><br>";
        $message .= "Для успешного поиска ".$search_factor.":<br/><br/>";

        if(is_emp($t_user->role)) {
            $message .= "1. Опубликуйте <a href='".$GLOBALS['host']."/public/?step=1' target='_blank'>проект</a> или <a href='".$GLOBALS['host']."/public/?step=1&kind=7' target='_blank'>вакансию</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Опишите задачу, укажите ее бюджет и сроки выполнения – <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;вам останется только выбрать исполнителя и начать сотрудничество.<br/><br/>";
            $message .= "2. Проведите <a href='".$GLOBALS['host']."/masssending/' target='_blank'>рассылку</a> по фрилансерам<br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Отправьте задание сотням фрилансеров, получите массу предложений<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;и выбирайте лучшего из числа откликнувшихся.<br><br>";
            $message .= "3. Просмотреть <a href='".$GLOBALS['host']."/freelancers/' target='_blank'>Каталог фрилансеров</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Самостоятельно найти исполнителя в каталоге, оценив его портфолио,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;отзывы и указанные в профиле условия сотрудничества.<br><br>";
            $message .= "4. Создать конкурс на сайте <a href='http://dizkon.ru' target='_blank'>Dizkon.ru</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Если вам нужен логотип или другое графическое решение,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;воспользуйтесь нашим сервисом - DizKon.ru.<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Создайте конкурс с полным его юридическим и финансовым сопровождением.<br><br>";
        } else {
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/payed.php';
            
            $message .= "1. Купите аккаунт <a href='".$GLOBALS['host']."/payed/' target='_blank'>ПРО</a> за <strike style='color:#d7d7d7'>".payed::getPriceByOpCode(48)."</strike> ".payed::getPriceByOpCode(163)." рублей<br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Отвечайте на проекты, вакансии и конкурсы без ограничений.<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Будьте <a href='".$GLOBALS['host']."/payed/' target='_blank'>ПРО</a> и получайте выгодные заказы.<br><br>";
            
            $message .= "2. Заполните <a href='".$GLOBALS['host']."/users/{$login}/setup/main/' target='_blank'>профиль</a> и <a href='".$GLOBALS['host']."/users/{$login}/setup/portfolio/' target='_blank'>портфолио</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Укажите в профиле свои контактные данные и реальное ФИО,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;загрузите примеры работы и опишите условия сотрудничества.<br><br>";
            
            $message .= "3. Ответить на понравившиеся <a href='".$GLOBALS['host']."/projects/' target='_blank'>проекты на главной</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Просмотрите список проектов на главной и, настроив фильтр по <a href='".$GLOBALS['host']."/users/{$login}/setup/specsetup/' target='_blank'>своим</a><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='".$GLOBALS['host']."/users/{$login}/setup/specsetup/' target='_blank'>специализациям</a>, выберите и ответьте на подходящие вам задания.<br><br>";
            
            $message .= "4. Зарегистрируйтесь также на сайте <a href='http://dizkon.ru' target='_blank'>Dizkon.ru</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Если вы хотите участвовать и побеждать в дизайнерских конкурсах<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;воспользуйтесь нашим сервисом - DizKon.ru.<br><br>";
        }
        
        $message .= "Если у вас возникли вопросы, обратитесь в <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}' target='_blank'>службу поддержки FL.ru.</a><br/><br/>";
        
        $this->message = $this->GetHtml('', $message, array('header'=>'simple_with_add', 'footer'=>'simple'));
        //$this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->recipient = "{$t_user->login} <".$t_user->email.">";
        $this->subject = $subject;
        if (!$this->send('text/html')) return 'Неудалось отправить сообщение';
        //if (!$this->SmtpMail('text/html')) return 'Неудалось отправить сообщение';
        return '';
    }
    
    /**
     * Уведомляет админа о том, что появился новый подозрительный юзер.
     *
     * @param string $sLogin логин подозрительного юзера.
     * @param string $sName имя подозрительного юзера.
     * @param string $sSurname фамилия подозрительного юзера.
     */
    function adminNewSuspectUser( $sLogin = '', $sName = '', $sSurname = '' ) {
        $this->message = $this->GetHtml( '', 
'В списке <a href="' . $_SERVER["HTTP_HOST"] . '/siteadmin/suspicious-users/' . $this->_addUrlParams('b') . '">' . $_SERVER["HTTP_HOST"] . '/siteadmin/suspicious-users/</a> появился новый человек:<br />
----------------------------<br />
Логин: <a href="' . $_SERVER["HTTP_HOST"] . '/users/' . $sLogin . $this->_addUrlParams('b') .'">' . $sLogin . '</a><br />
Имя: ' . $sName . '<br />
Фамилия: ' . $sSurname . '<br />
----------------------------<br />' );
        
        $this->recipient = 'info@FL.ru';
        $this->subject   = 'Подозрительный пользователь на сайте '.$_SERVER["HTTP_HOST"];
        $this->SmtpMail( 'text/html' );
    }
	

    /**
     * Отправляет уведомлелние юзеру о смене пароля.
     * @param int $uid   users.uid юзера, сменившего пароль.
     * @param string $passwd   новый пароль.
     *
     * @return string   возможная ошибка.
     */
    function ChangePwd($uid, $passwd){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        
        $msg = 
        "На сайте FL.ru были произведены изменения параметров аккаунта, в контактных данных которого 
        в качестве адреса электронной почты был указан адрес вашего почтового ящика. 
        Если подобные письма будут продолжать приходить вам, пожалуйста, обратитесь в службу поддержки 
        FL.ru по адресу <a href='http://feedback.fl.ru/' target='blank'>http://feedback.fl.ru/</a>";
        
		$this->message = $this->GetHtml($t_user->uname, $msg, array('header'=>'simple', 'footer' => 'simple'));
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Изменение параметров учетной записи на сайте FL.ru";
        if (!$this->SmtpMail('text/html')) return 'Неудалось отправить сообщение';
        return '';
    }

	
    /**
     * Отправляет предупреждению юзеру о том, что аккаунт ПРО истекает в ближайшие дни. Вызывается из hourly.php.
     *
     * @return string   возможная ошибка.
     */
    function SendWarnings(){
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
        $t_user = new payed();
        $all = $t_user->GetWarnings();
        if ($all) foreach ($all as $ikey=>$recp){
            $page = ( substr($recp['role'], 0, 1) != 1 ) ? 'payed' : 'payed-emp';
            $body = 
            "Напоминаем, что ".date('d '.monthtostr(date('m', strtotime($recp['to_date']))).' Y в H:i ', strtotime($recp['to_date'])).
            "заканчивается время действия приобретенного вами аккаунта PRO на сайте FL.ru. 
             Вы можете <a href='{$GLOBALS['host']}/$page/{$this->_addUrlParams('b')}'>продлить</a> срок действия профессионального аккаунта.";
            
            $this->message = $this->GetHtml($recp['uname'], $body, array('header' =>'simple', 'footer'=>'simple_adv'));
            //$this->recipient = "\"".$recp['uname']." ".$recp['usurname']." [".$recp['login']."]\"<".$recp['email'].">";
            $this->recipient = $recp['uname']." ".$recp['usurname']." [".$recp['login']."] <".$recp['email'].">";
            $this->subject = "Заканчивается срок действия вашего аккаунта PRO на FL.ru";
            $this->SmtpMail('text/html');
        }
        return '';
    }


    /**
     * Отправляет предупреждение юзеру о том, что нужно логиниться хотя бы иногда, а то удалят. Устарела.
     * @deprecated
     *
     * @return string   возможная ошибка.
     */
    function SendUnactive(){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
		$t_user = new freelancer();
        $all = $t_user->GetUnactive();
        if ($all) foreach ($all as $ikey=>$recp){
            $this->message = "

Здравствуйте.

Вы не появлялись на сайте в течение трех месяцев, ваш аккаунт будет удален ".date('d '.monthtostr(date('m', strtotime($recp['to_date']))).' Y в H:i ', strtotime($recp['to_date'])).".
Если вы хотите оставить аккаунт, пожалуйста залогиньтесь на сайте ".$GLOBALS['host']."

-- 
Команда \"FL.ru\"
info@FL.ru
".$GLOBALS['host'];
            //$this->recipient = "\"".$recp['uname']." ".$recp['usurname']." [".$recp['login']."]\"<".$recp['email'].">";
            $this->recipient = $recp['uname']." ".$recp['usurname']." [".$recp['login']."] <".$recp['email'].">";
            $this->subject = "Внимание! Удаление аккаунта - FL.ru";
            $this->SmtpMail();
        }
        return '';
    }


    /**
     * @deprecated
     * Оставил как шаблон. Щас не используются подобные уведомления.
     *
     * @return string   возможная ошибка.
     */
    function OrderFP($login, $order_id, $d_time, $sum){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUser($login);
        $this->message = "Здравствуйте, ".$t_user->uname.".

Команда \"FL.ru\" благодарит вас за ваше желание участвовать в жизни нашего проекта.


Сайтом FL.ru была принята ваша заявка на платное размещение на главной странице.
Параметры заявки:

Срок действия - $d_time

Итого: $".$sum."

При оплате в примечании необходимо указать:

$t_user->uname $t_user->usurname [$t_user->login] (fp $order_id) 


Выбранные услуги будут предоставлены с момента зачисления платежа на счет FL.ru
После совершения вами перевода в течение рабочего дня платеж будет зачислен на счет FL.ru





Способы оплаты:

1. WebMoney 
Наш идентификатор в системе WebMoney 200477354071.
Для оплаты с использованием WebMoney Transfer

R199396491834 - кошелек для платежа в рублях
Z801604194058 - кошелек для платежа в долларах 
Перевод необходимо производить без протекции сделки.

В примечании укажите:
$t_user->uname $t_user->usurname [$t_user->login] (fp $order_id) 


2. Яндекс.Деньги 
Наш номер счета в системе Яндекс.Деньги 4100126337426.

В примечании укажите:
$t_user->uname $t_user->usurname [$t_user->login] (fp $order_id) 


3. Western Union 

Минимальный перевод 50 долларов.
Для совершения перевода с помощью Western Union необходимо сделать запрос о реквизитах на info@FL.ru.


4. Безналичный платеж

Минимальный перевод 100 долларов.
Для совершения оплаты по безналичному переводу необходимо сделать запрос о реквизитах на info@FL.ru.


-- 
Команда \"FL.ru\"
info@FL.ru
".$GLOBALS['host'];
        $this->message = input_ref($this->message);
        $this->recipient = "\"$t_user->uname $t_user->usurname [$t_user->login]\"<".$t_user->email.">";
        $this->subject = "Платное место на главной странице - FL.ru";
        if (!$this->SmtpMail()) $error = 'Невозможно отправить сообщение';
        return $error;
    }

	
    /**
     * Отправляет уведомление юзеру о том, что другой юзер ему сделал подарок (например, ПРО).
     *
     * @param  string  $from_login  users.login -- от кого подарок.
     * @param  string  $to_login    users.login -- кому подарок.
     * @param  string  $msg         не используется.
     * @param  int     $idg         present.id -- ид. подарка.
     *
     * @return string               возможная ошибка.
     */
    function NewGift($from_login, $to_login, $msg, $idg){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUser($to_login);
        $f_user = new users();
        $f_user->GetUser($from_login);
        
        $body = "Вы получили <a href=\"".$GLOBALS['host']."/present/?id=$idg{$this->_addUrlParams('b')}\">подарок</a> от пользователя <a href=\"".$GLOBALS['host']."/users/{$f_user->login}{$this->_addUrlParams('b')}\">{$f_user->uname} {$f_user->usurname}</a> [<a href=\"".$GLOBALS['host']."/users/{$f_user->login}{$this->_addUrlParams('b')}\">{$f_user->login}</a>]";
        
        $this->message = $this->GetHtml($t_user->uname, $body, array('header'=>'simple', 'footer'=>'simple'));
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Подарок на сайте FL.ru";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
    
    /**
     * Альфа-банк: уведомление о том, что ошибочно зачисленное списано
     * 
     * @param int $to_id uid юзера кому шлем уведомление.
     */
    function alphaBankMistakeSorry( $to_id, $op_date ) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID( $to_id );
        
        $this->message = $this->GetHtml( $t_user->uname, '
Информируем вас о том, что '.date('d.m.Y', strtotime($op_date)).' в '.date('H:i', strtotime($op_date)).' вам был ошибочно зачислен перевод средств на личный счет на сайте FL.ru. Данное некорректное зачисление было отменено, зачисленные по ошибке денежные средства списаны с вашего счета.
<br />
Приносим свои извинения за неудобства!
<br />
<br />
', 'simple' );
        
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject   = 'Приносим извинения за ошибочное начисление';
        if ( !$this->SmtpMail('text/html') ) $error = 'Невозможно отправить сообщение';
        return $error;
    }
	

    /**
     * Отправляет инструкцию (код подтверждения) юзеру, который запросил смену своего e-mail.
     *
     * @param   string  $login    users.login -- логин юзера.
     * @param   string  $newmail  новое мыло юзера.
     * @param   string  $code     код подтверждения.
     *
     * @return  string            возможная ошибка.
     */
    function ConfirmNewEmail($login, $newmail, $code){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUser($login);
        $this->message = $this->GetHtml($t_user->uname, "
Данное письмо отправлено почтовым роботом сервера FL.ru и не требует ответа.
<br />
Пожалуйста, если у вас есть вопросы, свяжитесь с нами по адресу: <a href='http://feedback.fl.ru/' target='_blank'>http://feedback.fl.ru/</a>
<br />
<br />
Чтобы изменить ваш старый e-mail на $newmail перейдите по этой ссылке
<a href='".HTTP_PREFIX.$_SERVER["HTTP_HOST"]."/activatemail.php?code=$code{$this->_addUrlParams('b', '&')}'>".HTTP_PREFIX.$_SERVER["HTTP_HOST"]."/activatemail.php?code=$code</a>
", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Изменение электронной почты на сайте FL.ru";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }

	
    /**
     * Отправляет уведомление юзеру о том, что его забанили.
     * первоначальная версия
     *
     * @param   string   $login   users.login -- логин юзера.
     * @return  string            возможная ошибка.
     */
    function SendBan($login) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$f_user = new users();
        $f_user->GetUser($login);
        $uid = $f_user->GetUid($error, $login);
        $ban = $f_user->GetBan($uid);

        $this->message = "Блокировка вашего аккаунта: $f_user->uname $f_user->usurname! <br />
<br />
Вы лишены доступа к вашему аккаунту на сайте FL.ru по причине некорректного поведения.<br />
<br />
";

        switch ($ban["reason"]) {
            case 1:
                $this->message .= "Причина: Крайне некорректное поведение на сайте <br /><br />";
                break;
            case 2:
                  $this->message .=   "Причина: Спам в блогах <br /><br />";
                break;
            case 3:
                  $this->message .=   "Причина: Спам в проектах <br /><br />";
                break;
        }
        $this->message .= "

".($ban["comment"] ? "Комментарий администратора: ".$this->ToHtml($ban["comment"])."<br /><br />" : "")."

Все ваши сообщения снимаются с публикации. <br />
Чтобы восстановить доступ к аккаунту, вам необходимо связаться с Командой FL.ru по адресу <a href='http://feedback.fl.ru/' target='_blank'>http://feedback.fl.ru/</a>. <br />
<br />
Ответ на это сообщение не будет рассматриваться <br />
Команда FL.ru";
        // print $this->message; exit;
        $this->message = $this->GetHtml($f_user->uname, $this->message, array());
		$this->recipient = "{$f_user->uname} {$f_user->usurname} [{$f_user->login}] <".$f_user->email.">";
        $this->subject = "Бан на FL.ru";
        $this->from = "FL.ru <administration@FL.ru>";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
	}
	
	/**
	 * Отправляет уведомление о том, что пользователя забанили.
	 * новая версия с текстом причины
	 * 
	 * @param  int $uid UID пользователя.
	 * @param  string $reason текст причины блокировки
	 * @return bool true - успех, false - провал
	 */
	function SendBan2( $uid, $reason ) {
	    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
	    $f_user = new users();
        $f_user->GetUserByUID( $uid );
        
        $this->message = "Блокировка вашего аккаунта: $f_user->uname $f_user->usurname! <br />
<br />
Вы лишены доступа к вашему аккаунту на сайте FL.ru по причине некорректного поведения.<br />
<br />
Причина: " . reformat( $reason, 24, 0, 0, 1, 24 ) . "<br />
<br />
Все ваши сообщения снимаются с публикации. <br />
Чтобы восстановить доступ к аккаунту, вам необходимо связаться с Командой FL.ru по адресу <a href='http://feedback.fl.ru/' target='_blank'>http://feedback.fl.ru/</a>. <br />
<br />
Ответ на это сообщение не будет рассматриваться <br />
Команда FL.ru
";
        
        $this->message   = $this->GetHtml( $f_user->uname, $this->message, array() );
		$this->recipient = "{$f_user->uname} {$f_user->usurname} [{$f_user->login}] <{$f_user->email}>";
        $this->subject   = 'Бан на FL.ru';
        $this->from      = 'FL.ru <administration@FL.ru>';
        
        return $this->SmtpMail( 'text/html' );
	}

    /**
     * Отсылает письмо о возврате документов (безналичный расчет)
     *
     * @param integer $uid      UID юзера
     * @return string           строка об ошибке
     */
    function DocsBack($uid){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$f_user = new users();
        $f_user->GetUserByUID($uid);
        $this->message = $this->GetHtml($f_user->uname,
"Здравствуйте, $f_user->uname $f_user->usurname!
<br />
<br />
Закрывающие документы по вашему безналичному расчету вернулись.Возможно вы указали некорректный
почтовый адрес в реквизитах или просто не получили наше письмо на почте.
Пожалуйста, свяжитесь с менеджером по адресу <a href='mailto:finance@FL.ru'>finance@FL.ru</a>

С уважением, команда FL.ru", array());
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Документы по безналичному расчету на FL.ru";
        $this->from = "FL.ru <finance@FL.ru>";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
	

    /**
     * Отсылает письмо об отмене выбора исполнителя по СбР
     *
     * @param integer $frl_id       UID фрилансера
     * @param integer $prj_id       id проекта
     * @return string
     */
    function NoRiskCancelFrl($frl_id, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUserByUID($frl_id);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
Сообщаем вам, что автор проекта <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a>
по «Безопасной Сделке» принял решение о смене исполнителя. 

", 'simple');

        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Смена исполнителя в «Безопасной Сделке»";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
	
	
    /**
     * Отсылает письмо о том, что Заказчик отправил т3 по СбР
     *
     * @param string $login     Логин фрилансера
     * @param integer $prj_id       id проекта
     * @return string
     */
    function NoRiskT3Send($login, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUser($login);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
Заказчик выслал вам техническое задание, бюджет и сроки по проекту <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a>
<br />
Пожалуйста, ознакомьтесь с условиями и подтвердите ваше согласие.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Утверждение бюджета, технического задания и сроков по «Безопасной Сделке»";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }

	
    /**
     * Отсылает письмо о том, что Заказчик отправил новое т3 по СбР
     *
     * @param string $login         Логин фрилансера
     * @param integer $prj_id       id проекта
     * @return string
     */
    function NoRiskNewT3Send($login, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUser($login);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
Сообщаем вам, что в техническое задание, бюджет и сроки по проекту <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> внесены изменения (дополнения).
<br />
Пожалуйста, ознакомьтесь с новой версией и подтвердите ваше согласие. 

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Техническое задание, бюджет и сроки по «Безопасной Сделке» были изменены ";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }

    /**
     * Отсылает письмо об обращении в арбитраж по СбР фрилансером (письмо Заказчику)
     *
     * @param integer $uid          UID Заказчика
     * @param integer $prj_id       id проекта
     * @return string
    */
    function NoRiskArbitrageEmp($uid, $prj_id){

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
        $t_user = new employer();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
		$this->message = $this->GetHtml($t_user->uname, "
		
Сообщаем вам, что исполнитель призвал Арбитраж для решения спорных вопросов,
возникших в процессе выполнения вами проекта <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('e')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a>

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Исполнитель призвал Арбитраж по «Безопасной Сделке»";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }

	
    /**
     * Отсылает письмо об обращении в арбитраж по СбР Заказчиком (письмо фрилансеру)
     *
     * @param integer $uid          UID фрилансера
     * @param integer $prj_id       id проекта
     * @return string
     */
    function NoRiskArbitrageFrl($uid, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
        $t_user = new freelancer();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
Сообщаем вам, что Заказчик призвал Арбитраж для решения спорных вопросов,
возникших в процессе работы над проектом <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> по «Безопасной Сделке».

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Заказчик призвал Арбитраж по «Безопасной Сделке»";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }


    /**
     * Отсылает письмо о завершении СбР (Заказчик нажал на кнопку)
     *
     * @param integer $frl_id       UID фрилансера
     * @param integer $prj_id       id проекта
     * @return string
     */
    function NoRiskClosed($frl_id, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID($frl_id);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
Сообщаем вам, что автор проекта <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> по «Безопасной Сделке» считает проект завершенным.
<br />
Вы можете получить деньги удобным вам способом.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Заказчик принял проект по «Безопасной Сделке»";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
  
    /**
     * Отсылает письмо о решении арбитража по СбР
     *
     * @param integer $uid          UID юзера
     * @param integer $prj_id       id проекта
     * @return string
     */
    function NoRiskArbiterClosed($uid, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "

Сообщаем вам, что арбитраж вынес решение по спорным вопросам, возникшим в процессе работы над проектом 
<a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('b')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> по «Безопасной Сделке».

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Арбитраж вынес решение по «Безопасной Сделке»";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }

    /**
     * Отсылает письмо о резерве денег по СбР
     *
     * @param integer $uid          UID фрилансера
     * @param integer $prj_id       id проекта
     * @return string
     */
    function NoRiskMoneyReserved($uid, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
		$t_user = new freelancer();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "

Сообщаем вам, что Заказчик зарезервировал деньги на личном счёте под «Безопасную Сделку» по проекту <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a>.
<br />
Теперь вы можете приступать к работе.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Заказчик зарезервировал деньги под «Безопасную Сделку»";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
  
    /**
     * Отсылает письмо о переводе денег на счет юзера по СбР
     *
     * @param integer $uid          UID юзера
     * @param integer $prj_id       id проекта
     * @return string
     */
    function NoRiskPaymentCommited($uid, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "

Поздравляем вас! «Безопасная Сделка» по проекту <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('b')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> завершена.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "«Безопасная Сделка» завершена";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }

    /**
     * Отсылает письмо с предупреждениями об окончании закрепления проекта на верху главной страницы
     *
     * @return string
     */
    function EndTopDaysPrjSendAlerts(){
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $projects = new projects();
        $alerts = $projects->GetAlertsPrjTopDays();
        if ($alerts) {
            foreach ($alerts as $alert) {
		        $prj_user = new users();
                $prj_user->GetUserByUID($alert['user_id']);
                if($prj_user->is_banned == '1') continue;
                if($alert['kind']==2 || $alert['kind']==7) {
                    // Конкурс
                    $this->subject = "Закрепление конкурса в ленте проектов FL.ru";
                    $this->message = $this->GetHtml($prj_user->uname, 
"{$alert['date_d']} ".monthtostr($alert['date_m'], true)." {$alert['date_y']} в {$alert['date_t']} заканчивается время закрепления вашего конкурса \"<a href='{$GLOBALS['host']}".getFriendlyURL("project", $alert['id']).$this->_addUrlParams('e')."'>".$alert['name']."</a>\" наверху ленты проектов на сайте FL.ru.
<br><br>
Для того чтобы продлить срок закрепления вашего конкурса, необходимо повторно закрепить конкурс в режиме его редактирования по ссылке <a href=".$GLOBALS['host']. "/public/?step=2&public=".$alert['id']."&red=/users/".$prj_user->login."/setup/projects/".$this->_addUrlParams('e', '&').">".$GLOBALS['host']. "/public/?step=2&public=".$alert['id']."&red=/users/".$prj_user->login."/setup/projects/</a>.
", array('header' => 'simple', 'footer' => 'emp_projects'));
                } else {
                    // Проект
                    $this->subject = "Завтра заканчивается срок закрепления вашего проекта на главной странице FL.ru";
                    $this->message = $this->GetHtml($prj_user->uname, 
"Завтра, {$alert['date_d']} ".monthtostr($alert['date_m'], true)." {$alert['date_y']} в {$alert['date_t']} заканчивается срок действия услуги «Закрепление проекта на главной странице сайта FL.ru». 
Вы можете <a href=".$GLOBALS['host']. "/public/?step=1&public=".$alert['id']."&red=/users/".$prj_user->login."/setup/projects/".$this->_addUrlParams('e', '&').">продлить срок</a> закрепления вашего проекта.
Напоминаем вам, что пользователи с <a href='".$GLOBALS['host']. "/payed/".$this->_addUrlParams('e')."'>аккаунтом PRO</a> экономят на платных услугах сайта.", array('header' => 'simple', 'footer' => 'emp_projects'));
                }
                $this->recipient = $prj_user->uname." ".$prj_user->usurname." [".$prj_user->login."] <".$prj_user->email.">";
				$this->SmtpMail('text/html');
            }
        }
    }
	
	
    /**
     * Отсылает письмо с сообщением о том, что пользователь забанен в блогах
     * первоначальная версия с кодом причины
     * 
     * @param  string $login login пользователя, которого нужно забанить
     * @param  int $reason код причины
     * @return string
     */
    function SendBlogsBan( $login, $reason = 1 ) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUser($login);
        
        $sReason  = ( $reason == 2 ) ? 'Спам в блогах' : (( $reason == 3 ) ? 'Спам в проектах' : 'Крайне некорректное поведение на сайте' );
        $sMessage = "Команда FL.ru заблокировала вам доступ в сервис \"Блоги\" по причине: $sReason.<br/><br/>
        Убедительно просим Вас ознакомиться с правилами сайта FL.ru <a href='".WDCPREFIX."/about/documents/appendix_2_regulations.pdf'>".WDCPREFIX."/about/documents/appendix_2_regulations.pdf</a> и руководствоваться ими в работе и общении на сайте.";
        
        $this->message = $this->GetHtml($t_user->uname, $sMessage, 'info');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Доступ в \"Блоги\" на FL.ru заблокирован";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }

    /**
	 * Отправляет уведомление о том, что пользователя забанили в блогах
	 * новая версия с текстом причины
	 * 
	 * @param  int $uid UID пользователя.
	 * @param  string $reason текст причины блокировки
	 * @return bool true - успех, false - провал
	 */
    function SendBlogsBan2( $uid, $reason ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
	    $t_user = new users();
        $t_user->GetUserByUID( $uid );
        
        $sMessage = "Команда FL.ru заблокировала вам доступ в сервис \"Блоги\" по причине: " . reformat( $reason, 24, 0, 0, 1, 24 ) . "<br/><br/>
        Убедительно просим Вас ознакомиться с правилами сайта FL.ru <a href='".WDCPREFIX."/about/documents/appendix_2_regulations.pdf'>".WDCPREFIX."/about/documents/appendix_2_regulations.pdf</a> и руководствоваться ими в работе и общении на сайте.";
        
        $this->message   = $this->GetHtml( $t_user->uname, $sMessage, 'info' );
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject   = 'Доступ в "Блоги" на FL.ru заблокирован';
        $this->from      = 'FL.ru <administration@FL.ru>';
        
        return $this->SmtpMail( 'text/html' );
    }
	
    /**
     * Отсылает юзеру ответ от менеджера (manager.php)
     *
     * @param integer $uid   UID юзера
     * @param integer $msg   текст ответа.
     *
     * @return string возможная ошибка.
     */
    function SendManagerAnswer($uid, $msg) {

        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUserByUID($uid);
      
        $this->message = $this->GetHtml($t_user->uname, $this->ToHtml($msg."
      <br />
	  <br />
      -----------
	  <br />
      Менеджер команды \"FL.ru\"
      "), array('header'=>'default', 'footer'=>''));
      
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Ответ менеджера на заявку";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
	
	
    /**
     * Отсылает юзеру уведомление о комментарии на его мнение.
     *
     * @param integer $uid   users.uid юзера, оставившего отзыв.
     * @param integer $uid2  users.uid юзера, оставившего комментарий на этот отзыв.
     *
     * @return string возможная ошибка.
     */
    function SendCommentOpinions($uid, $uid2) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        if($t_user->is_banned == '1') return null;
      
        $t_user2 = new users();
        $t_user2->GetUserByUID($uid2);
      
        $this->message = $this->GetHtml($t_user->uname, 
"Пользователь <a href='{$GLOBALS['host']}/users/{$t_user2->login}{$this->_addUrlParams('b')}'>{$t_user2->uname} {$t_user2->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$t_user2->login}{$this->_addUrlParams('b')}'>{$t_user2->login}</a>]
оставил комментарий на ваше мнение на странице своего аккаунта.
<br />
Вы можете прочитать его на странице аккаунта пользователя - <a href='{$GLOBALS['host']}/users/$t_user2->login/opinions/{$this->_addUrlParams('b')}'>{$GLOBALS['host']}/users/$t_user2->login/opinions/</a>
", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Новый комментарий на ваше мнение на FL.ru";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
    
    /**
     * Отсылает юзеру уведомление о комментарии на его отзыв.
     *
     * @param integer $uid   users.uid юзера, оставившего отзыв.
     * @param integer $uid2  users.uid юзера, оставившего комментарий на этот отзыв.
     *
     * @return string возможная ошибка.
     */
    function SendCommentFeedback($uid, $uid2, $isEdit = false) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        if($t_user->is_banned == '1') return null;
      
        $t_user2 = new users();
        $t_user2->GetUserByUID($uid2);
        
        $isEditText = $isEdit ? 'изменил' : 'оставил';
      
        $this->message = $this->GetHtml($t_user->uname, 
"Пользователь <a href='{$GLOBALS['host']}/users/{$t_user2->login}{$this->_addUrlParams('b')}'>{$t_user2->uname} {$t_user2->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$t_user2->login}{$this->_addUrlParams('b')}'>{$t_user2->login}</a>]
$isEditText комментарий на ваш отзыв на странице своего аккаунта.
<br />
Вы можете прочитать его на странице аккаунта пользователя - <a href='{$GLOBALS['host']}/users/$t_user2->login/opinions/{$this->_addUrlParams('b')}'>{$GLOBALS['host']}/users/$t_user2->login/opinions/</a>
", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "Новый комментарий на ваш отзыв на FL.ru";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
	
	
	

    /**
     * Отсылает сообщение личному менеджеру из обратной связи
     *
     * @param integer $uid id пользователя
     * @param string $msg сообщение
     * @param string $email e-mail адрес
     * @return string возможная ошибка
     */
    function SendManagerWork( $uid, $msg, $phone, $umail="", $fio="", $files=false, $email='' ){
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        if ($uid) {
            $t_user->GetUserByUID($uid);
        } else {
        	$t_user->uname = "Аноним";
        	$t_user->usurname =  "Аноним";
        	$t_user->login = "Аноним";
        	$t_user->email = "no_reply@free-lance.ru";
        }
        
        if($umail == "") {
            $mail = $t_user->email;
        } else {
            $mail = $umail;    
        }
        
        if($fio == "") {
            $name = $t_user->uname." ".$t_user->usurname." [".$t_user->login."]";    
        } else {
            $name = html_entity_decode($fio, ENT_QUOTES);
        }
        
        $msg = "Здравствуйте!\n\n".$msg;
        
        if($phone != "") {
            $msg .= "\n\nТелефон: $phone";
        }
        
        if($fio) {
            $msg .= "\r\nФИО: $fio";
        }
        
        $this->message = $this->GetHtml('', $this->ToHtml($msg), array());
        
        $this->recipient = ($email) ? $email : "Менеджер <{$GLOBALS['sManagerEmail']}>";
        $this->subject = "Подбор фрилансеров, обратная связь";
        
		$this->from = "$name <$mail>";
        
		$attaches = $this->CreateAttach($files);
		if (!$this->SmtpMail('text/html', $attaches)) $error = 'Невозможно отправить сообщение';
        return $error;
    }
    
    /**
     * Отсылает сообщение личному менеджеру на заказ звонка
     *
     * @param integer $uid id пользователя
     * @param string $fio Фамилия имя отчествое
     * @param string $phone телефон для связи
     * @param string $time_to_call Удобное время звонка
     * @return string возможная ошибка
     */
    function SendManagerOrderCall( $uid, $fio, $phone, $time_to_call, $client_email='', $email='' ){
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        if ($uid) {
            $t_user->GetUserByUID($uid);
        } else {
        	$t_user->uname = "Аноним";
        	$t_user->usurname =  "Аноним";
        	$t_user->login = "Аноним";
        	$t_user->email = "no_reply@free-lance.ru";
        }
        
        $mail = $t_user->email;
        
        if($fio == "") {
            $name = $t_user->uname." ".$t_user->usurname." [".$t_user->login."]";    
        } else {
            $name = html_entity_decode($fio, ENT_QUOTES);
        }
        
        $msg = "Здравствуйте!\n\n";
        $msg .= "\n\nФИО: {$name}";
        $msg .= "\n\nТелефон: $phone";
        $msg .= "\n\nУдобное время звонка: $time_to_call";
        if($client_email != '') $msg .= "\n\nE-mail: $client_email";
        
        $this->message = $this->GetHtml('', $this->ToHtml($msg), array());
        
        $this->recipient = ($email) ? $email : "Менеджер <{$GLOBALS['sManagerEmail']}>";
        $this->subject = "Заказ звонка менеджера";
        
		$this->from = "$name <$mail>";
        
		if (!$this->SmtpMail('text/html', $attaches)) $error = 'Невозможно отправить сообщение';
        return $error;
    }

	
    /**
     * Отсылает сообщение порльзователю о том, что аккаунт PRO будет автоматически продлен
     *
     * @param integer $user_id id пользователя
     * @param  string $to_date дата/время окончания PRO
     * @return string возможная ошибка
     */
    function PROEnding( $user_id, $to_date ) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/payed.php";
		$user = new users();
        $user->GetUser($user->GetField($user_id,$ee,'login'));
        if($user->is_banned == '1') return null;

        $cost = ( substr($user->role, 0, 1) != 1 ) ? payed::PRICE_FRL_PRO : payed::PRICE_EMP_PRO;
        $page = ( substr($user->role, 0, 1) != 1 ) ? 'payed' : 'payed-emp';
        $date = date('d '.monthtostr(date('m', time()+86400)).' Y года', time()+86400);
        $time = date('H:i ', strtotime($to_date));
        $body = 
        "Завтра, {$date}, в {$time} заканчивается срок действия вашего аккаунта PRO. 
         Напоминаем, что вы подключили функцию автопродления профессионального аккаунта. 
         Это означает, что завтра, {$date}, система автоматически продлит действие аккаунта PRO на месяц, 
         при этом с вашего счета будут списаны " . $cost . " руб. (стоимость аккаунта PRO за месяц). 
         Услуга автопродления срока действия профессионального аккаунта является бесплатной.";
        
         $this->message = $this->GetHtml($user->uname, $body, array('header' => 'simple', 'footer' => 'simple_adv'));
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <".$user->email.">";
        $this->subject = "Срок действия вашего аккаунта PRO истекает завтра";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
    
    /**
     * Отсылает сообщение пользователю о невозможности автоматического продления аккаунта PRO из-за нехватки средств на счету
     *
     * @param integer $user_id id пользователя
     * @return string возможная ошибка
     */
    function PROAutoProlongError($user_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/payed.php";
		$user = new users();
        $user->GetUser($user->GetField($user_id,$ee,'login'));
        $cost = ( substr($user->role, 0, 1) != 1 ) ? payed::PRICE_FRL_PRO : payed::PRICE_EMP_PRO;
        $page = ( substr($user->role, 0, 1) != 1 ) ? 'payed' : 'payed-emp';
        $this->message = $this->GetHtml($user->uname, 
"Суммы на вашем счёте недостаточно для автопродления PRO. Для того чтобы система смогла продлить ваш PRO на месяц, вам необходимо пополнить ваш счет на FL.ru на $cost руб. (стоимость аккаунта pro в месяц).
<br />
Сама услуга автопродления является бесплатной.
<br />
<br />
Вы можете отказаться от данной функции на странице <a href='{$GLOBALS['host']}/$page/{$this->_addUrlParams('b', '&')}#pro_autoprolong'>{$GLOBALS['host']}/$page/#pro_autoprolong</a>", 'simple');
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <".$user->email.">";
        $this->subject = "Суммы на вашем счёте недостаточно для автопродления PRO";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
	
	
    /**
     * Отсылает сообщение порльзователю о об успешном автоматическом продлении аккаунта PRO
     *
     * @param integer $user_id id пользователя
     * @return string возможная ошибка
     */
    function PROAutoProlongOk($user_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/payed.php";
		$user = new users();
        $user->GetUser($user->GetField($user_id,$ee,'login'));
        $cost = ( substr($user->role, 0, 1) != 1 ) ? payed::PRICE_FRL_PRO : payed::PRICE_EMP_PRO;
        $page = ( substr($user->role, 0, 1) != 1 ) ? 'payed' : 'payed-emp';
        
        $body = 
        "Срок действия вашего аккаунта PRO был автоматически продлен на месяц, так как у вас подключена функция автопродления PRO. 
         С вашего счета были списаны ". $cost . " руб. (стоимость аккаунта PRO за месяц). 
         Обратите внимание: сама услуга автопродления является бесплатной.  
         Вы можете <a href='{$GLOBALS['host']}/$page/{$this->_addUrlParams('b', '&')}#pro_autoprolong'>отказаться</a> от автопродления аккаунта PRO.";
        
        $this->message = $this->GetHtml($user->uname, $body, array('header' => 'simple', 'footer' =>'simple_adv'));
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <".$user->email.">";
        $this->subject = "Ваш аккаунт PRO продлен на месяц. " . $cost ." руб. были списаны с вашего счёта";
        if (!$this->SmtpMail('text/html')) $error = 'Невозможно отправить сообщение';
        return $error;
    }
	
	
    /**
     * Отправляет уведомления о новых комментариях в корп блоге.
     * 
     * @param string $message  комментарий
     * @param resource $user   данные юзера
     */
    function CorporativeBlogNewComment($comment, $user, $p_user, $link=null)
    {  
		if(substr($user->subscr, 2, 1) == '1'){ } 
            
        $this->subject = "Ответ на комментарий в корпоративном блоге на сайте FL.ru";

        
        $this->message = $this->GetHtml($p_user->uname, 

"Команда FL.ru благодарит вас за ваше желание участвовать в жизни нашего портала. 
<br /><br />
<a href='{$GLOBALS['host']}/users/{$user->login}'>{$user->uname} {$user->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$user->login}{$this->_addUrlParams('b', '&')}'>{$user->login}</a>]
оставил(а) вам комментарий к сообщениям/комментариям в корпоративном блоге на сайте FL.ru.

<br />--------
<br />".strip_tags(input_ref(LenghtFormatEx($comment['title'], 300), 1))."
<br />---
<br />".strip_tags(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
<br />--------
<br />
$link

");
        $this->recipient = $p_user->uname." ".$p_user->usurname." [".$p_user->login."] <".$p_user->email.">";
        $this->SmtpMail('text/html');
        
        return $this->sended;
    }
	
	
	/**
     * Напоминание о объявлении победителей
     *
     * @return   string    возможная ошибка
     */    
	function ContestReminder() {
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
		$users = contest::WInterval('1 day');
		
		if (!$this->Connect())
			return "Невозможно соеденится с SMTP сервером";
		
		foreach ($users as $prj_id=>$u) {
            if ( intval($u['is_blocked']) > 0 ) {
                continue;
            }
            
            $project_name = htmlspecialchars($u['project_name'], ENT_QUOTES, 'CP1251', false);
            
			// Заказчик
			$user = new employer();
			$user->GetUserByUID($u['employer']);
			if ($user->email && substr($user->subscr, 8, 1) == '1') {
                
                $u['project_name'] = htmlspecialchars($u['project_name'], ENT_QUOTES, 'CP1251', false);
                
				$this->message = $this->GetHtml($user->uname, "
Сообщаем вам, что до объявления победителей в конкурсе «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id).$this->_addUrlParams('e')."\">".$project_name."</a>» остался один день.
", array('header' => 'default', 'footer'=>'sub_emp_projects'), array('login'=>$user->login));
				$this->recipient = "$user->uname $user->usurname [$user->login] <".$user->email.">";
				$this->subject = 'Остался 1 день до объявления победителей конкурса «'.htmlspecialchars_decode($u['project_name'], ENT_QUOTES).'»';
				$this->SmtpMail('text/html');
			}

			// фрилансеры
			foreach ($u['freelancer'] as $user) {
				if (!$user['email'] || substr($user['subscr'], 8, 1) != '1' || $user['is_banned'] == '1') continue;
				$this->message = $this->GetHtml($user['uname'], "
Сообщаем вам, что остался один день до объявления победителей в конкурсе «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id).$this->_addUrlParams('f')."\">".$project_name."</a>».
Вы можете перейти к своей <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id)."?offer={$user['offer_id']}{$this->_addUrlParams('f', '&')}#offer-{$user['offer_id']}\">работе</a>.
<br />
", array('header'=>'simple', 'footer'=>'default'), array('login' => $user['login']));
				$this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
				$this->subject = 'Остался 1 день до объявления победителей конкурса «'.htmlspecialchars_decode($u['project_name'], ENT_QUOTES).'»';
				$this->SmtpMail('text/html');
			}
		
		}
		return 0;
	}

	
	/**
     * Напоминание об окончании конкурса
     *
     * @return   string    возможная ошибка
     */    
	function ContestEndReminder() {
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
		$users = contest::WInterval('1 day', 'end_date');
		
		if (!$this->Connect()) return "Невозможно соеденится с SMTP сервером";
		
		foreach ($users as $prj_id=>$u) {
            
            $project_name = htmlspecialchars($u['project_name'], ENT_QUOTES, 'CP1251', false);
            
			// Заказчик
			$user = new employer();
			$user->GetUserByUID($u['employer']);
			if ($user->email && substr($user->subscr, 8, 1) == '1') {
				$this->message = $this->GetHtml($user->uname, "
Сообщаем вам, что остается один день до окончания конкурса «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id).$this->_addUrlParams('e')."\">".$project_name."</a>».
", array('header' => 'simple', 'footer' => 'sub_emp_projects'), array('login'=> $user->login));
				$this->recipient = "$user->uname $user->usurname [$user->login] <".$user->email.">";
				$this->subject = 'Остался день до окончания конкурса «'.htmlspecialchars_decode($u['project_name'], ENT_QUOTES).'»';
				$this->SmtpMail('text/html');
			}

			// фрилансеры
			foreach ($u['freelancer'] as $user) {
				if (!$user['email'] || substr($user['subscr'], 8, 1) != '1' || $user['is_banned'] == '1') continue;
				
				$this->message = $this->GetHtml($user['uname'], "
Сообщаем вам, что остается один день до окончания конкурса «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id).$this->_addUrlParams('f')."\">".$project_name."</a>».
<br />
Вы можете перейти к своей <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id)."?offer={$user['offer_id']}{$this->_addUrlParams('f', '&')}#offer-{$user['offer_id']}\">работе</a>.
<br />
", array('header'=>'simple', 'footer'=>'default'), array('login' => $user['login']));
				$this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
				$this->subject = 'Остался день до окончания конкурса «'.htmlspecialchars_decode($u['project_name'], ENT_QUOTES).'»';
				$this->SmtpMail('text/html');
			}
		
		}
		
		return 0;
	}
	
    /**
     * Отпрака счета-фактуры либо акта 
     * 
     * @param users $user 
     * @param CFile $file 
     * @param string $type Тип документа (sf - счет-фактура, act - акт)
     * @return string
     */
    function DocSend(users $user, CFile $file, $type = 'sf') {
        if(!$user->uid) return false;

        $this->message = $this->GetHtml($user->uname,
        "Здравствуйте, $user->uname $user->usurname!
        <br />
        <br />
        Это сообщение создано автоматически. Пожалуйста, не отвечайте на него!
        <br />
        <br />
        В приложении " . ($type == 'sf' ? 'счет' : 'акт') . " за услуги, оказанные на сайте FL.ru (ООО \"ВААН\") <br />
        Оригиналы будут  направлены вам в ближайшее время.
        <br />
        <br />
        По всем вопросам вы можете обращаться по адресу - <a href='mailto:finance@FL.ru'>finance@FL.ru</a> <br />
        Благодарим за сотрудничество!", array());

        $att = $this->CreateAttach($file);

        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        if ($type == 'sf') {
            $this->subject = "Счет-фактура, пополнение лицевого счета на сайте FL.ru (ООО \"ВААН\")";
        } else {
            $this->subject = "Акт, пополнение лицевого счета на сайте FL.ru (ООО \"ВААН\")";
        }
        $this->from = "FL.ru <finance@FL.ru>";
        if (!$this->SmtpMail('text/html', $att))
            $error = 'Невозможно отправить сообщение';

        return $error;
    }

    /**
     * Отпрака счета-фактуры либо акта для личного менеджера
     * 
     * @param users $user 
     * @param CFile $file 
     * @param string $type Тип документа (sf - счет-фактура, act - акт)
     * @return string
     */
    function LMDocSend(users $user, CFile $file, $type = 'sf') {
        if(!$user->uid) return false;

        $this->message = $this->GetHtml($user->uname,
        "Здравствуйте, $user->uname $user->usurname!
        <br />
        <br />
        Это сообщение создано автоматически. Пожалуйста, не отвечайте на него!
        <br />
        <br />
        В приложении " . ($type == 'sf' ? 'счет' : 'акт') . " за услуги, оказанные на сайте FL.ru (ООО \"ВААН\") <br />
        Оригиналы будут  направлены вам в ближайшее время.
        <br />
        <br />
        По всем вопросам вы можете обращаться по адресу - <a href='mailto:finance@FL.ru'>finance@FL.ru</a> <br />
        Благодарим за сотрудничество!", array());

        $att = $this->CreateAttach($file);

        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        if ($type == 'sf') {
            $this->subject = "Счет-фактура, услуги личного менеджера на сайте FL.ru (ООО \"ВААН\")";
        } else {
            $this->subject = "Акт, услуги личного менеджера на сайте FL.ru (ООО \"ВААН\")";
        }
        $this->from = "FL.ru <finance@FL.ru>";
        if (!$this->SmtpMail('text/html', $att))
            $error = 'Невозможно отправить сообщение';

        return $error;
    }
    
    /**
     * Посылаем сообщение всем емейлам из файла вида: 
     * 
     * @example file.txt
     * 'email1@email.ru'
     * 'email2@email.ru'
     * ...
     * 'email1299@email.ru'
     *
     * @param string $file_name   Полный путь до файла если файл не в папке со скриптом где запускают функцию
     * @param string $subject     тема сообщения
     * @param string $message     Сообщение
     * @param string $from        От кого письмо
     */
    function massSendingForFile($file_name, $subject, $message, $from = 'FL.ru <no_reply@free-lance.ru>') {
        $file = file($file_name); // Читкаем файл
        if(count($file) < 1) return false;
        
        $this->subject = $subject;
        $this->message = $this->GetHtml(NULL, $message, NULL); 
        $this->from    = $from;
         
        foreach($file as $mail) {
            if(trim($mail) == "") continue;
            $mail = str_replace("'", "", $mail);
            $this->recipient = "<" . trim($mail) . ">";
            $this->SmtpMail('text/html');
        }
    }

    /**
     * Уведомление админу о том, что пользователь внес изменения на вкладке "Финансы".
     *
     * @param string $login   логин пользователя.
     */
    function FinanceChanged($login) {
        $user = new users();
        $user->GetUser($login);
        if(!$user->uid) return;
        $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS, $user, is_emp($user->role));
        if(!$sbr->getReserved()) return;

        $this->subject = "Пользователь {$user->login} заполнил реквизиты на странице Финансы";
        $this->message = $this->GetHtml(NULL, 
          "Пользователь {$user->uname} {$user->usurname} [{$user->login}] заполнил реквизиты на страницы Финансы:<br/><br/>
           <a href='{$GLOBALS['host']}/users/{$user->login}/setup/finance/{$this->_addUrlParams('b')}'>{$GLOBALS['host']}/users/{$user->login}/setup/finance/</a>
          ",
          'info'
        );
        $this->recipient = '<donpaul@FL.ru>';
        $this->SmtpMail('text/html');
    }

    /**
     * Напоминание, что нужно заполнить закладку "Финансы", блок "Личные данные", если у юзера есть активные СБР.
     * @see sbr_meta::getReqvAlerts()
     * @deprecated Нужно переработать логику отправки уведомлений
     */
    function SbrReqvAlerts() {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        if($users = sbr_meta::getReqvAlerts()) {
            $this->subject = "Заполнение вкладки «Финансы»";
            foreach($users as $u) {
                $msg =  "
                  Пожалуйста, внесите все необходимые данные на вкладке «<a href='{$GLOBALS['host']}/users/{$u['login']}/setup/finance/{$this->_addUrlParams('b')}'>Финансы</a>». Указанные во вкладке реквизиты требуются для составления договора
                  на оказание услуг и успешного проведения «Безопасной Сделки».
                ";
                $this->message = $this->GetHtml($u['uname'], $msg, array('header'=>'simple', 'footer'=>'simple'));
                $this->recipient = $u['uname']." ".$u['usurname']." [".$u['login']."] <".$u['email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }
    
    /**
     * Уведомление о скором истечении сроков выполнения этапа СБР или о том, что сроки уже истекли.
     * @see sbr_meta::getDeadlines()
     */
    function SbrDeadlineAlert() {
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/sbr.php");
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($deadlines = sbr_meta::getDeadlines())) return 0;
        foreach($deadlines as $stage) {
            $sbr_link_e = "задачи «<a href='{$url}?site=Stage&id={$stage['id']}'>{$stage['name']}</a>» в проекте «Безопасной Сделки» «<a href='{$url}?id={$stage['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$stage['sbr_name']}</a>»";
            $sbr_link_f = "задачи «<a href='{$url}?site=Stage&id={$stage['id']}'>{$stage['name']}</a>» в проекте «Безопасной Сделки» «<a href='{$url}?id={$stage['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$stage['sbr_name']}</a>»";
            for($e=0;$e<2;$e++) {
                $r = $e ? 'e_' : 'f_';
                if($stage[$r.'banned'] == '1') continue;
                if($stage['is_dead'] == 't') {
                    $this->subject = 'Сроки выполнения проекта по «Безопасной Сделке» истекли';
                    $msg = "Сообщаем вам о том, что закончился срок выполнения ".($e ? $sbr_link_e : $sbr_link_f)."<br/><br/>";
                    $msg .= $e ? "На настоящий момент проект является просроченным, и вы можете обратиться в Арбитраж для выяснения дальнейшей судьбы сделки.<br/><br/>
                                  Пожалуйста, свяжитесь с фрилансером для выяснения возможных путей разрешения ситуации."
                               : "На настоящий момент проект является просроченным, и Заказчик вправе обратиться в Арбитраж для выяснения дальнейшей судьбы сделки.<br/><br/>
                                  Пожалуйста, свяжитесь с Заказчиком для выяснения возможных путей разрешения ситуации.";
                }
                else {
                    $this->subject = 'До окончания «Безопасной Сделки» остается 1 день';
                    $msg = $e ? "Напоминаем вам о том, что до окончания выполнения {$sbr_link_e} остался один день.<br/><br/>
                                 Мы уже предупредили об этом исполнителя проекта, фрилансера {$stage['f_uname']} {$stage['f_usurname']} [{$stage['f_login']}], и надеемся, что он успеет в срок."
                              : "Сообщаем вам о том, что до окончания {$sbr_link_f} остается 1 день.";
                }
                $this->message = $this->GetHtml($stage[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple'));
                $this->recipient = $stage[$r.'uname']." ".$stage[$r.'usurname']." [".$stage[$r.'login']."] <".$stage[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    
    /**
     * Отправляет реестр платежей в ЯД (СБР)
     * @see yd_payments
     * @see sbr_stages::ydPayout()
     *
     * @param string from_dt   день, на который нужно сформировать реестр.
     */
    function sendYdDayRegistry($from_dt = NULL, $debug = false) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yd_payments.php');
        $yd = new yd_payments();
        $yd->DEBUG = $debug;
        
        $from_dt = date('Y-m-d', strtotime($from_dt ? $from_dt : '-1 day'));
        if( !($enc_file = $yd->createRegistry($from_dt)) )
            return implode(' | ', $yd->errors);

        $default_from = $this->from;
        $this->from = yd_payments::REGISTRY_FROM;
        $this->subject = "Реестр принятых платежей, {$from_dt}, ".yd_payments::AGENT_NAME;
        $this->message = '';

        if(!$yd->DEBUG) {
            // в ЯД.
            $this->recipient = yd_payments::REGISTRY_YDTO;
            $this->SmtpMail('text/plain', $this->CreateLocalAttach($enc_file));
        }
        
        // в ВААН
        $noenc_att = $this->CreateLocalAttach($enc_file.yd_payments::REGISTRY_NOENC_SFX);
        foreach(yd_payments::$REGISTRY_VAANTO as $email) {
            $this->recipient = $email;
            $this->SmtpMail('text/plain', $noenc_att);
        }
        $this->from = $default_from;
    }
    
    /**
     * Уведомление за 1 день до окончания срока платных специализаций, если выключено автопродление
     * 
     * @param  string $user_id UID пользователя
     * @param  string $sum сумма к оплате
     * @return string сообщение об ошибке
     */
    function PaidSpecsEnding($user_id, $sum) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
        $user = new users();
        $user->GetUser($user->GetField($user_id, $ee, 'login'));
        if($user->is_banned == '1') return null;
        $date = date('d '.monthtostr(date('m', time()+86400)).' Y года', time()+86400);
        $body = 
        "Завтра, {$date}, заканчивается срок действия оплаченных вами дополнительных специализаций.
        У вас включена функция автопродления. Это означает, что завтра, {$date}, система автоматически 
        продлит действие выбранных вами дополнительных специализаций, при этом с вашего счета будут списаны 90 руб. 
        Обратите внимание: сама услуга автопродления является бесплатной.
        <br/><br/>
        Напоминаем вам, что обладатели «Аккаунта PRO» могут бесплатно выбрать 5 дополнительных специализаций. 
        Подробнее о всех возможностях профессионального аккаунта можно узнать <a href='{$GLOBALS['host']}/payed/{$this->_addUrlParams('f')}'>здесь</a>.";
        
        $this->message = $this->GetHtml($user->uname, $body, array('footer' => 'simple', 'header'=>'simple'));

        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        $this->subject = "Завтра истекает срок действия дополнительных специализаций в портфолио на FL.ru ";
        if (!$this->SmtpMail('text/html'))
            $error = 'Невозможно отправить сообщение';
        return $error;
    }

    /**
     * Уведомление об автоматическом продлении платных специализаций
     *
     * @param  string $user_id UID пользователя
     * @param  string $sum сумма к оплате
     * @return string сообщение об ошибке
     */
    function PaidSpecsAutopayed($user_id, $sum) {
        return; // #0022795
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
        $user = new users();
        $user->GetUser($user->GetField($user_id, $ee, 'login'));
        if($user->is_banned == '1') return null;
        $body = 
        "Срок действия выбранных вами дополнительных специализаций был автоматически продлен на месяц, 
         так как у вас включена функция автопродления данной услуги. С вашего счета были списаны " . round($sum, 2) . " руб. 
         Обратите внимание: сама услуга автопродления является бесплатной. Вы можете <a href='{$GLOBALS['host']}/payed/{$this->_addUrlParams('f')}'>отказаться</a> от функции автопродления.";
        $this->message = $this->GetHtml($user->uname, $body, array('footer' => 'simple', 'header'=>'simple'));

        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        $this->subject = "Действие ваших дополнительных специализаций продлено на месяц";// {$sum} FM были списаны с вашего счёта.";
        if (!$this->SmtpMail('text/html'))
            $error = 'Невозможно отправить сообщение';
        return $error;
    }

    /**
     * Отправляет email контакам из /siteadmin/contacts/. Вызвается из hourly.php.
	 *
	 * @return   string   возможная ошибка
     */
	function SendMailToContacts()
	{

		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/contacts.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $mails = contacts::GetMails();

        if($mails) {
            $fromSave = $this->from;
            foreach($mails as $mail) {
                $user = new users();
                $user->GetUser($user->GetField($mail['user_id'], $ee, 'login'));
                $this->subject = $mail['subject'];
                $attaches = array();
                if($mail['attaches']) {
                    $files = preg_split("/,/",$mail['attaches']);
        			foreach($files as $a) {
        				$attaches[] = new CFile('users/'.substr($user->login, 0 ,2).'/'.$user->login.'/upload/'.$a);
		        	}
		            $attaches = $this->CreateAttach($attaches);
                }
                $contact_ids = preg_split("/,/",$mail['contact_ids']);
                
                foreach ( $contact_ids as $contact_id ) {
                    $contact = contacts::getContactInfo( $contact_id );
                    
                    if ( $contact['emails'] ) {
                        $msg_text = $mail['message'];
                        $msg_text = preg_replace( "/%CONTACT_NAME%/", $contact['name'], $msg_text );
                        $msg_text = preg_replace( "/%CONTACT_SURNAME%/", $contact['surname'], $msg_text );
                        $msg_text = preg_replace( "/%CONTACT_COMPANY%/", $contact['company'], $msg_text );
                        
                        foreach ( $contact['emails'] as $email ) {
                            $this->from      = 'ekaterina@FL.ru';
        					$this->recipient = $contact['name']." <".$email.">";
        					$this->message   = $msg_text;
        					
        					$this->SmtpMail( 'text/html', $attaches );
                        }
                    }
                }
            contacts::DeleteMail($mail['id']);
            }
            $this->from = $fromSave;
        }
		return '';
	}

	/**
	 * Уведомление от отказе в публикации статьи
	 * 
	 * @param string $user_id UID пользователя
	 * @param string $title заголовок статьи
	 * @param string $msg причина отказа
	 */
    function delArticleSendReason($user_id, $title, $msg) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
        $user = new users();
        $user->GetUserByUID($user_id);

        if(!$user->uid) return;

        $this->subject = "Ваша статья не будет опубликована на FL.ru";
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        
        
        //$body  = "Спасибо за интерес к разделу “Статьи и интервью”";
        $reason = ".";
        if ($msg) {
            $reason = " по причине:<br/><br/>
         ----<br/>
         {$msg}<br/>
         ----";
        }
        $body  = 
        "К сожалению, ваша статья не была принята к публикации модератором FL.ru. 
         В публикации статьи «{$title}» было отказано$reason
         <br/><br/>
         Обязательно присылайте ваши новые работы – возможно, они будут опубликованы. 
         Благодарим за проявленный интерес к разделу <a href='{$GLOBALS['host']}/articles/{$this->_addUrlParams('b')}'>«Статьи и интервью»</a> сайта FL.ru.";

        $this->message = $this->GetHtml($user->uname, $body, array('header'=>'default', 'footer'=>'simple'));
        $this->SmtpMail('text/html');
    }

        /**
         * @deprecated @see pmail::DepositMail #0016262 
         * Уведомления по безналичным рассчетам.
         * пополнение личного счета и резервирование денег через банк по СБР #0010465
         *
         * @param string $user_id UID пользователя
         * @param string $billCode номер счета
         * @param float $sum сумма
         */
        public function depositNotify($uid, $billCode, $sum) {
//            echo $uid.' '.$bill_no.' '.$sum.' '.$op_id.' '.$payment_sys;
//            require_once dirname(__FILE__).'/users.php';
//            require_once dirname(__FILE__).'/reqv_ordered.php';
//            $reserved = account::getOperationInfo($op_id);
//            if($payment_sys == 4){// безнал
//                $reqv_ordered = new reqv_ordered();
//                $billCode = 'Б-'.$reserved['billing_id'].'-'.sizeof($reqv_ordered->GetByUid($uid));
//            }elseif($payment_sys == 5){// физлицо
//                $DB = new DB('master');
//                if($code = $DB->val("SELECT bill_num FROM bank_payments WHERE billing_id=?i", $op_id)) {
//                    $billCode = $code;
//                }
//                
//            }else{
//                return false;
//            }
            $num_str = 'по  счету № '. $billCode;

            $t_user = new users();
            $t_user->GetUserByUID($uid);
            $this->message = $this->GetHtml($t_user->uname, $this->ToHtml(
"Команда FL.ru информирует вас о том, что денежные средства {$num_str}
на безналичный перевод в сумме {$sum} ".  ending($sum, 'рубль', 'рубля', 'рублей')." были зачислены на ваш личный счет на сайте.
"), array('header'=>'simple', 'footer'=>'simple'));
            $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
            $this->subject = "Пополнение личного счета на FL.ru";
            $this->SmtpMail('text/html');
    }

    
    /**
     * Посылаем уведомление фрилансеру о том что документы по СБР получены
     * @param string $suids Значение типа 27_11 где 27 - ИД Этапа СБР, 11 - ИД Пользователя   
     * @return bolean 
     */
    public function docsReceivedSBR($suids) {
        session_start();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        list($stage_id, $user_id) = explode("_", $suids);
        $sbr = new sbr($user_id);
        $stage = current($sbr->getStages($stage_id));
        $t_user = new users();
        $t_user->GetUserByUID($user_id);
        
        $this->subject = "Получены документы по «Безопасной Сделке» №{$stage->data['sbr_id']}/{$stage->data['id']}";
        $message = "Отправленные вами документы по «Безопасной Сделке» №{$stage->data['sbr_id']}/{$stage->data['id']} получены компанией FL.ru. 
                    На следующем этапе <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>завершения текущей «Безопасной Сделки»</a> вам будут перечислены денежные средства в счет оплаты за выполненную работу. 
                    Вы можете получить дополнительную информацию относительно документации по «Безопасным Сделкам» в соответствующем <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>разделе «Помощи»</a>.";
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->message = $this->GetHtml($t_user->uname, $message, array('header'=>'default', 'footer'=>'simple_norisk'));

        return $this->SmtpMail('text/html');
    }
    
    /**
     * Посылает пользователям уведомления если до их разбана на сайте осталось несколько дней -- по умолчанию 1 день.
     * 
     * @param integer $days   За сколько дней смотреть.
     */
    public function sendReminderUsersUnBan($days = 1) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        $users = users::getReminderUsersUnBan($days);
        
        foreach($users as $user) {
            if (!$user['email']) continue;
            $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $day_str = $days . " " . ending($days, "день", "дня", "дней");
            $date_str = date('d.m.Y г.', strtotime("+ {$days} day"));
            
            switch($user['where']) {
                // В блогах
                case 1:
                    $this->subject = "Вам будет открыт доступ в «Блоги» на FL.ru";
                    $message = 
                    "Через {$day_str}, {$date_str}, доступ в «Блоги» на FL.ru будет для вас открыт. 
                    Во избежание блокировки доступа в данный раздел сайта в будущем рекомендуем вам ознакомиться с <a href='https://feedback.fl.ru/article/details/id/168{$this->_addUrlParams('b', '?')}' target='_blank'>правилами поведения</a> в «Блогах».
                    <br/><br/>
                    По всем возникающим вопросам вы можете обращаться в нашу <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}' target='_blank'>службу поддержки</a>.";
                    $this->message = $this->GetHtml($user['uname'], $message, array('header'=>'default', 'footer'=>'simple'));
                    $this->SmtpMail('text/html');
                    break;
                // На всем сайте
                case 0:
                    $this->subject = "Скоро ваш аккаунт будет разблокирован на FL.ru";
                    
                    $message = 
                    "Через {$day_str}, {$date_str}, ваш аккаунт на FL.ru будет разблокирован. 
                    Во избежание блокировки в будущем рекомендуем вам ознакомиться с <a href='https://feedback.fl.ru/knowledgebase?category=38{$this->_addUrlParams('b', '?')}' target='_blank'>правилами ресурса.</a><br/><br/>
                    По всем возникающим вопросам вы можете обращаться в нашу <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}' target='_blank'>службу поддержки</a>.";
                    $this->message = $this->GetHtml($user['uname'], $message, array('header'=>'default', 'footer'=>'simple'));
                    $this->SmtpMail('text/html' );
                    break;
            }
        }
        
        return '';
    }
    
    /**
     * Уведомления на почту для #0015818: Рассылка Заказчикам по конкурсам без бюджета 
     * 
     */
    function sendEmpContestWithoutBudget() {
        global $DB;
        $eHost = $GLOBALS['host'];
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/messages.php';
        $msg = new messages();
        $result = employer::GetPROEmployersCreatedProjectsWithoutPrice();
        $users = new users();
        $adminId = $users->GetUid($err, 'admin');
        $pHttp = str_replace("://", "", HTTP_PREFIX);
		$pHost = str_replace(HTTP_PREFIX, "", $eHost);        
        $this->subject = "Вы теряете отклики от фрилансеров в конкурсе на FL.ru";
        if(count($result) > 0) {
            foreach($result as $user) {
                if (!$user['email'] || substr($user['subscr'], 7, 1) == '0') continue;
                $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
                $message  = "<p>Вы опубликовали <a href=\"{$eHost}/projects/{$user['prj_id']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">конкурс</a> на сайте FL.ru, но не указали бюджет для вашего конкурса. Вы рискуете потерять  довольно много предложений от фрилансеров. Чтобы  получить больше откликов, а с ними – свежие идеи, креатив и результат, рекомендуем вам всегда заполнять поле «Бюджет» при публикации конкурса. Подробная инструкция находится в <a href=\"https://feedback.fl.ru/article/details/id/144?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">соответствующем разделе</a> помощи.</p>
<p>Если вы уже приняли решение о начале работы с конкретным фрилансером, рекомендуем заключить «<a href=\"{$eHost}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">«Безопасную Сделку»</a>», чтобы быть на 100% уверенными в успешном сотрудничестве.</p>
<p>По всем возникающим вопросам вы можете обращаться в нашу <a href=\"https://feedback.fl.ru/\" target=\"_blank\">службу поддержки</a>.</p>";
        
        $contacts_message  = "Здравствуйте!
Вы опубликовали {$pHttp}:/{конкурс}/{$pHost}/projects/{$user['prj_id']}?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget на сайте FL.ru, но не указали бюджет для вашего конкурса. Вы рискуете потерять  довольно много предложений от фрилансеров. Чтобы  получить больше откликов, а с ними – свежие идеи, креатив и результат, рекомендуем вам всегда заполнять поле «Бюджет» при публикации конкурса. Подробная инструкция находится в {$pHttp}:/{соответствующем разделе}/feedback.FL.ru/article/details/id/144?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget помощи.

Если вы уже приняли решение о начале работы с конкретным фрилансером, рекомендуем заключить {$pHttp}:/{&laquo;Безопасную Сделку&raquo;}/{$pHost}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget, чтобы быть на 100% уверенными в успешном сотрудничестве.

По всем возникающим вопросам вы можете обращаться в нашу {$pHttp}:/{службу поддержки}/feedback.FL.ru/.";
                
                $this->message = $this->GetHtml($user['uname'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
                $msg->Add($adminId, $user['login'], $contacts_message, false, 0, true);    
                $this->SmtpMail('text/html' );
            }
        }
        
        $result = employer::GetNoPROEmployersCreatedProjectsWithoutPrice();
        if(count($result) > 0) {
            foreach($result as $user) {
                if (!$user['email'] || substr($user['subscr'], 7, 1) == '0') continue;
                $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
                $message  = "<p>Вы опубликовали <a href=\"{$eHost}/projects/{$user['prj_id']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">конкурс</a> на сайте FL.ru, но не указали бюджет для вашего конкурса. Вы рискуете потерять  довольно много предложений от фрилансеров. Чтобы  получить больше откликов, а с ними – свежие идеи, креатив и результат, рекомендуем вам всегда заполнять поле «Бюджет» при публикации конкурса. Подробная инструкция находится в <a href=\"https://feedback.fl.ru/article/details/id/144?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">соответствующем разделе</a> помощи.</p>
<p>Напоминаем, что без аккаунта PRO вы не можете просматривать контакты всех фрилансеров. Для того чтобы связываться напрямую с понравившимся исполнителем, </p>
<p>рекомендуем приобрести <a href=\"{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">аккаунт PRO</a>, который позволяет видеть контактную информацию всех пользователей, пользоваться скидками на все платные услуги сайта и дает другие приятные бонусы. </p>
<p>Если вы уже приняли решение о начале работы с конкретным фрилансером, рекомендуем заключить «<a href=\"{$eHost}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">Безопасную Сделку</a>», чтобы быть на 100% уверенными в успешном сотрудничестве.</p>

<p>По всем возникающим вопросам вы можете обращаться в нашу <a href=\"https://feedback.fl.ru/\" target=\"_blank\">службу поддержки</a>.</p>";
                $contacts_message  = "Здравствуйте!
Вы опубликовали {$pHttp}:/{конкурс}/{$pHost}/projects/{$user['prj_id']}?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget на сайте FL.ru, но не указали бюджет для вашего конкурса. Вы рискуете потерять  довольно много предложений от фрилансеров. Чтобы  получить больше откликов, а с ними – свежие идеи, креатив и результат, рекомендуем вам всегда заполнять поле «Бюджет» при публикации конкурса. Подробная инструкция находится в {$pHttp}:/{соответствующем разделе}/feedback.FL.ru/article/details/id/144?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget помощи.

Напоминаем, что без аккаунта PRO вы не можете просматривать контакты всех фрилансеров. Для того чтобы связываться напрямую с понравившимся исполнителем,рекомендуем приобрести {$pHttp}:/{аккаунт PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget, который позволяет видеть контактную информацию всех пользователей, пользоваться скидками на все платные услуги сайта и дает другие приятные бонусы.

Если вы уже приняли решение о начале работы с конкретным фрилансером, рекомендуем заключить {$pHttp}:/{&laquo;Безопасную Сделку&raquo;}/{$pHost}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget, чтобы быть на 100% уверенными в успешном сотрудничестве.

По всем возникающим вопросам вы можете обращаться в нашу {$pHttp}:/{службу поддержки}/feedback.FL.ru/.";
                
                $this->message = $this->GetHtml($user['uname'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
                $msg->Add($adminId, $user['login'], $contacts_message, false, 0, true);    
                $this->SmtpMail('text/html' );
            }
        }
        
        return false;
    }

    
    /**
     * Уведомления о редактировании мнения модератором
     * 
     * @return integer  id письма (0 - не отправлено)
     */
    function sbrFeedbackEdit($fbId, $userId, $moderId, $sbr) {
        $moder = new users;
        $user  = new users;
        $moder->GetUserByUID($moderId);
        $user->GetUserByUID($userId);
        if ( substr($user->subscr, 14, 1) == '0' ) {
            return 0;
        }
        $uniqId = $fbId * 2 + 1;
        if($sbr->frl_id == $moderId) {
            $role_name = "Исполнитель";
            $role_opinion = "вам ";
        } elseif($sbr->emp_id == $moderId) {
            $role_name = "Заказчик";
            $role_opinion = "вам ";
        } else {
            $role_name = "Модератор";
            $role_opinion = "";
        }
        
        $this->subject   = "{$role_name} отредактировал {$role_opinion}рекомендацию на FL.ru";
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <{$user->email}>";
        $message = 
"{$role_name} отредактировал рекомендацию по «Безопасной Сделке».<br />
<br />
Вы можете прочитать ее на <a href='{$GLOBALS['host']}/users/{$user->login}/opinions/#p_{$uniqId}'>странице вашего аккаунта</a>.<br />
";
        $this->message = $this->GetHtml($user->uname, $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user->login));
        return $this->send('text/html');
    }

    /**
     * Уведомление о новом комментарии в теме сообщества
     * @param $themeName         - Наименование темы в сообществе
     * @param $communeName       - Наименование сообщества
     * @param $userLink          - ссылка на профиль пользователя оставившего комментарий
     * @param $authorName        - имя автора комментария
     * @param $authorLogin       - логин автора комментария
     * @param $authorSurname     - фамилия автора комментария
     * @param $msgtext           - текст сообщения,
     * @param $domain            - домен
     * @param $url               - ссылка на комментарий,
     * @param $recipientName     - имя получателя,
     * @param $recipientSurname  - фамилия получателя,
     * @param $recipientLogin    - логин получателя,
     * @param $email             - адрес получателя  
     * @param $topicUrl          - ссылка на тему сообщества   
     * @param $communeUrl        - ссылка на сообщество
     * */
    public function commentInThemeOfCommune($themeName, $communeName, $userLink, $authorName, $authorLogin, $authorSurname, $msgtext, $domain, $url, $recipientName, $recipientSurname, $recipientLogin, $email, $topicUrl, $communeUrl) {
        $body = " 
  	    В сообщении \"<a href=\"$topicUrl\">{$themeName}</a>\" сообщества \"<a href=\"$communeUrl\">{$communeName}</a>\", на которое вы подписались <a href=\"$userLink\">{$authorName}</a> <a href=\"$userLink\">{$authorSurname}</a> [<a href=\"$userLink\">{$authorLogin}</a>] оставил(а) <a href=\"$url\">комментарий</a> к сообщению/комментарию.
  	                     <br/>
  	                     --------<br/>".reformat(LenghtFormatEx(strip_tags($msgtext, "<br><p>"), 300))."
  	                     --------
  	                     ";
  	    $mail = new smtp;
        $mail->subject   = 'Новый комментарий в сообщении «'.$themeName.'» сообщества «'.$communeName.'»';
        $mail->message   = $mail->GetHtml($recipientName, $body, array('header' => 'subscribe', 'footer' => 'subscribe'), array('login'=>$recipientLogin));
        $mail->recipient = $recipientName." ".$recipientSurname." [".$recipientLogin."] <".$email.">";
        $mail->send('text/html');
    }
    
    /**
     * Заказчику о резерве (должно отсылатся в момент когда Заказчик нажимает на Зарезервировать деньги) -- только для аккредитива
     * 
     * @param type $sbr_id      ИД сделки
     * @param type $user_id     ИД пользователя
     * @return type 
     */
    public function SbrReservedMoney($sbr_id, $user_id) {
        session_start();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        
        $sbr = sbr_meta::getInstanceLocal($user_id);
        $sbr->initFromId($sbr_id, false);
        $sbr_num = $sbr->getContractNum();
        $t_user = new users();
        $t_user->GetUserByUID($user_id);
        
        $url_sbr = "{$GLOBALS['host']}/" . sbr::NEW_TEMPLATE_SBR . "/";
        
        $this->subject = "Резервирование денежных средств по «Безопасной Сделке» № {$sbr_num}";
        $message = "Вам необходимо перечислить денежные средства по «Безопасной Сделке» <a href='{$url_sbr}?id={$sbr->id}'>№ {$sbr_num}</a> в течение " . ( pskb::PERIOD_RESERVED ) ." рабочих дней с момента нажатия на кнопку «Зарезервировать средства». В противном случае сделка будет отменена.";
        
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->message = $this->GetHtml($t_user->uname, $message, array('header'=>'default', 'footer'=>'norisk_robot'));

        return $this->send('text/html');
    }
    
    /**
     * Как выбрать исполнителя: опубликовать проект, или ПФ, или В офис, или рассылку.
     * Это рассылка для новых работодателей. Отправляется после регистрации.     
     * @param type $user_id     ИД пользователя
     * @return type 
     */
    public function employerQuickStartGuide($user_id) {
        session_start();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $host = $GLOBALS['host'];
        $t_user = new users();
        $t_user->GetUserByUID($user_id);
        $login = $t_user->login;
        $this->subject = "FL.ru: как начать работу на сайте?";
        $message =
"<p>Мы рады приветствовать вас на крупнейшей бирже удаленной работы FL.ru. Сотрудничать с нами легко и безопасно &ndash; мы защищаем своих пользователей при помощи сервиса &laquo;<a href='{$host}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>Безопасная Сделка</a>&raquo;: вам не надо беспокоиться о том, что фрилансер не выполнит ваш заказ или пропадет с предоплатой. Будьте уверены: он выполнит работу в срок и по ТЗ.</p>

<p>Прежде всего, вам нужно найти исполнителя и обговорить с ним детали проекта. Вы можете общаться с фрилансерами с помощью сообщений на сайте или связаться напрямую. Если у вас будет начальный аккаунт, вы сможете видеть контактную информацию (телефон, e-mail, Skype и т.д.) только тех фрилансеров, которые открыли их. Мы рекомендуем вам приобрести <a href='{$host}/payed/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>аккаунт PRO</a>, который позволяет видеть прямые контакты всех пользователей и дает множество других преимуществ.</p>

<p>Найти исполнителей на нашем сайте можно разными способами:</p>

<ul>
<li><a href='https://feedback.fl.ru/article/details/id/121?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>Публикация проекта</a>: вы создаете проект с описанием условий работы и получаете отклики от фрилансеров, которые хотели бы взяться за выполнение вашего заказа.<br />&nbsp;<br /></li>
<li>Поиск исполнителя <a href='{$host}/freelancers/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>в каталоге</a>: выберите подходящего кандидата, просматривая портфолио фрилансеров.<br />&nbsp;<br /></li>
<li>Вкладка &laquo;<a href='{$host}/projects/?kind=4&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>Вакансии</a>&raquo;: главное отличие такого проекта от обычного – вы будете получать отклики от фрилансеров, которые заинтересованы в долгосрочном сотрудничестве.<br />&nbsp;<br /></li>
<li><a href='https://feedback.fl.ru/article/details/id/139?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>Рассылка по каталогу фрилансеров</a>: вы можете обратиться сразу к большому количеству потенциальных исполнителей через рассылку.</li>
</ul>

<br />&nbsp;<br />

<p>
<a href='{$host}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>Перейти на сайт и найти исполнителя</a>
</p>
";
        
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->message = $this->GetHtml($t_user->uname, $message, array('header'=>'default', 'footer'=>'feedback_default'), array("target_footer"=>true, "login"=>$login));

        return $this->send('text/html');
    }
    
    /**
     * Как получить работу: откликаться на проекты или вас найдут напрямую через каталог. Работайте через сайт, а не уходите, рисукуете тем-то тем-то.
     * Это рассылка для новых фрилансеров. Письмо уходит после регистрации.     
     * @param type $user_id     ИД пользователя
     * @return type 
     */
    public function freelancerQuickStartGuide($user_id) {
        session_start();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $host = $GLOBALS['host'];
        $t_user = new users();
        $t_user->GetUserByUID($user_id);
        $login = $t_user->login;
        $this->subject = "FL.ru: как найти работу на сайте?";
        $message = 
"<p>Мы рады приветствовать вас на крупнейшей бирже удаленной работы FL.ru. Работать с нами легко и безопасно &ndash; мы защищаем своих пользователей при помощи сервиса &laquo;<a href='{$host}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>Безопасная Сделка</a>&raquo;. Вам не надо беспокоиться о том, заплатит ли заказчик, не изменит ли условия проекта в последний момент или о чем-то другом. Мы полностью сопровождаем вашу работу с заказчиком.</p>
    
<p>Есть несколько способов начать работу на нашем сайте, о которых мы хотели бы вам рассказать.</p>

<ul>
<li>Загрузите в <a href='https://feedback.fl.ru/article/details/id/204?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>портфолио</a> примеры ваших лучших работ и укажите свою специализацию &ndash; так заказчикам будет легче найти вас среди других фрилансеров.</li>
<li>Откройте свои контакты (телефон, e-mail, Skype и т.д.): чтобы  заказчики могли напрямую связываться с вами, рекомендуем приобрести <a href='{$host}/payed/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>аккаунт PRO</a>.</li>
<li><a href='https://feedback.fl.ru/article/details/id/149?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>Отвечайте на проекты</a>: выбирайте наиболее интересные для вас предложения, просматривая ленту проектов на главной странице сайта, &ndash; у вас есть 3 бесплатных ответа в месяц. С аккаунтом PRO можно отвечать на неограниченное количество проектов.</li>
<li>Участвуйте в <a href='{$host}/konkurs/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>конкурсах</a>: суть конкурса заключается в том, что Заказчик описывает задание, а фрилансеры выполняют его. Победителю достается денежное вознаграждение.</li>
</ul>

<p>Рекомендуем работать через сервис &laquo;<a href='/". sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>Безопасная Сделка</a>&raquo; &ndash; это полная гарантия того, что вы всегда получите заработанные деньги: перед началом сотрудничества заказчик резервирует ваш гонорар на специальном счете, к которому не имеет доступа.</p>

<p>После того как работа будет выполнена, а заказчик примет ее, вы можете вывести деньги различными способами: на банковскую карту через кошельки в электронных платежных системах Веб-кошелек, Яндекс.Деньги, WebMoney, а также на расчетный счет в банке.</p>

<p><a href='{$host}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>Перейти на сайт и начать работать</a>!</p>";
        
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->message = $this->GetHtml($t_user->uname, $message, array('header'=>'default', 'footer'=>'feedback_default'), array("target_footer"=>true, "login"=>$login));

        return $this->send('text/html');
    }
    
    /**
     * Как работать с выбранным исполнителем: вы можете обсудить проект в сообщениях и договорившись о сотр-ве, начать СБР.
     * Это рассылка для новых работодателей. Отправляется в тот же день через несколько часов.
     * вызывается из hourly.php раз в сутки  
     */
    public function employerHelpInfo() {
        session_start();
        global $DB;
        $rows = employer::GetNewEmployer();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $host = $GLOBALS['host'];
        $this->subject = "FL.ru: как работать с выбранным исполнителем?";
        $message =
"<p>Мы рады приветствовать вас на крупнейшей бирже удаленной работы FL.ru. Начало работы на сайте &ndash; это подбор нужного вам специалиста. Найти исполнителя можно несколькими способами:</p>
    
<ul>
<li>публикация проекта или конкурса;</li>
<li>поиск в каталоге фрилансеров;</li>
<li>заказ подбора исполнителя у наших менеджеров.</li>
</ul>

<p>После того как вы определитесь с фрилансером, который будет выполнять ваш заказ, обсудите с ним детали сотрудничества.</p>

<p>Мы рекомендуем всегда заключать &laquo;<a href='{$host}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_2'>Безопасную Сделку</a>&raquo; &ndash; так вы сможете обмениваться любой информацией и будете уверены в том, что ваш заказ будет выполнен точно в срок и в соответствии с техническим заданием.</p>

<p>При сотрудничестве через &laquo;Безопасную Сделку&raquo; гонорар исполнителю выплачивается только после того, как вы примете результат работы. <a href='https://feedback.fl.ru/topic/397436-chto-takoe-bezopasnaya-sdelka/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_2'>Оплата</a> производится любым удобным для вас способом: банковской картой, с помощью электронных платежных систем или же по безналичному расчету.</p>

<p>Перейти на сайт и приступить к поиску исполнителя!</p>";
        
        $this->message = $this->GetHtml(
            false, 
            $message, 
            array('header' => 'default', 'footer' => 'feedback_default'), 
            //array('login' => '%USER_LOGIN%')
            array('target_footer' => true)
        );
        if ( count($rows) < 20) {
            foreach ($rows as $user) {
                $this->message = $this->GetHtml(
                    false, 
                    $message, 
                    array('header' => 'default', 'footer' => 'feedback_default'), 
                    array('login' => $user['login'])
                );
                $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                $this->send("text/html");
            }   
            return;
        }
        $this->recipient = '';
        $massId = $this->send('text/html');
        foreach ($rows as $user) {
            if (!$user['unsubscribe_key']) {
                $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
            }
	        $this->recipient[] = array(
	                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
	                    'extra' => array(
	                        'USER_NAME'    => $user['uname'],
	                        'USER_SURNAME' => $user['usurname'],
	                        'USER_LOGIN'   => $user['login'],
	                        'MESSAGE'      => $message,
	                        'UNSUBSCRIBE_KEY' => $user['unsubscribe_key']
	                    )
	                );
        }
        $this->bind($massId);        
    }
    
    /**
     * Уведомление Заказчиком об автоматическом поднятии проекта если в течении 2-х дней не было ни одного ответа на данный проект(ы)
     * 
     * @param array $projects    Список проектов
     */
    public function sendAutoSetTopProject($projects) {
        session_start();
        
        $host = $GLOBALS['host'];
        $this->subject = "FL.ru: Мы подняли ваш проект в общей ленте опубликованных проектов";
        $is_binding = ( count($projects) > 20 );
        
        $pHost = str_replace("http://", "", $GLOBALS['host']);
        if ( defined('HTTP_PREFIX') ) {
            $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
        } else {
            $pHttp = 'http';
        }
        $PLDB = new DB('plproxy');
        $adm  = new users();
        $adm_id = $adm->GetUid($e, "admin");
        
        if($is_binding) {
            $this->recipient = '';
            $massId = $this->send('text/html');
        }
        // Группируем по пользователям
        foreach($projects as $prj) {
            $users_sended[$prj['user_id']][] = $prj;
        }
        
        foreach($users_sended as $uid => $project) {
            $user = current($users_sended[$uid]);
            if(substr($user['subscr'], 8, 1) != '1') continue;
//            $uname    = ( $user['uname'] != '' && $user['usurname'] != '' ) ? "{$user['uname']} {$user['usurname']}" : $user['login'] ;
//            $_message = "Здравствуйте, {$uname}!<br/><br/>";
            foreach($project as $value) {
                $value['name'] = htmlspecialchars($value['name'], ENT_QUOTES, 'CP1251', false);
                $message  = "<p>На ваш проект «<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $value['id']).$this->_addUrlParams('e')."\" target=\"_blank\">".$value['name']."</a>» не поступило откликов от фрилансеров, поэтому мы подняли его в общем списке – теперь он расположен наверху ленты проектов как вновь опубликованный. Надеемся, это поможет вам найти подходящих исполнителей.</p><br/><br/>";
                $message .= "<p>Для привлечения внимания к проекту воспользуйтесь дополнительными опциями – <a href=\"http://feedback.fl.ru/topic/397530-zakreplenie-proekta-naverhu-lentyi-proektov-opisanie-stoimost-instruktsiya/\" target=\"_blank\">закрепление проекта наверху ленты</a> и загрузка логотипа и ссылки на ваш сайт (подробная инструкция – в разделе статьи <a href=\"http://feedback.fl.ru/topic/397524-platnyie-proektyi-opisanie/\" target=\"_blank\">«Закрепление проекта и добавление логотипа и ссылки на сайт компании»</a>).</p>";
                // В личные сообщения
//                $_message .= "На ваш проект «{$pHttp}:/{{$value['name']}}/{$pHost}" . getFriendlyURL("project", $value['id'])."» не поступило откликов от фрилансеров, поэтому мы подняли его в общем списке – теперь он расположен наверху ленты проектов как вновь опубликованный. Надеемся, это поможет вам найти подходящих исполнителей.<br/><br/>";
//                $_message .= "Для привлечения внимания к проекту воспользуйтесь дополнительными опциями – http:/{закрепление проекта наверху ленты}/feedback.FL.ru/article/details/id/157 и загрузка логотипа и ссылки на ваш сайт (подробная инструкция – в разделе статьи http:/{«Закрепление проекта и добавление логотипа и ссылки на сайт компании»}/feedback.FL.ru/article/details/id/127<span>)</span>.<br/><br/>";
            
                $this->message = $this->GetHtml(
                    false, 
                    $message, 
                    array('header' => 'default', 'footer' => 'feedback_default'), 
                    $is_binding ? array('target_footer' => true) : array('login' => $user['login'])
                );


                if(!$is_binding) {
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    $this->send("text/html");
                } else {
                    if (!$user['unsubscribe_key']) {
                        $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                    }
                    $this->recipient[] = array(
                        'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                        'extra' => array(
                            'USER_NAME'    => $user['uname'],
                            'USER_SURNAME' => $user['usurname'],
                            'USER_LOGIN'   => $user['login'],
                            'MESSAGE'      => $this->message,
                            'UNSUBSCRIBE_KEY' => $user['unsubscribe_key']
                        )
                    );
                }
            }
            // В личку не посылаем
            //$_message .= "Приятной работы с FL.ru!";
            //$PLDB->val("SELECT messages_add(?i, ?i, ?, ?b, ?a, ?b)", $adm_id, $user['uid'], $_message, true, array(), true);
        }
        
        if($is_binding) {
            $this->bind($massId); 
        }
    }
    
    /**
    * Уведомление об удалении комментария или поста в блогах
    * @param int $moderator_uid - идентификатор автора блога
    * @param array $userSubscribe - массив идентификаторов сообщений подписаных польователей
    * */
    public function sendBlogPostDeleted($moderator_uid, $userSubscribe) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
     // Посылаем подписавшимся на темы  
        if($userSubscribe) {
            $moderator = new users();
            $moderator->GetUserByUID($moderator_uid);
            $a_login = $moderator->login;
            $a_uname = $moderator->uname;
            $a_usurname = $moderator->usurname;
        	foreach($userSubscribe as $comment) {
	            if( substr($comment['s_subscr'], 2, 1) == '1' 
	                && !$notSend[$comment['s_uid']]
	                && $comment['s_email'])
	            {
	                $this->subject = "В блоге на сайте FL.ru удален комментарий";
	                $post_type = "комментарий в <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>в блоге</a>, на который вы подписаны";
	                if ( $comment['s_uid'] == $comment['uid']  ) {
                        $this->subject = "Ваш комментарий в блоге на сайте FL.ru удален";
                        $post_type = "ваш комментарий в <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'> блоге</a>";
                    }
	                $message_template = "subscribe_delete_comment";
	                if ( $comment['reply_to'] == '' ) {
	                    $this->subject = "На сайте FL.ru удален блог";
	                    $post_type = "блог, на который вы подписаны";
	                    if ( $comment['s_uid'] == $comment['uid']  ) {
                            $this->subject = "Ваш пост в блогах на сайте FL.ru удален";
                            $post_type = "ваш пост в блогах";
                        }
	                    $message_template = "subscribe_delete_post";
	                }
	                $link_title = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}{$this->_addUrlParams('b', '&')}' target='_blank'>" . ( $comment['blog_title'] == ''? 'Без названия' : $comment['blog_title'] )  ."</a>";  
	                $this->message = $this->GetHtml($comment['s_uname'], "
	Пользователь <a href='{$GLOBALS['host']}/users/{$a_login}/{$this->_addUrlParams('b')}'>{$a_uname} {$a_usurname}</a> [<a href='{$GLOBALS['host']}/users/{$a_login}{$this->_addUrlParams('b')}'>{$a_login}</a>]
	удалил(-а) {$post_type} на сайте FL.ru.
	<br /> --------
	<br />"
	.($comment['title']? ($this->ToHtml(input_ref(LenghtFormatEx($comment['title'], 300), 1))."<br />---<br />"): "")
	.$this->ToHtml(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
	<br /> --------
	<br />
	", array('header' => $message_template, 'footer' => 'subscribe'), array('type' => 1, 'title' => $link_title));
	                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
	                $this->SmtpMail('text/html');
	                $message = "Здравствуйте, ".$comment['s_uname'].".                
	Пользователь <a href='{$GLOBALS['host']}/users/{$a_login}/{$this->_addUrlParams('b')}'>{$a_uname} {$a_usurname}</a> [<a href='{$GLOBALS['host']}/users/{$a_login}{$this->_addUrlParams('b')}'>{$a_login}</a>]
    удалил(-а) {$post_type} на сайте FL.ru.
	--------"
	.($comment['title']? ($this->ToHtml(input_ref(LenghtFormatEx($comment['title'], 300), 1))."
	---
	"): "")
	.$this->ToHtml(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
	 --------
	 ";
	                messages::Add( users::GetUid($err, 'admin'), $comment['s_login'], $message, '', 1 );
	            }
	        }
    }
    }

    /**
     * для тех у кого включено автопродление ПРО аккаунта
     * Отсылает сообщение порльзователю о том, что аккаунт PRO закончится через день
     *
     * @param integer $user_id ИД пользователя
     * @return null
     */
    public function sendAutoPROEnding($role = 'FRL', $users) {
        global $host;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";

        $UTM = "utm_source=newsletter4&utm_medium=email&utm_campaign=expiring_PRO";
        $this->subject   = "FL.ru: заканчивается действие вашего аккаунта PRO";

        $message =
            '<p>Здравствуйте, %USERNAME%!</p>
            <p>Завтра заканчивается срок действия оплаченной вами услуги «PRO аккаунт».<br/>
            Для продления срока действия услуги перейдите, пожалуйста, по <a href="' . $host . '/bill/">этой ссылке</a>, чтобы приобрести и оплатить аккаунт любым удобным вам способом.</p>
            <p>С подробной информацией по управлению услугами и личным счетом на FL.ru вы можете ознакомиться в нашем <a href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=9239https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=9239">сообществе поддержки</a>.</p>
            <p>По всем возникающим вопросам обращайтесь в нашу <a href="https://feedback.fl.ru/">службу поддержки</a>.<br/>
            <p>Приятной работы с FL.ru!</p>';

        foreach($users as $user) {
            if ($user['bill_subscribe'] === 'f') {
                continue;
            }
            $this->recipient = (string)"{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $this->message   = str_replace(array('%USERNAME%', '%LOGIN%'), array(($user['uname'] ? $user['uname'] : $user['login']), $user['login']) , $message);
            $this->send('text/html');
        }
    }


    /**
     * Оплата услуг
     * 
     * @global type $host
     * @global type $DB
     * @param type $reserves
     * @param type $is_reserved
     */
    public function sendReservedOrders($reserves, $is_reserved) {
        global $host, $DB;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/billing.php";
        
        $reserved_ids  = array_keys($reserves);
        $not_reserved  = array_diff($reserved_ids, $is_reserved);
        
        if(!empty($is_reserved)) {
            $bill = new billing($reserves[$is_reserved[0]]['uid']);
            $this->recipient = (string)"{$bill->user['uname']} {$bill->user['usurname']} [{$bill->user['login']}] <{$bill->user['email']}>";
            $more  = (count($is_reserved) > 1);
            
            $this->subject   = "FL.ru: исполнение " . ( $more ? "списков" : "списка" ) . " заказов";
            
            $payed_sum = 0;
            foreach($is_reserved as $id) {
                $payed_sum += $reserves[$id]['ammount'];
            }
            
            $where = $DB->parse(" AND status = ? AND reserve_id IN (?l)", billing::STATUS_COMPLETE, $is_reserved);
            $orders = $bill->findOrders($where);
            
            $message = "По ранее " . ($more ? "сформированным спискам заказов" : "сформированному списку заказа")  . " №" . implode(", №", $is_reserved).  "  списана сумма ".to_money($payed_sum, 2)." руб. и активированы следующие услуги:<br/><br/>";
            $message .= "---<br/>";
            
            foreach($orders as $order) {
                $message .= $order['comment']."<br>";
            }
            
            $message .= "---<br/><br/>";
            //$message .= "Доступная сумма после оплаты услуг – " . $bill->acc['sum'] . " руб. ";
            
            if(!empty($not_reserved)) {
                $notpayed_sum = 0;
                foreach($not_reserved as $id) {
                    $notpayed_sum += $reserves[$id]['ammount'];
                }

                if(count($not_reserved) >= 1) {
                    $message .= ( count($not_reserved) > 1 ? "Списки заказов №" : " Список заказов №" ). implode(", №", $not_reserved) . " на сумму " . to_money($notpayed_sum, 2) . " руб. по-прежнему ожидают оплаты.<br/><br/>";
                }
            }
            
            $message .= "С подробной информацией по управлению услугами и личным счетом на FL.ru вы можете ознакомиться в нашем <a href='https://feedback.fl.ru/' target='_blank'>сообществе поддержки</a>.<br/>";
            
            $this->message = $this->getHtml($bill->user['login'], $message, array('header'=>'default', 'footer'=>'feedback_default'), array('login' => $bill->user['login']));
            
        } else {
            $bill = new billing($reserves[$not_reserved[0]]['uid']);
            $this->recipient = (string)"{$bill->user['uname']} {$bill->user['usurname']} [{$bill->user['login']}] <{$bill->user['email']}>";
            $more = (count($not_reserved) > 1);
            
            $this->subject   = "FL.ru: Недостаточно средств для исполнения " . ( $more ? "списков" : "списка" ) . " заказов";
            
            $message  = "Ранее вами " . ($more ? "были сформированы списки" : "был сформирован список") . " заказов №".implode(", №", $not_reserved).", однако сумма оплаты в " . ($more ? "них" : "нем")  . " больше той, которая доступна на вашем личном счете.<br/><br/>";
            
            $message .= "Изменить " . ($more ? "списки" : "список"). " заказов, отменить их или завершить оплату вы можете по этой <a href='{$host}/bill/history/'>ссылке</a>.";
            $this->message = $this->getHtml($bill->user['login'], $message, array('header'=>'default', 'footer'=>'feedback_default'), array('login' => $bill->user['login']));
        }
        
        $this->send('text/html');
    }
    
    /**
     *
     *
     */
    public function sendCancelReserve ($reserves, $resDays) {
        global $host;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";

        $this->subject   = "FL.ru: отменен список заказов";

        $message =
            '<p>Ранее вами был сформирован счет на покупку услуг №%RESERVE_ID%. Так как в течение ' . $resDays . ending($resDays, ' дня', 'дней', ' дней') . ' он не был оплачен, счет автоматически отменен.<br/>
            Вы можете повторно купить и оплатить услуги по этой <a href="' . $host . '/bill/">ссылке</a>.<br/>
            С подробной информацией по управлению услугами и личным счетом на FL.ru вы можете ознакомиться в нашем <a href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=9239">сообществе поддержки</a>.</p>';

        foreach($reserves as $reserve) {
            $this->recipient = (string)"{$reserve['uname']} {$reserve['usurname']} [{$reserve['login']}] <{$reserve['email']}>";
            $message_ = $this->getHtml('', $message, array('header'=>'default_new', 'footer'=>'feedback_default'), array('login' => $reserve['login']));
            $this->message = str_replace(array('%USER_NAME%', '%RESERVE_ID%'), array(($reserve['uname'] ? $reserve['uname'] : $reserve['login']), $reserve['reserve_id']) , $message_);
            //print ($this->message); exit;
            $this->send('text/html');
        }
    }

    /**
     * Отслеживает услугу ПРО и посылает уведомление о том что она скоро закончится
     */
    public function remindTimeleftPRO($users, $days=3) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";

        if($days == 1) {
            $this->subject = "FL.ru: Завтра истекает срок действия аккаунта PRO";
        } else {
            $this->subject = "FL.ru: Истекает срок действия аккаунта PRO";
        }

        $time     = strtotime("+{$days} days");
        $date     = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y года', $time);

        if($days > 1) {
            $message  = "Напоминаем, что через {$days} " . ending($days, "день", "дня", "дней") . ", {$date}, заканчивается срок действия вашего аккаунта PRO.<br/>";
        } else {
            $message  = "Напоминаем, что завтра, {$date},  заканчивается срок действия вашего аккаунта PRO.<br/>";
        }
        $message .= "Рекомендуем вам повторно приобрести услугу.<br/><br/>";
        $message .= "Информацию о способах оплаты, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>сообществе поддержки</a>.<br/><br/>";

        foreach($users as $user) {
            if($user['bill_subscribe'] == 'f') continue;

            $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $this->message   = str_replace("%UNSUBSCRIBE_KEY%", users::GetUnsubscribeKey($user['login']), $message);
            $this->message   = $this->getHtml($user['login'], $this->message, array('header'=>'default', 'footer'=>'simple'), array('login' => $user['login']));
            $this->send('text/html');
        }
    }

    /**
     * Предупреждаем за 3 дня до окончания услуги
     *
     * @param $users            Список пользователей
     * @param string $role
     */
    public function remindAutoprolongPRO($users, $role = 'freelancer', $days=3) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/payed.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/wallet.php";

        $this->subject = "FL.ru: Истекает срок действия аккаунта PRO";
        $cost     = $role == 'freelancer' ? payed::PRICE_FRL_PRO : payed::PRICE_EMP_PRO;
        $time     = strtotime("+{$days} days");
        $date     = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y года', $time);

        foreach($users as $user) {
            if($user['bill_subscribe'] == 'f') continue;

            $time        = strtotime("+". ($days-1). " days");
            $next        = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y года', $time);
            $wallet      = WalletTypes::initWalletByType($user['uid']);
            $type        = WalletTypes::checkWallet($wallet) ? $wallet->data['type'] : -1;
            $walletName  = WalletTypes::getNameWallet($type, 3, $user['acc_id']);
            $unsunscribe = users::GetUnsubscribeKey($user['login']);

            $message   = "Напоминаем, что через {$days} " . ending($days, "день", "дня", "дней") . ", {$date}, заканчивается срок действия вашего аккаунта PRO.<br/><br/>";

            if($type == -1) {
                $time     = strtotime("+". ($days-1). " days");
                $date     = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y года', $time);
                $message .= "Так как ранее вы включили автопродление услуги (без указания способа оплаты), то за сутки до окончания срока ее действия, {$date}, с {$walletName} будет списана соответствующая сумма ({$val['sum_cost']} " . ending($val['sum_cost'], 'рубль', 'рубля', 'рублей') . ").<br/><br/>";
                $message .= "Обращаем внимание, что для своевременного списания средств и продления услуг в дальнейшем необходимо выбрать и активировать один из доступных вам способов оплаты.<br/><br/>";
            } else {
                $message .= "Так как ранее вы включили автопродление услуги, то за сутки до окончания срока ее действия, {$next}, с {$walletName} будет списана соответствующая сумма ({$cost} " . ending($cost, 'рубль', 'рубля', 'рублей') . "). После списания средств действие аккаунта PRO будет продлено.<br/><br/>";
                $message .= "Рекомендуем вам проверить наличие достаточной суммы, которая будет списана с {$walletName}.<br/><br/>";
            }

            $message .= "Информацию об автопродлении, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>сообществе поддержки</a>.<br/><br/>";


            $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $this->message   = $this->getHtml($user['login'], $message, array('header'=>'default', 'footer'=>'simple'), array('login' => $user['login']));
            $this->send('text/html');
        }
    }

    /**
     * Услуга продлена успешно
     *
     * @param string $service
     * @param $cost
     * @param $user
     */
    public function successAutoprolong($info, $service = 'pro') {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/wallet.php";

        $user   = $info['user'];
        if($user['bill_subscribe'] == 'f') return;
        $date   = date('j') . ' ' . monthtostr(date('n'), true) . ' ' . date('Y года');
        $cost   = $info['sum_cost'];

        $wallet = WalletTypes::initWalletByType($user['uid']);
        $type   = WalletTypes::checkWallet($wallet) ? $wallet->data['type'] : -1;
        $walletName  = WalletTypes::getNameWallet($type, 3, $user['acc_id']);

        if($service == 'pro') {
            $time     = strtotime("+1 month");
            $next     = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y года', $time);

            $link     = is_emp($user['role']) ? "{$GLOBALS['host']}/payed-emp/" : "{$GLOBALS['host']}/payed/";
            $message  = "Сегодня, {$date}, был автоматически продлен ваш аккаунт PRO. С {$walletName} было списано {$cost} " . ending($cost, 'рубль.', 'рубля.', 'рублей.')."<br/><br/>";
            $message .= "Следующее автопродление аккаунта PRO состоится через месяц, {$next}. Вы можете настроить или отключить <a href='{$link}{$this->_addUrlParams('b', '?')}'>функцию автопродления</a> аккаунта PRO на FL.ru.<br/><br/>";
        }
        $message .= "Информацию об автопродлении, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>сообществе поддержки</a>.";

        $this->subject   = "FL.ru: Срок действия " . ( $service == 'pro' || sizeof($info['prof'])==1 ? 'услуги' : 'услуг' ) . " продлен";
        $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
        $this->message   = $this->getHtml($user['login'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
        $this->send('text/html');
    }

    // не удалось продлить за день
    public function attemptAutoprolong($info, $service = 'pro') {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/wallet.php";

        $user   = $info['user'];
        if($user['bill_subscribe'] == 'f') return;
        $date   = date('j') . ' ' . monthtostr(date('n'), true) . ' ' . date('Y года');
        $cost   = $info['sum_cost'];
        $wallet = WalletTypes::initWalletByType($user['uid']);
        $type   = WalletTypes::checkWallet($wallet) ? $wallet->data['type'] : -1;
        $walletName  = WalletTypes::getNameWallet($type, 3, $user['acc_id']);

        if($service == 'pro') {
            $this->subject = "FL.ru: Ошибка при автопродлении аккаунта PRO";

            $message  = "Сегодня, {$date}, должно было состояться автоматическое продление срока действия вашего аккаунта PRO.<br/><br/>";
        }
        $message .= "Всего с {$walletName} должно быть списано {$cost} " . ending($cost, 'рубль', 'рубля', 'рублей') . ", однако в процессе списания произошла ошибка.<br/><br/>";
        $message .= "Повторное списание будет осуществлено завтра в это же время. Рекомендуем вам проверить наличие достаточной суммы, которая будет списана с {$walletName}.<br/><br/>";
        $message .= "Информацию об автопродлении, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>сообществе поддержки</a>.";

        $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
        $this->message   = $this->getHtml($user['login'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
        $this->send('text/html');
    }

    // Не удалось продлить за час до окончания (второй фейл)
    public function failAutoprolong($info, $service = 'pro') {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/wallet.php";

        $user   = $info['user'];
        if($user['bill_subscribe'] == 'f') return;
        $date   = date('j') . ' ' . monthtostr(date('n'), true) . ' ' . date('Y года');
        $cost   = $info['sum_cost'];
        $wallet = WalletTypes::initWalletByType($user['uid']);
        $type   = WalletTypes::checkWallet($wallet) ? $wallet->data['type'] : -1;
        $walletName  = WalletTypes::getNameWallet($type, 3, $user['acc_id']);

        if($service == 'pro') {
            $this->subject = "FL.ru: Автопродление аккаунта PRO отключено";

            $message  = "Сегодня, {$date}, должно было состояться повторное списание средств для автоматического продления срока действия вашего аккаунта PRO.<br/>";
            $message .= "Всего с {$walletName} должно быть списано {$cost} ".ending($cost, 'рубль', 'рубля', 'рублей').", однако в процессе списания вновь произошла ошибка.<br/><br/>";
            $message .= "Срок действия аккаунта PRO завершен, а его автопродление временно отключено и возобновится при следующем приобретении услуги.<br/><br/>";
            $message .= "Информацию о повторном приобретении услуг и автопродлении, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>сообществе поддержки</a>.";
        }

        $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
        $this->message   = $this->getHtml($user['login'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
        $this->send('text/html');
    }
    /**
    * @desc Сообщение пользователю о том, что на его email была совершена попытка повторной регистрации (#0024792) 
    * @param string $email
    **/
    public function reRegisterToYourMail($email) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        $user = new users();
        $user->GetUser($email, true, $email);
        if ($user->login) {
	        $this->subject = "Регистрация новой учетной записи на FL.ru";
	        $message = "На сайте FL.ru активирована услуга «Регистрация новой учетной записи» и был указан ваш адрес электронной почты.<br>";
	        $message .= "<p>Если это были не вы и подобные письма будут продолжать приходить вам, пожалуйста, обратитесь в Службу поддержки FL.ru по адресу http://feedback.fl.ru/<br>";
	        $message .= "<p> Если же услугу активировали именно вы, то обращаем внимание, что на указанный адрес вами уже зарегистрирован аккаунт с параметрами:";
	        $message .= "<p> логин {$user->login}";
	        $message .= "<p>  пароль ******";
	        $message .= "<p> Далее вы можете авторизоваться в аккаунте и продолжить работу на сайте.<br>";
	        $this->message   = $this->getHtml($user->uname, $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user->login));
	        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <{$user->email}>";
	        $this->send('text/html');
        }
    }

    /**
     * Уведомление Заказчикам о том, что надо зарезервировать деньги через сутки   и трое суток после создания сделки
     * @param int $hours - количество часов, от которых зависит текст письма 24 или 72 
     */
    public function sendSbrReserveNotice($hours = 24) {
        session_start();        
        $host = $GLOBALS['host'];
        $this->subject = "Вы не зарезервировали бюджет сделки";
        $message = "<p>Напоминаем вам, что необходимо зарезервировать деньги по Безопасной сделке БС-%SBR_ID%-Б/О, если вы планируете сотрудничество в ней. Чтобы сделка не закрылась автоматически, средства должны быть зачислены в течение 5 рабочих дней.<p>
        <p>Информацию о резервировании и проведении сделок, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/' target='_blank'>сообществе поддержки</a>.</p>
        ";
        if ($hours == 72) {
        	$this->subject = "Осталось 2 рабочих дня до автоматического завершения сделки";
        	$message = "<p>Напоминаем, что вы еще не зарезервировали бюджет Безопасной сделки БС-%SBR_ID%-Б/О. Если сумма не зачислится в течение 2 рабочих дней, сделка будет автоматически отменена.</p>
            <p>Пожалуйста, зарезервируйте деньги, если вам нужно выполнить проект, и вы планируете работать с выбранным исполнителем. <p>
            <p>Информацию о резервировании и проведении сделок, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/' target='_blank'>сообществе поддержки</a>.</p>
            ";
        }
        $time_limit = $hours + 24;
        $query = "SELECT emp_id, e.email, e.login, e.uname, e.usurname, usk.key AS ukey, e.uid, sbr.id AS sbr_id
                  FROM sbr
                  LEFT JOIN employer AS e ON e.uid = sbr.emp_id
                  LEFT JOIN users_subscribe_keys AS usk ON usk.uid = e.uid
                  WHERE reserved_time IS NULL 
                      AND NOW() - posted > '{$hours} hours'::interval
                      AND NOW() - posted < '{$time_limit} hours'::interval;";
        if  ($_GET["debug"] == 1 && $_GET["bs"] == 1) {
            $query = "SELECT emp_id, e.email, e.login, e.uname, e.usurname, usk.key AS ukey, e.uid, sbr.id AS sbr_id
                  FROM sbr
                  LEFT JOIN employer AS e ON e.uid = sbr.emp_id
                  LEFT JOIN users_subscribe_keys AS usk ON usk.uid = e.uid
                  WHERE e.email = 'lamzin.a.n@rambler.ru' LIMIT 1";
        }
        $DB = new DB("master");
        $users = $DB->rows($query);
        $this->message = $this->GetHtml(
                false, 
                $message, 
                array('header' => 'default_new', 'footer' => 'feedback_default'), 
                array('target_footer' => true)
         );
        $this->recipient = '';
        $massId = $this->send('text/html');
        require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/users.php";
        $i = 0;
        $cnt = 0;
        $this->recipient= array();
        foreach($users as $row) {
            if($row['email'] == '') continue;
		    if ( strlen($row['ukey']) == 0 ) {
		        $row['ukey'] = users::writeUnsubscribeKey($row["uid"], true);
		    }
		    $this->recipient[] = array(
		        'email' => $row['email'],
		        'extra' => array( 
		                         'USER_LOGIN' => $row['login'],
		                         'UNSUBSCRIBE_KEY' => $row['ukey'],
		                         'USER_NAME' => $row['uname'],
		                         'SBR_ID' => $row["sbr_id"])
		    );
		    if (++$i >= 30000) {
		        $this->bind($massId);
		        $this->recipient = array();
		        $i = 0;
		    }
		    $cnt++;
        }
        $this->bind($massId);
    }
    /**
     * Уведомление Заказчикам о том, что надо зарезервировать деньги через сутки   и трое суток после создания сделки
     */
    public function activateAccountNotice() {
        $DB = new DB("master");
        $host = $GLOBALS['host'];
        $this->subject = "Последний день для активации аккаунта";
        $message = "<p>Пожалуйста, активируйте ваш аккаунт %NAME_LOGIN% в течении суток.<p>
        <p>Для активации достаточно перейти по указанной ссылке или скопировать ее в адресную строку браузера:</p>
        <p><a href='%LINK%' target='_blank'>%LINK%</a></p>
        <p>При возникновении проблем с  активацией аккаунта рекомендуем вам <a href='https://feedback.fl.ru/' target='_blank'>ознакомиться с инструкцией</a> или <a href='https://feedback.fl.ru/' target='_blank'>написать нам</a>. Мы обязательно вам поможем.</p>
        <p>Информацию о резервировании и проведении сделок, а также ответы на все интересующие вопросы вы можете найти в нашем <a href='https://feedback.fl.ru/' target='_blank'>сообществе поддержки</a>.</p>
        ";
        $hours = 48;
        $time_limit = $hours + 24;
        $query = "SELECT u.email, u.login, u.uname, u.usurname, usk.key AS ukey, u.uid, ac.code
                  FROM users AS u
                  LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
                  LEFT JOIN activate_code AS ac ON ac.user_id = u.uid
                  WHERE active = false  
                      AND NOW() - last_time > '{$hours} hours'::interval
                      AND NOW() - last_time < '{$time_limit} hours'::interval;";
        if  ($_GET["debug"] == 1 && $_GET["activate"] == 1) {
            $query = $DB->parse("SELECT u.email, u.login, u.uname, u.usurname, usk.key AS ukey, u.uid, ac.code
                  FROM users AS u
                  LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
                  LEFT JOIN activate_code AS ac ON ac.user_id = u.uid
                  WHERE u.login = ? LIMIT 1", $_GET['login']);
        }
        
        $users = $DB->rows($query);
        $this->message = $this->GetHtml(
                false, 
                $message, 
                array('header' => 'noname', 'footer' => 'feedback_default'), 
                array('target_footer' => true)
         );
        $this->recipient = '';
        $massId = $this->send('text/html');
        require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/users.php";
        $i = 0;
        $cnt = 0;
        $this->recipient= array();
        foreach($users as $row) {
            if($row['email'] == '') continue;
            if ( strlen($row['ukey']) == 0 ) {
                $row['ukey'] = users::writeUnsubscribeKey($row["uid"], true);
            }
            $link = $host . "/registration/activate.php?code={$row['code']}";
            $name = trim($row["uname"] . " " . $row["usurname"]);
            $name_login = ($name ? $name . ", " : '') . $row["login"]; 
            $this->recipient[] = array(
                'email' => $row['email'],
                'extra' => array( 
                    'USER_LOGIN' => $row['login'],
                    'UNSUBSCRIBE_KEY' => $row['ukey'],
                    'NAME_LOGIN' => $name_login,
                    'LINK' => $link
                )
            );
            if (++$i >= 30000) {
                $this->bind($massId);
                $this->recipient = array();
                $i = 0;
            }
            $cnt++;
        }
        $this->bind($massId);
    }
    
    
    
    
    
//------------------------------------------------------------------------------
    
    
    /**
     * ##0026613
     * Рассылка по заказчикам с уведовлением о возможности оставить отзыв
     * по проектам 2014 года
     * https://beta.free-lance.ru/mantis/view.php?id=26613
     */
    public function sendEmpPrjFeedback()
    {
        $type = 2;
        $date_interval = $this->__get_next_spam_date($type);
        if(!$date_interval) return 'Достигнута минимальная дата рассылки';
        
        $datefrom = $date_interval['from_date']; 
        $dateto = $date_interval['to_date'];
        $host = $GLOBALS['host'];
        
        $this->subject = "Получите отзывы по проектам {$date_interval['year']} года!";
        
        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/spam/emp_projects_feedback.tpl.php', 
                array(
                    'host' => $host,
                    'login' => '%USER_LOGIN%'
                )
        );        
        $this->recipient = '';
        $massId = $this->send('text/html');        
        
        $page  = 0;
        $count = 0;
        
        while ( $users = projects::getEmpPrjFeedback($datefrom, $dateto, ++$page, 200) ) 
        {
            $ids = array();
            foreach ( $users as $user ) 
            {

               $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login']
                    )
                );

               $ids[] = array('user_id' => $user['uid'], 'type' => $type);
               $count++;
            }
            
            $this->__save_sended_ids($ids);
            $page = 0;
        
            $this->bind($massId, true);
        }
        
        $this->__save_spam_date(array(
            'from_date' => $date_interval['from_date'],
            'to_date' => $date_interval['to_date'],
            'type' => $type,
            'sended' => $count
        ));  
        
        return $count;        
    }



//------------------------------------------------------------------------------
    
    
    /**
     * ##0026617
     * Рассылка по исполнителям выбранных в проектах 2014 года
     * https://beta.free-lance.ru/mantis/view.php?id=26617
     * 
     */
    public function sendFrlProjectsExec()
    {
        $type = 1;
        $date_interval = $this->__get_next_spam_date($type);
        if(!$date_interval) return 'Достигнута минимальная дата рассылки';
        
        
        $datefrom = $date_interval['from_date']; 
        $dateto = $date_interval['to_date'];
        $host = $GLOBALS['host'];
        
        $this->subject = "Получите отзывы по проектам {$date_interval['year']} года!";

        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/spam/frl_projects_exec.tpl.php', 
                array(
                    'project_links' => '%PROJECT_LINKS%',
                    'host' => $host
                )
        );        
        $this->recipient = '';
        $massId = $this->send('text/html');        
        
        $page  = 0;
        $count = 0;
        
        while ( $users = projects::getFrlExec($datefrom, $dateto, ++$page, 200) ) 
        {
            $ids = array();
            foreach ( $users as $user ) 
            {
               //сразу в игнор неважно что юзер может не попасть в рассылку по проверкам ниже
               $ids[] = array('user_id' => $user['uid'], 'type' => $type);
               
               $projects_list = DB::array_to_php($user['projects_list']); 
               if(empty($projects_list)) continue;
               //Ограничиваемся мах 10ю ссылками
               $projects_list = array_slice($projects_list, 0, 10);
               
               $links = '';
               foreach($projects_list as $el)
               {
                   $parts = explode('||', $el);
                   if(!isset($parts[0],$parts[1]) || intval($parts[0]) <= 0) continue;
                   $links .= '<a href="'. $host . getFriendlyURL("project", array('id' => intval($parts[0]),'name' => $parts[1])) . '">'.$parts[1].'</a><br/>';
               }
               
               if(empty($links)) continue; 
              
               $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login'],
                        'PROJECT_LINKS'     => $links,
                    )
                );

                $count++;
            }
            
            $this->__save_sended_ids($ids);
            $page = 0;
            
            $this->bind($massId, true);
        }
        
        $this->__save_spam_date(array(
            'from_date' => $date_interval['from_date'],
            'to_date' => $date_interval['to_date'],
            'type' => $type,
            'sended' => $count
        ));        
        
        return $count;
    }
    
    
    
    
//------------------------------------------------------------------------------
    
    
    /**
     * ##0026615
     * Рассылка по фрилансерам ответивших хоть один раз в проектах за 2014 год 
     * так и не став исполнителями.
     * 
     * https://beta.free-lance.ru/mantis/view.php?id=26615
     * 
     */
    public function sendFrlOffer()
    {
        $type = 0;
        $date_interval = $this->__get_next_spam_date($type);
        if(!$date_interval) return 'Достигнута минимальная дата рассылки';
        
        
        $datefrom = $date_interval['from_date']; 
        $dateto = $date_interval['to_date'];
        $host = $GLOBALS['host'];
        
        $this->subject = "Получите отзывы по проектам {$date_interval['year']} года!";
        
        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/spam/frl_project_offer.tpl.php', 
                array(
                    //'project_links' => '%PROJECT_LINKS%',
                    'host' => $host
                )
        );        
        $this->recipient = '';
        $massId = $this->send('text/html');        
        
        $page  = 0;
        $count = 0;
        
        while ( $users = projects::getFrlOffer($datefrom, $dateto, ++$page, 200) ) 
        {
            $ids = array();
            foreach ( $users as $user ) 
            {
               /* 
               $projects_list = DB::array_to_php($user['projects_list']); 
               if(empty($projects_list)) continue;
               $links = '';
               foreach($projects_list as $el)
               {
                   $parts = explode('||', $el);
                   if(!isset($parts[0],$parts[1]) || intval($parts[0]) <= 0) continue;
                   $links .= '<a href="'. $host . getFriendlyURL("project", array('id' => intval($parts[0]),'name' => $parts[1])) . '">'.$parts[1].'</a><br/>';
               }
               
               if(empty($links)) continue; 
              */
                
               $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login']//,
                        //'PROJECT_LINKS'     => $links,
                    )
                );
                
                $ids[] = array('user_id' => $user['uid'], 'type' => $type);
                $count++;
            }
            
            $this->__save_sended_ids($ids);
            //сбрасываем страницу так как выбока будет проходить 
            //до тех пор пока все id юзеров не попадут в список 
            //отправленых и постраничность сдесь только мешает.
            $page = 0;
            
            $this->bind($massId, true);
        }
        
        $this->__save_spam_date(array(
            'from_date' => $date_interval['from_date'],
            'to_date' => $date_interval['to_date'],
            'type' => $type,
            'sended' => $count
        ));
        
        return $count;
    }
    
    
    
    /**
     * Сохраняем пачку юзеров тем кому отправили
     * 
     * @global type $DB
     * @param type $ids
     * @return type
     */
    private function __save_sended_ids($ids)
    {
        global $DB;
        return $DB->insert('projects_spam_is_send',$ids);
    }

    /**
     * Сохраняем последний интервал рассылки
     * 
     * @global type $DB
     * @param type $data
     * @return type
     */
    private function __save_spam_date($data)
    {
        global $DB;
        return $DB->insert('projects_spam_interval',$data,'id');
    }

    
    /**
     * Получаем следующий интервал времени для рассылки указанного типа
     * 
     * @global type $DB
     * @param int $type
     * @return boolean | array
     */
    private function __get_next_spam_date($type = 0)
    {
        global $DB;
        
        //Интервал рассылки
        $spam_interval = 3;//за месяца
        //минимальная дата рассылки
        $min_date = strtotime('2009-01-01');

        $last = $DB->val("
            SELECT
                from_date
            FROM projects_spam_interval
            WHERE type = ?i
            ORDER BY from_date, id DESC
            LIMIT 1
        ",$type);
        
        if(!$last) return FALSE;

        $from = strtotime("- {$spam_interval} month", strtotime($last));
        
        if($from < $min_date) return FALSE;
        
        return array(
            'year' => date('Y',$from),
            'from_date' => date('Y-m-d H:i:s',$from),
            'to_date' => $last
        );
    }
    
    
   /**
    * Уведомление заказчику после успешной оплаты 
    * перемещенной из проектов вакансии
    * 
    * @param type $project
    * @return type
    */
   public function sendMovedToVacancySuccessPayed($project) 
   {
        $this->subject = "Ваша вакансия успешно оплачена";
        $this->recipient = "{$project['email']} <{$project['email']}>";
        $this->message = Template::render(ABS_PATH . '/templates/mail/projects/makevacancy_payed.tpl.php',array(
            'title' => $project['name']
        ));
        return $this->send('text/html');
    }
    
    
    /**
     * Уведомление фрилансеру за 1 день до окончания 
     * размещения в закрепления в каталоге
     */
    public function remindFreelancerbindsProlong() {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_binds.php";

        $binds = freelancer_binds::getExpiring();
        if(!$binds) return false;
        
        foreach($binds as $val) {
            if($val['bill_subscribe'] == 'f') continue;
            
            $catalog_name = '';
            $catalog_url = "{$GLOBALS['host']}/freelancers/";
            if ($val['prof_id'] == 0) {
                $catalog_name = 'общем разделе';
            } elseif ($val['is_spec'] == 'f') {
                $group = professions::GetGroup($val['prof_id'], $error);
                $catalog_url .= $group['link'];
                $catalog_name = "разделе <a href='{$catalog_url}'>{$group['name']}</a>";
            } else {
                $prof_name = professions::GetProfName($val['prof_id']);
                $catalog_url .= professions::GetProfLink($val['prof_id']);
                $catalog_name = "подразделе <a href='{$catalog_url}'>{$prof_name}</a>";
            }

            $this->recipient = "{$val['uname']} {$val['usurname']} [{$val['login']}] <{$val['email']}>";
            $this->message = Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/freelancer_binds/remind_prolong.tpl.php', 
                    array(
                        'smail' => &$this,
                        'time' => dateFormat('H:i', $val['to_date']),
                        'catalog_url' => $catalog_url,
                        'catalog_name' => $catalog_name
                    )
            );
            $ok = $this->send('text/html');
            if ($ok) {
                freelancer_binds::markSent('prolong', $val['uid'], $val['prof_id'], $val['is_spec']);
            }
        }
        return 0;
    }
    
    /**
     * Уведомление фрилансеру за 1 день до окончания 
     * размещения в freelancer_binds
     */
    public function remindFreelancerbindsUp() {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_binds.php";

        $binds = freelancer_binds::getDowned();
        if(!$binds) return false;
        
        foreach($binds as $val) {
            if($val['bill_subscribe'] == 'f') continue;
            
            $catalog_name = '';
            $catalog_url = "{$GLOBALS['host']}/freelancers/";
            if ($val['prof_id'] == 0) {
                $catalog_name = 'общем разделе';
            } elseif ($val['is_spec'] == 'f') {
                $group = professions::GetGroup($val['prof_id'], $error);
                $catalog_url .= $group['link'];
                $catalog_name = "разделе <a href='{$catalog_url}'>{$group['name']}</a>";
            } else {
                $prof_name = professions::GetProfName($val['prof_id']);
                $catalog_url .= professions::GetProfLink($val['prof_id']);
                $catalog_name = "подразделе <a href='{$catalog_url}'>{$prof_name}</a>";
            }

            $this->recipient = "{$val['uname']} {$val['usurname']} [{$val['login']}] <{$val['email']}>";
            $this->message = Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/freelancer_binds/remind_up.tpl.php', 
                    array(
                        'smail' => &$this,
                        'catalog_url' => $catalog_url,
                        'catalog_name' => $catalog_name
                    )
            );
            $ok = $this->send('text/html');
            if ($ok) {
                freelancer_binds::markSent('up', $val['uid'], $val['prof_id'], $val['is_spec']);
            }
        }
        return 0;
    }
}

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smtp2.php';

class smail2 extends SMTP2 
{
    /**
     * Отсылает сообщение порльзователю о том, что аккаунт PRO закончится через день
     * 
     * @param integer $user_id ИД пользователя
     * @return null
     *
     * @deprecated #0024638
     */
    public function sendPROEnding($role = 'FRL', $users) {
        return;
        global $host;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        
        $UTM = "utm_source=newsletter4&utm_medium=email&utm_campaign=expiring_PRO";
        $this->subject   = "FL.ru: заканчивается действие вашего аккаунта PRO";
        
        if($role == 'EMP') {
            $cid1  = $this->cid();
            $cid2  = $this->cid();
            $cid3  = $this->cid();

            $this->attach(ABS_PATH . '/images/letter/19.png', $cid1);
            $this->attach(ABS_PATH . '/images/letter/14.png', $cid2);
            $this->attach(ABS_PATH . '/images/letter/18.png', $cid3);

            ob_start();
            include ($_SERVER['DOCUMENT_ROOT']."/classes/letters_html/tpl.outpro-emp.php");
            $body_html = ob_get_clean();
        } else {
            $cid1  = $this->cid();
            $cid2  = $this->cid();
            $cid3  = $this->cid();

            $this->attach(ABS_PATH . '/images/letter/19.png', $cid1);
            $this->attach(ABS_PATH . '/images/letter/13.png', $cid2);
            $this->attach(ABS_PATH . '/images/letter/18.png', $cid3);

            ob_start();
            include ($_SERVER['DOCUMENT_ROOT']."/classes/letters_html/tpl.outpro-frl.php");
            $body_html = ob_get_clean();
        }
        foreach($users as $user) {
            $this->message   = str_replace("%UNSUBSCRIBE_KEY%", users::GetUnsubscribeKey($user['login']) , $body_html);
            $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $this->send('plain/text');
        }
    }


    /**
     * письмо о том что рассылка принята
     * @param $user автор рассылки
     */
    public function masssendingAccepted ($params) {
        global $host;
        $UTM = '';//"?utm_source=newsletter4&utm_medium=email&utm_campaign=expiring_PRO";
        $this->subject   = "FL.ru: заявка на рассылку одобрена";

        $cid = $this->cid();
        $this->attach(ABS_PATH . '/images/letter/pay.png', $cid);

        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.accept_letter.php");
        $body_html = ob_get_clean();

        $this->message   = str_replace("%UNSUBSCRIBE_KEY%", users::GetUnsubscribeKey($params['login']) , $body_html);
        $this->recipient = "{$params['uname']} {$params['usurname']} [{$params['login']}] <{$params['email']}>";
        $this->send('plain/text');
    }
    /**
     * Рассылка о новых проектах лицам, отсутствовавшим на сайте более суток и менее года. Вызывается раз в день из hourly.php
     * @param array &$uids - массив идентификаторов пользователей, подписанных на новые проекты но которым отправлено это уведомление
     *                        необходимо передать в smail::NewProj
     * @return integer   количество получивших рассылку
     */
    public function NewProjForMissingMoreThan24h(&$uids) {
        $projects = projects::GetNewProjectsWithBudjet($error);
        //сортировка по стоимости
        foreach ($projects as $key=>$prj) {
            $prj["sort_cost"] = $prj["cost"];
            if ($prj["currency"] == 0) {
                $prj["sort_cost"] *= 30; //в данном случае точный курс не важен, важно отсортировать
            }
            if ($prj["currency"] == 1) {
                $prj["sort_cost"] *= 40; //в данном случае точный курс не важен, важно отсортировать
            }
            $projects[$key] = $prj;
        }
        
        $all_mirrored_specs = professions::GetAllMirroredProfsId();
        $professions = professions::GetProfessionsAndGroup();
        $professionsTree = array();
        foreach ($professions as $k=>$i) {                         
            if ($professionsTree[$i["gid"]] === null) {
                $professionsTree[$i["gid"]] = array( "gid" => $i["gname"]);
                if ($i["id"] !== null) $professionsTree[$i["gid"]] [$i["id"]] = $i["name"];
                    else $professionsTree[$i["gid"]] = $i["gname"];
            }else if ( is_array($professionsTree[$i["gid"]]) ) {
                $professionsTree[$i["gid"]] [$i["id"]] = $i["name"];
            }
        }
        $page  = 0;
        $count = 0; // total
        $countBs     = 0; // БС
        $countCar    = 0; // карусель
        $countPro    = 0; // ПРО
        $countPayed  = 0; // платные места
        $countVerify = 0; // верификация
        $this->subject = "Новые проекты и конкурсы на FL.ru";
        $pHost = $GLOBALS['host'];
        
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.missing_more_than_24h.php");
        $this->message = ob_get_clean();
        $this->recipient = '';
        $massId = $this->masssend();
        $dbStat = new DB("master");
        while ( $users = freelancer::GetMissingMoreThan24h($error, ++$page, 100) ) {
            $this->recipient = array();
            foreach ( $users as $user ) {
                if (!$user['unsubscribe_key']) {
                    $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                }
                $unsubscribe_link = "{$pHost}/unsubscribe?ukey=" . $user['unsubscribe_key'];
                $advert_template = $this->getAdvertTemplate($user, $n);
                //номер рекламного блока 0 - БС, 1 - карусель, 2 - ПРО, 3 - платные места, 4 - верификация
                switch ($n) {
                    case 0:
                        $countBs++;
                        break;
                    case 1:
                        $countCar++;
                        break;
                    case 2:
                        $countPro++;
                        break;
                    case 3:
                        $countPayed++;
                        break;
                    case 4:
                        $countVerify++;
                        break;
                }
                $pList = $this->getProjectsForUser($projects, $user, $all_mirrored_specs, $professionsTree);
                $length = count( $pList );
                if ( $length == 0 ) {
                    continue;
                }
	            for ($i = 0; $i < count($pList); $i++) {
		            for ($j = $i; $j < count($pList); $j++) {
		                $a = $pList[$i];
		                $b = $pList[$j];
		                if ( $b["sort_cost"] > $a["sort_cost"]) {
		                    $buf = $pList[$i];
		                    $pList[$i] = $pList[$j];
		                    $pList[$j] = $buf;
		                }
		            }
		        }
		        $pListHtml = "";
		        foreach ($pList as $p) {
		            ob_start();
		            include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.missing_more_than_24h_list_item.php");
		            $pListHtml .= ob_get_clean();
		        }
                $str = "Посмотрите пять самых свежих &mdash; они могут вам понравиться.";
                switch ($length) {
                	case 1:
                        $str = "Посмотрите самый свежий &mdash; он может вам понравиться.";
                        break;
                    case 2:
                        $str = "Посмотрите два самых свежих &mdash; они могут вам понравиться.";
                        break;
                    case 3:
                        $str = "Посмотрите три самых свежих &mdash; они могут вам понравиться.";
                        break;
                    case 4:
                        $str = "Посмотрите четыре самых свежих &mdash; они могут вам понравиться.";
                        break;
                }
                ob_start();
                include $_SERVER['DOCUMENT_ROOT'] . "/masssending/$advert_template";
                $advHtml = ob_get_clean();
                if ($user["subscr_new_prj"] == 't') {
                    $uids[] = $user["uid"];
                }
                
                $recipient[] = array (
	                'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] " . " <" . $user['email'] . ">",
	                'extra' => array (
	                    'NAME'  => (string) $user['uname'],
	                    'EMAIL' => (string) $user['email'],
	                    'LIST'  => (string) $pListHtml,
	                    'ADV'   => (string) $advHtml,
                        'STR'   => (string) $str,
                        'UNSUBSCRIBE_LINK'   => (string) $unsubscribe_link
	                )
	            );
                $count++;
            }
            
            $this->recipient = $recipient;
            $this->bind($massId);
            $recipient = array();
        }
        $query = "INSERT INTO subscribe_missing_24h_stat (date_subscribe, bs, carusel, pro, payed_places, verify) VALUES (?, ?i, ?i, ?i, ?i, ?i)";
        $dbStat->query($query, date("Y-m-d"), $countBs, $countCar, $countPro, $countPayed, $countVerify);
        return $count;
    }
    /**
     * @see    NewProjForMissingMoreThan24h
     * @desc   Определяет шаблон рекламы, которую показываем пользователю
     * @param  array $user - ассоциативный массив с данными о пользователе
     * @param  int   $n    - сюда записывается номер рекламного блока 0 - БС, 1 - карусель, 2 - ПРО, 3 - платные места, 4 - верификация
     * @return string имя шаблона в папке $_SERVER['DOCUMENT_ROOT'] . "/masssending/ 
    **/
    private function getAdvertTemplate($user, &$n) {
        $tplList = array(
            0 => "tpl.missing_more_than_24h_sbr_advert.php", //БС 
            1 => "tpl.missing_more_than_24h_carusel_advert.php", //карусель 
            2 => "tpl.missing_more_than_24h_pro_advert.php", //PRO 
            3 => "tpl.missing_more_than_24h_adv_places_advert.php", //платные места 
            4 => "tpl.missing_more_than_24h_verify_advert.php" //Верификация 
        );
        $n = intval(date('z')) % 5;
        if ( $_GET["debug"] == 1  ) {
            $n = intval($_GET["type"]) % 5;
        } else {
            if ( $n == 2 && $user["is_pro"] == 't') {
                $n = ($n + 2) % 5;
            }
            if ( $n == 4 && $user["is_pro"] == 't') {
                $n = ($n + 2) % 5;
            }
        }
        return $tplList[$n];
    }
    /**
     * @see    NewProjForMissingMoreThan24h
     * @desc   Выбирает из projects два проекта и три конкурса, соответствующих специализации пользователя
     * @param  array $projects - массив с данными о проектах, опубликованых за последние сутки
     * @param  array $user     - ассоциативный массив с данными о пользователе
     * @param  array $all_mirrored_specs - массив с данными об отраженных специальностях
     * @param  array $professionsTree - дерево групп специальностей
     * @return string имя шаблона в папке $_SERVER['DOCUMENT_ROOT'] . "/masssending/ 
    **/
    private function getProjectsForUser($projects, $user, $all_mirrored_specs, $professionsTree) {
        $userProjects = array();
        $userTenders  = array();
        $foundProjectsSpecIds = array(); //хранит те специальности пользователя, по которым найдены проекты
        $foundTenderSpecIds = array();   ////хранит те специальности пользователя, по которым найдены конкурсы
        foreach ($projects as $p) {
            $projectForSpec = null;
            if ( count($p["specs"]) ) {
            	$projectForSpec = '';
            	foreach ( $p["specs"] as $k => $i ) {
            		$projectForSpec[] = $i["subcategory_id"];
            	}
            }
            if ($projectForSpec !== null ) {
	            //base
	            if ( in_array($user['spec'], $projectForSpec) ) {
	            	if ($p["kind"] != 2 && $p["kind"] != 7) {
	            		$userProjects[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
	            		$foundProjectsSpecIds[] = $user['spec'];
	            	} else {
	            		$userTenders[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
	            		$foundTendersSpecIds[] = $user['spec'];
	            	}
	                continue;
	            }
	            //additional
	            $add_specs = $user["additional_specs"];
	            $add_specs = $this->getMirroredSpecs(explode(',', $add_specs . ',' . $user['spec']), $all_mirrored_specs);
	            $continue = false;
	            foreach ($add_specs as $spec) {
	                $spec = intval($spec);
		            if ( in_array($spec, $projectForSpec) ) {
		                if ($p["kind"] != 2 && $p["kind"] != 7) {
		                    $userProjects[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
		                    $foundProjectsSpecIds[] = $spec;
		                } else {
		                    $userTenders[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
		                    $foundTendersSpecIds[] = $spec;
		                }
		                $continue = true;
	                 }
	            }
	            if ($continue) {
	                continue;
	            }
            }
        }
        //ищем среди смежных
        if ( count($userProjects) + count($userTenders) < 5) {
            $rel_specs = $this->getRelatedSpecs( $user["additional_specs"] . ',' . $user["spec"], $professionsTree );
            //удалить все те, по которым уже найдено
            $sz = count($foundProjectsSpecIds);
            $sz2 = count($foundTendersSpecIds);
            if ($sz2 > $sz) {
                $sz = $sz2;
            }
            for ($i = 0; $i < $sz; $i++ ) {
            	if ( $i < count($foundProjectsSpecIds) ) {
	            	$n = $foundProjectsSpecIds[$i];
	            	$rel_specs = str_replace("$n,", "", $rel_specs);
            	}
                if ( $i < count($foundTendersSpecIds) ) {
                    $n = $foundTendersSpecIds[$i];
                    $rel_specs = str_replace("$n,", "", $rel_specs);
                }
            }
            $rel_specs = explode(",", $rel_specs);
            foreach ($projects as $p) {
	            if ( count($p["specs"]) ) {
	                $projectForSpec = array();
	                foreach ( $p["specs"] as $k => $i ) {
	                    $projectForSpec[] = $i["subcategory_id"];
	                }
	            }
                foreach ($rel_specs as $spec) {
                    $spec = intval($spec);
                    if ( in_array($spec, $projectForSpec) ) {
                        if ($p["kind"] != 2 && $p["kind"] != 7) {
                            $userProjects[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
                        } else {
                            $userTenders[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
                        }
                        continue;
                    }
                }
            }
        }
        $result = array();
        $i = 0;
        $limit = 5 - count($userTenders);
        if ($limit < 3) {
            $limit = 3;
        }
        foreach ($userProjects as $project) {
            if ($i >= $limit) {
                break;
            }
            $result[] = $project;
            $i++;
        }
        foreach ($userTenders as $project) {
            if (count($result) > 4) {
                break;
            }
            $result[] = $project;
        }
        return $result;
    }

    /**
     * @see getProjectsForUser
     * @desc выбирает все отраженные специальности специальностей $specs  
     * @param array $specs - специальности, зеркала которых надо найти
     * @param array $all_mirrored_specs - массив отражений специальностей
     * @return array отражения специальностей пользователя
    **/
    private function getMirroredSpecs($specs, $all_mirrored_specs) {
    	$mspecs = array();
        foreach ($specs as $spec) {
            $spec = (int)$spec;
            if ($spec) {
                foreach ($all_mirrored_specs as $ms) {
                    if ( $ms["main_prof"] == $spec ) {
                        $mspecs[] = $ms["mirror_prof"];
                    }
                    if ( $ms["mirror_prof"] == $spec ) {
                        $mspecs[] = $ms["main_prof"];
                    }
                }
                $mspecs[] = $spec;
            }
        }
        return $mspecs;
    }
    /**
    * @see 
    * @desc получить специальности смежные $specs 
    * @param string $specs строка идентификаторов специальностей разделенных запятой
    * @param array $professionsTree дерево специальностей и их групп 
    * @return string строка идентификаторов смежных специальностей разделенных запятой
    **/
    private function getRelatedSpecs( $specs, $professionsTree ) {
    	$dbg = false;
    	$relatedSpecs = "";
        $specs = explode(",", $specs);
        foreach ($specs as $spec) {
            $spec = (int)$spec;
            if ($spec) { echo "";
            	if ($spec == 1) {
            		$dbg = true;
            	}
	            foreach ($professionsTree as $group => $list) {
	            	$buffer = "";
	            	$k = 0;
	            	$found = 0;
	                foreach ($list as $id => $name) {
	                    if ($k == 0) {
	                        $k++;
	                        continue;
	                    }
	                    if ($id == $spec) {
	                        $found = 1;
	                    } else {
	                        $buffer .= $id . ",";
	                    }
	                }
	                if ($found) {
	                    $relatedSpecs .= $buffer;
	                }
	            }
            }
        }
        return $relatedSpecs;
    }

    /**
    * @see  getProjectsForUser
    * @desc   Подготавливает данные проекта (добавляет поля массива, которые выводятся в шаблонах рассылки tpl.missing_more_than_24h)
    * @param  array $project ассоциативный массив с данными о проекте
    * @return array ассоциативный массив с данными о проекте
    **/
    private function prepareProjectDataForSubscribe($project) {
    	$p = $project;
    	if ($p["kind"] == 2 || $p["kind"] == 7) {
    	    $p["str_kind"] = "Конкурс";
    	} else {
    	    $p["str_kind"] = "Проект";
    	}
        //Валюта проекта currency (0 - USD, 1 - EUR, 2 - RUR, 3 - FM)
        // Тип стоимости проекта: 1 - за час, 2 - за день, 3 - за месяц, 4 - за проект
        $currency = array(0 => '$', 1 => '&euro;', 2 => 'р.', 3 => 'FM');
        $priceby = array(1 => 'час', 2 => 'день', 3 => 'месяц', 4 => 'проект');
        $p["measure"] = "{$currency[ $p['currency'] ]}/{$priceby[ $p['priceby'] ]}";
        if ($p["kind"] == 7 || $p["kind"] == 2) {
            $p["measure"] = "{$currency[ $p['currency'] ]}";
        }
        if ($p["cost"] == 0) {
            $p["measure"] = "По договоренности";
            $p["cost"] = '';
        }
        $p["link"] = $GLOBALS["host"] . getFriendlyURL("project" , array( "id" => $p["id"], "name" => $p["name"] ));
        $p["link"] .= "?utm_source=newsletter4&utm_medium=email&utm_campaign=notif_ed_all";
        if ( strlen($p["descr"]) > 200 ) {
            $s = $p["descr"];
            $j = 200;
            for ($i = 199; $i > -1; $i--) {
                if ($s[$i] == ' ') {
                    $j = $i;
                    break;
                }
            }
            $p["descr"] = substr($s, 0, $j) . "&hellip;";
        }
        if ( strlen($p["name"]) > 50 ) {
            $s = $p["name"];
            $j = 50;
            for ($i = 49; $i > -1; $i--) {
                if ($s[$i] == ' ') {
                    $j = $i;
                    break;
                }
            }
            $p["name"] = substr($s, 0, $j) . "&hellip;";
        }
        return $p;
    }
}