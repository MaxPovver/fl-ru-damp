<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/tservices.common.php");
    $xajax->printJavascript('/xajax/');
    
    $user_profile_url = sprintf('/users/%s/',$user_obj->login);
?>
<style type="text/css">.b-icon__ver{ vertical-align:top;}</style>
<div class="b-layout b-layout_center b-layout_bord_c6">
        <div class="b-fon b-fon_bg_f2 b-layout b-layout_bordbot_dedfe0 b-layout_pad_10 b-layout_box">
            <a href="<?=$user_profile_url?>" class="b-layout__link"><?=view_avatar($user_obj->login, $user_obj->photo)?></a>
            <?=$session->view_online_status($user_obj->login)?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold b-layout__txt_inline-block b-layout__txt_inline-block">
                <a href="<?=$user_profile_url?>" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold" title="<?=($user_obj->uname." ".$user_obj->usurname)?>">
                    <?=($user_obj->uname." ".$user_obj->usurname)?>
                </a> [<a href="<?=$user_profile_url?>" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold" title="<?=$user_obj->login?>"><?=$user_obj->login?></a>]
                <?= view_mark_user(array(
                    "login"         => $user_obj->login, 
                    "is_profi"      => $user_obj->is_profi,
                    "is_pro"        => $user_obj->is_pro, 
                    "is_pro_test"   => $user_obj->is_pro_test, 
                    "is_team"       => $user_obj->is_team,
                    "role"          => $user_obj->role))
                ?>								
            </div> 
        </div>
        
        <div class="b-fon b-fon_pad_20 <?php if(isset($is_bg)){ ?>b-fon_bg_f2<?php } ?>">
            <?php if ($inner) include ($fpath . $inner); else print('&nbsp;') ?>
            
            <?php if(false){ ?>
                <?
                    $prevLink = ( $proj['prev_pict']||$proj['pict'] ) ? 
                    WDCPREFIX . '/users/' . $proj['login'] . '/upload/' . ($proj['prev_pict']?$proj['prev_pict']:$proj['pict']) :
                    $host . "/images/free-lance_logo.jpg";
                ?>
                    <div class="b-free-share"><?= ViewSocialButtons('viewproj', $proj['name'], true, true, null, null, $prevLink)?></div>
            <?php } ?>
        </div>
</div>

<?php
if(isset($tservicesPopular)): 
    $tservicesPopular->run(
            $category_spec_title? $category_spec_title : $category_group_title,
            $category_stitle
    );
endif; 
?>

<div class="b-layout">
    <h2 class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666 b-layout_top_100 b-layout__txt_padbot_10 b-layout__txt_weight_normal">
        <?php echo SeoTags::getInstance()->getFooterText() ?>
    </h2>
</div>

<?php
if($is_adm)
{
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/del_overlay.php' );
}