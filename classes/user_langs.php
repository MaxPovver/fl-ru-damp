<?php
/**
* Класс для работы со справочником языков 
**/
class user_langs {
    /**
     * Получить языки из справочника
     * @return  mixed bool|array
     */
    static public function getLanguages() {
        global $DB;
        $rows = $DB->cache(3600)->rows("SELECT id, name FROM languages ORDER BY name");
        return $rows;
    }
    /**
     * Сохранить языки пользователя
     * @paramv uint  $uid        - идентификатор пользователя
     * @paramv array $user_langs - массив ассоциативных массивов, где каждый элемент:
     *  Array ( 'id'=> uint      - идентификатор языка из таблицы languages,
     *          'quality'=> uint - степень знания языка ( 1 - начальный, 2 - средний, 3 - продвинутывй, 4 - родной )
     *  )
     */
    static public function updateUserLangs($uid, $user_langs) {
        $uid = (int)$uid;
        if ( $uid ) {
            global $DB;
            $DB->query("DELETE FROM user_langs WHERE uid = {$uid}");
            $values = array();
            foreach ( $user_langs as $lang ) {
                $values[] = "({$lang['id']}, {$uid}, {$lang['quality']})";
            }
            if ( count($values) ) {
                $values = join(",", $values);
                $DB->query("INSERT INTO user_langs (lang_id, uid, quality) VALUES $values");
            }
        }
    }
}
?>
