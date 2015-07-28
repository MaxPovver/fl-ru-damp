<?php

define('EXTERNAL_TRUE',  1);
define('EXTERNAL_FALSE', 0);
define('EXTERNAL_DT_STRING', 1);
define('EXTERNAL_DT_BOOL', 2);
define('EXTERNAL_DT_TIME', 3);

define('EXTERNAL_WARN_UNDEFINED_API', 10);
define('EXTERNAL_WARN_UNDEFINED_METHOD', 11);
define('EXTERNAL_WARN_UNDEFINED_TABLE', 12);

define('EXTERNAL_ERR_INVALID_METHOD_ARG', 100);
define('EXTERNAL_ERR_NEED_AUTH', 101);
define('EXTERNAL_ERR_WRONG_AUTH', 102);
define('EXTERNAL_ERR_USER_BANNED', 103);
define('EXTERNAL_ERR_USER_NOTACTIVE', 104);
define('EXTERNAL_ERR_SESSION_EXPIRED', 105);
define('EXTERNAL_ERR_USER_NOTFOUND', 106);
define('EXTERNAL_ERR_ONLYFRL', 107);
define('EXTERNAL_ERR_ONLYPRO', 108);
define('EXTERNAL_ERR_EMP_ONLY', 109);

define('EXTERNAL_ERR_WRONG_REQ', 1000);
define('EXTERNAL_ERR_SERVER_CLOSED', 1001);
define('EXTERNAL_ERR_DB_CONNECT', 1002);
define('EXTERNAL_ERR_BAD_ENCODING', 1003);
define('EXTERNAL_ERR_MEMCACHE', 1004);
define('EXTERNAL_ERR_SERVER_ERROR', 1005);

