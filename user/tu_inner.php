<?php
if(!defined('IN_STDF')) {
    header("HTTP/1.0 404 Not Found");
    exit;
}
?>
<div class="b-layout b-layout__cf b-layout_relative">
    <?php if($is_owner || $is_perm){ ?>
    <?php $tserviceOrderDebtMessageWidget->run(); ?>
    <?php echo tservices_helper::showFlashMessages() ?>
    <?php if($is_owner && @$_SESSION['tu_orders']): ?>
    <a href="<?=tservices_helper::url('frl_orders')?>" 
       class="b-button b-button_flat b-button_flat_green b-button_margbot_30 b-button_margtop_30">
        Перейти в заказы услуг
    </a>
    <?php endif; ?>
    <a href="<?php echo sprintf(tservices_helper::url('new'),$user->login); ?>" 
       class="b-button b-button_flat b-button_flat_green b-button_margbot_30 b-button_margtop_30" 
       onClick="yaCounter6051055.reachGoal('add_new_tu');">
        Добавить услугу
    </a>
    <?php } ?>
    <div class="b-layout b-layout_padtop_20 b-layout__cf">
    <?php if(count($data)){ ?>
        <?php foreach($data as $el){ 
            $url = sprintf('/tu/%d/%s.html',$el['id'],tservices_helper::translit($el['title']));
            $edit_url = sprintf('/users/%s/tu/edit/%d/',$user->login,$el['id']);
            $videos = (!empty($el['videos']))?mb_unserialize($el['videos']):array();
            $video = (count($videos))?current($videos):NULL;
        ?>
        <figure class="i-pic i-pic_port_z-index_inherit <?php if($user->is_pro=='f'){?>i-pic_tu<?php } else { ?>i-pic_tu-col-4<?php } ?>">

            <a href="<?php if($el['is_blocked'] == 't'){ echo 'javascript:void(0)'; } else { echo $url; }?>"
               class="b-pic__lnk b-pic__lnk_relative <?php if($el['is_blocked'] == 't'){ ?> b-pic__lnk_cursor_default<?php } ?>">
                <div class="b-pic__price-box">
                    <?php echo tservices_helper::cost_format($el['price'],true) ?>
                </div>
                <?php if(isset($video['image']) && !empty($video['image'])){ ?>
                <div class="b-icon b-icon__play b-icon_absolute b-icon_bot_14 b-icon_left_4"></div>
                <?php } ?>
                <?php if($el['file']){ ?>
                <img width="200" height="150" class="b-pic b-pic_margbot_10" src="<?php echo tservices_helper::image_src($el['file'],$user->login);?>">
                <?php }else{ ?>
                    <div class="b-pic b-pic_margbot_10 b-pic_no_img b-pic_w200_h150 b-pic_bg_f2"></div>
                <?php } ?>
            </a>

            <figcaption class="b-txt">
                <a href="<?php if($el['is_blocked'] == 't'){ echo 'javascript:void(0)'; } else { echo $url; }?>"
                   class="b-pic__lnk <?php if($el['is_blocked'] == 't'){ ?> b-pic__lnk_color_de2c2c b-pic__lnk_line_through b-pic__lnk_cursor_default<?php } ?>">
                    <?php echo LenghtFormatEx(reformat($el['title'], 20, 0, 1),80);?>
                </a>

                <?php if($is_owner || $is_perm){ ?>
                    <br/>
                    <?php if($el['is_blocked'] == 't'){ ?>
                        Заблокирована модератором

                        <?php if($el['reason']){ ?>
                            <a class="b-pic__lnk b-pic__lnk_dot_0F71C8 i-shadow __tooltip" href="javascript:void(0)">
                                Почему заблокирована?
                                <div class="b-shadow b-shadow_hide b-shadow_m b-shadow_width_230 b-shadow_top_20">
                                    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                        <div class="b-txt b-txt_fs_11 b-txt_color_eb0000 b-txt_bw">
                                            <?php echo $el['reason'] ?> 
                                        </div>
                                    </div>
                                    <span class="b-shadow__icon b-shadow__icon_nosik b-shadow__icon_left_50"></span>
                                </div>
                            </a>
                        <?php } ?>

                        <?php if($is_owner || $is_perm) { ?>
                            <a class="b-pic__lnk" href="<?php echo $edit_url; ?>" onClick="yaCounter6051055.reachGoal('republic_tu');">
                                Исправить и отправить снова
                            </a>
                        <?php } ?>

                    <?php }else{ ?>

                        <?php if($el['active'] == 'f'){ ?>Снята с публикации<?php } ?>

                        <?php if($is_owner || $is_perm){?> <!-- Ссылка на редактирование только владельцу -->
                            <a class="b-button b-button_admin_edit" href="<?php echo $edit_url; ?>"></a>
                        <?php } ?>

                    <?php } ?>
                <?php } ?>
            </figcaption>
        </figure>
        <?php } ?>
    <?php }else{ ?>
      <div class="b-txt b-txt_center b-txt_padtop_20 b-txt_padbot_40">
          Типовых услуг не найдено.
      </div>
    <?php } ?>
    </div>
</div>
<?php if(count($data)){ ?>
<div style="padding: 0 19px; margin: 19px 0;">
    <table cellpadding="0" cellspacing="0"  width="100%">
        <tr>
            <td style="width:100%" >
                <?php
                    $pages = ceil($cnt / $on_page);
                    echo new_paginator2($page, $pages, 4, "%s/users/{$user->login}/tu/?page=%d" . ($t ? "&amp;t=$t" : "") . "%s");
                ?>
            </td>
        </tr>
    </table>
</div>
<?php } ?>