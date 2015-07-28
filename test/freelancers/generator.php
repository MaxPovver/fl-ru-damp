<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

function getName($is_array = false) {

	$first = array(
	    'Абрам', 'Аваз', 'Аввакум', 'Август', 'Августин', 'Авдей', 'Авенир',
	    'Аверьян', 'Авксентий', 'Автандил', 'Агап', 'Агафон', 'Агей', 'Адам',
	    'Адильхан', 'Адис', 'Адольф', 'Адриан', 'Азад', 'Азамат', 'Азар',
        'Азарий', 'Азиз', 'Акакий', 'Аким', 'Алан', 'Александр', 'Алексей',
        'Али', 'Алим', 'Алискер', 'Алихан', 'Алоис', 'Алферий', 'Альберт',
        'Альбин', 'Альфред', 'Алянур', 'Амадей', 'Амадеус', 'Амаяк', 'Амвросий',
        'Анастас', 'Анатолий', 'Анвар', 'Ангел', 'Андоим', 'Андрей', 'Андрон',
        'Андроп', 'Анжей', 'Аникита', 'Анисим', 'Антип', 'Антон', 'Ануфрий',
        'Анфим', 'Аполлон', 'Арам', 'Ардалион', 'Арий', 'Аристарх', 'Аристон',
        'Аркадий', 'Арно', 'Арнольд', 'Арон', 'Арсен', 'Арслан', 'Арулан',
        'Арье', 'Артамон', 'Артем', 'Артемий', 'Артур', 'Архип', 'Аршак',
        'Аршиддин', 'Аскар', 'Аскольд', 'Атанас', 'Афанасий', 'Афиноген',
        'Африкан', 'Ахмет', 'Ашот', 'Бадри',  'Базан', 'Байракдар', 'Бари',
        'Барузда', 'Батыр' 
	);

	$second = array(
	    'Иванов', 'Петров',	'Сидоров', 'Воська', 'Семаченкуй', 'Барыжников',
	    'Сурок', 'Белый', 'Федософ-Ледовских', 'Онянов-Зенина', 'Казаков'
	);

	return ($is_array)
        ? array($first[array_rand($first)],$second[array_rand($second)])
        : $first[array_rand($first)] . ' ' . $second[array_rand($second)];
}

$nums = 50000;
$role = 0;


$last_id = $DB->val("SELECT uid FROM users ORDER BY uid DESC LIMIT 1");
$city_ids = $DB->col("SELECT id FROM city WHERE country_id = 1");
        
$user = new users();
        
$rolesize = $GLOBALS['rolesize'];

for($i = 0; $i < $nums; $i++) {
    $last_id++;
    $fullname = explode(' ', getName());
    $ip = rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255) . '.' . rand(1, 255);
    
    $prefix = ($role == 0)?'freelancer':'employer';
    
    $login = $prefix . $last_id;
    
    if($DB->val('SELECT uid FROM users WHERE login = ?',$login))
            continue;
    
    $sql = $DB->parse("INSERT INTO users
        (login, uname, usurname, passwd, email, role, reg_date, reg_ip, last_time, last_ip, sex, active, self_deleted, country, city, icq, skype, is_pro, jabber, phone, ljuser, site, is_verify) 
        VALUES 
        (?, ?, ?, ?, lower(?), B'$role'::bit($rolesize),current_date, ?, now(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);

        SELECT currval('users_uid_seq');",
            $login,
            $fullname[0],
            $fullname[1],
            $user->hashPasswd('123456'),
            $login . '@test.lo',
            $ip,
            $ip,
            (bool)rand(0,1),
            TRUE,
            FALSE,//rand(0,1),//self_deleted
            1,
            $city_ids[array_rand($city_ids)],//rand(1,500),
            '123456789',
            $login,
            (bool)rand(0,1),
            '123456789',
            "911-911-911",
            $login,
            'http://google.com',
            (bool)rand(0,1)
    );
            
    $last_id = $DB->val($sql);
}

echo $last_id;