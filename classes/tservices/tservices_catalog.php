<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/sphinxapi.php");

/**
 * ћодель каталога типовых услуг
 */
class tservices_catalog extends atservices_model
{
	private $TABLE                  = 'tservices';
	private $TABLE_BLOCKED		= 'tservices_blocked';
	private $TABLE_CATEGORIES	= 'tservices_categories';
	private $TABLE_USERS		= 'users';
	private $TABLE_FREELANCER       = 'freelancer';
	private $TABLE_COUNTERS		= 'tservices_counters';
	private $TABLE_FILES		= 'file_tservices';
	private $TABLE_CITY		= 'city';
    private $TABLE_BINDS        = 'tservices_binds';

	public $category_id;
	public $keywords = array();
	public $price_ranges = array();
	public $country_id;
	public $city_id;
    public $price_max;
    public $order;
	public $limit;
	public $page;
	public $offset;
    public $user_id;

    private $_ttl;

	/**
	 * ID диапазона, охватывающего все цены
	 */
	const ANY_PRICE_RANGE = 1;

	public static function getPriceRanges() {
		return array(
			self::ANY_PRICE_RANGE => array(
				'title' => 'люба€',
				'max' => null,
				'min' => null,
			),
			2 => array(
				'title' => 'дешевле 1 000 р.',
				'max' => 1000,
				'min' => null,
			),
			3 => array(
				'title' => '1 000 Ч 3 000 р.',
				'max' => 3000,
				'min' => 1000,
			),
			4 => array(
				'title' => '3 000 Ч 4 500 р.',
				'max' => 4500,
				'min' => 3000,
			),
			5 => array(
				'title' => '4 500 Ч 6 000 р.',
				'max' => 6000,
				'min' => 4500,
			),
			6 => array(
				'value' => 6,
				'title' => 'дороже 6 000 р.',
				'max' => null,
				'min' => 6000,
			),
		);
	}

	/**
	 * ”становить параметры пагинации
	 *
	 * @param int $limit
	 * @param int $page
	 * @return \Tservices_Module
	 */
	public function setPage($limit, $page = 1, $count_bind = 0, $count_bind_cur_page = 0)
	{
		$page = ($page > 0) ? $page : 1;
		$this->page = +$page - floor($count_bind / $limit);
		$this->limit = +$limit;
        
        //≈сли тизер открыт, то последнюю услугу с первой страницы дублируем на следующей
        //соответственно, будет сдвиг всего каталога
        $repeat_hidden = get_uid(false) && !is_emp() && !isset($_COOKIE['hide_tservices_teaser']) && $page > 1 
                ? 1 
                : 0;
        
		$this->offset = ($page - 1) * $limit - ($count_bind - $count_bind_cur_page) - $repeat_hidden;
        
		return $this;
	}

	public function cache($ttl)
	{
		$this->_ttl = $ttl;
		return $this;
	}

