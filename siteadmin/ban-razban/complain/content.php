<?
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/HTML/projects_lenta.php';

function buildNavigation($iCurrent, $iStart, $iAll, $sHref) {
    $sNavigation = '';
	for ($i=$iStart; $i<=$iAll; $i++) {
		if ($i != $iCurrent) {
			$sNavigation .= "<a href=\"".$sHref.$i."\" >".$i."</a> ";
		}else {
			$sNavigation .= '<b style="margin-right: 5px">'.$i.'</b>';
		}
	}
	return $sNavigation;
}

?>

<? include ('head.php') ?>

<script type="text/javascript">
function project_banned() {
    window.location = '<?=("/siteadmin/ban-razban/?mode=$mode".($page? "&p=$page": '').($search? "&search=$search": '').($admin? "&admin=$admin": '').($sort? "&sort=$sort": '').($group? "&group=$group": ''))?>';
}
</script>
<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.common.php");
if (!$no_answers) {$xajax->printJavascript('/xajax/');}
?>


<p>&nbsp;</p>

<? if (empty($projects)) { print "<center><div style='font: bold 16px Tahoma'>Нет проектов.</div></center>"; return; } ?>

<!-- Это временно, когда будет везде в админке новый дизайн - эти стили удалить -->
<style type="text/css">
.project-offers{
	font-weight:bold;
	}
.lnk-br-block:link, .lnk-br-block:visited{
	color: #FF0000;
	}
.br-from{
	font-size: 11px !important;
	color: #999;
	}
.br-from a:link, .br-from a:visited{
	color: #999;
	}
.project-info{
	margin: 0 0 0px 0px;
    padding: 0px;
	}
.project-info li{
	list-style-type:none;
	color: #666;
	padding: 0 0px 4px;
	}
.project-info li a{
	color: #666;
	}
.project-info li a:hover{
	color: #6BB24B;
	}
.project-preview-desc{
	margin: 0 0 15px 0;
	}
.project-preview-desc p{
	margin: 0 0 10px 0;
	}
.br-pl-one{
	list-style-type:none;
	border-bottom: 1px solid #D7D7D7;
	margin: 0 0 25px 0;
	padding: 0 0 15px 0;
	}
.br-pl-one h4{
	font: 180% Tahoma;
	margin: 0 0 10px 0;
    text-decoration: underline;
	}
.br-pl-one h4 a{
    text-decoration: underline;
	}
.br-pl-one p{
	margin: 0 0 10px 0;
	}
.br-search-list {
    margin: 0px;
    padding: 0px;
}
.br-search-list .br-ul-one{
	border-top: none !important;
	border-bottom: 1px solid #D7D7D7 !important;
	padding: 0 0 15px 0;
	}
.ban-report{
	border: 1px solid #D7D7D7;
	background: #FEFCDB;
	padding: 12px;
	font-size: 12px;
	margin: 0 0 10px 0;
	}
.ban-report h4{
	font-size: 12px;
	font-weight: 900;
	color: #666;
	}
.clear, .apf-option, .project-logo, .thread, .comment-one .set-date-line, .br-ul-one, .br-bl-one{
	height: 1%;
	}
.clear:after, .apf-option:after, .project-logo:after, .thread:after, .comment-one:after, .set-date-line:after, .mess-list li:after, .br-ul-one:after, .br-bl-one:after{
	content:".";
	display:block;
	clear:both;
	overflow:hidden;
	height: 0px;
	visibility:hidden;
	}

</style>

