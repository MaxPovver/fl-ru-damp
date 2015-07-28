<?php

class tservices_const
{

    const CURRENCY_RUS = 0;

    static protected $_currency = array (
        self::CURRENCY_RUS => 'р.',
    );


    //--------------------------------------------------------------------------
    
    
    const LABEL_TITLE           = 0;
    const LABEL_EXTRA           = 1;
    const LABEL_DISTANCE        = 2;
    const LABEL_DISTANCE_FIELD  = 3;
    const LABEL_PERSONAL_FIELD  = 4;
    const LABEL_REQUIREMENT     = 5;
    const LABEL_EXPRESS         = 6;
    const LABEL_DESCRIPTION     = 7;
    const LABEL_TAGS            = 8;
    const LABEL_CATEGORY        = 9;
    const LABEL_DAYS            = 10;
    const LABEL_UPLOADER        = 11;
    const LABEL_UPLOAD_AREA     = 12;
    const LABEL_VIDEO           = 13;

    static protected $_label = array(
        self::LABEL_TITLE           => 'Название и стоимость услуги',
        self::LABEL_EXTRA           => 'Заработайте больше, предлагая сопутствующие услуги',
        self::LABEL_DISTANCE        => 'Способ выполнения работы',
        self::LABEL_DISTANCE_FIELD  => 'Удаленно',
        self::LABEL_PERSONAL_FIELD  => 'Возможна личная встреча',
        self::LABEL_REQUIREMENT     => 'Необходимая от заказчика информация',
        self::LABEL_EXPRESS         => 'Могу выполнить срочно за дополнительные',
        self::LABEL_DESCRIPTION     => 'Подробное описание',
        self::LABEL_TAGS            => 'Ключевые слова',
        self::LABEL_CATEGORY        => 'Категория',
        self::LABEL_DAYS            => 'Срок выполнения работы',
        self::LABEL_UPLOADER        => 'Фото',
        self::LABEL_UPLOAD_AREA     => 'Перетащите файл сюда',
        self::LABEL_VIDEO           => 'Добавить ссылку на видео'
    );
    
    
    
    
    //--------------------------------------------------------------------------
    
    
    
    const PLACEHOLDER_REQUIREMENT = 0;
    const PLACEHOLDER_DESCRIPTION = 1;
    
    static protected $_placeholder = array(
        self::PLACEHOLDER_REQUIREMENT => 'Опишите по пунктам, что должен предоставить заказчик для начала работы',
        self::PLACEHOLDER_DESCRIPTION => 'Подробно опишите результат, который получит заказчик',
    );

    
    
    //--------------------------------------------------------------------------
    
    
    
    const HINT_TITLE    = 0;
    const HINT_TAGS     = 1;
    const HINT_VIDEO    = 2;
    const HINT_IMG      = 3;
    
    static protected $_hint = array(
        self::HINT_TITLE    => 'Например: Дизайн визитки за 2 000 р.',
        self::HINT_TAGS     => 'Можно указать до 10 слов через запятую',
        self::HINT_VIDEO    => 'Ссылка на видео с YouTube, RuTube или Vimeo',
        self::HINT_IMG      => 'Минимальное разрешение 600x600 пикселей в формате jpg, jpeg, png.'
    );
    
    
    //--------------------------------------------------------------------------
    
    
    const MISC_FORM_TITLE_EDIT  = 0;
    const MISC_FORM_TITLE_NEW   = 1;
    const MISC_FORM_SUBTITLE    = 2;
    const MISC_TU_NONE          = 3;
    
    static protected $_misc = array(
        self::MISC_FORM_TITLE_EDIT   => 'Редактирование типовой услуги',
        self::MISC_FORM_TITLE_NEW    => 'Создайте типовую услугу за пару минут',
        self::MISC_FORM_SUBTITLE     => 'Типовая услуга — фиксированный объем работ, который вы можете выполнить по фиксированной цене',
        self::MISC_TU_NONE           => 'Типовых услуг не найдено.'
    );

    
    
