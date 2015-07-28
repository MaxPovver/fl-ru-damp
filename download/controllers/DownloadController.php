<?php

class DownloadController extends CController 
{
    const USER_BASE_PATH = '^users(\/[-a-zA-Z0-9_]{2})?\/([a-zA-Z0-9]+[-a-zA-Z0-9_]{2,})';
    
    protected $uid = 0;
    protected $login;
    protected $permission = 'adm';
    protected $filename;


    /**
     * Доступ к резюме
     * 
     * @todo: метод не обязателен так как файл доступен всем
     * 
     * @param type $params
     * @return boolean
     */
    /*
    protected function _resume($params)
    {
        return true;
    }
    */


   /**
    * Поумолчанию доступно всем авторизованным
    * 
    * - ответы на проекты
    * 
    * @param type $params
    * @return boolean
    */
   protected function _upload($params, CFile $file)
   {
       
       $tableName = $file->getTableName();
       $allow_download = ($this->uid > 0);
       
       
       
       switch ($tableName) {
           
           case 'file_sbr':
               if ($this->uid > 0) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
                    $allow_download = (bool)sbr::isAllowDownloadFile($file->id, $this->uid);
               } else {
                   $allow_download = false;
               }
               break;
           
           default:
               
               //остальные файлы публичные
               $allow_download = true;
               
               break;
       }
       
        
       