<ol class="br-search-list"> 
<? foreach($projects as $prj) { ?>
								<li class="br-pl-one"> 
                                    <a name="p<?=$prj['id']?>"></a>
									<h4><? if ($prj['payed']) { ?><img src="/images/ico_prepay.gif" width="21" height="21" border="0">&nbsp;<? } ?><a href="<?=getFriendlyURL("project", $prj['id'])?>"><?=YellowLine($prj['name'])?></a></h4> 
									<div class="project-preview-desc"> 
                                        <?=reformat($prj['descr'], 60)?>
									</div> 
									<ul class="project-info"> 
										<li><?=ago_pub_x(strtotimeEx($prj['post_date']))?></li> 
										<li>Автор: <?=(($prj['is_pro'] == 't')?' '.view_pro_emp(false, 8):'')?> <a href="/users/<?=$prj['login']?>/"><?=YellowLine($prj['uname'])?> <?=YellowLine($prj['usurname'])?></a> [<a href="/users/<?=$prj['login']?>/"><?=YellowLine($prj['login'])?></a>]</li> 
										<li class="last">Категория: <?= projects::printCategories($prj['categories']);?></li>
										<li class="last"><div class="project-offers"><a href="<?=getFriendlyURL("project", $prj['id'])?>">Предложения (<?=(int) $prj['offers_count']?>)</a></div></li>
									</ul> 
                                    <br clear="all" />
                                        
    									<div id="div_compliant_<?=$prj['id']?>" class="ban-report"> 
    										<h4>
                                                <?=projects_complains::GetComplainType( $prj['type'], true )?>
                                            </h4> 
                                            <p><?=reformat(html_entity_decode($prj['msg'], ENT_QUOTES),60)?></p> 
                                            <?
                                            if ( $prj['c_files'] && $prj['c_files'] != 'false' ) {
                                                echo '<p>';
                                                $files = preg_split("/,/", $prj['c_files']);
                                                foreach($files as $file) {
                                                    echo '<a href="'.WDCPREFIX.'/users/'.$prj['login'].'/upload/'.$file.'">'.$file.'</a><br>';
                                                }
                                                echo '</p>';
                                            }
                                            ?>
    										<div class="br-from"> 
    											<?=dateFormat("d.m.Y", $prj['date'])?> <a href="/users/<?=$prj['c_login']?>/"><?=$prj['c_uname']?> <?=$prj['c_usurname']?> [<?=$prj['c_login']?>]</a>  
    											<?if($prj['is_satisfied']):?>
    											<div class="br-from">
                        							<?=(($prj['is_satisfied'] == 't') ? 'Принято' : 'Отклонено')?> админом <?=dateFormat("d.m.Y", $prj['processed_at'])?> <a href="/users/<?=$prj['admin_login']?>/"><?=$prj['admin_uname'].' '.$prj['admin_usurname']?> [<?=$prj['admin_login']?>]</a>
                        						</div>
    											<?endif?>
    										</div> 
    									</div> 
    									
    									<div id="div_all_compliants_<?=$prj['id']?>" style="display: none;"></div>
    									
                                    <div id="project-reason-<?=$prj['id']?>" style="margin-top: 10px; display: none">&nbsp;</div>
									<div class="project-offers"> 
                                        <?php if ( $prj['complain_cnt'] > 1 ) { ?>
                            			<a onclick="getProjectComplaints(<?=$prj['id']?>, '<?=$group?>');" href="javascript:void(0);">Все жалобы (<?=(int) $prj['complain_cnt']?>)</a>
                            				<?if($group == 'new'):?> | <?endif?>
                            			<?php } ?>
                            			<?if($group == 'new'):?>
										<a id="prj_<?=$prj['id']?>" href="./?mode=<?=$mode?><?=($page? "&p=$page": '')?><?=($search? "&search=$search": '')?><?=($sort? "&sort=$sort": '')?><?=($admin? "&admin=$admin": '')?><?=($group? "&group=$group": '')?>&action=not_satisfycomplain&pid=<?=$prj['id']?>" style="font-weight:bold; color: #999;" onclick="return addTokenToLink('prj_<?=$prj['id']?>', 'Уверены, что хотите удалить все жалобы на проект?');">Снять все жалобы</a> | 
										<a href="javascript:;" class="lnk-br-block" onclick="banned.blockedProjectWithComplains(<?=$prj['id']?>);">Заблокировать</a>
										<?endif?>
									</div> 
								</li> 
<? } ?>
</ol>


