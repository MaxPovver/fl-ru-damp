<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/atservices_model.php");

/**
 * Категории ТУ
 *
 */
class tservices_categories extends atservices_model
{
    
    private $TABLE = 'tservices_categories';
    
    
    protected $_cache = array();


    
    /**
     * Получить ID категории ТУ по связной из каталога фрилансеров
     * 
     * @param type $prof_group_id
     * @param type $prof_id
     * @return boolean
     */
    public function getCategoryByFreelancersCatalog($prof_group_id, $prof_id)
    {
        if ($prof_id > 0) {
            $where = $this->db()->parse('pid = ?i', $prof_id);
        }
        
        if ($prof_group_id > 0) {
            $where = $this->db()->parse('gid = ?i', $prof_group_id);
        }
        
        if (isset($where)) {
            return $this->db()->cache(3600)->row("SELECT id, title, link FROM {$this->TABLE} WHERE {$where} LIMIT 1");
        }
        
        return false;
    }

    



    /**
    * Вернуть ID родителя категории
    * 
    * @param type $category_id
    * @return boolean / int
    */
   public function getCategoryParentId($category_id) 
   {
        $result = $this->db()->row("
            SELECT parent_id 
            FROM {$this->TABLE} 
            WHERE id = ?i AND active > 0", 
            $category_id);
            
        if (isset($result['parent_id']) && is_numeric($result['parent_id'])) 
        {
            return (int)$result['parent_id'];
        }

        return false;
    }
    
    
    
    
    /**
     * Получаем заголовок категории и его родителя если есть.
     * 
     * @param int $category_id
     * @return row
     */
    public function getTitleAndSubtitle($category_id)
    {
        return $this->db()->row("
                SELECT 
                    c1.title AS spec_title,
                    c2.title AS group_title
                FROM {$this->TABLE} AS c1 
                LEFT JOIN {$this->TABLE} AS c2 ON c2.id = c1.parent_id 
                WHERE 
                    c1.active > 0 AND
                    (c2.active > 0 OR c2.id IS NULL) AND 
                    c1.id = ?i 
                LIMIT 1
            ", $category_id);
    }

    
    
    
    
    /**
     * @deprecated не использовать дубликат getCategoriesByParent(0) 
     * 
     * Список только родительских категорий
     * кешируется на 15 минут
     * 
     * @return array
     */
    public function getParents()
    {
        return $this->db()->cache(900)->rows("
            SELECT 
                c.id,
                c.title,
                c.link
            FROM {$this->TABLE} AS c 
            WHERE 
                c.active = 1 AND 
                c.parent_id = 0 
            ORDER BY c.ucnt DESC
        ");
    }

    



    /**
	 * Возвращает список всех категорий
	 *
	 * @param bool $collectParentChild false = вернуть плоский список категорий, true = вернуть дерево категорий с учётом отношений parent/child
	 * @param bool $nocache false = прямой запрос в БД, true = кэширование результата
	 * @return array
	 */
	public function getAllCategories($collectParentChild = false, $nocache = false)
	{
		$sql = <<<SQL
select
  ts_cat.id category_id,
  ts_cat.title category_title,
  ts_cat.link category_link,
  0 category_count,
  ts_parent_cat.id category_parent_id,
  ts_parent_cat.title category_parent_title,
  ts_parent_cat.link category_parent_link
from tservices_categories ts_cat
left join tservices_categories ts_parent_cat on ts_parent_cat.id = ts_cat.parent_id
where ts_cat.active > 0
      and (ts_parent_cat.id is null or ts_parent_cat.active > 0)
order by ts_parent_cat.n_order asc nulls first, ts_cat.n_order asc
SQL;

		$result = ( $nocache ? $this->db()->rows($sql) : $this->db()->cache(60)->rows($sql) );

		$result = $result ? $result : array();
		if (!$collectParentChild) {
			return $result;
		}

		$tree = array();
		foreach($result as $row) {
			$parent = array(
				'category_id' => $row['category_parent_id'],
				'category_title' => $row['category_parent_title'],
				'category_link' => $row['category_parent_link'],
				'category_count' => 0,
				'children' => array(),
			);
			$parentCategoryId = +$row['category_parent_id'];
			if (!isset($tree[$parentCategoryId])) {
				$tree[$parentCategoryId] = $parent;
			}
			$tree[$parentCategoryId]['children'][] = array(
				'category_id' => $row['category_id'],
				'category_title' => $row['category_title'],
				'category_link' => $row['category_link'],
				'category_count' => $row['category_count'],
			);
			$tree[$parentCategoryId]['category_count'] += $row['category_count'];
		}

		return $tree;
	}
    
    
    /**
     * Список вложенных подкатегорий
     * кешируется на 15 минут
     * 
     * @return array
     */
    public function getCategoriesByParent($category_id)
    {
        return $this->db()->cache(900)->rows("
            SELECT 
                c.id,
                c.title,
                c.link
            FROM {$this->TABLE} AS c 
            WHERE 
                c.active = 1 AND 
                c.parent_id = ?i 
            ORDER BY c.ucnt DESC
        ", $category_id);
    }

    
    
	/**
	 * Получаем информацию о категории по её ID
	 *
	 * @param int $category_id
	 * @return row
	 */
	public function getCategoryById($category_id)
	{
        if(isset($this->_cache[$category_id])) {
            return $this->_cache[$category_id];
        }
        
        $this->_cache[$category_id] = $this->db()->row(<<<SQL
SELECT c.*
FROM {$this->TABLE} AS c
WHERE c.id = ?i
LIMIT 1
SQL
, $category_id);

        return $this->_cache[$category_id];
	}

    
    
	/**
	 * Получаем информацию о категории по её символьному алиасу (link)
	 *
	 * @param string $category_link
	 * @return row
	 */
	public function getCategoryByLink($category_link)
	{
		return $this->db()->row(<<<SQL
SELECT c.*
FROM {$this->TABLE} AS c
WHERE c.link = ?
LIMIT 1
SQL
			, $category_link);
	}
        
        /**
        * Вернуть ID категории по группе
        * 
        * @param type $gid
        * @return boolean / int
        */
       public function getIdByGid($gid) 
       {
            $id = $this->db()->val("
                SELECT id 
                FROM {$this->TABLE} 
                WHERE gid = ?i AND active > 0", 
                $gid);

            return (int)$id;
        }
        
        /**
        * Вернуть ID категории по pid 
        * 
        * @param type $pid
        * @return boolean / int
        */
       public function getIdByPid($pid) 
       {
            $id = $this->db()->val("
                SELECT id 
                FROM {$this->TABLE} 
                WHERE pid = ?i AND active > 0", 
                $pid);

            return (int)$id;
        }
        
        
        /**
         * Пересчет количества пользователей ТУ в данной категории
         * 
         * @global type $DB
         * @return type
         */
        public static function ReCalcCategoriesCount() 
        {
            global $DB;
            return (int) $DB->mquery("SELECT recalc_tservices_categories_count()");
        }
}

