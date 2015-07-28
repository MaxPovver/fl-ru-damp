<? foreach ($gifts as $i => $gift) {
    $link = '/users/' . $gift['login'] . '/';
?>
    <div class="b-fon b-fon_width_full b-fon_padbot_10 last-gift-block<?= $i > 0 ? " b-fon_hide" : "" ?>" id="last_gift<?= $gift['id'] ?>">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
            <div class="b-fon__txt b-fon__txt_center b-username">
                <? if ($gift['op_code'] == 23) { // перевод ?>
                    <span class="b-icon b-icon_mid_f b-icon_valign_middle"></span>
                    Пользователь 
                    <a class="b-username__link" href="<?= $link ?>"><?= $gift['uname'] . ' ' . $gift['usurname'] ?></a> 
                    <span class="b-username__login b-username__login_color_fd6c30">[<a class="b-username__link b-username__link_color_fd6c30" href="<?= $link ?>"><?= $gift['login'] ?></a>]</span>
                    перевел<?= $gift['sex'] == 'f' ? 'а' : '' ?> вам 
                    <span class="b-fon__txt b-fon__txt_bold"><?= round($gift['ammount'],2) ?> руб.</span> &nbsp;&nbsp;
                <? } else { // подарок ?>
                    <span class="b-icon b-icon_mid_gift b-icon_valign_middle"></span>
                    <?php if ( $gift['login'] != 'admin' ) {?>
                        Пользователь 
                        <a class="b-username__link" href="<?= $link ?>"><?= $gift['uname'] . ' ' . $gift['usurname'] ?></a> 
                        <span class="b-username__login b-username__login_color_fd6c30">[<a class="b-username__link b-username__link_color_fd6c30" href="<?= $link ?>"><?= $gift['login'] ?></a>]</span> 
                        подарил<?= $gift['sex'] == 'f' ? 'а' : '' ?> вам 
                    <?php }?>
                    <? switch ($gift['op_code']) {
                        case 16:
                        case 52:
                            $text1 = 'аккаунт PRO';
                            $count = - $gift['ammount_from'] / (is_emp() ? 10 : 19);
                            $text2 = ' на ' . $count . ' ' . ending($count, 'месяц', 'месяца', 'месяцев') . '.';
                            break;
                        case 69:
                            $text1 = 'платное место в каталогах сайта';
                            break;
                        case 17:
                            $text1 = 'платное место на главной странице';
                            $count = - $gift['ammount_from'] / 150;
                            $text2 = ' на ' . $count . ' ' . ending($count, 'месяц', 'месяца', 'месяцев') . '.';
                            break;
                        case 83:
                            $text1 = 'платное место наверху каталога';
                            break;
                        case 84: // во всем каталоге
                            $text1 = 'платное место в каталоге';
                            $count = - $gift['ammount_from'] / 25;
                            $text2 = ' на ' . $count . ' ' . ending($count, 'неделю', 'недели', 'недель') . '.';
                            break;
                        case 85: // в каком-то разделе
                            $text1 = 'платное место в каталоге';
                            $count = - $gift['ammount_from'] / 10;
                            $text2 = ' на ' . $count . ' ' . ending($count, 'неделю', 'недели', 'недель') . '.';
                            break;
                        case 115: // 
                            $text1 = 'Вы активировали подарок - профессиональный аккаунт на 1 неделю. Воспользуйтесь расширенными возможностями PRO.';
                            if ( is_emp() ) {
                                $text1 = 'Вы активировали подарок - профессиональный аккаунт на 1 месяц. Воспользуйтесь расширенными возможностями PRO';
                            }
                            $count = 1;
                            $text2 = '';
                            break;
                            //Спасибо, что воспользовались Сбербанком/WebMoney при пополнении счета. Ваш подарок - ХХХ рублей. 
                        case 95: 
                        case 96: 
                        case 97:
                        case 100:
                            include_once $_SERVER["DOCUMENT_ROOT"]."/classes/op_codes.php";
                            include_once $_SERVER["DOCUMENT_ROOT"]."/classes/payed.php";
                            $op_codes = new op_codes();
                            if ( $gift['op_code'] == 95 ) {
                                $n = $op_codes->GetField(is_emp() ? 15 : 48, $err, "sum") * 300;
                                if(is_emp()) $n = payed::PRICE_EMP_PRO;
                            } elseif ( $gift['op_code'] == 96 || $gift['op_code'] == 100) {
                                $n = $op_codes->GetField(is_emp() ? 15 : 48, $err, "sum") * 300;
                                if(is_emp()) $n = payed::PRICE_EMP_PRO;
                            } elseif ( $gift['op_code'] == 97 ) {
                                if ( !is_emp() ) {
                                    $n = $op_codes->GetField(17, $err, "sum") * 30;
                                } else {
                                    $n = 2550;
                                }
                            }
                            $text1 = "Спасибо, что воспользовались банковским переводом при пополнении счета. Ваш подарок - $n рублей.";
                            $count = 1;
                            $text2 = '';
                            break;
                        case 91: 
                        case 93:
                            include_once $_SERVER["DOCUMENT_ROOT"]."/classes/op_codes.php";
                            $op_codes = new op_codes();
                            if ( $gift['op_code'] == 91 ) {
                                $n = $op_codes->GetField(48, $err, "sum") * 300;
                            } elseif ( $gift['op_code'] == 93 ) {
                                if ( !is_emp() ) {
                                    $n = $op_codes->GetField(17, $err, "sum") * 30;
                                } else {
                                    $n = 2550;
                                }
                            } 
                            $text1 = "Спасибо, что воспользовались WebMoney при пополнении счета. Ваш подарок - $n рублей.";
                            $count = 1;
                            $text2 = '';
                            break;
                        default:
                            $text1 = $gift['op_name'];
                            break;
                    } ?>
                    <?php if ( $gift['op_code'] == 115 ) {?>
                    <span class="b-fon__txt b-fon__txt_bold"><?= $text1 ?></span><span class="b-fon__txt b-fon__txt_nowrap">
                    <?php } else {?>
                    <span class="b-fon__txt b-fon__txt_bold"><?= $text1 ?></span><?= $text2 ?> &nbsp;&nbsp;<span class="b-fon__txt b-fon__txt_nowrap">
                    <?php } ?>
                    <? if( in_array($gift['op_code'], array(16, 52, 91, 92, 95, 99, 96, 100)) ) { ?>
                    <a class="b-button b-button_rectangle_color_green"  href="javascript:void(0)" onclick="SetGiftResv(<?= $gift['id'] ?>)">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">Принять</span>
                            </span>
                        </span>
                    </a>&nbsp;&nbsp;
                    <? }//if?>
                    <? if ( $gift['login'] != "admin" ) {?>
                        <a class="b-fon__link b-fon__link_fontsize_11" href="/bill/gift/">Ответный подарок</a> &nbsp;&nbsp;<? if( in_array($gift['op_code'], array(16, 52, 91, 92, 95, 99, 96, 100)) ) { ?></span><? }//if?>
                    <? } ?>
                <? } ?>
                        
                <? if( !in_array($gift['op_code'], array(16, 52, 91, 92, 95, 99, 96, 100)) ) { ?>
                <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8 b-fon__link_fontsize_11" href="javascript:void(0)" onclick="SetGiftResv(<?= $gift['id'] ?>)">Закрыть</a></span>
                <? }//if?>
            </div> 
        </div>
    </div>
<? } ?>