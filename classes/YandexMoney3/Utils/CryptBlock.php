<?php

namespace YandexMoney3\Utils;


require_once(__DIR__ . '/../Exception/Exception.php');


use YandexMoney3\Exception;


class CryptBlock 
{
    const OPENSSL_ENCRYPT_CMD = 'openssl smime -sign -signer "%s" -inkey "%s" -nochain -nocerts -outform PEM -nodetach -passin fd:3';
    const OPENSSL_DECRYPT_CMD = 'openssl smime -verify -inform PEM -nointern -certfile "%s" -CAfile "%s"';
    
    private $encrypt_cert_path;
    
    private $decrypt_cert_path = '';
    
    private $private_key_path;
    
    private $passphrase;
    
    
    
    
    public function attributes($attributes = null) 
    {
        if (is_null($attributes)) 
        {
            return get_object_vars($this);
        }

        foreach ($attributes as $key => $value) 
        {
            if (property_exists($this, $key)) 
            {
                $this->{$key} = $value;
            }
        }
    }
    
    
    public function __construct($options = array()) 
    {
        $this->attributes($options);
        putenv('RANDFILE='.$this->getRandFile());
    }
    
    
    public function setEncruptCertPath($path)
    {
        $this->encrypt_cert_path = $path;
    }
    
    public function setDecryptCertPath($path)
    {
        $this->decrypt_cert_path = $path;
    }
    
    public function setPrivateKeyPath($path)
    {
        $this->private_key_path = $path;
    }
    
    public function setPassphrase($pass)
    {
        $this->passphrase = $pass;
    }
    
    
    public function isReqDecrypt()
    {
        return !empty($this->decrypt_cert_path);
    }
    

    public function decrypt($string)
    {
        if(empty($string)) 
            throw new Exception\Exception("You must pass a not empty string for decrypt.");
        
        $decryptedString = '';
        
        $descriptorspec = array(
            0 => array("pipe", "r"), // stdin <- encrypted string
            1 => array("pipe", "w"), // stdout -> decrypted string
            2 => array("pipe", "a") // stderr -> error string
        ); 
        
        $pipes = array();        
        
        $cmd = sprintf(
                self::OPENSSL_DECRYPT_CMD, 
                $this->decrypt_cert_path, 
                $this->decrypt_cert_path);       
        
        $process = proc_open($cmd, $descriptorspec, $pipes);
        
        if(!is_resource($process))                                          
            throw new Exception\Exception("Error execute openssl fucntion.");        
        
        fwrite($pipes[0], $string);
        fflush($pipes[0]);           
        fclose($pipes[0]);
        
        $execResult = stream_get_contents($pipes[1]);
        $execErrors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);

        $return_value = proc_close($process);
        
        
        if(!empty($execResult)) 
        {
            $decryptedString = $execResult;
        }
        
        if(!empty($execErrors)) 
            throw new Exception\Exception("Openssl return errors: {$execErrors}\nreturned value: {$return_value}");

        return $decryptedString;
    }

    



    public function encrypt($string)
    {
        if(empty($string)) 
            throw new Exception\Exception("You must pass a not empty string for encrypt.");
        
        $encryptedString = '';
        
        $descriptorspec = array(
            0 => array("pipe", "r"), // stdin <- string
            1 => array("pipe", "w"), // stdout -> encrypted string
            2 => array("pipe", "w"), // stderr -> error string
            3 => array("pipe", "r")  // stdin <- passphrase
        ); 
        
        $pipes = array();
        
        $cmd = sprintf(
                self::OPENSSL_ENCRYPT_CMD, 
                $this->encrypt_cert_path, 
                $this->private_key_path);
        
        $process = proc_open($cmd, $descriptorspec, $pipes);
        
        if(!is_resource($process))                                          
            throw new Exception\Exception("Error execute openssl fucntion.");
                             
        fwrite($pipes[0], $string);
        fflush($pipes[0]);
        fclose($pipes[0]);
        
        fwrite($pipes[3], $this->passphrase);
        fflush($pipes[3]);
        fclose($pipes[3]);  
        
        $execResult = stream_get_contents($pipes[1]);
        $execErrors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);

        $return_value = proc_close($process);
        
        if(!empty($execResult)) 
        {
            $encryptedString = $execResult;
        }
        
        if(!empty($execErrors)) 
            throw new Exception\Exception("Openssl return errors: {$execErrors}\nreturned value: {$return_value}");

        return $encryptedString;
    }
    
    
    
    private function getRandFile()
    {
        //$path = (isset($this->private_key_path))?dirname($this->private_key_path):'/tmp';
        //$path = '/tmp';
        //return $path . '/.rnd';
        
        return '/dev/urandom';
    }
    
    
}