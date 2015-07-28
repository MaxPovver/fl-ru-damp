<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';


/**
 * Класс для работы с СБР со стороны админа.
 */
class sbr_adm extends sbr
{
    const PAGE_SIZE = 20; // кол-во элементов на странице админки СБР.
    const PAGE_SA_SIZE = 20; // кол-во элементов на странице админки СБР (siteadmin).
    const INVOICES_PAGE_SIZE = 20; // количество позиций на странице "Автозагрузка актов и с/ф"

    const STATUS_RESERVED = -2; // код псевдо-статуса "зарезервировано".

    /**
     * Параметры колонок таблиц админки.
     * @var array
     */
    public $form_cols = array (
        'refunds' => array (
            array('№ дог.', 's.id'),
            array('Заказчик', array('ASC'=>'login', 'DESC'=>'login DESC')),
            array('Проект', array('ASC' => 's.name', 'DESC' => 's.name DESC')),
            array('Бюджет', array('ASC' => 's.cost', 'DESC' => 's.cost DESC')),
            array('Действие/выполнен', array('ASC'=>"COALESCE(pp.is_refund, 'epoch')", 'DESC'=>"COALESCE(pp.is_refund, 'epoch') DESC"))
        ),
        'all' => array (
            array('Начало', "COALESCE(ss.start_time, 'epoch')"),
            array('№ дог.', 's.id'),
            array('Проект', 'ss.id'),
            array('Бюджет', 'ss.cost', 2),
            array('Срок',   "COALESCE(ss.start_time, 'epoch') + ss.work_time"),
            array('Статус', 'ss.status')
        ),
        'payouts' => array (
            array('Дата заявки', array('ASC'=>"(sp.completed IS NOT NULL), sp.requested", 'DESC'=>'(sp.completed IS NOT NULL), sp.requested DESC')),
            array('№ дог.', array('ASC'=>"sp.sbr_id", 'DESC'=>"sp.sbr_id DESC")),
            array('Проект',     array('ASC'=>"sp.stage_id, sp.requested", 'DESC'=>'sp.stage_id DESC, sp.requested')),
            array('Получатель', array('ASC'=>'login', 'DESC'=>'login DESC')),
            array('Сумма',      array('ASC'=>'sp.credit_sum', 'DESC'=>'sp.credit_sum DESC'), 2),
            array('№ кошелька/счета',   array('ASC'=>"account_num", 'DESC'=>"account_num DESC")),
            array('Действие/выполнен', array('ASC'=>"COALESCE(sp.completed, 'epoch'), sp.requested", 'DESC'=>"COALESCE(sp.completed, 'epoch') DESC, sp.requested"))
        ),
        'docsflow' => array (
            array('Дате формирования акта', array('ASC'=>"act_upload_time", 'DESC'=>'act_upload_time DESC')),
            array('Дате завершения', array('ASC'=>"ss.arch_closed_time", 'DESC'=>'ss.arch_closed_time DESC')),
            array('Номеру «Безопасной Сделки»', array('ASC'=>"ss.sbr_id", 'DESC'=>"ss.sbr_id DESC"))
        )
    );

    /**
     * Параметры графиков.
     * @var array
     */
    static $stat_graphs = array (
      0 => 'Резерв заключенных',
      1 => 'Выплачено всего по завершению',
      2 => 'Выплачено с Арбитража в пользу фрилансеров',
      3 => 'Выплачено с Арбитража в пользу работодателей',
      4 => 'Комиссия с фрилансеров',
      5 => 'Комиссия с работодателей'
    );

    /**
     * Параметры отчетов по приходам СБР.
     * @var array
     */
    static $reports_ss = array
    (
        exrates::BANK => array (
            'name'=>'Приход денежных средств на р/счет по «Безопасной Сделке»',
            'note'=>NULL,
            'columns'=>array(
                'contract_num'=>array('№ договора', array(15,'left')), // array(название колонки, array(ширина колонки, горизонтальное выравнивание))
                'emp_name'=>array('Наименование работодателя', array(65,'left')),
                'sum_deal'=>array('Сумма сделки, руб.коп', array(15,'right')),
                'sum_commision' => array('Вознаграждение, руб.коп', array(15,'right')),
                'sum'=>array('Сумма, руб.', array(15,'right')),
                'date'=>array('Дата', array(15,'center')),
                'frl_name'=>array('Справочно: исполнитель', array(60,'left'))
            )
        ),

        exrates::YM => array (
            'name'=>'Приложение к отчету ООО "ПС Яндекс.Деньги" Договору № Эк.11111.01 от 03/05/2007',
            'note'=>NULL,
            'columns'=>array(
                'contract_num'=>array('№ договора', array(15,'left')),
                'ydorder_id'=>array('Номер транзакции, ID', array(24,'left')),
                'emp_name'=>array('Наименование работодателя', array(40,'left')),
                'sum_deal'=>array('Сумма сделки, руб.коп', array(15,'right')),
                'sum_commision' => array('Вознаграждение, руб.коп', array(15,'right')),
                'sum' => array('Сумма всего, руб.коп', array(15,'right')),
                'date'=>array('Дата', array(15,'center')),
                'ympurse'=>array('Номер кошелька', array(25,'center')),
                'frl_name'=>array('Справочно: исполнитель', array(40,'left'))

            )
        ),
        exrates::WMR => array (
            'name'=>'Приложение к отчету ОАО "Консервативный Коммерческий Банк" Договору № ДМ-295 от 04.10.11',
            'note'=>NULL,
            'columns'=>array(
                'contract_num'=>array('№ договора', array(15,'left')),
                'emp_name'=>array('Наименование работодателя', array(40,'left')),
                'wmorder_id'=>array('Order ID', array(12,'left')),
                'wmpaymaster_id'=>array('Номер платежа(Paymaster)', array(15,'left')),
                'wmpayment_id'=>array('Номер платежа', array(15,'left')),
                'wmid'=>array('WMID', array(17,'left')),
                'sum_deal'=>array('Сумма сделки, руб.коп', array(15,'right')),
                'sum_commision' => array('Вознаграждение, руб.коп', array(15,'right')),
                'sum'=>array('Сумма всего, руб.', array(15,'right')),
                'date'=>array('Дата', array(15,'center')),
                'wmpurse'=>array('Номер кошелька', array(20,'center')),
                'frl_name'=>array('Справочно: исполнитель', array(40,'left'))
            )
        ),
        'NDFL' => array (
            'name'=>'Наименование организации: Общество с ограниченной отвественностью "Ваан" (ООО "Ваан")',
            'columns'=>array(
                'num'=>array('№', array(5,'center')),
                'fio'=>array('ФИО', array(40,'left')),
                'profit'=>array('Доход', array(12,'right')),
                'ndfl'=>array('НДФЛ', array(12,'right')),
                'payout_sum'=>array('Сумма выплаты', array(15,'right')),
                'date'=>array('Дата', array(14,'center')),
                'contract_num'=>array('№ договора «Безопасной Сделки»', array(17,'center')),
                'payout_sys'=>array('Оплата', array(11,'center')),
                'inn'=>array('ИНН', array(17,'center')),
                'address'=>array('Адрес', array(24,'left')),
                'idcard'=>array('Паспортные данные', array(24,'left')),
                'birthday'=>array('Дата рождения', array(14,'center')),
                'pss'=>array('Номер ПФ', array(22,'left')),
            )
        ),
        'REV' => array(
            'name' => 'ООО "Ваан"',
            'columns' => array(
                'num'           => array('№ п/п', array(5,'center')),
                'contract_num'  => array('Номер «Безопасной Сделки»', array(7, 'center')),
                'fio'           => array('ФИО (название) работодателя', array(40,'left')),
                'sum_deal'      => array('Сумма сделки, руб.коп', array(15,'right')),
                'sum_commision' => array('Вознаграждение ООО "Ваан", руб.коп', array(15,'right')),
                'sum_dept'      => array('Остаток кредиторской  задолженности всего, руб.коп', array(15,'right'))
            )   
        ),
        'YD_REPORT' => array(
            'name' => 'Выплаты ЯД',
            'columns' => array(
                'num'           => array('№ п/п', array(7,'center')),
                'pdate'         => array('Дата', array(20,'left')),
                'summ'          => array('Расход', array(15,'right')),
                'recp'          => array('Корреспондент', array(25,'left')),
                'descr'         => array('Назначение', array(55,'left')),
                'type'          => array('Ч/Б', array(10,'center')),
            )
        )
    );
    
    /**
     * Статусы формируемых закрывающих документов
     * @var array 
     */
    public static $invoice_state = array(
        0 => 'В очереди',
        99 => 'В обработке',
        1 => 'Обработано',
        2 => 'Ошибка',
    );

    /**
     * Редактировать документ по данным пользовательского запроса.
     * 
     * @param array $request   данные запроса (гет, пост).
     * @param array $files   массив $_FILES

     * @return boolean   успешно?
     */
    function editDocR($request, $files) {
        if(!($old_doc = $this->getDocs((int)$request['id']))) return false;
        $old_doc = current($old_doc);
        $this->_docInitFromRequest($request, $files);
        if(!$this->error)
            $ok = $this->editDoc($this->post_doc, $old_doc);
        if(!$ok) {
            $this->post_doc['file_id'] = $old_doc['file_id'];
            $this->post_doc['file_name'] = $old_doc['file_name'];
            $this->post_doc['file_path'] = $old_doc['file_path'];
            $this->post_doc['file_size'] = $old_doc['file_size'];
        }
        return $ok;
    }

    /**
     * Редактировать документ
     * 
     * @param array $doc   новые данные по документу.
     * @param array $old_doc   старые данные по документу.
     * @return boolean   успешно?
     */
    function editDoc($doc, $old_doc) {
        $sql_data = $doc;
        $sql_data['name'] = pg_escape_string(change_q_x($sql_data['name']));
        $act_time = self::$docs_ss[$sql_data['status']][1];
        $file_set = $doc['file_id'] ? ", file_id = {$doc['file_id']}" : '';
        $sql_data['stage_id'] = $sql_data['stage_id'] ? (int)$sql_data['stage_id'] : 'NULL';
        $sql = "
          UPDATE sbr_docs
             SET name = '{$sql_data['name']}',
                 status = {$sql_data['status']},
                 access_role = {$sql_data['access_role']},
                 stage_id = {$sql_data['stage_id']},
                 {$act_time} = COALESCE({$act_time}, now()),
                 type = {$sql_data['type']}
                 {$file_set}
           WHERE id = {$sql_data['id']}
        ";
        $ok = $this->_eventQuery($sql);
        if($ok && $file_set) {
            $cfile = new CFile();
            $cfile->Delete(0, $old_doc['file_path'], $old_doc['file_name']);
        }
        return $ok;
    }

    /**
     * Удаляет документы.
     * 
     * @param array|integer $ids   один или несколько ид. документов.
     * @return boolean   успешно?
     */
    function delDocs($ids) {
        $ids = implode(',', intarrPgSql($ids));
        $sql = "UPDATE sbr_docs SET is_deleted = true WHERE id IN ({$ids})";
        return $this->_eventQuery($sql);
    }
    
    /**
     * Восстанавливает удаленный документ
     * 
     * @param type $ids
     * @return type
     */
    function recoveryDocs($ids) {
        $ids = implode(',', intarrPgSql($ids));
        $sql = "UPDATE sbr_docs SET is_deleted = false WHERE id IN ({$ids})";
        return $this->_eventQuery($sql);
    }

    /**
     * Уснаваливает доступ просмотра документа.
     * 
     * @param array|integer $ids   один или несколько ид. документов.
     * @param integer $mode   флаг доступа (0:скрыт, 1:фрилансер, 2:работодатель, 3:всем)
     * @return boolean   успешно?
     */
    function setDocAccess($ids, $mode) {
        $ids = implode(',', intarrPgSql($ids));
        $sql = "UPDATE sbr_docs SET access_role = {$mode} WHERE id IN ({$ids})";
        return $this->_eventQuery($sql);
    }

    /**
     * Уснаваливает статус документа.

     * 
     * @param array|integer $ids   один или несколько ид. документов.
     * @param integer $mode   статус (1:отправлен, 2:получен, 3:подписан, 4:опубликовано)
     * @return boolean   успешно?
     */
    function setDocStatus($ids, $mode) {
        $ids = implode(',', intarrPgSql($ids));
        $act_time = self::$docs_ss[$mode][1];
        $sql = "UPDATE sbr_docs SET status = {$mode}, {$act_time} = COALESCE({$act_time}, now()) WHERE id IN ({$ids})";
        return $this->_eventQuery($sql);
    }

    /**
     * Устанавливает статус "Документы пришли" на юзеров в определенных этапах.
     *
     * @param array $suids   массив ключей этап_юзер.
     * @param boolean  $mode   если NULL, то развернуть противоположно, иначе жестко установить в заданное значение.
     * @return array   данные по первой записи sbr_stages_users с флагом docs_ready -- документы на месте, можно отправлять в "Выплаты".
     */
    function setDocsReceived($suids, $mode = NULL) {
        if(!is_array($suids)) $suids = array($suids);
        $suids = array_map('pg_escape_string', $suids);
        $mode = $mode===NULL ? 'NOT(docs_received)' : ($mode ? 'true' : 'false');
        $sql = "UPDATE sbr_stages_users SET docs_received = ?b WHERE stage_id||'_'||user_id IN (?l) RETURNING *, (uploaded_docs & " . sbr::DOCS_REQUIRED . ") = " . sbr::DOCS_REQUIRED . " as docs_ready";
        $sql = $this->db()->parse($sql, $mode, $suids);
        if(($res=pg_query(self::connect(), $sql)) && pg_num_rows($res)) {
            require_once($_SERVER['DOCUMENT_ROOT'].'/classes/smail.php');
            $smail = new smail();
            $smail->docsReceivedSBR($suids);
            return pg_fetch_assoc($res);
        }
        return NULL;
    }


    /**
     * Печатает блок документа в админке (либо форму с загрузкой, либо ссылку на готовый док).
     *
     * @param array $doc   документ. Если задан, то остальные параметры не нужны, выдаем ссылку.
     * @param string $anc   якорь на блок сделки (чтоб вернуть туда после загрузки).
     * @param integer  $stage_id   ид. этапа СБР, к которому принадлежит док.
     * @param integer  $doc_type   тип документа.
     * @param integer  $doc_access   доступ к документу.
     * @param boolean  $action_access полный доступ к функциям или нет / если нет только просмотр
     * 
     * @return string
     */
    static function view_doc_field($doc, $anc = NULL, $stage_id = NULL, $doc_type = NULL, $doc_access = NULL, $action_access = true) {
        $doc_tp = $doc ? $doc['type'] : $doc_type;

        $doc_nm = sbr::$docs_types[$doc_tp][2] ? sbr::$docs_types[$doc_tp][2] : sbr::$docs_types[$doc_tp][0];
        if(!$doc) {
            $word = $doc_type==sbr::DOCS_TYPE_ACT || $doc_type==sbr::DOCS_TYPE_FACTURA ? ' Word' : ($doc_type==sbr::DOCS_TYPE_COPY_ACT || $doc_type==sbr::DOCS_TYPE_COPY_FACTURA ? ' скан' : '');
            return '<span>' . $doc_nm
                 . ($action_access ? ', <a href="javascript:;" class="lnk-dot-666" ' . 'onclick="SBR.openDocLoader(this, ' . $stage_id . ', \'' . $anc . '\', ' . $doc_type . ', ' . sbr::DOCS_STATUS_PUBL . ', ' . $doc_access . ')">':'')
                 . ($action_access ? "загрузить {$word}</a>&nbsp;":"")
                 . ($action_access ? '</span>':'');
        }
        return '<a href="'.WDCPREFIX.'/'.$doc['file_path'].$doc['file_name'].'" target="_blank">'.$doc_nm.'</a> <a href="javascript:;" onclick="SBR.delDoc(this, '.$doc['sbr_id'].','.$doc['id']. ',\'' . $anc . '\');">'
               . ($action_access ? '<img src="/images/btn-remove2.png" alt="Удалить" />':'')
               . '</a>';
    }

    /**

     * Формирует SQL-условие по фильтру, заданному в на вкладках "Все" и "В Арбитраже" в админке СБР.
     * @see sbr_adm::getAll()
     *
     * @param array $filter   фильтр по дате, этапу, бюджету и т.д.
     * @return string   sql-текст
     */
    private function _buildAllFilter($filter) {
        global $DB;
        if($fv = trim($filter['start_time'])) {
            $fv = date('d.m.Y', strtotime($fv));
            $where[] = "ss.start_time::date = '{$fv}'::date";
        }
        if($fv = pg_escape_string(trim($filter['stage']))) {
            $fv_int = (int)($fv[0] == '#' ? substr($fv,1) : $fv);
            $where[] = "(ss.name ILIKE '%{$fv}%' OR ss.id = {$fv_int})";
        }
        if($fv = round((float)$filter['cost'], 2))
            $where[] = "ss.cost = {$fv}";
        if($fv = (int)$filter['cost_sys'])
            $where[] = "s.cost_sys = {$fv}";
        if($fv = (int)$filter['sbr'])
            $where[] = "s.id = {$fv}";
        if($fv = trim($filter['work_time'])) {
            if(is_numeric($fv)) {
                $fv .= ' days';
                $where[] = $DB->parse("ss.work_time = ?::interval AND ss.start_time IS NULL", $fv);
            } elseif (strtotime($fv)) {
                $where[] = $DB->parse("(ss.start_time + ss.work_time)::date = ?", $fv);
            }
        }
        if(($fv = (int)$filter['status']) >= 0)
            $where[] = "ss.status = {$fv}";
        else if($fv==self::STATUS_RESERVED)
            $where[] = "s.reserved_id IS NOT NULL";
        if($where)
            return implode(' AND ', $where);
        return NULL;
    }

