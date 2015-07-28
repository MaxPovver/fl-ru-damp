<?php

require_once(ABS_PATH . '/classes/config/tservices_config.php');
require_once(ABS_PATH . '/tu/models/TServiceOrderModel.php');

/**
 * Импровизированный вью-хелпер
 */
class tservices_helper 
{
    private static $_urls = array(
        'new'                   => '/users/%s/tu/new/',
        'edit'                  => '/users/%s/tu/edit/%d/',
        'close'                 => '/users/%s/tu/close/',
        'delete'                => '/users/%s/tu/delete/',
        'profile_lis'           => '/users/%s/tu/',
        'img_src'               => '%s/users/%s/tu/%s',
        'card'                  => '/tu/%d/%s.html',
        'no_photo_path'         => '%s/images/no_foto_b.png',
        'photo_path'            => '%s/users/%s/foto/%s',
        'catalog_category'      => '/tu/%s/',
        'sbr_url'               => '/bezopasnaya-sdelka/?site=create&tuid=%d',
        'order_url'             => '/tu/%d/order/',
        'order_card_url'        => '/tu/order/%d/',
        'order_change_status'   => '/tu/order/%d/status/%s/%s/',
        'new_order_url'         => '/tu/new-order/%s/',
        'frl_orders'            => '/tu-orders/',
        'feedback_url'          => '/users/%s/opinions/#p_%d-2',
        'guest_porder_url'      => '/guest/new/personal-order/%s/',
        'porder_url'            => '/new-personal-order/%s/'
        //'order_cancel_url'  => '/tu/order/%d/cancel/',
        //'order_decline_url' => '/tu/order/%d/decline/',
        //'order_accept_url'  => '/tu/order/%d/accept/'
        );


    private static $_order_cost_txt = array(
        1 => 'Заказ на сумму %s',
        0 => 'Сделка на сумму %s'
    );

        



        /**
      * Заменяем протокол
      * @todo: отказываемся так как браузер блокирует картинки с не подтвержденным сертификатом
      * 
      * @param type $str
      * @return type
      */
     
    public static function setProtocol($str)
    {
        return $str;
        //if(strpos($str, 'rutube.ru')) return $str;
        //return preg_replace("/^http:\/\//", "https://", $str);
    }
     
        /**
     * Сохранить сообщение из константы
     * 
     * @param type $const
     * @param type $title
     * @return type
     */
    public static function setFlashMessageFromConstWithTitle($const, $title)
    {
        require_once 'tservices_const.php';
        $title = reformat(stripslashes($title), 30, 0, 1);
        $message = sprintf(tservices_const::enum('msg', $const),$title);
        return self::setFlashMessage($message);
    }
        

    
    /**
     * Зафиксировать сообщение об ошибке для дальшейшего отображения
     * 
     * @param string $value
     * @return bool
     */
    public static function setErrorFlashMessage($value)
    {
        return self::setFlashMessage($value, 'error');
    }

    

