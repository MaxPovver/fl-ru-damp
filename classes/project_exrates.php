<?
/**
 * Класс работы с курсами обмена валют на сайте при работе с проектами
 * 
 */
class project_exrates
{
    /**
     * код валюты FM
     *
     * @var integer
     */
    const FM  = 1;

    /**
     * код валюты USD
     *
     * @var integer
     */
    const USD = 2;

    /**
     * код валюты EUR
     *
     * @var integer
     */
    const EUR = 3;

    /**
     * код валюты RUR
     *
     * @var integer
     */
    const RUR = 4;

    /**
     * идентификатор курса между двумя валютами, см. таблицу project_exrates.     *
     *
     * @var integer
     */
    var $id;

    /**
     * значение курса
     *
     * @var float
     */
    var $val;
    
    /**
     * имя primary key в таблице project_exrates
     *
     * @var string
     */
    var $pr_key="id";

    /**
     * Получает курсы валют ЦБ и обновляет их у нас (используется в crone файл hourly.php)
     */
    function updateCBRates() {
        // Удаляем старые курсы из кеша
        $memBuff = new memBuff();
        $memBuff->delete('getCBRates');

        $CBRates = getCBRates();

        if($CBRates) {
            $pex = self::GetAll(false);

            $exs = array();
            $exs[12] = $pex[12];
            $exs[13] = $pex[13];
            $exs[14] = $pex[14];
            $exs[24] = str_replace(",",".",$CBRates['USD']['Value']);
            $exs[34] = str_replace(",",".",$CBRates['EUR']['Value']);

            $error = 0;
            foreach($exs as $k=>$v) {
                if((float)$v<=0) {
                    $error = 1;
                    break;
                }
                $pex[$k] = $v;
            }
      
            if(!$error) {
                $ex_cnt = 4;
                for($i=1;$i<=$ex_cnt;$i++) {
                    @$pex[$i.'1'] = 1 / $pex['1'.$i];
                    $pex[$i.$i] = 1;
                }
                for($i=2;$i<=$ex_cnt;$i++) {
                    for($j=2;$j<=$ex_cnt;$j++) {
                        $pex[$j.$i] = $pex[$j.$j] / $pex[$i.$j];
                    }   
                }
                self::BatchUpdate($pex);
            }
        }
    }
    
    /**
     * Обновляет обменные курсы
     *
     * @param  array $arr массив с курсами валют
     * @return integer 1 - обновлено / 0 - ошибка, не обновлено
     */
    function BatchUpdate( $arr ) {
        foreach ( $arr as $ikey => $val ) {
            $vals[] = "INSERT INTO project_exrates (id, val) VALUES ('".$ikey."','".$val."')";
        }
        
        global $DB;
        $sql = "DELETE FROM project_exrates; ".implode('; ', $vals).";";
        
        if (  $DB->squery($sql) ) {
            return 1;
        }

        return 0;
    }

    /**
     * Получает обменные курсы
     *
     * @param  boolean $cache получить из базы или из кэша
     * @return array массив курсов валют
     */
    function GetAll( $cache = true ) {
        $sql = "SELECT * FROM project_exrates";
        
        if ( $cache ) {
            $memBuff = new memBuff();
            $ret = $memBuff->getSql( $error, $sql, 600 );
        } 
        else {
            global $DB;
            $res = $DB->squery( $sql );
            $ret = pg_fetch_all( $res );
        }
        
        if ( $ret ) {
            foreach( $ret as $ikey => $val ) {
                $out[$val['id']] = $val['val'];
            }
        }
        
        return $out;
    }
}
?>