    /**
     * Формирует SQL-условие по фильтру, заданному в на вкладке "Выплаты" в админке СБР.
     * @see sbr_adm::getAllPayouts()
     *

     * @param array $filter   фильтр по дате заявке, этапу, сумме, юзеру и т.д.
     * @return string   sql-текст
     */
    private function _buildPayoutFilter($filter) {
        if($fv = trim($filter['requested'])) {
            $fv = date('d.m.Y', strtotime($fv));
            $where[] = "sp.requested::date = '{$fv}'::date";
        }
        if($fv = $filter['stage']) {
            $fv_int = (int)($fv[0] == '#' ? substr($fv,1) : $fv);
            $where[] = "sp.stage_id = {$fv_int}";
        }
        if($fv = pg_escape_string(trim($filter['user'])))
            $where[] = "(u.login ILIKE '%{$fv}%' OR u.uname ILIKE '%{$fv}%' OR u.usurname ILIKE '%{$fv}%')";
        if($fv = (float)$filter['sum'])
            $where[] = "sp.credit_sum = {$fv}";
        if($fv = (int)$filter['sys'])
            $where[] = "sp.credit_sys = {$fv}";
        if($fv = (int)$filter['completed'])
            $where[] = "sp.completed IS" .($fv == 1 ? ' NOT' : ''). " NULL";
        if($fv = (int)$filter['refund']) {
            if($fv == 1 || $fv == 2) {
                $where[] = "pp.is_refund = " .($fv == 1 ? 'TRUE' : 'FALSE' );
            } else if($fv == 3) {
                $where[] = "pp.is_refund IS NULL ";
            }
        }
        if($fv = $filter['sbr_name']) {
            $where[] = "s.name ILIKE '%{$fv}%'";
        }
         if($fv = (int)$filter['sbr'])
            $where[] = "s.id = {$fv}";
        if($where)
            return implode(' AND ', $where);
        return NULL;
    }
    
    /**

     * Формирует SQL-условие по фильтру, заданному в на вкладках "В Арбитраже" в админке СБР.
     * @see sbr_adm::getAll()
     *
     * @param array $filter
     * @return string   sql-текст
     */
    private function _buildArbFilter($filter) {
        global $DB;
        // номер сделки
        if ($fv = (int)$filter['sbr']) {
            $where[] = "s.id = {$fv}";
        }
        // номер этапа
        //if($fv = pg_escape_string(trim($filter['stage']))) {
        if($fv = trim($filter['stage'])) {
            $fv_int = (int)($fv[0] == '#' ? substr($fv,1) : $fv);
            $fv_like = '%' . $fv . '%';
            $where[] = $DB->parse("(ss.name ILIKE ? OR ss.id = ?i)", $fv_like, $fv_int);
        }
        // время ожидания ответа/дата окончания срока арбитража
        if($fv = trim($filter['date_to_answer'])) {
            $fv = date('Y-m-d', strtotime($fv));
            $where[] = "GREATEST(add_work_days(ssa.requested, " . sbr_stages::MAX_ARBITRAGE_DAYS . "), ssa.date_to_answer)::date = '$fv'::date";
        }
        // имя арбитра
        if($fv = trim($filter['arbitr_name'])) {
            $where[] = $DB->parse("ssar.name = ?", $fv);
        }
        // срок арбитража
        if ($fv = (int)$filter['days_left']) {
            $where[] = "GREATEST(ssa.date_to_answer::date - now()::date + 1, add_work_days(ssa.requested, " . sbr_stages::MAX_ARBITRAGE_DAYS . ")::date - now()::date + 1) = $fv";
        }
        
        if ($where) {
            return implode(' AND ', $where);
        }
        return NULL;
    }

    /**
     * Выборка всех сделок в админке СБР.
     *
     * @param string $mode   all:взять все, arbitrage:взять все с обращение в Арбитраж.
     * @param integer $page   номер страницы.
     * @param string $dir   сортировка ASC|DESC
     * @param integer $dir_col   номер колонки, по которой соритруем (см. $this->form_cols['all']).
     * @param array $filter   фильтр по дате, этапу, бюджету и т.д.
     * @return array
     */
    function getAll($mode = 'all', $page = 1, $dir='DESC', $dir_col=0, $filter = NULL) {
        $ret = array('data'=>array());
        $limit = self::PAGE_SIZE;
        $offset = ($page-1)*$limit;
        switch($mode) {
            case 'all': $where = 's.is_draft = false'; break;
            case 'arbitrage': $where = 'ss.status = ' . sbr_stages::STATUS_INARBITRAGE; break;
        }
        if($filter) {
            if($fcond = $this->_buildAllFilter($filter))
                $where .= ' AND ' . $fcond;
            $ret['filter'] = $filter;
        }

        $sql = "
          SELECT ss.*, s.scheme_type, s.reserved_id, s.scheme_id, s.cost_sys, pl.ps_emp, extract(day from ss.work_time) as work_days, ss.start_time + ss.work_time as dead_time
          FROM sbr s
          INNER JOIN
            sbr_stages ss
              ON ss.sbr_id = s.id
          LEFT JOIN 
            pskb_lc pl 
              ON pl.sbr_id = s.id
           WHERE {$where}
           ORDER BY {$this->form_cols['all'][$dir_col][1]} {$dir}
           LIMIT ?i OFFSET ?i
        ";

        $sql = $this->db()->parse($sql, $limit, $offset);
           
        if(($res=pg_query(self::connect(), $sql)) && pg_num_rows($res))
            $ret['data'] = pg_fetch_all($res);
        return $ret;
    }
    
    /**
     * Выборка всех сделок в админке СБР которые находятся в арбитраже.
     *
     * @param integer $page   номер страницы.
     * @param string $dir   сортировка ASC|DESC
     * @param integer $dir_col   номер колонки, по которой соритруем
     * @param array $filter   фильтр
     * @return array
     */
    function getArb($page = 1, $dir='DESC', $dir_col=0, $filter = NULL) {
        global $DB;
        
        $ret = array('data'=>array());
        $limit = self::PAGE_SIZE;
        $offset = ($page - 1) * $limit;
        if($filter) {
            if($fcond = $this->_buildArbFilter($filter))
                $where = ' AND ' . $fcond;
            $ret['filter'] = $filter;
        }
        
        $columnsOrder = array (
            '(COALESCE(ssa.date_to_answer < now(), FALSE) OR get_work_days_count(now()::timestamp without time zone , add_work_days(ssa.requested, ' . sbr_stages::MAX_ARBITRAGE_DAYS . ')) <= 1)', // восклицательный знак (alert)
            's.id', // номер договора
            'ss.id', // проект
            'ssm.post_date', // время последнего ответа
            'GREATEST(add_work_days(ssa.requested, ' . sbr_stages::MAX_ARBITRAGE_DAYS . '), ssa.date_to_answer)', // ожидаем до
            'ssar.name', // имя арбитра
            'GREATEST(ssa.date_to_answer::date - now()::date + 1, add_work_days(ssa.requested, ' . sbr_stages::MAX_ARBITRAGE_DAYS . ')::date - now()::date + 1)', // срок арбитража
        );

        $sql = "
            SELECT
                ss.*, ss.id as stage_id, s.scheme_type, s.reserved_id, s.scheme_id, s.cost_sys, s.posted, extract(day from ss.work_time) as work_days, ss.start_time + ss.work_time as dead_time,
                ssa.last_msg_id_users, ssa.last_msg_id_arbitr, ssa.date_to_answer, -- данные по арбитражу
                ssm.post_date as last_msg_post_date, -- дата последнего комментария арбитра
                ssar.name as arbitr_name, -- имя арбитра
                --add_work_days(ssa.requested::date, " . sbr_stages::MAX_ARBITRAGE_DAYS . ") as arbitrage_overdate, -- последняя дата арбитража
                -- наибольшее из даты до которой ждем ответ и последнего дня арбитража
                COALESCE(ssa.date_to_answer, add_work_days(ssa.requested, " . sbr_stages::MAX_ARBITRAGE_DAYS . ")) as date_to_answer_,
                -- TRUE если истек срок ожидания ответа или остался один день до окончания срока арбитража
                (COALESCE(ssa.date_to_answer < now(), FALSE) OR get_work_days_count(now()::timestamp without time zone , add_work_days(ssa.requested, " . sbr_stages::MAX_ARBITRAGE_DAYS . ")) <= 1) as arbitrage_alert,
                -- сколько календарных дней осталось до конца срока арбитража или до окончания срока ожидания ответа, берется большее
                GREATEST(ssa.date_to_answer::date - now()::date + 1, add_work_days(ssa.requested, " . sbr_stages::MAX_ARBITRAGE_DAYS . ")::date - now()::date + 1) as days_to_end
            FROM sbr s
            INNER JOIN sbr_stages ss
                ON ss.sbr_id = s.id
            INNER JOIN sbr_stages_arbitrage ssa ON ssa.stage_id = ss.id
            -- последний коммент от арбитра
            LEFT JOIN sbr_stages_msgs ssm
                ON ssm.id = GREATEST(ssa.last_msg_id_arbitr, ssa.last_msg_id_users)
            LEFT JOIN sbr_stages_arbitrs ssar
                ON ssar.id = ssa.arbitr_id
            WHERE ss.status = " . sbr_stages::STATUS_INARBITRAGE . "
                $where
            ORDER BY {$columnsOrder[$dir_col]} {$dir}
            LIMIT {$limit} OFFSET {$offset}
        ";

        if ($rows = $DB->rows($sql)) {
            $ret['data'] = $rows;
        }
        return $ret;
    }

    /**
     * Выборка всех отзывов сервису в админке СБР. 
     *
     * @param integer $page   номер страницы.
     * @return array
     */
    function getAllFeedbacks($page = 1) {
        $limit = self::PAGE_SIZE;
        $offset = ($page-1)*$limit;
        $sql = "
            SELECT sf.*, s.id as sbr_id, sf.descr as descr_srv, (sf.p_rate + sf.n_rate + sf.a_rate) / 3 as avg_rate_srv, u.login, u.uname, u.usurname, u.photo, u.role, u.is_pro, u.is_team, u.is_pro_test
              FROM (
                SELECT id, frl_id as user_id, frl_feedback_id as feedback_id FROM sbr WHERE frl_feedback_id IS NOT NULL
                UNION ALL
                SELECT id, emp_id, emp_feedback_id FROM sbr WHERE emp_feedback_id IS NOT NULL
              ) as s
            INNER JOIN 
              sbr_feedbacks sf
                ON sf.id = s.feedback_id
            INNER JOIN
              users u
                ON u.uid = s.user_id
          ORDER BY sf.posted_time DESC
          LIMIT ?i OFFSET ?i
        ";
        
        $sql = $this->db()->parse($sql, $limit, $offset);
          
        if($res=pg_query(self::connect(), $sql))
            return pg_fetch_all($res);
        return NULL;
    }
    
    /**
     * Выборка всех выплат в админке СБР для возврата
     *
     * @param integer $page   номер страницы.
     * @param string $dir   сортировка ASC|DESC
     * @param integer $dir_col   номер колонки, по которой соритруем (см. $this->form_cols['all']).
     * @param array $filter   фильтр по дате заявке, этапу, сумме, юзеру и т.д.
     * @return array
     */
    function getAllRefunds($page = 1, $dir='DESC', $dir_col=0, $filter=NULL) {
        $ret = array('data'=>array());
        $dir = $dir=='DESC'?'DESC':'ASC';
        $limit = self::PAGE_SIZE;
        $offset = ($page-1)*$limit;
        if($filter) {
            if($fcond = $this->_buildPayoutFilter($filter))
                $where .= ' AND ' . $fcond;
            $ret['filter'] = $filter;
        }
        $sql = "SELECT 
                    s.id, pp.payment_id, pp.is_refund, u.login, u.role, u.usurname, u.uname, u.uid as user_id,
                    s.name as sbr_name, s.scheme_type, s.status, s.cost, s.cost_sys,
                    replace(substring(ao.descr from 'pmnum: \\\\d+'), 'pmnum: ', '') as wmpaymaster_id
                FROM sbr s
                INNER JOIN employer u ON u.uid = s.emp_id
                INNER JOIN 
                    account_operations ao ON ao.id = s.reserved_id
                LEFT JOIN 
                    pm_payments pp ON pp.billing_id = s.reserved_id
                WHERE s.reserved_id IS NOT NULL AND ao.payment_sys = 10 {$where}
                ORDER BY {$this->form_cols['refunds'][$dir_col][1][$dir]}
                LIMIT {$limit} OFFSET {$offset}
                ";       
        if(($res=pg_query(self::connect(), $sql)) && pg_num_rows($res))
            $ret['data'] = pg_fetch_all($res);
        return $ret;
    }

    /**
     * Выборка всех выплат в админке СБР
     *
     * @param integer $page   номер страницы.
     * @param string $dir   сортировка ASC|DESC
     * @param integer $dir_col   номер колонки, по которой соритруем (см. $this->form_cols['all']).
     * @param array $filter   фильтр по дате заявке, этапу, сумме, юзеру и т.д.
     * @return array
     */
    function getAllPayouts($page = 1, $dir='DESC', $dir_col=0, $filter = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/yd_payments.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wm_payments.php';
        $ret = array('data'=>array());
        $dir = $dir=='DESC'?'DESC':'ASC';
        $limit = self::PAGE_SIZE;
        $offset = ($page-1)*$limit;
        $where = "WHERE su.docs_received = true";
        if($filter) {
            if($fcond = $this->_buildPayoutFilter($filter))
                $where .= ' AND ' . $fcond;
            if($fv = pg_escape_string(trim($filter['account_num'])))
                $where_account_num = "WHERE sp.account_num = '{$fv}'";
            $ret['filter'] = $filter;
        }
        $sql = "
          SELECT *
            FROM (
              SELECT sp.*, ss.sbr_id, ss.num as stage_num, s.scheme_type, s.scheme_id,
                     u.login, u.uname, u.usurname, u.photo, u.role, u.is_pro, u.is_team, u.is_pro_test,
                     CASE credit_sys::text
                       WHEN " . exrates::BANK . "::text THEN (CASE sr.form_type WHEN " . sbr::FT_JURI . " THEN sr._2_bank_rs ELSE sr._1_bank_rs END)::text
                       WHEN " . exrates::YM   . "::text THEN sr._1_el_yd::text  
                       WHEN " . exrates::WMR  . "::text THEN sr._1_el_wmr::text
                       WHEN " . exrates::WMZ  . "::text THEN sr._1_el_wmz::text
                       WHEN " . exrates::FM   . "::text THEN a.id::text
                      END as account_num,
                     yd.id as yd_id, yd.in_amt as yd_in_amt, yd.out_amt as yd_out_amt, yd.ltr_id as yd_ltr_id, (yd.out_amt >= yd.in_amt) as yd_completed,
                     wm.id as wm_id, wm.in_amt as wm_in_amt, wm.out_amt as wm_out_amt, (wm.out_amt >= wm.in_amt) as wm_completed, 
                     pp.payment_id, replace(substring(ao.descr from 'pmnum: \\\\d+'), 'pmnum: ', '') as wmpaymaster_id, ao.payment_sys
                FROM sbr_stages_payouts sp
              INNER JOIN
                sbr_stages_users su
                  ON su.stage_id = sp.stage_id

                 AND su.user_id = sp.user_id
              INNER JOIN
                users u
                  ON u.uid = sp.user_id
              INNER JOIN
                account a
                  ON a.uid = u.uid
              INNER JOIN
                sbr_stages ss
                  ON ss.id = sp.stage_id
              INNER JOIN
                sbr s
                  ON s.id = ss.sbr_id
              INNER JOIN 
                account_operations ao ON ao.id = s.reserved_id    
              LEFT JOIN
                sbr_reqv sr
                  ON sr.user_id = sp.user_id
              LEFT JOIN 
                pm_payments pp ON pp.billing_id = s.reserved_id  
              LEFT JOIN
                yd_payments yd
                  ON yd.src_id = sp.id
                 AND yd.src_type = " . yd_payments::SRC_SBR . "
              LEFT JOIN
                wm_payments wm
                  ON wm.src_id = sp.id
                 AND wm.src_type = " . wm_payments::SRC_SBR . "
               {$where}
            ) as sp
           {$where_account_num}
           ORDER BY {$this->form_cols['payouts'][$dir_col][1][$dir]}
           LIMIT {$limit} OFFSET {$offset}
        ";
        if(($res=pg_query(self::connect(), $sql)) && pg_num_rows($res))
            $ret['data'] = pg_fetch_all($res);
        return $ret;
    }

