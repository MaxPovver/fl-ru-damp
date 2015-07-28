<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_catalog.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/yii/tinyyii.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/FreelancerModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceFilter.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceFreelancersCategories.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceNavigation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceCatalogHeader.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceCatalogCategories.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceBindTeaser.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceBindTeaserShort.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceBindLinks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupTservicebind.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupTservicebindup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");


class TServiceCatalogController extends CController {

	/** @var TServiceFilter */
	public $filter_widget;
    private $is_adm;
    private $uid;


    public function init() 
    {
		parent::init();
        
        stat_collector::setStamp(); // stamp
        
        $this->uid = get_uid();
        $this->is_adm = hasPermissions('tservices');
                
		// разметка страницы с левым сайдбаром
		$this->layout = '//layouts/content-with-right-sidebar';

		// в сайдбаре вывести фильтр с учётом текущей категории
		$this->getClips()->add('sidebar', $this->widget('TServiceFilter', array(/*без опций*/), true)); // чтобы отрисовать фильтр и опции

		# TODO добиться, чтобы $this->widget('TServiceFilter') и $this->createWidget($this,'TServiceFilter') возвращал один и тот же объект
		$this->filter_widget = $this->createWidget($this,'TServiceFilter', array(/*без опций*/)); // копия, чтобы узнать, какие опции были выбраны
        
        $prof_id = $this->filter_widget->filter->category 
                ? $this->filter_widget->filter->category 
                : $this->filter_widget->filter->category_group;

        //----------------------------------------------------------------------
        
        //@todo: возможно нужно общее хранилище собираемых данных 
        //в течении работы скрипта с последующей передачей в GA и Adriver?
        
        GaJsHelper::getInstance()->setTuCategories(
                $this->filter_widget->filter->category_group,
                $this->filter_widget->filter->category);
        
        adriver::getInstance()->setTuCategories(
                $this->filter_widget->filter->category_group,
                $this->filter_widget->filter->category);        
        
        //----------------------------------------------------------------------        
        
        
        SeoTags::getInstance()->initTserviceList($prof_id, $this->filter_widget->filter->category > 0);
        
        
        $this->getClips()->add('header', $this->widget('TServiceNavigation', array(
            'category_group' => $this->filter_widget->filter->category_group,
            'category' => $this->filter_widget->filter->category,
            'filter_get_params' => $this->filter_widget->getUserFriendlyUrl(false)
        ), true));
        
        $this->getClips()->add('content_top', $this->widget('TServiceCatalogHeader', array(/*без опций*/), true));
        
        $this->getClips()->add('categories', $this->widget('TServiceCatalogCategories', array(
            'category_group' => $this->filter_widget->filter->category_group,
            'filter_get_params' => $this->filter_widget->getUserFriendlyUrl(false)
        ), true));

		// в футере каталога вывести список специализаций фрилансеров
		$this->getClips()->add('footer', $this->widget('TServiceFreelancersCategories', array(/*без опций*/), true));

        $tserviceModel = TServiceModel::model();
		
        $this->counter_users = $tserviceModel->countUsers();
        $this->counter_tu = $tserviceModel->countTservices();

    }