	/**
	 * ¬озвращает список публичных типовых услуг по заданным услови€м и пагинацией
	 * 
	 * @return array
	 */
	public function getList($excluded_ids = array())
	{
		$criteria = array(
			$this->category_id,
			$this->city_id,
			$this->country_id,
			$this->keywords,
			$this->limit,
			$this->offset,
			$this->price_ranges,
            $this->price_max,
            $this->order,
            $excluded_ids,
            $this->user_id
		);

		$membuf = new memBuff();
		$memkey = __METHOD__.'#' . md5(serialize($criteria));

		if (false!==($result = $membuf->get($memkey)) && is_release())
		{
			return $result;
		}
                
        $sort = $this->getSort();

		# @see http://sphinxsearch.com/forum/view.html?id=11538 about city = x or country = y
		$sphinxClient = new SphinxClient;
                $sphinxClient->SetServer(SEARCHHOST, SEARCHPORT);
                $sphinxClient->SetLimits($this->offset, $this->limit, 20000);
                $sphinxClient->SetSortMode(SPH_SORT_EXTENDED, $sort);
                $sphinxClient->SetFieldWeights(array('title' => 2, 'extra_title' => 1));
                //$sphinxClient->SetRankingMode(SPH_RANK_PROXIMITY_BM25);
                
		$selectExpression = '*'; // все колонки
                
        
        if ($this->user_id) {
			$selectExpression .= ", IF(user_id = {$this->user_id}, 1, 0) as match_user";
			$sphinxClient->setFilter('match_user', array(1));            
        }
        
        
		if ($this->category_id)
		{
			$selectExpression .= ", IF(category_id = {$this->category_id} or category_parent_id = {$this->category_id}, 1, 0) as match_category";
			$sphinxClient->setFilter('match_category', array(1));
		}
                
		if ($this->country_id)
		{
			$selectExpression .= ", IF(user_country_id = {$this->country_id} or country_id = {$this->country_id}, 1, 0) as match_country";
			$sphinxClient->setFilter('match_country', array(1));
		}
                
		if ($this->city_id)
		{
			$selectExpression .= ", IF(user_city_id = {$this->city_id} or city_id = {$this->city_id}, 1, 0) as match_city";
			$sphinxClient->setFilter('match_city', array(1));
		}
                
		if (count($this->price_ranges)) {
			$match_price_exprs = array();
			foreach($this->getPriceRanges() as $i => $price_range) {
				if (!isset($this->price_ranges[$i]))
				{
					continue;
				}
				$match_price_exprs[] = "price_{$i} = 1";
			}
			$match_price_exprs = implode(' or ', $match_price_exprs);
			$selectExpression .= ", IF({$match_price_exprs}, 1, 0) as match_price";
			$sphinxClient->setFilter('match_price', array(1));
		}
                
                if($this->price_max > 0)
                {
			$selectExpression .= ", IF(price <= {$this->price_max}, 1, 0) as match_price_max";
			$sphinxClient->setFilter('match_price_max', array(1));                    
                }
                
		$searchString = '';
		if (!empty($this->keywords))
		{
			$keywords = implode(' ', array_filter(preg_split('/\s*,\s*/', $this->keywords)));
			$searchString = trim($keywords);
                        //$searchString = $this->GetSphinxKeyword($searchString);
			$sphinxClient->SetMatchMode(SPH_MATCH_ANY); //SPH_MATCH_EXTENDED2);
		}

        if (count($excluded_ids)) {
			$sphinxClient->setFilter('tservice_id', $excluded_ids, true);
        }
        
		$sphinxClient->SetSelect($selectExpression);
		$queryResult = $sphinxClient->query($searchString, "tservices;delta_tservices");

		//echo '<pre>error: ', $sphinxClient->GetLastError(), '</pre>';
		//echo '<pre>warn : ', $sphinxClient->GetLastWarning(), '</pre>';

		$list = array();
                $total = 0;
                
		if (isset($queryResult['matches']))
		{
			foreach($queryResult['matches'] as $id => $row)
			{
				$row['attrs']['id'] = $id;
				$list[] = $row['attrs'];
			}
                        
                        $total = ($queryResult['total_found'] < $queryResult['total'])?$queryResult['total_found']:$queryResult['total'];
		}
                
		$result = array(
			'list' => $list,
			'total' => $total
		);

		if ($this->_ttl)
		{
			$membuf->set($memkey, $result, $this->_ttl);
		}

		return $result;
	}
     
    
    
    private function getSort() 
    {
        require_once(ABS_PATH . '/tu/widgets/TServiceFilter.php');
        
        $sort = '';
        switch ($this->order) {
            case TServiceFilter::ORDER_PRICE_ASC:
                $sort = 'price ASC, @weight DESC, tax_payed_last DESC, total_feedbacks DESC, created_timestamp DESC';
                break;

            case TServiceFilter::ORDER_PRICE_DESC:
                $sort = 'price DESC, @weight DESC, tax_payed_last DESC, total_feedbacks DESC, created_timestamp DESC';
                break;

            case TServiceFilter::ORDER_FEEDBACK:
                $sort = 'plus_feedbacks DESC, minus_feedbacks ASC, @weight DESC, tax_payed_last DESC, created_timestamp DESC';
                break;

            case TServiceFilter::ORDER_SOLD:
                $sort = 'count_sold DESC, total_feedbacks DESC, @weight DESC, tax_payed_last DESC, created_timestamp DESC';
                break;

            case TServiceFilter::ORDER_TAX_SUM:
                $sort = 'payed_tax DESC, @weight DESC, tax_payed_last DESC, total_feedbacks DESC, created_timestamp DESC';
                break;

            case TServiceFilter::ORDER_RELEVANT:
            default:
                $sort = '@weight DESC, tax_payed_last DESC, payed_tax DESC, total_feedbacks DESC, created_timestamp DESC';
                break;
        }
        return $sort;
    }
        
