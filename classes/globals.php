<?php
/**
 * Файл глобальных перменных которые используются с системе
 */
	
    /**
     * @global integer размер битового поля для "роли" юзера
     */
    $rolesize = 6;
    /**
     * @global размер битового поля для закаладок юзера
     * @todo: стоит указать что только фрилансера?
     */
    $tabsize = 8;
    /**
     * @global integer размер битового поля для "подписки" юзера на рассылки
     */
    $subscrsize = 16;
    /**
     * @global integer размер битового поля blocks в таблице freelancer, означающий какие блоки инфы показывать, а какие нет.
     */
    $blockssize = 8;
    /**
     * @global integer
     * @todo   Не нашел где используется в системе
     */
    $lgflagssize = 2;
    /**
     * @global integer кол-во сообщений в диалоге в "контактах"
     */
    $msgspp = 41;
    /**
     * @global integer кол-во проектов на страницу (на главной)
     */
    $prjspp = 30;
    /**
     * @global integer кол-во тем в блогах на страницу
     */
    $blogspp = 20;	
    /**
     * @global integer кол-во фрилансеров на страницу в каталоге
     *
     */
    define("FRL_PP", 40);
    /**
     * @global integer кол-во работодателей на страницу в каталоге
     *
     */
    define("EMP_PP", 30);
    /**
     * @global integer кол-во работ на страницу в каталоге
     *
     */
    define("PRF_PP", 30);
    /**
     * @global integer максимум работ в портфолио для одной категории
     */
    $prjs_pu = 1000;
    /**
     * @global string битовая маска для обычного фрилансера
     */
    $frlmask = '000000';
    /**
     * @global string битовая маска для обычного работодателя
     */
    $empmask = '100000';	
    /**
     * @global string битовая маска для админа
     */
    $adminmask = '000100';	
    /**
     * @global string битовая маска для модератора
     */
    $modermask = '010000';	
    /**
     * @global string битовая маска для редактора
     */
    $redactormask = '001000';
    /**
     * @global string длинна текста
     * @todo Где используется не нашел
     */
    $textlength = '500';	
    /**
     * @global integer ограничение на размер папки для аплода - щаз снято
     */
    $upload_dir_size = 104857600;
    if (!isset($rpath)) $rpath = "../";
    /**
     * @global integer  страничка с сообщением "неверный пароль"
     */
    $wrong_pass = "wrongpass.php";
    /**
     * @global resource коннект к БД
     */
    $connection = false;

    // Когда здесь добавляются/убиваются расширения надо это продублировать
    // в js-функцию checkext() (должна лежать в /warning.js).
    /**
     * @global array массив форматов файлов "картинок"
     */
    $graf_array = array("gif", "jpg", "jpeg", "png", "swf");
    /**
     * @global array массив форматов обычных файлов
     */
    $file_array = array("zip", "rar", "mp3", "doc", "docx", "psd", "pdf", "xls", "xlsx", "rtf", "txt", "bmp");	
    /**
     * @global array массив форматов файлов "видео"
     */
    $video_array = array("avi","flv","mp4","3gp","wmv","mpeg","mpg");
    /**
     * @global array  массив форматов файлов "аудио"
     */
    $audio_array = array("wma","ogg","wav");
    
    /**
     * @global array массив со списком запрещенных типов файлов.
     * 
     * Когда здесь добавляются/убиваются расширения надо это продублировать
     * в js-функцию allowedExt (должна лежать в /warning.js).
     */    
    $disallowed_array = array( "ade", "adp", "bat", "chm", "cmd", "com", "cpl", "exe",
        "hta", "ins", "isp", "jse", "lib", "mde", "msc", "msp",
        "mst", "pif", "scr", "sct", "shb", "sys", "vb", "vbe",
        "vbs", "vxd", "wsc", "wsf", "wsh" );
    
    /**
     * @global integer максимальное кол-во попыток залогинивания
     */
    $max_login_tries = 15;	
    /**
     * @global integer время бана
     */
    $login_wait_time = 5;	

    
    /**
     * @global integer максимальный размер аудио
     */
    $maxpw_audio = 1048576 * 2;	
    /**
     * @global integer максимальный размер видео
     */
    $maxpw_video = 1048576 * 2; 
    
    /**
     * @global integer процент за СбР
     */
    $norisk_service_prc = 10; 

    /**
     * @global array логины наших пользователей на боевой, убираем из каталога
     */
    $ourUserLogins = array('vvvv', 'test-freelance', 'CheGevara2', 'kim-test-2', 'comedie1', 'comedie3', 'comedie5', 'testuser', 'vagavr');

    /**
     * @global array логины пользователей запрещенные для регистрации
     */
    $disallowUserLogins = array(
                                'ms',
                                'saint-petersburg',
                                'kdar',
                                'ekburg',
                                'nsibirsk',
                                'nizhnov',
                                'sm',
                                'rost-don',
                                'vlstok',
                                'khrsk',
                                'chbinsk',
                                'kryarsk',
                                'kazn',
                                'irktsk',
                                'uf',
                                'srtov',
                                'ulyansk',
                                'pm',
                                'kemvo',
                                'vorzh',
                                'stapol',
                                'om',
                                'tmsk',
                                'rzan',
                                'vlgrad',
                                'brnl',
                                'h-m',
                                'tm',
                                'orburg',
                                'izhvsk',
                                'yavl',
                                'kgrad',
                                'tla',
                                'vdmir',
                                'kv',
                                'odssa',
                                'khkov',
                                'dtsk',
                                'd-p',
                                'lv',
                                'pt',
                                'lg',
                                'chrk',
                                'vinnca',
                                'zprozhie',
                                'nklv',
                                'sm-ua',
                                'khmeln',
                                'i-f',
                                'kirovgrad',
                                'rovno',
                                'hrson',
                                'zhtomir',
                                'ternopol',
                                'chernovcy',
                                'chrnigov',
                                'lck',
                                'mnsk',
                                'gmel',
                                'grdno',
                                'brst',
                                'almaaty',
                                'shimkent',
                                'trz',
                                'astna'
                               );
    
    /**
     * @global array логины пользователей которых нельзя игнорировать в сообщениях
     * 
     */
    $usersNotBeIgnored = array('administrator', 'moderation', 'moderator', 'norisk', 'admin');
    
    /**
     * @global array ид. пользователей, которые являются лчными менеджерами
     */
    $aPmUserUids = array(519633, 419427, 543545);

    /**
     * @global array логины наших пользователей на боевой, оставляем в каталоге
     */
    $ourUserLoginsInCatalog = array('clients', 'fmanager'); // при изменении скорректировать индекс "ix employer/catalog"
    
    /**
     * @global array массив UID пользователей, сообщения от которых в личку не нуждаются в модерировании
     */
    if ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE) ) { // тестовые
        if ( SERVER == 'local' || SERVER == 'beta' ) {
            $aContactsNoMod = array( 237871, 142409, 53791, 103 );
        }
        else {
            $aContactsNoMod = array( 235515, 226893, 53791, 142409, 419427, 103 );
        }
    }
    else { // боевой
        $aContactsNoMod = array( 142409, 419427, 543545, 103, 235515, 226893, 53791 );
    }
    
    /**
     * @global array список доменов, ссылки на которые не заворачиваются на страницу a.php, а идут напрямую
     * @see reformat_callback($matches)
     * @see _wysiwygLinkDecodeCallback($matches)
     */
    $white_list = array( 'hh.ru', 'fl.ru', 'free-lance.ru', 'dizkon.ru' );

    /**
     * @global boolean полностью отключает заворачивание ссылок через страницу a.php
     * @see reformat_callback($matches)
     * @see _wysiwygLinkDecodeCallback($matches)
     */
    $disable_link_processing = false;
    
    /**
     * @global браузеры плохо работающие с JS
     */
    $JSProblemBrowser = array('Opera Mini'=>'/Opera\sMini\//');
    
    /**
     * @global Статусы пользователей @see /search/
     */
    $status_users = array(
	  "<div class='b-page__desktop b-page__ipad'>Свободен</div><div class='b-page__iphone'>С<br>в<br>о<br>б<br>о<br>д<br>е<br>н</div>",
	  "<div class='b-page__desktop b-page__ipad'>Занят</div><div class='b-page__iphone'>З<br>а<br>н<br>я<br>т</div>",
	  "<div class='b-page__desktop b-page__ipad'>Отсутствую</div><div class='b-page__iphone'>О<br>т<br>с<br>у<br>т<br>с<br>т<br>в<br>у<br>ю</div>",
	  -1=>"<div class='b-page__desktop b-page__ipad'>Без статуса</div><div class='b-page__iphone'>Б<br>е<br>з<br> с<br>т<br>а<br>т<br>у<br>с<br>а</div>"
	  );
    
    /**
     * @global boolean можно ли использовать спец. форматирование для вывода гипер-ссылок (см. stdf.php, reformat())
     *
     */
    define('HYPER_LINKS', TRUE);

    /**
     * @global integer Время между обновлениями скрипта уведомлений (личные сообщения, сбр)
     */
    define('NOTIFICATION_DELAY', 300000);
    
    /**
     * @global integer Время между обновлениями статуса в проектах новых сообщений в миллисекундах. (10 мин)
     */
    define('PRJ_CHECK_DELAY', 600000);
    
    /**
     * @global integer ид. юзера, для которого запрещены переводы денег, подарки.
     */
    define('SPEC_USER', 12245);

    /**
     * @global string   имя экземпляра класса links. Должно использоваться если модуль поддерживает спец. обработку ссылок.
     * @see links
     */
    define('LINK_INSTANCE_NAME', '___iLinks___');

    define('VALENTIN_DATE_BEGIN', 'Feb 14, 2012 00:00:00');
    define('VALENTIN_DATE_END', 'Feb 15, 2012 00:00:00');
    
    if ( !defined('COOKIE_SECURE') ) {
        define('COOKIE_SECURE', isset($_SERVER['HTTP_NGINX_HTTPS']));
    }
    
    $allow_love = (strtotime(VALENTIN_DATE_BEGIN) < time() && strtotime(VALENTIN_DATE_END) > time());
    
    /**
     * @global array email адреса отделов обратной связи темы писем отдельно для тестовых и боевого серверов
     * @see smail::FeedbackPost
     */
    if ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE) ) { // тестовые
        $aFeedbackPost = array(
            1 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => 'Вопрос по сервисам сайта, обратная связь' ),
            2 => array( 'email' => 'helpdesk_beta_3@free-lance.ru', 'subj' => 'Ошибки на сайте, обратная связь' ),
            3 => array( 'email' => 'helpdesk_beta_2@free-lance.ru', 'subj' => 'Финансовый вопрос, обратная связь' ),
            4 => null, // занято личным менеджером
            5 => array( 'email' => 'helpdesk_beta_5@free-lance.ru', 'subj' => '«Безопасная Сделка»' ),
            6 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => 'Отправить жалобу руководству на работу модераторов и обратной связи, обратная связь' ),
            7 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => 'Ваши предложения по улучшению нашего сайта, обратная связь' ),
            8 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => 'Жалоба на обман со стороны пользователя' ),
            9 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => 'Вопрос по теме «Подбор фрилансеров»' ),
            10 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => 'Вопрос по теме «Реклама»' ),
            11 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => 'Проблема с регистрацией или авторизацией на сайте' )
        );
    }
    else { // боевой
        $aFeedbackPost = array(
            1 => array( 'email' => 'info@free-lance.ru',    'subj' => 'Вопрос по сервисам сайта, обратная связь' ),
            2 => array( 'email' => 'tester@free-lance.ru',  'subj' => 'Ошибки на сайте, обратная связь' ),
            3 => array( 'email' => 'finance@free-lance.ru', 'subj' => 'Финансовый вопрос, обратная связь' ),
            4 => null, // занято личным менеджером
            5 => array( 'email' => 'norisk@free-lance.ru',  'subj' => '«Безопасная Сделка»' ),
            6 => array( 'email' => 'info@free-lance.ru',    'subj' => 'Отправить жалобу руководству на работу модераторов и обратной связи, обратная связь' ),
            7 => array( 'email' => 'info@free-lance.ru',    'subj' => 'Ваши предложения по улучшению нашего сайта, обратная связь' ),
            8 => array( 'email' => 'help@free-lance.ru',    'subj' => 'Жалоба на обман со стороны пользователя' ),
            9 => array( 'email' => 'manager@free-lance.ru', 'subj' => 'Вопрос по теме «Подбор фрилансеров»' ),
            10 => array( 'email' => 'adv@free-lance.ru', 'subj' => 'Вопрос по теме «Реклама»' ),
            11 => array( 'email' => 'info@free-lance.ru',    'subj' => 'Проблема с регистрацией или авторизацией на сайте' )
        );
    }
    
    /**
     * @global email адрес личного менеджера (Подбор фрилансеров)
     */
    $sManagerEmail = ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE) ) ? 'helpdesk_beta_4@free-lance.ru' : 'manager@free-lance.ru';
    
    /**
     * @global string дополнение к файлу логотипа
     */
    $logoAddition = (date('dm') === "0104" || date('dmH') === "300317") ? "1april" : '';
    
    
    /**
     * @global integer минимальный период между регистрациями посещений сайта юзером при GET-запросах.
     */
    define('VISIT_GET_UPDATE_PERIOD', 300);
    
    /**
     * @global integer минимальный период между регистрациями посещений сайта юзером при POST-запросах.
     */
    define('VISIT_POST_UPDATE_PERIOD', 60);
    
    /**
     * @global string адреса, переход по которым не нужно считать за посещение.
     */
    $GLOBALS['VISIT_IGNORED_URI'] = '~(?:notification\.php|iframe_[^.]+\.php|xajax/blocks\.server\.php|kword_js\.php)~';
    
    /**
     * @global array админы которые могут менять счет пользователя
     */
    $GLOBALS['balanceCanChangeAdmins'] = array('administrator', 'pppiu');
    
    /**
     * @global string ключ мемкеш для данных кросс-доменной авторизации 
     */
    define(CROSSDOMAINAUTH_KEY_NAME, 'CROSSDOMAINAUTH_');

    
    /**
     * Строка на которую бедт заменяться запрещенный контент в сообщениях пользователей
     */
    define( 'CENSORED', '[Запрещенная информация]' );
    

    define('HOME', realpath(__DIR__.'/../'));
    
