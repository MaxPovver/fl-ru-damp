<?php
define('IS_PGQ', 1);
require_once dirname(__FILE__) . '/../stdf.php';
$db_conf = $GLOBALS['pg_db'];

define('SYSDAEMON', (stripos($_SERVER['OS'], 'WINDOWS')!==false ? 'Win' : '').'SystemDaemon');
define('DEBUG_DAEMON', 0);
define("PGQ_DB_CONN", "host=".$db_conf['master']['host']." port=".$db_conf['master']['port']." dbname=".$db_conf['master']['name']." user=".$db_conf['master']['user']." password=".$db_conf['master']['pwd']);

require_once ABS_PATH.'/classes/pgq/api/PGQConsumer.php';
require_once ABS_PATH.'/classes/CFile.php';
require_once ABS_PATH."/classes/stdf.php";
require_once ABS_PATH."/classes/payed.php";
require_once ABS_PATH."/classes/projects.php";
require_once ABS_PATH."/classes/autoresponse.php";

$config["LOGLEVEL"] = NOTICE;
$config["LOGFILE"]  = ABS_PATH . '/classes/pgq/logs/autoresponse.pgq';
$config["DELAY"]    = 5;

class PGQDaemonAutoresponse extends PGQConsumer {
    private $_mainDb = NULL;
        
    public function config() {
        global $config;
        if($this->log !== null) {
            $this->log->notice("Reloading configuration (HUP)");
        }
        $this->loglevel = $config["LOGLEVEL"];
        $this->logfile  = $config["LOGFILE"];
        $this->delay    = $config["DELAY"];
    }

    protected function force_connect() 
    {
        global $DB;

        if (!$this->_mainDb) {
            $this->_mainDb = $DB->connect(TRUE);
        }

        return $this->_mainDb;
    }

    public function process_event(&$event) {
        global $DB;

        $this->force_connect();

        $r = FALSE;

        switch ($event->type) {

            case 'ProjectPosted': {
                $project_id = $event->data['id'];

                $this->log->notice("New project posted #id = " . $project_id);

                $obj_project = new projects();
                $project = $obj_project->GetPrjCust($project_id);
                
                // Не выбран испольнитель (если испольнитель выбран, то не пишем ответ на этот проект)
                if ($project && $project['exec_id'] == 0 && $project['kind'] == 1) {
                    $autoresponses = autoresponse::getListForProject($project);
                    
                    foreach ($autoresponses as $autoresponse) {
                        $freelancer = $autoresponse->data['freelancer'];
                        $contacts_freelancer = $autoresponse->data['contacts_freelancer'];

                        // Проверяем если проект только для про, то и пользователь который на него отвечает должен быть ПРО
                        if ($project['pro_only'] == 't' && !payed::CheckPro($freelancer->login)) {
                            continue;
                        }

                        // Проверяем если проект только для верифицированных, то и пользователь который на него отвечает должен быть верифицирован
                        if ($project['verify_only'] == 't' && !$freelancer->IsVerified()) {
                            continue;
                        }

                        // Проверка, что текущий пользователь не является владельцем проекта
                        if ($project['user_id'] == $freelancer->uid) {
                            continue;
                        }

                        // Добавление нового отзыва к проекту
                        $obj_offer = new projects_offers();
                        $save_contacts = serialize($contacts_freelancer);

                        $DB->start();
                        $error_offer = $obj_offer->AddOffer(
                            $freelancer->uid, 
                            $project['id'], 
                            '', '', '', // цена (от, до, тип)
                            '', '', '', // время (от, до, тип)
                            antispam(stripslashes($autoresponse->data['descr'])), // текст автоответа
                            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', // прикрепленные работы 
                            $autoresponse->toBoolean($autoresponse->data['only_4_cust']), // видимость только для заказчика
                            0, 
                            0, 
                            false, // sbr
                            false, // is_color 
                            $save_contacts, 
                            0, // $payed_items
                            $autoresponse->data['id']
                        );

                        // В случае добавление автоответа, уменьшаем счетчик автоответов для пользователя (в транзакции)
                        if ($error_offer || !$autoresponse->reduce($freelancer, $obj_offer, $project_id)) {
                            $this->log->notice("Rollback autoresponse posted for project #id = " . $project_id);
                            $DB->rollback();
                        }
                        else {
                            $obj_project->incrementViews($project_id);
                            $this->log->notice(
                                sprintf("New autoresponse #%d posted for project #%d", $obj_offer->offer_id, $project_id)
                            );
                            $DB->commit();
                        }
                    }
                }

                break;
            }
        }

        return PGQ_EVENT_OK;

    }
}

$daemon = new PGQDaemonAutoresponse("autoresponse", "mail_simple", $argc, $argv, PGQ_DB_CONN);
