<?
    if(is_emp()) {
        $pro_ico = view_pro_emp();
    }else {
        if (strtotime($_SESSION['pro_last']) > time() && $_SESSION['pro_test'] == "t")	$is_pro_test = true;
        else $is_pro_test = false;
        $pro_ico = view_pro2($is_pro_test);
    }

    $login_val = ($_SESSION['anti_uid']) ? $_SESSION['anti_login'] : 'Логин';
?>

<script type="text/javascript">
function change_au() {
    qu = $("asw_form").toQueryString();
    _action = 'switch';
    _redirect = $('redirect_au').get('value');
    $('asw_form').getElements('input').setProperty("disabled", true);
    login = $('asw_form').getElement('input[name=a_login]').value;
    if(login != '<?=$login_val?>') {
       _action = 'change_au';
    }
    new Request.JSON({
        url: "<?=$host?>/",
        data: qu + "&action=" + _action + "&redirect=" + _redirect,
        onSuccess: function(resp){
            $('asw_form').getElements('input').setProperty("disabled", false);
            if(resp) {
                if($chk(resp.redir)) {
                    document.location.href = resp.redir;
                } else if(resp.success) {
                    document.location.reload();
                } else {
                    resp=null;
                }
            }
            if(!resp)
                $('asw_form').getElements('input').addClass('n-ub-err');
        }
    }).post();
}
window.addEvent('domready', function() {
   $('asw_form').getElements('input').addEvent('focus', function() {
       this.removeClass('n-ub-err');
   });
});
</script>
        <span class="b-userbar__switcher">
			<a id="l-switch" class="b-userbar__toplink" href="javascript:void(0);"><i class="b-userbar__icsw"></i>Аккаунт <?=!is_emp() ? 'работодателя' : 'фрилансера'?></a>
		</span>
		
		
		
		
        <? if($_SESSION['pro_last'] || is_emp() || $_SESSION['anti_pro_last']) { ?>
		<div id="b-switch" class="b-userbar__switchblock">
			<b class="b-userbar__b2"></b>
			<form id="asw_form" class="b-userbar__frmtransit" onsubmit="change_au(); return false" method="post" action="/">
				<div>
					<input type="hidden" name="redirect" id="redirect_au" value="<?= urlencode($_SERVER['REQUEST_URI']);?>" />
					<label class="b-userbar__transloginlabel" for="b-userbar__transitlogin"><i class="b-userbar__icsw"></i></label>
					<input id="b-userbar__transitlogin" class="b-userbar__transitlogin" type="text" name="a_login" value="<?=$login_val?>" onfocus="if(this.value=='Логин'){this.value='';}" onblur="if(this.value==''){this.value='Логин';}" />
					<input  id="b-userbar__transitpswrd" class="b-userbar__transitpswrd"  type="password" name="passwd" value="******" onfocus="if(this.value=='******') {this.value='';}" onblur="if(this.value==''){this.value='******';}" />
					<a id="l-cancel" class="b-userbar__toplink" href="">Отмена</a>
					<input class="b-userbar__transition" type="submit" value="Перейти" />
				</div>
			</form>
		</div>
        <? } ?>