// API мобильного приложения
define( 'EXTERNAL_NO_ERROR',              0 );
define( 'EXTERNAL_ERR_EMPTY_UDID',        2001 );
define( 'EXTERNAL_ERR_EMPTY_AGENT',       2002 );
define( 'EXTERNAL_ERR_INVALID_AGENT',     2003 );
define( 'EXTERNAL_ERR_EMPTY_USERNAME',    2004 );
define( 'EXTERNAL_ERR_EMPTY_PASSWORD',    2005 );
define( 'EXTERNAL_ERR_USER_DENYIP',       2006 );
define( 'EXTERNAL_ERR_INVALID_USERNAME',  2007 );
define( 'EXTERNAL_ERR_ILLEGAL_USERNAME',  2008 );
define( 'EXTERNAL_ERR_EXISTS_USERNAME',   2009 );
define( 'EXTERNAL_ERR_INVALID_EMAIL',     2010 );
define( 'EXTERNAL_ERR_ILLEGAL_EMAIL',     2011 );
define( 'EXTERNAL_ERR_EXISTS_EMAIL',      2012 );
define( 'EXTERNAL_ERR_LENGTH_PASSWORD',   2013 );
define( 'EXTERNAL_ERR_INVALID_PASSWORD',  2014 );
define( 'EXTERNAL_ERR_EMPTY_PHONE',       2015 );
define( 'EXTERNAL_ERR_MISMATCH_PHONE',    2016 );
define( 'EXTERNAL_ERR_EMPTY_ROLE',        2017 );
define( 'EXTERNAL_ERR_INVALID_ROLE',      2018 );
define( 'EXTERNAL_ERR_EXCEED_MAX_REG_IP', 2019 );
define( 'EXTERNAL_ERR_SEND_SMS',          2020 );
define( 'EXTERNAL_ERR_INVALID_SMS_CODE',  2021 );
define( 'EXTERNAL_ERR_USER_ACTIVATED',    2022 );
define( 'EXTERNAL_ERR_EXISTS_PHONE',      2023 );
define( 'EXTERNAL_ERR_PHONE_NOT_FOUND',   2024 );
define( 'EXTERNAL_ERR_REMIND_PHONE_ONLY', 2025 );
define( 'EXTERNAL_ERR_EMPTY_PROJECT_ID',  2026 );
define( 'EXTERNAL_ERR_PROJECT_NOT_FOUND', 2027 );
define( 'EXTERNAL_ERR_PRJ_COST_MIN',      2028 );
define( 'EXTERNAL_ERR_PRJ_COST_MAX',      2029 );
define( 'EXTERNAL_ERR_PRJ_CURRENCY',      2030 );
define( 'EXTERNAL_ERR_PRJ_EMPTY_DESCR',   2031 );
define( 'EXTERNAL_ERR_PRJ_EMPTY_TITLE',   2032 );
define( 'EXTERNAL_ERR_PRJ_LENGTH_DESCR',  2033 );
define( 'EXTERNAL_ERR_FILE',              2034 );
define( 'EXTERNAL_ERR_MAX_FILES_CONUT',   2035 );
define( 'EXTERNAL_ERR_MAX_FILES_SIZE',    2036 );
define( 'EXTERNAL_ERR_FILE_FORMAT',       2037 );
define( 'EXTERNAL_ERR_OFFER_NOT_FOUND',   2038 );
define( 'EXTERNAL_ERR_OWNER',             2039 );
define( 'EXTERNAL_ERR_PRJ_SELECTED',      2040 );
define( 'EXTERNAL_ERR_EMPTY_USER_ID',     2041 );
define( 'EXTERNAL_ERR_EMPTY_MESSAGE',     2042 );
define( 'EXTERNAL_ERR_LENGTH_MESSAGE',    2043 );
define( 'EXTERNAL_ERR_SELF_MESSAGE',      2044 );
define( 'EXTERNAL_ERR_MESSAGE_IGNOR',     2045 );
define( 'EXTERNAL_ERR_FAVORITES_IN',      2046 );
define( 'EXTERNAL_ERR_FAVORITES_NOT_IN',  2047 );
define( 'EXTERNAL_ERR_EMPTY_COUNTRY',     2048 );
define( 'EXTERNAL_ERR_EMPTY_CITY',        2049 );
define( 'EXTERNAL_ERR_EMPTY_BIRTHDAY',    2050 );
define( 'EXTERNAL_ERR_INVALID_BIRTHDAY',  2051 );
define( 'EXTERNAL_ERR_EMPTY_FIRSTNAME',   2052 );
define( 'EXTERNAL_ERR_INVALID_FIRSTNAME', 2053 );
define( 'EXTERNAL_ERR_EMPTY_LASTNAME',    2054 );
define( 'EXTERNAL_ERR_INVALID_LASTNAME',  2055 );
define( 'EXTERNAL_ERR_OFFER_SPEC',        2056 );
define( 'EXTERNAL_ERR_MISMATCH_USERNAME', 2057 );
define( 'EXTERNAL_ERR_FIELDS_REQUIRED',   2058 );
define( 'EXTERNAL_ERR_EMPTY_PROF_ID',     2059 );
define( 'EXTERNAL_ERR_PROF_ID_LAST_MOD',  2060 );

define('EXTERNAL_CLS_PATH', dirname(__FILE__));
define('EXTERNAL_API_PATH', EXTERNAL_CLS_PATH.'/api');


require_once(ABS_PATH.'/classes/memBuff.php');
require_once(ABS_PATH.'/classes/log.php');
require_once(EXTERNAL_CLS_PATH . '/session.php');
require_once(EXTERNAL_API_PATH . '/api.php');


/**
 * Точка входа и родительский класс для всех серверов-обработчиков внешних запросов к API.
 * Выдает нужный сервер в зависимости от переданных клиентом параметров.
 * Содержит общие функции и хранит общие данные.
 */
abstract class externalBase {

