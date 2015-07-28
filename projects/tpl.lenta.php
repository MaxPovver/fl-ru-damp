<a name="viewfirst"></a><div id="publicfirst"></div>

<script type="text/javascript">var openedProjects=new Array();var isPrjCssOpened=<?=($isPrjOpened? 'true' : 'false')?>;</script>
<style type="text/css">.prj-full-display {display:<?=($isPrjOpened? 'block' : 'none')?>}</style>
<style type="text/css">.prj-one .prj-clogo {float:<?=($isPrjOpened? 'right' : 'none')?>;}</style>

                 <?php                 
                 $can_change_prj = hasPermissions("projects");
                 
                 if($can_change_prj) {
                 ?>
                    <?php require_once($_SERVER['DOCUMENT_ROOT'].'/projects/tpl.prj-quickedit.php'); ?>

                    <div id="popup_budget" class="b-popup b-popup_center b-popup_width_350" style="top: 95px;  margin-left:-10000px">
                    	 <b class="b-popup__c1"></b>
                    	 <b class="b-popup__c2"></b>
                    	 <b class="b-popup__t"></b>
                    	 <div class="b-popup__r">
                    		 <div class="b-popup__l">
                    			 <form id="popup_budget_form" class="b-popup__body b-popup__body_padbot_50" action="">
                    				<h4 class="b-popup__h4">Редактирование бюджета</h4>
                    				<h5 id="popup_budget_prj_name" class="b-popup__h5 b-popup__h5_padbot_20"></h5>
                    				<div class="b-input b-input_inline-block b-input_padbot_10 b-input_height_24">
                    					<input id="popup_budget_prj_price" class="b-input__text b-input__text_width_90 b-input__text_fontsize_20" name="cost" type="text" maxlength="6" >
                    				</div>&nbsp;<div class="b-select b-select_inline-block b-select_padbot_10">
                    					<select id="popup_budget_prj_currency" class="b-select__select b-select__select_width_70 b-select__select_fontsize_20" name="currency">
                    						<option value="2">руб.</option>
                    						<option value="0">дол.</option>
                    						<option value="1">евро</option>
                    						<option value="3">FM</option>
                    					</select>
                    				</div>&nbsp;<div class="b-select b-select_inline-block b-select_padbot_10">
                    					<select id="popup_budget_prj_type" class="b-select__select b-select__select_width_110 b-select__select_fontsize_20" name="costby">
                    						<option value="1">за час</option>
                    						<option value="2">за день</option>
                    						<option value="3">за месяц</option>
                    						<option value="4">за проект</option>
                    					</select>
                    				</div>
                    				<div class="b-check b-check_padbot_10">
                    					<input id="popup_budget_prj_agreement" class="b-check__input" name="agreement" type="checkbox" value="1">
                    					<label class="b-check__label b-check__label_fontsize_13" for="popup_budget_prj_agreement">Бюджет по договорённости</label>
                    				</div>
                                    <div id="popup_budget_prj_price_error" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10" style="display: none; ">
                                        <b class="b-fon__b1"></b>
                                        <b class="b-fon__b2"></b>
                                        <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                                            <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20">Бюджет заполнен не верно</div>
                                        </div>
                                        <b class="b-fon__b2"></b>
                                        <b class="b-fon__b1"></b>
                                    </div>
                    				 <div class="b-popup__foot">
                    					<div class="b-buttons">
                    						<a id="popupBtnSaveBudget" class="b-button b-button_rectangle_transparent" onclick="" href="#">
                    							<span class="b-button__b1">
                    								<span class="b-button__b2 b-button__b2_padlr_5">
                    									<span class="b-button__txt">Сохранить изменения</span>
                    								</span>
                    							</span>
                    						</a>
                    						<a class="b-buttons__link b-buttons__link_dot_039 b-popup__close" href="#" onClick="popupHideChangeBudget(); return false;">Закрыть без изменений</a>
                    					</div>
                    				 </div>
			                    </form>
                    		 </div>
                    	 </div>
                    	 <b class="b-popup__b"></b>
                    	 <b class="b-popup__c3"></b>
                    	 <b class="b-popup__c4"></b>
                    </div>
                 <?
                 }
                 ?>


