<?php 
$groups_repeat = array(); 
if (isset($query_string_cat) && $query_string_cat) {
    $prof_suffix_url = '?'.$query_string_cat;   
}
?>

<?php if ($promo_profs || !$cur_prof): ?>
    <div class="b-cat">
        <?php if (!$promo_profs && !$cur_prof): ?>    
            <?php foreach ($profs as $prof): ?>
                <?php if (!isset($groups_repeat[$prof['grouplink']]) && ($groups_repeat[$prof['grouplink']] = 1)): ?>
                    <div class="b-cat__item">
                        <a class="b-cat__link" 
                            href="<?=htmlentities('/freelancers/'.$prof['grouplink'].'/'.$prof_suffix_url)?>">
                            <?=htmlspecialchars($prof['groupname'])?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php elseif($promo_profs): ?>
            <?php foreach ($promo_profs as $prof): ?>
                <div class="b-cat__item">
                    <a class="b-cat__link" 
                        href="<?=htmlentities('/freelancers/'.$prof['link'].'/'.$prof_suffix_url)?>">
                        <?=$prof['profname']?>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
