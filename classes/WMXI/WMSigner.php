<?php
################################################################################
#                                                                              #
# Webmoney Signer PHP edition by DKameleon (http://dkameleon.com)              #
#                                                                              #
# Updates and new versions: http://my-tools.net/wmxi/                          #
#                                                                              #
# Server requirements:                                                         #
# - BCMath or GMP                                                              #
# - MD4 or MHash or Hash                                                       #
#                                                                              #
################################################################################


# including classes
if (!defined('__DIR__')) { define('__DIR__', dirname(__FILE__)); }
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'WMXILogger.php');
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'MD4.php')) { include_once(__DIR__ . DIRECTORY_SEPARATOR . 'MD4.php'); }


# WMSigner class
class WMSigner {

	private $wmid = '';

	private $ekey = '';
	private $nkey = '';

	private $md4_use  = '';
	private $math_use = '';
	
	private $md4     = null;

	# debug switch
	private $debug = false;

	# constructor
	public function __construct($wmid, $key) {
		$this->debug = defined('WMXI_LOG');
	
		$this->math_use = $this->InitMath();
		$this->md4_use  = $this->InitMD4();
		WMXILogger::Append("\$this->math_use = ".$this->math_use.";");
		WMXILogger::Append("\$this->md4_use = ".$this->md4_use.";");
	
		$this->wmid = $wmid;

		# loading e-n-key
		if (isset($key['ekey']) && isset($key['nkey'])) {
			$this->ekey = $key['ekey'];
			$this->nkey = $key['nkey'];
			return true;
		}

		$pass = isset($key['pass']) ? $key['pass'] : '';
		if (isset($key['file'])) {
			if (!file_exists($key['file'])) { die('Key file not found: ' . $key['file']); }
			$data = file_get_contents($key['file']);
		}
		if (isset($key['data'])) { $data = $key['data']; }
		# seems can be another size (162 bytes)
#		if ($this->_strlen($data) != 164) { die('Key data has invalid size: ' . $this->_strlen($data)); }

		// extracting n & e from data
		$key_data = unpack('vreserved/vsignflag/a16crc/Vlen/a*buf', $data);
		$key_test = $this->SecureKeyByIDPW($wmid, $pass, $key_data);
		$sign_keys = $this->InitKeys($key_test);
		$this->ekey = $this->_hex2dec(bin2hex(strrev($sign_keys['ekey'])));
		$this->nkey = $this->_hex2dec(bin2hex(strrev($sign_keys['nkey'])));
	}


	# export keys for feature usage
	public function ExportKeys() {
		return array('ekey' => $this->ekey, 'nkey' => $this->nkey);
	}


	# Math init
	private function InitMath() {
		if (defined('WMXI_MATH')) {
			switch (WMXI_MATH) {
				case 'gmp': if (extension_loaded('gmp')) { return WMXI_MATH; } break;
				case 'bcmath5': if (function_exists('bcpowmod')) { return WMXI_MATH; } break;
				case 'bcmath4': if (extension_loaded('bcmath')) { return WMXI_MATH; } break;
			}
			die('Can not use WMXI_MATH = '. WMXI_MATH);
		} else {
			if (extension_loaded('gmp')) { return 'gmp'; } 
			if (function_exists('bcpowmod')) { return 'bcmath5'; } 
			if (extension_loaded('bcmath')) { return 'bcmath4'; } 
			die('Supported math implementations not found.');
		}
	}

	
	# MD4 init
	private function InitMD4() {
		if (defined('WMXI_MD4')) {
			switch (WMXI_MD4) {
				case 'mhash': if (function_exists('mhash')) { return WMXI_MD4; } break;
				case 'hash':  if (function_exists('hash')) { return WMXI_MD4; } break;
				case 'class': if (class_exists('MD4')) { $this->md4 = new MD4(true); return WMXI_MD4; } break;
			}
			die('Can not use WMXI_MD4 = '. WMXI_MD4);
		} else {
			if (function_exists('mhash')) { return 'mhash'; }
			if (function_exists('hash')) { return 'hash'; }
			if (class_exists('MD4')) {
				$this->md4 = new MD4(true);
				return 'class';
			}
			die('Supported MD4 implementations not found.');
		}
	}

	private function _strlen($data) {
		return mb_strlen($data, 'windows-1251');
	}
	
	# md4 wrapper
	private function _md4($data) {
		if ($this->md4_use == 'mhash') { return mhash(MHASH_MD4, $data); }
		if ($this->md4_use == 'hash' ) { return hash('md4', $data, true); }
		if ($this->md4_use == 'class') { return $this->md4->Calc($data, true); }
		die('MD4 implementations not found for _md4.');
	}


	# bcpowmod wrapper for old PHP
	private function _bcpowmod($m, $e, $n) {
		if ($this->math_use == 'gmp') { return gmp_strval(gmp_powm($m, $e, $n)); }
		if ($this->math_use == 'bcmath5') { return bcpowmod($m, $e, $n); }
		if ($this->math_use == 'bcmath4') {
			$r = '';
			while ($e != '0') {
				$t = bcmod($e, '4096');
				$r = substr('000000000000'.decbin(intval($t)), -12).$r;
				$e = bcdiv($e, '4096', 0);
				WMXILogger::Append("\$t = $t; \$e = $e;");
			}
			$r = preg_replace('!^0+!', '', $r);
			if ($r == '') $r = '0';
			$m = bcmod($m, $n);
			WMXILogger::Append("\$m = $m;");
			$erb = strrev($r);
			WMXILogger::Append("\$erb = $erb;");
			$result = '1';
			$a[0] = $m;
			for ($i = 1; $i < $this->_strlen($erb); $i++) {
				$a[$i] = bcmod(bcmul($a[$i-1], $a[$i-1], 0), $n);
			}
			WMXILogger::Append("\$a = $a;");
			for ($i = 0; $i < $this->_strlen($erb); $i++) {
				if ($erb[$i] == '1') {
					$result = bcmod(bcmul($result, $a[$i], 0), $n);
				}
			}
			WMXILogger::Append("\$result = $result;");
			return $result;
		}
		die('Math implementations not found for _bcpowmod.');
	}