<? foreach ($list as $row) { ?>
<div class="prj-one<?= $row['t_is_payed'] ? ' prj-payed' : '' ?><?= $row['is_color'] == 't' ? ' prj-colored' : ' prj-usual' ?><?= $row['is_bold'] == 't' ? ' prj-weight': '' ?>"  id="project-item<?= $row['id'] ?>">

    
    <div class="form">
        <div class="form-in">
            <? seo_start($is_ajax)?>	
            <?// БЮДЖЕТ ?>
            <? if ($row['cost']) {
            $priceby_str = getPricebyProject($row['priceby']);
            if($row['cost']=='' || $row['cost']==0) { $priceby_str = ""; }
            ?>
            <var class="bujet">
                <?php if($can_change_prj) { ?>
                    <a href="#" id="prj_budget_lnk_<?=$row['id']?>" onClick="popupShowChangeBudget(<?=$row['id']?>, '<?=$row['cost']?>', '<?=$row['currency']?>', '<?=$row['priceby']?>', false, <?=$row['id']?>, 1); return false;"><?= CurToChar($row['cost'], $row['currency']) ?><?=$priceby_str?></a>
                <? } else { ?>
                    <?= CurToChar($row['cost'], $row['currency']) ?><?=$priceby_str?>
                <? } ?>
            </var>
            <? } else { ?>
                <?php if($can_change_prj) { ?>
                    <var class="bujet-dogovor"><a href="#" id="prj_budget_lnk_<?=$row['id']?>" onClick="popupShowChangeBudget(<?=$row['id']?>, '', 0, 1, true, <?=$row['id']?>, 1); return false;">По договоренности</a></var>
                <? } else { ?>
                    <var class="bujet-dogovor">По договоренности</var>
                <? } ?>
            <? } ?>

            <?// ЛОГО ?>
            <? if ($row['logo_name']) { ?>
                <? if ($row['link'] != "") { ?>
                <noindex>
                <a href="http://<?= formatLink($row['link']) ?>" target="_blank" rel="nofollow">
                    <img src="<?= WDCPREFIX.'/'.$row['logo_path'].$row['logo_name'] ?>" alt="" class="prj-clogo" />
                </a>
                </noindex>
                <? } else { ?>
                <img src="<?= WDCPREFIX.'/'.$row['logo_path'].$row['logo_name'] ?>" alt="" class="prj-clogo" />
                <? } ?>
            <? } ?>
            <?= seo_end(false, $is_ajax)?>	
            <?// ЗАГОЛОВОК ?>
            <h3>
                <? if ($row['t_is_ontop']) { ?>
                <img src="/images/tp<?/*= $row['is_color'] == 't' ? '2' : ''*/ ?>.png" alt="" />
                <? } ?>
                <? /* #0019741 if ($row['t_prefer_sbr']) { ?>
                <img src="/images/sbr_p.png" class="sbr_p" title="Работодатель&nbsp;хочет&nbsp;работать&nbsp;через&nbsp;Cделку&nbsp;без&nbsp;риска" alt="Работодатель хочет работать через Cделку без риска" />
                <? } */?>
                <a id="prj_name_<?=$row['id']?>" name="prj<?= $row['id'] ?>" href="<?= getFriendlyURL("project", $row['id']) ?>">
                    <?php $sTitle = $row['moderator_status'] === '0' && $row['is_pro'] != 't' ? $stop_words->replace($row['name']) : $row['name']; ?>
                    <?= reformat2($sTitle,30,0,1) ?>
                </a>
            </h3>
            <? if (get_uid(false) && $row['t_is_ontop'] && !is_emp()) { ?>
            <a href="#" title="Скрыть" onclick="xajax_HideProject(<?= $row['id'] ?>, 'hide', '<?= $this->kind ?>', '<?= $this->page ?>', '<?= $this->filter ?>'); return false;" class = "close">скрыть</a>
            <? } ?>
            
            <?// ТЕКСТ ПРОЕКТА ?>
            <? seo_start($is_ajax)?>	
            <div class="prj-full-display">
                <div class="utxt">
                    <p>
                        <?= $row['descr'] ?>
                    </p>
                </div>

                <div id="project-reason-<?= $row['id'] ?>" style="display: <?= ($row['is_blocked'] ? 'blocked': 'none') ?>">
                    <? if ($row['is_blocked']) { ?>
                    <div class='br-moderation-options'>
                        <a href='https://feedback.fl.ru/' class='lnk-feedback' style='color: #fff;'>Служба поддержки</a>
                        <div class='br-mo-status'><strong>Проект заблокирован.</strong> Причина: <?= reformat($row['blocked_reason'], 24, 0, 0, 1, 24) ?></div>
                        <p class='br-mo-info'><?= ($row['admin_login'] ? "Заблокировал: <a href='/users/{$row['admin_login']}' style='color: #FF6B3D'>{$row['admin_uname']} {$row['admin_usurname']} [{$row['admin_login']}]</a><br />": '') ?>
                        Дата блокировки: <?= dateFormat('d.m.Y H:i', $row['blocked_time']) ?></p>
                    </div>
                    <? } ?>
                </div>

                <? if ($row['t_pro_only'] == 't' && $this->kind != 6) { ?>
                <ul class="project-info clear">
                    <li>Только для <a href="/payed/"><img src="/images/icons/f-pro.png" alt="PRO" /></a></li>
                </ul>
                <? } ?>

                <? if (hasPermissions('projects')) { ?>
                <ul class="prj-info c" style="margin-bottom: 5px;">
                    <li class="pi-time">Автор: <a href="/users/<?= $row['login'] ?>"><?= $row['uname']." ".$row['usurname']." [".$row['login'] ?>]</a></li>
                </ul>
                <? } ?>
                
                <ul class="prj-info c" id="prj-info-<?=$row['id']?>">
                    <li class="pi-answer">
                        <? if (!is_emp()) { ?>
                        <a href="<?= getFriendlyURL("project", $row['id']) ?>">
                            <? if (!$row['offer_id']) { ?>
                            Ответить на проект<? } else { ?>Вы уже ответили на этот проект<? } ?></a>
                        <? } else { ?>
                        <a href="<?= getFriendlyURL("project", $row['id']) ?>">
                            <? if ($row['prj_status'] != 't' && $row['offers_count'] > 0 && $row['user_id'] == $this->uid) { ?>
                            <strong>Предложения</strong><? } else { ?>Предложения<? } ?></a>
                        <? } ?>
                        (<?= intval($row['offers_count']) ?>) 
                        <? if (hasPermissions('projects') && $row['unread']) { ?>
                        <strong style='color:#6BB24B'>(<?= $row['unread'] . ' ' . ending($row['unread'], 'новое', 'новых', 'новых') ?>)</strong>
                        <? } ?>
                    </li>
                    <? if ($row['t_is_payed']  && $row['kind'] != 2 && $row['kind'] != 7) { ?>
                    <li class="pi-payed"><strong>Платный проект</strong></li>
                    <? } ?>

                    <? if ($row['kind'] == 2 || $row['kind'] == 7) { ?>
                    <li class="pi-red">Конкурс</li>
                    <? } else if ($row['kind'] == 4) { ?>
                    <li class="pi-office">
                        В офис <?= (($row['country']) ? "(".$row['country_name'] . (($row['city']) ? ", " . $row['city_name'] : "" ) . ")" : "") ?>
                    </li>
                    <? } ?>
                    <?php if($row['kind'] == 2 || $row['kind'] == 7) { ?>
                        <?if(strtotime($row['end_date']) > time()) { ?>
                        <li class="pi-time">до окончания осталось: <?= ago_pub_x(strtotime($row['end_date']), "ynjGx") ?></li> 
                        <? } else {?>
                        <li class="pi-time">завершен</li>
                        <? }?>
                    <?php } else { //if?>
                    <li class="pi-time"><?= ago_pub_x(strtotime($row['create_date']), "ynjGx", 0, true) ?></li>
                    <?php }//else?>
                </ul>
            </div>
            <?= seo_end(false, $is_ajax)?>	
        </div>
    </div>
    
    
    <? if($this->edit_mode || ($this->uid == $row['user_id'] && $this->uid && $row['is_blocked'] != 't')) { ?>
    <div class="post-admin prj-full-display">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="post-admin-in c">
            <ul>
                <? // Для админов ?>
                <? if ($this->edit_mode) { ?>
                <li class="first"><a href="/public/?step=1&public=<?= $row['id'] ?>&red=<?= rawurlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']) ?>" onClick="popupQEditPrjShow(<?=$row['id']?>, event); return false;" class="lbk-dred">Редактировать</a></li>
                <li class="last"><span id='project-button-<?= $row['id'] ?>'><a href="javascript:void(0);" onclick="banned.<?= ($row['is_blocked']? 'unblockedProject': 'blockedProject') ?>(<?= $row['id'] ?>)" class="lnk-block lnk-dred"><?= ($row['is_blocked'] ? 'Разблокировать' : 'Заблокировать') ?></a></span></li>

                <? // Для автора проекта ?>
                <? } elseif ($this->uid == $row['user_id'] && $this->uid && $row['is_blocked'] != 't') { ?>
                <li class="first"><a href="/public/?step=1&public=<?= $row['id'] ?>&red=<?= rawurlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']) ?>" class="lnk-dred">Редактировать</a></li>
                  <? if($row['kind'] != 2 && $row['kind'] != 7) { ?>
                  <li><a href="?action=prj_close&prid=<?= $row['id'] ?>&kind=<?= $row['kind'] ?>" onclick="return warning(2)" class="lnk-dred">Снять с публикации</a></li>
                  <? } ?>
                <? } ?>
            </ul>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
    <? } ?>
