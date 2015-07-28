<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/settings.php");

/**
 * Класс для работы с празднованием Дня Рождения Фриланса
 *
 */
class birthday
{
    
    /**
     * Периоды проведения дня рождения
     *
     * @var array
     */
    private $periods = array(2009=>array('2009-04-20','2009-05-13'));

    /**
     * Дата начала открытой регистрации
     *
     * @var date
     */
    public $regFromTm;

    /**
     * Дата закрытия регистрации
     *
     * @var date
     */
    public $regToTm;

    /**
     * Год, с которым работаем
     *
     * @var integer
     */
    public $year;

    /**
     * Флаг закрытия
     *
     * @var integer
     */
    public $isClosed;



    /**
     * Конструктор класса
     *
     * @param integer $year              год, для которого инициализируем данные
     *
     * @return void
     */    
    function __construct($year)
    {
        $this->regFromTm = strtotime($this->periods[$year][0]);
        $this->regToTm   = strtotime($this->periods[$year][1]) + 24*3600;
        $this->year = $year;
        $status = settings::GetVariable('birthday'.$this->year, 'status');
        $this->isClosed = (!$status && (time() < $this->regFromTm || time() > $this->regToTm) || $status=='close');
    }



    /**
     * Устанавливает или проверяет статус дня рождения
     *
     * @param integer $status            статус
     *
     * @return integer                   1 в случае успеха, 0 в случае ошибки
     */    
    function setStatus($status)
    {
        if(pg_affected_rows(settings::SetVariable('birthday'.$this->year, 'status', $status)))
            return 1;
        $sql = "INSERT INTO settings (id, module, variable, value) SELECT COALESCE(MAX(id),0)+1, 'birthday{$this->year}', 'status', '{$status}' FROM settings";
        if(pg_query(DBConnect(),$sql))
            return 1;
        return 0;
    }



    /**
     * Получает данные пользователя, зарегистрированного на празднике
     *
     * @param integer $user_id           id пользователя
     *
     * @return mixed                     данные пользователя или NULL в случае неуспеха или если пользователь не зарегистрирован на празднике
     */    
    function getUser($user_id)
    {
        if($user_id) {
            if(($res = pg_query(DBConnect(), "SELECT * FROM birthday WHERE uid = {$user_id} AND year = {$this->year}")) && pg_num_rows($res))
                return pg_fetch_assoc($res);
        }
        return NULL;
    }



    /**
     * Добавляет нового пользователя в список, принимающих участие в праздновании дня рождения
     *
     * @param integer $user_id           id пользователя
     * @param array $user                массив с данными пользователя
     * @param boolean $edit              добавление (false) или обновление пользователя (true)
     *
     * @return integer                   1 в случае успеха, 0 в случае ошибки
     */    
    function add($user_id, $user, $edit = false)
    {
        if(!$edit)
            $sql = "INSERT INTO birthday (uid, uname, usurname, utype, year) VALUES ({$user_id}, '{$user['uname']}', '{$user['usurname']}', {$user['utype']}, {$this->year})";
        else
            $sql = "UPDATE birthday SET (uname, usurname, utype) = ('{$user['uname']}', '{$user['usurname']}', {$user['utype']}) WHERE uid = {$user_id} AND year = {$this->year}";
        if(pg_query(DBConnect(),$sql))
            return 1;
        return 0;
    }



    /**
     * Получает количество зарегистрированный пользователей на празднике
     *
     * @return integer                   количество пользователей
     */    
    function getRegCount()
    {
        if(($res = pg_query(DBConnect(), "SELECT COUNT(uid) FROM birthday WHERE year = {$this->year}")) && pg_num_rows($res))
            return pg_fetch_result($res,0,0);
        return 0;
    }



    /**
     * Получает данные зарегистрированный пользователей на празднике
     *
     * @param mixed $accepted            все пользователи (NULL), подтвердившие регистрацию, оплатившие (true), не подтвердившие регистрацию, оплатившие (false)
     * @param string $order_by           поля и методы сортировки (ORDER BY SQL)
     *
     * @return mixed                     массив с данными пользователей или 0 в случае неуспеха
     */    
    function getAll($accepted = NULL, $order_by="")
    {
        $addit = ($order_by)?" ORDER BY ".$order_by.", id":" ORDER BY id";
        $sql = 
        "SELECT b.*, u.login, u.uname, u.usurname, u.email
           FROM birthday b
         INNER JOIN
           users u
             ON u.uid = b.uid
          WHERE b.year = {$this->year}
            ".($accepted===NULL ? '' : 'AND b.is_accepted = '.($accepted ? 'true' : 'false')).$addit;
        if(($res = pg_query(DBConnect(), $sql)) && pg_num_rows($res))
            return pg_fetch_all($res);
        return 0;
    }



    /**
     * Подтверждает или отменяет участие пользователя на празднговании дня рождения
     *
     * @param integer $id                id пользователя
     *
     * @return integer                   1 в случае успеха, 0 в случае ошибки
     */    
    function accept($id)
    {
        $sql = "UPDATE birthday SET is_accepted = NOT(is_accepted) WHERE id = {$id}";
        if(pg_query(DBConnect(), $sql))
            return 1;
        return 0;
    }



    /**
     * Удаляет пользователя из списка
     *
     * @param integer $id                id пользователя
     *
     * @return integer                   1 в случае успеха, 0 в случае ошибки
     */    
    function del($id)
    {
        $sql = "DELETE FROM birthday WHERE id = {$id}";
        if(pg_query(DBConnect(), $sql))
            return 1;
        return 0;
    }
}
    
?>