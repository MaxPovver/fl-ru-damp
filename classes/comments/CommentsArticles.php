<?php

/**
 * Description of CommentsArticles
 *
 * @author sergey
 */
require_once ABS_PATH . '/classes/comments/Comments.php';
require_once ABS_PATH . '/classes/user_content.php';

class CommentsArticles extends TComments {

    public $enableRating = false;
    
    /**
     * Шаблон адреса страницы с комментариями
     * 
     * @var string
     */
    public $urlTemplate = 'http://{host}/articles/{resource}/#c_{id}';
    
    /**
     * Отправлять уведомления об удалении комментария.
     * в уведомлении используется urlTemplate
     * 
     * @var bool
     */
    public $sendDeleteWarn = true;
    
    /**
     * Переключаемся на новый визивиг или нет
     * @var type 
     */
    public $enableNewWysiwyg = true;
    
    public $configNewWywiwyg = '/scripts/ckedit/config_nocut.js';
    
    /**
     * Конфиг данных для комментариев сервиса.
     * Пример для статей.
     *
     * @return array
     */
    public function model() {
        return array(
            // комментарии
            'comments' => array(
                'table' => 'articles_comments',
                'fields' => array(
                    'id' => 'id',
                    'resource' => 'article_id',
                    'author' => 'from_id',
                    'parent_id' => 'parent_id',
                    'msgtext' => 'msgtext',
                    'yt' => 'youtube_link',
                    'created_time' => 'created_time',
                    'modified' => 'modified_id',
                    'modified_time' => 'modified_time',
                    'deleted_time' => 'modified_time',
                    'deleted' => 'deleted_id',
                    'reason' => 'deleted_reason',
                    'rating' => null,
                    'moderator_status' => 'moderator_status'
                )
            ),
            // файлы, если аттачи в отдельной таблице
            'attaches' => array(
                'file_table' => 'file',
                'table' => 'articles_comments_files',
                'fields' => array(
                    'comment' => 'comment_id',
                    'file'    => 'file_id',
                    'inline'  => 'inline',
                    'temp'    => 'temp'
                )
            ),
            'moderation_rec_type' => user_content::MODER_ART_COM, 
            'moderation_sort_order' => 3, 
            'permissions' => (hasPermissions('articles') || hasPermissions('comments'))
        );
    }

}

