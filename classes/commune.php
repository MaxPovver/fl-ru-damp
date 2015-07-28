<?php
define('TPL_COMMUNE_PATH',realpath($_SERVER['DOCUMENT_ROOT'].'/commune'));
/**
 * Подключаем файл с основными функцифми
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/*
 * Подключаем memBuf
 * */
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/memBuff2.php";
/**
 * Подключаем файл для работы с оплатой
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune_carma.php");

/**
 * Класс для работы с сообщестами
 * Используется просто как пространство имен.
 * 
 */
class commune 
{
    /**
     * ИД Сообщества "Общение" (перенос из блогов)
     */
    const COMMUNE_BLOGS_ID = 5000;
    const COMMUNE2_BLOGS_ID = 5100;
    
    const FILE_TABLE = 'file_commune';
    
    /**
  	 * Максимальное количество вложений в сообщение
  	 *
  	 */
    const MAX_FILES = 10;
    
    /**
	 * Максимально допустимый размер вложенных в сообщение файлов
	 *
	 */
    const MAX_FILE_SIZE = 5242880;

    /**
     * Минимальное значение рейтинга для скрытия скрытия
     */
    const MIN_HIDE_RATING = 15;
    
    /**
  	 * Время в секундах, которое нужно переждать прежде чем создавать еще одно сообщество.
  	 *
  	 */
	const CREATION_INTERVAL = 30;

	/**
	 * максимальное количество сообществ, которое может создать один чел.
	 *
	 */
  	const MAX_COUNT = 5;
  	
  	/**
  	 * время жизни неактивного сообщества в общих списков, дней.
  	 *
  	 */
  	const NOACT_LIFETIME = 30; 
  	
  	/**
  	 * макс. кол-во соообществ на странице.
  	 *
  	 */
  	const MAX_ON_PAGE = 10;
  	
  	/**
  	 * макс. кол-во топ-сообщений на странице.
  	 *
  	 */
  	const MAX_TOP_ON_PAGE = 20;
  	
  	/**
  	 * макс. кол-во членов на странице членов.
  	 *
  	 */
  	const MAX_MEMBERS_ON_PAGE = 30;
  	
  	/**
  	 * макс. кол-во модераторов сообщества.
  	 *
  	 */
  	const MAX_ADMIN_COUNT = 5; 
  
  	/**
  	 * количество закладок на странице сообщества.
  	 *
  	 */
  	const OM_TH_COUNT   = 4;
  	
  	/**
  	 * ид. закладки "Новые" (топы)
  	 *
  	 */
  	const OM_TH_NEW     = 0;
  	
  	/**
  	 * ид. закладки "Популярные" (топы)
  	 *
  	 */
  	const OM_TH_POPULAR = 1; 
  	
  	/**
  	 * ид. закладки "Актуальные" (топы)
  	 *
  	 */
  	const OM_TH_ACTUAL  = 2; 
  	
  	/**
  	 * ид. закладки "Мои" (топы)
  	 *
  	 */
  	const OM_TH_MY      = 3; 

  	/**
  	 * количество закладок на странице раздела.
  	 *
  	 */
  	const OM_CM_COUNT   = 6;
  	
  	/**
  	 * ид. закладки "Лучшие" (сообщества)
  	 *
  	 */
  	const OM_CM_BEST    = 0;
  	
  	/**
  	 * ид. закладки "Популрные" (сообщества)
  	 *
  	 */
  	const OM_CM_POPULAR = 1;
  	
  	/**
  	 * ид. закладки "Актуальные" (сообщества)
  	 *
  	 */
  	const OM_CM_ACTUAL  = 2;
  	
  	/**
  	 * ид. закладки "Новые" (сообщества)
  	 *
  	 */
  	const OM_CM_NEW     = 3;
  	
  	/**
  	 * ид. закладки "Мои" (сообщества)
  	 *
  	 */
  	const OM_CM_MY      = 4;
  	
  	/**
  	 * ид. закладки "Приглашенные" (сообщества)
  	 *
  	 */
  	const OM_CM_JOINED  = 5;
  	
  	/**
  	 * Сортировка в закладке "Я вступил" по дате вступления
  	 */
  	const OM_CM_JOINED_ACCEPTED  = 6;
  	
  	/**
  	 * Сортировка в закладке "Я вступил" по дате создания сообщества
  	 */
  	const OM_CM_JOINED_CREATED   = 7;
  	
  	/**
  	 * Сортировка в закладке "Я вступил" по рейтингу сообщества
  	 */
  	const OM_CM_JOINED_BEST      = 8;
  	
  	/**
  	 * Сортировка в закладке "Я вступил" по дате последней темы в сообществе
  	 */
  	const OM_CM_JOINED_LAST      = 9;
  	
  	/**
  	 * Сортировка "Свой порядок" в закладке "Я вступил"
  	 */
  	const OM_CM_JOINED_MY      = 10;
  	
        /**
         * Максимальны размер для категорий
         */
        const MAX_CATEGORY_NAME_SIZE = 30;
        
  	/**
  	 * флаги отношения юзера к сообществу.
  	 * MOD_COMM_MODERATOR или MOD_COMM_MANAGER или админ, у которого сняты эти обе функции !!! пройтись перепроверить.
  	 */
  	const MOD_COMM_ADMIN      = 0x001;
  	
  	/**
  	 * больше по комментам.
  	 *
  	 */
  	const MOD_COMM_MODERATOR  = 0x002; 
  	
  	/**
  	 * управляет людьми (бан, вступление, удаление)
  	 *
  	 */
  	const MOD_COMM_MANAGER    = 0x004;
  	
  	/**
  	 * командир сообщества.
  	 *
  	 */
  	const MOD_COMM_AUTHOR     = 0x008; 
  	
  	/**
  	 * принят в сообщество.
  	 *
  	 */
  	const MOD_COMM_ACCEPTED   = 0x010;
  	
  	/**
  	 * забанен в сообществе.
  	 *
  	 */
  	const MOD_COMM_BANNED     = 0x020; 
  	
  	/**
  	 * изъявил желание вступить, но модеры еще не приняли решение.
  	 *
  	 */
  	const MOD_COMM_ASKED      = 0x040;
  	
  	/**
  	 * удален. Лучше проверять MOD_COMM_ACCEPTED в связке с другими.
  	 *
  	 */
  	const MOD_COMM_DELETED    = 0x080; 
  	
    /**
     * флаги отношения юзера ко всему сайту.
     * 
     * Администратор
     * 
     */
  	const MOD_ADMIN           = 0x100;
  	
  	/**
  	 * Модератор
  	 *
  	 */
  	const MOD_MODER           = 0x200;
  	
  	/**
  	 * Юзер ПРО
  	 *
  	 */
  	const MOD_PRO             = 0x400;
  	
  	/**
  	 * Работодатель
  	 *
  	 */
  	const MOD_EMPLOYER        = 0x800;
  	
  	/**
  	 * Забаненный юзер
  	 *
  	 */
  	const MOD_BANNED          = 0x1000;

    /**
     * Рабочие флаги, см. как используются в некоторый функциях.
     * 
     */
  	const MEMBER_MODERATOR     = 0x01;
  	const MEMBER_MANAGER       = 0x02;
  	const MEMBER_ADMIN         = 0x04;
  	const MEMBER_SIMPLE        = 0x08;
  	const MEMBER_ANY           = 0x0f;
  	const JOIN_STATUS_NOT      = 0x10;
  	const JOIN_STATUS_ACCEPTED = 0x20;
  	const JOIN_STATUS_ASKED    = 0x40;
  	const JOIN_STATUS_DELETED  = 0x80;

  // 
  	/**
  	 * маски типа сообщества
  	 *
  	 */
  	const RESTRICT_TYPE_SIZE = 2;
  	
  	/**
  	 * закрытое сообщества.
  	 *
  	 */
  	const RESTRICT_JOIN_MASK = 0x02;
  	
  	/**
  	 * читать могут только члены.
  	 *
  	 */
  	const RESTRICT_READ_MASK = 0x01; // 

	/**
	 * Максимальное количество символов в названии группы
	 *
	 */
  	const GROUP_NAME_MAX_LENGTH  = 50;
  	
  	/**
  	 * Максимальное количество символов в описании группы
  	 *
  	 */
  	const GROUP_DESCR_MAX_LENGTH = 1000;
  	
  	/**
  	 * Максимальное количество символов в имени
  	 *
  	 */
  	const NAME_MAX_LENGTH      = 50;
  	
  	/**
  	 * Максимальное количество символов в описании
  	 *
  	 */
  	const DESCR_MAX_LENGTH     = 1000;
  	
  	/**
  	 * Максимальная длинна изображения
  	 *
  	 */
  	const IMAGE_MAX_WIDTH      = 200;
  	
  	/**
  	 * Максимальная высота изображения
  	 *
  	 */
  	const IMAGE_MAX_HEIGHT     = 400;
  	
  	/**
  	 * ДОступные виды изображения
  	 *
  	 */
  	const IMAGE_EXTENSIONS     = 'gif,jpg,jpeg';
  	
  	/**
  	 * Максимальный вес файла
  	 *
  	 */
  	const FILE_MAX_SIZE        = 2097152;
  	
  	/**
  	 * Максимальная длинна изображения в сообщении
  	 *
  	 */
  	const MSG_IMAGE_MAX_WIDTH  = 470;
  	
  	/**
  	 * Максимальная длинна изображения в сообщении
  	 *
  	 */
  	const _MSG_IMAGE_MAX_WIDTH  = 600;
  	
  	/**
  	 * Максимальная высота изображения в сообщении
  	 *
  	 */
  	const MSG_IMAGE_MAX_HEIGHT = 1000;
  	
  	/**
  	 * Максимальный размер изображения в сообщении
  	 *
  	 */
  	const MSG_IMAGE_MAX_SIZE   = 307200;
  	
  	/**
  	 * Максимальное количество символов в заголовке сообщения
  	 *
  	 */
  	const MSG_TITLE_MAX_LENGTH = 120;
  	
  	/**
  	 * Максимальное количество символов в тексте сообщения
  	 *
  	 */
  	const MSG_TEXT_MAX_LENGTH  = 20000;
  	
  	/**
  	 * Максимальный размер вложенного файла в сообщении
  	 *
  	 */
  	const MSG_FILE_MAX_SIZE    = 5242880;
	
  	/**
  	 * Максимальное количество символов записки пользователей
  	 *
  	 */
  	const MEMBER_NOTE_MAX_LENGTH  = 500;
	
  	/**
  	 * Сообщение об ошибке
  	 *
  	 * @var string
  	 */
  	protected $error = NULL;
  
  const POLL_ANSWERS_MAX        = 10;
  const POLL_QUESTION_CHARS_MAX = 256;
  const POLL_ANSWER_CHARS_MAX   = 100;

  
  /**
   * Уровень доступа к теме.
   * Нет доступа.
   *
   */
  const ACL_DENIED   = 0;
  /**
   * Уровень доступа к теме.
   * Возможность чтения темы.
   *
   */
  const ACL_READ     = 1;
  /**
   * Уровень доступа к теме.
   * Возможность комментирования и участие в голосовании.
   *
   */
  const ACL_COMMENTS = 2;
  /**
   * Уровень доступа к теме.
   * Возможность модерирования.
   *
   */
  const ACL_MODER    = 3;
  
    const IS_NEW_WYSIWYG = true;

    /**
     * Возвращает данные необходимые для построения ЧПУ ссылки группы
     *
     * @param     integer    $id    ID группы
     * @return    array             Данные для ЧПУ
     */
    public function getGroupInfoForFriendlyURL($id) {
        global $DB;
        $sql = "SELECT link FROM commune_groups WHERE id=?";
        return $DB->cache(1800)->row($sql, $id);
    }

    /**
     * Возвращает данные необходимые для построения ЧПУ ссылки сообщения
     *
     * @param     integer    $id    ID сообщения
     * @return    array             Данные для ЧПУ
     */
    public function getMsgInfoForFriendlyURL($id) {
        global $DB;
        $sql = "SELECT commune.id as commune_id, commune_messages.title as name, commune.name as commune_name, commune_groups.link as group_link 
                FROM commune_messages 
                INNER JOIN commune_themes on commune_themes.id = commune_messages.theme_id 
                INNER JOIN commune on commune.id = commune_themes.commune_id 
                INNER JOIN commune_groups on commune_groups.id = commune.group_id 
                WHERE commune_messages.id=?i
                ";
        return $DB->cache(1800)->row($sql, $id);
    }

    /**
     * Возвращает данные необходимые для построения ЧПУ ссылки сообщества
     *
     * @param     integer    $id    ID сообщества
     * @return    array             Данные для ЧПУ
     */
    public function getCommuneInfoForFriendlyURL($id) {
        global $DB;
        $sql = "SELECT commune.id, commune.name, commune_groups.link as category_link FROM commune INNER JOIN commune_groups ON commune_groups.id=commune.group_id WHERE commune.id=?";
        return $DB->cache(1800)->row($sql, $id);
    }
    /**
     * Возвращает информацию о сообществе по ID сообщения
     *
     * @param    integer    $msg_id    ID сообщения в сообществе
     * @return   array                 Информация о сообществе
     */
    function getCommuneInfoByMsgID($msg_id) {
      global $DB;
      $sql = "SELECT
                communes.id AS commune_id,
                communes.name AS commune_name, 
                groups.name AS group_name
              FROM commune_messages as msgs
              INNER JOIN commune_themes AS themes ON themes.id = msgs.theme_id
              INNER JOIN commune AS communes ON communes.id = themes.commune_id
              INNER JOIN commune_groups AS groups ON groups.id = communes.group_id
              WHERE msgs.id = ?i";
      return $DB->cache(21600)->row($sql, $msg_id);
    }
    /**
     * Возвращает идентификатор сообщества по ID сообщения
     * @param    integer    $msg_id    ID сообщения в сообществе
     * @return   int                   Идентификатор сообщества
     */
    public static function getCommuneIdByMsgID($msg_id) {
      global $DB;
      $sql = "SELECT themes.commune_id FROM commune_messages as msgs
              INNER JOIN commune_themes AS themes ON themes.id = msgs.theme_id 
              WHERE msgs.id = ?i";
      return $DB->val($sql, $msg_id);
    }
    /**
     * Получает список приатаченных файлов к сообщению
     *
     * @param   integer $msg_id     ID сообщения
     * @param   string  $login      Логин пользователя
     * @return  array               Информация о файлах
     *
     */
    function getAttachedFiles($msg_id) {
        global $DB;
        
        $where = "inline = FALSE";
        $files = CFile::selectFilesBySrc(self::FILE_TABLE, $msg_id, NULL, $where);
        
        $fList = array();
        if($files) {
            foreach($files as $file) {
                $fList[] = $file['id'];
            }
        }
        return $fList;
    }

