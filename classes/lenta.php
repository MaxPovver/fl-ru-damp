<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");



/**
 * Класс для работы с разделом "Лента"
 *
 */
class lenta {
  
    /**
     * Количество записей на странице
     *
     * @var integer
     */
    const MAX_ON_PAGE = 20;



    /**
     * Сохраняет настройки пользователя
     * Создает запись в таблице lenta, если таковой не было. Если пользователь уже имел настройки в lenta_options, то они удаляются
     *
     * @param boolean $has_lenta         истина, если настройки ранее были определены.
     * @param integer $user_id           id пользователя
     * @param boolean $my_team           истина, если стоит галка "Моя команда" или "Рекоммендованые мной".
     * @param boolean $all_profs         истина, если установлена галка "Все разделы".
     * @param mixed $communes            массив или сторока, разделенных запятыми идентификаторов сообществ, которые выбрал пользователь
     * @param mixed $prof_groups         массив или сторока, разделенных запятыми идентификаторов групп профессий, которые выбрал пользователь.
     * @param mixed $blog_groups         массив или сторока, разделенных запятыми идентификаторов групп блогов, которые выбрал пользователь.
     *
     * @return integer                   1 в случае успеха, 0 в случае неудачи
     */
    function SaveUserSettings($has_lenta, $user_id, $my_team, $all_profs, $communes, $prof_groups, $blog_groups)
    {
        global $DB; 
      
      if(!$user_id || !preg_match('/^[-0-9]+$/', (string) $user_id))  // На всяк. случай.
        return 0;
  
      $my_team = ($my_team ? 'true' : 'false');
      $all_profs = ($all_profs ? 'true' : 'false');

      $has_lenta = $DB->val("SELECT 1 FROM lenta WHERE user_id=?i", $user_id);
  
      if($has_lenta) {
        $sql = "UPDATE lenta
                   SET my_team_checked = ?,
                       all_profs_checked = ?
                 WHERE user_id = ?i ";
  
        if(!$DB->query($sql, $my_team, $all_profs, $user_id))
          return 0;
  
        $sql = "DELETE FROM lenta_options WHERE lenta_user_id = ?i";
        if(!$DB->query($sql, $user_id))
          return 0;
      }
      else {
        $sql = "INSERT INTO lenta (user_id, my_team_checked, all_profs_checked) VALUES (?i , ? , ? )";
        if(!$DB->query($sql, $user_id, $my_team, $all_profs))
          return 0;
      }
  
      if($communes && !empty($communes))
      {
        if(!is_array($communes))
          $communes = split('[^0-9]', $communes);
  
        $sql = '';
        
        foreach ( $communes as $commune_id ) {
            $sql .= $DB->parse( ', (?i, ?i)', $user_id, $commune_id );
        }
        
        if ( $sql ) {
            $DB->squery( 'INSERT INTO lenta_options (lenta_user_id, commune_id) VALUES ' . substr($sql, 1) );
        }
      }
  
      if($prof_groups && !empty($prof_groups))
      {
        if(!is_array($prof_groups))
          $prof_groups = split('[^0-9]', $prof_groups);
  
        $sql = '';
        
        foreach ( $prof_groups as $prof_group_id ) {
            $sql .= $DB->parse( ', (?i, ?i)', $user_id, $prof_group_id );
        }
  
        if ( $sql ) {
            $DB->squery( 'INSERT INTO lenta_options (lenta_user_id, prof_group_id) VALUES ' . substr($sql, 1) );
        }
      }

      if($blog_groups && !empty($blog_groups))
      {
        if(!is_array($blog_groups))
          $blog_groups = split('[^0-9]', $blog_groups);
  
        $sql = '';
        
        foreach ( $blog_groups as $blog_group_id ) {
            if ( !empty($blog_group_id) ) {
                $sql .= $DB->parse( ', (?i, ?i)', $user_id, $blog_group_id );
            }
        }

        if ( $sql ) {
            $DB->squery( 'INSERT INTO lenta_options (lenta_user_id, blog_group_id) VALUES ' . substr($sql, 1) );
        }
      }
      
      return 1;
    }



    /**
     * Возвращает настройки пользователя
     * В членах .communes и .prof_groups возвращаемого массива находятся либо пустые массивы,
     * либо массивы соответсвующих идентификаторов. То есть, например, в .communes могут лежать
     * идентификаторы сообществ, которые ранее определил пользователь (поставил галки).
     *
     * @param integer $user_id           id пользователя
     *
     * @return mixed                     данные пользователя в случае успеха, NULL в случае неудачи
     */
    function GetUserLenta($user_id)
    {
      global $DB; 
      $ret = NULL;
      $sql = "SELECT user_id, my_team_checked, all_profs_checked FROM lenta WHERE user_id = ?i ";
      if($ret=$DB->row($sql, $user_id)) {
        $ret['communes']    = array();
        $ret['prof_groups'] = array();
        $ret['blog_grs'] = array();
        $sql = "SELECT commune_id, prof_group_id, blog_group_id FROM lenta_options WHERE lenta_user_id = ?i";
        if($res=$DB->rows($sql, $user_id)) {
            foreach($res as $row) {
                if($row['commune_id']) {
                  $ret['communes'][] = $row['commune_id'];
                }
                if($row['prof_group_id']) {
                  $ret['prof_groups'][] = $row['prof_group_id'];
                }
                if($row['blog_group_id']) {
                  $ret['blog_grs'][] = $row['blog_group_id'];
                }
            }

        }
        
        return $ret;
      }
  
      return NULL;
    }



