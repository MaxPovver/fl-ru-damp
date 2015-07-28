<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
	$stc = new static_compress;
	$stc->Add("/scripts/mootools-new.js");
	$stc->Add( '/scripts/raphael-min.js' );
    $stc->Add( '/scripts/svg.js' );
    
    if ($css_file) { $stc->Add("/css/".$css_file);}
    
    $stc->Send();

	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/rating.common.php");
	$xajax->printJavascript('/xajax/');
?>
<script>
    window.addEvent('domready', function() {
        
        xajax_GetRating('month');
        document.getElement('select[name=ratingmode]').addEvent('change', function() {
            xajax_GetRating(this.get('value'));
        });
    });
</script>

<div class="month-rate-graph" style="padding:0;margin:0;">
    <h3 class="b-page__iphone">√рафик изменений рейтинга</h3>
    <select name="ratingmode">
        <option value="month">в этом мес€це</option>
        <option value="prev">в прошлом мес€це</option>
        <option value="year">за год</option>
    </select>
    <h3 class="b-page__desktop b-page__ipad">√рафик изменений рейтинга</h3>
    <div id="raph"></div>
</div>