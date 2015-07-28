<?php

/**
 * Базовый класс для всех API.
 */
abstract class externalApi extends externalBase {

    const OBJTYPE_TABLE = 1;
    
    const METHOD_PREFIX = 'x____';

    static $apis = array();
    
    protected $_sess;

    protected $_mName;
    protected $_mCfg;


    /**
     * Получает экземпляр необходимого API-класса в соотвествии с заданным пространством имен.
     *
     * @param string $ns   пространство имен (по-другому идентификатор класса API -- uri, см. схему), соотвествующее нужному классу.
     * @param externalSession $sess   объект сессии.
     * @return externalApi   инициализированный экземпляр.
     */
    static function getInst($ns, $sess) {
        $api = NULL;
        if(isset(externalApi::$apis[$ns]))
            $api = externalApi::$apis[$ns];
        if(!$api) {
            $api_name = basename($ns);
            $api_path = EXTERNAL_API_PATH . "/{$api_name}.php";
            if(file_exists($api_path)) {
                require_once($api_path);
                $api_cls = 'externalApi_'.$api_name;
                if(class_exists($api_cls)) {
                    $api = new $api_cls($sess);
                    // @here проверка прав доступа для закрытых api.
                    if($api->getNamespase() !== $ns)
                        unset($api);
                }
            }
        }
        if($api) {
            externalApi::$apis[$ns] = $api;
        }
        return $api;
    }

    /**
     * Главный конструктор.
     *
     * @param externalSession $sess   объект сессии.
     */
    function __construct($sess) {
        $this->_sess = $sess;
    }

    /**
     * Вернуть пространство имен (uri) данного API-класса.
     * @return string
     */
    function getNamespase() {
        return $this->API_NAMESPACE;
    }

    /**
     * Вернуть префикс пространства имен данного API-класса.
     * @return string
     */
    function getDefaultPrefix() {
        return $this->API_DEFAULT_PREFIX;
    }
    
    /**
     * Выполняет заданный метод. Если пространство имен имеет общие ограничения на свои методы, то выдается ошибка и NULL в результате.
     * @see externalApi::_methodsDenied()
     *
     * @param string $method   имя метода, согласно схеме протокола обмена (внутреннее имя отличается, добавляется префикс и т.д.)
     * @param array $args   параметры, которые нужно передать методу.
     * @return mixed    результат работы метода.
     */
    function invoke($method, $args) {
        $xmethod = self::METHOD_PREFIX.$method;
        if(!method_exists($this, $xmethod))
            return $this->warning( EXTERNAL_WARN_UNDEFINED_METHOD );
        $this->_mName = $method;
        $this->_mCfg = $this->_methodsCfg ? $this->_methodsCfg[$this->_mName] : NULL;
        $denied = false;
        if(!method_exists(__CLASS__, $xmethod))
            $denied = $this->_methodsDenied();
        return $denied ? NULL : $this->$xmethod($args);
    }


    /**
     * Заглушка для расшифровки переданного клиентом пароля.
     */
    private function _decriptPasswd($passwd) {
        return $passwd;
    }
    

    /**
     * Вызывается для проверки доступности авторизации данного пользователя.
     * Перегружается в отдельных пространствах имен, если те требуют доп. проверок.
     *
     * @param object $user   пользователь (инициализированный экземпляр класса users).
     * @return integer   код ошибки или 0 -- можно авторизировать.
     */
    protected function _authDenied($user) {
        if(!$user->uid)
            return EXTERNAL_ERR_USER_NOTFOUND;
        if($user->is_banned)
            return EXTERNAL_ERR_USER_BANNED;
        if($user->active != 't')
            return EXTERNAL_ERR_USER_NOTACTIVE;
        return 0;
    }

    /**
     * Вызывается перед каждым методом только внутри данного пространства имен (кроме методов externalApi) для 
     * проверки прав на вызов метода.
     * Доступны $this->_mName и $this->_mCfg.
     * Например, в методах freetray запрещены вызовы без авторизации, а также работодательским аккаунтам.
     *
     * @return integer   код ошибки или 0 -- метод разрешен.
     */
    protected function _methodsDenied() {
        return 0;
    }



