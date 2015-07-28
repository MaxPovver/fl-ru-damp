<?php

require_once 'HTTP/Request2.php';

$Config = array(
    'URI'              => 'http://x13-net.ru/tunnel.php',
    'Method'           => HTTP_Request2::METHOD_POST,
    'Login'            => '',
    'Password'         => '',
    'Head-In-Exclude'  => array('Host', 'Connection', 'Cache-Control'),
    'Head-Out-Exclude' => array('Server', 'X-Powered-By', 'Vary', 'Content-Encoding', 'Content-Length', 'Connection', 'Keep-Alive'),
    'Log-Mode'         => 2,
    'Log-Path'         => '/var/tmp/tunnel',
    'Log-Header-File'  => 'headers.log',
    'Request-Config'   => array(
        'adapter'           => 'HTTP_Request2_Adapter_Curl',
        'connect_timeout'   => 20,
        'protocol_version'  => '1.1',
        'ssl_verify_peer'   => false,
        'ssl_verify_host'   => false,
        'ssl_cafile'        => null,
        'ssl_capath'        => null,
        'ssl_passphrase'    => null
    )
);

$log = array();
register_shutdown_function('hlog');

$request = new HTTP_Request2($Config['URI'], $Config['Method']);
$request->setConfig($Config['Request-Config']);
if ( !empty($Config['Login']) && !empty($Config['Password']) ) {
    $request->setAuth($Config['Login'], $Config['Password']);
}
$headers = headers();
if ( $Config['Log-Mode'] ) {
    $log[] = '--------------------------';
    $log[] = '>>||>> ' . date('Y-m-d H:i:s');
    $log[] = '--------------------------';
    foreach ( $headers as $name => $value ) {
        $log[] = $name . ': ' . $value;
    }
}
$headers = exclude($headers, $Config['Head-In-Exclude']);
foreach ( $headers as $name => $value ) {
    $request->setHeader($name, $value);
}
if ( !empty($_POST) ) {
    foreach ( $_POST as $key => $value ) {
        $request->addPostParameter($key, $value);
    }
}
// @see http://xpoint.ru/forums/programming/PHP/faq.xhtml#740
if ( !empty($GLOBALS['HTTP_RAW_POST_DATA']) ) {
    $body = $GLOBALS['HTTP_RAW_POST_DATA'];
    if ( $Config['Log-Mode'] >= 2 && $Config['Log-Path'] ) {
        for ( $i=1; $i<=200; $i++ ) {
            $fname = date('Ymd-His') . '-I-' . $i . '.html';
            if ( !file_exists($fname) ) {
                $fp = fopen($Config['Log-Path'] . '/' . $fname, 'w');
                fwrite($fp, $body);
                fclose($fp);
                break;
            }
        }
    }
    $request->setBody($body);
}

$resp = $request->send();

$headers = $resp->getHeader();
if ( $Config['Log-Mode'] ) {
    $log[] = '--------------------------';
    $log[] = '<<||<< ' . date('Y-m-d H:i:s');
    $log[] = '--------------------------';
    foreach ( $headers as $name => $value ) {
        $log[] = $name . ': ' . $value;
    }
}
$headers = exclude($headers, $Config['Head-Out-Exclude']);
foreach ( $headers as $name => $value ) {
    header("{$name}: {$value}");
}
$res = $resp->getBody();
if ( $Config['Log-Mode'] >= 2 && $Config['Log-Path'] ) {
    for ( $i=1; $i<=200; $i++ ) {
        $fname = date('Ymd-His') . '-O-' . $i . '.html';
        if ( !file_exists($fname) ) {
            $fp = fopen($Config['Log-Path'] . '/' . $fname, 'w');
            fwrite($fp, $res);
            fclose($fp);
            break;
        }
    }
}
echo $res;



// http://php.net/manual/en/function.getallheaders.php (second comment)
function headers() { 
    foreach ( $_SERVER as $name => $value ) { 
        if ( substr($name, 0, 5) == 'HTTP_' ) {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
        } 
    } 
    return $headers; 
} 

function exclude($headers, $exclude) {
    if ( $exclude ) {
        $result = array();
        foreach ( $headers as $name => $value ) {
            $ok = true;
            foreach ( $exclude as $exc ) {
                if ( strtolower($name) == strtolower($exc) ) {
                    $ok = false;
                    break;
                }
            }
            if ( $ok ) {
                $result[$name] = $value;
            }
        }
        return $result;
    }
}

function hlog() {
    global $Config, $log;
    if ( $Config['Log-Mode'] && $Config['Log-Path'] && !empty($log) ) {
        $fp = fopen($Config['Log-Path'] . '/' . $Config['Log-Header-File'], 'a');
        flock($fp, LOCK_SH);
        fwrite($fp, implode("\n", $log) . "\n\n");
        flock($fp, LOCK_SH);
        fclose($fp);
    }
}



/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/alphaclick.php';

new AlphaClick(true);


//if ( $alpha->InvoiceCreate(7, '2694835', 1) ) {
//    echo "OK";
//} else {
//    echo $alpha->error;
//}
*/