    static private $errcodes = array (
        EXTERNAL_WARN_UNDEFINED_API     => 'Неизвестное пространство имен',
        EXTERNAL_WARN_UNDEFINED_METHOD  => 'Неизвестная функция',
        EXTERNAL_WARN_UNDEFINED_TABLE   => 'Таблицы не существует',
        EXTERNAL_ERR_INVALID_METHOD_ARG => 'Пропущен аргумент',
        EXTERNAL_ERR_NEED_AUTH          => 'Функция требует авторизации',
        EXTERNAL_ERR_WRONG_AUTH         => 'Неверная пара логин/пароль',
        EXTERNAL_ERR_USER_BANNED        => 'Пользователь заблокирован',
        EXTERNAL_ERR_USER_NOTACTIVE     => 'Аккаунт не активирован',
        EXTERNAL_ERR_USER_NOTFOUND      => 'Пользователь не найден',
        EXTERNAL_ERR_ONLYFRL            => 'Сервис доступен только для фрилансера',
        EXTERNAL_ERR_ONLYPRO            => 'Сервис доступен только для аккаунта PRO',
        EXTERNAL_ERR_EMP_ONLY           => 'Сервис доступен только для работодателей',
        EXTERNAL_ERR_SESSION_EXPIRED    => 'Сессия устарела',
        EXTERNAL_ERR_WRONG_REQ          => 'Ошибочный запрос',
        EXTERNAL_ERR_SERVER_CLOSED      => 'Сервер временно недоступен',
        EXTERNAL_ERR_DB_CONNECT         => 'Ошибка базы данных',
        EXTERNAL_ERR_BAD_ENCODING       => 'Кодировка не поддерживается',
        EXTERNAL_ERR_MEMCACHE           => 'Ошибка сервера',
        EXTERNAL_ERR_SERVER_ERROR       => 'Ошибка сервера',
        EXTERNAL_ERR_EMPTY_UDID         => 'Не указан параметр - Уникальный индификатор устройства',
        EXTERNAL_ERR_EMPTY_AGENT        => 'Не указан параметр - Тип устройства',
        EXTERNAL_ERR_INVALID_AGENT      => 'Ошибочный параметр - Тип устройства',
        EXTERNAL_ERR_EMPTY_USERNAME     => 'Необходимо указать логин',
        EXTERNAL_ERR_USER_DENYIP        => 'Текущий IP адрес не соответствует установленному в настройках безопасности пользователя',
        EXTERNAL_ERR_INVALID_USERNAME   => 'От 3 до 15 символов. Может содержать латинские буквы, цифры, подчёркивание (_) и дефис (-)',
        EXTERNAL_ERR_ILLEGAL_USERNAME   => 'Извините, такой логин использовать нельзя',
        EXTERNAL_ERR_EXISTS_USERNAME    => 'Извините, такой логин уже существует',
        EXTERNAL_ERR_INVALID_EMAIL      => 'Поле E-mail заполнено некорректно',
        EXTERNAL_ERR_ILLEGAL_EMAIL      => 'К сожалению, регистрация аккаунта на указанный адрес электронной почты невозможна. Пожалуйста, для регистрации воспользуйтесь почтовым адресом другого домена',
        EXTERNAL_ERR_EXISTS_EMAIL       => 'Указанная вами электронная почта уже зарегистрирована. Авторизуйтесь на сайте или укажите другую электронную почту',
        EXTERNAL_ERR_EMPTY_PASSWORD     => 'Введите пароль',
        EXTERNAL_ERR_LENGTH_PASSWORD    => 'Пароль должен содержать от 6 до 20 символов',
        EXTERNAL_ERR_INVALID_PASSWORD   => 'Поле Пароль заполнено некорректно',
        EXTERNAL_ERR_EMPTY_PHONE        => 'Необходимо ввести номер телефона',
        EXTERNAL_ERR_MISMATCH_PHONE     => 'Вы подтвердили не этот номер',
        EXTERNAL_ERR_EMPTY_ROLE         => 'Не указан параметр - Роль пользователя',
        EXTERNAL_ERR_INVALID_ROLE       => 'Ошибочный параметр - Роль пользователя',
        EXTERNAL_ERR_EXCEED_MAX_REG_IP  => 'Превышено количество регистраций с одного IP',
        EXTERNAL_ERR_SEND_SMS           => 'Не удалось отправить сообщение. Попробуйте через несколько минут',
        EXTERNAL_ERR_INVALID_SMS_CODE   => 'Неверный код',
        EXTERNAL_ERR_USER_ACTIVATED     => 'Пользователь уже активирован',
        EXTERNAL_ERR_EXISTS_PHONE       => 'Пользователь с таким номером уже зарегистрирован',
        EXTERNAL_ERR_PHONE_NOT_FOUND    => 'Этот логин не связан ни с одним номером телефона',
        EXTERNAL_ERR_REMIND_PHONE_ONLY  => 'Восстановить доступ к аккаунту возможно только через телефон',
        EXTERNAL_ERR_EMPTY_PROJECT_ID   => 'Не указан параметр - ID проекта',
        EXTERNAL_ERR_PROJECT_NOT_FOUND  => 'Проект с данным ID не найден',
        EXTERNAL_ERR_PRJ_COST_MIN       => 'Введите положительную сумму',
        EXTERNAL_ERR_PRJ_COST_MAX       => 'Слишком большая сумма',
        EXTERNAL_ERR_PRJ_CURRENCY       => 'Валюта не определена',
        EXTERNAL_ERR_PRJ_EMPTY_DESCR    => 'Не указан параметр - Описание проекта',
        EXTERNAL_ERR_PRJ_EMPTY_TITLE    => 'Не указан параметр - Название проекта',
        EXTERNAL_ERR_PRJ_LENGTH_DESCR   => 'Исчерпан лимит символов',
        EXTERNAL_ERR_FILE               => 'Ошибка загрузки файла',
        EXTERNAL_ERR_MAX_FILES_CONUT    => 'Превышено максимальное количество файлов',
        EXTERNAL_ERR_MAX_FILES_SIZE     => 'Превышен максимальный объем файлов',
        EXTERNAL_ERR_FILE_FORMAT        => 'Недопустимый формат файла',
        EXTERNAL_ERR_OFFER_NOT_FOUND    => 'Не найдена запись предложения к проекту с таким ID',
        EXTERNAL_ERR_OWNER              => 'Нельзя редактировать чужие записи',
        EXTERNAL_ERR_PRJ_SELECTED       => 'Неверное значение - Признак',
        EXTERNAL_ERR_EMPTY_USER_ID      => 'Не указан параметр - ID пользователя',
        EXTERNAL_ERR_EMPTY_MESSAGE      => 'Не указан параметр - text',
        EXTERNAL_ERR_LENGTH_MESSAGE     => 'Вы ввели слишком большое сообщение. Текст сообщения не должен превышать 20 000 символов',
        EXTERNAL_ERR_SELF_MESSAGE       => 'Вы не можете отправить сообщение самому себе',
        EXTERNAL_ERR_MESSAGE_IGNOR      => 'Пользователь запретил отправлять ему сообщения',
        EXTERNAL_ERR_FAVORITES_IN       => 'Пользователь уже в избранном',
        EXTERNAL_ERR_FAVORITES_NOT_IN   => 'Пользователь не в избранном',
        EXTERNAL_ERR_EMPTY_COUNTRY      => 'Выберите страну',
        EXTERNAL_ERR_EMPTY_CITY         => 'Выберите город',
        EXTERNAL_ERR_EMPTY_BIRTHDAY     => 'Заполните дату дня рождения',
        EXTERNAL_ERR_INVALID_BIRTHDAY   => 'Укажите корректную дату дня рождения',
        EXTERNAL_ERR_EMPTY_FIRSTNAME    => 'Укажите имя',
        EXTERNAL_ERR_INVALID_FIRSTNAME  => 'Имя заполнено некорректно',
        EXTERNAL_ERR_EMPTY_LASTNAME     => 'Укажите фамилию',
        EXTERNAL_ERR_INVALID_LASTNAME   => 'Фамилия заполнено некорректно',
        EXTERNAL_ERR_OFFER_SPEC         => 'Вы не можете ответить на этот проект, так как он не соответствует вашей специализации',
        EXTERNAL_ERR_MISMATCH_USERNAME  => 'Вы указали не этот логин при регистрации',
        EXTERNAL_ERR_FIELDS_REQUIRED    => 'Нужно заполнить необходимые при регистрации поля',
        EXTERNAL_ERR_EMPTY_PROF_ID      => 'Не указан параметр ID профессии',
        EXTERNAL_ERR_PROF_ID_LAST_MOD   => 'Не прошло 30 дней с момента последней смены специализации',
    );
    
