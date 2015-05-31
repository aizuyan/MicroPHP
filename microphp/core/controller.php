<?php
!defined('SYSTEM_PATH') && die("can not access direct！！");
/**
 * @FileName: controller.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-03-31
 * @Desc 	: 控制器基类
 */
class Controller {
	/**
	 * @Param smarty模版
	 */
	private static $smarty = null;

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		if(self::$smarty === null)
		{
			self::$smarty = loadClass('Smarty', '3rd/Smarty', SYSTEM_FOLDER);
			if(!self::$smarty)
			{
				throw new AppException("loading smarty trigger a error!!", E_USER_ERROR);
				return false;
			}

			$config = loadConfig('3rd.config', CONFIG_FOLDER, SYSTEM_FOLDER);
			if(!$config || !is_array($config))
			{
				$template_dir = MicroPHP::$ns->getMap(MicroPHP::$actions['mod']).DIRECTORY_SEPARATOR.VIEW_FOLDER.DIRECTORY_SEPARATOR;
				$compile_dir = $template_dir.'compile_dir'.DIRECTORY_SEPARATOR;
			}else
			{
				$template_dir = isset($config['smarty_config']['template_dir']) ? $config['smarty_config']['template_dir'] : MicroPHP::$ns->getMap(MicroPHP::$actions['mod']).DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR;
				$compile_dir = isset($config['smarty_config']['compile_dir']) ? $config['smarty_config']['compile_dir'] : $template_dir.'compile_dir'.DIRECTORY_SEPARATOR;
			}
			is_dir($template_dir) OR mkdir($template_dir, 0777, true);
			is_dir($compile_dir) OR mkdir($compile_dir, 0777, true);
			if(!is_dir($template_dir) || !is_dir($compile_dir))
			{
				throw new AppException("smarty view path can't esists!!", E_USER_ERROR);
			}

			self::$smarty->template_dir = $template_dir;
			self::$smarty->compile_dir = $compile_dir;
			#关闭smarty的warning和notice
			self::$smarty->muteExpectedErrors();
		}
	
		//开启ob缓存，用于获取数据
		ob_start();
	}

	/**
	 * 析构函数
	 */
	public function __destruct()
	{
	}

	/**
	 * @Desc 	: 获取变量
	 * @Param $name string 变量名称
	 * @Param $type string 变量类型
	 * @Param $defVal mixed 变量默认值
	 * @Return false/mixed
	 */
	public function getValue($name, $defVal="", $type="GET")
	{
		if(empty($name))
		{
			return false;
		}
		$type = strtoupper($type);
		switch ($type) {
			case 'GET':
				$retVal = isset($_GET[$name]) ? $_GET[$name] : null;
				break;

			case 'POST':
				$retVal = isset($_POST[$name]) ? $_POST[$name] : null;
				break;

			case 'COOKIE':
				$retVal = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
				break;
			
			default:
				return false;
				break;
		}

		//由于数据库编码全是GBK，这里获取变量的时候进行一次转换
		if(isset($retVal) && MicroPHP::$io->charset != null && MicroPHP::$io->charset == "utf-8")
		{
			$retVal = iconv('UTF-8', 'GB2312//IGNORE', $retVal);
		}
		return isset($retVal) ? $retVal : $defVal;
	}

	//设置smarty变量
	protected function assignVal($name, $value)
	{
		self::$smarty->assign($name, $value);
	}

	/**
	 * @Desc smarty渲染制定的模版文件
	 * @Param $tpl string 模版文件路径
	 */
	protected function render($tpl)
	{
		self::$smarty->display($tpl);
		$_ret['content'] = ob_get_contents();
		ob_end_clean();
		return $_ret;
	}
}