    /**
     * Возвращает количество всех отзывов сервису.
     * @return integer
     */
    function countFeedbacks() {
        $sql = "SELECT COUNT(*) as cnt FROM sbr s INNER JOIN sbr_feedbacks sf ON sf.id IN (s.frl_feedback_id, s.emp_feedback_id)";
        $mem = new memBuff();
        if($rows = $mem->getSql($err, $sql, 60))
            return $rows[0]['cnt'];
        return 0;
    }

    /**
     * Возвращает количество всех выплат.
     *
     * @param array $filter   фильтр по дате заявке, этапу, сумме, юзеру и т.д.
     * @return integer
     */
    function countPayouts($filter = NULL) {
        //$where = "WHERE ((su.uploaded_docs & " . sbr::DOCS_REQUIRED . ") = " . sbr::DOCS_REQUIRED . " AND su.docs_received = true OR sp.user_id = s.emp_id)";
        $where = "WHERE su.docs_received = true";
        if($filter) {
            $join_u = 'INNER JOIN users u ON u.uid = sp.user_id';
            if($fcond = $this->_buildPayoutFilter($filter))
                $where .= ' AND ' . $fcond;
        }
        $sql = "
          SELECT COUNT(*) as cnt
            FROM sbr_stages_payouts sp
          INNER JOIN
            sbr_stages_users su
              ON su.stage_id = sp.stage_id
             AND su.user_id = sp.user_id
          INNER JOIN
            sbr_stages ss
              ON ss.id = sp.stage_id
          INNER JOIN
            sbr s
              ON s.id = ss.sbr_id
          {$join_u}
          {$where}
        ";
        $mem = new memBuff();
        if($rows = $mem->getSql($err, $sql, 60))
            return $rows[0]['cnt'];
        return 0;
    }
    
    /**
     * Количество зарезервированных денег для возврата
     * 
     * @return int 
     */
    function countRefunds() {
        $sql = "SELECT COUNT(*) as cnt FROM sbr s
                INNER JOIN account_operations ao ON ao.id = s.reserved_id
                WHERE s.reserved_id IS NOT NULL AND ao.payment_sys = 10";
        
        $mem = new memBuff();
        if($rows = $mem->getSql($err, $sql, 60))
            return $rows[0]['cnt'];
        return 0;
    }

    /**
     * Возвращает количество всех этапов СБР.
     *
     * @param array $filter   фильтр по дате, этапу, бюджету и т.д.
     * @return integer
     */
    function countAll($filter = NULL) {
        $where = 'WHERE s.is_draft = false';
        if($filter) {

            if($fcond = $this->_buildAllFilter($filter))
                $where .= ' AND ' . $fcond;

        }
        $sql = "SELECT COUNT(*) as cnt FROM sbr s INNER JOIN sbr_stages ss ON ss.sbr_id = s.id {$where}";
        $mem = new memBuff();
        if($rows = $mem->getSql($err, $sql, 60))
            return $rows[0]['cnt'];
        return 0;
    }

    /**
     * Возвращает количество всех этапов с обращение в Арбитраж.
     *
     * @param array $filter   фильтр по дате, этапу, бюджету и т.д.

     * @return integer
     */
    function countArbitrage($filter = NULL) {
        if($filter) {
            if($fcond = $this->_buildArbFilter($filter))
                $where .= ' AND ' . $fcond;
        }
        $sql = "
            SELECT count(*) as cnt
            FROM sbr s
            INNER JOIN sbr_stages ss
                ON ss.sbr_id = s.id
            INNER JOIN sbr_stages_arbitrage ssa ON ssa.stage_id = ss.id
            -- последний коммент от арбитра
            LEFT JOIN sbr_stages_msgs ssm
                ON ssm.id = ssa.last_msg_id_arbitr AND ssm.is_admin = TRUE
            LEFT JOIN sbr_stages_arbitrs ssar
                ON ssar.id = ssa.arbitr_id
            WHERE ss.status = " . sbr_stages::STATUS_INARBITRAGE . "
                AND s.is_draft = false
                $where";
        $mem = new memBuff();
        if($rows = $mem->getSql($err, $sql, 60)) {
            return $rows[0]['cnt'];
        }
        return 0;
    }


    /**
     * Берет массив с количествами элементов для каждой закладки в админке СБР.
     * @return array   массив, индексированный именами закладок.
     */
    function getCount() {
        return array( 'all'=>$this->countAll(), 'arbitrage'=>$this->countArbitrage(), 'feedbacks'=>$this->countFeedbacks(), 'payouts'=>$this->countPayouts(), 'refunds' => $this->countRefunds() );
    }


    // siteadmin

    /**
     * Информацию по документообороту СБР для админки.
     *
     * @param integer $scheme   тип схем сделок.
     * @param array $filter   фильтр
     * @param integer $page   номер страницы
     * @param string $dir   сортировка ASC|DESC
     * @param integer $dir_col   поле сортировки.
     * @param integer $page_count   вернет всего кол-во строк.
     * @return array
     */
    function getDocsFlow($scheme = sbr::SCHEME_AGNT, $filter = NULL, $page = 1, $dir = 'DESC', $dir_col = 0, &$page_count = NULL) {
        
        $dir = $dir=='DESC'?'DESC':'ASC';
        $limit = self::PAGE_SA_SIZE;
        $offset = ($page-1)*$limit;
        $where = $this->_buildFilterPeriod('ss.arch_closed_time', $filter);
        $page_count = 1;
        $emp_upload_docs_cond = sbr::DOCS_TYPE_ACT | sbr::DOCS_TYPE_ARB_REP; // документы, после загрузки которых выводим работодателя в док-те.
        $frl_upload_docs_cond = sbr::DOCS_TYPE_ACT;
        if($scheme) {
            if($scheme != -1) {
                $where[] = "ss.arch_closed_time > NOW()::date - interval '6 months'";
                $scheme_cond = "AND s.scheme_type = {$scheme}";
                if($scheme == sbr::SCHEME_PDRD || $scheme == sbr::SCHEME_PDRD2) $scheme_cond = "AND ( s.scheme_type = {$scheme} OR s.scheme_type = ".sbr::SCHEME_PDRD2.")";
            }
            $docs_cond = 'AND su.docs_received = false AND su.is_removed = false';
        } else {
            if($filter['archive'] == 't')
                $where[] = "ss.arch_closed_time < NOW()::date - interval '6 months'";
            if($filter['archive'] == 'f')
                $where[] = "ss.arch_closed_time >= NOW()::date - interval '6 months'";
        }

        if($fv = pg_escape_string(trim($filter['contract_num']))) {
            $where[] = "'СБР-'||ss.sbr_id||'-'||ss.num ILIKE '%{$fv}%'";
        }
        if($fv = pg_escape_string(trim($filter['user'])))
            $where[] = "(u.login ILIKE '%{$fv}%' OR u.uname ILIKE '%{$fv}%' OR u.usurname ILIKE '%{$fv}%')";
        if($fv = pg_escape_string(trim($filter['name'])))
            $where[] = "ss.name ILIKE '%{$fv}%'";
        if($fv = round((float)str_replace(array(' ', ','), array('', '.'), $filter['act_sum']),2)) 
            $where[] = "round(su.act_lcomm+su.act_lintr, 2) = {$fv}";
        if($fv = (int)$filter['act_sys'])
            $where[] = "ss.act_sys = {$fv}";
        if($fv = $filter['has_docs'])
            $where[] = "su.docs_received = '{$fv}'";
        if($fv = $filter['has_act'])
            $where[] = "((su.uploaded_docs & " . sbr::DOCS_TYPE_COPY_ACT . ") <> 0) = '{$fv}'";
        if($fv = $filter['has_fct'])
            $where[] = "((su.uploaded_docs & " . sbr::DOCS_TYPE_COPY_FACTURA . ") <> 0) = '{$fv}'";
        if($fv = $filter['has_reqv'])
            $where[] = "COALESCE(sr.is_filled[sr.form_type], false) = '{$fv}'";
        if($fv = $filter['is_removed']) 
            $where[] = "su.is_removed = '{$fv}'";
        if($scheme == 0) {
            $where[] = " ( ss.scheme_type <> " . sbr::SCHEME_LC . " ) "; // исключаем Аккредитив
        }
        if($where)
            $where = 'WHERE ' . implode(' AND ', $where);

             
        $leftPdrd = "WHERE sp.completed IS NOT NULL OR su.user_id IS NOT NULL";            
        if($scheme == SBR::SCHEME_PDRD || $scheme == sbr::SCHEME_PDRD2) {
            $leftPdrd = "LEFT JOIN 
                 sbr_stages_users su1 
                   ON su1.stage_id = wss.id 
                  AND su1.user_id = wss.frl_id 
                  AND (su1.uploaded_docs & {$frl_upload_docs_cond}) <> 0 
                  WHERE su1.user_id IS NOT NULL OR su.user_id IS NOT NULL
                  ";
        }
            
        $from = "
            FROM (
              WITH w_sbr_stages AS (
                SELECT ss.*, s.emp_id, s.frl_id, s.scheme_id, s.scheme_type, s.cost_sys, 
                        arb.resolved, arb.frl_percent
                  FROM sbr s
                INNER JOIN
                  sbr_stages ss
                    ON ss.sbr_id = s.id
                LEFT JOIN sbr_stages_arbitrage arb
                    ON arb.stage_id = ss.id
                 WHERE s.reserved_id IS NOT NULL
                   AND s.norisk_id IS NULL
                       $scheme_cond
              )
              SELECT wss.*, wss.emp_id as user_id, wss.cost_sys as act_sys,
                    wss.closed_time as arch_closed_time
                FROM w_sbr_stages wss 
              LEFT JOIN
                sbr_stages_payouts sp
                  ON sp.stage_id = wss.id
                 AND sp.user_id = wss.frl_id
              LEFT JOIN
                sbr_stages_users su
                  ON su.stage_id = wss.id
                 AND su.user_id = wss.emp_id
                 AND (su.uploaded_docs & {$emp_upload_docs_cond}) <> 0
              {$leftPdrd}  
              UNION ALL
              SELECT wss.*, wss.frl_id, sp.credit_sys,
                    sp.requested as arch_closed_time
                FROM w_sbr_stages wss
              INNER JOIN
                sbr_stages_payouts sp
                  ON sp.stage_id = wss.id
                 AND sp.user_id = wss.frl_id
            ) as ss
          INNER JOIN
            sbr_stages_users su
              ON su.stage_id = ss.id
             AND su.user_id = ss.user_id
             {$docs_cond}
          INNER JOIN
            users u
              ON u.uid = ss.user_id
          LEFT JOIN
            sbr_reqv sr
              ON sr.user_id = ss.user_id
        ";

        $sql = "
          SELECT ss.*, sr.*, ss.id as stage_id,
                 su.uploaded_docs, su.docs_received, su.act_lcomm, su.act_lintr, su.act_lndfl, su.act_lnp, su.act_lcomm + su.act_lintr as act_sum, su.act_notnp, su.is_removed,
                 sp.credit_sys, sp.credit_sum,
                 u.login, u.uname, u.usurname, u.role,
                 COALESCE(docs.publ_time, ss.arch_closed_time) as act_upload_time
          {$from}
          LEFT JOIN
            sbr_stages_payouts sp

              ON sp.stage_id = ss.id
             AND sp.user_id = ss.user_id
             
          --LEFT JOIN sbr_docs docs ON docs.stage_id = ss.id AND docs.access_role IN (1,2) AND docs.type IN (1,8) AND docs.is_deleted = false AND docs.owner_role = 0
          LEFT JOIN (
                SELECT DISTINCT ON (stage_id, access_role) stage_id, d.publ_time, f.modified, d.access_role FROM sbr_docs d
                INNER JOIN file_sbr f ON f.id = d.file_id
                WHERE 
                is_deleted = false AND access_role IN (1,2)
                AND owner_role = 0
                ORDER BY stage_id, access_role, publ_time DESC
          ) docs ON docs.stage_id = ss.id AND (docs.access_role = substring(u.role, 1, 1)::integer + 1)
          
          {$where}
           ORDER BY {$this->form_cols['docsflow'][$dir_col][1][$dir]}
           LIMIT {$limit} OFFSET {$offset}
        ";
           /*
          LEFT JOIN (
                SELECT DISTINCT ON (stage_id) stage_id, d.publ_time, f.modified FROM sbr_docs d
                INNER JOIN sbr s ON s.id = d.sbr_id
                INNER JOIN file_sbr f ON f.id = d.file_id
                WHERE type IN (1,8) 
                --AND status = 4 
                AND is_deleted = false AND access_role IN (1,2)
                AND owner_role = 0
                ORDER BY stage_id, publ_time DESC
          ) docs ON docs.stage_id = ss.id
            */
        if($res = pg_query(self::connect(), $sql)) {
            if($ret = pg_fetch_all($res)) {
                $account = new account;
                foreach($ret as &$row) {
                    if($row['uploaded_docs']) {
                        $access_role = is_emp($row['role']) ? self::DOCS_ACCESS_EMP : self::DOCS_ACCESS_FRL;
                        if($docs = sbr_meta::getDocs("WHERE sd.stage_id = '{$row['id']}' AND (sd.access_role & {$access_role}) = {$access_role} AND sd.is_deleted = false", NULL, true)) {
                            $row['uploaded_docs_a'] = array();
                            foreach($docs as $doc)
                                $row['uploaded_docs_a'][$doc['type']] = $doc;
                        }
                    }
                    // это потом переделать
                    $account->GetInfo($row['user_id']);
                    $row['attaches'] = $account->getAllAttach();
                }
                $sql = "SELECT COUNT(1) as cnt {$from} {$where}";
                $mem = new memBuff();
                if($rows = $mem->getSql($err, $sql, 60))
                    $page_count = $rows[0]['cnt'];
            }
        }

        return $ret;
    }

    /**
     * Строит фильтр для SQL запроса
     *
     * @param  array $dcol имя поля
     * @param  array $filter фильтр по периоду
     * @return array
     */
    function _buildFilterPeriod($dcol, $filter) {
        if($filter['from']) {
            $fv = date('Y-m-d', strtotime($filter['from']['day'].'.'.$filter['from']['month'].'.'.$filter['from']['year']));
            $where[] = "{$dcol} >= '{$fv}'::date";
        }
        if($filter['to']) {
            $fv = date('Y-m-d', strtotime($filter['to']['day'].'.'.$filter['to']['month'].'.'.$filter['to']['year']));
            $where[] = "{$dcol} < '{$fv}'::date + 1";
        }
        return $where;

    }


