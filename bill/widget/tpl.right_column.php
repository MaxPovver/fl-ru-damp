  <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.last_history.php"); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_help.php"); ?>
  <span class="walletRightBlock">
      <?php 
      $wallet = $bill->wallet;
      require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
      $u = new users();
      $u->GetUserByUID(get_uid('false'));
      ?>
      <?php if( $u->GetField(get_uid(false), $e, 'is_pro_auto_prolong', false)=='t' && WalletTypes::checkWallet($wallet) ) { ?>
          <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_wallet.php"); ?>
      <? } ?>
  </span>
