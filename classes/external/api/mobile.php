<?php
/**
 * API для работы с мобильным приложением
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class externalApi_Mobile extends externalApi {
    // @todo php 5.3 сделать const
    protected $API_NAMESPACE      = 'http://www.free-lance.ru/external/api/mobile';
    protected $API_DEFAULT_PREFIX = '';
    
    /**
     * Поддерживаемые платформы
     * 
     * @var array 
     */
    static private $_aPlatform = array( 
        'ios'      => array( 'name' => 'iOS',          'filter_page' => 2 ), 
        'android'  => array( 'name' => 'Android',      'filter_page' => 3 ), 
        'facebook' => array( 'name' => 'facebook.com', 'filter_page' => 4 ), 
        'vk'       => array( 'name' => 'vk.com',       'filter_page' => 5 )
    );
    
    /**
     * Текущая платформа
     * 
     * @var string 
     */
    private $_sPlatform = '';
    
    /**
     * Текущий уникальный индификатор устройства
     * 
     * @var string 
     */
    private $_sUdid = '';


    /**
     * Конфигурация для методов
     * 
     * @var array 
     */
    protected $_methodsCfg = array(
        'users_signin'             => array( 'req_no_auth' => true ), // не требует авторизации
        'users_signout'            => array(), 
        'users_signup'             => array( 'req_no_auth' => true ), 
        'users_signup_sms_resend'  => array( 'req_no_auth' => true ), 
        'users_signup_complete'    => array( 'req_no_auth' => true ), 
        'users_signup_required'    => array(), 
        'users_forgot_phone'       => array( 'req_no_auth' => true ), 
        'users_forgot_email'       => array( 'req_no_auth' => true ), 
        'users_list'               => array(), 
        'users_get'                => array(), 
        'users_portfolio'          => array(), 
        'users_favorites_list'     => array(), 
        'users_favorites_set'      => array( 'fields_required' => true ), // нужно заполнить обязательные поля
        'users_exists'             => array( 'req_no_auth' => true ), 
        'projects_list'            => array( 
            'items_per_page'  => 20, // количество записей на страницу
            'default_kind'    => -1, // тип проекта по умолчанию
            'req_no_auth'     => true
        ), 
        'projects_get'             => array(),
        'projects_add'             => array( 
            'emp_only'        => true, // только для работодателей
            'fields_required' => true, 
            'default_kind'    => 1,    // тип проекта по умолчанию
            'descr_limit'     => 5000  // максимальное количество символов в описании проекта
        ), 
        'projects_response_add'    => array( 
            'frl_only'        => true, // только для фрилансеров 
            'fields_required' => true 
            ), 
        'projects_response_select' => array( 
            'fields_required' => true, 
            'emp_only'        => true 
        ),
        'messages_list'            => array(),
        'messages_send'            => array( 
            'fields_required' => true, 
            'text_limit'      => 20000 // максимальное количество символов в сообщении
        ), 
        'messages_read'            => array(
            'fields_required' => true 
        ),
        'settings_get'             => array(
            'tables' => array (
                'city'        => array( 'out' => 'cities',           'view' => 'vw_external_city' ),
                'country'     => array( 'out' => 'countries',        'view' => 'vw_external_country' ),
                'professions' => array( 'out' => 'categories',       'view' => 'vw_external_professions' ),
                'prof_group'  => array( 'out' => 'categories_group', 'view' => 'vw_external_prof_group' )
            )
        ),
        'settings_set'             => array( 
            'fields_required' => true, 
            'frl_only'        => true
        ),
        'settings_filter_set' => array( 
            'fields_required' => true, 
            'frl_only'        => true
        ),
        'settings_push_set'   => array(),
        'device_register'     => array( 'req_no_auth' => true )
    );

    const GOOGLE_EMAIL = 'developerflru@gmail.com';
    const GOOGLE_PASS = 'Ew4LAng0sIMNk3e';
    const GOOGLE_SOURCE = 'ru.freelance';

    const GOOGLE_APIKEY = 'AIzaSyBLrVWnx_QuhSGjKE2x-QDI80sM8gB5Ah8';
    
    /**
     * Объект сессии
     * 
     * @var type 
     */
    private $_oSession;
    
    /**
     * Конструктор класса
     * 
     * @param externalSession $sess объект сессии.
     */
    function __construct( $sess ) {
        parent::__construct( $sess );
        
        require_once( ABS_PATH . '/classes/session.php' );
        session_start();
        
        $this->_oSession = $session;
    }
    
    /**
     * Вызывается перед каждым методом только внутри данного пространства имен (кроме методов externalApi) для 
     * проверки прав на вызов метода.
     * Доступны $this->_mName и $this->_mCfg.
     *
     * @return integer код ошибки или 0 -- метод разрешен.
     */
    protected function _methodsDenied() {
        if ( empty($this->_mCfg['req_no_auth']) && empty($_SESSION['uid']) ) {
            $this->error( EXTERNAL_ERR_NEED_AUTH );
        }
        
        if ( !empty($this->_mCfg['emp_only']) && !is_emp() ) {
            $this->error( EXTERNAL_ERR_EMP_ONLY );
        }
        
        if ( !empty($this->_mCfg['frl_only']) && is_emp() ) {
            $this->error( EXTERNAL_ERR_ONLYFRL );
        }
        
        if ( !empty($this->_mCfg['fields_required']) ) {
            require_once( ABS_PATH . '/classes/registration.php' );
            
            $registration = new registration();
            
            if ( !$registration->checkUserAccess($_SESSION['uid']) ) {
                $this->error( EXTERNAL_ERR_FIELDS_REQUIRED );
            }
        }
        
        return false;
    }
    
    /**
     * Получение списка настроек @deprecated
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____settings_set( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once( ABS_PATH . '/classes/projects_filter.php' );
        
        $nPage      = self::$_aPlatform[$this->_sPlatform]['filter_page']; // какой фильтр сохранять
        $oPrjFilter = new projects_filters();
        $bUseMain   = intvalPgSql( $aParams['enabled'] ) ? true : false;
        $sKeyword   = iconv( 'utf-8', 'cp1251', $aParams['keyword'] );
        $aProfs     = array( array(), array() );
        
        if ( is_array($aParams['items']) && $aParams['items'] ) {
            foreach ( $aParams['items'] as $aOne ) {
                $nGroupId = intvalPgSql( $aOne['categories_group_id'] );
                $nProfId  = intvalPgSql( $aOne['categories_id'] );
                
                if ( !empty($nProfId) ) {
                    $aProfs[1][$nProfId] = 1;
                }
                elseif ( !empty ($nGroupId) ) {
                    $aProfs[0][$nGroupId] = 0;
                }
            }
        }
        
        $prj_filter = new projects_filters();
        $prj_filter->Save( get_uid(false), 0, 0, 2, true, $aProfs, 0, 0, $sKeyword, false, $rerror, $error,0, $nPage, false, false, false, false, false, null, null, $bUseMain );
        
        if ( $rerror || $error ) {
            $this->error( EXTERNAL_ERR_SERVER_ERROR );
        }
        
        return array();
    }

    /**
     * Сохранение настроек PUSH
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____settings_push_set( $aParams = array() ) {
        global $DB;

        $this->_validDevice( $aParams );
        
        $uid = get_uid(false);
        $b_message_new   = intvalPgSql( $aParams['message_new'] ) ? 't' : 'f';
        $b_projects_new   = intvalPgSql( $aParams['projects_new'] ) ? 't' : 'f';
        $b_project_response_new   = intvalPgSql( $aParams['project_response_new'] ) ? 't' : 'f';
        $b_project_select_candidate   = intvalPgSql( $aParams['project_select_candidate'] ) ? 't' : 'f';
        $b_project_select_performer   = intvalPgSql( $aParams['project_select_performer'] ) ? 't' : 'f';
        $b_project_select_reject   = intvalPgSql( $aParams['project_select_reject'] ) ? 't' : 'f';

        $sql = "SELECT id FROM external_m_push_settings WHERE device_id = ? AND device_type = ? AND user_id = ?i";
        $settings_id = $DB->val($sql, $this->_sUdid, $this->_sPlatform, $uid);
        if($settings_id) {
            $sql = "UPDATE external_m_push_settings 
                    SET message_new=?, 
                        projects_new=?, 
                        project_response_new=?, 
                        project_select_candidate=?, 
                        project_select_performer=?, 
                        project_select_reject=? 
                    WHERE id = ?i";
            $DB->query($sql, $b_message_new, $b_projects_new, $b_project_response_new, $b_project_select_candidate, $b_project_select_performer, $b_project_select_reject, $settings_id);
        } else {
            $sql = "INSERT INTO external_m_push_settings (
                            device_id, 
                            device_type, 
                            user_id, 
                            message_new, 
                            project_response_new, 
                            project_select_candidate, 
                            project_select_performer, 
                            project_select_reject
                        ) VALUES (
                            ?,
                            ?,
                            ?i,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?
                        )";
            $DB->query($sql, $this->_sUdid, $this->_sPlatform, $uid, $b_message_new, $b_projects_new, $b_project_response_new, $b_project_select_candidate, $b_project_select_performer, $b_project_select_reject);
        }

        $rerror = $DB->error;
        if ( $rerror || $error ) {
            $this->error( EXTERNAL_ERR_SERVER_ERROR );
        }
        
        return array();
    }

    /**
     * Регистрация устройства для PUSH
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____device_register( $aParams = array() ) {
        global $DB;

        $this->_validDevice( $aParams );
        
        $uid = get_uid(false);

        $sql = "DELETE FROM external_m_devices WHERE device_id=? AND device_type=?";
        $DB->query($sql, $this->_sUdid, $this->_sPlatform);

        $data = base64_encode(serialize($aParams));

        $sql = "INSERT INTO external_m_devices(device_id, device_type, data, user_id) VALUES(?, ?, ?, ?i)";
        $DB->query($sql, $this->_sUdid, $this->_sPlatform, $data, $uid);

        if ( $rerror || $error ) {
            $this->error( EXTERNAL_ERR_SERVER_ERROR );
        }
        
        return array();
    }

    /**
     * Получение списка настроек
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____settings_filter_set( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once( ABS_PATH . '/classes/projects_filter.php' );
        
        $nPage      = self::$_aPlatform[$this->_sPlatform]['filter_page']; // какой фильтр сохранять
        $oPrjFilter = new projects_filters();
        $bUseMain   = intvalPgSql( $aParams['enabled'] ) ? true : false;
        $sKeyword   = iconv( 'utf-8', 'cp1251', $aParams['keyword'] );
        $aProfs     = array( array(), array() );
        
        if ( is_array($aParams['items']) && $aParams['items'] ) {
            foreach ( $aParams['items'] as $aOne ) {
                $nGroupId = intvalPgSql( $aOne['categories_group_id'] );
                $nProfId  = intvalPgSql( $aOne['categories_id'] );
                
                if ( !empty($nProfId) ) {
                    $aProfs[1][$nProfId] = 1;
                }
                elseif ( !empty ($nGroupId) ) {
                    $aProfs[0][$nGroupId] = 0;
                }
            }
        }
        
        $prj_filter = new projects_filters();
        $prj_filter->Save( get_uid(false), 0, 0, 2, true, $aProfs, 0, 0, $sKeyword, false, $rerror, $error,0, $nPage, false, false, false, false, false, null, null, $bUseMain );
        
        if ( $rerror || $error ) {
            $this->error( EXTERNAL_ERR_SERVER_ERROR );
        }
        
        return array();
    }


    /**
     * Получение списка настроек
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____settings_get( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        global $DB;
        
        $aKeys      = array_keys( $this->_mCfg['tables'] ); // ключи справочных таблиц
        $aLastTime  = array(); // последнее время обновления
        $bRetMirror = false;   // возвращать ли зеркальные профессии
        
        foreach ( $aKeys as $sKey ) {
            $aLastTime[$sKey] = intvalPgSql( $aParams['last_update_' . $this->_mCfg['tables'][$sKey]['out']] );
        }
        
        $aResult = array( 
            'settings' => array(
                "version_ios"     => "1.0", // TODO: положить в таблицу variables
                "version_android" => "1.0"  // TODO: положить в таблицу variables
            )
        );
        
        foreach ( $aKeys as $sKey ) {
            $sql  = "SELECT version FROM external_cache WHERE obj_name = '{$sKey}' AND obj_type = " . self::OBJTYPE_TABLE;
            $bRet = true; // выводить данные
            
            if ( $server_version = $DB->val($sql) ) {
                $server_version = $this->pg2ex($server_version, EXTERNAL_DT_TIME);
                
                if ( $aLastTime[$sKey] && $server_version <= $aLastTime[$sKey] ) {
                    $bRet = false; // данные не изменялись - не выводить данные
                }
            }
            
            if ( $bRet ) {
                if ( $sKey == 'professions' ) {
                    $bRetMirror = true;
                }
                
                $aData = $DB->rows( 'SELECT * FROM ' . $this->_mCfg['tables'][$sKey]['view'] );
                $sFunc = '_settings_get_' . $sKey;
                $aResult['settings'][$this->_mCfg['tables'][$sKey]['out']] = $this->$sFunc( $aData, $server_version );
            }
            else {
                $aResult['settings'][$sKey] = array();
            }
        }
        
        $aResult['settings']['filter'] = $this->_settings_get_filter(); // настройки фильтра
        
        // зеркальные профессии
        if ( $bRetMirror ) {
            require_once( ABS_PATH  . '/classes/professions.php' );
            $aResult['settings']['mirrored_professions'] = professions::GetAllMirroredProfsId();
        }
        else {
            $aResult['settings']['mirrored_professions'] = array();
        }

        // Push
        $uid = get_uid(false);
        $sql = "SELECT * FROM external_m_push_settings WHERE device_id = ? AND device_type = ? AND user_id = ?i";
        $settings = $DB->row($sql, $this->_sUdid, $this->_sPlatform, $uid);
        $aResult['settings']['push'] = array();
        $aResult['settings']['push']['message_new']   = $settings['message_new']=='t' ? 1 : 0;
        $aResult['settings']['push']['projects_new']   = $settings['projects_new']=='t' ? 1 : 0;
        $aResult['settings']['push']['project_response_new']   = $settings['project_response_new']=='t' ? 1 : 0;
        $aResult['settings']['push']['project_select_candidate']   = $settings['project_select_candidate']=='t' ? 1 : 0;
        $aResult['settings']['push']['project_select_performer']   = $settings['project_select_performer']=='t' ? 1 : 0;
        $aResult['settings']['push']['project_select_reject']   = $settings['project_select_reject']=='t' ? 1 : 0;
        
        return $aResult;
    }
    
    /**
     * Получение настроек фильтра
     * 
     * @return array
     */
    private function _settings_get_filter() {
        require_once( ABS_PATH . '/classes/projects_filter.php' );
        require_once( ABS_PATH . '/classes/professions.php' );
        
        $aReturn = array();
        $nPage   = self::$_aPlatform[$this->_sPlatform]['filter_page'];    // какой фильтр получать
        $oFilter = new projects_filters();
        $aFilter = $oFilter->GetFilter( $_SESSION['uid'], $error, $nPage ); // фильтр из базы даных
        
        if ( $this->_sPlatform == 'ios' || $this->_sPlatform == 'android' ) {
            $aReturn['enabled'] = $aFilter['use_main_filter'] == 't' ? 1 : 0; // наследовать фильтр с главной
        }
        
        $aReturn['keyword'] = iconv( 'cp1251', 'utf-8', $aFilter['keywords'] ); // ключевые слова
        
        $aReturn['items'] = array();
        
        // группы профессий, где выбран весь раздел
        if ( !empty($aFilter['categories']) && !empty($aFilter['categories'][0]) && is_array($aFilter['categories'][0]) ) {
            
            foreach ( $aFilter['categories'][0] as $nId => $nFake ) {
                $aReturn['items'][] = array( 'categories_group_id' => $nId, 'categories_id' => 0 );
            }
        }
        
        // профессии, где выбрана конкретная
        if ( !empty($aFilter['categories']) && !empty($aFilter['categories'][1]) && is_array($aFilter['categories'][1]) ) {
            $aProfsAndGroups = professions::GetProfessionsAndGroup();
            $aProfsToGroups  = array();
            
            foreach ( $aProfsAndGroups as $aOne ) {
                $aProfsToGroups[$aOne['id']] = $aOne['gid'];
            }
            
            foreach ( $aFilter['categories'][1] as $nId => $nFake ) {
                $aReturn['items'][] = array( 'categories_group_id' => $aProfsToGroups[$nId], 'categories_id' => $nId );
            }
        }
        
        return $aReturn;
    }


    /**
     * Получение списка городов
     * 
     * @param aray $aData Массив с городами
     */
    private function _settings_get_city( $aData = array(), $server_version = 0 ) {
        $aReturn = array();
        
        if (is_array($aData) && $aData ) {
            foreach ( $aData as $aOne ) {
                $aReturn[] = array(
                    "id"          => $aOne['id'],
                    "country_id"  => $aOne['country_id'],
                    "title"       => iconv( 'cp1251', 'utf-8', $aOne['name'] ),
                    "sequence"    => $aOne['id'],
                    "status"      => 1,
                    "create_time" => $server_version,
                    "update_time" => $server_version
                );
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Получение списка стран
     * 
     * @param aray $aData Массив со странами
     */
    private function _settings_get_country( $aData = array(), $server_version = 0 ) {
        $aReturn = array();
        
        if (is_array($aData) && $aData ) {
            foreach ( $aData as $aOne ) {
                $aReturn[] = array(
                    "id"          => $aOne['id'],
                    "title"       => iconv( 'cp1251', 'utf-8', $aOne['name'] ),
                    "sequence"    => $aOne['pos'],
                    "status"      => 1,
                    "create_time" => $server_version,
                    "update_time" => $server_version
                );
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Получение списка профессий
     * 
     * @param aray $aData Массив с профессиями
     */
    private function _settings_get_professions( $aData = array(), $server_version = 0 ) {
        $aReturn = array();
        
        if (is_array($aData) && $aData ) {
            foreach ( $aData as $aOne ) {
                $aReturn[] = array(
                    "id"                  => $aOne['id'],
                    "categories_group_id" => $aOne['grp_id'],
                    "title"               => iconv( 'cp1251', 'utf-8', $aOne['name'] ),
                    "sequence"            => $aOne['pos'],
                    "status"              => 1,
                    "create_time"         => $server_version,
                    "update_time"         => $server_version
                );
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Получение списка групп профессий
     * 
     * @param aray $aData Массив с группами профессий
     */
    private function _settings_get_prof_group( $aData = array(), $server_version = 0 ) {
        $aReturn = array();
        
        if (is_array($aData) && $aData ) {
            foreach ( $aData as $aOne ) {
                $aReturn[] = array(
                    "id"          => $aOne['id'],
                    "title"       => iconv( 'cp1251', 'utf-8', $aOne['name'] ),
                    "sequence"    => $aOne['pos'],
                    "status"      => 1,
                    "create_time" => $server_version,
                    "update_time" => $server_version
                );
            }
        }
        
        return $aReturn;
    }


    /**
     * Установка статуса прочитано к диалогу
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____messages_read( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $nUid = get_uid( false );
        $nTo  = intvalPgSql( $aParams['user_id'] );
        
        if ( !empty($nTo) ) {
            require_once( ABS_PATH  . '/classes/users.php' );
            
            $oUser = new users();
            $oUser->GetUserByUID( $nTo );
            
            if ( $oUser->uid ) {
                if ( $oUser->uid != $nUid ) {
                    if ( empty($oUser->is_banned) ) {
                        require_once( ABS_PATH  . '/classes/messages.php' );
                        
                        if ( !messages::readDialog( $nUid, $oUser->uid ) ) {
                            $this->error( EXTERNAL_ERR_SERVER_ERROR );
                        }
                    }
                    else {
                        $this->error( EXTERNAL_ERR_USER_BANNED );
                    }
                }
                else {
                    $this->error( EXTERNAL_ERR_SELF_MESSAGE );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_USER_NOTFOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_USER_ID );
        }
        
        return array();
    }
    
    /**
     * Добавление личного сообщения
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____messages_send( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $nUid = get_uid( false );
        $nTo  = intvalPgSql( $aParams['to_id'] );
        
        if ( !empty($nTo) ) {
            require_once( ABS_PATH  . '/classes/users.php' );
            
            $oUser = new users();
            $oUser->GetUserByUID( $nTo );
        
            if ( $oUser->uid ) {
                if ( $oUser->uid != $nUid ) {
                    if ( empty($oUser->is_banned) ) {
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/ignor.php' );
                        
                        $bIgnor = ( ignor::CheckIgnored($oUser->uid, $nUid) || in_array($oUser->login, array('admin', 'Anonymous')) );
                        
                        if ( !$bIgnor ) {
                            $sMessage = __paramValue( 'html', antispam(iconv( 'utf-8', 'cp1251', $aParams['text'])), null, true );

                            if ( !$sMessage || trim($sMessage) == '' ) {
                                $this->error( EXTERNAL_ERR_EMPTY_MESSAGE );
                            } 
                            elseif( $sMessage && strlen($sMessage) > $this->_mCfg['text_limit'] ) {
                                $this->error( EXTERNAL_ERR_LENGTH_MESSAGE );
                            }
                            
                            require_once( ABS_PATH  . '/classes/messages.php' );

                            list( $alert, $error ) = messages::Add( $nUid, $oUser->login, $sMessage, array(), 0, false, null, $sId );

                            if (! $error && isNulArray($alert) ){
                                messages::updateSendLog( $nUid );
                            }
                            else {
                                $this->error( EXTERNAL_ERR_SERVER_ERROR );
                            }
                            
                            $aResult = array( // чтобы реально не вытаскиваро сообщение из базы
                                "message" => array(
                                    "id"          => $sId,
                                    "from_id"     => $nUid,
                                    "to_id"       => $oUser->uid,
                                    "text"        => $aParams['text'],
                                    "status"      => 1,
                                    "read"        => 0,
                                    "create_time" => time(),
                                    "update_time" => time()
                                  )
                            );
                        }
                        else {
                            $this->error( EXTERNAL_ERR_MESSAGE_IGNOR );
                        }
                    }
                    else {
                        $this->error( EXTERNAL_ERR_USER_BANNED );
                    }
                }
                else {
                    $this->error( EXTERNAL_ERR_SELF_MESSAGE );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_USER_NOTFOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_USER_ID );
        }
        
        return $aResult;
    }
    
    /**
     * Получение списка личных сообщений.
     * Возвращает все сообщения пользователя (входящие и исходящие), которые были добавлены, изменены или помечены удаленными после определенной даты.
     * (!) Массовые рассылки только входящие.
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____messages_list( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once( ABS_PATH  . '/classes/messages.php' );
        
        $aResult   = array( 'list' => array() );
        $nTime     = intvalPgSql( $aParams['last_update'] );
        $aMessages = messages::getMessagesAllSinceDate( get_uid(false), date('Y-m-d H:i:s', $nTime) );
        
        if ( is_array($aMessages) && $aMessages ) {
            messages::getMessagesAttaches($aMessages);
            foreach ( $aMessages as $aOne ) {
                if( (strtotime($aOne['read_time']) && $aOne['read_time']!='1970-01-01 00:00:00' && $aOne['read_time'] > $nTime) || ($aOne['read_time']=='1970-01-01 00:00:00' && strtotime($aOne['post_time'])>$nTime) ) {
                    $aResult['list'][] = $this->_getMessageData( $aOne );
                }
            }
        }
        
        return $aResult;
    }
    
    /**
     * Выбор пользователя в предложении к проекту
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____projects_response_select( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once( ABS_PATH . '/classes/projects_offers.php' );
        
        $nUid   = get_uid( false );
        $oOffer = new projects_offers();
        $nId    = intvalPgSql($aParams['id']);
        $aOffer = $oOffer->GetPrjOfferById( $nId );
        
        if (is_array($aOffer) && $aOffer ) {
            require_once(ABS_PATH.'/classes/projects.php');
            
            $oPrj     = new new_projects();
            $aProject = $oPrj->GetPrjCust( $aOffer['project_id'] );
            
            if ( !empty($aProject) || $aProject['is_blocked'] == 'f' ) { // Не позволяем производить действия с заблокированным проектом
                if ( $nUid == $aProject['user_id'] ) {
                    $nSelect = intvalPgSql($aParams['select']);
                    
                    if ( $nSelect > 0 && $nSelect < 4 ) {
                        $aOffer['emp_uid'] = $aProject['user_id'];
                        switch ($nSelect) {
                            case 1: 
                                $error = false;
                                
                                if ( $aProject['exec_id'] == $aOffer['user_id'] ) {
                                    $error = $oPrj->ClearExecutor( $aProject['id'], $nUid );
                                }
                                
                                if ( !$error ) {
                                   $error = $oOffer->SetRefused($aOffer['id'], $aProject['id'], $aOffer['user_id'], '', true); 
                                }
                                
                                if ( $error ) {
                                    $this->error( EXTERNAL_ERR_SERVER_ERROR );
                                } else {
                                    $aOffer['refused'] = 't';
                                }
                                break;
                            case 2: 
                                $error = false;
                                
                                if ( $aProject['exec_id'] == $aOffer['user_id'] ) {
                                    $error = $oPrj->ClearExecutor( $aProject['id'], $nUid );
                                }
                                
                                if ( !$error ) {
                                   $error = $oOffer->SetSelected($aOffer['id'], $aProject['id'], $aOffer['user_id'], true);
                                }
                                
                                if ( $error ) {
                                    $this->error( EXTERNAL_ERR_SERVER_ERROR );
                                } else {
                                    $aOffer['selected'] = 't';
                                }
                                break;
                            case 3: 
                                if ( $oPrj->SetExecutor($aProject['id'], $aOffer['user_id'], $nUid) ) {
                                    $this->error( EXTERNAL_ERR_SERVER_ERROR );
                                } else {
                                    $aOffer['exec_id'] = $aOffer['user_id'];
                                }
                                break;
                        }
                        $aResult = array( 'project_response' => $this->_getProjectOfferData($aOffer) );
                    }
                    else {
                        $this->error( EXTERNAL_ERR_PRJ_SELECTED );
                    }
                }
                else {
                    $this->error( EXTERNAL_ERR_OWNER );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_PROJECT_NOT_FOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_OFFER_NOT_FOUND );
        }
        
        return $aResult;
    }
    
    /**
     * Добавления предложения к проекту
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____projects_response_add( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $sId = __paramValue( 'int', $aParams['project_id'], null, true );
        
        if ( !empty($sId) ) {
            require_once(ABS_PATH . "/classes/projects.php");
            
            $nUid     = get_uid( false );
            $oPrj     = new new_projects();
            $aProject = $oPrj->GetPrjCust( $sId );
            
            if ( !empty($aProject) ) {
                require_once( ABS_PATH . '/classes/projects_offers.php' );
                
                if ( projects_offers::offerSpecIsAllowed($sId) ) {
                    require_once( ABS_PATH . '/classes/projects_offers_dialogue.php' );

                    if($aParams['term']>9999) {
                        $this->error( EXTERNAL_ERR_SERVER_ERROR );
                    } else {
                        // TODO: все что с ps_ - по умолчанию
                        $obj_offer   = new projects_offers();
                        $error_offer = $obj_offer->AddOffer(
                            $nUid, 
                            $sId, 
                            $aParams['budget'],
                            $aParams['ps_cost_to'], 
                            $aParams['currency'],
                            $aParams['term'], 
                            $aParams['ps_time_to'], 
                            $aParams['term_dimension'],  
                            antispam(stripslashes(iconv( 'utf-8', 'cp1251', $aParams['comment']))),
                            $aParams['ps_work_1_id'], 
                            $aParams['ps_work_2_id'], 
                            $aParams['ps_work_3_id'],
                            $aParams['ps_work_1_link'], 
                            $aParams['ps_work_2_link'], 
                            $aParams['ps_work_3_link'],
                            $aParams['ps_work_1_name'], 
                            $aParams['ps_work_2_name'], 
                            $aParams['ps_work_3_name'],
                            $aParams['ps_work_1_pict'], 
                            $aParams['ps_work_2_pict'], 
                            $aParams['ps_work_3_pict'],
                            $aParams['ps_work_1_prev_pict'], 
                            $aParams['ps_work_2_prev_pict'], 
                            $aParams['ps_work_3_prev_pict'],
                            !empty($aParams['only_customer']), 
                            0, 
                            0, 
                            false, //isset($aParams['prefer_sbr']), 
                            false, //$aParams['is_color'], 
                            serialize(array()), //$save_contacts, 
                            '0' //$payed_items
                        );
                    }

                    if ( $error_offer ) {
                        $this->error( EXTERNAL_ERR_SERVER_ERROR );
                    }

                    $aOffer = $obj_offer->GetPrjOffer( $sId, $nUid );
                    $aResult = array( 'project_response' => $this->_getProjectOfferData($aOffer) );
                }
                else {
                    $this->error( EXTERNAL_ERR_OFFER_SPEC );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_PROJECT_NOT_FOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_PROJECT_ID );
        }
        
        return $aResult;
    }
    
    /**
     * Добавление проекта
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____projects_add( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once(ABS_PATH . "/classes/projects.php");
        
        $nKind = intvalPgSql( $aParams['kind'] );
        $nKind = !empty($nKind) ? $nKind : $this->_mCfg['default_kind'];
        
        $nUid  = get_uid( false );
        $sKey  = md5(uniqid($uid)); // ключ-идентификатор создаваемого/редактируемого проекта, для хранения в кэше.
        $aCats = array( array('category_id' => intvalPgSql($aParams['group_category_id']), 'subcategory_id' => intvalPgSql($aParams['category_id'])) );
        
        $tmpPrj = new tmp_project( $sKey );
        $tmpPrj->init( 1, 0 );
        $tmpPrj->setProjectField( 'kind', $nKind );
        $tmpPrj->setProjectField( 'descr', __paramValue( 'html', antispam(iconv( 'utf-8', 'cp1251', $aParams['descr'])), null, true) );
        $tmpPrj->setProjectField( 'name', substr( antispam(__paramValue('string', iconv( 'utf-8', 'cp1251', $aParams['title']), 60)),0,512) );
        $tmpPrj->setProjectField( 'cost', __paramValue('float', $aParams['budget']) );
        $tmpPrj->setProjectField( 'currency', intvalPgSql($aParams['currency']) );
        $tmpPrj->setProjectField( 'priceby', intvalPgSql($aParams['dimension']) );
        $tmpPrj->setProjectField( 'agreement', intvalPgSql($aParams['budget_agreement']) );
        $tmpPrj->setProjectField('budget_type', 0 ); // TODO: ???
        $tmpPrj->setCategories( $aCats );
        $tmpPrj->setProjectField( 'country', intvalPgSql($aParams['country_id']) );
        $tmpPrj->setProjectField( 'city', intvalPgSql($aParams['city_id']) );
        $tmpPrj->setProjectField('pro_only', intvalPgSql($aParams['only_pro']) ? 't' : 'f');
        $tmpPrj->setProjectField('verify_only', intvalPgSql($aParams['only_verified']) ? 't' : 'f');
        $tmpPrj->setProjectField('prefer_sbr', intvalPgSql($aParams['prefer_sbr']) ? 't' : 'f');
        
        $project = $tmpPrj->getProject();
        
        if ( $project['cost'] < 0 ) {
            $this->error( EXTERNAL_ERR_PRJ_COST_MIN );
        }

        if ( $project['cost'] > 999999 ) {
            $this->error( EXTERNAL_ERR_PRJ_COST_MAX );
        }

        if ( $project['cost']>0 && ($project['currency'] < 0 || $project['currency'] > 3) ) {
            $this->error( EXTERNAL_ERR_PRJ_CURRENCY );
        }
        
        if ( is_empty_html($project['descr']) ) {
            $this->error(EXTERNAL_ERR_PRJ_EMPTY_DESCR);
        }

        if ( is_empty_html($project['name']) ) {
            $this->error (EXTERNAL_ERR_PRJ_EMPTY_TITLE);
        }
        
        if ( strlen_real($project['descr']) > $this->_mCfg['descr_limit'] ) {
            $this->error( EXTERNAL_ERR_PRJ_LENGTH_DESCR );
        }
        
        // TODO: пока только проекты
        /*if ( $project['kind'] == 7 ) {
                $tmpPrj->setProjectField('end_date', __paramInit('string', NULL, 'end_date'),0,64);
                $tmpPrj->setProjectField('win_date', __paramInit('string', NULL, 'win_date'),0,64);
                $project = $tmpPrj->getProject();

                if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $project['end_date'], $o1) || !checkdate($o1[2], $o1[1], $o1[3]))
                        $error['end_date'] = 'Неправильная дата';

                if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $project['win_date'], $o2) || !checkdate($o2[2], $o2[1], $o2[3]))
                        $error['win_date'] = 'Неправильная дата';

                // Модераторам аккуратней	
            if(!hasPermissions('projects')) {
                if (!$error['end_date'] && mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]) <= mktime(0, 0, 0))
                        $error['end_date'] = 'Дата окончания конкурса не может находиться  в прошлом';

                if (!$error['win_date'] && mktime(0, 0, 0, $o2[2], $o2[1], $o2[3]) <= mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]))
                        $error['win_date'] = 'Дата определения победителя должна быть больше даты окончания конкурса';
                }

        }*/
        
        // сохранение файлов
        if ( is_array($_FILES['files']) && $_FILES['files'] ) {
            $aFiles = $this->_multiple($_FILES);
            
            foreach ( $aFiles['files'] as $aFile ) {
                if ( is_array($aFile) && !$aFile['error'] ) {
                    $_FILES['attachedfiles_file']  = $aFile;
                    $_POST['attachedfiles_action'] = 'add';
                    $_POST['attachedfiles_type']   = 'project';
                    $bSilentMode = true;
                    
                    include( ABS_PATH . '/attachedfiles.php' );
                    
                    if ( isset($file['errno']) ) {
                        switch ($file['errno']) {
                            case 1:  $this->error( EXTERNAL_ERR_FILE );            break;
                            case 2:  $this->error( EXTERNAL_ERR_MAX_FILES_CONUT ); break;
                            case 3:  $this->error( EXTERNAL_ERR_MAX_FILES_SIZE );  break;
                            case 4:  $this->error( EXTERNAL_ERR_FILE_FORMAT );     break;
                            default: $this->error( EXTERNAL_ERR_SERVER_ERROR );        break;
                        }
                    }
                    
                    $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
                    $tmpPrj->addAttachedFiles($attachedfiles_files);
                    $attachedfiles->clear();
                }
            }
        }
        
        $tmpPrj->fix();
        $tmpPrj->saveProject( null, $aProject );
        
        return $this->x____projects_get( array('id' => $aProject['id']) );
    }
    
    /**
     * Вспомогательная для загрузки файлов
     * 
     * @param  array $_files
     * @param  type $top
     * @return array
     */
    private function _multiple(array $_files, $top = TRUE)
    {
        $files = array();
        foreach($_files as $name=>$file){
            if($top) $sub_name = $file['name'];
            else    $sub_name = $name;

            if(is_array($sub_name)){
                foreach(array_keys($sub_name) as $key){
                    $files[$name][$key] = array(
                        'name'     => $file['name'][$key],
                        'type'     => $file['type'][$key],
                        'tmp_name' => $file['tmp_name'][$key],
                        'error'    => $file['error'][$key],
                        'size'     => $file['size'][$key],
                    );
                    $files[$name] = $this->_multiple($files[$name], FALSE);
                }
            }else{
                $files[$name] = $file;
            }
        }
        return $files;
    }
    
    /**
     * Получение одного проекта
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____projects_get( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $sId = __paramValue( 'int', $aParams['id'], null, true );
        
        if ( !empty($sId) ) {
            require_once(ABS_PATH.'/classes/projects.php');
            
            $oPrj     = new new_projects();
            $aProject = $oPrj->GetPrjCust( $sId );
            $aAttaches = $oPrj->getAllAttach( $sId );
            
            if ( !empty($aProject) ) {
                if ( $aProject['is_banned'] || $aProject['is_blocked'] ) { // работодатель или проест заблокированы
                    $this->error( EXTERNAL_ERR_PROJECT_NOT_FOUND );
                }
                
                $aResult = array( 'item' => $this->_getProjectData($aProject) );
                $nUid    = get_uid( false );
                
                $aSpecs = new_projects::getSpecs( $sId );
                
                if ( is_array($aSpecs) && $aSpecs ) {
                    $aResult['item']['category_id']    = $aSpecs[0]['category_id'];
                    $aResult['item']['subcategory_id'] = $aSpecs[0]['subcategory_id'];
                }
                
                $aResult['item']['attaches'] = array();
                if($aAttaches) {
                    foreach($aAttaches as $attach) {
                        $aResult['item']['attaches'][] = array("url" => WDCPREFIX.'/'.$attach['path'], "file" => $attach['name']);
                    }
                }

                $aResult['item']['responses'] = array();
                
                if ( $aProject['kind'] == 7 ) { // конкурс
                    require_once( ABS_PATH.'/classes/contest.php' );
                    
                    $oContest = new contest( $sId, $nUid, is_emp(), ($aProject['user_id'] == $nUid), false, is_pro() );
                    $oContest->GetOffers();
                    
                    if ( is_array($oContest->offers) && $oContest->offers ) {
                        foreach ( $oContest->offers as $aOne ) {
                            $aResult['item']['responses'][] = $this->_getContestOfferData( $aOne );
                        }
                    }
                }
                else { // проект
                    require_once( ABS_PATH . '/classes/projects_offers.php' );
                    require_once( ABS_PATH . '/classes/projects_offers_dialogue.php' );
                    
                    $oPrjOffers   = new projects_offers();
                    $obj_dialogue = new projects_offers_dialogue();
                    $nOffersCnt   = 0;
                    
                    if ( is_emp() ) { // залогинен работодатель
                        $aOffers = $oPrjOffers->GetPrjOffers(
                            $nOffersCnt, $sId, 'ALL', 0, $nUid, ($aProject['user_id'] == $nUid), 'date', (($aProject['user_id'] == $nUid) ? 'a' : 'nor')
                        );
                    }
                    else {
                        $aOffers = $oPrjOffers->GetPrjOffers( $nOffersCnt, $sId, 'ALL', 0, $nUid, false, null, 'nor' );
                    }
                    
                    // Диалоги по предложениям к данному проекту и все остальное
                    if ( is_array($aOffers) && $aOffers ) {
                        foreach ($aOffers as $key => $value) {
                            $aOffers[$key]['exec_id']       = $aProject['exec_id'];
                            $aOffers[$key]['emp_uid']       = $aProject['user_id'];
                            $aOffers[$key]['dialogue']      = $obj_dialogue->GetDialogueForOffer($value['id']);
                            $aResult['item']['responses'][] = $this->_getProjectOfferData( $aOffers[$key] );
                        }
                    }
                    
                    $aResult['item']['responses_count']     = $nOffersCnt;
                    $aResult['item']['is_responses_exists'] = $oPrjOffers->OfferExist($sId, $nUid) ? 1 : 2;
                    
                    // Наличие предложения данного юзера по данному проекту
                    if ( $aResult['item']['is_responses_exists'] == 1 ) {
                        // Предложение данного пользователя по данному проекту
                        $user_offer = $oPrjOffers->GetPrjOffer( $sId, $nUid );
                        
                        $user_offer['exec_id'] = $aProject['exec_id'];
                        $user_offer['emp_uid'] = $aProject['user_id'];

                        // Диалог по предложению данного пользователя
                        $user_offer['dialogue'] = $obj_dialogue->GetDialogueForOffer( $user_offer['id'] );
                        
                        $aResult['item']['responses'][] = $this->_getProjectOfferData( $user_offer );
                    }
                }
            }
            else {
                $this->error( EXTERNAL_ERR_PROJECT_NOT_FOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_PROJECT_ID );
        }
        
        return $aResult;
    }
    
    /**
     * Получение списока проектов
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____projects_list( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once( ABS_PATH . '/classes/projects_filter.php' );
        require_once( ABS_PATH . '/classes/projects.php' );
        
        $nUid = get_uid( false );
        
        $nPage      = self::$_aPlatform[$this->_sPlatform]['filter_page'];    // какой фильтр получать
        $prj_filter = new projects_filters();
        $filter     = $prj_filter->GetFilter( $nUid, $error, $nPage );
        
        if ( is_array($filter) && $filter && $filter['active'] == 't' && $filter['use_main_filter'] == 't' ) {
            $filter = $prj_filter->GetFilter( $nUid, $error, 0 );
        }
        
        $nKind = intvalPgSql( $aParams['kind'] );
        $nKind = !empty($nKind) ? $nKind : $this->_mCfg['default_kind'];
        
        $nPage = intvalPgSql( $aParams['page'] );
        $nPage = !empty($nPage) ? $nPage : 1;
        $oPrj  = new new_projects();
        
        $oPrj->page_size = $this->_mCfg['items_per_page'];

        if(is_emp()) {
            $aProjects = $oPrj->GetCurPrjs( $nUid, '', true, false, $nKind);
            $nPrgCnt = count($aProjects);
        } else {
            $aProjects = $oPrj->getProjects( $nPrgCnt, $nKind, $nPage, true, $filter, true, false, NULL, false, NULL, true );
        }
        
        $aResult = array( 'projects_list' => array() );
        
        if ( is_array($aProjects) && $aProjects ) {
            $aItems = array();
            $aIds   = array();
            $nCnt   = 0;
            
            foreach ( $aProjects as $aOne ) {
                $aResult['projects_list'][$nCnt] = $this->_getProjectData( $aOne );
                $aItems[$aOne['id']] = &$aResult['projects_list'][$nCnt];
                $aIds[] = $aOne['id'];

                $nCnt++;
            }
            
            $aSpecs = new_projects::getSpecs( $aIds );
            
            if ( is_array($aSpecs) && $aSpecs ) {
                foreach ( $aSpecs as $aOne ) {
                    $aItems[$aOne['project_id']]['category_id']    = $aOne['category_id'];
                    $aItems[$aOne['project_id']]['subcategory_id'] = $aOne['subcategory_id'];
                }
            }

            $aResult['projects_total'] = intval($nPrgCnt);
        }
        
        return $aResult;
    }
    
    /**
     * Добавление/удаление пользователя из избранного
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_favorites_set( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $nId     = intvalPgSql( $aParams['user_id'] );
        $nStatus = intvalPgSql( $aParams['status'] );
        
        if ( !empty($nId) ) {
            require_once( ABS_PATH  . '/classes/users.php' );
            
            $oUser = new users();
            $oUser->GetUserByUID( $nId );
            
            if ( $oUser->uid ) {
                if ( empty($oUser->is_banned) ) {
                    require_once( ABS_PATH . '/classes/teams.php' );
                    
                    $nInFav = teams::teamsIsInFavorites( $_SESSION['uid'], $nId );
                    
                    if ( $nInFav && $nStatus ) {
                        $this->error( EXTERNAL_ERR_FAVORITES_IN );
                    }
                    
                    if ( !$nInFav && !$nStatus ) {
                        $this->error( EXTERNAL_ERR_FAVORITES_NOT_IN );
                    }
                    
                    if ( $nStatus ) {
                        if ( teams::teamsAddFavorites($_SESSION['uid'], $oUser->login) ) {
                            $this->error( EXTERNAL_ERR_SERVER_ERROR );
                        }
                    }
                    else {
                        if ( teams::teamsDelFavoritesByLogin($_SESSION['uid'], $oUser->login) ) {
                            $this->error( EXTERNAL_ERR_SERVER_ERROR );
                        }
                    }
                }
                else {
                    $this->error( EXTERNAL_ERR_USER_BANNED );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_USER_NOTFOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_USER_ID );
        }
        
        return array();
    }
    
    /**
     * Получение списка избранных пользователей
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_favorites_list( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $aResult = array( 'favorites_list' => array() );
        
        require_once( ABS_PATH . '/classes/teams.php' );

        $aUsers = teams::teamsFavorites( $_SESSION['uid'], $error );

        if ( is_array($aUsers) && $aUsers ) {
            $aUids   = array();
            $aLogins = array();
            $nCnt    = 0;

            foreach ( $aUsers as $aOne ) {
                $aResult['favorites_list'][$nCnt] = $this->_getUserData( $aOne );

                if ( !is_emp($aOne['role']) ) {
                    $aUids[$aOne['uid']] = &$aResult['favorites_list'][$nCnt];
                    $aLogins[] = $aOne['login'];
                }

                $nCnt++;
            }

            if ( is_array($aLogins) && $aLogins ) {
                require_once( ABS_PATH . '/classes/freelancer.php' );

                $aFree = freelancer::getFreelancerByLogin( $aLogins );

                if ( is_array($aFree) && $aFree ) {
                    foreach ( $aFree as $aOne ) {
                        $aUids[$aOne['uid']]['spec'] = $aOne['spec'];
                    }
                }
            }
        }
        
        return $aResult;
    }
    
    /**
     * Получение портфолио пользователя
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_portfolio( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $nUid    = get_uid( false );
        $nId     = intvalPgSql( $aParams['user_id'] );
        $aResult = array( 'porfolio' => array() );
        
        if ( !empty($nId) ) {
            require_once( ABS_PATH  . '/classes/users.php' );
            
            $oUser = new users();
            $oUser->GetUserByUID( $nId );
            
            if ( $oUser->uid ) {
                if ( empty($oUser->is_banned) ) {
                    require_once( ABS_PATH  . '/classes/portfolio.php' );
                    
                    $aPortfolio = portfolio::GetPortf( $nId, "NULL", true );
                    $aResult    = array( 'porfolio' => $this->_getPortfolioData($aPortfolio, $nId) );
                }
                else {
                    $this->error( EXTERNAL_ERR_USER_BANNED );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_USER_NOTFOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_USER_ID );
        }
        
        return $aResult;
    }
    
    
    /**
     * Получение списка пользователей
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_get( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $nUid    = get_uid( false );
        $nId     = intvalPgSql( $aParams['id'] );
        $aResult = array( 'user' => array() );
        
        if ( !empty($nId) ) {
            require_once( ABS_PATH  . '/classes/users.php' );
            
            $oUser = new users();
            $oUser->GetUserByUID( $nId );
            
            if ( $oUser->uid ) {
                if ( empty($oUser->is_banned) ) {
                    $aData      = array();
                    $aClassVars = array_keys( get_class_vars('users') );

                    foreach ( $aClassVars as $sVar ) {
                        $aData[$sVar] = $oUser->$sVar;
                    }
                    
                    if ( !is_emp($aData['role']) ) {
                        require_once( ABS_PATH . '/classes/freelancer.php' );
                        $aTmp = freelancer::getFreelancerByLogin( array($aData['login']) );
                        $aData['spec'] = $aTmp[0]['spec'];
                    }
                    
                    $aResult['user'] = $this->_getUserData( $aData, false, true );
                }
                else {
                    $this->error( EXTERNAL_ERR_USER_BANNED );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_USER_NOTFOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_USER_ID );
        }
        
        return $aResult;
    }
    
    /**
     * Получение списка пользователей
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_list( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $aResult = array( 'users' => array() );
        
        if ( is_array($aParams['ids']) && $aParams['ids'] ) {
            require_once( ABS_PATH . '/classes/users.php' );
            array_map( 'intvalPgSql', $aParams['ids'] );
            
            $oUsers = new users();
            $aUsers = $oUsers->GetUsers( $GLOBALS['DB']->parse('uid IN (?l)', $aParams['ids']) );
            
            if ( is_array($aUsers) && $aUsers ) {
                $aUids   = array();
                $aLogins = array();
                $nCnt    = 0;
                
                foreach ( $aUsers as $aOne ) {
                    $aResult['users'][$nCnt] = $this->_getUserData( $aOne );
                    
                    if ( !is_emp($aOne['role']) ) {
                        $aUids[$aOne['uid']] = &$aResult['users'][$nCnt];
                        $aLogins[] = $aOne['login'];
                    }
                    
                    $nCnt++;
                }
                
                if ( is_array($aLogins) && $aLogins ) {
                    require_once( ABS_PATH . '/classes/freelancer.php' );
                    
                    $aFree = freelancer::getFreelancerByLogin( $aLogins );
                    
                    if ( is_array($aFree) && $aFree ) {
                        foreach ( $aFree as $aOne ) {
                            $aUids[$aOne['uid']]['spec'] = $aOne['spec'];
                        }
                    }
                }
            }
        }
        
        return $aResult;
    }
    
    /**
     * Забыли пароль. Телефон.
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_forgot_email( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $sLogin = __paramValue( 'string', $aParams['username'], null, true );
        
        if ( !empty($sLogin) ) {
            require_once( ABS_PATH . '/classes/users.php' );
            
            $oUser = new users();
            if (preg_match( "/^[-^!#$%&'*+\/=?`{|}~.\w]+@[-a-zA-Z0-9]+(\.[-a-zA-Z0-9]+)+$/", $sLogin) ) {
                $err = $oUser->GetUser($sLogin, true, $sLogin);
            } else {
                $err = $oUser->GetUser($sLogin);
            }

            
            if ( $oUser->uid ) {
                if( !users::isRemindByPhoneOnly($sLogin) ) {
                    require_once( ABS_PATH . '/classes/smail.php' );
                    
                    $sm    = new smail();
                    $error = $sm->remind( $oUser->email );
                    
                    if ( $error ) {
                        $this->error( EXTERNAL_ERR_SERVER_ERROR );
                    }
                }
                else {
                    $this->error( EXTERNAL_ERR_REMIND_PHONE_ONLY );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_USER_NOTFOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_USERNAME );
        }
        
        return array();
    }
    
    /**
     * Забыли пароль. Телефон.
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_forgot_phone( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $sLogin = __paramValue( 'string', $aParams['username'], null, true );
        
        if ( !empty($sLogin) ) {
            require_once( ABS_PATH . '/classes/sms_gate_a1.php' );
            require_once( ABS_PATH . '/classes/sbr_meta.php' );
            require_once( ABS_PATH . '/classes/users.php' );
            
            // проверяем существует ли пользователь с таким логином
            $oUser = new users();
            $oUser->GetUser( $sLogin );
            
            if ( $oUser->uid ) {
                $safety = sbr_meta::findSafetyPhoneByLogin( $sLogin );
                
                if ( $safety ) {
                    $passwd   = users::ResetPasswordSMS( $safety['uid'], $safety['phone'] );
                    $sms_gate = new sms_gate_a1( $safety['phone'] );
                    $sms_gate->sendSMS( $sms_gate->getTextMessage(sms_gate::TYPE_PASS, $passwd) );
                    
                    if ( $sms_gate->getHTTPCode() != 200 ) {
                        $this->error( EXTERNAL_ERR_SEND_SMS );
                    }
                }
                else {
                    $this->error( EXTERNAL_ERR_PHONE_NOT_FOUND );
                }
            }
            else {
                $this->error( EXTERNAL_ERR_USER_NOTFOUND );
            }
        }
        else {
            $this->error( EXTERNAL_ERR_EMPTY_USERNAME );
        }
        
        return array();
    }
    
    /**
     * Регистрация. Заполнение обязательных полей.
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_signup_required( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once( ABS_PATH . '/classes/registration.php' );
        $registration = new registration();
        
        if ( !$registration->actionSaveInfoMobile($aParams) ) {
            if ( !empty($registration->error['uname']) ) {
                switch ( $registration->errno['uname'] ) {
                    case 1: $this->error( EXTERNAL_ERR_EMPTY_FIRSTNAME );   break;
                    case 2: $this->error( EXTERNAL_ERR_INVALID_FIRSTNAME ); break;
                }
            }
            
            if ( !empty($registration->error['usurname']) ) {
                switch ( $registration->errno['usurname'] ) {
                    case 1: $this->error( EXTERNAL_ERR_EMPTY_LASTNAME );   break;
                    case 2: $this->error( EXTERNAL_ERR_INVALID_LASTNAME ); break;
                }
            }
            
            if ( !empty($registration->error['birthday']) ) {
                switch ( $registration->errno['birthday'] ) {
                    case 1: $this->error( EXTERNAL_ERR_EMPTY_BIRTHDAY );   break;
                    case 2: $this->error( EXTERNAL_ERR_INVALID_BIRTHDAY ); break;
                }
            }
            
            if ( !empty($registration->error['country']) ) {
                $this->error( EXTERNAL_ERR_EMPTY_COUNTRY );
            }
            
            if ( !empty($registration->error['city']) ) {
                $this->error( EXTERNAL_ERR_EMPTY_CITY );
            }
            
            if ( !empty($registration->error['spec']) ) {
                switch ( $registration->errno['spec'] ) {
                    case 1: $this->error( EXTERNAL_ERR_EMPTY_PROF_ID );    break;
                    case 2: $this->error( EXTERNAL_ERR_PROF_ID_LAST_MOD ); break;
                }
            }
            
            $this->error( EXTERNAL_ERR_SERVER_ERROR );
        }
        
        return array();
    }
    /**
     * Регистрация. Подтверждение.
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_signup_complete( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        $aResult = array();
        
        require_once( ABS_PATH . '/classes/registration.php' );
        $registration = new registration();
        $oUser = $registration->actionRegistrationMobileComplete( $aParams );

        if ( !empty($oUser->uid) ) { // пользователь существует
            // формируем ответ --------------
            $aData      = array();
            $aClassVars = array_keys( get_class_vars('users') );

            foreach ( $aClassVars as $sVar ) {
                $aData[$sVar] = $oUser->$sVar;
            }

            if ( !is_emp($aData['role']) ) {
                require_once( ABS_PATH . '/classes/freelancer.php' );
                $aTmp = freelancer::getFreelancerByLogin( array($aData['login']) );
                $aData['spec'] = $aTmp[0]['spec'];
            }

            $aResult = $this->_getUserData( $aData, true );

            // логиним ----------------------
            login( $oUser->login, $oUser->GetField($oUser->uid, $error, "passwd"), 0, false );

            switch($this->_sPlatform) {
                case 'ios':
                case 'android':
                    $sql = "SELECT id FROM external_m_push_settings WHERE device_id = ? AND device_type = ? AND user_id = ?i";
                    $settings_id = $DB->val($sql, $this->_sUdid, $this->_sPlatform, $oUser->uid);
                    if(!$settings_id) {
                        $sql = "INSERT INTO external_m_push_settings (
                                            device_id, 
                                            device_type, 
                                            user_id, 
                                            message_new, 
                                            project_response_new, 
                                            project_select_candidate, 
                                            project_select_performer, 
                                            project_select_reject
                                            ) VALUES (
                                            ?,
                                            ?,
                                            ?i,
                                            't',
                                            't',
                                            't',
                                            't',
                                            't'
                                            )";
                                $DB->query($sql, $this->_sUdid, $this->_sPlatform, $oUser->uid);
                    }
                    break;
            }
            users::regVisit();
        }
        else {
            $this->_setLoginError( $registration );
            $this->_setEmailError( $registration );
            $this->_setPasswordError( $registration );
            $this->_setPhoneError( $registration );
            $this->_setRoleError( $registration );

            if ( !empty($registration->error['exceed_max_reg_ip']) ) {
                $this->error( EXTERNAL_ERR_EXCEED_MAX_REG_IP );
            }

            if ( !empty($registration->error['smscode']) ) {
                $this->error( EXTERNAL_ERR_INVALID_SMS_CODE );
            }
        }
        
        return $aResult;
    }
    
    /**
     * Регистрация. Выслать SMS еще раз.
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_signup_sms_resend( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once( ABS_PATH . '/classes/registration.php' );
        $registration = new registration();

        if ( !$registration->actionResendSmsMobile($aParams) ) {
            $this->_setLoginError( $registration );
            $this->_setPhoneError( $registration );

            if ( !empty($registration->error['actionSendSms']) ) {
                $this->error( EXTERNAL_ERR_SEND_SMS );
            }
        }
        
        return array();
    }
    /**
     * Регистрация. Начало.
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_signup( $aParams = array() ) {
        $this->_validDevice( $aParams );
        
        require_once( ABS_PATH . '/classes/registration.php' );
        $registration = new registration();

        if ( !$registration->actionRegistrationMobile($aParams) ) {
            $this->_setLoginError( $registration );
            $this->_setEmailError( $registration );
            $this->_setPasswordError( $registration );
            $this->_setPhoneError( $registration );
            $this->_setRoleError( $registration );

            if ( !empty($registration->error['actionSendSms']) ) {
                $this->error( EXTERNAL_ERR_SEND_SMS );
            }

            $this->error( EXTERNAL_ERR_SERVER_ERROR ); // неизвестная ошибка
        }
        
        return array();
    }

    /**
     * Выход
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_signout( $aParams = array() ) {
        global $DB;

        $this->_validDevice( $aParams );
        
        $aResult = array();

        if(get_uid(false)) {
            $sql = "DELETE FROM external_m_devices WHERE device_id=? AND user_id=?";
            $DB->query($sql, $this->_sUdid, get_uid(false));
            uncookie();
            session_unset();
            session_destroy();
            session_write_close();
        } else {
            $this->error( EXTERNAL_ERR_USER_NO_AUTH );
        }

        return $aResult;
    }

    /**
     * Проверка существования пользователя
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_exists( $aParams = array() ) {
        global $DB;
        $this->_validDevice( $aParams );

        $aResult = array();
        
        require_once( ABS_PATH . '/classes/users.php' );
        $sLogin  = strtolower(strip_tags( trim($aParams['username']) ));
        
        if ( empty($sLogin) ) {
            $this->error( EXTERNAL_ERR_EMPTY_USERNAME );
        }
        else {
            $users = new users();
            $users->GetUser($sLogin, true, $sLogin);
            
            $aResult['exists'] = ($users->login == $sLogin || $users->email == $sLogin);
        }
        
        return $aResult;
    }

    /**
     * Авторизация
     * 
     * @param  array $aParams массив входящих данных
     * @return array $aResult ответ
     */
    protected function x____users_signin( $aParams = array() ) {
        global $DB;
        $this->_validDevice( $aParams );
        
        $aResult = array();
        
        require_once( ABS_PATH . '/classes/users.php' );
        $sLogin  = strip_tags( trim($aParams['username']) );
        $sPasswd = users::hashPasswd( trim(stripslashes($aParams['password'])), 1 );

        if ( empty($sLogin) ) {
            $this->error( EXTERNAL_ERR_EMPTY_USERNAME );
        }
        elseif ( empty($sPasswd) ) {
            $this->error( EXTERNAL_ERR_EMPTY_PASSWORD );
        }
        else {
            $nResult = login( $sLogin, $sPasswd, 0, false );

            switch ( $nResult ) {
                case  0: $this->error( EXTERNAL_ERR_WRONG_AUTH );     break;
                case -1: $this->error( EXTERNAL_ERR_USER_BANNED );    break;
                case -2: $this->error( EXTERNAL_ERR_USER_NOTACTIVE ); break;
                case -3: $this->error( EXTERNAL_ERR_USER_DENYIP );    break;
                default:
                    $sClassName = is_emp($_SESSION['role']) ? 'employer' : 'freelancer';

                    require_once( ABS_PATH . '/classes/'. $sClassName .'.php' );

                    $aData      = array();
                    $aClassVars = array_keys( get_class_vars($sClassName) );
                    $oUser      = new $sClassName();
                    $oUser->GetUserByUID( $nResult );
                    users::regVisit();

                    foreach ( $aClassVars as $sVar ) {
                        $aData[$sVar] = $oUser->$sVar;
                    }

                    $aResult = $this->_getUserData( $aData, true );

                    session_write_close();

                    if($this->_sPlatform=='ios' || $this->_sPlatform=='android') {
                        if($this->_sPlatform=='ios') {
                            $sql = "UPDATE external_m_devices SET user_id = ?i WHERE device_id = ?";
                            $DB->query($sql, $aData['uid'], $this->_sUdid);
                        }
                        $sql = "SELECT id FROM external_m_push_settings WHERE device_id = ? AND device_type = ? AND user_id = ?i";
                        $settings_id = $DB->val($sql, $this->_sUdid, $this->_sPlatform, $aData['uid']);
                        if(!$settings_id) {
                            $sql = "INSERT INTO external_m_push_settings (
                                            device_id, 
                                            device_type, 
                                            user_id, 
                                            message_new, 
                                            project_response_new, 
                                            project_select_candidate, 
                                            project_select_performer, 
                                            project_select_reject
                                        ) VALUES (
                                            ?,
                                            ?,
                                            ?i,
                                            't',
                                            't',
                                            't',
                                            't',
                                            't'
                                        )";
                            $DB->query($sql, $this->_sUdid, $this->_sPlatform, $aData['uid']);
                        }
                    }

                    break;
            }
        }
        
        return $aResult;
    }
    
    /**
     * Возвращает часть ответа с информацией о личном сообщении
     * 
     * @param  array $aData информация о личном сообщении
     * @return array
     */
    private function _getMessageData( $aData = array() ) {

        $msg = array(
            'id'           => $aData['id'],
            'from_id' => $aData['from_id'],
            'to_id'   => $aData['to_id'],
            'text'         => $aData['msg_text'] ? iconv( 'cp1251', 'utf-8', $aData['msg_text'] ) : '',
            'status'       => $aData['deleted'] ? 2 : 1,
            'read'         => strtotime($aData['read_time']) && $aData['read_time']!='1970-01-01 00:00:00'  ? 1 : 0,
            'create_time'  => strtotime($aData['post_time']),
            //'update_time'  => $aData['read_time'] ? strtotime($aData['modified']) : strtotime($aData['post_time'])
            'update_time'  => strtotime($aData['read_time']) && $aData['read_time']!='1970-01-01 00:00:00' ? strtotime($aData['read_time']) : strtotime($aData['post_time'])
        );
        $msg['files'] = array();
        if($aData['files']) {
            foreach($aData['files'] as $f) {
                $msg['files'][] = array('fname'=>$f['fname'], 'size'=>$f['size'], 'path'=>WDCPREFIX.'/'.$f['path']);
            }
        }
        return $msg;
    }
    
    /**
     * Возвращает часть ответа с информацией о работе в портфолио
     * 
     * @param  array $aData все портфолио юзера
     * @param  int $nUid UID юзера владельца портфолио
     * @return array
     */
    private function _getPortfolioData( $aPortfolio = array(), $nUid = 0 ) {
        $aReturn = array();
        
        if (is_array($aPortfolio) && $aPortfolio ) {
            require_once( ABS_PATH . '/classes/freelancer.php' );
            $freelancer = new freelancer();
            $login = $freelancer->GetField( $nUid, $error, 'login' );
            
            foreach ($aPortfolio as $aOne) {
                $sExt = pathinfo( $aOne['pict'], PATHINFO_EXTENSION );
                
                if ( !in_array($sExt, $GLOBALS['graf_array']) || $sExt == 'swf' || $aOne['is_video'] == 't' || $aOne['is_blocked'] == 't' ) {
                    continue;
                }
				
				$nUpdateTime = $aOne['edit_date'] ? strtotime($aOne['edit_date']) : 0;
                
                $aReturn[] = array(
                    "id"                => $aOne['id'],
                    "user_id"           => $aOne['user_id'],
                    "category_group_id" => $aOne['prof_group_id'],
                    "category_id"       => $aOne['prof_id'],
                    "title"             => $aOne['name'] ? iconv( 'cp1251', 'utf-8', $aOne['name'] ) : '',
                    "sequence"          => $aOne['norder'],
                    "image"             => array(
                        "url"  => WDCPREFIX . '/users/'. $login .'/upload/',
                        "file" => $aOne['prev_pict'] ? substr( $aOne['prev_pict'], 5 ) . '?'. $nUpdateTime : '',
						"file_big" => $aOne['pict'] ? substr( $aOne['pict'], 2 ) . '?'. $nUpdateTime : '',
                    ),
                    "status"            => 1, // $aOne[?] 
                    "create_time"       => strtotime($aOne['post_date']),
                    "update_time"       => $nUpdateTime,
                );
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает часть ответа с информацией о предложении в конкурсе
     * 
     * @param  array $aData информация о предложении в конкурсе
     * @param  bool $bShowEmail включить пользователя Email в ответ
     * @return array
     */
    private function _getContestOfferData( $aData = array(), $bShowEmail = false ) {
        $nUid    = get_uid( false );
        $nStatus = ( $aData['is_deleted'] || $aData['is_blocked'] == 't' ) ? 2 : 1;
        $nSelect = 0; // исполнитель кандидат отказ
        
        if ( $aData['selected'] == 't' ) {
            $nSelect = 2; // кандидат
        }
        
        $aReturn = array(
            'id'          => $aData['id'],
            'project_id'  => $aData['project_id'],
            'comment'     => $aData['descr'] ? iconv( 'cp1251', 'utf-8', $aData['descr'] ) : '',
            'position'    => $aData['position'],
            'select'      => $nSelect,
            'status'      => $nStatus,
            'user_id'     => $aData['user_id'],
            'user'        => $this->_getUserData( $aData, $bShowEmail ),
            'create_time' => strtotime($aData['post_date']),
            'update_time' => $aData['modified'] ? strtotime($aData['modified']) : 0
        );
        
        return $aReturn;
    }
    
    /**
     * Возвращает часть ответа с информацией о предложении в проекте
     * 
     * @param  array $aData информация о предложении в проекте
     * @param  bool $bShowEmail включить пользователя Email в ответ
     * @return array
     */
    private function _getProjectOfferData( $aData = array(), $bShowEmail = false ) {
        $nUid    = get_uid( false );
        $nStatus = ( $aData['frl_refused'] && $aData['user_id'] != $nUid || $aData['is_deleted'] || $aData['is_blocked'] == 't' ) ? 2 : 1;
        $nSelect = 0; // исполнитель кандидат отказ
        
        if ( $nUid == $aData['emp_uid'] || $nUid == $aData['user_id'] ) {
            if ( $aData['user_id'] == $aData['exec_id'] ) {
                $nSelect = 3; // исполнитель
            }
            elseif ( $aData['selected'] == 't' ) {
                $nSelect = 2; // кандидат
            }
            elseif ( $aData['refused'] == 't' ) {
                $nSelect = 1; // отказ
            }
            elseif ( $aData['frl_refused'] == 't' ) {
                $nSelect = 4; // фрилансер отказался
            }
        }
        
        $aReturn = array(
            'id'             => $aData['id'],
            'project_id'     => $aData['project_id'],
            'comment'        => $aData['dialogue'][0]['post_text'] ? iconv( 'cp1251', 'utf-8', $aData['dialogue'][0]['post_text'] ) : '',
            'budget'         => $aData['cost_from'],
            'currency'       => $aData['cost_type'],
            'term'           => $aData['time_from'],
            'term_dimension' => $aData['time_type'],
            'only_customer'  => $aData['only_4_cust'] ? 1 : 0,
            'select'         => $nSelect,
            'status'         => $nStatus,
            'user_id'        => $aData['user_id'],
            'user'           => $this->_getUserData( $aData, $bShowEmail ),
            'create_time'    => strtotime($aData['post_date']),
            'update_time'    => $aData['modified'] ? strtotime($aData['modified']) : 0
        );
        
        return $aReturn;
    }
    
    /**
     * Возвращает часть ответа с информацией о проекте
     * 
     * @param  array $aData информация о проекте
     * @param  bool $bShowEmail включить пользователя Email в ответ
     * @return array
     */
    private function _getProjectData( $aData = array(), $bShowEmail = false ) {
        $aReturn = array(
            'id'               => $aData['id'],
            'kind'             => $aData['kind'],
            'title'            => $aData['name'] ? iconv( 'cp1251', 'utf-8', $aData['name'] ) : '',
            'descr'            => $aData['descr'] ? iconv( 'cp1251', 'utf-8', $aData['descr'] ) : '',
            'budget_agreement' => empty($aData['cost']) ? 1 : 0,
            'budget'           => $aData['cost'],
            'currency'         => $aData['currency'],
            'dimension'        => $aData['priceby'],
            'only_pro'         => $aData['pro_only'] == 't' ? 1 : 0,
            'only_verified'    => $aData['verify_only'] == 't' ? 1 : 0,
            'country_id'       => $aData['country'], 
            'city_id'          => $aData['city'], 
            'status'           => ($aData['closed'] == 't' || $aData['is_banned'] || $aData['is_blocked']) ? 2 : 1, 
            'user_id'          => $aData['uid'], 
            'user'             => $this->_getUserData( $aData, $bShowEmail ),
            'create_time'      => strtotime( $aData['create_date'] ),
            'update_time'      => $aData['edit_date'] ? strtotime( $aData['edit_date'] ) : 0
        );
        
        return $aReturn;
    }
    
    /**
     * Возвращает часть ответа с информацией о пользователе
     * 
     * @param  array $aData информация о пользователе из базы
     * @param  bool $bShowEmail включить пользователя Email в ответ
     * @param  bool $bExtended получить расширенную информацию (аналог профиля на сайте)
     * @return array
     */
    private function _getUserData( $aData = array(), $bShowEmail = false, $bExtended = false ) {
        $this->_oSession->view_online_status( $aData['login'], false );

        $u = new users();
        if ( !is_emp($aData['role']) ) {
            require_once( ABS_PATH  . '/classes/professions.php' );
            $prof_id = $aData['spec'] ? $aData['spec'] : 0;
            $prof_group_id = $aData['spec'] ? professions::GetProfField($aData['spec'], 'prof_group') : 0;
        } else {
            $prof_id = 0;
            $prof_group_id = 0;
        }

        $aReturn = array(
            'id'          => $aData['uid'],
            'status'      => $aData['self_deleted'] == 't' ? 2 : 1,
            'username'    => $aData['login'] ? iconv( 'cp1251', 'utf-8', $aData['login'] ) : '',
            'firstname'   => $aData['uname'] ? iconv( 'cp1251', 'utf-8', $aData['uname'] ) : '',
            'lastname'    => $aData['usurname'] ? iconv( 'cp1251', 'utf-8', $aData['usurname'] ) : '',
            'role'        => is_emp($aData['role']) ? 2 : 1,
            'pro'         => $aData['is_pro'] == 't' ? 1 : 0,
            'verified'    => $aData['is_verify'] == 't' ? 1 : 0,
            'online'      => $this->_oSession->is_active ? 1 : 0,
            'spec'        => $aData['spec'] ? $aData['spec'] : 0,
            'avatar'      => array(
                'url'  => $aData['photo'] ? WDCPREFIX . '/users/'. $aData['login'] .'/foto/' : '',
                'file' => $aData['photo'] ? substr( $aData['photo'], 2 ) . '?'. strtotime($aData['photo_modified_time'] ) : '',
            ),
            'gender'      => $aData['sex'] == 't' ? 1 : ( $aData['sex'] == 'f' ? 2 : 0 ),
            'country_id'  => intval( $aData['country'] ),
            'city_id'     => intval( $aData['city'] ),
            'age'         => ElapsedYears(strtotime( $u->GetField($aData['uid'], $e, 'birthday') )) ,
            'birthday'    => $u->GetField($aData['uid'], $e, 'birthday'),
            'prof_id'     => $prof_id,
            'prof_group_id' => $prof_group_id,
            'create_time' => strtotime( $aData['reg_date'] ),
            'update_time' => $aData['modified_time'] ? strtotime( $aData['modified_time'] ) : 0
        );
        
        if ( $bShowEmail ) {
            $aReturn['email'] = $aData['email'] ? iconv( 'cp1251', 'utf-8', $aData['email'] ) : '';
        }
        
        if ( $bExtended ) {
            require_once( ABS_PATH  . '/classes/rating.php' );
            require_once( ABS_PATH . '/classes/teams.php' );
            
            $rating = new rating( $aData['uid'], $aData['is_pro'], $aData['is_verify'], @$aData['is_profi'], 1 );
            $team   = new teams();
            
            $aReturn['rating']     = rating::round( $rating->data['total'] );
            $aReturn['favorite']   = $team->teamsIsInFavorites( $_SESSION['uid'], $aData['uid'] );
            $aReturn['contacts']   = is_view_contacts( get_uid(false) ) ? $this->_getUserContactsData( $aData ) : array();
            $aReturn['reviews']    = $this->_getUserOpinionsData( $aData );
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает часть ответа с отзывами о пользователе
     * 
     * @param  array $aData информация о отзывами о пользователе
     * @return array
     */
    private function _getUserOpinionsData( $aData ) {
        require_once( ABS_PATH . '/classes/paid_advices.php' );
        require_once( ABS_PATH . '/classes/opinions.php' );
        require_once( ABS_PATH . '/classes/sbr_meta.php' );
        require_once( ABS_PATH . '/classes/sbr.php' );
        
        $aReturn = array();
        $msgs    = sbr::getUserFeedbacks( $aData['uid'], is_emp($aData['role']), false, 0, false, false ); // рекомендации
        $msgs2   = opinions::GetMsgs( $aData['uid'], null, null, null, $error, 'users', false, 0, 0 );     // мнения
            
        if (is_array($msgs) && $msgs ) {
            foreach ( $msgs as $theme ) {
                $oUser = new users();
                $oUser->GetUserByUID( $theme['fromuser_id'] );
                $taData      = array();
                $taClassVars = array_keys( get_class_vars('users') );
                foreach ( $taClassVars as $sVar ) {
                    $taData[$sVar] = $oUser->$sVar;
                }
                if ( !is_emp($taData['role']) ) {
                    require_once( ABS_PATH . '/classes/freelancer.php' );
                    $aTmp = freelancer::getFreelancerByLogin( array($taData['login']) );
                    $taData['spec'] = $aTmp[0]['spec'];
                }

                $aReturn[] = array(
                    "id"           => $theme['id'],
                    'type'         => $theme['is_payed'] ? 2 : 1,
                    "from_user_id" => $theme['fromuser_id'],
                    "to_user_id"   => $theme['touser_id'],
                    "rate"         => $theme['sbr_rating'],
                    "text"         => $theme['descr'] ? iconv( 'cp1251', 'utf-8', $theme['descr'] ) : '', 
                    "status"       => 1, //$theme[''],
                    "create_time"  => strtotime($theme['posted_time']),
                    "update_time"  => $theme[''],
                    "user"         => $this->_getUserData($taData),
                );
            }
        }
        
        if (is_array($msgs2) && $msgs2 ) {
            foreach ( $msgs2 as $opinion ) {
                $oUser = new users();
                $oUser->GetUserByUID( $opinion['fromuser_id'] );
                $taData      = array();
                $taClassVars = array_keys( get_class_vars('users') );
                foreach ( $taClassVars as $sVar ) {
                    $taData[$sVar] = $oUser->$sVar;
                }
                if ( !is_emp($taData['role']) ) {
                    require_once( ABS_PATH . '/classes/freelancer.php' );
                    $aTmp = freelancer::getFreelancerByLogin( array($taData['login']) );
                    $taData['spec'] = $aTmp[0]['spec'];
                }

                $aReturn[] = array(
                    "id"           => $opinion['id'],
                    'type'         => 2,
                    "from_user_id" => $opinion['fromuser_id'],
                    "to_user_id"   => $opinion['touser_id'],
                    "rate"         => $opinion['rating'], // 1 - положительный, 0 - нейтральный, -1 - отрицательный
                    "text"         => $opinion['msgtext'] ? iconv( 'cp1251', 'utf-8', $opinion['msgtext'] ) : '',
                    "status"       => 1, // $opinion[''],
                    "create_time"  => strtotime($opinion['post_time']),
                    "update_time"  => $opinion['modified'] ? strtotime($opinion['modified']) : 0,
                    "user"         => $this->_getUserData($taData),
                );
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает часть ответа с контактной информацией о пользователе
     * 
     * @param  array $aData информация о пользователе из базы
     * @return array
     */
    private function _getUserContactsData( $aData ) {
        $aReturn = array();
        $aThree  = array( 'email', 'icq', 'skype', 'jabber', 'lj', 'site', 'phone' );
        $aZero   = array( 'email' => 'second_email', 'lj' => 'ljuser' );
        
        foreach ( $aThree as $sFld ) {
            $aFields = array();
            
            if ( !in_array($sFld, array_keys($aZero)) ) {
                if ( !empty($aData[$sFld]) ) {
                    $aFields[] = $aData[$sFld];
                }
            }
            else {
                if ( !empty($aData[$sFld]) ) {
                    $aFields[] = $aData[$aZero[$sFld]];
                }
            }
            
            for ( $i=1; $i<=3; $i++ ) {
                if ( !empty($aData[$sFld . '_' . $i]) ) {
                    $aFields[] = $aData[$sFld . '_' . $i];
                }
            }
            
            if ( !empty($aFields) ) {
                $aReturn[$sFld] = $aFields;
            }
        }
        
        return $aReturn;
    }

    /**
     * Проверка устройства на валидность (платформа, уникальный индификатор устройства)
     * Устанавливает соответствующие переменные класса
     * 
     * @param  array $aParams массив входящих данных
     * @return bool true - успех, false - провал
     */
    private function _validDevice( $aParams = array() ) {
        if ( !empty($this->_sUdid) && !empty($this->_sPlatform) ) {
            return true;
        }
        
        $sUdid     = __paramValue( 'string', $aParams['udid'], null, true );  // Уникальный индификатор устройства (используется для Push сообщений)
        $sPlatform = __paramValue( 'string', $aParams['agent'], null, true ); // Тип устройства: ios, android
        
        if ( empty($sUdid) ) {
            $this->error( EXTERNAL_ERR_EMPTY_UDID );
        }
        else {
            if ( empty($sPlatform) ) {
                $this->error( EXTERNAL_ERR_EMPTY_AGENT );
            }
            elseif ( !in_array($sPlatform, array_keys(self::$_aPlatform)) ) {
                $this->error( EXTERNAL_ERR_INVALID_AGENT );
            }
        }
        
        $this->_sUdid     = $sUdid;
        $this->_sPlatform = $sPlatform;
        
        return true;
    }
    
    /**
     * Устанавливает ошибки для параметра Логин
     * 
     * @param type $obj
     */
    private function _setLoginError( $obj ) {
        if ( !empty($obj->error['login']) ) {
            switch ( $obj->errno['login'] ) {
                case 1: $this->error( EXTERNAL_ERR_INVALID_USERNAME );  break;
                case 2: $this->error( EXTERNAL_ERR_ILLEGAL_USERNAME );  break;
                case 3: $this->error( EXTERNAL_ERR_EXISTS_USERNAME );   break;
                case 4: $this->error( EXTERNAL_ERR_USER_NOTFOUND );     break;
                case 5: $this->error( EXTERNAL_ERR_USER_ACTIVATED );    break;
                case 6: $this->error( EXTERNAL_ERR_MISMATCH_USERNAME ); break;
            }
        }
    }
    
    /**
     * Устанавливает ошибки для параметра Email
     * 
     * @param type $obj
     */
    private function _setEmailError( $obj ) {
        if ( !empty($obj->error['email']) ) {
            switch ( $obj->errno['email'] ) {
                case 1: $this->error( EXTERNAL_ERR_INVALID_EMAIL ); break;
                case 2: $this->error( EXTERNAL_ERR_ILLEGAL_EMAIL ); break;
                case 3: $this->error( EXTERNAL_ERR_EXISTS_EMAIL );  break;
            }
        }
    }


    /**
     * Устанавливает ошибки для параметра Пароль
     * 
     * @param type $obj
     */
    private function _setPasswordError( $obj ) {
        if ( !empty($obj->error['password']) ) {
            switch ( $obj->errno['password'] ) {
                case 1: $this->error( EXTERNAL_ERR_EMPTY_PASSWORD );   break;
                case 2: $this->error( EXTERNAL_ERR_LENGTH_PASSWORD );  break;
                case 3: $this->error( EXTERNAL_ERR_LENGTH_PASSWORD );  break;
                case 4: $this->error( EXTERNAL_ERR_INVALID_PASSWORD ); break;
            }
        }
    }


    /**
     * Устанавливает ошибки для параметра Логин
     * 
     * @param type $obj
     */
    private function _setPhoneError( $obj ) {
        if ( !empty($obj->error['phone']) ) {
            switch ( $obj->errno['phone'] ) {
                case 1: $this->error( EXTERNAL_ERR_MISMATCH_PHONE ); break;
                case 2: $this->error( EXTERNAL_ERR_EMPTY_PHONE );    break;
                case 3: $this->error( EXTERNAL_ERR_EXISTS_PHONE );   break;
            }
        }
    }
    
    /**
     * Устанавливает ошибки для параметра Роль
     * 
     * @param type $obj
     */
    private function _setRoleError( $obj ) {
        if ( !empty($obj->error['role']) ) {
            switch ( $obj->errno['role'] ) {
                case 1: $this->error( EXTERNAL_ERR_EMPTY_ROLE );   break;
                case 2: $this->error( EXTERNAL_ERR_INVALID_ROLE ); break;
            }
        }
    }

    /**
     * Добавляет PUSH сообщение в очередь
     *
     * @param    integer    $user_id    ID пользователя
     * @param    string     $type       Тип сообщения
     * @param    array      $data       Данные сообщения
     */
    public function addPushMsg($user_id, $type, $data = array()) {
        global $DB;

        $sql = "SELECT * FROM external_m_devices WHERE user_id=?";
        $pushes = $DB->rows($sql, $user_id);

        if($pushes) {
            foreach ($pushes as $push) {
                $sql = "SELECT * FROM external_m_push_settings WHERE device_id=? AND user_id=?";
                $push_settings = $DB->row($sql, $push['device_id'], $user_id);
                $msg = '';
                $pdata = array();
                require_once($_SERVER['DOCUMENT_ROOT']."/classes/users.php");
                switch($type) {
                    case 'message':
                        if($push_settings['message_new']=='t') {
                            $user = new users;
                            $name  = $user->GetField($data['from_user_id'], $err, 'uname').' '.$user->GetField($data['from_user_id'], $err, 'usurname').' ['.$user->GetField($data['from_user_id'], $err, 'login').']';
                            $msg = 'У вас новое сообщение от '.$name;
                            $type = 1;
                            $pdata['type'] = 1;
                            $pdata['text'] = $msg;
                            $pdata['user_login'] = $user->GetField($data['from_user_id'], $err, 'login');
                            $pdata['user_id'] = $data['from_user_id'];
                        }
                        break;
                    case 'prj_response':
                        if($push_settings['project_response_new']=='t') {
                            $user = new users;
                            $msg = 'У вас новое предложение в проекте "'.$data['name'].'"';
                            $type = 2;
                            $pdata['type'] = 2;
                            $pdata['text'] = $msg;
                            $pdata['project_id'] = $data['project_id'];
                        }
                        break;
                    case 'prj_select_candidate':
                        if($push_settings['project_select_candidate']=='t') {
                            $user = new users;
                            $name  = $user->GetField($data['from_user_id'], $err, 'uname').' '.$user->GetField($data['from_user_id'], $err, 'usurname').' ['.$user->GetField($data['from_user_id'], $err, 'login').']';
                            $msg = $name.' выбрал вас кандидатом в проекте "'.$data['name'].'"';
                            $type = 3;
                            $pdata['type'] = 3;
                            $pdata['text'] = $msg;
                            $pdata['project_id'] = $data['project_id'];
                        }
                        break;
                    case 'prj_select_performer':
                        if($push_settings['project_select_performer']=='t') {
                            $user = new users;
                            $name  = $user->GetField($data['from_user_id'], $err, 'uname').' '.$user->GetField($data['from_user_id'], $err, 'usurname').' ['.$user->GetField($data['from_user_id'], $err, 'login').']';
                            $msg = $name.' выбрал вас исполнителем в проекте "'.$data['name'].'"';
                            $type = 4;
                            $pdata['type'] = 4;
                            $pdata['text'] = $msg;
                            $pdata['project_id'] = $data['project_id'];
                        }
                        break;
                    case 'prj_select_reject':
                        if($push_settings['project_select_reject']=='t') {
                            $user = new users;
                            $name  = $user->GetField($data['from_user_id'], $err, 'uname').' '.$user->GetField($data['from_user_id'], $err, 'usurname').' ['.$user->GetField($data['from_user_id'], $err, 'login').']';
                            $msg = $name.' отказал вам в проекте "'.$data['name'].'"';
                            $type = 5;
                            $pdata['type'] = 5;
                            $pdata['text'] = $msg;
                            $pdata['project_id'] = $data['project_id'];
                        }
                        break;
                }
                if($msg) {
                    
                    $pdata['text'] = iconv('CP1251', 'UTF-8//IGNORE', $pdata['text']);
                    
                    switch($push['device_type']) {
                        case 'android':
                            $dev_data = unserialize(base64_decode($push['data']));
                            $pdata['device_reg_id'] = $dev_data['device_id'];
                            self::sendPushAndroid($pdata);
                            break;
                        
                        /*
                         * @todo: Вырубил iOS Push сообщения так приложение нет AppStore
                         * и постоянно возникает ошибка соединение на сокет
                        case 'ios':
                            $dev_data = unserialize(base64_decode($push['data']));
                            $pdata['devicetoken'] = $dev_data['devicetoken'];
                            self::sendPushiOS($pdata);
                            break;
                         */
                    }
                }
            }
        }
    }

    /**
     * Отправка PUSH для Android
     *
     * @param    array    $data    Данные сообщения
     */
    public function sendPushAndroid($data) {
        $apiKey = self::GOOGLE_APIKEY;
        $registrationIDs = array($data['device_reg_id']);
        $message = $data['text'];
        $url = 'https://android.googleapis.com/gcm/send';

        switch($data['type']) {
            case 1:
                $fields = array(
                        'registration_ids'  => $registrationIDs,
                        'data'              => array( "message" => $data['text'], "type" => 1, "user_id" => $data['user_id'], "user_login" => $data['user_login'] ),
                        );
                break;
            case 2:
                $fields = array(
                        'registration_ids'  => $registrationIDs,
                        'data'              => array( "message" => $data['text'], "type" => 2, "project_id" => $data['project_id'] ),
                        );
                break;
            case 3:
                $fields = array(
                        'registration_ids'  => $registrationIDs,
                        'data'              => array( "message" => $data['text'], "type" => 3, "project_id" => $data['project_id'] ),
                        );
                break;
            case 4:
                $fields = array(
                        'registration_ids'  => $registrationIDs,
                        'data'              => array( "message" => $data['text'], "type" => 4, "project_id" => $data['project_id'] ),
                        );
                break;
            case 5:
                $fields = array(
                        'registration_ids'  => $registrationIDs,
                        'data'              => array( "message" => $data['text'], "type" => 5, "project_id" => $data['project_id'] ),
                        );
                break;
        }

        $headers = array( 
                    'Authorization: key=' . $apiKey,
                    'Content-Type: application/json'
                );

        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $url );

        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );

        $result = curl_exec($ch);

        curl_close($ch);
    }

    /**
     * Отправка PUSH для iOS
     *
     * @param    array    $data    Данные сообщения
     */
    public function sendPushiOS($data) {
        switch($data['type']) {
            case 1:
                $data['loc-key'] = 'PUSH_MESSAGE_NEW';
                $data['loc-args'] = array();
                $data['loc-args'][] = $data['user_login'];
                $data['fields'] = array();
                $data['fields']['user_id'] = $data['user_id'];
                break;
            case 2:
                $data['loc-key'] = 'PUSH_PROJECT_RESPONSE_NEW';
                $data['loc-args'] = array();
                $data['loc-args'][] = $data['project_id'];
                break;
            case 3:
                $data['loc-key'] = 'PUSH_PROJECT_SELECT_CANDIDATE';
                $data['loc-args'] = array();
                $data['loc-args'][] = $data['project_id'];
                break;
            case 4:
                $data['loc-key'] = 'PUSH_PROJECT_SELECT_CANDIDATE';
                $data['loc-args'] = array();
                $data['loc-args'][] = $data['project_id'];
                break;
            case 5:
                $data['loc-key'] = 'PUSH_PROJECT_SELECT_CANDIDATE';
                $data['loc-args'] = array();
                $data['loc-args'][] = $data['project_id'];
                break;
        }

        $payload = array();

        $payload['aps'] = array('alert' => array('loc-key' => $data['loc-key'], 'loc-args' => $data['loc-args'] ) );
        if($data['fields']) {
            foreach($data['fields'] as $k=>$v) {
                $payload['aps'][$k] = $v;
            }
        }
        $payload = json_encode($payload);

        $apns_url = 'gateway.push.apple.com';
        $apns_cert = $_SERVER['DOCUMENT_ROOT'].'/classes/external/apns.pem';
        $apns_port = 2195;

        $stream_context = stream_context_create();
        stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);
 
        $apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT, $stream_context);
        
        if ($apns) {
            $device_token = $data['devicetoken'];
            $apns_message = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $device_token)) . chr(0) . chr(strlen($payload)) . $payload;
            fwrite($apns, $apns_message);
            @socket_close($apns);
            @fclose($apns);
        }
    }
}