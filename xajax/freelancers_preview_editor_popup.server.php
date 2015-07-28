<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/xajax/freelancers_preview_editor_popup.common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');


//------------------------------------------------------------------------------

/**
 * Сохранить выбор пользователя
 * 
 * @param type $data
 * @return \xajaxResponse
 */
function FPEP_saveProcess($data)
{
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    
    if ($uid > 0 && !is_emp() && is_pro()) {
        
        require_once(ABS_PATH . '/freelancers/models/FreelancersPreviewModel.php');
        $model = new FreelancersPreviewModel();
        $data['uid'] = $uid;
        
        if ($model->isValid($data)) {
            if ($model->save()) {
                require_once(ABS_PATH . '/freelancers/widgets/FreelancersPreviewWidget.php');
  
                $data = $model->getLastItem();
                if (count($data)) {
                    $item = $data->current();
                    $item->setUser(array('login' => $_SESSION['login']));
                    $widget = new FreelancersPreviewWidget(array('is_ajax' => true));
                    $widget->addItem($item);  
                    $html = $widget->render();
                    $objResponse->assign("preview_pos_{$model->getPos()}", 'innerHTML', $html);
                }
            }
        }

        $objResponse->call("window.popups_factory.getPopup('freelancersPreviewEditorPopup').close_popup");         
    }

    return $objResponse;
}


//------------------------------------------------------------------------------

/**
 * Получить станицу с выбором работ для таба
 * 
 * @param boolean $params
 * @return \xajaxResponse
 */
function FPEP_getTab($params)
{
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    
    if ($uid > 0 && !is_emp() && is_pro()) {
        
        $query = http_build_query($params);
        $params['is_ajax'] = true;
        
        require_once(ABS_PATH . '/freelancers/widgets/FreelancersPreviewEditorPopup.php');
        $freelancersPreviewEditorPopup = new FreelancersPreviewEditorPopup($params);
        $html = $freelancersPreviewEditorPopup->render();

        $objResponse->call("window.popups_factory.getPopup('freelancersPreviewEditorPopup').showTabContent",
                $freelancersPreviewEditorPopup->getCurrentTab(), $html, $query);
    }
    
    return $objResponse;
}


//------------------------------------------------------------------------------


$xajax->processRequest();