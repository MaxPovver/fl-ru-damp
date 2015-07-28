<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/portfoliopos.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");

function ChangeProfPos($prof_id, $direction){
	global $session;
	session_start();
	$objResponse = new xajaxResponse();
	if ($prof_id2 = portfolio::ChangeProfOrder($_SESSION['uid'], intval($prof_id), intval($direction))) {
		$script = "can_move = 1;
		wall=document.getElementById('sprof".$prof_id."');
		wall2=document.getElementById('sprof".$prof_id2."');
		icoup1=document.getElementById('icoup".$prof_id."');
		icoup2=document.getElementById('icoup".$prof_id2."');
		icodn1=document.getElementById('icodn".$prof_id."');
		icodn2=document.getElementById('icodn".$prof_id2."');
		tmp = icoup1.src;
		icoup1.src = icoup2.src;
		icoup2.src = tmp;
		tmp = icodn1.src;
		icodn1.src = icodn2.src;
		icodn2.src = tmp;
		tmp = wall.innerHTML;
		tmp2 = wall.className;
		wall.innerHTML = wall2.innerHTML;
		wall2.innerHTML = tmp;
		wall.className = wall2.className;
		wall2.className = tmp2;
		tmp = wall.id;
		wall.id = wall2.id;
		wall2.id = tmp;
		";
	} else {
		$script = "can_move = 1;";
	}
	$objResponse->script($script);
	return $objResponse;
}

function ChangePos($proj_id, $direction, $pro = 0, $portf_id = 0){
	session_start();
	$objResponse = new xajaxResponse();
/*
	$proj_id2 = portfolio::ChangePos($_SESSION['uid'], intval($proj_id), intval($direction));

	$s = "
	
	if (document.getElementById('w_select_".$portf_id."_".$proj_id."').checked)
	{
		alert('ok-".$proj_id."');
	}
	else
	{
		alert ('nope-".$proj_id."');
	}
	
	
	if (document.getElementById('w_select_".$portf_id."_".$proj_id2."').checked)
	{
		alert('ok-".$proj_id2."');
	}
	else
	{
		alert ('nope-".$proj_id2."');
	}
	
	";
	$objResponse->script($s);
//	$objResponse->alert($objResponse->script($s));


*/

	if ($proj_id2 = portfolio::ChangePos($_SESSION['uid'], intval($proj_id), intval($direction))) {
		$script = "can_move = 1;
        var td = $('num$proj_id');        
        var position    =  parseInt(td.innerHTML);
        var td = $('num$proj_id2');        
        var position2    =  parseInt(td.innerHTML);
		$('num$proj_id').set('html', position2 + '.');
		$('num$proj_id2').set('html', position + '.');
		if (document.getElementById('w_select_".$portf_id."_".$proj_id."').checked){checked1 = true;} else {checked1 = false;}
		if (document.getElementById('w_select_".$portf_id."_".$proj_id2."').checked){checked2 = true;} else {checked2 = false;}
		icoup1 = document.getElementById('icoupw".$proj_id."');
		icoup2 = document.getElementById('icoupw".$proj_id2."');
		icodn1 = document.getElementById('icodnw".$proj_id."');
		icodn2 = document.getElementById('icodnw".$proj_id2."');
		tmp = icoup1.src;
		icoup1.src = icoup2.src;
		icoup2.src = tmp;
		tmp = icodn1.src;
		icodn1.src = icodn2.src;
		icodn2.src = tmp;
		wall = document.getElementById('sproj".$proj_id."a');
		wall2 = document.getElementById('sproj".$proj_id2."a');
			tmp3 = wall.innerHTML;
			wall.innerHTML = wall2.innerHTML;
			wall2.innerHTML = tmp3;
			wall.id = 'sproj".$proj_id2."a';
			wall2.id = 'sproj".$proj_id."a';";
		if ($pro) $script .= "
		wall = document.getElementById('sproj".$proj_id."d');
		wall2 = document.getElementById('sproj".$proj_id2."d');
			tmp3 = wall.innerHTML;
			wall.innerHTML = wall2.innerHTML;
			wall2.innerHTML = tmp3;
			wall.id = 'sproj".$proj_id2."d';
			wall2.id = 'sproj".$proj_id."d';
		wall = document.getElementById('sproj".$proj_id."e');
		wall2 = document.getElementById('sproj".$proj_id2."e');
			tmp3 = wall.innerHTML;
			wall.innerHTML = wall2.innerHTML;
			wall2.innerHTML = tmp3;
			wall.id = 'sproj".$proj_id2."e';
			wall2.id = 'sproj".$proj_id."e';
		document.getElementById('w_select_".$portf_id."_".$proj_id."').checked = checked1;
		document.getElementById('w_select_".$portf_id."_".$proj_id2."').checked = checked2;";

		$objResponse->assign("prj_msg_$proj_id", "innerHTML", '');
		$objResponse->assign("prj_msg_$proj_id2", "innerHTML", '');
	} else {
		$script = "can_move = 1;";
	}
	$objResponse->script($script);

	return $objResponse;
}