    /**
     * Получае количество тем в ленте по пользователю
     *
     * @param integer $user_id           id пользователя
     * @param mixed $communes            сторока, разделенных запятыми идентификаторов сообществ
     *
     * @return integer                   количество тем в случае успеха, 0 в случае неудачи
     */
    function GetLentaThemesCount($communes=NULL) {
        $sql = "
          SELECT COUNT(ms.id) as count 
            FROM commune_themes t
          INNER JOIN
            commune_messages ms
              ON ms.theme_id = t.id
             AND ms.parent_id IS NULL
          INNER JOIN
            users u
              ON u.uid = ms.user_id
             AND u.is_banned = '0'
           WHERE t.commune_id IN ({$communes}) AND ms.created_time > now() - '1 month'::interval
        ";
        $count = 0;
        if($communes) {
            $memBuff = new memBuff();
            $count_arr = $memBuff->getSql($error, $sql, 600);

            if(!$error)
              $count = $count_arr[0]['count'];
        }
      return $count;
    }  



    /**
     * Получаем блоки (элементы) ленты по пользователю
     * Получаем единый массив данных, необходимых для вывода элементов ленты. Разделением между топиком сообщества и работой портфолио
     * может служить, например, член массива .portfolio_id, у сообществ он NULL. В запросе pf.post_date может быть NULL, так как
     * поле portfolio.post_date заведено совсем недавно.
     *
     * @param integer $user_id           id пользователя
     * @param integer $my_team_checked   истина, если стоит галка "Моя команда" или "Рекоммендованые мной".
     * @param integer $all_profs_checked истина, если стоит галка "Все разделы".
     * @param mixed $prof_groups         строка идентификаторов групп профессий, разделенных запятыми.
     * @param mixed $communes            строка идентификаторов сообществ, разделенных запятыми.
     * @param integer $offset            SQL OFFSET
     * @param string $limit              SQL LIMIT
     * @param integer &$count=-1         количество работ, если пользователь определил в настройках разделы или стоит галка "Все разделы". Количество тем сообществ, считается отдельно
     * @param mixed $blog_groups         строка идентификаторов разделов блогов, разделенных запятыми.
     *
     * @return array                     массив тем в случае успеха, 0 в случае неудачи
     */
    function GetLentaItems($user_id, $my_team_checked=0, $all_profs_checked=0, $prof_groups=NULL, $communes=NULL, $offset=0, $limit='ALL', &$count=-1, $blog_groups=NULL) 
                 
