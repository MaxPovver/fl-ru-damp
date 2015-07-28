<?
/**
 * Класс для работы со странами
 *
 */
class country
{
    
    const ISO_RUSSIA = 643;
    
    //Основные страны и их ISO коды 
    //для быстрого доступа
    protected $iso_country_list = array(
        'Россия' => self::ISO_RUSSIA,
        'Украина' => 804,
        'Азербайджан' => 31,
        'Казахстан' => 398,
        'Кыргызстан' => 417,
        'Латвия' => 428,
        'Литва' => 440,
        'Молдова' => 498,
        'Монголия' => 496,
        'Таджикистан' => 762,
        'Туркменистан' => 795,
        'Узбекистан' => 860,
        'Эстония' => 233,
        'Абхазия' => 895,
        'Южная Осетия' => 896,
        'Грузия' => 268,
        'Аргентина' => 32,
        'Армения' => 51,
        'Беларусь' => 112,
        'Израиль' => 376
    );
    
    
    static protected $_dataId_cache = array();




    /**
     * Берем ID страны по имени в транслит
     *
     * @param    string    $translit    Название страны в транслит
     * @return   array                  ID страны
     */
    function getCountryIDByTranslit( $translit ) {
        global $DB;
        $id = $DB->val( 'SELECT id FROM country WHERE translit_country_name = ?', strtolower($translit) );

        return $id;
    }

    /**
     * Берем название страны по ИД
     * 
     * @param  integer $id ИД страны
     * @return string название страны
     */
    function GetCountryName( $id ) {
        global $DB;
        return $DB->cache(300)->val('SELECT country_name FROM country WHERE id = ?i', $id);
    }
    
    /**
     * Берем ИД страны по названию.
     * 
     * @param  string $name название страны
     * @return int ИД страны
     */
    function getCountryId($name) 
    {
        global $DB;
        
        if (isset(self::$_dataId_cache[$name])) {
            return self::$_dataId_cache[$name];
        }        
        
        $ret = $DB->val('SELECT id FROM country WHERE country_name = ?', $name);
        
        if ($ret) {
            self::$_dataId_cache[$name] = $ret;
        }
        
        return $ret;
    }
    
    /**
     * Страну по имени
     * 
     * @param type $name
     * @return type
     */
    function getCountryByName($name)
    {
        return $GLOBALS['DB']->cache(300)->row('SELECT * FROM country WHERE country_name = ?', $name);
    }

    
    /**
     * По имени страны вернуть ее ISO код
     * 
     * @param type $name
     * @return type
     */
    function getCountryISO($name)
    {
        if (isset($this->iso_country_list[$name])) {
            return $this->iso_country_list[$name];
        }
        
        return $GLOBALS['DB']->cache(300)->val('SELECT iso FROM country WHERE country_name = ?', $name);
    }


    
    /**
     * Берем Ид страны по ИД города
     * 
     * @param integer $id  ИД города
     * @return integer 
     */
    public function getCountryByCityId($id) {
        return $GLOBALS['DB']->val( 'SELECT country_id FROM city WHERE id = ?i', $id );
    }
    
    /**
     * Взять все страны из таблицы
     * 
     * @param  boolean $full Берем из таблицы только названия страны или все поля (false - только название, true - все поля)
     * @return array Информация выборки
     */
    function GetCountries( $full = false ) {
        global $DB;
        $ret = $DB->rows( "SELECT * FROM country WHERE id <> '0' ORDER BY pos" );
        $out = array();
        
        if( !$full ) {
            foreach ( $ret as $value ) {
                $out[$value['id']] = $value['country_name'];
            }
        }
        else { 
            foreach ( $ret as $value ) {
                $out[$value['id']] = $value;
            }
        }
        
        return ($out);
    }
    
    /**
     * Количество юзеров по странам
     * 
     * @param  string $limit ЛИМИТ выдачи
     * @return array Данные выборки
     */
    function CountAll( $limit = '' ) {
        $sql_limit = ( $limit ) ? ' LIMIT ' . (int)$limit : '';
        
        global $DB;
        $sql = 'SELECT country_name, COUNT(*) as cnt, country as country_id 
                FROM users LEFT JOIN country ON users.country = country.id GROUP BY country_name, country 
                ORDER BY cnt DESC' . $sql_limit;
        $ret = $DB->cache(1200)->rows( $sql );
        
        return ($ret);
    }
    
    /**
     * Возвращает страны упорядоченные по количеству зарегистрированных пользователей из этих стран 
     * */
    function GetCountriesByCountUser() {
    	$cmd = "SELECT c.id AS id , count(uid) as nn, c.country_name AS name
							FROM country AS c
							LEFT JOIN users as u
							 ON c.id = u.country 							
							GROUP BY c.id, c.country_name  
							ORDER BY nn desc";
        $DB = new DB('master');
        $rows = $DB->cache(1200)->rows($cmd);
        return $rows;
    }
    
    function GetCountryIsoCode($country_id = 0) {
        return $GLOBALS['DB']->val( 'SELECT iso_code3 FROM country WHERE id = ?', $country_id);
    }
    
    
    /**
     * Получить название страны и города
     * 
     * @global type $DB
     * @param type $country_id
     * @param type $city_id
     * @return type
     */
    public function getCountryAndCityNames($country_id, $city_id = null)
    {
        global $DB;
        return $DB->row("
            SELECT
                co.country_name,
                ci.city_name,
                co.country_name || (CASE WHEN ci.city_name IS NOT NULL THEN ': ' || ci.city_name ELSE ': Все города' END) AS name
            FROM country AS co
            LEFT JOIN city AS ci ON ci.country_id = co.id ".(($city_id > 0)?"AND ci.id = {$city_id}":"AND ci.id IS NULL")."
            WHERE co.id > 0 AND co.id = ?i 
        ", $country_id);
    }
    
    
}