function ChangeTextPrev($proj_id, $check)
{
	session_start();
	$objResponse = new xajaxResponse();
	$portf = new portfolio();
  $descr = $portf->GetField($proj_id, "descr");
	if (!$portf->ChangeTextPrev($_SESSION['uid'], intval($proj_id), intval($check)))
	{
		$script = "document.getElementById('prev".$proj_id."').disabled = false;";
		if ($check != 0)
		{
      $text = "<div align=\"left\" style=\"width:200px;vertical-align:top;\"><div style=\"text-align:left;vertical-align:top;\">" . trim(viewdescr($user->login, reformat($descr, 32, 0, 1))) . "</div></div>";
		}
		$objResponse->assign("previmg".intval($proj_id),"innerHTML", $text);
	} else {
		$script = "document.getElementById('prev".$proj_id."').disabled = false;";
	}
	$objResponse->script($script);
	return $objResponse;
}

function ChangeGrPrev($prof_id, $check){
	session_start();
	$objResponse = new xajaxResponse();
	$portf = new portfolio();
	if (!$portf->ChangeGrPrev($_SESSION['uid'], intval($prof_id), $projs))
	{
		if ($projs)
		{
  		foreach ($projs as $id => $prj)
  		{
  			if ($check != 0)
  			{
  			  if ($prj['prev_type'] == 1)
  			  {
                $text = "<div style=\"width:200px\">" . reformat2($prj['prev_data'], 37) . "</div>";
  			  }
  			  else
  			  {
    				if (in_array(strtolower(CFile::getext($prj['prev_data'])), $GLOBALS['graf_array']) && strtolower(CFile::getext($prj['prev_data'])) != "swf")
    				{
    					$text = "<div align=\"left\" style=\"width:200px;\"><a href=\"/users/".$_SESSION['login']."/viewproj.php?prjid=".$id."\" target=\"_blank\" class=\"blue\">
    				" . viewattach($_SESSION['login'], $prj['prev_data'], "upload", $file, 500, 200, 307200, 0, 0, 'left')."</a></div>";
    				}
    				else
    				{
    				  $text = viewattach($_SESSION['login'], $prj['prev_data'], "upload", $file, 500, 200, 307200, 0, 0, 'left');
    				}
    #				$script .= "document.getElementById('prev".$id."').checked = true;";
  			  }
  			}
  			else
  			{
  #				$script = "document.getElementById('prev".$id."').checked = false;";
		      $text = '<div style="width:200px">&nbsp;</div>';
  			}
  			$objResponse->assign("previmg".intval($id),"innerHTML",$text);
  			$objResponse->script($script);
  		}
		}
		$script = "document.getElementById('grprev".$prof_id."').disabled = false;";
	} else {
		$script = "document.getElementById('grprev".$prof_id."').disabled = false;";
	}
	$objResponse->script($script);
	return $objResponse;
}

