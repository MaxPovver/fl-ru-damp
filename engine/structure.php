<?php
/**
 * Карта роутинга движка
 * Ключ - имя адреса,
 * Значение - связанный пласс + (необязательно) метод
 * 
 * /test/ => $map = array("test" => array("class"=>"test"));
 * /test/action/ => $map = array("test" => array("class"=>"test")); -> вызван в классе test будет метод  actionAction
 * /test/action2/ => $map = array("test" => array("class"=>"test", "action2"=>array("class"=>"action2"))); -> вызван в классе action2 будет метод  indexAction
 * Любая вложенность адреса и гибкость
 * @var
 */
$map = array(
    "press" => array("class"=>"press"),
    "about" => array("class"=>"about"),
    "myblog" => array("class"=>"mycorp"),
    "test" => array("class"=>"test"),
    "bill" => array("class"=>"bill"),
    
    
    //Р’ РґРІРёР¶РєРµ Р¶РµСЃС‚РєРѕ РїСЂРѕРІРµСЂСЏСЋС‚СЃСЏ РІС‹Р·РѕРІС‹ action С‡РµСЂРµР· http, РІС‹РѕР·РІСЂР°С‰Р°РµС‚СЃСЏ 404, РµСЃР»Рё РЅРµ РјРѕРґРµСЂР°С‚РѕСЂ РёР»Рё Р°РґРјРёРЅ!
    "adminback" => array("class"=>"admin",
        "news"=>array("class"=>"admin_news"),
        "static_pages"=>array("class"=>"admin_static_pages"),
        "tests"=>array("class"=>"admin_tests"),
        "cblog"=>array("class"=>"admin_cblog"),
        "faq"=>array("class"=>"admin_faq"),
        "team"=>array("class"=>"admin_team"),
        "smi"=>array("class"=>"admin_smi"),
        "opinions"=>array("class"=>"admin_opinions"),
        "partners"=>array("class"=>"admin_partners"),
        "flashUpload"=>array("class"=>"admin_flash_upload2"),
    ),
    "flash"=>array("class"=>"admin_flash_upload2"),
    
	"pda2" => array("class"=>"pda_index",
		"login"=>array("class"=>"pda_index", "method" => "login"),
		"logout"=>array("class"=>"pda_index", "method" => "logout"),
		"blogs"=>array("class"=>"pda_blogs"),
		"contacts"=>array("class"=>"pda_contacts"),
		),
);

?>
