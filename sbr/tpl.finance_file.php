<?
$params['maxsize'] = account::MAX_FILE_SIZE;
$params['maxfiles'] = account::MAX_FILE_COUNT;
$params['graph_init'] = false;
$params['css_class'] = $params['css_class'] != '' ? $params['css_class'] : "b-file_padleft_183 b-file_padbot_20";
?>

    
<div class="b-file <?= $params['css_class']?>" id="attach_<?= $fileCategory ?>">
    <?= $attachedFiles->getFormTemplate("attachedfiles_finance", $fileCategory, $params) ?>
    <script>
        window.addEvent('domready', function(){
            var attachBlock = $('attach_<?= $fileCategory ?>');
            //attachBlock.setStyle('display', '');
            var attachedFiles = new attachedFiles2(
                attachBlock,
                {
                    session: '<?= $attachedFiles->session[0] ?>',
                    hiddenName: "<?= $fileCategory ?>[]",
                    files: <?= json_encode($attached) ?>
                },
                '<?= $attachedFiles->session[0] ?>'
            );
            <?php if ($params['error']): ?>
            attachedFiles.raiseError('<?=$params['error']?>');
            <?php endif; ?>
        });
    </script>
</div>
