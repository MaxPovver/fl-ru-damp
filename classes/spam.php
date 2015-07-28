<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';

/**
 * Класс для работы с массовыми рассылками в личных сообщениях
 */
class spam {

    const MASSSEND_BIND_QUEUE_SIZE = 5000;
    
    /**
     * Объект класса user с заполненными свойствами автора рассылки
     * 
     * @var users
     */
    protected $_sender;
    /**
     * Объект класса DB с подключением к БД master
     * 
     * @var DB
     */
    protected $_dbMaster;
    /**
     * Объект класса DB с подключением к БД plproxy
     * 
     * @var DB
     */
    protected $_dbProxy;
    
    
    /**
     * Конструктор класса
     * 
     * @param  string  $sender  Логин автора рассылки
     */
    public function __construct($sender='admin') {
        $this->_sender = new users;
        $this->_sender->GetUser($sender);
        $this->_dbMaster = new DB('master');
        $this->_dbProxy  = new DB('plproxy');
    }

    
    /**
     * Делает рассылку по sql запросу.
     * В SELECT части sql запроса обязательно должно быть поле uid
     * 
     * @param  string   $sql         SQL запрос возвращающий список респондетов
     * @param  string   $message     сообщение
     * @param  string   $mailFunc    функция отправки сообщений на почту (из класса pmail)
     * @param  integer  $recOnStep   количество пользователей попадающих в одну очередь
     * @return integer               id сообщения из таблицы messages
     */
    protected function _masssendSql($sql, $message, $mailFunc, $recOnStep = self::MASSSEND_BIND_QUEUE_SIZE) {
        $msgid = $this->_dbProxy->val("SELECT masssend(?, ?, ?a, ?)", $this->_sender->uid, $message, array(), $mailFunc);
        $res   = $this->_dbMaster->query($sql);
        $i = 0;
        $uids = array();
        while ( $row = pg_fetch_assoc($res) ) {
            $uids[] = $row['uid'];
            if ( ++$i % $recOnStep == 0 ) {
                $this->_dbProxy->query("SELECT masssend_bind(?, ?, ?a)", $msgid, $this->_sender->uid, $uids);
                $uids = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->_dbProxy->query("SELECT masssend_bind(?, ?, ?a)", $msgid, $this->_sender->uid, $uids);
            $uids = array();
        }
        $this->_dbProxy->query("SELECT masssend_commit(?, ?)", $msgid, $this->_sender->uid);
        return $msgid; 
    }

    
    /**
     * Формирует сслыку (работает только с авторами рассылок, которым можно использовать
     * ссылки в личных сообщениях)
     * 
     * @param  string  $href   Ссылка
     * @param  string  $title  Текст ссылки
     * @return string          Ссылка для вставки в текст
     */
    protected function _link($href = '', $text = '') {
        $h = preg_replace("/https?\:\/\//", "", $GLOBALS['host']);
        return 'http:/{'.$text.'}/'.$h.$href;
    }
    
    
    /**
     * Шаблон для сообщений (вариант 1)
     * 
     * @param  string  $text   Текст сообщения
     * @return string          Сообщение обработанное по шаблону
     */
    protected function _template1($text) {
        return "
Здравствуйте!
        
{$text}

По всем возникающим вопросам вы можете обращаться в нашу " . $this->_link('https://feedback.fl.ru/', 'службу поддержки') . ".
Вы можете отключить уведомления на ". $this->_link('/users/%USER_LOGIN%/setup/mailer/', 'странице «Уведомления/Рассылка»') . " вашего аккаунта.

Приятной работы,
Команда " . $this->_link('', 'Free-lance.ru') . "
        ";
    }
    
    
    /**
     * @todo: отключено в hourly, при использовании исправить текст
     * 
     * Рассылка PRO работодателям, которые зарегистрировались менее 30 дней назад
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function proEmpRegLess30() {
        $message = $this->_template1(
"Мы очень рады вам на Free-lance.ru!

На нашем сайте работают около 1 млн. исполнителей.  И мы знаем, сколько времени нужно провести, просматривая портфолио и отвечая на сотни писем от фрилансеров, чтобы выбрать идеального исполнителя из такого количества кандидатов. Именно поэтому мы предлагаем вам свою помощь. Если вы хотите сэкономить свое время, воспользуйтесь услугой ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "&laquo;Подбор фрилансеров&raquo;").".

Наши квалифицированные менеджеры по подбору персонала найдут для вас самых лучших исполнителей. Вам нужно лишь ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "заполнить заявку")." – все остальное мы сделаем за вас в кратчайшие сроки."
        );
        //PRO
        $sql = $this->_dbMaster->parse("
            SELECT 
                uid 
            FROM 
                employer u
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE
                is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND uid <> 103
                AND reg_date >= NOW()::date - interval '1 month'
            AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)   
        ", $this->_sender->uid);
        $res1 = $this->_masssendSql($sql, $message, "empRegLess30");
        return $res1;
    }

    
    /**
     * @todo: отключено в hourly, при использовании исправить текст
     * 
     * Рассылка работодателям, которые зарегистрировались менее 30 дней назад
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function noProEmpRegLess30() {
        //NOT PRO
        $message = "Мы очень рады вам на Free-lance.ru!

На нашем сайте работают около 1 млн. исполнителей.  И мы знаем, сколько времени нужно провести, просматривая портфолио и отвечая на сотни сообщений фрилансеров, чтобы выбрать идеального исполнителя из такого количества кандидатов. Именно поэтому мы предлагаем вам свою помощь. Если вы хотите сэкономить свое время, воспользуйтесь услугой ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "&laquo;Подбор фрилансеров&raquo;").". 

Наши квалифицированные менеджеры по подбору персонала найдут для вас самых лучших исполнителей. Вам нужно лишь ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "заполнить заявку")." – все остальное мы сделаем за вас в кратчайшие сроки.

Если же вы хотите найти фрилансеров для выполнения своих проектов самостоятельно, просмотрите его контактную информацию. Обратите внимание: видеть контакты всех фрилансеров могут только владельцы ".$this->_link("/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "аккаунта PRO").". Помимо возможности видеть прямые контакты (e-mail, ICQ, Skype) пользователей, вы будете пользоваться скидками на все платные услуги сайта, сможете указать дополнительную информацию о себе и получите размещение в особой зоне каталога работодателей.";
        $sql = $this->_dbMaster->parse("
            SELECT 
                uid 
            FROM 
                employer u
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE
                is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND uid <> ?i
                AND reg_date >= NOW()::date - interval '1 month'
            AND                 
                (o.payed IS NULL                
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )   
        ", $this->_sender->uid);
        $res1 = $this->_masssendSql($sql, $message, "empRegLess30");
        return $res1;
    }
    
    /**
     * Рассылка фрилансерам, которые зарегистрировались на сайте менее 30 дней назад и не купили никакой ПРО
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function frlNotBuyPro() {
        $message = $this->_template1(
'Мы очень рады вам на Free-lance.ru!

Чтобы сразу получить интересный проект, стоит выделиться среди конкурентов и стать заметнее для работодателей. Для этого предлагаем вам ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_registration_30', 'протестировать аккаунт PRO') . '.

По статистике Free-lance.ru, на каждого владельца PRO-аккаунта приходится по 2 проекта от работодателей. А средний гонорар за проект &laquo;только для PRO&raquo; составляет 25&nbsp;000 рублей.
Это значит, что, ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_registration_30', 'купив PRO') . ', вы будете зарабатывать больше.

А вот и другие преимущества:<ul><li>у PRO неограниченное количество ответов на проекты, в то время как у пользователей с начальным аккаунтом имеется только 3 бесплатных ответа в месяц;</li><li>отклики на проекты от PRO располагаются выше откликов остальных фрилансеров, что привлекает внимание работодателей.</li><li>фрилансеры с PRO располагаются выше других пользователей в каталоге фрилансеров.</li></ul>Дополнительный бонус: работодатели в своих проектах будут видеть фрилансеров с PRO в качестве рекомендованных исполнителей. Теперь выгодные заказы найдут вас сами &ndash; вам нужно лишь следить за своевременным обновлением срока действия своего профессионального аккаунта. 

' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_registration_30', 'Узнайте больше и поPROбуйте!')
        );
        $sql = "
            SELECT 
                u.uid 
            FROM 
                freelancer u
            LEFT JOIN 
                orders o ON o.from_id = u.uid AND o.ordered = '1' AND o.payed = 't' AND 
                    o.tarif IN ( 15, 16, 28, 35, 42, 47, 48, 49, 50, 51, 52, 76 ) 
            WHERE 
                o.id is NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND u.reg_date >= NOW()::date - interval '1 month' AND u.reg_date <= NOW()::date
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    
    
    /**
     * Рассылка фрилансерам, которые купили тестовый ПРО и не купили обычный ПРО в течение месяца
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function frlBuyTestPro() {
        $message = $this->_template1(
'Мы обратили внимание, что вы попробовали тестовый PRO. Может, пришло время ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_PRO', 'приобрести полноценный') . '<nobr>?</nobr>

Несколько аргументов в пользу PRO:<ol><li>На каждого владельца PRO-аккаунта приходится по 2 проекта от работодателей. А средний гонорар за проект &laquo;только для PRO&raquo; составляет 25 000 рублей. 
Это значит, что, купив PRO, вы будете зарабатывать больше.</li><li>Неограниченное количество ответов на проекты (фрилансеры с начальным аккаунтом могут бесплатно отвечать всего на 3 проекта в месяц) и возможность показывать работодателю свои лучшие работы.</li><li>Размещение в зоне PRO &ndash; выше, чем остальные пользователи &ndash; при отклике на каждый проект и в Каталоге фрилансеров.</li><li>Выбор целых пяти специализаций (в отличие от одной – для обладателей начального аккаунта).</li></ol>Дополнительный бонус: работодатели в своих проектах будут видеть фрилансеров с PRO в качестве рекомендованных исполнителей. Теперь выгодные заказы найдут вас сами &ndash; вам нужно лишь следить за своевременным обновлением срока действия своего профессионального аккаунта. 

Узнать более подробно обо всех преимуществах и приобрести аккаунт PRO можно '. $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_PRO', 'здесь' ) .'.'
        );
        $sql = "
            SELECT 
                u.uid 
            FROM 
                freelancer u
            INNER JOIN 
                orders ot ON ot.from_id = u.uid AND ot.ordered = '1' AND ot.payed = 't' AND ot.tarif = 47 
            LEFT JOIN 
                orders op ON op.from_id = u.uid AND op.ordered = '1' AND op.payed = 't' 
                    AND op.tarif IN ( 15, 16, 28, 35, 42, 48, 49, 50, 51, 52, 76 ) 
            WHERE 
                op.id is NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND ot.from_date + ot.to_date + COALESCE(ot.freeze_to, '0')::interval >= NOW() - interval '1 month' 
                    AND ot.from_date + ot.to_date + COALESCE(ot.freeze_to, '0')::interval < NOW()
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    

    /**
     * Рассылка фрилансерам, которые купили тестовый ПРО и после него только однажды купили обычный
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function frlBuyProOnce() {
        $message = $this->_template1(
	'Мы обратили внимание, что после покупки ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_repeat_PRO', 'аккаунта PRO') . ' вы не продлили срок его действия. 

Вместе с тем, по статистике на каждого владельца PRO-аккаунта приходится по 2 проекта от работодателей, а средний бюджет проекта с пометкой &laquo;Только для PRO&raquo; составляет более 25 000  рублей. 
А это значит, что PRO сразу себя окупает.

' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_repeat_PRO', 'Аккаунт PRO') . ' дает дополнительные возможности своим владельцам:<ul><li>открытый доступ к вашим контактам: вашу контактную информацию могут видеть все работодатели – любой из них сможет быстро связаться с вами в случае необходимости;</li><li>неограниченное количество ответов на проекты (фрилансеры с начальным аккаунтом могут бесплатно отвечать всего на 3 проекта в месяц) и возможность показать заказчику свои лучшие работы, прикрепив их к своему предложению в проекте;</li><li>размещение в зоне PRO – выше фрилансеров с начальным аккаунтом – при отклике на каждый проект и в каталоге фрилансеров;</li><li>пять специализаций (в отличие от одной – для обладателей начального аккаунта).</li></ul>Дополнительный бонус: работодатели видят фрилансеров с PRO в качестве рекомендованных исполнителей в своих проектах. Теперь выгодные заказы найдут вас сами – вам нужно лишь следить за своевременным обновлением срока действия профессионального аккаунта.

Узнать более подробно обо всех преимуществах и приобрести аккаунт PRO ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_repeat_PRO', 'можно здесь')
        );
        $sql = "
            SELECT 
                u.uid 
            FROM 
                freelancer u
            INNER JOIN 
                orders ot ON ot.from_id = u.uid AND ot.ordered = '1' AND ot.payed = 't' AND ot.tarif = 47 
            INNER JOIN (
                SELECT 
                    from_id, COUNT(tarif) AS pro_cnt, MAX(from_date + to_date + COALESCE(freeze_to, '0')::interval) AS max_date 
                FROM 
                    orders 
                WHERE 
                    ordered = '1' AND payed = 't' 
                        AND tarif IN ( 15, 16, 28, 35, 42, 48, 49, 50, 51, 52, 76 ) 
                GROUP BY from_id 
            ) AS op ON op.from_id = u.uid 
            WHERE 
                u.is_banned = '0' AND op.pro_cnt = 1
                    AND substr(u.subscr::text,8,1) = '1'
                    AND op.max_date >= NOW() - interval '1 month' AND op.max_date < NOW()
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    
    
    /**
     * Рассылка фрилансерам, у которых через 2 недели заканчивается про на 6 или 12 месяцев.
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function frlEndingPro() {
        $message = $this->_template1(
'Хотим напомнить, что через 2 недели заканчивается действие вашего ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_ending_PRO', 'аккаунта PRO') .'.

Вместе с окончанием срока действия профессионального аккаунта исчезнут такие удобные и привычные вещи как: <ul>
<li>возможность напрямую связаться с заказчиком: ваши контакты не будут видны работодателям</li>
<li>неограниченное количество ответов на проекты</li>
<li>возможность отвечать на проекты с пометкой &laquo;Только для PRO&raquo;</li>
<li>размещение в зоне PRO в Каталоге фрилансеров и в списке откликнувшихся на опубликованные проекты</li>
<li>высокий рейтинг, который рассчитывается по особой формуле</li>
<li>ярко и интересно оформленное портфолио с превью, возможность демонстрировать работодателю свои лучшие работы при ответе на опубликованный проект</li>
<li>дополнительные специализации</li>
<li>возможность создавать свои сообщества, отсутствие рекламы на сайте и многое другое.</li>
</ul>Для того чтобы и дальше наслаждаться преимуществами профессионального аккаунта, вы можете '.$this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_ending_PRO', 'продлить срок действия аккаунта PRO').'.'
        );
        //Получить тех, у кого PRO заканчивается через две недели
        $sql = "
            SELECT
                    uid
                FROM
                    orders o
                LEFT JOIN 
                    freelancer u ON o.from_id = u.uid 
                WHERE 
                    u.is_banned = '0' 
                    AND u.is_pro='true' AND u.is_pro_auto_prolong = 'f' 
                        AND substr(u.subscr::text,8,1) = '1'
                        AND o.payed='true' AND o.active='true'
                        AND o.tarif IN (42, 48, 49, 50, 51, 52, 66, 67, 68, 76 )
                        AND o.from_date + o.to_date + COALESCE(o.freeze_to, '0')::interval > (NOW()+'2 weeks')
                        AND o.from_date + o.to_date + COALESCE(o.freeze_to, '0')::interval < (NOW()+'2 weeks 1 day')
                GROUP BY 
                    uid 
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    
    
    /**
     * @todo: отключено в hourly, при использовании исправить текст
     * 
     * Рассылка PRO работодателям опубликовавшим платный проект или конкурс в течение 30 дней
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function empProPubPrj30Days() {
        $message = $this->_template1(
        "Мы обратили внимание на то, что вы недавно опубликовали проект. Если вы уже приняли решение о начале работы с конкретным фрилансером, воспользуйтесь сервисом ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "&laquo;Безопасная Сделка&raquo;").", гарантирующим безопасность и соблюдение сроков со стороны исполнителя.

Если же никто из откликнувшихся на проект не подошел, рекомендуем вам воспользоваться ".$this->_link("/masssending/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "рассылкой по каталогу фрилансеров").". Рассылка позволяет четко определить круг потенциальных исполнителей и обратиться к ним напрямую.

Кроме того, вы можете обратиться к ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "нашим менеджерам").", которые возьмут на себя все обязанности по подбору удаленных сотрудников."
);
        $sql = "
            SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u 
            INNER JOIN 
                projects p ON p.user_id = u.uid
            LEFT JOIN
                orders o ON o.from_id = u.uid 
            WHERE 
                p.billing_id IS NOT NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND p.create_date >= NOW() - interval '1 month' AND p.create_date < NOW()
            AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)
        ";
        $res1 = $this->_masssendSql($sql, $message, "empPubPrj30Days");
        return $res1; 
    }
    
    
    /**
     * @todo: отключено в hourly, при использовании исправить текст
     * 
     * Рассылка не PRO работодателям опубликовавшим платный проект или конкурс в течение 30 дней
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function empNoProPubPrj30Days() {        
        $message = $this->_template1(
        "Мы обратили внимание на то, что вы недавно опубликовали проект. Если вы уже приняли решение о начале работы с конкретным фрилансером, воспользуйтесь сервисом ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "«Безопасная Сделка»").", гарантирующим безопасность и соблюдение сроков со стороны исполнителя.
Если же никто из откликнувшихся на проект не подошел, рекомендуем вам воспользоваться рассылкой по каталогу фрилансеров. Рассылка позволяет четко определить круг потенциальных исполнителей и обратиться к ним напрямую. Чтобы связаться с нужным вам пользователем, просмотрите его контактную информацию. Обратите внимание: видеть контакты всех фрилансеров могут только владельцы ".$this->_link("/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "аккаунта PRO").".

Также  вы можете обратиться к ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "нашим менеджерам").", которые возьмут на себя все обязанности по подбору удаленных сотрудников."
);
        $sql = "
            SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u 
            INNER JOIN 
                projects p ON p.user_id = u.uid
            LEFT JOIN
                orders o ON o.from_id = u.uid 
            WHERE 
                p.billing_id IS NOT NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND p.create_date >= NOW() - interval '1 month' AND p.create_date < NOW()
            AND                 
                (o.payed IS NULL                
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
        ";
        $res2 = $this->_masssendSql($sql, $message, "empPubPrj30Days");
        return $res2; 
    }
    
    /**
     * Рассылка PRO  работодателям купившим рассылку в течение 30 дней
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function empProBuyMass30Days() {
        $message = $this->_template1(
"Мы обратили внимание на то, что вы пользовались услугой ".$this->_link("/masssending/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "&laquo;Рассылка по каталогу фрилансеров&raquo;").". Если вы еще не нашли исполнителей, мы рекомендуем вам ".$this->_link("/public/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "опубликовать проект или конкурс")." на Free-lance.ru. 

Если вы уже приняли решение о начале работы с конкретным фрилансером, напоминаем вам, что на Free-lance.ru есть сервис, обеспечивающий полную безопасность и надежность сотрудничества, – ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "&laquo;Безопасная Сделка&raquo;")."."
        );
        //PRO
        $sql = "
            SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u
            INNER JOIN 
                mass_sending p ON p.user_id = u.uid 
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE 
                u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND p.posted_time >= NOW() - interval '1 month' AND p.posted_time < NOW()
            AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)
        ";
        $res1 = $this->_masssendSql($sql, $message, "empBuyMass30Days");       
        return $res1;
    }
    
/**
     * Рассылка не PRO работодателям купившим рассылку в течение 30 дней
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function empNoProBuyMass30Days() {
        $message = $this->_template1(
"Мы обратили внимание на то, что вы пользовались услугой ".$this->_link("/masssending/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "&laquo;Рассылка по каталогу фрилансеров&raquo;").". Если вы еще не нашли исполнителей, мы рекомендуем вам ".$this->_link("/public/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "опубликовать проект или конкурс")." на Free-lance.ru. 

Если вы уже приняли решение о начале работы с конкретным фрилансером, напоминаем вам, что на Free-lance.ru есть сервис, обеспечивающий полную безопасность и надежность сотрудничества, – ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "&laquo;Безопасная Сделка&raquo;").".

Чтобы связаться с исполнителем напрямую, просмотрите его контактную информацию. Обратите внимание: видеть контакты всех фрилансеров могут только владельцы ".$this->_link("/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "аккаунта PRO").". Также профессиональный аккаунт дает и другие преимущества своим обладателям: скидки на все платные услуги, размещение в особой зоне каталога, бесплатное выделение вашего проекта в общей ленте и многое другое.
"
        );
        //no PRO
        $sql = "
            SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u
            INNER JOIN 
                mass_sending p ON p.user_id = u.uid 
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE 
                u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND p.posted_time >= NOW() - interval '1 month' AND p.posted_time < NOW()
            AND                 
                (o.payed IS NULL
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
        ";
        $res2 = $this->_masssendSql($sql, $message, "empBuyMass30Days");
        return $res2;
    }
    
    
    /**
     * @todo: отключено в hourly, при использовании исправить текст
     * 
     * Рассылка работодателям активным за 30 дней, но не публиковавшим проектов.
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function empNoProNotPubPrj() {
        //Запрос  Не ПРО работадателей
        $sql = "SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u
            LEFT JOIN 
                projects p ON p.user_id = u.uid
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE 
                p.id is NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND u.is_active = true
                AND                 
                (o.payed IS NULL                
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
            ";
        $messageNoPro = $this->_template1(
"Вы зарегистрированы на Free-lance.ru, однако еще не разместили ни одного проекта. К сожалению, обычно фрилансеры не доверяют работодателям без опубликованных проектов. Чтобы завоевать доверие у исполнителей, вам стоит начать ".$this->_link("/public/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "публиковать собственные проекты или конкурсы").".
Если вы нашли исполнителя в каталоге фрилансеров и хотите связаться с ним напрямую, просмотрите его контактную информацию. Обратите внимание: видеть контакты всех фрилансеров могут только владельцы ".$this->_link("/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "аккаунта PRO").". 
Мы рекомендуем всегда заключать ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "&laquo;Безопасную Сделку&raquo;")."  – так вы сможете обмениваться любой информацией и будете уверены в том, что ваш заказ будет выполнен точно в срок и в соответствии с техническим заданием. При сотрудничестве через «Безопасную Сделку» гонорар исполнителю выплачивается только после того, как вы примете результат работы.
Если у вас нет времени на поиск фрилансера для выполнения своих заказов, вы можете подбирать исполнителей при помощи ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "наших менеджеров").".
"        );
        $res2 = $this->_masssendSql($sql, $messageNoPro, "empNotPubPrj");
        return $res2;
    }


    /**
     * @todo: отключено в hourly, при использовании исправить текст
     * 
     * Рассылка PROработодателям активным за 30 дней, но не публиковавшим проектов.
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function empProNotPubPrj() {
        $messagePro = $this->_template1("Вы зарегистрированы на Free-lance.ru, однако еще не разместили ни одного проекта. К сожалению, обычно фрилансеры не доверяют работодателям без опубликованных проектов. Чтобы завоевать доверие у исполнителей, вам стоит начать ".$this->_link("/public/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "публиковать собственные проекты или конкурсы").".
Мы рекомендуем всегда заключать ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "&laquo;Безопасную Сделку&raquo;")."  – так вы сможете обмениваться любой информацией и будете уверены в том, что ваш заказ будет выполнен точно в срок и в соответствии с техническим заданием. При сотрудничестве через «Безопасную Сделку» гонорар исполнителю выплачивается только после того, как вы примете результат работы.
Если у вас нет времени на поиск фрилансера для выполнения своих заказов, вы можете подбирать исполнителей при помощи ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "наших менеджеров").".");
        //Запрос ПРО получателей
        $sql = "
            SELECT 
                u.uid 
            FROM 
                employer u
            LEFT JOIN 
                projects p ON p.user_id = u.uid
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE 
                p.id is NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND u.is_active = true
                AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)
        ";
        $res1 = $this->_masssendSql($sql, $messagePro, "empNotPubPrj");
        return $res1;
    }

    /**
     * Рассылка работодателям  у которых на счету есть 35+ бонусных FM.
     * 
     * @return integer  id сообщение или 0, если ошибка
     */
    public function empBonusFm() {
        $message = $this->_template1(
'На данный момент на вашем бонусном счете Free-lance.ru скопилась сумма в 1050 руб. Вы можете потратить бонусные рубли на оплату таких услуг как:<ul>
<li>поднятие опубликованного конкурса наверх ленты,</li>
<li>публикация платного проекта,</li>
<li>поднятие проекта наверх ленты проектов,</li></ul>
а также на ряд других платных сервисов сайта.

Также вы можете получить дополнительное количество ' . $this->_link('/help/?q=936&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=bonus_35', 'бонусных рублей') . ', участвуя в акциях от Free-lance.ru. Актуальную информацию о действующих акциях можно найти на странице вашего ' . $this->_link('/bill/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=bonus_35', 'личного счета') . '.'
        );
        $sql = "
            SELECT 
                u.uid 
            FROM 
                employer u 
            INNER JOIN 
                account a ON a.uid = u.uid 
            WHERE 
                u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND a.bonus_sum >= 35
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    
    
}

