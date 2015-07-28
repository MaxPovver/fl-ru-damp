<?php

// просто запустить в браузере

require_once '../classes/stdf.php';

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM surveys");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE surveys_id_seq RESTART WITH ".($m['max_id']+1));

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM surveys_questions");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE surveys_questions_id_seq RESTART WITH ".($m['max_id']+1));

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM surveys_questions_options");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE surveys_questions_options_id_seq RESTART WITH ".($m['max_id']+1));

pg_query(DBConnect(), "START TRANSACTION");

$res = pg_query(DBConnect(), "INSERT INTO surveys (title, description, date_begin, date_end, code, visibility, thanks_text, u_count, e_count, f_count) VALUES (
'Сколько времени вы проводите в социальных сетях?',
'Друзья!<br/>Мы хотим знать про вас как можно больше. Поэтому очень просим вас поучаствовать в небольшом опросе, посвященном социальным сетям. Он займет у вас всего пару минут, а мы узнаем, каким социальным сетям вы отдаете предпочтение и почему. Спасибо!',
'2011-06-22 00:00:00',
'2011-07-01 00:00:00',
'',
1,
'Результаты теста помогут нам лучше узнать вас. Спасибо, что уделили нам время и ответили на все вопросы!',
0,
0,
0
) RETURNING id");
list($opros_id) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('Есть ли у вас аккаунты в социальных сетях?', '', 2, 't', 1, 1, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('В какой социальной сети вы бываете наиболее часто?', '', 2, 't', 1, 2, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('Как часто вы заходите в социальную сеть?', '', 2, 't', 1, 3, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('Сколько времени в общей сложности вы проводите в социальной сети в день?', '', 2, 't', 1, 4, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('Отвлекают ли вас социальные сети от работы?', '', 2, 't', 1, 5, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_5) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('Заходите ли вы в социальные сети во время работы над проектом?', '', 2, 't', 1, 6, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_6) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('Находили ли вы заказчиков/исполнителей в социальных сетях?', '', 2, 't', 1, 7, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_7) = pg_fetch_row($res);




//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('да', 'f', 'f', 0, {$question_1}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_11) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('нет', 'f', 'f', 0, {$question_1}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_12) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('Facebook', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_21) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('ВКонтакте', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_22) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('Мой мир', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 3, 0, 0, 0)
RETURNING id");
list($answer_23) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('Одноклассники', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 4, 0, 0, 0)
RETURNING id");
list($answer_24) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('Twitter', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 5, 0, 0, 0)
RETURNING id");
list($answer_25) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('реже одного раза в день', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_31) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('1-3 раза в день', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_32) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('3-7 раз в день', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 3, 0, 0, 0)
RETURNING id");
list($answer_33) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('7-15 раз в день', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 4, 0, 0, 0)
RETURNING id");
list($answer_34) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('более 15 раз в день', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 5, 0, 0, 0)
RETURNING id");
list($answer_35) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('менее часа', 'f', 'f', 0, {$question_4}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_41) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('1-3 часа', 'f', 'f', 0, {$question_4}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_42) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('3-5 часов', 'f', 'f', 0, {$question_4}, 'f', NULL, NULL, 3, 0, 0, 0)
RETURNING id");
list($answer_43) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('более 5 часов', 'f', 'f', 0, {$question_4}, 'f', NULL, NULL, 4, 0, 0, 0)
RETURNING id");
list($answer_44) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('да', 'f', 'f', 0, {$question_5}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_51) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('нет', 'f', 'f', 0, {$question_5}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_52) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('часто', 'f', 'f', 0, {$question_6}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_61) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('иногда', 'f', 'f', 0, {$question_6}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_62) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('никогда', 'f', 'f', 0, {$question_6}, 'f', NULL, NULL, 3, 0, 0, 0)
RETURNING id");
list($answer_63) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('да', 'f', 'f', 0, {$question_7}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_71) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('нет', 'f', 'f', 0, {$question_7}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_72) = pg_fetch_row($res);


pg_query(DBConnect(), "COMMIT");

echo "Done";
