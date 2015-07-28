<?php

/**
 * —ервер дл€ работы с API через протокол XML.
 */
class externalServer_XML extends externalBase {

    const XML_RES_ROOT_TAG    = 'server';
    const XML_RES_CALL_TAG    = 'call-r';
    const XML_REQ_ROOT_TAG    = 'client';
    const XML_REQ_CALL_TAG    = 'call';
    const XML_VAR_PRM_TAG     = 'v-p';
    const XML_VAR_RES_TAG     = 'v-r';
    const XML_VAR_ANY_TAG     = 'v-x';
    const XML_VAR_ITM_TAG     = 'v';
    const XML_VAR_ITM_KEY_ATT = 'k';
    const XML_METHOD_U_ATT    = 'u';

    private $_reqXmlStr;
    private $_clientXml;
    private $_responseXmlns = array();
    private $_responseErr = NULL;
    private $_responseWarns = array();
    private $_responseBody = '';
    private $_sess;
    private $_debug = 0;
    private $_clientVersion;

    public $eHandler = 'setError';
    public $wHandler = 'setWarning';

    /**
     * ¬озвращает экземпл€р класса в зависимости от версии протокола.
     * @param array $req   параметры запроса:
     *                       'protocol-version' => 1.0, -- верси€ протокола
     *                       'data' => file_get_contents('php://input') -- тело запроса.
     * @return object
     */
    static function getInst($req) {
        if( 1 == (int)$req['protocol-version'] )
            return new externalServer_XML($req['data']);
    }
    
    /**
     *  онструктор. 
     * @param string $xml   текст XML-запроса
     */
    function __construct($xml) {
        $this->regErrorHandler();
        $this->regWarnHandler();
        $this->saveLog($xml);
        $this->_reqXml = $xml;
    }
    
    function saveLog($xml) {
    // !!!
        // @todo ¬от не знаю стоит ли тут делать так, или все же легче вызвать self::initReqXml(); но там у нас сесси€ инициализируетс€
        $this->_clientXml = new DOMDocument();
        libxml_use_internal_errors(true);
        if(!$this->_clientXml->loadXML($xml)) {
            $this->_debug = 1;
            $xe_levels = array(LIBXML_ERR_WARNING=>'WARNING', LIBXML_ERR_ERROR=>'ERROR', LIBXML_ERR_FATAL=>'FATAL');
            foreach(libxml_get_errors() as $xe)
                $err .= $xe_levels[$xe->level].": (line: {$xe->line}, column: {$xe->column}): {$xe->message}";
            libxml_clear_errors();
            $this->error( EXTERNAL_ERR_WRONG_REQ, $err );
        }
        $ns_name = basename($this->_clientXml->documentElement->getAttribute('xmlns:f'));
        if($ns_name == '') $ns_name = basename($this->_clientXml->documentElement->getAttribute('xmlns:hh'));
        else $ns_name = "freetray";
        if($ns_name == '') $ns_name = "other";
        $log = new log("external/{$ns_name}-%d%m%Y.log");
        $log->writeln('--------------'.getRemoteIP().'--------------');
        $log->writeln($xml);    
    }