       return $allow_download;
   }

   
   /**
    * @todo: пока для этого пути не прописаны рерайты в NGINX!
    * 
    * Файлы проекта доступны всем авторизованным
    *
    * @param type $params
    * @return boolean
    */
   protected function _projects($params)
   {
       return true;
   }

   
   /**
    * Файлы переписки в ЛС доступны участникам и админу
    * 
    * @param type $params
    * @param CFile $file
    * @return type
    */
   protected function _contacts($params, CFile $file)
   {
        //нет необходимости так как в общей таблице
        //$tableName = $file->getTableName();

        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php');
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php';
        
        $allow_download = false;
        $users = new users();
        $from_uid = $users->GetUid($error, $params['login']);
        
        if ($from_uid > 0) {
            $msgObj = new messages;
            $allow_download = $msgObj->isFileExist($from_uid, $this->uid, $file->id);
        }
        
        return $allow_download;
    }

    

    /**
     * Обработки доступа на документы БС в заказе
     * 
     * @param type $params
     * @return type
     */
    protected function _reserves($params)
    {
        return $this->_orders($params);
    }

    /**
     * Доступ к файлам переписки в заказе (возможно по БС)
     * 
     * @param type $params
     * @return type
     */
    protected function _orders($params)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

        $orderModel = TServiceOrderModel::model();
        return $orderModel->isOrderMember($params['order_id'], $this->uid);        
    }

    

    /**
     * Обработки доступа на различные файлы в папке юзера
     * 
     * @param type $params
     * @param CFile $file
     * @return type
     */
    protected function _attach($params, CFile $file)
    {
        $tableName = $file->getTableName();
        $allow_download = false;
        
        //Так как в одной папке файлы разных мастей то для непонятного юзера нужно
        //вычислять что за файл и проверять доступ к нему
        switch ($tableName) {
            
            //файлы БС в сообщениях 
            case 'file_tservice_msg':

                require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
                $this->permission = 'tservices';
                $msg_id = intval($file->src_id);
                $msgModel = TServiceMsgModel::model();
                $allow_download = $msgModel->isMsgMember($msg_id, $this->uid);
                
            break;
        }
        
        return $allow_download;
    }

    
    /**
     * Поумолчанию скачивать ничего не даем
     * если это не пользователь директории или админ 
     * 
     * @param type $params
     * @return boolean
     */
    protected function _default($params)
    {
        return false;
    }

    
    /**
     * Роутинг запросов на скачивание
     * 
     * @return boolean
     */
    protected function routeMaps()
    {
        $routes = array(

            //Документы БС
            'reserves' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/reserves\/(\d+)\//',
                'params' => array('login' => 2, 'order_id' => 3),
                'permission' => 'tservices',
                
                //не проверять и не передавать обьект файл CFile
                //так как всех данных для проверки достаточно из URI
                //это снижает нагрузку на базу
                'is_file' => false 
             ),

            //Перевиска в заказе (новая директория)
            'orders' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/private\/orders\/(\d+)\//',
                'params' => array('login' => 2, 'order_id' => 3),
                'permission' => 'tservices',
                
                //не проверять и не передавать обьект файл CFile
                //так как всех данных для проверки достаточно из URI
                //это снижает нагрузку на базу
                'is_file' => false 
             ),
            
            //Переписка в ЛС
            'contacts' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/contacts\//',
                'params' => array('login' => 2)
            ),

            //Файлы разного назначения (публичные)
            'upload' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/upload\//',
                'params' => array('login' => 2),
                'is_check_auth_in_method' => true
            ),

            //Резюме пользователя (публичная)
            'resume' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/resume\//',
                'params' => array('login' => 2),
                
                //не требуется авторизация файл отдаем всем
                'is_auth' => false
            ),

            //Временные файлы (пока только владельцу и админу) 
            //и сканы паспортов (владельцу и админу) - постепенно выносим в отдельную директорию (см account)
            //и файлы переписки в БС (проверка прав доступа участников и админу) - постепенно выносим в отдельную директорию (см orders)
            'attach' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/attach\//',
                'params' => array('login' => 2)
            ),

            //Файлы сканов (только владельцу и админу)
            'account' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/private\/account\//',
                'params' => array('login' => 2),
                
                'is_file' => false
            ),
            
            //Только админу
            'letters' => array(
                'regex' => '/^letters\//',
                'is_file' => false
            ),
            
            /*
            //Файлы проектов (публичные) - не направляются на проверку
            'projects' => array(
                'regex' => '/projects\/upload\/(\d+)\//',
                'params' => array('project_id' => 1)
            ),*/
        );       

        
        foreach ($routes as $action => $route) {
            
            $match = array();
            
            if (preg_match($route['regex'], $this->filename, $match)) {
                
                $params = array();
                if (isset($route['params'])) {
                    foreach ($route['params'] as $pname => $pidx) {
                        $params[$pname] = $match[$pidx];
                    }
                }

                if (isset($route['permission'])) {
                    $this->permission = $route['permission'];
                }

                $is_file = isset($route['is_file']) && $route['is_file'] === false? false:true;
                $is_auth = isset($route['is_auth']) && $route['is_auth'] === false? false:true;
                $is_check_auth_in_method = isset($route['is_check_auth_in_method']) && $route['is_check_auth_in_method'] === true? true:false;
                
                return array($action, $params, $is_file, $is_auth, $is_check_auth_in_method);
            }
        }

        return false;
    }

    
    

    /**
     * Инициализация контроллера
     */
    public function init($path) 
    {
        parent::init();

        $this->filename = $path? ltrim(parse_url($path, PHP_URL_PATH) ,'/') : null;
        
        if ($this->filename) {
            $this->filename = $this->fixFilename($this->filename);
        }
        
        if (!$this->filename) {
            $this->send404();
        }
        

        $this->uid = isset($_SESSION['uid'])? $_SESSION['uid'] : 0;
        $this->login = isset($_SESSION['login'])? $_SESSION['login'] : '';
    }


    /**
     * Обработка события до какого-либо экшена
     * 
     * @param string $action
     * @return bool
     */
    /*
    public function beforeAction($action) 
    {
    }
    */
    
    
    /**
     * Обработка очевидного доступа по URI для владельцев 
     * или правам админа а в сложном случае направление 
     * на специальную обработку получения прав.
     * По результатам возможна выдача файла.
     */
    public function actionIndex()
    {
        $_data = $this->routeMaps();

        if (!$_data) {
            $this->send404();
        }
        
        $_method = "_{$_data[0]}";
        $_params = $_data[1];
        $_is_file = $_data[2];
        $_is_auth = $_data[3];
        $_is_check_auth_in_method = $_data[4];
        
        //Авторизация не нужна отдем файл всем
        if ($_is_auth === false) {
            $this->sendFile();
        //Инче проверяем авторизацию    
        } elseif (!$this->isCurrentUserAuth() && 
                  !$_is_check_auth_in_method) {
            $this->send404();
        }

        //Если это дериктория текущего юзера то сразу даем скачать
        if (isset($_params['login'])) {
            $allow_download = $this->isCurrentUserLogin($_params['login']);
        } 
        
        //Может это админ тогда у него есть право скачать
        if (!$allow_download) {
            $allow_download = currentUserHasPermissions($this->permission);
        }
        
        //Если нет то тут уже более детальные проверки
        if (!$allow_download) {
            if (method_exists($this, $_method)) {
                
                $GLOBALS['DB'] = new DB('master');
                
                if ($_is_file) {
                    $file = new CFile($this->filename);
                    $allow_download = ($file->id > 0)? $this->{$_method}($_params, $file) : false;
                } else {
                    $allow_download = $this->{$_method}($_params);
                }

            } else {
                $allow_download = $this->_default($_params);
            }
        }

        if ($allow_download) {
            $this->sendFile();
        }
            
        $this->send404();
    }
    
    
    /**
     * Исправляем не корректный путь у папки юзера
     * 
     * @return type
     */
    protected function fixFilename($filename)
    {
        $components = explode('/', $filename);
        $components_cnt = count($components);
        
        //Полный путь для директории пользвателя
        if ($components[0] === 'users' && $components_cnt > 2) {
            if (strlen($components[1]) > 2) {
                array_splice($components, 1, 0, array(substr($components[1], 0, 2)));
                $filename = implode('/',  $components);
            }
        }
        
        return $filename;
    }
    

    protected function isCurrentUserAuth()
    {
        return $this->uid > 0;
    }
    

    protected function isCurrentUserLogin($login)
    {
        return $login === $this->login;
    }
    

    protected function sendFile()
    {
        header('X-Accel-Redirect: /bzqvzvyw/' . $this->filename);
        header("Content-Type:");

        if (is_local()) {
            //header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($this->filename));
        }

        exit;         
    }
    

    protected function send404()
    {
        global $host;
        $this->redirect("{$host}/404.php");
    }
}