    /////// external protocol public functions //////////////////////////////////////////


    /**
     * Тестовая фукнция.
     */
    protected function x____test($args)
    {
        list($arg) = $args;
        return $arg;
    }

    /**
     * Тестовая фукнция.
     */
    protected function x____testError($args)
    {
        list($err_code) = $args;
        $this->error( $err_code, 'You have been fucking testError()' );
    }

    /**
     * Тестовая фукнция.
     */
    protected function x____testWarning($args)
    {
        list($err_code) = $args;
        $this->warning( $err_code, 'You have been fucking testWarning()' );
    }

    /**
     * Авторизирует пользователя, в случае успеха инициализирует сессию.
     * Одна функция для всех пространств имен, т.е. можно авторизоваться из любого из них.
     * Каждое пространство ограничивает вход для разных типов пользовтелей, например, во фритрее доступ есть только у фрилансера.
     * Ограничения описываются в $this->_authDenied().
     * Note: может получится так, что обошли ограничения, вызвав auth() из другого пространства имен. Но в таком случае
     * методы (в ограниченном NS) все равно будут недоступны, если правильно описать $this->_methodsDenied().
     *
     * @param string $login   логин пользователя.
     * @param string $passwd   пароль пользователя в md5.
     * @return int   EXTERNAL_TRUE, если все ок.
     */
    final
    protected function x____auth($args)
    {
        list($login, $passwd) = $args;
        if(!isset($passwd) || !isset($login))
            $this->error( EXTERNAL_ERR_INVALID_METHOD_ARG, 'Use auth(string login, string passwd)' );

        require_once(ABS_PATH.'/classes/users.php');
        $user = new users();
        $user->GetUserByLoginPasswd($login, users::hashPasswd($this->_decriptPasswd($passwd), 1));
        if(!$user->uid)
            $this->error( EXTERNAL_ERR_WRONG_AUTH );
        if($err = $this->_authDenied($user))
            $this->error( $err );

        $this->_sess->fillU($user);

        return EXTERNAL_TRUE;
    }

    /**
     * Проверяет соотвествие пары логин/пароль.
     *
     * @param string $login   логин пользователя.
     * @param string $passwd   пароль пользователя в md5.
     * @return int   0:все ок; N:код ошибки.
     */
    final
    protected function x____checkAuth($args)
    {
        list($login, $passwd) = $args;
        if(!isset($passwd) || !isset($login))
            $this->error( EXTERNAL_ERR_INVALID_METHOD_ARG, 'Use checkAuth(string login, string passwd)' );

        require_once(ABS_PATH.'/classes/users.php');
        $user = new users();
        $user->GetUserByLoginPasswd($login, users::hashPasswd($this->_decriptPasswd($passwd), 1));
        return $this->_authDenied($user);
    }

    /**
     * Обновляет данные сессии.
     *
     * @return int   успешно?
     */
    final
    protected function x____refresh()
    {
        if(!$this->_sess->id)
            $this->error( EXTERNAL_ERR_NEED_AUTH );
        $this->_sess->refresh();
        return EXTERNAL_TRUE;
    }

    /**
     * Возвращает все коды ошибок с кратким описанием.
     *
     * @return array   коды ошибок.
     */
    protected function x____getErrCodes()
    {
        return $this->getErrCodes();
    }
    
    /**
     * Возвращает имена всех видимых данному API протокольных методов.
     *
     * @return array   имена методов.
     */
    protected function x____getMethods()
    {
        $mm = get_class_methods($this);
        $rm = array();
        foreach($mm as $m) {
            if(strpos($m, self::METHOD_PREFIX) === 0)
                $rm[] = preg_replace('/^'.self::METHOD_PREFIX.'(.*)$/', '$1', $m);
        }
        return $rm;
    }
}
