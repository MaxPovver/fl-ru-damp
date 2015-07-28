<?php
require_once ABS_PATH."/classes/freelancer.php";
require_once ABS_PATH."/classes/projects.php";
require_once ABS_PATH . "/classes/projects_offers.php";

/**
* Класс модель для работы с автоответами на проекты
*/

class autoresponse
{
    static $table = 'autoresponse';
    static $db = null;

    // Настройки
    static $config = array(
        'price' => 10, // цена за один автоответ (руб.)
        'default_quantity' => 10, // количество автоответов по умолчанию для заказа
    );

    public $data = array();

    public function __construct($data = array())
    {
        $this->data = $data;
    }

    public function toBoolean($value)
    {
        return $value == 't';
    }

    public function isActive()
    {
        return (isset($this->data['active']) && $this->toBoolean($this->data['active']));
    }

    /**
    * Активация текущего автоответа
    */
    public function activate()
    {
        $res = false;

        if (isset($this->data['id']) && $this->data['id']) {
            $sql = 'UPDATE autoresponse SET active = TRUE WHERE id='.$this->data['id'];

            $res = self::$db->query($sql);
        }

        return $res;
    }

    /**
    * Уменьшаем количество автоответов на единицу и записываем в лог добавление автоответа
    */
    public function reduce($user, $offer, $project_id)
    {
        global $DB;
        $res = false;

        if (isset($this->data['id']) && $this->data['id']) {
            $sql = sprintf("INSERT INTO autoresponse_log (user_id, autoresponse_id, project_id, offer_id) 
                    VALUES (%d, %d, %d, %d)",
                    $user->uid, $this->data['id'], $project_id, $offer->offer_id
            );
            $res = $DB->query($sql);

            if ($res) {
                $sql = 'UPDATE autoresponse SET remained = remained - 1 WHERE id = ?i AND remained > 0';
                $res = $DB->query($sql, $this->data['id']);
            }
        }

        return $res;
    }
    
    /**
     * Увеличиваем количество автоответов на единицу
     * 
     * @global type $DB
     * @param type $id ИД автоответов
     * @return bool TRUE, если успешно
     */    
    public function increase($id)
    {
        global $DB;
        $res = false;
        
        if ($id > 0) {
            $sql = 'UPDATE autoresponse SET remained = remained + 1 WHERE id = ?i';
            $res = $DB->query($sql, $id);
        }

        return $res;
    }
    
    /**
     * Уменьщает количество автоответов для всех предложений проекта
     * @param type $project_id
     * @return type
     */
    public function reduceByProject($project_id)
    {
        $projects_offers = new projects_offers();
        $offers = $projects_offers->getPrjOffersLite($project_id);
        
        if (!is_array($offers) || !count($offers)) {
            return;
        }
        
        $user = new freelancer();
        foreach ($offers as $offer) {
            if ($offer['auto'] > 0) {
                $this->data['id'] = $offer['auto'];
                $user->uid = $offer['user_id'];
                $projects_offers->offer_id = $offer['id'];
                $this->reduce($user, $projects_offers, $project_id);
            } 
        }
    }
    
    /**
     * Увеличивает количество автоответов для всех предложений проекта
     * @param type $project_id
     * @return type
     */
    public function increaseByProject($project_id)
    {
        $projects_offers = new projects_offers();
        $offers = $projects_offers->getPrjOffersLite($project_id);

        if (!is_array($offers) || !count($offers)) {
            return;
        }
        
        foreach ($offers as $offer) {
            if ($offer['auto'] > 0) {
                $this->increase($offer['auto']);
            } 
        }
    }
    
