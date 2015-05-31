<?php
!defined('SYSTEM_PATH') && die("can not access direct！！");
/**
 * @FileName: io.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-03-30
 * @Desc 	: IO接口
 */
interface IOInterface {
	/**
	 * 输入
	 * @return array 接收到的数据
	 */
	public function input();

	/**
	 * 输出
	 * @param array $data 要输出的数据
	 */
	public function output($data);
}

/**
 * IO工厂类
 */
class IOFactory {
	/**
	 * 自动创建IO类对象
	 * @return IOInterface
	 */
	public static function autoCreate() {
		return new WebIO();
	}
}

/**
 * WEB常规IO
 */
class WebIO implements IOInterface {
	/**
	 * JSONP回调函数名
	 * @var string
	 */
	private $_jsonpCallback = '';

	/**
	 * @Param 输出时header编码
	 */
	public $charset = null;

	/**
	 * @Param 输出内容的类型(mime type)
	 */
	private $mime = 'text/html';

	private $char_sets = array('utf-8', 'GBK');

	private $mimes = array('txt/html', 'application/json', 'text/javascript');

	/**
	 * 输入
	 * @return array 接收到的数据
	 */
	public function input() 
	{
		if(isset($_REQUEST['callback']) && substr($_REQUEST['callback'], 0, 6) == 'jQuery') 
		{
			$this->_jsonpCallback = $_REQUEST['callback'];
		}

		//获取字符编码
		if(isset($_REQUEST['charset']) && in_array($_REQUEST['charset'], $this->char_sets))
		{
			$this->charset = $_REQUEST['charset'];
		}

		//获取输出文件的mimetype
		if(isset($_REQUEST['mime']) && in_array($_REQUEST['mime'], $this->mimes))
		{
			$this->mime = $_REQUEST['mime'];
		}
		else if($this->_jsonpCallback)
		{
			$this->mime = "text/javascript";
		}
		return;
	}

	/**
	 * 输出
	 * @param array $data 要输出的数据
	 */
	public function output($data) {
		if(is_array($data) && $data['content'] !== null) 
		{
			if(!headers_sent()) 
			{	
				$charset = $this->charset ? $this->charset : 'utf-8';
				header("Content-Type: {$this->mime}; charset={$charset}");
			}
			echo ($this->_jsonpCallback ? "{$this->_jsonpCallback}({$data[content]})" : $data['content']);
		}
	}
}