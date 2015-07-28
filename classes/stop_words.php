<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );

/**
 * Работа с подозрительными словами и запрещенными выражениями
 *
 * @author Max 'BlackHawk' Yastrembovich
 * @todo несколько раз все менялось, но лишний код не чистился. так что некоторые методы могут не работать
 */
class stop_words {    
    /**
     * Шаблон подсветки стоп-слова для модератора
     */
    const STOP_WORDS = '<span style="font-weight:bold; color: #cc4642;">$1</span>';
    
    /**
     * Шаблон подсветки запрещенных выражений для модератора
     */
    const STOP_REGEX = '<span style="color: #cc4642;">[скрыто: %s]</span>';
    
    /**
     * Шаблон подсветки запрещенных выражений для модератора без HTML
     */
    const STOP_REGEX_PLAIN = '[скрыто: %s]';
    
    /**
     * Код для замены ссылок
     */
    const STOP_LINK_CODE   = 'MY_SW_L';
    
    /**
     * Код для замены запрещенных выражений
     */
    const STOP_REGEX_CODE  = 'MY_SW_C';


    /**
     * Допустимые значения $site
     * 
     * @var array
     */
    static $site_allow = array( 'words', 'regex' );
    
    /**
     * Отображение запрещенного контента в режиме администратора
     * 
     * @var bool
     */
    private $admin_mode = false;
    
    /**
     * Режим замены: 'html', 'plain'
     * 
     * @var string
     */
    private $replace_mode = 'html';
    
    /**
     * Список слов для подсветки подозрительного содержания в модерировании сообщений
     * 
     * @var array 
     */
    private $stop_words = array();
    
    /**
     * список запрещенных выражений в сообщениях пользователей
     * 
     * @var array 
     */
    private $stop_regex = array(
        '#([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}#i', // email
        '#([a-zа-яёА-ЯЁ0-9_-]+\.)*[a-zа-яёА-ЯЁ0-9_-]+@[a-zа-яёА-ЯЁ0-9_-]+(\.[a-zа-яёА-ЯЁ0-9_-]+)*\.(рф|rf)#i', // email с кирилицей и доменом рф
        '#\+?(?:[1-9](?:(?:[\(\)]*\d){10,12})|0(?:[\(\)]*\d){9})#', // мобильные телефоны
        '#\d{3}-\d{2}-\d{2}#U', //'#(?:[ \-\(\)]*\d+){7}#', // городские телефоны
        '#[GREZUBYCD]+[\d]{12}#i' // кошельки вебмани
    );
    
    /**
     * список запрещенных выражений для профилей пользователей.
     * применяется даже если запись не находится на модерировании,
     * поэтому тут только сто процентные шаблоны в отличие от $stop_regex
     * 
     * @var array 
     */
    private $profile_stop_regex = array(
        '#([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}#i', // email
        '#([a-zа-яёА-ЯЁ0-9_-]+\.)*[a-zа-яёА-ЯЁ0-9_-]+@[a-zа-яёА-ЯЁ0-9_-]+(\.[a-zа-яёА-ЯЁ0-9_-]+)*\.(рф|rf)#i', // email с кирилицей и доменом рф
        '#\b[GREZUBYCD]+[\d]{12}\b#i' // кошельки вебмани
    );
    
    /**
     * список запрещенных выражений для раздела админки suspicious_contacts Прошерстить профили юзеров на наличие контактов
     * самые параноидальные - подозревают все подряд
     * 
     * @var array
     */
    private $suspect_stop_regex = array(
        '~(?:[a-z0-9_-]+\.)*[a-z0-9_-]+\s*(@|#)\s*[a-z0-9_-]+(?:\.[a-z0-9_-]+)*\s*\.\s*[a-z]{2,4}~i', 
        '~([a-zа-яёА-ЯЁ0-9_-]+\.)*[a-zа-яёА-ЯЁ0-9_-]+\s*(@|#)\s*[a-zа-яёА-ЯЁ0-9_-]+(\.[a-zа-яёА-ЯЁ0-9_-]+)*\s*\.\s*(рф|rf)~i', 
        '#\+?(?:[\(\)\s-]*\d){5,}#', 
        '#\d{3}-\d{2}-\d{2}#', 
        '#(?i)[GREZUBYCD]+[\d]{12}#', 
        '#\d{16}#', 
        '#\d{4}\s+\d{4}\s+\d{4}\s+\d{4}#', 
        '#4100\d{10}#'
    );
    
