<?php
//defined('SYSTEM_PATH') or die("No direct script access allowed");
/**
 * @FileName: namespacemap.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-04-19
 * @Desc 	: 命名空间映射类 namespace map
 */
class NamespaceMap {
	/**
	 * @Desc 	: 映射关系hash数组
	 */
	protected static $maps = null;

	/**
	 * @Desc 	: 命名空间中允许的字符
	 */
	private $namespace_char = "/^[a-zA-Z\\\\]+$/";

	/**
	 * @Desc 	: 目录的合法字符
	 */
	private $dir_char = "/^[a-zA-Z\/\:\\\\]+$/";

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		self::$maps = array();
	}

	/**
	 * @Desc 	: 添加命名空间和目录映射关系
	 * @param $name string/array 命名空间名称/hash数组
	 * @param $dir stinrg 目录
	 */
	public function addMaps($name, $dir='', $iscover=true)
	{
		if(empty($name)) return false;
		if(is_string($name))
		{
			if(!$this->isLegalName($name) || !$this->isLegalDir($dir))
			{
				return false;
			}
			if(!array_key_exists($name, self::$maps) || $iscover)
			{
				self::$maps[$name] = $dir;
				return true;
			}
			return false;
		}
		//批量添加
		if(is_array($name))
		{
			if($iscover)
			{
				self::$maps = array_merge(self::$maps, $name);
			} else
			{
				self::$maps = array_merge($name, self::$maps);
			}
			return true;
		}
	}

	/**
	 * @Desc 	: 获取命名空间的内容
	 * @param $name string 命名空间的名称
	 */
	public function getMap($name)
	{
		if(!$name)
		{
			return '';
		}
		if(!$this->isLegalName($name) || !array_key_exists($name, self::$maps)) return '';
		return self::$maps[$name];
	}

	/**
	 * @Desc 	: 判断命名空间名称中是否有非法字符串
	 */
	public function isLegalName($name)
	{
		return preg_match($this->namespace_char, $name);
	}

	/**
	 * @Desc 	: 判断目录字符是否合法
	 */
	public function isLegalDir($dir)
	{
		return preg_match($this->dir_char, $dir);
	}
	public function pr()
	{
		print_r(self::$maps);
	}
}