    /**
     * Возвращает график для статистики СБР в админке.
     *
     * @param array $filter   можно фильтровать по периоду.
     * @return array   
     */
    function getStats($filter = NULL, $ignore_staff = FALSE) {
        $ret = array();
        if($fltpa = $this->_buildFilterPeriod('s.date', $filter))
            $where = 'WHERE ' . implode(' AND ', $fltpa);
        
        if ($ignore_staff) {
            $where = $where ? $where . ' AND ' : ' WHERE ';
            $where .= " NOT (s.date >= '2011-01-01' AND s.user_id IN (SELECT uid FROM users WHERE ignore_in_stats = TRUE))";
        }
        
        $sql = "
          SELECT s.graph_type, s.sys, date_trunc('month', s.date) as month, SUM(s.sum) as sum, COUNT(*) as cnt
            FROM (
                SELECT 0 as graph_type, s.cost_sys as sys, ao.op_date as date, ao.trs_sum as sum, s.emp_id as user_id
                  FROM sbr s
                INNER JOIN
                  account_operations ao
                    ON ao.id = s.reserved_id
                 WHERE s.norisk_id IS NULL
                UNION ALL
                SELECT 1, sp.credit_sys, sp.completed, sp.credit_sum, sp.user_id as user_id
                  FROM sbr s
                INNER JOIN
                  sbr_stages ss
                    ON ss.sbr_id = s.id
                INNER JOIN
                  sbr_stages_payouts sp
                    ON sp.stage_id = ss.id
                   AND sp.completed IS NOT NULL
                 WHERE s.norisk_id IS NULL
                UNION ALL
                SELECT 2, sp.credit_sys, sp.completed, sp.credit_sum, sp.user_id as user_id
                  FROM sbr_stages_payouts sp
                INNER JOIN
                  sbr_stages ss
                    ON ss.id = sp.stage_id
                INNER JOIN sbr s
                    ON s.id = ss.sbr_id
                   AND s.frl_id = sp.user_id
                   AND s.norisk_id IS NULL
                 WHERE sp.completed IS NOT NULL
                   AND sp.is_arbitrage = true
                UNION ALL
                SELECT 3, sp.credit_sys, sp.completed, sp.credit_sum, sp.user_id as user_id
                  FROM sbr_stages_payouts sp
                INNER JOIN
                  sbr_stages ss
                    ON ss.id = sp.stage_id
                INNER JOIN sbr s
                    ON s.id = ss.sbr_id
                   AND s.emp_id = sp.user_id
                   AND s.norisk_id IS NULL
                 WHERE sp.completed IS NOT NULL
                   AND sp.is_arbitrage = true
                UNION ALL
                -- комиссия с фрилансеров (агент: 5% от суммы бюджета, но если по арбитражу 0, то не берется; подряд: 5%, если арбитраж, то пропорционально проценту арбитража)
                SELECT 4, sp.credit_sys, sp.completed, su.act_lcomm, sp.user_id as user_id
                  FROM sbr s
                INNER JOIN
                  sbr_stages ss
                    ON ss.sbr_id = s.id
                INNER JOIN
                  sbr_stages_payouts sp
                    ON sp.stage_id = ss.id
                INNER JOIN
                  sbr_stages_users su
                    ON su.stage_id = sp.stage_id
                   AND su.user_id = sp.user_id
                 WHERE s.norisk_id IS NULL
                UNION ALL
                -- комиссия с работодателя (агент: всегда 5% от суммы бюджета; подряд: 5%, если арбитраж, то пропорционально проценту арбитража).
                SELECT 5, s.cost_sys, s.reserved_time, su.act_lcomm, s.emp_id as user_id
                  FROM sbr s
                INNER JOIN
                  sbr_stages ss
                    ON ss.sbr_id = s.id
                INNER JOIN
                  sbr_stages_users su
                    ON su.stage_id = ss.id
                   AND su.user_id = s.emp_id
                 WHERE s.reserved_id IS NOT NULL
                   AND s.norisk_id IS NULL
                   AND s.status = " . sbr::STATUS_COMPLETED . "
                 --GROUP BY s.id, s.cost_sys, s.reserved_time, s.emp_id
            ) as s
           {$where}
           GROUP BY s.graph_type, s.sys, month
           ORDER BY s.graph_type, s.sys, month
        ";
           
        $fft = 0;
        $llt = 0;
        if($res = pg_query(self::connect(), $sql)) {
            $this->getExrates();
            while($row = pg_fetch_assoc($res)) {
                $gt = $row['graph_type'];
                $sys = $row['sys'];
                if(!$ret[$gt]) {
                    $ret[$gt] = array(
                      'graphs'=>array(),
                      'total'=>array('cnt'=>0, 'fm_sum'=>0.00, 'fm_max'=>0.00)
                    );
                }
                $mt = strtotime($row['month']);
                if($mt < $fft || !$fft) $fft = $mt;
                if($mt > $llt) $llt = $mt;
                if(!$ret[$gt]['graphs'][$sys]) {
                    $ret[$gt]['graphs'][$sys] = array(
                      'months'=>array(),
                      'total'=>array('cnt'=>0, 'sum'=>0.00, 'max'=>0.00, 'fdate'=>$mt, 'ldate'=>$mt)
                    );

                }
                $ret[$gt]['total']['cnt'] += $row['cnt'];
                if ($sys != 1 || ($sys == 1 && $gt >= 4)) {
                    $row['fm_sum'] = $row['sum'] * $this->exrates[exrates::BANK.exrates::FM];
                    $row['sum'] = $row['sum'] * $this->exrates[exrates::BANK.$sys];
                }
                $ret[$gt]['total']['fm_sum'] += $row['fm_sum'];
                $ret[$gt]['graphs'][$sys]['total']['cnt'] += $row['cnt'];
                $ret[$gt]['graphs'][$sys]['total']['sum'] += $row['sum'];
                $ret[$gt]['graphs'][$sys]['total']['ldate'] = $mt;

                if($row['sum'] > $ret[$gt]['graphs'][$sys]['total']['max']) // !!! убрать.
                    $ret[$gt]['graphs'][$sys]['total']['max'] = $row['sum'];
                if($row['fm_sum'] > $ret[$gt]['total']['fm_max'])
                    $ret[$gt]['total']['fm_max'] = $row['fm_sum'];
                $ret[$gt]['graphs'][$sys]['months'][date('Y', $mt)][date('n', $mt)] = $row;

            }


            foreach($ret as &$st) {
                foreach($st['graphs'] as &$graph) {
                    // если начало и конец можно задать индивидуально.
                    $ft = $graph['total']['fdate'];
                    $lt = $graph['total']['ldate'];
                    // если начало -- самый первый месяц в любом из графиков, конец -- аналогично.
                    $ft = $fft;
                    $lt = $llt;

                    $mmy = array();
                    $y = date('Y', $ft);
                    $m = date('n', $ft);
                    while(strtotime("01-{$m}-{$y}") <= $lt) {
                        if(!isset($graph['months'][$y][$m])) {
                            $graph['months'][$y][$m] = array('cnt'=>0, 'sum'=>0.00);
                            if(!$mmy[$y])
                                $mmy[$y] = &$graph['months'][$y];
                        }
                        if($m == 12) {
                            $m = 0;
                            $y++;
                        }
                        $m++;
                    }
                    ksort($graph['months']);
                    foreach($mmy as $y=>$m)
                        ksort($mmy[$y]);
                }
                
            }

        }
        return $ret;
    }

    /**
     * Возвращает график для статистики СБР в админке.
     *
     * @param array $filter   можно фильтровать по периоду.
     * @return array   
     */
    function getStatsByDay($filter = NULL, $ignore_staff = false, $trunc = 'day') {
        $ret = array();
        if($fltpa = $this->_buildFilterPeriod('s.date', $filter))
            $where = 'WHERE ' . implode(' AND ', $fltpa);
        if ($ignore_staff) { 
            $where = $where ? $where . ' AND ' : ' WHERE '; 
            $where .= " NOT (s.date >= '2011-01-01' AND s.user_id IN (SELECT uid FROM users WHERE ignore_in_stats = TRUE))"; 
        } 
            
            
        $sql = "
          SELECT s.graph_type, s.sys, date_trunc('{$trunc}', s.date) as day, SUM(s.sum) as sum, COUNT(*) as cnt
            FROM (

                -- комиссия с фрилансеров (агент: 5% от суммы бюджета, но если по арбитражу 0, то не берется; подряд: 5%, если арбитраж, то пропорционально проценту арбитража)
                SELECT 4 as graph_type, su.user_id, sp.credit_sys as sys, sp.completed as date, su.act_lcomm as sum
                  FROM sbr s
                INNER JOIN
                  sbr_stages ss
                    ON ss.sbr_id = s.id

                INNER JOIN
                  sbr_stages_payouts sp
                    ON sp.stage_id = ss.id
                   AND sp.user_id = s.frl_id
                INNER JOIN
                  sbr_stages_users su
                    ON su.stage_id = sp.stage_id
                   AND su.user_id = sp.user_id

                 WHERE s.norisk_id IS NULL
                UNION ALL
                
                SELECT 0 as graph_type, s.emp_id as user_id, s.cost_sys as sys, ao.op_date as date, ao.trs_sum as sum
                  FROM sbr s
                INNER JOIN
                  account_operations ao
                    ON ao.id = s.reserved_id
                 WHERE s.norisk_id IS NULL
                UNION ALL
                
                -- комиссия с работодателя (агент: всегда 5% от суммы бюджета; подряд: 5%, если арбитраж, то пропорционально проценту арбитража).
                SELECT 5, su.user_id, s.cost_sys, s.reserved_time, su.act_lcomm
                  FROM sbr s
                INNER JOIN

                  sbr_stages ss
                    ON ss.sbr_id = s.id
                INNER JOIN
                  sbr_stages_users su
                    ON su.stage_id = ss.id
                   AND su.user_id = s.emp_id
                 WHERE s.reserved_id IS NOT NULL

                   AND s.status = " . sbr::STATUS_COMPLETED . "
                   AND s.norisk_id IS NULL
            ) as s
           {$where}
           GROUP BY s.graph_type, s.sys, day
           ORDER BY s.graph_type, s.sys, day

        ";
           
        $fft = 0;
        $llt = 0;
        if($res = pg_query(self::connect(), $sql)) {
            $this->getExrates();
            while($row = pg_fetch_assoc($res)) {
                $gt = $row['graph_type'];
                $sys = $row['sys'];
                if(!$ret[$gt]) {
                    $ret[$gt] = array(
                      'graphs'=>array(),
                      'total'=>array('cnt'=>0, 'fm_sum'=>0.00, 'fm_max'=>0.00)
                    );
                }
                $mt = strtotime($row['day']);
                if($mt < $fft || !$fft) $fft = $mt;
                if($mt > $llt) $llt = $mt;
                if(!$ret[$gt]['graphs'][$sys]) {

                    $ret[$gt]['graphs'][$sys] = array(
                      'days'=>array(),
                      'total'=>array('cnt'=>0, 'sum'=>0.00, 'max'=>0.00, 'fdate'=>$mt, 'ldate'=>$mt)
                    );

                }
                $ret[$gt]['total']['cnt'] += $row['cnt'];
                $row['fm_sum'] = $row['sum'] * $this->exrates[exrates::BANK.exrates::FM];
                $row['sum'] = $row['sum'] * $this->exrates[exrates::BANK.$sys];
                $ret[$gt]['total']['fm_sum'] += $row['fm_sum'];
                $ret[$gt]['graphs'][$sys]['total']['cnt'] += $row['cnt'];
                $ret[$gt]['graphs'][$sys]['total']['sum'] += $row['sum'];
                $ret[$gt]['graphs'][$sys]['total']['ldate'] = $mt;
                if($row['sum'] > $ret[$gt]['graphs'][$sys]['total']['max']) // !!! убрать.
                    $ret[$gt]['graphs'][$sys]['total']['max'] = $row['sum'];
                if($row['fm_sum'] > $ret[$gt]['total']['fm_max'])
                    $ret[$gt]['total']['fm_max'] = $row['fm_sum'];
                if ( $trunc == 'year' ) {
                    $ret[$gt]['graphs'][$sys]['days'][date('Y', $mt)] = $row;
                } else if ( $trunc == 'month' ) {
                    $ret[$gt]['graphs'][$sys]['days'][date('Y', $mt)][date('n', $mt)] = $row;
                } else {
                    $ret[$gt]['graphs'][$sys]['days'][date('Y', $mt)][date('n', $mt)][date('j', $mt)] = $row;
                }
            }

            foreach($ret as &$st) {
                foreach($st['graphs'] as &$graph) {
                    $ft = mktime(0, 0, 0, $filter['from']['month'], $filter['from']['day'], $filter['from']['year']);
                    $lt = mktime(0, 0, 0, $filter['to']['month'], $filter['to']['day'], $filter['to']['year']);
                    $c = $ft;
                    
                    while ( $c <= $lt ) {
                        $y = date('Y', $c);
                        $m = date('n', $c);
                        $d = date('j', $c);
                        switch ( $trunc ) {
                            case 'day': {
                                if( !isset($graph['days'][$y][$m][$d]) ) {
                                    $graph['days'][$y][$m][$d] = array('cnt'=>0, 'sum'=>0.00, 'day'=>date('Y-m-d H:i:s', $c));
                                }
                                $c = mktime(0, 0, 0, $m, $d + 1, $y);
                                break;
                            }
                            case 'month': {
                                if( !isset($graph['days'][$y][$m]) ) {
                                    $graph['days'][$y][$m] = array('cnt'=>0, 'sum'=>0.00, 'day'=>date('Y-m-d H:i:s', $c));
                                }
                                $c = mktime(0, 0, 0, $m + 1, $d, $y);
                                break;
                            }
                            case 'year': {
                                if( !isset($graph['days'][$y]) ) {
                                    $graph['days'][$y] = array('cnt'=>0, 'sum'=>0.00, 'day'=>date('Y-m-d H:i:s', $c));
                                }
                                $c = mktime(0, 0, 0, $m, $d, $y + 1);
                                break;
                            }
                        }
                    }
                    
                    ksort($graph['days']);
                    if ( $trunc == 'month' || $trunc == 'day' ) {
                        foreach ( $graph['days'] as $year => $months ) {
                            if ( $trunc == 'day' ) {
                                foreach ( $graph['days'][$year] as $month => $days ) {
                                    ksort($graph['days'][$year][$month]);
                                }
                            }
                            ksort($graph['days'][$year]);
                        }
                    }
                }
            }

        }
        return $ret;
    }