    //--------------------------------------------------------------------------
    
    
    const SEO_CARD_TITLE    = 0;
    const SEO_PROF_TITLE    = 1;
    const SEO_NEW_TITLE     = 2;
    const SEO_EDIT_TITLE    = 3;
    const SEO_PROF_SHARE    = 4;
    
    static protected $_seo = array(
        self::SEO_CARD_TITLE   => '%s — Типовая услуга на FL.ru',
        self::SEO_PROF_TITLE   => 'Типовые услуги на FL.ru',
        self::SEO_NEW_TITLE    => 'Добавление типовой услуги на FL.ru',
        self::SEO_EDIT_TITLE   => 'Редактирование типовой услуги на FL.ru',
        self::SEO_PROF_SHARE   => 'Типовые услуги — %s на FL.ru'
    );


    
    //--------------------------------------------------------------------------

    
    
    const TIP_DAYS      = 0;
    const TIP_DISTANCE  = 1;
    const TIP_EXTRA     = 2;
    const TIP_EXPRESS   = 3;
    const TIP_EMP_ONLY  = 4;
    
    static protected $_tip = array(
        self::TIP_DAYS      => 'Максимальный срок, в который вы точно уложитесь',
        self::TIP_DISTANCE  => 'Готовы ли вы встретиться с заказчиком лично, или работа происходит строго удаленно?',
        self::TIP_EXTRA     => 'Предложите покупателю вместе с основной услугой дополнительные опции за отдельные оплату и сроки',
        self::TIP_EXPRESS   => 'Укажите сумму доплаты за срочное выполнение всей работы (по услуге и дополнительным опциям)',
        self::TIP_EMP_ONLY  => 'Заказ услуги возможен только из аккаунта работодателя'
    );


    //--------------------------------------------------------------------------
    
    
    const BUTTON_EDIT                   = 0;
    const BUTTON_SAVE                   = 1;
    const BUTTON_ADD                    = 2;
    const BUTTON_STOP_PUBLISH           = 3;
    const BUTTON_SAVE_WITHOUT_PUBLISH   = 4;
    const BUTTON_DEL                    = 5;

    static protected $_button = array(
        self::BUTTON_SAVE                   => 'Сохранить',
        self::BUTTON_ADD                    => 'Опубликовать',
        self::BUTTON_EDIT                   => 'Редактировать',
        self::BUTTON_STOP_PUBLISH           => 'Снять с публикации',
        self::BUTTON_SAVE_WITHOUT_PUBLISH   => 'Сохранить без публикации',
        self::BUTTON_DEL                    => 'Удалить услугу'
    );



    //--------------------------------------------------------------------------
    
    const MSG_SHOW                  = 0;
    const MSG_HIDE                  = 1;
    const MSG_DELETED               = 2;
    const MSG_NEW_SAVED             = 3;
    const MSG_NEW_SAVED_PUBLISH     = 4;
    const MSG_UPDATE                = 5;
    const MSG_UPDATE_PUBLISH        = 6;
    
    static protected $_msg = array(
        self::MSG_SHOW              => 'Типовая услуга &laquo;%s&raquo; опубликована.',
        self::MSG_HIDE              => 'Типовая услуга &laquo;%s&raquo; снята с публикации.',
        self::MSG_DELETED           => 'Типовая услуга &laquo;%s&raquo; удалена.',
        self::MSG_NEW_SAVED         => 'Типовая услуга &laquo;%s&raquo; добавлена без публикации.',
        self::MSG_NEW_SAVED_PUBLISH => 'Типовая услуга &laquo;%s&raquo; добавлена и опубликована.',
        self::MSG_UPDATE            => 'Типовая услуга &laquo;%s&raquo; сохранена без публикации.',
        self::MSG_UPDATE_PUBLISH    => 'Типовая услуга &laquo;%s&raquo; сохранена и опубликована.'
    );
    
    //--------------------------------------------------------------------------
    
    static function enum($name, $const)
    {
       if(!isset(self::${'_' . $name})) return FALSE; 
       $a = self::${'_' . $name};
       return @$a[constant('self::'.strtoupper($name . '_' . $const))];
    }
    
}