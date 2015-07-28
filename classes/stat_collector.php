<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");


/**
 * Класс для подсчета и обработки посещений страниц пользователей.
 */
class stat_collector
{
  /**
   * Максимальное количество дней, подробную статистику о которых необходимо сохранить.
   * Используется при очистке stat_daily
   */
  const MAX_CYCLICS_OFFSET = 30;

    
  /**
   * Тип сообщения в логе. Ошибка.
   */
  const LT_ERROR     = 1;

  /**
   * Тип сообщения в логе. Предупреждение.
   */
  const LT_WARNING   = 2;

  /**
   * Тип сообщения в логе. Сообщение.
   */
  const LT_NOTICE    = 3;

  /**
   * Тип сообщения в логе. Исходный код.
   */
  const LT_CODE      = 4;

  /**
   * Тип сообщения в логе. Начало группы операции.
   */
  const LT_HEADER    = 5;
  /**
   * Тип сообщения в логе. Начало какой-либо операции.
   */
  const LT_SUBHEADER = 6;

  /**
   * Тип сообщения в логе. Результат операции.
   */
  const LT_RESULT    = 7;

  /**
   * Тип сообщения в логе. Сообщения сервера.
   */
  const LT_SERVER_MESSAGE = 8;

  
  /**
   * Раздел сайта. Неизвестный раздел.
   */
  const REFID_UNKNOWN      = 0;

  /**
   * Раздел сайта. Блоги.
   */
  const REFID_BLOGS        = 1;

  /**
   * Раздел сайта. Каталоги.
   */
  const REFID_CATALOG      = 2;

  /**
   * Раздел сайта. Сообщества.
   */
  const REFID_COMMUNE      = 3;

  /**
   * Раздел сайта. Платные места.
   */
  const REFID_PAIDSEATINGS = 4;

  /**
   * Раздел сайта. Страница пользователей.
   */
  const REFID_USERS        = 5;
  /**
   * Раздел сайта. Платные места наверху страницы.
   */
  const REFID_PAYPLACE     = 6;
  /**
   * Лента предложений фрилансеров
   *
   */
  const REFID_FRL_OFFERS = 7; 
  
  /**
   * Поиск 
   *
   */
  const REFID_SEARCH = 8;
    
  const LOGTBL_MEM_KEY     = 'stat_collector.LOGTBL_MEM_KEY';
  
  
  /**
   * Корневой каталог для файлов необходимых данному классу
   * @var  string
   */
  private $root    = '';

  /**
   * Каталог для сохранения логов и дампов. 
   * @var  string
   */
  private $log_dir = '';

  /**
   * Каталог для хранения временных данных. 
   * @var  string
   */
  private $tmp_dir = '';

  /**
   * Каталог для дампов из таблицы stat_log
   * @var  string
   */
  private $arc_dir = '';

  /**
   * Лог файл
   * @var  string
   */
  private $run_log = '';

  /**
   * Если TRUE, то выводит лог на экран, вместо $this->run_log
   * @var  boolean
   */
  private $output  = FALSE;

  /**
   * Ресурс соединения с БД
   * @var  resource
   */
  private $connect = NULL;

  /**
   * буффер для хранения лога
   * @var  string
   */
  private $log_str    = '';

  /**
   * Коннектор к БД статистики.
   * @var object
   */
  private $_sDB;

  /**
   * Конструктор. Определяет пути для всех необходимых каталогов.
   */
  function __construct($output = FALSE)
  {
    $this->_sDB = new DB('stat');
    $this->output  = $output;
    $this->root    = preg_replace('/\/$/','',$_SERVER['DOCUMENT_ROOT']).'/stat_collector';
    $this->log_dir = $this->root.'/logs';
    $this->tmp_dir = $this->log_dir.'/tmp';
    $this->arc_dir = $this->log_dir.'/arc';
    $this->run_log = $this->log_dir.'/run.log';
  }

  /**
   * Возвращает текущее время базы данных в unixtime
   * @return   float   текущее время в unixtime
   */
  private function get_time()
  {
    if ( $res = $this->_sDB->val('SELECT EXTRACT(EPOCH FROM now())') )
        return $res;
    
    return time();
  }

  /**
   * Добавляет данные о событии в лог
   * @param   string    $msg        описание события
   * @param   integer   $log_type   тип события
   * @return  string                возвращает переданное фукнции сообщение если произошло LT_ERROR или LT_SERVER_MESSAGE, иначе NULL
   */
  private function log($msg, $log_type = self::LT_NOTICE)
  {
    
    $e = NULL;
    $m = date('Y-m-d H:i:s').' ';
    switch($log_type)
    {
      case self::LT_ERROR :
        $m .= 'ОШИБКА! '.$msg."\n";
        $e = $msg;
        break;
      case self::LT_WARNING :
        $m .= 'ВНИМАНИЕ! '.$msg."\n";
        break;
      case self::LT_NOTICE :
        $m .= $msg."\n";
        break;
      case self::LT_CODE :
        $m .= $msg."\n";
        break;
      case self::LT_HEADER :
        $m = "\n".$m.strtoupper($msg)."\n\n";
        break;
      case self::LT_SUBHEADER :
        $m .= $msg."\n";
        break;
      case self::LT_RESULT :
        $m .= $msg."\n";
        break;
      case self::LT_SERVER_MESSAGE :
        if($msg) $m .= 'ПОЛУЧЕНЫ СООБЩЕНИЯ ОТ СЕРВЕРА: '.$msg."\n";
        else
          return $e;
        break;
      default :
        $m .= $msg."\n";
        break;
    }

    $this->log_str .= $m;
    return $e;
  }


    /**
     * Устанавливает таблицу для записи посещений.
     * @see stat_collector::Step1()
     *
     * @param string $table       имя таблицы (stat_log|stat_log_t)
     * @param boolean $only_mem   true: сохранить только в мемкэш, иначе еще и в БД.
     * @return boolean   успешно?
     */
     private function _setLogTable($table, $only_mem = false) {
        $MEM = new memBuff();
        if ( $only_mem || $this->_sDB->update('stat_variables', array('value'=>$table), 'name = ?', 'log_table') ) {
            return $MEM->set(stat_collector::LOGTBL_MEM_KEY, $table, 3000);
        }
        return false;
    }

    /**
     * Получает текущую таблицу для записи посещений.
     * @see stat_collector::LogStat()
     * @return string   stat_log|stat_log_t
     */
    function getLogTable() {
        $MEM = new memBuff();
        if ( !($table = $MEM->get(stat_collector::LOGTBL_MEM_KEY)) ) {
            $table = $this->_sDB->val('SELECT value FROM stat_variables WHERE name = ?', 'log_table');
            $this->_setLogTable($table, true);
        }
        if ( !$table ) {
            $table = 'stat_log';
        }
        return $table;
    }

    /**
     * Проверяет отключена ли статистика в конфиге.
     * ONLYLOG устанавливается вместе с ['pg_db']['stat_tmp'] в случае аварии и т.п., когда
     * оставляем только сбор и пересчет, но результаты не выводим юзеру.
     * 
     * @return integer   0:не отключена, 1:отчключена совсем, 2:включена только для сбора.
     */ 
    function isDisabled() {
        if(defined('STAT_DISABLED') && STAT_DISABLED) {
            if(STAT_DISABLED == 'ONLYLOG' && isset($GLOBALS['pg_db']['stat_tmp'])) {
                $this->_sDB = new DB('stat_tmp');
                return 2;
            }
            return 1;
        }
        return 0;
    }

    /**
     * Сохраняет данные о посещении пользовательской страницы.
     * Дополнительно, подсчитывает общее кол-во переходов с платных мест за сегодня, если переход был сделан - 
     * с певрого места в разделе "все фрилансеры", с первого, второго и последнего места на главной странице.
     * @param   integer   $user_id    uid пользователя, страницу которого просматривают
     * @param   integer   $guest_id   uid пользователя, смотрящего страницу
     * @param   string    $guest_ip   IP смотрящего
     * @param   integer   $referer_id место, с которого перешел смотрящий (смотри костанты REFID_*)
     * @param   integer   $by_e       1 - если просматривает работодатель, 0 - если кто-то другой
     * @return  string                сообщение об ошибке или 0, если все прошло успешно
     */
    function LogStat($user_id, $guest_id, $guest_ip, $referer_id, $by_e, $stamp = false) {
        if($this->isDisabled() == 1) {
            return false;
        }
        $DB = $this->_sDB;
        
        if(self::checkStamp($stamp, true)) $referer_id = 0; //return false; // Проверяем пользователь не специально ли зашел на аккаунт с параметром
        if((int)$guest_id < 0)
            $guest_id = 0; // бывают баны лезут...
        $aSQLdata = compact( 'user_id', 'guest_id', 'guest_ip', 'referer_id', 'by_e' );
        $log_table = $this->getLogTable();
                
        if( !$DB->insert($log_table, $aSQLdata) )
            return $this->log("stat_collector::LogStat(). Ошибка записи в {$log_table}. " . $DB->error, self::LT_ERROR);

        return 0;
    }

