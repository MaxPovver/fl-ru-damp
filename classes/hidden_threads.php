<?php

class hidden_threads {

    /**
     * ћассив с информацией о куках и таблице,
     * в которых хран€тс€ идентификаторы свернутых ветвей комментов
     * 
     * @var array
     */
    public static $hiddenThreadsDbConfig = array(
        'articleThreads' => array(              //им€ куки
            'table_name' => 'articles_users',   //им€ таблицы с пользовательскими данными
            'hidden_field' => 'hidden_threads', //им€ таблицы с массивом идентификаторов скрытых комментов
            'user_field' => 'user_id',          //им€ таблицы с ид пользовател€
            'id_field' => 'article_id',         //им€ таблицы с ид статьи
        ),
//        'blogsThreads' => array(
//            
//        )
    );


    /**
     * ѕровер€ет в куках наличие идентификаторов свернутых веток комментов.
     *
     * @param  array $cookies массив с куками
     * @return bool true - успех, false - провал
     */
    public static function checkCommentsThreads($cookies) {
        session_start();
        global $DB;
        $uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : NULL;
//        var_dump($cookies);
        if(!$uid) return FALSE;

        foreach(self::$hiddenThreadsDbConfig as $cookie_name => $config) {
            if (isset($cookies[$cookie_name])) {
                $articles = json_decode(stripslashes($cookies[$cookie_name]), 1);
                if (!$articles)
                    return FALSE;

                if (is_array($articles)) {
                    foreach ($articles as $id => $hidden) {
                        $sql = "SELECT {$config['hidden_field']} FROM {$config['table_name']}
                                    WHERE {$config['id_field']} = ?i AND {$config['user_field']} = ?i";
                        $val = $DB->val($sql, $id, $uid);
                        if ( $DB->error )
                            return FALSE;
                        $hidden_db = $val;
                        $hidden_db = preg_replace('/[\{\}]/', '', $hidden_db);

                        $hidden_db = strlen(trim($hidden_db)) != 0 ? explode(',', $hidden_db) : array();
                        $hidden = explode(',', $hidden);

                        $changed = 0;

                        if(!in_array('hide', $hidden) && !in_array('show', $hidden)) {
                            foreach ($hidden as $tid) {
                                if (in_array(intval($tid), $hidden_db)) {
                                    unset($hidden_db[array_search(intval($tid), $hidden_db)]);
                                    $changed = 1;
                                } else {
                                    $hidden_db[] = intval($tid);
                                    $changed = 1;
                                }
                            }
                        } else {

                            if(in_array('show', $hidden) && count($hidden_db)) {
                                $hidden_db = array();
                                unset($hidden[array_search('show', $hidden)]);
                                $changed = 1;
                            }
                            if (in_array('hide', $hidden) && 
                                (!in_array(-1, $hidden_db) || (in_array(-1, $hidden_db) && count($hidden_db))) ) {
                                $hidden_db = array();
                                $hidden_db[] = -1;
                                unset($hidden[array_search('hide', $hidden)]);
                                $changed = 1;
                            }

                            if(count($hidden)) {
                                foreach ($hidden as $tid) {
                                    if(!intval($tid)) continue;
                                    $hidden_db[] = intval($tid);
                                    $changed = 1;
                                }
                            }
                        }

                        if ($changed) {
                            //на вс€кий случай
                            foreach ($hidden_db as $k => $v) {
                                if(!$v) unset($hidden_db[$k]);
                            }
                            
                            $hidden_str = count($hidden_db) ? "ARRAY[" . implode(',', $hidden_db) . "]" : "NULL";

                            $sql = "UPDATE {$config['table_name']} SET {$config['hidden_field']} = {$hidden_str}
                                        WHERE {$config['id_field']} = ?i AND {$config['user_field']} = ?i";

                            if (!$DB->query($sql, $id, $uid)) {
                                return FALSE;
                            }
                        }
                        
                        setcookie($cookie_name, null, time() - 100);
                    }
                }
            }
        }


        return TRUE;
    }

}
?>