        private function GetSphinxKeyword($sQuery) 
        {
            $cnt = count(preg_split('/[\s,-]+/', $sQuery, 5));
            //’от€бы минимум 2 совпадени€ слов
            $sQuery = ($cnt > 1)?"\"{$sQuery}\"/2":$sQuery;
            return $sQuery;
            
            /*
            $aKeyword = array();
            $sSphinxKeyword = $sQuery;
            
            $aRequestString = preg_split('/[\s,-]+/', $sQuery, 5);
            if ($aRequestString) 
            {
                foreach ($aRequestString as $sValue) 
                {
                    if (strlen($sValue) >= 3) {
                        $aKeyword[] .= "(" . $sValue . " | *" . $sValue . "*)";
                    }
                }

                if(!empty($aKeyword)) 
                {
                    $sSphinxKeyword = implode(" | ", $aKeyword);
                }
            }
            
            return $sSphinxKeyword;
             */
        }
        
    /**
     * ¬озвращает список закрепленных услуг
     * @return type
     */
    public function getBindedList($kind)
    {
        $sql = $this->db()->parse("
        SELECT 
            DISTINCT ON (tb.date_start, s.id) 
            s.id AS id, 
            s.title AS title, 
            s.price AS price,
            s.active AS active,
            s.videos AS videos,
            s.user_id as user_id,
            COALESCE((sc.sbr_null + sc.sbr_plus + sc.order_plus),0) AS plus_feedbacks,
            COALESCE(s.total_feedbacks, 0) as total_feedbacks,
            f.fname AS file,
            COALESCE(sb.src_id::boolean, FALSE) AS is_blocked,
            sb.reason,
            1 as is_binded,
            tb.date_stop as date_stop
        FROM {$this->TABLE} AS s 
        INNER JOIN {$this->TABLE_BINDS} AS tb ON tb.tservice_id = s.id 
        LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
        LEFT JOIN {$this->TABLE_FILES} AS f ON f.src_id = s.id AND f.small = 4
        LEFT JOIN {$this->TABLE_COUNTERS} sc ON sc.service_id = s.id
        WHERE s.deleted = FALSE AND s.active = TRUE AND sb.src_id IS NULL AND tb.kind = ?i "
        .($this->category_id ? ' AND tb.prof_id = ?i' : '')."
        AND tb.date_stop > now()
        ORDER BY tb.date_start DESC, s.id DESC, f.preview DESC, f.id 
        ", (int)$kind, $this->category_id);        

        $sql = $this->_limit($sql);
        $rows = $this->db()->rows($sql);
        return $rows;
    }
    
    /**
     * ¬озвращает список »ƒ закрепленных услуг без учета постраничности
     * @return type
     */
    public function getBindedIds($kind)
    {
        $sql = $this->db()->parse("
        SELECT 
            s.id
        FROM {$this->TABLE} AS s 
        INNER JOIN {$this->TABLE_BINDS} AS tb ON tb.tservice_id = s.id 
        LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
        WHERE s.deleted = FALSE AND s.active = TRUE AND sb.src_id IS NULL AND tb.kind = ?i "
        .($this->category_id ? ' AND tb.prof_id = ?i' : '')."
        AND tb.date_stop > now()
        ", (int)$kind, $this->category_id);        

        $rows = $this->db()->col($sql);
        return $rows;
    }
    
    /**
     * 
     * @param type $kind
     */
    public function getBindedCount($kind)
    {
        /**
         * @todo ƒобавить кеширование и сброс кеша при продлении
         */
        
        $sql = $this->db()->parse("
        SELECT 
            COUNT(s.id)
        FROM {$this->TABLE} AS s 
        INNER JOIN {$this->TABLE_BINDS} AS tb ON tb.tservice_id = s.id 
        LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
        WHERE s.deleted = FALSE AND s.active = TRUE AND sb.src_id IS NULL 
        AND tb.kind = ?i AND tb.prof_id = ?i
        AND tb.date_stop > now()
        ", (int)$kind, (int)$this->category_id);        

        return (int)($this->db()->val($sql));
    }

}