<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>

    <td align="left" width="100%">
    <div id="fl2_paginator"><?

        // Страницы
        $pages = ceil($num_threads / $log_pp);
        if ($pages > 1) {
            $maxpages = $pages;
            $i = 1;
            $sHref = './?mode=complain'.($sort? "&sort=$sort": "").($ft? "&ft=$ft": "").($search? "&search=$search": "").($admin? "&admin=$admin": "").($log_pp ? '&log_pp='. $log_pp : '').'&p=';
            
            if ($pages > 32) {
                $i = floor($page/10)*10 + 1;
                if ($i >= 10 && $page%10 < 5) $i = $i - 5;
                $maxpages = $i + 22 - floor(log($page,10)-1)*4;
                if ($maxpages > $pages) $maxpages = $pages;
                if ($maxpages - $i + floor(log($page,10)-1)*4 < 22 && $maxpages - 22 > 0) $i = $maxpages - 24 + floor(log($page,10)-1)*3;
            }

            $sBox = '<table width="100%"><tr>';
            if ($page == 1) {
                $sBox .= '<td><div id="nav_pre_not_active"><span>предыдущая</span></div></td>';
            } else {
                $sBox .= "<input type=\"hidden\" id=\"pre_navigation_link\" value=\"".($sHref.($page-1))."\">";
                $sBox .= "<td><div id=\"nav_pre_not_active\"><a href=\"".($sHref.($page-1))."\" style=\"color: #717171\">предыдущая</a></div></td>";
            }
            $sBox .= '<td width="90%" align="center">';
            //в начале
            if ($page <= 10) {
                $sBox .= buildNavigation($page, 1, ($pages>10)?($page+4):$pages, $sHref);
                if ($pages > 15) {
                    $sBox .= '<span style="padding-right: 5px">...</span>';
                    //$sBox .= buildNavigation($page, $pages-5, $pages, $sHref);
                }
            }
            //в конце
            elseif ($page >= $pages-10) {
                $sBox .= buildNavigation($page, 1, 5, $sHref);
                $sBox .= '<span style="padding-right: 5px">...</span>';
                //$sBox .= buildNavigation($page, $page-5, $pages, $sHref);
            }
            else {
                $sBox .= buildNavigation($page, 1, 5, $sHref);
                $sBox .= '<span style="padding-right: 5px">...</span>';
                $sBox .= buildNavigation($page, $page-4, $page+4, $sHref);
                $sBox .= '<span style="padding-right: 5px">...</span>';
                //$sBox .= buildNavigation($page, $pages-5, $pages, $sHref);
            }
            $sBox .= '</td>';
            if ($page == $pages) {
                $sBox .= "<td><div id=\"nav_next_not_active\"><span>следующая</span></div></td>";
            } else {
                $sBox .= "<input type=\"hidden\" id=\"next_navigation_link\" value=\"".($sHref.($page+1))."\">";
                $sBox .= "<td><div id=\"nav_next_not_active\"><a href=\"".($sHref.($page+1))."\" style=\"color: #717171\">следующая</a></div></td>";
            }
            $sBox .= '</tr>';
            $sBox .= '</table>';
        }
        $sBox .= '</div>';
        echo $sBox;
        // Страницы закончились
        
        echo printPerPageSelect( $log_pp, 'p' );
    ?></td>

</tr>

</table>

<script type="text/javascript">
var openComplaints = new Array();

function getProjectComplaints( id, group ) {
    if ( typeof(openComplaints[id]) != 'undefined' ) {
        if ( openComplaints[id].opened == true ) {
            openComplaints[id].opened = false;
            $( 'div_all_compliants_'+id ).setStyle('display','none');
            $('div_compliant_'+id).setStyle('display','');
        }
        else {
            openComplaints[id].opened = true;
            $('div_compliant_'+id).setStyle('display','none');
            $('div_all_compliants_'+id).setStyle('display','');
        }
    }
    else {
        new Request.JSON({
            url: '/xajax/projects.server.php',
            onSuccess: function(resp) {
                if ( resp && resp.success ) {
                    var html  = '';
                    var files = '';
                    
                    for ( i = 0; i < resp.data.length; i++ ) {
                        files = '';
                        
                        if ( resp.data[i].files.length ) {
                            files = '<p>';
                            
                            for ( j = 0; j < resp.data[i].files.length; j++ ) {
                                files = files + resp.data[i].files[j];
                            }
                            
                            files = files + '<p>';
                        }

                        
                        html += '<div class="ban-report">\
                        	<h4>' + resp.data[i].type + '</h4>\
                        	<p>' + resp.data[i].text + '</p>' + files + '\
                        	<div class="br-from">\
                        		' + resp.data[i].date + ' <a href="/users/' + resp.data[i].login + '/">' + resp.data[i].name + ' ' + resp.data[i].surname + ' [' + resp.data[i].login + ']</a>\
                        	</div>';

                        if(resp.data[i].status) {
                            if(resp.data[i].status == 1) var action_string = 'Принято';
                            else if(resp.data[i].status == 2) var action_string = 'Отклонено';
                            
                        	html += '<div class="br-from">\
                        		' + action_string + ' админом ' + resp.data[i].pdate + ' <a href="/users/' + resp.data[i].admin_login + '/">' + resp.data[i].admin_uname + ' ' + resp.data[i].admin_usurname + ' [' + resp.data[i].admin_login + ']</a>\
                        	</div>';
                        }

                        html += '</div>';
                    }
                    
                    openComplaints[id] = {opened: true};
                    $('div_all_compliants_'+id).set('html',html);
                    $('div_all_compliants_'+id).setStyle('display','');
                    $('div_compliant_'+id).setStyle('display','none');
                }
                else {
                    alert('Ошибка получения данных');
                }
            }
        }).post({
           'xjxfun': 'getProjectComplaints',
           'xjxargs': ['N' + id, group],
           'u_token_key': _TOKEN_KEY
        });
    }
}
</script>