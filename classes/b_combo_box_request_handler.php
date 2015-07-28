<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/memBuff2.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/search/sphinxapi.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/professions.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/op_codes.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/country.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/city.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/users.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/search/search.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/messages.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/sbr_frl.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/sbr_emp.php";

class CBComboRequestHandler {
    public function __construct() {}
	/**
     * Обрабатывает POST переменную action  и  возвращает null или данные
     *
     * @return string
     */ 
    public function processRequest() {
        $expire = 1;//3600;        
        $action = __paramInit("string", "", "action");
        switch ($action) {
            
            case "getdays":

                $days = array(1 => iconv("WINDOWS-1251", "UTF-8//IGNORE",'1 день'));
                $max = __paramInit("integer", "", "max", 1);
                $all = array(1,2,3,4,5,6,7,8,9,10,14,21,30,45,60,90);
                
                if($max > 1)
                {
                    $days = array();
                    foreach($all as $day)
                    {
                        if($day >= $max) break;
                        $days[$day] = iconv("WINDOWS-1251", "UTF-8//IGNORE",$day . ending($day, ' день', ' дня', ' дней'));
                    }
                }


                $days = array(
                    array('parentId' => '0'),
                    $days
                );
                
                return json_encode($days);
                
                break;
            
            case "gettucategories":
                $expire = 300;//3600;
                $membuf = new memBuff();
                $memkey = "b-combo-gettucategories";
                $result = $membuf->get($memkey);
                
                if (!$result) {
                    
                    /*
                     * Получаем из базы иерархию категорий для
                     * типовой услуги
                     */

                     $DB = new DB('master');
                     $sql = "SELECT 
                                g.id AS gid, 
                                g.title AS gname, 
                                g.ucnt AS gucnt, 
                                p.id AS pid, 
                                p.title AS name,
                                p.ucnt AS pucnt
                             FROM tservices_categories AS g 
                             INNER JOIN tservices_categories AS p ON p.parent_id = g.id 
                             ORDER BY g.ucnt DESC, p.ucnt DESC --gid, pid --g.n_order, p.n_order";
                     $rows = $DB->rows($sql);
                     
                     $result = array();
                     
                     if(count($rows))
                     foreach ($rows as $k => $i) 
                     {                         
                         if ($result[$i["gid"]] === null) 
                         {
                            $result[$i["gid"]] = array(
                                '0' =>  array(
                                    iconv("WINDOWS-1251", "UTF-8//IGNORE",$i["gname"]),/*,"undefined_value" => iconv("WINDOWS-1251", "UTF-8//IGNORE",'Все специальности')*/
                                    $i['gucnt']));
                                    
                            if ($i["pid"] !== null) { 
                                $result[$i["gid"]][$i["pid"]] = array(iconv("WINDOWS-1251", "UTF-8//IGNORE",$i["name"]), $i['pucnt']);
                            } else { 
                                $result[$i["gid"]] = array(iconv("WINDOWS-1251", "UTF-8//IGNORE",$i["gname"]), $i['gucnt']);
                            }
                         }
                         else if ( is_array($result[$i["gid"]]) ) 
                         {
                             $result[$i["gid"]] [$i["pid"]] = array(iconv("WINDOWS-1251", "UTF-8//IGNORE",$i["name"]), $i['pucnt']);
                         }
                    }
                    
                    //print_r($result);
                    //exit;
                    
                    $result = json_encode($result);
                    $membuf->add($memkey, $result, $expire);  
                }
                
                return $result;
            
                break;
            
            case "getlettergrouplist":
                require_once $_SERVER["DOCUMENT_ROOT"]."/classes/letters.php";
                $lettergroups = letters::getGroups($_POST["word"], (int)$_POST["limit"]);
                $result = array();
                $n = 0;
                foreach ($lettergroups as $k=>$i) {
                    $result[$n]['uid'] = $i["id"];
                    $result[$n]['uname'] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["title"]);
                    $result[$n]['usurname'] = '';
                    $result[$n]['login'] = '';
                    $result[$n]['photo'] = '';
                    $result[$n]['path'] = '';
                    $result[$n]['isContacts'] = '';
                    $n++;
                }
                $list = array();
                $list['list'] = $result;
                $list['counters']['moreContacts'] = 0;
                $list['counters']['moreUsers'] = 0;
                $list['dav'] = WDCPREFIX;
                return (json_encode($list));
                break;
            case "getlettergroupinfo":
                require_once $_SERVER["DOCUMENT_ROOT"]."/classes/letters.php";
                $group = letters::getGroup($_POST['uid']);
                if($group) {
                    $data = array(
                                    "record" => array(
                                                        "uid" => $group["id"],
                                                        "uname" => iconv("WINDOWS-1251", "UTF-8//IGNORE", $group["title"]),
                                                        "usurname" => '',
                                                        "login" => '',
                                                        "photo" => '',
                                                        "path" => "",
                                                        "isContacts" => ''
                                                     ),
                                    "found" => 1,
                                    "dav" => WDCPREFIX
                                 );

                }
                return (json_encode($data));
                break;
            case "getletterdocinfo":
                require_once $_SERVER["DOCUMENT_ROOT"]."/classes/letters.php";
                $doc = letters::getDocument($_POST['uid']);
                if($doc) {
                    $data = array(
                                    "record" => array(
                                                        "uid" => $doc["id"],
                                                        "uname" => iconv("WINDOWS-1251", "UTF-8//IGNORE", $doc["id"].' '.htmlspecialchars($doc["group_title"]).' - '.htmlspecialchars($doc['title'])),
                                                        "usurname" => '',
                                                        "login" => '',
                                                        "photo" => '',
                                                        "path" => "",
                                                        "isContacts" => ''
                                                     ),
                                    "found" => 1,
                                    "dav" => WDCPREFIX
                                 );

                }
                return (json_encode($data));
                break;
            case "getletterdoclist":
                require_once $_SERVER["DOCUMENT_ROOT"]."/classes/letters.php";
                $letterdocs = letters::getDocuments($_POST["word"], (int)$_POST["limit"]);
                $result = array();
                $n = 0;
                foreach ($letterdocs as $k=>$i) {
                    $result[$n]['uid'] = $i["id"];
                    $result[$n]['uname'] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["id"].' '.htmlspecialchars($i["group_title"]).' - '.htmlspecialchars($i['title']));
                    $result[$n]['usurname'] = '';
                    $result[$n]['login'] = '';
                    $result[$n]['photo'] = '';
                    $result[$n]['path'] = '';
                    $result[$n]['isContacts'] = '';
                    $n++;
                }
                $list = array();
                $list['list'] = $result;
                $list['counters']['moreContacts'] = 0;
                $list['counters']['moreUsers'] = 0;
                $list['dav'] = WDCPREFIX;
                return (json_encode($list));
                break;
            case "getletterdocsearch":
                require_once $_SERVER["DOCUMENT_ROOT"]."/classes/letters.php";
                $letterdocs = letters::getSearchDocuments($_POST["word"], (int)$_POST["limit"]);
                $result = array();
                $n = 0;
                foreach ($letterdocs as $k=>$i) {
                    $result[$n]['uid'] = $i["id"];
                    $result[$n]['uname'] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["id"].' '.htmlspecialchars($i["group_title"]).' - '.htmlspecialchars($i['title']));
                    $result[$n]['usurname'] = '';
                    $result[$n]['login'] = '';
                    $result[$n]['photo'] = '';
                    $result[$n]['path'] = '';
                    $result[$n]['isContacts'] = '';
                    $n++;
                }
                $list = array();
                $list['list'] = $result;
                $list['counters']['moreContacts'] = 0;
                $list['counters']['moreUsers'] = 0;
                $list['dav'] = WDCPREFIX;
                return (json_encode($list));
                break;
            case "getdate":
                return (date('Y-m-d'));
            case "getprofgroups":
            	$membuf = new memBuff();
                $memkey = "b-combo-getprofgroups";
                $result = $membuf->get($memkey);
                if (!$result) {
                    $rows = professions::GetAllGroupsLite();
                    $result = array();
                    foreach ($rows as $k=>$i) {
                        $result[$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
                    }                    
                    $membuf->add($memkey, $result, $expire);    
                }
                return (json_encode($result));               
            case "getprofessionsandgroups": 
		        	$membuf = new memBuff();
	                $memkey = "b-combo-getprofandgroups";
	                $result = $membuf->get($memkey);
	                if (!$result) {
		                $rows = professions::GetProfessionsAndGroup();
	                    $result = array();
	                    foreach ($rows as $k=>$i) {	                        
	                        if ($result[$i["gid"]] === null) {
	                        	$result[$i["gid"]] = array( $i["gid"] => iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["gname"]));
	                        	if ($i["id"] !== null) $result[$i["gid"]] [$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
	                        		else $result[$i["gid"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["gname"]);
	                        }
	                        else if ( is_array($result[$i["gid"]]) ) {
	                        	$result[$i["gid"]] [$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
	                        }
	                    }
	                    $membuf->add($memkey, $result, $expire);    
	                }                    
                    return (json_encode($result));
            case "getprofessions":
                $n = __paramInit("integer", "", "id");
                if ($n !== false) {
                    $membuf = new memBuff();
                    $memkey = "b-combo-getprofbygroup$n";
                    $result = $membuf->get($memkey);
                    if (!$result) {
                        $rows = professions::GetProfs($n);
                        $result = array(0=>iconv("WINDOWS-1251", "UTF-8//IGNORE", "Все специальности"));
                        foreach ($rows as $k=>$i) {
                            $result[$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
                        }
                        $membuf->add($memkey, $result, $expire);    
                    }
                    $data = array(
                        array("parentId" => "$n"),
                        $result
                    );
                    return (json_encode($data));
                }
            case "get_pro_types":
                $membuf = new memBuff();
                $memkey = "b-combo-get_pro_type";
                $result = $membuf->get($memkey);
                if (!$result) {
                    $rows = op_codes::getCodes(array(47,48, 49, 50, 51));
                    $result = array();
                    foreach ($rows as $k=>$i) {
                        $result[$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["op_name"]);
                    }
                    $result[76] = iconv("WINDOWS-1251", "UTF-8//IGNORE", "На несколько недель");
                    $membuf->add($memkey, $result, $expire);    
                }
                return (json_encode($result));
                
            case "getcountries":
            case "getrelevantcountries":
                $membuf = new memBuff();
                $memkey = "b-combo-getcountriesr";
                $result = $membuf->get($memkey);
                if (!$result) {
                    $rows = country::GetCountriesByCountUser();
                    $result = array();
                    foreach ($rows as $k=>$i) {
                        $result[$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
                    }
                    $membuf->add($memkey, $result, $expire);    
                }
                return (json_encode($result));    
             case "getcities":
                $n = __paramInit("integer", "", "id");
                if ($n !== false) {
                    $membuf = new memBuff();
                    $memkey = "b-combo-getcitybycountry$n";
                    $result = $membuf->get($memkey);
                    if (!$result) {
                    	$rows = city::GetCities($n);
                        $result = array("0"=>iconv("WINDOWS-1251", "UTF-8//IGNORE", "Все города"));
                        if (is_array($rows)) foreach ($rows as $k=>$i) {
                            $result[$k] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i);
                        }
                        $membuf->add($memkey, $result, $expire);    
                    }
                    $data = array(
                        array("parentId" => "$n"),
                        $result
                    );
                    return json_encode($data);
                }
             case "getcitiesbyid":
                $n = __paramInit("integer", "", "id");
                if ($n !== false) {
                    $membuf = new memBuff();
                    $memkey = "b-combo-getcitybycountry$n";
                    $result = $membuf->get($memkey);
                    if (!$result) {
                    	$rows = city::GetCities($n);
                        $result = array("0"=>iconv("WINDOWS-1251", "UTF-8//IGNORE", "Все города"));
                        foreach ($rows as $k=>$i) {
                            $result[$k] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i);
                        }
                        $membuf->add($memkey, $result, $expire);    
                    }                    
                    return json_encode($result);
                }  
            case "getuserlistbysbr":
            case "getuserlistold":
            case "getuserlist":
                return $this->getUsersList($_POST["word"], (int)$_POST["limit"], (int)$_POST["userType"], (int)$_POST["scope"]);
            case "get_user_info":
                return json_encode(users::GetUserShortInfo((int)$_POST["uid"]));
            case "getusersandcompanies":
                return $this->getUsersAndCompany($_POST["word"], (int)$_POST["limit"], (int)$_POST["userType"], (int)$_POST["scope"], false);
            case "get_user_or_company_info":
                return $this->getUserOrCompanyRecord();
            case "getsms":
            	require_once $_SERVER['DOCUMENT_ROOT']."/classes/registration.php";
                $registration = new registration();
                $registration->listenerAction(__paramInit('string', null, 'action'));
        }
        return false;
    }
    
   
    /**
     * @param $matchIds - массив в котором все ключи - идентификаторы найденных пользователей (по идее это массив выдачи сфинкса $result['matches'] но сейчас пока буду использовать вместо сфинкса работу с базой напрямую)
     * @param $limit    - сколько записей показывать
     * @param &$rows     - массив с результатами поиска по вхождению подстрок
     * Array
        (
            [uid] => Идентификатор пользователя из таблицы users 
            [uname] => Имя пользователя
            [usurname] => Фамилия пользователя
            [login] => Логин пользователя
            [photo] => короткое имя файла пользователя, например f_4f7aedf03b54e.png
            [path]  => Путь к файлу пользователя относительно корня dav сервера без первого слеша, например users/la/land_f/foto/           
        ) 
     * @param &$moreContacts -hfpyjcnm количествf найденных контактов и limit  
     * */
    private function getLastContacts($matchIds, $limit, &$rows, &$moreContacts) {
        //получаем роль пользователя
        session_start();
        $role = $_SESSION["role"][0];             
        if ($role !== null)  {//если роль пользователя известна             
	        //получаем партнеров по СБР пользователя            
	        $partner = 'emp_id';
	        $entity  = 'frl_id';
	        if ($role == 1) {
			    $partner = 'frl_id';
	            $entity  = 'emp_id';
	        }
	        $cmd = "SELECT  $partner FROM sbr WHERE $entity = ".$_SESSION["uid"]." ORDER BY reserved_time DESC";
	        $DB = new DB("master");
	        $rawsbr = $DB->cache(600)->rows($cmd);
	        $partners = array();
	        $j = 0;		//счетчик СБР пользователя
	        $complete = 0;
	        foreach ($rawsbr as $i) {
			    if ($matchIds[$i[$partner]] !== null) {
				    if (!$complete) {
					    $partners[] = $i[$partner];
					}
					$j++;
					if ($j > $limit) $complete = 1;
                }
            }
			//здесь надо обработать случай, когда СБР у пользователя нет (получить из контактов)
			if ($j) {
			    $ids = join(',', array_reverse($partners));				
				$cmd = "SELECT u.uid, u.uname, u.usurname, u.login, u.photo, u.role, f.path FROM users AS u
						LEFT JOIN file AS f
							ON f.fname = u.photo
						WHERE uid IN ($ids);
					";				
				$partners = $DB->cache(600)->rows($cmd);
				foreach ($partners as $k=>$i) {
					$i["uname"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["uname"]);
					$i["usurname"]   = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["usurname"]);
					$i["isContacts"] = 1;
					if ($rows[$k] !== null) $more--;
					$rows[$k] = $i;
				}
				$moreContacts = $j - $limit;
				if ($moreContacts <= 0) $moreContacts = 0;
	        }
     }/**/

    }
    
    
    /**
     * Получение списка пользователей
     * @todo кеш и js
     * 
     * @param string  $s     - поисковая строка
     * @param string  $limit - сколько записей запрашивать
     * @param integer $userType - фильтр результатов поиска:  0 - искать и фриленсеров и работодателей,   
     *                                                         1 - искать только фриленсеров,
     *                                                         2 - искать только работодателей,
     * @param integer $scope - определяет, где искать:  0 - в СБР/контактах и общем списке пользователей,
     *                                                   1 - в СБР/контактах,
     *                                                   2 - в общем списке пользователей
     * @return string - список пользователей в формате JSON
     */
    public function getUsersList($s, $limit='ALL', $userType=0, $scope=0) {
        session_start();
        $uid = get_uid(false);
        $result   = array();
        $contacts = array();
        $more     = array();
        // подготовка строки для поиска в shpinx
        if ( $s != '' ) {
            $s = iconv("UTF-8", "WINDOWS-1251//IGNORE", $s);
            $s = substr(trim($s), 0, 60);
            $r = preg_split("/\s+/", $s);
            $s = '';
            // если введено два слова, то пологаем что это имя и фамилия и обрабатываем вариант такого поиска особо
            // если одно из слов состоит только из /^[-_a-z0-9]+$/i также полагаем что это может быть часть логина
            if ( count($r) == 2 ) {
                $s = "(@uname {$r[0]} & (@usurname *{$r[1]}* | @usurname {$r[1]})) | (@usurname {$r[0]} & (@uname *{$r[1]}* | @uname {$r[1]}))";
                if ( preg_match("/^[-_a-z0-9]+$/i", $r[0]) ) {
                    $s .= " | (@login *{$r[0]}* & (@uname *{$r[1]}* | @usurname *{$r[1]}*)) | @login *{$r[0]}*";
                }
                if ( preg_match("/^[-_a-z0-9]+$/i", $r[1]) ) {
                    $s .= " | (@login *{$r[1]}* & (@uname {$r[0]} | @usurname {$r[0]})) | @login *{$r[1]}*";
                }
            // во всех остальных случаях полагаем что все слова кроме последнего введены полностью и просто ищем совпадения
            } else {
                for ( $i=0; $i<count($r)-1; $i++ ) {
                    $s .= "{$r[$i]} | ";
                }
                $s .= "*{$r[$i]}* | {$r[$i]}";
            }
        }
        // если нужны контакты в сбр и личке, то получаем их id
        if ( $scope == 0 || $scope == 1 ) {
            // контакты в сбр
            $memBuff = new memBuff;
            $contacts = $memBuff->get("bComboUsers{$uid}");
            if ( $contacts === false ) {
                if ( is_emp() ) {
                    $sbr = new sbr_emp($uid);
                } else {
                    $sbr = new sbr_frl($uid);
                }
                $contacts = $sbr->getPartersId();
                // контакты в личке
                $mess  = new messages;
                $rows = $mess->GetContacts($uid);
                for ( $i=0; $i<count($rows); $i++ ) {
                    if ( !in_array($rows[$i]['uid'], $contacts) ) {
                        $contacts[] = $rows[$i]['uid'];
                    }
                }
                $memBuff->set("bComboUsers{$uid}", $contacts, 600);
            }
        }

        // $i = 0-контакты, 1-остальные пользователи
        for ( $i=0; $i<=1; $i++ ) {
            if ( !$i && !$contacts ) {
                continue;
            }
            $rows = array();
            // если поиск не требуется
            if ( $s == '' ) {
                if ( !$i ) {
                    if ( $userType ) {
                        $table = ($userType == 2)? 'employer': 'freelancer';
                    } else {
                        $table = 'users';
                    }
                    $rows = users::GetUsersInfoByIds($limit == 'ALL'? $contacts: array_slice($contacts, 0, $limit), $table);
                }
            // если требуется
            } else {
                $filter = array();
                if ( $contacts ) {
                    if ( !$i ) {
                        $filter['uids'] = $contacts;
                    } else {
                        $filter['nouids'] = $contacts;
                    }
                }
                if ( $userType ) {
                    $filter['utype'] = $userType - 1;
                }
                $search = new search(get_uid(false));
                $search->addElement('users_simple', true);
                $search->search($s, 0, $filter, ($limit == 'ALL'? 1000: $limit));
                $res = $search->getElements();
                $rows = $res['users_simple']->getRecords();
                $more[$i] = $res['users_simple']->total - count($rows);
            }
            // готовим вывод
            foreach ( $rows as $k => $v ) {
                if($v['uid'] == null) {
                    $v['uid']    = $v['id'];
                }
                $v["uname"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", $v["uname"]);
                $v["usurname"]   = iconv("WINDOWS-1251", "UTF-8//IGNORE", $v["usurname"]);
                if ( $v['photo'] ) {
                    $v['path']  = 'users/' . substr($v['login'], 0, 2) . '/' . $v['login'] . '/foto';
                    $v['photo'] = get_unanimated_gif($v['login'], $v['photo']);
                } else {
                    $v["photo"]  = "/images/temp/small-pic.gif";
                }
                $v["isContacts"] = (int) !((bool) $i);
                $result[] = $v;
            }
        }
        //return $result;
        // выводим
        $data = array (
            "list"     => $result,
            "counters" => array(
                "moreContacts" => $more[0],
                "moreUsers"    => $more[1],
            ),
            "dav"      => WDCPREFIX
        );
        return json_encode($data);
    }
    
    
    /**
     * Поиск пользователей и компаний
     * @param $s     - поисковая строка
     * @param $limit - сколько записей запрашивать
     * @param $userType - фильтр результатов поиска  0: искать и фриленсеров и работодателей,   
     *                                               1: искать только фриленсеров,
     *                                               2: искать только работодателей,
     * @param $scope    - определяет, где искать     0:   в СБР/контактах и общем списке пользователей,
     *                                               1:   в СБР/контактах,
     *                                               2:   в общем списке пользователей
     * @param $is_delete - искать удаленных пользователей или нет
     * */
    private function getUsersAndCompany($s, $limit = " ALL ", $userType = 0, $scope = 0, $is_delete = true) {
        require_once $_SERVER["DOCUMENT_ROOT"]."/classes/letters.php";
        $s = iconv("UTF-8", "WINDOWS-1251//IGNORE", $s);                 
        $s = substr($s, 0, 40);
        $rows = array();
        if ($scope != 1) {
	        if ($s != "") {
		        $rows = users::GetUsersBySubstringInFinInfo($s, $limit, $moreUsers);//$DB->cache(600)->rows($cmd);
		        $map = array();
		        foreach ($rows as $k=>$i) {
                    $i["uname"]      = str_replace( array("onerror", "onload", '<script'), array('', '', ''),  $i["uname"]);
                    $i["uname"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", htmlspecialchars_decode($i["uname"]));
		            $i["usurname"]   = '';
                    $i["address"]      = str_replace( array("onerror", "onload", '<script'), array('', '', ''),  $i["address"]);
                    $i["address"]    = iconv("WINDOWS-1251", "UTF-8//IGNORE", "{$i['country']}, {$i['city']}, {$i['index']}, {$i['address']}");
                    $i["photo"]      = str_replace( array("onerror", "onload", '<script'), array('', '', ''),  $i["photo"]);
                    $i['photo']      = get_unanimated_gif($i['login'], $i['photo']);
                    $i['path']       = "users/".substr($i['login'], 0, 2)."/".$i['login']."/foto/";
                    $i["name"]      = str_replace( array("onerror", "onload", '<script'), array('', '', ''),  $i["name"]);
                    $i["name"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", htmlspecialchars_decode($i["name"]));
                    $i["country"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", htmlspecialchars_decode($i["country"]));
                    $i["city"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", htmlspecialchars_decode($i["city"]));
                    $i["isContacts"] = 1;
		            if (($i["photo"] === null)||($i["path"] === null)) {
		                $i["photo"] = "/images/temp/small-pic.gif";
		            }
		            $rows[$k] = $i;
		            $map[$i["uid"]] = $k;
		        }
                $moreUsers -= $limit;
		        if ($moreUsers < 0) $moreUsers = 0;   
	        }
	    }

        //get company list
        $companies = letters::getCompanies($s, $limit);
        if($companies['data']) {
            foreach($companies['data'] AS $company) {
                if($company['frm_type']) {
                    $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                }
                $i["uid"]        = $company['id'];
                $company["name"]      = str_replace( array("onerror", "onload", '<script'), array('', '', ''),  $company["name"]);
                $i["uname"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", htmlspecialchars_decode($company['name']));
                $company["address"]      = str_replace( array("onerror", "onload", '<script'), array('', '', ''),  $company["address"]);
                $company["index"]      = str_replace( array("onerror", "onload", '<script'), array('', '', ''),  $company["index"]);
                $company["city_title"]      = str_replace( array("onerror", "onload", '<script'), array('', '', ''),  $company["city_title"]);
                $i["address"]    = iconv("WINDOWS-1251", "UTF-8//IGNORE", "{$company['country_title']}, {$company['city_title']}, {$company['index']}, {$company['address']}");
                $i["name"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", htmlspecialchars_decode($i["name"]));
                $i["country"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", htmlspecialchars_decode($i["country"]));
                $i["city"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", htmlspecialchars_decode($i["city"]));
                $i["usurname"]   = "";
                $i["login"]      = "";
                $i['photo']      = "";
                $i["isContacts"] = 0;
                $i["isCompany"]  = 1;
                $rows[] = $i;
            }
        }
        $moreCompany = intval($companies['count'])-intval(count($companies['data'])); //сюда запишем N из фразы "Показаны первые M из N компаний"
        $result = $rows;
        $data = Array(
                            "list"=>$result,
                            "counters"=>array(
                                "moreContacts"=>$moreUsers,
                                "moreUsers"=>$moreCompany                
                            ),
                            "dav"=>WDCPREFIX
                    );
        return (json_encode($data));
    }    
    
    /**
     * получить запись по умолчанию для списка пользователей и компаний
     * */    
    private function getUserOrCompanyRecord() {
        require_once $_SERVER["DOCUMENT_ROOT"]."/classes/letters.php";
		if ($_POST["type"] == "user") {
            $user = users::GetUserShortInfoFinInfo((int)$_POST["uid"]);
            $user["record"]["isContacts"] = 1; //т. к. компании должны идти вторыми по дизайну
            return (json_encode($user));
        } else {
			//Здесь надо получить запись о компании use (int)$_POST["uid"]
            $company = letters::getCompany((int)$_POST["uid"]);
            $i["uid"]        = $company['id'];
            $i["uname"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", $company['name']);
            $i["address"]    = iconv("WINDOWS-1251", "UTF-8//IGNORE", "{$company['country_title']}, {$company['city_title']}, {$company['index']}, {$company['address']}");
            $i["usurname"]   = "";
            $i["login"]      = "";
            $i['photo']      = "";
            $i["isContacts"] = 0;
            $i["isCompany"]  = 1;
            $data = Array(
		                  "record"=>$i,
                          "found"=>1, // OR 0 если не найдено с таким id                
                          "dav"=>WDCPREFIX
                     );
            return (json_encode($data));
        }
    }
    
}
