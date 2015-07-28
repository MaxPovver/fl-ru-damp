<?php

namespace YandexMoney3\Utils;

require_once(__DIR__ . '/../Exception/Exception.php');
require_once(__DIR__ . '/../Exception/ApiException.php');
require_once(__DIR__ . '/../Exception/ApiConnectionException.php');
require_once(__DIR__ . '/../Exception/InsufficientScopeException.php');
require_once(__DIR__ . '/../Exception/InternalServerException.php');
require_once(__DIR__ . '/../Response/OriginalServerResponse.php');
require_once(__DIR__ . '/Array2XML.php');
require_once(__DIR__ . '/CryptBlock.php');


use YandexMoney3\Exception;
use YandexMoney3\Utils\Array2XML;
use YandexMoney3\Utils\CryptBlock;
use YandexMoney3\Response\OriginalServerResponse;



class ApiNetworkClient
{
    private $options = array();




    /**
     * @var string
     */
    private $logFile;

    /**
     * @var bool
     */
    //private $transmitRawResponse = false;

    /**
     * @var bool
     */
    //private $headerRequired = false;

    /**
     * @var bool
     */
    //private $sslVerificationRequired;

    /**
     * @var OriginalServerResponse
     */
    private $originalServerResponse;

    
    
    
    /**
     * @return bool
     */
    /*
    private function isSSLVerificationRequired()
    {
        return $this->sslVerificationRequired;
    }
    */
    /**
     * @param boolean $headerRequired
     */
    /*
    public function toggleHeadersRequired($headerRequired = false)
    {
        $this->headerRequired = $headerRequired;
    }
    */
    
    /**
     * @return boolean
     */
    /*
    public function areHeadersRequired()
    {
        return $this->headerRequired;
    }
    */
    
    /**
     * @param boolean $transmitRawResponse
     */
    /*
    public function toggleTransmitRawResponse($transmitRawResponse = false)
    {
        $this->transmitRawResponse = $transmitRawResponse;
    }
    */
    
    /**
     * @return boolean
     */
    /*
    public function isTransmitRawResponseEnable()
    {
        return $this->transmitRawResponse;
    }
    */
    
    
    /**
     * @param string $logFile
     * @param bool $sslVerification
     */
    public function __construct($options = array(), $logFile = null, $sslVerification = true)
    {
        $this->options = $options;
        
        //@todo: вынести эти настройки в options
        $this->logFile = $logFile;
        $this->sslVerificationRequired = $sslVerification;
    }

    /**
     * @param string $uri
     * @param string $params
     * @return \YandexMoney3\Response\OriginalServerResponse
     */
    public function request($uri, $params)
    {
        $this->makePostCurlRequest($uri, $params);
        $this->checkOriginalServerResponse();
        
        return $this->originalServerResponse;
    }


