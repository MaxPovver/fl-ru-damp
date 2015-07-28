<?php



//------------------------------------------------------------------------------


/**
 * Список ID категорий для которых доступен функционал 
 * резервирования в заказах ТУ по "новой БС". 
 * Категории ТУ по таблице tservices_categories.
 * 
 * Если пуст или не существует то доступен всем категориям
 */
$allow_categories_to_reserve = array(  
/*    
10,
2,
13,
9,
7,
27,
56,
230,
11,
96,
198,
28,
39,
130,
252,
26,    
0*/
);


/**
 * Список Логин пользователей которым 
 * доступен функционал резервирования в заказах ТУ по "новой БС"
 * 
 * Если пуст или не существует то доступен всем пользователям
 */
$allow_users_to_reserve = array(
    /*
    'local' => array(
        'alex',
        'dezinger',
        'kazakov',
        'employer33',
        'danil_emp2',
        'danil61',
        'freelancer7',
        'freelancer4',
        'employer101',
        'employer104'
    ),
    'beta' => array(
        'lkj99',
        'danil5',
        'danil_wtf',
        'tehrabota_6',
        'NIGGAtiff',
        'DOWNshifter',
        'vg_rabot4',
        'tester10',
        'vg_rabot1',
        'tester11',
        'tester17',
        'vg_rabot6',
        'vg_rabot3'
    ),
    'alpha' => array(
        'lirex',
        'bolvan1',
        'funtik1',
        'vg_rabot4',
        'vg_tester'
    ),
    'release' => array(
        'evient_',
        'fl-test',
        'comedie',
        'comedie1',
        'Dragoneye',
        'winter2001',
        'winter_2011',
        'testuser4', 
        'testuser7',
        'testuser', 
        'prorab32'
    )*/
);




//------------------------------------------------------------------------------



/**
 * Список логинов которым 
 * доступен новый заказ ТУ
 * 
 * Если пуст или не существует
 * то новый заказ доступен всем
 */
$order_whitelist = array(
    /*
    'alex',
    'employer33',
    'freelancer4',
    'alex2013',
    'lkj99',
    'NIGGAtiff',
    'tester10',
    '0--',
    'tester17',
    'bolvan1',
    'admin',
    'lirex',
    'givejob10',
    'givejob11',
    'givejob115',
    'givejob13',
    'givejob14',
    'DOWNshifter',
    'vg_rabot1', 
    'vg_rabot5', 
    'tester20',
    'test11',
    'spring2002',
    'dezinger',
    'danil',
    'danil_emp2',
    'danil_wtf',
    'onyanov'
*/
);