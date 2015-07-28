<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');

/**
 * Class TServiceModel
 * Модель типовой услуги
 */
class TServiceModel extends atservices_model {

	private $TABLE            = 'tservices';
	private $TABLE_CATEGORIES = 'tservices_categories';
	private $TABLE_FILES      = 'file_tservices';
    private $TABLE_BLOCKED    = 'tservices_blocked';
    private $TABLE_FREELANCER = 'freelancer';
    private $TABLE_DEBT       = 'tservices_orders_debt';
        
    
    /**
     * Расширение запроса
     *  
     * @var type 
     */
    protected $_select = '';
    protected $_join = '';




    /**
     * Выбрать ТУ для списка пользователей 
     * по указанному количеству на каждого
     * 
     * @param type $uids
     * @param type $limit
     * @param type $expire
     * @param type $group
     * @return type
     */
    public function getListByUids($uids, $limit = 3, $expire = 0, $group = false)
    {
        $sql = $this->db()->parse("
            SELECT 
                DISTINCT ON (q.id) 
                q.*,
                f.fname AS file
            FROM (
                SELECT 
                    s.id AS id, 
                    s.user_id,
                    s.title AS title, 
                    s.price AS price,
                    s.videos AS videos,
                    s.total_feedbacks AS total_feedbacks,
                    row_number() OVER(PARTITION BY s.user_id ORDER BY s.id DESC) AS rownum
                FROM {$this->TABLE} AS s 
                LEFT JOIN {$this->TABLE_DEBT} AS od ON od.user_id = s.user_id 
                LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
                WHERE 
                    s.user_id IN(?l) 
                    AND s.deleted = FALSE 
                    AND s.active = TRUE 
                    AND sb.src_id IS NULL
                    AND (od.id IS NULL OR od.date >= NOW())
            ) AS q
            LEFT JOIN {$this->TABLE_FILES} AS f ON f.src_id = q.id AND f.small = 4
            WHERE q.rownum <= ?i
            ORDER BY q.id DESC, f.preview DESC, f.id 
        ", $uids, $limit);
            
        $memBuff = new memBuff();
        $result = $memBuff->getSql($error, $sql, $expire, true, $group);
        return $result;   
    }
    

    /**
     * Расширяем запрос выборки данными пользователя владельца ТУ
     * 
     * @return \TServiceModel
     */
    public function addOwnerInfo()
    {
        $this->_select .= "
            u.login,
            u.photo,
            u.uname,
            u.usurname,
            u.is_profi,
        ";
        
        $this->_join .= "INNER JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = s.user_id";        
                
        return $this;
    }

    

    /**
	 * Для каждой строки массива $rows извлекает сведения о типовой услуге, ID которой указан в $id_attr
	 * Если $extend_attr указан, то сведения вписываются в строки rows отдельным ключом
	 * Иначе ключи строк расширяются извлечёнными сведениями, при необходимости им дописываются префиксы $extend_prefix
	 *
	 * @param $rows
	 * @param $id_attr
	 * @param $extend_attr
	 * @param $extend_prefix
	 * @return $this
	 */
	public function extend(&$rows, $id_attr, $extend_attr = null, $extend_prefix = '')
	{
		$ids = array();
		foreach($rows as $row) // собрать ID
		{
			if (!empty($row[$id_attr]))
			{
				$ids[$row[$id_attr]] = false;
			}
		}
		if (empty($ids))
		{
			return $this;
		}

		$sql = <<<SQL
SELECT
    DISTINCT ON (s.id)
	s.id AS {$extend_prefix}id,
	s.title AS {$extend_prefix}title, -- заголовок типовой услуги
	s.price AS {$extend_prefix}price, -- цена типовой услуги
	s.days AS {$extend_prefix}days, -- срок исполнения в днях
	s.videos AS {$extend_prefix}videos, -- видеоролики к типовой услуге
	f.fname AS {$extend_prefix}file,-- иллюстрация (thumbnail) к типовой
    {$this->_select}
	c1.title AS {$extend_prefix}category_title, -- категория типовой услуги
	c1.link AS {$extend_prefix}category_link,
	c2.title AS {$extend_prefix}category_parent_title, -- родительская категория типовой услуги
	c2.link AS {$extend_prefix}category_parent_link
FROM {$this->TABLE} AS s
LEFT JOIN {$this->TABLE_FILES} AS f ON f.src_id = s.id AND f.small = 4
LEFT JOIN {$this->TABLE_CATEGORIES} AS c1 ON c1.id = s.category_id
LEFT JOIN {$this->TABLE_CATEGORIES} AS c2 ON c2.id = c1.parent_id
{$this->_join}
WHERE s.id in (?lu)
ORDER BY s.id, f.preview DESC, f.id
SQL;

        $extends = $this->db()->cache(300)->rows($sql, array_keys($ids));
            
		foreach($extends as $extend) // разобрать строки по ID
		{
			$ids[$extend['id']] = $extend;
		}

		foreach($rows as $i => &$row) // подставить дополнительные сведения в исходный список строк
		{
			if (empty($ids[$row[$id_attr]]))
			{
				continue;
			}
			$extend = $ids[$row[$id_attr]];
			if (false === $extend)
			{
				continue;
			}

			if ($extend_attr)
			{
				$row[$extend_attr] = $extend; // отдельный ключ
			} else
			{
				$row = array_merge($row, $extend); // расширение массива
			}
		}
        
		return $this;
	}

	/**
	 * В каждой строке списка типовых услуг добавляет массив видео-клипов
	 *
	 * @param array $rows
	 * @param $src_attrs имя атрибута, где указана информация о видео-клипах
	 * @param $dest_attr имя атрибута, в котором будет сохранён список видео-клипов
	 * @return $this
	 */
	public function readVideos(&$rows, $src_attrs, $dest_attr)
	{
		if (empty($src_attrs) || empty($dest_attr)) {
			return $this;
		}
		foreach($rows as &$row) {
			$row[$dest_attr] = mb_unserialize($row[$src_attrs]);
		}
		return $this;
	}
        
        /**
         * Кол-во типовых услуг всего
         * 
         * @return type
         */
        public function countTservices() {
            return $this->db()->cache(300)->val("
                SELECT 
                    COUNT(*) 
                FROM {$this->TABLE} AS s
                    LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id  
                    LEFT JOIN {$this->TABLE_DEBT} AS od ON od.user_id = s.user_id   
                    LEFT JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = s.user_id   
                WHERE
                    s.active = TRUE 
                    AND s.deleted = FALSE 
                    AND sb.src_id IS NULL
                    AND (od.id IS NULL OR od.date::DATE >= NOW()::DATE) 
                    AND u.is_banned = B'0'
                    AND u.tabs & b'00000001' = b'00000001' 
                    AND u.self_deleted = FALSE
            ");
        }
        
        /**
         * Кол-во юзеров с типовыми услугами
         * 
         * @return type
         */
        public function countUsers() {
            return $this->db()->cache(300)->val("
                SELECT 
                    COUNT(DISTINCT s.user_id) 
                FROM {$this->TABLE} AS s 
                    LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
                    LEFT JOIN {$this->TABLE_DEBT} AS od ON od.user_id = s.user_id   
                    LEFT JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = s.user_id   
                WHERE
                    s.active = TRUE 
                    AND s.deleted = FALSE 
                    AND sb.src_id IS NULL
                    AND (od.id IS NULL OR od.date::DATE >= NOW()::DATE) 
                    AND u.is_banned = B'0'
                    AND u.tabs & b'00000001' = b'00000001' 
                    AND u.self_deleted = FALSE
            ");
        }

	/**
	 * @return TServiceModel
	 */
	public static function model()
	{
		$class = get_called_class();
		return new $class;
	}

}