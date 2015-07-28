<?php

/**
 * Класс для работы с комментариями к действиям админов
 */
require_once 'Comments.php';

class CommentsAdminLog extends TComments {
    
    public $enableRating = false;
    
    /**
     * Шаблон адреса страницы с комментариями
     * 
     * @var string
     */
    public $urlTemplate = '';
    
    /**
     * Отправлять уведомления об удалении комментария.
     * в уведомлении используется urlTemplate
     * 
     * @var bool
     */
    public $sendDeleteWarn = false;
    
    /**
     * Конфиг данных для комментариев сервиса.
     * 
     * @return array
     */
    public function model() {
        return array(
            // комментарии
            'comments' => array(
                'table'  => 'admin_log_comments',
                'fields' => array(
                    'id'            => 'id',
                    'resource'      => 'log_id',
                    'author'        => 'from_id',
                    'parent_id'     => 'reply_to',
                    'msgtext'       => 'msgtext',
                    'yt'            => 'yt_link',
                    'created_time'  => 'post_time',
                    'modified'      => 'modified_id',
                    'modified_time' => 'modified',
                    'deleted'       => 'deluser_id',
                    'deleted_time'  => 'deleted',
                    'rating'        => null,
                )
            ),
            // файлы, если аттачи в отдельной таблице
            'attaches' => array(
                'file_table' => 'file',
                'table'      => 'admin_log_comments_files',
                'fields'     => array(
                    'comment' => 'comment_id',
                    'file'    => 'file_id',
                )
            )
        );
    }
}
