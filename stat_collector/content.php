<?  
  if(!hasGroupPermissions('administrator') && $_SESSION['login']!='sll') { header('Location: /404.php'); exit; }
  $scl = new stat_collector(TRUE);
  switch($_GET['step']) {
    case 'all': $scl->Run(); break;
    case 1: $scl->Step1(); break;
    case 2: $scl->Step2(); break;
    case 3: $scl->Step3(); break;
    case 4: $scl->Step4(); break;
    case 5: $scl->Step5(); break;
    case 6: $scl->Step6(); break;
    case 'crStatLog': $scl->tmp__crStatLog(); break;
  }
  
  switch ( $_GET['words_step'] ) {
      case 'all': $scl->wordsStatRun(); break;
      case 1: $scl->wordsStatStep1(); break;
      case 2: $scl->wordsStatStep2(); break;
      case 3: $scl->wordsStatStep3(); break;
  }
?>
  <h1>–учной перегон статистики по таблицам постгреса</h1>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr valign="top">
      <td bgcolor="#FFFFFF" class="box">
        <div style="padding:20px 0 0 20px">
          <h1>—татистика посещений пользовательских страниц</h1>
          ¬се и сразу (рекомендуетс€ при отсутсвии критических сбоев):<br/><br/>
          <a href="?step=all">stat_collector::Run()</a><br/><br/><br/><br/>
          ѕошагово:<br/><br/>
          <a href="?step=1">stat_collector::Step1()</a><br/>
          <a href="?step=2">stat_collector::Step2()</a><br/>
          <a href="?step=3">stat_collector::Step3()</a><br/>
          <a href="?step=4">stat_collector::Step4()</a><br/>
          <a href="?step=5">stat_collector::Step5()</a><br/>
          <a href="?step=6">stat_collector::Step6()</a>
        </div><br/><br/>
      </td>
    </tr>
    <tr valign="top">
      <td bgcolor="#FFFFFF" class="box">
        <div style="padding:20px 0 0 20px">
            <h1>—татистика по ключевым словам</h1>
            ¬се и сразу (рекомендуетс€):<br/><br/>
            <a href="?words_step=all">stat_collector::wordsStatRun()</a><br/><br/><br/><br/>
            
            ѕошагово (дл€ тестировани€):<br/><br/>
            <a href="?words_step=1">stat_collector::wordsStatStep1() - пересчет почасовой статистики</a><br/><br/>
            <a href="?words_step=2">stat_collector::wordsStatStep2() - почасовой пересчет общей статистики</a><br/><br/>
            <a href="?words_step=3">stat_collector::wordsStatStep3() - пересчет ежедневной статистики</a><br/><br/>
        </div><br/><br/>
      </td>
    </tr>
    <tr>
        <td>
        <pre style="color:blue;padding:20px"><? $scl=NULL; ?></pre>
        </td>
    </tr>
  </table>
