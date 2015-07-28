<?php
################################################################################
#                                                                              #
# Webmoney XML Interfaces by DKameleon (http://dkameleon.com)                  #
#                                                                              #
# Updates and new versions: http://my-tools.net/wmxi/                          #
#                                                                              #
# Server requirements:                                                         #
#  - SimpleXML                                                                 #
#                                                                              #
################################################################################


# including classes
if (!defined('__DIR__')) { define('__DIR__', dirname(__FILE__)); }
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'WMXILogger.php');
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'WMXIResult.php')) { include_once(__DIR__ . DIRECTORY_SEPARATOR . 'WMXIResult.php'); }


# WMXILogin class
class WMXILogin {

	private $urlId      = '';
	private $siteHolder = '';
	private $LastAuth   = array();
	
	private $cainfo     = '';

	
	# constructor
	public function __construct($urlId, $siteHolder, $cainfo = '') {
		$this->urlId = $urlId;
		$this->siteHolder = $siteHolder;
		
		if (!empty($cainfo) && !file_exists($cainfo)) { die("Specified certificates dir $cainfo not found."); }
		$this->cainfo = $cainfo;
	}

	
	# request to server
	protected function _request($url, $xml, $scope = '') {
		WMXILogger::Append($xml);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

		if ($this->cainfo != '') {
			curl_setopt($ch, CURLOPT_CAINFO, $this->cainfo);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}

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
	

	private function CheckParams() {
		$params = array(
			'WmLogin_AuthType' => '#^KeeperClassic|KeeperLight|Enum|Fingerprint|Telepat|KeeperMini|EnumViaSms|KeeperMiniSocial|PasswordSms$#',
			'WmLogin_Created' => '#^\d{2}\.\d{2}\.\d{4} \d{2}\:\d{2}\:\d{2}$#',
			'WmLogin_Expires' => '#^\d{2}\.\d{2}\.\d{4} \d{2}\:\d{2}\:\d{2}$#',
			'WmLogin_LastAccess' => '#^\d{2}\.\d{2}\.\d{4} \d{2}\:\d{2}\:\d{2}$#',
			'WmLogin_Ticket' => '#^[0-9a-z\$\!\/]+$#i',
			'WmLogin_UrlID' => '#^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$#i',
			'WmLogin_UserAddress' => '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#',
			'WmLogin_WMID' => '#^\d{12}$#',
		);
		
		$result = array();
		foreach($params as $k => $v) {
			$val = isset($_POST[$k]) ? $_POST[$k] : '';
			if (preg_match($v, $val, $m)) { $result[$k] = $val; }
		}
		
		return $result;
	}
	

	public function Login() {
		header('Location: https://login.wmtransfer.com/GateKeeper.aspx?RID='.$this->urlId);
		die();
	}
	
	
	public function LastAuth() {
		return $this->LastAuth;
	}
	
	
	public function Expired() {
		echo strtotime($this->LastAuth['WmLogin_Expires'].' UTC') - time() < 0;
	}
	
	
	# interface authorize.xiface
	# https://login.wmtransfer.com/Help.aspx?AK=ws/xmliface
	public function Authorize() {
		$params = $this->CheckParams();
		if (count($params) != 8) { return false; }
		$this->LastAuth = $params;
		if ($this->Expired()) { return false; }
		
		$req = new SimpleXMLElement('<request/>');

		$req->siteHolder = $this->siteHolder;
		$req->user = $params['WmLogin_WMID'];
		$req->ticket = $params['WmLogin_Ticket'];
		$req->urlId = $this->urlId;
		$req->authType = $params['WmLogin_AuthType'];
		$req->userAddress = $params['WmLogin_UserAddress'];
		$url = 'https://login.wmtransfer.com/ws/authorize.xiface';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}
	
	# auth shortcut
	public function AuthorizeWMID() {
		$res = $this->Authorize();
		if ($res === false) { return false; }
		if ($res->ErrorCode() == 0) { return $this->LastAuth['WmLogin_WMID']; }
		return false;
	}


}


?>