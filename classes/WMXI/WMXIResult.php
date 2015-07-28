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


class WMXIResult {


	private $req = array();
	private $res = array();
	private $scope = '';

	private $i18n = null;
	

	public function __construct($req, $res, $scope) {
		$this->req['str'] = $req;
		$this->res['str'] = $res;
		$this->scope = $scope;

		try {
			libxml_disable_entity_loader();
			$this->req['obj'] = new SimpleXMLElement($req);
		} catch (Exception $e) {
			$this->req['obj'] = null;
		}

		try {
			libxml_disable_entity_loader();
			$this->res['obj'] = new SimpleXMLElement($res);
		} catch (Exception $e) {
			$this->res['obj'] = null;
		}
		
		$this->i18n();
	}


	private function i18n() {
		libxml_disable_entity_loader();
		if (!defined('WMXI_LOCALE')) { define('WMXI_LOCALE', 'en_US'); };
		$fname = __DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . WMXI_LOCALE . DIRECTORY_SEPARATOR . 'WMXIErrors.xml'; 
		$this->i18n = file_exists($fname) ? new SimpleXMLElement(file_get_contents($fname)) : null;
	}
	

	public function toString() {
		return $this->res['str'];
	}


	public function toObject() {
		return $this->res['obj'];
	}


	private function toArrayMap($data) {
		if (is_object($data)) { $data = get_object_vars($data); }
		return is_array($data) ? array_map(array($this, __METHOD__), $data) : $data;
	}


	public function toArray() {
		return $this->toArrayMap($this->res['obj']);
	}

	
	public function GetRequest($plain = true) {
		return $this->req[$plain ? 'str' : 'obj'];
	}
	
	
	public function GetResponse($plain = true) {
		return $this->res[$plain ? 'str' : 'obj'];
	}
	
	
	public function ErrorCode() {
		if (!$this->res['obj']) { return false; }
		$obj = $this->res['obj'];
		
		# cURL error code
		if (isset($obj->errno )) { return intval($obj->errno ); }

		# WMXI error code
		if (isset($obj->retval)) { return intval($obj->retval); }
		if (isset($obj['retval'])) { return intval($obj['retval']); }
		
		# no suitable error code detected
		return false;
	}


	public function ErrorText($code = false) {
		if ($code === false) { $code = $this->ErrorCode(); }
		if ($code === false) { return false; }
		$obj = $this->res['obj'];

		$message = '';
		if (isset($obj->error  )) { $message = strval($obj->error ); }
		if (isset($obj->retdesc)) { $message = strval($obj->retdesc); }
		$message = !empty($message) ? " [$message]" : '';

		$result = array();
		foreach($this->i18n as $k => $v) {
			$scope = strval($v['scope']);
			$value = strval($v[0]);
			if (strval($v['code']) == '') { $result[''] = $value; }
			if (strval($v['code']) == strval($code)) { $result[$scope] = $value; }
		}

		if (isset($result[$this->scope])) { return $result[$this->scope].$message; } 
		if (isset($result['*'])) { return $result['*'].$message; }
		if (isset($result[''])) { return $result[''].$message; }
		return false;
	}


	private function SortASC($a, $b) {
		if (!is_array($a) || !is_array($b)) { return 0; }
		$delta = intval($a['@attributes']['id']) - intval($b['@attributes']['id']);
		if ($delta == 0) { return 0; }
		return $delta / abs($delta);
	}


	private function SortDSC($a, $b) {
		return - $this->SortASC($a, $b);
	}


	public function Sort($ascending = true) {
		$sort_func = array($this, __METHOD__ . ($ascending ? 'ASC' : 'DSC'));
		$res = $this->toArray();

		# X3
		$cnt = isset($res['operations']['@attributes']['cnt']) ? $res['operations']['@attributes']['cnt'] : 0;
		if ($cnt > 1) { usort($res['operations']['operation'], $sort_func); }

		# X4
		$cnt = isset($res['outinvoices']['@attributes']['cnt']) ? $res['outinvoices']['@attributes']['cnt'] : 0;
		if ($cnt > 1) { usort($res['outinvoices']['outinvoice'], $sort_func); }

		# X10
		$cnt = isset($res['ininvoices']['@attributes']['cnt']) ? $res['ininvoices']['@attributes']['cnt'] : 0;
		if ($cnt > 1) { usort($res['ininvoices']['ininvoice'], $sort_func); }

		# X15
		$cnt = isset($res['trustlist']['@attributes']['cnt']) ? $res['trustlist']['@attributes']['cnt'] : 0;
		if ($cnt > 1) { usort($res['trustlist']['trust'], $sort_func); }

		return $res;
	}


}


?>