    /**
     * Сохранить сообщения до следующего обращения к странице
     * 
     * @param type $value
     * @param type $type
     * @return boolean
     */
    public static function setFlashMessage($value, $type = 'success', $key = 'default')
    {
        if(empty($value)) return false;
        $flashdata = array('type' => $type, 'value' => $value);
        $_SESSION['flash_message'][$key] = $flashdata;
        return true;
    }
    
    
    /**
     * Показать все текущии сообщения в очереди
     * 
     * @return string
     */
    public static function showFlashMessages()
    {   
        $html = '';
        if(!isset($_SESSION['flash_message']) || 
           !is_array($_SESSION['flash_message']) || 
           !count($_SESSION['flash_message'])) return $html;
        
        ob_start();
        foreach($_SESSION['flash_message'] as $key => $flashdata)
        {
            $message = $flashdata['value'];
            $type = $flashdata['type'];
            include 'tpl.flash_message.php';
            unset($_SESSION['flash_message'][$key]);
        }
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
       

    
    public static function getMessage($message, $type)
    {
        ob_start();
        include 'tpl.flash_message.php';
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }

    


    /**
     * Хелпер всплывающих ошибок
     * 
     * @param type $field_id
     * @param type $error_text
     * @param type $visible_css_selector
     * @return type
     */
    public static function input_element_error($field_id, $error_text = '', $visible_css_selector = '')
    {
        ob_start();
        include 'tpl.input_error.php';
        $result = ob_get_contents();
        ob_end_clean();
        
        return $result;
    }
    
    
    /**
     * Хелпер всплывающей подсказки
     * 
     * @param type $message
     */
    public static function tooltip($message)
    {
        if(!empty($message))
        {
            include 'tpl.tooltip.php';
        }
    }
    
    
    /**
     * Дай ури
     * 
     * @param type $key
     * @return type
     */
    public static function url($key)
    {
        return @self::$_urls[$key];
    }
    
    
    /**
     * Транслит, сер
     * 
     * @param type $str
     * @return type
     */
    public static function translit($str)
    {
        return translit(strtolower(htmlspecialchars_decode( $str , ENT_QUOTES )));
    }
    
    
    /**
     * Собираем урл картинки
     * 
     * @param type $file
     * @param type $login
     * @param type $prefix
     * @return type
     */
    public static function image_src($file, $login, $prefix = '')
    {
        return sprintf(self::url('img_src'), WDCPREFIX, $login, $prefix . $file);
    }
    
    
    
    /**
     * Формируем юзер-пик
     * 
     * @param type $file
     * @param type $login
     * @return type
     */
    public static function photo_src($file, $login)
    {
        if(!$file) return sprintf (self::url('no_photo_path'), WDCPREFIX);
        return sprintf(self::url('photo_path'), WDCPREFIX, $login, $file);
    }

    



    /**
     * Формирование ссылки на карточку ТУ
     * 
     * @param type $id
     * @param type $title
     * @return type
     */
    public static function card_link($id, $title)
    {
        return sprintf(self::url('card'), $id, self::translit($title));
    }

    
    /**
     * Формирование ссылки на редактирование
     * 
     * @param type $login
     * @param type $id
     * @return type
     */
    public static function edit_link($login, $id)
    {
        return sprintf(self::url('edit'), $login, $id);
    }


    
    
    /**
     * Формирование ссылки на форму добавления ТУ
     * 
     * @param string $login
     * @return string
     */
    public static function new_url($login = null)
    {
        if(!$login) $login = $_SESSION['login'];
        return sprintf(self::url('new'), $login);
    }

    



    /**
     * Формирование ссылки на категорию в каталоге
     * 
     * @param string $translit
     * @return string
     */
    public static function category_url($str)
    {
        return sprintf(self::url('catalog_category'), trim($str));
    }


    /**
     * Форматирование цены
     * 
     * @param type $cost
     * @param type $currency
     * @param type $preffix
     * @return type
     */
    public static function cost_format($cost, $currency = true, $preffix = false, $short_currency_format = true)
    {
        $is_negative = ($cost < 0);
        $cost = round(abs($cost), 2);
        $cur = '&nbsp;' . (($short_currency_format)?'р.':ending($cost, 'рубль', 'рубля', 'рублей'));
        $cost = number_format($cost, 2, ',', ' ');
        
        return (($preffix || $is_negative)?(($is_negative)?'-':'+'):'') . 
               str_replace(',00', '', $cost) . 
               (($currency)?$cur:'');        
    }
    
    
    /**
     * Форматирование кол-ва дней
     * 
     * @param int $days - кол-во дней
     * @return string
     */
    public static function days_format($days)
    {
        return $days . ' ' . ending($days, 'день', 'дня', 'дней');
    }

    



    /**
     * Выводит дату словами: то есть "2013-09-12" преобразует в "12 сентября 2013"
     * 
     * @param  string 2013-09-12
     * @return string 12 сентября 2013
     */
    public static function date_text($date = '') 
    {
        $time = strtotime( $date );
        return date( 'j', $time ) . ' ' . monthtostr(date('n', $time),true) . ' ' . date( 'Y', $time );
    }
    
    

    /**
     * Проверяем есть ли логин в списке разрешенных
     * 
     * @assert ('alex') == TRUE
     * @assert () == TRUE
     * @assert ('fake') == FALSE
     * 
     * @param type $login
     * @return boolean
     */
    public static function isUserOrderWhiteList($login = null)
    {
        global $order_whitelist;
        if(!isset($order_whitelist) || empty($order_whitelist)) return TRUE;
        if(!$login && isset($_SESSION['login'])) $login = $_SESSION['login'];
        
        return in_array($login, $order_whitelist);
    }
    
    
    
    
    /**
     * Проверка доступа к функционалу резервирования "новой БС" для заказов ТУ
     * Проверяется доступ из списков в настройках - /classes/config/tservices_config.php
     * Учитывается доступ к категории И пользователю
     *
     * 
     * @global type $allow_categories_to_reserve - список доступных категорий
     * @global type $allow_users_to_reserve - список разрешенных пользователей
     * @param type $category_id - ID категории типовой услуги
     * @param type $login - логин пользователя
     * @return boolean - разрешен/запрещен доступ
     */
    public static function isAllowOrderReserve($category_id = 0, $login = null)
    {
        global $allow_categories_to_reserve, $allow_users_to_reserve;
        
        $is_cat = isset($allow_categories_to_reserve) && !empty($allow_categories_to_reserve);
        $is_cat = ($is_cat)?in_array($category_id, $allow_categories_to_reserve):!$is_cat;       

        $_srv = strtolower(SERVER);
        if(!$login && isset($_SESSION['login'])) $login = $_SESSION['login'];
        $is_lgn = isset($allow_users_to_reserve[$_srv]) && !empty($allow_users_to_reserve[$_srv]);
        $is_lgn = ($is_lgn)?in_array($login, $allow_users_to_reserve[$_srv]):!$is_lgn;
        
        return ($is_cat && $is_lgn);
    }

    
    
    /**
     * Проверить имеет ли текущий заказ схему оплаты по новой БС через резерв средств
     * @todo: Может переместить в модель заказов ТУ (TServiceOrderModel)?
     * 
     * @param type $paytype
     * @return type
     */
    public static function isOrderReserve($paytype)
    {
        $paytype = (is_array($paytype))?$paytype['pay_type']:$paytype;
        return $paytype == TServiceOrderModel::PAYTYPE_RESERVE;
    }

    


    /**
     * Вернуть в зависимости от доступа ссылку
     * 
     * @param int $service_id ID ТУ
     * 
     * @return string
     */
    public static function getOrderUrl($service_id)
    {
        return sprintf(static::url((static::isUserOrderWhiteList())?'order_url':'sbr_url'),$service_id);
    }
    
    
    /**
     * Ссылка на карточку заказа на базе ТУ
     * 
     * @param int $service_id
     * @return string
     */
    public static function getOrderCardUrl($order_id)
    {
        return sprintf(tservices_helper::url('order_card_url'),$order_id);
    }
    
    
    
    public static function getOrderStatusUrl($order_id, $status, $uid = NULL)
    {
        $params = array('order_id' => $order_id,'status' => $status);
        $hash = static::getOrderUrlHash($params, $uid);
        return sprintf(tservices_helper::url("order_change_status"),$order_id,$status,$hash);
    }
    
    
    public static function getOrderUrlHash($params, $uid = NULL)
    {
        if (!$uid)  {
            $uid = $_SESSION['uid'];
        }
        return md5(TServiceOrderModel::SOLT . serialize(array_values($params)) . $uid);
    }
    
    
    public static function getNewOrderUrl($code)
    {
        return sprintf(tservices_helper::url('new_order_url'), $code);
    }
    
    
    
    public static function getFeedbackUrl($id, $login = null)
    {
        if(!$login && isset($_SESSION['login'])) $login = $_SESSION['login'];
        return sprintf(tservices_helper::url('feedback_url'), $login, $id);
    }

    



    /**
     * Форматирование теста и стоимости в отзывах карточки ТУ
     * 
     * @param int $type
     * @param float $cost
     * @return string
     */
    public static function getOrderCostTxt($type, $cost)
    {
        return sprintf(self::$_order_cost_txt[$type],self::cost_format($cost, true, false, false));
    }
    
    
    /**
     * Ссылка на создание персонального заказа для не зарегистрированного пользователя
     * 
     * @return string
     */
    public static function getGuestPersonalOrderUrl($login = null)
    {
        $login = (!$login)?__paramInit('string', 'user', 'user'):$login;
        return sprintf(tservices_helper::url('guest_porder_url'), $login);
    }
    
    
    public static function getPersonalOrderUrl($login)
    {
        $url_key = (get_uid(false))?'porder_url':'guest_porder_url';
        return sprintf(tservices_helper::url($url_key), $login);
    }
    
    
}

