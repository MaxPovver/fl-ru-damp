<?php

// просто запустить в браузере

require_once '../classes/stdf.php';

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM opros");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE opros_id_seq RESTART WITH ".($m['max_id']+1));

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM opros_questions");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE opros_questions_id_seq RESTART WITH ".($m['max_id']+1));

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM opros_answers");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE opros_answers_id_seq RESTART WITH ".($m['max_id']+1));

pg_query(DBConnect(), "START TRANSACTION");

$res = pg_query(DBConnect(), "INSERT INTO opros (name, descr, flags, is_active, is_multi_page, content) VALUES ('Заработок фрилансеров', 'Привет, друзья! Многие, в том числе и вы сами, спорят о заработках фрилансеров. Мы решили прояснить эту ситуацию с вашей помощью. Пожалуйста, ответьте на несколько вопросов о ваших заработках. Это отнимет у вас несколько минут.', B'1110', true, false, '') RETURNING id");
list($opros_id) = pg_fetch_row($res);




$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Как давно вы занимаетесь фри-лансом?', $opros_id, 1, 1, 1, 2)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Совмещаете ли вы фри-ланс с офисной работой?', $opros_id, 1, 1, 2, 2)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Хватает ли вам гонораров, которые вы зарабатываете на фри-лансе?', $opros_id, 1, 1, 3, 2)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Ваша сфера деятельности:', $opros_id, 1, 1, 4, 2)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Ваш средний ежемесячный доход на фри-лансе (важны результаты в зависимости от вопросов 2, 3 и 4)', $opros_id, 1, 1, 5, 2)
RETURNING id");
list($question_5) = pg_fetch_row($res);



// 1
$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('меньше года', $question_1, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('от года до трех лет', $question_1, 2, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('от трех до пяти лет', $question_1, 3, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('более пяти лет', $question_1, 4, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");


// 2
$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Да', $question_2, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет', $question_2, 2, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");


// 3
$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Да', $question_3, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет', $question_3, 2, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");


// 4
$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('разработка сайтов', $question_4, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('программирование', $question_4, 2, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('дизайн', $question_4, 3, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('копирайтинг', $question_4, 4, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('переводы', $question_4, 5, 5, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('оптимизация (SEO)', $question_4, 6, 6, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('архитектура/интерьер', $question_4, 7, 7, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('фотография', $question_4, 8, 8, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('аудио/видео', $question_4, 9, 9, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('3D графика', $question_4, 10, 10, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('флеш', $question_4, 11, 11, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('полиграфия', $question_4, 12, 12, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('анимация/мультипликация', $question_4, 13, 13, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('маркетинг/реклама/PR', $question_4, 14, 14, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");


// 5
$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('менее 10 000 рублей', $question_5, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('10 000 – 25 000 рублей', $question_5, 2, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('25 000 – 40 000 рублей', $question_5, 3, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('40 000 – 60 000 рублей', $question_5, 4, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('60 000 – 80 000 рублей', $question_5, 5, 5, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('80 000 – 100 000 рублей', $question_5, 6, 6, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('100 000 – 120 000 рублей', $question_5, 7, 7, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('120 000 – 150 000 рублей', $question_5, 8, 8, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('более 150 000 рублей', $question_5, 9, 9, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");




pg_query(DBConnect(), "COMMIT");

echo "Done";
