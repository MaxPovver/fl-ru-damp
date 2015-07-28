<?php

/**
 * Class GuestConst
 * Константы модуля
 */
class GuestConst 
{

    const TYPE_PERSONAL_ORDER   = 10;
    const TYPE_PROJECT          = 20;
    const TYPE_VACANCY          = 25;

    const EMAIL_ERR  = 0x0001;


    protected static $_error_messages = array(
        self::EMAIL_ERR => array(
            self::TYPE_PERSONAL_ORDER => '
                На этот e-mail зарегистрирован ваш аккаунт фрилансера. <br/>
                Чтобы стать работодателем и предложить заказ, пожалуйста, укажите другой e-mail адрес.',

            self::TYPE_PROJECT => '
                На этот e-mail зарегистрирован ваш аккаунт фрилансера. <br/>
                Чтобы стать работодателем и опубликовать проект, пожалуйста, укажите другой e-mail адрес.'
        )
    ); 



    const MSG_AL        = 0x1001;
    const MSG_AL_EXIST  = 0x1002;
    const MSG_SUBMIT    = 0x1003;
    const URI_CANCEL    = 0x1004;
    const FORM_ID      = 0x1005;

    const VACANCY_ACTION_NOPRO = 'оплатить';
    const VACANCY_ACTION_PRO = 'подтвердить и оплатить'; 

    const VACANCY_EMAIL_BUSY = '
        На этот e-mail зарегистрирован ваш аккаунт фрилансера. 
        Чтобы стать работодателем и разместить вакансию, пожалуйста, укажите другой e-mail адрес.
        ';

    public static $_unsubscribe_ok_message = array(
        'title' => 'Настройки уведомлений',
        'message' => '
            Вы успешно отписались от уведомлений о вакансиях/проектах и больше не будете получать их на адрес %s'
        );


    protected static $_messages = array(

        self::FORM_ID => array(
            self::TYPE_PERSONAL_ORDER => 'new-personal-order',
            self::TYPE_PROJECT => 'new-project',
            self::TYPE_VACANCY => 'new-vacancy'
        ), 

        //Сообщение после добавления в попапе для новых юзеров
        self::MSG_AL => array(
            self::TYPE_PERSONAL_ORDER => array(
                   'title' => 'Заказ предложен исполнителю',
                   'message' => '
                        На указанный e-mail будет отправлено письмо со ссылкой, 
                        по которой вы сможете автоматически подтвердить регистрацию 
                        и перейти в предложенный вами заказ.'
               ),
            self::TYPE_PROJECT => array(
                   'title' => 'Проект создан',
                   'message' => '
                        На указанный e-mail будет отправлено письмо со ссылкой, 
                        по которой вы сможете автоматически подтвердить регистрацию 
                        и перейти в созданный проект.'
               ),
            self::TYPE_VACANCY => array(
                   'title' => 'Вакансия добавлена',
                   'message' => '
                        На указанный e-mail будет отправлено письмо со ссылкой, 
                        по которой вы сможете автоматически подтвердить регистрацию 
                        и оплатить публикацию вашей вакансии.'
               )
        ),

        //Сообщение после добавления в попапе для существующих юзеров
        self::MSG_AL_EXIST => array(
            self::TYPE_PERSONAL_ORDER => array(
                   'title' => 'Заказ предложен исполнителю',
                   'message' => '
                        На указанный e-mail будет отправлено письмо со ссылкой, 
                        по которой вы сможете перейти в предложенный вами заказ.'
               ),
            self::TYPE_PROJECT => array(
                   'title' => 'Проект создан',
                   'message' => '
                        На указанный e-mail будет отправлено письмо со ссылкой, 
                        по которой вы сможете автоматически подтвердить публикацию  
                        и перейти в созданный проект.'
               ),
            self::TYPE_VACANCY => array(
                   'title' => 'Вакансия добавлена',
                   'message' => '
                        На указанный e-mail будет отправлено письмо со ссылкой, 
                        по которой вы сможете %s публикацию вашей вакансии.'
               )
        ),


        //Текст под кнопкай добавления и/или регистрации
        self::MSG_SUBMIT => array(
            self::TYPE_PERSONAL_ORDER => 'Зарегистрироваться и предложить заказ',
            self::TYPE_PROJECT => 'Зарегистрироваться и опубликовать проект',
            self::TYPE_VACANCY => 'Зарегистрироваться и разместить вакансию за %d руб'
        ),

        //Ссылка рядом с кнопкой
        self::URI_CANCEL => array(
            self::TYPE_PERSONAL_ORDER => '/registration/?user_action=add_order',
            self::TYPE_PROJECT => '/registration/?user_action=add_project',
            self::TYPE_VACANCY => '/registration/?user_action=add_vacancy'
        )
    );


    public static function getErrorMessage($err, $type)
    {
        return isset(self::$_error_messages[$err][$type])?
            self::$_error_messages[$err][$type]:false;
    }


    public static function getMessage($mes, $type)
    {
        return isset(self::$_messages[$mes][$type])?
            self::$_messages[$mes][$type]:false; 
    }


}