<?php
/**
 * Класс обрабатывающий все действия на странице /about/
 *
 */
ob_start();
class page_about extends page_base { 
	/**
	 * Название страницы, необходимо для правильных ссылок в шаблонах.
	 *
	 * @var string
	 */
	public $name_page = "about";
	/**
	 * Переменная необходимая для баннеров (определяет какой тип баннеров выводить на данной странице)
	 *
	 * @var string
	 */
	public $b_page    = "0|2";
	
	/**
	 * Конструктор класса
	 *
	 */
	function __construct() {
	    front::og("tpl")->main_css  = "/css/press-center.css";
		front::og("tpl")->g_page_id = $this->b_page;
            front::og("tpl")->page = 'about';
	}
	/**
	 * Обработчик событий главной страницы /about/
	 *
	 */
    function indexAction() {
        front::og("tpl")->text = static_pages::get("about_index");
        front::og("tpl")->display("about/about_index.tpl");
    }
    
    /*function contactsAction() {
        front::og("tpl")->text = static_pages::get("about_contacts");
        front::og("tpl")->display("about/about_contacts.tpl");
    }*/
    
    function advAction() {
        front::og("tpl")->text = static_pages::get("about_adv");
        front::og("tpl")->css  = "/css/main.css";
        front::og("tpl")->display("about/about_adv.tpl");
    }
    
    /**
     * Обработчик событий страницы /about/logos/ (Логотипы)
     */
    function logosAction() {
        front::og("tpl")->text = static_pages::get("about_logos");
        front::og("tpl")->display("about/about_logos.tpl");
    }
    
    /**
     * Обработчик событий страницы /about/documents/ (Официальные документы)
     */
    function documentsAction() {
        front::og("tpl")->text = static_pages::get("about_documents");
        front::og("tpl")->display("about/about_documents.tpl");
    }
    
    /**
     * Обработчик событий страницы /about/history/ (История)
     *
     */
    /*function historyAction() {
        front::og("tpl")->text = static_pages::get("about_history"); 
        front::og("tpl")->display("about/about_history.tpl");
    }*/
    /**
     * Обработчик событий страницы /about/rules/ (Правила сайта)
     *
     */
    function rulesAction() {
        front::og("tpl")->text = static_pages::get("about_rules"); 
        front::og("tpl")->display("about/about_rules.tpl");
    }
	/**
     * Обработчик событий страницы /about/offer/ (Пользовательское соглашение)
     *
     */
    function offerAction() {
        front::og("tpl")->text = static_pages::get("about_offer"); 
        front::og("tpl")->display("about/about_offer.tpl");
    }
	/**
     * Обработчик событий страницы /about/tpo/ (Требования к ПО)
     *
     */
    function tpoAction() {
        front::og("tpl")->text = static_pages::get("about_tpo"); 
        front::og("tpl")->display("about/about_tpo.tpl");
    }
    /**
     * Обработчик событий страницы /about/team/ (Команда)
     *
     */
    function teamAction() {
        $DB = new DB('master'); 
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/team.php");
        $action = $_POST['action'];
        switch ($action) {
            case 'updatecategory':
                if (!get_uid(false)) { header("Location: /fbd.php"); exit;}
                if (!(hasPermissions('about'))) { header("Location: /about/team"); exit; }
                $id = front::$_req['ecf_id'];
                $name = stripslashes(front::$_req['ecf_name']);
                $position = front::$_req['ecf_number'];
                $error = 0;
                if(empty($name)) {
                    $error = 1;
                    $error_msgs[1] = 'Поле "Название" заполнено некорректно';
                }
                if(!is_numeric($position)) {
                    $error = 1;
                    $error_msgs[2] = 'Поле "Порядок" заполнено некорректно';
                } else {
                    $position = (int) $position;
                    if($position<=0) {
                        $error = 1;
                        $error_msgs[2] = 'Поле "Порядок" заполнено некорректно';
                    }
                }
                $name = change_q($name);
                if($error) {
                    front::og("tpl")->error_msgs_ecf = $error_msgs;
                    front::og("tpl")->ecf_name = $name;
                    front::og("tpl")->ecf_position = $position;
                    front::og("tpl")->ecf_id = $id;
                } else {
                    team::EditGroup($id,$name,$position);
                    header('Location: /about/team/');
                    exit;
                }
                break;
            case 'addpeople':
                if (!get_uid(false)) { header("Location: /fbd.php"); exit;}
                if (!(hasPermissions('about'))) { header("Location: /about/team"); exit; }
                $p_name = stripslashes(front::$_req['pt_name']);
                $p_login = stripslashes(front::$_req['pt_login']);
                $p_occupation = stripslashes(front::$_req['pt_occupation']);
                $p_position = stripslashes(front::$_req['pt_position']);
                $p_group = stripslashes(front::$_req['pt_group']);
                $p_info = stripslashes(front::$_req['pt_info']);
                $p_foto = $_FILES['pt_photo'];
                $error = 0;
                if(empty($p_name)) {
                    $error = 1;
                    $error_msgs[1] = 'Поле "Имя, фамилия" заполнено некорректно';
                }
                if(empty($p_occupation)) {
                    $error = 1;
                    $error_msgs[2] = 'Поле "Должность" заполнено некорректно';
                }
                if(!is_numeric($p_position) && $p_position!='') {
                    $error = 1;
                    $error_msgs[3] = 'Поле "Порядок" заполнено некорректно';
                } else {
                    $p_position = (int) $p_position;
                }
                if(!empty($p_login)) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
                    $u = new users();
                    if(!$u->GetUid($ee,$p_login)) {
                        $error = 1;
                        $error_msgs[4] = 'Поле "Логин" заполнено некорректно';
                    }
                }
                $p_name = change_q($p_name);
                $p_login = change_q($p_login);
                $p_occupation = change_q($p_occupation);
                $p_info = change_q($p_info);

                $p_name = addslashes($p_name);
                $p_occupation = addslashes($p_occupation);
                $p_info = addslashes($p_info);

                $p_foto = '';
                if(!empty($_FILES['pt_photo']['tmp_name'])) {
                    list($i_width, $i_height, $i_type) = @getimagesize($_FILES['pt_photo']['tmp_name']);
                    if(in_array($i_type,array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG)) && $i_width==150 && $i_height==200) {
                        $p_userpic = new CFile($_FILES['pt_photo']);
                       	if ($p_userpic->name){
                       		$e = team::UpdateFoto($p_userpic);
                       		if ($e['error']=='1') {
                                   $error = 1;
                                   $error_msgs[5] = 'Файл не удовлетворяет условиям загрузки';
                                   $p_foto = $e['foto'];
                            } else {
                                $p_foto = $e['foto'];
                                $error_msgs[5] = 'Файл не удовлетворяет условиям загрузки';
                            }
                        }
                    } else {
                        $error = 1;
                        $error_msgs[5] = 'Файл не удовлетворяет условиям загрузки';
                    }
                }


