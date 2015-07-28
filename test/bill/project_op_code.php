<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");

$uid = get_uid(false);
$employer = new employer();
$employer->GetUserByUID($uid);

$account = new account();
$account->GetInfo($uid);
$_SESSION['ac_sum'] = $account->sum;
$_SESSION['ac_sum_rub'] = $account->sum_rub;
$_SESSION['bn_sum'] = $account->bonus_sum;


//Формируем проект
$tmpPrj = new tmp_project('key');
$tmpPrj->setEdit(true);
$tmpPrj->setProjectField('kind', 1);
$tmpPrj->setProjectField('descr', 'Описание проекта для оплаты с разными op_code');
$tmpPrj->setProjectField('name', 'Проект для оплаты с разными op_code');
$tmpPrj->setProjectField('agreement', 1);
$tmpPrj->setProjectField('priceby', 1);
$tmpPrj->setProjectField('budget_type', 1);
$cats[] = array(
    'category_id'   => 12,
    'subcategory_id'=> 120
);
$tmpPrj->setCategories($cats);

//Добавляем ему купленную опцию Срочный
$tmpPrj->setProjectField('urgent', 't');

//Другие платные услуги
if (false) {
    $tmpPrj->setAddedTopDays(4);
}

//Таким проект был в базе
$project = $tmpPrj->getProject();

//Юзер пытается купить скрытый
$tmpPrj->setProjectField('hide', 't');

//Записываются данные о скрытом и срочном. будут сохранены в кэш при $tmpPrj->fix()
$oproject = $project;
if ($tmpPrj->isEdit()) {
    $tmpPrj->setProjectField('o_hide', $oproject['hide']);
    $tmpPrj->setProjectField('o_urgent', $oproject['urgent']);
}


$tmpPrj->fix();

$account_sum = $account->sum;
$account_bonus_sum = $account->bonus_sum;

$bill = new billing($uid);
$bill->cancelAllNewAndReserved();

//Здесь впервые считается цена
if ($tmpPrj->getAmmount()) {
    $tmpProject = $tmpPrj->getProject();

    //Цена считается повторно, в $items формируется список услуг
    $price = $tmpPrj->getPrice($items, $__temp, true);
    $option = array(
        'is_edit' => $tmpPrj->isEdit(),
        'items' => $items,
        'prj_id' => $project['id'],
        'logo_id' => $logo['id'],
        'logo_link' => $tmpProject['link']
    );

    if ($items['top']) {
        $option['addTop'] = $tmpPrj->getAddedTopDays();
    }

    if ($tmpPrj->isKonkurs()) {
        if (new_projects::isNewContestBudget()) {
            $cost = $tmpPrj->getCostRub();
            $op_code = new_projects::getContestTaxOpCode($tmpPrj->getCostRub(), is_pro());
            $items['contest']["no_pro"] = $tmpPrj->isEdit() ? 0 : new_projects::getContestTax($cost, is_pro());
            $items['contest']["pro"] = $tmpPrj->isEdit() ? 0 : new_projects::getContestTax($cost, true);
        } else {
            //Здесь счет создастся при редактировании конкурса
            $items['contest']["no_pro"] = $tmpPrj->isEdit() ? 0 : 3300;
            $items['contest']["pro"] = $tmpPrj->isEdit() ? 0 : 3000;
            $op_code = is_pro() ? new_projects::OPCODE_KON : new_projects::OPCODE_KON_NOPRO;
        }
        $op_code_pay = new_projects::OPCODE_PAYED_KON;
    } else {
        $op_code = new_projects::OPCODE_PAYED;
        $op_code_pay = new_projects::OPCODE_PAYED;
    }

    if ($items) {
        $bill->start();


        // Конкурс
        if ($items['contest'] > 0) {
            $option['items'] = array('contest' => $items['contest']);
            $bill->setOptions($option);
            $success = $bill->create($op_code, 0 , false);
            $items['contest'] = 0;
        }

        // Создаем услуги по отдельности
        foreach ($items as $opt => $value) {

            if (is_array($value) && $value["no_pro"] <= 0) {
                continue;
            }
            if ($value <= 0) {
                continue;
            }

            /* Почему-то этот код здесь был. Не купятся услуги, если при редактировании не покупается логотип
             * Проверка на наличие ранее купленных должна быть именно тут, до создания счета
             * Делалось, видимо, чтобы деньги не списывались при редактировании, но отказ обрабатывать - не выход
              if($opt == 'hide' && $tmpPrj->isEdit()) {
              continue;
              }
              if($opt == 'urgent' && $tmpPrj->isEdit()) {
              continue;
              }
              if($opt == 'top' && $tmpPrj->isEdit()) {
              continue;
              } */

            $option['items'] = array($opt => $value);
            $bill->setOptions($option);

            //Берем код услуги и применяем его, если найден
            $ownOpCode = new_projects::getOpCodeByService($opt);
            if ($ownOpCode) {
                $op_code_pay = $ownOpCode;
            }

            $success = $bill->create($op_code_pay, 0 , false);
            if (!$success)
                break;
        }

        if (!$success) {
            $bill->rollback();
        } else {
            $bill->commit();
            // Сохраним данные проекта при этом убираем платные плюшки
            if ($tmpPrj->isEdit()) {
                if ($items['logo'] > 0) {
                    $tmpPrj->clearLogo();
                }

                if ($items['top'] > 0) {
                    $tmpPrj->setAddedTopDays(0);
                }

                $error = $tmpPrj->saveProject(hasPermissions('projects') ? $uid : NULL, $proj);
            }
            
            //Продолжаем на выход
            echo 'Счета созданы<br />';
        }
    }
}

if (!($error['buy'] = $tmpPrj->saveProject(hasPermissions('projects') ? $uid : NULL, $proj))) {
    //Здесь удаляем черновик
    //$drafts->DeleteDraft($draft_id, $uid, 1);

    echo 'Сохранено без платных опций<br />';
}                       
                        