    /**
     * Связывает файлы загруженные ассинхронно с сообщением
     *
     * @param   array     $files              Список загруженных файлов
     * @param   string    $login              Логин пользователя
     * @param   integer   $msg_id             ID сообщения
     * @param   boolean   $from_draft         Файлы из черновика
     */
    function addAttachedFiles($files, $msg_id, $login, $from_draft=false) {
        global $DB;

        if(!$login) {
            $login = $_SESSION['login'];
        }
        
        $bModeration = false;
        $where = " inline != TRUE";
        $attaches = CFile::selectFilesBySrc(self::FILE_TABLE, $msg_id, NULL, $where);//$DB->rows($sql, $msg_id);
        $old_files = array();
        if($attaches) {
          foreach($attaches as $f) { array_push($old_files, $f['fid']); }
        }
        if($from_draft) {
            $notdeleted_files = array();
            if($files) {
                foreach($files as $f) {
                    if($f['status']==3) { array_push($notdeleted_files, $f['id']); }
                }
            }
            if ($attaches) {
                $cfile = new CFile();
                
                foreach ($attaches as $attach){
                    if(in_array($attach['id'], $notdeleted_files)) { continue; }
                    $cfile->Delete(0, "users/" . substr($login, 0, 2) . "/" . $login . "/upload/", $attach['fname']);
                    //if ($attach['small'] == 't') {
                    //    $cfile->Delete(0, "users/" . substr($login, 0, 2) . "/" . $login . "/upload/", "sm_" . $attach['fname']);
                    //}
                }
            }
        }
        $max_image_size = array('width' => 470, 'height' => 1000, 'less' => 0);
        if($files) {
            $cfile = new CFile();
            $num = 0;
            foreach($files as $file) {
                switch($file['status']) {
                    case 3:
                        $num++;
                        break;
                    case 4:
                        // Удаляем файл
                        $cFile = new CFile($file['id']);
                        $cFile->table = self::FILE_TABLE;
                        if($cFile->id) {
                            $cFile->updateFileParams(array('src_id'=>null), false); // Удаляем связь
                            $cFile->Delete($cFile->id);
                        }
                        break;
                    case 1:
                        $num++;
                        if(in_array($file['id'], $old_files)) {
                          $need_copy = false;
                        } else {
                          $bModeration = true;
                          $need_copy = true;
                        }
                        // Добавляем файл
                        $cFile = new CFile($file['id']);
                        $cFile->proportional = 1;
                        $cFile->table = self::FILE_TABLE;
                        $ext = $cFile->getext();

                        if($need_copy) {
                          $tmp_dir = "users/".substr($login, 0, 2)."/".$login."/upload/";
                          $tmp_name = $cFile->secure_tmpname($tmp_dir, '.'.$ext);
                          $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));
                          $cFile->_remoteCopy($tmp_dir.$tmp_name, true);
                        }
                        if (in_array($ext, $GLOBALS['graf_array']))
                            $is_image = TRUE;
                        else
                            $is_image = FALSE;
                        if ($is_image && $ext != 'swf' && $ext != 'flv') {
                            if ( ($cFile->image_size['width'] > $max_image_size['width'] || $cFile->image_size['height'] > $max_image_size['height']) ) {
                                if($need_copy) {
                                  if ( $cFile->resizeImage($cFile->path.'sm_'.$cFile->name, $max_image_size['width'], $cFile->image_size['height'], 'landscape')) {
                                      $cFile->small = true;
                                  }
                                } else {
                                  $cFile->small = true;
                                }
                            } else {
                                $cFile->small = false;
                            }
                        } else {
                            $cFile->small = false;
                        }
                        
                        $cFile->updateFileParams(array('src_id'=>$msg_id, 'small'=>$cFile->small, 'sort' => $num), false);
                        //$sql = "INSERT INTO commune_attach(cid, fid, small, sort) VALUES(?i, ?i, ?b, ?i)";
                        //$DB->query($sql, $msg_id, $cFile->id, $cFile->small, $num);
                        break;
                }
            }
        }
        
        if ( $bModeration && $login == $_SESSION['login'] && !hasPermissions('communes') && !is_pro() ) {
            // отравляет сообщение на модерирование сразу по факту создания или редактирования
            // появились новые файлы и это правит автор - отправить на модерирование
            /*$sId = $DB->query( 'UPDATE commune_messages SET moderator_status = 0 WHERE id = ?i AND user_id = ?i  RETURNING id', $msg_id, $_SESSION['uid'] );
            
            if ( $sId ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $DB->insert( 'moderation', array('rec_id' => $msg_id, 'rec_type' => user_content::MODER_COMMUNITY) );
            }*/
        }
    }


    /**
    * Возвращает список категорий для сообщества
    *
    * @param    integer     $commune_id     Идентификатор сообщества
    * @param    boolean     $is_for_admin   Получить все категории или только для посетителей
    * @return   array                       Массив с данными категорий
    */
    function getCategories($commune_id, $is_for_admin) {
        global $DB; 
        $sql = "SELECT * FROM commune_categories WHERE commune_id=?i ".(!$is_for_admin?'AND is_only_for_admin=false':'')." ORDER BY position, name ASC";
        return $DB->rows($sql, $commune_id);
    }

    /**
    * Удаление категори в сообществе
    *
    * @param    integer     $category_id    Идентификатор категории
    * @param    integer     $commune_id     Идентификатор сообщества
    */
    function deleteCategory($category_id, $commune_id) {
        global $DB; 
        $sql = "DELETE FROM commune_categories WHERE id=? AND commune_id=?";
        $DB->query($sql, $category_id, $commune_id);
    }

    /**
    * Получить информацию о категори в сообществе
    *
    * @param    integer     $category_id    Идентификатор категории
    * @return   array                       Информация о категории
    */
    function getCategory($category_id) {
        global $DB; 
        $sql = "SELECT * FROM commune_categories WHERE id=?i";
        return $DB->row($sql, $category_id);
    }

    /**
    * Добавление категори в сообщество
    *
    * @param    string      $name               Название категории
    * @param    boolean     $is_only_for_admin  Признак того, что публиковать темы в эту категорию может только админ сообщества
    * @param    integer     $commune_id         Идентификатор сообщества
    */
    function addCategory($name, $is_only_for_admin, $commune_id) {
        global $DB; 
        $name = substr($name,0,30);
        $is_only_for_admin = intval($is_only_for_admin);
        $sql = "INSERT INTO commune_categories(name, is_only_for_admin, commune_id) VALUES('".pg_escape_string($name)."', ".($is_only_for_admin?'true':'false').",{$commune_id})";
        $DB->squery($sql);
    }
    
    /**
     * Проверяем имя категории в сообществе на существование
     * 
     * 
     * @global type $DB
     * @param type $name
     * @param type $commune_id
     * @return type 
     */
    function issetCategory($name, $commune_id) {
        global $DB; 
        $name = mb_strtolower(substr($name,0,30));
        $sql = "SELECT id FROM commune_categories WHERE lower(name) = ? AND commune_id = ?i";
        $id = $DB->val($sql, $name, $commune_id);
        return $id;
    }

    /**
    * Изменение категори в сообществе
    *
    * @param    integer     $id                 Идентификатор категории
    * @param    string      $name               Название категории
    * @param    boolean     $is_only_for_admin  Признак того, что публиковать темы в эту категорию может только админ сообщества
    * @param    integer     $commune_id         Идентификатор сообщества
    */
    function updateCategory($id, $name, $is_only_for_admin, $commune_id) {
        global $DB; 
        $is_only_for_admin = intval($is_only_for_admin);
        $sql = "UPDATE commune_categories SET name='".pg_escape_string($name)."', is_only_for_admin=".($is_only_for_admin?'true':'false')." WHERE id={$id}";
        $DB->squery($sql);
    }

	/**
	 * Создает новый раздел для сообществ.
	 *
	 * @param string $name  Имя раздела
	 * @param string $descr Описание раздела
	 * @return integer 1 - если все прошло успешно, 0 - если ошибка
	 */
  function AddGroup($name, $descr) 
  {
        global $DB; 
		$n_order = 1;
		$sql = "SELECT n_order FROM commune_groups ORDER BY n_order DESC LIMIT 1";
        $n_order = $DB->val($sql);
        if($n_order) {
			  $n_order = $n_order + 1;
    	        $sql = "INSERT INTO commune_groups (name, descr, n_order) VALUES (?, ?, ?i)";
    	        if($DB->query($sql, $name, $descr, $n_order))
    	            return 1;
		}

    return 0;
  }

	/**
	 *  Изменяет параметры раздела.
	 *
	 * @param integer $id      ИД раздела
	 * @param string  $name    Название раздела
	 * @param string  $descr   Описание раздела
	 * @param integer $n_order Номер сортировки
	 * @return integer 1 - если все прошло успешно, 0 - если ошибка
	 */
  function UpdateGroup($id, $name, $descr, $n_order) 
  {
    global $DB; 
    $sql = "UPDATE commune_groups SET name = ?, descr = ?, n_order = ?i WHERE id = ?i";
    if($DB->query($sql, $name, $descr, $n_order, $id))
      return 1;

    return 0;
  }

	/**
	 * Удаляет раздел.
	 * 
	 * @param integer $id Удаляет раздел 
	 * @return integer 1 - если все прошло успешно, 0 - если ошибка
	 */
  function DeleteGroup($id)    
  {
    global $DB; 
    $sql = "DELETE FROM commune_groups WHERE id = ?i";
    if($DB->query($sql, $id))
      return 1;

    return 0;
  }

	/**
	 * Возвращает все группы (разделы) сообществ.
	 *
	 * @return array Данные выборки
	 */
  function GetGroups()            
  {
    global $DB;     
    $sql = "SELECT * FROM commune_groups ORDER BY n_order";
    return $DB->rows($sql);
  }
  
    /**
     * Возвращает информацию о разделе по его id
     *
     * @param  int $sId
     * @return array
     */
    function getGroupById( $sId = '' ) {
        global $DB;
        return $DB->row( 'SELECT * FROM commune_groups WHERE id = ?', $sId );
    }

    /**
     * Определение информацию о группе по seo ссылке
     * 
     * @param  string  $link  имя ссылки
     * @return array          группа
     */
    function getGroupByLink($link) {
        global $DB;
        return $DB->cache(21600)->row( 'SELECT * FROM commune_groups WHERE link = ?', $link );
    }

    /**
     * Определение информации о сообществе по ID
     * 
     * @param  string  $link  имя ссылки
     * @return array          группа
     */
    function getCommuneById($id) {
        global $DB;
        return $DB->cache(21600)->row( 'SELECT * FROM commune WHERE id = ?i', $id );
    }

	/**
	 *  Закладки сообщений (тем) конкретного пользователя.
	 *
	 * @param integer  $user_id    Пользователь
	 * @param ineteger $message_id Служит для проверки, является ли данное сообщение закладкой пользователя.
     * @param string $sort сортировка
     * @param integer $communeID ID сообщества
	 * @return array Возвращает массив индексированный идентификаторами сообщений-закладок
	 */
  function GetFavorites($user_id, $message_id=NULL, $sort="date", $communeID = null) {
    global $DB;     
    switch($sort) {
      case "date": $sql_sort = "date_create DESC"; break;
      case "priority": $sql_sort = "priority DESC"; break;
      case "abc": $sql_sort = "title ASC"; break;
      default: $sql_sort = "date_create DESC"; break;   
    }
    $message_id = $message_id !== null ? __paramValue('int', $message_id) : $message_id;
    if($communeID == null) {
        $communeID = self::getCommuneIDByMessageID($message_id);
    } else {
        $communeID = __paramValue('int', $communeID);
    }
    $commune_users_messages = self::getTableName('commune_users_messages', $communeID, false, true);
    $commune_messages       = self::getTableName('commune_messages', $communeID, false, true);

    $sql_commune_id = $communeID ? $DB->parse(" AND th.commune_id = ?i", $communeID) : "";
    $sql_message_id = $message_id === null ? "" : $DB->parse(" AND um.message_id = ?i", $message_id);
    $sql = "SET join_collapse_limit = 2;
            SELECT um.message_id, th.commune_id, um.priority, COALESCE(um.name_fav, ms.title) as title
              FROM {$commune_users_messages} um
            INNER JOIN
              {$commune_messages} ms
                ON ms.id = um.message_id
            INNER JOIN
              commune_themes th
                ON th.id = ms.theme_id
            LEFT JOIN
              commune_blocked cb
                ON cb.commune_id = th.commune_id
            WHERE um.user_id= ?i
               AND um.is_favorite=true
               AND cb.commune_id IS NULL
               {$sql_commune_id}
               {$sql_message_id}
               ORDER BY {$sql_sort};";

    $um = NULL;
    $res = $DB->rows($sql, $user_id);
    if($res) {
      foreach($res as $row)
        $um[$row['message_id']] = array('title'=>$row['title'], 'commune_id'=>$row['commune_id'], 'priority'=>$row['priority']);
    }

    return $um;
  }

  /**
   * Возвращает закрашенную иконку (путь) по номеру приоритета
   * 
   * @param  string $pr номер приоритета
   * @return string
   */
  public static function getStarByPR($pr){
        switch($pr){
            case '0':
                return '/images/bookmarks/bsg.png';
                break;
            case '1':
                return '/images/bookmarks/bsgr.png';
                break;
            case '2':
                return '/images/bookmarks/bsy.png';
                break;
            case '3':
                return '/images/bookmarks/bsr.png';
                break;
            default:
                return '/images/bookmarks/bsw.png';
                break;
        }
    }
    
  /**
   * Возвращает контурную иконку (путь) по номеру приоритета
   * 
   * @param  string $pr номер приоритета
   * @return string
   */
    public static function getEmptyStarByPR( $pr ) {
        switch ( $pr) {
            case '0':
                return '/images/ico_star_0_empty.gif';
                break;
            case '1':
                return '/images/ico_star_1_empty.gif';
                break;
            case '2':
                return '/images/ico_star_2_empty.gif';
                break;
            case '3':
                return '/images/ico_star_3_empty.gif';
                break;
            default:
                return '/images/ico_star_0_empty.gif';
                break;
        }
    }

	/**
	 * Возвращает массив параметров, определяющих пользователя в конкретном сообществе.
	 *
	 * @param integer $id       ИД Сообщества
	 * @param integer $user_id  ИД пользователя
	 * @return array Значения возвращаются как 1 или 0. Не 't', 'f'.
	 */
  function GetUserCommuneRel($id, $user_id)
  {
    global $DB;
    
    if(!$user_id) {
        return NULL;
    }

    $sql = "SELECT CASE WHEN cm.author_id = ?i THEN 1 ELSE 0 END as is_author,
                   m.is_manager::int,
                   m.is_moderator::int,
                   m.is_admin::int,
                   m.is_banned::int,
                   m.is_accepted::int,
                   m.is_deleted::int,
                   CASE WHEN cb.id > 0 THEN 1 ELSE 0 END as is_blocked_commune,
                   CASE WHEN m.is_accepted=false AND m.is_deleted=false THEN 1
												ELSE 0 END as is_asked
              FROM commune cm
            LEFT JOIN commune_blocked cb
                ON cb.commune_id = cm.id
            LEFT JOIN
              commune_members m
                ON m.commune_id = cm.id
               AND m.user_id = ?i
             WHERE cm.id = ?i
           ";
    return $DB->row($sql, $user_id, $user_id, $id);
  }

  /**
   * Возвращает определенное сообщество.
   *
   * @param integer $id       ИД Сообщества
   * @param integer $user_id  если указан, то вернет дополнительно поле, показывающее какой 
   * 							   статус имеет данный пользователь в данном сообществе: 
   *  							   вступил, подтвержден, удален, не вступал.
   * @param integer $mod      битовая маска пользователя
   * @return array Данные выборки
   */
  function GetCommune($id, $user_id = NULL, $mod = 0)
  {
    global $DB;
    $sel_adm = '';
    $join_adm = '';
    if ($mod & (commune::MOD_ADMIN | commune::MOD_MODER)) {
        $sel_adm  = "admins.login as admin_login, admins.uname as admin_name, admins.usurname as admin_uname, admins.is_verify as admin_is_verify,";
        $join_adm = "LEFT JOIN users AS admins ON cb.admin = admins.uid";
    }
    $sql = "SELECT cm.*,
                   u.uid as author_uid,
                   u.login as author_login,
                   u.photo as author_photo,
                   u.usurname as author_usurname,
                   u.uname as author_uname,
                   u.role as author_role,
                   u.email as author_email,
                   u.subscr as author_subscr,
                   u.is_pro as author_is_pro,
                   u.is_profi as author_is_profi,
                   u.is_verify as author_is_verify,
                   u.is_team as author_is_team,
                   u.is_pro_test as author_is_pro_test,
                  -- p.name as author_prof_name,
                  u.reg_date as author_reg_date,
                   cb.commune_id::boolean as is_blocked,
                   cb.reason as blocked_reason,
                   cb.blocked_time,
                   $sel_adm
                   ".
                   (
                     $user_id === NULL
                     ? ''
                     : " CASE
                           WHEN m.id IS NULL THEN ".self::JOIN_STATUS_NOT."
                           WHEN m.is_deleted THEN ".self::JOIN_STATUS_DELETED."
                           WHEN m.is_accepted THEN ".self::JOIN_STATUS_ACCEPTED."
                           ELSE ".self::JOIN_STATUS_ASKED."
                          END
                          as current_user_join_status,
                          m.is_banned,"
                   )."
                   extract(year from age(cm.created_time::date)) as year, 
                   extract(month from age(cm.created_time::date)) as month, 
                   extract(day from age(cm.created_time::date)) as day,
                   cm.id as note_commune_id,
                   cm.author_id as note_user_id,
                   cmn.note as note_txt
              FROM commune cm
            INNER JOIN
              users u
                ON u.uid = cm.author_id".
            (
              $user_id === NULL
              ? ''
              : " LEFT JOIN
                     commune_members m
                       ON m.user_id = ?i
                      AND m.commune_id = cm.id"
            )."
             LEFT JOIN commune_blocked cb ON cb.commune_id = cm.id
             LEFT JOIN commune_members_notes cmn ON cmn.commune_id = cm.id AND cmn.user_id = cm.author_id

             -- LEFT JOIN freelancer ON freelancer.uid = u.uid
             -- LEFT JOIN professions p ON p.id = freelancer.spec_orig

             $join_adm
             WHERE cm.id = ?i
           ";
    return $user_id ? $DB->row($sql, $user_id, $id) : $DB->row($sql,  $id);
  }

	/**
	 * Возвращает все сообщества
	 *
	 * @param integer $group_id     NULL: из любого раздела, иначе идентификатор группы.
	 * @param integer $author_id    NULL: любого автора, иначе ид. автора.
	 * @param integer $user_id      NULL: может присутствовать любой, иначе ид. члена, который обязан присутствовать в этом сообществе. 
	 * @param integer $mod          права пользователя
	 * @param integer $order_mode   режим сортировки (в соответствии с закладками).
	 * @param integer $cur_user_id  если задан, то будет возвращено поле с флагом, присутствует ли данный пользователь в сообществе или нет.
	 * @param integer $offset       Позиция с какого места брать в БД
	 * @param string  $limit        Лимит выборки 
	 * @param integer $search       для поиска сообщества по названию или описанию 
	 * @param integer $count        если не NULL, то будем выдавать сюда количество всех строк, удовлетворяющих параметрам запроса, так, как если бы в условии не было $limit и $offset.
	 * @param integer $mod          права пользователя
         * @param string  $rating       рейтинг сообществ для фильтрации (bronze|silver|gold)
  * @param boolen  $check_image  выводить только сообщества с картинкой
	 * @return array Данные выборки
	 */
  function GetCommunes($group_id = NULL, $author_id = NULL, $user_id = NULL, $order_mode = self::OM_CM_BEST, $cur_user_id = NULL, $offset = 0, $limit = 'ALL', $search = NULL, &$count = NULL, $mod = 0, $rating=false, $check_image = false)
  {
    global $DB;
      $ranges = array(
          'bronze' => array('min' => 50, 'max' => 199),
          'silver' => array('min' => 200, 'max' => 999),
          'gold' => array('min' => 1000, 'max' => false)
      );
    $fields = 
    " cm.*,
      u.uid as author_uid,
      u.login as author_login,
      u.photo as author_photo,
      u.usurname as author_usurname,
      u.uname as author_uname,
      u.role as author_role,
      u.is_pro as author_is_pro,
      u.is_profi as author_is_profi,
      u.is_verify as author_is_verify,
      u.is_team as author_is_team,
      -- p.name as author_prof_name,
      u.reg_date as author_reg_date,
      u.is_pro_test as author_is_pro_test,".
      (($mod & commune::MOD_ADMIN)? "
        admins.login as admin_login,
        admins.uname as admin_name,
        admins.usurname as admin_uname,
        admins.is_verify as admin_is_verify,
        ": ""
      )."
      cb.commune_id::boolean as is_blocked,
      cb.reason as blocked_reason,
      cb.blocked_time as blocked_time,
      ".
      (
        $cur_user_id === NULL
        ? ''
        : " CASE
              WHEN mc.id IS NULL THEN ".self::JOIN_STATUS_NOT."
              WHEN mc.is_deleted THEN ".self::JOIN_STATUS_DELETED."
              WHEN mc.is_accepted THEN ".self::JOIN_STATUS_ACCEPTED."
              ELSE ".self::JOIN_STATUS_ASKED."
             END
             as current_user_join_status,
             mc.is_banned as is_banned,
             mc.is_moderator as is_moderator,"
      )."
      extract(year from age(cm.created_time::date)) as year, 
      extract(month from age(cm.created_time::date)) as month, 
      extract(day from age(cm.created_time::date)) as day";


    $where  = (!$group_id ? '' : " WHERE cm.group_id={$group_id}");
    if($author_id!==NULL)
      $where .= (!$where ? ' WHERE' : ' AND')." cm.author_id={$author_id}";
    if($search!=NULL && $search!=''){
      $search = strtr($search,array('%'=>'\\\\%'));
      $where .= (!$where ? ' WHERE' : ' AND')." (cm.descr ILIKE '%{$search}%' OR cm.name ILIKE '%{$search}%')";
    }
    // следующие 2 строчки отключают поиск по сообществам, неактивным более 30 дней
    /*if($user_id===NULL && $author_id===NULL)
      $where .= (!$where ? ' WHERE' : ' AND').' COALESCE(cm.last_activity, cm.created_time) > now()::date - '.self::NOACT_LIFETIME;*/
    if($rating){
        $min = !empty($ranges[$rating]['min']) ? (int)$ranges[$rating]['min'] : false;
        $max = !empty($ranges[$rating]['max']) ? (int)$ranges[$rating]['max'] : false;
        if($min || $max){
            $where .= (!$where ? ' WHERE' : ' AND');
            $rsql = '';
            if($min) $rsql = '(cm.yeas - cm.noes) >= '.$min;
            if($max) $rsql .= ($min ? ' AND' : '').' (cm.yeas-cm.noes) <= '.$max;
            $where .= ' '. $rsql;
        }
    }
    if (!($mod & commune::MOD_ADMIN))
      $where_no_blocked .= " WHERE (cb.commune_id IS NULL" . ($cur_user_id? " OR cm.author_id = '$cur_user_id'": "") . ")";
    
    $where_add = "";
    if ($check_image) {
      $where_add .= (!$where ? ' WHERE' : ' AND')." cm.image != '' ";
    }
    
    $order_by = '';
    switch($order_mode)
    {
      case self::OM_CM_BEST   : $order_by = ' ORDER BY cm.yeas - cm.noes DESC, cm.a_count - cm.w_count DESC, cm.created_time DESC'; break;
      case self::OM_CM_POPULAR : $order_by = ' ORDER BY cm.a_count - cm.w_count DESC, cm.yeas + cm.noes DESC, cm.created_time DESC'; break;
      case self::OM_CM_ACTUAL : $order_by = ' ORDER BY cm.last_activity DESC NULLS LAST'; break;
      case self::OM_CM_NEW     : $order_by = ' ORDER BY cm.created_time DESC'; break;
      case self::OM_CM_MY     : $order_by = ' ORDER BY cm.created_time DESC'; break;
      case self::OM_CM_JOINED : 
      case self::OM_CM_JOINED_ACCEPTED  : 
      case self::OM_CM_JOINED_CREATED  : 
      case self::OM_CM_JOINED_BEST  : 
      case self::OM_CM_JOINED_LAST  : 
      case self::OM_CM_JOINED_MY  : 
          if ( $user_id!==NULL ) {
              if ( $order_mode == self::OM_CM_JOINED_ACCEPTED ) {
                  $order_by = ' ORDER BY accepted_time DESC'; 
              }
              elseif ( $order_mode == self::OM_CM_JOINED_BEST ) {
                  $order_by = ' ORDER BY cm.yeas - cm.noes DESC, cm.a_count - cm.w_count DESC, cm.created_time DESC';
              }
              elseif ( $order_mode == self::OM_CM_JOINED_CREATED ) {
                  $order_by = ' ORDER BY cm.created_time DESC';
              }
              elseif ( $order_mode == self::OM_CM_JOINED_LAST ) {
                  $order_by = ' ORDER BY max_created_time DESC NULLS LAST, accepted_time DESC';
              }
              elseif ( $order_mode == self::OM_CM_JOINED_MY ) {
                  $order_by = ' ORDER BY position_time DESC';
              }
              else {
                  $order_by = ' ORDER BY accepted_time DESC'; 
              }
          }
      break;
    }

    $group_by = '';
    
    if ( $order_mode == self::OM_CM_JOINED_LAST ) {
    	$group_by = ' 
    	GROUP BY cm.id, cm.group_id, cm.author_id, cm.name, cm.descr, cm.image, cm.small, cm.restrict_type, cm.yeas ,
    	cm.noes, cm.w_count, cm.a_count, cm.themes_count, cm.created_time, cm.last_activity, cm.subscribed, m.accepted_time 
    	';
    }
    
    $from = "
        FROM commune cm ".
        (
           $user_id===NULL
           ? ''
           : $DB->parse(" INNER JOIN
                 commune_members m
                   ON m.commune_id = cm.id
                  AND m.user_id = ?i
                  AND m.is_accepted = true
                  AND m.is_deleted = false
                  AND m.is_banned = false
             ", $user_id)
        );

    $sql = "
        SELECT {$fields}
          FROM ( SELECT cm.* ".($user_id===NULL ? '' : ', m.accepted_time').( $order_mode == self::OM_CM_JOINED_MY && $user_id!==NULL ? ', COALESCE(m.position_time, m.accepted_time) AS position_time' : '').($order_mode == self::OM_CM_JOINED_LAST ? ', MAX(ms.created_time) AS max_created_time' : '')." {$from} ".
          ( 
            $order_mode == self::OM_CM_JOINED_LAST
            ? 
            'LEFT JOIN commune_themes t ON t.commune_id = cm.id 
             LEFT JOIN commune_messages ms ON ms.theme_id = t.id AND ms.parent_id IS NULL' 
            : '' 
        )." {$where} {$where_add} {$group_by} {$order_by} LIMIT {$limit} OFFSET {$offset} ) as cm
        INNER JOIN
          users u
            ON u.uid = cm.author_id
        -- LEFT JOIN freelancer ON freelancer.uid = u.uid
        -- LEFT JOIN professions p ON p.id = freelancer.spec_orig
        LEFT JOIN
          commune_blocked cb
            ON cb.commune_id = cm.id
        ".
        (
          ($mod & commune::MOD_ADMIN) ?
          " LEFT JOIN users admins ON admins.uid = cb.admin " :
          ""
        ).
        (
          $cur_user_id === NULL
          ? ''
          : $DB->parse(" LEFT JOIN
                commune_members mc
                  ON mc.user_id = ?i
                 AND mc.commune_id = cm.id ", $cur_user_id)
        ).
        " {$where_no_blocked} {$order_by}";
//exit(nl2br($sql));
    $communes = $DB->rows($sql);
    if($communes) {
      if($count!==NULL) { // !!! да он всегда не NULL
        $nr = count($communes);
        if($limit=='ALL' && $nr) {
          $count = $offset + $nr;
        }
        else {
          $sql = "SELECT COUNT(cm.id) {$from} {$where}";
          $count = $DB->val($sql);
        }
      }
    }

    return $communes;
  }
    
    function getRandomCommunes($size=3) {
        global $DB;
        
        $sql = "SELECT c.* FROM commune c 
                LEFT JOIN commune_blocked cb ON cb.commune_id = c.id 
                WHERE cb.commune_id IS NULL ORDER BY RANDOM() LIMIT 15;";
        
        $themes = $DB->cache(7200)->rows($sql);
        
        if($themes) {
            if($size > count($themes)) $size = count($themes);
            $rand_keys = array_rand($themes, $size);
            foreach($rand_keys as $k=>$v) $result[] = $themes[$v];
            
            return $result;
        }
        
        return false;
    }
  
    /**
     * Перемещение сообщества вверх/вниз при своей сортировке в звкладке "Я вступил"
     *
     * @param  int $sCommId сообщество котоое перемещаем
     * @param  int $sUserId текущий юзер
     * @param  string $sSign в какую сторону перемещаем: > - вверх, < - вниз
     * @return bool true on sussess or false on failure
     */
    function communeMove( $sCommId = '', $sUserId = '', $sSign = '>' ) {
        if ( !$sCommId || !$sUserId ) {
            return false;
        }
        
        global $DB;
        $sSign  = ( $sSign != '<' && $sSign != '>' ) ? '>' : $sSign;
        $sDir   = ( $sSign == '<' ) ? 'DESC' : '';
        $sQuery = "SELECT 
                mn.commune_id, 
                COALESCE(m.position_time, m.accepted_time) AS old_pos,  
                COALESCE(mn.position_time, mn.accepted_time) AS new_pos  
            FROM commune_members m 
            INNER JOIN commune_members mn ON m.user_id = mn.user_id 
            WHERE m.commune_id = ? AND m.user_id = ? AND m.is_accepted = true  AND m.is_deleted = false AND m.is_banned = false 
                AND COALESCE(mn.position_time, mn.accepted_time) $sSign COALESCE(m.position_time, m.accepted_time) ORDER BY new_pos $sDir LIMIT 1";
        
        $aRow = $DB->row( $sQuery, $sCommId, $sUserId );
        
        if ( !$DB->error && $aRow ) {
            $sQuery = "UPDATE commune_members SET position_time = ? WHERE commune_id = ? AND user_id = ?;
                UPDATE commune_members SET position_time = ? WHERE commune_id = ? AND user_id = ?";
            
            $DB->query( $sQuery, $aRow['old_pos'], $aRow['commune_id'], $sUserId, $aRow['new_pos'], $sCommId, $sUserId );
        }
        
        return ( !$DB->error ) ? true : false;
    }
    
    /**
     * Напрямую установить номер сообщества при своей сортировке в звкладке "Я вступил"
     *
     * @param  int $sCommId сообщество котоое перемещаем
     * @param  int $sUserId текущий юзер
     * @param  int $nCurrNum текущий номер сообщества
     * @param  int $nNum номер который желаем присвоить
     * @param  int $nTotal общее количество сообществ
     * @return bool true on sussess or false on failure
     */
    function CommuneSetPosition( $sCommId = '', $sUserId = '', $nCurrNum = 0, $nNum = 0, $nTotal = 0 ) {
        $nNum = intval($nNum);
        
        if ( !$sCommId || !$sUserId || !$nCurrNum || !$nNum ) {
            // ошибка ввода
        	return false;
        }
        
        if ( $nNum < 1 ) {
        	// желаем присвоить номер 0 или еще меньше
        	return false;
        }
        
        $nNum = ( $nNum > $nTotal ) ? $nTotal : $nNum;
        
        if ( $nNum == $nCurrNum ) {
            // мы желаем присвоить такой же номер как был - нечего менять
        	return false;
        }
        
        global $DB;
        
        $nLimit  = 2;
        $nOffset = ( $nCurrNum > $nNum ) ? $nNum - 1 : $nNum;
        
        $nOffset--;
        
        if ( $nOffset < 0 ) {
        	$nOffset = 0;
        	$nLimit  = 1;
        }
        
        $sQuery  = "SELECT COALESCE(position_time, accepted_time) AS pos 
            FROM commune_members WHERE user_id = ? AND is_accepted = true  AND is_deleted = false AND is_banned = false 
            ORDER BY pos DESC LIMIT $nLimit OFFSET $nOffset";
        $aRows   = $DB->rows( $sQuery, $sUserId );
        
        if ( $DB->error ) {
            // ошибка 
        	return false;
        }
        
        $nCnt = count($aRows);
        
        if ( $nCnt ) {
        	if ( $nCnt > 1 ) {
        		// получили 2 строки: вычисляем середину и присваем ее сообществу
        		$nStart  = strtotime( $aRows[0]['pos'] );
                $nEnd    = strtotime( $aRows[1]['pos'] );
                $nMiddle = ceil( ($nStart + $nEnd) / 2 );
                $sDate   = date('Y-m-d H:i:s', $nMiddle);
                
                if ( $sDate == $aRows[0]['pos'] || $sDate == $aRows[1]['pos'] ) {
                	// даты сомкнулись: требуется пересчет позиций
                	if ( commune::CommuneCalculatePositions($sUserId) ) {
                		return commune::CommuneSetPosition( $sCommId, $sUserId, $nNum );
                	}
                	else {
                	    // не пересчитали
                	    return false;
                	}
                }
        	}
        	else {
        	    // поместить в начало либо в конец списка
        	    $sDate = ( $nCurrNum > $nNum ) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($aRows[0]['pos'].' -1 week'));
        	}
        	
        	$sQuery = 'UPDATE commune_members SET position_time = ? WHERE commune_id = ? AND user_id = ?';
        	$DB->query( $sQuery, $sDate, $sCommId, $sUserId );
        	
        	if ( $DB->error ) {
        		// ошибка 
                return false;
        	}
        }
        else {
            // ошибка 
            return false;
        }
        
        return true;
    }
    
    /**
     * Пересчитать позиции всех сообществ при своей сортировке в звкладке "Я вступил"
     *
     * @param  int $sUserId текущий юзер
     * @return bool true on sussess or false on failure
     */
    function CommuneCalculatePositions( $sUserId = '' ) {
        global $DB;
        $sQuery = 'SELECT commune_id, COALESCE(position_time, accepted_time) AS pos FROM commune_members WHERE user_id = ? AND is_accepted = true  AND is_deleted = false AND is_banned = false ORDER BY pos DESC';
        $aRows  = $DB->rows( $sQuery, $sUserId );
        
        if ( !$DB->error && $aRows ) {
            $sQuery = '';
        	$sDate  = date('Y-m-d H:i:s');
        	
        	foreach ($aRows as $aOne) {
        		$sQuery .= $DB->parse(
                    "UPDATE commune_members SET position_time = ? WHERE commune_id = ?i AND user_id = ?i;", 
                    $sDate, 
                    $aOne['commune_id'], 
                    $sUserId
                );
        		$sDate   = date('Y-m-d H:i:s', strtotime($sDate.' -1 week'));
        	}
        	
        	$DB->squery( $sQuery );
        }
        
        return true;
    }

  /**
   * Находит комментарии и собирает данные по ним для отправки уведомлений.
   * После изменения этой функции, необходимо перезапустить консьюмер /classes/pgq/mail_cons.php на сервере.
   * Если нет возможности, то сообщить админу.
   * @see smail::CommuneNewComment()
   * @see PGQMailSimpleConsumer::finish_batch()
   * 
   * @param string|array $message_ids   идентификаторы комментариев.
   * @param resource $connect           соединение к БД (необходимо в PgQ) или NULL -- создать новое.
   * @return array
   */
function GetComments4Sending($message_ids, $connect = NULL)
  {
    global $DB;
    if(!$message_ids) return NULL;
    if(is_array($message_ids))
      $message_ids = implode(',', array_unique($message_ids));

    $sql = 
    "SELECT ms.*,
            t.commune_id,
            mt.id as top_id, mt.title AS top_title,
            cm.name as commune_name,
            u.login,
            u.usurname,
            u.uname,
            u.email,
            mp.user_id as p_user_id,
            up.login as p_login,
            up.usurname as p_usurname,
            up.uname as p_uname,
            up.email as p_email,
            up.subscr as p_subscr,
            up.is_banned as p_banned,
            mt.user_id as t_user_id,
            ut.login as t_login,
            ut.usurname as t_usurname,
            ut.uname as t_uname,
            ut.email as t_email,
            ut.subscr as t_subscr,
            ut.is_banned as t_banned,
            cg.name AS group_name,
            admin.login as admin_login,
            admin.usurname as admin_usurname,
            admin.uname as admin_uname

       FROM commune_messages ms

     INNER JOIN
       commune_themes t
         ON t.id = ms.theme_id
     INNER JOIN
       users u
         ON u.uid = ms.user_id
        AND u.is_banned = '0'
     INNER JOIN
       commune cm
         ON cm.id = t.commune_id
     INNER JOIN
       commune_messages mt -- топик
         ON mt.theme_id = t.id
        AND mt.parent_id IS NULL
     INNER JOIN
       users ut
         ON ut.uid = mt.user_id
        AND ut.is_banned = '0'
     LEFT JOIN
       commune_messages mp -- родитель
         ON mp.id = ms.parent_id
         AND mp.user_id <> ms.user_id
     LEFT JOIN
       users up
         ON up.uid = mp.user_id
        AND up.is_banned = '0'
     LEFT JOIN
       users admin
         ON admin.uid = ms.modified_id
    INNER JOIN
       commune_groups cg
         ON cg.id = cm.group_id
      WHERE ms.id IN ({$message_ids})
        AND ms.deleted_id IS NULL
        AND ms.parent_id IS NOT NULL";

    return $DB->rows($sql);
  }

	/**
	 * Ставит признак, что уведомление о данном топике отправлено.
	 *
	 * @param integer $theme_id ИД темы
	 * @return integer 1 - если все прошло успешно, 0 - если ошибка
	 */
  function SetTopicIsSent($theme_id)
  {
    global $DB;
    $sql = "UPDATE commune_themes SET is_sent = true WHERE id = ?i";
    if($DB->query($sql, $theme_id))
      return 1;
    return 0;
  }
  /**
   * Выдает топ-сообщения, которые нужно разослать по e-mail участников сообщества.
   *
   * @param integer $limit Лимит
   * @param boolean $skip_private Пропускать приватные топики
   * @return array  Данные по выборке, наче null
   */
  function GetTopic4Sending($limit=1, $skip_private = false)
  {
    global $DB;
    $where = $skip_private ? ' AND is_private = false ' : '';
    $sql = 
    "SELECT ms.*,
            t.commune_id,
            cm.name as commune_name,
            u.login as user_login,
            u.usurname as user_usurname,
            u.uname as user_uname
       FROM (SELECT id, commune_id FROM commune_themes WHERE is_sent = false {$where}) as t
     INNER JOIN
       commune cm
         ON cm.id = t.commune_id
     INNER JOIN
       commune_messages ms
         ON ms.theme_id = t.id
        AND ms.parent_id IS NULL
        /*AND (ms.moderator_status > 0 OR ms.moderator_status IS NULL)*/
        AND ms.created_time > now() - interval '24 hours'
     INNER JOIN
       users u
         ON u.uid = ms.user_id
        AND u.is_banned = '0'
      ORDER BY t.id
      LIMIT {$limit}";

    return $DB->rows($sql);
  }
	/**
	 * Выдает топ-сообщения кому нужно разослать
	 *
	 * @param integer $commune_id ИД сообщества
	 * @param string  $limit      Лимит 
	 * @param integer $offset     С какой позиции начать выборку
	 * @return array  Данные по выборке, наче null
	 */
  function GetTopicSubscribers($commune_id, $limit='ALL', $offset=0)
  {
    global $DB;
    $sql = 
    "SELECT u.email,
            u.login,
            u.usurname,
            u.uname,
            usk.key AS unsubscribe_key,
            usk.uid
       FROM
       (
         SELECT user_id
           FROM commune_members
          WHERE commune_id = ?i
            AND is_accepted = true
            AND is_deleted = false
            AND is_banned = false
            AND subscribed = true
         UNION ALL
         SELECT author_id FROM commune WHERE id = ?i AND subscribed = true
       ) as m
     INNER JOIN
       users u
         ON u.uid = m.user_id
        AND u.is_banned = '0'
        AND substr(u.subscr::text,7,1) = '1'
     LEFT JOIN
       users_subscribe_keys AS usk
        ON u.uid = usk.uid
      LIMIT {$limit} OFFSET ?i";

    return $DB->rows($sql, $commune_id, $commune_id, $offset);
  }

  /**
   * Возвращает пользователей, подписавшихся на темы заданных сообщений.
   * @param string $message_ids   ид. сообщений (может быть ид. топиков, а может быть ид. комментариев -- по ним ищем ид. топиков)
   * @return array
   */
  function getThemeSubscribers($message_ids) {
    global $DB;

    $sql = "
      SELECT um.message_id, um.user_id,
             u.email, u.login, u.usurname, u.uname
        FROM commune_messages cm
      INNER JOIN commune_messages cx
          ON cx.theme_id = cm.theme_id
         AND cx.parent_id is null
      INNER JOIN commune_users_messages um ON um.message_id = cx.id AND um.subscribed = TRUE
      INNER JOIN users u ON u.uid = um.user_id AND u.is_banned = '0' -- AND substr(u.subscr::text,7,1) = '1'
       WHERE cm.id IN (?l)
    ";

    return $DB->rows($sql, $message_ids);
  }

    /**
     * Блокировка/разблокировка топика администратором сообщества
     * 
     * @param  int $topic_id ID топика
     * @param  int $msg_id ID топового сообщеия топика
     * @param  string $action действие ('block' или 'unblock')
     */
    function BlockedTopic( $topic_id = 0, $msg_id, $action = 'unblock' ) {
        if ( $action == 'block' ) {
        	$aData = array( 'is_blocked' => true, 'blocked_time' => date('Y-m-d H:i:s'), 'blocked_admin' => get_uid(false) );
        }
        else {
            $aData = array( 'is_blocked' => false, 'blocked_time' => null, 'blocked_admin' => 0 );
        }
        
        $GLOBALS['DB']->update( 'commune_themes', $aData, 'id = ?i', $topic_id );
        
        // отправляем уведомление о блокировке после самой блокировки
    	if ( $action == 'block' && $topic = commune::GetTopMessageByAnyOther( $msg_id, null, commune::MOD_ADMIN ) ) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
            
            messages::SendBlockedCommuneTheme( $topic );
    	}
        
        return (!$GLOBALS['DB']->error);
    }

	/**
	 * Возвращаем топ-сообщения (темы).
	 *
	 * @param integer $commune_ids  один или несколько идентификаторов (строка ид., разделнных запятыми) сообществ, из которых брать темы.
	 * @param integer $author_id    если указан, значит жестко привязывается к конкретному пользователю.
	 * @param integer $user_id      сессионный юзер. Смотрим по нему дату последнего просмотра топа и голосования.
	 * @param integer $order_mode   режим сортировки (в соответсвии с закладками, как в блогах).
	 * @param integer $offset       Позиция выборки
	 * @param string  $limit        Лимит выборки
	 * @param boolean $get_polls    Подгружать голосования
	 * @param boolean $get_attache  Подгрузить один из приложенных файлов
	 * @param boolean $get_attach_all Подгрузить все вложенные файлы
	 * @return array  Данные по выборке, наче null
	 */
  function GetTopMessages($commune_ids, $author_id = NULL, $user_id = NULL, $mod = 0, $order_mode = self::OM_TH_NEW, $offset = 0, $limit = 'ALL', $get_polls = true, $get_attach = true, $get_attach_all = false)
  {
    global $DB;
    
    $commune_users_messages = self::getTableName('commune_users_messages', $commune_ids, false, true);
    $commune_messages       = self::getTableName('commune_messages', $commune_ids, false, true);
    
    $add_sql = $user_id ? '(SELECT rating FROM ' . $commune_users_messages . ' WHERE message_id = cms.id AND user_id = '.(int)$user_id.' LIMIT 1) AS user_vote,' : '0 AS user_vote,';
    global $cat;    
    switch($order_mode)
    {
      case self::OM_TH_NEW     : 
          $order_by = ' ORDER BY pos NULLS LAST, ms.created_time DESC';
          $post_order_by = ' ORDER BY pos NULLS LAST, created_time DESC';
          break;
      case self::OM_TH_MY      : 
          $order_by = ' ORDER BY pos NULLS LAST, cms.modified_time DESC';
          $post_order_by = ' ORDER BY pos NULLS LAST, last_activity DESC';
          break;
      case self::OM_TH_POPULAR : 
          $order_by = ' ORDER BY pos NULLS LAST, a_count DESC';
          $post_order_by = ' ORDER BY pos NULLS LAST, a_count DESC';
          break;
      case self::OM_TH_ACTUAL  : 
          $order_by = ' ORDER BY pos NULLS LAST, last_activity DESC';
          $post_order_by = ' ORDER BY pos NULLS LAST, last_activity DESC';
          break;
    }
    if($cat==0) $cat = NULL;
    
    $where_no_blocked = '';
    
    if ( !($mod & (commune::MOD_ADMIN | commune::MOD_MODER)) ) {
        $where_no_blocked2 = '';
        
        if ( !($mod & (commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR)) ) {
            $where_no_blocked2 = ' AND t.is_blocked = false ';
        }
        
        $where_no_blocked .= " (tb.theme_id IS NULL $where_no_blocked2" . ( $user_id ? " OR t.user_id = '$user_id'": "") . ")";
    }
    
    $sql = "SET enable_sort = false;
            SET enable_hashjoin = false;
            --SET join_collapse_limit = 4;
            SELECT cms.*, ms.*, cm.name as commune_name, cm.author_id as commune_author_id, 
                   cg.id as commune_group_id, cg.name as commune_group_name,
				   cb.commune_id::boolean as commune_blocked,
                   $add_sql
                   m.warn_count as member_warn_count,
                   m.id as member_id,
                   cms.cnt_files as file_exists,
                   m.is_banned::int as member_is_banned,
                   m.is_accepted::int as member_is_accepted,
                   u.is_banned::int as user_is_banned,
                   u.ban_where::int as user_ban_where,
                   u.warn::int as user_warn_cnt,
                   m.is_admin::int as member_is_admin,
                   u.is_pro as user_is_pro,
                   u.is_profi as user_is_profi,
                   u.is_verify as user_is_verify,
                   u.is_team as user_is_team,
                   u.is_pro_test as user_is_pro_test,
                   u.role as user_role,
                   u.login as user_login,
                   u.photo as user_photo,
                   u.usurname as user_usurname,
                   u.subscr as user_subscr,
                   u.email as user_email,
                   u.uname as user_uname,
                   u.is_chuck as user_is_chuck,
                   -- p.name as user_prof_name,
                   u.reg_date,
				   cp.question as question,
				   cp.closed as poll_closed,
				   cp.multiple as poll_multiple,".
                   (
                     !$user_id
                     ? '0'
                     : 'um.last_viewed_time'
                   )." as last_viewed_time, ".
                   (!$user_id ? ''
                     : 'um.current_count, cv._cnt as poll_votes,')."
                   umm.login as modified_login,
                   umm.usurname as modified_usurname,
                   umm.uname as modified_uname,
                   (am.user_id IS NOT NULL)::int as modified_by_commune_admin,
                   cc.name as category_name,
                   us.login AS admin_login_s, us.uname AS admin_uname_s, us.usurname AS admin_usurname_s, 
                   uc.login AS admin_login_c, uc.uname AS admin_uname_c, uc.usurname AS admin_usurname_c 
              FROM
              (
                SELECT t.id as theme_id, t.pos, t.commune_id, t.a_count, t.last_activity, t.close_comments, t.is_private, t.category_id, 
                    tb.theme_id::boolean AS is_blocked_s, tb.reason AS blocked_reason_s, tb.blocked_time AS blocked_time_s, tb.\"admin\" AS blocked_admin_s, 
                    t.is_blocked AS is_blocked_c, t.blocked_time AS blocked_time_c, t.blocked_admin AS blocked_admin_c,
                    t.created_time
                  FROM commune_themes t
                  LEFT JOIN commune_theme_blocked tb ON tb.theme_id = t.id 
                 WHERE ".($where_no_blocked ? $where_no_blocked.' AND ' : '').($cat?'t.category_id='.$cat.' AND ':'')."t.commune_id IN ({$commune_ids})
                     ".
                   (
                     $author_id!==NULL ? $DB->parse(" AND t.user_id = ?i ", $author_id) : '' // очень важно именно !==NULL, т.к. $author_id может быть 0.
                   ).
                   "
                {$post_order_by}
                LIMIT {$limit} OFFSET {$offset}
              ) as ms
            INNER JOIN
              {$commune_messages} cms
                ON cms.theme_id = ms.theme_id
               AND cms.parent_id IS NULL
            LEFT JOIN
              commune_members m
                ON m.user_id = cms.user_id
               AND m.commune_id = ms.commune_id  
            INNER JOIN
              commune cm
                ON cm.id = ms.commune_id
            INNER JOIN
              commune_groups cg
                ON cg.id = cm.group_id
            INNER JOIN
              users u ON u.uid = cms.user_id
            -- LEFT JOIN  freelancer ON freelancer.uid = u.uid
            
            LEFT JOIN users us ON us.uid = ms.blocked_admin_s 
            LEFT JOIN users uc ON uc.uid = ms.blocked_admin_c 
                
            LEFT JOIN
			  commune_blocked cb
			    ON cb.commune_id = ms.commune_id
            LEFT JOIN
              users umm
                ON umm.uid = cms.modified_id
            LEFT JOIN
              commune_members am
                ON am.user_id = umm.uid
               AND am.commune_id = ms.commune_id
               AND am.is_admin = true 
			LEFT JOIN
			  commune_poll cp
			    ON cp.theme_id = cms.theme_id 
            LEFT JOIN 
              commune_categories cc
                ON cc.id = ms.category_id
           /* LEFT JOIN  professions p ON p.id = freelancer.spec_orig */ " .
       
            (
                !$user_id ? ''
                : $DB->parse(" LEFT JOIN
                      {$commune_users_messages} um
                        ON um.message_id = cms.id
                       AND um.user_id = ?i
					LEFT JOIN
					    (SELECT theme_id, COUNT(answer_id) AS _cnt FROM commune_poll_votes WHERE user_id = ?i GROUP BY theme_id) cv ON cv.theme_id = cms.theme_id
                    ", $user_id, $user_id)
            )."
             {$order_by}
    ";
             
    $rows = $DB->rows($sql);
	if ($DB->error) return NULL;
	if (!$rows)
	    return array();
	    
    if($get_polls) {
		// ответы на опросы
		$ids = '';
		$lnk = array();
		for ($i=0,$c=count($rows); $i<$c; $i++) {
			$ids .= ",{$rows[$i]['theme_id']}";
			$lnk[ $rows[$i]['theme_id'] ] = &$rows[$i];
		}
		if ($ids) {
			$res = $DB->rows("SELECT * FROM commune_poll_answers WHERE theme_id IN (".substr($ids, 1).") ORDER BY id");
            if($res) {
                foreach($res as $row) {
    				$lnk[ $row['theme_id'] ]['answers'][] = $row;
    			}
            }
		}
	}

  $ids2 = '';
  $lnk2 = array();
  for ($i=0,$c=count($rows); $i<$c; $i++) {
    $ids2 .= ",{$rows[$i]['theme_id']}";
    $lnk2[ $rows[$i]['theme_id'] ] = &$rows[$i];
  }
  if($ids2) {
    $res = $DB->rows("SELECT COUNT(DISTINCT(user_id)) as count_users, theme_id FROM commune_messages WHERE theme_id IN (".substr($ids2, 1).") AND parent_id IS NOT NULL GROUP BY theme_id");
    if($res) {
      foreach($res as $v) {
        $lnk2[ $v['theme_id'] ]['a_users_count'] = $v['count_users'];
      }
    }
  }

	if($get_attach) {
		foreach($rows as $k=>$v) {
            $x[$v['id']] = $v;
            if($v['file_exists']) {
                $id_attach[$v['id']] = $v['id'];
                //$countFile[$v['id']] = $v['cnt_files']
            }
        }
            
        if($id_attach) {
            //$ret = $DB->rows("SELECT file.*, commune_attach.cid, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid IN (".implode(", ", $id_attach).") AND commune_attach.sort = 1");            
            $ret = CFile::selectFilesBySrc(self::FILE_TABLE, $id_attach, 'sort, src_id, id');
            if($ret) { 
                foreach($ret as $k=>$val) {
                    if(!isset($x[$val['src_id']]['attach'])) {
                        $x[$val['src_id']]['attach'][] = $val;
                    }
                }
                    
                foreach($x as $k=>$val) $r[] = $val;
                  
                $rows = $r;
            }
        }
    }
	
    return $rows;
  }

	/**
	 * Возвращаем одно топ-сообщение.
	 *
	 * @param integer $message_id идентификатор любого сообщения
	 * @param integer $user_id    сессионный юзер. Смотрим по нему дату последнего просмотра топа.
	 * @param integer $mod        Битовая маска прав пользователя
	 * @param boolean $by_self    если true название таблицы идет как mx
	 * @return array  Данные по выборке, наче null  (Получаем топ-сообщение из той же темы, что и сообщение $message_id).
	 */
  function GetTopMessageByAnyOther($message_id, $user_id = NULL, $mod = 0, $by_self = FALSE) {
    global $DB;    
    $message_id = intval($message_id);
    $user_id    = $user_id !== null ? intval($user_id) : $user_id;
    if ( !($mod & (commune::MOD_ADMIN | commune::MOD_MODER)) ) {
        $where_no_blocked2 = '';
        
        if ( !($mod & (commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR | MOD_COMM_AUTHOR)) ) {
            $where_no_blocked2 = ' AND t.is_blocked = false ';
        }
        
        $where_no_blocked .= " (tb.theme_id IS NULL $where_no_blocked2 " . ($user_id? $DB->parse(" OR mx.user_id = ?i", $user_id): "") . ")";
    }
    $communeID = self::getCommuneIDByMessageID($message_id);
    $commune_users_messages = self::getTableName('commune_users_messages', $communeID, false, true);
    $commune_messages       = self::getTableName('commune_messages', $communeID, false, true);
    
	$a = $by_self ? 'mx' : 'ms';
    $add_sql = $user_id ? $DB->parse('(SELECT rating FROM ' . $commune_users_messages . ' WHERE message_id = '.$a.'.id AND user_id = ?i) AS user_vote,', $user_id) : '0 AS user_vote,';
    $sql = "
    SET enable_sort = false;
    SET enable_hashjoin = false;
    SET enable_mergejoin = false;
    SELECT mx.*, t.commune_id, cm.name as commune_name, t.a_count, t.last_activity,
    $add_sql
                   t.close_comments, t.is_private,
                   cg.id as commune_group_id, cg.name as commune_group_name,
                   cm.author_id as commune_author_id,
                   m.warn_count as member_warn_count,
                   m.is_banned::int as member_is_banned,
                   m.is_accepted::int as member_is_accepted,
                   m.is_admin::int as member_is_admin,
                   m.id as member_id,
                   u.is_banned::int as user_is_banned,
                   u.ban_where::int as user_ban_where,
                   u.warn::int as user_warn_cnt,
                   u.is_pro as user_is_pro,
                   u.is_profi as user_is_profi,
                   u.is_verify as user_is_verify,
                   u.is_team as user_is_team,
                   u.is_pro_test as user_is_pro_test,
                   u.role as user_role,
                   u.login as user_login,
                   u.photo as user_photo,
                   u.usurname as user_usurname,
                   u.subscr as user_subscr,
                   u.email as user_email,
                   u.uname as user_uname,
                   u.is_chuck as user_is_chuck,
                   -- p.name as user_prof_name,
                   u.reg_date,
				   cp.question,
				   cpa.id as answer_id,
				   cpa.answer,
				   cpa.votes,
				   cp.closed as poll_closed,
				   cp.multiple as poll_multiple,".
									 (
									   !$user_id
									   ? '0'
                     : 'um.last_viewed_time'
                   )." as last_viewed_time, ".
               (!$user_id ? '' : 'um.current_count, um.subscribed, um.hidden_threads, cv._cnt as poll_votes, ')
                   ."umm.login as modified_login,
                   umm.usurname as modified_usurname,
                   umm.uname as modified_uname,
                   (am.user_id IS NOT NULL)::int as modified_by_commune_admin,
		   cc.name as category_name,
		   t.category_id,
		          tb.theme_id::boolean AS is_blocked_s, tb.reason AS blocked_reason_s, tb.blocked_time AS blocked_time_s, tb.\"admin\" AS blocked_admin_s, 
                  t.is_blocked AS is_blocked_c, t.blocked_time AS blocked_time_c, t.blocked_admin AS blocked_admin_c, 
                  us.login AS admin_login_s, us.uname AS admin_uname_s, us.usurname AS admin_usurname_s, 
                  uc.login AS admin_login_c, uc.uname AS admin_uname_c, uc.usurname AS admin_usurname_c 
              FROM {$commune_messages} {$a}".
						(
							$by_self
							? ''
							: " INNER JOIN
            			  {$commune_messages}  mx
            			    ON mx.theme_id = {$a}.theme_id
            			   AND mx.parent_id IS NULL"
						)."
            INNER JOIN
              commune_themes t
                ON t.id = mx.theme_id
            INNER JOIN
              commune cm
                ON cm.id = t.commune_id
            INNER JOIN
              commune_groups cg
                ON cg.id = cm.group_id
            INNER JOIN
              users u ON u.uid = mx.user_id
            -- LEFT JOIN freelancer ON freelancer.uid = u.uid
            LEFT JOIN commune_theme_blocked tb ON tb.theme_id = t.id 
			LEFT JOIN
			  commune_poll cp
			    ON cp.theme_id = mx.theme_id
			LEFT JOIN
			  commune_poll_answers cpa
			    ON cpa.theme_id = mx.theme_id
            LEFT JOIN users us ON us.uid = tb.\"admin\" 
            LEFT JOIN users uc ON uc.uid = t.blocked_admin 
            LEFT JOIN
              commune_members m
                ON m.user_id = u.uid
               AND m.commune_id = t.commune_id
            LEFT JOIN
              users umm
                ON umm.uid = mx.modified_id ".
				($user_id 
                    ? $DB->parse("LEFT JOIN (SELECT theme_id, COUNT(answer_id) AS _cnt FROM commune_poll_votes WHERE user_id = ?i GROUP BY theme_id) cv ON cv.theme_id = mx.theme_id", $user_id) 
                    : ""
                )
            ."
                        
			LEFT JOIN
              commune_members am
                ON am.user_id = umm.uid
               AND am.commune_id = cm.id
               AND am.is_admin = true
	      LEFT JOIN 
              commune_categories cc
                ON cc.id = t.category_id
            /* LEFT JOIN professions p ON p.id = freelancer.spec_orig */".
						(
							!$user_id
							? ''
							: $DB->parse(" LEFT JOIN
                    {$commune_users_messages} um
                      ON um.message_id = mx.id
            			   AND um.user_id = ?i", $user_id)
						).$DB->parse("
             WHERE {$a}.id = ?i". ($where_no_blocked ? ' AND '. $where_no_blocked : '')
              . ( $by_self ? " AND {$a}.parent_id IS NULL" : '' ).
			 " ORDER BY cpa.id", $message_id);

    $res = $DB->rows($sql);
    if ($DB->error) return NULL;
   
	if ($mess = $res[0]) {
        unset($res[0]);
        //$sql = "SELECT file.*, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid = ?i AND commune_attach.inline = FALSE ORDER BY commune_attach.id";
	    //$mess['attach'] = $DB->rows($sql, $message_id);
	    $mess['attach'] = CFile::selectFilesBySrc(self::FILE_TABLE, $message_id, 'id', 'inline = FALSE');
        
		if ($mess['question'] !== '') {
			$mess['answers'][0] = array('id'=>$mess['answer_id'], 'answer'=>$mess['answer'], 'votes'=>$mess['votes']);
            if($res) {
                foreach($res as $row) {
    				$mess['answers'][] = array('id'=>$row['answer_id'], 'answer'=>$row['answer'], 'votes'=>$row['votes']);
    			}
            }
		} else {
			$mess['answers'] = array();
		}
		return $mess;
	} else {
		return array();
	}
  }

	/**
	 *  Возвращает сообщение.
	 *
	 * @param integer $message_id ИД сообщения
	 * @return array  Данные по выборке, наче null
	 */
  function GetMessage($message_id)
  {
    global $DB;
    $message_id = (int)$message_id;
    
    $sql = "SELECT ms.*, t.commune_id, t.a_count, t.pos, t.close_comments, t.is_private, t,category_id,

                   u.is_pro as user_is_pro,
                   u.is_profi as user_is_profi,
                   u.is_verify as user_is_verify,
                   u.is_team as user_is_team,
                   u.is_pro_test as user_is_pro_test,
                   u.role as user_role,
                   u.login as user_login,
                   u.photo as user_photo,
                   u.usurname as user_usurname,
                   u.uname as user_uname,
                   u.subscr as user_subscr,
                   u.email as user_email,
                   u.is_chuck as user_is_chuck,
				   cp.question,
				   cp.multiple,
				   cpa.id as answer_id,
				   cpa.answer,
				   cpa.votes
              FROM commune_messages ms
            INNER JOIN
              commune_themes t
                ON t.id = ms.theme_id
            INNER JOIN
              users u
                ON u.uid = ms.user_id
			LEFT JOIN
			  commune_poll cp
			    ON cp.theme_id = t.id
            LEFT JOIN
              commune_categories cc
                ON cc.id = t.category_id
			LEFT JOIN
			  commune_poll_answers cpa
			    ON cpa.theme_id = t.id
             $add_sql
             WHERE ms.id = ?i
			 ORDER BY cpa.id";

    $res = $DB->rows($sql, $message_id);
    if ($DB->error) return NULL;
    
	if ($mess = $res[0]) {
        unset($res[0]);
        //$sql = "SELECT file.*, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid = ?i ORDER BY commune_attach.sort";
	    //$mess['attach'] = $DB->rows($sql, $message_id);
		$mess['attach'] = CFile::selectFilesBySrc(self::FILE_TABLE, $message_id, 'sort');
        if ($mess['question'] !== '') {
			$mess['answers'][0] = array('id'=>$mess['answer_id'], 'answer'=>$mess['answer'], 'votes'=>$mess['votes']);
            if($res) {
                foreach($res as $row) {
    				$mess['answers'][] = array('id'=>$row['answer_id'], 'answer'=>$row['answer'], 'votes'=>$row['votes']);
    			}
            }
		} else {
			$mess['answers'] = array();
		}
		return $mess;
	} else {
		return array();
	}
  }

	/**
	 * Возвращает комментарии (или один конкретный) в той форме, которая необходима для вывода на страницу комментариев (site=Topic)
	 * Топики не входят в возвращаемый массив.
	 * 
	 * @param integer $theme_id    либо это. Возвращает все комментарии по заданной теме.
	 * @param integer $message_id  либо это. Просто один комментарий.
	 * @param integer $user_id     ид пользователя, просматривающего сообщения
	 * @return array  Данные по выборке, наче null
	 */
    function GetAsThread($theme_id, $message_id=NULL, $user_id = null) {
        global $DB;        
        $fields = "
       ms.*,
       CASE WHEN ms.parent_id = t.id THEN NULL ELSE ms.parent_id END AS parent_id,
       u.is_pro as user_is_pro,
       u.is_profi as user_is_profi,
       u.is_verify as user_is_verify,
       u.is_team as user_is_team,
       u.is_pro_test as user_is_pro_test,
       u.role as user_role,
       u.login as user_login,
       u.photo as user_photo,
       u.usurname as user_usurname,
       u.uname as user_uname,
       u.is_banned::int as user_is_banned,
       u.is_chuck as user_is_chuck,
       -- p.name as prof_name,
       u.reg_date,
       m.is_banned::int as member_is_banned,
       m.is_accepted::int as member_is_accepted,
       m.is_admin::int as member_is_admin,
       cm.author_id as commune_author_id,
       m.warn_count as member_warn_count,
       m.id as member_id,
       ms.cnt_files as file_exists, 
       ud.login as deleted_login,
       ud.usurname as deleted_usurname,
       ud.uname as deleted_uname,
       (ad.user_id IS NOT NULL)::int as deleted_by_commune_admin,
       um.login as modified_login,
       um.usurname as modified_usurname,
       um.uname as modified_uname,
       (am.user_id IS NOT NULL)::int as modified_by_commune_admin ";

    $joins = "
      INNER JOIN
        commune cm
          ON cm.id = t.commune_id
      INNER JOIN
        users u ON u.uid = ms.user_id
      -- LEFT JOIN  freelancer ON freelancer.uid = u.uid   
      LEFT JOIN
        commune_members m
          ON m.user_id = u.uid
         AND m.commune_id = t.commune_id
      LEFT JOIN
        users ud
          ON ud.uid = ms.deleted_id
      LEFT JOIN
        commune_members ad
          ON ad.user_id = ud.uid
         AND ad.commune_id = cm.id
         AND ad.is_admin = true
      LEFT JOIN
        users um
          ON um.uid = ms.modified_id
      LEFT JOIN
        commune_members am
          ON am.user_id = um.uid
         AND am.commune_id = cm.id
         AND am.is_admin = true
      /* LEFT JOIN professions p ON p.id = freelancer.spec_orig */";


    if($user_id) {
        $joins .= $DB->parse(" LEFT JOIN commune_users_messages AS cmm ON cmm.message_id = ms.id AND cmm.user_id = ?i ", $user_id);
        $fields .= ", cmm.rating as user_rating ";
    }
    
    $sql = "
    SET enable_sort = false;
    SET enable_hashjoin = false;
    SET join_collapse_limit = 4;
    SELECT {$fields}
              FROM ".
              (
                $message_id === null
                ? $DB->parse("
                    commune_themes t
                    INNER JOIN commune_messages ms ON ms.theme_id = t.id AND ms.parent_id IS NOT NULL
                    {$joins}
                    WHERE t.id = ?i
                  ", $theme_id)
                : $DB->parse("
                    commune_messages ms
                    INNER JOIN commune_themes t ON t.id = ms.theme_id
                    {$joins}
                    WHERE ms.id = ?i
                  ", $message_id)
              );
    
    $sql .= ' ORDER BY ms.created_time';
    $result = ($message_id===NULL ? $DB->rows($sql) : $DB->row($sql));
    if(!$DB->error) {
        //var_dump($result);
        if($message_id === NULL) {
            if($result) {
                foreach($result as $k=>$v) {
                    $x[$v['id']] = $v;
                    if($v['file_exists']) $ids[$v['id']] = $v['id'];
                }
                
                if($ids) {
                    //$sql = "SELECT file.*, commune_attach.cid, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid IN (".implode(", ", $ids).") ORDER BY commune_attach.sort";
                    //$ret = $DB->rows($sql);
                    $ret =  CFile::selectFilesBySrc(self::FILE_TABLE, $ids, 'sort');
                    foreach($ret as $k=>$v) {
                        $x[$v['src_id']]['attach'][] = $v;
                    }
                    
                    foreach($x as $k=>$v) $r[] = $v;
                  
                    return $r;
                }
            }
        } else {
            //$sql = "SELECT file.*, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid = '{$result['id']}' ORDER BY commune_attach.sort";
	        //$result['attach'] = $DB->rows($sql);
            $result['attach'] =  CFile::selectFilesBySrc(self::FILE_TABLE, $result['id'], 'sort');
        }
        
        return $result;      
    }
      

    return NULL;
  }

	/**
	 * Возвращает время последнего просмотра конкретного сообщения данным юзером.
	 *
	 * @param integer $message_id ИД сообщения
	 * @param integer $user_id    ИД Пользователя
	 * @return integer Данные по выборке, наче 0
	 */
  function GetMessageLVT($message_id, $user_id)          
  {
    global $DB;
    $sql = "SELECT last_viewed_time
              FROM commune_users_messages
             WHERE message_id = ?i
               AND user_id = ?i";

    $ret = $DB->val($sql, $message_id, $user_id);

    return ($ret?$ret:0);
  }

	/**
	 * Устанавливает время последнего просмотра конкретного сообщения данным юзером.
	 *
	 * @param integer $message_id   ИД сообщения
	 * @param integer $user_id      ИД пользователя 
	 * @param integer $time         -1: postgreSQL.LOCALTIMESTAMP -- текущее время,
							        NULL: в NULL, либо строковая дата подходящего формата
	 * @param integer $current_count   Количество сообщений на момент просмотра
	 * @return integer если все ок 1, иначе 0
	 */
  function SetMessageLVT($message_id, $user_id, $time=-1, $current_count = 1)
  {
    global $DB;
    $lvt =  $time==-1 ? 'LOCALTIMESTAMP' : ($time===NULL ? 'NULL' : $DB->parse("?", $time)) ;
    // $current_count не может быть отрицательным
    if ($current_count < 0) $current_count = 0;
    $sql = "UPDATE commune_users_messages SET last_viewed_time = {$lvt},
                current_count = ?i
            WHERE message_id = ?i
                AND user_id = ?i";
    $res = $DB->query($sql, $current_count, $message_id, $user_id);

    if(!pg_affected_rows($res)) {
        $insert_table = self::getTableName('commune_users_messages', self::getCommuneIDByMessageID($message_id));
        $sql = "INSERT INTO {$insert_table} (message_id, user_id, is_favorite, last_viewed_time, current_count)
                VALUES (?i, ?i, false, {$lvt}, ?i)";
        $res = $DB->query($sql, $message_id, $user_id, $current_count);
    }

    if($res)
      return 1;

    return 0;
  }
	
	/**
	 * Количество тем, созданных пользователем
	 *
	 * @param string  $commune_ids ИДы сообществ
	 * @param integer $my_id       ИД пользователя
     * @param integer $cat         ИД категории
	 * @return integer Количество тем, наче 0
	 */
  function GetMyThemesCount($commune_ids, $my_id, $cat=0)          
  {
    global $DB;
    $sql = "SELECT COUNT(ms.id)
              FROM commune_themes t
            INNER JOIN
              commune_messages ms
                ON ms.theme_id = t.id
               AND ms.parent_id IS NULL
               AND ms.user_id=?i
             WHERE t.commune_id IN ({$commune_ids}) " . ($cat ? $DB->parse(' AND category_id = ?i', $cat) : '') . "
           ";
    $ret = $DB->cache(1800)->val($sql, $my_id); // Кеш на пол часа

    return ($ret?$ret:0);
  }

	/**
	 * Количество тем, в категории
	 *
	 * @param string  $commune_ids ИДы сообществ
	 * @param integer $category_id       ИД пользователя
	 * @return integer Количество тем, наче 0
	 */
  function GetCategoryThemesCount($commune_ids, $category_id)          
  {
    global $DB;
    $sql = "SELECT COUNT(ms.id)
              FROM commune_themes t
            INNER JOIN
              commune_messages ms
                ON ms.theme_id = t.id
               AND ms.parent_id IS NULL
             WHERE category_id = ?i AND t.commune_id IN ({$commune_ids})
           ";
    $ret = $DB->cache(1800)->val($sql, $category_id); // Кеш на пол часа
    return ($ret?$ret:0);
  }

	/**
	 * Количество тем, чьи авторы забанены.
     * Если указан раздел, то данные выводятся только для указанного раздела
	 *
	 * @param integer $commune_id ИД сообществ
     * @param integer $category_id ID раздела
	 * @return array|integer  [количество забаненых членов собщества, количество забаненых юзеров], иначе возвращает 0;
	 */
  function GetBannedThemesCount($commune_id, $category_id = false)
  {
    global $DB;
    if ($category_id) {
        $categoryCondition = $DB->parse('AND ct.category_id = ?i', $category_id);
    }
    $sql = "SELECT SUM(u.is_banned::int) as user_banned,
                   SUM((m.is_banned IS TRUE AND u.is_banned<>'1')::int) as member_banned,
                   SUM((ct.is_private IS TRUE AND m.is_banned IS NOT TRUE AND u.is_banned<>'1')::int) as private_posts
              FROM commune_themes t
            INNER JOIN
              commune_messages ms
                ON ms.theme_id = t.id
               AND ms.parent_id IS NULL
            LEFT JOIN commune_themes ct 
                ON ct.id = ms.theme_id
            INNER JOIN
              users u
                ON u.uid = ms.user_id
            LEFT JOIN
              commune_members m
                ON m.user_id = ms.user_id
               AND m.commune_id = t.commune_id
             WHERE t.commune_id = {$commune_id}
               $categoryCondition
               AND (m.is_banned IS NOT FALSE OR u.is_banned = '1' OR ct.is_private = TRUE)";

    $memBuff = new memBuff();
    $count_arr = $memBuff->getSql($error, $sql, 1200);

    if(!$error)
      return array('member_banned' => $count_arr[0]['member_banned'], 'user_banned' => $count_arr[0]['user_banned'], 'private_posts' => $count_arr[0]['private_posts']);

    return 0;
  }

	
	/**
	 * Количество всех админов сообщества.
	 *
	 * @param integer $commune_id ИД сообщества
	 * @return integer Данные по выборке, наче 0
	 */
  function GetAdminCount($commune_id)
  {
    global $DB;
    $sql = "SELECT COUNT(id) FROM commune_members WHERE commune_id = ?i AND is_admin=true";
    $ret = $DB->val($sql, $commune_id);
    return ($ret?$ret:0);
  }

 
  /**
   * Участники сообщества.
   *
   * @param integer $id           идентификатор сообщества.
   * @param integer $member_type  по умолчанию self::MEMBER_ANY | self::JOIN_STATUS_ACCEPTED
   * 							  Можно, в общем то, комбинировать флаги и получать	отряд отборных участников. 
   * @param integer $offset       Позиция выборки 
   * @param string  $limit        Лимит 
   * @param string  $user_login   для поиска по логину.
   * @return array Данные по выборке, наче 0
   */
  function GetMembers($id, $member_type = 0x27, $offset = 0, $limit = 'ALL', $user_login = NULL,$sort=false)
  {
      global $DB;
      $order_by = "m.accepted_time DESC";
      if($sort){
          switch($sort){
              case 'name_asc':
                  $order_by = "u.login ASC";
                  break;
              case 'name_desc':
                  $order_by = "u.login DESC";
                  break;
              case 'date_asc':
                  $order_by = "m.accepted_time ASC";
                  break;
              case 'date_desc':
                  $order_by = "m.accepted_time DESC";
                  break;
              case 'asked_asc':
                  $order_by = "m.asked_time ASC";
                  break;
              case 'asked_desc':
                  $order_by = "m.asked_time DESC";
                  break;
          }
      }
    $mt = '';
    if($member_type != self::MEMBER_ANY) {
      $mt .= (($member_type & self::MEMBER_MODERATOR) ? 'm.is_moderator = true' : '');
      $mt .= (($member_type & self::MEMBER_MANAGER) ? ($mt?' OR ':'').'m.is_manager = true' : '');
      $mt .= (($member_type & self::MEMBER_ADMIN) ? ($mt?' OR ':'').'m.is_admin = true' : '');
      $mt .= (($member_type & self::MEMBER_SIMPLE) ? ($mt?' OR  ':'').'(m.is_admin = false AND m.is_moderator = false AND m.is_manager = false)' : '');
    }
    if($mt)
      $mt = ' AND ('.$mt.') ';

    if($member_type & self::JOIN_STATUS_DELETED)
      $mt .= ' AND m.is_deleted = true ';
    else {

      if($member_type & self::JOIN_STATUS_ACCEPTED) {
        if(!($member_type & self::JOIN_STATUS_ASKED))
          $mt .= ' AND m.is_accepted = true ';
      }
      else if($member_type & self::JOIN_STATUS_ASKED)
        $mt .= ' AND m.is_accepted = false ';

      $mt .= ' AND m.is_deleted = false ';
    }
    
    // дополняем sql запрос если если была введена поисковая фраза
    if (!$user_login) {
        $user_login_sql = '';
    } else {
        // количество слов в поисковом запросе
        $wordsCount = count(explode('|', $user_login));
        switch ($wordsCount) {
            case 1:
                $user_login_sql = " AND (u.login ~* '{$user_login}' OR u.uname ~* '{$user_login}' OR u.usurname ~* '{$user_login}')";
                break;
            case 2:
                $user_login_sql = " AND ((u.login ~* '({$user_login})' AND u.uname ~* '({$user_login})')
                                        OR (u.login ~* '({$user_login})' AND u.usurname ~* '({$user_login})')
                                        OR (u.uname ~* '({$user_login})' AND u.usurname ~* '({$user_login})'))";
                break;
            case 3:
                $user_login_sql = " AND (u.login ~* '({$user_login})' AND u.uname ~* '({$user_login})' AND u.usurname ~* '({$user_login})')";
                break;
        }
        
    }
    $sql = "SELECT m.*,
                   u.login,
                   u.photo,
                   u.usurname,
                   u.uname,
                   u.role,
                   u.is_pro,
                   u.is_profi,
                   u.is_team,
                   u.is_pro_test,
                   u.reg_date,
                   cm.id as note_commune_id,
                   m.user_id as note_user_id,
                   cmn.note as note_txt
              FROM commune cm
            INNER JOIN
              commune_members m
                ON m.commune_id = cm.id";
    $sql .= $mt;
    $sql .= "INNER JOIN
              users u
                ON u.uid = m.user_id AND u.is_banned = 0::bit(1)
               {$user_login_sql}
            LEFT JOIN commune_members_notes cmn ON cmn.commune_id = cm.id AND cmn.user_id = m.user_id
             WHERE cm.id = ?i
             ORDER BY {$order_by}
             LIMIT {$limit} OFFSET {$offset}";

    return $DB->rows($sql, $id);
  }
  


	/**
	 * Проверка на тип группы(закрытая или открытая)
	 *
	 * @param bit     $accessBits доступ
	 * @param boolean $br         Перенос если необходим
	 * @return string Сообщает тип группы
	 */
  function GetJoinAccessStr($accessBits, $br = FALSE)
  {
    if(bitStr2Int($accessBits) & self::RESTRICT_JOIN_MASK)
      return 'Закрытая группа.';
    return "Открытая группа.".($br ? '<br/>' : ' ')."Любой может присоединиться.";
  }

	/**
	 * Голос пользователя в рейтинге.
	 *
	 * @param integer $commune_id ИД сообщества
	 * @param integer $user_id    ИД пользователя
	 * @return integer -1 | 0 | 1
	 */
  function GetUserVote($commune_id, $user_id)
  
  {
    global $DB;
    $sql = "SELECT vote FROM commune_votes WHERE commune_id = ?i AND user_id = ?i";
    $ret = $DB->val($sql, $commune_id, $user_id);
    return ($ret?$ret:0);
  }

  	/**
	 * Голос пользователя в рейтинге.
	 *
	 * @param integer $message_id ИД сообщества
	 * @param integer $user_id    ИД пользователя
	 * @return integer -1 | 0 | 1
	 */
  function GetUserTopicVote($message_id, $user_id)
  {
    global $DB;
    $sql = "SELECT rating FROM commune_users_messages WHERE message_id = ?i AND user_id = ?i";
    return (int) $DB->val($sql, $message_id, $user_id);
  }

  /**
   * Лимит пользователя в сообществах (макс число сообществ на одгного пользователя)
   *
   * @param integer $user_id ИД пользователя
   * @return array Данные по выборке, наче null
   */
  function GetUserCommunesLimits($user_id) {
    global $DB;    
    $sql = "SELECT COUNT(id) as user_communes_count, 
									 (EXTRACT(EPOCH FROM LOCALTIMESTAMP) - EXTRACT(EPOCH FROM MAX(created_time)))::int as seconds_passed_since_user_created_his_last_commune
              FROM commune WHERE author_id = ?i"; 


    return $DB->row($sql, $user_id);
  }

	/**
	 * Позиция темы в сообществе, при заданной сортировке.
	 *
	 * @param integer $message_id ИД сообщения
	 * @param integer $author_id  Ид автора сообщения, Только при режиме self::OM_TH_MY
	 * @param mixed   $order_mode режим
	 * @return integer Позиция, наче 1
	 */
  function GetTopMessagePosition($message_id, $author_id = NULL,  $order_mode = self::OM_TH_NEW)
  {
    global $DB;
    $lefts = '';
    
    switch($order_mode)
    {
      case self::OM_TH_NEW     : $lefts .= ' mx.created_time > ms.created_time'; break;
      case self::OM_TH_POPULAR : $lefts .= ' (tx.a_count >= t.a_count AND (tx.a_count > t.a_count OR mx.id < ms.id))'; break;
      case self::OM_TH_ACTUAL : $lefts .= ' tx.last_activity > t.last_activity'; break;
      case self::OM_TH_MY     : $lefts .= ' mx.modified_time > ms.modified_time'; break;
    }

    $sql = "SELECT COUNT(mx.id) as position
              FROM commune_messages ms

            INNER JOIN
              commune_themes t
                ON t.id = ms.theme_id

            INNER JOIN
              commune_themes tx
                ON tx.commune_id = t.commune_id

            INNER JOIN
              commune_messages mx
                ON {$lefts}
               AND mx.theme_id = tx.id
               AND mx.parent_id IS NULL".
               (
                 ($author_id && $order_mode == self::OM_TH_MY) ? $DB->parse(" AND mx.user_id = ?i ", $author_id) : ''
               )."

             WHERE ms.id = ?i
               AND ms.parent_id IS NULL";


    $ret = $DB->val($sql, $message_id);

    return ($ret?($ret+1):1);
  }
	/**
	 * Получаем данные по автору сообщения.
	 *
	 * @param integer $message_id ИД сообщения
	 * @return array Данные по выборке, наче null
	 */
  function GetMessageAuthor($message_id)
  {
    global $DB;
    $sql = "SELECT u.uid, u.login, u.usurname, u.uname, u.subscr
              FROM commune_messages ms
            INNER JOIN
              users u
                ON u.uid = ms.user_id
             WHERE ms.id = ?i";

    return $DB->row($sql, $message_id);
  }

	/**
	 * Регистрируем, редактируем сообщение.
	 *
	 * @param mixed   $fields           Поля выборки 
	 * @param integer $commune_id       ИД сообщества
	 * @param integer $user_id          Тот, кто правит или тот, кто изменяет сообщение.
	 * @param integer $message_id       ИД сообщения
	 * @param mixed   $attach           Вложение
	 * @param string  $question         Вопрос для опросов. Если NULL, то голосование не добавляется и не изменяется.
	 * @param array   $new_answers      Массив с новыми ответами, которые необходимо добавить в опросам
	 * @param array   $answers_exists   Массив с существующими ответами на вопросы, в котором: индекс - id ответа, значение - текст ответа.
	 *                                  Если прав на изменение ответов нет, то текст ответа любой, но в текущем варианте права есть всегда.
	 *                                  Если ответа нет в этом массиве, то он удалится и из таблицы БД.
	 * @return string array Данные по выборке, наче null
	 */
  function CreateMessage($fields, $commune_id, $user_id, $message_id = NULL, $attach = NULL, $question = NULL, $new_answers = NULL, $answers_exists = NULL, $multiple = NULL)
  {
  	validate_code_style($fields["msgtext"]);
    global $DB;
    $pos = $fields['pos'] ? $fields['pos'] : 'NULL'; // позиция для топиков, которые закрепляются сверху (если -1, значит не изменять позицию)
    $pos_updated = FALSE;
    $close_comments = $fields['close_comments'] ? 't' : 'f';
    $is_private = $fields['is_private'] ? 't' : 'f';
    
    $is_edit = false;
    if($message_id===NULL)
    {
      $parent_id = $fields['parent_id'];
      if ($pos < 0) $pos = 'NULL';

      if($parent_id!==NULL && $parent_id!='') {
        
        $sql = "SELECT theme_id FROM commune_messages WHERE id = ?";
        $theme_id = $DB->val($sql, $parent_id);
        if($DB->error) return NULL;
      }
      else {
        $sql = "SELECT re_pos_commune_themes({$commune_id}, NULL, {$pos});
                INSERT INTO commune_themes (commune_id, pos, close_comments, is_private, category_id) VALUES ({$commune_id}, {$pos}, '{$close_comments}', '{$is_private}', ".($fields['category_id']==0?'NULL':$fields['category_id']).") RETURNING id";
        $theme_id = $DB->val($sql);
        if(!$DB->error) {
          $pos_updated = TRUE;
        }
        else
          return NULL; // !!! ошибку выдать.

        $parent_id = null;
      }
      
      $sModVal = is_pro() ? 'NULL' : '0';
      $insert_table = self::getTableName('commune_messages', $commune_id);
      $sql = "INSERT INTO {$insert_table} (parent_id, theme_id, user_id, msgtext, title,  youtube_link, moderator_status)
             VALUES (?, ?, ?, ?, ?, ?, $sModVal) RETURNING id, theme_id, parent_id";
      $res = $DB->query($sql, $parent_id, $theme_id, $user_id, $fields['msgtext'], $fields['title'], $fields['youtube_link']);
    }
    else {
        $sUserId = $DB->val( 'SELECT user_id FROM commune_messages WHERE id = ?i', $message_id );
        $u_status = commune::GetUserCommuneRel( $commune_id, get_uid(false));   
        if ( $sUserId != $_SESSION['uid'] && !hasPermissions('communes') && !( $u_status['is_moderator']==1 || $u_status['is_admin']==1 || $u_status['is_author']==1 )) {
            return 0; // Чужой топик редактировать нельзя если ты не админ сайта
        }
        
      $is_edit = true;
      
        $sModer = '';
        $sql    = '';

        if ( $user_id == $_SESSION['uid'] && !hasPermissions('communes') && !is_pro() ) {
            // автор, не админ, не про - отправить на модерирование
            $sModer = ' , moderator_status = 0 ';
        }
      
      $sql .= "UPDATE commune_messages 
                 SET modified_id = ?i,
				     modified_time = LOCALTIMESTAMP,
                     msgtext = ?,
                     title = ?,
                     youtube_link = ?". $sModer .
                     ( ''
                       //$attach!==NULL
                       //? ", attach = '{$attach}', small = '{$fields['small']}'"
                       //: ''
                     )."
               WHERE id = ?i RETURNING id, theme_id, parent_id";
      $res = $DB->query($sql, $user_id, $fields['msgtext'], $fields['title'], $fields['youtube_link'], $message_id);
    }

	if($res && pg_affected_rows($res)) {
	    list($message_id, $theme_id, $parent_id) = pg_fetch_row($res);
	    self::checkWysiwygInlineImages($message_id, $fields['msgtext'], $is_edit);
        
        if ( (!$is_edit || $user_id == $_SESSION['uid'] && !hasPermissions('communes')) && !is_pro() ) {
            /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $stop_words = new stop_words();
            $aEx  = is_array($answers_exists) && $answers_exists ? $answers_exists : '';
            $aNew = is_array($new_answers)    && $new_answers    ? $new_answers    : '';
            $nStopWordsCnt = $stop_words->calculate( $fields['msgtext'], $fields['title'], $question, $aEx, $aNew );
            $DB->insert( 'moderation', array('rec_id' => $message_id, 'rec_type' => user_content::MODER_COMMUNITY, 'stop_words_cnt' => $nStopWordsCnt) );*/
        }
        
        //
	    if($attach) {
                $max = self::getMaxSort($message_id);
                foreach($attach as $file) {
                    $max++;
                    //$at_sql = "INSERT INTO commune_attach (cid, fid, small,sort) VALUES('{$message_id}', '{$file->id}', '{$file->is_smalled}','{$max}')";
                    //$DB->squery($at_sql);
                    $file->updateFileParams(array('src_id' => $message_id, 'small' => $file->is_smalled, 'sort' => $max), false);
                }
	    }
     if($parent_id===NULL || $parent_id=='') {
        $sql = "UPDATE commune_themes SET close_comments = '{$close_comments}', is_private = '{$is_private}', category_id=".($fields['category_id']==0?'NULL':$fields['category_id'])." WHERE id = {$theme_id}";
        if(!$DB->squery($sql) ) return NULL;
     }

        if($theme_id && !$parent_id) {
          /* опросы */
		  $change_access = true;
		  if ($question !== NULL && $question != '') {
			$sql = "SELECT COUNT(*) FROM commune_poll WHERE theme_id = ?i";
			$p = $DB->val($sql, $theme_id);
			if ($p && $change_access) {
				$m = (($multiple !== NULL)? ($multiple? ", multiple = 't'": ", multiple = 'f'"): "");
				$sql = "UPDATE commune_poll SET question = '{$question}' {$m} WHERE theme_id = {$theme_id};";
			} else if (!$p) {
				$sql = "INSERT INTO commune_poll (theme_id, question, closed, multiple) VALUES ({$theme_id}, '{$question}', 'f', ".($multiple? "'t'": "'f'").");";
			}
			$sql .= "DELETE FROM commune_poll_answers WHERE theme_id = {$theme_id}".(($answers_exists && is_array($answers_exists))? " AND id NOT IN (".implode(",", array_keys($answers_exists)).");": ";");
			if ($change_access && is_array($answers_exists)) {
				foreach ($answers_exists as $id=>$answer) {
					$sql .= "UPDATE commune_poll_answers SET answer = '{$answer}' WHERE id = {$id} AND theme_id = {$theme_id};";
				}
			}
			if (is_array($new_answers)) {
				foreach ($new_answers as $answer) {
					$sql .= "INSERT INTO commune_poll_answers (theme_id, answer) VALUES ({$theme_id}, '{$answer}');";
				}
			}
			if ($sql) {
				if (!$DB->squery($sql)) return NULL;
			}
		  } else {
			$DB->query("DELETE FROM commune_poll WHERE theme_id = ?i", $theme_id);
		  }
		  /* --- */
		  if(!$pos_updated && $pos != -1) {
            $sql = '';
            $sql .= "SELECT re_pos_commune_themes({$commune_id}, (SELECT pos FROM commune_themes WHERE id = {$theme_id}), {$pos});";
            $sql .= "UPDATE commune_themes SET pos = {$pos} WHERE id = {$theme_id}";
            $DB->squery($sql);
          }
        } 
        
        
        
      return (pg_fetch_result($res,0,0));
    }


    return NULL;
  }

  /**
   * Возвращает максимальный порядклвый номер файла прикрепленного к комментарию в теме сообщества.
   * 
   * @param  int $message_id ID комментария.
   * @return int
   */
  public static function getMaxSort($message_id){
      global $DB;
      $sql = "SELECT MAX(sort) FROM ".self::FILE_TABLE." WHERE src_id = ?";
      return (int) $DB->val($sql, $message_id);
  }
  
  /**
   * Возвращает информацию о прикрепленном файле по ID файла 
   *
   * @param  int $fid ID файла (см. таблицу file_commune)
   * @return array
   */
  public static function GetAttachByFID($fid){
//      global $DB;
//      $sql = "SELECT * FROM commune_attach WHERE fid = ?i";
//      return $DB->row($sql, $fid);
      return CFile::selectFilesById(self::FILE_TABLE, $fid);
  }

          /**
     * Функция меняет позицию раздела в сортировке на -1
     *
     * @param integer $id ИД файла
     * @return string Сообщение об ошибке
     */
    public static function MoveUp($id) {
        global $DB;
        $curr = self::GetAttachByFID($id);
        //$sql = "SELECT id, sort, cid FROM commune_attach WHERE cid = ?i AND sort = (SELECT MAX(sort) FROM commune_attach WHERE cid = ?i AND sort < ?i);";
        //$donor = $DB->row($sql, $curr['cid'], $curr['cid'], $curr['sort']);
        $where = "sort = (SELECT MAX(sort) FROM " . self::FILE_TABLE . " WHERE src_id = {$curr['src_id']} AND sort < {$curr['sort']})";
        $donor = CFile::selectFilesBySrc(self::FILE_TABLE, $curr['src_id'], NULL, $where);
        if ($donor) {
            $sql = "UPDATE " . self::FILE_TABLE . " SET sort = {$curr['sort']} WHERE id = {$donor['id']};
                    UPDATE " . self::FILE_TABLE . " SET sort = {$donor['sort']} WHERE id = {$curr['id']};";
            $DB->squery($sql);
            return $DB->error;
        }
        return false;
    }

            /**
     * Функция меняет позицию раздела в сортировке на +1
     *
     * @param integer $id ИД файла
     * @return string Сообщение об ошибке
     */
    public static function MoveDown($id) {
        global $DB;
        $curr = self::GetAttachByFID($id);
        //$sql = "SELECT id, sort, cid FROM commune_attach WHERE cid = ?i AND sort = (SELECT MIN(sort) FROM commune_attach WHERE cid = ?i AND sort > ?i);";
        //$donor = $DB->row($sql, $curr['cid'], $curr['cid'], $curr['sort']);
        $where = "sort = (SELECT MIN(sort) FROM " . self::FILE_TABLE . " WHERE src_id = {$curr['src_id']} AND sort > {$curr['sort']})";
        $donor = CFile::selectFilesBySrc(self::FILE_TABLE, $curr['src_id'], NULL, $where);
        if ($donor) {
            $sql = "UPDATE " . self::FILE_TABLE . " SET sort = {$curr['sort']} WHERE id = {$donor['id']};
                    UPDATE " . self::FILE_TABLE . " SET sort = {$donor['sort']} WHERE id = {$curr['id']};";
            $DB->squery($sql);
            return $DB->error;
        }
        return false;
    }

    /**
     * Возвращает файлы, прикрепленные к комментарию
     * 
     * @param  int $id_attach ID комментария
     * @param  bool $seeing_marks опционально. 
     *             установить в true если нужно получать только файлы не помеченные как удаленные
     * @return array
     */
    public static function GetAttach($id_attach, $seeing_marks = false){
//      global $DB;
//      $sql = "SELECT file.*, commune_attach.cid, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid = ?i ".($seeing_marks ? " AND commune_attach.is_deleted <> true " : '')." ORDER BY commune_attach.sort";
//      $ret = $DB->rows($sql, $id_attach);
//      return $ret;
        $where = $seeing_marks ? "deleted <> true" : NULL;
        return CFile::selectFilesBySrc(self::FILE_TABLE, $id_attach, 'sort', $where);
    }
    
    /**
     * Получить только теприкрепленные к комментарию, которые помеченны как удаленные
     *
     * @param  int $id_attach ID комментария
     * @return array
     */
    public static function GetMarkedAttach($id_attach){
//      global $DB;
//      $sql = "SELECT file.*, commune_attach.cid, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid = ?i AND commune_attach.is_deleted = true ORDER BY commune_attach.sort";
//      $ret = $DB->rows($sql, $id_attach);
//      return $ret;
        return CFile::selectFilesBySrc(self::FILE_TABLE, $id_attach, 'sort', 'deleted = true');
    }

    /**
     * Удалить файл, прикрепленный к комментарию
     * 
     * @param  int $cid ID комментария
     * @param  int $id_attach ID файла
     * @param  bool $mark_only опционально. установить в true если нужно только пометить файл как удаленный
     * @return bool true - успех, false - провал
     */
    public static function DeleteAttach($cid, $id_attach, $mark_only = false){
      global $DB;
      //$sql = "SELECT file.*, commune_attach.cid, commune_attach.small, commune_attach.id AS att_id, commune_attach.sort AS att_sort FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid = ?i AND commune_attach.fid = ?i";
      //$ret = $DB->row($sql, $cid, $id_attach);
      $ret = CFile::selectFilesBySrc(self::FILE_TABLE, $cid, 'sort', "id = {$id_attach}");
      $login = $_SESSION['login'];
      $dir = "users/".substr($login,0,2)."/".$login."/upload/";
      $file = $ret['fname'];
      $file = str_replace('sm_', '', $file);
      
      $cfile = new CFile($ret['id']);
      if ($file && $login){
          if($mark_only){// только пометить на удаление
              $cfile->updateFileParams(array('deleted'=>true), false);
              //$sql = "UPDATE commune_attach SET is_deleted = true WHERE id = {$ret['att_id']}";
              //$DB->squery($sql);
          }else{
              $cfile->Delete(0,$dir,$file);
              $cfile->Delete(0,$dir,'sm_'.$file);
//              $sql = "DELETE FROM commune_attach WHERE id = {$ret['att_id']}";
//              $DB->squery($sql);
          }
          $sql = "UPDATE " . self::FILE_TABLE . " SET sort = sort-1 WHERE sort > {$ret['sort']} AND src_id = {$cid}";
          $DB->squery($sql);
          $sql = "SELECT max(sort) FROM " . self::FILE_TABLE . " WHERE id <> {$ret['id']} AND src_id = {$cid}";
          $max_sort = $DB->val($sql)+1;
          $sql = "UPDATE " . self::FILE_TABLE . " SET sort = {$max_sort} WHERE id = {$ret['id']} AND src_id = {$cid}";
          $DB->squery($sql);
          return true;
      }
      return false;
    }
    
    /**
     * Реально удалить все файлы комментария, помеченные как удаленные.
     * 
     * @param  int $cid ID комментария
     * @return bool true
     */
    public static function DeleteMarkedAttach($cid){
        if(!$uid = get_uid(false)) return false;
        $list = self::GetMarkedAttach($cid);
        if(count($list)){
            foreach ($list as $item){
                self::DeleteAttach($cid, $item['id']);
            }
        }
        return true;
    }
    
    /**
     * Восстановить все файлы комментария, помеченные как удаленные.
     * 
     * @param  int $cid ID комментария
     * @return bool true - успех, false - провал
     */
    public static function RestoreMarkedAttach($cid){
        if(!$uid = get_uid(false)) return false;
        global $DB;
        
        $list = self::GetMarkedAttach($cid);
        if(count($list)){
            $in = array();
            foreach ($list as $item){
                 $in[] = $item['id'];
            }
            $sql = "UPDATE " . self::FILE_TABLE . " SET deleted = FALSE WHERE src_id = ?i;";
            $DB->query($sql, $cid);
            return true;
        }
        return false;
    }

	/**
	 * Удаление сообщества.
	 *
	 * @param integer $commune_id ИД сообщества
	 * @return integer 1 - если все ок, иначе 0
	 */
  function Delete($commune_id)
  {
    global $DB;
    $sql = "DELETE FROM commune WHERE id = ?i";
    if(($res = $DB->query($sql, $commune_id)) && pg_affected_rows($res))
      return 1;
    return 0;
  }

  /**
   * Блокирует сообщество
   *
   * @param integer $commune_id  id сообщества
   * @param string  $reason      причина
   * @param integer $uid         uid администратора (если 0, используется $_SESSION['uid'])
   * @return int                ID блокировки
   */
  function Blocked( $commune_id, $reason, $reason_id = null, $uid = 0 ) {
    if ( !$uid && !($uid = $_SESSION['uid']) ) return 'Недостаточно прав';
    
    $sql = 'INSERT INTO commune_blocked (commune_id, "admin", reason, reason_id, blocked_time) VALUES(?, ?, ?, ?, NOW()) RETURNING id';
    return $GLOBALS['DB']->val( $sql, $commune_id, $uid, $reason, $reason_id );
  }

 /**
   * разблокирует сообщество
   *
   * @param integer $commune_id  id сообщества
   * @return string Сообщение об ошибке
   */
  function UnBlocked( $commune_id ) {
     $GLOBALS['DB']->query( 'DELETE FROM commune_blocked WHERE commune_id = ?i', $commune_id );
     return $GLOBALS['DB']->error;
  }
  
  /**
   * Блокировка топика администратором free-lance.ru
   *
   * @param array   $topic массив информации о топике @see commune::GetTopMessageByAnyOther
   * @param string  $reason      причина
   * @param integer $uid         uid администратора (если 0, используется $_SESSION['uid'])
   * @param boolean $from_stream true - блокировка из потока, false - на сайте
   * @return string Сообщение об ошибке
   */
  function blockedCommuneTheme( $topic = array(), $reason = '', $reason_id = null, $uid = 0, $from_stream = false ) {
    if ( !$uid && !($uid = $_SESSION['uid']) ) return 'Недостаточно прав';
    
    if ( $topic ) {
        if ( !$from_stream ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $GLOBALS['DB']->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $topic['id'], user_content::MODER_COMMUNITY );
            $sId = $GLOBALS['DB']->val( 'UPDATE commune_messages SET moderator_status = ?i WHERE id = ?i', $uid, $topic['id'] );
        }
        
        $sql = 'INSERT INTO commune_theme_blocked (theme_id, "admin", reason, reason_id, blocked_time) VALUES(?, ?, ?, ?, NOW())';
        $GLOBALS['DB']->query( $sql, $topic['theme_id'], $uid, $reason, $reason_id );
        
        if(!$from_stream) {
          // отправляем уведомление о блокировке
          require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
        
          messages::SendBlockedCommuneTheme( $topic, $reason );
        }
        
        return $GLOBALS['DB']->error;
    }
    else {
		return 'Несуществующее сообщение в сообществе';
	}
  }

 /**
   * Разблокировка топика администратором free-lance.ru
   *
   * @param integer theme_id  id топика
   * @return string Сообщение об ошибке
   */
  function unblockedCommuneTheme( $theme_id ) {
     $GLOBALS['DB']->query( 'DELETE FROM commune_theme_blocked WHERE theme_id = ?i', $theme_id );
     return $GLOBALS['DB']->error;
  }
  
    /**
     * Возвращает заблокированные сообщества
     *
     * @param integer  $nums          возвращает кол-во заблокированных сообществ
     * @param string   $error		  возвращает сообщение об ошибке
     * @param integer  $page          номер страницы
     * @param string   $sort          тип сортировки
     * @param string   $search        строка для поиска
     * @param integer  $admin         uid модератора, заблокированные сообщества которого нужно показать
     * @return array				  [[массив с сообществами]]
     */
    function GetBlockedCommunes(&$nums, &$error, $page=1, $sort='', $search='', $admin=0) {
        global $DB;
        $limit = commune::MAX_ON_PAGE;
        $offset = $limit*($page-1);
        $limit_into = false;
        $count_cahce = false;
        // сортировка
        if ($search) {
            switch ($sort) {
                case 'btime':
                    $order = "ORDER BY blocked_time DESC";
                break;
                case 'login':
                    $order = "ORDER BY login";
                break;
                default:
                    $order = "ORDER BY relevant DESC";
                break;
            }
        } else {
            switch ($sort) {
                case 'btime':
                    $order = "ORDER BY commune_blocked.blocked_time DESC";
                    $limit_into = true;
                break;
                case 'login':
                    $order = "ORDER BY login";
                break;
                default:
                    $order = "ORDER BY commune_blocked.commune_id";
                    $limit_into = true;
                break;
            }
        }
        $sql = "
            SELECT
                commune.*,
                users.login, users.uname, users.usurname, users.photo, users.is_pro, users.is_profi, users.is_team,
                commune_blocked.commune_id, commune_blocked.reason AS blocked_reason, commune_blocked.blocked_time,
                admins.login AS admin_login, admins.uname AS admin_uname, admins.usurname AS admin_usurname,
                extract(year from age(commune.created_time::date)) as year, 
                extract(month from age(commune.created_time::date)) as month, 
                extract(day from age(commune.created_time::date)) as day
            FROM
            " . ($limit_into? "(SELECT * FROM commune_blocked ".($admin? "WHERE commune_blocked.admin = '$admin'": "")." $order LIMIT $limit OFFSET $offset) AS commune_blocked": "commune_blocked") . "
            JOIN
                commune ON commune_blocked.commune_id = commune.id
            LEFT JOIN
                users ON users.uid = commune.author_id
            LEFT JOIN
                users AS admins ON commune_blocked.admin = admins.uid
        " . (($admin && !$limit_into)? "WHERE commune_blocked.admin = '$admin'": "");
        if ($search) {
            $w = preg_split("/\\s/", $search);
            for ($i=0; $i<count($w); $i++) {
                $s .= "(
                    CASE
                    WHEN
                        (LOWER(login) = LOWER('{$w[$i]}') OR LOWER(uname) = LOWER('{$w[$i]}') OR LOWER(usurname) = LOWER('{$w[$i]}') OR LOWER(name) = LOWER('{$w[$i]}')) THEN 2
                    WHEN
                        (LOWER(login) LIKE LOWER('%{$w[$i]}%') OR LOWER(uname) LIKE LOWER('%{$w[$i]}%') OR LOWER(usurname) LIKE LOWER('%{$w[$i]}%') OR LOWER(name) LIKE LOWER('%{$w[$i]}%')) THEN 1
                    ELSE 0
                    END
                ) + ";
                $t .= "(LOWER(login) LIKE LOWER('%{$w[$i]}%') OR LOWER(uname) LIKE LOWER('%{$w[$i]}%') OR LOWER(usurname) LIKE LOWER('%{$w[$i]}%') OR LOWER(name) LIKE LOWER('%{$w[$i]}%')) OR ";
            }
            $s = substr($s, 0, strlen($s) - 3);
            $t = substr($t, 0, strlen($t) - 4);
            $sql  = "SELECT s.*, ($s) AS relevant FROM ($sql) AS s WHERE $t";
            $csql = "
                SELECT COUNT(*) 
                FROM (
                    SELECT users.login, users.uname, users.usurname, commune.name
                    FROM commune_blocked 
                    JOIN commune ON commune.id = commune_blocked.commune_id 
                    LEFT JOIN users ON commune.author_id = users.uid
                    " . ($admin? "WHERE commune_blocked.admin = '$admin'": "") . "
                ) AS s
                WHERE $t
            ";
        } else {
            $count_cache = true;
            $csql = "SELECT COUNT(*) AS cnt FROM commune_blocked JOIN commune ON commune.id = commune_blocked.commune_id".($admin? " WHERE commune_blocked.admin = '$admin'": "");
        }
        //echo "<pre>" . "$sql $order" . ($limit_into? "": " $order LIMIT $limit OFFSET $offset") . "</pre>";
        //echo "<br><pre>$csql</pre>";
        if ($count_cache) {
            $memBuff = new memBuff();
            $row  = $memBuff->getSql($error, $csql, 180);
            $nums = (int) $row[0]['cnt'];
        } else {
            $nums = $DB->val($csql);
        }
        $ret = $DB->rows("$sql $order" . ($limit_into? "": " LIMIT $limit OFFSET $offset"));
        return $ret;
    }

    /**
     * Возвращает кол-во заблокированных проектов
     *
     * @return integer
     */    
    function NumsBlockedCommunes() {
        $sql = "SELECT COUNT(*) AS cnt FROM commune_blocked JOIN commune ON commune.id = commune_blocked.commune_id";
        $memBuff = new memBuff();
        $row = $memBuff->getSql($error, $sql, 180);
        return (int) $row[0]['cnt'];
    }


  /**
   * Удаление сообщение.
   *
   * @param integer $message_id     Ид сообщения
   * @param integer $user_id        Кто удаляет
   * @param integer $user_mod       Права удаляющего
   * @param string  &$deleted_time  Время удаления комментария 
   * @return integer 1 если удаление прошл успешно, иначе 0
   */
  function DeleteMessage($message_id, $user_id, $user_mod=0, &$deleted_time)
  {
    global $DB;
    $message = commune::GetTopMessageByAnyOther( $message_id, $_SESSION['uid'], $user_mod );

    if(get_uid(false)) {
      $comm = commune::getCommuneIDByMessageID($message_id);
      $status = commune::GetUserCommuneRel($comm, get_uid(false));
    }
    
    if ( $message['user_id'] != get_uid(false) && !hasPermissions('communes') && !($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1) ) {
        return 0; // Чужой топик удалять нельзя если ты не админ сайта
    }
    
    if(!$message['parent_id'])
    {     
      $uid = get_uid( false );
      $deleted_time = date('Y-m-d H:i:s');
      //modified_id обновляю исключительно ради получения имени удалившего модератора, т к в запросе итак предостаточно джоинов. Если спама об редактировании не начнет приходить, то думаю можно оставить
      $sql = "UPDATE commune_messages SET deleted_id = {$uid}, modified_id = {$uid}, deleted_time = '{$deleted_time}' WHERE id = ?i RETURNING theme_id, parent_id";
      if($res = $DB->query($sql, $message_id))
      {
        if(pg_affected_rows($res)) {
          // Пересчитать позиции
          list($theme_id, $parent_id) = pg_fetch_row($res);
          if(!$parent_id && $theme_id) {
            $sql = "SELECT re_pos_commune_themes(?i, (SELECT pos FROM commune_themes WHERE id = ?i), NULL);";
            $DB->query($sql, $message['commune_id'], $theme_id);
          }
        
          // уведомление об удалении, если удаляет админ
          if ( $message['user_id'] != $user_id ) {
              require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
              $commune = self::GetCommune($message['commune_id'], get_uid(0));
              $member = self::getMember($message['commune_id'], get_uid(0), self::MEMBER_ADMIN | self::MEMBER_MODERATOR);
              if ($commune['author_uid'] == get_uid(0)) {
                  $deleter = 'admin';
              } elseif ($member) {
                  $deleter = 'moder';
              } else {
                  $deleter = 'site-moder';
              }
              messages::SendDeletedCommuneTheme( $message, $deleter );
          }
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $message_id, user_content::MODER_COMMUNITY );
          
          return 1;
        }
        else; // Не удалилось. Значит будем делать UPDATE.
      }
      else
        return 0;
    }

    $sql = "UPDATE commune_messages
               SET deleted_time = LOCALTIMESTAMP,
                   deleted_id = ?i,
                   modified_id = ?i
            WHERE id = ?i RETURNING *";

    if($res = $DB->query($sql, $user_id, $user_id, $message['id'])) {
      return -1;
    }
    
    return 0;
  }

	/**
	 * Создает сообщество.
	 *
	 * @param integer $group_id   		ИД группы
	 * @param integer $author_id  		ИД автора  
	 * @param string  $name       		Название сообщества
	 * @param string  $descr      		Описание сообщества
	 * @param mixed   $image      		Изображение сообщества
	 * @param integer $restrict_type 	Тип вступления в сообщество(закрытый открытый)
	 * @param string  $small      		мелкое изображение
	 * @param integer $id         		ИД
	 * @return integer 1 если все прошло успешно, иначе 0
	 */
  function CreateCommune($group_id, $author_id, $name, $descr, $image, $restrict_type, $small, $id = NULL)
  {
    global $DB;


    if($id===NULL) 
      $sql = "INSERT INTO commune (group_id, author_id, name, descr, image, restrict_type, small)
              VALUES ({$group_id}, {$author_id}, '{$name}', '{$descr}', '{$image}', {$restrict_type}::bit(".self::RESTRICT_TYPE_SIZE."), '{$small}')";
    else
      $sql = "UPDATE commune 
                 SET group_id = {$group_id},
                     author_id = {$author_id},
                     name = '{$name}',
                     descr = '{$descr}',".
                     ( $image!==NULL ? " image = '{$image}'," : '' )."
                     restrict_type = {$restrict_type}::bit(".self::RESTRICT_TYPE_SIZE."),
                     small = '{$small}'
               WHERE id = {$id}";

    if($res = $DB->query($sql))
      return 1;
    return 0;
  }

	/**
	 * Принимает/отвергает пользователя.
	 *
	 * @param integer $member_id ИД пользователя
	 * @param boolean $undo      Тип (true-удалить, false-принять)
	 * @return unknown  Возвращает uid пользователя, если все прошло. Нужен для отправки ему уведомления.
	 */
  function AcceptMember($member_id, $undo=NULL)
  {
    global $DB;
    $sql = "SELECT is_banned, ban_where FROM users WHERE uid=(SELECT user_id FROM commune_members WHERE id=?i)";
    $user = $DB->row($sql, $member_id);

    if(!($user['is_banned']=='1' && $user['ban_where']=='0')) {
      if($undo)
        // DELETE (из-за триггеров видимо) не возвращает pg_affected_rows...
        $sql = "UPDATE commune_members SET is_deleted = true WHERE id = ?i RETURNING user_id";
      else
        $sql = "UPDATE commune_members SET is_accepted = true WHERE id = ?i RETURNING user_id";
      $ret = $DB->val($sql, $member_id);
    }
    return ($ret?$ret:0);
  }

	/**
	 * Присоединение в сообществу
	 *
	 * @param integer  $commune_id Ид сообщества
	 * @param integer  $user_id    ИД пользователя
	 * @param boolean  $out        Операция над пользователем (true-пользователь выходит из сообщества, входит в сообщество)
	 * @return unknown
	 */
  function Join(                                          //
   $commune_id,
   $user_id,
   $out = FALSE) // TRUE: 
  // Сообщество может быть закрытым или открытым.
  // Если сообщество закрытое, тогда ставим статус is_accepted=false,
  // направляем письмо создателю, возвращаем JOIN_STATUS_ASKED.
  // Если участник выходит из сообщества, то пытаемся его удалить. В этом случае,
  // если его еще не подтвердили он реально удалится, т.к. он не мог оставить ни одного
  // сообщения в сообществе (см. триггер). Иначе, от будет помечен флагом is_deleted и все
  // его сообщностные данные будут сброшены.
  // Если участник до вызова функции был удален, то в зависимости от типа сообщества
  // устанавливаем is_accepted и делаем is_deleted=false. Об остальном позаботится триггер.
  {
    global $DB;
    if($out) {
      // забаненый в сообществе пользователь не может выйти из него (иначе будет иметь возможность войти в него и быть уже не забаненым)
      if (self::isUserBanned($commune_id, $user_id)) {
          return self::JOIN_STATUS_ACCEPTED;
      }
      $sql = "DELETE FROM commune_members WHERE commune_id=?i AND user_id=?i";
      if($res = $DB->query($sql, $commune_id, $user_id))
        return self::JOIN_STATUS_DELETED;
    }
    else
    {
      $jRStrMask = self::RESTRICT_JOIN_MASK;

      // Пытаемся восстновить.
      $sql = "UPDATE commune_members m
                 SET is_deleted = false,
                     is_accepted = NOT ((cm.restrict_type & {$jRStrMask}::bit(".self::RESTRICT_TYPE_SIZE."))::int::bool)
                FROM commune cm
               WHERE cm.id = ?i
                 AND m.user_id = ?i
                 AND m.commune_id = cm.id
              RETURNING is_accepted::int";
      $ret = $DB->val($sql, $commune_id, $user_id);
      if(!$DB->error && $ret)
        return NULL;

      if($ret)  // Пользователь был восстановлен.
        return ( $ret ? self::JOIN_STATUS_ACCEPTED : self::JOIN_STATUS_ASKED ); // !!! проверить.

      // Пытаемся создать. Нельзя добавлять автора сообщества в члены. Хотя, по идее, такой ситуации не должно просто возникать.
      $sql = "INSERT INTO commune_members (commune_id, user_id, is_accepted)
              SELECT commune.id, ?i,
                     NOT ((restrict_type & {$jRStrMask}::bit(".self::RESTRICT_TYPE_SIZE."))::int::bool)
                FROM commune
              LEFT JOIN commune_members cm ON cm.commune_id = commune.id AND cm.user_id = ?i
               WHERE commune.id = ?i
                 AND author_id <> ?i
                 AND cm.user_id IS NULL
               RETURNING is_accepted::int";

      $ret = $DB->val($sql, $user_id, $user_id, $commune_id, $user_id);
      
      if($DB->error) return NULL; // !!!skif!!! не понятное условие было if(!$DB->error || !ret) мешало для следующей проверки $ret и отсылания уведомления

      return ( $ret ? self::JOIN_STATUS_ACCEPTED : self::JOIN_STATUS_ASKED ); // !!! проверить.
    }

    return NULL;
  }
	/**
	 * Обновляем админов сообщества
	 *
	 * @param integer $member_id    ИД обновления
	 * @param integer $note         Заметка
	 * @param boolean $is_moderator Модератор или нет
	 * @param boolean $is_manager   Менеджер или нет
	 * @return integer 1 - обновление прошло успешно, иначе 0
	 */
  function UpdateAdmin($member_id, $note, $is_moderator, $is_manager, $comm=false) {
    global $DB;
    
    $info = $this->GetCommuneByMember($member_id);
    $sql = "UPDATE commune_members
               SET note = ?,
                   is_moderator=".($is_moderator ? 'true' : 'false').",
                   is_manager=".($is_manager ? 'true' : 'false')."
             WHERE id = ?i";
    
    
    if($DB->query($sql, $note, $member_id)) {
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/smail.php");
        $sm = new smail();
        if(($info['is_admin'] == 'f') && ($is_moderator == true || $is_manager == true)) {
            $sm->CommuneMemberAction($info['member_user_id'], 'do.Add.admin', $comm);
        }
        if($is_moderator == false && $is_manager == false) {
        	$sm->CommuneMemberAction($info['member_user_id'], 'do.Remove.admin', $comm);    
        }
        return 1;
    }

    return 0;
  }
	/**
	 * Добавляем админа сообщества
	 *
	 * @param integer $commune_id ИД сообщества
	 * @param string  $user_login Логин добавляемого
	 * @param mixed   $error      Ошибка
	 * @return integer 1 - все прошло успешно, 0 - ошибка запроса, -1 - ошибка выборки
	 */
  function AddAdmin($commune_id, $user_login, &$error = NULL)
  {
    global $DB;
    // !!! А забаненных?
    $sql = "UPDATE commune_members m       
               SET is_moderator=true,
                   is_manager=true,
									 is_banned=false,
									 warn_count=0
              FROM users u
             WHERE u.login ILIKE '{$user_login}'
               AND m.commune_id = {$commune_id}
               AND m.user_id = u.uid
               AND m.is_accepted=true
               AND m.is_deleted=false";

    if(!($res = $DB->query($sql, $user_login, $commune_id))) {
      $error = $DB->error;
      return 0;
    }

    if(!pg_affected_rows($res))
      return -1;

    return 1;
  }
  
	/**
	 * Удаляем админа
	 *
	 * @param integer $member_id ИД удаляемого
	 * @return integer 
	 */
  function RemoveAdmin($member_id)
  {
    global $DB;
    $sql = "UPDATE commune_members
               SET is_moderator=false,
                   is_manager=false,
                   is_admin=false
             WHERE id = ?i
            RETURNING user_id";

    $ret = $DB->val($sql, $member_id);

    return ($ret?$ret:0);
  }


	/**
	 * Баним комментарий члена собщества
	 *
	 * @param integer $member_id Ид того кого баним
	 * @return integer
	 */
  function BanMemberForComment($member_id, $commune_id = false, &$warn_count=0)
  {
    global $DB;
    $sql = "UPDATE commune_members
               SET is_banned = (warn_count >= 3),
                   warn_count = warn_count + 1
             WHERE id = ?i
               AND (is_banned = false OR warn_count < 3)
               AND is_accepted = true
               ".( $commune_id ? "AND commune_id = ?i" : "" )."
            RETURNING warn_count";
    $ret = $DB->val($sql, $member_id, $commune_id);
    $warn_count = $ret;
    return ($ret ? (int)( $ret >= 3) : 0);
  }

  /**
   * Выбрать инфу про члена сообщества по его ИД
   *
   * @param integer $member_id ИД пользователя
   * @return array
   */
  function GetCommuneByMember($member_id)
  {
    global $DB;
    $sql = "SELECT cm.*, m.user_id as member_user_id, m.is_admin FROM commune_members m INNER JOIN commune cm ON cm.id = m.commune_id WHERE m.id = ?i";
    return $DB->row($sql, $member_id);
  }

  
  /**
   * Проверяет, если у пользователя доступ к просмотрю или комментированию/голосованию (в общем участию) в теме
   * Удобно использовать для xajax функций.
   *
   * @param   integer   $user_id   uid пользвоателя, которого нужно проверить
   * @param   integer   $theme_id  id темы, в которой нужно проверить права
   * @param   boolean   $part      TRUE - проверить возможность участия в теме, FALSE - проверить только возможность чтения
   * @return  boolean              TRUE - доступе есть, иначе FALSE
   */
  function AccessToTheme($user_id, $theme_id) {
    global $DB;
	$sql = "
		SELECT
			cm.user_id AS author_id, 
			m.user_id AS member_id, m.is_banned AS member_banned, u.is_banned AS site_banned, u.role AS member_role, m.is_moderator,
			c.author_id AS commune_author_id, a.is_banned AS commune_author_banned,
			c.restrict_type, b.commune_id::boolean AS commune_blocked
		FROM
			commune_themes t
		JOIN
			commune c ON t.commune_id = c.id
		JOIN
			commune_messages cm ON t.id = cm.theme_id AND parent_id IS NULL
		JOIN
			users a ON c.author_id = a.uid
		LEFT JOIN
			commune_members m ON t.commune_id = m.commune_id AND m.user_id = ?i AND m.is_accepted = 't'
		LEFT JOIN
			users u ON m.user_id = u.uid
		LEFT JOIN
			commune_blocked b ON m.commune_id = b.commune_id
		WHERE
			t.id = ?i";
	$row = $DB->row($sql, intval($user_id), intval($theme_id));
	if (!$row) return self::ACL_DENIED;
	// если модератор сайта
	if (hasPermissions('communes',$row['member_id'])) {
		return self::ACL_MODER;
	// если автор сообщества, то полный доступ или чтение если если сообщество заблокировано
	} else if ($row['commune_author_id'] == $user_id && !$row['commune_author_banned']) {
		return ($row['commune_blocked'] == 't')? self::ACL_READ: self::ACL_MODER;
	// если автор темы и он соостоит в сообществе или модератор, то полный доступ
	} else if ((($row['author_id'] == $user_id && $row['member_id'] == $user_id) || $row['is_moderator'] == 't') && !$row['site_banned'] && $row['commune_blocked'] != 't') {
		return ($row['member_banned'] == 't')? self::ACL_READ: self::ACL_MODER;
	} else if ($row['member_id']) {
		return self::ACL_COMMENTS;
	} else if (!($row['restrict_type'] & self::RESTRICT_READ_MASK)) {
		return self::ACL_READ;
	}
	return self::ACL_DENIED;
  }
  
  /**
   * Забанить члена сообщества
   *
   * @param integer $member_id ИД пользователя
   * @return integer
   */
  function BanMember($member_id)
  {
    global $DB;
    $sql = "UPDATE commune_members SET warn_count = CASE WHEN is_banned THEN 0 ELSE warn_count END, is_banned = NOT(is_banned)
             WHERE id = ?i AND is_accepted = true RETURNING is_banned";
    $ret = $DB->val($sql, $member_id);
    return ($ret?($ret=='t' ? 1 : -1):0);
  }

/**
 * Добавить в избранное
 *
 * @param integer $message_id Ид сообщения
 * @param integer $user_id    ИД пользователя
 * @param integer $undo       Определяет добавить в избранное или нет(поле is_favorite)
 * @return integer
 */
  function AddFav($message_id, $user_id, $undo=0, $priority=0, $title="")
  {
    global $DB;
    if ($user_id != get_uid(0)) {
        return 0;
    }
    $lvt = NULL;
    $title = htmlspecialchars($title);
    $sql = "SELECT last_viewed_time FROM commune_users_messages WHERE message_id = ?i AND user_id = ?i";
    if(($res = $DB->query($sql, $message_id, $user_id)) && pg_num_rows($res)) {
        $lvt = pg_fetch_result($res,0,0);
    }

    if(pg_num_rows($res) && $lvt === NULL && $undo) {
        $sql = "DELETE FROM commune_users_messages  WHERE message_id = ?i AND user_id = ?i";

        if($DB->query($sql, $message_id, $user_id))
            return 1;
    }

    if(!pg_num_rows($res)) {
        $insert_table = self::getTableName('commune_users_messages', self::getCommuneIDByMessageID($message_id));
        $sql = "INSERT INTO {$insert_table} (message_id, user_id, is_favorite, last_viewed_time, current_count, priority, name_fav)
                SELECT ?i, ?i, true, ?u, t.a_count, ?i, cm.title
                FROM commune_themes t
                    LEFT JOIN commune_messages cm ON cm.theme_id = t.id
                WHERE cm.id = ?i";
        if ($DB->query($sql, $message_id, $user_id, $lvt, $priority, $message_id)) {
            return 1;
        }
    } else {
        $is_fav = $undo ? 0 : 1;
        if ($title) {
            $sql = "UPDATE commune_users_messages SET is_favorite = ?b, priority = ?i,
                        last_viewed_time = ?u, current_count = t.a_count, name_fav = ?u
                    FROM commune_themes t
                        LEFT JOIN commune_messages cm ON cm.theme_id = t.id
                    WHERE cm.id = ?i
                    AND commune_users_messages.message_id = ?i
                    AND commune_users_messages.user_id = ?i";
            if ($DB->query($sql, $is_fav, $priority, $lvt, $title, $message_id, $message_id, $user_id)) {
                return 1;
            }
        } else {
            $sql = "UPDATE commune_users_messages SET is_favorite = ?b, priority = ?i,
                        last_viewed_time = ?u, current_count = t.a_count
                    FROM commune_themes t
                        LEFT JOIN commune_messages cm ON cm.theme_id = t.id
                    WHERE cm.id = ?i
                    AND commune_users_messages.message_id = ?i
                    AND commune_users_messages.user_id = ?i";
            if ($DB->query($sql, $is_fav, $priority, $lvt, $message_id, $message_id, $user_id)) {
                return 1;
            }
        }
    }
    
    return 0;
  }
	
  /**
	 * Обновить заметку
	 *
	 * @param integer $member_id ИД пользователя
	 * @param stting  $note      Заметка
	 * @return integer 1 - все прошло успешно, 0 - нет
	 */
  function UpdateNote($member_id, $note)
  {
    global $DB;
    $sql = "UPDATE commune_members SET note = ? WHERE id = ?i";
    if($DB->query($sql, $note, $member_id))
      return 1;
    return 0;
  }

    /**
	 * Обновить личную заметку пользователя
	 *
	 * @param integer $user_id      ИД пользователя
	 * @param integer $commune_id   ИД сообщества   
	 * @param stting  $note         Заметка
	 * @return integer              1 - все прошло успешно, 0 - нет
	 */
    function UpdateNoteMP($user_id, $commune_id, $note)
    {
        global $DB;
        $ret = 0;
        $sql = "SELECT author_id FROM commune WHERE id = ?i";
        $author_id = $DB->val($sql, $commune_id, $user_id);
        $mod_id    = get_uid(false);
        $member = self::getMember($commune_id, $mod_id, self::MEMBER_ADMIN);
        if(hasPermissions('communes') || $user_id == $mod_id || $author_id == $mod_id || $member['user_id'] == $mod_id) {
            $sql = "SELECT id FROM commune_members_notes WHERE commune_id=?i AND user_id=?i";
            $id = $DB->val($sql, $commune_id, $user_id);
            if($id) {
                $sql = "UPDATE commune_members_notes SET note = ? WHERE id = ?i";
                if($DB->query($sql, $note, $id)) 
                    $ret = 1;
            } else {
                $sql = "INSERT INTO commune_members_notes(commune_id, user_id, note) VALUES(?i, ?i, ?)";
                if($DB->query($sql, $commune_id, $user_id, $note)) 
                    $ret = 1;
            }
        }

        return $ret;
    }
    
    /**
     * Берем одного из членов сообщества по ИД сообщества и ИД Пользователя
     * 
     * @global type $DB         Подключение к БД
     * @param type $commune_id  ИД Сообщества
     * @param type $user_id     ИД Пользователя
     * @param type $member_type Тип выборки по умолчанию self::MEMBER_ANY | self::JOIN_STATUS_ACCEPTED   
     * @return type 
     */
    function getMember($commune_id, $user_id, $member_type = 0x27) {
        global $DB;
        
        $mt = '';
        if($member_type != self::MEMBER_ANY) {
            $mt .= (($member_type & self::MEMBER_MODERATOR) ? 'm.is_moderator = true' : '');
            $mt .= (($member_type & self::MEMBER_MANAGER) ? ($mt?' OR ':'').'m.is_manager = true' : '');
            $mt .= (($member_type & self::MEMBER_ADMIN) ? ($mt?' OR ':'').'m.is_admin = true' : '');
            $mt .= (($member_type & self::MEMBER_SIMPLE) ? ($mt?' OR  ':'').'(m.is_admin = false AND m.is_moderator = false AND m.is_manager = false)' : '');
        }
        
        if($mt) {
            $mt = ' AND ('.$mt.') ';
        }
        
        if($member_type & self::JOIN_STATUS_DELETED) {
            $mt .= ' AND m.is_deleted = true ';
        } else {
            if($member_type & self::JOIN_STATUS_ACCEPTED) {
                if(!($member_type & self::JOIN_STATUS_ASKED)) {
                    $mt .= ' AND m.is_accepted = true ';
                }
            } else if($member_type & self::JOIN_STATUS_ASKED) {
                $mt .= ' AND m.is_accepted = false ';
            }
            $mt .= ' AND m.is_deleted = false ';
        }
        
        $sql = "SELECT m.*, u.login, u.photo, u.usurname, u.uname, u.role,
                       u.is_pro, u.is_profi, u.is_team, u.is_pro_test, u.reg_date, m.commune_id as note_commune_id,
                       m.user_id as note_user_id, cmn.note as note_txt
                FROM commune_members m
                INNER JOIN users u ON u.uid = m.user_id
                LEFT JOIN commune_members_notes cmn ON cmn.commune_id = m.commune_id AND cmn.user_id = m.user_id
                WHERE m.commune_id = ?i AND u.uid = ?i {$mt}";

        return $DB->row($sql, $commune_id, $user_id);
    }

	/**
	 * Голосование
	 *
	 * @param integer $commune_id ИД сообщества
	 * @param integer $user_id    Ид пользователя
	 * @param integer $vote       Голос
	 * @return  Возвращает: -1, если голос ПРОТИВ был принят; 1, если голос ЗА был принят;
   				0, если голос не принят или ошибка.
	  			Принят -- то есть, пошел в расчет, рейтинг изменился.
	 */
  function Vote( $commune_id, $user_id, $vote)
  {
    global $DB;
    if($vote!=1 && $vote!=-1)
      return 0;


    // !!! Везде где триггеры работают сделать нормальные комментарии.

    $vB = (int)self::GetUserVote($commune_id, $user_id);
    $sql = "INSERT INTO commune_votes (commune_id, user_id, vote) VALUES (?i, ?i, ?i)";


    if($DB->query($sql, $commune_id, $user_id, $vote)) {
    // Там, в общем, триггер что-то воротит с голосами.
    // Просто сравним голос ДО и голос ПОСЛЕ.
      $vA = (int)self::GetUserVote($commune_id, $user_id);

      return ( ($vA > $vB) - ($vA < $vB) );
    }

    return 0;
  }

    	/**
	 * Голосование
	 *
	 * @param integer $commune_id ИД сообщества
	 * @param integer $user_id    Ид пользователя
	 * @param integer $rating       Голос
	 * @return  Возвращает: -1, если голос ПРОТИВ был принят; 1, если голос ЗА был принят;
   				0, если голос не принят или ошибка.
	  			Принят -- то есть, пошел в расчет, рейтинг изменился.
	 */
  function TopicVote( $topic_id, $user_id, $rating)
  {
    global $DB;      
    $vB = (int)self::GetUserTopicVote($topic_id, $user_id);
    if($vB == 0) $rating = $rating > 0 ? 1: -1;
    elseif($vB < 0 && $rating > 0) $rating = 0;
    elseif($vB > 0 && $rating < 0) $rating = 0;
    elseif($vB == $rating) return 0;
    elseif($vB < 0 && $rating < 0) return 0;
    elseif($vB > 0 && $rating > 0) return 0;
    $sql = "UPDATE commune_users_messages SET rating = ?i
            WHERE message_id = ?i
                AND user_id = ?i;";
    $res = $DB->query($sql, $rating, $topic_id, $user_id);

    if(!pg_affected_rows($res)) {
        $insert_table = self::getTableName('commune_users_messages', self::getCommuneIDByMessageID($topic_id));
        $sql = "INSERT INTO {$insert_table} (message_id, user_id, rating)
                VALUES (?i, ?i, ?i)";
        $res = $DB->query($sql, $topic_id, $user_id, $rating);
    }
    $rate = self::GetTopicRating($topic_id);
    $actionRate = commune_carma::actionByRate($rate, commune_carma::getScale('post'));
    if($actionRate == 'banned') {
        $themes= current( commune::getCommunePostByIds(array($topic_id)) );
        if( !commune_carma::isImmunity($themes['user_id'], array(), $topic_id) && $themes['id'] > 0) {
            commune::BlockedTopic( $themes['theme_id'], $topic_id, 'block' );
        }
    }
//    $vB = (int)self::GetUserTopicVote($topic_id, $user_id);

      $vA = (int)self::GetUserTopicVote($topic_id, $user_id);
      return ( ($vA > $vB) - ($vA < $vB) );
  }


  /**
   * Получить рейтинг комментария.
   * 
   * @param  int $topic_id ID комментария
   * @return int
   */
  public static function GetTopicRating($topic_id){
      global $DB;
      $sql = "SELECT rating FROM commune_messages WHERE id = ?i";
      $ret = $DB->val($sql, $topic_id);
      return ($ret?$ret:0);
  }

  

	/**
	 * Проголосовать в опросах
	 * 
	 * @param   integer   $user_id     id пользователя
	 * @param   integer   $answer_id   id ответа
	 * @return  boolean                результат операции
	 */	
	function Poll_Vote($uid, $answers, &$error) {
        global $DB;
		$error = "";
		$poll = $DB->row("
			SELECT
				p.theme_id, p.closed, p.multiple, COUNT(a.id) AS answers
			FROM
				commune_poll_answers a
			INNER JOIN
				commune_poll p ON a.theme_id = p.theme_id
			WHERE
				a.id IN (?l)
			GROUP BY
				p.theme_id, p.closed, p.multiple
		", $answers);
		if (count($answers) != $poll['answers']) {
			$error = "Опрос не найден";
			return FALSE;
		}
		elseif ($poll['closed'] == "t") {
			$error = "Опрос закрыт";
			return FALSE;
		}
		elseif ($this->Poll_Voted($uid, $poll['theme_id'])) {
			$error = "Вы уже голосовали в этом опросе";
			return FALSE;
		}
		$data = array();
		$max = ($poll['multiple'] == 't')? count($answers): 1;
		for ($i=0; $i<$max; $i++) {
			$data[$i] = array(
				'user_id'   => $uid,
				'theme_id'  => $poll['theme_id'],
				'answer_id' => $answers[$i]
			);
		}
		if ($data) {
			$DB->insert('commune_poll_votes', $data);
		}
		return TRUE;
	}

    /**
     * Голосовал ли пользователь в в данной теме?
     * 
     * @param   integer   $user_id    id пользователя
     * @param   integer   $theme_id   id темы
     * @return  boolean               TRUE - если голосовал
     */	
	function Poll_Voted($user_id, $theme_id) {
        global $DB;
		$sql = "SELECT COUNT(*) FROM commune_poll_votes WHERE user_id = ?i AND theme_id = ?i";
		$p = $DB->val($sql, intval($user_id), intval($theme_id));
		return (bool) $p;
	}
	
	/**
	 * Возвращает данные по опросу
	 *
	 * @param   integer   $theme_id   id темы
	 * @param   array                 строка из commune_poll + id автора темы
	 */
	function Poll_Get($theme_id) {
        global $DB;
		$sql = "
			SELECT
				cp.*, c.author_id
			FROM
				commune_themes ct
			JOIN
				commune c ON ct.commune_id = c.id
			JOIN
				commune_poll cp ON cp.theme_id = ct.id
			WHERE
				ct.id = ?i";
		return $DB->row($sql, intval($theme_id));
	}
	
	/**
     * Возвращает список вариантов ответов опроса
     * 
     * @param   integer   $theme_id   id темы
     * @return  array                 массив с данными
     */	
	function Poll_Answers($theme_id) {
        global $DB;
		$sql = "SELECT * FROM commune_poll_answers WHERE theme_id = ?i ORDER BY id";
		return $DB->rows($sql, intval($theme_id));
	}
	
	
	/**
	 * Закрывает/Открывает опрос
	 *
     * @param   integer   $theme_id   id темы
	 * @return  boolean               результат операции
	 */
	function Poll_Close($theme_id) {
        global $DB;
		$sql = "UPDATE commune_poll SET closed = NOT closed WHERE theme_id = ?i RETURNING closed";
		$r = $DB->val($sql, intval($theme_id));
		return ($r == 't');
	}
	
	/**
	 * Открыт или закрыт опрос?
	 *
     * @param   integer   $theme_id   id темы
	 * @return  boolean               TRUE - открыт, FALSE - закрыт
	 */
	function Poll_Closed($theme_id) {
        global $DB;
		$sql = "SELECT closed FROM commune_poll WHERE theme_id = ?i";
		$r = $DB->val($sql, intval($theme_id));
		return ($r == 't');
	}

	/**
	 * Удаляет опрос
	 *
	 * @param   integer   $theme_id   id темы
	 * @param   string    $msgtext    возвращает сообщение, которое нужно выдать пользователю
	 * @return  boolean               результат операции
	 */
	function Poll_Remove($theme_id, &$msgtext) {
        global $DB;
		$theme_id = intval($theme_id);
		$msgtext  = FALSE;
		$row = $DB->row("SELECT * FROM commune_messages WHERE theme_id = ?i AND parent_id IS NULL", $theme_id);
		if (!$row) return FALSE;
		if (!$row['msgtext'] && !$row['attach']) {
			$msgtext = "Опрос удален";
			$DB->query("UPDATE commune_messages SET msgtext = ? WHERE id = ?i", $msgtext, $row['id']);
		}
		$DB->query("DELETE FROM commune_poll WHERE theme_id = ?i", $theme_id);
		return !((bool) $DB->error);
	}


 /**
  * Отписаться/подписаться на уведомления.
  *
  * @param <type> $theme_id
  * @param <type> $user_id
  */
 function SubscribeTheme($theme_id, $user_id) {
     global $DB;
     $theme_id = intval($theme_id);
     $user_id = intval($user_id);
     $sql = "UPDATE commune_users_messages SET subscribed = NOT subscribed WHERE message_id = ?i AND user_id = ?i RETURNING subscribed";
     $r = $DB->val($sql, $theme_id, $user_id);
     return ($r == 't');
 }
 	
//======================Подписка на сообщества======================= 
 	/**
 	 * Функция возвращает результат подписки на комментарии топика сообщества для пользователя
 	 * 
 	 * @param integer $user_id
 	 * @param integer $message_id
 	 * @return array
 	 */
	public static function isCommuneTopicSubscribed($message_id, $user_id){
        $membuf = new memBuff();
        $memkey = "comm_topic_subscribe_$message_id"."_"."$user_id";
        $result = $membuf->get($memkey);
        if (!$result) {
	        global $DB;
		    $sql = "SELECT subscribed FROM commune_users_messages WHERE user_id = ?i AND message_id = ?i";
		    $result = $DB->val($sql, $user_id, $message_id);
		    $membuf->add($memkey, $result, 3600);	
        }
        if ($result == 't') {
            return true;
        }
        return false;
	}

/**
 	 * Функция возвращает результат подписки на коментарии топика сообщества для пользователя
 	 * 
 	 * @param integer $user_id
 	 * @return array
 	 */
	public static function isCommuneSubscribed($commune_id, $user_id){
        global $DB;
		$sql = "SELECT COUNT(*) AS cnt FROM (
				SELECT id AS cid FROM commune WHERE id = ?i AND author_id = ?i AND subscribed = true
  				UNION
  				SELECT a.commune_id AS cid FROM commune_members a
  				INNER JOIN commune b ON (b.id = a.commune_id AND b.author_id <> ?i)
  				WHERE a.commune_id = ?i AND a.user_id = ?i AND a.subscribed = true) c";
		$ret = $DB->row($sql, $commune_id, $user_id, $user_id, $commune_id, $user_id);
		if($ret){
			return ((int)$ret['cnt'] > 0);
		}
		return false;
	}
	
        /**
         * Проверяет забанен ли пользователь в сообществе.
         * 
         * @param integer $commune_id ID сообщества (см. таблицу commune)
         * @param integer $user_id ID пользователя
         * @return boolean
         */
        public static function isUserBanned($commune_id,$user_id){
            global $DB;
            $sql = "SELECT a.is_banned FROM commune_members a
            WHERE commune_id = ?i AND user_id = ?i";
            $ret = $DB->row($sql, $commune_id, $user_id);

            return ($ret?($ret['is_banned']=='t'):false);
        }
 
 	/**
 	 * Фнкция возвращает ID сообществ, на которые подписан пользователь
 	 * 
 	 * @param integer $user_id
 	 * @return array
 	 */
	public static function getSubscribedCommunes($user_id){
        global $DB;
		$out = array();
		$sql = "SELECT id AS cid FROM commune WHERE author_id = ?i AND subscribed = true
		UNION
		SELECT a.commune_id AS cid FROM commune_members a
		INNER JOIN commune b ON (b.id = a.commune_id AND b.author_id <> ?i)
		WHERE a.user_id = ?i AND a.subscribed = true";
		$ret = $DB->rows($sql, $user_id, $user_id, $user_id);
		if($ret){
			foreach ($ret as $item){
				$out[] = $item['cid'];
			}
		}
		return $out;
	}
	
	/**
	 * Функция снимает все подписки пользователя в сообществах
	 * @param integer $user_id
	 * @return boolean
	 */
	public static function clearSubscription($user_id){
        global $DB;
		$sql = "UPDATE commune SET subscribed = false WHERE author_id = ?i;
		UPDATE commune_members SET subscribed = false WHERE user_id = ?i";
		$res = $DB->query($sql, $user_id, $user_id);
		return (bool)$res;
	}
	
	/**
	 * Функция устанавливает подписку для пользователя на определенное сообщество
	 * 
	 * @param integer $commune_id
	 * @param integer $user_id
	 * @param boolean $subscribed
	 * @return boolean
	 */
	public static function setCommuneSubscription($commune_id, $user_id, $subscribed = false){
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        global $DB;
		$bool_sql = $subscribed ? 'true' : 'false';
		$sql = "UPDATE commune SET subscribed = $bool_sql WHERE id = ?i AND author_id = ?i;
		UPDATE commune_members SET subscribed = $bool_sql WHERE commune_id = ?i AND user_id = ?i";
		$res = $DB->query($sql, $commune_id, $user_id, $commune_id, $user_id);
        
        if ($subscribed) {
            $users = new users();
            $users->UpdateCommuneSubscr($user_id, $subscribed);
        }
        
		return (bool)$res;
	}
	
	/**
	 * Функция устанавливает подписки для пользователя на определенные сообщества
	 * 
	 * @param integer $commune_ids
	 * @param integer $user_id
	 * @param boolean $subscribed
	 * @return boolean
	 */
	public static function setCommunesSubscription($commune_ids, $user_id, $subscribed = true){
        global $DB;
        if(empty($commune_ids)) return false;
		$in_range = implode(', ',$commune_ids);
		$bool_sql = $subscribed ? 'true' : 'false';
		$sql = "UPDATE commune SET subscribed = $bool_sql WHERE id IN ($in_range) AND author_id = ?i;
		UPDATE commune_members SET subscribed = $bool_sql WHERE commune_id IN ($in_range) AND user_id = ?i";
		$res = $DB->query($sql, $user_id, $user_id);
		return (bool)$res;
	}
	
	/**
     * Показывает количество прикрепленных аттачей к теме сообщества, помимо первого
     *
     * @param integer $num		количество аттачей, исключая первый
     * @return string			ссылка внутрь темы
     */
    function ShowMoreAttaches($num){
        $num = intval($num-1);
        if ($num)
        {
            if ($num == 1)
            {
                return "Внутри ещё 1 файл";
            }
            elseif($num <= 4)
            {
                return "Внутри ещё $num файла";
            }
            elseif($num <= 20)
            {
                return "Внутри ещё $num файлов";
            }
        }
    }
    
    /**
     * Назначение прав на редактирование комментариев
     * 
     * @param type $user_mod
     * @return int|boolean 
     */
    public function setAccessComments($user_mod) {
        if($user_mod & self::MOD_COMM_AUTHOR) {
            return 2;
        } else if($user_mod & self::MOD_COMM_MODERATOR) {
            return 3;
        } else if(hasPermissions('comments')) {
            return 1;
        }
        
        return false;
    }

    /**
     * @deprecated В настоящий момент не используется на сайте
     * Добавляет в commune_attach запись о временном файле, загруженном во время наботра текста в визивиге 
     * */
    function addWysiwygFile($cfile) {
        global $DB;
        //временно прикрепляю файл к самому старому удаленному сообщению  
        $messageId = $DB->val("SELECT id FROM commune_messages WHERE deleted_time = 
          (
            SELECT min(deleted_time) FROM commune_messages 
          )");
        $DB->query("INSERT INTO commune_attach (cid, fid, small, sort, is_deleted, inline, temp) 
                    VALUES ({$messageId}, {$cfile->id}, FALSE, 1, FALSE, TRUE, TRUE)");
    }
    
    /**
     * @deprecated В настоящий момент не используется на сайте
     * Удаляет файлы и записи у которых флаги commune_attach.inline и commune_attach.temp равны TRUE
     *  и с момента создания которых прошло больше суток
     *  вызывается из hourly.php раз в сутки
     * */
    function removeWysiwygTrash() {
        global $DB;
        $rows = $DB->rows("SELECT ca.id, fid 
        FROM commune_attach AS ca 
        LEFT JOIN file_commune AS fc
         ON fc.id = ca.fid
        WHERE ca.inline = TRUE AND ca.temp = TRUE AND fc.modified + '1 day'::interval < NOW()");
        $files   = array();
        $records = array();
        $doDelete = 0;
        foreach ($rows as $row) {
            $files   [] = $row['fid'];
            $records [] = $row['id'];
            $doDelete++;
        }
        if ($doDelete) {
            $_files   = join(", ", $files);
            $_records = join(", ", $records);
            $DB->query("DELETE FROM commune_attach WHERE id IN ({$_records})");
            $DB->query("DELETE FROM file_commune   WHERE id IN ({$_files})");
        }
    }
    
   /**
    * @deprecated В настоящий момент не используется на сайте
    * 
     * При создании и изменении текста поста в сообществе проверяет, на все ли загруженые при наборе текста в визивиге
     * изображения есть ссылки в тексте комментария, если не на все, удаляет лишние.
     * При создании комментария обновляет cid (ID Сообщения сообщества) записи в commune_attaches
     * @param int    $messageId - номер записи в commune_messages 
     * @param string $text      - текст комментария
     * @param bool   $edit      - true когда редактируется 
     * */
    function checkWysiwygInlineImages($messageId, $text, $edit = false) {
    	session_start();
    	$filesIds = $_SESSION['wysiwyg_inline_files']; //получить id вставленных при наборе текста файлов
    	
    	global $DB;    	    	
    	//получаем все теги img из соообщения
        if($text == '') return;
        $text = str_replace("<cut>", "", $text); // Не любит cut сволочь
    	$dom = new DOMDocument();
    	$dom->validateOnParse = false;
        libxml_use_internal_errors(true);
    	$dom->loadHTML($text);
        libxml_use_internal_errors(false);
    	$images = $dom->getElementsByTagName('img');
    	$w_files = array();    //файлы, ссылки на которые есть в wisywyg
    	for ($i = 0; $i < $images->length; $i++) {
    	  	$filePath = $images->item($i)->getAttribute('src');    	    	
    	    $filePath = str_replace(WDCPREFIX."/", "", $filePath);
    	    $file = new CFile($filePath, "file_commune");
    	    if ($file->id) {
    	       	$w_files[$file->id] = $file->id;
    	    }
    	}
    	if ($cid) {//если комментарий редактируется, добавим к идентификаторам вновь вставленных в визивиг файлов идентификаторв ранее вставленных
    	    $rows = $DB->rows("SELECT fid FROM commune_attach
    	    			WHERE cid = $cid AND inline = TRUE");
    	    foreach ($rows as $row) {
    	      	$filesIds[$row['fid']] = $row['fid'];
    	    }
    	}
    	//удалить из $filesIds те, ссылок на которые нет в тексте визивига
    	foreach ($filesIds as $id) {
    	 	if (!$w_files[$id]) {
    	   		$cfile = new CFile($id, "file_commune");
    	   		if ($cfile->id) {
    	   			$cfile->delete($id);
    	   		}	
    	   		unset($filesIds[$id]);
    	   	}
    	}
    	$ids = join(',', $filesIds);
    	if (count($filesIds)) {
    	$cmd = "UPDATE commune_attach 
	    	    SET cid = {$messageId},
	    	         temp = FALSE
	            WHERE fid IN ( $ids )";
	    	    $DB->query($cmd);
    	}
       $_SESSION['wysiwyg_inline_files'] = array();
    }
    
    /**
     * Берем темы сообществ по их ИД
     * 
     * @global type $DB
     * @param array $ids
     * @return boolean
     */
    public static function getCommunePostByIds($ids) {
        global $DB;
        if(!is_array($ids)) return false;
        
        $sql = "SELECT id, title, created_time as post_time, theme_id, user_id, parent_id
                FROM commune_messages
                WHERE parent_id IS NULL AND id IN (?l)";
        
        return $DB->rows($sql, $ids);
    }
    /**
     * Восстановить ранее удаленное сообщение
     * @param $message_id  идентификатор сообщения
    **/
   public static function RestoreMessage( $message_id ) {
   	    global $DB;
        $sql = "UPDATE commune_messages SET deleted_id = NULL, deleted_time = NULL WHERE id = ? RETURNING theme_id";
        if($res = $DB->query($sql, $message_id)) {
            if( pg_affected_rows($res) ) {
                list($theme_id) = pg_fetch_row($res);
                $commId = commune::getCommuneIdByMsgID( $message_id );
                $sql = "SELECT re_pos_commune_themes(?i, (SELECT pos FROM commune_themes WHERE id = ?i), NULL)";
                $DB->query($sql, $commId, $theme_id);
            }
        }
   }
   
   /**
    * Отдаем ИД Поста сообщества по ИД поста блога
    * @global type $DB
    * @param integer $id   ИД поста блога
    * @return boolean
    */
   public static function getCommunePostByThreadID($id) {
       global $DB;
       if(!$id) return false;
       
       $sql = "SELECT cm.id FROM commune_themes ct
               INNER JOIN commune_messages cm ON cm.theme_id = ct.id AND cm.parent_id IS NULL
               WHERE ct.commune_id IN (5000,5001,5100) AND blog_thread_id = ?i";
       
       return $DB->val($sql, $id);
   }
   
   /**
    * Отдает ИД комментария сообщества по Ид комментария блога
    * 
    * @global type $DB
    * @param integer $blog_id
    * @return boolean
    */
   public static function getCommuneMessageByBlogID($blog_id) {
       global $DB;
       if(!$blog_id) return false;
       
       return $DB->val("SELECT id FROM commune_messages WHERE blog_id = ?i", $blog_id);
   }
   
   public static function getCommuneIDByThemeID($theme_id) {
       global $DB;
       return $GLOBALS['DB']->cache(3600)->val("SELECT commune_id FROM commune_themes WHERE id = ?i", $theme_id);
   } 
   
   public static function getCommuneIDByMessageID($message_id) {
       global $DB;
       if($message_id == null) return;
       return $GLOBALS['DB']->cache(3600)->val("SELECT ct.commune_id FROM commune_messages cm INNER JOIN commune_themes ct ON ct.id = cm.theme_id WHERE cm.id = ?i", $message_id);
   }
   
   public static function getTableName($table_name, $commune_id, $year = true, $only = false) {
       switch($table_name) {
           case 'commune_messages':
               return ($commune_id == self::COMMUNE_BLOGS_ID || $commune_id == self::COMMUNE2_BLOGS_ID) ? "commune_messages_blogs" . ( !$year ?'': '_' . date('Y') ) : ($only ? "ONLY {$table_name}" : $table_name);
               break;
           case 'commune_users_messages':
               return ($commune_id == self::COMMUNE_BLOGS_ID || $commune_id == self::COMMUNE2_BLOGS_ID) ? "commune_users_messages_blogs" . ( !$year ?'': '_' . date('Y') ) : ($only ? "ONLY {$table_name}" : $table_name);
               break;
       }
       return $table_name;
   }
   
   /**
     * Возвращает информацию о сообществе и сообщениии по ID сообщения
     * @param    integer    $msg_id    ID сообщения в сообществе
     * @return   array                 Информация о сообществе
     */
    function getMessageInfoByMsgID($msg_id) {
      global $DB;
      $sql = "SELECT
                communes.id AS commune_id,
                communes.name AS commune_name,
                groups.name AS group_name,
                msgs.msgtext, msgs.deleted_id,
                u.uid AS editor_id, u.uname AS editor_uname, u.usurname AS editor_usurname, u.login AS editor_login,
                cm_2.id AS top_id, cm_2.title, msgs.parent_id, cm_2.created_time,
                a.uid AS topicstarter_uid, a.uname AS topicstarter_uname, a.usurname AS topicstarter_usurname, a.login AS topicstarter_login, a.email AS topicstarter_email,
                comment_auth.uid AS commentator_uid, comment_auth.uname AS commentator_uname, comment_auth.usurname AS commentator_usurname, comment_auth.login AS commentator_login, comment_auth.email AS commentator_email,
                d.uid AS deleter_uid, d.uname AS deleter_uname, d.usurname AS deleter_usurname, d.login AS deleter_login, d.email AS deleter_email
              FROM commune_messages as msgs
              INNER JOIN commune_themes AS themes ON themes.id = msgs.theme_id
              INNER JOIN commune AS communes ON communes.id = themes.commune_id
              INNER JOIN commune_groups AS groups ON groups.id = communes.group_id
              LEFT JOIN users AS u ON u.uid = msgs.modified_id
              JOIN commune_messages AS cm_2 ON msgs.theme_id = cm_2.theme_id AND cm_2.parent_id IS NULL
              LEFT JOIN users AS a ON a.uid = cm_2.user_id
              LEFT JOIN users AS comment_auth ON comment_auth.uid = msgs.user_id
              LEFT JOIN users AS d ON d.uid = msgs.deleted_id
              WHERE msgs.id = ?i";
      return $DB->row($sql, $msg_id); //убрал кеш, так как нужна информация из полей  modified_id, deleted_id 
    } 
    
    
    public static function isBannedCommune($user_mod) {
        return ($user_mod & commune::MOD_COMM_BANNED && ! hasPermissions('communes'));
    }
    
    /**
     * считает количество топиков для заданного сообщества,
     * а также для всех категорий этого сообщества
     * результат хранится в мемкэше, на боевом 1800 сек.
     * @param type $communeID ID сообщества
     * @return array результат возвращается в виде массива 
     * с ключами count, hidden_count, admin_hidden_count, categories.
     * Где count - общее количество тем в сообществе, неважно скрытых, заблокированных, помеченных удаленными и т.д.;
     * admin_hidden_count - количество постов скрытых от админов сообщества (удаленные, заблокированные администрацией сайта или автор которых заблокирован на всем сайте);
     * hidden_count - количество постов скрытых от обычных пользователей (приватные, заблокированные админом/модером сообщества, автор которых удален или забанен в сообществе, плюс все те которые скрыты от админов сообществ);
     * categories - все вышеописанные ключи, только для категорий сообщества.
     */
    public static function getCommuneThemesCount ($communeID) {
        if (!$communeID) {
            return array();
        }
        
        $membuf = new memBuff();
        $memkey = "commune_themes_count_$communeID";
        $result = $membuf->get($memkey);
        
        if (!$result['count']) {
	        global $DB;
            $sql = "SELECT * FROM commune_meta WHERE commune_id = ?i";
            
            $res = $DB->rows($sql, $communeID);
            
            foreach($res as $value) {
                if($value['category_id'] == 0) {
                    $commune = $value;
                }
                $cats[$value['category_id']] = $value;
            }
            $result = $commune;
            $result['categories'] = $cats;
            
            $cacheLifeTime = is_release() ? 1200 : 600;
		    $membuf->set($memkey, $result, $cacheLifeTime);
            if(!$result['count']) $result['count'] = 0;
        }
        
        return $result;
        
    }
    
    /**
     * Функция пересчета счетчиков
     * 
     * @global type $DB
     * @return boolean
     */
    public static function recalcThemesCountCommunes($commune_id = NULL, $not_commune_id = NULL) {
        global $DB;
        
        $where = '';
        if($commune_id !== NULL) {
            $where = ' AND ct.commune_id = ' . intval($commune_id);
        }
        if($not_commune_id !== NULL) {
            $where = ' AND ct.commune_id <> ' . intval($not_commune_id);
        }
        // Пересчитываем счетчики раз в час
        $sql = "WITH recalc_data AS ( 
                    SELECT ct.commune_id, ct.category_id, 
                        count(*) as cnt,
                        -- количество постов которые не видят обычные пользователи
                        sum(CASE WHEN ct.is_private OR ct.is_blocked OR cmem.is_deleted OR cmem.is_banned OR cm.deleted_id IS NOT NULL OR u.is_banned = B'1' OR ctb.theme_id IS NOT NULL THEN 1 ELSE 0 END) as hidden_count,
                        -- количество постов которые не видят админи сообщества
                        sum(CASE WHEN cm.deleted_id IS NOT NULL OR u.is_banned = B'1' OR ctb.theme_id IS NOT NULL THEN 1 ELSE 0 END) as admin_hidden_count
                    FROM commune_messages cm
                    INNER JOIN commune_themes ct ON cm.theme_id = ct.id
                    LEFT JOIN commune_members cmem ON cmem.user_id = cm.user_id AND cmem.commune_id = ct.commune_id
                    LEFT JOIN commune_theme_blocked ctb ON ctb.theme_id = ct.id
                    LEFT JOIN users u ON u.uid = cm.user_id

                    WHERE cm.parent_id IS NULL {$where}
                    GROUP BY ct.commune_id, ct.category_id)
                    
                INSERT INTO commune_meta 
                (commune_id, category_id, count, hidden_count, admin_hidden_count)  
                
                SELECT commune_id, 0 as category_id, SUM(cnt) as count, SUM(hidden_count) as hidden_count, SUM(admin_hidden_count) as admin_hidden_count FROM recalc_data GROUP BY commune_id 
                UNION
                SELECT * FROM recalc_data WHERE category_id IS NOT NULL";
        
        return $DB->query($sql);
    }
//======================Подписка на сообщества======================= 
  // ...
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////      
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/**
 * Вывод топа в сообществе, в ленте.
 *
 * @global $session Сессия пользователя
 * 
 * @param array    $top             получается вызовами commune::GetTopMessages, commune::GetTopMessageByAnyOther,
							        используемые члены:
							        .commune_id,        Ид. сообщества, в котором сидит топ.
							        .commune_name,      Название сообщества (в ленте (/lenta/), например, ипсользуется).
							        .commune_group_id,  Ид. раздела, в которое входит сообщество (в ленте (/lenta/), например, ипсользуется).
							        .commune_group_name,
							        .commune_author_id  Ид. создателя сообщества.
							        .id,                Ид. топа.
							        .title,             Заголовок топа.
							        .msgtext,           Текст.
							        .attach,            Имя прикрепленного файла (картинки).
							        .youtube_link       Ссылка на ролик YouTube
							        .user_login,        Логин автора топа.
							        .user_role,         users.role как есть. 
							        .user_id,           Ид. автора.
							        .modified_id        Ид. изменившего комментарий.
							        .modified_login.
							        .modified_usurname.
							        .modified_uname
							        .modified_by_commune_admin  Отредактирован админом сообщества или нет (1 или 0).
							        .small,             Имеется ли уменьшенный вариант картинки.
							        .member_warn_count, Количество предупреждений автора.
							        .member_is_banned,  Истина, если автор забанен в сообществе.
							        .member_is_accepted, Если ложь, то автор не состоит уже в сообществе.
							        .a_count            Количество комментариев на топ плюс 1 (сам топ).
							        .last_activity      Время последней активности (добавления/редактирования комментариев) в топе.
							        .last_viewed_time   Время последнего просмотра топа текущим пользователем.
							        .is_viewed          Можно устанавливать в 1, если нет необходимости проверять last_viewed_time.
 * @param integer $user_id          сеансовый (сессионный, текущий) юзер.
 * @param integer $mod				битовое поле, характеризующее текущего пользователя (см. в доп. константы commune::MOD_),
							        хранит такую информацию о пользователе:
							        - администратор сообщества.
							        - модератор сообщества.
							        - управленец по людям в сообществе.
							        - автор сообщества.
							        - принят в сообщество.
							        - забанен в сообществе.
							        - администратор (модератор) сайта.
							        - про-фрилансер.
							        - работодатель.
							        - забанен на сайте.
 * @param integre $om			    нужен для прохода по страницам, возвратам, переходам.
 * @param integer $page				тоже в навигаторских целях.
 * @param string  $site				$site==NULL|'Commune' -- топик выводится на странице сообщества (/commune/),
					                $site=='Topic' -- на странице комментариев (/commune/?site=Topic),
					                $site=='Lenta' -- в ленте (/lenta/).
 * @param boolean $is_fav			находится в закладка пользователя $user_id или нет.	
 * @return string					Функция возвращает HTML топ-сообщения (тему). Выделена отдельно, т.к. используется в
 *									несокольких местах и в xajax-е.
 */
function __commPrntTopic(
 $top, 
 $user_id, 
 $mod, 
 $om=NULL,  
 $page=NULL, 
 $site=NULL,  
 $is_fav=NULL,
 $favs=NULL, $ajax_view = false)  

{ 
  global $session, $foto_alt, $alert; //, $stop_words;
  if($site != 'Topic') unset($alert); 
  if ($top["msgtext"]) {
      validate_code_style($top["msgtext"]);
  }
  if(!$site)
    $site = 'Commune';
    //if(!$favs) $favs = commune::GetFavorites($user_id); // !!! удалить надо будет
  $commune_id = $top['commune_id'];
	$msg_id  = $top['id'];
  $box_id = 'idTop_'.$msg_id;
	$edit_id = '';
  $created_time = strtotimeEx($top['created_time']); // !!! В stdf говорят, не использовать strtotimeEx()
  $last_activity = strtotimeEx($top['last_activity']);
	$last_viewed_time = strtotimeEx($top['last_viewed_time']);
  //$attach = '';
  
  /*if($top['attach'])
    $attach = '<br/><br/>'.
              viewattachLeft($top['user_login'],
                             $top['attach'], 'upload',
                             $file,
                             commune::MSG_IMAGE_MAX_HEIGHT, commune::MSG_IMAGE_MAX_WIDTH, commune::MSG_IMAGE_MAX_SIZE,
                             !($top['small']=='t'), (int)($top['small']=='t')).
              '<br/>';*/


  switch($site) {
    
    case 'Topic' :
      $title_max = 40;
      $msgtext_max = 46;
      $pt = '10';
      $pl = '15';
      $edit_id = 'idEditCommentForm_'.$msg_id;
      $foto_alt = $top['title'];
      $hideInJS = false;
      break;
    case 'xajaxCommune':
      $foto_alt = $top['title'];
      $title_max = 30;
      $msgtext_max = 46;
      $pt = '20';
      $pl = '0';
	  $edit_id = 'editmsg';
      $hideInJS = false;
      break;
    default:
      $foto_alt = $top['title'];
      $title_max = 30;
      $msgtext_max = 46;
      $pt = '20';
      $pl = '0';
	    $edit_id = 'editmsg';
      $hideInJS = true;
      break;
  }

  $is_member = $mod & (commune::MOD_ADMIN | commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_AUTHOR);
  $is_moder  = $mod & (commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER);
  include_once $_SERVER['DOCUMENT_ROOT'].'/classes/links.php';
  $GLOBALS[LINK_INSTANCE_NAME] = new links('commune');
  $om_parts = (int)$om ? '&om='.$om : '';
    if (!commune_carma::isImmunity($top['user_id'], array('is_team' => $top['user_is_team'], 'login' => $top['user_login']), $msg_id)) {
        $actionRating = commune_carma::actionByRate($top['rating'], commune_carma::getScale('post'));
    }
  ob_start();
  include(TPL_COMMUNE_PATH.'/tpl.topic_new.php');
  $str = ob_get_contents();
  ob_end_clean();
//  $str = preg_replace('/>\s+</','><',$str);
//  $str = preg_replace('/\r?\n/',' ',$str);
//  $str = preg_replace('/\s{2,}/',' ',$str);
  return $str;
}


/**
 * Вывод одного комментария на странице site=Topic.
 *
 * @global $session Сессия пользователя
 * 
 * @param array $top			    	получается вызовом commune::,
								        используемые члены:
								        .commune_id,        Ид. сообщества, в котором сидит топ.
								        .id,                Ид. топа.
								        .commune_author_id  Ид. создателя сообщества.
								        .user_id            Ид. автора топа.
								        .last_viewed_time   Время последнего просмотра топа текущим пользователем.
								        .is_viewed          Если установить, то .last_viewed_time проверяться не будет и вообще не понадобится. Можно использовать
								                            в случае, когда точно известно, что топик уже просмотрен.
 * @param array $comment				получается вызовами commune::GetAsThread,
							            необходимые члены:
							            .user_role,         users.role как есть. 
							            .user_login,        Логин автора комментария.
							            .user_photo,        Аватар автора комментария.
							            .user_is_pro        Про, не про.
							            .user_is_team       Пользовтаель в команде фриланса
							            .user_is_pro_test   Тестовый про или нет
							            .created_time       Дата создания комментария.
							            .deleted_time       Дата удаления комментария.
							            .deleted_id         Ид. удалившего комментарий.
							            .deleted_login.
							            .deleted_usurname.
							            .deleted_uname
							            .deleted_by_commune_admin  Удален админом сообщества или нет (1 или 0).
							            .modified_time      Дата изменения комментария.
							            .modified_id        Ид. изменившего комментарий.
							            .modified_login.
							            .modified_usurname.
							            .modified_uname
							            .modified_by_commune_admin  Отредактирован админом сообщества или нет (1 или 0).
							            .user_is_banned     Это комментарий забаненного или незабаненного юзера на сайте.
							            .member_is_banned   Это комментарий забаненного или незабаненного юзера в сообществе.
							            .member_is_accepted, Если ложь, то автор не состоит уже в сообществе.
							            .member_id,         Ид. участинка сообщества, автора сообщения.
							            .id,                Ид. комментария.
							            .title,             Заголовок комментария.
							            .msgtext,           Текст.
							            .attach,            Имя прикрепленного файла (картинки).
							            .youtube_link       Ссылка на ролик YouTube
							            .user_id,           Ид. пользователя, автора сообщения.
							            .small,             Имеется ли уменьшенный вариант картинки.
							            .member_warn_count, Количество предупреждений автора.
 * @param integer $user_id				текущий пользователь (сессионный)
 * @param integer $mod					битовое поле, характеризующее текущего пользователя (см. в доп. константы commune::MOD_),
							        	хранит такую информацию о пользователе:
								        - администратор сообщества.
								        - модератор сообщества.
								        - управленец по людям в сообществе.
								        - автор сообщества.
								        - принят в сообщество.
								        - забанен в сообществе.
								        - администратор (модератор) сайта.
								        - про-фрилансер.
								        - работодатель.
								        - забанен на сайте.
 * @param integer $om					нужен для прохода по страницам, возвратам, переходам.
 * @param integer $active_id			ид. активного комментария.
 * @param integer $level				уровень вложенности.
 * @param integer $is_last			 1: последний в блоке.
 * @return string 
 */
function __commPrntComment (  
 $top, 
 $comment, 
 $user_id, 
 $mod, 
 $om=NULL, 
 $active_id=NULL,  
 $level=NULL,  
 $is_last=NULL)
{ 
  global $session, $foto_alt;
  $commune_id = $top['commune_id'];
  $padding = (($level > 19) ? 19 * 20 : $level * 20) - ($level==1)*5;
	$created_time = strtotimeEx($comment['created_time']);
	$last_viewed_time = strtotimeEx($top['last_viewed_time']);
  $is_deleted = $comment['deleted_time'] ? 1 : 0;
  if(!class_exists('links')){
      include_once $_SERVER['DOCUMENT_ROOT'].'/classes/links.php';
  }
  $GLOBALS[LINK_INSTANCE_NAME] = new links('commune');
  
  ob_start();
  include(TPL_COMMUNE_PATH.'/tpl.comment.php');
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}

/**
 * Вывод рейтингов
 *
 * @param array   $comm 	Данные по комментарию
 * @param integer $user_id  ИД пользователя
 * @param integer $rating   Рейтинг
 * @return string
 */
function __commPrntRating($comm, $user_id=NULL, $rating=NULL)  // 
{
  if($rating===NULL)
    $rating = $comm['yeas'] - $comm['noes'];

  if($user_id)
    $vote = commune::GetUserVote($comm['id'], $user_id);

  $p_onClick  = '';
  $m_onClick = '';
  $p_href = '';
  $m_href = '';
  $p_alt = '';
  $m_alt = '';

  $onclick = '';
  if($user_id
     && $comm['author_uid']!=$user_id // Вообще, везде стараться проверять.
     && $comm['current_user_join_status'] == commune::JOIN_STATUS_ACCEPTED
     && $comm['is_banned'] !== 't')
  {
    $onclick = "
      xajax_Vote('idCommRating_','{$comm['id']}', '{$user_id}', document.getElementById('idCommRatingValue_{$comm['id']}').innerHTML
    ";
  }

  if($onclick) {
    if($vote!=1) {
      $p_onClick = " onclick=\"try { if(!lockRating{$comm['id']}) {$onclick},  1); lockRating{$comm['id']}=1; } catch(e) { }\"";
      $p_href = " href='javascript:void(0)'";
      $p_alt = " alt='+'";
    }
    if($vote!=-1) {
      $m_onClick = " onclick=\"try { if(!lockRating{$comm['id']}) {$onclick}, -1); lockRating{$comm['id']}=1; } catch(e) { }\"";
      $m_href = " href='javascript:void(0)'";
      $m_alt = " alt='-'";
    }
  }
  ob_start();
?>
  <script type="text/javascript">var lockRating<?=$comm['id']?>=0;</script>
  <?php $classname = $rating < 0 ? '_color_red' : ($rating >= 1 ? '_color_green' : '') ;?>
               
                    <? if($onclick && $vote != 1) { ?>
                    <a class="b-button b-button_poll_plus normal_behavior b-button_active b-voiting__right"<?=$p_href.$alt.$p_onClick?>></a>
                    <? } else { ?>
                    <a class="b-button b-button_poll_plus normal_behavior b-button_poll_nopointer b-voiting__right"></a>
                    <? } ?>
                    <? if($onclick && $vote != -1) { ?>
                    <a class="b-button b-button_poll_minus normal_behavior b-button_active b-voiting__left"<?=$p_href.$alt.$m_onClick?>></a>
                    <? } else { ?>
                    <a class="b-button b-button_poll_minus normal_behavior b-button_poll_nopointer b-voiting__left"></a>
                    <? } ?>
                    <span class="b-voting__mid b-voting__mid<?=$classname?>" id="idCommRatingValue_<?=$comm['id']?>">
                        <?= ($rating > 0 ? '+' : ($rating < 0 ? '&minus;' : '')) . abs(intval($rating))?>
                    </span>                    

<?php if(false){// Сратый вывод рейтинга?> 
  <div id="idCommRatingValue_<?=$comm['id']?>" class="commune-rating"><?=$rating?></div>
  <a<?=$p_href.$alt.$p_onClick?>><img src="/images/plusCommBtn.gif"  width="29" height="14" /></a><a<?=$m_href.$alt.$m_onClick?>><img src="/images/minusCommBtn.gif"  width="28" height="14" /></a>
<?php }?>
<?
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}


/**
 * Вывод рейтингов
 *
 * @param array   $topic 	Данные по комментарию
 * @param integer $mod      Битовая маска прав пользователя
 * @param integer $user_id  ИД пользователя
 * @return string
 */
function __commPrntTopicRating($topic, $mod, $user_id=NULL)  //
{
//    var_dump($topic);
    if(!$user_id) $user_id = get_uid(false);
//  if($rating===NULL)
//    $rating = (int)$topic['vote'] ? (int)$topic['vote'] : 0;

//  if($user_id){
    $vote = $topic['user_vote'];
//  }
    $rating = $topic['rating'];
//  echo $rating.'<br/>';

  $p_onClick  = '';
  $m_onClick = '';
  $p_href = '';
  $m_href = '';
  $p_alt = '';
  $m_alt = '';

  $onclick = '';
  $ponclick = '';
  $monclick = '';
//  var_dump($user_id.' > '.$topic['user_id']);
  $uStatus = commune::GetUserCommuneRel($topic['commune_id'], $user_id);
  // можно ли голосовать
  //$allow_vote = $user_id && $user_id != $topic['user_id']; // можно ли голосовать за топик
//  var_dump($uStatus);
  //if(   ((!$uStatus || !$uStatus['is_accepted']) && (!$uStatus['is_author'] && $topic['user_id'] != $user_id ))
    if ($uStatus
        && ($uStatus['is_accepted'] || $uStatus['is_author'])
        && $topic['is_blocked_c'] != 't'
        && $topic['user_id'] != $user_id
        && !$uStatus['is_deleted']
        && !$uStatus['is_banned']
        && !is_banned($user_id)
        && !$topic['deleted_id']
        && commune_carma::isAllowedVote()) {
      $allow_vote = true;
  } else {
      $allow_vote = false;
  }
  
  if (!$allow_vote) {
    $ponclick = ' href=""';
    $monclick = ' href=""';
  } else {
    $ponclick = $vote <= 0 ? "if(!lockRating{$topic['id']}) xajax_VoteTopic({$topic['id']}, {$user_id}, {$mod}, 1); lockRating{$topic['id']} = 1;" : false;
    $monclick = $vote >= 0 ? "if(!lockRating{$topic['id']}) xajax_VoteTopic({$topic['id']}, {$user_id}, {$mod}, -1); lockRating{$topic['id']} = 1; " : false;
  }

    if($ponclick) {
//      $p_onClick = " onclick=\"try { if(!lockRating{$topic['id']}) {$ponclick},  1); lockRating{$topic['id']}=1; } catch(e) { }\"";
      $p_href = " href='javascript:void(0)'";
      $p_onClick = " onclick=\"$ponclick\"";
      $p_alt = " alt='+'";
    }
    if($monclick) {
//      $m_onClick = " onclick=\"try { if(!lockRating{$topic['id']}) {$monclick}, -1); lockRating{$topic['id']}=1; } catch(e) { }\"";
      $m_href = " href='javascript:void(0)'";
      $m_onClick = " onclick=\"$monclick\"";
      $m_alt = " alt='-'";
    }

  if($topic['user_is_team']!='t') {
  ob_start();
?>
  <script>var lockRating<?=$topic['id']?>=0;</script>
  <?php $classname = $rating < 0 ? '_color_red' : ($rating >= 1 ? '_color_green' : '') ;?>
                <div class="b-voting b-voting_float_right">
                    <? if($allow_vote && $vote <= 0) { ?>
                        <a class="b-button b-button_poll_plus normal_behavior b-button_active b-voiting__right" <?=$p_href.$alt.$p_onClick?>></a>
                    <? } else { ?>
                        <a class="b-button b-button_poll_plus normal_behavior b-button_poll_nopointer b-voiting__right"></a>
                    <? } ?>
                    <? if($allow_vote && $vote >= 0) { ?>
                        <a class="b-button b-button_poll_minus normal_behavior b-button_active b-voiting__left" <?=$p_href.$alt.$m_onClick?>></a>
                    <? } else { ?>
                        <a class="b-button b-button_poll_minus normal_behavior b-button_poll_nopointer b-voiting__left"></a>
                    <? } ?>
                    <span class="b-voting__mid b-voting__mid<?=$classname?>" id="idCommRatingValue_<?=$comm['id']?>">
                        <?= ($rating > 0 ? '+' : ($rating < 0 ? '&minus;' : '')) . abs(intval($rating)) ?>
                    </span> 
                </div>
<?
  $str = ob_get_contents();
  ob_end_clean();
  }
  return $str;
}


/**
 * Рисует форму для создания/редактирования комментария.
 *
 * Пользователь создает комментарий.
 * 1. Вызывается данная форма с пустыми полями, подставляется под сообщение,
 *    которое комментируется. При этом удаляется со страницы ранее вызванная форма,
 *    если таковая была.
 * 2. Пользователь заполняет поля, вызывается POST. POST несет:
 *    - id (ид. сообщества);  
 *    - action = do.Create.post;  
 *    - top_id;
 *    - parent_id;
 *    - om (закладка);
 *    - title;
 *    - msgtext;
 *    - file;
 *    Остальные поля NULL.
 *      
 * 3. POST-запрос обрабатывается и:
 *    - в случае успеха выдает текущую страницу (страницу с текущей темой),
 *      движок прыгает на новый комментарий.
 *    - в случае неудачи, обработчик возвращает объект $request (почти тот же $_POST,
 *      только малость измененный) и $alert. Страница проверяет наличие $request и если
 *      он не NULL и $request['message_id'] совпадает id текущего комментария, то
 *      выводит данную форму под этим комментарием, заполняя ее данными из $request
 *      и выдает ошибки через $alert.
 * Пользователь редактирует комментарий.
 * 1. AJAX-ом вызывается данная форма, $action='Edit.post'. Форма заполняется атрибутами редактируемого сообщения,
 *    взятыми AJAX-ом из базы.
 * 2. Пользователь заполняет поля, вызывается POST. POST несет все параметры, action = do.Edit.post;
 * 
 * @param integer $id 		ид. сообщества.
 * @param integer $om 		для поддержания параметров навигации.
 * @param integer $page 	страница 
 * @param string  $action тип операции при субмите.
 * @param integer $top_id ид. топа (не темы, а корневого сообщения в теме). Используется только когда $site=='Topic'.
 * @param integer $message_id если указан, значит это сообщение редактируется.
 * @param integer $parent_id комментируем данное сообщение (создаем новое).
 * @param string  $request возвращается при ошибке при совершении операции.
 * @param string  $alert ошибки операции.
 * @param string  $site режим вывода.
 * @param integer $mod админ, модер и т.д.
 * @return string
 */
function __commPrntCommentForm(      
 $id,  
 $om, 
 $page = NULL, 
 $action = NULL,
 $top_id = NULL,  
 $message_id = NULL,  
 $parent_id = NULL,  
 $request = NULL, 
 $alert = NULL,  
 $site = NULL,   
 $mod = NULL,
 $cat = 0,
 $draft_id = 0) 
{ 

  if(!$action)
    $action = 'Create.post';
  
  $title = '';
  $msgtext = '';
  $attach = '';
  $youtube_link = '';
  $user_login = ''; // Чтобы в него загрузить картинку.
  $pos=NULL;
  $close_comments=NULL;
  $is_private=NULL;
  $category = 0;

  $mess = commune::GetMessage(intval($message_id));

  if($request) { // do...
                      
    $parent_id = $request['parent_id'];
    $title = __htmlchars($request['title']);
    $category_id = $request['category_id'];
    $msgtext = __htmlchars($request['msgtext']);
    $attach = $request['attach'];
    $youtube_link = $request['youtube_link'];
    $user_login = $request['user_login'];
    $pos = $request['pos'];
    $close_comments=$request['close_comments'] ? true : false;
    $is_private=$request['is_private'] ? true : false;
	$question = __htmlchars($request['question']);
	$multiple = $request['multiple'];
  }
  else if($action=='Edit.post') { // Edit.post
    if($mess) {
	  $parent_id = $mess['parent_id'];
      $title = $mess['title'];
      $category_id = $mess['category_id'];
      $msgtext = htmlspecialchars($mess['msgtext']);
      $attach = $mess['attach'];
      $youtube_link = $mess['youtube_link'];
      $user_login = $mess['user_login'];
      $pos = $mess['pos'];
      $close_comments = $mess['close_comments'] == 't' ? true : false;
      $is_private = $mess['is_private'] == 't' ? true : false;
	  $question = $mess['question'];
	  $multiple = $mess['multiple']=='t' ? 1 : 0;
    }
  }
  
  $answers = array();
  $exists  = (isset($request['answers_exists']) && is_array($request['answers_exists']))? $request['answers_exists']: array();
  if ($mess['question'] != '') {
	for ($i=0; $i<count($mess['answers']); $i++) {
		$ok = !isset($request['question']);
		for ($j=0; $j<count($exists); $j++) {
			if (!empty($exists[ $mess['answers'][$i]['id'] ])) {
				$ok = TRUE;
				break;
			}
		}
		if ($ok) {
			$answers[] = array('id'=>$mess['answers'][$i]['id'], 'answer'=>($exists[ $mess['answers'][$i]['id'] ]? __htmlchars($exists[ $mess['answers'][$i]['id'] ]): $mess['answers'][$i]['answer']));
		}
	}
  }
  if (isset($request['answers']) && is_array($request['answers'])) {
	foreach ($request['answers'] as $answer) $answers[] = array('id' => 0, 'answer' => __htmlchars($answer));
  }
  if (!$answers) $answers[] = array('id' => 0, 'answer' => '');

  $h = $site=='Topic' ? 'H1' : 'H2';
  $header = !$message_id ? ($site=='Topic' ? 'Комментировать' : 'Создать новое сообщение') : 'Редактировать';
  $button = !$message_id ? ($site=='Topic' ? 'Комментировать' : 'Создать') : 'Сохранить';
  $tah = $site=='Topic' ? '150' : '200';
  $action = str_replace('do.', '', $action);

  $anchor = '';
  if($site!='Topic') {
    if($alert) 
      $anchor = $action=='Edit.post' ? 'o'.($message_id ? $message_id : $parent_id) : 'o';
    else
      $anchor = 'bottom';
  }
  else {
    if($alert) 
      $anchor = 'op';
  }
  $pt = $site=='Topic' ? '25' : '0';
  $pb = $site=='Topic' ? '25' : '0';
  $iid = mt_rand(1,50000);
  $sub_cat = commune::getCategories($id, true);
  ob_start();
  include(TPL_COMMUNE_PATH.'/tpl.comment_form_new.php');
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}

/**
 * Вывод комментариев о заметках
 *
 * @param integer $member_id ИД пользователя заметки
 * @param string $note      Заметка
 * @return string   HTML
 */
function __commPrntMemberNote(                            //  
 $member_id,
 $commune_id,
 $note,
 $admin
)
{ 
  ob_start();
  include(TPL_COMMUNE_PATH.'/tpl.member_note.php');
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}

/**
 * вывод блока закладок
 *
 * @param array   $favs    Избранное
 * @param integer $user_id ИД пользователя
 * @param integer $om      Для навигации
 * @return string   HTML
 */
function __commPrntFavs($favs, $user_id, $om) { 
    if(empty($favs) || !is_array($favs) || !count($favs)) return '';
  ob_start();
?>
    <?foreach($favs as $key=>$fav): ?>
        <li id="fav<?=$key?>" class="b-menu__item b-menu__item_padbot_10">
        <?=__commPrntFavContent($fav, $key, $user_id, $om)?>
        </li>
    <? endforeach; ?>
<?	
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}

/**
 * вывод одной закладки в блоке
 * @see __commPrntFavs()
 *
 * @param array   $fav    закладка
 * @param mixed   $key    ключ (ид) закладки 
 * @param integer $user_id ИД пользователя
 * @param integer $om      Для навигации
 * @return string   HTML
 */
function __commPrntFavContent($fav, $key, $user_id, $om) { 
  ob_start();
  include(TPL_COMMUNE_PATH.'/tpl.fav_content.php');
  return ob_get_clean();
}  
  
/**
 * Вывод блока (HTML-код) для загрузки изображения сообщества
 *
 * @param string $error Сообщение об ошибке
 * @return string HTML-код
 */
function __commPrntCommImgBox($error=NULL)              
{
  ob_start();
  include(TPL_COMMUNE_PATH.'/tpl.comm_img_box.php');
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}

/**
 * Вывод приложения файлоф
 *
 * @param string $error Сообщение об ошибке
 * @return string
 */
function __commPrntMsgAttachBox($error=NULL, $max=0)              
{
  ob_start();
  include(TPL_COMMUNE_PATH.'/tpl.msg_attach_box.php');
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}

/**
 * Вывод картинки сообщества
 *
 * @param array $comm Данные коментария
 * @param string $prfx Префикс
 * @param string $a_class Cтиль для тега A вместо дефолтного
 * @param string $img_class Стиль для тега IMG вместо дефолтного
 * @return string HTML-код
 */
function __commPrntImage($comm, $prfx='', $a_class = false, $img_class = false)                          
{
  $image = $comm['image'];
  $src = WDCPREFIX."/users/".$comm[$prfx.'login']."/upload/".$image;
  //$src = "/commune/comm1.gif";
  if($image)
    $image =    "<a href='".getFriendlyURL("commune_commune", $comm['id'])."'".($a_class !== false ? ' class="'.$a_class.'"' : '').">".
                    "<img class='".($img_class === false ? "b-post__pic" : $img_class)."' src='{$src}' alt=''/>".
                "</a>";
  else
    $image = '&nbsp;';

  return $image;
}

/**
 * Вывод возраста сообщества
 *
 * @param array $comm Данные комментария
 * @return string
 */
function __commPrntAge($comm)                             
{
  $age = '';
  if($comm['year'])
    $age .= $comm['year'].' '.getSymbolicName($comm['year'], 'year').' ';
  if($comm['month'])
    $age .= $comm['month'].' '.getSymbolicName($comm['month'], 'month').' ';
  if($comm['day'])
    $age .= $comm['day'].' '.getSymbolicName($comm['day'], 'day');


  return ( $age ? "Существует {$age}" : 'Создано сегодня' );
}

/**
 * Вывод возраста сообщества в формате data(age)
 *
 * @param array $comm Данные комментария
 * @return string
 */
function __commPrntAgeEx($comm)
{
  $date = strtotime($comm['created_time']);
  $month = monthtostr(date("m",$date), true);
  $date_str = date('d',$date).' '.$month.' '.date('Y',$date);
  if($comm['year'])
    $age .= $comm['year'].' '.getSymbolicName($comm['year'], 'year').' ';
  if($comm['month'])
    $age .= $comm['month'].' '.getSymbolicName($comm['month'], 'month').' ';
  if($comm['day'])
    $age .= $comm['day'].' '.getSymbolicName($comm['day'], 'day');


  return $date_str .' '. ( $age ? "({$age})" : '(Создано сегодня)' );
}

/**
 * Вывод кнопки(ссылки) подписки в сообщество в зависимости от статуса
 *
 * @param array   $comm    Данные сообщества
 * @param integer $user_id Ид пользователя
 * @param integer $fromPage ИД страницы
 * @param string  $a_style  Стиль тега A
 * @param string  $span_style Стиль внутреннего тега SPAN
 * @return string
 */
function __commPrntSubmitButton($comm, $user_id, $fromPage = NULL, $mode = false,
                                $a_style = 'b-button b-button_flat b-button_flat_grey b-button_margbot_10_ipad',
                                $span_style = 'b-button__txt'){
	$html = !$mode ? '&nbsp;&nbsp;' : '';
	if((commune::isUserBanned($comm['id'],$user_id)) || ($comm['current_user_join_status'] != commune::JOIN_STATUS_ACCEPTED && $comm['author_uid']!=$user_id)) return false;
	if(commune::isCommuneSubscribed($comm['id'],$user_id)){
		$onclick = "xajax_SubscribeCommune(".$comm['id'].",false,'$mode'); return false;";
		$index = $mode=='green' ? 2 : false;
                $html .=    '<a href="javascript:void(0)" onclick="'.$onclick.'" class="'.$a_style.'">Отписаться</span></a>';
	}else{
		$onclick = "xajax_SubscribeCommune(".$comm['id'].",true,'$mode'); return false";
                $html .= $mode == 'green'
                ?   '<a href="javascript:void(0)" onclick="'.$onclick.'" class="'.$a_style.'">Подписаться</span></a>'
                :   '<a href="javascript:void(0)" onclick="'.$onclick.'" class="'.$a_style.'">Подписаться</span></a>';
	}
	return $html;
}function __commPrntSubmitButtonFromCommune($comm, $user_id, $fromPage = NULL, $mode = false){
	if((commune::isUserBanned($comm['id'],$user_id)) || ($comm['current_user_join_status'] != commune::JOIN_STATUS_ACCEPTED && $comm['author_uid']!=$user_id)) return false;
	
        ob_start();
        if (commune::isCommuneSubscribed($comm['id'],$user_id)) { ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">
                <a onclick="xajax_SubscribeCommune(<?= $comm['id'] ?>, false, 0, true);" class="b-layout__link b-layout__link_float_right b-layout__link_dot_c10600 b-layout__link_fontsize_11" href="javascript:void(0)">Отписаться</a>
                и подписаны на новые посты
            </div> 
        <? } else { ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">
                <a onclick="xajax_SubscribeCommune(<?= $comm['id'] ?>, true, 0, true);" class="b-layout__link b-layout__link_float_right b-layout__link_dot_c10600 b-layout__link_fontsize_11" href="javascript:void(0)">Подписаться</a>
                и не подписаны на новые посты
            </div>
        <? }
	$html = ob_get_clean();
        return $html;
}

/**
 * Вывод кнопки(ссылки) вступления в сообщество в зависимости от статуса
 *
 * @param array   $comm    Данные комментария
 * @param integer $user_id Ид пользователя
 * @param integer $fromPage ИД страницы
 * @param boolean $async вывод кнопки для асинхронного режима работы
 * @param string  $a_style стиль для тега A вместо дефолтного
 * @param string  $span_style стиль для внутреннего тега SPAN вместо дефолтного
 * @return string
 */
function __commPrntJoinButton($comm, $user_id, $fromPage = NULL, $async = false,
        $a_style = "b-button b-button_flat b-button_flat_green b-button_nowrap",
        $span_style = "b-button__txt")
{
  $html = '';
  $is_restricted = (bitStr2Int($comm['restrict_type']) & commune::RESTRICT_JOIN_MASK);
  
  if($comm['author_uid']==$user_id || !get_uid(false))
    $html = '';

  else if($comm['current_user_join_status'] == commune::JOIN_STATUS_NOT
          || $comm['current_user_join_status'] == commune::JOIN_STATUS_DELETED)
    $html = '<a href="javascript:void(0)" onclick="xajax_JoinCommune('.$comm["id"].', '.$async.'); return false;" class="'.$a_style.'">Вступить в сообщество</a>';
  else if($comm['current_user_join_status'] == commune::JOIN_STATUS_ASKED)
    $html = '<a href="javascript:void(0)" onclick="xajax_OutCommune('.$comm["id"].', '.$async.'); return false;" class="'.$a_style.'">Отозвать заявку</a>';
  else if($comm['current_user_join_status'] == commune::JOIN_STATUS_ACCEPTED)
    $html = '<a href="javascript:void(0)" onclick="xajax_OutCommune('.$comm["id"].', '.$async.'); return false;" class="'.$a_style.'">Выйти из сообщества</a>';
  return $html ? $html : '';
}

/**
 * Показывает диалог вступления/выхода пользователя из сообщества
 * 
 * @param  int $comm ID сообщества (см. таблицу commune)
 * @param  int $user_id ID пользователя
 * @return string
 */
function __commPrntInOutDialog($comm, $user_id){
  ob_start();
  $html = '';

  if($comm['author_uid']==$user_id)
    return false;

  $comm_name = $comm['name'];
  if($comm['current_user_join_status'] == commune::JOIN_STATUS_NOT
          || $comm['current_user_join_status'] == commune::JOIN_STATUS_DELETED){
        $msg = 'Вы действительно хотите вступить в сообщество';
  } else if($comm['current_user_join_status'] == commune::JOIN_STATUS_ASKED){
        $msg = 'Вы действительно хотите выйти из сообщества';
  }else if($comm['current_user_join_status'] == commune::JOIN_STATUS_ACCEPTED){
        $msg = 'Вы действительно хотите выйти из сообщества';
  }
    include(TPL_COMMUNE_PATH.'/tpl.in_out_dialog.php');
  $html = ob_get_clean();
  return $html;
}

/**
 * Вывод доп информации по юзеру
 *
 * @param array  $user Данные пользователя
 * @param string $pfx  Префикс данных
 * @return string
 */
function __commPrntUsrAvtr($user, $pfx = '')              
{
  	//if($user[$pfx.'prof_name']) {
  	//	$title_info = "Специализация: ".htmlspecialchars($user[$pfx.'prof_name'], ENT_QUOTES, 'cp1251');
  	//}
  	
  	//if($user[$pfx.'reg_date']) {
  	//	if($title_info) $title_info .= ", на сайте ".ElapsedMnths(strtotime($user[$pfx.'reg_date']));
  	//	else $title_info = "На сайте ".ElapsedMnths(strtotime($user[$pfx.'reg_date']));
  	//}
	
	return (
           "<a href='/users/".$user[$pfx.'login']."' title='".$user[$pfx.'uname']." ".$user[$pfx.'usurname']." ".@$title_info. " ' style='float:left;'>".
             view_avatar_info($user[$pfx.'login'], $user[$pfx.'photo'], 1).
           "</a>"
         );
}

/**
 * Информация пользователя
 *
 * @param array  $user  Данные пользователя
 * @param string $pfx   Префикс данных
 * @param string $cls   Класс отображения ссылки на пользователя (для работодателя либо для фрилансера)
 * @param string $sty   Дополнительный стиль отображения(если необходим)
 * @return string
 */
function __commPrntUsrInfo(                              
$user,
$pfx='',
$cls='',
$sty='',
$hyp=false, $ajax_view = false)
{
  
  global $session;
  $is_emp=is_emp($user[$pfx.'role']);
  $is_verify = ( $user[$pfx.'is_verify'] == 't' );
  $login=$user[$pfx.'login'];
  $uname=$user[$pfx.'uname'];
  $usurname=$user[$pfx.'usurname'];
  if($sty)  $sty = " style='$sty'";
  else{
      if($is_emp) $sty = " style='color:green'";
  }
  if(!$cls) $cls = ($is_emp ? 'b-username__login_color_6db335' : 'b-username__login_color_fd6c30');

  //require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
  //return (   (payed::CheckPro($login) ? ($is_emp ? view_pro_emp() : view_pro()).'&nbsp;' : '').

  if($hyp) {
      $uname = hyphen_words($user['dsp_uname']? $user['dsp_uname']: $uname);
      $usurname = hyphen_words($user['dsp_usurname']? $user['dsp_usurname']: $usurname);
  }
  
  /*!!!is_team!!!*/
  
  if(@$user[$pfx.'is_profi'] == 't') {
    $pro = view_profi();
  } else {
    $pro = ($user[$pfx.'is_pro']=='t'?($is_emp?view_pro_emp():view_pro2(($user[$pfx.'is_pro_test']=='t')?true:false)):""); 
  }
  
  $is_team = view_team_fl();
  if ($is_verify) {
      $pro      .=  view_verify();
      $is_team  .=  view_verify();
  }
  
  $seo_text = "<a class=\"b-username__link\"{$sty} href=\"/users/{$login}\" title=\"{$login}\">[".($user['dsp_login']? $user['dsp_login']: $login)."]</a>";              
  /*return (   ($user[$pfx.'is_team']=='t'?$is_team:$pro).
             "<span class='{$cls}'{$sty}>".
             $session->view_online_status($login)."&nbsp;".
             '<span class="cau-admin" id="cau_admin'.$user['id'].'">'.($user['is_admin'] == 't' ? 'Admin&nbsp;' : '').'</span>'.
             "<a class='{$cls}'{$sty} href='/users/{$login}' title='{$uname} {$usurname}'>".$uname." ".$usurname."</a>\n".
             ($ajax_view ? $bhtml : "<script type=\"text/javascript\">seo_print('{$bhtml}');</script> " ).
             "</span>"      );*/
    return (    $session->view_online_status($login)."<a class='b-username__link b-username__link_bold' href='/users/{$login}'>".$uname." ".$usurname."</a> ".
                "<span class='b-username__login b-username__login_bold $cls'>".
                    ($ajax_view ? $seo_text : seo_end($seo_text)).
                "</span> ".
                ($user[$pfx.'is_team']=='t'?$is_team:$pro)
            );
}


/**
 * Информация пользователя для главной страницы сообществ
 *
 * @param array  $user  Данные пользователя
 * @param string $pfx   Префикс данных
 * @param string $cls   Класс отображения ссылки на пользователя (для работодателя либо для фрилансера)
 * @param string $sty   Дополнительный стиль отображения(если необходим)
 * @return string
 */
function __commPrntUsrInfoMain(                              
$user,
$pfx='',
$cls='',
$sty='',
$hyp=false,
$admin=false,
$view_admin = false
)
{
  
  global $session;
  $is_emp=is_emp($user[$pfx.'role']);
  $login=$user[$pfx.'login'];
  $uname=$user[$pfx.'uname'];
  $usurname=$user[$pfx.'usurname'];
  if($sty)  $sty = " style='$sty'";
  else{
      if($is_emp) $sty = " style='color:green'";
  }
  if(!$cls) $cls = ($is_emp == 1 ? 'employer' : 'freelancer').'-name';
  $mcls = ($is_emp == 1 ? 'emp' : 'frl').'-name';
  //require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
  //return (   (payed::CheckPro($login) ? ($is_emp ? view_pro_emp() : view_pro()).'&nbsp;' : '').

  if($hyp) {
      $uname = hyphen_words($user['dsp_uname']? $user['dsp_uname']: $uname);
      $usurname = hyphen_words($user['dsp_usurname']? $user['dsp_usurname']: $usurname);
  }
  
  /*!!!is_team!!!*/
  if (@$user[$pfx.'is_profi'] == 't') {
    $pro = view_profi();  
  } else {
    $pro = ($user[$pfx.'is_pro']=='t'?($is_emp?view_pro_emp():view_pro2(($user[$pfx.'is_pro_test']=='t')?true:false)):""); 
  }
  
  $is_team = view_team_fl();

  $seo_text = "<span class=\"{$mcls}\">[</span><a class=\"{$cls}\" href=\"/users/{$login}\" title=\"{$login}\">{$login}</a><span class=\"{$mcls}\">]</span>";
  
  $html = ($user[$pfx.'is_team']=='t'?$is_team:$pro) . "
    <span class='{$cls}'>&nbsp;".$session->view_online_status($login)."&nbsp;
        <a class='{$cls}' href='/users/{$login}' title='{$uname} {$usurname}'>{$uname} {$usurname}</a> " . seo_end($seo_text) ."
    </span>
    <div class='commun-info'>
        " . (empty($user['author_id'])? "": "<span class='commun-creator'>Создатель сообщества</span>") . "
        " . ($view_admin ? "<div id='ne1{$user['note_user_id']}'><p>".reformat(stripslashes($user['note_txt']), 20, 0, 0, 1, 15)."</p></div>" : "") . "
        <div id='ne2{$user['note_user_id']}' style='display:none'>
            <textarea name='' cols='30' rows='5' class='tawl' rel='".commune::MEMBER_NOTE_MAX_LENGTH."'>{$user['note_txt']}</textarea>
            <input type='button' value='Сохранить' onclick='$(\"ne2{$user['note_user_id']}\").getElement(\"textarea\").disabled=true; xajax_UpdateNoteMP(".intval($user['note_user_id']).", ".intval($user['note_commune_id']).", $(\"ne2{$user['note_user_id']}\").getElement(\"textarea\").value)' />
            <span class='commun-info-edit'><a href='javascript:void(0)' onclick='memberNoteForm({$user['note_user_id']})'>Отменить</a></span>
        </div>
        " . ($admin ? "<p class='commun-info-edit' id='ne3{$user['note_user_id']}'><a href='javascript:void(0)' onclick='memberNoteForm({$user['note_user_id']})'>Редактировать информацию</a></p>": "") . "
    </div> 
  ";

  return $html;
  
  return (   ($user[$pfx.'is_team']=='t'?$is_team:$pro).
             "<span class='{$cls}'{$sty}>".
             $session->view_online_status($login)."&nbsp;".
             '<span class="cau-admin" id="cau_admin'.$user['id'].'">'.($user['is_admin'] == 't' ? 'Admin&nbsp;' : '').'</span>'.
             "<a class='{$cls}'{$sty} href='/users/{$login}' title='{$uname} {$usurname}'>".$uname." ".$usurname."</a>\n".
             " [<a class='{$cls}'{$sty} href='/users/{$login}' title='{$login}'>".($user['dsp_login']? $user['dsp_login']: $login)."</a>]".
             "</span>"      );
}



// !!! stdf

/**
 * Проверяет туда ли попал пользователь и есть ли у него на это право.
 * Возвращает адрес переброски пользователя, если он не туда попал,
 * и если не NULL, то надо будет вызвать header().
 * Если NULL, но $error не пуст, то надо будет выдать страницу ошибок __COMMUNES__ERROR и
 * прекратить операцию.
 * Заодно заполняет необходимые переменные.
 *
 * @global $uid    Ид пользователя
 * @global $id     Ид сообщения
 * @global $top_id Ид вкладки ТОП
 * @global $site   Сайт
 * @global $action Действие для данной функции при отсылке данных (submit)
 * 
 * @param string  $error Возвращает сообщение об ошибке
 * @param array   $comm  Возвращает данные по комментариям
 * @param array   $top   Возвращает популярные сообщения
 * @param integer $restrict_type  Возвращает тип пользования
 * @param integer $user_mod Возвращает битовую маску прав пользователя
 * @return string если не null, название страницы куда перенести пользователя
 */
function __commShaolin(&$error, &$comm, &$top, &$restrict_type, &$user_mod) {
    global $uid, $id, $top_id, $site, $action, $draft_id;
    
    $comm = NULL;
    $user_mod = 0;
    
    
    if ( $uid ) {
        $user_mod = commune::MOD_ADMIN * hasPermissions('communes');
        $user_mod |= commune::MOD_MODER * (($user_mod & commune::MOD_ADMIN) || hasPermissions('communes'));
        $user_mod |= commune::MOD_PRO * (payed::CheckPro(get_login($uid)) ? 1 : 0);
        $user_mod |= commune::MOD_EMPLOYER * ((int)is_emp());
        $user_mod |= commune::MOD_BANNED * is_banned($uid);
    }

    if (!$id) {
        if (!$site)
            return NULL;
            
        if ($site == 'Create') {
            if (!$uid)
                return '/fbd.php';

            if ( !($user_mod & (commune::MOD_PRO | commune::MOD_ADMIN))) {
                /*if ($user_mod & commune::MOD_EMPLOYER)
                    return '/payed-emp/';*/
                return '/proonly.php';
            }

            if (($limit = commune::GetUserCommunesLimits($uid)) && $limit['user_communes_count']) {
                if ($limit['user_communes_count'] >= commune::MAX_COUNT ) {
                    $error['name'] = 'Создание вашего сообщества';
                    $error['message'] = 'Вы уже создали максимальное количество сообществ.';
                    return NULL;
                }
                $seconds = $limit['seconds_passed_since_user_created_his_last_commune'];
                if ($seconds < commune::CREATION_INTERVAL ) {
                    $error['name'] = 'Создание вашего сообщества';
                    $wait = commune::CREATION_INTERVAL - $seconds;
                    $error['message'] = "Подождите {$wait} ".getSymbolicName($wait, 'second').'.';
                    return NULL;
                }
            }
        } else {
            return '/404.php';    
        }
        return NULL;
    }
    
    
    if(!($comm = commune::GetCommune($id, !$uid ? NULL : $uid, $user_mod)) )
        return '/commune/';

    if (!$uid ) {
        if ( $action ) 
            return "/commune/?id={$id}";

        if ( $site == 'Topic' );      // !!! ?site=Members -- можно поди тоже пустить...
        else if ( $site == 'Join' )
            return '/fbd.php';
        else if ( $site == 'Members') 
            return '/fbd.php';   
        else if ( $site )
            return "/commune/?id={$id}";
    } else if ( $uStatus = commune::GetUserCommuneRel($id, $uid) ) {
        $user_mod |= commune::MOD_COMM_MODERATOR * $uStatus['is_moderator'];
        $user_mod |= commune::MOD_COMM_MANAGER * $uStatus['is_manager'];
        $user_mod |= commune::MOD_COMM_ADMIN * ($uStatus['is_admin'] || $uStatus['is_moderator'] || $uStatus['is_manager']);
        $user_mod |= commune::MOD_COMM_AUTHOR * $uStatus['is_author'];
        $user_mod |= commune::MOD_COMM_ASKED * $uStatus['is_asked'];
        $user_mod |= commune::MOD_COMM_ACCEPTED * ($uStatus['is_accepted'] || ($user_mod & commune::MOD_COMM_ADMIN));
        $user_mod |= commune::MOD_COMM_BANNED * $uStatus['is_banned'];
    }
    
    if ($comm['is_blocked'] && !($user_mod & commune::MOD_MODER)) {
        if (($comm['author_id'] != $uid) || (($comm['author_id'] == $uid) && (($site && $site != 'Members') || $action))) {
            return '/commune/';
        }
    }
  
    if ( $user_mod & commune::MOD_COMM_BANNED && ! hasPermissions('communes') && $comm['restrict_type'] != '00') {
        $error['name'] = 'Нет доступа в сообщество';
        $error['message'] = 'Вы находитесь в бан-листе этой группы. Вы можете обратиться к создателю группы:<br/><br/>'.
                        '<div style="float:left">'.__commPrntUsrAvtr($comm, 'author_').'</div>'.
                        '<div style="padding-left:10px;float:left">'.
                           __commPrntUsrInfo($comm, 'author_').
                        '</div>';
        return NULL;
    }


    $restrict_type = bitStr2Int($comm['restrict_type']);


    if ( $restrict_type & commune::RESTRICT_READ_MASK ) {
        if ( $site != 'Join' && $action != 'Join'
         && ! ($user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_COMM_ACCEPTED | commune::MOD_ADMIN | commune::MOD_MODER)) )
        {
          $error['name'] = 'Нет доступа в сообщество';
          if ( $user_mod & commune::MOD_COMM_ASKED )
            $error['message'] = "&nbsp;Администратор сообщества <b>&laquo;{$comm['name']}&raquo;</b> еще не рассмотрел вашу заявку. Просмотр этого сообщества вам не доступен.<br/><br/>".__commPrntJoinButton($comm, $uid);
          else
            $error['message'] = "&nbsp;Вы не являетесь членом сообщества <b>&laquo;{$comm['name']}&raquo;</b>. Просмотр этого сообщества вам не доступен.<br/><br/>".__commPrntJoinButton($comm, $uid);
          return NULL;
        }
    }

    switch ($site) {
        case 'Join':
          
//        if ( $user_mod & commune::MOD_COMM_AUTHOR )
//          return "/commune/?id={$id}";

//        if ( $user_mod & commune::MOD_COMM_ASKED )
//          return "/commune/?id={$id}";
            break;


        case 'Create':
            if ( $id )
                return "/commune/?id={$id}";
            break;
            
        case 'Edit':
            if ( ! ($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR))  )
                return "/commune/?id={$id}";
            break;
            
        case 'Admin':
            if ( ! ($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR))  ) {
                if ( $user_mod & commune::MOD_COMM_MANAGER )
                    return "/commune/?id={$id}&site=Admin.members";

                return "/commune/?id={$id}";
            }
            break;
            
        case 'Admin.members':
            if ( !($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER) || hasPermissions('communes'))  )
                return "/commune/?id={$id}";
            break;
            
        case 'Topic':
            if ( $action && $action != 'do.Edit.post' && $action != 'do.Create.post' && $action != 'add_comment' && $action != 'edit_comment'  && $action != 'wysiwygUploadImage') { 
                //if ($action && $action != 'Edit.post')
                return "/404.php";
            }

            if ( ! $top_id
               || ! ($top = commune::GetTopMessageByAnyOther($top_id, $uid, $user_mod, TRUE))
               || ( $top['member_is_banned'] && $comm['restrict_type'] != '00' && ($action != 'do.Edit.post' && $action != 'do.Create.post')
                    && ! ($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER)))
               || ( $top['is_private'] == 't' && $top['user_id'] != $uid
                    && ! ($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER)))
               || ( $top['deleted_id'] && !hasPermissions('communes')))
                return "/404.php";
            break;

        case 'Members':
            break;
        
        case 'Newtopic' :
            if(commune::isBannedCommune($user_mod)) {
                $error['name'] = 'Нет доступа';
                $error['message'] = 'Вы находитесь в бан-листе этой группы. Вы можете обратиться к создателю группы:<br/><br/>'.
                                '<div style="float:left">'.__commPrntUsrAvtr($comm, 'author_').'</div>'.
                                '<div style="padding-left:10px;float:left">'.
                                   __commPrntUsrInfo($comm, 'author_').
                                '</div>';
                return NULL;
            }
            if (!($user_mod & commune::MOD_COMM_AUTHOR && !$comm['is_blocked']) && !($user_mod & (commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_ADMIN))) {
                return getFriendlyURL('commune_commune', $comm['id']);
            }
            break;
            
        case 'Editdraft':
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/drafts.php");
            $draftData = drafts::getDraft($draft_id, get_uid(false), 4);
            if (!$draftData) {
                return getFriendlyURL('commune_commune', $comm['id']);
            }
            break;
        
        case 'Edittopic' :
            break;
            
        default:
            if ($site)
                return "/404.php";
            break;
    }

    if ( $action ) {
        if ($action == 'Delete' && ! ($user_mod & commune::MOD_ADMIN) )
            return "/commune/?id={$id}";

            if (!$site) {
                if (($action == 'do.Edit.post' || $action == 'do.Create.post' )
                     && ( $user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_ACCEPTED) ))
                    return NULL;

            if ($action == 'Join')
                return NULL;

            return "/commune/?id={$id}";
        }
    }
    
    return NULL;
}

