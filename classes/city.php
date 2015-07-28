<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с городами в БД
 */
class city 
{
    static protected $_data_cache = array();

    /**
     * Берем ID города по имени в транслит
     *
     * @param    string    $translit    Название города в транслит
     * @param    integer   $country_id  ID страны
     * @return   array                  Информация о городе
     */
    function getCityIDByTranslit( $translit, & $country_id=0 ) {
        global $DB;
        if($country_id == 0) {
            $country_id = $DB->val( 'SELECT country_id FROM city WHERE translit_city_name = ?', strtolower($translit));
            $id = $DB->val( 'SELECT * FROM city WHERE translit_city_name = ? AND country_id = ?i', strtolower($translit), $country_id);
        } else {
            $id = $DB->val( 'SELECT * FROM city WHERE translit_city_name = ? AND country_id = ?i', strtolower($translit), $country_id );
        }
        return $id;
    }

    /**
     * Получить всю информацию о городе по ID
     *
     * @param    integer    $id    ID города
     * @return   array             информация о городе
     */
    function getCity($id) {
        global $DB;
        $sql = "SELECT * FROM city WHERE id = ?i";
        $city = $DB->row($sql, intval($id));
        return $city;
    }

    /**
     * Взять название города по его ИД
     * 
     * @param  integer $id ИД города
     * @return string Название города
     */
    function GetCityName( $id ) {
        global $DB;
        return $DB->cache(300)->val('SELECT city_name FROM city WHERE id = ?i', $id);
    }
    
    /**
     * Взять название страны по идентификатору города 
     * @param  integer $id ИД города
     * @return string Название страны
     */
    function GetCountryName($cityId) {
        global $DB;
        $name = $DB->val( 'SELECT country_name FROM city LEFT JOIN country ON country.id = city.country_id WHERE city.id = ?i', $cityId );
        return ($name);
    }
    
    /**
     * Берем ИД города по названию.
     * 
     * @param  string $name название города
     * @return int ИД города
     */
    function getCityId( $name ) {
        return $GLOBALS['DB']->val( 'SELECT id FROM city WHERE city_name = ?', $name );
    }
    
    /**
     * Берем ИД города по названию и стране.
     * 
     * @param  string $name название города
     * @param  int $country_id ИД страны
     * @return int ИД страны
     */
    function getCityIdByCountry($name, $country_id) {
        return $GLOBALS['DB']->val( 'SELECT id FROM city WHERE city_name = ? AND country_id = ?i', $name, $country_id);
    }
    
    /**
     * Взять все города по определенной стране
     * 
     * @param  integer $country ИД Страны
     * @return array Массив городов
     */
    function GetCities( $country ) {
        if (!$country) return 0;
        
        global $DB;
        $sql = 'SELECT id, city_name FROM city WHERE country_id = ?i ORDER BY id IN(1,2) DESC, TRIM(city_name)';
        $ret = $DB->rows( $sql, $country );
        $out = array();
        
        if ( $ret ) {
            foreach ( $ret as $value ) {
                $out[$value['id']] = $value['city_name'];
            }
        }
        
        return ($out);
    }
    
    /**
     * Количество всех городов в БД
     * 
     * @param  string $limit Лимит показа - '10 OFFSET 0'
     * @return array
     */
    function CountAll( $limit = '' ) {
        $sql_limit = ( $limit ) ? ' LIMIT ' . (int)$limit : '';
        
        global $DB;
        $sql = 'SELECT city_name, COUNT(*) as cnt FROM users 
                LEFT JOIN city ON users.city = city.id 
                GROUP BY city_name ORDER BY cnt DESC' . $sql_limit;
        $ret = $DB->cache(1200)->rows( $sql );
        
        return ($ret);
    }
    /**
     * Выборка всех профессий и групп к которым они относятся.
     * @return array $rows 
     * */
    function GetCountriesAndCities(){
        $DB = new DB('master');
        $cmd = "SELECT country.id AS country_id, country.country_name, city.id, city.city_name AS name
                FROM country 
                LEFT JOIN city
                 ON city.country_id = country.id					
				ORDER BY country.pos, city.id
	            ";
        $rows = $DB->cache(1200)->rows($cmd);
        return $rows;
     }
     

     /**
      * Все данные по имени города
      * 
      * @global type $DB
      * @param type $name
      * @return type
      */
     public function getByName($name)
     {
        global $DB;
         
        if (isset(self::$_data_cache[$name])) {
            return self::$_data_cache[$name];
        }
         
        $ret = $DB->cache(1200)->row('SELECT * FROM city WHERE city_name = ?', $name);
        
        if ($ret) {
            self::$_data_cache[$name] = $ret;
        }
        
        return $ret; 
     }
}