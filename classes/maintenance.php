<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/log.php');

/**
 * Класс для обслуживания БД (в процессе разработки).
 */
class Maintenance {

    const MODE_VACUUM  = 1;
    const MODE_ANALYZE = 2;

    private $_config = array (
        self::MODE_ANALYZE => array (
          'min_age' => 43200, // секунды
          'changed_factor' => 0.0000025, // процент измененных данных по отношению к кол-ву записей в таблице за 1 час.
          'max_duration' => 1800,
          'log_file' => 'autovacuum/analyze-%d%m%Y[%H].log'
        ),
        self::MODE_VACUUM => array (
          'min_age'  => 86400,
          'changed_factor' => 0.000005,
          'max_duration' => 1800,
          'log_file' => 'autovacuum/vacuum-%d%m%Y[%H].log'
        ),
        'log_line_prefix' => '%d.%m.%Y %H:%M:%S : '
    );

    private $_mt_cols = array('relid', 'relname', 'seq_scan', 'seq_tup_read', 'idx_scan', 'idx_tup_fetch', 'n_tup_ins',
                              'n_tup_upd', 'n_tup_del', 'n_tup_hot_upd', 'n_live_tup', 'n_dead_tup');
    
    /**
     * Конструктор класса
     */
    function __construct() { }
    
    /**
     * Устанавливает параметр конфигурации
     * 
     * @param mixed $name ключ конфигурации
     * @param mixed $val значение параметра конфигурации
     */
    function setFlag($name, $val) {
        $this->_config[$name] = $val;
    }
    
    /**
     * Берем таблицы, последний анализ, которых был ранее чем analyze_min_age секунд назад
     * или количество изменненных строк > analyze_changed_factor * кол-во строк.
     * Сначала идут по времени последнего анализа.
     * Вопрос в том, что если сработал обычный автовакуум, то в autovacuum_log статистика не поменяется,
     * т.е. не будет известно сколько строк в реальности изменилось с последнего анализа.
     * Решение: не принимать во внимание, т.е. просто соблюдаем условия выше. Может случиться "лишний" анализ, не беда.
     */
    function analyze($db_alias, $mode = Maintenance::MODE_ANALYZE) {
        $cfg = $this->_config[$mode];
        $log = new log($cfg['log_file'], 'w'); // !!! SERVER добавить (для альфы/беты).
        $DB = new DB($db_alias);
        
        $log->linePrefix = $this->_config['log_line_prefix'];
        $log->writeln('Получаем список таблиц');
        
        $last_av_col = "COALESCE(maintenance.max("
                     . ($mode == Maintenance::MODE_ANALYZE ? 'pt.last_analyze, pt.last_autoanalyze, ' : '')
                     . "pt.last_vacuum, pt.last_autovacuum), 'epoch')";
        
        $sql = "
          WITH w_stat as (
            SELECT ts.*, pt.*, ts.relid IS NULL as _is_new, {$last_av_col} as _last_av
              FROM pg_stat_user_tables pt
            LEFT JOIN
              maintenance.table_stat ts
                ON ts.relid = pt.relid
             WHERE pt.schemaname = 'public'
               AND pt.n_live_tup + pt.n_dead_tup > 0
          )
          (
            (SELECT *, 0 as ord, 0.00 as ord2 FROM w_stat WHERE (_is_new OR _last_av + interval '?i seconds' <= now()))
            UNION ALL
            (SELECT *, 1, tup_factor FROM w_stat WHERE tup_factor >= ?f)
          )
          ORDER BY ord, ord2 DESC, _last_av
        ";
        $tbls = $DB->rows($sql, $cfg['min_age'], $cfg['changed_factor']);

        $acnt = 0;
        $log->writeln(count($tbls).' таблиц');
        foreach($tbls as $t) {
            if($log->getTotalTime(NULL) >= $cfg['max_duration']) {
                $log->writeln('Время истекло.');
                break;
            }
            $DB->query( ($mode == Maintenance::MODE_VACUUM ? 'VACUUM ' : 'ANALYZE') . " VERBOSE {$t['relname']}" );
            $cmpls[(int)($t['_is_new']=='t')][] = $t['relid'];
            $log->writeln(pg_last_notice(DB::$connections[$db_alias]));
            $acnt++;
        }

        // Обновляем статистику.
        if( $cmpls[0] ) {
            $cols = $this->_mt_cols;
            unset($cols[0], $cols[1]);
            $lcols = implode(',', $cols);
            $rcols = 'pt.'.implode(', pt.', $cols);
            $sql = "UPDATE maintenance.table_stat ts SET ({$lcols}) = ({$rcols}) FROM pg_stat_user_tables pt WHERE pt.relid IN (?l) AND ts.relid = pt.relid";
            $DB->query($sql, $cmpls[0]);
        }
        if( $cmpls[1] ) {
            $cols = implode(',', $this->_mt_cols);
            $sql = "INSERT INTO maintenance.table_stat ({$cols}) SELECT {$cols} FROM pg_stat_user_tables WHERE relid IN (?l)";
            $DB->query($sql, $cmpls[1]);
        }
        
        // Удаляем лишние таблицы, типа catalog_positions2 (создаются/удаляются автоматом)
        $DB->query('DELETE FROM maintenance.table_stat ts WHERE NOT EXISTS (SELECT 1 FROM pg_stat_user_tables WHERE relid = ts.relid)');

        $log->writeln("Обработано {$acnt} таблиц");
    }
}
