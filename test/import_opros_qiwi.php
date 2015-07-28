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

$res = pg_query(DBConnect(), "INSERT INTO opros (name, descr, flags, is_active, is_multi_page, content) VALUES (
'Qiwi 1',
'',
B'1111',
TRUE,
TRUE,
'qiwi'
) RETURNING id");
list($opros_id) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Используете ли вы QIWI Кошелек в мобильном телефоне?', $opros_id, 0, 1, 1, 2)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Какие услуги вы оплачиваете через QIWI Кошелек?', $opros_id, 0, 1, 2, 2)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Почему вы пользуетесь для оплаты услуг QIWI Кошельком?', $opros_id, 0, 1, 3, 2)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Как часто вы пользуетесь электронным кошельком?', $opros_id, 0, 1, 4, 2)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Есть ли у вас электронный QIWI Кошелек?', $opros_id, 0, 1, 5, 2)
RETURNING id");
list($question_5) = pg_fetch_row($res);





$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Да, очень удобно', $question_1, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет, впервые про это слышу', $question_1, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Иногда', $question_1, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('А что такое QIWI Кошелек?', $question_1, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Покупка билетов', $question_2, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Выплата банковских кредитов', $question_2, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('А что такое QIWI Кошелек?', $question_2, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ничего не оплачиваю, мне самому платят в QIWI Кошелек!', $question_2, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Оплата поступает мгновенно', $question_3, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Потому что нет процентов', $question_3, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('QIWI Кошелек всегда под рукой (в компьютере, телефоне)', $question_3, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ничего не знаю о QIWI Кошельке', $question_3, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);




$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ежедневно', $question_4, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ежемесячно', $question_4, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Иногда', $question_4, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Не пользуюсь', $question_4, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Есть, и я использую его как для получения денег, так и для оплаты', $question_5, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет, я не знал, что он существует', $question_5, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет, мне хватает уже существующих электронных кошельков', $question_5, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Я не пользуюсь электронными кошельками', $question_5, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



///////////////////////////////////////////////



$res = pg_query(DBConnect(), "INSERT INTO opros (name, descr, flags, is_active, is_multi_page, content) VALUES (
'Qiwi 2',
'',
B'1111',
TRUE,
TRUE,
'qiwi'
) RETURNING id");
list($opros_id) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Пользуетесь ли вы QIWI Кошельком в мобильном?', $opros_id, 0, 1, 1, 2)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Что такое QIWI?', $opros_id, 0, 1, 2, 2)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Какой вид кошелька действительно существует?', $opros_id, 0, 1, 3, 2)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Что такое электронный кошелек?', $opros_id, 0, 1, 4, 2)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Что вы знаете о QIWI Кошельке?', $opros_id, 0, 1, 5, 2)
RETURNING id");
list($question_5) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Для вас электронные деньги – это:', $opros_id, 0, 1, 6, 2)
RETURNING id");
list($question_6) = pg_fetch_row($res);




$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Да, конечно', $question_1, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('А что такое QIWI?', $question_1, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('А что такое мобильный телефон?', $question_1, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ой, я даже и не знаю...', $question_1, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Крем для обуви', $question_2, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нелетающая смешная птица, которая вымерла', $question_2, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Электронный кошелёк', $question_2, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Зеленый волосатый фрукт', $question_2, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нанокошелек', $question_3, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Электронный кошелек', $question_3, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Генетический кошелек', $question_3, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Космический кошелек', $question_3, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);




$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Средство хранения электронных денег', $question_4, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Кошелек, состоящий из электронов', $question_4, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Новая государственная программа Правительства РФ', $question_4, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Неудачный ответ мужа на вопрос жены: \"Куда деньги дел?\"', $question_4, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Все: постоянно использую', $question_5, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Пользовался пару раз', $question_5, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ничего не знаю, а что это?', $question_5, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Хочу узнать больше!', $question_5, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);


$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Наше все!', $question_6, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Удобно и быстро', $question_6, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Универсальный способ расчета с заказчиком', $question_6, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('QIWI Кошелек!', $question_6, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);

pg_query(DBConnect(), "COMMIT");

echo "Done";
