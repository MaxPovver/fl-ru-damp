<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
       
$page = trim($_GET['page']);

if (!$page) {
    $page = trim($_POST['page']);
}
if (!$page) {
    $page = 1;
}
                
function drawStars($mode, $id) {
    $html = '';
    for ($ii = 1; $ii <= 10; $ii++) {
        $html .= '<b onclick="setStar(\'' . $mode . '\',\'' . $id . '\',' . $ii . ')"></b><span>';
    };
    for ($ii = 10; $ii >= 1; $ii--) {
        $html .= '</span>';
    }
    return $html;
}
?>

<script type="text/javascript">
    var opinion_max_length  = <?php echo opinions::$opinion_max_length ?>;
    var comment_max_length  = <?php echo opinions::$comment_max_length ?>;
    var opinion_error_limit = '<? print(ref_scr(view_error('Исчерпан лимит символов для поля (??? символов)'))); ?>';
    var opinion_error_empty = '<? print(ref_scr(view_error('Поле не должно быть пустым!'))); ?>';
    var opinion_error_rating = '<? print(ref_scr(view_error('Выберите характер мнения.'))); ?>';
    var comment_error_empty = '<? print(ref_scr(view_error('Комментарий не должен быть пустым!'))); ?>';
</script>

<? if ($ops_type == 'total') {

    include_once(dirname(__FILE__).'/opinions/tpl.total_opinions.php');
} elseif ($ops_type == 'norisk') {
    if($is_transfer) {
        include_once(dirname(__FILE__).'/tpl.advice-edit.php');
    } else if($edit === 0) {
        include_once(dirname(__FILE__).'/tpl.advices.php');
    } else if($edit > 0) {
        include_once(dirname(__FILE__).'/tpl.advice-edit.php');
    } 
    
} else { // НЕ СБР ОТЗЫВЫ?>
    <div id="messages_container">
        <? include_once(dirname(__FILE__).'/opinions/tpl.opinions.php');?>
    </div>

    <?
    // Страницы
    $pages = ceil($num_msgs / $blogspp);
    if ($pages > 1) {
        ?>
                            <table width="100%" cellspacing="0" cellpadding="0" >
                                <tr>
                                    <td style="width:19px">&nbsp;</td>
                                    <td align="right">
                                        <table  cellspacing="1" cellpadding="0" class="pgs">
                                            <tr><?
                                        for ($i = 1; $i <= $pages; $i++) {
                                            if ($i != $page) {
                                                ?>
                                                        <td><a href="/users/<?= $user->login ?>/opinions/?page=<?= $i ?><? if ($t)
                                                    print "&t=$t" ?>" class="pages"><?= $i ?></a></td>
            <? }
            else { ?>
                                                        <td class="box"><?= $i ?></td>
            <? }
        } // Страницы закончились ?></tr>
                                        </table>
                                    </td>
                                    <td  style="width:19px">&nbsp;</td>
                                </tr>
                            </table>
    <? } ?>
<?php } ?>
