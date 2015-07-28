<?php


class ReservesArbitrageException extends Exception
{
     const NOT_ALLOWED = 'Нехватает прав для уплавление арбитражем.';
     const CLOSE_FAIL  = 'Неудалось закрыть арбитраж.';
}