    /**
     * »нициализаци€, предобработка, валидаци€ XML-документа. «агружает xml-запрос в DOM. ƒелает проверки, устанавливает флаги инициализирует сессию.
     * @param string $xml   текст запроса
     */
    function initReqXml() {
        $err = '';
        $root = NULL;
        $this->_clientXml = new DOMDocument();
        libxml_use_internal_errors(true);


        // xml verification...
        if(!$this->_clientXml->loadXML($this->_reqXml)) {
            $this->_debug = 1;
            $xe_levels = array(LIBXML_ERR_WARNING=>'WARNING', LIBXML_ERR_ERROR=>'ERROR', LIBXML_ERR_FATAL=>'FATAL');
            foreach(libxml_get_errors() as $xe)
                $err .= $xe_levels[$xe->level].": (line: {$xe->line}, column: {$xe->column}): {$xe->message}";
            libxml_clear_errors();
            $this->error( EXTERNAL_ERR_WRONG_REQ, $err );
        }

        $root = $this->_clientXml->documentElement;

        // flags...
        $this->_debug  = $root->getAttribute('debug');
        $this->checkClientVersion($root->getAttribute('v'));

        // encoding...
        if($this->_clientXml->encoding != 'windows-1251')
            $this->error( EXTERNAL_ERR_BAD_ENCODING, 'XML encoding must be "windows-1251"' );

        // schema validations...
        if($root->localName != self::XML_REQ_ROOT_TAG) {
            $this->error( EXTERNAL_ERR_WRONG_REQ, 'Ќеверный тег документа' );
        }

        // init session...
        $this->_sess = new externalSession($root->getAttribute('sid'));
    }
    

    /**
     * –азбирает атрибут client/@v -- верси€ клиентского приложени€.
     * @param string $cv   значение атрибута (например, "Freetray 6.7.8 (Windows)").
     */
    function checkClientVersion($cv) {
        if(!$cv) return;
        $cv = explode(' ', $cv);
        if(strtolower($cv[0]) == 'freetray') {
            if(stripos($cv[2], 'windows')!==false)
                $lcv = FLTRAY_WIN_VERSION;
            else if(stripos($cv[2], 'mac')!==false)
                $lcv = FLTRAY_MAC_VERSION;
            else if(stripos($cv[2], 'linux')!==false)
                $lcv = FLTRAY_LINUX_VERSION;
            if($lcv && $lcv != $cv[1])
                $this->_clientVersion = $lcv;
        }
    }

    /**
     * ќбработка валидного XML-документа, отправка ответа клиенту.
     */
    function handle() {
        $this->initReqXml();
        foreach($this->_clientXml->documentElement->childNodes as $t)
            $this->handleTag($t);
        $this->response();
    }

    /**
     * ќбработка xml-тега внутри корн€.
     * @param DOMNode $t   узел (тег)
     */
    function handleTag($t) {
        if($t->localName == self::XML_REQ_CALL_TAG) {
            $this->_responseBody .= '<'.self::XML_RES_CALL_TAG.'>';
            $methods = $t->childNodes;
            foreach($methods as $m) {
                if($m->nodeType != XML_ELEMENT_NODE) continue;
                $this->handleMethod($m);
            }
            $this->_responseBody .= '</'.self::XML_RES_CALL_TAG.'>';
        }
    }                            

    /**
     * ќбработка метода. »нициализирует api, соотвествующее пространству имен данного метода,
     * делает вызов, формирует тег результата.
     *
     * @param DOMNode $m   тег метода.
     */
    function handleMethod($m) {
        static $pos = 1;
        $name = $m->localName;
        $this->errorSetContext( array('tag'=>$name, 'ns'=>$m->namespaceURI, 'pos'=>$pos) );
        if($api = externalApi::getInst($m->namespaceURI, $this->_sess)) {
            $args = NULL;
            foreach($m->childNodes as $pr) {
                if($pr->localName == self::XML_VAR_PRM_TAG)
                    $args[] = $this->varXml2Php($pr);
            }
            $result = $api->invoke($name, $args);
            if($u = $m->getAttribute(self::XML_METHOD_U_ATT))
                 $uatt =  ' '.self::XML_METHOD_U_ATT.'="'.$u.'"';
            $xml_prefix = $api->getDefaultPrefix();
            $this->_responseBody .= "<{$xml_prefix}:{$name}{$uatt}>".$this->varPhp2Xml($result, self::XML_VAR_RES_TAG)."</{$xml_prefix}:{$name}>";
            $this->_responseXmlns[$xml_prefix] = $m->namespaceURI;
        }
        else {
            $this->warning(EXTERNAL_WARN_UNDEFINED_API);
        }
        $this->errorSetContext( NULL );
        $pos++;
    }