                if($error) {
                    if($p_foto!='') {
                        $p_userpic->Delete(0,'team/'.$p_foto);
                    }
                    front::og("tpl")->error_msgs_apf = $error_msgs;
                    front::og("tpl")->p_name = $p_name;
                    front::og("tpl")->p_login = $p_login;
                    front::og("tpl")->p_occupation = $p_occupation;
                    front::og("tpl")->p_group = $p_group;
                    front::og("tpl")->p_position = $p_position;
                    front::og("tpl")->p_info = $p_info;
                } else {
                    if($p_position<=0) {
                        $max_position = front::og("db")->select("SELECT MAX(position) as position FROM team_people WHERE groupid = ?;", $p_group)->fetchOne();
                        $p_position = $max_position['position']+1;
                    }
                    team::AddUser($p_name, $p_login, $p_occupation, $p_foto, $p_group, $p_position, $p_info);
                    header('Location: /about/team/');
                    exit;
                }
                break;
            case 'updatepeople':
                if (!get_uid(false)) { header("Location: /fbd.php"); exit;}
                if (!(hasPermissions('about'))) { header("Location: /about/team"); exit; }
                $p_id = stripslashes(front::$_req['pt_id']);
                $p_name = stripslashes(front::$_req['pt_name']);
                $p_login = stripslashes(front::$_req['pt_login']);
                $p_occupation = stripslashes(front::$_req['pt_occupation']);
                $p_position = stripslashes(front::$_req['pt_position']);
                $p_group = stripslashes(front::$_req['pt_group']);
                $p_info = stripslashes(front::$_req['pt_info']);
                $p_foto = $_FILES['pt_photo'];
                $error = 0;
                if(empty($p_name)) {
                    $error = 1;
                    $error_msgs[1] = 'Поле "Имя, фамилия" заполнено некорректно';
                }
                if(empty($p_occupation)) {
                    $error = 1;
                    $error_msgs[2] = 'Поле "Должность" заполнено некорректно';
                }
                if(!is_numeric($p_position)) {
                    $error = 1;
                    $error_msgs[3] = 'Поле "Порядок" заполнено некорректно';
                } else {
                    $p_position = (int) $p_position;
                    if($p_position<=0) {
                        $error = 1;
                        $error_msgs[3] = 'Поле "Порядок" заполнено некорректно';
                    }
                }
                if(!empty($p_login)) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
                    $u = new users();
                    if(!$u->GetUid($ee,$p_login)) {
                        $error = 1;
                        $error_msgs[4] = 'Поле "Логин" заполнено некорректно';
                    }
                }
                $p_name = change_q($p_name);
                $p_login = change_q($p_login);
                $p_occupation = change_q($p_occupation);
                $p_info = change_q($p_info);

                $p_name = addslashes($p_name);
                $p_occupation = addslashes($p_occupation);
                $p_info = addslashes($p_info);

                $p_foto = '';
                if(!empty($_FILES['pt_photo']['tmp_name'])) {
                    list($i_width, $i_height, $i_type) = @getimagesize($_FILES['pt_photo']['tmp_name']);
                    if(in_array($i_type,array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG)) && $i_width==150 && $i_height==200) {
                        $p_userpic = new CFile($_FILES['pt_photo']);
                       	if ($p_userpic->name){
                       		$e = team::UpdateFoto($p_userpic);
                       		if ($e['error']=='1') {
                                   $error = 1;
                                   $error_msgs[5] = 'Файл не удовлетворяет условиям загрузки';
                                   $p_foto = $e['foto'];
                            } else {
                                $p_foto = $e['foto'];
                                $error_msgs[5] = 'Файл не удовлетворяет условиям загрузки';
                            }
                        }
                    } else {
                        $error = 1;
                        $error_msgs[5] = 'Файл не удовлетворяет условиям загрузки';
                    }
                }



                if($error) {
                    if($p_foto!='') {
                        $p_userpic->Delete(0,'team/'.$p_foto);
                    }
                    front::og("tpl")->error_msgs_apf = $error_msgs;
                    front::og("tpl")->p_name = $p_name;
                    front::og("tpl")->p_login = $p_login;
                    front::og("tpl")->p_occupation = $p_occupation;
                    front::og("tpl")->p_group = $p_group;
                    front::og("tpl")->p_position = $p_position;
                    front::og("tpl")->p_info = $p_info;
                    front::og("tpl")->p_id = $p_id;
                    front::og("tpl")->p_action = 'updatepeople';
                } else {
                    team::EditUser($p_id, $p_name, $p_login, $p_occupation, $p_foto, $p_group, $p_position, $p_info);
                    header('Location: /about/team/');
                    exit;
                }
                break;
            case 'insertcategory':
                if (!get_uid(false)) { header("Location: /fbd.php"); exit;}
                if (!(hasPermissions('about'))) { header("Location: /about/team"); exit; }
                $name = stripslashes(front::$_req['acf_name']);
                $position = front::$_req['acf_number'];
                $error = 0;
                if(empty($name)) {
                    $error = 1;
                    $error_msgs[1] = 'Поле "Название" заполнено некорректно';
                }
                if(!is_numeric($position)) {
                    $error = 1;
                    $error_msgs[2] = 'Поле "Порядок" заполнено некорректно';
                } else {
                    $position = (int) $position;
                    if($position<=0) {
                        $error = 1;
                        $error_msgs[2] = 'Поле "Порядок" заполнено некорректно';
                    }
                }
                $name = change_q($name);
                if($error) {
                    front::og("tpl")->error_msgs_acf = $error_msgs;
                    front::og("tpl")->acf_name = $name;
                    front::og("tpl")->acf_position = $position;
                    front::og("tpl")->acf_id = $id;
                } else {
                    team::CreateGroup($name,$position);
                    header('Location: /about/team/');
                    exit;
                }
                break;
            case 'deletecategory':
                if (hasPermissions('about')) {
                    team::DeleteGroup(front::$_req['dcf_id']);
                }
                header('Location: /about/team/');
                exit;
                break;
            case 'deleteteampeople':
                if (hasPermissions('about')) {
                    team::DeleteUser(front::$_req['dtf_id']);
                }
                header('Location: /about/team/');
                exit;
                break;
        }

    	// Берем команду для вывода
        front::og("tpl")->groups = team::GetAllGroups();
        foreach(front::og("tpl")->groups as $group) {
            $sql = "SELECT team_people.*, team_groups.id as groups_id,team_groups.title as groups_title, team_groups.position as groups_position FROM team_people LEFT JOIN team_groups ON team_groups.id = team_people.groupid WHERE team_people.groupid=?i ORDER BY team_groups.position, team_people.position, team_people.id ASC";
            $team = $DB->rows($sql, $group['id']);
            front::og("tpl")->team_people[$group['id']] = array();
            if($team) {
                foreach($team as $t) {
                    array_push(front::og("tpl")->team_people[$group['id']], $t);
                }
            }            
        }
    	
        front::og("tpl")->text = static_pages::get("about_team"); 
        front::og("tpl")->script = array( 'team.js' );
        front::og("tpl")->display("about/about_team.tpl");
    }
    /**
     * Обработчик событий страницы /about/services/ (Сервисы)
     *
     */
    function servicesAction() {
        front::og("tpl")->text = static_pages::get("about_services"); 
        front::og("tpl")->display("about/about_services.tpl");
    }
    /**
     * Обработчик событий страницы /about/faq/ 
     *
     */
    function faqAction() {
        if($this->uri[0]) {
            if($this->uri[0] == "id") {
                $id = intval($this->uri[1]);
                $id = intval(front::og("db")->select("SELECT id FROM faq WHERE is_show = 1 AND id = ?;", $id)->fetchOne());
            } else {
                $id = intval(front::og("db")->select("SELECT id FROM faq WHERE is_show = 1 AND url =?;", $this->uri[0])->fetchOne());
            }
        }
        
        if($id > 0) {
            front::og("tpl")->faq_el = front::og("db")->select("SELECT * FROM faq WHERE id = ?n;", $id)->fetchRow();
        } else {
            front::og("tpl")->razdels = front::og("db")->select("SELECT * FROM faq_category ORDER BY _order ASC;")->fetchAll();
            front::og("tpl")->faq = front::og("db")->select("SELECT * FROM faq WHERE is_show = 1 ORDER BY _order ASC;")->fetchAll(array("faqcategory_id"=>false));
        }
        front::og("tpl")->display("about/about_faq.tpl");
    }
    /**
     * Обработчик событий страницы /about/coprorative/ (Корпоративный блог)
     *
     */
    function corporativeAction() {
    	global $session; // Переменная сессии
    	
    	front::og("tpl")->css       = "/css/press-center.css";
    	front::og("tpl")->session   = $session;
    	front::og("tpl")->name_page = $this->name_page;
        
    	if($this->uri[0]) {
    		if($this->uri[0] == "post") {
	    		/**
		    	 * Подключаем файлы для работы с XAJAX на данной странице
		    	 */
		    	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.common.php");
		    	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.server.php");
		    	
		    	front::og("tpl")->xajax     = $xajax; 
    		} else {
    			/**
		    	 * Подключаем файлы для работы с XAJAX на данной странице
		    	 */
    			require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.common.php");
			    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.server.php");
			    	
			    front::og("tpl")->xajax     = $xajax; 
    		}
    		/**
    		 * Обработка событий на странице
    		 */
    		switch($this->uri[0]) {
    			// Страница просмотра определенного Сообщения.
    			case 'post':
    				$action = @$_POST['action'];
    				
    				
    				switch($action) {
    					// Добавить коментарий
    					case "addcmt": 
    						if(!get_uid()) header("Location: /{$this->name_page}/corporative/post/{$_POST['blogID']}/");
    						if(($_SESSION['last_comment_add']+5) > time()) break;
    						list($e, $e1, $idCom) = self::addComment();
    						// Осли есть ошибка перенаправление на страницу не делать
    						if(!$e) {
    						    header("Location: /{$this->name_page}/corporative/post/{$_POST['blogID']}/link/{$idCom}/?new={$idCom}#new");
    						}
    						break;
    					// Редктировать комментарий
    					case "editcmt":
    						$idCom = intval($this->uri[3]);
    						if(!get_uid()) header("Location: /{$this->name_page}/corporative/post/{$_POST['blogID']}/?new={$idCom}");	
    						self::editComment($idCom);
    						header("Location: /{$this->name_page}/corporative/post/{$_POST['blogID']}/link/{$idCom}/?new={$idCom}");
    						break;	
    					default: 
    						break;
    				}
    				
    				$id   = intval($this->uri[1]);
    				
    			 	if(strlen($id) > 6) return header("Location: /404.php");  
    				
    				
    				
		    		front::og("tpl")->blog = $_blog = front::og("db")->select("SELECT cb.*, u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.boss_rate FROM corporative_blog as cb, users as u WHERE cb.id = ?n AND u.uid = cb.id_user;", $id)->fetchRow();
		    		if(!$_blog) header("Location: /404");
		    		
		    		list($lastDate, $_id_) = self::updViewPost($id, date('c'), $_blog['m_count']);
    				
    				$tags = self::getPostTags($id);
    				front::og("tpl")->tags = $tags;
    				
    				front::og("tpl")->lastDate = $lastDate;
		    		
		    		front::og("tpl")->count_comment = front::og("db")->select("SELECT COUNT(id_blog) as count FROM corporative_blog WHERE id_blog =?n GROUP BY id_blog", $id)->fetchOne();
		    		$comments = front::og("db")->select("SELECT cb.*, u.login, u.uname, u.usurname, u.photo, u.role, u.is_pro, u.is_pro_test, u.boss_rate, u.warn, u.is_banned FROM corporative_blog as cb, users as u WHERE cb.id_blog = ?n AND u.uid = cb.id_user;", $id)->fetchAll();
		    		
		    		if($comments) foreach($comments as $k=>$v) {
		    			if($v['id_modified']) $mod[$v['id_modified']] = $v['id_modified'];
		    			if($v['id_deleted']) $mod[$v['id_deleted']] = $v['id_deleted'];
		    			$cid[$v['id']] = $v['id'];
		    		}
		    		
		    		if($cid) $attach = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id IN(?a)", $cid)->fetchAll();
		    		if($mod) $moders = front::og("db")->select("SELECT login, uname, usurname, uid, role, is_pro, is_pro_test, boss_rate FROM users WHERE uid IN(?a)", $mod)->fetchAll();
		    		
		    		if($moders) {
		    			foreach($moders as $key=>$val) $res_mod[$val['uid']] = $val;
		    			front::og("tpl")->moders = $res_mod;
		    		}
		    		if($attach) {
			    		foreach($attach as $key=>$val) {
			    			$res_attach[$val['msg_id']][] = $val;
			    		}
			    		front::og("tpl")->attach = $res_attach;
		    		}
		    		if($comments) {
		    			$sortComm = $this->sortTreeComment($comments);
		    			front::og("tpl")->sortComm = $sortComm;
		    		}
		    		
		    		$attach_blog = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id = ?", $id)->fetchAll();
			    	if($attach_blog) {
						front::og("tpl")->attach_blog = $attach_blog;
					}	
		    		
		    		$action2 = $this->uri[2];
		    		
		    		switch($action2) {
		    			// Редактирование комментария
		    			case "edit":
		    				$IDEdit = intval($this->uri[3]);
		    				
		    				if($comments[$IDEdit]['id_user'] != get_uid() && (!hasPermissions('about'))) header("Location: /{$this->name_page}/corporative/post/{$comments[$IDEdit]['id_blog']}/");
		    				
		    				front::og("tpl")->edit_flag = 1;
		    				front::og("tpl")->IDEdit    = $IDEdit;
		    				break;
		    			// Редактирование комментариев (только для админов и модераторов)	
		    			case "adminedit":
		    				if(!hasPermissions('about')) header("Location: /{$this->name_page}/corporative/post/$id/");
				    		
		    				if($_POST['action']) {
		    					list($eflag, $estr) = self::editComment($id);
		    					if(!$eflag) header("Location: /{$this->name_page}/corporative/post/$id/");	
		    				}
		    				
		    				front::og("tpl")->IDEditAdm = $id;
		    				
		    				break;	
		    			// Редактирование сообщения
		    			case "renew":
		    				$IDEdit = intval($this->uri[3]);
		    				if(!hasPermissions('about')) header("Location: /{$this->name_page}/corporative/post/{$comments[$IDEdit]['id_blog']}/");
		    				
		    				$save = array("id_deleted"   => 0); 
		    				front::og("db")->update("UPDATE corporative_blog SET ?s WHERE (id = ?n)", $save, $IDEdit);
		    				header("Location: /{$this->name_page}/corporative/post/{$comments[$IDEdit]['id_blog']}/#c".$IDEdit);
		    				break;
		    			// Ссылка на какой либо комментарий на странице.	
		    			case "link":
		    				front::og("tpl")->linked = intval($this->uri[3]);
		    				break;		
		    			default: 
		    				break;
		    		}
		    		
		    		front::og("tpl")->comments = $comments;
		    		front::og("tpl")->script = array( 'mAttach.js', 'banned.js' );
		        	front::og("tpl")->display("about/about_corporative_post.tpl");	 
    				break;
    			// Пагинация	
    			case 'page': 
    				$page = intval($this->uri[1]);
    				
    				if(strlen($page) > 6 ) header("Location: /404.php");
        if(!$page) $page = 1;
    				
    				$IDEdit = intval($this->uri[3]);
    				
    				if($_POST['action']) {
    					if(!hasPermissions('about')) header("Location: /{$this->name_page}/corporative/");
    					list($eflag, $estr) = self::editComment($IDEdit);
    					if(!$eflag) header("Location: /{$this->name_page}/corporative/");	
    				}
    				
    				front::og("tpl")->IDEdit = $IDEdit;
    				
    				
    				self::getCorporateBlog($page);
    				front::og("tpl")->script = array( 'mAttach.js' );
    				front::og("tpl")->display("about/about_corporative.tpl");
    				break;
    			// Удаление комментария	
    			case 'delete':
    				if($_SESSION["uid"]) {
	    				$id  = intval($this->uri[1]);
	    				$del = front::og("db")->select("SELECT * FROM corporative_blog WHERE id = ?n", $id)->fetchRow();
	    				
	    				
	    				if($_SESSION["uid"] == $del['id_user'] || hasPermissions('about')) {
		    				$save = array(
						            "id_deleted"   => get_uid(), 
						            //"msg"          => hasPermissions('about')?"":"Комментарий удален автором",
						            "date_deleted" => date("Y-m-d H:i:s"),  
						        );
		    				front::og("db")->update("UPDATE corporative_blog SET ?s WHERE (id = ?n)", $save, $id);
	    				} 
	    				
	    				header("Location: /{$this->name_page}/corporative/post/{$del['id_blog']}/#c{$id}");
    				}
    				
    				break;
    			// Удаление сообщения (только для админов и модераторов)	
    			case 'deleted':
    				if(hasPermissions('about')) {
	    				$id  = intval($this->uri[1]);
	    				$del = front::og("db")->select("SELECT * FROM corporative_blog WHERE id = ?", $id)->fetchRow();
	    				
	    				if($_SESSION["uid"] == $del['id_user'] || hasPermissions('about')) {
		    				$save = array(
						            "id_deleted"   => get_uid(), 
						            //"msg"          => hasPermissions('about')?"":"Комментарий удален автором",
						            "date_deleted" => date("Y-m-d H:i:s"),  
						        );
		    				front::og("db")->update("UPDATE corporative_blog SET ?s WHERE (id = ?n)", $save, $id);
	    				}
	    				
	    				// front::og("db")->delete("DELETE FROM corp_tags WHERE corp_id = ?n", $id); 
    				}
    				header("Location: /{$this->name_page}/corporative/");
    				
    				break;	
    			// Редактирование сообщения (только для админов и модераторов)	
    			case "edit":
    				if(!hasPermissions('about')) header("Location: /{$this->name_page}/corporative/");
    				
    				$IDEdit = intval($this->uri[1]);
    				
    				if($_POST['action']) {
    					list($eflag, $estr) = self::editComment($IDEdit);
    					if(!$eflag) header("Location: /{$this->name_page}/corporative/");	
    				}
    				
    				front::og("tpl")->IDEdit = $IDEdit;
    				
    				self::getCorporateBlog();
    				front::og("tpl")->script = array( 'mAttach.js' );
		        	front::og("tpl")->display("about/about_corporative.tpl");
		        	break;	
    			case "tags":
		    		if($_POST['action']) {
		    			if(!hasPermissions('about')) header("Location: /{$this->name_page}/corporative/");
		    				
		    			list($eflag, $estr) = self::addComment();
		    			if(!$eflag) header("Location: /{$this->name_page}/corporative/");
		    		}
		    		
		    		//if($this->uri[2] == "oblako") {
		    			front::og("tpl")->oblako = self::GetTags($count, 10);
		    		//}
		    		
		    		
		    		$tag_id = intval($this->uri[1]);
		    		if(strlen($tag_id) > 6) return header("Location: /404.php");  
		    		
		    		$cc = front::og("db")->select("SELECT corp_id, tag_id, tags.name FROM corp_tags, tags WHERE tags.id = ?n AND corp_tags.tag_id = tags.id", $tag_id)->fetchAll();
		    		if(!$cc) header("Location: /404");
		    		
		    		$tname  = $cc[0]['name'];
		    		front::og("tpl")->tag_name = $tname;
		    		front::og("tpl")->tag_id   = $tag_id;
		    		
		    		$cc = front::get_hash($cc, "corp_id", "corp_id");
		    		
		    		
		    		self::getCorporateBlog(1, 100, $cc);
		    		front::og("tpl")->script = array( 'mAttach.js' );
		        	front::og("tpl")->display("about/about_corporative.tpl");	
		        	break;				
    			default:
    				header("Location: /404.php");
    				break; 
    		}
    		
    		
    	} else {
    		require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.common.php");
	    	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.server.php");
	    	
	    	front::og("tpl")->xajax     = $xajax; 
    		
    		if($_POST['action']) {
    			if(!hasPermissions('about')) header("Location: /{$this->name_page}/corporative/");
    				
    			list($eflag, $estr) = self::addComment();
    			if(!$eflag) {
    			    header("Location: /{$this->name_page}/corporative/#top");
    			} 
    		}
    		
    		self::getCorporateBlog();
    		front::og("tpl")->script = array( 'mAttach.js' );
        	front::og("tpl")->display("about/about_corporative.tpl");
    	}
    }
    /**
     * Обработчик событий страницы /about/feedback/ (Обратная связь)
     *
     */
    function feedbackAction() {
        $redirect_to = 'https://feedback.fl.ru/';
        header("Location: $redirect_to");
        exit();    
	}
	
	function evaluateAction() {
            $GLOBALS['mem_buff'] = new memBuff();
		$error = '';
		// откуда пришел пользователь
		if (preg_match("/\/([A-Za-z0-9]{14,32})\/$/", front::$_req['pg'], $o)) {
			$from = 'feedback';
		} else if (preg_match("/\/([0-9]+)_([0-9\.]+)\/$/", front::$_req['pg'], $o))  {
            $from = 'webim';
		}
		front::og("tpl")->page = front::$_req['pg'];
		if ($from == 'webim') {
			// консультант
			front::og("tpl")->evtype = 'webim';
			$thread  = $o[1];
            $visitor = $o[2];
			$webim = new webim;
			if ($webim->Check($thread, $visitor) && $webim->GetChat(get_uid(FALSE), $thread)) {
				front::og("tpl")->thread   = $webim->thread;
				front::og("tpl")->operator = $webim->operator;
			} else {
			    if (front::$_req['evaluate'] ) {
			        $alert = 'Указанного обращения не существует или вы уже оставили отзыв.';
			        echo "Error:" . $alert;
        			exit;
			    }
			    else {
                    front::og("tpl")->error = $error = 'Указанного обращения не существует или вы уже оставили отзыв.';
			    }
			}
		} else {
			// обратная связь
			front::og("tpl")->evtype = 'feedback';
			$feedback = new feedback;
			if (!($code = $feedback->DecodeUCode($o[1])) || !$feedback->Check($code['uc'], $code['id'])) {
				front::og("tpl")->error = $error = 'Указанного обращения не существует или вы уже оставили отзыв.';
			} else {
				front::og("tpl")->code = $code;
			}
			front::og("tpl")->kind_name = $feedback->departaments[substr($code['id'], 0, 2)];
		}
		if (front::$_req['evaluate'] && !$error) {
			$e1 = intval(front::$_req['e1']);
			$e2 = intval(front::$_req['e2']);
			$e3 = intval(front::$_req['e3']);
			$wish = change_q(front::$_req['wish'], TRUE, 0, FALSE);
			$alert = '';
			if (!$alert && !in_array($e1, array(0, 1, 2, 3, 4, 5))) {
				$alert = 'Ошибка в оценке "Ожидание ответа".';
			}
			if (!$alert && !in_array($e2, array(0, 1, 2, 3, 4, 5))) {
				$alert = 'Ошибка в оценке "Доступное содержание".';
			}
			if (!$alert && !in_array($e3, array(0, 1, 2, 3, 4, 5))) {
				$alert = 'Ошибка в оценке "Общее впечатление".';
			}
			if (!$alert && !$e1 && !$e2 && !$e3 && !$wish) {
				$alert = 'Пожалуйста, поставте хотя бы одну оценку или напишите пожелание.';
			}
			if (!$alert) {
				$wish = substr(iconv('UTF-8', 'CP1251', $wish), 0, feedback::MAX_WISH_CHARS-1);
				if ($from == 'webim') {
					$alert = $webim->Evaluate($e1, $e2, $e3, $wish);
				} else {
					$alert = $feedback->Evaluate($code['uc'], $code['id'], $e1, $e2, $e3, $wish);
				}
			}
			echo $alert? "Error:".iconv('CP1251', 'UTF-8', $alert): "Success:";
			exit;
		}
		front::og("tpl")->script = array( 'feedback.js' );
		front::og("tpl")->display("about/about_evaluate.tpl");
	}
	
    /**
     * Выборка блогов из таблицы
     *
     * @param int $page Страница
     * @param int $count Количество на странице
     */
    function getCorporateBlog($page=1, $count=10, $tags=false) {
    	if(!$tags) {
    		$total = front::og("db")->select("SELECT COUNT(*) FROM corporative_blog WHERE id_blog = 0  AND (id_deleted IS NULL OR id_deleted = 0)")->fetchOne();
    	
	    	front::og("tpl")->page_corp  = $page;
	    	front::og("tpl")->pages_corp = ceil($total/$count);
	    	front::og("tpl")->total_corp = $total;
	    	
	    	$nPages = ceil( $total / $count );

            if ( 
                ($total == 0 || $total - 1 < ($page - 1) * $count) && $this->uri[0] == 'page' 
                || $nPages == 1 && $this->uri[0] == 'page' 
            ) {
            	include( ABS_PATH . '/404.php' );
                exit;
            }
    	}
    	$page--;
    	
    	$sql_page =$page*$count;
    	
    	$blogs = front::og("db")->select("SELECT * FROM corporative_blog WHERE ".($tags?' id IN(?a) AND ':' 0 = ? AND ')." id_blog = 0 AND (id_deleted IS NULL OR id_deleted = 0) ORDER BY id DESC LIMIT ? OFFSET ?", $tags?$tags:0, $count, $sql_page)->fetchAll();
    	if(!$blogs && $tags) Header("Location: /404.php");
    	$bids  = front::get_hash($blogs, "id", "id");
    	$uids  = front::get_hash($blogs, "id_user", "id_user");
    	
    	//return false;
    	if ($bids){ 
        	//$comm  = front::get_hash(front::og("db")->select("SELECT COUNT(id_blog) as count, id_blog FROM corporative_blog WHERE id_blog IN(?a) GROUP BY id_blog", $bids)->fetchAll(), "id_blog", "count");
        	$user  = front::og("db")->select("SELECT uname, usurname, login, uid, role, is_pro, is_pro_test, boss_rate FROM users WHERE uid IN(?a)", $uids)->fetchAll();//, "uid", "usname");
        	
        	foreach($blogs as $k=>$v) $cid[$v['id']] = $v['id'];
        	
        	
        	if($cid) $attach = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id IN(?a)", $cid)->fetchAll();
        	if($attach) {
    			foreach($attach as $key=>$val) {
    				$res_attach[$val['msg_id']][] = $val;
    			}
    			front::og("tpl")->attach = $res_attach;
    		}	
        	
        	foreach($user as $k=>$v)  $usr[$v['uid']]= $v;
        	
        	list($lastDate, $upDate) = self::getViewPostsDate($bids);
    	}
    	
    	//var_dump($lastDate);
    	//var_dump($upDate);
    	
    	front::og("tpl")->usbank  = $usr;
    	//front::og("tpl")->comment = $comm;
    	front::og("tpl")->blogs   = $blogs;
    	front::og("tpl")->lastDate  = $lastDate;
    	front::og("tpl")->upDate  = $upDate;
    	front::og("tpl")->tags    = self::getCorpTags();//self::GetTags($count, 10);
    	//front::og("tpl")->attach  = $attach;
    	
    	//self::getCorpTags();
    	
    	return array($blogs, $usr);
    }
    
    /**
     * Сортировка дерева комментарие
     *
     * @param mixed $comments Массив комментариев. 
     * @return array Возвращает массив с отсортированными идишниками комментариев
     */
    function sortTreeComment(&$comments) {
    	// Создаем два вспомогательных массива, для сортировки
    	foreach($comments as $k=>$v) {
			$tree[$v['id']] = $v; // Такой же массив $comments, только ключи данного массива это ИД комментария
			$lvl[$v['id_reply']][$v['id']] = $v['id']; // Двумерный массив, для определения уровня вложенности комментария
		}
		
		$comments = $tree; // Перезаписываем $comments.
		$sort     = array();
		$level    = $last_id = 0;
		// Сортируем. Укомлектовываем массив $sort пока он не будет равен размеру исходного массива $tree
		while(count($sort) < count($tree)) {
			$i++;
			if(array_key_exists((int)$last_id, $lvl)) {
				$min = min($lvl[$last_id]);
				unset($lvl[$last_id][$min]);
				if(count($lvl[$last_id]) == 0) unset($lvl[$last_id]);
				$id = $min;
			} else {
				$id = $last_id;
			}
			
			if(!array_key_exists($id, $sort)) $sort[$id] = $level;
			
			if($last_id === $id) {
				$level--; 
				$last_id = $tree[$id]['id_reply'] ;
			} else {
				$level++;
				$last_id = $id;
			}
			
			if($i>10000) break; // Защита от зацикливания (на всякий случай)
		}
		
		return $sort;
    }
    
    
    /**
     * Добавление Комментария/Сообщения
     *
     */
    function addComment() {
    	$DB = new DB('master'); 
    	if(($_SESSION['last_comment_add']+5) > time()) return false;
    	$_SESSION['last_comment_add'] = time();
    	/* Данные комментария */
    	$blog   = $_POST['blogID'];
    	$user   = get_uid();
    	$parent = $_POST['parent'];
        $alert = array();
    	if (strlen($_POST['msg']) > blogs::MAX_DESC_CHARS) {
            $error_flag = 1;
            $alert[2] = "Максимальный размер сообщения ".blogs::MAX_DESC_CHARS." символов!";
            $msg =& $_POST['msg'];
        } else {
            $msg = $_POST['msg'];
            $msg = preg_replace("/<ul.*>/Ui","<ul>",$msg);
            $msg = preg_replace("/<li.*>/Ui","<li>",$msg);
            $msg = (change_q_x_a(antispam($msg), false, false));
        }	
        
        
        
        
        $msg_name = (substr(change_q_x(antispam($_POST['title']), true), 0, 96));
        
        $yt_link = substr(change_q_x(antispam(str_replace('watch?v=', 'v/', $_POST['yt_link'])), true), 0, 128);
        
        if ($yt_link != '') {
            if ((strpos($yt_link, 'http://ru.youtube.com/v/') !== 0)
            && (strpos($yt_link, 'http://youtube.com/v/') !== 0)
            && (strpos($yt_link, 'http://www.youtube.com/v/') !== 0)) {
                $error_flag = 1; $alert[4] = "Неверная ссылка.";
            }
        }
        if(is_empty_html($msg)) $msg='';
        
        // загрузка файлов
        $attach = $_FILES['attach'];
        
        if(is_array($attach) && sizeof($attach) <= 10) {
            if (is_array($attach) && !empty($attach['name'])) {
                foreach ($attach['name'] as $key=>$v) {
                    if (!$attach['name'][$key]) continue;
                    $files[] = new CFile(array(
                        'name'     => $attach['name'][$key],
                        'type'     => $attach['type'][$key], 
                        'tmp_name' => $attach['tmp_name'][$key], 
                        'error'    => $attach['error'][$key], 
                        'size'     => $attach['size'][$key]
                    ));
                }
            }
            
            if ($group == 7) {
                $max_image_size = array('width' => 400, 'height' => 600, 'less' => 0);
            } else {
                $max_image_size = array('width' => 470, 'height' => 1000, 'less' => 0);
            }
            
            list($files, $alert_, $error_flag___) = self::uploadFile($files, $max_image_size);
            $error_flag = max($error_flag___, $error_flag);
            if(is_array($alert_)) $alert = array_merge($alert, $alert_);
        } else {
            if (is_array($attach) && !empty($attach['name'])) {
                $error_flag = 1; $alert[2] = "Файлов не должно быть больше 10";
            }
        }
        
        
        if (!$msg && !count($files)) {$error_flag = 1; $alert[2] = "Поле заполнено некорректно";}
        
        if (($msg || $files['f_name'][0]) && get_uid() && !$error_flag) {
        	//if($files['f_name'][0])
        	//error_reporting(E_ALL);
        	$eUser = $DB->row("SELECT email, uid FROM corporative_blog LEFT JOIN users ON users.uid = corporative_blog.id_user WHERE corporative_blog.id = ?", $parent);

        	$e_user = new users();
        	$e_user->GetUser($e_user->GetField($eUser['uid'],$ee,'login'));
        	
        	$sql = "INSERT INTO corporative_blog (title, yt_link, msg, id_blog, id_user, id_reply) VALUES(?, ?, ?, ?, ?, ?) RETURNING id;";

         $res = $DB->row($sql, $msg_name, $yt_link, $msg, $blog, $user, $parent);
         $idCom = $res['id'];

//            $idCom =  front::og("db")->select("SELECT id FROM corporative_blog WHERE title = ? AND msg = ? AND id_blog = ? AND id_user = ?", $msg_name, $msg, $blog, $user)->fetchOne();
            
           	if(substr($e_user->subscr, 2, 1) == '1' && $idCom && $eUser['uid']!=$user){ 
           		$p_user = new users();
	        	$p_user->GetUser($p_user->GetField($user,$ee,'login'));
	        	
		        $smail = new smail();
		        
		        $link = "http://free-lance.ru/about/corporative/post/$blog/link/$idCom/#c$idCom";
		        $smail->CorporativeBlogNewComment(array("title"=>$msg_name, "msgtext"=>$msg), $p_user, $e_user, $link);
		    } 
           
            if (is_array($files)) {
            	$asql = '';
            	for($i = 0; $i < count($files['f_name']); $i ++)
                if ($files['f_name'][$i]) $asql .= ", (currval('corporative_blog_id_seq'), '{$files['f_name'][$i]}', '{$files['tn'][$i]}')";
                if ($asql) $asql = substr($asql, 2);
            }
            
            if ($asql) $DB->squery("INSERT INTO corporative_blog_attach(msg_id, \"name\", small) VALUES $asql");
            
            
            $tags     = $_POST['tags'];
	        if($tags) {
	            $tags_arr = $tags; //explode(",", $tags);
		        array_unique($tags_arr);
		        $this->tagsDelete($idCom);
		        $tg = tags::Add($tags_arr);
		        $this->tagsAdd($idCom, $tg);
	        }
	        
	        
	        //Уведомление о комментарии
	        
	        
	        
        	//list($alert1, $error_flag, $error) = $sql_error;
        	//list($alert1, $error_flag, $error) = $blog_obj->NewThread(get_uid(), $gr, $base, $name, $msg, $files, getRemoteIP(), $mod, 0, $tags, $yt_link, $ontop);
        }
        
        
        
        //if ($alert1) $alert = $alert + $alert1;
        //vardump($alert);
        front::og("tpl")->error_flag = $error_flag;
        //
        front::og("tpl")->alert     = $alert;
        
        front::og("tpl")->post = array("blog" => $blog, "user"=>$user, "parent"=>$parent, "msg"=>$msg, "title"=>$msg_name, "yt_link"=>$yt_link, "tags"=>$_POST['tags']);
        
        
        return array($error_flag, $error, $idCom);
    }
    /**
     * 
     *
     * @param unknown_type $trid
     * @param unknown_type $tg
     * @return unknown
     */
    function tagsAdd($trid, $tg) {
        $DB = new DB('master'); 
        $sql = "PREPARE ins(int4) as INSERT INTO corp_tags(tag_id, corp_id) VALUES ($1, '$trid');";
        if (sizeof($tg)){
            foreach ($tg as $ikey => $item) {
                $sql .= "EXECUTE ins('$item');";
            }
	    $DB->query($sql);
	}
        return $DB->error;
    }

    function tagsDelete($itemid) {
        $DB = new DB('master'); 
        $sql = "DELETE FROM corp_tags WHERE corp_id=?i";
        $DB->query($sql, $itemid);
        return $DB->error;
    }
    
    function GetTags(&$count, $limit, $offset = 0) {
        $DB = new DB('master'); 
        $sql = "SELECT COUNT(*) FROM (SELECT DISTINCT tag_id FROM corp_tags) as t";
        $count = $DB->val($sql);
        $sql = "SELECT t.name, COUNT(*) as count, t.id 
            FROM corp_tags c 
            INNER JOIN tags t ON c.tag_id = t.id 
            INNER JOIN corporative_blog b ON b.id = c.corp_id AND b.date_deleted IS NULL
            GROUP BY t.name, t.id ORDER BY count DESC, name "; 
        /*$sql = "SELECT tags.name, COUNT(*) as count, tags.id FROM corp_tags LEFT JOIN tags ON corp_tags.tag_id=tags.id
                GROUP BY tags.name, tags.id ORDER BY count DESC, name "; //LIMIT $limit OFFSET $offset"; */
        return $DB->rows($sql);
    }
    
    function getCorpTags() {
    	//$sql = "SELECT tags.name, corp_tags.corp_id, corp_tags.tag_id FROM corp_tags INNER JOIN tags ON corp_tags.tag_id=tags.id";
    	$sql = "SELECT t.name, c.corp_id, c.tag_id 
                FROM corp_tags c 
                INNER JOIN tags t ON c.tag_id = t.id 
                INNER JOIN corporative_blog b ON b.id = c.corp_id AND b.date_deleted IS NULL";
    	$all = front::og("db")->select($sql)->fetchAll();
    	
    	//error_reporting(E_ALL);
    	if(!$all) return false;
    	foreach($all as $k=>$val) {
    		$tags[$val['corp_id']][$val['tag_id']] = $val['name'];
    	}	
    	
    	return $tags;
    }
    
    function getPostTags($id= false) {
    	if(!$id) return false;
    	$sql = "SELECT tags.name, corp_tags.corp_id, corp_tags.tag_id FROM corp_tags LEFT JOIN tags ON corp_tags.tag_id = tags.id WHERE corp_tags.corp_id = ?n";
    	
    	$all = front::og("db")->select($sql, $id)->fetchAll();
    	
    	return $all;
    }
    
    /**
     * Закачать файл
     *
     * @param mixed $attach Массив закаченных файлов
     * @param array $max_image_size Максимально разрешенные размеры файла [width=длинна,height=ширина]
     * @param string $login Логин того кто закачивает файл
     * @return array [файлы, ошибки(если есть), коды ошибок(если есть)]
     */
    function uploadFile($attach, $max_image_size, $login = '') {
    	if ($login == '') $login = $_SESSION['login'];
    	
        if ($attach)
            foreach ($attach as $file) {
                $file->max_size = blogs::MAX_FILE_SIZE;
                $file->proportional = 1;
                $f_name = $file->MoveUploadedFile($login . "/upload");
                $ext = $file->getext();
                if (in_array($ext, $GLOBALS['graf_array']))
                    $is_image = TRUE;
                else
                    $is_image = FALSE;
                
                $p_name = '';
                if (! isNulArray($file->error)) {
                    $error_flag = 1;
                    $alert[3] = "Один или несколько файлов не удовлетворяют условиям загрузки.";
                    break;
                } else {
                    if ($is_image && $ext != 'swf' && $ext != 'flv') {
                        if (! $file->image_size['width'] || ! $file->image_size['height']) {
                            $error_flag = 1;
                            $alert[3] = 'Невозможно уменьшить картинку';
                            break;
                        }
                        if (! $error_flag && ($file->image_size['width'] > $max_image_size['width'] || $file->image_size['height'] > $max_image_size['height'])) {
                            if (! $file->img_to_small("sm_" . $f_name, $max_image_size)) {
                                $error_flag = 1;
                                $alert[3] = 'Невозможно уменьшить картинку.';
                                break;
                            } else {
                                $tn = 2;
                                $p_name = "sm_$f_name";
                            }
                        } else {
                            $tn = 1;
                        }
                    } else 
                        if ($ext == 'flv') {
                            $tn = 2;
                        } else {
                            $tn = 0;
                        }
                }
                $files['f_name'][] = $f_name;
                $files['p_name'][] = $p_name;
                $files['tn'][] = $tn;
            }
        return array($files, $alert, $error_flag);
    }
    /**
     * Редактировать комментарий
     *
     * @param integer $id ИД редактируемого комментария
     * @return array  [коды ошибок, описание ошибок] (по умолчанию оба занчения null)
     */
    function editComment($id) {
        $DB = new DB('master'); 
    	$blog   = $_POST['blogID'];
    	$user   = get_uid();	
    	$IDEdit = $id;//intval($this->uri[3]);
    	$alert = array();
    	$deleted_attach = $_POST['editattach'];
    	
    	if($deleted_attach) {
	    	foreach($deleted_attach as $key=>$val) {
	    		if($val == 1) {
	    			front::og("db")->delete("DELETE FROM corporative_blog_attach WHERE id = ?n", $key);
	    		}
	    	}
    	}
    	
    	if (strlen($_POST['msg']) > blogs::MAX_DESC_CHARS) {
            $error_flag = 1;
            $alert[2] = "Максимальный размер сообщения ".blogs::MAX_DESC_CHARS." символов!";
            $msg =& $_POST['msg'];
        } else {
            $msg = $_POST['msg'];
            $msg = preg_replace("/<ul.*>/Ui","<ul>",$msg);
            $msg = preg_replace("/<li.*>/Ui","<li>",$msg);
            $msg = change_q_x_a(antispam($msg), false, false);
        }	
        
        $msg_name = substr(change_q_x(antispam($_POST['title']), true), 0, 96);
        
        $yt_link = substr(change_q_x(antispam(str_replace('watch?v=', 'v/', $_POST['yt_link'])), true), 0, 128);
        if ($yt_link != '') {
            if ((strpos($yt_link, 'http://ru.youtube.com/v/') !== 0)
            && (strpos($yt_link, 'http://youtube.com/v/') !== 0)
            && (strpos($yt_link, 'http://www.youtube.com/v/') !== 0)) {
                $error_flag = 1; $alert[4] = "Неверная ссылка.";
            }
        }
        
        if(is_empty_html($msg)) $msg='';
        
         // загрузка файлов
        $attach = $_FILES['attach'];
        
        if(is_array($attach) && sizeof($attach) <= 10) {
        
            if (is_array($attach) && !empty($attach['name'])) {
                foreach ($attach['name'] as $key=>$v) {
                    if (!$attach['name'][$key]) continue;
                    $files[] = new CFile(array(
                        'name'     => $attach['name'][$key],
                        'type'     => $attach['type'][$key], 

                        'tmp_name' => $attach['tmp_name'][$key], 
                        'error'    => $attach['error'][$key], 
                        'size'     => $attach['size'][$key]
                    ));
                }
            }
            
            if ($group == 7) {
                $max_image_size = array('width' => 400, 'height' => 600, 'less' => 0);
            } else {
                $max_image_size = array('width' => 470, 'height' => 1000, 'less' => 0);
            }
            
            list($files, $alert_, $error_flag___) = self::uploadFile($files, $max_image_size);
            $error_flag = max($error_flag___, $error_flag);
            if(is_array($alert_)) $alert = array_merge($alert, $alert_);
        
        } else {
            if (is_array($attach) && !empty($attach['name'])) {
                $error_flag = 1; $alert[2] = "Файлов не должно быть больше 10";
            }
        }
        
        if (!$msg && !count($files)) {$error_flag = 1; $alert[2] = "Поле заполнено некорректно";}
        
        if (($msg || $files['f_name'][0]) && get_uid() && !$error_flag) {
        	$upd = array(
        				"title"         => $msg_name,
        				"yt_link"       => $yt_link,
        				"msg"           => $msg,
						"id_modified"   => get_uid(),
						"id_deleted"    => 0,
						"date_change"   => date("Y-m-d H:i:s"),  
				);
				
		    front::og("db")->update("UPDATE corporative_blog SET ?s WHERE (id = ?n)", $upd, $IDEdit);
            
            if (is_array($files)) {
            	$asql = '';
            	for($i = 0; $i < count($files['f_name']); $i ++)
                	if ($files['f_name'][$i]) $asql .= ", ({$IDEdit}, '{$files['f_name'][$i]}', '{$files['tn'][$i]}')";
                if ($asql) $asql = substr($asql, 2);
            }
            
            if ($asql) $DB->query("INSERT INTO corporative_blog_attach(msg_id, \"name\", small) VALUES $asql");
            
            
            $tags     = $_POST['tags'];
	        if($tags) {
	            $tags_arr = $tags;//explode(",", $tags);
		        array_unique($tags_arr);
		        $this->tagsDelete($IDEdit);
		        $tg = tags::Add($tags_arr);
		        $this->tagsAdd($IDEdit, $tg);
	        }
        }
        
        front::og("tpl")->ederror_flag = $error_flag;
        front::og("tpl")->edalert      = $alert;
        
        
        
        front::og("tpl")->edpost = array("blog" => $blog, "user"=>$user, "parent"=>$parent, "msg"=>$msg, "title"=>$msg_name, "yt_link"=>$yt_link);
        return array($error_flag, $error);
    }
    /**
     * Обновить посты которые юзер посмотрел, для того чтобы в след раз когда он зашел он видел есть ли новые комментарии к посту
     *
     * @param integer $bid ИД поста
     * @param unknown_type $date дата последнего просмотра (UNIX_TIME)
     * @return array [дата, ИД]
     */
    function updViewPost($bid, $date=false, $count=0) {
    	if(!$date) $date = date('c');
    	
    	$save = front::toWin(array(
            "uid"       => get_uid(), 
            "blog_id"   => $bid, 
            "date_view" => $date,
            "v_count"   => intval($count)
        ));
        list($date, $id) = self::getViewPost(get_uid(), $bid);
        if($id) {
        	$aff = front::og("db")->update("UPDATE corporative_blog_update SET ?s WHERE (id = ?n)", $save, $id);
        } else {
        	$id = front::og("db")->insert("corporative_blog_update", $save);
        }
        
        return array($date, $id);
    }
    /**
     * Берем последнюю дату просмотра
     *
     * @param integer $uid ID юзера который смотрит
     * @param integer $bid ID блога который он смотрит
     * @return array [дата, ИД]
     */
    function getViewPost($uid, $bid) {
    	$date = front::og("db")->select("SELECT date_view, id FROM corporative_blog_update WHERE uid = ? AND blog_id = ?", $uid, $bid)->fetchRow();
    	
    	return array(strtotime($date['date_view']), intval($date['id']));
    }
    /**
     * Берем разом все даты блогов (для первой страницы, чтобы выяснить есть ли блоги в которых новые коменты)
     *
     * @param array $bids Массив идишников блогов
     * @return array [[массив последних просмотров], [массив даты создания]]
     */
    function getViewPostsDate($bids) {
    	$res = front::og("db")->select("SELECT date_view, blog_id, v_count, id FROM corporative_blog_update WHERE uid = ? AND blog_id IN (?a)", get_uid(), $bids)->fetchAll();
    	
    	$com = front::og("db")->select("SELECT MAX(date_create) as date_create, id_blog FROM corporative_blog WHERE id_blog IN(?a) GROUP BY id_blog", $bids)->fetchAll();
    	
    	if($com) {
	    	foreach($com as $val) {
	    		$cret[$val['id_blog']] = strtotime($val['date_create']);
	    	}
    	}
    	
    	if(!$res) return false;
    	
    	foreach($res as $val) {
    		$ret[$val['blog_id']] = array("create"=>strtotime($val['date_view']), "count"=>(int)$val['v_count']); //strtotime($val['date_view']);
    	}
    	
    	return array($ret, $cret);	
    }
    
    function getTagsPosts($bids) {
    	$tags = front::og("db")->select("SELECT * FROM corp_rel_tags WHERE id_blog IN(?a)", $bids)->fetchAll();	
    	
    	foreach($tags as $k=>$v) {
    		$tt[$v['id_blog']] += array($v['id_tags']);
    	}
    }
}
?>
