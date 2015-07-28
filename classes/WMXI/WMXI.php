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
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'WMXICore.php');


# WMXI class
class WMXI extends WMXICore {


	# interface X1
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X1
	public function X1($orderid, $customerwmid, $storepurse, $amount, $desc, $address, $period, $expiration) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($orderid.$customerwmid.$storepurse.$amount.$desc.$address.$period.$expiration.$reqn);
		}
		$group = 'invoice';
		$req->$group->orderid = $orderid;
		$req->$group->customerwmid = $customerwmid;
		$req->$group->storepurse = $storepurse;
		$req->$group->amount = $amount;
		$req->$group->desc = $desc;
		$req->$group->address = $address;
		$req->$group->period = $period;
		$req->$group->expiration = $expiration;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLInvoice.asp' : 'https://w3s.wmtransfer.com/asp/XMLInvoiceCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X2
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X2
	public function X2($tranid, $pursesrc, $pursedest, $amount, $period, $pcode, $desc, $wminvid, $onlyauth) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($reqn.$tranid.$pursesrc.$pursedest.$amount.$period.$pcode.$desc.$wminvid);
		}
		$group = 'trans';
		$req->$group->tranid = $tranid;
		$req->$group->pursesrc = $pursesrc;
		$req->$group->pursedest = $pursedest;
		$req->$group->amount = $amount;
		$req->$group->period = $period;
		$req->$group->pcode = $pcode;
		$req->$group->desc = $desc;
		$req->$group->wminvid = $wminvid;
		$req->$group->onlyauth = $onlyauth;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLTrans.asp' : 'https://w3s.wmtransfer.com/asp/XMLTransCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X3
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X3
	public function X3($purse, $wmtranid, $tranid, $wminvid, $orderid, $datestart, $datefinish) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($purse.$reqn);
		}
		$group = 'getoperations';
		$req->$group->purse = $purse;
		$req->$group->wmtranid = $wmtranid;
		$req->$group->tranid = $tranid;
		$req->$group->wminvid = $wminvid;
		$req->$group->orderid = $orderid;
		$req->$group->datestart = $datestart;
		$req->$group->datefinish = $datefinish;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLOperations.asp' : 'https://w3s.wmtransfer.com/asp/XMLOperationsCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X4
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X4
	public function X4($purse, $wminvid, $orderid, $datestart, $datefinish) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($purse.$reqn);
		}
		$group = 'getoutinvoices';
		$req->$group->purse = $purse;
		$req->$group->wminvid = $wminvid;
		$req->$group->orderid = $orderid;
		$req->$group->datestart = $datestart;
		$req->$group->datefinish = $datefinish;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLOutInvoices.asp' : 'https://w3s.webmoney.ru/asp/XMLOutInvoicesCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X5
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X5
	public function X5($wmtranid, $pcode) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($wmtranid.$pcode.$reqn);
		}
		$group = 'finishprotect';
		$req->$group->wmtranid = $wmtranid;
		$req->$group->pcode = $pcode;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLFinishProtect.asp' : 'https://w3s.wmtransfer.com/asp/XMLFinishProtectCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X6
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X6
	public function X6($receiverwmid, $msgsubj, $msgtext) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($receiverwmid.$reqn.$msgtext.$msgsubj);
		}
		$group = 'message';
		$req->$group->receiverwmid = $receiverwmid;
		$req->$group->msgsubj = $msgsubj;
		$req->$group->msgtext = $msgtext;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLSendMsg.asp' : 'https://w3s.wmtransfer.com/asp/XMLSendMsgCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X7
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X7
	public function X7($wmid, $plan, $sign) {
#		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
#		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($this->wmid.$wmid.$plan.$sign);
		}
		$group = 'testsign';
		$req->$group->wmid = $wmid;
		$req->$group->plan = $plan;
		$req->$group->sign = $sign;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLClassicAuth.asp' : 'https://w3s.wmtransfer.com/asp/XMLClassicAuthCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X8
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X8
	public function X8($wmid, $purse) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($wmid.$purse);
		}
		$group = 'testwmpurse';
		$req->$group->wmid = $wmid;
		$req->$group->purse = $purse;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLFindWMPurseNew.asp' : 'https://w3s.wmtransfer.com/asp/XMLFindWMPurseCertNew.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X9
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X9
	public function X9($wmid) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($wmid.$reqn);
		}
		$group = 'getpurses';
		$req->$group->wmid = $wmid;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLPurses.asp' : 'https://w3s.wmtransfer.com/asp/XMLPursesCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X10
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X10
	public function X10($wmid, $wminvid, $datestart, $datefinish) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($wmid.$wminvid.$datestart.$datefinish.$reqn);
		}
		$group = 'getininvoices';
		$req->$group->wmid = $wmid;
		$req->$group->wminvid = $wminvid;
		$req->$group->datestart = $datestart;
		$req->$group->datefinish = $datefinish;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLInInvoices.asp' : 'https://w3s.webmoney.ru/asp/XMLInInvoicesCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X11
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X11
	public function X11($passportwmid, $dict, $info, $mode) {
#		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<request/>');
#		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($this->wmid.$passportwmid);
		} else {
			$req->wmid = '';
			$req->sign = '';
		}
		$req->passportwmid = $passportwmid;
		$group = 'params';
		$req->$group->dict = $dict;
		$req->$group->info = $info;
		$req->$group->mode = $mode;
		$url = 'https://passport.webmoney.ru/asp/XMLGetWMPassport.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X13
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X13
	public function X13($wmtranid) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($wmtranid.$reqn);
		}
		$group = 'rejectprotect';
		$req->$group->wmtranid = $wmtranid;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLRejectProtect.asp' : 'https://w3s.wmtransfer.com/asp/XMLRejectProtectCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X14
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X14
	public function X14($inwmtranid, $amount) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($reqn.$inwmtranid.$amount);
		}
		$group = 'trans';
		$req->$group->inwmtranid = $inwmtranid;
		$req->$group->amount = $amount;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLTransMoneyback.asp' : 'https://w3s.wmtransfer.com/asp/XMLTransMoneybackCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X15
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X15
	public function X15a($wmid) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($wmid.$reqn);
		}
		$group = 'gettrustlist';
		$req->$group->wmid = $wmid;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLTrustList.asp' : 'https://w3s.webmoney.ru/asp/XMLTrustListCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X15
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X15
	public function X15b($wmid) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($wmid.$reqn);
		}
		$group = 'gettrustlist';
		$req->$group->wmid = $wmid;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLTrustList2.asp' : 'https://w3s.webmoney.ru/asp/XMLTrustList2Cert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X15
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X15
	public function X15c($masterwmid, $slavewmid, $purse, $ainv, $atrans, $apurse, $atranshist, $limit, $daylimit, $weeklimit, $monthlimit) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($this->wmid.$purse.$masterwmid.$reqn);
		}
		$group = 'trust';
		$req->$group->addAttribute('inv', $ainv);
		$req->$group->addAttribute('trans', $atrans);
		$req->$group->addAttribute('purse', $apurse);
		$req->$group->addAttribute('transhist', $atranshist);
		$req->$group->masterwmid = $masterwmid;
		$req->$group->slavewmid = $slavewmid;
		$req->$group->purse = $purse;
		$req->$group->limit = $limit;
		$req->$group->daylimit = $daylimit;
		$req->$group->weeklimit = $weeklimit;
		$req->$group->monthlimit = $monthlimit;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLTrustSave2.asp' : 'https://w3s.webmoney.ru/asp/XMLTrustSave2Cert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X16
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X16
	public function X16($wmid, $pursetype, $desc) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<w3s.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($wmid.$pursetype.$reqn);
		}
		$group = 'createpurse';
		$req->$group->wmid = $wmid;
		$req->$group->pursetype = $pursetype;
		$req->$group->desc = $desc;
		$url = $this->classic ? 'https://w3s.webmoney.ru/asp/XMLCreatePurse.asp' : 'https://w3s.wmtransfer.com/asp/XMLCreatePurseCert.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X17
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X17
	public function X17a($name, $ctype, $text, $wmidlist) {
#		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<contract.request/>');
#		$req->reqn = $reqn;

		if ($this->classic) {
			$req->sign_wmid = $this->wmid;
			$req->sign = $this->_sign($this->wmid.mb_strlen($name, 'UTF-8').$ctype);
		}
		$req->name = $name;
		$req->ctype = $ctype;
		$req->text = $text;
		if (count($wmidlist) > 0) {
			$req->addChild('accesslist');
			foreach($wmidlist as $k => $v) {
				$req->accesslist->addChild('wmid', $v);
			}
		}
		$url = 'https://arbitrage.webmoney.ru/xml/X17_CreateContract.aspx';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X17
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X17
	public function X17b($contractid) {
#		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<contract.request/>');
#		$req->reqn = $reqn;

		if ($this->classic) {
			$req->wmid = $this->wmid;
			$req->sign = $this->_sign($contractid.'acceptdate');
		}
		$req->contractid = $contractid;
		$req->mode = 'acceptdate';
		$url = 'https://arbitrage.webmoney.ru/xml/X17_GetContractInfo.aspx';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X18
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X18
	public function X18($wmid, $lmi_payee_purse, $lmi_payment_no, $secret_key) {
#		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<merchant.request/>');
#		$req->reqn = $reqn;

		$req->wmid = $wmid;
		$req->lmi_payee_purse = $lmi_payee_purse;
		$req->lmi_payment_no = $lmi_payment_no;

		if ($this->classic) {
			$req->sign = $this->_sign($wmid.$lmi_payee_purse.$lmi_payment_no);
		} elseif ($secret_key != '') {
			$req->md5 = strtoupper(md5($wmid.$lmi_payee_purse.$lmi_payment_no.$secret_key));
		}
#		$req->secret_key = $secret_key;
		$url = 'https://merchant.webmoney.ru/conf/xml/XMLTransGet.asp';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


	# interface X19
	# http://wiki.webmoney.ru/wiki/show/Interfeys_X19
	public function X19($type, $direction, $pursetype, $amount, $wmid, $pnomer, $fname, $iname, $bank_name, $bank_account, $card_number, $emoney_name, $emoney_id) {
		$reqn = $this->_reqn();
		$req = new SimpleXMLElement('<passport.request/>');
		$req->reqn = $reqn;

		if ($this->classic) {
			$req->signerwmid = $this->wmid;
			$req->sign = $this->_sign($reqn.$type.$wmid);
		}
		$group = 'operation';
		$req->$group->type = $type;
		$req->$group->direction = $direction;
		$req->$group->pursetype = $pursetype;
		$req->$group->amount = $amount;
		$group = 'userinfo';
		$req->$group->wmid = $wmid;
		$req->$group->pnomer = $pnomer;
		$req->$group->fname = $fname;
		$req->$group->iname = $iname;
		$req->$group->bank_name = $bank_name;
		$req->$group->bank_account = $bank_account;
		$req->$group->card_number = $card_number;
		$req->$group->emoney_name = $emoney_name;
		$req->$group->emoney_id = $emoney_id;
		$url = $this->classic ? 'https://passport.webmoney.ru/XML/XMLCheckUser.aspx' : 'https://passport.webmoney.ru/XML/XMLCheckUserCert.aspx';

		return $this->_request($url, $req->asXML(), __FUNCTION__);
	}


}


?>