    /**
    * Создание нового автоответа на проект
    * @param array $data Массив с параметрами для создания автоответа
    */
    static function create($data)
    {
        global $DB;
        $table = self::$table;
        $autoresponse = null;
        
        $price = intval($data['total']) * self::$config['price'];

        $sql = "INSERT INTO $table 
            (user_id, descr, only_4_cust, 
            filter_budget, filter_budget_currency, filter_budget_priceby, 
            filter_category_id, filter_subcategory_id, price, total, remained, is_pro) 
            VALUES (?i, ?, ?, ?i, ?i, ?i, ?i, ?i, ?i, ?i, ?i, ?);
            SELECT currval('autoresponse_id_seq');
        ";
        $id = $DB->val($sql, 
            $data['user_id'], 
            $data['descr'],
            isset($data['only_4_cust']) && $data['only_4_cust']?'t':'f', 
            intval($data['filter_budget']),
            intval($data['filter_budget_currency']),
            intval($data['filter_budget_priceby']),
            intval($data['filter_category_id']),
            intval($data['filter_subcategory_id']),
            $price,
            intval($data['total']),
            intval($data['total']),
            $data['is_pro']?'t':'f'
        );

        if (!$DB->error && $id) {
            $data['id'] = $id;
            $data['price'] = $price;
            $autoresponse = new autoresponse($data);
        }

        return $autoresponse;        
    }

    /**
    * Извлечение одного автоответа
    *
    * @param int $id Идентификатор автоответа
    * @return Object autoresponse
    */
    static function get($id)
    {
        $autoresponse = null;

        $table = self::$table;

        if ($row = self::$db->row("SELECT * FROM {$table} WHERE id=? ", $id)) {
            $autoresponse = new autoresponse($row);
        }

        return $autoresponse;
    }

    /**
    * Извлечение купленых автоответов для пользователя
    *
    * @param int $user_id Идентификатор пользователя
    * @return array(autoresponse a1, ... autoresponse an)|array()
    */
    static function findForUser($user_id)
    {
        $list = array();

        if ($user_id) {
            $table = self::$table;

            $rows = self::$db->rows("SELECT * FROM {$table} WHERE user_id=? AND active=true ORDER BY payed_date DESC",
                $user_id
            );

            foreach ($rows as $row) {
                $list[] = new autoresponse($row);
            }
        }
        return $list;
    }

    /**
    * Извлечение автоответов которые соответсвуют критериям проекта
    *
    * @param project $project Проект (объект класса project)
    * @return array (autoresponse a1, autoresponse a2, ... autoresponse a1)
    */
    static function getListForProject($project)
    {
        global $DB;
        $list = array();

        // Специализация проекта
        $sql_spec = '';
        $specs = projects::getProjectCategories($project['id']);
        foreach ($specs as $spec) {
            if ($sql_spec) {
                $sql_spec .= ' OR ';
            }
            $sql_spec .= sprintf(" 
                (filter_category_id = %d AND filter_subcategory_id = %d) 
                OR 
                (filter_category_id = %d AND filter_subcategory_id = 0)",
                $spec['category_id'], $spec['subcategory_id'], $spec['category_id']
            );
        }

        // Запрос на извлечение автоответов, подходящих к выбранному проекту
        $sql = sprintf("SELECT DISTINCT ON (user_id) user_id, id, post_date, descr, only_4_cust 
                FROM autoresponse 
                WHERE 
                    active = 't' AND remained > 0 
                    AND (filter_budget = 0 OR 
                            (filter_budget <= %d AND filter_budget_currency = %d AND filter_budget_priceby = %d)
                    )
                    AND ($sql_spec) 
                ORDER BY user_id, post_date", 
                intval($project['cost']), $project['currency'], $project['priceby']
        );

        if ($res = $DB->rows($sql)) {
            foreach ($res as $data) {
                $freelancer = new freelancer();
                $freelancer->GetUserByUID($data['user_id']);

                if (!$freelancer->uid) {
                    continue;
                }

                $data['freelancer'] = $freelancer;
                $data['contacts_freelancer'] = array(
                    'phone' => array(
                        'name' => 'Телефон',
                        'value' => $freelancer->phone
                    ),
                    'site' => array(
                        'name' => 'Сайт',
                        'value' => $freelancer->site
                    ),
                    'icq' => array(
                        'name' => 'ICQ',
                        'value' => $freelancer->icq
                    ),
                    'skype' => array(
                        'name' => 'Skype',
                        'value' => $freelancer->skype
                    ),
                    'email' => array(
                        'name' => 'E-mail',
                        'value' => $freelancer->second_email
                    )
                );

                $list[] = new autoresponse($data);
            }
        }

        return $list;
    }
}