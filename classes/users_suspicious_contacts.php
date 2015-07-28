<?php
/**
 * Класс для работы с разделом админки Подозрения на обмен контактами.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class users_suspicious_contacts {
    static $ipp = 50;
    
    /**
     * Поставить процесс перепроверки в расписание или удалить из расписания.
     * 
     * @return int 0 - ошибка, 1 - поставлен, 2 - удален
     */
    function setResetContacts() {
        $nRet = 0;
        
        if ( self::getResetContacts() ) {
            $GLOBALS['DB']->query( "DELETE FROM variables WHERE name = 'users_suspicious_contacts'" );
            $nRet = 2;
        }
        else {
            $GLOBALS['DB']->insert( 'variables', array('name' => 'users_suspicious_contacts') );
            $nRet = 1;
        }
        
        return empty( $GLOBALS['DB']->error ) ? 0 : $nRet;
    }
    
    /**
     * Проверить находится ли процесс перепроверки в расписании
     * 
     * @return bool true - находится, false - отсутствует
     */
    function getResetContacts() {
        $sId = $GLOBALS['DB']->val( "SELECT id FROM variables WHERE name = 'users_suspicious_contacts'" );
        return !empty( $sId );
    }
    
    /**
     * Запускает процесс перепроверки
     */
    function resetContacts() {
        set_time_limit(0);
        
        $sQuery = "DELETE FROM users_suspicious_contacts_hide;
            INSERT INTO users_suspicious_contacts(user_id, utable, ucolumn, rec_id)
            SELECT i.user_id, i.utable, i.ucolumn, i.rec_id 
            FROM (
                (SELECT user_id, 'portf_choise' AS utable, '' AS ucolumn, prof_id AS rec_id 
                FROM portf_choise pc 
                INNER JOIN freelancer f ON f.uid = pc.user_id 
                WHERE f.is_pro = false AND f.is_banned = '0' AND (". self::getWhere('pc.portf_text') ." ))
                UNION 
                (SELECT user_id, 'portfolio' AS utable, '' AS ucolumn, id AS rec_id 
                FROM portfolio p 
                INNER JOIN freelancer f ON f.uid = p.user_id 
                WHERE f.is_pro = false AND f.is_banned = '0' AND (". self::getWhere('p.name') ." ". self::getWhere('p.descr', 'OR') ."))
                UNION 
                SELECT uid AS user_id, 'freelancer' AS utable, 'pname' AS ucolumn, NULL AS rec_id FROM freelancer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('pname') .") 
                UNION   
                SELECT uid AS user_id, 'freelancer' AS utable, 'spec_text' AS ucolumn, NULL AS rec_id FROM freelancer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('spec_text') .") 
                UNION 
                SELECT uid AS user_id, 'freelancer' AS utable, 'resume' AS ucolumn, NULL AS rec_id FROM freelancer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('resume') .") 
                UNION 
                SELECT uid AS user_id, 'freelancer' AS utable, 'konk' AS ucolumn, NULL AS rec_id FROM freelancer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('konk') .") 
                UNION 
                SELECT uid AS user_id, 'freelancer' AS utable, 'status_text' AS ucolumn, NULL AS rec_id FROM freelancer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('status_text') .") 
                UNION 
                SELECT uid AS user_id, 'employer' AS utable, 'compname' AS ucolumn, NULL AS rec_id FROM employer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('compname') .") 
                UNION 
                SELECT uid AS user_id, 'employer' AS utable, 'resume' AS ucolumn, NULL AS rec_id FROM employer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('resume') .") 
                UNION 
                SELECT uid AS user_id, 'employer' AS utable, 'pname' AS ucolumn, NULL AS rec_id FROM employer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('pname') .") 
                UNION 
                SELECT uid AS user_id, 'employer' AS utable, 'company' AS ucolumn, NULL AS rec_id FROM employer WHERE is_pro = false AND is_banned = '0' AND (". self::getWhere('company') .") 
            ) AS i;";

        $GLOBALS['DB']->squery( $sQuery );
    }
    
    /**
     * Утвердить запись
     * 
     * @param int $sid ID записи
     */
    function resolveContacts( $sid = '' ) {
        $GLOBALS['DB']->query( 'DELETE FROM users_suspicious_contacts WHERE id = ?i', $sid );
    }
    
    /**
     * Массово утвердить записи
     * 
     * @param array $sid массив ID записей
     */
    function massResolveContacts( $sid = array() ) {
        $GLOBALS['DB']->query( 'DELETE FROM users_suspicious_contacts WHERE id IN (?l)', $sid );
    }
    
    /**
     * Список подозрений на контакты
     * 
     * @param  string $prefix проверенные, непроверенные (фактически пустая строка или '_hide')
     * @param  int $page номер страницы списка
     * @param  string $order сортировка ASC/DESC
     * @return array 
     */
    function getContacts( $prefix = '', $page = 1, $order = 'ASC' ) {
        $offset = self::$ipp * ( $page - 1 );
        $sQuery = 'SELECT i.sid, i.rec_id, i.utable, i.id, i.user_id, i.ucolumn, i.new_val, 
                i.is_video, i.video_link, i.pict, i.prev_pict, i.cost, i.cost_type, i.time_value, i.time_type, i.link, i.name, i.descr, i.prof_id, 
                i.login, i.uname, i.usurname, i.is_chuck, i.warn, i.is_banned, i.ban_where, 
                i.resume, i.pname, i.spec_text, i.konk, i.status_text, i.company, i.compname 
            FROM ( 
                SELECT un.sid, un.rec_id, un.utable, un.id, un.user_id, un.ucolumn, un.new_val, 
                    un.is_video, un.video_link, un.pict, un.prev_pict, un.cost, un.cost_type, un.time_value, un.time_type, un.link, un.name, un.descr, un.prof_id, 
                    u.login, u.uname, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
                    NULL AS resume, NULL AS pname, NULL AS spec_text, NULL AS konk, NULL AS status_text, NULL AS company, NULL AS compname 
                FROM (
                    (SELECT b.id AS sid, b.rec_id, b.utable, pc.prof_id AS id, b.user_id, b.ucolumn, pc.portf_text AS new_val, 
                        NULL AS is_video, NULL AS video_link, NULL AS pict, NULL AS prev_pict, NULL AS cost, NULL AS cost_type, NULL AS time_value, NULL AS time_type, NULL AS link, NULL AS name, NULL AS descr, pc.prof_id 
                    FROM users_suspicious_contacts' . $prefix . ' b 
                    INNER JOIN portf_choise pc ON pc.user_id = b.user_id AND pc.prof_id = b.rec_id 
                    WHERE b.utable = \'portf_choise\')

                    UNION

                    (SELECT b.id AS sid, b.rec_id, b.utable, p.id, p.user_id, NULL AS ucolumn, NULL AS new_val, 
                        p.is_video, p.video_link, p.pict, p.prev_pict, p.cost, p.cost_type, p.time_value, p.time_type, p.link, p.name, p.descr, NULL AS prof_id 
                    FROM users_suspicious_contacts' . $prefix . ' b 
                    INNER JOIN portfolio p ON p.id = b.rec_id 
                    WHERE b.utable = \'portfolio\')
                ) AS un
                LEFT JOIN users AS u ON u.uid = un.user_id 

                UNION

                (SELECT b.id AS sid, b.rec_id, b.utable, NULL AS id, b.user_id, b.ucolumn, NULL AS new_val, 
                    NULL AS is_video, NULL AS video_link, NULL AS pict, NULL AS prev_pict, NULL AS cost, NULL AS cost_type, NULL AS time_value, NULL AS time_type, NULL AS link, NULL AS name, NULL AS descr, NULL AS prof_id, 
                    u.login, u.uname, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
                    u.resume, u.pname, u.spec_text, u.konk, u.status_text, NULL AS company, NULL AS compname 
                FROM users_suspicious_contacts' . $prefix . ' b 
                INNER JOIN freelancer u ON u.uid = b.user_id 
                WHERE b.utable = \'freelancer\')

                UNION

                (SELECT b.id AS sid, b.rec_id, b.utable, NULL AS id, b.user_id, b.ucolumn, NULL AS new_val, 
                    NULL AS is_video, NULL AS video_link, NULL AS pict, NULL AS prev_pict, NULL AS cost, NULL AS cost_type, NULL AS time_value, NULL AS time_type, NULL AS link, NULL AS name, NULL AS descr, NULL AS prof_id, 
                    u.login, u.uname, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
                    u.resume, u.pname, NULL AS spec_text, NULL AS konk, NULL AS status_text, u.company, u.compname
                FROM users_suspicious_contacts' . $prefix . ' b 
                INNER JOIN employer u ON u.uid = b.user_id 
                WHERE b.utable = \'employer\')
            ) AS i 
            ORDER BY i.id '. $order .' LIMIT '. self::$ipp . ' OFFSET ' . $offset;
        
        return $GLOBALS['DB']->rows( $sQuery );
    }
    
    /**
     * Вспомогательная функция. Часть запроса перепроверки
     * 
     * @param  string $sFld имя поля
     * @param  string $sOr опционально. оператор
     * @return string 
     */
    function getWhere( $sFld = '', $sOr = '' ) {
        return ' '. $sOr .' '. $sFld .' ~ E\'(?i)(?:[a-z0-9_-]+\\\.)*[a-z0-9_-]+@[a-z0-9_-]+(?:\\\.[a-z0-9_-]+)*\\\.[a-z]{2,4}\' 
        OR '. $sFld .' ~ E\'(?i)([a-zа-яёА-ЯЁ0-9_-]+\\\.)*[a-zа-яёА-ЯЁ0-9_-]+@[a-zа-яёА-ЯЁ0-9_-]+(\\\.[a-zа-яёА-ЯЁ0-9_-]+)*\\\.рф\' 
        OR '. $sFld .' ~ E\'\\\+?(?:[\\\(\\\)\\\s-]*\\\d){5,13}\' 
        OR '. $sFld .' ~ E\'\\\d{3}-\\\d{2}-\\\d{2}\' 
        OR '. $sFld .' ~ E\'(?i)[GREZUBYCD]+[\\\d]{12}\' 
        OR '. $sFld .' ~ E\'\\\d{16}\' 
        OR '. $sFld .' ~ E\'\\\d{4}\\\s+\\\d{4}\\\s+\\\d{4}\\\s+\\\d{4}\' 
        OR '. $sFld .' ~ E\'4100\\\d{10}\'';
    }
    
    /**
     * Список подозрений на контакты
     * 
     * @param  string $prefix проверенные, непроверенные (фактически пустая строка или '_hide')
     * @return int 
     */
    function countContacts( $prefix = '' ) {
        return $GLOBALS['DB']->val( 'SELECT COUNT(id) FROM users_suspicious_contacts' . $prefix );
    }
}