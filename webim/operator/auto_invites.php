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
$TITLE_KEY = 'leftMenu.auto_invites';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');


require_once('../classes/functions.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/models/generic/class.mapperfactory.php');


Operator::getInstance()->IsCurrentUserAdminOrRedirect();

$TML = new SmartyClass($TITLE_KEY);

$TML->assign('invites', MapperFactory::getMapper("AutoInvite")->getAll());

$TML->display('auto_invites.tpl');
?>