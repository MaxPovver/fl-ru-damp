<?php

//@todo: нужно переделать и использовать GaJsHelper
//проверить все варианты чтобы не делать повторные запросы!

$dimension4 = array();
if (isset($_SESSION['specs']) && count($_SESSION['specs'])) {
    $groups = array_unique(professions::GetGroupIdsByProfs($_SESSION['specs']));
    foreach ($groups as $group) {
        if ($group > 0) {
            $dimension4[] = '[g' . $group . ']';
        }
    }
    foreach ($_SESSION['specs'] as $prof) {
        if ($prof > 0) {
            $dimension4[] = '[p' . $prof . ']';
        }
    }
    
    GaJsHelper::getInstance()->gaSet('dimension4', implode(',', $dimension4));
}    

?>
<script type='text/javascript'>
    var _gaq = _gaq || [];

    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    <?php if (($ga_uid = get_uid(false)) > 0): ?>
        ga('create', '<?=GA_COUNTER_CODE?>', 'auto', {'userId': '<?=$ga_uid?>'});
        <?php GaJsHelper::getInstance()->gaSet('dimension5', $ga_uid); ?>
    <?php else: ?>
        ga('create', '<?=GA_COUNTER_CODE?>', 'auto');
    <?php endif; ?>

    ga(function (tracker) {
        var clientId = tracker.get('clientId');
        document.cookie = "_ga_cid=" + clientId + "; path=/";
        ga('set', 'dimension1', clientId);
    });

    <?=GaJsHelper::getInstance()->render();?>

    ga('require', 'displayfeatures');
    ga('send', 'pageview');
</script>
