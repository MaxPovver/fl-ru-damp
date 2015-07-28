<?php

require_once(ABS_PATH . "/classes/vendors/UniversalAnalytics/vendor/autoload.php");
require_once(ABS_PATH . "/classes/statistic/Adapters/AbstractStatisticAdapter.php");

/**
 *  Адаптер для работы с сервисом Google Analitics с помощью сторонней библиотеки. 
 *  Здесь реализуются бизнес логика отправки статистики. 
 */
class StatisticAdapterGA extends AbstractStatisticAdapter
{
    /**
     * Инициализация сервиса
     */
    public function initService() 
    {
        $this->service = new \UniversalAnalytics\UA($this->options);
    }
    
    /**
     * Отправить статистику о количестве разосланных писем по рассылке проектов фрилансерам 
     * групированную по годам регистрации
     * 
     * @param array $data
     * @param int $timestamp
     * @return bool
     */
    public function newsletterNewProjectsFrl($data, $timestamp)
    {
        return $this->newsletterNewProjects('newsletter_new_projects_freelancer', $data, $timestamp);
    }
    
    
    /**
     * Отправить статистику о количестве разосланных писем по рассылке проектов работодателям 
     * групированную по годам регистрации
     * 
     * @param array $data
     * @param int $timestamp
     * @return bool
     */
    public function newsletterNewProjectsEmp($data, $timestamp)
    {
        return $this->newsletterNewProjects('newsletter_new_projects_employer', $data, $timestamp);
    }
    
    
    /**
     * Отправить статистику о количестве разосланных писем по рассылке проектов
     * 
     * @param string $category
     * @param array $data
     * @return bool
     */
    protected function newsletterNewProjects($category, $data, $timestamp)
    {
        $start_year = 2005;
        $default_label_value = array(/*'total' => 0,*/'new' => 0);
        $default_label_value += array_fill($start_year, date('Y') - $start_year + 1, 0);
        
        foreach($default_label_value as $label => $value)
        {
            $label_txt = (is_numeric($label))?sprintf($this->config->text('year'),$label):$this->config->text($label);
            if(empty($label_txt)) continue;
            
            $cnt = (isset($data[$label]))?$data[$label]:$value;
            
            $request = $this->service->event(array(
                'category'  => $this->config->text($category),
                'action'    => sprintf($this->config->text('sended'), date('d.m.Y',$timestamp)),
                'label'     => $label_txt,
                'value'     => $cnt
            ))->track();              
            
            $this->lastRequest = $request;
            
            $response = $request->send(false);
        }
        
        return $response;
    }
    
    
    
    
    
    
    
    public function newsletterNewProjectsOpenHitFrl($label, $timestamp)
    {
        return $this->newsletterNewProjectsOpenHit('newsletter_new_projects_freelancer', $label, $timestamp);
    }
    
    
    public function newsletterNewProjectsOpenHitEmp($label, $timestamp)
    {
        return $this->newsletterNewProjectsOpenHit('newsletter_new_projects_employer', $label, $timestamp);
    }
    
    
    protected function newsletterNewProjectsOpenHit($category, $label, $timestamp)
    {
        $label_txt = (is_numeric($label))?sprintf($this->config->text('year'),$label):$this->config->text($label);
        if(empty($label_txt)) $label_txt = $label;
        
        $request = $this->service->event(array(
            'category' => $this->config->text($category),
            'action' => sprintf($this->config->text('open'),date('d.m.Y',$timestamp)),
            'label' => $label_txt
        ))->track();
        
        $this->lastRequest = $request;
        
        $response = $request->send(false);
        return $response;
    }
    

    
    /**
     * Отправляет событие о покупке услуги
     * @param type $is_emp Заказчик или фрилансер
     * @param type $label 
     * @param type $value
     * @param type $cid
     * @return type
     */
    public function serviceWasPayed($is_emp, $label, $value, $cid = '', $from_account = false)
    {
        //Пока только через кассу
        if ($from_account || !$label) {
            return null;
        }
        
        $label_template = $from_account ? '' : 'payed_ykassa';

        $request = $this->service->build(array(
            'cid' => $cid,
            'sc' => 'start'
        ))->event(array(
            'category' => $is_emp ? 'customer' : 'freelancer',
            'action' => 'purchase',
            'label' => sprintf($this->config->text($label_template), $label),
            'value' => $value
        ))->track();
        
        $this->lastRequest = $request;
        
        $response = $request->send(false);
        return $response;
    }
    
    
    /**
     * Событие по добавлению ответа фрилансером
     * @param string $cid
     * @param string $project_kind_ident
     * @param int $offer_count
     * @param bool $is_pro
     * @return type
     */
    public function projectAnwer($cid, $project_kind_ident, $offer_count, $is_pro)
    {
        $label_arr = array();
        $label_arr[] = $project_kind_ident;
        $label_arr[] = $is_pro ? 'pro' : 'remain_' . $offer_count;
        $label = join(',', $label_arr);

        $request = $this->service->build(array(
            'cid' => $cid,
            'sc' => 'start'
        ))->event(array(
            'category' => 'freelancer',
            'action' => 'answer',
            'label' => $label,
            'value' => 0
        ))->track();
        
        $this->lastRequest = $request;
        
        $response = $request->send(false);
        return $response;
    }    
    
    
    /**
     * Обработать событие GA
     * 
     * @param type $category
     * @param type $action
     * @param type $label
     * @param type $value
     * @return type
     */
    public function event($category, $action, $label = null, $value = null)
    {
        $data = array(
            'category' => $category,
            'action' => $action
        );

        if ($label) {
            $data['label'] = $label;
        }

        if ($value) {
            $data['value'] = $value;
        }

        $request = $this->service->event($data)->track();              
        $response = $request->send(false);

        $this->lastRequest = $request;

        return $response;
    }
}