    {
      global $DB;

      if($my_team_checked) {
        $DBProxy = new DB('plproxy');
        $sql = "SELECT uid FROM teams_get(?i);";
        $quids_team = $DBProxy->rows($sql, $user_id);
        $uids_team = array();
        $uids_team[] = 0;
        if($quids_team) {
          foreach($quids_team as $uid_team) {
            $uids_team[] = $uid_team['uid'];
          }
        }
      }

      $sql =
      "
        SELECT 
               li.*,
               u.is_banned::int as user_is_banned,
               u.is_pro as user_is_pro,
               u.is_profi AS user_is_profi,
               u.is_team as user_is_team,
               u.is_pro_test as user_is_pro_test,
               u.role as user_role,
               u.login as user_login,
               u.photo as user_photo,
               u.usurname as user_usurname,
               u.uname as user_uname,
               u.reg_date, u.is_chuck, u.is_verify
          FROM
          (".
            ( 
              !$all_profs_checked && !$prof_groups
              ? ''
              : "
                SELECT
                       1 as item_type,
                       pf.user_id as user_id, 
                       pf.post_date as post_time,
                       NULL as id,
                       'PF-' || pf.id as key,
                       NULL::integer as parent_id,
                       NULL::integer as theme_id,
                       NULL as msgtext,
                       NULL as title,
                       NULL::integer as deleted_id,
                       NULL::integer as modified_id,
                       NULL as created_time,
                       NULL as deleted_time,
                       NULL as modified_time,
                       NULL as file_exists,
                       NULL::integer as commune_id,
                       NULL as a_count,
                       NULL as is_blocked,
                       NULL as last_activity,
                       NULL::integer as commune_group_id,
                       NULL as commune_group_name,
                       NULL as commune_name,
                       NULL::integer as commune_author_id,
                       NULL as member_warn_count,
                       NULL::integer as member_id,
                       NULL as member_is_banned,
                       NULL as member_is_admin,
                       NULL as last_viewed_time,
                       NULL as modified_login,
                       NULL as modified_usurname,
                       NULL as modified_uname,
                       NULL as modified_by_commune_admin,
                       pf.id as portfolio_id,
                       pf.name as name,
                       pf.link as link,
                       pf.descr as descr,
                       pf.pict as pict,
                       pf.prev_pict as prev_pict,
                       pf.prof_id as prof_id,
                       p.name as prof_name,
                       p.id as prof_id, 
                       NULL as question,
                       NULL::boolean as poll_closed,
					   NULL::boolean as poll_multiple,
                       NULL::bigint as poll_votes,
                       NULL::boolean as close_comments,
                       NULL as is_private,
                       NULL::smallint as current_count,
NULL as dfl_title,
NULL as dfl_description,
NULL as dfl_type,
0 as dfl_jury_id,
NULL as dfl_image,
0 as dfl_type_id,
NULL as yt_link,
0 as count_comments,
0 as status_comments,
/*pf.moderator_status,*/
pfb.admin AS work_is_blocked
                  FROM
                  (
                    SELECT DISTINCT COALESCE(m.main_prof, p.id) as id
                      FROM prof_group pg
                    INNER JOIN
                      professions p
                        ON p.prof_group = pg.id
                    LEFT JOIN
                      mirrored_professions m
                        ON m.mirror_prof = p.id
                    ".( !$prof_groups ? '' : " WHERE pg.id IN ({$prof_groups})" )."
                  ) as px
                INNER JOIN
                  portf_choise pc
                    ON pc.prof_id = px.id
                   AND pc.user_id <> {$user_id}
                INNER JOIN
                  professions p
                    ON p.id = COALESCE(pc.prof_origin, pc.prof_id)
                INNER JOIN
                  portfolio pf
                    ON pf.prof_id = pc.prof_id
                   AND pf.user_id = pc.user_id
                   AND pf.post_date > now()  - '1 month'::interval
                   /*AND (pf.moderator_status != 0 OR pf.moderator_status IS NULL)*/
                   ".
                (
                  !$my_team_checked 
                  ? ''
                  : " 
                      AND pf.user_id IN (?l)
                    "
                ).
                "LEFT JOIN
                    portfolio_blocked AS pfb
                      ON pfb.src_id = pf.id".
                ( ($communes||$blog_groups) ? " UNION ALL" : '' )."
                "
            ).
            (
              !$communes
              ? ''
              : "
                SELECT 
                       2 as item_type,
                       ms.user_id as user_id,
                       ms.created_time as post_time,
                       ms.id as id,
                       'CM-' || ms.id as key,
                       ms.parent_id as parent_id,
                       ms.theme_id as theme_id,
                       ms.msgtext as msgtext,
                       ms.title as title,
                       ms.deleted_id as deleted_id,
                       ms.modified_id as modified_id,
                       ms.created_time as created_time,
                       ms.deleted_time as deleted_time,
                       ms.modified_time as modified_time,
                       ms.cnt_files as file_exists,
                       t.commune_id as commune_id,
                       t.a_count as a_count,
                       (CASE WHEN ctb.blocked_time IS NOT NULL THEN ctb.blocked_time
                             WHEN t.blocked_time IS NOT NULL THEN t.blocked_time
                             WHEN ms.deleted_time IS NOT NULL THEN ms.deleted_time
                             ELSE NULL END) as is_blocked,
                       t.last_activity as last_activity,
                       cg.id as commune_group_id,
                       cg.name as commune_group_name,
                       cm.name as commune_name,
                       cm.author_id as commune_author_id,
                       m.warn_count as member_warn_count,
                       m.id as member_id,
                       m.is_banned::int as member_is_banned,
                       m.is_admin::int as member_is_admin,
                       um.last_viewed_time as last_viewed_time,
                       umm.login as modified_login,
                       umm.usurname as modified_usurname,
                       umm.uname as modified_uname,
                       (am.user_id IS NOT NULL)::int as modified_by_commune_admin,
                       NULL as portfolio_id,
                       NULL as name,
                       NULL as link,
                       NULL as descr,
                       NULL as pict,
                       NULL as prev_pict,
                       NULL as prof_id,
                       NULL as prof_name,
                       NULL as prof_id, 
    				   cp.question as question,
    				   cp.closed as poll_closed,
					   cp.multiple as poll_multiple,
    				   cv._cnt as poll_votes,
                       t.close_comments as closed_comments,
                       t.is_private as is_private,
                       um.current_count as current_count,
NULL as dfl_title,
NULL as dfl_description,
NULL as dfl_type,
0 as dfl_jury_id,
NULL as dfl_image,
0 as dfl_type_id,
ms.youtube_link as yt_link,
t.a_count-1 as count_comments,
um.current_count as status_comments,
/*ms.moderator_status, */
NULL AS work_is_blocked
                 FROM commune_themes t
               INNER JOIN
                 commune cm
                   ON cm.id = t.commune_id
               INNER JOIN
                 commune_groups cg
                   ON cg.id = cm.group_id
               INNER JOIN
                 commune_messages ms
                   ON ms.theme_id = t.id
                  AND ms.parent_id IS NULL
                  AND ms.created_time > now()  - '1 month'::interval
                  /*AND (ms.moderator_status != 0 OR ms.moderator_status IS NULL)*/
               LEFT JOIN
                 commune_members m
                   ON m.user_id = ms.user_id
                  AND m.commune_id = t.commune_id
               LEFT JOIN
                 users umm
                   ON umm.uid = ms.modified_id
               LEFT JOIN
                 commune_members am
                   ON am.user_id = umm.uid
                  AND am.commune_id = cm.id
                  AND am.is_admin = true
			LEFT JOIN
			  commune_poll cp
			    ON cp.theme_id = ms.theme_id
            LEFT JOIN
                commune_theme_blocked ctb
                ON ctb.theme_id = t.id
			LEFT JOIN
			  (SELECT theme_id, COUNT(answer_id) AS _cnt FROM commune_poll_votes WHERE user_id = {$user_id} GROUP BY theme_id) cv
			    ON cv.theme_id = ms.theme_id
               LEFT JOIN
                 commune_users_messages um
                   ON um.message_id = ms.id
                  AND um.user_id = {$user_id}
                WHERE t.commune_id IN ({$communes})
                ".
                ( ($blog_groups) ? " UNION ALL" : '' )
            ).           
            (
              !$blog_groups
              ? ''
              : "
SELECT 
                    4 as item_type,
                    bm.fromuser_id as user_id,
                    bm.post_time as post_time,
                    bm.id as id,
                    'BL-' || bm.id as key,
                    NULL as parent_id,
                    b.thread_id as theme_id,
                    bm.msgtext as msgtext,
                    bm.title as title,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    bg.id as commune_group_id,
                    bg.t_name as commune_group_name,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    bp.question as question,
                    bp.closed as poll_closed,
					bp.multiple as poll_multiple,
                    bv._cnt as poll_votes,
                    b.close_comments as closed_comments,
                    NULL,
                    w.status AS current_count,
NULL,
NULL,
NULL,
NULL,
NULL,
NULL,
bm.yt_link as yt_link,
(b.messages_cnt-1) as count_comments ,
w.status as status_comments,
/*bm.moderator_status, */
NULL AS work_is_blocked 
FROM blogs_themes b 
INNER JOIN blogs_msgs_".date('Y')." bm
        ON bm.thread_id = b.thread_id
       AND bm.reply_to IS NULL
       AND (b.is_private='f' OR bm.fromuser_id={$user_id})
       AND bm.post_time > now()  - '1 month'::interval
       /*AND (bm.moderator_status != 0 OR bm.moderator_status IS NULL)*/
LEFT JOIN blogs_blocked ON blogs_blocked.thread_id = b.thread_id
INNER JOIN blogs_groups bg
        ON bg.id = b.id_gr
LEFT JOIN blogs_poll bp
        ON bp.thread_id = bm.thread_id
LEFT JOIN (SELECT thread_id, COUNT(answer_id) AS _cnt FROM blogs_poll_votes WHERE user_id = {$user_id} GROUP BY thread_id) bv
        ON bv.thread_id = bm.thread_id
LEFT JOIN (SELECT * FROM blogs_themes_watch WHERE user_id = '$user_id') AS w ON (theme_id=b.thread_id)

   
WHERE b.id_gr IN ($blog_groups) AND bm.deleted IS NULL AND blogs_blocked.thread_id IS NULL

                " . (date('n') < 2 ? " 
                
                UNION ALL
                
                SELECT 
                    4 as item_type,
                    bm.fromuser_id as user_id,
                    bm.post_time as post_time,
                    bm.id as id,
                    'BL-' || bm.id as key,
                    NULL as parent_id,
                    b.thread_id as theme_id,
                    bm.msgtext as msgtext,
                    bm.title as title,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    bg.id as commune_group_id,
                    bg.t_name as commune_group_name,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    bp.question as question,
                    bp.closed as poll_closed,
					bp.multiple as poll_multiple,
                    bv._cnt as poll_votes,
                    b.close_comments as closed_comments,
                    NULL,
                    w.status AS current_count,
NULL,
NULL,
NULL,
NULL,
NULL,
NULL,
bm.yt_link as yt_link,
(b.messages_cnt-1) as count_comments ,
w.status as status_comments,
/*bm.moderator_status, */
NULL AS work_is_blocked 
FROM blogs_themes b 
INNER JOIN blogs_msgs_".(date('Y') - 1)." bm
        ON bm.thread_id = b.thread_id
       AND bm.reply_to IS NULL
       AND (b.is_private='f' OR bm.fromuser_id={$user_id})
       AND bm.post_time > now()  - '1 month'::interval
       /*AND (bm.moderator_status != 0 OR bm.moderator_status IS NULL)*/
LEFT JOIN blogs_blocked ON blogs_blocked.thread_id = b.thread_id
INNER JOIN blogs_groups bg
        ON bg.id = b.id_gr
LEFT JOIN blogs_poll bp
        ON bp.thread_id = bm.thread_id
LEFT JOIN (SELECT thread_id, COUNT(answer_id) AS _cnt FROM blogs_poll_votes WHERE user_id = {$user_id} GROUP BY thread_id) bv
        ON bv.thread_id = bm.thread_id
LEFT JOIN (SELECT * FROM blogs_themes_watch WHERE user_id = '$user_id') AS w ON (theme_id=b.thread_id)

   
WHERE b.id_gr IN ($blog_groups) AND bm.deleted IS NULL AND blogs_blocked.thread_id IS NULL

                " : '') 
            ).
"

          ) AS li
        INNER JOIN
          users u
            ON u.uid = li.user_id
           AND u.is_banned = '0'
            

        ORDER BY li.post_time DESC".
                 ($all_profs_checked || $prof_groups ? ', li.portfolio_id DESC' : '')."
        LIMIT {$limit} OFFSET {$offset}
      ";

      $res = $DB->rows($sql, $uids_team);
      if($res)
      {
        foreach($res as $row) {
            $ret[$row['key']] = $row;
            if($row['item_type']==2) {
                $ids2[] = $row['id'];
            }
            if($row['item_type']==4) {
                $ids4[] = $row['id'];
            }
//            if($row['id']) $ids[] = $row['id'];
        }
                

        if($ids2) {
            //$sql = "SELECT file.*, commune_attach.cid, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid IN (".implode(", ", $ids2).")";
            //$res2 = $DB->rows($sql);
            $res2 = CFile::selectFilesBySrc(commune::FILE_TABLE, $ids2);
            foreach($res2 as $row) {
                $ret['CM-'.$row['src_id']]['attach'][] = $row;
            } 
        }

        if($ids4) {
            $sql = 'SELECT * FROM file_blogs WHERE src_id IN (?l)';
            $res2 = $DB->rows($sql, $ids4);
            foreach($res2 as $row) {
                $ret['BL-'.$row['src_id']]['attach'][] = $row;
            } 
        }
        
        $count = 0;
        if($all_profs_checked || $prof_groups || $communes || $blog_groups)
        {
            $sql = "SELECT SUM(items.count) as count FROM (".
                        ( 
                        !$all_profs_checked && !$prof_groups
                        ? ''
                        : 
                        "
                            SELECT
                                COUNT(pf.id) as count
                            FROM (
                                    SELECT DISTINCT COALESCE(m.main_prof, p.id) as id
                                    FROM prof_group pg
                                    INNER JOIN professions p ON p.prof_group = pg.id
                                    LEFT JOIN mirrored_professions m ON m.mirror_prof = p.id
                                    ".( !$prof_groups ? '' : " WHERE pg.id IN ({$prof_groups})" )."
                                 ) as px
                             INNER JOIN portf_choise pc
                                     ON pc.prof_id = px.id
                                    AND pc.user_id <> {$user_id}
                             INNER JOIN professions p
                                     ON p.id = COALESCE(pc.prof_origin, pc.prof_id)
                             INNER JOIN portfolio pf
                                     ON pf.prof_id = pc.prof_id
                                    AND pf.user_id = pc.user_id
                                    AND pf.post_date > now()  - '1 month'::interval".
                (
                  !$my_team_checked 
                  ? ''
                  : " 
                      AND pf.user_id IN (".implode(',', $uids_team).")
                    "
                ).
                        (($communes||$blog_groups) ? " UNION ALL" : '' )
                        ).
                        ( 
                        !$communes
                        ? ''
                        : 
                        "
                            SELECT 
                                COUNT(ms.id) as count
                            FROM commune_themes t
                            INNER JOIN commune_messages ms 
                                    ON ms.theme_id = t.id
                                   AND ms.parent_id IS NULL
                                   AND ms.created_time > now()  - '1 month'::interval
                            INNER JOIN users u
                                    ON u.uid = ms.user_id
                                   AND u.is_banned = '0'
                            WHERE t.commune_id IN ({$communes})
                        ".(($blog_groups) ? " UNION ALL" : '' )
                        ).
                        ( 
                        !$blog_groups
                        ? ''
                        : 
                        "
                            SELECT COUNT(1) as count
                              FROM blogs_themes b 
                             WHERE b.id_gr IN ($blog_groups) AND b.deleted IS NULL AND b.is_blocked = false
                               AND (b.is_private='f' OR b.fromuser_id={$user_id})
                               AND b.post_time > now()  - '1 month'::interval
                        "
                        ).
                    ") as items";

/*
          $sql =
          "
            SELECT COUNT(pf.id) as count
                  FROM prof_group pg
                INNER JOIN
                  professions p
                    ON p.prof_group = pg.id
                INNER JOIN
                  portf_choise pc
                    ON pc.prof_id = p.id
                   AND pc.user_id <> {$user_id}
                INNER JOIN
                  portfolio pf
                    ON pf.prof_id = pc.prof_id
                   AND pf.user_id = pc.user_id
                INNER JOIN
                  freelancer f
                    ON f.uid = pf.user_id
                   AND f.is_banned = '0'".
                (
                  !$my_team_checked
                  ? ''
                  : " INNER JOIN
                        teams tm
                          ON tm.target_id = f.uid
                         AND tm.user_id = {$user_id}"
              ).
            " WHERE pf.post_date > now() - '1 month'::interval "
            .
            ( !$prof_groups ? '' : " AND pg.id IN ({$prof_groups})" );
*/  
          $memBuff = new memBuff();
          $count_arr = $memBuff->getSql($error, $sql, 120);
  
          if(!$error)
            $count = $count_arr[0]['count'];
        }
        return $ret;
      }
  
      return 0;
    }



