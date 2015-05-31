<?php
/**
 * @FileName: CoreEvent.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-03-30
 * @Desc 	: 主控程序事件
 */
class CoreEvent {
	/**
	 * 异常错误处理初始化
	 */
	public static function initExceptionLog()
	{
		switch (MicroPHP::$env) {
			case 'development':
				error_reporting(-1);
				ini_set('display_errors', 1);
				MicroPHP::$log->attach(new Logger(MicroPHP::$path."data/log/"));		//dev 环境记录报告所有错误
				break;

			case 'test':
				error_reporting(E_ALL & ~E_NOTICE);
				ini_set('display_errors', 0);
				MicroPHP::$log->attach(new Logger(MicroPHP::$path."data/log/"), LOG_NOTICE);		//gamma 环境记录报告所有错误
				break;

			case 'product':
				error_reporting(E_ALL & ~E_NOTICE);
				ini_set('display_errors', 0);
				MicroPHP::$log->attach(new Logger(MicroPHP::$path."data/log/"), LOG_ERR);		//IDC 环境记录报告所有错误
				break;
			
			default:
				throw new AppException("init ExceptionLog error, the env not support!!", E_USER_ERROR);		
				break;
		}
	}


	/**
	 * 初始化事件(设置配置、初始化操作)
	 */
	public static function start() 
	{
		date_default_timezone_set('PRC');
		//WEB模式下对自动转义的变量取消转义
		if(get_magic_quotes_gpc() && PHP_SAPI != 'cli') {
			if(!empty($_GET)) {
				foreach($_GET as &$v) {
					$v = unesc($v);
				}
			}
			if(!empty($_POST)) {
				foreach($_POST as &$v) {
					$v = unesc($v);
				}
			}
			if(!empty($_COOKIE)) {
				foreach($_COOKIE as &$v) {
					$v = unesc($v);
				}
			}
		}
	}

	/**
	 * 加载命名空间映射关系
	 */
	public static function nsmaps()
	{
		$maps = (array)loadConfig( 'namespacemap', CONFIG_FOLDER, SYSTEM_FOLDER);
		MicroPHP::$ns->addMaps($maps);
	}

	/**
	 * 数据获取后事件(可做数据安全验证)
	 */
	public static function input() {
	}

	/**
	 * 分发后事件
	 */
	public static function dispatch() 
	{
		if(!isset($_SERVER['PATH_INFO']))
		{
			output_404_json(404, 'routes not found!!');
			throw new AppException("not support pathinfo format!!", E_USER_NOTICE);
			return false;
		}
		//匹配pathinfo为 /namespace/controller.method 的形式
		if(!preg_match("/^\/[a-zA-Z0-9]+\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/", $_SERVER['PATH_INFO']))
		{
			output_404_json(404, 'routes not found!!');
			throw new AppException("pathinfo contain wrong chars!!", E_USER_NOTICE);
			return false;
		}

		$acts = explode("/", ltrim($_SERVER['PATH_INFO'], "/"), 2);
		MicroPHP::$actions['mod'] = array_shift($acts);
		$actions = explode(".", array_shift($acts));
		MicroPHP::$actions['controller'] = $actions[0];
		MicroPHP::$actions['method'] = $actions[1];
		return true;
	}

	/**
	 * 加载用户日志模块，区别于异常日志
	 */
	public static function initUserLog()
	{
		$userlogs_config = loadConfig('sphp', 'config', 'userlogs');
		$userlogs_config = $userlogs_config['userlogs'];
		if(!is_array($userlogs_config))
		{
			throw new AppException("userlogs not config, please chech the config file", null);
		}

		if(!isset($userlogs_config['default']) && !isset($userlogs_config[MicroPHP::$actions['mod']]))
		{
			throw new AppException("userlogs don't have default and mod [".MicroPHP::$actions[mod]."]", null);
		}

		$config = isset($userlogs_config[MicroPHP::$actions['mod']]) ? $userlogs_config[MicroPHP::$actions['mod']] : $userlogs_config['default'];
		
		$filename = isset($config['filename']) ? $config['filename'] : 'default';
		switch ($filename) {
			//设置文件名，根据规则，默认是mcm（mod.controller.method）格式的
			case 'mcm':
			case 'default':
			default:
				$filename = implode(".", MicroPHP::$actions);
				break;
		}

		if(!UserLog::getInstance()->init($config['level'], $config['maxnum'], $config['maxsize'], $config['path'], $filename))
		{
			throw new AppException("init userlogs error!", null);
		}
	}

	public static function isCatchException($code)
	{
		switch (MicroPHP::$env) {
			case 'development':
				return true;
				break;

			case 'test':
			case 'product':
				if(in_array($code, array(E_USER_WARNING, E_USER_NOTICE)))
				{
					return false;
				}else
				{
					return true;
				}
				break;
			
			default:
				return false;
				break;
		}
	}
	
	/**
	 * 数据返回前事件(修改结果)
	 */
	public static function output() {
	}

	/**
	 * 结束事件
	 */
	public static function finish() {
		
	}
}