/**
 * Возвращает текст о том, что сообщество заблокировано
 *
 * @param string $reason        Причина блокировки
 * @param string $blocked_time  Время блока
 * @param string $moder_login   Кто заблокировал (логин)
 * @param string $moder_name    Имя фамилия кто заблокировал
 * @return string
 */
function __commPrntBlockedBlock($reason, $blocked_time, $moder_login, $moder_name, $comm_id = false) {
    /*return "
        <div class='br-moderation-options'>
            <a href='/about/feedback/' class='lnk-feedback' style='color: #fff;'>Служба поддержки</a>
            <div class='br-mo-status'><strong>Сообщество заблокировано!</strong> Причина: ".str_replace("\n", "<br>", htmlspecialchars($reason))."</div>
            <p class='br-mo-info'>".
            ($moder_login? "Заблокировал: <a href='/users/$moder_login' style='color: #FF6B3D'>$moder_name [$moder_login]</a><br />": '').
            "Дата блокировки: ".dateFormat('d.m.Y', $blocked_time)."</p>
        </div>
    ";*/
    ob_start() 
    ?><div class="b-fon b-fon_width_full b-fon_padbot_20">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_bg_ff6d2d">
            <span class="b-fon__attent_white"></span>
            <span class="b-fon__txt b-fon__txt_bold">Сообщество заблокировано</span>: <?= str_replace("\n", "<br>", $reason) ?> 
            <a href="/about/feedback" class="b-fon__link b-fon__link_bold">Служба поддержки</a>
            <div class="b-fon__txt b-fon__txt_fontsize_11 b-fon__txt_padtop_5 b-fon__txt_color_fff">
                <?= $moder_login ? 'Заблокировал <a class="b-fon__link" href="/users/'.$moder_login.'">'.$moder_login.'</a>' : "" ?> <?= dateFormat('d.m.Y', $blocked_time) ?> 
                <? if (hasPermissions('communes') && $comm_id) { ?>
                    <a onclick="banned.unblockedCommune(<?=$comm_id?>)" class="b-fon__link" href="javascript:void(0)"> Разблокировать</a>.
                <? } ?>
            </div>
        </div>
    </div><?
    $html = ob_get_clean();
    return $html;
}

