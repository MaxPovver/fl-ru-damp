<?php
/**
scid:52128
ShopID:17004
Sum:780
customerNumber:481255
paymentType:WM
 */

/*
<input name="shopId" value="1234" type="hidden"/>
<input name="scid" value="4321" type="hidden"/>
<input name="sum" value="100.50" type="hidden">
<input name="customerNumber" value="abc000" type="hidden"/> 
 */

//$_GET['test'] = 1;

$url = isset($_GET['test'])?'https://demomoney.yandex.ru/eshop.xml':
                            'https://money.yandex.ru/eshop.xml';


?>
<form method="post" action="<?=$url?>">
    <input name="scid" value="8420" type="hidden" />
    <input name="shopId" value="17004" type="hidden" />
    <input name="sum" value="780" type="hidden" />
    <input name="customerNumber" value="481255" type="hidden" />
    <!-- <input name="paymentType" value="CD" type="hidden" /> -->
    
    <input type="submit" value="Купить" />
</form>