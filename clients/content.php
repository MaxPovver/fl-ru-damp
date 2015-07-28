<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/freelancers.common.php");
$prfs = new professions();

$profs = $prfs->GetAllProfessions("",0, 1);

//require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/portfolio.common.php");
$xajax->printJavascript('/xajax/');
$uid = get_uid();
?>
<? /*
<script type="text/javascript">var ___isIE5_5 = 1;</script>
<![if lt IE 5.5]>
<script type="text/javascript">var ___isIE5_5 = 0;</script>
<![endif]>
*/ ?>
<script type="text/javascript">
var ___WDCPREFIX = '<?=WDCPREFIX?>';
</script>

<a name="frl" id="frl_anc"></a>

    <h1 class="b-page__title">С фрилансерами работают</h1>
<div class="b-layout__right b-layout__right_relative b-layout__left_width_72ps b-layout__left_float_left">
		<div class="b-menu b-menu_padbot_10 b-menu_line b-menu_relative b-menu__cat" >
                            <div class=" cat-tab">
                            <div class="b-menu"  data-accordion="true" data-accordion-descriptor="worktype">
			<ul class="b-menu__list">
                <li class="b-menu__item">
                    <a class="b-menu__link" href="/freelancers/" title="Все фрилансеры">
                        <span class="b-menu__b1">Все фрилансеры</span>
                    </a>
                </li>
                <li class="b-menu__item">
                    <a class="b-menu__link" href="/portfolio/<?= ($prof_id ? '?prof=' . $prof_id : '') ?>" title="Работы">
                        <span class="b-menu__b1">Работы</span>
                    </a>
                </li>
                <li class="b-menu__item <?php if (!( $page > 1 )) print 'b-menu__item_active' ?>"  data-accordion-opener="true" data-accordion-descriptor="worktype">
                    <a class="b-menu__link" href="/clients/<?= ($prof_id) ? '?prof=' . $prof_id : "" ?>" title="Клиенты"><span class="b-menu__b1">Клиенты</span></a>
                </li>
				<li class="b-menu__item b-menu__item_last b-page__ipad b-page__iphone"><a class="b-menu__link" href="/profi/"><span class="b-menu__b1">PROFI</span></a></li>
				<li class="b-menu__item b-menu__item_padbot_null b-page__desktop"><a class="b-menu__link" href="/profi/"><span class="b-icon b-icon__profi b-icon_valign_bas" data-profi-txt="Лучшие фрилансеры сайта FL.ru. Работают на сайте более 2-х лет, прошли верификацию личности и имеют не менее 98% положительных отзывов."></span></a></li>
                <li class="b-menu__item b-menu__item_promo b-page__desktop"><?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?></li>
			</ul>
		</div>
		</div>
		</div>
        <div class="b-layout__txt b-layout__txt_padlr_20 b-layout__txt_padbot_20"><? if(hasPermissions('users') && !$admin): ?><a class="b-layout__link" href="/clients/?a=1">Admin mode</a><? endif; ?></div>
    <? if($admin): ?>
    <div class="c">
        <div class="form lnk-aclients-add" id="button" <? if($error): ?>style="display:none"<? endif; ?>>
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in"><a href="javascript:void(0)" onClick="$('newclient').setStyle('display', 'block'); $('button').setStyle('display', 'none');">Добавить клиента</a></div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        
        <div class="form form-cc" id="newclient" <? if(!$error): ?>style="display:none"<? endif; ?>>
            <b class="b1"></b>
            <b class="b2"></b>
            <form method="post" enctype="multipart/form-data">
            <div class="form-in">
                <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
                <input type="hidden" name="action" value="new_client" />
                <div class="form-block first last">
                    <div class="form-el">
                        <div class="f-cc-logo">
                            <div class="logo-canvas"></div>
                            <input type="file" name="logo" />
                            <?/*<a href="" class="lnk-upload">Загрузить</a>*/?>
                            <span>140х100px,<br />JPG, PNG, GIF<br />до 100Кб</span>
                        </div>
                        <div class="f-cc-desc">
                            <div>
                                <label>Название:</label>
                                <input type="text" name="name_client" value="<?=( $name ? stripslashes($name) : '' )?>" maxlength="128" />
                            </div>
                            <div>
                                <label>Ссылка:</label>
                                <input type="text" name="link_client" value="<?=( $link ? $link : '' )?>" maxlength="255"/>
                            </div>
                        </div>
                    </div>
                </div>
                
                <? if ($error) print(view_error($error)) ?>
                
                <div class="form-btns">
                    <input type="submit" value="Добавить" class="i-btn i-bold" />
                    <input type="button" value="Отменить" class="i-btn" onClick="$('newclient').setStyle('display', 'none'); $('button').setStyle('display', 'block');" />
				</div>
			</div>
			
			</form>
			<b class="b2"></b>
			<b class="b1"></b>
		</div>
		
    </div>
    <table class="catalog-aclients">
        <tbody>
            <col width="140" />
            <col width="170" />
            <col width="325" />
            <col width="65" />
            <? if($clients):?>
            <? foreach($clients as $client): ?>
            <tr>
                <? if($client['id'] == $edit): ?>
                <td colspan="4">
                    <a name="edit"></a>
                    <div class="form form-cc">
                        <b class="b1"></b>
                        <b class="b2"></b>
                        <form method="post" enctype="multipart/form-data">
                        <div class="form-in">
                            <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
                            <input type="hidden" name="action" value="edit_client" />
                            <input type="hidden" name="logo_tmp" value="<?=$client['logo']?>" />
                            <div class="form-block first last">
                                <div class="form-el">
                                    <div class="f-cc-logo">
                                        <div class="logo-canvas"><img src="<?=WDCPREFIX?>/clients/<?=$client['logo']?>" width="140" height="100" alt="<?=$client['name_client']?>" class="cat-logo" /></div>
										<input type="file" name="logo">
                                        <?/*<a href="" class="lnk-upload">Загрузить</a>*/?>
										<span>140х100px,<br />JPG, PNG, GIF<br />до 100Кб</span>
									</div>
									<div class="f-cc-desc">
									   <div>
									       <label>Название:</label>
									       <input type="text" name="name_client" value="<?=$action == 'edit_client' ? stripslashes($name) : stripslashes($client['name_client'])?>" />
									   </div>
									   <div>
									       <label>Ссылка:</label>
									       <input type="text" name="link_client" value="<?=$action == 'edit_client' ? $link : $client['link_client']?>" />
									   </div>
									</div>
                                </div>
                            </div>
                            
                            <? if ($error_edit) print(view_error($error_edit)) ?>
                            
                            <div class="form-btns">
                                <input type="submit" value="Применить" class="i-btn i-bold" />
								<input type="button" value="Отменить" class="i-btn" onClick="document.location = '/clients/?a=1<?=$upage?>'"/>
							</div>
						</div>
						</form>
						<b class="b2"></b>
						<b class="b1"></b>
					</div>
				</td>

                <? else: ?>
                <td><img src="<?=WDCPREFIX?>/clients/<?=$client['logo']?>" width="140" height="100" alt="<?=$client['name_client']?>" class="cat-logo" /></td>
                <td><strong><?=$client['name_client']?></strong></td>
                <td><a href="<?=$client['link_client']?>" target="_blank"><?=$client['link_client']?></a></td>
                <td>
                    <a href="/clients/?a=1&edit=<?=$client['id'].$upage?>#edit"><img src="/images/btn-edit4.png" alt="Редактировать" /></a>
			        <a href="/clients/?a=1&delete=<?=$client['id'].$upage?>" onClick="if(confirm('Вы действительно хотите удалить клиента?')) { return true; } else { return false; }"><img src="/images/btn-remove4.png" alt="Удалить" /></a>
			    </td>
			    <? endif; ?>
			</tr>
            <? endforeach; ?>
            <? else: ?>
            <tr>
                <td colspan="4"><div class="form form-cc">Нет ни одного клиента</div></td>
			</tr>
            <? endif; ?>
            
			
        </tbody>
    </table>
    
    <?
    $pages = ceil($count / $limit);
    $sHref = "%s?a=1&page=%d%s";
    print new_paginator($page, $pages, 3, $sHref);
    ?>
    <? else: ?>
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0">
            <? if($clients): ?>
                <? $i=0; $j=0; $ccnt=count($clients); foreach($clients as $client): ++$j; if($i>=3) $i = 0;?>
                <?=($i==0?'<tr class="b-layout__tr">':'');$i++;?>
                    <td class="b-layout__td b-layout__td_width_33ps b-layout__td_center b-layout__td_padtb_20 <?=($ccnt-$j<3 ? '' : 'b-layout__td_bordbot_c3') ?>"><noindex><a class="b-layout__link" href="<?=$client['link_client']?>" target="_blank" title="<?=$client['name_client']?>" rel="nofollow"><img class="b-layout__pic" src="<?=WDCPREFIX?>/clients/<?=$client['logo']?>" width="140" height="100" alt="<?=$client['name_client']?>" /></a></noindex></td>
                <?=($i==3?"</tr>":"")?>  
                <? endforeach; ?>
                <? if($i<3): ?>
                    <? for($k=$i;$k<3;$k++) { echo "<td class='b-layout__td b-layout__td_padtb_20 b-layout__td_width_33ps'>&#160;</td>"; } echo "</tr>";?>
                <? endif; ?>
            <? else: ?>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padtb_20 b-layout__td_center" colspan="3"><div class="b-layout__txt">Нет ни одного клиента</div></td>
            </tr>
            <? endif; ?>
    </table>
    <? endif; ?>
</div>

<div class="b-layout__left b-layout__left_width_25ps b-layout__right_margleft_3ps b-layout__right_float_left">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->
    <? if(!get_uid() && $prof_descr != ''): ?>
    <div class="main-text-seo">
        <b class="b1"></b>
        <b class="b2"></b>
		<div class="main-text-seo-in"> <?=$prof_descr?></div>
		<b class="b2"></b>
		<b class="b1"></b>
	</div>
    <? endif; ?>
</div>
<style type="text/css">
@media screen and (max-width: 1000px){
.b-layout__page .b-layout__left .b-catalog{ top:20px;}
.b-layout__title{ margin-right:150px !important;}
.b-layout__right{ width:100% !important;}
.b-layout__page .body .main td.b-layout__td{ display:table-cell;}
.b-layout__page .body .main tr.b-layout__tr{ display:table-row;}
.b-layout__page .body .main table.b-layout__table{ display:table;}
}
@media screen and (max-width: 500px){
.b-layout__page .body .main td.b-layout__td, .b-layout__page .body .main tr.b-layout__tr, .b-layout__page .body .main table.b-layout__table{ display: block; width:100%;}
}
@media screen and (max-width: 400px){
.b-layout__title{ font-size:18px !important;}
}
</style>