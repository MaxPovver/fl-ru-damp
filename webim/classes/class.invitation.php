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
require_once('common.php');
require_once('models/generic/class.mapperfactory.php');

class Invitation  {
  private static $instance = NULL;

  static function GetInstance() {
    if (self::$instance == NULL) {
      self::$instance = new Invitation();
    }
    return self::$instance;
  }

  public function CreateInvitation($threadid) {
    return MapperFactory::getMapper("Invitation")->save(
      array(
      	'state' => INVITATION_CAN_BE_SENT,
      	'threadid' => $threadid
      )
    );
  }

  public function UpdateInvitationMessage($threadid, $inviteMessageId) {
     MapperFactory::getMapper("Invitation")->updateInvitationMessageByThreadId($threadid, $inviteMessageId);
  }

  public function GetInvitationByVisitedPageId($visitedpageid) {
    $visitedpage = VisitedPage::GetInstance()->GetVisitedPageById($visitedpageid);
    if(!is_array($visitedpage) || !isset($visitedpage['invitationid']) || empty($visitedpage['invitationid'])) {
  		return null;
  	}
    
    $invitation = MapperFactory::getMapper("Invitation")->getById($visitedpage['invitationid']);

    return $invitation;
  }

  function GetInvitationState($visitedpageid) {
    $visitedpage = VisitedPage::GetInstance()->GetVisitedPageById($visitedpageid);    

    $state = INVITATION_UNINITIALIZED;
    if (!empty($visitedpage['invitationid'])) {
      $invitation = MapperFactory::getMapper("Invitation")->getById($visitedpage['invitationid']);
      $state = $invitation['state'];
    }
    return $state;
  }

  function GetInvitationById($invitationId) {
    return MapperFactory::getMapper("Invitation")->getById($invitationId);
  }
  
  function GetInvitationMessageById($messageId) {
    return MapperFactory::getMapper("Message")->getById($messageId);
  }
}
?>
