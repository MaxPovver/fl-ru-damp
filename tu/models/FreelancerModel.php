<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');

/**
 * Class UserModel
 * Модель пользователя - фрилансера
 */
class FreelancerModel extends atservices_model {

	private $TABLE_USERS = 'users';

	/**
	 * Для каждой строки массива $rows извлекает сведения о пользователе, ID которого указан в $id_attr
	 * Если $extend_attr указан, то сведения вписываются в строки rows отдельным ключом
	 * Иначе ключи строк расширяются извлечёнными сведениями, при необходимости им дописываются префиксы $extend_prefix
	 *
	 * @param $rows
	 * @param $id_attr
	 * @param $extend_attr
	 * @param $extend_prefix
	 * @return $this
	 */
	public function extend(&$rows, $id_attr, $extend_attr = null, $extend_prefix = '')
	{
		$ids = array();
		foreach($rows as $row) // собрать ID
		{
			if (!empty($row[$id_attr]))
			{
				$ids[$row[$id_attr]] = false;
			}
		}
		if (empty($ids))
		{
			return $this;
		}

		$sql = <<<SQL
SELECT
	u.uid as {$extend_prefix}uid,
	u.uname as {$extend_prefix}uname, -- имя
	u.usurname as {$extend_prefix}usurname, -- фамилия
	u.login as {$extend_prefix}login, -- логин пользователя
	u.photo as {$extend_prefix}photo, -- аватара max 100x100
	u.photosm as {$extend_prefix}photosm, -- мелкая аватара max 50x50
	u.role as {$extend_prefix}role, -- фрилансер/работодатель ...
    u.is_profi as {$extend_prefix}is_profi,
	u.is_pro as {$extend_prefix}is_pro, -- пользователь ПРО
	u.is_verify as {$extend_prefix}is_verify, -- пользователь верифицирован
	u.country as {$extend_prefix}country, -- cтрана
	u.city as {$extend_prefix}city -- город
FROM {$this->TABLE_USERS} u
WHERE u.uid in (?lu)
SQL;
		$extends = $this->db()->cache(300)->rows($sql, array_keys($ids));
		foreach($extends as $extend) // разобрать строки по ID
		{
			$ids[$extend['uid']] = $extend;
		}

		foreach($rows as &$row) // подставить дополнительные сведения в исходный список строк
		{
			if (empty($ids[$row[$id_attr]]))
			{
				continue;
			}
			$extend = $ids[$row[$id_attr]];
			if (false === $extend)
			{
				continue;
			}

			if ($extend_attr)
			{
				$row[$extend_attr] = $extend; // отдельный ключ
			} else
			{
				$row = array_merge($row, $extend); // расширение массива
			}
		}
		return $this;
	}

	/**
	 * @return FreelancerModel
	 */
	public static function model()
	{
		$class = get_called_class();
		return new $class;
	}
}