    /**
     * Ѕуфер ошибок. –егистрирует ошибку, заканчивает работу.
     * @param mixed $err   описание ошибки (массив -- структурированное описание).
     */
    function setError($err) {
        if(!$this->_debug && isset($err['debug']))
            unset($err['debug']);
           
        $this->_responseBody = '';
        $this->_responseErr = $err;
        $this->response();
    }

    /**
     * –егистрирует предупреждение.
     * @param mixed $warn   описание сообщени€ (массив -- структурированное описание).
     */
    function setWarning($warn) {
        if(!$this->_debug) return;
        $this->_responseWarns[] = $warn;
    }

    /**
     * ‘ормирует ответ, заканчивает работу.
     */
    function response() {
        $xmlns=$sess=$err=$warns = '';
        if($this->_sess && $this->_sess->is_updated)
            $sess = '<sess>'.$this->varPhp2Xml($this->_sess->public).'</sess>';
        foreach($this->_responseXmlns as $pfx=>$uri)
            $xmlns .= " xmlns:{$pfx}=\"{$uri}\"";
        foreach($this->_responseWarns as $warn)
            $warns .= '<warn>'.$this->varPhp2Xml($warn).'</warn>';
        if($this->_responseErr)
            $err = '<err>'.$this->varPhp2Xml($this->_responseErr).'</err>';
        if($this->_clientVersion)
            $cv = ' client-v="'.$this->_clientVersion.'"';

        die(
          '<?xml version="1.0" encoding="windows-1251"?>' .
          '<' . self::XML_RES_ROOT_TAG . $xmlns . $cv . '>' .
             $sess .
             $err .
             $warns .
             $this->_responseBody .
          '</' . self::XML_RES_ROOT_TAG . '>'
        );
    }


    /**
     * ѕреобразует xml-переменную в php-переменную.
     * @param DOMNode $vnode   контейнер сореджимого переменной (<v-p>, <v-r>, <v-x>)
     * @return mixed   значение переменной.
     */
    function varXml2Php($vnode) {
        $pv = NULL;
        if($vnode->childNodes) {
            foreach($vnode->childNodes as $vi) {
                if($vi->nodeType == XML_ELEMENT_NODE && $vi->localName==self::XML_VAR_ITM_TAG) {
                    $t = $this->varXml2Php($vi);
                    if(($key = $vi->getAttribute(self::XML_VAR_ITM_KEY_ATT)) !== '')
                        $pv[$key] = $t;
                    else
                        $pv[] = $t;
                }
            }
            if(!$pv)
                $pv = iconv('UTF-8', 'CP1251//IGNORE', $vnode->textContent); // !!!
        }
        return $pv;
    }


    /**
     * ѕреобразует php-переменную в xml-переменную.
     * @param mixed $pv   значение переменной.
     * @param string $vtype   тип контейнера переменной (<v-p>, <v-r>, <v-x>, <v>)
     * @param string $key   индекс элемента, если переменна€ находитс€ внутри массива. »спользуетс€ только с self::XML_VAR_ITM_TAG
     * @return string   xml-текст переменной.
     */
    function varPhp2Xml($pv, $vtype = self::XML_VAR_ANY_TAG, $key = NULL) {
        $katt = $key!==NULL ? ' '.self::XML_VAR_ITM_KEY_ATT.'="'.$key.'"' : '';
        $xv = "<{$vtype}{$katt}>";
        if(is_array($pv)) {
            $no_key = is_vector($pv);
            foreach($pv as $k=>$vi) {
                $xv .= $this->varPhp2Xml($vi, self::XML_VAR_ITM_TAG, $no_key ? NULL : $k);
            }
        }
        else {
            $pv = preg_replace('/[\x00-\x08\x0B-\x1F\x7F-\x9F]/', '', $pv);
            $xv .= preg_match('/[&><]/', $pv) ? '<![CDATA['.$pv.']]>' : $pv;
        }
        $xv .= "</{$vtype}>";
        return $xv;
    }
}
