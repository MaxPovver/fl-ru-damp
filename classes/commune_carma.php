<?php

/**
 * Интерфейс необходим для работы в комментариях
 */
interface AutoModeration {
    public static function actionByRate($rate, $scale);
    public static function getScale($name = 'comment');
}

/**
 * Класс для работы с самомодерацией сообществ
 * 
 */
class commune_carma implements AutoModeration 
{
    
    /**
     * Количество минусов для того чтобы сделать комментарий серым
     */
    const CARMA_COMMENT_BLUR = 5; 
    
    /**
     * Количество минусов для того чтобы скрыть комментарий
     */
    const CARMA_COMMENT_HIDE = 10;
    
    /**
     * Количество минусов для того чтобы сделать пост серым
     */
    const CARMA_POST_BLUR = 5;
    
    /**
     * Количество минусов для того чтобы скрыть пост
     */
    const CARMA_POST_HIDE = 20;
    
    /**
     * Количество заблокированных постов у пользователя для блокировки пользователя в сообществе
     * Бан пользователя происходит в триггерах. Если необходимо изменить количество, см. тригеры к таблице commune_members
     */
    const COUNT_BLOCKED_POST = 10;
    
    /**
     * Градация действий в зависимости от количества рейтинга
     * 
     * @param string $name  Тип градации
     * @return array
     */
    public static function getScale($name = 'comment') {
        $scale['comment'] = array(
            'hide' => self::CARMA_COMMENT_HIDE,
            'blur' => self::CARMA_COMMENT_BLUR
        );
        
        $scale['post'] = array(
            'banned' => self::CARMA_POST_HIDE,
            'blur'   => self::CARMA_POST_BLUR
        );
        
        return $scale[$name];
    }
    
    /**
     * Проверяем есть ли возможность голосовать у пользователя в зависимости от даты регистрации
     * 
     * @param integer $uid ИД Пользователя
     */
    public static function isAllowedVote($uid = false) {
        static $is_accept_vote;
        
        if(isset($is_accept_vote[$uid])) {
            return $is_accept_vote[$uid];
        }
        
        if ($uid == false) $uid = get_uid(false);
        if (!$uid)         return false;
        
        if($uid == $_SESSION['uid']) {
            $is_accept_vote[$uid] = (strtotime("{$_SESSION['reg_date']} + 1 month") < time());
            return $is_accept_vote[$uid];
        } else {
            require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
            $user = new users();
            $user->GetUserByUID($uid);
            $is_accept_vote[$uid] = (strtotime("{$user->reg_date} + 1 month") < time());
            return $is_accept_vote[$uid];
        }
    }
    
    /**
     * Выясняем действие которое необходимо произвести с объектом
     * 
     * @param integer $rate Рейтинг
     * @param array  $scale Градация действий по которой выясняем что будем делать
     * @return string название действия
     */
    public static function actionByRate($rate, $scale) {
        foreach($scale as $action => $R) {
            if($rate <= $R * -1) {
                return $action;
                break;
            }
        } 
    }
    
    /**
     * Пользовательский иммунитет у команды, топики нельзя заблокировать
     * 
     * @staticvar array $is_immunity
     * @param integer $uid Ид Пользователя
     * @param array   $data Данные пользователя если имеются
     * @param integer $msg_id ID сообщения
     * @return boolean
     */
    public static function isImmunity($uid, $data = array(), $msg_id = null) {
        static $is_immunity;
        
        if(isset($is_immunity[$uid])) {
            return $is_immunity[$uid];
        }

        $commune_id = commune::getCommuneIDByMessageID($msg_id);
        $status = commune::GetUserCommuneRel($commune_id, $uid);

        if(empty($data)) {
            require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
            $user = new users();
            $user->GetUserByUID($uid);
            $is_immunity[$uid] = ( $user->is_team == 't' || strtolower($user->login) == 'admin' || $status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 );
        } else {
            $is_immunity[$uid] = ( $data['is_team'] == 't' || strtolower($data['login']) == 'admin' || $status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 );
        }

        return $is_immunity[$uid];
    }
}

?>