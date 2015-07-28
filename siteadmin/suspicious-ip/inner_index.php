<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<h3>IP с которых производилось <?=users::MAX_REG_IP?> регистраций в сутки</h3>

<?php if ( $nTotal ): ?>
<table cellpadding="5" cellspacing="1" border="0" style="background-color: #cdcdcd; border-collapse: separate;">
<?php foreach ( $aRecords as $aOne ): ?>
<tr style="background-color: #fff;">
    <td style="padding: 10px;">
        <h2><?=$aOne['reg_ip']?></h2><br/>
        <?=$aOne['reg_date']?>
    </td>
    <td style="padding: 10px;">
        <?php if ($aOne['users']): ?>
        <?php foreach ( $aOne['users'] as $aUser ): ?>
        <a class="frlname11" <?=($aUser['is_banned'] ? 'style="color:#C0C0C0 !important"' : '')?> href="/users/<?=$aUser['login']?>/" title="<?=$aUser['uname']?> <?=$aUser['usurname']?>"><?=$aUser['uname']?> <?=$aUser['usurname']?> [<?=$aUser['login']?>]</a><br>
        <?php endforeach; ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?
$pages = ceil($nTotal / RECORDS_ON_PAGE);
$sHref = "%s?".  preg_replace("/&?page=[0-9]+/", "", $_SERVER['QUERY_STRING'])."&page=%d%s";
echo new_paginator($page, $pages, 3, $sHref);
?>