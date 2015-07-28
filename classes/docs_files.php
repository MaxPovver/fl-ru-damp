<?

/**
 * подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 *
 * Класс для работы с файлами
 *
 */
class docs_files {

    /**
     * Взять файлы для определенного документа
     * $param integer $docs_id - ИД документа
     * @return array Данные выборки
     */
    public static function getDocsFiles($docs_id) {
        global $DB;
        $sql = 'SELECT D.*, F.id AS cfile_id, F.size, F.path, F.modified, F.fname
        FROM docs_files D
        INNER JOIN file F ON (D.file_id = F.id)
        WHERE D.docs_id = ?i
        ORDER BY D.sort';
        
        $data = $DB->rows( $sql, (int)$docs_id );
        
        if ($data){
            foreach ($data as &$file) {
                $file['ico_class'] = getIcoClassByExt($file['fname']);
                $file['file_size'] = sizeFormat($file['size']);
            }
            self::SelectMinMax($data);
        }
        return $data;
    }

    /**
     * Устанавливает какой из прикрепленных файлов первый, а какой последный по индексу сортировки
     * 
     * @param array $data файлы прикрепеленные к документу.
     */
    public static function SelectMinMax(&$data){
        $min = $data[0]['sort'];
        $min_id = 0;
        $max = 0;
        $max_id = 0;
        foreach ($data as $key => $item){
            $item['is_first'] = false;
            $item['is_last'] = false;
            if($min > $item['sort']){
                $min = $item['sort'];
                $min_id = $key;
            }
            if($max < $item['sort']){
                $max = $item['sort'];
                $max_id = $key;
            }
        }
        $data[$min_id]['is_first'] = true;
        $data[$max_id]['is_last'] = true;
    }

    /**
     * Взять определенный файл
     *
     * @param integer $id ИД файла
     * @return array Данные выборки
     */
    public static function getFile($id) {
        global $DB;
        $sql = 'SELECT D.*, F.id AS cfile_id, F.size, F.path, F.modified, F.fname
        FROM docs_files D
        INNER JOIN file F ON (D.file_id = F.id)
        WHERE D.id = ?i';
        
        return $DB->row( $sql, $id );
    }

    /**
     * Добавить новый файл в БД
     *
     * @param integer $docs_id      ИД документа
     * @param string  $file_id      ИД файла из таблицы file
     * @param string  $file_name    Имя файла для вывода
     * @return string Сообщение об ошибке
     */
    public static function Add($docs_id, $file_id, $file_name) {
        global $DB;
        $max  = $DB->val( 'SELECT MAX(sort) as _max FROM docs_files WHERE docs_id = ?i', $docs_id );
        $sort = ($max) ? ($max + 1) : 1;
        $data = compact( 'docs_id', 'file_id', 'file_name', 'sort' );
        
        $DB->insert( 'docs_files', $data );
        
        return $DB->error;
    }

    /**
     * Удалить Файл
     *
     * @param integer $id Ид файла
     * @return string Сообщение об ошибке
     */
    public static function Delete($id) {
        global $DB;
        $fid = $DB->val( "DELETE FROM docs_files WHERE id = ?i RETURNING file_id", $id );
        
        if ( $fid ) {
            $file = new CFile();
            $file->Delete( $fid );
        }
        
        return $DB->error;
    }

    /**
     * Функция меняет позицию файла в сортировке на -1
     *
     * @param integer $id ИД файла
     * @return integer ИД документа (нужно для xajax)
     */
    public static function MoveDown($id) {
        global $DB;
        $curr  = self::getFile($id);
        $sql   = "SELECT id, sort FROM docs_files WHERE docs_id = ?i AND sort = (SELECT MIN(sort) FROM docs_files WHERE docs_id = ?i AND sort > ?i);";
        $donor = $DB->row( $sql, $curr['docs_id'], $curr['docs_id'], $curr['sort'] );
        if ( $donor ) {
            $DB->update( 'docs_files', array('sort' => $curr['sort']), 'id = ?i', $donor['id'] );
            $DB->update( 'docs_files', array('sort' => $donor['sort']), 'id = ?i', $curr['id'] );
            
            return $curr['docs_id'];
        }
        return $curr['docs_id'];
    }

        /**
     * Функция меняет позицию файла в сортировке на +1
     *
     * @param integer $id ИД файла
     * @return integer ИД документа (нужно для xajax)
     */
    public static function MoveUp($id) {
        global $DB;
        $curr  = self::getFile($id);
        $sql   = "SELECT id, sort FROM docs_files WHERE docs_id = ?i AND sort = (SELECT MAX(sort) FROM docs_files WHERE docs_id = ?i AND sort < ?i);";
        $donor = $DB->row( $sql, $curr['docs_id'], $curr['docs_id'], $curr['sort'] );
        if ( $donor ) {
            $DB->update( 'docs_files', array('sort' => $curr['sort']), 'id = ?i', $donor['id'] );
            $DB->update( 'docs_files', array('sort' => $donor['sort']), 'id = ?i', $curr['id'] );
            
            return $curr['docs_id'];
        }
        return $curr['docs_id'];
    }
}