<form method="POST" action="<?=$url?>">
    <input name="scid" value="<?=$scid?>" type="hidden" />
    <input name="ShopID" value="<?=$shopId?>" type="hidden" />
    <input name="Sum" value="<?=$ammount?>" type=hidden />
    <input name="customerNumber" value="<?=$customerNumber?>" type="hidden" />
    <input name="paymentType" value="<?=$payment?>" type="hidden" />
    <?php if(isset($billReserveId)): ?>
    <input name="orderId" value="<?=$billReserveId?>" type="hidden" />
    <?php endif; ?>
</form>