    /**
     * Настройки таблицы подозрительных слов. Имя таблицы.
     * 
     * @var string
     */
    private static $stop_words_table = 'user_content_stop_words';
    
    /**
     * Настройки таблицы подозрительных слов. Тип - слова
     * 
     * @var int
     */
    private static $stop_words_words = 1;
    
    /**
     * Настройки таблицы подозрительных слов. Тип - регулярные выражения
     * 
     * @var int
     */
    private static $stop_words_regex = 2;
    
    /**
     * Режим подсчета стоп слов. Префикс набора регулярок
     * 
     * @var string 
     */
    private $calculate_regex_prefix  = 'suspect';
    
    /**
     * Режим подсчета стоп слов. Нужно ли считать стоп слова
     * 
     * @var bool 
     */
    private $calculate_words         = true;
    
    /**
     * Массив ссылок для замены и восстановления в _linkReplace и _linkRestore
     * 
     * @var array 
     */
    private $links                   = array();
    
    /**
     * Массив скрытого для замены и восстановления в _callbackReplace и _restore
     * 
     * @var array 
     */
    private $censored                = array();
    
    /**
     * Конструктор класса
     * 
     * @param bool $admin_mode отображение запрещенного контента в режиме администратора
     */
    function __construct( $admin_mode = false ) {
        $this->admin_mode = $admin_mode;
        
        // больше не берем из базы - захардкодили выше
        //$this->stop_regex = $this->getAdminStopRegex();
        
        $this->stop_words = $this->getAdminStopWords();

        $this->_prepareStopWords();
        $this->_prepareStopRegex();
    }

    /**
     * Устанавливает переменную класса для подозрительных слов
     * 
     * @param  string $sWords текст со словами через запятую, или нулл если взять из базы
     */
    function setStopWords( $sWords = null ) {
        $this->stop_words = array();
        
        if ( is_null($sWords) ) {
            $this->stop_words = $this->getAdminStopWords();
        }
        elseif ( !empty($sWords) ) {
            $this->stop_words = explode(',', $sWords);
            $this->stop_words = array_map( 'trim', $this->stop_words );
            $this->stop_words = array_unique( $this->stop_words );
        }
        
        $this->_prepareStopWords();
    }
    
    /**
     * Вспомогательная функция для подготовки подозрительных слов
     */
    private function _prepareStopWords() {
        if ( $this->stop_words ) {
            usort( $this->stop_words, array('stop_words', '_cmp_len') );

            $this->stop_words = array_map( array('stop_words', '_pattern'), $this->stop_words );
        }
    }
    
    /**
     * Вспомогательная функция для подготовки подозрительных слов
     */
    function _cmp_len($a, $b) {
        return strlen($a) > strlen($b) ? -1 : ( strlen($a) < strlen($b) ? 1 : 0 );
    }
    
    /**
     * Вспомогательная функция для подготовки подозрительных слов
     */
    function _pattern($s) {
        return '/('. preg_quote($s, '/') .')/i';
    }
    
    /**
     * Устанавливает переменную класса для подозрительных слов
     * 
     * @param  string $sRegex текст с выражениями: одна строка - одно выражение
     */
    function setStopRegex( $sRegex = null ) {
        $this->stop_regex = array();
        
        if ( is_null($sRegex) ) {
            $this->stop_regex = $this->getAdminStopRegex();
        }
        elseif ( !empty($sRegex) ) {
            $this->stop_regex = explode("\n", $sRegex);
            $this->stop_regex = array_map( 'trim', $this->stop_regex );
            $this->stop_regex = array_unique( $this->stop_regex );
        }
        
        $this->_prepareStopRegex();
    }

    /**
     * Вспомогательная функция для подготовки запрещенных выражений
     */
    private function _prepareStopRegex() {
        $aRegex = array( 'stop_regex', 'profile_stop_regex', 'suspect_stop_regex' );
        foreach ( $aRegex as $sFld ) {
            if ( $this->$sFld ) {
                $aTmp = $this->$sFld;
                foreach ( $aTmp as $sKey => $sVal ) {
                    $sFirst  = preg_quote( $sVal[0], '/');
                    $sSearch = '/^(['. $sFirst .']{1})(.+)(['. $sFirst .']{1})([\w]*)?$/';
                    $aTmp[$sKey] = preg_replace( $sSearch, '$1($2)$3$4', $sVal );
                }
                
                $this->$sFld = $aTmp;
            }
        }
    }