    private function getCryptOption()
    {
        return isset($this->options['crypt']) && 
               !empty($this->options['crypt']) ?$this->options['crypt']:null;
    }

    
    /**
     * @param string $uri
     * @param string $params
     * @return OriginalServerResponse
     */
    private function makePostCurlRequest($uri, $params)
    {
        $cmd = key($params);
        $params = current($params);

        $converter = new Array2XML();
        $converter->setConvertFromEncoding('windows-1251');
        $converter->setTopNodeName($cmd);
        $converter->importArray($params);
        $data = $converter->saveXml();  
        
        $crypt = $this->getCryptOption();
        
        if ($crypt) {
            $cryptBlock = new CryptBlock($crypt);
            $encryptData = $cryptBlock->encrypt($data);
        } else {
            $encryptData = $data;
        }
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
        $log = new \log('reserves_payout/'.SERVER.'-%d%m%Y.log');
        $log->writeln(PHP_EOL . PHP_EOL);
        $log->writeln('[' . date('d.m.Y H:i:s') . ']');
        $log->writeln($data);
        
        $headers = $this->prepareRequestHeaders();

        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($crypt) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSLCERT, $crypt['encrypt_cert_path']);
            curl_setopt($curl, CURLOPT_SSLKEY, $crypt['private_key_path']);
            curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $crypt['passphrase']);
        }
            
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 80);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $encryptData);

        //$this->writeMessageToLogFile($this->prepareLogMessage($uri, $params));

        $responseRaw = curl_exec($curl);

        $errorCode = curl_errno($curl);
        $errorMessage = curl_error($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseHeaderSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeadersRaw = trim(substr($responseRaw, 0, $responseHeaderSize));
        $responseBodyRaw = trim(substr($responseRaw, $responseHeaderSize));

        curl_close($curl);

        if ($responseCode == 200) {
            if ($crypt && $cryptBlock->isReqDecrypt()) {
                $responseBodyRaw = $cryptBlock->decrypt($responseBodyRaw);
            }
            
            $log->writeln($responseBodyRaw);
        }
        
        
        /*
        $this->writeMessageToLogFile(
            $this->prepareLogMessageExtended($uri, $params, $responseCode, $errorCode, $errorMessage)
        );
        */
        
        $this->originalServerResponse = new OriginalServerResponse();
        $this->originalServerResponse->setCode($responseCode);
        $this->originalServerResponse->setHeadersRaw($responseHeadersRaw);
        $this->originalServerResponse->setBodyRaw($responseBodyRaw);
        $this->originalServerResponse->setErrorCode($errorCode);
        $this->originalServerResponse->setErrorMessage($errorMessage);
    }

    
    private function checkOriginalServerResponse()
    {
        $this->checkForCurlErrors();
        $this->checkForApiErrors();
    }

    /**
     * @param int $errorCode
     * @param string $errorMessage
     *
     * @throws \YandexMoney\Exception\ApiConnectionException
     */
    private function handleCurlError($errorCode, $errorMessage)
    {
        switch ($errorCode) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connect to Yandex.Money. Please check your internet connection and try again.";
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
                $msg = "Could not verify Yandex.Money's SSL certificate. Please make sure that your network is not intercepting certificates.";
                break;
            default:
                $msg = "Unexpected error communicating with Yandex.Money.";
        }

        $msg .= "\n\n(Network error: $errorMessage)";

        throw new Exception\ApiConnectionException($msg, $errorCode);
    }

    /**
     * @param string $responseCode
     * @param string $responseBody
     * @throws \YandexMoney3\Exception\InsufficientScopeException
     * @throws \YandexMoney3\Exception\InternalServerErrorException
     * @throws \YandexMoney3\Exception\ApiException
     * @internal param string $response
     *
     */
    private function handleApiError($responseCode, $responseBody)
    {
        switch ($responseCode) 
        {
            case 400:
                //Запрос не принят к обработке. Тело запроса испорчено, сервер не смог прочитать или разобрать запрос.
                //Возможные причины: запрос невозможно разобрать; неверный MIME-тип (Content-Type).
                throw new Exception\ApiException('Invalid request error', $responseCode, $responseBody);

            case 403:
                //Сертификат Контрагента не зарегистрирован в Системе, либо в настоящий момент шлюз отключен.
                throw new Exception\InsufficientScopeException('The certificate does not have permissions for the requested operation.',
                    $responseCode, $responseBody);
            
            case 500:
                //Технические проблемы Системы. Обратитесь в службу поддержки.
                throw new Exception\InternalServerErrorException('It is a technical error occurs, the server responds with the HTTP code
                    500 Internal Server Error. The application should repeat the request with the same parameters later.',
                    $responseCode, $responseBody);
                
            case 501:
                //Запрос отправлен методом, отличным от POST.
                throw new Exception\ApiException('Not Implemented', $responseCode, $responseBody);

            default:
                throw new Exception\ApiException('Unknown API response error. You should inform your software developer.',
                    $responseCode, $responseBody);
        }
    }

    /**
     * @param string $message
     *
     * @throws \YandexMoney\Exception\Exception
     */
    private function writeMessageToLogFile($message)
    {
        $f = $this->logFile;
        if ($f !== null) {
            if (file_exists($f)) {
                if (!is_file($f)) {
                    throw new Exception\Exception("log file $f is not a file");
                }
                if (!is_writable($f)) {
                    throw new Exception\Exception("log file $f is not writable");
                }
            }

            if (!$handle = fopen($f, 'a+')) {
                throw new Exception\Exception("couldn't open log file $f for appending");
            }

            $time = '[' . date("Y-m-d H:i:s") . '] ';
            if (fwrite($handle, $time . $message . "\r\n") === false) {
                throw new Exception\Exception("couldn't fwrite message log to $f");
            }

            fclose($handle);
        }
    }

    /**
     * @param string $uri
     * @param string $params
     *
     * @return string
     */
    /*
    private function prepareLogMessage($uri, $params)
    {
        if ($this->logFile === null) {
            return '';
        }

        $m = "request: " . $uri . '; ';

        $token = $this->accessToken;
        if (isset($token)) {
            $m = $m . 'token = *' . substr($token, -4) . '; ';
        }

        parse_str($params);
        if (isset($to)) {
            $m = $m . 'param to = *' . substr($to, -4) . '; ';
        }

        if (isset($pattern_id)) {
            $m = $m . 'param pattern_id = ' . $pattern_id . '; ';
        }

        if (isset($request_id)) {
            $m = $m . 'param request_id = *' . substr($request_id, -4) . '; ';
        }

        return $m;
    }
    */
    
    
    /**
     * @param string $uri
     * @param string $params
     * @param int $code
     * @param int $errorCode
     * @param string $errorMessage
     *
     * @return string
     */
    
    private function prepareLogMessageExtended($uri, $params, $code, $errorCode, $errorMessage)
    {
        if ($this->logFile === null) {
            return '';
        }

        $m = $this->prepareLogMessage($uri, $params);
        $m = str_replace('request: ', 'response: ', $m);
        $m = $m . "http_code = $code; curl_errno = $errorCode; curl_error = $errorMessage";

        return $m;
    }

    
    
    /**
     * @internal param $headers
     * @return array
     */
    private function prepareRequestHeaders()
    {
        $headers = array();
        $headers[] = 'Content-Type: application/pkcs7-mime';
        return $headers;
    }

    
    private function checkForCurlErrors()
    {
        if ($this->originalServerResponse->getErrorCode() != 0) {
            $this->handleCurlError(
                $this->originalServerResponse->getErrorCode(),
                $this->originalServerResponse->getErrorMessage()
            );
        }
    }

    private function checkForApiErrors()
    {
        if ($this->originalServerResponse->getCode() < 200 || $this->originalServerResponse->getCode() >= 308) {
            $this->handleApiError(
                $this->originalServerResponse->getCode(),
                $this->originalServerResponse->getBodyRaw()
            );
        }
    }
}