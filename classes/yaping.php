<?php
/**
 * ��������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ������ � ����a�
 * @deprecated � ������� �� ����� �� ������������
 */
class yaping 
{
	
	/**
	 * ������� ������
	 *
	 * @param integer $gr ������
	 * @return string
	 */
	function doping($gr) {
	
		if (!defined('RELEASE')) return '0';
		
		$req = '<?xml version="1.0"?>
	<methodCall>
		<methodName>weblogUpdates.ping</methodName>
		<params>
			<param>
				<value>Free-lance.ru</value>
			</param>
			<param>
				<value>'.$GLOBALS['host'].'/rss/blogs.php'.(($gr)?'?gr='.$gr:'').'</value>
			</param>
		</params>
	</methodCall>';
	
		$res = $this->do_post_request('http://ping.blogs.yandex.ru/RPC2', $req, "Content-Type:text/xml");

		return $res;
		
//		���� �������� ��������� ������� �������
	}
	
	/**
	 * ���������� ����, �������� ����������� ������
	 *
	 * @param string $url  				�����
	 * @param string $data 				���� ��������
	 * @param string $optional_headers  ����� � ��������
	 * @return string
	 */
	function do_post_request($url, $data, $optional_headers = null)
  {
     $params = array('http' => array(
                  'method' => 'POST',
                  'content' => $data
               ));
     if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
     }
     $ctx = stream_context_create($params);
     $fp = fopen($url, 'rb', false, $ctx);
     if (!$fp) {
        throw new Exception("Problem with $url, $php_errormsg");
     }
     $response = stream_get_contents($fp);
     if ($response === false) {
        throw new Exception("Problem reading data from $url, $php_errormsg");
     }
     return $response;
  }
	
	
}

?>