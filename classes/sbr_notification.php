<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_stages.php';
/**
 * Класс для работы с оповещениями 
 */
class sbr_notification
{
    public $name;
    
    /**
     * Иконки выводимые фрилансеру в зависимости от статуса этапа и оповещения
     * 
     * @var array array('Идентификатор оповещения' => array('Класс иконки', 'Класс текста для оповещения'));
     */
    static public $ico_frl = array (
        sbr_stages::STATUS_NEW => array(
            'sbr.AGREE'                    => array('b-icon_sbr_srur', 'b-layout__txt_color_c10600'),
            'sbr.DELADD_SS_AGREE'          => array('b-icon_sbr_srur', 'b-layout__txt_color_c10600'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_sattent', ''),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_sattent', ''),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_sattent', ''),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_sattent', ''),
            'sbr_stages.EMP_ROLLBACK'      => array('b-icon_sbr_sattent', ''),
            'sbr_stages.PAUSE_RESET'       => array('b-icon_sbr_sattent', ''),
            'sbr_stages.PAUSE_OVER'        => array('b-icon_sbr_sattent', ''),
            'sbr.EMP_ROLLBACK'             => array('b-icon_sbr_sattent', ''),
            'sbr.SCHEME_MODIFIED'          => array('b-icon_sbr_sattent', ''),
            'sbr.COST_SYS_MODIFIED'        => array('b-icon_sbr_sattent', ''),
            'sbr_stages.COMMENT'           => array('b-icon_sbr_scom', ''),
            'sbr.REFUSE'                   => array('b-icon_sbr_rdel', ''),
            'sbr_stages.REFUSE'            => array('b-icon_sbr_rdel', ''),
            'sbr.DELADD_SS_REFUSE'         => array('b-icon_sbr_rdel', ''),
            'sbr.CANCEL'                   => array('b-icon_sbr_rdel', ''),
            'sbr.RESERVE'                  => array('b-icon_sbr_srur', 'b-layout__txt_color_a0763b'),
        ),
        sbr_stages::STATUS_PROCESS     =>  array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_bcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.PAUSE_RESET'       => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.PAUSE_OVER'        => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.AGREE'             => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr.RESERVE'                  => array('b-icon_sbr_brur', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.OVERTIME'          => array('b-icon_sbr_bplay', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_FROZEN      => array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_scom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.AGREE'             => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_INARBITRAGE  => array(
            'sbr_stages.EMP_ARB'           => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.FRL_ARB'           => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COMMENT'           => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_aattent', ''),
            'sbr_stages.ARB_COMMENT'       => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_ARBITRAGED  => array(
            'sbr_stages.ARB_RESOLVED'      => array('b-icon_sbr_aok', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_sattent', ''),
            'sbr_stages.EMP_FEEDBACK'      => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.FRL_FEEDBACK'      => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_COMPLETED   => array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_gcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COMPLETED'         => array('b-icon_sbr_gattent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.EMP_FEEDBACK'      => array('b-icon_sbr_gcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.FRL_FEEDBACK'      => array('b-icon_sbr_gattent', ''),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.DOC_RECEIVED'      => array('b-icon_sbr_gattent', ''),
          
        ),
    );
    
    /**
     * Иконки выводимые работодателю в зависимости от статуса этапа и оповещения
     * 
     * @var array array('Идентификатор оповещения' => array('Класс иконки', 'Класс текста для оповещения'));
     */
    static public $ico_emp = array(
        sbr_stages::STATUS_NEW => array(
            'sbr.OPEN'                     => array('b-icon_sbr_stime', ''),
            'sbr.AGREE'                    => array('b-icon_sbr_srur', ''),
            'sbr_stages.AGREE'             => array('b-icon_sbr_sattent', ''),
            'sbr_stages.REFUSE'            => array('b-icon_sbr_rdel', ''),
            'sbr.DELADD_SS_AGREE'          => array('b-icon_sbr_sattent', ''),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_stime', ''),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_stime', ''),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_stime', ''),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_sattent', ''),
            'sbr_stages.PAUSE_RESET'       => array('b-icon_sbr_sattent', ''),
            'sbr_stages.PAUSE_OVER'        => array('b-icon_sbr_sattent', ''),
            'sbr.SCHEME_MODIFIED'          => array('b-icon_sbr_sattent', ''),
            'sbr.COST_SYS_MODIFIED'        => array('b-icon_sbr_sattent', ''),
            'sbr_stages.COMMENT'           => array('b-icon_sbr_scom', ''),
            'sbr.REFUSE'                   => array('b-icon_sbr_rdel', ''),
            'sbr.DELADD_SS_REFUSE'         => array('b-icon_sbr_sattent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.REFUSE'            => array('b-icon_sbr_sattent', 'b-layout__txt_color_a0763b'),
            'sbr.CANCEL'                   => array('b-icon_sbr_rdel', ''),
            'sbr.RESERVE'                  => array('b-icon_sbr_sattent', ''),
            'pskb.FORM'                    => array('b-icon_sbr_sattent', ''),
            'pskb.NEW'                     => array('b-icon_sbr_sattent', ''),
            'pskb.EXP'                     => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_EXEC'                => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_END'                 => array('b-icon_sbr_sattent', ''),
        ),
        sbr_stages::STATUS_PROCESS     =>  array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_bcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STARTED_WORK'      => array('b-icon_sbr_bplay', 'b-layout__txt_color_a0763b'),
            'sbr_stages.REFUSE'            => array('b-icon_sbr_battent', ''),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_battent', ''),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_battent', ''),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_battent', ''),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_battent', ''),
            'sbr_stages.PAUSE_RESET'       => array('b-icon_sbr_battent', ''),
            'sbr_stages.PAUSE_OVER'        => array('b-icon_sbr_battent', ''),
            'sbr.AGREE'                    => array('b-icon_sbr_battent', ''),
            'sbr.RESERVE'                  => array('b-icon_sbr_battent', ''),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.OVERTIME'          => array('b-icon_sbr_bplay', 'b-layout__txt_color_a0763b'),
            'pskb.EXP'                     => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_EXEC'                => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_END'                 => array('b-icon_sbr_sattent', ''),
        ),
        sbr_stages::STATUS_FROZEN      => array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_scom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.AGREE'             => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_INARBITRAGE  => array(
            'sbr_stages.FRL_ARB'           => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.EMP_ARB'           => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_COMMENT'       => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_aattent', ''),
            'sbr_stages.COMMENT'           => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_ARBITRAGED  => array(
            'sbr_stages.ARB_RESOLVED'      => array('b-icon_sbr_aok', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_sattent', ''),
            'sbr_stages.FRL_FEEDBACK'      => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_COMPLETED   => array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_gcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COMPLETED'         => array('b-icon_sbr_gattent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.FRL_FEEDBACK'      => array('b-icon_sbr_gcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'pskb.EXP'                     => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_EXEC'                => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_END'                 => array('b-icon_sbr_sattent', ''),
            'pskb.PASSED'                  => array('b-icon_sbr_gattent', ''),
            'pskb.PASSED_EMP'              => array('b-icon_sbr_gattent', '')
            
        ),
    );
    
    /**
     * Названия оповещения
     * @var array (0 - оповещение для работодателя, 1 - оповещение для исполнителя, 2 - что видит админ)  
     */
    static public $notification = array(
        // Только исполнителя
        'sbr_stages.EMP_FEEDBACK'       => array(false, 'Заказчик оставил вам отзыв', 'Заказчик оставил отзыв'),
        'sbr_stages.EMP_ARB'            => array(false, 'Заказчик обратился в арбитраж', 'Заказчик обратился в арбитраж'),
        'sbr_stages.DOC_RECEIVED'       => array(false, 'Идет выплата'),
        'sbr_stages.FRL_PAID'           => array(false, 'Идет выплата'),
        'sbr_stages.MONEY_PAID'         => array(false, 'Гонорар выплачен'),
        // Только заказчика
        'sbr.DELADD_SS_REFUSE'          => array('Исполнитель отказался от изменений', false, 'Исполнитель отказался от изменений'),
        'sbr.OPEN'                      => array('Ожидание согласия исполнителя', false, 'Ожидание согласия исполнителя'),
        'sbr_stages.STARTED_WORK'       => array('Исполнитель приступил к работе', 'Вы приступили к работе', 'Исполнитель приступил к работе'),
        'sbr_stages.REFUSE'             => array('Исполнитель отказался от изменений', false, 'Исполнитель отказался от изменений'),
        'sbr_stages.FRL_ARB'            => array('Исполнитель обратился в арбитраж', false, 'Исполнитель обратился в арбитраж'),
        'sbr_stages.EMP_PAID'           => array('Ожидание завершения сделки исполнителем', false, 'Идет выплата заказчику'),
        'sbr_stages.EMP_MONEY_REFUNDED' => array('Деньги отправлены, ожидайте поступления', false, 'Деньги отправлены, заказчик ожидает поступления'),
        'pskb.FORM'                     => array('Идет проверка реквизитов ', false, 'Идет проверка реквизитов заказчика'),
        'pskb.NEW'                      => array('Ожидание поступления денег', false, 'Заказчик ожидает поступления денег'),
        'pskb.EXP'                      => array('Деньги не поступили в банк. Сделка не начата', false, 'Деньги не поступили в банк. Сделка не начата'),
        'pskb.EXP_EXEC'                 => array('Исполнитель не подал документы. Деньги возвращаются Заказчику', false, 'Исполнитель не подал документы. Деньги возвращаются Заказчику'),
        'pskb.EXP_END'                  => array('Срок аккредитива истек. Деньги возвращаются Заказчику', false, 'Срок аккредитива истек. Деньги возвращаются Заказчику'),
        'pskb.PASSED'                   => array('Ожидание подтверждения Исполнителем получения денег', 'Ожидание кода подтверждения', 'Ожидание подтверждения Исполнителем получения денег'),
        'pskb.PASSED_EMP'               => array('Ожидание подтверждения Исполнителем отправки денег', 'Ожидание кода подтверждения', 'Ожидание подтверждения Исполнителем отправки денег'),
        // Общие
        'sbr_stages.OVERTIME'           => array('Время на этап истекло', 'Время на этап истекло', 'Время на этап истекло'),
        'sbr.AGREE'                     => array('Исполнитель согласился на сделку', 'Заказчик еще не зарезервировал деньги!', 'Заказчик еще не зарезервировал деньги!'),
        'sbr.DELADD_SS_AGREE'           => array('Исполнитель согласился с изменениями', 'Заказчик еще не зарезервировал деньги!', 'Заказчик еще не зарезервировал деньги!'),
        'sbr_stages.AGREE'              => array('Исполнитель согласился с изменениями', 'Вы согласились с изменениями', 'Исполнитель согласился с изменениями'),
        'sbr_stages.WORKTIME_MODIFIED'  => array('Ожидание согласия исполнителя', 'Заказчик хочет изменить условия этапа', 'Заказчик хочет изменить условия этапа'),
        'sbr_stages.TZ_MODIFIED'        => array('Ожидание согласия исполнителя', 'Заказчик хочет изменить условия этапа', 'Заказчик хочет изменить условия этапа'),
        'sbr_stages.COST_MODIFIED'      => array('Ожидание согласия исполнителя', 'Заказчик хочет изменить условия этапа', 'Заказчик хочет изменить условия этапа'),
        'sbr_stages.STATUS_MODIFIED'    => array('Ожидание согласия исполнителя', 'Заказчик хочет изменить статус этапа', 'Заказчик хочет изменить статус этапа'),
        'sbr_stages.PAUSE_RESET'        => array('Пауза отменена (не подтверждена в срок)', 'Пауза отменена (не подтверждена в срок)', 'Пауза отменена (не подтверждена в срок)'),
        'sbr_stages.PAUSE_OVER'         => array('Срок паузы завершен', 'Срок паузы завершен', 'Срок паузы завершен'),
        'sbr.SCHEME_MODIFIED'           => array('Ожидание согласия исполнителя', 'Заказчик хочет изменить условия сделки', 'Заказчик хочет изменить условия сделки'),
        'sbr.COST_SYS_MODIFIED'         => array('Ожидание согласия исполнителя', 'Заказчик хочет изменить условия сделки', 'Заказчик хочет изменить условия сделки'),
        'sbr.EMP_ROLLBACK'              => array('Ожидание согласия исполнителя', 'Заказчик хочет изменить условия сделки', 'Заказчик хочет изменить условия сделки'),
        'sbr_stages.EMP_ROLLBACK'       => array('Ожидание согласия исполнителя', 'Заказчик хочет изменить условия этапа', 'Заказчик хочет изменить условия этапа'),
        'sbr_stages.COMMENT'            => array('Исполнитель оставил комментарий', 'Заказчик оставил комментарий'),
        'sbr.REFUSE'                    => array('Исполнитель отказался от сделки', 'Вы отказались от сделки', 'Исполнитель отказался от сделки'),
        'sbr.CANCEL'                    => array('Вы отменили сделку', 'Сделка отменена заказчиком', 'Сделка отменена заказчиком'),
        'sbr.RESERVE'                   => array('Этап поставлен в очередь', 'Заказчик зарезервировал деньги', 'Заказчик зарезервировал деньги'),
        'sbr_stages.COMPLETED'          => array('', 'Заказчик завершил этап', 'Заказчик завершил этап'),
        'sbr_stages.FRL_FEEDBACK'       => array('Исполнитель оставил вам отзыв', 'Ожидание подписанного акта', 'Исполнитель оставил отзыв'),
        'sbr_stages.ARB_COMMENT'        => array('Арбитраж оставил комментарий', 'Арбитраж оставил комментарий', 'Арбитраж оставил комментарий'),
        'sbr_stages.ARB_RESOLVED'       => array('Арбитраж вынес окончательное решение', 'Арбитраж вынес окончательное решение', 'Арбитраж вынес окончательное решение'),
        'sbr_stages.ARB_CANCELED'       => array('Администратор отменил арбитраж', 'Администратор отменил арбитраж', 'Администратор отменил арбитраж')
    );
    
    /**
     * По каким событиям должна быть реакция от пользователя
     * 
     * @var array (0 - реакция должна быть от работодателя, 1 - реакция должна быть от фрилансера) 
     */
    static public $reaction = array(
        // Общие
        'sbr_stages.COMMENT'            => array(true, true),
        'sbr_stages.COMPLETED'          => array(true, true),
        'sbr_stages.ARB_COMMENT'        => array(true, true),
        'sbr_stages.ARB_RESOLVED'       => array(true, true),
        'sbr_stages.ARB_CANCELED'       => array(true, true),
        'sbr_stages.OVERTIME'           => array(true, true),
        'sbr_stages.PAUSE_RESET'        => array(true, true),
        'sbr_stages.PAUSE_OVER'         => array(true, true),
        // Работодателя
        'sbr.AGREE'                     => array(true, false),
        'sbr_stages.AGREE'              => array(true, false),
        'sbr.DELADD_SS_AGREE'           => array(true, false),
        'sbr.REFUSE'                    => array(true, false),
        'sbr_stages.STARTED_WORK'       => array(true, false),
        'sbr.REFUSE'                    => array(true, false),
        'sbr_stages.FRL_FEEDBACK'       => array(true, false),
        'sbr.DELADD_SS_REFUSE'          => array(true, false),
        'sbr_stages.REFUSE'             => array(true, false),
        'sbr_stages.FRL_ARB'            => array(true, false),
        // Фрилансера
        'sbr_stages.WORKTIME_MODIFIED'  => array(false,true),
        'sbr_stages.TZ_MODIFIED'        => array(false,true),
        'sbr_stages.COST_MODIFIED'      => array(false,true),
        'sbr.SCHEME_MODIFIED'           => array(false,true),
        'sbr.COST_SYS_MODIFIED'         => array(false,true),
        'sbr.CANCEL'                    => array(false,true),
        'sbr.RESERVE'                   => array(false,true),
        'sbr_stages.EMP_FEEDBACK'       => array(false,true),
        'sbr.EMP_ROLLBACK'              => array(false,true),
        'sbr_stages.EMP_ROLLBACK'       => array(false,true),
        'sbr_stages.EMP_ARB'            => array(false,true)
    );
    
    /**
     * Название в истории
     * @var array (0 - история для работодателя, 1 - история для исполнителя)  
     */
    static public $history = array (
        'sbr.SCHEME_MODIFIED'           => array('Вы изменили тип договора.', 'Заказчик изменил тип договора.', 'Заказчик изменил тип договора.'),
        'sbr_stages.ADD_DOC'            => array('Добавлен документ.', 'Добавлен документ.', 'Добавлен документ.'),
        'sbr.ADD_DOC'                   => array('Добавлен документ.', 'Добавлен документ.', 'Добавлен документ.'),
        'sbr.OPEN'                      => array('Вы оставили заявку на сделку.', 'Заказчик оставил вам заявку на сделку.', 'Заказчик оставил заявку на сделку.'),
        'sbr.REOPEN'                    => array('Вы снова оставили заявку на сделку.', 'Заказчик снова оставил вам заявку на сделку.', 'Заказчик снова оставил заявку на сделку.'), // Псевдо аббревиатура
        'sbr.COST_SYS_MODIFIED'         => array('Вы хотите изменить условия этапа.', 'Заказчик хочет изменить условия этапа (в соответствии с разделом 5 <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Заказчик хочет изменить условия этапа.'),
        'sbr.DELADD_SS_REFUSE'          => array('Исполнитель отказался от новых условий.', 'Вы отказались от новых условий.', 'Исполнитель отказался от новых условий.'),
        'sbr.DELADD_SS_AGREE'           => array('Исполнитель согласился с новыми условиями.', 'Вы согласились с новыми условиями.', 'Исполнитель согласился с новыми условиями.'),
        'sbr_stages.COST_MODIFIED'      => array('Вы хотите изменить условия этапа.', 'Заказчик хочет изменить условия этапа (в соответствии с разделом 5 <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Заказчик хочет изменить условия этапа.'),
        'sbr_stages.TZ_MODIFIED'        => array('Вы хотите изменить условия этапа.', 'Заказчик хочет изменить условия этапа (в соответствии с разделом 5 <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Заказчик хочет изменить условия этапа.'),
        'sbr_stages.STATUS_MODIFIED'    => array('Вы хотите изменить статус этапа.', 'Заказчик хочет изменить статус этапа (в соответствии с разделом 5 <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Заказчик хочет изменить статус этапа.'),
        'sbr_stages.PAUSE_RESET'        => array('Пауза отменена (не подтверждена в срок)', 'Пауза отменена (не подтверждена в срок)', 'Пауза отменена (не подтверждена в срок)'),
        'sbr_stages.PAUSE_OVER'         => array('Срок паузы завершен', 'Срок паузы завершен', 'Срок паузы завершен'),
        'sbr_stages.STATUS_MODIFIED_OK' => array('Вы изменили статус этапа.', 'Заказчик изменил статус этапа (в соответствии с пунктами 8.2, 8.3 <a class="b-layout__link" href="/offer_lc.pdf">Договора</a>).', 'Заказчик изменил статус этапа.'),
        'sbr_stages.STATUS_MODIFIED_OK_NEW_CONTRACT' => array('Вы изменили статус этапа.', 'Заказчик изменил статус этапа (в соответствии с пунктами 8.2 - 8.4 <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Заказчик изменил статус этапа.'),
        'sbr_stages.WORKTIME_MODIFIED'  => array('Вы хотите изменить условия этапа.', 'Заказчик хочет изменить условия этапа (в соответствии с разделом 5 <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Заказчик хочет изменить условия этапа.'),
        'sbr.AGREE'                     => array('Исполнитель согласился на сделку.', 'Вы согласились на сделку%s.', 'Исполнитель согласился на сделку.'),
        'sbr.REFUSE'                    => array('Исполнитель отказался от сделки.', 'Вы отказались от сделки.', 'Исполнитель отказался от сделки.'),
        'sbr_stages.AGREE'              => array('Исполнитель согласился с изменениями.', 'Вы согласились с изменениями.', 'Исполнитель согласился с изменениями.'),
        'sbr_stages.REFUSE'             => array('Исполнитель отказался от изменений%s.', 'Вы отказались от изменений%s.', 'Исполнитель отказался от изменений%s.'),
        'sbr_stages.EMP_ROLLBACK'       => array('Вы повторно отправили изменения этапа.', 'Заказчик повторно отправил измения этапа.', 'Заказчик повторно отправил измения этапа.'),
        'sbr.RESERVE'                   => array('Вы зарезервировали деньги%s.', 'Заказчик зарезервировал деньги.', 'Заказчик зарезервировал деньги.'),
        'sbr_stages.EMP_ARB'            => array('Вы обратились в арбитраж.', 'Заказчик обратился в арбитраж (в соответствии с пунктом 9.1 <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Заказчик обратился в арбитраж.'),
        'sbr_stages.FRL_ARB'            => array('Исполнитель обратился в арбитраж (в соответствии с пунктом 9.1 <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Вы обратились в арбитраж.', 'Исполнитель обратился в арбитраж.'),
        'sbr_stages.ARB_RESOLVED'       => array('Арбитраж вынес окончательное решение (в соответствии с пунктом %s <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Арбитраж вынес окончательное решение (в соответствии с пунктом %s <a class="b-layout__link" href="link_offer_lc">Договора</a>).', 'Арбитраж вынес окончательное решение.'),
        'sbr.CANCEL'                    => array('Вы отменили сделку.', 'Заказчик отменил сделку.', 'Заказчик отменил сделку.'),
        'sbr_stages.FRL_FEEDBACK'               => array('Исполнитель оставил вам %s (в соответствии с разделом 4 <a class="b-layout__link" href="/about/appendix_2_regulations.pdf">Пользовательского соглашения</a>).', 'Вы оставили заказчику %s.', 'Исполнитель оставил %s.'),
        'sbr_stages.FRL_FEEDBACK_NEW_CONTRACT'  => array('Исполнитель оставил вам %s (в соответствии с Приложением №2 к <a class="b-layout__link" href="/about/appendix_2_regulations.pdf">Пользовательскому соглашению</a>).', 'Вы оставили заказчику %s.', 'Исполнитель оставил %s.'),
        'sbr_stages.EMP_FEEDBACK'               => array('Вы оставили исполнителю %s.', 'Заказчик оставил вам %s (в соответствии с разделом 4 <a class="b-layout__link" href="/about/appendix_2_regulations.pdf">Пользовательского соглашения</a>).', 'Заказчик оставил %s.'),
        'sbr_stages.EMP_FEEDBACK_NEW_CONTRACT'  => array('Вы оставили исполнителю %s.', 'Заказчик оставил вам %s (в соответствии с Приложением №2 к <a class="b-layout__link" href="/about/appendix_2_regulations.pdf">Пользовательскому соглашению</a>).', 'Заказчик оставил %s.'),
        'sbr_stages.STARTED_WORK'       => array('Исполнитель приступил к работе.', 'Вы приступили к работе.', 'Исполнитель приступил к работе.'),
        'sbr_stages.ARB_CANCELED'       => array('Арбитраж отменен.', 'Арбитраж отменен.', 'Арбитраж отменен.'),
        'sbr_stages.EMP_MONEY_REFUNDED' => array('%s', '', '%s'),
        'sbr_stages.MONEY_PAID'         => array('', '%s', '%s'),
        'sbr_stages.COMMENT'            => array('', ''),
        'sbr_stages.ARB_COMMENT'        => array('', ''),
        'sbr.COMPLETED'                 => array('Сделка завершена', 'Сделка завершена', 'Сделка завершена'),
        'sbr_stages.OVERTIME'           => array('Время, отведенное вами на выполнение этого этапа сделки, истекло. До %s,  вы должны принять решение о том, что делать с результатами работы по этому этапу:', 'Время, отведенное заказчиком на выполнение этого этапа сделки, истекло.', 'Время, отведенное заказчиком на выполнение этого этапа сделки, истекло.'),
        'sbr_stages.DOCS_NOTE'          => array('', '', ''),
    );
    
    /**
     * Возвращает название в историю в зависимоти от типа истории и роли
     * 
     * @param string  $arb             Тип истории
     * @param boolean $role            Роль (true - заказчик, false - исполнитель) @see sbr::isEmp();
     * @param string  $str_additional  Дополнительная строка @see self::$history['sbr_stages.FRL_FEEDBACK']
     * @return string Название истории 
     */
    public function getHistoryName($arb, $role, $str_additional = "") {
        return sprintf(self::$history[$arb][$role], $str_additional);
    }
    
    /**
     * Возвращает последнее оповещение по этапу (или СБР)
     * 
     * @global object $DB Подключение к БД
     * 
     * @param integer $sbr_id  ИД СБР
     * @param integer $own_id  ИД записи @see таблица sbr_events
     * @param string  $level   Символьный код типа оповещения (sbr_stages - оповещение на этап, sbr - Оповещение на всю сделку)  
     * @param inetegr $uid     ИД Пользователя
     * @return array
     */
    public function getNotification($sbr_id, $own_id, $level = 'sbr_stages', $uid = false) {
        global $DB;
        
        if(is_emp()) {
            $where = " AND se.estatus IS NULL";
        } else {
            $where = " AND se.fstatus IS NULL";
        }
        
        $sql = "(SELECT sec.*, (sec.own_rel || '.' || sec.abbr) as ntype, se.id as evnt, se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                WHERE se.sbr_id = ?i AND se.own_id = ? AND sec.own_rel = ? {$where} ORDER BY se.id DESC LIMIT 1)
                
                UNION 

                (SELECT sec.*, (sec.own_rel || '.' || sec.abbr) as ntype, se.id as evnt, se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                INNER JOIN sbr_stages_msgs ssm ON ssm.id = se.own_id
                WHERE se.sbr_id = ?i  AND ssm.stage_id = ? AND sec.own_rel = 'sbr_stages' AND sec.abbr IN ('COMMENT', 'ARB_COMMENT') {$where} ORDER BY se.id DESC LIMIT 1)
                
                ORDER BY evnt DESC 
                LIMIT 1";
        
        $result = $DB->row($sql, $sbr_id, $level == 'sbr_stages' ? $own_id : $sbr_id, $level, $sbr_id, $own_id);
        if($result['abbr'] == 'ADD_DOC' || $result['abbr'] == 'DEL_DOC') return false;
        if(!$result && $level == 'sbr_stages') {
            return self::getNotification($sbr_id, $own_id, 'sbr', $uid);
        } else if(!$result && $level == 'sbr_stages') {
            return false;
        }
        return $result;
    }
    
    /**
     * Берем оповещение конкретно по этапу и его ИД события
     * 
     * @global object $DB Подключение к БД
     *   
     * @param integer $sbr_id  ИД СБР
     * @param integer $own_id  ИД записи @see таблица sbr_events
     * @param integer $ev_code ИД события  
     * @return string 
     */
    public function getNotificationsForStage($sbr_id, $own_id, $ev_code) {
        global $DB;
        $sql = "SELECT se.*, sx.xtime
                FROM sbr_events se 
                INNER JOIN sbr_xacts sx ON sx.id = se.xact_id
                WHERE se.sbr_id = ?i AND se.own_id = ?i AND se.ev_code = ?i
                ORDER BY se.id DESC";
        
        return $DB->rows($sql, $sbr_id, $own_id, $ev_code);
        
    }
    
    /**
     * Надо ли реагировать пользователю на оповещение
     * @param array $notification    Оповещение @see self::getNotification();
     * @return boolean
     */
    public static function isReaction($notification) {
        return (is_emp() ? self::$reaction[$notification['ntype']][0] : self::$reaction[$notification['ntype']][1]);
    }
    
    /**
     * Берем ИД кодов событий
     * 
     * @global object $DB Подключение к БД
     * 
     * @param array $list  Символьные коды события @see self::parseEventName();
     * @return array 
     */
    public function getEventCode($list) {
        global $DB;
        if(!$list) return array();
        
        foreach($list as $own=>$abbr) {
            $str_abbr  = implode("', '", $abbr);
            $where[] = " ( sec.own_rel = '$own' AND sec.abbr IN ('{$str_abbr}') )";
        }
        
        $where = implode(" OR ", $where);
        
        $sql = "SELECT * FROM sbr_ev_codes sec WHERE {$where}";
        return $DB->cache(1800)->rows($sql, $own, $abbr);
    }
    
    /**
     * Парсим символные коды событий 
     *  
     * @param string $name    Событие @example "sbr.AGREE" (где sbr - Имя таблицы, AGREE - Символьный код события)
     * @return array    array['Имя таблицы'] = array('Символьный код', 'Символьный код', ...);
     */
    public function parseEventName($name) {
        if(is_array($name)) {
            foreach($name as $k=>$val) {
                list($own, $abbr) = explode(".", $val);
                $result[$own][] = $abbr;
            }
        } else {
            list($own, $abbr) = explode(".", $name);
            $result[$own][] = $abbr;
        }
        
        return $result;
    }
    
    /**
     * Берем последние активные оповещения
     * 
     * @global object $DB Подключение к БД
     * 
     * @param inetger $sbr_id      ИД СБР
     * @param integer $stage_id    ИД Этапа Сбр
     * @return array - ид транзакций оповещения
     */
    public function getNotificationActive($sbr_id, $stage_id) {
        global $DB;
        if(is_emp()) {
            $where = " AND se.estatus IS NULL";
        } else {
            $where = " AND se.fstatus IS NULL";
        }
        
        if(hasPermissions('sbr')  && $_SESSION['access']=='A') {
            $where = " AND (se.estatus IS NULL OR se.fstatus IS NULL)";
        }
        
        $sql = "(SELECT se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                WHERE se.sbr_id = ?i {$where} ORDER BY se.id DESC)
                
                UNION 

                (SELECT se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                INNER JOIN sbr_stages_msgs ssm ON ssm.id = se.own_id
                WHERE se.sbr_id = ? AND ssm.stage_id = ? AND sec.own_rel = 'sbr_stages' AND sec.abbr IN ('COMMENT', 'ARB_COMMENT') {$where} ORDER BY se.id DESC)
                
                ORDER BY xact_id DESC";
                
        return $DB->col($sql, $sbr_id, $sbr_id, $stage_id);
    }
    
    /**
     * Переводим оповещения о новном комментарии в просмотренное состояние (это значит что оно не будет браться через функцию self::getNotification())
     * 
     * @global object $DB Подключение к БД
     * 
     * @param inetger $sbr_id      ИД СБР
     * @param integer $stage_id    ИД Этапа Сбр
     * @return boolean 
     */
    public function setNotificationCommentViewCompleted($sbr_id, $stage_id) {
        global $DB;
        
        $where = is_emp() ? " AND se.estatus IS NULL" : " AND se.fstatus IS NULL";
        $name_fld = (is_emp() ? 'estatus': 'fstatus');
        
        $sql = "UPDATE sbr_events SET {$name_fld} = true WHERE xact_id IN 
                (SELECT se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                INNER JOIN sbr_stages_msgs ssm ON ssm.id = se.own_id
                WHERE se.sbr_id = ?i AND ssm.stage_id = ?i AND sec.own_rel = 'sbr_stages' AND sec.abbr IN ('COMMENT', 'ARB_COMMENT') {$where})";
        
        return $DB->query($sql, $sbr_id, $stage_id);
    }
    
    /**
     * Переводим конкретное оповещение в отработанное состояние (это значит что оно не будет браться через функцию self::getNotification())
     * 
     * @global object $DB Подключение к БД
     * 
     * @param array|string $name    Название оповещения @example sbr.AGREE или array('sbr.AGREE', 'sbr.OPEN');
     * @param integer $sbr_id       ИД сделки
     * @param inetger $own_id       Дополнительный ИД (может быть равен ИД сделки или ИД этапа) @see таблицу sbr_events
     */
    public function setNotificationCompleted($name, $sbr_id, $own_id) {
        global $DB;
        
        $event_name = self::parseEventName($name);
        $ev_codes   = array_map(create_function('$res', 'return $res["id"];'), self::getEventCode($event_name));
        
        $status = (is_emp() ? 'estatus': 'fstatus');
        $update[$status] = true;
        
        if(count($ev_codes) > 0) {
            $DB->update("sbr_events", $update, "own_id = ? AND sbr_id =? AND ev_code IN (?l) AND {$status} IS NOT true", $own_id, $sbr_id, $ev_codes);
        }
    }
    
    public function setNotificationCompletedAdmin($xact_id) {
        global $DB;
        $update = array('estatus' => true, 'fstatus' => true);
        return $DB->update("sbr_events", $update, "xact_id = ? AND (estatus IS NOT true OR fstatus IS NOT true) ", $xact_id);
    }
    
    public function sbr_add_event($XACT_ID, $sbr_id, $own_id, $abbr, $version, $foronly = null, $role = null) {
        global $DB;
        $sql = "SELECT sbr_add_event({$XACT_ID}, {$sbr_id}, {$own_id}, sbr_evc('{$abbr}'), {$version}, ?, ?i); ";
        return $DB->mquery($sql, $foronly, $role);
    }
    
    static public function getNotificationName($abbr, $type, $stages) {
        if($abbr == 'sbr_stages.FRL_FEEDBACK' && $type == 1 && !$stages->head_docs) {
            return '';
        }
        return self::$notification[$abbr][$type];
    }
}
?>