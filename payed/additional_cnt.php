<h2>Дополнительные услуги</h2>

<? if($_POST['spec_sum']>0): ?>
Дополнительные специализации: <?=htmlspecialchars($_POST['spec_sum'])?> (<?=htmlspecialchars($_POST['spec_sum'])?> FM)<br/>
<? endif; ?>

<?if($_POST['rating_sum'] >0):?>
Рейтинг: +<?=htmlspecialchars($_POST['rating_sum'])?> (<?=htmlspecialchars($_POST['rating_sum'])?> FM)<br/>
<?endif;?>