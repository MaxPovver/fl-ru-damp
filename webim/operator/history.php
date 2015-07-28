<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php 
$TITLE_KEY = 'page_analysis.search.title';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');


require_once('../classes/functions.php');
require_once('../classes/class.thread.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.pagination.php');


$TML = new SmartyClass($TITLE_KEY);
$tmlPage = null;

$operator = Operator::getInstance()->GetLoggedOperator();
$items_per_page = verify_param("items", "/^\d{1,3}$/", DEFAULT_ITEMS_PER_PAGE);
$show_empty = isset($_REQUEST['show_empty']) && $_REQUEST['show_empty'] == 1 ? true : false;

if (isset($_REQUEST['q'])) {
    $nTotal = Thread::getInstance()->GetListThreadsCount( $operator['operatorid'], $_REQUEST['q'], $show_empty );
    
    if ( $nTotal ) {
        $pagination = setup_pagination_cnt( $nTotal, $items_per_page );
        $nLimit     = $pagination['items'];
        $nOffset    = $pagination['start'];
        
        $res = Thread::getInstance()->GetListThreads( $operator['operatorid'], $_REQUEST['q'], $show_empty, $nLimit, $nOffset );
        
        $tmlPage['pagination'] = $pagination;
        $tmlPage['pagination_items'] = $res;
    }

  if (!empty($tmlPage['pagination_items'])) {
    $TML->assign('pagination', generate_pagination($tmlPage['pagination']));
  }
  
  $tmlPage['formq'] = $_GET['q'];
  $tmlPage['show_empty'] = $show_empty;
}


$TML->assign('advanced', false);
$TML->assign('page_settings', $tmlPage);
$TML->display('thread_search.tpl');

require_once(dirname(__FILE__).'/inc/admin_epilog.php');

?>