	# convert decimal to hexadecimal
	private function _dec2hex($number) {
		if ($this->math_use == 'gmp') {
			$hexval = gmp_strval($number, 16);
			if ($this->_strlen($hexval) % 2) { $hexval = '0'.$hexval; }
			WMXILogger::Append("\$hexval = $hexval;");
			return $hexval;
		}
		if ($this->math_use == 'bcmath4' || $this->math_use == 'bcmath5') {
			$hexvalues = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
			$hexval = '';
			while($number != '0') {
				$hexval = $hexvalues[bcmod($number, '16')].$hexval;
				$number = bcdiv($number, '16', 0);
			}
			if ($this->_strlen($hexval) % 2) { $hexval = '0'.$hexval; }
			WMXILogger::Append("\$hexval = $hexval;");
			return $hexval;
		}
		die('Math implementations not found for _dec2hex.');
	}


	# convert hexadecimal to decimal
	private function _hex2dec($number) {
		if ($this->math_use == 'gmp') { return gmp_strval("0x$number", 10); }

		if ($this->math_use == 'bcmath4' || $this->math_use == 'bcmath5') {
			$decvalues = array(
				'0' => '0', '1' => '1', '2' => '2', '3' => '3',
				'4' => '4', '5' => '5', '6' => '6', '7' => '7',
				'8' => '8', '9' => '9', 'A' => '10', 'B' => '11',
				'C' => '12', 'D' => '13', 'E' => '14', 'F' => '15');
			$decval = '0';
			$number = strrev(strtoupper($number));
			WMXILogger::Append("\$number = $number;");
			for($i = 0; $i < $this->_strlen($number); $i++) {
				$decval = bcadd(bcmul(bcpow('16', $i, 0), $decvalues[$number[$i]], 0), $decval, 0);
			}
			WMXILogger::Append("\$decval = $decval;");
			return $decval;
		}
		die('Math implementations not found for _hex2dec.');
	}


	# swap hexadecimal string
	private function _shortunswap($hex_str) {
		$result = '';
		while($this->_strlen($hex_str) < 132) { $hex_str = '00'.$hex_str; }
		for($i = 0; $i < $this->_strlen($hex_str) / 4; $i++) {
			$result = substr($hex_str, $i * 4, 4).$result;
		}
		return $result;
	}


	# XOR two strings
	private function _XOR($str, $xor_str, $shift = 0) {
		$str_len = $this->_strlen($str);
		$xor_len = $this->_strlen($xor_str);
		$i = $shift;
		$k = 0;
		while ($i < $str_len) {
			$str[$i] = chr(ord($str[$i]) ^ ord($xor_str[$k]));
			$i++;
			$k++;
			if ($k >= $xor_len) { $k = 0; }
		}
		return $str;
	}


	# both of SecureKeyByIDPW
	private function SecureKeyByIDPW($wmid, $pass, $key_data) {
		$digest = $this->_md4($wmid . $pass);
		$result = $key_data;
		$result['buf'] = $this->_XOR($result['buf'], $digest, 6);
		return $result;
	}


	# initializing E and N
	private function InitKeys($key_data) {
		$crc_cont = '';
		$crc_cont .= pack('v', $key_data['reserved']);
		$crc_cont .= pack('v', 0);
		$crc_cont .= pack('V4', 0, 0, 0, 0);
		$crc_cont .= pack('V', $key_data['len']);
		$crc_cont .= $key_data['buf'];
		$digest = $this->_md4($crc_cont);
		if (strcmp($digest, $key_data['crc'])) { die('Checksum failed. KWM seems corrupted.'); }

		$keys = unpack('Vreserved/ve_len', $key_data['buf']);
		$keys = unpack('Vreserved/ve_len/a'.$keys['e_len'].'ekey/vn_len', $key_data['buf']);
		$keys = unpack('Vreserved/ve_len/a'.$keys['e_len'].'ekey/vn_len/a'.$keys['n_len'].'nkey', $key_data['buf']);
		return $keys;
	}


	# sign data
	public function Sign($data) {
		if ($this->ekey == '' || $this->nkey == '') { die('Key is not loaded.'); }
		WMXILogger::Append("\$ekey = ".$this->ekey.";");
		WMXILogger::Append("\$nkey = ".$this->nkey.";");

		$result = '';
		$plain = $this->_md4($data);
		for($i = 0; $i < 10; ++$i) { $plain .= pack('V', $this->debug ? 0 : mt_rand()); }
		$plain = pack('v', $this->_strlen($plain)).$plain;
		$m = $this->_hex2dec(bin2hex(strrev($plain)));
		WMXILogger::Append("\$m = $m;");
		$a = $this->_bcpowmod($m, $this->ekey, $this->nkey);
		WMXILogger::Append("\$a = $a;");
		$result = strtolower($this->_shortunswap($this->_dec2hex($a)));
		WMXILogger::Append("\$result = $result;");
		return $result;
	}


}


?>