    /**
     * Получаем "избранное" пользователя
     * Возвращается массив, индексированный идентификаторами топиков сообществ и/или работ портфолио
     * с добавлением соответствующих префиксов. Если префикс индекса массива 'CM', значит речь идет
     * о топ-сообщении сообщества, которую пользователь положил к себе в избранное. Если префикс 'PF', то 
     * это работа. Контент по данным индексам для сообществ и портфолио разный и имена членов
     * могут быть разные (см. код).
     *
     * @param integer $user_id           id пользователя
     *
     * @return array                     избранное в случае успеха, 0 в случае неудачи
     */
    function GetFavorites($user_id, $sort="date") {
        global $DB; 
        switch($sort) {
            case "date": 
                $_sort = "sort_time DESC";
                break;
            case "priority": 
                $_sort = "priority DESC";
                break;
            case "abc":
                $_sort = "main_title ASC";   
                break;
            default:
                $_sort = "sort_time DESC";   
                break;   
        }
        
        
        $sql = "(SELECT ms.created_time as sort_time, ms.id, ms.title, 'login' as login, lf.priority, lf.title as title_fav, th.commune_id as cid, 'CM' as pfx, COALESCE(NULLIF(lf.title, ''), ms.title) as main_title
                         FROM lenta_fav lf
                       INNER JOIN
                         commune_messages ms
                           ON ms.id = lf.commune_message_id
                       INNER JOIN
                         commune_themes th
                           ON th.id = ms.theme_id
                        WHERE lf.lenta_user_id = {$user_id}
                          AND lf.commune_message_id IS NOT NULL
                UNION ALL

                SELECT pf.post_date as sort_time, pf.id, pf.name as title, u.login, lf.priority, lf.title as title_fav, 0 as cid, 'PF' as pfx, COALESCE(NULLIF(lf.title, ''), pf.name) as main_title
                         FROM lenta_fav lf
                       INNER JOIN
                         portfolio pf
                           ON pf.id = lf.portfolio_id
                       INNER JOIN
                         freelancer u
                           ON u.uid = pf.user_id
                        WHERE lf.lenta_user_id = {$user_id}
                          AND lf.portfolio_id IS NOT NULL 
                UNION ALL
                SELECT ms.post_time as sort_time, ms.thread_id as id, ms.title, 'login' as login, lf.priority, lf.title as title_fav, 0 as cid, 'BL' as pfx, COALESCE(NULLIF(lf.title, ''), ms.title) as main_title
                         FROM lenta_fav lf
                       INNER JOIN
                         blogs_msgs ms
                           ON ms.thread_id = lf.blog_id
                        WHERE lf.lenta_user_id = {$user_id}
                          AND lf.blog_id IS NOT NULL
                          AND ms.reply_to IS NULL
                ) 
                ORDER BY {$_sort}";

