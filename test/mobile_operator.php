<?php
/**
 * Заполняем базу мобильныйх операторов.
 * 
 * http://my-number.ru/code_rus_mob/mobile_codes_russia.php
 */

set_time_limit(0);

require_once("../classes/config.php");
require_once("../classes/stdf.php");

if ( !$DB->start() ) {
	die( "\nCOULD NOT START TRANSACTION\n" );
}
/*
if ( 
    !$DB->query('truncate mobile_operator cascade;
    alter sequence mobile_operator_id_seq restart 1;
    alter sequence mobile_operator_codes_id_seq restart 1;') 
) {
	die( "\nCOULD NOT TRUNCATE\n" );
}
*/
$sPage = curl_get('http://my-number.ru/code_rus_mob/mobile_codes_russia.php');
//$sPage = file_get_contents('/web/data/free-lance.int/CURL');

if ( $sPage ) {
    setlocale(LC_ALL, 'ru_RU.CP1251');
    preg_match_all( '#texts">\s*<a\s*href="([^"]+)?">#is', $sPage, $aMatches );
    
    if ( $aMatches[1] ) {
        $aOperator = array();
        
    	foreach ( $aMatches[1] as $sOne ) {
    		$sPage = curl_get( 'http://my-number.ru/code_rus_mob/'.$sOne );
//    		$sPage = file_get_contents('/web/data/free-lance.int/CURL2');
            
    		if ( $sPage ) {
        		preg_match_all( '#<tr[^>]*?>\s*<td[^>]*?>([^<]+)</td>\s*<td[^>]*?>[^<]+</td>\s*<td[^>]*?>(\d+)</td>\s*<td.*?sortkey="\d\d\d(\d+)-\d\d\d(\d+)"#is', $sPage, $aMatches2 );
                
        		if ( $aMatches2[1] ) {
            		for ( $i=0; $i<count($aMatches2[1]); $i++ ) {
//echo $aMatches2[1][$i], ' ' , $aMatches2[2][$i], ' ' , $aMatches2[3][$i], ' ' , $aMatches2[4][$i], '<br>';
                        $sOpName = html_entity_decode( $aMatches2[1][$i] );
                        if ( !$sOpId = $aOperator[$sOpName] ) {
                            if ( !$sOpId =$DB->val('SELECT id FROM mobile_operator WHERE operator = ? AND country_id = 1', $sOpName) ) {
                                if ( $DB->error ) {
                                	$DB->rollback();
                                    die("\nCOULD NOT SELECT mobile_operator\n");
                                }
                                
                                $aData = array(
                                    'country_id' => 1,
                                    'operator'   => $sOpName
                                );
                                
                                $sOpId = $DB->insert( 'mobile_operator', $aData, 'id' );
                                
                                if ( !$sOpId ) {
                                	$DB->rollback();
                                    die("\nCOULD NOT INSERT mobile_operator\n");
                                }
                                
                                $aOperator[$sOpName] = $sOpId;
                            }
                            else {
                                $aOperator[$sOpName] = $sOpId;
                            }
                        }

                        $aData = array(
                            'operator_id' => $sOpId,
                            'code'        => $aMatches2[2][$i],
                            'start_num'   => $aMatches2[3][$i],
                            'end_num'     => $aMatches2[4][$i]
                        );
                        
                        if ( !$DB->insert('mobile_operator_codes', $aData) ) {
                        	$DB->rollback();
                            die("\nCOULD NOT INSERT mobile_operator_codes\n");
                        }
            		}
        		}
        		else {
                    $DB->rollback();
                	die("\nCOULD NOT PARSE CODES PAGE http://my-number.ru/code_rus_mob/$sOne\n");
                }
    		}
    		else {
                $DB->rollback();
            	die("\nCOULD NOT GRAB CODES PAGE http://my-number.ru/code_rus_mob/$sOne\n");
            }
    	}
    }
    else {
        $DB->rollback();
    	die("\nCOULD NOT PARSE MAIN PAGE\n");
    }
    
    setlocale(LC_ALL, "en_US.UTF-8");
}
else {
    $DB->rollback();
	die("\nCOULD NOT GRAB MAIN PAGE\n");
}

if ( !$DB->commit() ) {
    $DB->rollback();
	die("\nCOULD NOT COMMIT TRANSACTION\n");
}

function curl_get( $url ) {
    $defaults = array( 
        CURLOPT_URL => $url, 
        CURLOPT_HEADER => 0, 
        CURLOPT_RETURNTRANSFER => TRUE, 
        CURLOPT_TIMEOUT => 4 
    ); 
    
    $ch = curl_init(); 
    curl_setopt_array( $ch, $defaults ); 
    
    if ( !$result = curl_exec($ch) ) { 
        return false;
    } 
    
    curl_close($ch); 
    
    return $result; 
}

echo "\nDONE\n"; 
// если не увидел DONE - значит скрипт не отработал до конца
// если вместо DONE увидел что то другое - значит скрипт отработал с глюками
