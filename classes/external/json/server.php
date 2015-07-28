<?php
/**
 * Сервер для работы с API через протокол JSON
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class externalServer_JSON extends externalBase {
    /**
     * Вызываемое апи
     * 
     * @var string 
     */
    private $_sApi;
    
    /**
     * Вызываемый метод
     * 
     * @var string 
     */
    private $_sMethod;
    
    /**
     * Параметры вызова
     * 
     * @var array
     */
    private $_aParams;
    
    /**
     * Номер ошибки
     * 
     * @var int 
     */
    private $_nErrorNum = EXTERNAL_NO_ERROR;
    
    /**
     * Возвращаемые данные
     * 
     * @var array 
     */
    private $_aRespData = array();


    /**
     * Имя метода, обрабатывающего ошибки
     * 
     * @var string 
     */
    public $eHandler = 'setError';
    
    /**
     * Имя метода, обрабатывающего предупреждения.
     * 
     * @var string 
     */
    public $wHandler = 'setError';

    /**
     * Возвращает экземпляр класса в зависимости от версии протокола.
     * 
     * @param array $req   параметры запроса:
     *                       'protocol-version' => 1.0, -- версия протокола
     *                       'data' => file_get_contents('php://input') -- тело запроса.
     * @return object
     */
    static function getInst( $req ) {
        if ( 1 == (int)$req['protocol-version'] )
            return new externalServer_JSON( $req['data'] );
    }
    
    /**
     * Конструктор класса
     * 
     * @param string $json   текст JSON-запроса
     */
    function __construct( $json ) {
        $this->regErrorHandler();
        $this->regWarnHandler();
        $aParams = json_decode( $json, true );
        
        if ( is_array($aParams) && $aParams ) {
            if ( $aParams['api'] ) {
                $this->_sApi = $aParams['api'];
                unset($aParams['api']);
            }

            if ( $aParams['method'] ) {
                $this->_sMethod = $aParams['method'];
                unset($aParams['method']);
            }
            
            $this->_aParams = $aParams;
        }
    }
    
    /**
     * Обработка запроса, отправка ответа клиенту.
     */
    function handle() {
        $sName = 'http://www.free-lance.ru/external/api/' . $this->_sApi;
        
        if ( $api = externalApi::getInst($sName, $this->_sess) ) {
            $this->_aRespData = $api->invoke( $this->_sMethod, $this->_aParams );
            $this->response();
        }
        else {
            $this->error( EXTERNAL_WARN_UNDEFINED_API );
        }
    }
    
    /**
     * Регистрирует ошибку, заканчивает работу.
     * 
     * @param mixed $err описание ошибки (массив -- структурированное описание).
     */
    function setError( $err ) {
        $this->_nErrorNum = $err['code'];
        $this->_aRespData = array();
        $this->response();
    }
    
    /**
     * Формирует ответ, заканчивает работу.
     */
    function response() {
        $aResult = array( 
            'error'      => $this->_nErrorNum, 
            'error_text' => self::$_aError[$this->_nErrorNum], 
            'data'       => $this->_aRespData
        );
        
        die( json_encode($aResult, empty($this->_aRespData) ? JSON_FORCE_OBJECT : 0) );
    }
}