        $res = $DB->rows($sql);
        if($res) {
            $favs=array();
            foreach($res as $row) {
                $favs[$row['pfx'].$row['id']] = 
                    array('title'      => $row['main_title'], 
                          'commune_id' => $row['cid'], 
                          'priority'   => $row['priority'], 
                          'user_login' => $row['login'],
                          'pfx'        => $row['pfx']);
            }
        }  
        
        return $favs;
    }
    
    /**
     * Берем одну из закладок
     *
     * @param unknown_type $user_id
     * @param unknown_type $msg_id
     */
    function getFav($user_id, $msg_id, $type='CM') {
        global $DB; 
        switch($type) {
            case 'CM':
                $sql = "SELECT ms.id, ms.title, th.commune_id, lf.priority, lf.title as title_fav
                         FROM lenta_fav lf
                       INNER JOIN
                         commune_messages ms
                           ON ms.id = lf.commune_message_id
                       INNER JOIN
                         commune_themes th
                           ON th.id = ms.theme_id
                        WHERE lf.lenta_user_id = ?i
                          AND lf.commune_message_id = ?i";
                break;
            case 'PF':
                $sql = "SELECT pf.id, pf.name as title, u.login, lf.priority, lf.title as title_fav
                        FROM lenta_fav lf
                       INNER JOIN
                         portfolio pf
                           ON pf.id = lf.portfolio_id
                       INNER JOIN
                         freelancer u
                           ON u.uid = pf.user_id
                        WHERE lf.lenta_user_id = ?i
                          AND lf.portfolio_id = ?i";    
                break;
            case 'BL':
                $sql = "SELECT ms.thread_id as id, ms.title, lf.priority, lf.title as title_fav
                         FROM lenta_fav lf
                       INNER JOIN
                         blogs_msgs ms
                           ON ms.thread_id = lf.blog_id
                        WHERE lf.lenta_user_id = ?i
                          AND lf.blog_id = ?i
                          AND ms.reply_to IS NULL";
                break;
        }

 
        $row = $DB->row($sql, $user_id, $msg_id);
        if(!$row) return false;
        
        $row['title'] = $row['title_fav']!=''?$row['title_fav']:$row['title'];
        return $row; 
    }



    /**
     * Добавляет/удаляет элемент ленты из избранного
     * Функция работает, только если один из идентификаторов определен, а второй нет. То есть,
     * нельзя, чтобы одновременно оба были определены либо неопределены.
     *
     * @param integer $user_id            id пользователя
     * @param integer $commune_message_id идентификатор топика сообщества.
     * @param integer $portfolio_id       идентификатор работы портфолио
     * @param integer $blog_id     идентификатор топика блога
     * @param boolean $undo               истина, если нужно убрать из закладок, иначе добавить.
     *
     * @return integer                    1 в случае успеха, 0 в случае неудачи
     */
    function AddFav($user_id, $commune_message_id, $portfolio_id, $blog_id, $undo = FALSE, $priority, $title=false) {
        global $DB;
        if(!(
                (!$portfolio_id && $commune_message_id && !$blog_id) || 
                ($portfolio_id && !$commune_message_id && !$blog_id) ||
                (!$portfolio_id && !$commune_message_id && !$blog_id) ||
                (!$portfolio_id && !$commune_message_id && $blog_id)
                )) return 0;
        if(!$portfolio_id) $portfolio_id = NULL;
        if(!$commune_message_id) $commune_message_id = NULL;
        if(!$blog_id) $blog_id = NULL;
        
        if(!$undo) {
            $pf_sql = is_null($portfolio_id)?"":$DB->parse(" AND portfolio_id = ?", $portfolio_id);
            $cm_sql = is_null($commune_message_id)?"":$DB->parse(" AND commune_message_id = ?", $commune_message_id);
            $bl_sql = is_null($blog_id)?"":$DB->parse(" AND blog_id = ?", $blog_id);
            
            $sql = "SELECT lenta_user_id FROM lenta_fav WHERE lenta_user_id = ? {$pf_sql} {$cm_sql} {$bl_sql}";
            $row = $DB->row($sql, $user_id);
            $stitle = htmlspecialchars($title);
            $stitle = $title==='' ? NULL : $stitle;
            if($row['lenta_user_id']>0) {
                $stitle = $title===false ? '' : $DB->parse(", title = ?u", $stitle);
                $sql = "UPDATE lenta_fav SET priority = ? {$stitle} WHERE lenta_user_id = ? {$pf_sql} {$cm_sql} {$bl_sql}";
                if(!$DB->query($sql, $priority, $user_id)) return 0;
            } else {
                $sql = "INSERT INTO lenta_fav (lenta_user_id, portfolio_id, commune_message_id, priority, title, blog_id) VALUES (?, ?, ?, ?, ?, ?)";
                if(!$DB->query($sql, $user_id, $portfolio_id, $commune_message_id, $priority, $stitle, $blog_id)) return 0;
            }
        } else {
            self::DelFav($user_id, $commune_message_id, $portfolio_id, $blog_id);
        }
        
        return 1;
    }
    
    /**
     * Удалить из закладок
     *
     * @param integer $user_id            id пользователя
     * @param integer $commune_message_id идентификатор топика сообщества.
     * @param integer $portfolio_id       идентификатор работы портфолио
     * @param integer $blog_id            идентификатор топика блога.
     */
    function DelFav($user_id, $commune_message_id, $portfolio_id, $blog_id) {
        global $DB;
        if ( !is_null($portfolio_id) ) {
            $and = $DB->parse("portfolio_id = ?", $portfolio_id);
        }
        if ( !is_null($commune_message_id) ) {
            $and = $DB->parse("commune_message_id = ?", $commune_message_id);
        }
        if ( !is_null($blog_id) ) {
            $and = $DB->parse("blog_id = ?", $blog_id);
        }
        
        $sql = "DELETE FROM lenta_fav WHERE lenta_user_id = ?i AND {$and}";
        if(!$DB->query($sql, $user_id)) return 0; 
        
        return 1;   
    }
}




    /**
     * Выводит блок закладок пользователя
     *
     * @param array $favs                массив закладок
     * @param integer $user_id           id пользователя
     *
     * @return HTML                      HTML-код
     */
    function __lentaPrntFavs($favs, $user_id) {
        if(!is_array($favs)) return false;
        ob_start();
        foreach($favs as $key=>$fav) { 
            $pfx    = substr($key,0,2);
            $fav_id = substr($key,2);
            switch($pfx) {
                case 'CM':
                    $fav_href = "/commune/?id={$fav['commune_id']}&site=Topic&post={$fav_id}";
                    break;
                case 'PF':
                    $fav_href = "/users/{$fav['user_login']}/viewproj.php?prjid={$fav_id}";
                    break;
                case 'BL':
                    $fav_href = getFriendlyURL("blog", $fav_id);
                    break;
            }
            
            echo "<li id=\"fav{$key}\">";
            echo __lentaPrntFavContent($fav, $fav_id, $user_id, $pfx, $fav_href);
            echo "</li>";
        }
    	$str = ob_get_contents();
    	ob_end_clean();
    	return $str;
    }
     /*   ob_start();
    ?>
      <ul class="favs">
        <? 
          foreach ($favs as $key => $fav)
          {
            $pfx    = substr($key,0,2);
            $fav_id = substr($key,2);
            $fav_href = 
            (
              $pfx=='CM'
              ? "/commune/?id={$fav['commune_id']}&site=Topic&post={$fav_id}"
              : "/users/{$fav['user_login']}/viewproj.php?prjid={$fav_id}"
            );
        ?>
          <li class="fav" id="fav<?=$pfx.$fav_id?>">
            <a class="blue" href="<?=$fav_href?>">
              <?=($fav['title'] ? parse_words($fav['title'], 18) : '<без темы>')?>
            </a>&nbsp;
            <img src="/images/ico_close1.gif" alt="Удалить из закладок"
                 onClick="xajax_AddFav(<?=$fav_id?>, '<?=$pfx?>', <?=$user_id?>, 1)">
          </li>
        <? } ?>
      </ul>
    <?
      $str = ob_get_contents();
      ob_end_clean();
      return $str;
    }*/
    
    /**
     * Вывод одной закладки в блоке
     * @see __lentaPrntFavs()
     *
     * @param array   $fav    закладка
     * @param mixed   $key    ключ (ид) закладки 
     * @param integer $user_id ИД пользователя
     * @param integer $om      Для навигации
     * @return string   HTML
     */
    function __lentaPrntFavContent($fav, $key, $user_id, $pfx, $fav_href) {
      global $stars; 
      $stars = array(0=>'bsg.png', 1=>'bsgr.png', 2=>'bsy.png', 3=>'bsr.png');
      ob_start();
    ?>
												<img src="/images/bookmarks/<?=$stars[$fav['priority']]?>" alt="" id="curfavstar<?=$pfx.$key?>" />
                                                <input id="favpriority<?=$pfx.$key?>" value="<?=$fav['priority']?>" type="hidden" />
												<div class="bm-l">
													<a href=""><img src="/images/ico_edit2.gif" alt="Редактировать закладку" onclick="if(!$('fav<?=$pfx.$key?>edit')) { xajax_Lenta_EditFav('<?=$key?>', 0, '', 'edit', '<?=$pfx?>'); } return false;" /></a>
													<a href=""><img src="/images/ico_close2.gif" alt="Удалить из закладок" onClick="if(confirm('Удалить закладку?')) xajax_Lenta_AddFav(<?=$key?>, '<?=$pfx?>', <?=$user_id?>, 1);  return false;" /></a>
												</div>
												<span><a href="<?=$fav_href?>"><?=($fav['title'] ? reformat2($fav['title'], 18, 1, 1) : '&lt;без темы&gt;')?></a></span>
<!--
        <table border="0" cellpadding="2" cellspacing="2">
            <tbody>
                <tr valign="top">
                    <td style="width:10px"><img alt="" src="/images/ico_star_<?=$fav['priority']?>.gif" align="absmiddle" border="0" width="15" height="15">
                    <input id="favpriority<?=$pfx.$key?>" value="<?=$fav['priority']?>" type="hidden">
                    </td>
                    <td style="width: 214px;"><a class="blue" href="<?=$fav_href?>"><?=($fav['title'] ? stripslashes(reformat2($fav['title'], 18, 1, 1)) : '<без темы>')?></a>&nbsp;<nobr>
                    <img style="cursor:pointer;" src="/images/ico_close2.gif" alt="Удалить из закладок" onClick="if(confirm('Удалить закладку?')) xajax_Lenta_AddFav(<?=$key?>, '<?=$pfx?>', <?=$user_id?>, 1)">
                    &nbsp;<img style="cursor:pointer;" src="/images/ico_edit2.gif" alt="Редактировать закладку" onclick="xajax_Lenta_EditFav('<?=$key?>', 0, '', 'edit', '<?=$pfx?>')"></nobr>
                    </td>
                 </tr>
              </tbody>
        </table>
-->
    <?	
      return ob_get_clean();
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
function __LentaPrntUsrInfo(                              
$user,
$pfx='',
$cls='',
$sty='',
$hyp=false,
$show_userpic=false)
{
  global $session;
  $is_emp=is_emp($user[$pfx.'role']);
  $login=$user[$pfx.'login'];
  $uname=$user[$pfx.'uname'];
  $photo=$user[$pfx.'photo'];

  $usurname=$user[$pfx.'usurname'];

  if($sty)  $sty = " style='$sty'";

  if(!$cls) $cls = ($is_emp ? '6db335' : '000');

  //require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
  //return (   (payed::CheckPro($login) ? ($is_emp ? view_pro_emp() : view_pro()).'&nbsp;' : '').

  if($hyp) {
      $uname = hyphen_words($user['dsp_uname']? $user['dsp_uname']: $uname);
      $usurname = hyphen_words($user['dsp_usurname']? $user['dsp_usurname']: $usurname);
  }

  if($show_userpic) {
    $avatar = view_avatar($login, $photo, 1);
  }
  
  $ret = $avatar.
             "<span class='b-layout__txt b-layout__txt_color_{$cls}'{$sty}><a class='b-layout__link b-layout__link_color_{$cls}'{$sty} href='/users/{$login}' title='{$uname} {$usurname}'>".$uname." ".$usurname."</a>\n".
             "  [<a class='b-layout__link b-layout__link_color_{$cls}'{$sty} href='/users/{$login}' title='{$login}'>".($user['dsp_login']? $user['dsp_login']: $login)."</a>]".view_mark_user($user, $pfx)."</span>";  

  return $ret;
}

?>
