<?
/**
 * Класс для работы с уведомлениями юзербара
 */
class bar_notify
{

    private $_userID;

    /**
     * ВНИМАНИЕ!!! проверка на наличие uid не делается
     * @param integer $userID
     */
    public function __construct($userID = null) {
        if (!$userID) {
            $userID = get_uid(0);
        }
        $this->_userID = $userID;
    }

    /**
     * добавляет уведомление
     * возвращает ID уведомления или null в случае ошибки
     * @param string $page к какой странице относится уведомление
     * @param string $subpage подстраница
     * @param string $message сообщение
     */
    public function addNotify ($page, $subpage, $message) {
        global $DB;

        if (!$page || !$message) {
            return null;
        }

        $data = array();
        $data['page'] = $page;
        if ($subpage) {
            $data['subpage'] = $subpage;
        }
        $data['message'] = $message;
        $data['user_id'] = $this->_userID;
        $data['create_time'] = 'NOW()';

        $notifyID = $DB->insert('bar_notify', $data, 'id');
        return $notifyID;
    }

    /**
     * удаляет уведомление по ID
     */
    public function delNotifyByID ($notifyID) {

    }

    /**
     * помечает уведомления прочитанными
     * @param array $filter
     */
    public function delNotifies ($filter = null) {
        global $DB;

        $sql = "
            UPDATE bar_notify
            SET lookup_time = NOW()
            WHERE user_id = ?i
                AND lookup_time IS NULL";
        $sql .= $this->_getFilterSQL($filter);

        $res = $DB->query($sql, $this->_userID);
        return (bool)$res;
    }

    /**
     * возвращает уведомления все или прошедшие фильтр
     */
    public function getNotifies ($filter = null) {
        global $DB;

        $sql = "
            SELECT bar_notify.page, bar_notify.subpage, bar_notify.message
            FROM bar_notify
            WHERE bar_notify.user_id = ?i
                AND bar_notify.lookup_time IS NULL";
        $sql .= $this->_getFilterSQL($filter);
        $sql .= ' ORDER BY bar_notify.create_time DESC';

        $rows = $DB->rows($sql, $this->_userID);
        $result = array();
        foreach($rows as $row) {
            if (!$result[$row['page']]) {
                $result[$row['page']] = $row;
                $result[$row['page']]['count'] = 0;
            }
            $result[$row['page']]['count']++;
        }
        foreach($result as &$notify) {
            $this->_correctMessage($notify);
        }

        return $result;
    }

    /**
     * корректирует сообщение в зависимости от количества непросмотренных событий
     * @param array $notify передается по ссылке
     */
    private function _correctMessage (&$notify) {
        switch ($notify['page']) {
            case 'bill':
                if ($notify['count'] > 1) {
                    $notify['message'] = $notify['count'] . ending($notify['count'], ' новое событие', ' новых события', ' новых событий') . ' в личном счете';
                }
                break;
        }

    }

    private function _getFilterSQL ($filter) {
        global $DB;
        if (!$filter || !is_array($filter)) {
            return '';
        }
        $sql = '';
        if ($filter['page']) {
            $sql .= $DB->parse(' AND bar_notify.page = ? ', $filter['page']);
            if ($filter['subpage']) {
                $sql .= $DB->parse(' AND (bar_notify.subpage = ? OR bar_notify.subpage IS NULL) ', $filter['subpage']);
            } else {
                $sql .= ' AND bar_notify.subpage IS NULL';
            }
        }

        return $sql;
    }

}
?>