    /**
     * Возвращает отчеты по приходам СБР за указанный период.
     * @param array $filter   фильтр по дате и валюте резервирования.
     */
    function getReports($filter = NULL) {
        $ret = array();
        $where = 'WHERE ao.op_code = ' . sbr::OP_RESERVE;
        if($fltpa = $this->_buildFilterPeriod('ao.op_date', $filter))
            $where .= ' AND ' . implode(' AND ', $fltpa);

        if($filter['cost_sys']) {
            $fv = implode(',', $filter['cost_sys']);
            $where .= " AND s.cost_sys IN ({$fv})";
            if(in_array(exrates::WMR, $filter['cost_sys'])) {
                require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wmpay.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/classes/pmpay.php';
                $wmpay = new wmpay();
                $pmpay = new pmpay();
                $where .= " AND (s.cost_sys <> ".exrates::WMR." OR ao.descr ILIKE '%на кошелек {$wmpay->wmzr[2]}%'OR ao.descr ILIKE '%на кошелек {$pmpay->merchants[1]}%')"; // только белый WMR
            }
        }

        $sql = "
            SELECT s.cost_sys,
                   s.id,
                   s.scheme_type, s.scheme_id,
                   replace(substring(ao.descr from E'wmid:\\\\d+'),'wmid:','') as wmid,
                   replace(substring(ao.descr from E'#\\\\d+'),'#','') as wmorder_id,
                   replace(substring(ao.descr from 'pmnum: \\\\d+'), 'pmnum: ', '') as wmpaymaster_id,
                   replace(substring(ao.descr from E'номер платежа - \\\\d+'),'номер платежа - ','') as wmpayment_id,
                   replace(substring(ao.descr from E'номер покупки - \\\\d+'),'номер покупки - ','') as ydorder_id,
                   ao.trs_sum as sum,
                   (CASE WHEN p.id IS NOT NULL THEN 
			ao.trs_sum - sbr_calctax ( sbr_taxes_id( sbr_exrates_map(p.ps_emp), 1, null), s.scheme_id, s.cost, (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(p.ps_emp), null, null  )
			WHEN s.scheme_type = 5 THEN (ao.trs_sum / (1 + 0.07))
			ELSE (ao.trs_sum / (1 + 0.05)) END ) as sum_deal,
                   (CASE WHEN p.id IS NOT NULL THEN 
			sbr_calctax ( sbr_taxes_id( sbr_exrates_map(p.ps_emp), 1, null), s.scheme_id, s.cost, (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(p.ps_emp), null, null  )
			WHEN s.scheme_type = 5 THEN
			( ao.trs_sum - ao.trs_sum / (1 + 0.07) )
			ELSE 
			( ao.trs_sum - ao.trs_sum / (1 + 0.05) )
			END )  as sum_commision,
                   ao.op_date as date,
                   replace(substring(ao.descr from E'с кошелька R\\\\d+'),'с кошелька ','') as wmpurse,
                   replace(substring(ao.descr from E'с кошелька \\\\d+'),'с кошелька ','') as ympurse,
                   replace(CASE WHEN sr.form_type = 1 OR s.cost_sys = " . exrates::YM . " THEN sr._1_fio ELSE COALESCE(sr._2_full_name,sr._2_org_name) END, '&quot;', '\"') as emp_name,
                   replace(CASE WHEN sf.form_type = 1 THEN sf._1_fio ELSE COALESCE(sf._2_full_name,sf._2_org_name) END, '&quot;', '\"') as frl_name
              FROM account_operations ao
            INNER JOIN
              sbr s
                ON s.reserved_id = ao.id
            LEFT JOIN pskb_lc p ON p.sbr_id = s.id      
            LEFT JOIN
              sbr_reqv sr
                ON sr.user_id = s.emp_id
            LEFT JOIN
              sbr_reqv sf
                ON sf.user_id = s.frl_id
             {$where}
             ORDER BY s.cost_sys, ao.op_date
        ";

        if($res = pg_query(self::connect(), $sql)) {
            while($row = pg_fetch_assoc($res)) {
                $row['contract_num'] = $this->getContractNum($row['id'], $row['scheme_type']);
                $row['date'] = date('d.m.Y H:s', strtotime($row['date']));
                $ret[$row['cost_sys']][] = $row;
            }
        }
        return $ret;
    }

    /**
     * Формирует строки периода времени
     *
     * @param  array $filter фильтр по дате и валюте резервирования.
     * @return array
     */
    function _createPeriodStr($filter) {
        $period = array();
        if($filter['to']) 
            $to_time = strtotime($filter['to']['day'].'.'.$filter['to']['month'].'.'.$filter['to']['year']);
        if($filter['from'])
            $from_time = strtotime($filter['from']['day'].'.'.$filter['from']['month'].'.'.$filter['from']['year']);
        $period[0] = 'Период'.($from_time ? ' с '.date('d.m.Y', $from_time) : '').' по '.date('d.m.Y', $to_time);
        if($filter['from']['month'] == $filter['to']['month']
           && $filter['from']['year'] == $filter['to']['year']
           && $filter['from']['day']==1 && $filter['to']['day']==date('t', $from_time))
        {
            $period[1] = $filter['to']['day'].' '.$GLOBALS['MONTHA'][$filter['to']['month']].' '.$filter['to']['year'] .' года';
        }
        return $period;
    }

    /**
     * Формирует .xls отчеты по приходам СБР за указанный период.
     * @param array $filter   фильтр по дате и валюте резервирования.
     */
    function printReports($filter = NULL) {
        if(!$filter['to'])
            $filter['to'] = array('day'=>date('d'), 'month'=>date('n'), 'year'=>date('Y'));
        if(!($reps = $this->getReports($filter)))
            return false;

        $period = $this->_createPeriodStr($filter);

        $ROW_NAME     = 1;
        $ROW_NOTE     = 2;
        $ROW_PERIOD   = 5;
        $ROW_TBL_HEAD = 7;
        $ROW_TBL_FST  = 8;

        require_once 'Spreadsheet/Excel/Writer.php';
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->send('СБР_'.str_replace('.', '_', $period[0]).'.xls');
        $main_sty = array(
          'FontFamily'=>'Calibri Bold',
          'VAlign'=>'top',
          'Align'=>'center',
          'NumFormat'=>'#'
        );

        $smpl_sty = $main_sty + array('Size'=>11);
        $l_sty = array('Align'=>'left', 'NumFormat'=>'#');
        $r_sty = array('Align'=>'right', 'NumFormat'=>'### ### ##0.00');
        $fld_sty = $smpl_sty + array('Border'=>1, 'BorderColor'=>'black');
        $fmtH = &$workbook->addFormat($main_sty + array('Size'=>12));
        $fmtS = &$workbook->addFormat($smpl_sty);
        $fmtSR = &$workbook->addFormat($r_sty + $smpl_sty);
        $fmtSL = &$workbook->addFormat($l_sty + $smpl_sty);
        $fmtFld = &$workbook->addFormat($fld_sty);
        $fmtFldL = &$workbook->addFormat($l_sty + $fld_sty);
        $fmtFldR = &$workbook->addFormat($r_sty + $fld_sty);
        $fmtU = &$workbook->addFormat($smpl_sty + array('Bottom'=>1, 'BottomColor'=>'black'));

        foreach($reps as $sys=>$rep) {
            if(!($rpss = self::$reports_ss[$sys])) continue;
            $col_cnt = count($rpss['columns']);
            
            $worksheet = $workbook->addWorksheet($GLOBALS['EXRATE_CODES'][$sys][3]);
            $worksheet->setInputEncoding('windows-1251');

            // Заголовок
            if($sys == exrates::YM){
                $from_time =  mktime (0,0,0,(int)$filter['from']['month'],(int)$filter['from']['day'], (int)$filter['from']['year']);
                //$to_time = mktime (59,59,59,(int)$filter['to']['month'],(int)$filter['to']['day'], (int)$filter['to']['year']);
                $event_time = mktime(0,0,0,5,1,2011);
                if($from_time >= $event_time){
                    $rpss['name'] = strtr($rpss['name'],array('11111.01 от 03/05/2007' => '11111.04 от 9 марта 2011 г.'));
                }
            }
            $worksheet->write($ROW_NAME, 0, $rpss['name'], $fmtH);
            $worksheet->setRow($ROW_NAME, 30);
            $worksheet->mergeCells($ROW_NAME, 0, $ROW_NAME, $col_cnt-1);
            
            // Примечание.
            if($rpss['note']) {
                $worksheet->write($ROW_NOTE, 0, $rpss['note'], $fmtS);
                $worksheet->setRow($ROW_NOTE, 20);
                $worksheet->mergeCells($ROW_NOTE, 0, $ROW_NOTE, $col_cnt-1);
            }

            // Период.
            if($period[0])
                $worksheet->write($ROW_PERIOD, 1, $period[0], $fmtS);
            if($period[1])
                $worksheet->write($ROW_PERIOD, $col_cnt-1, $period[1], $fmtS);

            // Шапка таблицы.
            $i=0;
            foreach($rpss['columns'] as $f=>$col) {
                $worksheet->setColumn($i, $i, $col[1][0]);
                $worksheet->write($ROW_TBL_HEAD, $i, $col[0], $fmtFld);
                if($f=='sum')
                    $sum_idx = $i;
                if($f=='sum_deal')
                	$sum_deal_idx = $i;
                if($f=='sum_commision') 
                	$sum_commision_idx = $i;	    
                $i++;
            }

            // Таблица.
            $i=$ROW_TBL_FST;
            foreach($rep as $row) {
                $j=0;
                foreach($rpss['columns'] as $nm=>$col) {
                    $worksheet->write($i, $j, htmlspecialchars_decode($row[$nm], ENT_QUOTES), $col[1][1]=='right' ? $fmtFldR : ($col[1][1]=='left' ? $fmtFldL : $fmtFld));
                    $j++;
                }
                $i++;

            }
            
            // Дно таблицы (итого).
            $worksheet->write($i, 0, 'Итого с учетом НДС ', $fmtSR);
            $worksheet->mergeCells($i, 0, $i, ($sys == exrates::YM || $sys == exrates::WMR || $sys == exrates::WMZ || $sys == exrates::BANK ?$sum_deal_idx-1:$sum_idx-1));
            if($i>$ROW_TBL_FST) {
            	if($sum_deal_idx) {
	                $c1 = Spreadsheet_Excel_Writer::rowcolToCell($ROW_TBL_FST, $sum_deal_idx);
	                $c2 = Spreadsheet_Excel_Writer::rowcolToCell($i-1, $sum_deal_idx);
	                $worksheet->writeFormula($i, $sum_deal_idx, "=SUM({$c1}:{$c2})", $fmtSR);
                }
                
                if($sum_commision_idx) {
	                $c1 = Spreadsheet_Excel_Writer::rowcolToCell($ROW_TBL_FST, $sum_commision_idx);
	                $c2 = Spreadsheet_Excel_Writer::rowcolToCell($i-1, $sum_commision_idx);
	                $worksheet->writeFormula($i, $sum_commision_idx, "=SUM({$c1}:{$c2})", $fmtSR);
                }
                
                $c1 = Spreadsheet_Excel_Writer::rowcolToCell($ROW_TBL_FST, $sum_idx);
                $c2 = Spreadsheet_Excel_Writer::rowcolToCell($i-1, $sum_idx);
                $worksheet->writeFormula($i, $sum_idx, "=SUM({$c1}:{$c2})", $fmtSR);
            }

            // Дно листа.
            $i+=2;
            $worksheet->write($i, 1,  '        Генеральный директор ООО "ВААН"', $fmtSL);
            $worksheet->write($i, 3,  '', $fmtU);
            $worksheet->write($i, 4,  'Тарханов В.О.', $fmtSL);
            $i+=2;
            $worksheet->write($i, 1,  '        Главный бухгалтер ООО "ВААН"', $fmtSL);
            $worksheet->write($i, 3,  '', $fmtU);
            $worksheet->write($i, 4,  'Яцук Е.Г.', $fmtSL);
        }

        $workbook->close();
    }
    
    /**
     * Формирует отчет по арбитражу за определенный период.
     *
     * @param string $sStartDate дата начала периода
     * @param string $sEndDate дата конца периода
     */
    function printArbitrageReport( $sStartDate = null, $sEndDate = null ) {
        global $EXRATE_CODES;
        
        // имя итогового файла
        $sWorkTitle  = 'Arbitrage report';
        $sWorkTitle .= ( $sStartDate ) ? ' '.$sStartDate : '';
        $sWorkTitle .= ( $sEndDate ) ? ' - '.$sEndDate : '';
        $sWorkTitle .= '.xls';
        
        // выбираем все этапы которые закрыты арбитражом
        global $DB;
        $sQuery  = 'SELECT ss.id, sd.num FROM sbr_stages ss 
            LEFT JOIN sbr_docs sd ON ss.sbr_id = sd.sbr_id AND sd.type = 8 
            WHERE ss.status = ' . sbr_stages::STATUS_ARBITRAGED;
        $sQuery .= ( $sStartDate ) ? " AND ss.closed_time >= '$sStartDate'" : '';
        $sQuery .= ( $sEndDate ) ? " AND ss.closed_time <= '$sEndDate'" : '';
        $aRows   = $DB->rows( $sQuery. ' ORDER BY ss.closed_time' );
        
        // подключаем pear
        require_once( 'Spreadsheet/Excel/Writer.php' );
        
        // создаем документ
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion( 8 );
        
        // создаем лист
        $worksheet =& $workbook->addWorksheet( '1' );
        $worksheet->setInputEncoding( 'CP1251' );
        
        // заголовок листа
        $worksheet->write( 0, 0, 'ООО "Ваан"' );
        $worksheet->write( 2, 1, 'Таблица по актам арбитража' );
        
        $m_sty    = array('NumFormat' => '### ### ##0.00', 'Align'=>'right' );
        $d_sty    = array('NumFormat' => 'DD MMM, YYYY HH:MM:SS' );
        $td_sty = array('FontFamily'=>'Calibri', 'VAlign'=>'vequal_space', 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black');
        $th_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black', 'Bold'=>1);
        
        $format_top   =& $workbook->addFormat( $th_sty );
        $format_td    =& $workbook->addFormat( $td_sty );
        $format_money =& $workbook->addFormat( array_merge($td_sty, $m_sty) );
        $format_date  =& $workbook->addFormat( array_merge($td_sty, $d_sty) );
        
        $format_top->setTextWrap( 1 );
        
        $aHeader = array('№ п/п', 'Номер акта', 'Дата', 'Номер «Безопасной Сделки»', "Наименование Работодателя", 'Наименование Исполнителя', 'Сумма к выплате Работодателю, руб.коп.', 'Сумма к выплате Исполнителю, руб.коп.', 'Способ выплаты' );
        
        for ( $i = 0; $i<count($aHeader); $i++ ) {
            $worksheet->write( 3, $i, $aHeader[$i], $format_top );
        }
        
        // данные
        if ( $aRows ) {

            $nCnt = 1;
            $aRates = exrates::GetAll();
            
        	foreach ($aRows as $aOne) {
        	    $sbr   = sbr_meta::getInstance( sbr_meta::ADMIN_ACCESS );
        		$stage = $sbr->initFromStage( $aOne['id'], false );
        		$stage->getArbitrage( true );
        		
        		// № п/п
        		$worksheet->write( $nCnt+3, 0, $nCnt, $format_td );
        		
        		// Номер акта
        		$worksheet->write( $nCnt+3, 1, $aOne['num'], $format_td );
        		
        		// Дата
        		$sDate = date('Y-m-d H:i:s', strtotime($stage->arbitrage['resolved']));
        		$worksheet->write( $nCnt+3, 2, $sDate, $format_date );
        		
        		// Номер СБР
        		$worksheet->write( $nCnt+3, 3, $stage->sbr->getContractNum(), $format_td );
        		
        		// Наименование Работодателя
                $stage->sbr->getEmpReqvs();
        		$sEmpFio = sbr_meta::getFioFromReqvs( $stage->sbr->emp_reqvs );
        		
        		if ( !$sEmpFio ) {
        			$emp = new employer();
        			$emp->GetUserByUID( $stage->sbr->emp_id );
        			$sEmpFio = $emp->uname.' '.$emp->usurname.' ['.$emp->login.']';
        		}
        		
        		$worksheet->write( $nCnt+3, 4, $sEmpFio, $format_td );
        		
        		// Наименование Исполнителя
        		$stage->sbr->getFrlReqvs();
                $sFrlFio = sbr_meta::getFioFromReqvs( $stage->sbr->frl_reqvs );
                
                if ( !$sFrlFio ) {
        			$frl = new freelancer();
        			$frl->GetUserByUID( $stage->sbr->frl_id );
        			$sFrlFio = $frl->uname.' '.$frl->usurname.' ['.$frl->login.']';
        		}
                
                $worksheet->write( $nCnt+3, 5, $sFrlFio, $format_td );
                
                // Сумма к выплате Работодателю, руб.коп.
                $nSumm  = $stage->getPayoutSum( sbr::EMP, exrates::BANK );
                $worksheet->write( $nCnt+3, 6, $nSumm, $format_money );
                
                // Сумма к выплате Исполнителю, руб.коп.
                $nSumm  = $stage->getPayoutSum( sbr::FRL, exrates::BANK );
                $worksheet->write( $nCnt+3, 7, $nSumm, $format_money );
                
                // Способ выплаты
                $worksheet->write( $nCnt+3, 8, $EXRATE_CODES[$stage->sbr->cost_sys][1], $format_td );
        		
        		$nCnt++;
        	}
        }
        
        // отправляем на скачивание
        $workbook->send( $sWorkTitle );
        
        // закрываем документ
        $workbook->close();
    }

    /**
     * Выдает данные по НДФЛ в СБР за указанный период.
     * @param array $filter   фильтр по дате.
     */
    function getNdflReport($filter = NULL) {
        $ret = array();
        $where = 'WHERE sp.completed IS NOT NULL';
        if($fltpa = $this->_buildFilterPeriod('sp.completed', $filter))
            $where .= ' AND ' . implode(' AND ', $fltpa);
        
        $sql = "
          SELECT ss.sbr_id, s.scheme_type, s.scheme_id,
                 sp.credit_sum, sp.credit_sys, sp.completed,
                 su.act_lndfl,
                 (CASE WHEN srh.id > 0 THEN srh._1_fio ELSE sr._1_fio END) as fio,
				 (CASE WHEN srh.id > 0 THEN srh._1_inn ELSE sr._1_inn END) as inn,
				 (CASE WHEN srh.id > 0 THEN srh._1_index ELSE sr._1_index END) as indx,
				 (CASE WHEN srh.id > 0 THEN srh._1_country ELSE sr._1_country END) as country,
				 (CASE WHEN srh.id > 0 THEN srh._1_city ELSE sr._1_city END) as city,
				 (CASE WHEN srh.id > 0 THEN srh._1_address ELSE sr._1_address END) as address,
				 (CASE WHEN srh.id > 0 THEN srh._1_idcard ELSE sr._1_idcard END) as idcard,
				 (CASE WHEN srh.id > 0 THEN srh._1_idcard_by ELSE sr._1_idcard_by END) as idcard_by,
				 (CASE WHEN srh.id > 0 THEN srh._1_idcard_name ELSE sr._1_idcard_name END) as idcard_name,
				 (CASE WHEN srh.id > 0 THEN srh._1_idcard_from ELSE sr._1_idcard_from END) as idcard_from,
				 (CASE WHEN srh.id > 0 THEN srh._1_birthday ELSE sr._1_birthday END) as birthday,
				 (CASE WHEN srh.id > 0 THEN srh._1_pss ELSE sr._1_pss END) as pss,
                 s.cost / s.cost_fm as fm_rate -- если будет возможность резервировать не в рублевых валютах, то такой способ не подойдет (например, резерв в USD, а выплата в RUR).
            FROM sbr_stages_payouts sp
          INNER JOIN 
            sbr_stages_users su
              ON su.stage_id = sp.stage_id

             AND su.user_id = sp.user_id
             AND su.act_lndfl <> 0
             AND su.is_removed = FALSE
          INNER JOIN 
            sbr_stages ss
              ON ss.id = su.stage_id
          INNER JOIN 
            sbr s
              ON s.id = ss.sbr_id
          LEFT JOIN
            sbr_reqv sr
              ON sr.user_id = su.user_id
          LEFT JOIN 
          	sbr_reqv_history srh 
              ON srh.user_id = su.user_id 
             
             AND srh.stage_id = sp.stage_id 
             AND srh.history_type = 1    
           {$where}
           ORDER BY sp.completed
        ";

        if($res = pg_query(self::connect(), $sql)) {
            $i=1;
            while($row = pg_fetch_assoc($res)) {
                $exr = $row['credit_sys'] == exrates::FM ? $row['fm_rate'] : 1;
                $row['num'] = $i;
                $row['profit'] = round($exr * $row['credit_sum'] + $row['act_lndfl'], 2);
                $row['ndfl'] = round($row['act_lndfl'], 2); // НДФЛ в базу пишется в рублях.
                $row['payout_sum'] = round($exr * $row['credit_sum'], 2);
                $row['payout_sys'] = $row['credit_sys'] == exrates::BANK ? 'р/счет' : 'эл. чеки';
                $row['date'] = date('d.m.Y', strtotime($row['completed']));
                $row['contract_num'] = $this->getContractNum($row['sbr_id'], $row['scheme_type']);
                $row['idcard'] = $row['idcard_name'].' № '.$row['idcard'].' '.$row['idcard_from'].' '.$row['idcard_by'];
                $row['birthday'] = date('d.m.Y', strtotime($row['birthday']));
                $row['address'] = ($row['indx']?"{$row['indx']}, ":"")."{$row['country']}, {$row['city']}, {$row['address']}";
                $ret[] = $row;
                $i++;
            }
        }
        return $ret;
    }

    /**
     * Формирует .xls отчет по НДФЛ в СБР за указанный период.
     * @param array $filter   фильтр по дате.
     */
    function printNdflReport($filter = NULL) {
        if(!$filter['to'])
            $filter['to'] = array('day'=>date('d'), 'month'=>date('n'), 'year'=>date('Y'));
        
        if(!($rep = $this->getNdflReport($filter)))
            return false;

        $period = $this->_createPeriodStr($filter);
        $rpss = self::$reports_ss['NDFL'];

        $COL_START = 1;

        require_once 'Spreadsheet/Excel/Writer.php';
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->send('СБР_НДФЛ_'.str_replace('.', '_', $period[0]).'.xls');
        
        $body_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Bold'=>1);
        $td_sty = array('FontFamily'=>'Calibri', 'VAlign'=>'vequal_space', 'Align'=>'center', 'Size'=>11, 'Border'=>1, 'BorderColor'=>'black', 'NumFormat'=>'#');
        $th_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black', 'Bold'=>1);
        $l_sty = array('Align'=>'left');
        $r_sty = array('Align'=>'right', 'NumFormat'=>'### ### ##0.00');

        $fmtBODY = &$workbook->addFormat($body_sty);
        $fmtTD = &$workbook->addFormat($td_sty);
        $fmtTH = &$workbook->addFormat($th_sty);

        $fmtTDL = &$workbook->addFormat($l_sty + $td_sty);
        $fmtTDR = &$workbook->addFormat($r_sty + $td_sty);

        $worksheet = $workbook->addWorksheet('НДФЛ');
        $worksheet->setInputEncoding('windows-1251');
        $worksheet->setZoom(75);
        $worksheet->setColumn(0,0, 2);

        // Заголовок
        $worksheet->write(0, $COL_START, $rpss['name'], $fmtBODY);
        $worksheet->mergeCells(0, $COL_START, 0, 6);


        // Шапка таблицы.
        $i=$COL_START;
        foreach($rpss['columns'] as $f=>$col) {
            $worksheet->setColumn($i, $i, $col[1][0]);
            $worksheet->write(2, $i, $col[0], $fmtTH);
            $i++;
        }
        
        // Таблица.
        $i=3;
        foreach($rep as $row) {
            $j=$COL_START;
            foreach($rpss['columns'] as $nm=>$col) {
                $worksheet->write($i, $j, htmlspecialchars_decode($row[$nm],ENT_QUOTES), $col[1][1]=='right' ? $fmtTDR : ($col[1][1]=='left' ? $fmtTDL : $fmtTD));
                $j++;
            }
            $i++;
        }

        $workbook->close();
    }
    
    /**
     * Данные о деньгах работодателей по текущим незакрытым сделкам за указанный период.
     *
     * @param array $filter   фильтр по дате.
     * @return array
     */
    function getRevisionReport($filter) {
        $ret = array();
        $where = 'WHERE ss.closed_time IS NULL';
        if($fltpa = $this->_buildFilterPeriod('s.reserved_time', $filter))
            $where .= ' AND ' . implode(' AND ', $fltpa);
        
        $sql = "SELECT ss.sbr_id, ss.id as stage_id, ss.cost, su.act_lcomm, s.scheme_id, s.scheme_type, sa.id as is_arb, sa.resolved, srh.form_type, ss.num+1 as stage_num,
                    COALESCE(COALESCE (bp.fio, srh._1_fio), sr._1_fio) as fio, s.cost_sys, 
				    replace(
				    CASE WHEN srh.form_type = 1 OR s.cost_sys = 4  THEN COALESCE (COALESCE (bp.fio, srh._1_fio), sr._1_fio)  -- физ
				    ELSE 
				        COALESCE ( 
				            COALESCE (ro.full_name, COALESCE (srh._2_full_name, sr._2_full_name)), 
				            COALESCE (srh._2_org_name, sr._2_org_name)
				        ) END, '&quot;', '\"') as emp_name,
                    sbr_calctax ( sbr_taxes_id( sbr_exrates_map(p.ps_frl), 0, null), s.scheme_id, ss.cost, (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(p.ps_frl), null, null  ) as tax
                FROM 
                    sbr_stages ss
                INNER JOIN  
                    sbr s ON s.id = ss.sbr_id AND s.reserved_id IS NOT NULL
                INNER JOIN 
                    sbr_stages_users su ON su.stage_id = ss.id AND su.user_id = s.emp_id
                LEFT JOIN pskb_lc p ON p.sbr_id = s.id    
                LEFT JOIN 
                    reqv_ordered ro ON ro.billing_id = s.reserved_id
                LEFT JOIN 
                    bank_payments bp ON bp.sbr_id = s.id
                LEFT JOIN  
                    sbr_stages_arbitrage sa ON sa.stage_id = ss.id AND sa.user_id = su.user_id
                LEFT JOIN
                    sbr_reqv sr ON sr.user_id = su.user_id
                LEFT JOIN 
                    sbr_reqv_history srh ON srh.user_id = su.user_id  AND srh.stage_id = ss.id AND srh.history_type = 0
               {$where}
               ORDER BY ss.sbr_id DESC";

        if($res = pg_query(self::connect(), $sql)) {
            $i=1;
            while($row = pg_fetch_assoc($res)) {
                $exr = $row['credit_sys'] == exrates::FM ? $row['fm_rate'] : 1;
                $row['num'] = $i;
                $row['contract_num'] = $this->getContractNum($row['sbr_id'], $row['scheme_type'])."-({$row['stage_num']})";
                if(trim($row['fio']) == "" || $row['form_type'] == 2) $row['fio'] = $row['emp_name'];
                if(trim($row['fio']) == "" && ($row['cost_sys'] == exrates::YM || $row['cost_sys'] == exrates::WMR)) $row['fio'] = 'Физическое лицо';
                if($row['tax'] > 0) {
                    $row['sum_commision'] = $row['tax'];
                    $row['sum_deal']      = $row['is_arb'] > 0 ? $row['cost'] : ($row['cost'] - $row['tax']);
                } else if($row['scheme_type'] == sbr::SCHEME_PDRD2) {
                    $row['sum_deal']      = $row['is_arb'] > 0 ? $row['cost'] : ($row['cost'] - ($row['cost'] * 0.03));
                    $row['sum_commision'] = $row['is_arb'] > 0 ? "" : ($row['cost'] * 0.03);
                } else {
                    $row['sum_deal']      = $row['is_arb'] > 0 ? $row['cost'] : ($row['cost'] - ($row['cost'] * 0.05));
                    $row['sum_commision'] = $row['is_arb'] > 0 ? "" : ($row['cost'] * 0.05);
                }
                $row['sum_dept']      = $row['cost'];
                $ret[] = $row;
                $i++;
            }
        }
        return $ret;    
    }
    
    /**
     * Формирует .xls данные о деньгах работодателей по текущим незакрытым сделкам за указанный период.
     * @param array $filter   фильтр по дате.
     */
    function printRevisionReport($filter = NULL) {
        if(!$filter['to'])
            $filter['to'] = array('day'=>date('d'), 'month'=>date('n'), 'year'=>date('Y'));
        
        if(!($rep = $this->getRevisionReport($filter)))
            return false;

        $period = $this->_createPeriodStr($filter);
        $rpss = self::$reports_ss['REV'];

        $COL_START = 0;

        require_once 'Spreadsheet/Excel/Writer.php';
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->send('СБР_сверка_'.str_replace('.', '_', $period[0]).'.xls');
        
        $body_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Bold'=>1);
        $td_sty = array('FontFamily'=>'Calibri', 'VAlign'=>'vequal_space', 'Align'=>'center', 'Size'=>11, 'Border'=>1, 'BorderColor'=>'black', 'NumFormat'=>'#');
        $th_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black', 'Bold'=>1);
        $l_sty = array('Align'=>'left');
        $r_sty = array('Align'=>'right', 'NumFormat'=>'### ### ##0.00');

        $fmtBODY = &$workbook->addFormat($body_sty);
        $fmtBODYR = &$workbook->addFormat($body_sty + $r_sty);
        $fmtTD = &$workbook->addFormat($td_sty);
        $fmtTH = &$workbook->addFormat($th_sty);
        $fmtTH->setTextWrap(1);

        $fmtTDL = &$workbook->addFormat($l_sty + $td_sty);
        $fmtTDR = &$workbook->addFormat($r_sty + $td_sty);

        $worksheet = $workbook->addWorksheet('Сверка');
        $worksheet->setInputEncoding('windows-1251');
        $worksheet->setZoom(75);
        $worksheet->setColumn(0,0, 2);

        // Заголовок
        $worksheet->write(0, $COL_START, $rpss['name'], $fmtBODY);
        $worksheet->write(1, $COL_START, "Остатки кредиторской задолженности по сервису «Безопасная Сделка»", $fmtBODY);
        $worksheet->write(2, $COL_START, $period[0], $fmtBODY);
        $worksheet->write(2, 5, "Дата ".date('d.m.Y H:i'), $fmtBODY);
        $worksheet->mergeCells(0, $COL_START, 0, 6);


        // Шапка таблицы.
        $i=$COL_START;
        foreach($rpss['columns'] as $f=>$col) {
            $worksheet->setColumn($i, $i, $col[1][0]);
            $worksheet->write(4, $i, $col[0], $fmtTH);
            $i++;
        }
        
        // Таблица.
        $i=5;
        $sum_all = 0;
        foreach($rep as $row) {
            $j=$COL_START;
            $sum_all += $row['sum_dept'];
            foreach($rpss['columns'] as $nm=>$col) {
                $worksheet->write($i, $j, htmlspecialchars_decode($row[$nm],ENT_QUOTES), $col[1][1]=='right' ? $fmtTDR : ($col[1][1]=='left' ? $fmtTDL : $fmtTD));
                $j++;
            }
            $i++;
        }
        
        // Дно таблицы (итого).
        $worksheet->write($i, 0, 'ИТОГО', $fmtBODYR);
        $worksheet->mergeCells($i, 0, $i, 2);
        $c1 = Spreadsheet_Excel_Writer::rowcolToCell(5, 3);
	    $c2 = Spreadsheet_Excel_Writer::rowcolToCell($i-1, 3);
	    $worksheet->writeFormula($i, 3, "=SUM({$c1}:{$c2})", $fmtSR);
	    
	    $c1 = Spreadsheet_Excel_Writer::rowcolToCell(5, 4);
	    $c2 = Spreadsheet_Excel_Writer::rowcolToCell($i-1, 4);
	    $worksheet->writeFormula($i, 4, "=SUM({$c1}:{$c2})", $fmtSR);
	    
	    $c1 = Spreadsheet_Excel_Writer::rowcolToCell(5, 5);
	    $c2 = Spreadsheet_Excel_Writer::rowcolToCell($i-1, 5);
	    $worksheet->writeFormula($i, 5, "=SUM({$c1}:{$c2})", $fmtSR);
	    
	    $i += 3;
	    $r = explode("." , (string)$sum_all);
	    $worksheet->write($i, 0, "На {$filter['to']['day']}.".($filter['to']['month']<10?"0".$filter['to']['month']:$filter['to']['month']).".{$filter['to']['year']} задолженность ООО \"Ваан\" в пользу Работодателей  по сервису «Безопасная Сделка»", $fmtBODY);
	    $worksheet->write($i+1, 0, "Cоставляет ".intval($r[0])." руб. ".intval($r[1])." коп.", $fmtBODY);
	    
        $workbook->close();
    }
    /**
     * Устанавливает удалена или нет запись
     * 
     * @param  string $suid stage_id . '_'  .user_id
     * @return bool новое состояние флага
     */
    function setRemoved($suid) {
        $sql = "UPDATE sbr_stages_users SET is_removed = CASE WHEN is_removed = FALSE THEN TRUE ELSE FALSE END  WHERE stage_id||'_'||user_id IN ('{$suid}') RETURNING is_removed";
        if(($res=pg_query(self::connect(), $sql)) && pg_num_rows($res))
            return pg_fetch_assoc($res);
        return NULL;
    }
    
    
    
    /**
     * Формирует .xls отчет по выплатам ЯД за указанный период.
     * @param array $filter   фильтр по дате.
     */
    function printYdReport($filter = NULL) {
        if(!$filter['to'])
            $filter['to'] = array('day'=>date('d'), 'month'=>date('n'), 'year'=>date('Y'));
        
        $period = $this->_createPeriodStr($filter);
        $period1 = str_replace('.', '_', $period[0]);
        
        $filter['fromdate'] = implode('-', array(
            $filter['from']['year'], 
            $filter['from']['month'], 
            $filter['from']['day']
        ));
        
        $filter['todate'] = implode('-', array(
            $filter['to']['year'], 
            $filter['to']['month'], 
            $filter['to']['day']
        ));
        $rep = $this->getYdReport($filter);
        
        $rpss = self::$reports_ss['YD_REPORT'];

        $COL_START = 0;

        require_once 'Spreadsheet/Excel/Writer.php';
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);
        $workbook->send("СБР_Выплаты_ЯД_{$period1}.xls");
        
        $body_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Bold'=>1);
        $td_sty = array('FontFamily'=>'Calibri', 'VAlign'=>'vequal_space', 'Align'=>'center', 'Size'=>11, 'Border'=>1, 'BorderColor'=>'black', 'NumFormat'=>'#');
        $th_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black', 'Bold'=>1);
        $l_sty = array('Align'=>'left');
        $r_sty = array('Align'=>'right', 'NumFormat'=>'### ### ##0.00');

        $fmtBODY = &$workbook->addFormat($body_sty);
        $fmtTD = &$workbook->addFormat($td_sty);
        $fmtTH = &$workbook->addFormat($th_sty);

        $fmtTDL = &$workbook->addFormat($l_sty + $td_sty);
        $fmtTDR = &$workbook->addFormat($r_sty + $td_sty);

        $worksheet = $workbook->addWorksheet('Выплаты ЯД');
        $worksheet->setInputEncoding('windows-1251');
        $worksheet->setZoom(75);
        $worksheet->setColumn(0,0, 2);

        // Заголовок
        $worksheet->write(0, $COL_START, $rpss['name'] . ' ' . $period[0] , $fmtBODY);
        $worksheet->mergeCells(0, $COL_START, 0, 5);


        // Шапка таблицы.
        $i=$COL_START;
        foreach($rpss['columns'] as $f=>$col) {
            $worksheet->setColumn($i, $i, $col[1][0]);
            $worksheet->write(2, $i, $col[0], $fmtTH);
            $i++;
        }
        
        // Таблица.
        $i=3;
        foreach($rep as $k => $row) {
            $row['num'] = $k+1;
            $j=$COL_START;
            foreach($rpss['columns'] as $nm=>$col) {
                if ($nm == 'pdate') {
                    $row[$nm] = date('d.m.Y H:i', strtotime($row[$nm]));
                }
                $worksheet->write($i, $j, htmlspecialchars_decode($row[$nm],ENT_QUOTES), $col[1][1]=='right' ? $fmtTDR : ($col[1][1]=='left' ? $fmtTDL : $fmtTD));
                $j++;
            }
            $i++;
        }

        $workbook->close();
        
    }

    
    /**
     * Данные для отчета по выплатам ЯД
     *
     * @param array $filter   фильтр по дате.
     * @return array
     */
    function getYdReport($filter) {
        $DB = new DB('master');
        
        $sql = "select sp.completed as pdate, 
                        sp.credit_sum as summ, 
                        coalesce(yt.dstacnt_nr, sr._1_el_yd) as recp,
                'Выплата по договору СБР-'||s.id||'-'||CASE s.scheme_type WHEN 1 THEN 'А' WHEN 4 THEN 'Б' ELSE 'П' END||'/О '||sr._1_fio||' ['||u.login||']' as descr,
                CASE WHEN yp.id IS NULL THEN 'Ч' ELSE 'Б' END as type
                from sbr_stages_payouts sp
                inner join sbr_stages ss on ss.id = sp.stage_id
                inner join sbr s on s.id = ss.sbr_id
                inner join users u on u.uid = sp.user_id
                left join sbr_reqv_history sr on sr.user_id = sp.user_id and sr.history_type = 1 and sr.stage_id = sp.stage_id 
                left join yd_payments yp
                inner join yd_trs yt
                    on yt.id = yp.ltr_id
                    on yp.src_type = 1
                    and yp.src_id = sp.id
                where sp.completed >= ? and sp.completed < ?
                and sp.credit_sys = 4 and s.scheme_type <> 4 
                order by sp.completed;";
        
        $res = $DB->rows($sql, $filter['fromdate'], $filter['todate']);
        
        return $res;
    }
    
    /**
     * Возврат денежных средств 
     * 
     * @global type $DB
     * @param type $payment_id  ИД операции в paymaster
     * @return boolean 
     */
    public function refund($payment_id = null, $stage = null, $debug = false) {
        global $DB;
        $log = new log('pmpay/refundPayments-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        $log->writeln("payment_id = [{$payment_id}], stage = [{$stage}], debug = [{$debug}]");
        if(!$payment_id) return false;
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/pmpay.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/exrates.php';
        $pmpay = new pmpay();
        
        // Возврат осуществляется только при резервировании через WMR
        $sql = "SELECT * FROM sbr_stages_payouts WHERE stage_id = ?i AND user_id = ?i AND is_refund IS NULL;";
        $row = $DB->row($sql, $stage->id, $stage->sbr->emp_id); // Возврат осуществляется только для работодателей
        if($row) {
            if(DEBUG) {
                $log->writeln("debug_mode = ON");
                $pmpay->setDebugUrl($GLOBALS['host'].'/norisk2/admin/pm-server-test.php');
            }  
            $operation = (array) $pmpay->refundPayments($payment_id, $row['credit_sum']);
            if($operation && $operation['Status'] != 'FAILURE' && $operation['ErrorCode'] == 0) {
                if($operation['Status'] == 'EXECUTING' || $operation['Status'] == 'PENDING') {
                    $update = array('is_refund' => false, 'refund_id' => $operation['RefundID']);
                    $this->refundStatusUpdate($update, $row['id']);
                } elseif($operation['Status'] == 'SUCCESS') {
                    $update = array('is_refund' => true, 'refund_id' => $operation['RefundID'], 'completed' => 'NOW()');
                    $this->refundStatusUpdate($update, $row['id']);
                } else {
                    $update = array('is_refund' => null, 'refund_id' => $operation['RefundID']);
                    $this->refundStatusUpdate($update, $row['id']);
                }
            }
        } else {
            $log = new log('pmpay/refundPayments-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
            $log->writeln("Ошибка выдачи SQL -- [{$DB->sql}].");
        }
    }
    
    /**
     * Обновляем статус возврата денег
     * 
     * @global type $DB
     * @param type $update  Данные для обновления
     * @param type $id      ИД операции
     * @return type 
     */
    public function refundStatusUpdate($update, $id) {
        global $DB;
        return $DB->update('sbr_stages_payouts', $update, 'id = ?i', $id); // Обновляем плату
    }
    
    
    /**
     * Выбирает список всех записей, загруженных из 1с
     * 
     * @param type $filter
     * @return type
     */
    public function getInvoices ($filter = null) {
        $db = new DB('master');
        
        $where = ' WHERE TRUE ';
        $where .= $filter['f_sbr'] ? ' AND ' . $db->parse('s.id = ?i', $filter['f_sbr']) : '';
        $where .= $filter['f_login'] ? ' AND ' . $db->parse('rr.login = ?', $filter['f_login']) : '';
        $where .= $filter['f_akkr'] ? ' AND ' . $db->parse('rr.lc_id = ?i', $filter['f_akkr']) : '';
        $where .= $filter['f_actdate_pg'] ? " AND '" . $filter['f_actdate_pg'] . " 00:00:00' <= rr.actdate AND rr.actdate <= '" . $filter['f_actdate_pg'] . " 23:59:59'" : '';
        $where .= $filter['f_invdate_pg'] ? " AND '" . $filter['f_invdate_pg'] . " 00:00:00' <= rr.invdate AND rr.invdate <= '" . $filter['f_invdate_pg'] . " 23:59:59'" : '';
        $where .= $filter['f_sum'] ? ' AND ' . $db->parse('rr.sum = ?', $filter['f_sum']) : '';
        
        if (in_array($filter['f_status'], array_keys(sbr_adm::$invoice_state)) && $filter['f_status'] !== null) {
            $where .= ' AND ' . $db->parse('rr.status = ?', $filter['f_status']) ;
        }
        
        $orders = array (
            'sbr'       => 's.id',
            'login'     => 'rr.login',
            'akkr'      => 'rr.lc_id',
            'actdate'   => 'rr.actdate',
            'invdate'   => 'rr.invdate',
            'sum'       => 'rr.sum',
            'status'    => 'rr.status'
        );
        $orderBy = ' ORDER BY ' . $orders[$filter['f_orderby']] . ($filter['f_desc'] ? ' DESC' : '');
        
        $offset = $filter['f_offset'] ? ' OFFSET ' . (int)$filter['f_offset'] . ' ' : '';
        $limit = $filter['f_limit'] ? ' LIMIT ' . (int)$filter['f_limit'] . ' ' : '';
        
        $sql = "SELECT rr.*, s.id sbr_id
                FROM pskb_invoice_raw rr
                LEFT JOIN pskb_lc lc ON lc.lc_id = rr.lc_id
                LEFT JOIN sbr s ON s.id = lc.sbr_id
                " . $where . $orderBy . $limit . $offset;
        
        return $db->rows($sql);
    }
    
    /**
     * возвращает количество страниц в разделе /siteadmin/norisk2/?site=invoices
     * учитывается фильтр (такой же как в getInvoices())
     * @global type $DB
     * @param type $filter
     * @return type
     */
    public function getInvoicesPagesCount ($filter) {
        global $DB;
        $pageSize = self::INVOICES_PAGE_SIZE;
        
        $where = ' WHERE TRUE ';
        $where .= $filter['f_sbr'] ? ' AND ' . $DB->parse('s.id = ?i', $filter['f_sbr']) : '';
        $where .= $filter['f_login'] ? ' AND ' . $DB->parse('rr.login = ?', $filter['f_login']) : '';
        $where .= $filter['f_akkr'] ? ' AND ' . $DB->parse('rr.lc_id = ?i', $filter['f_akkr']) : '';
        $where .= $filter['f_actdate_pg'] ? " AND '" . $filter['f_actdate_pg'] . " 00:00:00' <= rr.actdate AND rr.actdate <= '" . $filter['f_actdate_pg'] . " 23:59:59'" : '';
        $where .= $filter['f_invdate_pg'] ? " AND '" . $filter['f_invdate_pg'] . " 00:00:00' <= rr.invdate AND rr.invdate <= '" . $filter['f_invdate_pg'] . " 23:59:59'" : '';
        $where .= $filter['f_sum'] ? ' AND ' . $DB->parse('rr.sum = ?', $filter['f_sum']) : '';
        
        if (in_array($filter['f_status'], array_keys(sbr_adm::$invoice_state)) && $filter['f_status'] !== null) {
            $where .= ' AND ' . $DB->parse('rr.status = ?', $filter['f_status']) ;
        }
        
        $sql = "SELECT count(*)
                FROM pskb_invoice_raw rr
                LEFT JOIN pskb_lc lc ON lc.lc_id = rr.lc_id
                LEFT JOIN sbr s ON s.id = lc.sbr_id
                " . $where;
        $invoices = $DB->val($sql);
        $pages = ceil($invoices / $pageSize);
        return $pages;
    }
    
    /**
     * Обработка данных, загруженных из 1С
     */
    public static function processInvoiceData() {
        $db = new DB('master');
        $sql = "SELECT rr.*, s.id sbr_id, s.emp_id, (u.uid = s.emp_id)::int is_emp
                FROM pskb_invoice_raw rr 
                INNER JOIN users u ON u.login = rr.login
                INNER JOIN pskb_lc lc ON lc.lc_id = rr.lc_id
                INNER JOIN sbr s ON s.id = lc.sbr_id
                WHERE rr.status = 0
                ORDER BY id LIMIT 50";
        
        $rows = $db->rows($sql);
        
        foreach ($rows as $row) {
            $err = '';
            $params = array(
                'status' => 1,
                'err' => ''
            );
            $db->update('pskb_invoice_raw', array('status' => 99), 'lc_id = ? AND login = ? AND status != ? AND actnum = ? AND invnum = ?', 
                $row['lc_id'], $row['login'], 1, $row['actnum'], $row['invnum']);
            if (!self::addInvoice($row, $err)) {
                $params = array(
                    'status' => 2,
                );
            }
            if ($err) {
                $params['err'] = $err;
            }
            $db->update('pskb_invoice_raw', $params, 'lc_id = ? AND login = ? AND status != ? AND actnum = ? AND invnum = ?', 
                $row['lc_id'], $row['login'], 1, $row['actnum'], $row['invnum']);
        }
    }
    
    /**
     * Формирование актов и счет-фактур по данным из pskb_invoice_raw
     * Загрузка документов в сделку
     * 
     * @param type $data        массив с данными - строка результата запроса из sbr_adm::processInvoiceData
     * @param type $error
     * @return boolean
     */
    public static  function addInvoice ($data, &$error = '') {        
        $row = $data;
        
        if (!$row) {
            $error = 'Не найден аккредитив';
            return false;
        }
        
        if (!trim($row['addr'])) {
            $error = 'Не указан юрадрес';
//            return false;
        }
        
        $sbr = sbr_meta::getInstanceLocal($row['emp_id']);
        $sbr->initFromId($row['sbr_id'], false, false, false);
        
        
        require_once ($_SERVER['DOCUMENT_ROOT'].'/classes/odt2pdf.php');
        require_once (dirname(__FILE__).'/num_to_word.php');
        
        /**
         * Акт на сумму комиссии ВААН
         */
        $replace = array(
            'USER_NAME' => $row['name'],
            'ACT_SUM'   => number_format($row['sum'], 2, ',', ''),
            'NDS_SUM'   => number_format($row['sum'] - ($row['sum']/1.18), 2, ',', ''),
            'NO_NDS_SUM'   => number_format($row['sum']/1.18, 2, ',', ''),
            'ACT_NUM'   => intval($row['actnum']),
            'DOC_NUM'   => $sbr->getContractNum(),
            'SUM_STR'   => num2strEx(floatval($row['sum'])),
            'ACT_DATE'  => date('d.m.Y', strtotime($row['actdate']))
        );
        $tpl = 'pskb_close_act.ods';
        $pdf = new odt2pdf($tpl);
        $pdf->convert($replace);
        if(!($file = $sbr->_saveDocFile($pdf->Output(NULL, 'S')))) {
            $error = 'Ошибка при формировании Акта';
            return false;
        }
        
        $docs[] = array(
            'file_id'       => $file->id, 
            'status'        => sbr::DOCS_STATUS_PUBL, 
            'access_role'   => ($row['is_emp'] ? sbr::DOCS_ACCESS_EMP : sbr::DOCS_ACCESS_FRL),
            'owner_role'    => 0, 
            'type'          => sbr::DOCS_TYPE_PSKB_ACT
        );
        
        /**
         * Счет-фактура
         */
        $replace = array(
            'USER_NAME' => $row['name'],
            'USER_ADDR' => $row['addr'],
            'USER_INN' => $row['inn'] . ($row['kpp'] ? '/' . $row['kpp'] : ''),
            'INV_SUM'   => number_format($row['sum'], 2, ',', ''),
            'NDS_SUM'   => number_format($row['sum'] - ($row['sum']/1.18), 2, ',', ''),
            'NO_NDS_SUM'   => number_format($row['sum']/1.18, 2, ',', ''),
            'INV_NUM'   => intval($row['invnum']),
            'DOC_NUM'   => $sbr->getContractNum(),
            'INV_DATE'  => date('d.m.Y', strtotime($row['invdate']))
        );
        $tpl = 'pskb_close_invoice.ods';
        $pdf = new odt2pdf($tpl);
        $pdf->convert($replace);
        if(!($file = $sbr->_saveDocFile($pdf->Output(NULL, 'S')))) {
            $error = 'Ошибка при формировании счета-фактуры';
            return false;
        }
        
        $docs[] = array(
            'file_id'       => $file->id, 
            'status'        => sbr::DOCS_STATUS_PUBL, 
            'access_role'   => ($row['is_emp'] ? sbr::DOCS_ACCESS_EMP : sbr::DOCS_ACCESS_FRL),
            'owner_role'    => 0, 
            'type'          => sbr::DOCS_TYPE_FACTURA
        );
        
        foreach($docs as $doc) {
            $ok = $sbr->addDocR($doc);
        }
        
        return true;
    }
    
    /**
     * Парсит файл выгрузки из 1С, для последующей загрузки документов
     * 
     * @param type $file
     * @return boolean
     */
    public static function parseInvoiceData ($file) {
        $db = new DB('master');
        
        if (!file_exists($file)) {
            return false;
        }
        
        $list = array();
        
        $f = fopen($file, 'r');
        $c = 0;
        
        while (!feof($f)) {
            $row = fgets($f);
            $data = explode(';', $row);
            $data = array_map('trim', $data);
            
            if (!$data[0]) {
                continue;
            }
            
            $data[6] = preg_replace('/[\s\xc2\xa0]/si', '', $data[6]);
            $data[6] = str_replace(',', '.', $data[6]);
            $data[6] = floatval($data[6]);
            
            $sql = "SELECT 
                    s.id,
                    CASE WHEN u.uid = s.frl_id THEN lc.\"namePerf\" ELSE lc.\"nameCust\" END as name,
                    CASE WHEN u.uid = s.frl_id THEN lc.\"innPerf\" ELSE lc.\"innCust\" END as inn,
                    CASE WHEN u.uid = s.frl_id AND lc.\"alienPerf\" = 1 THEN r._2_address_fct 
                         WHEN u.uid = s.emp_id AND lc.\"alienCust\" = 1 THEN r._2_address_fct
                         ELSE r._2_address_jry 
                    END as addr,
                    r._2_kpp kpp,
                    (u.uid = s.emp_id)::int is_emp,
                    i.lc_id is_exists,
                    CASE WHEN u.uid = s.frl_id THEN lc.\"tagPerf\" ELSE lc.\"tagCust\" END as tag
                FROM pskb_lc lc 
                INNER JOIN sbr s ON s.id = lc.sbr_id
                INNER JOIN users u ON u.login = ? AND u.uid IN (s.frl_id, s.emp_id)
                LEFT JOIN sbr_reqv r ON r.user_id = u.uid 
                LEFT JOIN pskb_invoice_raw i ON i.lc_id = lc.lc_id AND i.login = u.login AND i.actnum = ? AND i.invnum = ?
                WHERE lc.lc_id = ?";
            $row = $db->row($sql, $data[0], $data[2], $data[3], $data[1]);
        
            $params = array(
                'login' =>      $data[0],
                'lc_id' =>      $data[1],
                'actnum' =>     $data[2],
                'invnum' =>     $data[3],
                'actdate' =>    date('c', strtotime($data[4])),
                'invdate' =>    date('c', strtotime($data[5])),
                'sum' =>        $data[6],
                'name' =>       $row['name'],
                'addr' =>       $row['addr'],
                'inn' =>        $row['inn'],
                'kpp' =>        $row['kpp'],
                'status' =>     0,
                'err' =>        '',
            );
            
            if (!$row) {
                if ($db->row("SELECT * FROM pskb_invoice_raw WHERE lc_id = ? AND login = ? AND actnum = ? AND invnum = ?", $params['lc_id'], $params['login'], $params['actnum'], $params['invnum'])) {
                    continue;
                }
                $params['status'] = 2;
                $params['err'] = 'Не найден аккредитив';
                $res = $db->insert('pskb_invoice_raw', $params);
                continue;
            }
            
            if ($row['tag'] != 1) {
                continue;
            }
            
            if (!$row['is_exists']) {
                $res = $db->insert('pskb_invoice_raw', $params);
            } else {
                $res = $db->update('pskb_invoice_raw', $params, 'lc_id = ? AND login = ? AND status != ? AND status != ? AND actnum = ? AND invnum = ?', 
                    $params['lc_id'], $params['login'], 1, 99, $params['actnum'], $params['invnum']);
            }
        }
    }
}

/**
 * Класс для работы с СБР со стороны админа СБР (только просмотр данных).
 */
class sbr_adm_finance extends sbr_adm {

    /**
     * Возвращает данные о комиссиях и сторонах СБР в CSV
     *
     * @param     string    $date_s    Дата начала периода
     * @param     string    $date_e    Дата окончания периода
     * @return    string               Файл с данными в CSV
     */
    function exportSBRDataToCSV($date_s, $date_e) {
        global $DB;
        if(!$date_s) {
            $date_s = '1970-01-01';
        }
        if(!$date_e) {
            $date_e = (date('Y')+1).'-01-01';
        }

        $pskb_commissions = array();
        $sql = "SELECT * FROM pskb_lc_commission";
        $commissions = $DB->rows($sql);
        if($commissions) {
            foreach($commissions as $v) {
                $pskb_commissions[$v['lc_id']]['bank'] = $v['bank'];
                $pskb_commissions[$v['lc_id']]['fl'] = $v['fl'];
            }
        }

        $data_csv = '';
        /*
        $sql = "SELECT 
                    'emp' AS u_type, COALESCE(pskb_lc.date_fix_reestr, pskb_lc.bank_covered) AS lc_date, u.login, s.cost AS sum, NULL as frl_percent, pskb_lc.lc_id, 
                    pskb_lc.sbr_id, pskb_lc.ps_emp AS ps, pskb_lc.state, pskb_lc.\"tagCust\", pskb_lc.\"tagPerf\", pskb_lc.\"nameCust\", pskb_lc.\"namePerf\", 
                    pskb_lc.\"innCust\", pskb_lc.\"innPerf\", s_r.*, pskb_lc.sum as lc_sum   
                FROM account_operations AS a_o 
                INNER JOIN sbr AS s ON s.reserved_id = a_o.id 
                INNER JOIN pskb_lc ON pskb_lc.sbr_id = s.id AND pskb_lc.lc_id > 126
                INNER JOIN users AS u ON u.uid = s.emp_id 
                INNER JOIN sbr_reqv AS s_r ON s_r.user_id = s.emp_id 
                WHERE a_o.op_code IN (77) AND COALESCE(pskb_lc.date_fix_reestr, pskb_lc.bank_covered)::date >= ? AND COALESCE(pskb_lc.date_fix_reestr, pskb_lc.bank_covered)::date <= ?

                UNION ALL

                SELECT 
                    'frl' AS u_type, COALESCE(s_s_p.date_fix_reestr, s_s_p.bank_completed) AS lc_date, u.login, s_s.cost AS sum, s_s_a.frl_percent, pskb_lc.lc_id, 
                    pskb_lc.sbr_id, s_s_p.credit_sys AS ps, s_s_p.state, pskb_lc.\"tagCust\", pskb_lc.\"tagPerf\", pskb_lc.\"nameCust\", pskb_lc.\"namePerf\", 
                    pskb_lc.\"innCust\", pskb_lc.\"innPerf\", s_r.*, pskb_lc.sum as lc_sum  
                FROM account_operations AS a_o 
                INNER JOIN sbr_stages_payouts AS s_s_p ON s_s_p.credit_id = a_o.id 
                INNER JOIN sbr_stages AS s_s ON s_s.id = s_s_p.stage_id 
                LEFT JOIN sbr_stages_arbitrage AS s_s_a ON s_s_a.stage_id = s_s.id 
                INNER JOIN sbr AS s ON s.id = s_s.sbr_id AND s_s_p.user_id = s.frl_id
                INNER JOIN pskb_lc ON pskb_lc.sbr_id = s_s.sbr_id AND pskb_lc.lc_id > 126
                INNER JOIN users AS u ON u.uid = s_s_p.user_id 
                INNER JOIN sbr_reqv AS s_r ON s_r.user_id = s_s_p.user_id 
                WHERE a_o.op_code IN (79) AND COALESCE(s_s_p.date_fix_reestr, s_s_p.bank_completed)::date < '2013-03-07' AND COALESCE(s_s_p.date_fix_reestr, s_s_p.bank_completed)::date >= ? AND COALESCE(s_s_p.date_fix_reestr, s_s_p.bank_completed)::date <= ?";
        */
        $sql = "SELECT
                    bb.u_type, 
                    bb.lc_date, 
                    u.login, 
                    bb.sum, 
                    bb.lc_id, 
                    pskb_lc.sbr_id, 
                    bb.ps, 
                    bb.state,
                    (CASE WHEN bb.u_type = 'emp' THEN 
                    sbr_calctax( sbr_taxes_id( sbr_exrates_map(pskb_lc.ps_emp), null, null, bb.scheme, bb.sum ),bb.scheme, bb.sum, (pskb_lc.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(pskb_lc.ps_emp), null, null)
                    ELSE
                    sbr_calctax( sbr_taxes_id( 
                            sbr_exrates_map( 
                                CASE WHEN (pskb_lc.\"tagPerf\" <> 1 AND bb.sum < 15000 AND bb.state = 'END') OR 
                                ( bb.lc_id IN(578,714,1057,1257,1344,1600,1600,1748,1795,2215,2234,2336,2573,2727,2833,3042,3134,3250,3502,3527,3599,3964,4224,4326,4333,4828,5331,5389,5631,5693,5778,5778,6167,6342,6730) AND
                                  bb.state = 'ERR' )
                                THEN 199 ELSE pskb_lc.ps_frl END 
                            ), 
                        0, null, bb.scheme, bb.sum ),bb.scheme, bb.sum, (pskb_lc.\"tagPerf\" + 1), 1, 1, 
                            sbr_exrates_map(
                            CASE WHEN (pskb_lc.\"tagPerf\" <> 1 AND bb.sum < 15000 AND bb.state = 'END') OR 
                            ( bb.lc_id IN(578,714,1057,1257,1344,1600,1600,1748,1795,2215,2234,2336,2573,2727,2833,3042,3134,3250,3502,3527,3599,3964,4224,4326,4333,4828,5331,5389,5631,5693,5778,5778,6167,6342,6730) AND
                              bb.state = 'ERR' )
                            THEN 199 ELSE pskb_lc.ps_frl END
                            ), null, null) 
                    END ) as fl_tax,
                    pskb_lc.\"tagCust\", 
                    pskb_lc.\"tagPerf\", 
                    pskb_lc.\"nameCust\", 
                    pskb_lc.\"namePerf\", 
                    pskb_lc.\"innCust\", 
                    pskb_lc.\"innPerf\", 
                    s_r.*, 
                    pskb_lc.sum AS lc_sum
                FROM (
                    SELECT
                        s.scheme_id as scheme,
                        lc_id, 'emp' AS u_type, 
                        s.emp_id _uid, 
                        SUM(s.cost) sum,
                        MIN (COALESCE(pskb_lc.date_fix_reestr, pskb_lc.bank_covered)) AS lc_date,
                        MIN (pskb_lc.ps_emp) AS ps,
                        MIN(pskb_lc.state) as state
                    FROM account_operations AS a_o
                    INNER JOIN sbr AS s ON s.reserved_id = a_o.id
                    INNER JOIN pskb_lc ON pskb_lc.sbr_id = s.id AND pskb_lc.lc_id > 126
                    WHERE a_o.op_code IN (77) AND COALESCE(pskb_lc.date_fix_reestr, pskb_lc.bank_covered)::date >= ? AND COALESCE(pskb_lc.date_fix_reestr, pskb_lc.bank_covered)::date <= ?
                    GROUP BY lc_id, s.emp_id, s.scheme_id

                    UNION ALL

                    SELECT
                        s.scheme_id as scheme,
                        lc_id, 'frl' AS u_type, 
                        s_s_p.user_id _uid, 
                        SUM( CASE WHEN s_s_a.frl_percent > 0 THEN s_s.cost * s_s_a.frl_percent ELSE s_s.cost END ) sum,
                        MIN(COALESCE(s_s_p.date_fix_reestr, s_s_p.bank_completed)) as lc_date,
                        MIN(s_s_p.credit_sys) AS ps,
                        MIN(s_s_p.state) as state
                    FROM account_operations AS a_o
                    INNER JOIN sbr_stages_payouts AS s_s_p ON s_s_p.credit_id = a_o.id
                    INNER JOIN sbr_stages AS s_s ON s_s.id = s_s_p.stage_id
                    LEFT JOIN sbr_stages_arbitrage AS s_s_a ON s_s_a.stage_id = s_s.id
                    INNER JOIN sbr AS s ON s.id = s_s.sbr_id AND s_s_p.user_id = s.frl_id
                    INNER JOIN pskb_lc ON pskb_lc.sbr_id = s_s.sbr_id AND pskb_lc.lc_id > 126
                    WHERE a_o.op_code IN (79) AND COALESCE(s_s_p.date_fix_reestr, s_s_p.bank_completed)::date < '2013-03-07' AND COALESCE(s_s_p.date_fix_reestr, s_s_p.bank_completed)::date >= ? AND COALESCE(s_s_p.date_fix_reestr, s_s_p.bank_completed)::date <= ?
                    GROUP BY lc_id, s_s_p.user_id, s.scheme_id
                ) bb
                INNER JOIN users AS u ON u.uid = _uid
                INNER JOIN pskb_lc ON pskb_lc.lc_id = bb.lc_id
                INNER JOIN sbr_reqv AS s_r ON s_r.user_id = _uid";

        $qres = $DB->query($sql, $date_s, $date_e, $date_s, $date_e);
        if($qres) {
            if(pg_num_rows($qres)) {
                //$taxes = sbr::getTaxes(21);
                /*
                $res2 = array();
                while($item = pg_fetch_array($qres)) {
                    if(!$res2[$item['lc_id'].'-'.$item['user_id']]) {
                        if($item['frl_percent']>0) { 
                            $item['sum'] = $item['sum']*$item['frl_percent'];
                        }
                        $res2[$item['lc_id'].'-'.$item['user_id']] = $item;
                    } else {
                        if($item['frl_percent']>0) { 
                            $res2[$item['lc_id'].'-'.$item['user_id']]['sum'] = $res2[$item['lc_id'].'-'.$item['user_id']]['sum'] + $item['sum']*$item['frl_percent'];
                        } else {
                            $res2[$item['lc_id'].'-'.$item['user_id']]['sum'] = $res2[$item['lc_id'].'-'.$item['user_id']]['sum'] + $item['sum'];
                        }
                    }
                }
                */
                $file_name = "/tmp/".uniqid("sbrcsvdata");
                $f = fopen($file_name, "w");
                while($item = pg_fetch_array($qres)) {
                    $ps_sys = $item['ps'];
                    switch($item['u_type']) {
                        case 'emp':
                            $f_type = ($item['tagCust']==1 ? 'Юридическое лицо' : 'Физическое лицо');
                            $f_name = htmlspecialchars_decode($item['nameCust']);
                            $f_inn = $item['innCust'];
                            $item['form_type'] = ($item['tagCust']==1 ? 2 : 1);
                            break;
                        case 'frl':
                            $f_type = ($item['tagPerf']==1 ? 'Юридическое лицо' : 'Физическое лицо');
                            $f_name = htmlspecialchars_decode($item['namePerf']);
                            $f_inn = $item['innPerf'];
                            $item['form_type'] = ($item['tagPerf']==1 ? 2 : 1);
                            if($item['tagPerf']!=1 && $item['sum']<=15000 && $item['state']==pskb::PAYOUT_END) {
                                $item['ps'] = exrates::WEBM;
                                $ps_sys     = pskb::WW;
                            }
                            break;
                    }
                    if(!$item['sum']) continue;
                    //$f_name = ($item['form_type']==1 ? $item['_1_fio'] : $item['_2_full_name']);
                    //$f_inn = ($item['form_type']==1 ? $item['_1_inn'] : $item['_2_inn']);
                    $f_address = htmlspecialchars_decode(($item['form_type']==1 ?
                                   "{$item['_1_index']}, {$item['_1_country']}, {$item['_1_city']}, {$item['_1_address']}"
                                    :
                                   "{$item['_2_index']}, {$item['_2_country']}, {$item['_2_city']}, {$item['_2_address']}"
                                 ));
                    //$f_type = ($item['form_type']==1 ? 'Физическое лицо' : 'Юридическое лицо');
                    $f_u_type = ($item['u_type']=='emp' ? 'Работодатель' : 'Исполнитель');
                    $f_commission_our = 0;
                    $f_commission_bank = 0;
                    if ( $item['u_type'] == 'emp' ) {
                        $ps_sys     = $item['ps'];
                        $item['ps'] = pskb::$exrates_map[$item['ps']];
                    } else {
                        $tt = array(578,714,1057,1257,1344,1600,1600,1748,1795,2215,2234,2336,2573,2727,2833,3042,3134,3250,3502,3527,3599,3964,4224,4326,4333,4828,5331,5389,5631,5693,5778,5778,6167,6342,6730);
                        if(in_array($item['lc_id'],$tt) && $item['state'] == pskb::PAYOUT_ERR) {
                            $item['ps'] = exrates::WEBM;
                            $ps_sys     = pskb::WW;
                        }
                        /*
                        if ( $item['state'] == pskb::PAYOUT_ERR ) {
                            $item['ps'] = exrates::WEBM;
                        }
                        */
                    }

                    $f_date = dateFormat("d.m.Y H.i.s", $item['lc_date']);
                    $f_id = $item['lc_id'];
                    $f_sbr = $item['sbr_id'];
                    $f_login = $item['login'];

                    if($item['u_type']=='emp' && in_array($f_id, array_keys($pskb_commissions)) && is_release()) {
                        $f_commission_bank = $pskb_commissions[$f_id]['bank'];
                        $f_commission_our = $pskb_commissions[$f_id]['fl'];
                    } else {
                        $f_all = $item['lc_sum'] - $item['sum'];
                        $f_commission_bank = round( ($f_all - $item['fl_tax']), 2);
                        $f_commission_our  = round( $item['fl_tax'], 2);
                        $f_ps = pskb::$psys[$item['u_type'] == 'emp' ? pskb::USER_EMP : pskb::USER_FRL ][$ps_sys];
                        /*switch($item['ps']) {
                            case exrates::WMR:
                                $f_ps = 'WebMoney';
                                switch($item['u_type']) {
                                    case 'emp':
                                        $f_tax = $taxes[1][28]['percent'] + $taxes[1][29]['percent'];
                                        //$f_all = $item['sum']*$f_tax;
                                        $f_all = $item['lc_sum'] - $item['sum'];
                                        $f_commission_bank = round(($item['sum']*$taxes[1][29]['percent']), 2);
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[1][28]['percent'];
                                        //$f_commission_bank = $item['sum']*$taxes[1][29]['percent'];
                                        break;
                                    case 'frl':
                                        $f_tax = $taxes[0][19]['percent'] + $taxes[0][23]['percent'];
                                        $f_all = $item['sum']*$f_tax;
                                        $f_commission_bank = round(($item['sum']*$taxes[0][23]['percent']), 2);
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[0][19]['percent'];
                                        //$f_commission_bank = $item['sum']*$taxes[0][23]['percent'];
                                        break;
                                }
                                break;
                            case exrates::YM:
                                $f_ps = 'ЯндексДеньги';
                                switch($item['u_type']) {
                                    case 'emp':
                                        $f_tax = $taxes[1][26]['percent'] + $taxes[1][27]['percent'];
                                        //$f_all = $item['sum']*$f_tax;
                                        $f_all = $item['lc_sum'] - $item['sum'];
                                        $f_commission_bank = round(($item['sum']*$taxes[1][27]['percent']), 2);
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[1][26]['percent'];
                                        //$f_commission_bank = $item['sum']*$taxes[1][27]['percent'];
                                        break;
                                    case 'frl':
                                        $f_tax = $taxes[0][18]['percent'] + $taxes[0][22]['percent'];
                                        $f_all = $item['sum']*$f_tax;
                                        $f_commission_bank = round(($item['sum']*$taxes[0][22]['percent']), 2);
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[0][18]['percent'];
                                        //$f_commission_bank = $item['sum']*$taxes[0][22]['percent'];
                                        break;
                                }
                                break;
                            case exrates::CARD:
                                $f_ps = 'Пластиковая карта';
                                switch($item['u_type']) {
                                    case 'emp':
                                        $f_tax = $taxes[1][32]['percent'] + $taxes[1][33]['percent'];
                                        //$f_all = $item['sum']*$f_tax;
                                        $f_all = $item['lc_sum'] - $item['sum'];
                                        $f_commission_bank = round(($item['sum']*$taxes[1][33]['percent']), 2);
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[1][32]['percent'];
                                        //$f_commission_bank = $item['sum']*$taxes[1][33]['percent'];
                                        break;
                                    case 'frl':
                                        $f_commission_our = 0;
                                        $f_commission_bank = 0;
                                        break;
                                }
                                break;
                            case exrates::BANK:
                                $f_ps = 'Безнал';
                                switch($item['u_type']) {
                                    case 'emp':
                                        $f_tax = $taxes[1][30]['percent'] + $taxes[1][31]['percent'];
                                        //$f_all = $item['sum']*$f_tax;
                                        $f_all = $item['lc_sum'] - $item['sum'];
                                        $f_commission_bank = round(($item['sum']*$taxes[1][31]['percent']), 2);
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[1][30]['percent'];
                                        //$f_commission_bank = $item['sum']*$taxes[1][31]['percent'];
                                        break;
                                    case 'frl':
                                        $f_tax = $taxes[0][20]['percent'] + $taxes[0][24]['percent'];
                                        $f_all = $item['sum']*$f_tax;
                                        $f_commission_bank = round(($item['sum']*$taxes[0][24]['percent']), 2);
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[0][20]['percent'];
                                        //$f_commission_bank = $item['sum']*$taxes[0][24]['percent'];
                                        break;
                                }
                                break;
                            case exrates::WEBM:
                                $f_ps = 'Веб кошелек';
                                switch($item['u_type']) {
                                    case 'emp':
                                        $f_tax = $taxes[1][34]['percent'] + $taxes[1][35]['percent'];
                                        //$f_all = $item['sum']*$f_tax;
                                        $f_all = $item['lc_sum'] - $item['sum'];
                                        $f_commission_bank = round(($item['sum']*$taxes[1][35]['percent']), 2);
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[1][34]['percent'];
                                        //$f_commission_bank = $item['sum']*$taxes[1][35]['percent'];
                                        break;
                                    case 'frl':
                                        $f_tax = $taxes[0][21]['percent'];
                                        $f_all = $item['sum']*$f_tax;
                                        $f_commission_bank = 0;
                                        $f_commission_our = round(($f_all-$f_commission_bank), 2);
                                        //$f_commission_our = $item['sum']*$taxes[0][21]['percent'];
                                        //$f_commission_bank = 0;
                                        break;
                                }
                                break;
                        }
                        */
                    }

                    $data_csv = "{$f_name};{$f_inn};{$f_type};{$f_address};{$f_u_type};".number_format($f_commission_our, 2, ',', '').";{$f_ps};".number_format($f_commission_bank, 2, ',', '').";{$f_date};{$f_id};{$f_sbr};{$f_login};\n";
                    fwrite($f, $data_csv);
                }
                fclose($f);
            }
        }
        return $file_name;
    }
}

?>