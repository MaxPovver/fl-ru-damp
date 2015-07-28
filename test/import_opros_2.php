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
'Фултайм VS Фри-ланс',
'<p>Привет!</p> <p>Мы вас любим и интересуемся вашей жизнью и успехами во фри-лансе. Пожалуйста, поучаствуйте в опросе. Это займет 3-5 минут вашего времени.</p>',
B'1111',
TRUE,
TRUE,
'fulltime'
) RETURNING id");
list($opros_id) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Вы работали раньше на фултайме в компании?', $opros_id, 0, 1, 1, 2)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Хотели бы вы вернуться в офис?', $opros_id, 0, 1, 2, 2)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Какие, по вашему мнению, преимущества у фри-ланса перед офисной работой?', $opros_id, 0, 1, 3, 1)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Какие преимущества офисной работы перед фри-лансом?', $opros_id, 0, 1, 4, 1)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Вам тяжело не иметь стабильного места работы в компании?', $opros_id, 0, 1, 5, 2)
RETURNING id");
list($question_5) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('Вы бы пошли работать в офис, если бы:', $opros_id, 0, 1, 6, 2)
RETURNING id");
list($question_6) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Да', $question_1, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет', $question_1, 2, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Да', $question_2, 3, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет', $question_2, 4, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Можно делать только то, что нравится', $question_3, 5, 5, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_5) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Работа более творческая', $question_3, 6, 6, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_6) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Все время разные проекты', $question_3, 7, 7, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_7) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Свободный рабочий день', $question_3, 8, 8, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_8) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет необходимости ездить в офис, можно работать где угодно', $question_3, 9, 9, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_9) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Больше свободного времени', $question_3, 10, 10, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_10) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Менее нервная работа', $question_3, 11, 11, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_11) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ваш вариант', $question_3, 12, 12, TRUE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_12) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Стабильность в оплате и уверенность в работе', $question_4, 13, 13, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_13) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Коллектив, общение с коллегами', $question_4, 14, 14, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_14) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет необходимости искать работу', $question_4, 15, 15, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_15) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Идет трудовой стаж', $question_4, 16, 16, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_16) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Накапливается пенсия', $question_4, 17, 17, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_17) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Социальный пакет', $question_4, 18, 18, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_18) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Оплачиваемый отпуск', $question_4, 19, 19, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_19) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('В офисе легче работать, чем дома', $question_4, 20, 20, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_20) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ваш вариант', $question_4, 21, 21, TRUE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_21) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Да, это напрягает, нет уверенности', $question_5, 22, 22, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_22) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Нет, для меня это намного комфортнее', $question_5, 23, 23, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_23) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Вам предложили очень высокую зарплату', $question_6, 24, 24, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_24) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Вас назначили большим начальником', $question_6, 25, 25, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_25) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Вам предложили интересный проект', $question_6, 26, 26, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_26) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('Ваш вариант', $question_6, 27, 27, TRUE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_27) = pg_fetch_row($res);

pg_query(DBConnect(), "COMMIT");

echo "Done";