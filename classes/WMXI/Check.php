<?php

	header('Content-Type: text/plain;');
	if (file_exists('MD4.php')) { include_once('MD4.php'); }

	# defining global constants
	define('PASSED', 'passed [+]');
	define('FAILED', 'failed [-]');

	libxml_disable_entity_loader();
	
	$md4a = class_exists('MD4');
	$md4b = extension_loaded('mhash');
	$md4c = extension_loaded('hash');
	$md4  = $md4a | $md4b | $md4c;

	$matha = extension_loaded('bcmath');
	$mathb = extension_loaded('gmp');
	$math  = $matha | $mathb;

	$mba = extension_loaded('mbstring');
	$mbb = extension_loaded('iconv');
	$mb  = $mba | $mbb;

	$curl = extension_loaded('curl');
	
	$xml  = extension_loaded('SimpleXML');

	$light   = $mb & $curl & $xml;
	$classic = $md4 & $math & $light;


	print("------------ MD4 ------------\n");
	print("   MD4 Class  : " . ($md4a ? PASSED : FAILED) . "   \n");
	print("   MHash      : " . ($md4b ? PASSED : FAILED) . "   \n");
	print("   Hash       : " . ($md4c ? PASSED : FAILED) . "   \n");
	print(" > Overall    : " . ($md4  ? PASSED : FAILED) . " < \n");
	print("\n");

	print("--------- Huge math ---------\n");
	print("   BCMath     : " . ($matha ? PASSED : FAILED) . "   \n");
	print("   GMP        : " . ($matha ? PASSED : FAILED) . "   \n");
	print(" > Overall    : " . ($math  ? PASSED : FAILED) . " < \n");
	print("\n");


	print("----- Multibyte strings -----\n");
	print("   MBString   : " . ($mba ? PASSED : FAILED) . "   \n");
	print("   iconv      : " . ($mbb ? PASSED : FAILED) . "   \n");
	print(" > Overall    : " . ($mb  ? PASSED : FAILED) . " < \n");
	print("\n");

	print("-----------  cURL -----------\n");
	print(" > cURL       : " . ($curl ? PASSED : FAILED) . " < \n");
	print("\n");

	print("--------- SimpleXML ---------\n");
	print(" > SimpleXML  : " . ($xml ? PASSED : FAILED) . " < \n");
	print("\n");

	print("-- WebMoney Keeper Classic --\n");
	print("   MD4        : " . ($md4     ? PASSED : FAILED) . "   \n");
	print("   Huge math  : " . ($math    ? PASSED : FAILED) . "   \n");
	print("   MB Strings : " . ($mb      ? PASSED : FAILED) . "   \n");
	print("   cURL       : " . ($curl    ? PASSED : FAILED) . "   \n");
	print("   SimpleXML  : " . ($xml     ? PASSED : FAILED) . "   \n");
	print(" > Overall    : " . ($classic ? PASSED : FAILED) . " < \n");
	print("\n");

	print("--- WebMoney Keeper Light ---\n");
	print("   MB Strings : " . ($mb      ? PASSED : FAILED) . "   \n");
	print("   cURL       : " . ($curl    ? PASSED : FAILED) . "   \n");
	print("   SimpleXML  : " . ($xml     ? PASSED : FAILED) . "   \n");
	print(" > Overall    : " . ($light   ? PASSED : FAILED) . " < \n");
	print("\n");

?>