    /**
     * Сохраняет данные для статистики посещений страницы пользователя по ключевым словам.
     *
     * @param int $user_id UID хозяина страницы
     * @param int $guest_id UID гостя или ноль, если гость неавторизован
     * @param string $guest_ip IP адрес гостя
     * @param bool $is_emp был ли гость работодателем
     * @param string $words строка ключевых слов, разделенных запятыми (если несколько)
     */
    function wordsStatLog( $user_id, $guest_id, $guest_ip, $is_emp, $words ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/kwords.php' );
        
        if ( $keywords = kwords::getKeys(stripslashes(urldecode($words))) ) {
            foreach ( $keywords as $k => $v ) {
                $aWords[] = $v['id']; 
            }
        }
        else {
            return false;
        }
        
        global $DB;
        $sQuery = "SELECT wid FROM portf_word WHERE uid=? AND wid IN (?l)";
        $aRows  = $DB->rows( $sQuery, $user_id, $aWords );
        
        if ( $DB->error ) {
        	return false;
        }
        
        if ( $aRows ) {
        	$aData = array();
        	
        	foreach ($aRows as $aOne) {
        		$aData[] = array(
                    'user_id'  => $user_id,
                    'word_id'  => $aOne['wid'],
                    'guest_id' => $guest_id,
                    'guest_ip' => $guest_ip,
                    'is_emp'   => $is_emp
        		);
        	}
        	
        	if( !$DB->insert('stat_word_log', $aData) ) {
        	    return false;
        	}
        }
        
        return true;
    }

  /**
   * Поочередный запуск всех шести методов для подсчета статистики (методы: Step1() - Step6())
   */
  function Run()
  {
    if($this->isDisabled() == 1) {
        $this->log('Сервер временно отключен.', self::LT_HEADER);
        return;
    }
    
    $this->log('Запуск stat_collector::Run()', self::LT_HEADER);

    ob_start();

    $this->Step1();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step2();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step3();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step4();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step5();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step6();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);

    ob_end_clean();

