<?php

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

/**
 * Class TServiceOrderUserProfile
 *
 * Виджет - Блок информации о фрилансере в карточке заказа
 */
class TServiceOrderUserProfile extends CWidget 
{
        protected $order;
        protected $is_emp;


        public function run() 
        {
            $user = $this->order[$this->is_emp?'freelancer':'employer'];    
            
            //получаем общее кол-во отзывов
            $oplinks = NULL;
            $opcount = opinions::GetCounts($user['uid'], array('total'));

            if (array_sum($opcount['total']) > 0) {
                $oplinks = array(
                    'p' => getSortOpinionLinkEx('frl', "total", 1, $user['login'], zin($opcount['total']['p']), null, 0),
                    'n' => getSortOpinionLinkEx('frl', "total", 2, $user['login'], zin($opcount['total']['n']), null, 0),
                    'm' => getSortOpinionLinkEx('frl', "total", 3, $user['login'], zin($opcount['total']['m']), null, 0)
                );
            }

            //город юзера
            $city_id = ($this->order['is_meet'] == 't' && $this->order['city'] > 0)?$this->order['city']:$user['city'];
            $user['place_title'] = '';        
            if($city_id > 0)
            {
                $user['place_title'] = city::getCountryName($city_id) . ', ' . 
                                       city::getCityName($city_id);                
            }
            
            //собираем шаблон
            $this->render('t-service-order-user-profile', array(
                'user' => $user,
                'oplinks' => $oplinks
            ));
	}
}