function ChangePortfPrice($proj_id, $cost, $cost_type, $time_type, $time_value)
{
	global $session;
	session_start();
	$objResponse = new xajaxResponse();
	$portf = new portfolio();
	
  $proj_id = intval($proj_id);
  $cost = intval(str_replace(" ", "", $cost) * 100) / 100;
  $cost_type = intval($cost_type);
  $time_type = intval($time_type);
  $time_value = intval($time_value);
  
	$error = $portf->ChangePortfPrice($_SESSION['uid'], $proj_id, $cost, $cost_type, $time_type, $time_value);
	if ($error == '')
	{
		$script = "document.getElementById('prj_cost_$proj_id').value = $cost;
		document.getElementById('prj_cost_type_$proj_id').value = $cost_type;
		document.getElementById('prj_time_type_$proj_id').value = $time_type;
		document.getElementById('prj_time_value_$proj_id').value = $time_value;";
  	$objResponse->script($script);
		$objResponse->assign("prj_msg_$proj_id","innerHTML",view_info('Данные сохранены'));
	}
	else
	{
		$objResponse->assign("prj_msg_$proj_id", "innerHTML", view_error($error));
	}
	return $objResponse;
}


    /**
     * Изменяет количество выделенных работ при редактировании в разделе портфолио
     *
     * @param integer $profid		id специализации
     * @param integer $wcnt		количество выделенных работ
     * @param integer $action		удалили или добавили работу в общий массив (add/delete)
     * @param integer $projid		id работы
     *
     * @return str
     */

function ChangeProfCountSelected($profid, $wcnt, $action, $projid)
{
	global $session;
	session_start();

	$objResponse = new xajaxResponse();

	if ($wcnt >= 11 && $wcnt <= 19)
	{
		$works_str = "Выделено $wcnt работ";
		$delete_str = "Вы действительно хотите удалить $wcnt работ?";
		$move_str = "Вы действительно хотите переместить $wcnt работ?";
	}
	else
	{
		$wcnt_tmp = $wcnt % 10;

		if ($wcnt_tmp == 1)
		{
			$works_str = "Выделена $wcnt работа";
			$delete_str = "Вы действительно хотите удалить работу?";
			$move_str = "Вы действительно хотите переместить работу?";
		}
		elseif ($wcnt_tmp >= 2 && $wcnt_tmp <= 4)
		{
			$works_str = "Выделено $wcnt работы";
			$delete_str = "Вы действительно хотите удалить $wcnt работы?";
			$move_str = "Вы действительно хотите переместить $wcnt работы?";
		}
		else
		{
			$works_str = "Выделено $wcnt работ";
			$delete_str = "Вы действительно хотите удалить $wcnt работ?";
			$move_str = "Вы действительно хотите переместить $wcnt работ?";
		}
	}

	if ($action == "add")
	{
		$_SESSION['w_select'][$profid][$projid] = 1;
	}
	elseif ($action == "delete")
	{
		unset($_SESSION['w_select'][$profid][$projid]);
	}

	$objResponse->assign("w_count_selected_".$profid,"innerHTML",$works_str);
	$objResponse->assign("w_delete_".$profid,"innerHTML",$delete_str);
	$objResponse->assign("w_move_".$profid,"innerHTML",$move_str);

	if (sizeof($_SESSION['w_select'][$profid]))
	{
		$objResponse->assign("w_delete_".$profid."_btn","disabled",false);
		$objResponse->assign("w_move_".$profid."_select","disabled",false);
		$objResponse->assign("w_move_".$profid."_btn","disabled",false);
	}
	else
	{
		$objResponse->assign("w_delete_".$profid."_btn","disabled",true);
		$objResponse->assign("w_move_".$profid."_select","disabled",true);
		$objResponse->assign("w_move_".$profid."_btn","disabled",true);
	}

	return $objResponse;
}

function DelPict($login, $prj_id, $pict_type)
{
	$objResponse = new xajaxResponse();
	$portf = new portfolio();
	if($portf->DelPict($login, $prj_id, $pict_type))
  	$objResponse->script("aftdelpict({$pict_type})");
	return $objResponse;
}


$xajax->processRequest();
?>