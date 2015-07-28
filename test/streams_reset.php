<?php
/*
 * ”дал€ет ключи из мемкеша св€занные с потоками и чисит таблицы в базе.
 * Ёто приводит к тому, что структура потоков создаетс€ заново.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
require_once( '../classes/stdf.php' );
require_once( '../classes/user_content.php' );

$mem_buff = new memBuff();
$DB9       = new DB( 'plproxy' );

$mem_buff->touchTag( 'user_content' );
$DB9->query( 'SELECT mod_streams_release()' );

$user_content = new user_content();
$user_content->releaseDelayedStreams(); // чтобы отработал метод _initStreams