    /**
     * Заменяет стоп слова в тексте.
     * 
     * В режиме администратора Подозрительные слова и Запрещенные выражения
     * подсвечиваются жирным и цветом.
     * В режиме пользователя Запрещенные выражения заменяются на CENSORED (@see globals.php)
     * 
     * @param  string $sText исходный текст
     * @param  string $replace_mode режим замены: 'html', 'plain'
     * @param  bool $admin_mode опционально. установить режим администратора, отличный от $this->admin_mode
     * @return string
     */
    function replace( $sText = '', $replace_mode = 'html', $admin_mode = null, $regex_prefix = '' ) {
        return $sText;

        if ( !empty($sText) ) {
            setlocale(LC_ALL, 'ru_RU.CP1251');
            
            $sRegexFld  = $regex_prefix . '_stop_regex';
            $aStopRegex = ( $regex_prefix && isset($this->$sRegexFld) ) ? $this->$sRegexFld : $this->stop_regex;
            $admin_mode = is_null( $admin_mode ) ? $this->admin_mode : $admin_mode;
            $this->replace_mode = $replace_mode;
            
            // обрабатываем ссылки отдельно чтобы больше не попадать внутрь них подсветками
            $sText = $this->_linkReplace( $sText );
            
            if ( $admin_mode ) {
                if( $aStopRegex ) {
                    $sText = preg_replace_callback( $aStopRegex, array('stop_words', '_callbackReplace'), $sText );
                }
                
                if ( $this->stop_words && $replace_mode == 'html' ) {
                    $sText = preg_replace( $this->stop_words, self::STOP_WORDS, $sText );
                }
                
                $sText = $this->_restore( $sText, self::STOP_REGEX_CODE, $this->censored );
            }
            elseif( $aStopRegex ) {
                $sText = preg_replace( $aStopRegex, CENSORED, $sText );
            }
            
            $sText = $this->_linkRestore( $sText );
            
            setlocale(LC_ALL, 'en_US.UTF-8');
        }
        
        return $sText;
    }
    
    /**
     * Устанавливает режим подсчета стоп слов на одно выполнение: регулярки стандартне, стоп слова не считать
     */
    function calculateRegexNoWords() {
        $this->calculate_regex_prefix = '';
        $this->calculate_words        = false;
    }
    
    /**
     * Подсчитывает стоп слова в тексте/текстах.
     * Можно передавать параметрами сразу несколько переменных
     * Переменные могут содержать массивы строк.
     * 
     * После подсчета сбрасывает режим подсчета в значения по умолчанию.
     * 
     * @return int общее количество стоп слов
     */
    function calculate() {
        $nRet  = 0;
        $aText = func_get_args();
        
        if ( $aText ) {
            foreach ( $aText as $mText ) {
                if ( is_array($mText) && $mText ) {
                    foreach ( $mText as $sText ) {
                        $nRet += $this->_calculate( $sText );
                    }
                }
                else {
                    $nRet += $this->_calculate( $mText );
                }
            }
        }
        
        $this->calculate_regex_prefix = 'suspect';
        $this->calculate_words        = true;
        
        return $nRet;
    }
    
    /**
     * Подсчитывает стоп слова в тексте
     * 
     * @param  string $sText текст
     * @param  string $regex_prefix опционально. префикс набора регулярок. по умолчанию suspect самые параноидальные
     * @return int количество стоп слов в тексте
     */
    function _calculate( $sText = '', $regex_prefix = 'suspect', $calc_words = true ) {
        $nReg  = 0;
        $nStop = 0;
        
        if ( is_string($sText) && !empty($sText) ) {
            $sRegexFld  = $this->calculate_regex_prefix . '_stop_regex';
            $aStopRegex = ( $this->calculate_regex_prefix && isset($this->$sRegexFld) ) ? $this->$sRegexFld : $this->stop_regex;
            
            // обрабатываем ссылки - что внутри них не считается
            $sText = $this->_linkReplace( $sText );
            $sText = preg_replace( $aStopRegex, CENSORED, $sText, -1, $nReg );
            
            if ( $this->calculate_words && $this->stop_words ) {
                $sText = preg_replace( $this->stop_words, CENSORED, $sText, -1, $nStop );
            }
        }
        
        return $nReg + $nStop;
    }
    
