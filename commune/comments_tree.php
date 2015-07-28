<script type="text/javascript">
    commune_config = {
        poll_id: 'idEditCommentForm_<?= $top['id'] ?>',
        poll_max: <?= commune::POLL_ANSWERS_MAX ?>,
        session: '<?= $_SESSION['rand'] ?>',
        question_max_char: <?= commune::POLL_QUESTION_CHARS_MAX ?>,
    };
</script>
<?
if ($len = count($tree))
    list($nidx, $nlevel) = split(":", $tree[0]);

if ($len) {
?>
    <script>var __commCCnt=<?= $len ?></script>


<? } ?>


<?= $comments_html ?>