    /**
     * Коды ошибок для мобильного приложения
     * 
     * @var array 
     */
    static protected $_aError = array(
        EXTERNAL_NO_ERROR               => 'NO_ERROR',
        EXTERNAL_ERR_SERVER_CLOSED      => 'ERROR_SERVER_CLOSED',
        EXTERNAL_ERR_NEED_AUTH          => 'ERROR_USER_NO_AUTH',
        EXTERNAL_WARN_UNDEFINED_API     => 'ERROR_BAD_API',
        EXTERNAL_WARN_UNDEFINED_METHOD  => 'ERROR_BAD_METHOD',
        EXTERNAL_ERR_SERVER_ERROR       => 'ERROR_SERVER',
        EXTERNAL_ERR_EMPTY_UDID         => 'ERROR_EMPTY_UDID',
        EXTERNAL_ERR_EMPTY_AGENT        => 'ERROR_EMPTY_AGENT',
        EXTERNAL_ERR_INVALID_AGENT      => 'ERROR_INVALID_AGENT',
        EXTERNAL_ERR_EMPTY_USERNAME     => 'ERROR_EMPTY_USERNAME',
        EXTERNAL_ERR_EMPTY_PASSWORD     => 'ERROR_EMPTY_PASSWORD',
        EXTERNAL_ERR_WRONG_AUTH         => 'ERROR_INVALID_PASSWORD',
        EXTERNAL_ERR_USER_BANNED        => 'ERROR_USER_BAN',
        EXTERNAL_ERR_USER_NOTACTIVE     => 'ERROR_USER_INACTIVE',
        EXTERNAL_ERR_USER_DENYIP        => 'ERROR_USER_DENYIP',
        EXTERNAL_ERR_INVALID_USERNAME   => 'ERROR_INVALID_USERNAME',
        EXTERNAL_ERR_ILLEGAL_USERNAME   => 'ERROR_ILLEGAL_USERNAME',
        EXTERNAL_ERR_EXISTS_USERNAME    => 'ERROR_EXISTS_USERNAME',
        EXTERNAL_ERR_INVALID_EMAIL      => 'ERROR_INVALID_EMAIL',
        EXTERNAL_ERR_ILLEGAL_EMAIL      => 'ERROR_ILLEGAL_EMAIL',
        EXTERNAL_ERR_EXISTS_EMAIL       => 'ERROR_EXISTS_EMAIL',
        EXTERNAL_ERR_LENGTH_PASSWORD    => 'ERROR_LENGTH_PASSWORD',
        EXTERNAL_ERR_INVALID_PASSWORD   => 'ERROR_INVALID_PASSWORD',
        EXTERNAL_ERR_EMPTY_PHONE        => 'ERROR_EMPTY_PHONE',
        EXTERNAL_ERR_MISMATCH_PHONE     => 'ERROR_MISMATCH_PHONE',
        EXTERNAL_ERR_EMPTY_ROLE         => 'ERROR_EMPTY_ROLE',
        EXTERNAL_ERR_INVALID_ROLE       => 'ERROR_INVALID_ROLE',
        EXTERNAL_ERR_EXCEED_MAX_REG_IP  => 'ERROR_EXCEED_MAX_REG_IP',
        EXTERNAL_ERR_SEND_SMS           => 'ERROR_SEND_SMS',
        EXTERNAL_ERR_USER_NOTFOUND      => 'ERROR_USER_NOT_FOUND',
        EXTERNAL_ERR_INVALID_SMS_CODE   => 'ERROR_INVALID_CODE',
        EXTERNAL_ERR_USER_ACTIVATED     => 'ERROR_USER_ACTIVATED',
        EXTERNAL_ERR_EXISTS_PHONE       => 'ERROR_EXISTS_PHONE',
        EXTERNAL_ERR_PHONE_NOT_FOUND    => 'ERROR_PHONE_NOT_FOUND',
        EXTERNAL_ERR_REMIND_PHONE_ONLY  => 'ERROR_REMIND_PHONE_ONLY',
        EXTERNAL_ERR_EMPTY_PROJECT_ID   => 'ERROR_EMPTY_PROJECT_ID',
        EXTERNAL_ERR_PROJECT_NOT_FOUND  => 'ERROR_PROJECT_NOT_FOUND',
        EXTERNAL_ERR_EMP_ONLY           => 'ERROR_USER_NOT_CUSTOMER',
        EXTERNAL_ERR_ONLYFRL            => 'ERROR_USER_NOT_PERFORMER',
        EXTERNAL_ERR_PRJ_COST_MIN       => 'ERROR_PROJECT_COST_MIN',
        EXTERNAL_ERR_PRJ_COST_MAX       => 'ERROR_PROJECT_COST_MAX',
        EXTERNAL_ERR_PRJ_CURRENCY       => 'ERROR_INVALID_PROJECT_CURRENCY',
        EXTERNAL_ERR_PRJ_EMPTY_DESCR    => 'ERROR_EMPTY_PROJECT_DESCR',
        EXTERNAL_ERR_PRJ_EMPTY_TITLE    => 'ERROR_EMPTY_PROJECT_TITLE',
        EXTERNAL_ERR_PRJ_LENGTH_DESCR   => 'ERROR_PROJECT_LENGTH_DESCR',
        EXTERNAL_ERR_FILE               => 'ERROR_FILE_UPLOAD',
        EXTERNAL_ERR_FILE               => 'ERROR_FILE_UPLOAD',
        EXTERNAL_ERR_MAX_FILES_CONUT    => 'ERROR_MAX_FILES_CONUT',
        EXTERNAL_ERR_MAX_FILES_SIZE     => 'ERROR_MAX_FILES_SIZE',
        EXTERNAL_ERR_FILE_FORMAT        => 'ERROR_FILE_FORMAT',
        EXTERNAL_ERR_OFFER_NOT_FOUND    => 'ERROR_NOT_FOUND_PROJECT_RESPONSE',
        EXTERNAL_ERR_OWNER              => 'ERROR_OWNER',
        EXTERNAL_ERR_PRJ_SELECTED       => 'ERROR_INVALID_PROJECT_RESPONSE_SELECT',
        EXTERNAL_ERR_EMPTY_USER_ID      => 'ERROR_EMPTY_USER_ID',
        EXTERNAL_ERR_EMPTY_MESSAGE      => 'ERROR_EMPTY_MESSAGE_TEXT',
        EXTERNAL_ERR_LENGTH_MESSAGE     => 'ERROR_MESSAGE_LENGTH',
        EXTERNAL_ERR_SELF_MESSAGE       => 'ERROR_MESSAGE_SELF',
        EXTERNAL_ERR_MESSAGE_IGNOR      => 'ERROR_MESSAGE_IGNOR',
        EXTERNAL_ERR_FAVORITES_IN       => 'ERROR_FAVORITES_IN',
        EXTERNAL_ERR_FAVORITES_NOT_IN   => 'ERROR_FAVORITES_NOT_IN',
        EXTERNAL_ERR_EMPTY_COUNTRY      => 'ERROR_EMPTY_COUNTRY',
        EXTERNAL_ERR_EMPTY_CITY         => 'ERROR_EMPTY_CITY',
        EXTERNAL_ERR_EMPTY_BIRTHDAY     => 'ERROR_EMPTY_BIRTHDAY',
        EXTERNAL_ERR_INVALID_BIRTHDAY   => 'ERROR_INVALID_BIRTHDAY',
        EXTERNAL_ERR_EMPTY_FIRSTNAME    => 'ERROR_EMPTY_FIRSTNAME',
        EXTERNAL_ERR_INVALID_FIRSTNAME  => 'ERROR_INVALID_FIRSTNAME',
        EXTERNAL_ERR_EMPTY_LASTNAME     => 'ERROR_EMPTY_LASTNAME',
        EXTERNAL_ERR_INVALID_LASTNAME   => 'ERROR_INVALID_LASTNAME',
        EXTERNAL_ERR_OFFER_SPEC         => 'ERROR_OFFER_SPECIALIZATION',
        EXTERNAL_ERR_MISMATCH_USERNAME  => 'ERROR_MISMATCH_USERNAME',
        EXTERNAL_ERR_FIELDS_REQUIRED    => 'ERROR_FIELDS_REQUIRED',
        EXTERNAL_ERR_EMPTY_PROF_ID      => 'ERROR_EMPTY_PROF_ID',
        EXTERNAL_ERR_PROF_ID_LAST_MOD   => 'ERROR_PROF_ID_LAST_MOD',
    );

