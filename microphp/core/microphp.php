<?php
/**
 * @FileName: microphp.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-03-30
 * @Desc 	: 框架核心文件
 */
class MicroPHP
{
	//项目主目录
	public static $path;

	//项目环境
	public static $env;

	//日志操作对象
	public static $log;

	//输入输出对象
	public static $io;

	//命名空间对象namespace
	public static $ns;

	//请求接口名
	public static $actions;

	/**
	 * 触发一个事件
	 * @param string $event 事件名(事件处理类名.方法名)
	 * @param string $namespace 命名空间下面的，默认系统目录
	 * @return mixed|boolean 事件处理程序返回结果或失败时返回false
	 */
	public static function triggerEvent($event, $namespace=SYSTEM_FOLDER) 
	{
		$handle = explode('.', $event, 2);
		if(!$handle[0] || !$handle[1]) {
			return false;
		}
		if(!loadClass($handle[0], 'event', $namespace, false))
		{
			return false;
		}
		if(class_exists($handle[0], false) && method_exists($handle[0], $handle[1])) {
			$param = func_get_args();
			$param = array_slice($param, 2);
			return call_user_func_array($handle, $param);
		}
		return false;
	}
	/**
	 * 加载核心文件
	 */
	private static function _requireFiles()
	{
		require_once(self::$path . 'core/io.php');
		require_once(self::$path . 'core/namespacemap.php');
		require_once(self::$path . 'core/functions.common.php');
		require_once(self::$path . 'core/controller.php');
		require_once(self::$path . 'core/model.php');
		require_once(self::$path . 'core/appexception.php');
		require_once(self::$path . 'core/log.php');
	}

	/**
	 * 注册异常处理函数
	 */
	private static function _regErrHandler()
	{
		//注册异常捕获函数
		set_exception_handler(array("AppException", "exceptionHandler"));
		//注册错误捕获函数
		set_error_handler(array("AppException", "errorHandler"));
		//注册致命错误处理函数
		register_shutdown_function(array("AppException", "shutdownHandler"));
	}

	/**
	 * 项目开始
	 */
	public static function run($path) 
	{
		global $_CONFIG, $_INPUT, $_OUTPUT, $_DATA, $_DEBUG; //全局变量

		$_CONFIG = $_INPUT = $_DATA = $_DEBUG = array();

		//初始化变量
		self::$path = $path;
		self::$env = isset($_SERVER['MicroPHP_ENV']) ? $_SERVER['MicroPHP_ENV'] : 'product';
		//加载必要的文件
		self::_requireFiles();

		//实例化命名空间并添加框架命名空间
		self::$ns 	= new NamespaceMap();
		self::$ns->addMaps(SYSTEM_FOLDER, SYSTEM_PATH);
		//异常错误日志模块
		self::$log = Log::instance();
		self::triggerEvent('CoreEvent.initExceptionLog');
		self::_regErrHandler();

		try {
			self::$io	= IOFactory::autoCreate();

			//触发请求开始事件
			self::triggerEvent('CoreEvent.start');

			//获取命名空间注册namespace maps
			self::triggerEvent('CoreEvent.nsmaps');

			//获取请求数据
			self::$io->input();

			//分发请求
			self::triggerEvent('CoreEvent.dispatch');

			//创建控制类对象
			$controller = loadClass(self::$actions['controller'], CONTROLLER_FOLDER, self::$actions['mod']);
			if($controller && method_exists($controller, self::$actions['method'])) 
			{
				//调用控制器对象方法
				$_OUTPUT	= call_user_func(array($controller, self::$actions['method']));
			} else 
			{
				output_404_json(404, 'routes not found!!');
				throw new AppException("Not Found ".self::$actions['mod']."/".self::$actions['controller'].".".self::$actions['method']." !!", E_USER_WARNING);
			}
		} catch(Exception $e) 
		{
			$code = $e->getCode();
			if(self::$log && self::triggerEvent('CoreEvent.isCatchException', SYSTEM_FOLDER, $code))
			{
				AppException::exceptionHandler($e);
			}
		}
		//处理返回结果
		if(!isset($_OUTPUT))
		{
			output_404_json(404, 'no output!!');
		}

		//触发数据返回前事件
		self::triggerEvent('CoreEvent.output');
		self::$io->output($_OUTPUT);
		
		//触发请求处理完毕事件
		self::triggerEvent('CoreEvent.finish');
	}

}
