<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_wizard.php';

/**
 * Общие шаги мастера регистрации
 */
class step_wizard_registration extends step_wizard
{
    /**
     * Тип мастера работодателя 
     */
    const TYPE_WIZARD_EMP = 1;
    
    /**
     * Тип мастера фрилансера 
     */
    const TYPE_WIZARD_FRL = 2;
    
    /**
     * Типы валют и его идентификатор
     * 
     * @var array
     */
    public $CURRENCY_TYPE = array(
        2 => "Руб",
        0 => "USD",
        1 => "Евро",
        3 => "FM"
    );
    
    /**
     * цена за ....
     * 
     * @var array
     */
    public $PRICEBY_TYPE = array(
        1 => "цена за час",
        2 => "цена за день",
        3 => "цена за месяц",
        4 => "цена за проект",
    );
    
    public function registration($type_wizard = step_wizard_registration::TYPE_WIZARD_EMP) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
        
        $action = __paramInit('string', null, 'action');
        if ($this->status == step_wizard::STATUS_CONFIRM) {
            if ($_SESSION['email'] == 0) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
                $user = new users();
                $email = $user->GetField(wizard::getUserIDReg(), $error, "email");
                $_SESSION['email'] = $email;
            }
            
            if($action == registration::ACTION_SEND_MAIL) {
                $send = registration::actionSendMail(false);
                if($send) {
                    header("Location: /wizard/registration/");
                    exit;
                }
          }
        }

        $type_user = $type_wizard;
        if ($action == 'registration' && $this->status == 0) {
            $error = array();
            if (!$_SESSION["regform_captcha_entered"]) {
                session_start();
                $captchanum =  __paramInit('string', null, 'captchanum');
                $num = __paramInit('string', null, 'rndnum');
                $_SESSION['w_reg_captcha_num'] = $captchanum;
                $captcha = new captcha($captchanum);
                if (!$captcha->checkNumber($num)) {
                    $error['captcha'] = 'Неверный код. Попробуйте еще раз';
                    unset($_SESSION['w_reg_captcha_num']);
                }
            }
            if($type_wizard == step_wizard_registration::TYPE_WIZARD_EMP) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
            } else {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
            }
            $login  = trim(__paramInit('string', null, 'login'));
            $email  = trim(__paramInit('string', null, 'email'));
            $agree  = trim(__paramInit('string', null, 'agree'));
            $phone  = trim(__paramInit('string', null, 'phone'));
            $smscode  = trim(__paramInit('string', null, 'smscode'));
            // пароль берем напрямую из $_POST, а то __paramInit режет спецсимволы (пароль хешируется - SQL инъекция невозможна)
            $passwd = $_POST['password'];

            if (!$agree) {
                $error['agree'] = 'Прочтите и согласитесь с правилами';
            }
            if ($passwd == '') {
                $error['pwd'] = 'Введите пароль';
            }
            if (!preg_match("/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/", $login)) {
                $error['login'] = 'От 3 до 15 символов. Может содержать латинские буквы, цифры, подчёркивание (_) и дефис (-)';
            }
            if (in_array(strtolower($login), $GLOBALS['disallowUserLogins'])) {
                $error['login'] = 'Извините, такой логин использовать нельзя';
            }

            if (!is_email($email)) {
                $error['email'] = 'Поле заполнено некорректно';
            }
            if ($smscode != $_SESSION["smsCode"]) {
                $error['smscode'] = 'Код не совпал';
            }
            if ($phone != $_SESSION["reg_phone"]) {
                $error['phone'] = 'Вы вводили другой номер при запросе кода';
            }
            $phone = preg_replace("#^\+#", "", $_SESSION["reg_phone"]);
            if (empty($error['login'])) {
                $sql = "SELECT uid FROM users WHERE lower(login) = ?";
                if ($this->_db->val($sql, strtolower($login))) {
                    $error['login'] = 'Извините, такой логин уже существует';
                }
            }

            if (empty($error['email'])&&empty($error['captcha'])) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/temp_email.php");

                if (temp_email::isTempEmail($email)) {
                    $error['email'] = 'К сожалению, регистрация аккаунта на указанный адрес электронной почты невозможна. Пожалуйста, для регистрации воспользуйтесь почтовым адресом другого домена';
                } else {
                    $sql = "SELECT uid FROM users WHERE lower(email) = ?";
                    if ($this->_db->val($sql, strtolower($email))) {
                        $error['email'] = 'Указанная вами электронная почта уже зарегистрирована. Авторизуйтесь на сайте или укажите другую электронную почту.';
                    }
                }
            }

            if (count($error) == 0) {
                if($type_wizard == step_wizard_registration::TYPE_WIZARD_EMP) {
                    $newuser = new employer();
                } else {
                    $newuser = new freelancer();
                }
                $newuser->checked_name = false;
                if($type_wizard == step_wizard_registration::TYPE_WIZARD_EMP) {
                    $newuser->role = 1;
                } else {
                    $newuser->role = 0;
                }

                $newuser->login = substr($login, 0, 15);
                $newuser->email = substr($email, 0, 64);
                $newuser->passwd = substr($passwd, 0, 24);

                $id = $newuser->Create($rerror, $error);

                if ($id && !$error) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/activate_code.php");

                    $this->parent->saveActionWizard($this, step_wizard::STATUS_CONFIRM);
                    $this->parent->bindUserIDReg($id);

                    unset($_SESSION['ref_uri']);
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php' );

                    $smail = new smail();
                    $bSuspect = users::isSuspiciousUser($id, $newuser->login, '', $newuser->uname, '', $newuser->usurname, '');
                    $sPasswd = ( $bSuspect ) ? $newuser->passwd : ''; // чтобы из админки можно было выслать $smail->NewUser
                    $code = activate_code::Create($id, $newuser->login, $sPasswd, $error);

                    if (!$bSuspect) {
                        $_SESSION['suspect'] = false;
                        // юзер не подозрительный - сразу отпавляем юзеру письмо с кодом активации
                        $smail->NewUser($newuser->login, false, $code, $this->getWizardUserID(), ($newuser->role? 'emp': 'frl'));
                    } else {
                        $_SESSION['suspect'] = true;
                        // отправляем уведомление админу о том, что зарегистрировался подозрительный юзер
                        // если админ его одобрит - то письмо с кодом активации уйдет из админки
                        $smail->adminNewSuspectUser($newuser->login, $newuser->uname, $newuser->usurname);
                    }
                    //Записываем подтвержденный номер телефона в финансы
                    require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate.php";
                    $phone = '+' . preg_replace("#^\+#", "", $_SESSION["reg_phone"]);
                    unset($_SESSION["regform_captcha_entered"]);
                    sms_gate::saveSmsInfo($phone, $_SESSION["reg_sms_isnn"], $_SESSION["smsCode"], $_SESION["reg_sms_date_send"], $id);
                    // стираем куку, чтобы показался блок "Вы успешно зарегистрировались"
                    setcookie('master_auth', "", time()-3600, '/');

                    // Серый список IP ----------------------
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/gray_ip.php' );

                    $aGrayList = gray_ip::getGrayListByRegIp(getRemoteIP());

                    if ($aGrayList) { // есть записи в списке первичных IP
                        gray_ip::addSecondaryIp($id, $newuser->login, $newuser->role, $aGrayList);
                    }
                    //---------------------------------------

                    $_SESSION['email'] = $newuser->email;

                    header("Location: /wizard/registration/");
                    exit;
                }
            }
        } elseif ($action == 'authorization') {
            $auth_error = $this->authorization($auth_login);
        }
        include $_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.step.reg.php";
    }
    
    /**
     * авторизация в мастере
     */
    public function authorization (&$login) {
        $alert = array();
        $login  = __paramInit('string', null, 'auth_login');
        $user = new users;
        $role = $user->GetRole($login, $error);
        if ($error || !$role) {
            $alert['login'] = 'Пользователя с таким логином не существует';
            return $alert;
        }
        // проверяем совпадают ли роли в мастере и у пользователя
        $role = $role == '000000' ? 2 : 1; // 2 - фрилансер, 1 - работодатель - роль зарегистрированного пользователя
        $wr = new wizard_registration;
        $masterRole = (int)$wr->getRole(); // роль в мастере
        if ($role !== $masterRole) {
            if ($masterRole === 1) {
                $alert['login'] = 'На этом этапе войти можно только работодателем';
            } else {
                $alert['login'] = 'На этом этапе войти можно только фрилансером';
            }
            return $alert;
        }
        // хэш пароля
        $passwd = users::hashPasswd(trim($_POST['auth_password']));
        $id = login($login, $passwd, 0, true);
        if (!$id) {
            $alert['password'] = 'Неверный пароль';
            return $alert;
        }
        // запоминаем в куках что авторизовались
        setcookie('master_auth', true, time()+1800, '/');
        
        $this->parent->saveActionWizard($this, step_wizard::STATUS_COMPLITED);
        $this->parent->bindUserIDReg($id);
        $this->parent->setNextStep($this->parent->getPosition() + 1);
        header("Location: /wizard/registration/");
        exit;
    }
    
    public function completeData($type_role = 1) {
        if($this->isDisable()) {
            header("Location: /wizard/registration/?step=1");
            exit;
        }
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
        
        $themes_blogs   = blogs::getRandomThemes(5);
        $themes_commune = commune::getRandomCommunes(3);
        
        $month = array(
            '1'  => 'января',
            '2'  => 'февраля',
            '3'  => 'марта',
            '4'  => 'апреля',
            '5'  => 'мая',
            '6'  => 'июня',
            '7'  => 'июля',
            '8'  => 'августа',
            '9'  => 'сентября',
            '10' => 'октября',
            '11' => 'ноября',
            '12' => 'декабря'
        );
        
        if($type_role == step_wizard_registration::TYPE_WIZARD_EMP) {
            $user     = new employer();
            $checkPRO = $this->checkWizardPRO(array(step_employer::OP_CODE_PRO));
            $pro_emp  = ($checkPRO['id']>0?1:0);
            if($pro_emp) $week_pro = round($checkPRO['ammount']/10);
        } else {
            $user     = new freelancer();
            $checkPRO = $this->checkWizardPRO(step_freelancer::getOperationCodePRO());
            $pro_frl  = ($checkPRO['id']>0?1:0);
            if($pro_frl) $op_code = $checkPRO['op_code'];
        }
        $user->GetUserByUID(wizard::getUserIDReg());
        
        $info_for_reg = unserialize($user->info_for_reg);
        
        $uname    = $user->uname;
        $usurname = $user->usurname;
        $sex      = $user->sex == 't' ? 1 : ($user->sex == 'f'? 0 : -1);
        $birthday = strtotime($user->birthday);
        if($birthday) {
            $bday     = date('d', $birthday);
            $bmonth   = (int) date('m', $birthday);
            $bmonth_value = $month[$bmonth];
            $byear    = date('Y', $birthday);
        } else {
            $bday     = '';
            $bmonth   = (int) date('m', $birthday);
            $bmonth_value = $month[$bmonth];
            $byear    = '';
        }
        $city     = $user->city;
        if($city) {
            $city_value = city::GetCityName($city);
        }
        $country  = $user->country;
        if($country) {
            $country_value = country::GetCountryName($country);
        }
        
        if($type_role == step_wizard_registration::TYPE_WIZARD_EMP) {
            $company = $user->compname;
            $about_company  = $user->company;

            $logo_name = $user->logo;
            $dir    = "users/".substr($user->login, 0, 2)."/".$user->login."/logo/";
            $logo_path = WDCPREFIX . "/" . $dir . $user->logo;
        }
        
        $info['site']   = $this->loadMultiVal('site', 'site', $user);
        $info['email']  = $this->loadMultiVal('second_email', 'email', $user);
        $info['phone']  = $this->loadMultiVal('phone', 'phone', $user);
        $info['icq']    = $this->loadMultiVal('icq', 'icq', $user);
        $info['skype']  = $this->loadMultiVal('skype', 'skype', $user);
        $info['jabber'] = $this->loadMultiVal('jabber', 'jabber', $user);
        $info['lj']     = $this->loadMultiVal('ljuser', 'lj', $user);
        
        $action = __paramInit('string', null, 'action');
        
        if($action == 'upd_info') {
            
            $info_for_reg = $_POST['info_for_reg'];
            if ($info_for_reg['email_0'] !== null) {
                $info_for_reg['second_email'] = $info_for_reg['email_0']; 
                unset($info_for_reg['email_0']); 
            }
            if ($info_for_reg['phone_0'] !== null) {
                $info_for_reg['phone'] = $info_for_reg['phone_0']; 
                unset($info_for_reg['phone_0']); 
            }
            if ($info_for_reg['site_0'] !== null) {
                $info_for_reg['site'] = $info_for_reg['site_0']; 
                unset($info_for_reg['site_0']); 
            }            
            if ($info_for_reg['lj_0'] !== null) {
                $info_for_reg['ljuser'] = $info_for_reg['lj_0']; 
                unset($info_for_reg['lj_0']); 
            }
            if ($info_for_reg['jabber_0'] !== null) {
                $info_for_reg['jabber'] = $info_for_reg['jabber_0']; 
                unset($info_for_reg['jabber_0']); 
            }
            if ($info_for_reg['skype_0'] !== null) {
                $info_for_reg['skype'] = $info_for_reg['skype_0']; 
                unset($info_for_reg['skype_0']); 
            }
            if ($info_for_reg['icq_0'] !== null) {
                $info_for_reg['icq'] = $info_for_reg['icq_0']; 
                unset($info_for_reg['icq_0']); 
            }
            if ($info_for_reg['compname'] !== null) {
                $info_for_reg['company'] = $info_for_reg['compname']; 
                unset($info_for_reg['compname']); 
            }
            $info_for_reg = array_map('intval', $info_for_reg);
            $user->info_for_reg = serialize($info_for_reg);
            
            $uname = __paramInit('string', null, 'uname', null, 21);
            $usurname = __paramInit('string', null, 'usurname', null, 21);
            
            if($uname == '') {
                $error['uname'] = "Поле заполнено некорректно"; 
            }
            
            if($usurname == '') {
                $error['usurname'] = "Поле заполнено некорректно"; 
            }
            
            if (!preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $uname)) {
                $error['uname'] = "Поле заполнено некорректно";  
            } else {
                $user->uname = $uname;
            }
            if (!preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $usurname)) {
                $error['usurname'] = "Поле заполнено некорректно";  
            } else {
                $user->usurname = $usurname;
            }
            
            $sex  = __paramInit('int', null, 'sex', 1); // по умолчанию мужской пол
            
            $user->sex = ($sex == 1 ? 't' : 'f');

            $bday = __paramInit('int', null, 'bday', null);
            $bmonth = __paramInit('int', null, 'bmonth_db_id', 1);
            $bmonth_value = __paramInit('string', null, 'bmonth');
            $byear  = __paramInit('int', null, 'byear', null);
            
            if($bday != null && $byear != null) {
                if (!is_numeric($bday) || !is_numeric($byear) || !checkdate($bmonth, $bday, $byear) || $byear < 1945 || $byear > date('Y') ) {
                    $error['birthday'] = "Поле заполнено некорректно";
                } else {
                    $user->birthday = dateFormat("Y-m-d", $byear."-".$bmonth."-".$bday);
                }
            } else {
                $user->birthday = "1910-01-01";
            }
            
            if (!$error['birthday'] && $user->birthday && (date("Y", strtotime($user->birthday)) >= date("Y"))) {
                $error['birthday'] = "Поле заполнено некорректно";
            }

            $city = __paramInit('int', null, 'city_db_id', 0);
            $city_value = __paramInit('string', null, 'city', false);
            $country = __paramInit('int', null, 'country_db_id', 0);
            $country_value = __paramInit('string', null, 'country', false);
            
            if ($city == 0 && strlen($city_value) != 0) {
                $error['city'] = 'Поле заполнено некорректно';
            }
            if ($country == 0 && strlen($country_value) != 0) {
                $error['country'] = 'Поле заполнено некорректно';
            }
            
            $user->country = $country;
            $user->city    = $city;
            
            $company = __paramInit('string', null, 'company') ? substr(__paramInit('string', null, 'company'), 0, 64) : '';
            $about_company = __paramInit('string', null, 'about_company');
            
            $user->compname =  $company;
            if(strlen($about_company) > 500) {
                $error['company'] = "Количество знаков в тексте о компании превышает допустимое значение";
            } else {
                $user->company = $about_company;
            }
            $logo_id = __paramInit('int', null, 'logo_company');
            $logo_name = __paramInit('string', null, 'logo_name');
            if($logo_name) {
                $user->logo = $logo_name;
                $user->Update(wizard::getUserIDReg(), $res);
            }
            
            $info['site']   = $this->initMultiVal('site');
            $info['email']  = $this->initMultiVal('email');
            $info['phone']  = $this->initMultiVal('phone');
            $info['icq']    = $this->initMultiVal('icq');
            $info['skype']  = $this->initMultiVal('skype');
            $info['jabber'] = $this->initMultiVal('jabber');
            $info['lj']     = $this->initMultiVal('lj');
            
            if(!empty($info['site'])) {
                foreach($info['site'] as $i=>$value) {
                    $name = 'site'.($i!=0?"_{$i}":"");
                    if ( !url_validate(addhttp($value), true) && trimhttp($value) != '') {
                        $error[$name] = "Поле заполнено некорректно";
                    } else {
                        $user->$name = addhttp($value);
                    }
                }
            }
            
            if(!empty($info['email'])) {
                foreach($info['email'] as $i=>$value) {
                    if($i == 0) {
                        $name_save = "second_email";
                    } else {
                        $name_save = "email_{$i}";
                    }
                    $name = 'email'.($i!=0?"_{$i}":"");
                    if ( !is_email($value) && $value != '') {
                        $error[$name] = "Поле заполнено некорректно";
                    } else {
                        $user->$name_save = $value;
                    }
                }
            }
            
            if(!empty($info['phone'])) {
                foreach($info['phone'] as $i=>$value) {
                    $name = 'phone'.($i!=0?"_{$i}":"");
                    if ( !preg_match("/^[-+0-9)( #]*$/", $value) ) {
                        $error[$name] = "Поле заполнено некорректно";
                    } else {
                        $user->$name = $value;
                    }
                }
            }
            
            if(!empty($info['icq'])) {
                foreach($info['icq'] as $i=>$value) {
                    $name = 'icq'.($i!=0?"_{$i}":"");
                    if ( !preg_match("/^[-0-9\s]*$/", $value) && !is_email($value) ) {
                        $error[$name] = "Поле заполнено некорректно";
                    } else {
                        $user->$name = $value;
                    }
                }
            }
            
            if(!empty($info['skype'])) {
                foreach($info['skype'] as $i=>$value) {
                    $name = 'skype'.($i!=0?"_{$i}":"");
                    $user->$name = $value;
                }
            }
            
            if(!empty($info['jabber'])) {
                foreach($info['jabber'] as $i=>$value) {
                    $name = 'jabber'.($i!=0?"_{$i}":"");
                    if ( strlen($value) > 255) {
                        $error[$name] = "Количество знаков превышает допустимое значение";
                    } else {
                        $user->$name = $value;
                    }
                }
            }
            
            if(!empty($info['lj'])) {
                foreach($info['lj'] as $i=>$value) {
                    if($i == 0) {
                        $name_save = "ljuser";
                    } else {
                        $name_save = "lj_{$i}";
                    }
                    $name = 'lj'.($i!=0?"_{$i}":"");
                    if ( !preg_match("/^[a-zA-Z0-9_-]*$/", $value)) {
                        $error[$name] = "Поле заполнено некорректно";
                    } else {
                        $user->$name_save = $value;
                    }
                }
            }
            
            if($type_role == step_wizard_registration::TYPE_WIZARD_EMP) {
                $pro_emp =  __paramInit('int', null, 'pro-emp', false); 
                if($pro_emp) {
                    $week_pro = round(__paramInit('int', null, 'week_pro', 0));
                }
            } else {
                $ammount = 0;
                $pro_frl =  __paramInit('int', null, 'pro-frl', false); 
                if($pro_frl) {
                    $pro = __paramInit('string', null, 'pro', -1);

                    switch($pro) {
                        case "1week":
                            $op_code = 76;
                            $ammount = 7;
                            break;
                        case "1":
                            $op_code = 48;
                            $ammount = 19;
                            break;
                        case "3":
                            $op_code = 49;
                            $ammount = 54;
                            break;
                        case "6":
                            $op_code = 50;
                            $ammount = 102;
                            break;
                        case "12":
                            $op_code = 51;
                            $ammount = 180;
                            break;
                        case "-1":
                        default:
                            $ammount = 0;
                            break;
                    }
                }
            }
            
            
            if(!$error && wizard::getUserIDReg()) {
                $error['save'] = $user->Update(wizard::getUserIDReg(), $res);
                
                if(!$error['save']) {
                    
                    if($type_role == step_wizard_registration::TYPE_WIZARD_EMP) {
                        $ammount = $week_pro * 10;
                        if($ammount > 0) {
                            $checkPRO = $this->checkWizardPRO(step_employer::OP_CODE_PRO);
                            if($checkPRO['id'] > 0) {
                                $update = array(
                                    "ammount" => $ammount
                                );
                                wizard_billing::editPaidOption($update, $checkPRO['id']);
                            } else {
                                $insert = array(
                                    "wiz_uid" => step_wizard::getWizardUserID(),
                                    "op_code" => step_employer::OP_CODE_PRO,
                                    "type"    => 3,
                                    "ammount" => $ammount,
                                    "parent"  => wizard::getUserIDReg()
                                );
                                wizard_billing::addPaidOption($insert);
                            }
                        } else {
                            $sql = "DELETE FROM wizard_billing WHERE wiz_uid = ? AND op_code = ?";
                            $this->_db->query($sql, step_wizard::getWizardUserID(), step_employer::OP_CODE_PRO);
                        }
                    } else {
                        // Чистим
                        $sql = "DELETE FROM wizard_billing WHERE wiz_uid = ? AND op_code IN (?l)";
                        $this->_db->query($sql, step_wizard::getWizardUserID(), step_freelancer::getOperationCodePRO());
                        
                        if($ammount > 0) {
                            $insert = array(
                                "wiz_uid" => step_wizard::getWizardUserID(),
                                "op_code" => $op_code,
                                "type"    => 4,
                                "ammount" => $ammount,
                                "parent"  => wizard::getUserIDReg()
                            );
                            
                            wizard_billing::addPaidOption($insert);
                        }
                    }
                    
                    $this->parent->setCompliteStep(true);
                    $this->parent->setNextStep( $this->parent->getPosition() + 1);
                    
                    header("Location: /wizard/registration/");
                    exit;
                }
            } 
             
            if($logo_id > 0) {
                $file = new CFile($logo_id);
                $logo_path = WDCPREFIX . "/" . $file->path . $file->name;
            }
        }
        include $_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.step.info.php";
    }
    
    /**
     * Проверка ПРО у пользователей
     * 
     * @return type 
     */
    public function checkWizardPRO($op_code) {
        if(!is_array($op_code)) $op_code = array($op_code);
        $sql = "SELECT id, ammount, op_code FROM wizard_billing WHERE wiz_uid = ? AND op_code IN(?l)";
        return $this->_db->row($sql, step_wizard::getWizardUserID(), $op_code);
    }
    
    /**
     * Обработка полей которых может быть несколько (icq, skype, etc)
     * 
     * @param type $fname
     * @param type $name
     * @param type $obj
     * @param type $itr
     * @return null 
     */
    public function loadMultiVal($fname, $name, $obj, $itr = 4) {
        $result = array();
        for ($i = 0; $i < $itr; $i++) {
            if($i==0) $field = $fname;
            else $field = "{$name}_{$i}";
            if($obj->$field != '') {
                $result[$i] = $obj->$field;
            } else {
                $result[$i] = null;
            }
        }
        return $result;
    }
    
    /**
     * Инициализация полей 
     * 
     * @param type $name
     * @param type $itr
     * @return string 
     */
    public function initMultiVal($name, $itr = 4) {
        $result = array();
        for ($i = 0; $i < $itr; $i++) {
            $field = "{$name}_{$i}";
            
            $value = __paramInit('string', null, $field, false, $this->getMaxLenForInfoValue($name));
            
            if($value) {
                $result[$i] = $value;
            }/* else {
                $result[$i] = '';
            }*/
        }
        return $result;
    } 
    
    /**
     * 
     * @param type $name
     * @return int|null 
     */
    public function getMaxLenForInfoValue($name) {
        switch($name) {
            case 'icq':
            case 'jabber':
            case 'lj':
            case 'email':
            case 'site': return 96;
            case 'skype': return 64;
            case 'phone': return 24;
            default: return null;  
        }
    }
    
    /**
     * Перенос файлов в рабочие папки сайта
     * 
     * @param array  $files   Массив файлов
     * @param string $table   Таблица для переноса
     * @param string $dir     Директория для переноса
     * @param bool = true $newName  Генерировать новое имя файла
     * @return array 
     */
    function transferFiles($files, $table, $dir, $newName = true) {
        foreach ($files as $key => $file) {
            $objFile        = new CFile($file['id']);
            $ext            = $objFile->getext();
            if (!$newName) {
                $tmp_name = $objFile->name;
            } else {
                $tmp_name       = $objFile->secure_tmpname($dir, '.' . $ext);
                $tmp_name       = substr_replace($tmp_name, "", 0, strlen($dir));
            }
            $objFile->table = $table;
            $copy           = $objFile->_remoteCopy($dir . $tmp_name);
            $data[]         = array('fname' => $objFile->name, 'id' => $objFile->id, 'orig_name' => $objFile->original_name, 'file_id' => $objFile->id);
            unset($objFile);
        }
        return $data;
    }
}

?>