    static private $errHandler;
    static private $warnHandler;
    static private $errContext;

    static private $_isRunned;

    /**
     * Получает класс сервера по типу запроса и стартует обработку.
     *
     * @param array $req   параметры запроса:
     *                       'type' => 'xml', -- тип сервера
     *                       'protocol-version' => 1.0, -- версия протокола (для xml)
     *                       'data' => file_get_contents('php://input') -- тело запроса.
     */
    final static function run($req) {
        if(!externalBase::$_isRunned) {
            externalBase::$_isRunned = TRUE;
            require_once(EXTERNAL_CLS_PATH.'/'.$req['type'].'/server.php');
            if($server = call_user_func(array('externalServer_'.$req['type'], 'getInst'), $req)) {
                if(defined('IS_CLOSED') && IS_CLOSED)
                    $server->error( EXTERNAL_ERR_SERVER_CLOSED );
                $server->handle();
            }
        }
    }


    /**
     * Регистрирует функцию, принимающую ошибки от всех рабочих объектов.
     */
    function regErrorHandler() {
        if($this->eHandler)
            externalBase::$errHandler = array($this, $this->eHandler);
    }

    /**
     * Регистрирует функцию, принимающую варнинги от всех рабочих объектов.
     */
    function regWarnHandler() {
        if($this->wHandler)
            externalBase::$warnHandler = array($this, $this->wHandler);
    }

