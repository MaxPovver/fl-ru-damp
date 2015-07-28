<?php
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

class splash_screens
{
    const SPLASH_EMPLOYER   = 0x0001;
    const SPLASH_FREELANCER = 0x0002;
    const SPLASH_KONKURS    = 0x0004;
    const SPLASH_MESSAGES   = 0x0008;
    const SPLASH_KONKURS_WINNER = 0x0010;
    const SPLASH_PROEMP_NOTPROFRL = 0x0020;
    const SPLASH_NOTPROEMP_NOTPROFRL = 0x0040;
    const SPLASH_REAL_NAMES = 0x0080;
    const SPLASH_NO_SPEC = 0x0100;
    const SPLASH_MONEY   = 0x0200;
    const SPLASH_DIZKON   = 0x0300;
    const SPLASH_DIR = '/templates/splash/';
    
    /**
     * показывает сплэшскрин, предварительно сделав проверку: нужно ли его показывать
     * @param type $type код сплэша
     * @param type $debug - принудительно показать сплэш, даже если не прошел проверку
     * @param type $setViewed если true - отметить как просмотренный
     * @return boolean
     */
    function show($type, $debug = false, $setViewed = true) {
        $need_show = false;

        if (self::SPLASH_EMPLOYER & $type || self::SPLASH_FREELANCER & $type) {
            $need_show = self::checkEmpFrlSplashShow($type);
        }

        if (self::SPLASH_MESSAGES & $type) {
            $need_show = self::checkContactsSplashShow();
        }
        
        if (self::SPLASH_MONEY & $type) {
            $need_show = !self::isViewed($type);
        }
        
        if(self::SPLASH_PROEMP_NOTPROFRL & $type || self::SPLASH_NOTPROEMP_NOTPROFRL & $type) {
            $need_show = true;
        }
        
        if((self::SPLASH_REAL_NAMES & $type && !self::isViewed($type)) || $debug) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
            $attachedFiles = new attachedfiles();
            $user = new users();
            $user->GetUserByUID(get_uid(0));
            
            $userpicSrc = $user->photo ? WDCPREFIX . '/users/' .  $user->login . '/foto/' .  $user->photo : WDCPREFIX . '/images/no_foto.png';
            
            $rating = new rating($user->uid, $user->is_pro, $user->is_verify, $user->is_profi);
            $r_data = $rating->data;
            
            $need_show = true;
        }
        