</div>
<? } ?>
<div class="rss">
    <script type="text/javascript">
    var sub = <?= $categories_js ?>;
    var RSS_LINK = '/rss/<?= $rss_link ?>';
    </script>
    <script type="text/javascript">document.write('<img src="/images/ico_rss.gif" alt="RSS" /><a href="javascript:void(0)" onClick="showRSS(); return false;" class="lnk-333">Подписаться через RSS</a>');</script>
    <div style="display: none;" class="overlay ov-out" id="rsso">
        <b class="c1"></b>
        <b class="c2"></b>
        <b class="ov-t"></b>
        <div class="ov-r">
            <div class="ov-l">
                <div class="ov-in" style="height:110px">
                    <label for="rss">Укажите разделы:</label>&nbsp;&nbsp;<br/>
                    <select style="width:340px"  onchange="FilterSubCategoryRSS(this.value);" name="rss_cat" id="rss_cat">
                        <option value="">Все разделы</option>
                        <? foreach($categories as $cat) { if(!$cat['id']) continue; ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                        <? } ?>
                    </select>
                    <br/>
                    <label for="rss_sub">Укажите подразделы:</label>&nbsp;&nbsp;<br/><select style="width:340px" name="rss_sub" id="rss_sub">
                        <option value="">Весь раздел</option>
                    </select>
                    <div class="ov-btns">
                        <input value="Подписаться" class="i-btn i-bold" type="button" onClick="gotoRSS(); document.getElementById('rsso').style.display='none'; return false;" />
                        <input value="Отменить" class="i-btn" onclick="$(this).getParent('div.overlay').setStyle('display', 'none'); return false;" type="button" />
                    </div>
                </div>
            </div>
        </div>
        <b class="ov-b"></b>
        <b class="c3"></b>
        <b class="c4"></b>
    </div>
</div>

<?= new_paginator($this->page, $this->pages, array(3, $this->filter ? 1 : 3), "%s?".urldecode(url('page,kind', array('page' => '%d')))."%s", true) ?>