    /**
     * Фиксирует данные о контексте события в случае возникновения ошибко или предупреждений.
     *
     * @param mixed $context   описание контекста.
     */
    function errorSetContext($context) {
        externalBase::$errContext = $context;
    }

    /**
     * Возвращает коды ошибок и предупреждений.
     */
    function getErrCodes() {
        return externalBase::$errcodes;
    }

    /**
     * Функция для приема ошибок и передачи обработчку в нужном формате.
     *
     * @param int $code   код ошибки.
     * @param mixed $debug_info   дополнительная информация.
     */
    protected function error($code, $debug_info = NULL) {
        if(externalBase::$errHandler) {
            $err = $this->_createErr($code, $debug_info);
            call_user_func(externalBase::$errHandler, $err);
        } else {
            die($code);
        }
    }

    /**
     * Функция для приема варнингов и передачи обработчку в нужном формате.
     *
     * @param int $code   код.
     * @param mixed $debug_info   дополнительная информация.
     */
    protected function warning($code, $debug_info = NULL) {
        if(externalBase::$warnHandler) {
            $err = $this->_createErr($code, $debug_info);
            call_user_func(externalBase::$warnHandler, $err);
        }
    }

    /**
     * Формирует ошибку в соотвествии с кодом, контекстом и отладочной информацией.
     *
     * @param int $code   код.
     * @param mixed $debug_info   дополнительная информация.
     * @return array   описание ошибки.
     */
    private function _createErr($code, $debug_info = NULL) {
        $err = array('code'=>$code, 'message'=>externalBase::$errcodes[$code]);
        if($debug_info)
            $err['debug'] = $debug_info;
        if(externalBase::$errContext)
            $err['context'] = externalBase::$errContext;
        return $err;
    }

