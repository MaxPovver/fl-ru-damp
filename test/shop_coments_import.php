<?php
/**
 * НА ОДИН РАЗ
 * Копирование пользователей подписавшихся на уведомления о комментариях в магазине.
 * Копирование комментариев к работам в магазине из блогов в новые таблицы.
 */

set_time_limit(0);

require_once("../classes/config.php");
require_once("../classes/stdf.php");

if ( !$DB->start() ) {
	die( "\nCOULD NOT START TRANSACTION\n" );
}

// 1. Копирование пользователей подписавшихся на уведомления о комментариях в магазине.

if ( !$DB->query('TRUNCATE shop_users CASCADE') ) {
    $DB->rollback();
	die( "\nCOULD NOT TRUNCATE shop_users\n" );
}

$mRid = $DB->query( "SELECT w.user_id AS uid, s.id AS shop_id FROM blogs_themes_watch w 
    INNER JOIN blogs_themes t ON w.theme_id = t.thread_id 
    INNER JOIN shop s ON s.id = t.id_gr 
    WHERE t.base = 4 AND w.is_mail='t' ORDER BY s.id" );

if ( $mRid ) {
    $aData = array('is_subscr' => 't');
    
    while ( $aRow = pg_fetch_assoc($mRid) ) {
        $aData = array_merge( $aData, $aRow );
        
        if ( !$DB->insert('shop_users', $aData) ) {
            $DB->rollback();
        	die( "\nCOULD NOT INSERT is_subscr\n" );
        }
    }
}
else {
    $DB->rollback();
	die("\nCOULD NOT SELECT is_subscr\n");
}

// 2. Копирование комментариев к работам в магазине из блогов в новые таблицы.

if ( 
    !$DB->query('DROP TRIGGER "bD shop_comments_files" ON shop_comments_files;
    TRUNCATE shop_comments CASCADE;
    CREATE TRIGGER "bD shop_comments_files"
        BEFORE DELETE
        ON shop_comments_files
        FOR EACH ROW
        EXECUTE PROCEDURE "bD shop_comments_files"();
    UPDATE shop SET comments_cnt = 0;') 
) {
    $DB->rollback();
	die( "\nCOULD NOT TRUNCATE shop_comments\n" );
}

$mRid = $DB->query( 'SELECT m.*, s.id AS shop_id FROM blogs_msgs m 
    INNER JOIN blogs_themes t ON m.thread_id = t.thread_id 
    INNER JOIN shop s ON s.id = t.id_gr 
    WHERE t.base = 4 AND m.reply_to IS NOT NULL ORDER BY s.id, m.reply_to, m.post_time' );

$sCurrShopId = '?273';  // текущая работа
$sCurrBlogId = '?273';  // текущая ветка блога
$aMap        = array(); // таблица соответствий старых и новых комментариев

if ( $mRid ) {
	while ( $aRow = pg_fetch_assoc($mRid) ) {
	    if ( $sCurrShopId != $aRow['shop_id'] ) {
	    	$sCurrShopId = $aRow['shop_id'];
	    	$sCurrBlogId = $aRow['reply_to'];
	    	$aMap        = array();
	    }
	    
	    if ( $aRow['reply_to'] == $sCurrBlogId ) {
	    	$aRow['reply_to'] = null; // те что указывали на корневой блог
	    }
	    else {
	        foreach ( $aMap as $sOld => $sNew ) {
	        	if ( $aRow['reply_to'] == $sOld ) {
	        		$aRow['reply_to'] = $sNew;
	        	}
	        }
	    }
	    
	    $aFiles = $DB->rows( 'SELECT a.*, f.id AS file_id FROM blogs_msgs_attach a 
	       INNER JOIN file f ON f.fname = a."name" 
	       WHERE a.msg_id = ? ORDER BY a.id', $aRow['id'] );
	    
	    if ( $DB->error ) {
            $DB->rollback();
            die("\nCOULD NOT SELECT blogs_msgs_attach\n");
	    }
	    
	    $aData = array(
            'fromuser_id' => $aRow['fromuser_id'],
            'reply_to'    => $aRow['reply_to'],
            'from_ip'     => $aRow['from_ip'],
            'post_time'   => $aRow['post_time'],
            'shop_id'     => $aRow['shop_id'],
            'msgtext'     => $aRow['msgtext'],
            'title'       => $aRow['title'],
            'modified'    => $aRow['modified'],
            'deleted'     => $aRow['deleted'],
            'deluser_id'  => $aRow['deluser_id'],
            'modified_id' => $aRow['modified_id'],
            'yt_link'     => $aRow['yt_link']
	    );
	    
	    $sNew = $DB->insert( 'shop_comments', $aData, 'id' );
	    
	    if ( $DB->error ) {
            $DB->rollback();
            die("\nCOULD NOT INSERT shop_comments\n");
	    }
	    
	    $aMap[ $aRow['id'] ] = $sNew;
	    
	    if ( $aFiles ) {
	    	foreach ($aFiles as $aOne) {
	    		$aData = array(
                    'comment_id' => $sNew,
                    'file_id'    => $aOne['file_id'],
                    'small'      => $aOne['small']
	    		);
	    		
	    		if ( !$DB->insert( 'shop_comments_files', $aData ) ) {
                    $DB->rollback();
                    die("\nCOULD NOT INSERT shop_comments_files\n");
        	    }
	    	}
	    }
	}
}
else {
    $DB->rollback();
	die("\nCOULD NOT SELECT blogs_msgs\n");
}

if ( !$DB->commit() ) {
    $DB->rollback();
	die("\nCOULD NOT COMMIT TRANSACTION\n");
}

echo "\nDONE\n"; 
// если не увидел DONE - значит скрипт не отработал до конца
// если вместо DONE увидел что то другое - значит скрипт отработал с глюками