    /**
     * Вспомогательная функция для замены запрещенных выражений
     * Обработка ссылок. Заменяет ссылки на коды
     * 
     * @param  string $sText исходный текст
     * @return string 
     */
    private function _linkReplace( $sText = '' ) {
        $this->links = array();
        
        if ( !empty ($sText) ) {
            setlocale(LC_ALL, 'ru_RU.CP1251');
            
            $hre      = HYPER_LINKS ? '{([^}]+)}' : '()'; // отключается в globals.php.
            $sPattern = '~(https?:/(' . $hre . ')?/|www\.)(([\da-z-_а-яёА-ЯЁ]+\.)*([\da-z-_]+|рф|РФ)(:\d+)?([/?#][^"\s<]*)*)~i';
            $sText = preg_replace_callback( $sPattern, array('stop_words', '_callbackLinkReplace'), $sText );
            
            setlocale(LC_ALL, 'en_US.UTF-8');
        }
        
        return $sText;
    }
    
    /**
     * Вспомогательная функция для замены запрещенных выражений
     * Обработка ссылок. Возвращает ссылки
     * 
     * @param string $sText
     */
    private function _linkRestore( $sText = '' ) {
        $sReturn = '';
        
        if ( !empty($sText) ) {
            $aParts = explode( self::STOP_LINK_CODE, $sText );
            $j      = 0;
            
            for ( $i = 0; $i < count($aParts); $i++ ) {
                $sReturn .= $aParts[$i];
                
                if ( !empty($this->links[$j]) ) {
                    $sReturn .= $this->links[$j];
                    $j++;
                }
            }
        }
        
        return $sReturn;
    }
    
    /**
     * Вспомогательная функция для замены запрещенных выражений
     * Обработка ссылок
     * 
     * @param  array $matches
     * @return string 
     */
    function _callbackLinkReplace( $matches ) {
        $this->links[] = $matches[0];
        
        return self::STOP_LINK_CODE;
    }
    
    /**
     * Вспомогательная функция для замены запрещенных выражений
     * Заменяет кода на сохраненную информаию
     * 
     * @param  string $sText текст
     * @param  string $sCode код
     * @param  array $aReplace массив замен
     * @return string 
     */
    private function _restore( $sText = '', $sCode = '', $aReplace = array() ) {
        if ( !empty($sText) && !empty($aReplace) ) {
            for ($i = 0; $i < count($aReplace); $i++) {
                $sText = str_replace( $sCode . ($i+1), $aReplace[$i], $sText );
            }
        }
        
        return $sText;
    }


    /**
     * Вспомогательная функция для замены запрещенных выражений
     * Заполняет массив $this->censored и заменяет запрещенные выражения на 
     * 
     * @param  array $matches
     * @return string 
     */
    function _callbackReplace( $matches ) {
        $sTxt = $matches[0];

        $sFormat          = $this->replace_mode == 'html' ? self::STOP_REGEX : self::STOP_REGEX_PLAIN;
        $this->censored[] = sprintf( $sFormat, $sTxt );
        
        return self::STOP_REGEX_CODE . count($this->censored);
    }
    
    /**
     * Проверяет список запрещенных в сообщениях пользователей выражений на валидность
     * 
     * @param  string $sRegex текст с выражениями: одна строка - одно выражение
     * @return string пустая строка - успех, регулярка с ошибкой - провал
     */
    function validateAdminStopRegex( $sRegex = '' ) {
        $sError = '';
        $aRegex = explode("\n", $sRegex);
        
        if ( is_array($aRegex) && count($aRegex) ) {
            foreach ( $aRegex as $sOne ) {
                if ( @preg_match( trim($sOne), 'test') === false ) {
                    $sError = $sOne;
                    break;
                }
            }
        }
        
        return $sError;
    }
    
