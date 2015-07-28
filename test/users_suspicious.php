<?php
/**
 * НА ОДИН РАЗ
 * Подозрительные пользователи.
 * 
 * Переход с users_suspicious_hide на users_suspicious
 */
set_time_limit(0);

require_once ( $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php' );
require_once ( $_SERVER['DOCUMENT_ROOT'].'/classes/users.php' );

$words_login = users::GetSuspiciousWordsLogin();
$words_name  = users::GetSuspiciousWordsName();
$sql_login   = '';
$sql_name    = '';

setlocale(LC_ALL, 'ru_RU.CP1251');

if ( $words_login ) {
    foreach($words_login as $word) {
        $sql_login .= "lower(login) LIKE '%".strtolower($word['word'])."%' OR ";
    }
    
    $sql_login = preg_replace("/OR $/","",$sql_login);
}

if ( $words_name ) {
    foreach($words_name as $word) {
        $sql_name .= "lower(uname) LIKE '%".strtolower($word['word'])."%' OR lower(usurname) LIKE '%".strtolower($word['word'])."%' OR ";
    }
    
    $sql_name = preg_replace("/OR $/","",$sql_name);
}

if ( $sql_login || $sql_name ) {
    if ( !$DB->start() ) {
    	die( "\nCOULD NOT START TRANSACTION\n" );
    }
    
    $mRid = $DB->query( 
        'SELECT u.uid, u.login, u.uname, u.usurname, u.is_banned, u.ban_where, (h.id IS NOT NULL) AS hide, b.to AS ban_to FROM users u 
        LEFT JOIN users_suspicious_hide h ON h.uid = u.uid 
        LEFT JOIN users_ban b ON b.uid = u.uid 
        WHERE ' . ($sql_login ? $sql_login : '') . ($sql_name ? ($sql_login ? ' OR ' : '').$sql_name : '')
    );
    
    if ( $mRid ) {
        while ( $aRow = pg_fetch_assoc($mRid) ) {
            $bIsBanned   = ( $aRow['is_banned'] && !$aRow['ban_where'] && !$aRow['ban_to'] );
            $bIsVerified = ( $bIsBanned || $aRow['hide'] == 't' );
            $bIsApproved = ( $aRow['hide'] == 't' && !$bIsBanned );
            $aDada       = array(
                'user_id'     => $aRow['uid'],
                'is_verified' => $bIsVerified,
                'is_approved' => $bIsApproved
            );
            
            $DB->insert( 'users_suspicious', $aDada );
            
            $aDada = array();
            
            if ( $DB->error ) {
                $DB->rollback();
                die("\nCOULD NOT INSERT users_suspicious\n");
    	    }
        }
    }
    else {
        $DB->rollback();
    	die("\nCOULD NOT SELECT users\n");
    }
    
    if ( !$DB->commit() ) {
        $DB->rollback();
    	die("\nCOULD NOT COMMIT TRANSACTION\n");
    }
}

setlocale(LC_ALL, "en_US.UTF-8");

echo "\nDONE\n"; 
// если не увидел DONE - значит скрипт не отработал до конца
// если вместо DONE увидел что то другое - значит скрипт отработал с глюками