    /**
     * Преобразует тип данных постгреса в соотвествующий EXTERNAL-тип.
     *
     * @param string $val   значение
     * @param int $dt   тип данных
     * @return mixed   значение, приведенное к EXTERNAL-типу
     */
    function pg2ex($val, $dt) {
        switch($dt) {
            case EXTERNAL_DT_BOOL :
                return $val=='t' ? EXTERNAL_TRUE : EXTERNAL_FALSE;
            case EXTERNAL_DT_TIME :
                return strtotime($val);
        }
        return $val;
    }

    /**
     * Формирует строку (массив) данных на основе исходного массива и перечня необходимых полей
     * Приводит значения элементов массива к EXTERNAL-типу.
     *
     * @param array $fields   поля, которые нужно вернуть в строке.
     * @param array $data   исходный массив.
     * @return array   преобразованный массив.
     */
    function pg2exRow($fields, $data) {
        $row = array();
        foreach($fields as $f=>$dt)
            $row[$f] = $this->pg2ex($data[$f], $dt);
        return $row;
    }

    /**
     * Преобразует EXTERNAL-типа в соотвествующий постгресу тип.
     *
     * @param string $val   значение
     * @param int $dt   тип данных
     * @return mixed   значение, пригодное для использования в postgreSql-запросах.
     */
    function ex2pg($val, $dt = EXTERNAL_DT_STRING) {
        switch($dt) {
            case EXTERNAL_DT_BOOL :
                if(strtolower($val)==='true') return 't';
                if(strtolower($val)==='false') return 'f';
                return (int)$val>0 ? 't' : 'f';
        }
        return pg_escape_string($val);
    }

}

// stdf

/**
 * Проверяет, является ли массив простым индексным массивом.
 * @param array $arr   исходный массив.
 * @return boolean   вектор?
 */
function is_vector($arr) {
    $kk = array_keys($arr);
    return ($kk === array_keys($kk));
}
