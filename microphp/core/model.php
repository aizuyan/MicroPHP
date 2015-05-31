<?php
/**
 * @FileName: model.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-05-02
 * @Desc 	: 模型文件基础类
 */
/**
 * 数据模型类基类
 */
class Model {
	/**
	 * 数据连接对象
	 */
	private static $_datas = array();

	/**
	 * 数据模型类对象
	 */
	private static $_models = array();

	/**
	 * 数据库连接对象，单表的
	 */
	protected $_link = null;

	/**
	 * @Desc 获取数据连接对象
	 * @Param $name string 数据库连接信息
	 * @Param $driver string 数据库驱动
	 * @Return object 数据库连接对象
	 */
	public static function DATA($name, $driver="db")
	{
		switch ($driver) 
		{
			case 'db':
				$class = 'Mypdo';
				if(!class_exists($class, false))
				{
					loadClass('Mypdo', 'lib', SYSTEM_FOLDER, false);
				}
				break;
			
			default:
				throw new AppException("model driver class not esists!!", E_USER_ERROR);
				break;
		}

		if(!isset(self::$_datas[$driver]))
		{
			self::$_datas[$driver] = array();
		}

		if(!isset(self::$_datas[$driver][$name]))
		{
			switch ($class) 
			{
				case 'Mypdo':
					//当前mod下寻找dbconfig配置文件
					$dbConfig = loadConfig('dbConfig', CONFIG_FOLDER, SYSTEM_FOLDER);
					if(!$dbConfig || !isset($dbConfig[$name]))
					{		
						throw new AppException("get db config name :[${name}] failed!!", E_USER_ERROR);
					}
					self::$_datas[$driver][$name] = new $class($dbConfig[$name]['dsn'], $dbConfig[$name]['user'], $dbConfig[$name]['passwd']);
					break;
				
				default:
					throw new AppException("class [${class}] don't have driver!!", E_USER_ERROR);
					break;
			}

		}
		return self::$_datas[$driver][$name];
	}

	private function __construct($link)
	{
		$this->_link = $link;
		return $this;
	}

	/**
	 * @Desc 获取一个数据表连接实例
	 * @Param $name string 模型名称：连接对象+模型名称
	 * @Param $driver string 连接类型，db、redis等，为以后扩展做准备
	 * @Return object
	 */
	public static function getInstance($name, $driver='db', $namespace='now')
	{
		$id = "{$driver}:{$name}:{$namespace}";
		if(!isset(self::$_models[$id]))
		{
			$namespace = ($namespace === 'now') ? MicroPHP::$actions['mod'] : $namespace;
			$pos = strrpos($name, '.');
			$mod_name = substr($name, intval($pos)+1);
			if(!$pos || !$mod_name)
			{
				output_404_json(404, "model instance {$id} does not exists!!");
				throw_exception("model instance {$name} does not exists!!", E_USER_ERROR);
			}

			$cls = ucfirst($mod_name).'_Model';
			if(!loadClass($mod_name, 'model', $namespace, false) || !class_exists($cls, false))
			{
				output_404_json(404, "file {$mod_name}.php does not exists!!");
				throw_exception("file {$mod_name}.php does not exists!!", E_USER_ERROR);
			}
			$config = substr($name, 0, $pos);
			$link = self::DATA($config, $driver);
			self::$_models[$id] = new $cls($link);
		}

		return self::$_models[$id];
	}
}
