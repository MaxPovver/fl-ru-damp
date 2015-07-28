<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if (is_release()) {
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pskb.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");

ini_set('default_charset', 'utf8');

class pskb_test extends pskb {
    
    private $_emp = array();
    
    private $_frl = array();
    
    private $_sum;
    
    private $_numDog;
    
    public function __construct() {
        if (defined('PSKB_TEST_MODE')) {
            $_host = !defined('IS_LOCAL') ? str_replace('http://', 'https://', $GLOBALS['host']) : $GLOBALS['host'];
            $this->_request_url = $_host . '/sbr/pskb_server.php?method=';
        }
        $this->_sum = 21400;
        $this->_numDog = mt_rand(mt_rand(0, 9999), mt_rand(10000, 999999));
        
        $this->_frl = new pskb_user(array(
            1 => array(
                'fio' => 'Иванов Иван Иванович',
                'phone' => '+79216130051',
                'el_yd' => '410011423430810',
                'el_wmr' => 'R487826911004',
                'inn' => '390613118663',
                'bank_rs' => '40817810708360006282',
                'bank_bik' => '044525593',
//                'bank_bik' => '333333333',
            ),
            2 => array(
                'org_name' => 'ООО Тест11',
                'phone' => '+79216130051',
                'kpp' => '771401001',
                'inn' => '7704138706',
                'bank_rs' => '40702810200700574016',
                'bank_bik' => '044525202'
            ),
            'form_type' => 1
        ), 0);
        
        $this->_emp = new pskb_user(array(
            1 => array(
                'fio' => 'Петров Петр Иванович',
                'phone' => '+79216103498',
                'el_yd' => '410011423430820',
                'el_wmr' => 'R487826911005',
                'inn' => '390613118664',
                'bank_rs' => '40817810655007287045',
                'bank_bik' => '044030653',
//                'bank_bik' => '777777777',
            ),
            2 => array(
                'org_name' => 'ООО Тест',
                'phone' => '+79216103498',
                'kpp' => '771001001',
                'inn' => '7710434132',
                'bank_rs' => '40702810900030002455',
                'bank_bik' => '044525187',
            ),
            'form_type' => 1
        ), 1);
        
        $this->_frl->setPs(onlinedengi::BANK_YL);
        $this->_emp->setPs(onlinedengi::BANK_YL);
        
//        var_export($this->_frl->getParams());
//        var_export($this->_emp->getParams());
//        die();
    }
    
    public function addLC() {
        $time = time();
        $timeCover = $time + 3600*24*3;
        $timeExec = $timeCover + 3600*24*5;
        $timeEnd = $timeExec + 3600*24*2;
        
        $dateExecLC = date('d.m.Y', $timeExec);
        $dateEndLC = date('d.m.Y', $timeEnd);
        $dateCoverLC = date('d.m.Y', $timeCover);
            
        if (!$this->_emp->ps || !$this->_frl->ps) {
            var_export($this->_emp->getParams());
            var_export($this->_frl->getParams());
//            var_dump($this->_emp->ps, $this->_frl->ps);
            die('problem');
            return;
        }
        
        $ret = $this->_addLC(
            $this->_sum, 
            $this->_numDog, 
            $dateExecLC, 
            $dateEndLC, 
            $dateCoverLC, 
            $this->_emp->tag, 
            $this->_emp->name, 
            $this->_emp->num, 
            $this->_emp->ps, 
            $this->_emp->acc, 
            $this->_emp->inn, 
            $this->_emp->kpp, 
            $this->_frl->tag, 
            $this->_frl->name, 
            $this->_frl->num, 
            $this->_frl->ps, 
            $this->_frl->acc, 
            $this->_frl->inn, 
            $this->_frl->kpp
        );
        
        var_export($ret);
    }
    
    public function checkLC($id) {
        var_export($this->_checkLC($id));
    }
    
    public function checks($ids) {
//        var_dump($ids); die();
        $ids = stripslashes($ids);
        var_export($this->_checks($ids));
    }
    
    public function changeDateLC($id, $dateExecLC, $dateEndLC, $dateCoverLC) {
        var_export($this->_changeDateLC($id, $dateExecLC, $dateEndLC, $dateCoverLC));
    }
    
    public function openLC($ID, $sumCust, $sumPerf, $dateAct, $idAct) {
        var_export($this->_openLC($ID, $sumCust, $sumPerf, $dateAct, $idAct));
    }
    
    public function subOpenLC($ID, $asp) {
        var_export($this->_subOpenLC($ID, $asp));
    }
    
    public function transLC($id) {
        var_export($this->_transLC($id));
    }
    
    public function reqCode($id) {
        var_export($this->_reqCode($id));
    }
    
    public function closeLC($id) {
        var_export($this->_closeLC($id));
    }
    
    public function superCheck($id, $urlRejoin) {
        var_export($this->_superCheck(array($id), $urlRejoin));
    }

    public function checkOrCreateWallet($num) {
        var_export($this->_checkOrCreateWallet($num));
    }
    
    protected function _request ($method, $params, $content_plain = false) {
        
        $ch = curl_init();
        if(defined('PSKB_BETA_MODE')) {
            curl_setopt($ch, CURLOPT_PORT, 8085);
            curl_setopt($ch, CURLOPT_URL, "http://localhost/apiLCPlace/" . $method);
        } else if (!defined('PSKB_TEST_MODE')) {
            curl_setopt($ch, CURLOPT_PORT, 8085);
            curl_setopt($ch, CURLOPT_URL, 'http://192.168.88.13/apiLCPlace/' . $method);
//            curl_setopt($ch, CURLOPT_URL, "http://localhost/apiLCPlace/" . $method);
        } else {
            if(defined('BASIC_AUTH')) {
                curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
            }
            curl_setopt($ch, CURLOPT_URL, $this->_request_url . $method);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($content_plain) {
            curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 
        }
        $res = curl_exec($ch);
        
        var_dump($res);
        var_dump(curl_getinfo($ch, CURLINFO_HEADER_OUT));
        
        return $res;
    }
    
}

$method = __paramInit('string', 'method');
    $test = new pskb_test();

if ($method) {
    echo "<pre>";
    unset($_GET['method']);
    var_export($_GET);
    ob_start();
    var_dump($method, $_GET);

    call_user_method_array($method, $test, $_GET);
    
    $out = ob_get_contents();
    $out = date('Y-m-d H:i:s') . " ------------------------------ \n" . $out . "\n\n\n";
    file_put_contents('/var/tmp/pskb_test.log', $out, FILE_APPEND);
    echo "</pre>";
} else {
?>
<style>
    iframe {
        width: 100%;
        height: 180px;
    }
</style>
<div>
    <h1>addLC</h1>
    <form target="addLC" method="get">
        <input type="hidden" name="method" value="addLC"/>
        <button>go</button>
    </form>
</div>
<iframe name="addLC" id="addLC" src="about:blank"></iframe>

<div>
    <h1>checkLC</h1>
    <form target="checkLC" method="get">
        <input type="hidden" name="method" value="checkLC"/>
        <label>id: <input type="text" name="id"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="checkLC" id="checkLC" src="about:blank"></iframe>

<div>
    <h1>checks</h1>
    <form target="checks" method="get">
        <input type="hidden" name="method" value="checks"/>
        <label>id: <input type="text" name="id"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="checks" id="checks" src="about:blank"></iframe>

<div>
    <h1>changeDateLC</h1>
    <form target="changeDateLC" method="get">
        <input type="hidden" name="method" value="changeDateLC"/>
        <label>id: <input type="text" name="id"/></label>
        <label>$dateExecLC: <input type="text" name="dateExecLC"/></label>
        <label>$dateEndLC: <input type="text" name="dateEndLC"/></label>
        <label>$dateCoverLC: <input type="text" name="dateCoverLC"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="changeDateLC" id="changeDateLC" src="about:blank"></iframe>

<div>
    <h1>openLC</h1>
    <form target="openLC" method="get">
        <input type="hidden" name="method" value="openLC"/>
        <label>$ID: <input type="text" name="id"/></label>
        <label>$sumCust: <input type="text" name="sumCust"/></label>
        <label>$sumPerf: <input type="text" name="sumPerf"/></label>
        <label>$dateAct: <input type="text" name="dateAct" value="<?= date('d.m.Y') ?>"/></label>
        <label>$idAct: <input type="text" name="idAct" value="<?= mt_rand(mt_rand(0, 999), mt_rand(1000, 99999)) ?>"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="openLC" id="openLC" src="about:blank"></iframe>

<div>
    <h1>subOpenLC</h1>
    <form target="subOpenLC" method="get">
        <input type="hidden" name="method" value="subOpenLC"/>
        <label>$ID: <input type="text" name="id"/></label>
        <label>$asp: <input type="text" name="asp"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="subOpenLC" id="subOpenLC" src="about:blank"></iframe>

<div>
    <h1>transLC</h1>
    <form target="transLC" method="get">
        <input type="hidden" name="method" value="transLC"/>
        <label>$id: <input type="text" name="id"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="transLC" id="transLC" src="about:blank"></iframe>

<div>
    <h1>reqCode</h1>
    <form target="reqCode" method="get">
        <input type="hidden" name="method" value="reqCode"/>
        <label>$id: <input type="text" name="id"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="reqCode" id="reqCode" src="about:blank"></iframe>

<div>
    <h1>closeLC</h1>
    <form target="closeLC" method="get">
        <input type="hidden" name="method" value="closeLC"/>
        <label>$id: <input type="text" name="id"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="closeLC" id="closeLC" src="about:blank"></iframe>


<div>
    <h1>superCheck</h1>
    <form target="superCheck" method="get">
        <input type="hidden" name="method" value="superCheck"/>
        <label>$id: <input type="text" name="id"/></label>
        <label>$urlRejoin: <input type="text" name="urlRejoin" value="/income/sbr_check.php?key=<?= pskb::KEY_CHECK_AUTH;?>"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="superCheck" id="superCheck" src="about:blank"></iframe>

<div>
    <h1>checkOrCreateWallet</h1>
    <form target="checkOrCreateWallet" method="get">
        <input type="hidden" name="method" value="checkOrCreateWallet"/>
        <label>$num: <input type="text" name="num"/></label>
        <button>go</button>
    </form>
</div>
<iframe name="checkOrCreateWallet" id="checkOrCreateWallet" src="about:blank"></iframe>

<? } ?>