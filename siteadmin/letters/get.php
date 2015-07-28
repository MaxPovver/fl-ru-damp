<?php
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/letters.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/fpdf/fpdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/fpdf/fpdf_tpl.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/fpdi/fpdi.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");


session_start();
$uid = get_uid();

if( !(hasPermissions('letters') && hasPermissions('adm')) ) {
  header ("Location: /404.php");
  exit;
}
$letter_num = 1;
$data = $_SESSION['admin_letters_data'];
$sid = uniqid();
$data_post = array();
$data_post_our = array();
$qdeliveries = letters::getDeliveries();
$deliveries = array();
foreach($qdeliveries as $v) {
	$deliveries[$v['id']] = $v['title'];
}

if($data) {
	foreach ($data as $key => $value) {
		$t = preg_split("/-/", $key);
		$user_id = $t[0];
		$docs = $value;
		if($docs) {
			foreach($docs as $doc) {
				$data_post[$user_id.'-'.$t[2]]['delivery'] = $doc['delivery'];
				if($doc['delivery']!=6) {
					$data_post[$user_id.'-'.$t[2]]['docs'][] = $doc;
				} else {
					$data_post_our[$user_id.'-'.$t[2]]['docs'][] = $doc;
				}
			}
		}
		$data_post[$user_id]['letter_num'] = $letter_num;
		$letter_num++;
	}

	$envelopes = array();

	require_once 'Spreadsheet/Excel/Writer.php';
    $title_sty = array(
          'FontFamily'=>'Calibri Bold',
          'VAlign'=>'top',
          'Align'=>'center',
          'Size'=>'10'
    );
    $border_sty = array(
          'Bottom'=>'2',
          'Top'=>'2',
          'Left'=>'2',
          'Right'=>'2'
    );
    $main_sty = array(
          'FontFamily'=>'Calibri',
          'VAlign'=>'top',
          'Align'=>'left',
          'Size'=>'10'
    );
    $merge_sty = array(
    	  'Align'=>'merge',
          'FontFamily'=>'Calibri',
          'VAlign'=>'top',
          'Size'=>'12'
    );

    $mergebold_sty = array(
    	  'Align'=>'merge',
          'FontFamily'=>'Calibri Bold',
          'VAlign'=>'top',
          'Size'=>'10'
    );



	$table_post = false;
    $workbook = new Spreadsheet_Excel_Writer("/tmp/{$sid}post.xls");
    $workbook->setVersion(8);
    $worksheet = $workbook->addWorksheet('');
    $worksheet->setLandscape();
    $worksheet->setInputEncoding('windows-1251');

    $fmtM = &$workbook->addFormat($main_sty);
    $fmtT = &$workbook->addFormat($title_sty);
    $fmtM->setTextWrap();

    $worksheet->write(0, 0, "N\nконверта", $fmtT);
    $worksheet->write(0, 1, "Кол-во писем\nв конверте", $fmtT);
    $worksheet->write(0, 2, "ID\nполучателя", $fmtT);
    $worksheet->write(0, 3, "Получатель", $fmtT);
    $worksheet->write(0, 4, "Адрес получателя", $fmtT);
    $worksheet->write(0, 5, "Тип доставки", $fmtT);

	$worksheet->setRow(0,40);
	$worksheet->setColumn(0,0,10);
	$worksheet->setColumn(1,1,13);
	$worksheet->setColumn(2,2,13);
	$worksheet->setColumn(3,3,25);
	$worksheet->setColumn(4,4,35);
	$worksheet->setColumn(5,5,30);

    $n = 1;
	foreach($data_post as $key=>$letter) {
		if($letter['docs']) {
			$t = preg_split("/-/", $key);

			$doc_files = array();
			foreach($letter['docs'] as $v) {
				array_push($doc_files, $v['file_id']);
			}

			if($t[1]=='t') {
				$company = letters::getCompany($t[0]);
				if($company['frm_type']) {
					$user_name = $company['frm_type'].' "'.$company['name'].'"';
				} else {
					$user_name = $company['name'];
				}
				$address = "{$company['index']}, {$company['country_title']}, {$company['city_title']}, {$company['address']}";
				array_push($envelopes, array( 'user'=>$user_name, 'address'=>$address, 'files'=>$doc_files ));
			} else {
				$recipient = letters::getUserReqvs($t[0]);
            	$user = new users();
            	$user->GetUserByUID($t[0]);
            	$user_name = ($recipient['form_type']==1 ? $recipient[1]['fio'] : $recipient[2]['full_name']);
            	$address =  $recipient['form_type']==1 ? $recipient[1]['address'] : $recipient[2]['address'];
				array_push($envelopes, array( 'user'=>($recipient['form_type']==1 ? $recipient[1]['fio'] : $recipient[2]['full_name']), 'address'=>$address, 'files'=>$doc_files ));
			}
			$worksheet->setRow($n,35);
            $worksheet->write($n, 0, $letter['letter_num'], $fmtM);
            $worksheet->write($n, 1, count($letter['docs']), $fmtM);
            $worksheet->write($n, 2, ($t[1]=='t' ? "c-{$t[0]}" : $t[0]), $fmtM);
            $worksheet->write($n, 3, $user_name, $fmtM);
            $worksheet->write($n, 4, $address, $fmtM);
            $worksheet->write($n, 5, $deliveries[$letter['delivery']], $fmtM);
			$n++;
			$table_post = true;
		}
	}
	$workbook->close();

	$table_post_our = false;
    $workbook = new Spreadsheet_Excel_Writer("/tmp/{$sid}post_our.xls");
	$workbook->setVersion(8);
    $worksheet = $workbook->addWorksheet('');
    $worksheet->setLandscape();
    $worksheet->setInputEncoding('windows-1251');

    $fmtM = &$workbook->addFormat($main_sty);
    $fmtMB = &$workbook->addFormat($main_sty+$border_sty);
    $fmtT = &$workbook->addFormat($title_sty);
    $fmtTB = &$workbook->addFormat($title_sty+$border_sty);
    $fmtMerge = &$workbook->addFormat($merge_sty);
    $fmtMergeBold = &$workbook->addFormat($mergebold_sty);


    $fmtM->setTextWrap();
    $fmtMB->setTextWrap();
    $fmtT->setTextWrap();
    $fmtTB->setTextWrap();
    $fmtMerge->setTextWrap();
    $fmtMergeBold->setTextWrap();

	$worksheet->setRow(0,80);

    $worksheet->write(0, 0, "ООО \"Ваан\"\n125040, Москва, улица Нижняя, дом 14, корпус 1\nИНН 7805399430/ КПП 771401001\nТелефон: +7 495 646-81-29", $fmtMerge);
    $worksheet->write(0, 1, '', $fmtMerge);
    $worksheet->write(0, 2, '', $fmtMerge);

	$worksheet->write(1, 0, "Реестр корреспонденции", $fmtMergeBold);

    $worksheet->write(1, 1, '', $fmtMergeBold);
    $worksheet->write(1, 2, '', $fmtMergeBold);

	$worksheet->write(2, 0, "", $fmtTB);
	$worksheet->write(2, 1, "Название организации", $fmtTB);
	$worksheet->write(2, 2, "Адрес", $fmtTB);
	$worksheet->write(2, 3, "Телефон", $fmtTB);
	$worksheet->write(2, 4, "Контактное лицо", $fmtTB);
	$worksheet->write(2, 5, "Срок доставки", $fmtTB);
	$worksheet->write(2, 6, "Комментарий", $fmtTB);
	$worksheet->write(2, 7, "Дата получения", $fmtTB);
	$worksheet->write(2, 8, "Подпись получателя", $fmtTB);
	$worksheet->write(2, 9, "ФИО получателя", $fmtTB);

	$worksheet->setRow(2,35);

	$worksheet->setColumn(0,0,6);
	$worksheet->setColumn(1,1,25);
	$worksheet->setColumn(2,2,30);
	$worksheet->setColumn(3,9,20);
	//$worksheet->setColumn(3,9,45);

	$n=3;
	foreach($data_post_our as $key=>$letter) {
		if($letter['docs']) {
			$table_post_our = true;
			$t = preg_split("/-/", $key);
			if($t[1]=='t') {
				$company = letters::getCompany($t[0]);
				if($company['frm_type']) {
					$user_name = $company['frm_type'].' "'.$company['name'].'"';
				} else {
					$user_name = $company['name'];
				}
				$address = "{$company['index']}, {$company['country_title']}, {$company['city_title']}, {$company['address']}";
				$phone = '';
				$fio = $company['fio'];
				array_push($envelopes, array( 'user'=>$user_name, 'address'=>$address ));
			} else {
				$recipient = letters::getUserReqvs($t[0]);
            	$user = new users();
            	$user->GetUserByUID($t[0]);
            	$address = ($recipient['form_type']==1 ? $recipient[1]['index'] : $recipient[2]['index']).", ".
					   ($recipient['form_type']==1 ? $recipient[1]['country'] : $recipient[2]['country']).", ".
					   ($recipient['form_type']==1 ? $recipient[1]['city'] : $recipient[2]['city']).", ".
					   ($recipient['form_type']==1 ? $recipient[1]['address'] : $recipient[2]['address']);
				$user_name = ($recipient['form_type']==1 ? $recipient[1]['fio'] : $recipient[2]['full_name']);
				$phone = ($recipient['form_type']==1 ? $recipient[1]['phone'] : $recipient[2]['phone']);
				$fio = ($recipient['form_type']==1 ? $recipient[1]['fio'] : $recipient[2]['fio']);
				array_push($envelopes, array( 'user'=>($recipient['form_type']==1 ? $recipient[1]['fio'] : $recipient[2]['full_name']), 'address'=>$address ));
			}
			
			$worksheet->setRow($n,35);
            $worksheet->write($n, 0, $n-2, $fmtMB);
			$worksheet->write($n, 1, $user_name, $fmtMB);
			$worksheet->write($n, 2, $address, $fmtMB);
			$worksheet->write($n, 3, $phone, $fmtMB);
			$worksheet->write($n, 4, $fio, $fmtMB);
			$worksheet->write($n, 5, "", $fmtMB);
			$worksheet->write($n, 6, "", $fmtMB);
			$worksheet->write($n, 7, "", $fmtMB);
			$worksheet->write($n, 8, "", $fmtMB);
			$worksheet->write($n, 9, "", $fmtMB);
            $n++;
		}
	}
	$workbook->close();

	if($table_post || $table_post_our) {

		$zip = new ZipArchive();
		$zip->open("/tmp/{$sid}postdocs.zip", ZIPARCHIVE::CREATE);

		if($envelopes) {
			$n = 1;
			foreach($envelopes as $letter) {
				$pdf = new FPDF('L', 'mm', 'A4');
				$pdf->AddFont('TimesNewRomanPSMT','','5f37f1915715e014ee2254b95c0b6cab_times.php');
				$pdf->SetFont('TimesNewRomanPSMT','',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->AddPage('L');
				$pdf->SetXY(197,145);
				$pdf->SetDrawColor(50,60,100);
				$pdf->MultiCell(65,6,"Кому: ".html_entity_decode($letter['user'])."\nКуда: ".html_entity_decode($letter['address']),0,'L');
				$pdf->Output("/tmp/{$sid}-{$n}letters.pdf", "F");
				$n++;
			}

			$pdf = new FPDI();
			$pagecount = 1;
			$n = 1;
			foreach($envelopes as $letter) {
				$pagecount = $pdf->setSourceFile("/tmp/{$sid}-{$n}letters.pdf");
				$tplidx = $pdf->importPage($pagecount, '/MediaBox');
				$pdf->addPage('L');
				$pdf->useTemplate($tplidx, 0, 0);

				if($letter['files']) {
					foreach($letter['files'] as $file_id) {
		               	$cfile = new CFile($file_id);
		               	if(preg_match("/\.pdf$/", $cfile->name)) {
        		       		$tmp_name = "/tmp/" . uniqid().".pdf";
                			file_put_contents($tmp_name, file_get_contents(WDCPREFIX . '/' . $cfile->path . $cfile->name));

							$pagecount = $pdf->setSourceFile($tmp_name);

							for($i = 1; $i <=  $pagecount; $i++){
							    $tplidx = $pdf->importPage($i);
    							$specs = $pdf->getTemplateSize($tplidx);
    							$pdf->addPage($specs['h'] > $specs['w'] ? 'P' : 'L');
    							$pdf->useTemplate($tplidx);
							}
						}
					}
				}
				$n++;
			}
			$pdf->Output("/tmp/{$sid}letters.pdf", 'F');

			$zip->addFile("/tmp/{$sid}letters.pdf","/letters.pdf");
		}

		if($table_post) {
			$zip->addFile("/tmp/{$sid}post.xls","/post.xls");
		}
		if($table_post_our) {
			$zip->addFile("/tmp/{$sid}post_our.xls","/post_our.xls");
		}
		$zip->close();

		@unlink("/tmp/{$sid}post.xls");
		@unlink("/tmp/{$sid}post_our.xls");
		@unlink("/tmp/{$sid}letters.pdf");

		$fsize = filesize("/tmp/{$sid}postdocs.zip");

 		header("Pragma: public");
    	header("Expires: 0"); 
    	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
    	header("Cache-Control: private",false);
    	header("Content-Type: application/zip"); 
    	header("Content-Disposition: attachment; filename=\"postdocs.zip\";" ); 
    	header("Content-Transfer-Encoding: binary"); 
    	header("Content-Length: ".$fsize); 
    	ob_clean(); 
    	flush(); 
    	readfile("/tmp/{$sid}postdocs.zip"); 

		@unlink("/tmp/{$sid}postdocs.zip");
		unset($_SESSION['admin_letters_data']);
	}

}

?>