    /**
     * Сохраняет список слов для подсветки подозрительного содержания в модерировании сообщений
     * 
     * @param  string $sWords текст со словами через запятую
     * @return bool true - успех, false - провал
     */
    function updateAdminStopWords( $sWords = '' ) {
        return self::_updateAdminStopWords( explode(',', $sWords), self::$stop_words_words );
    }
    
    /**
     * Сохраняет список запрещенных в сообщениях пользователей выражений
     * 
     * @param  string $sRegex текст с выражениями: одна строка - одно выражение
     * @return bool true - успех, false - провал
     */
    function updateAdminStopRegex( $sRegex = '' ) {
        return self::_updateAdminStopWords( explode("\n", $sRegex), self::$stop_words_regex );
    }
    
    /**
     * Сохраняет список подозрительных слов и регулярные выражения для модерирования пользовательского контента
     * 
     * @param  array $aWords массив слова
     * @param  int $nType тип: 1 - слова, 2 - регулярные выражения
     * @return bool true - успех, false - провал
     */
    private function _updateAdminStopWords( $aWords = '', $nType = 0 ) {
        $aStopWords = array();
        
        $GLOBALS['DB']->query( 'DELETE FROM '. self::$stop_words_table .' WHERE type = ?i', $nType );
        
        if ( !$GLOBALS['DB']->error ) {
            if ( is_array($aWords) && count($aWords) ) {
                $aWords = array_map( 'trim', $aWords );
                $aWords = array_unique( $aWords );
                $aData = array();
                
                foreach ( $aWords as $sOne ) {
                    if ( $sOne ) {
                        $aData[] = array( 'word' => $sOne, 'type' => $nType );
                    }
                }
                
                if ( $aData ) {
                    $GLOBALS['DB']->insert( self::$stop_words_table, $aData );

                    if ( $GLOBALS['DB']->error ) {
                        return FALSE;
                    }
                }
            }
        }
        else {
            return FALSE;
        }
        
        if ( !$sError ) {
            $sMemKey = self::_getAdminStopWordsMemKey( $nType );
            $oMemBuf = new memBuff();
            $oMemBuf->set( $sMemKey, $aWords, 3600 );
        }
        
        return TRUE;
    }
    
    /**
     * Возвращает список слов для подсветки подозрительного содержания в модерировании сообщений
     * 
     * @param  bool $bMemBuf установить в true если данные брать из мемкеша
     * @return array
     */
    function getAdminStopWords( $bMemBuf = true ) {
        return self::_getAdminStopWords( self::$stop_words_words, $bMemBuf );
    }
    
    /**
     * Возвращает список запрещенных в сообщениях пользователей выражений
     * 
     * @param  bool $bMemBuf установить в true если данные брать из мемкеша
     * @return array
     */
    function getAdminStopRegex( $bMemBuf = true ) {
        return self::_getAdminStopWords( self::$stop_words_regex, $bMemBuf );
    }
    
    /**
     * Сохраняет список подозрительных слов и регулярные выражения для модерирования пользовательского контента
     * 
     * @param  int $nType тип: 1 - слова, 2 - регулярные выражения
     * @param  bool $bMemBuf установить в true если данные брать из мемкеша
     * @return array
     */
    private function _getAdminStopWords( $nType = 0, $bMemBuf = true ) {
        $aWords  = array();
        $sMemKey = self::_getAdminStopWordsMemKey( $nType );
        $oMemBuf = new memBuff();
        
        if ( $bMemBuf ) {
            $aWords  = $oMemBuf->get( $sMemKey );
        }
        
        if ( !$bMemBuf || $aWords === false ) {
            $aWords = $GLOBALS['DB']->col(
                'SELECT word FROM '. self::$stop_words_table .' WHERE type = ?i ORDER BY id', $nType 
            );
            
            $oMemBuf->set( $sMemKey, $aWords, 3600 );
        }
        
        return $aWords;
    }
    
    /**
     * Возвращает имя ключа в мемкеше для хранения подозрительных слов и регулярные выражений
     * 
     * @param  int $nType тип: 1 - слова, 2 - регулярные выражения
     * @return string 
     */
    private function _getAdminStopWordsMemKey( $nType = 0 ) {
        $sMemKey = ( $nType == self::$stop_words_words ) ? 'words' : 'regex';
        return 'user_content_stop_' . $sMemKey;
    }
}