<?/*
<? if (!$_SESSION['pro_last']) { ?>
  <a href="javascript: void(0)" onclick="acc_toggler();" class="lnk-acc-change"><span>Аккаунт</span></a>&nbsp;&mdash;&nbsp;Начальный. <a href="/payed" class="payed-lnk">Купить Pro</a>
<? } else { ?>
  <a href="javascript: void(0)" onclick="acc_toggler();" class="lnk-acc-change"><? if($_SESSION['pro_last']) { ?><span>Аккаунт</span></a>&nbsp;&mdash;&nbsp;<?=$pro_ico." <b>(".pro_days($_SESSION['pro_last']).")</b>"?><? } ?>
<? } ?>
<? if ($_SESSION['pro_last'] || is_emp()) { ?>
    <div class="mb-change-menu" id="acc-change">
        <form action="/" method="post" id="asw_form">
        <div>
            <ul>
                <? if(!is_emp()) { ?>
                  <li class="mb-lancer-act" id="mb-lancer">
                       <span>Фрилансер<br /><strong><?=$_SESSION['name'].' '.$_SESSION['surname']?> [<?=$_SESSION['login']?>]</strong></span>
                  </li>
                  <? if($_SESSION['anti_uid']) { ?>
                       <li class="mb-employer" id="mb-employer">
                            <span>Работодатель<br/><strong><a href="javascript:asw_subm('switch')"><?=$_SESSION['anti_name'].' '.$_SESSION['anti_surname']?> [<?=$_SESSION['anti_login']?>]</a></strong></span>
                            <span class="mb-change-lnk"><a href="javascript:void(0)" onclick="emp_acc_exit();">Изменить</a></span>
                       </li>
                  <? }else{ ?>
                       <li class="mb-employer-add">
                           <span>
                               <span class="mbc-fl">
                                   <label for="fl2" onclick="this.nextSibling.focus();" class="fl">Логин</label><input id="fl2" name="a_login" type="text" class="mba-str" onfocus="clean(this)" onkeydown="if(event.keyCode==13)asw_subm('change_au')" />
                               </span>
                               <span class="mbc-fl">
                                   <label for="fp2" onclick="this.nextSibling.focus();" class="fp">Пароль</label><input id="fp2" name="passwd" type="password" class="mba-str" onfocus="clean(this)" onkeydown="if(event.keyCode==13)asw_subm('change_au')" />
                               </span>
                               <span class="lnc-add-acc"><a href="javascript:asw_subm('change_au')">Добавить аккаунт</a></span>
                           </span>
                       </li>
                  <? } ?>
                <? } else { ?>
                  <? if($_SESSION['anti_uid']) { ?>
                       <li class="mb-lancer" id="mb-lancer">
                            <span>Фрилансер<br/><strong><a href="javascript:asw_subm('switch')"><?=$_SESSION['anti_name'].' '.$_SESSION['anti_surname']?> [<?=$_SESSION['anti_login']?>]</a></strong></span>
                            <span class="mb-change-lnk"><a href="javascript:void(0)" onclick="lancer_acc_exit();">Изменить</a></span>
                       </li>
                  <? }else{ ?>
                       <li class="mb-lancer-add">
                           <span>
                               <span class="mbc-fl">
                                   <label for="fl2" onclick="this.nextSibling.focus();" class="fl">Логин</label><input id="fl2" name="a_login" type="text" class="mba-str" onfocus="clean(this)" onkeydown="if(event.keyCode==13)asw_subm('change_au')" />
                               </span>
                               <span class="mbc-fl">
                                   <label for="fp2" onclick="this.nextSibling.focus();" class="fp">Пароль</label><input id="fp2" name="passwd" type="password" class="mba-str" onfocus="clean(this)" onkeydown="if(event.keyCode==13)asw_subm('change_au')" />
                               </span>
                               <span class="lnc-add-acc"><a href="javascript:asw_subm('change_au')">Добавить аккаунт</a></span>
                           </span>
                       </li>
                  <? } ?>
                  <li class="mb-employer-act" id="mb-employer">
                       <span>Работодатель<br /><strong><?=$_SESSION['name'].' '.$_SESSION['surname']?> [<?=$_SESSION['login']?>]</strong></span>
                  </li>
                <? } ?>
            </ul>
            <input type="hidden" name="action"/>
        </div>
        </form>
    </div>
<? } else { ?>
    <div class="mb-change-menu" id="acc-change">
         <ul>
              <li class="mb-nopro"><span>Быстрое перемещение между аккаунтами доступно только для владельцев <?=view_pro()?></span></li>
         </ul>
    </div>
<? } 
*/