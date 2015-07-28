<?php
/**
 * подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");

class dav_file_upload {
    /**
     * Максимальное количество файлов
     */
    const MAX_FILE_COUNT    = 1;
    
    /**
     * Максимальные размер вложения файла
     */
    const MAX_FILE_SIZE     = 15728640;
    
    /**
     * Таблица для хранения данных о файлах
     */
    const FILE_TABLE       = "file";
    
    /**
     * Сколько показывать документов на странице
     */
    const RECORDS_PER_PAGE  = 12;
    
    /**
     * Сколько показывать элементов в строке постраничной навигации
     */
    const MAX_ITEMS_IN_PAGING_LINE  = 8;
    
    
    /**
     * Таблица для хранения данных о документах
     */
    const TABLE     = "replace_file_log";
    
    /**
     * Вставка записи
     * @param $fid            идентификатор файла
     * @param $file_name      имя файла
     * @param $old_file_name  имя файла, который был переименован при замене вновь загруженым
     * @return int идентификатор записи
     **/
    static public function addRecord( $fid, $file_name, $old_file_name) {
        global $DB;
        return $DB->insert(self::TABLE, array( "filename"=>$file_name, "fid"=>$fid, "old_file_name" => $old_file_name, "ip" => getRemoteIP(), "admin_id" => get_uid( false ) ), "id");
    }
}
