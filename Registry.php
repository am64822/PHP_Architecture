<?php

/* Шаблон проектирования «Registry» или «Реестр» представляет собой что то вроде массива, доступного на всех уровнях приложения и используется для передачи данных между модулями заменяя глобальные переменные. Паттерн реализован на статических методах и не требует создания экземпляра класса. ... */

/* Использование
В одной части сайта, например в контроллере, устанавливается значение:
Registry::set('sid', 'xxxxxx');
В другой, например в шаблоне, это значение выводится:
echo Registry::get('sid'); */

class Registry
{ 
	private static $_storage = array(); 
 
	/**
	 * Установка значения.
	 */
	public static function set($key, $value)
	{ 
		return self::$_storage[$key] = $value;
	}
 
	/**
	 * Получение значения.
	 */
	public static function get($key, $default = null)
	{
		return (isset(self::$_storage[$key])) ? self::$_storage[$key] : $default;
	}
 
	/**
	 * Удаление.
	 */
	public static function remove($key)
	{
		unset(self::$_storage[$key]); 
		return true;
	}
 
	/**
	 * Очистка.
	 */
	public static function clean()
	{
		self::$_storage = array(); 
		return true;
	}
}

/* Источник: https://snipp.ru/php/registry */