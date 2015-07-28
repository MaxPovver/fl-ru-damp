<?php

$maxlength = ($user['is_pro'] == 't')?55:35;
global $session;

?>
    <table class="b-layout__table">
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padright_35">
                <div class="b-user">
                    <a class="b-layout__link" href="/users/<?=$user['login']?>/" title="<?=($user['uname'] . ' ' . $user['usurname'])?>">
                        <?=view_avatar($user['login'], $user['photo'], 1, 1, 'b-user__pic')?>
                    </a>
                    <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padbot_5 b-layout__txt_nowrap b-layout__txt_lineheight_1">
                        <?=$session->view_online_status($user['login'])?>
                        <a class="b-layout__link b-layout__link_no-decorat b-layout__link_color_<?php if(is_emp($user['role'])){ ?>6db335<? } else { ?>000<?php } ?> b-layout__link_bold" href="/users/<?=$user['login']?>/">
                            <?php echo "{$user['uname']} {$user['usurname']} [{$user['login']}]" ?>
                        </a>
                        <?php echo view_mark_user2($user) ?>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_nowrap b-layout__txt_lineheight_1">
                        <?php if($oplinks){ ?>
                        Отзывы: 
                        <?php echo $oplinks['p'] ?>
                        <?php echo $oplinks['n'] ?>
                        <?php echo $oplinks['m'] ?>
                        <?php }else{ ?>
                        Отзывов пока нет
                        <?php } ?>
                    </div>
                    <div class="b-layout__txt b-layout__txt_nowrap b-layout__txt_lineheight_1">
                        <?php echo $user['place_title'] ?>
                    </div>
                </div>
            </td>
            <td class="b-layout__td">
                <?php if($user['skype']){ ?>
                <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_lineheight_1">
                    Skype: <?php echo reformat($user['skype'], 25, 0, 1)?>
                </div>
                <?php } ?>
                <?php if($user['second_email']){ ?>
                <div class="b-layout__txt b-layout__txt_lineheight_1">
                    Email: 
                    <a class="b-layout__link" href="mailto:<?php echo $user['second_email'] ?>">
                        <?= LenghtFormatEx($user['second_email'],$maxlength);?>
                    </a>
                </div>
                <?php } ?>
            </td>
        </tr>
    </table>