        if((self::SPLASH_NO_SPEC & $type && !self::isViewed($type)) || $debug) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
            $frl = new freelancer();
            $frl->GetUserByUID(get_uid(0));
            if (!$frl->spec) {
                $need_show = true;
            }
        }

        // Проверяем не показывали уже этот тип
        if( $need_show || $debug || self::SPLASH_KONKURS == $type) {
            $tpl_splash = $_SERVER['DOCUMENT_ROOT'] . self::SPLASH_DIR . self::getTemplateSplash($type);
            if($tpl_splash != '') {
                include $tpl_splash;
                if($debug) return true;
                // Отмечаем как просмотренный
                if($type!=self::SPLASH_MESSAGES && $setViewed) {
                    self::setViewed($type);
                }
                return true;
            }
        }
    }

    /** 
     * определяет надо ли показывать сплеш в личке
     */
    function checkContactsSplashShow() {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $users = new users();
        $splash_show = $users->GetField(get_uid(), $error, 'splash_show');
        if(!($splash_show & self::SPLASH_MESSAGES)) {
            $_SESSION['splash_show'] = $_SESSION['splash_show'] ^ self::SPLASH_MESSAGES;
            return true;
        }
    }
    
    /**
     * определяет надо ли показывать сплэш (SPLASH_EMPLOYER или SPLASH_FREELANCER)
     * если пользователь не PRO - то показываем один раз в 2 недели
     */
    function checkEmpFrlSplashShow ($splashType) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_emp.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_frl.php");
        
        // для тестов *************************************************
        // можно задать дату последнего показа сплэша через рапаметр ?splash_date=2012-10-15
        // если оставить параметр пустым (?splash_date=), то дата в сессии обнуляется и данные берутся из базы
        if (defined('SERVER') && (SERVER === 'beta' || SERVER === 'alpha') && isset($_GET['splash_date'])) {
            if ($_GET['splash_date']) {
                $_SESSION['splash_last_date'] = strtotime($_GET['splash_date']);
            } else {
                $_SESSION['splash_last_date'] = null;
            }
        }
        //**************************************************************

        $currentTime = time();
        $show_for_pro = false;

        // когда последний раз показывался сплэш
        if (isset($_SESSION['splash_last_date'])) {
            $lastTime = $_SESSION['splash_last_date'];
        } else {
            $lastDate = self::getSplashLastDate();
            // если в базе не сохранена дата последнего показа, значит начнем ставим дату из прошлого чтобы показать сплеш
            if (!$lastDate) {
                $show_for_pro = true;
                $lastDate = '1970-01-01 00:00:01';
                self::saveSplashLastDate(strtotime($lastDate));
            }
            $lastTime = strtotime($lastDate);
        }

        $pastTime = $currentTime - $lastTime; // сколько времени прошло
        $weekTime = 3600 * 24 * 7;
        $monthTime = 3600 * 24 * 30;
        
        // если прошло меньше двух недель с последнего показа сплэша? то сплэш не показываем
        if ($pastTime < $weekTime*2) {
            return false;
        }
        
        // если дошли до сюда, значит прошло больше двух недель
        
       
        // если не PRO получай сплэш
        if (!is_pro() || $show_for_pro) {
            $_SESSION['splash_show'] = $_SESSION['splash_show'] ^ $splashType;
            self::saveSplashLastDate($currentTime);
            return true;
        }       
    }
    
    /**
     * получаем дату последнего показа сплэша из таблицы users
     * @return string $lastDate в формате YYYY-MM-DD HH:MM:SS
     */
    function getSplashLastDate () {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $users = new users();
        //$users->GetUserByUID(get_uid());
        $lastDate = $users->GetField(get_uid(), $error, 'splash_last_date');
        return $lastDate;
    }
    
    /**
     * сохраняем дату последнего показа сплэша
     * @param integer $unixTime в формате UnixTime
     */
    function saveSplashLastDate ($unixTime) {
        $_SESSION['splash_last_date'] = $unixTime;
        $users = new users;
        $users->splash_last_date = date('Y-m-d H:i:s', $unixTime);
        $users->update(get_uid(0), $error);
    }
    
    function isViewed ($type) {
        return $_SESSION['splash_show'] & $type;
    }

    function setViewed($type) {
        $users = new users;
        $splash_show = $users->GetField(get_uid(), $error, 'splash_show');
        $splash_show = $splash_show | $type;
        $users->splash_show = $splash_show;
        $users->update($_SESSION['uid'], $error);
        $_SESSION['splash_show'] = $splash_show;
    }
    
    function getTemplateSplash($type) {
        switch($type) {
            case self::SPLASH_EMPLOYER:
                return 'splash-employer.tpl.php';
                break;
            case self::SPLASH_FREELANCER:
                return 'splash-freelancer.tpl.php';
                break;
            case self::SPLASH_KONKURS:
                return 'splash-konkurs'.(is_pro() ? '-pro' : '').'.tpl.php';
                break;
            case self::SPLASH_MESSAGES:
                return 'splash-messages.tpl.php';
                break;
            case self::SPLASH_KONKURS_WINNER:
                return 'splash-konkurs.tpl.php';
            case self::SPLASH_PROEMP_NOTPROFRL:
                return 'splash-contacts-proemp.tpl.php';
            case self::SPLASH_NOTPROEMP_NOTPROFRL:
                return 'splash-contacts-notproemp.tpl.php';
            case self::SPLASH_REAL_NAMES:
                return 'splash-real-names.tpl.php';
            case self::SPLASH_NO_SPEC:
                return 'splash-no-spec.tpl.php';
            case self::SPLASH_MONEY:
                return 'splash-money.php';    
        }
    }
    
}

?>