function __htmlchars($str) {
	return str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($str));
}

/**
 * Возвращает HTML атрибуты ширины (и высоты) для картинки.
 * 
 * Примера использования не нашел
 * 
 * @param  array $m информация о картинке (типа того что возвращает getimagesize?).
 * @return string
 */
function _reWidthImg($m) {
    return ((int)$m[2] > 520 ? "width=520" : $m[0]);
}

/**
 * Возвращает HTML содержимое списка сообществ
 * 
 * @param  int $page номер страницы
 * @param  array  $communes массив со списком сообществ
 * @param  string $sub_om   код дополнительного условия сортировки или вкладки
 * @param  int $total общее количество сообществ
 * @param  string $search   если есть поисковоая строка для подсвечивания найденого в названияих и описаниях
 * @return string HTML содержимое списка сообществ
 */
function __commPrintPage( $page = 1, $communes = array(), $total = 0, $sub_om = '', $search = null , $is_ajax = false) {
    $sHtml = '';
    
    if ( !is_array($communes) || !count($communes) ) {
    	return '';
    }
    
    // начало нумерации сообществ для своей сортировки
    $limit          = commune::MAX_ON_PAGE;
    $start_position = ($page - 1) * $limit;
    
    $i = 0;
    foreach ($communes as $comm) {
        $i++;
        // Название.
        $comm_url = getFriendlyURL('commune_commune', $comm['id']);
        $name = "<a href='".$comm_url."' class='b-post__link'>" . ($search !== NULL ? highlight(reformat2($comm['name'], 25, 1, 1), $search, 20) : reformat2($comm['name'], 25, 1, 1)) . "</a>";
        $descr = ($search !== NULL ? highlight(reformat2($comm['descr'], 25, 1), $search) : reformat2($comm['descr'], 25, 1));
        
        // Сколько участников.
        $mAcceptedCnt = $comm['a_count'] - $comm['w_count'] + 1; // +1 -- создатель
        $mCnt = $mAcceptedCnt . ' участник' . getSymbolicName($mAcceptedCnt, 'man');
        
        $sHtml .= '<div class="b-post b-post_padbot_20">';
        $sHtml .= '<div class="b-post__body b-post__body_bordbot_solid_f0  b-post__body_padbot_30 b-layout">';
        $sHtml .= '<table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">';
        $sHtml .= '<tr class="b-layout__tr">';
        
        if ( $sub_om == commune::OM_CM_JOINED_MY ) {
            $sHtml .= '
            <div class="form c-my-sort">
        		<b class="b1"></b>
        		<b class="b2"></b>
        		<div class="form-in">
        			<a onclick="xajax_CommuneMove('.$comm['id'].', \'>\', '.($group_id?$group_id:0).', 10, '.$page.');" href="javascript:void(0);" class="b-sqr b-sqr-t1"><em></em></a>
        			<a onclick="$(\'commune_set_order_'.$comm['id'].'\').setStyle(\'display\', \'\');" href="javascript:void(0);" class="b-sqr b-sqr-t2"><em>'.($start_position+$i).'</em></a>
        			<a onclick="xajax_CommuneMove('.$comm['id'].', \'<\', '.($group_id?$group_id:0).', 10, '.$page.');" href="javascript:void(0);" class="b-sqr b-sqr-t3"><em></em></a>
        		</div>
        		<b class="b2"></b>
        		<b class="b1"></b>
        	</div>';
        }
        // картинка сообщества
        $js = '<script type="text/javascript">seo_print(\''.clearTextForJS(__commPrntImage($comm, 'author_')).'\');</script>';
        
        if($comm['is_blocked'] == 't') { // если сообщество заблокировано
            $sHtml .= __commPrntBlockedBlock($comm['blocked_reason'], $comm['blocked_time'], $comm['admin_login'], "{$comm['admin_name']} {$comm['admin_uname']}");
        }
        // левый блок, вставка изображения сообшщества
        $sHtml .= '<td class="b-layout__left b-layout__left_width_220">'.($is_ajax ? __commPrntImage($comm, 'author_') : $js).'</td>';
        // начало правого блока
        $sHtml .= '<td class="b-layout__right"><div class="b-post__content">';
        // блок голосования
        $sHtml .= '<div class="b-voting b-voting_float_right"><div id="idCommRating_'.$comm['id'].'">'.__commPrntRating($comm, get_uid(false)).'</div></div>';
        // название сообщества
        $sHtml .=  '<h3 class="b-post__title b-post__title_padbot_15">'.$name.'</h3>';
        // описание сообщества
        $sHtml .= '<div class="b-post__txt b-post__txt_padbot_20">'.$descr.'</div>';
        // инфа о сообществе
        $sHtml .= '<div class="b-post__foot">';
        // количество участников и постов
        $sHtml .=   '<div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_11">'.
                        '<a class="b-post__link b-post__link_fontsize_11 b-post__link_float_right" href="'.$comm_url.'">'.
                            $comm['themes_count'].' '.ending($comm['themes_count'], 'пост', 'поста', 'постов').
                        '</a>'.
                        $mAcceptedCnt.' '.ending($mAcceptedCnt, 'участник', 'участника', 'участников').
                    '</div>';
        
        // дата создания
        $sHtml .= '<div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_11">Создано '.__commPrntAgeEx($comm).'</div>';
        // создатель
        $sHtml .=   '<div class="b-post__txt b-post__txt_padbot_30 b-post__txt_fontsize_11">Создатель '.
                        '<span class="b-username b-username_bold b-username_fontsize_11">'.
                            __commPrntUsrInfo($comm, 'author_', '', '', false).
                        '</span>'.
                    '</div>';
        // вступить в сообщество
        if ($uid = get_uid(false)) {
            $sHtml .= '<span id="commSubscrButton_'.$comm['id'].'">'.__commPrntSubmitButton($comm, $uid, null, 'green').'</span>';
            $sHtml .= __commPrntJoinButton($comm, $uid, null, 1);
        }
        $sHtml .= '</div>'; // закрытие тега div.b-post__foot
        
        if ( $sub_om == commune::OM_CM_JOINED_MY ) {
        $sHtml .= '
        <div id="commune_set_order_'.$comm['id'].'" class="overlay ov-out ov-commune-sort" style="display: none;">
            <b class="c1"></b>
            <b class="c2"></b>
            <b class="ov-t"></b>
            <div class="ov-r">
                    <div class="ov-l">
                            <div class="ov-in">
                                    <label>Позиция</label> <input type="text" id="position_time_'.$comm['id'].'" name="position_time_'.$comm['id'].'" size="3">&nbsp;
                                    <button onclick="xajax_CommuneSetPosition('.$comm['id'].', '.($start_position+$i).', $(\'position_time_'.$comm['id'].'\').get(\'value\'), '.$total.', '.($group_id?$group_id:0).', 10, '.$page.');">Применить</button>&nbsp;
                                    <a href="javascript:void(0);" onclick="$(this).getParent(\'.overlay\').setStyle(\'display\', \'none\');" class="lnk-dot-666">Отменить</a>
                            </div>
                    </div>
            </div>
            <b class="ov-b"></b>
            <b class="c3"></b>
            <b class="c4"></b>
        </div>';            
        }
            
        $sHtml .= '</div></td>';
        $sHtml .= '</tr></table></div></div>';
    }
    
    return $sHtml;
}

