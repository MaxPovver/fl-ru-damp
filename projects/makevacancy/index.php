<?php
require_once realpath(__DIR__ . '/../../classes/stdf.php');
require_once HOME.'/classes/projects.php';
require_once HOME.'/classes/smtp.php';
require_once HOME.'/classes/template.php';


$id = intval(__paramInit('int', 'id', 'id', 0));
$is_allow = (($id > 0) && hasPermissions('projects'));

if (!$is_allow) {
    header ('Location: /403.php');
    exit;    
}

$project = new_projects::initData(new_projects::getPrj($id));
if ($project->isAllowMovedToVacancy()) {
    // Делаем проект вакансией
    $project->movedToVacancy();

    // Отсылаем письмо заказчику о переносе его проекта в раздел вакансии
    $mail = new smtp();
    $mail->subject   = 'Ваш проект перенесен в раздел Вакансии и ожидает оплаты';  // заголовок письма
    $mail->message = Template::render(HOME.'/templates/mail/projects/makevacancy.tpl.php',array(
        'title' => $project->_project['name'],
        'project_id' => $project->_project['id'],
        'is_pro' => $project->isOwnerPro(),
        'pro_price' => $project->getProjectInOfficePrice(true),
        'not_pro_price' => $project->getProjectInOfficePrice(false)
    ));

    $mail->recipient = "{$project->_project['email']} <{$project->_project['email']}>"; // получатель
    $success = $mail->SmtpMail('text/html');
}


$ref_uri = (empty($_SESSION['ref_uri']))?'/':urldecode($_SESSION['ref_uri']);
header("Location: {$ref_uri}#project-item{$project->_project['id']}");
exit;