	/**
	 * Отображение страницы /tu/
	 */
	public function actionIndex() {

        $uid = get_uid();
        
		$page = __paramInit('int', 'page', 'page', 1);
		$limit = 21;
        
        $empty_criteria = $this->filter_widget->filter->isEmpty();

        $prof_id = $this->filter_widget->filter->category 
                ? $this->filter_widget->filter->category 
                : $this->filter_widget->filter->category_group;

        $tserviceModel = TServiceModel::model();
        $freelancerModel = FreelancerModel::model();
        $tservicesCatalogModel = new tservices_catalog();
		$tservicesCatalogModel->category_id = $prof_id;
        
        
        $kind = tservices_binds::KIND_ROOT;
        if ($this->filter_widget->filter->category) {
            $kind = tservices_binds::KIND_SPEC;
        } elseif ($this->filter_widget->filter->category_group) {
            $kind = tservices_binds::KIND_GROUP;
        } 
        
        
        if ($page == 1 && $uid && !is_emp()) {
            $this->getClips()->add('bind_teaser', $this->widget('TServiceBindTeaser', array(
                'kind' => $kind,
                'uid' => $uid,
                'prof_id' => $prof_id,
                'is_inner' => !$empty_criteria
            ), true));
            $this->getClips()->add('bind_teaser_short', $this->widget('TServiceBindTeaserShort', array(), true));
        }
        
        $free_places = true;
        
        //Сначала берем закрепленные
        $tservicesCatalogModel->setPage($limit, $page);
        $tservices_binded = $tservicesCatalogModel->getBindedList($kind); //Тут только для текущей страницы
        $tservices_binded_ids = $tservicesCatalogModel->getBindedIds($kind); //Тут для всех страниц
        $count_binded = count($tservices_binded_ids);
        $count_binded_cur_page = count($tservices_binded);
        if ($count_binded_cur_page) {
            // расширение сведений о типовых услугах
            $tserviceModel
                ->extend($tservices_binded, 'id')
                ->readVideos($tservices_binded, 'videos', 'videos'); // во всех строках "распаковать" массив видео-клипов

            // расширение сведений о пользователях
            $freelancerModel->extend($tservices_binded, 'user_id', 'user');

            //Добавляем попапы продления и поднятия к услугам текущего юзера
            foreach ($tservices_binded as $key=>$tservice) {
                $is_owner = $tservice['user_id'] == $uid;
                if ($is_owner) {
                    $this->getClips()->add('bind_links_'.$tservice['id'], $this->widget('TServiceBindLinks', array(
                        'kind' => $kind,
                        'uid' => $uid,
                        'is_inner' => !$empty_criteria,
                        'date_stop' => $tservice['date_stop'],
                        'allow_up' => $page > 1 || $key > 0,
                        'tservice_id' => $tservice['id']
                    ), true));
                    
                    
                    if (quickPaymentPopupTservicebind::getInstance()->inited == false) {
                        quickPaymentPopupTservicebind::getInstance()->init(array(
                            'uid' => $uid,
                            'kind' => $kind,
                            'prof_id' => $prof_id
                        ));
                    }

                    $popup_id = quickPaymentPopupTservicebind::getInstance()->getPopupId($tservice['id']);
                    $popups[] = quickPaymentPopupTservicebind::getInstance()->render(array(
                        'is_prolong' => true,
                        'date_stop' => $tservice['date_stop'],
                        'popup_id' => $popup_id,
                        'tservices_cur' => $tservice['id'],
                        'tservices_cur_text' => $tservice['title']
                    ));

                    if ($key > 0) {
                        if (quickPaymentPopupTservicebindup::getInstance()->inited == false) {
                            quickPaymentPopupTservicebindup::getInstance()->init(array(
                                'uid' => $uid,
                                'tservices_id' => $tservice['id'],
                                'tservices_title' => $tservice['title'],
                                'kind' => $kind,
                                'prof_id' => $prof_id
                            ));
                        }

                        $popup_id = quickPaymentPopupTservicebindup::getInstance()->getPopupId($tservice['id']);
                        $popups[] = quickPaymentPopupTservicebindup::getInstance()->render(array(
                            'popup_id' => $popup_id,
                            'tservices_cur' => $tservice['id'],
                            'tservices_cur_text' => $tservice['title']
                        ));
                    }
                }
            }
    
            $free_places = $count_binded_cur_page < $limit;
        }
        
        if ($free_places) { //Есть места для отображения незакрепленных услуг

            $tservicesCatalogModel->keywords = $this->filter_widget->filter->keywords;
            $tservicesCatalogModel->price_ranges = $this->filter_widget->filter->prices;
            $tservicesCatalogModel->price_max = $this->filter_widget->filter->price_max;
            $tservicesCatalogModel->country_id = $this->filter_widget->filter->country;
            $tservicesCatalogModel->city_id = $this->filter_widget->filter->city;
            $tservicesCatalogModel->order = $this->filter_widget->filter->order;
            $tservicesCatalogModel->setPage($limit, $page, $count_binded, $count_binded_cur_page);
            
            // поиск записей
            $list = $tservicesCatalogModel->cache(300)->getList($tservices_binded_ids);
            $tservices_search = $list['list'];
            $total = $list['total'];

            // расширение сведений о типовых услугах
            $tserviceModel
                ->extend($tservices_search, 'id')
                ->readVideos($tservices_search, 'videos', 'videos'); // во всех строках "распаковать" массив видео-клипов

            // расширение сведений о пользователях
            $freelancerModel->extend($tservices_search, 'user_id', 'user');
        }

        $tservices = $tservices_binded;
        foreach ($tservices_search as $tservice) {
            if (count($tservices) < $limit && !in_array($tservice['id'], $tservices_binded_ids)) {
                $tservices[] = $tservice;
            }
        }
        
        $tservicesCatalogModel2 = new tservices_catalog();
        $tservicesCatalogModel2->category_id = $prof_id;
        $tservicesCatalogModel2->order = TServiceFilter::ORDER_PRICE_ASC;
        $tservicesCatalogModel2->setPage(1, 1);
        $list2 = $tservicesCatalogModel2->cache(300)->getList();
        $min_price = $list2['list'][0]['price'];
        SeoTags::getInstance()->initTserviceList($prof_id, $this->filter_widget->filter->category > 0, $total, $min_price);
        
		$view_name = !$empty_criteria ? 'list' : 'tile';
        $this->is_main = $empty_criteria;
                
                /*
		if ($empty_criteria)
		{
			// над списком типовых услуг вывести рекламный блок раздела
			require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceCatalogPromo.php');
			$this->getClips()->add('content-promo', $this->widget('TServiceCatalogPromo', array(), true));
		}
                */

        $tservices_binds = new tservices_binds($kind);
        
		$this->render($view_name, array(
			//'category_title' => $this->filter_widget->getCategoryTitle() ? $this->filter_widget->getCategoryTitle() : $this->filter_widget->getCategoryGroupTitle(),
			'category_title' => $this->filter_widget->getCategoryAngGroupTitle(' / '),
            'total' => $total,
			'nothing_found' => empty($tservices), // ничего не найдено
			'tservices' => $tservices,
			'page' => $tservicesCatalogModel->page,
			'limit' => $limit,
			'paging_base_url' => $this->filter_widget->getUserFriendlyUrl(),
            'is_adm' => $this->is_adm,
            'orders' => $this->filter_widget->getAllowedOrders(true),
            'cur_order' => $this->filter_widget->filter->order,
            'uid' => $uid,
            'popups' => $popups,
            'bind_up_price' => $tservices_binds->getPrice(true, $uid, $prof_id)
		));
	}
}