/**
 * возвращает html блока с разделами сообщества, который в левом столбце
 * @param type $commune_id ID сообщества
 * @param type $om сортировка
 * @param type $curr_cat текущая активная категория
 * @param type $page страница
 * @return string html-код
 */
function __commPrintCategoriesList($commune_id, /*$is_for_admin, */$om, $curr_cat = '', $page = 0) {
    $comm = NULL;
    $user_mod = 0;
    $uid = get_uid(false);
    if($uid) {
        $status = commune::GetUserCommuneRel($commune_id, $uid);
    }
    $comm = commune::GetCommune($commune_id, !$uid?NULL:$uid, $user_mod);
    if($comm) { 
        $communeThemesCounts = commune::getCommuneThemesCount($comm['id']);
        if (hasPermissions('communes')) {
            $themes_count = $communeThemesCounts['count'];
            $for_admin = true;
        } elseif ($status['is_moderator'] == 1 || $status['is_admin'] == 1 || $status['is_author'] == 1) {
            $themes_count = $communeThemesCounts['count'] - $communeThemesCounts['admin_hidden_count'];
            $for_commune_admin = true;
        } else {
            $themes_count = $communeThemesCounts['count'] - $communeThemesCounts['hidden_count'];
            $for_admin = false;
        }
        $categories = commune::getCategories($commune_id, true);
        ob_start();
        include $_SERVER['DOCUMENT_ROOT'].'/commune/tpl.categories_list.php';
        return ob_get_clean();
    } else {
        return null;
    }
}
?>
