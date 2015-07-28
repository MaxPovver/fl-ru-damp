<?php


/**
 * Общий конфиг для всех адаптеров
 * @todo: возможно не лучшая идея зато удобно все параметры редактировать
 */
class StatisticConfig
{
    protected $type;
    
    /**
     * Конструктор устанавливает имя адаптера в нижней регистре
     * которое потом нужно использовать в конфигах ниже.
     * 
     * @param string $type
     */
    public function __construct($type) 
    {
        $this->type = strtolower($type);
    }

    
    /**
     * Возвращает текущие настройки по умолчанию для текущего адаптера
     * 
     * @return array
     */
    public function options()
    {
        $ua_list = array(
            'release' => 'UA-163162-4', //release
            'beta' => 'UA-49313708-3', //beta
            'alpha' => 'UA-49313708-2', //alpha
            'local' => 'UA-59845348-1' //local - @todo: можно использовать свой
        );
        
        $srv = defined('SERVER')?strtolower(SERVER):'local';
        $ua = (isset($ua_list[$srv]))?$ua_list[$srv]:'';
        
        $default_options = array(
            
            'ga' => array(
                'v' => 1,
                'tid' => $ua,
                'cid' => md5($ua)
            )
            
            
            
        );
        
        return $default_options[$this->type];
    }
    
    
    /**
     * Возвращает текстовую константу по ключу для текущего адаптера
     * 
     * @param string $key
     * @return string
     */
    public function text($key)
    {
        $default_text = array(
            
            'ga' => array(
                'newsletter_new_projects_freelancer' => 'Ежедневная рассылка проектов по фрилансерам',
                'newsletter_new_projects_employer' => 'Ежедневная рассылка проектов по работодателям',
                'sended' => 'Отправлено %s',
                'open' => 'Открыта рассылка от %s',//'Открыто. Рассылка: %s',
                'year' => '%d год',
                'total' => 'Всего',
                'new' => 'Менее недели назад', //В течении недели
                'payed_ykassa' => '%s,ykassa'                
            )
            
        );
        
        if(!isset($default_text[$this->type][$key])) return FALSE;
        
        return $this->conv($default_text[$this->type][$key]);
    }

    
    /**
     * Конвертер кодировки
     * 
     * @param string $str
     * @return string
     */
    protected function conv($str)
    {
        return iconv('cp1251', 'utf-8', $str);
    }

}