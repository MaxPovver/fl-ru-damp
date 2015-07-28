<table class="search-work">
    <tr>
        <?php foreach($result as $key=>$value) {
            $txt_cost = view_cost2($value['cost'], '', '', false, $value['cost_type']); 
            $txt_time = view_time($value['time_value'], $value['time_type']);
            $is_txt_time = ($txt_cost != '' && $txt_time != '');
        ?>
        <?php if($value) { $cls = "frlname11"; $value['role'] = '000000';?>
        <td class="cell-work-item">
		    <?= view_avatar($value['login'], $value['photo'], 1, 0, "b-pic b-pic_fl")?>
            <div style="margin-left:60px;">
              <span class="search-work-user">
                  <?=$session->view_online_status($value['login'])?><a href="/users/<?= $value['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank" class="<?=$cls?>"><?= "{$value['mark_uname']} {$value['mark_usurname']} [{$value['mark_login']}]"?></a> <?= view_mark_user($value)?> 
              </span>
              <h3><a href="/users/<?= $value['login']?>/viewproj.php?prjid=<?= $value['id']?>" target="_blank"><?=strip_tags($value['mark_name'], '<em><br>')?></a></h3>
              <?php if($value['prev_pict'] != '') {?>  
              <a href="/users/<?= $value['login']?>/viewproj.php?prjid=<?= $value['id']?>" target="_blank"><?=view_preview($value['login'], $value['prev_pict'], "upload", 'center', false, false, htmlspecialchars($value['name']), 200)?></a>
              <?php } //if?>
              <p><?= strip_tags($value['mark_descr'], "<em><br>")?></p>
              <p class="search-work-price"><span><?= $txt_cost?></span><?= ($is_txt_time ? ", ":"") . ($txt_time != ''?$txt_time:"")?></p>
            </div>
	    </td>
	    <? } //if?>
	    <?php } //foreach?>
	</tr>
</table>