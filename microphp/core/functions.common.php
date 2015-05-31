<?php
/**
 * @FileName: functions.common.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-04-19
 * @Desc 	: 公共函数库，运行框架的时候加载
 */

/**
 * @Desc 	: 加载类
 */
if(!function_exists("loadClass"))
{
	/**
	 * @Param $class string 类名（也是文件名）
	 * @Param $directory string 目录，命名空间下的某个目录
	 * @Param $namespace string 命名空间
	 * @Param $isnew boolean true/false 实例化后返回/仅仅将文件包含到环境中
	 * @Param $param string 参数，如果实例化的时候传递的对象
	 */

	function loadClass($class, $directory, $namespace='', $isnew=true, $param=null)
	{
		static $_classes = array();
		//处理namespace
		if($namespace == '')
		{
			if(!isset(MicroPHP::$actions['mod']))
			{
				output_404_json(404, "Wrong namespace when loadClass!!");
				throw new AppException("Error namespace when loadClass : class => {$class}, directory => {$directory}, namespace => {$namespace}", null);
				return false;
			}
			$namespace = MicroPHP::$actions['mod'];
		}
		$load_flag = "{$namespace}_{$directory}_{$class}_".($isnew ? 1 : 0)."_{$param}";
		//加载过的不重新加载
		if(!$isnew)
		{
			$load_flag_fake = "{$namespace}_{$directory}_{$class}_1_{$param}";
			if(isset($_classes[$load_flag]) || isset($_classes[$load_flag_fake])) return true;
		} else
		{
			if(isset($_classes[$load_flag])) return $_classes[$load_flag];
		}
		
		//命名空间对应的路径，$NS为实例化的namespace对象，框架入口中有加载
		$ns_dir = MicroPHP::$ns->getMap($namespace);
		if(!$ns_dir || !is_dir($ns_dir))
		{				
			output_404_json(404, "namespace not found!!");
			throw new AppException("namespace {$namespace} not found!!", E_USER_NOTICE);
			return false;
		}

		//要包含/实例化的文件路径，EXT为文件的扩展名，在框架入口中定义
		$file = $ns_dir.DIRECTORY_SEPARATOR.$directory.DIRECTORY_SEPARATOR.$class.EXT;
		if(!file_exists($file))
		{
			output_404_json(404, "file not found!!");
			throw new AppException("file {$file} not found!!", E_USER_NOTICE);
			return false;
		}
		if(!include_once($file)) return false;
		if(!$isnew)
		{
			//不用实例化的标记已经加载过
			$_classes[$load_flag] = true;
			return true;
		}

		$directory = strtolower($directory);
		if($directory == 'controller')
		{
			$class = $class."_Controller";
		}
		if(!class_exists($class, false))
		{
			output_404_json(404, "class not found!!");
			throw new AppException("class {$class} not found!!", E_USER_NOTICE);
			return false;
		}
		//将加载的内容存储到变量中，这里的参数如果不同可能出现问题
		$_classes[$load_flag] = $param !== null ? new $class($param) : new $class();
		return $_classes[$load_flag];
	}
}

/**
 * @Desc 	: 加载配置文件
 */
if(!function_exists('loadConfig'))
{
	/**
	 * @Desc 	: 加载配置文件
	 * @Param $namespace string 命名空间
	 * @Param $directory string 目录，命名空间下的某个目录
	 * @Param $file string 文件
	 * @Param $isret boolean true/false 加载配置文件后是否返回
	 */
	function loadConfig($file, $directory, $namespace='', $isret=true)
	{
		static $_configs = array();
		if($namespace == '')
		{
			if(!isset(MicroPHP::$actions['mod']))
			{
				output_404_json(404, "Wrong namespace when loadClass!!");
				throw new AppException("Error namespace when loadClass : class => {$class}, directory => {$directory}, namespace => {$namespace}", null);
				return false;
			}
			$namespace = MicroPHP::$actions['mod'];
		}
		$load_flag = "{$namespace}_{$directory}_{$file}_".($isret ? 1 : 0);
		//加载过的不重新加载
		if(!$isret)
		{
			$load_flag_fake = "{$namespace}_{$directory}_{$file}_1";
			if(isset($_configs[$load_flag]) || isset($_configs[$load_flag_fake])) return true;
		} else
		{
			if(isset($_configs[$load_flag])) return $_configs[$load_flag];
		}
		//命名空间对应的路径，$NS为实例化的namespace对象，框架入口中有加载
		$ns_dir = MicroPHP::$ns->getMap($namespace);
		if(!$ns_dir || !is_dir($ns_dir))
		{
			return false;
		}

		//要包含/实例化的文件路径，EXT为文件的扩展名，在框架入口中定义
		$file = $ns_dir.DIRECTORY_SEPARATOR.$directory.DIRECTORY_SEPARATOR.$file.EXT;
		if(!file_exists($file))
		{
			return false;
		}
		//TODO这里优化，不存在的设置为false，不用重复加载
		if(!($config = include_once($file)))	return false;
		if(!$isret)
		{
			$_configs[$load_flag] = true;
			return true;
		}
		$_configs[$load_flag] = $config;
		return $_configs[$load_flag];		
	}
}

if(!function_exists('output_404_json'))
{
	/**
	 * @Desc 	: 抛出404格式的json
	 */
	function output_404_json($code=404, $msg="routes not found!!")
	{
		global $_OUTPUT;
		//$_OUTPUT['down'] = false;
		$_OUTPUT['content'] = json_encode(array("code" => $code, "msg" => $msg));
	}
}


// if(!function_exists('LOGS_DEBUG'))
// {
// 	function LOGS_DEBUG($msg)
// 	{
// 		if(!UserLog::isInit())
// 		{
// 			throw new AppException("before use LOGS_DEBUG, must init userlogs", 1);
// 		}
// 		//获取文件和行号
// 		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
// 		UserLog::getInstance()->debug($msg, $backtrace[0]['file'], $backtrace[0]['line']);
// 	}
// }
// if(!function_exists('LOGS_MSG'))
// {
// 	function LOGS_MSG($msg)
// 	{
// 		if(!UserLog::isInit())
// 		{
// 			throw new AppException("before use LOGS_MSG, must init userlogs", 1);
// 		}
// 		//获取文件和行号
// 		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
// 		UserLog::getInstance()->msg($msg, $backtrace[0]['file'], $backtrace[0]['line']);
// 	}
// }
// if(!function_exists('LOGS_ERR'))
// {
// 	function LOGS_ERR($msg)
// 	{
// 		if(!UserLog::isInit())
// 		{
// 			throw new AppException("before use LOGS_ERR, must init userlogs", 1);
// 		}
// 		//获取文件和行号
// 		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
// 		UserLog::getInstance()->err($msg, $backtrace[0]['file'], $backtrace[0]['line']);
// 	}
// }

// /**
//  * 获取配置中心的配置ip:port
//  */
// if(!function_exists('getConfigCenter'))
// {
// 	 function getConfigCenter($service_name, $set_id, $route_key, &$ip, &$port)
// 	{
// 	    $net = configcenter4_get_serv($service_name, $set_id, $route_key);        

// 	    $pos = strpos($net, ":");
// 	    if ($pos === false)
// 	    {
// 	        return false;
// 	    }
// 	    else
// 	    {
// 	        $ip = substr($net, 0, $pos);
// 	        $port = substr($net, $pos+1);
// 	    }        
// 	    return true;
// 	}
// }
