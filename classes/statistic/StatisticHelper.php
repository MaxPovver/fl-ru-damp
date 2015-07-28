<?php

/**
 * ѕомошнички
 * Ќабор статических методов облегчающих работу
 */
class StatisticHelper 
{

    /**
     * ‘ормируем ссылку дл€ трекинга 
     * факта открыти€ письма с рассылкой
     * 
     * @param int $type тип 0/1 фрилансер/работодатель
     * @param string $label метка дл€ статистики - год регистрации текущего юзера
     * @param string $uid уникальный ID юзера - рекомендуетс€ login + uid
     * @return string
     */
    public static function track_url($type, $label, $timestamp, $uid)
    {
        $params = array(
            't' => (string)$type,
            'y' => (string)$label,
            's' => (string)$timestamp,
            'l' => md5($uid)
        );
        
        $params['h'] = md5(STAT_URL_PREFIX . serialize($params));
        return '/s.gif?' . http_build_query($params);
    }
    
    
    
}