    $this->log('Завершение stat_collector::Run()', self::LT_HEADER);
  }


  /**
  * Считает почасовую статистику.
  * Берет данные из stat_log до последнего часа и группирует их в промежутках по одному часу, uid смотрящего и uid кого смотрели.
  * Т.е. любое кол-во просмотров кем-либо (guest_id) в течении часа кого-либо (user_id) считается одним просмотром.
  * Сгруппированные данные сохраняются в stat_hourly, а полный дамп просмотров кладется в файл в директории $this->arc
  * @return   string   сообщение об ошибке или 0, если все прошло успешно
  */
  function Step1()
  {
    // Отправляем данные из stat_log в stat_hourly. Ежечасно.
    // Архивный лог ($arc_name) не удаляем. Пусть будут дубли, не страшно. Потом при использовании, нужно будет их очистить и все.
    // Еще заводим лог ошибок.
    
    $DB = $this->_sDB;

    $this->log('stat_collector::Step1(). Экспорт данных из stat_log в stat_hourly.', self::LT_SUBHEADER);
    
    $time  = $this->get_time();
    $curH  = date('Y-m-d H', $time).':00:00';
    $curTH = strtotime($curH);

    // 1. Убиваем индекс "ix stat_log_t/_time" для облегчения последующей переправки данных в stat_log_t.
    // 2. Перенаправляем инсерт на stat_log_t.
    // 3. Переносим все данные в stat_log_t.
    // 4. TRUNCATE stat_log.
    // 5. Возвращаем инсерты на stat_log.
    // 6. Создаем индекс "ix stat_log_t/_time" снова, чтобы использовать его в селектах.
    // 7. Обрабатываем по одному часу stat_log_t.
    // 8. В конце делаем VACUUM FULL stat_log_t.
    // Таким образом вся обработка будет осуществляться на таблице stat_log_t. В начале каждого вызова данной функции,
    // stat_log_t будет содержать данные за неполный предыдущий час (_time >= $curH в предыдущем вызове), которые будут дополняться
    // новыми данными, накопившимися в stat_log .

    $this->log('Удаляем индекс "ix stat_log_t/_time".', self::LT_NOTICE);
    $sql = 'DROP INDEX IF EXISTS "ix stat_log_t/_time"';
    if ( !$this->_sDB->squery($sql) )
      $this->log('Ошибка. '.$this->_sDB->error, self::LT_WARNING);

    $this->log('Перенаправляем инсерты на stat_log_t.', self::LT_NOTICE);
    if( !$this->_setLogTable('stat_log_t') )
      return $this->log('Ошибка. ', self::LT_ERROR);

    $this->log('Ок.', self::LT_NOTICE);
    $this->log('Ждем 3 секунды для завершения старых транзакций...', self::LT_NOTICE);
    sleep(3);

    $truncateErr = NULL;
    $this->log('Переносим все данные из stat_log в stat_log_t, TRUNCATE ONLY stat_log.', self::LT_NOTICE);
    $sql = 'INSERT INTO stat_log_t SELECT * FROM ONLY stat_log';
    if ( !$DB->squery($sql) )
      $truncateErr = $this->log('Ошибка. '.$DB->error, self::LT_ERROR);
    else {
      $sql = 'TRUNCATE ONLY stat_log';
      if ( !$DB->squery($sql) )
        $truncateErr = $this->log('Ошибка. '.$DB->error, self::LT_ERROR);
    }

    $this->log('Возвращаем инсерты на stat_log.', self::LT_NOTICE);
    if( !$this->_setLogTable('stat_log') )
      return $this->log('Ошибка. ', self::LT_ERROR);

    if($truncateErr)
      return $truncateErr;

    $this->log('Восстанавливаем индекс "ix stat_log_t/_time".', self::LT_NOTICE);
    $sql = 'CREATE INDEX CONCURRENTLY "ix stat_log_t/_time" ON stat_log_t USING btree (_time)';
    if ( !$DB->squery($sql) )
      $this->log('Ошибка. '.$DB->error, self::LT_WARNING);


    $lT = $DB->val( "SELECT _time FROM stat_log_t ORDER BY _time LIMIT 1" );
    
    if ( $DB->error )
      return $this->log('Ошибка чтения stat_log_t. '.$DB->error, self::LT_ERROR);

    if( !$lT )
      return $this->log('Данных нет.', self::LT_NOTICE);

    $tH = strtotime(date('Y-m-d H', strtotime($lT)).':00:00');
    if($tH < $curTH)
      $tH += 3600;


    // Обрабатываем по одному часу.

    for($tH; $tH <= $curTH; $tH += 3600)
    {
      $H = date('Y-m-d H', $tH).':00:00';
      // в конец $arc_name добавлен суффикс H, т.к. в разное время из-за всяких сбоев может обрабатываться один и тот же час,
      // это приводило к затиранию предыдущего архива.
      $arc_name = $this->arc_dir.'/'.date('YmdH', $tH).'-'.date('H').'.log';

      $this->log("Обработка данных: FROM stat_log_t WHERE _time < '{$H}'.", self::LT_NOTICE);

      // (а) Проверяем, есть ли данные в stat_log_t, которые можно экспортировать.

      $sql = "SELECT 1 FROM stat_log_t WHERE _time < ? LIMIT 1";
      if ( !($res = $DB->query($sql, $H)) )
        return $this->log('Ошибка чтения stat_log_t. '.$DB->error, self::LT_ERROR);

      if(!pg_num_rows($res)) {
        $this->log("Данных нет.", self::LT_NOTICE);
        continue;
      }
    

      // (б) Берем данные из stat_log_t за все "полные часы" (все, кроме текущего часа) и выбрасываем их во временную таблицу и в хранилище логов.
      //     Данные выбрасываются в чистом виде, без преобразований и упаковываются (пока не упаковываются).
      //     В случае ошибки прекращаем операцию.

      if ( !$DB->start() )
        return $this->log('Не удалось открыть транзакцию. '.$DB->error, self::LT_ERROR);

      $sql = "SELECT * INTO TEMPORARY TABLE ___tmp_arc FROM stat_log_t WHERE _time < ?";
      if ( !($res = $DB->query($sql, $H)) ) {
        $e = $DB->error;
        $DB->rollback();
        return $this->log('Ошибка инсерта в ___tmp_arc. '.$e, self::LT_ERROR);
      }

      $all_data = pg_copy_to($DB->connect(), '___tmp_arc');
      if(!file_put_contents($arc_name, $all_data))
        $this->log("Лог {$arc_name} не записался.", self::LT_WARNING);

      unset($all_data);
      

      // Пригодится для отката в случае конфликта ключей.
      if ( !$DB->query('SAVEPOINT arc_created') ) {
        $e = $DB->error;
        $DB->rollback();
        return $this->log('Не удалось создать SAVEPOINT. '.$e, self::LT_ERROR);
      }

      // (в) Берем данные из stat_log_t за тот же период, что и в (б), но группируем их специальным образом, так, чтобы не было ничего "лишнего".
      // (г) Если ошибка возникла в связи с конфликтом ключей (тут конкретная такая проверка,
      //     то есть, теперь мы не гурьбой пытаемся запихнуть данные, а через временную таблицу пробуем загрузить только
      //     "не дубликаты"), то удаляем файл. Иначе удаляем файл и прекращаем операцию.
      //     Каждый час занимаемся только конкретными данными, только одним .tmp файлом. Если он не проходит в
      //     stat_hourly по причине сбоя какого-нибудь, то прекращаем операцию. Так будет гарантия непрерывности данных,
      //     которая необходима для наращивания счетчиков (итоговых) в stat_summary.

      $grp_data_sql = 
      "(
         SELECT user_id,
                guest_id,
                CASE WHEN guest_id = 0 THEN guest_ip ELSE '' END as guest_ip,
                by_e,
                MAX(_time) as _time,
                MAX((referer_id=".self::REFID_BLOGS.")::int)::bool as from_b,
                MAX((referer_id=".self::REFID_CATALOG.")::int)::bool as from_c,
                MAX((referer_id=".self::REFID_PAIDSEATINGS.")::int)::bool as from_p,
				MAX((referer_id=".self::REFID_PAYPLACE.")::int)::bool as from_t,
				MAX((referer_id=".self::REFID_FRL_OFFERS.")::int)::bool as from_o,
				MAX((referer_id=".self::REFID_SEARCH.")::int)::bool as from_s
           FROM ___tmp_arc
          GROUP BY 
                user_id,
                guest_id,
                CASE WHEN guest_id = 0 THEN guest_ip ELSE '' END,
                by_e,
                DATE_TRUNC('hour', _time)
       )";

      $sql = "INSERT INTO stat_hourly (user_id, guest_id, guest_ip, by_e, _time, from_b, from_c, from_p, from_t, from_o, from_s) SELECT * FROM {$grp_data_sql} t";

      if( !($res = $DB->squery($sql)) )
      {
        $e = $DB->error;
        $DB->squery( "ROLLBACK TO SAVEPOINT arc_created" ); // откатываемся к "после создания вр. таблицы"
        $this->log('Ошибка при инсерте в stat_hourly. Возможно конфликт ключей, откатываемся, пытаемся обойти. '.$e, self::LT_WARNING);
        $sql = 
        "INSERT INTO stat_hourly (user_id, guest_id, guest_ip, by_e, _time, from_b, from_c, from_p, from_t, from_o, from_s)
         SELECT t.*
           FROM {$grp_data_sql} t
         LEFT JOIN
           stat_hourly h
             ON h.user_id  = t.user_id
            AND h.guest_id = t.guest_id
            AND h.guest_ip = t.guest_ip
            AND h._time    = t._time
            
-- !!! Нужно AND DATE_TRUNC('hour', h._time) = DATE_TRUNC('hour', t._time)
-- !!! и индекс переделать (user_id, guest_id, guest_ip, DATE_TRUNC('hour', _time))
            
          WHERE h.user_id IS NULL";

        if ( !($res = $DB->squery($sql)) ) {
          $e = $DB->error;
          $DB->rollback(); // откатываемся по полной.
          return $this->log('Ошибка не в конфликте ключей. '.$e, self::LT_ERROR);
        }
      }

      $this->log('Получено '.pg_affected_rows($res).' строк.', self::LT_NOTICE);
      pg_free_result($res);

      // Фиксируем все это дело.
      if ( !$DB->commit() ) {
        $e = $DB->error;
        $DB->rollback();
        return $this->log('Не удалось зафиксировать транзакцию. '.$e, self::LT_ERROR);
      }

      $DB->squery( 'DROP TABLE IF EXISTS ___tmp_arc' );

      // (д) Удаляем эспортированные данные из stat_log_t. Они уже не нужны, т.к. они уже есть в stat_hourly.
      //     Если данные не удалились, то в следующий раз сработает проверка на дубликаты в пункте (г), так что все должно
      //     быть окей.

      $DB->start();
      
      $sql = "DELETE FROM stat_log_t WHERE _time < ?";
      if( !($res = $DB->query($sql, $H)) || !$DB->commit() )
      {
        $e = $DB->error;
        $DB->rollback();
        $this->log('Ошибка при удалении из stat_log_t. '.$e, self::LT_WARNING);
      }
      else
        pg_free_result($res);


      $this->log('Ок.', self::LT_NOTICE);
    }

    $this->log('Пылесосим stat_log_t.', self::LT_NOTICE);

    if ( !$DB->squery('VACUUM FULL stat_log_t') )
      $this->log('VACUUM FULL stat_log_t не сработал. '.$DB->error, self::LT_WARNING);

    return 0;
  }


  /**
   * Наращивает итоговую статистику количества посещений пользовательских страниц, 
   * а также количество переходов на нее с каталогов, блогов и платных мест.
   * Подсчет идет с помента предыдущего запуска, результат сохраняется в stat_summary.
   * @return   string   сообщение об ошибке или 0, если все закончилось хорошо
   */
  function Step2()
  {
    // Наращиваем итоговые счетчики в stat_summary.
    // Все делаем в одной транзакции.
    // Находим точку, от которой нужно взять данные из stat_hourly, чтобы нарастить счетчики.
    // Запрашиваем поле ss_totals_last из таблицы stat_variables (lTC). Оно может быть NULL, то есть там значений не обнаружено.
    // Извлекаем все данные из таблицы stat_hourly, где _time > lTC, то есть, только те, которые мы еще не брали в расчет,
    // группируем и раскидываем по пользователям.
    // Запоминаем время, максимально приближенное к настоящему, взятое из таблицы stat_hourly в stat_variables.ss_totals_last.
    // Если произошла ошибка, то откатываемся.
    
    $this->log('stat_collector::Step2(). Наращиваем итоговые счетчики в stat_summary.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;
    
    $DB->start();
    
    $sql = 
    "INSERT INTO stat_summary (user_id)
     SELECT h.user_id
       FROM (SELECT DISTINCT user_id
               FROM stat_hourly
              WHERE _time > COALESCE((SELECT value::timestamp without time zone FROM stat_variables WHERE name = 'ss_totals_last'), '1970-01-01')) as h
     LEFT JOIN
       stat_summary s
         ON s.user_id = h.user_id
      WHERE s.user_id IS NULL";

    if ( !$DB->squery($sql) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('Обновление итоговых счетчиков. Ошибка на предварительном этапе. '.$e, self::LT_ERROR);
    }

    $DB->start();
    
    $sql = 
    "UPDATE stat_summary as s
        SET
            from_b = s.from_b + h.from_b,
            from_c = s.from_c + h.from_c,
            from_p = s.from_p + h.from_p,
			from_t = s.from_t + h.from_t,
            from_a = s.from_a + h.from_a,
            from_o = s.from_o + h.from_o,
            from_s = s.from_s + h.from_s
       FROM
       (
        SELECT user_id,
               SUM(from_b::int) as from_b,
               SUM(from_c::int) as from_c,
               SUM(from_p::int) as from_p,
			   SUM(from_t::int) as from_t,
			   SUM(from_o::int) as from_o,
			   SUM(from_s::int) as from_s,
               COUNT(*) as from_a
          FROM stat_hourly
         WHERE _time > COALESCE((SELECT value::timestamp without time zone FROM stat_variables WHERE name = 'ss_totals_last'), '1970-01-01')
         GROUP BY user_id
       ) as h
      WHERE s.user_id = h.user_id;

     UPDATE stat_variables SET value = (SELECT _time FROM stat_hourly ORDER BY _time DESC LIMIT 1) WHERE name = 'ss_totals_last'";

    if ( !$DB->squery($sql) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('Ошибка при обновлении итоговых счетчиков в stat_summary. '.$e, self::LT_ERROR);
    }
    
    return 0;
  }


  /**
   * Подсчитывает общую статистику посещений за предыдущий день (или несколько дней, если статистика по этим дням не велась).
   * Данные группируются по тому откуда пришли (блоги, каталоги, платные места) и кто смотрел (фрилансер, работодатель или аноним).
   * Дополнительно считает недельные коэффициенты (усредненное количество переходов из каталогов по дням недели), а также запускает Step3a().
   * @return    string    сообщение об ошибке или 0, если все прошло успешно
   */
  function Step3()
  {
    // Экспортируем данные из stat_hourly в stat_daily.
    // Находим последний день, ранее УЖЕ записанный в stat_daily (mD). Ошибка -- выходим.
    // Берем данные из stat_hourly, где _time < D (все ПОЛНЫЕ дни) и _time >= mD + 1 (mD + 1 = nD: этих дней в stat_daily еще нет).
    // Группируем и запихиваем данные в в stat_daily. Ошибка -- выходим.
    
    $this->log('stat_collector::Step3(). Экспортируем данные из stat_hourly в stat_daily.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;
    
    $time = $this->get_time();
    $D = date('Y-m-d', $time);
    
    $sql = "SELECT _date + 1 FROM stat_daily ORDER BY _date DESC LIMIT 1";
    $nD  = $DB->val( $sql );
    
    if ( $DB->error )
      return $this->log('Ошибка при чтении данных из stat_daily. '.$DB->error, self::LT_ERROR);

    if ( !$nD )
      $nD = '1970-01-01';
    
    $DB->start();
    
    $sql = 
    "INSERT INTO stat_daily
     (
       user_id,
       _date,
       by_f_from_b,
       by_e_from_b,
       by_u_from_b,
       by_f_from_c,
       by_e_from_c,
       by_u_from_c,
       by_f_from_p,
       by_e_from_p,
       by_u_from_p,
       by_f_from_t,
       by_e_from_t,
       by_u_from_t,
       by_f_from_o,
       by_e_from_o,
       by_u_from_o,
       by_f_from_s,
       by_e_from_s,
       by_u_from_s,
       by_f,
       by_e,
       by_u
     )
     SELECT 
            user_id,
            _time::date,
            SUM((guest_id<>0 AND NOT(by_e) AND from_b)::int)  as by_f_from_b,
            SUM((guest_id<>0 AND by_e AND from_b)::int)       as by_e_from_b,
            SUM((guest_id=0  AND from_b)::int)                as by_u_from_b,
            SUM((guest_id<>0 AND NOT(by_e) AND from_c)::int)  as by_f_from_c,
            SUM((guest_id<>0 AND by_e AND from_c)::int)       as by_e_from_c,
            SUM((guest_id=0  AND from_c)::int)                as by_u_from_c,
            SUM((guest_id<>0 AND NOT(by_e) AND from_p)::int)  as by_f_from_p,
            SUM((guest_id<>0 AND by_e AND from_p)::int)       as by_e_from_p,
            SUM((guest_id=0  AND from_p)::int)                as by_u_from_p,
            SUM((guest_id<>0 AND NOT(by_e) AND from_t)::int)  as by_f_from_t,
            SUM((guest_id<>0 AND by_e AND from_t)::int)       as by_e_from_t,
            SUM((guest_id=0  AND from_t)::int)                as by_u_from_t,
            SUM((guest_id<>0 AND NOT(by_e) AND from_o)::int)  as by_f_from_o,
            SUM((guest_id<>0 AND by_e AND from_o)::int)       as by_e_from_o,
            SUM((guest_id=0  AND from_o)::int)                as by_u_from_o,
            SUM((guest_id<>0 AND NOT(by_e) AND from_s)::int)  as by_f_from_s,
            SUM((guest_id<>0 AND by_e AND from_s)::int)       as by_e_from_s,
            SUM((guest_id=0  AND from_s)::int)                as by_u_from_s,
            SUM((guest_id<>0 AND NOT(by_e))::int)             as by_f,
            SUM((guest_id<>0 AND by_e)::int)                  as by_e,
            SUM((guest_id=0)::int)                            as by_u
       FROM stat_hourly
      WHERE _time >= ? AND _time < ? 
      GROUP BY user_id, _time::date";

    if ( !($res = $DB->query($sql, $nD, $D)) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('Ошибка при импорте данных в таблицу stat_daily. '.$e, self::LT_ERROR);
    }

    $daily_affected = pg_affected_rows($res);
    $this->log("Получено {$daily_affected} строк.", self::LT_NOTICE);

    // Удаляем данные из stat_hourly где _time < D - 1 (оставляем только сегодня и вчера). Ошибка, ну и хрен с ней, потом
    // удалятся.

    $sql = "DELETE FROM stat_hourly WHERE _time < ?::date - 1";
    if ( !$DB->query($sql, $D) )
      $this->log('Ошибка при удалении данных из таблицы stat_hourly. '.$DB->error, self::LT_WARNING);

    // Раз в день обновляем недельные коэффициэнты (доли дней недели в недельной посещаемости).
    // Если, например, сервер не работал целый день, то посещений в этот день не было (очень мало т.е. было).
    // Получится нулевая доля... Хреновато. Или наоборот в праздник какой-нить ненормально много народу набежало.
    // Правда, вряд ли они именно из каталога полезут...
    // Не будем делать резких переходов, пусть берется средняя из нового и старого значения.
    if($daily_affected) // может быть разгрузить все-таки функцию. Например, это конкретно в 3 часа ночи выполнять.
    {
        $DB->start();
        
      $sql =
      "CREATE TEMPORARY TABLE ___tmp_dp
       (
         week_day smallint,
         by_e_from_c decimal,
         by_f_from_c decimal,
         by_u_from_c decimal
       );
       INSERT INTO ___tmp_dp
       SELECT EXTRACT(DOW FROM _date),
              SUM(by_e_from_c),
              SUM(by_f_from_c),
              SUM(by_u_from_c)
         FROM stat_daily
        WHERE _date < CURRENT_DATE AND _date >= CURRENT_DATE - 7
        GROUP BY EXTRACT(DOW FROM _date);

       UPDATE stat_week_coeffs wc
          SET by_e_from_c = (wc.by_e_from_c + t.by_e_from_c) / 2, -- устремляем, но усредняя...
              by_f_from_c = (wc.by_f_from_c + t.by_f_from_c) / 2,
              by_u_from_c = (wc.by_u_from_c + t.by_u_from_c) / 2
         FROM 
         (
           SELECT s.week_day,
                  CASE WHEN a.by_e_from_c = 0 THEN 0 ELSE s.by_e_from_c / a.by_e_from_c END as by_e_from_c,
                  CASE WHEN a.by_f_from_c = 0 THEN 0 ELSE s.by_f_from_c / a.by_f_from_c END as by_f_from_c,
                  CASE WHEN a.by_u_from_c = 0 THEN 0 ELSE s.by_u_from_c / a.by_u_from_c END as by_u_from_c
             FROM ___tmp_dp s
           CROSS JOIN
             (
               SELECT COUNT(*) as count,
                      SUM(by_e_from_c) as by_e_from_c,
                      SUM(by_f_from_c) as by_f_from_c,
                      SUM(by_u_from_c) as by_u_from_c
                 FROM ___tmp_dp
             ) as a
            WHERE a.count = 7
              AND a.by_e_from_c + a.by_f_from_c + a.by_u_from_c > 0
         ) as t

        WHERE wc.week_day = t.week_day";

      if ( !($res = $DB->squery($sql)) || !$DB->commit() ) {
        $e = $DB->error;
        $DB->rollback();
        return $this->log('Ошибка при обновлении недельных коэффициэнтов. '.$e, self::LT_WARNING);
      }
      
      pg_free_result($res);
    }

    return $this->Step3a();
  }


  /**
   * Пересчитывает счетчики посещений за последние семь и тридцать дней.
   * @return    string    сообщение об ошибке или 0, если все прошло успешно
   */
  private function Step3a()
  {
    // Обновляем счетчики 7d, 30d. Опишем здесь алгоритм для 7d.
    // Нам нужно: 1) нарастить счетчики на количества поступившие в stat_daily; 2) Уменьшить счетчики на
    // количества, приходящиеся на выбывшие дни из ранее определенного 7-дневного периода.

    // (Пример) Мы заполнили таблицу последний раз 2008-06-23. Поступили данные за 25 и 27-ые числа (26-го нет ничего).
    //          Но нам это уже неинтересно. Мы берем дату сегодняшнего дня 2008-06-28, отнимаем из нее 2008-06-23 и получаем разницу nCnt. Потом находим
    //          дату вчерашний день - 7 и убиваем nCnt дней до этой даты из счетчиков.
    //          2008-06-23 тут означает, то последний раз мы считали неделю от 2008-06-16 до 2008-06-23 (23 не включается). Значит 23-е
    //          число означает, что данные посчитаны верно для 23-го числа. А когда мы находимся в 28-ом числе, то должны нарастить счетчики
    //          на недостающие дни, то есть: 23, 24, 25, 26, 27. А уменьшить их, изъяв дни: 16, 17, 18, 19, 20. Вот и получится новая посчитаная
    //          неделя с 21 по 28 (где 28 не включается).

    // (а) Узнаем дату, на которую последний раз были персчитаны счетчики (ldD). Ошибка -- идем в 6 (шесть).
    // (б) Находим разницу nCnt = D - lD, она означает количество новых дней, ранее не включенных в счетчики.
    //     Если nCnt >= 7, то счетчики не нужно уменьшать, нужно их сбросить в 0 и идти в (г), иначе (в). Ошибка -- выходим из шага.
    // (в, г, д) идут в одной транзакции.
    // (в) Уменьшаем счетчики. Берем период, где _date >= lD-7 (это первый день ранее посчитанной семидневки) и
    //     _date < D-7 (первый день новой семидневки) и уменьшаем счетчики, на количества, взятые из этого периода. То есть,
    //     это "лишние" дни, которые в текущую семидневку не входят. Ошибка -- откат.
    // (г) Наращиваем счетчики. Нужно взять субпериод из ближайшего семидневного периода, ранее не включенный в счетчики.
    //     Значит берем _date >= lD (первый из еще нерасчитанных дней) и _date >= D-7 (первый день новой семидневки, проверка нужна
    //     на случай если nCnt > 7. Да, мы сбросили в этом случае счетчики в 0, но нельзя нарастить ничего лишнего --
    //     например, nCnt = 20 -- надо взять только последнюю неделю). Ошибка -- откат.
    // (д) Заносим в БД сегодняшний день (теперь lD = D). Ошибка -- откат.
    //     Фиксируем.
    
    $this->log('stat_collector::Step3a(). Обновляем счетчики 7d, 30d таблицы stat_summary.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;

    $time = $this->get_time();
    $D = date('Y-m-d', $time);
    $limit = 10000; // максимум записей на один апдейт.
    $affected_total = 0;
    $offset = (int)$DB->val("SELECT value::int FROM stat_variables WHERE name='ss_cyclics_offset'");
    
    // (а), (б)
    $sql = "SELECT value::date AS ld, ?::date - value::date AS cnt FROM stat_variables WHERE name='ss_cyclics_last'";
    if ( !($res = $DB->query($sql, $D)) )
      return $this->log('Ошибка при получении ss_cyclics_last. '.$DB->error, self::LT_ERROR);

    $nCnt = 100000;
    if(!pg_num_rows($res) || !($lD = pg_fetch_result($res,0,0)))
      $lD   = '1970-01-01';
    else
      $nCnt = (int)pg_fetch_result($res,0,1);

    if($nCnt <= 0)
      return $this->log('Новых данных для обновления циклических счетчиков нет.', self::LT_NOTICE);

    $do_7decrease  = ($nCnt < 7);
    $do_30decrease = ($nCnt < 30);


    $this->log('Дописываем stat_summary новыми пользователями, если таковы есть.', self::LT_NOTICE);

    $sql = 
    "INSERT INTO stat_summary (user_id)
     SELECT d.user_id
       FROM (SELECT DISTINCT user_id
               FROM stat_daily
              WHERE _date >= ?) as d
     LEFT JOIN
       stat_summary s
         ON s.user_id = d.user_id
      WHERE s.user_id IS NULL
    ";


    if (!$DB->query($sql, $lD)) {
        return $this->log('Ошибка. '.$DB->error, self::LT_ERROR);
    }
    
    $this->log('Добавлено '.pg_affected_rows($res).' строк.', self::LT_NOTICE);
    
    
    do {
        
        if (!$DB->start()) {
            return $this->log("Обновление циклических счетчиков, офсет: {$offset}. Ошибка -- невозможно открыть транзакцию. {$DB->error}", self::LT_ERROR);
        }
        
        if($affected_total) {
            $offset += $limit;
        }
        
        $affected_total = 0;
        
        
        if($do_30decrease) {
            
            // (в)
            if($do_7decrease) {
            
                $this->log("Уменьшаем счетчики 7d на кол-во посещений за период [lD-7...D-7], офсет: {$offset}.", self::LT_NOTICE);
                
                $sql =
                "UPDATE stat_summary as s
                    SET 
                        by_f_from_b_7d = by_f_from_b_7d - n.by_f_from_b::smallint,
                        by_e_from_b_7d = by_e_from_b_7d - n.by_e_from_b::smallint,
                        by_u_from_b_7d = by_u_from_b_7d - n.by_u_from_b::smallint,
                        by_f_from_p_7d = by_f_from_p_7d - n.by_f_from_p::smallint,
                        by_e_from_p_7d = by_e_from_p_7d - n.by_e_from_p::smallint,
                        by_u_from_p_7d = by_u_from_p_7d - n.by_u_from_p::smallint,
                        by_f_from_o_7d = by_f_from_o_7d - n.by_f_from_o::smallint,
                        by_e_from_o_7d = by_e_from_o_7d - n.by_e_from_o::smallint,
                        by_u_from_o_7d = by_u_from_o_7d - n.by_u_from_o::smallint,
                        by_f_from_s_7d = by_f_from_s_7d - n.by_f_from_s::smallint,
                        by_e_from_s_7d = by_e_from_s_7d - n.by_e_from_s::smallint,
                        by_u_from_s_7d = by_u_from_s_7d - n.by_u_from_s::smallint
                   FROM
                   (
                     SELECT
                            user_id,
                            SUM(by_f_from_b) as by_f_from_b,
                            SUM(by_e_from_b) as by_e_from_b,
                            SUM(by_u_from_b) as by_u_from_b,
                            SUM(by_f_from_c) as by_f_from_c,
                            SUM(by_e_from_c) as by_e_from_c,
                            SUM(by_u_from_c) as by_u_from_c,
                            SUM(by_f_from_p) as by_f_from_p,
                            SUM(by_e_from_p) as by_e_from_p,
                            SUM(by_u_from_p) as by_u_from_p,
                            SUM(by_f_from_t) as by_f_from_t,
                            SUM(by_e_from_t) as by_e_from_t,
                            SUM(by_u_from_t) as by_u_from_t,
                            SUM(by_f_from_o) as by_f_from_o,
                            SUM(by_e_from_o) as by_e_from_o,
                            SUM(by_u_from_o) as by_u_from_o,
                            SUM(by_f_from_s) as by_f_from_s,
                            SUM(by_e_from_s) as by_e_from_s,
                            SUM(by_u_from_s) as by_u_from_s


                       FROM stat_daily

                      WHERE _date <  '{$D}'::date - 7
                        AND _date >= '{$lD}'::date - 7

                      GROUP BY user_id
                      ORDER BY user_id
                      LIMIT {$limit} OFFSET {$offset}

                   ) as n

                  WHERE s.user_id = n.user_id
                ";
                
                if( !($res = $DB->query($sql)) ) {
                    $e = $DB->error;
                    $DB->rollback();
                    return $this->log('Ошибка при обновлении циклических счетчиков. '.$e, self::LT_ERROR);
                }
                
                $affected = (int)pg_affected_rows($res);
                $affected_total += $affected;
                $this->log("Обновлено {$affected} строк.", self::LT_NOTICE);
            } // if($do_7decrease)


            $this->log("Уменьшаем счетчики 30d на кол-во посещений за период [lD-30...D-30], офсет: {$offset}.", self::LT_NOTICE);
            
            $sql =
            "UPDATE stat_summary as s
                SET 
                    by_f_from_b_30d = by_f_from_b_30d - n.by_f_from_b,
                    by_e_from_b_30d = by_e_from_b_30d - n.by_e_from_b,
                    by_u_from_b_30d = by_u_from_b_30d - n.by_u_from_b,
                    by_f_from_c_30d = by_f_from_c_30d - n.by_f_from_c,
                    by_e_from_c_30d = by_e_from_c_30d - n.by_e_from_c,
                    by_u_from_c_30d = by_u_from_c_30d - n.by_u_from_c,
                    by_f_from_p_30d = by_f_from_p_30d - n.by_f_from_p,
                    by_e_from_p_30d = by_e_from_p_30d - n.by_e_from_p,
                    by_u_from_p_30d = by_u_from_p_30d - n.by_u_from_p,
                    by_f_from_t_30d = by_f_from_t_30d - n.by_f_from_t,
                    by_e_from_t_30d = by_e_from_t_30d - n.by_e_from_t,
                    by_u_from_t_30d = by_u_from_t_30d - n.by_u_from_t,
                    by_f_from_o_30d = by_f_from_o_30d - n.by_f_from_o,
                    by_e_from_o_30d = by_e_from_o_30d - n.by_e_from_o,
                    by_u_from_o_30d = by_u_from_o_30d - n.by_u_from_o,
                    by_f_from_s_30d = by_f_from_s_30d - n.by_f_from_s,
                    by_e_from_s_30d = by_e_from_s_30d - n.by_e_from_s,
                    by_u_from_s_30d = by_u_from_s_30d - n.by_u_from_s,
                    by_f_30d = by_f_30d - n.by_f,
                    by_e_30d = by_e_30d - n.by_e,
                    by_u_30d = by_u_30d - n.by_u
               FROM
               (
                 SELECT
                        user_id,
                        SUM(by_f_from_b) as by_f_from_b,
                        SUM(by_e_from_b) as by_e_from_b,
                        SUM(by_u_from_b) as by_u_from_b,
                        SUM(by_f_from_c) as by_f_from_c,
                        SUM(by_e_from_c) as by_e_from_c,
                        SUM(by_u_from_c) as by_u_from_c,
                        SUM(by_f_from_p) as by_f_from_p,
                        SUM(by_e_from_p) as by_e_from_p,
                        SUM(by_u_from_p) as by_u_from_p,
                        SUM(by_f_from_t) as by_f_from_t,
                        SUM(by_e_from_t) as by_e_from_t,
                        SUM(by_u_from_t) as by_u_from_t,
                        SUM(by_f_from_o) as by_f_from_o,
                        SUM(by_e_from_o) as by_e_from_o,
                        SUM(by_u_from_o) as by_u_from_o,
                        SUM(by_f_from_s) as by_f_from_s,
                        SUM(by_e_from_s) as by_e_from_s,
                        SUM(by_u_from_s) as by_u_from_s,
                        SUM(by_f) as by_f,
                        SUM(by_e) as by_e,
                        SUM(by_u) as by_u

                   FROM stat_daily

                   WHERE _date <  '{$D}'::date - 30
                     AND _date >= '{$lD}'::date - 30

                  GROUP BY user_id
                  ORDER BY user_id
                  LIMIT {$limit} OFFSET {$offset}

               ) as n

             WHERE s.user_id = n.user_id
            ";
             
            if( !($res = $DB->query($sql)) ) {
                $e = $DB->error;
                $DB->rollback();
                return $this->log('Ошибка при обновлении циклических счетчиков. '.$e, self::LT_ERROR);
            }
            
            $affected = (int)pg_affected_rows($res);
            $affected_total += $affected;
            $this->log("Обновлено {$affected} строк.", self::LT_NOTICE);
        } // if($do_30decrease)



        $dd7  = $do_7decrease;  // для краткости.
        $dd30 = $do_30decrease;


        // (г), (д)
        $this->log("Увеличиваем счетчики 7d и 30d на кол-во посещений за период [lD...D], офсет: {$offset}.", self::LT_NOTICE);

        $sql = 
        "UPDATE stat_summary as s
            SET 
                by_f_from_b_7d  = ".($dd7 ? 's.by_f_from_b_7d +' : '')." p.by_f_from_b_7d::smallint,
                by_e_from_b_7d  = ".($dd7 ? 's.by_e_from_b_7d +' : '')." p.by_e_from_b_7d::smallint,
                by_u_from_b_7d  = ".($dd7 ? 's.by_u_from_b_7d +' : '')." p.by_u_from_b_7d::smallint,
                by_f_from_p_7d  = ".($dd7 ? 's.by_f_from_p_7d +' : '')." p.by_f_from_p_7d::smallint,
                by_e_from_p_7d  = ".($dd7 ? 's.by_e_from_p_7d +' : '')." p.by_e_from_p_7d::smallint,
                by_u_from_p_7d  = ".($dd7 ? 's.by_u_from_p_7d +' : '')." p.by_u_from_p_7d::smallint,
                by_f_from_o_7d  = ".($dd7 ? 's.by_f_from_o_7d +' : '')." p.by_f_from_o_7d::smallint,
                by_e_from_o_7d  = ".($dd7 ? 's.by_e_from_o_7d +' : '')." p.by_e_from_o_7d::smallint,
                by_u_from_o_7d  = ".($dd7 ? 's.by_u_from_o_7d +' : '')." p.by_u_from_o_7d::smallint,
                by_f_from_s_7d  = ".($dd7 ? 's.by_f_from_s_7d +' : '')." p.by_f_from_s_7d::smallint,
                by_e_from_s_7d  = ".($dd7 ? 's.by_e_from_s_7d +' : '')." p.by_e_from_s_7d::smallint,
                by_u_from_s_7d  = ".($dd7 ? 's.by_u_from_s_7d +' : '')." p.by_u_from_s_7d::smallint,
                by_f_from_b_30d = ".($dd30 ? 's.by_f_from_b_30d +' : '')." p.by_f_from_b_30d,
                by_e_from_b_30d = ".($dd30 ? 's.by_e_from_b_30d +' : '')." p.by_e_from_b_30d,
                by_u_from_b_30d = ".($dd30 ? 's.by_u_from_b_30d +' : '')." p.by_u_from_b_30d,
                by_f_from_c_30d = ".($dd30 ? 's.by_f_from_c_30d +' : '')." p.by_f_from_c_30d,
                by_e_from_c_30d = ".($dd30 ? 's.by_e_from_c_30d +' : '')." p.by_e_from_c_30d,
                by_u_from_c_30d = ".($dd30 ? 's.by_u_from_c_30d +' : '')." p.by_u_from_c_30d,
                by_f_from_p_30d = ".($dd30 ? 's.by_f_from_p_30d +' : '')." p.by_f_from_p_30d,
                by_e_from_p_30d = ".($dd30 ? 's.by_e_from_p_30d +' : '')." p.by_e_from_p_30d,
                by_u_from_p_30d = ".($dd30 ? 's.by_u_from_p_30d +' : '')." p.by_u_from_p_30d,
                by_f_from_t_30d = ".($dd30 ? 's.by_f_from_t_30d +' : '')." p.by_f_from_t_30d,
                by_e_from_t_30d = ".($dd30 ? 's.by_e_from_t_30d +' : '')." p.by_e_from_t_30d,
                by_u_from_t_30d = ".($dd30 ? 's.by_u_from_t_30d +' : '')." p.by_u_from_t_30d,
                by_f_from_o_30d = ".($dd30 ? 's.by_f_from_o_30d +' : '')." p.by_f_from_o_30d,
                by_e_from_o_30d = ".($dd30 ? 's.by_e_from_o_30d +' : '')." p.by_e_from_o_30d,
                by_u_from_o_30d = ".($dd30 ? 's.by_u_from_o_30d +' : '')." p.by_u_from_o_30d,
                by_f_from_s_30d = ".($dd30 ? 's.by_f_from_s_30d +' : '')." p.by_f_from_s_30d,
                by_e_from_s_30d = ".($dd30 ? 's.by_e_from_s_30d +' : '')." p.by_e_from_s_30d,
                by_u_from_s_30d = ".($dd30 ? 's.by_u_from_s_30d +' : '')." p.by_u_from_s_30d,
                by_f_30d = ".($dd30 ? 's.by_f_30d +' : '')." p.by_f_30d,
                by_e_30d = ".($dd30 ? 's.by_e_30d +' : '')." p.by_e_30d,
                by_u_30d = ".($dd30 ? 's.by_u_30d +' : '')." p.by_u_30d
           FROM
           (
             SELECT
                    user_id,
                    SUM((_date >= '{$D}'::date - 7)::int * by_f_from_b) as by_f_from_b_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_e_from_b) as by_e_from_b_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_u_from_b) as by_u_from_b_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_f_from_p) as by_f_from_p_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_e_from_p) as by_e_from_p_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_u_from_p) as by_u_from_p_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_f_from_o) as by_f_from_o_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_e_from_o) as by_e_from_o_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_u_from_o) as by_u_from_o_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_f_from_s) as by_f_from_s_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_e_from_s) as by_e_from_s_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_u_from_s) as by_u_from_s_7d,
                    SUM(by_f_from_b) as by_f_from_b_30d,
                    SUM(by_e_from_b) as by_e_from_b_30d,
                    SUM(by_u_from_b) as by_u_from_b_30d,
                    SUM(by_f_from_c) as by_f_from_c_30d,
                    SUM(by_e_from_c) as by_e_from_c_30d,
                    SUM(by_u_from_c) as by_u_from_c_30d,
                    SUM(by_f_from_p) as by_f_from_p_30d,
                    SUM(by_e_from_p) as by_e_from_p_30d,
                    SUM(by_u_from_p) as by_u_from_p_30d,
                    SUM(by_f_from_t) as by_f_from_t_30d,
                    SUM(by_e_from_t) as by_e_from_t_30d,
                    SUM(by_u_from_t) as by_u_from_t_30d,
                    SUM(by_f_from_o) as by_f_from_o_30d,
                    SUM(by_e_from_o) as by_e_from_o_30d,
                    SUM(by_u_from_o) as by_u_from_o_30d,
                    SUM(by_f_from_s) as by_f_from_s_30d,
                    SUM(by_e_from_s) as by_e_from_s_30d,
                    SUM(by_u_from_s) as by_u_from_s_30d,
                    SUM(by_f) as by_f_30d,
                    SUM(by_e) as by_e_30d,
                    SUM(by_u) as by_u_30d

               FROM stat_daily

              WHERE _date >= '{$lD}'
                AND _date >= '{$D}'::date - 30

              GROUP BY user_id
              ORDER BY user_id
              LIMIT {$limit} OFFSET {$offset}

           ) as p

          WHERE s.user_id = p.user_id
        ";
        
        if( !($res = $DB->query($sql))
            || !$DB->query('UPDATE stat_variables SET value = ? WHERE name = ?', $offset, 'ss_cyclics_offset')
            || !$DB->commit() )
        {
            $e = $DB->error;
            $DB->rollback();
            return $this->log('Ошибка при обновлении циклических счетчиков. '.$e, self::LT_ERROR);
        }
        
        $affected = (int)pg_affected_rows($res);
        $affected_total += $affected;
        $this->log("Обновлено {$affected} строк.", self::LT_NOTICE);
        sleep(1);
    
    } while($affected_total);
    
   

    // !!! Если была ошибка в заполнении stat_daily, то могут быть косяки...
    // _date >= '{$lD}' -- не добавятся вдруг поступившие данные в stat_daily, которые раньше пройти не смогли.
    // Может быть тут как раз нужно брать _date >= последнее число ПОСЧИТАННОЕ в stat_summary, а не число, НА КОТОРОЕ был последний раз рассчет.
    // Не сложности большие и с уменьшением... В общем, похоже эта функция зависит от Step3()...

    if( !$DB->query(
        'UPDATE stat_variables SET value = ? WHERE name = ?; UPDATE stat_variables SET value = ? WHERE name = ?',
        $D, 'ss_cyclics_last', '0', 'ss_cyclics_offset') )
    {
        return $this->log("Ошибка при установке stat_variables.ss_cyclics_*! {$DB->error}", self::LT_ERROR);
    }


    return 0;
  }


  /**
   * Подсчитывает статистику посещений пользотельских страниц за предыдущий месяц (или месяца, если метод для них раньше не запускался).
   * Результат сохраняется в stat_monthly.
   * @return    string   сообщение об ошибке или 0, если все прошло успешно
   */
  function Step4()
  {
    // Заносим данные из stat_daily в stat_monthly.
    // (а) Находим последний записанный месяц в stat_monthly (mM) (это дата первого дня какого-то месяца). Ошибка -- выходим из шага.
    // (б) Берем из stat_daily период, где mM != 0 и _date >= mM + '1 month' (первый день месяца, несуществующего еще в stat_monthly)
    //     и _date < M (текущий месяц не должен быть в stat_monthly). Упаковываем период по месяц/юзер.
    //     Ошибка -- выходим.
    
    $this->log('stat_collector::Step4(). Заносим данные из stat_daily в stat_monthly.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;

    $time = $this->get_time();
    $M = date('Y-m', $time).'-01';


    $sql = "SELECT _date + interval '1 month'  FROM stat_monthly ORDER BY _date DESC LIMIT 1";
    $nM  = $DB->val( $sql );
    
    if ( $DB->error )
      return $this->log('Ошибка при чтении stat_monthly. '.$DB->error, self::LT_ERROR);

    if ( !$nM )
      $nM   = '1970-01-01';

    $sql = 
    "INSERT INTO stat_monthly (user_id, _date, by_f, by_e, by_u)
     SELECT user_id,
            date_trunc('month', _date),
            stat_dayarray(date_part('day', _date)::smallint, by_f),
            stat_dayarray(date_part('day', _date)::smallint, by_e),
            stat_dayarray(date_part('day', _date)::smallint, by_u)
       FROM stat_daily
      WHERE _date >= ? 
        AND _date < ? 
      GROUP BY user_id, date_trunc('month', _date)";
    
    $DB->start();
    
    if ( !$DB->query($sql, $nM, $M) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('Ошибка при обновлении stat_monthly. '.$e, self::LT_ERROR);
    }


    return 0;
  }


  /**
   * Удаляет уже обработанные и ненужные данные из stat_daily.
   * @return   string   сообщение об ошибке или 0, если все прошло успешно
   */
  function Step5()
  {
    // Удаляем лишние данные из stat_daily.
    // (а) Находим последний записанный месяц в stat_monthly (mM). Если ошибка или mM = 0 (в stat_monthly еще ничего не поступало) -- выходим.
    // (б) Берем период _date < ldD - 30 (_date >= ldD - 30 -- необходимо сохранять для счетчиков 30d). ldD по идее должен быть равен D.
    //     и _date < mM + '1 month' (берем данные, только если они уже были перенесены в stat_monthly).

    $this->log('stat_collector::Step5(). Удаляем лишние данные из stat_daily.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;

    $time = $this->get_time();
    $M = date('Y-m', $time).'-01';
    
    $sql = "SELECT _date + interval '1 month'  FROM stat_monthly ORDER BY _date DESC LIMIT 1";
    $nM  = $DB->val( $sql );
    
    if ( $DB->error )
      return $this->log('Ошибка при чтении stat_monthly. '.$DB->error, self::LT_ERROR);

    if ( !$nM )
      return $this->log('Нет лишних данных для удаления из stat_daily.', self::LT_NOTICE);

    $sql = "SELECT value FROM stat_variables WHERE name = 'ss_cyclics_last'";
    $lD  = $DB->val( $sql );
    
    if ( $DB->error )
      return $this->log('Ошибка при получении ss_cyclics_last. '.$DB->error, self::LT_ERROR);

    if ( !$lD )
      return $this->log('Нет лишних данных для удаления из stat_daily.', self::LT_NOTICE);
    
    $DB->start();
    
    $sql = "DELETE FROM stat_daily WHERE _date < ?::date - ".self::MAX_CYCLICS_OFFSET." AND _date < ?";

    if ( !$DB->query($sql, $lD, $nM) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('Ошибка при удалении stat_daily. '.$e, self::LT_ERROR);
    }
    
    
    return 0;
  }


  /**
   * Делает VACUUM для stat_log
   * @return   string    сообщение об ошибке или 0, если все прошло успешно
   */
  function Step6()
  {
    $this->log('stat_collector::Step6(). Пылесос.', self::LT_SUBHEADER);
    
    if(date('G')==8 && date('w')==0) {
        $DB = $this->_sDB;
        
        if ( !$DB->squery('VACUUM FULL stat_log') )
            $this->log('Пылесос stat_log не сработал. '.$DB->error, self::LT_WARNING);
    }
    
    return 0;
  }

    /**
     * Подсчет статистики посещений страницы пользователей по ключевым словам.
     */
    function wordsStatRun() {
        $this->wordsStatStep1();
        $this->wordsStatStep2();
        $this->wordsStatStep3();
    }
  
    /**
     * Пересчет почасовой статистики по ключевым словам.
     * Обрабатываем сырые данные о переходах и считаем статистику по часам.
     */
    function wordsStatStep1() {
        global $DB;
        
        if ( !$DB->start() ) {
        	return false;
        }
        
        // 1.1 группируем сырые данные количества посещений страницы пользователей по каждому ключевому слову 
        // из stat_word_log (считаем несколько переходов одного и того же гостя в течение часа за один) и кладем во временную таблицу.
        // 
        // даже если по каким-то причинам статистика не обновлялась длительное время - то переходы будут засчитаны 
        // в нужный час нужного дня благодаря группировке по date_trunc('hour', stat_word_log.visited)
        $sQuery = "SELECT i.user_id, i.word_id, 
                sum( (i.emp_cnt<>0)::int ) AS emp_cnt, 
                sum( (i.frl_cnt<>0)::int ) AS frl_cnt, 
                sum( (i.user_cnt<>0)::int ) AS user_cnt, 
                i.visited_hour AS stat_date 
            INTO TEMPORARY TABLE ___tmp_word_stat 
            FROM ( 
                SELECT 
                    user_id, word_id, 
                    MAX( (guest_id<>0 AND is_emp='t')::int ) AS emp_cnt, 
                    MAX( (guest_id<>0 AND is_emp='f')::int ) AS frl_cnt, 
                    MAX( (guest_id=0)::int ) AS user_cnt,
                    date_trunc('hour', visited) AS visited_hour
                FROM stat_word_log 
                GROUP BY visited_hour, user_id, word_id, guest_id, 
                    CASE WHEN guest_id = 0 THEN guest_ip ELSE '' END, is_emp 
            ) i 
            GROUP BY i.user_id, i.word_id, i.visited_hour";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        
        // 1.2 кладем в почасовую статистику посещений страницы пользователя по ключевым словам в stat_word_hourly.
        // эта статистика отображается юзеру в колонке "сегодня" и служит для подсчета дневной статистики (шаг3)
        $sQuery = "INSERT INTO stat_word_hourly ( user_id, word_id, emp_cnt, frl_cnt, user_cnt, stat_date ) 
            SELECT user_id, word_id, emp_cnt, frl_cnt, user_cnt, stat_date 
            FROM ___tmp_word_stat";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 1.3 кладем в почасовую статистику посещений страницы пользователя по ключевым словам в stat_word_tmp.
        // эта статистика служит для пересчета статистики за 30 дней и общей статистики (шаг2)
        $sQuery = "INSERT INTO stat_word_tmp ( user_id, word_id, emp_cnt, frl_cnt, user_cnt, stat_date ) 
            SELECT user_id, word_id, emp_cnt, frl_cnt, user_cnt, stat_date 
            FROM ___tmp_word_stat";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 1.4 удаляем временную таблицу.
        if ( !$DB->squery('DROP TABLE IF EXISTS ___tmp_word_stat') ) {
            $DB->rollback();
        	return false;
        }
        
        // 1.5 очищаем stat_word_log.
        if ( !$DB->squery('TRUNCATE stat_word_log') ) {
            $DB->rollback();
        	return false;
        }
        
        if ( !$DB->commit() ) {
            $DB->rollback();
        	return false;
        }
        
        return true;
    }
    
    /**
     * Почасовой пересчет общей статистики по ключевым словам.
     * В общую статистику и статистику за последние 30 дней добавляем данные, которые появились со времени последнего пересчета.
     * 
     * длительная пауза в пересчете статистики не должна повлиять на этот шаг. когда бы ни был совершен переход его все равно 
     * нужно учесть в общей статистике. если статистика не обновлялась более 30 дней, то в статистику "за 30 дней" попадут лишние 
     * переходы, но они тут же вычтутся в wordsStatStep3. 
     */
    function wordsStatStep2() {
        global $DB;
        
        // 2.1 добавляем в общую статистику ключевые слова для которых еще нет записей - в любом случае пригодятся.
        $sQuery = "INSERT INTO stat_word_summary ( user_id, word_id ) 
            SELECT h.user_id, h.word_id 
            FROM ( SELECT DISTINCT user_id, word_id FROM stat_word_tmp ) as h 
            LEFT JOIN stat_word_summary s ON s.user_id = h.user_id AND s.word_id = h.word_id 
            WHERE s.user_id IS NULL AND s.word_id IS NULL";
        
        if ( !$DB->squery($sQuery) ) {
        	return false;
        }
        
        if ( !$DB->start() ) {
        	return false;
        }
        
        // 2.2 добавляем в общую статистику и в статистику за последние 30 дней количества переходов накопившиеся 
        // с момента последнего обновления общей статистики
        $sQuery = "UPDATE stat_word_summary AS s SET
            emp_cnt     = s.emp_cnt     + h.emp_cnt, 
            frl_cnt     = s.frl_cnt     + h.frl_cnt, 
            user_cnt    = s.user_cnt    + h.user_cnt, 
            emp_30_cnt  = s.emp_30_cnt  + h.emp_cnt, 
            frl_30_cnt  = s.frl_30_cnt  + h.frl_cnt, 
            user_30_cnt = s.user_30_cnt + h.user_cnt 
        FROM
        (
            SELECT user_id, word_id, 
                SUM(emp_cnt) AS emp_cnt, 
                SUM(frl_cnt) AS frl_cnt, 
                SUM(user_cnt) AS user_cnt
            FROM stat_word_tmp 
            GROUP BY user_id, word_id 
        ) AS h
        WHERE s.user_id = h.user_id AND s.word_id = h.word_id";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 2.3 удаляем учтенные данные
        if ( !$DB->squery('TRUNCATE stat_word_tmp') ) {
            $DB->rollback();
        	return false;
        }
        
        if ( !$DB->commit() ) {
            $DB->rollback();
        	return false;
        }
    }
    
    /**
     * Подсчитываем статистику по дням. Обновляем статистику за последние 30 дней.
     */
    function wordsStatStep3() {
        global $DB;
        
        if ( !$DB->start() ) {
        	return false;
        }
        
        // 3.1 собираем статистику по ключевым словам по дням
        // даже если по каким-то причинам статистика не обновлялась длительное время - то переходы будут засчитаны 
        // в нужный день благодаря группировке по date_trunc('hour', stat_word_hourly.stat_date)
        $sQuery = "INSERT INTO stat_word_daily ( user_id, word_id, stat_date, emp_cnt, frl_cnt, user_cnt ) 
        SELECT user_id, word_id, date_trunc('day', stat_date)::date AS visited_day, 
            SUM(emp_cnt) AS emp_cnt, 
            SUM(frl_cnt) AS frl_cnt, 
            SUM(user_cnt) AS user_cnt 
        FROM stat_word_hourly 
        WHERE stat_date < date_trunc('day', NOW())::date 
        GROUP BY visited_day, user_id, word_id";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 3.2 удаляем уже учтенную почасовую статистику - она больше не пригодится
        $sQuery = "DELETE FROM stat_word_hourly WHERE stat_date < date_trunc('day', NOW())::date";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        if ( !$DB->commit() ) {
            $DB->rollback();
        	return false;
        }
        
        // Обновляем статистику за последние 30 дней.
        if ( !$DB->start() ) {
        	return false;
        }
        
        // 3.3 Обновляем статистику за последние 30 дней по ключевым словам.
        $sQuery = "UPDATE stat_word_summary AS s SET 
            emp_30_cnt  = (CASE WHEN s.emp_30_cnt >= h.emp_cnt THEN s.emp_30_cnt - h.emp_cnt ELSE 0 END), 
            frl_30_cnt  = (CASE WHEN s.frl_30_cnt >= h.frl_cnt THEN s.frl_30_cnt - h.frl_cnt ELSE 0 END),
            user_30_cnt = (CASE WHEN s.user_30_cnt >= h.user_cnt THEN s.user_30_cnt - h.user_cnt ELSE 0 END)
        FROM
        (
            SELECT user_id, word_id, 
                SUM(emp_cnt)  AS emp_cnt, 
                SUM(frl_cnt)  AS frl_cnt, 
                SUM(user_cnt) AS user_cnt
            FROM stat_word_daily 
            WHERE stat_date <= date_trunc('day', NOW())::date - 30 
            GROUP BY user_id, word_id 
        ) AS h
        WHERE s.user_id = h.user_id AND s.word_id = h.word_id";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 3.4 удаляем учтенную дневную статистику ключевым словам - она больше не пригодится
        $sQuery = "DELETE FROM stat_word_daily WHERE stat_date <= date_trunc('day', NOW())::date - 30";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        if ( !$DB->commit() ) {
            $DB->rollback();
        	return false;
        }
        
        return true;
    }
    
  
  /**
   * Устанавливаем проверочное значение
   *
   */
  function setStamp() {
      if(!isset($_SESSION['stamp'])) $_SESSION['stamp'] = mt_rand(10000, 99999); 
  }
  
  /**
   * Проверяем параметр
   *
   * @param integer|boolean $stamp Параметр проверки
   * @param boolean $del   Удалить параметр или нет
   * @return boolean
   */
  function checkStamp($stamp = false, $del=true) {
      $s_stamp = $_SESSION['stamp'];
      if($del) {
          unset($_SESSION['stamp']);
      }
      if($stamp !== false && $stamp !== $s_stamp) return true;
      
      return false;
  }


  /**
   * Деструктор. Сохраняет лог накопленных ошибок в $this->run_log или выводит
   * их на экран, если $this->output == true.
   */
  function __destruct()
  {
    // Записываем все в лог.
    if($this->log_str) {
      if($this->output)
        print($this->log_str);
      if($f=fopen($this->run_log,"a")) {
        fwrite($f, $this->log_str);
        fclose($f);
      }
    }
  }

    /**
     * Вызывает функцию, которая разносит данные за прошлые года
     * из stat_monthly в архивные таблицы stat_monthly_YYYY
     *
     * @return <type>
     */
    function stat_monthly_split() {
        if($this->isDisabled() == 1) {
            $this->log('Сервер временно отключен.', self::LT_HEADER);
            return false;
        }
        if (!$this->_sDB->squery("SELECT stat_monthly_split()")) {
            return false;
        }
    }
  
}
?>
