<?php
################################################################################
#                                                                              #
# Webmoney XML Interfaces by DKameleon (http://dkameleon.com)                  #
#                                                                              #
# Updates and new versions: http://my-tools.net/wmxi/                          #
#                                                                              #
# Server requirements:                                                         #
#  - cURL                                                                      #
#  - MBString or iconv                                                         #
#                                                                              #
################################################################################


# including classes
if (!defined('__DIR__')) { define('__DIR__', dirname(__FILE__)); }
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'WMXILogger.php');
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'WMSigner.php'  )) { include_once(__DIR__ . DIRECTORY_SEPARATOR . 'WMSigner.php'  ); }
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'WMXIResult.php')) { include_once(__DIR__ . DIRECTORY_SEPARATOR . 'WMXIResult.php'); }


# WMXICore class
class WMXICore {


	private $cainfo    = '';
	private $encoding  = 'UTF-8';

	protected $classic = false;
	protected $wmid    = ''; # classic
	private   $signer  = null; # classic

	protected $light   = false;
	private   $cert    = array();  # light (key + cer + pass)

	private $reqn      = 0;
	private $lastreqn  = 0;


	# constructor
	public function __construct($cainfo = '', $encoding = 'UTF-8') {
		if (!empty($cainfo) && !file_exists($cainfo)) { die("Specified certificates dir $cainfo not found."); }
		$this->cainfo = $cainfo;
		$this->encoding = $encoding;
	}


	# initialize classic
	public function Classic($wmid, $key) {
		$this->classic = true;
		$this->light   = false;
		$this->wmid    = $wmid;
		if (!class_exists('WMSigner')) { die('WMSigner class not found.'); }
		$this->signer = new WMSigner($wmid, $key);
	}


	# initialize light
	public function Light($cert) {
		$this->classic = false;
		$this->light   = true;
		$this->cert    = $cert;
	}


	# generate reqn
	protected function _reqn() {
		list($usec, $sec) = explode(' ', substr(microtime(), 2));
		$this->lastreqn = ($this->reqn > 0) ? $this->reqn : substr($sec.$usec, 0, 15);
		return $this->lastreqn;
	}


	# use own request number
	public function SetReqn($value) {
		$this->reqn = $value;
	}


	# use own request number
	public function GetLastReqn($value) {
		return $this->lastreqn;
	}


	# sign function
	protected function _sign($text) {
		if (!$this->classic) { die('Classic initialization required to sign the string.'); }
		if (function_exists('mb_convert_encoding')) {
			$text = mb_convert_encoding($text, 'windows-1251', $this->encoding);
		} elseif (function_exists('iconv')) {
			$text = iconv($this->encoding, 'windows-1251', $text);
		}
		return $this->signer->Sign($text);
	}


	# request to server
	protected function _request($url, $xml, $scope = '') {
		WMXILogger::Append($xml);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

		if ($this->basic_auth) {
		    curl_setopt($ch, CURLOPT_USERPWD, $this->basic_auth);
		}

		if ($this->cainfo != '') {
			curl_setopt($ch, CURLOPT_CAINFO, $this->cainfo);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		if (!$this->classic){
			curl_setopt($ch, CURLOPT_SSLKEY, $this->cert['key']);
			curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->cert['pass']);
			curl_setopt($ch, CURLOPT_SSLCERT, $this->cert['cer']);
		};

		$result = curl_exec($ch);

		if (curl_errno($ch) != 0) {
			$result  = "<curl>\n";
			$result .= "<errno>".curl_errno($ch)."</errno>\n";
			$result .= "<error>".curl_error($ch)."</error>\n";
			$result .= "</curl>\n";
			$scope = 'cURL';
		}

		curl_close($ch);
		
		WMXILogger::Append($result);
		return class_exists('WMXIResult') ? new WMXIResult($xml, $result, $scope) : $result;
	}

}


?>