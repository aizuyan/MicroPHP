<?php
!defined('SYSTEM_PATH') && die("can not access direct！！");
/**
 * @FileName: Mypdo.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-05-10
 * @Desc 	: Pdo访问数据库驱动
 */
class Mypdo extends PDO {
	protected $_pdo = null;
	/**
	 * 构造函数
	 */
	public function __construct($dsn, $username="", $passwd="", $driver_options=array())
	{
		parent::__construct($dsn, $username, $passwd, $driver_options);
		//出错之后抛出异常
		$this->_pdo = $this;
		$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * 关闭链接
	 */
	public function close()
	{
		$this->pdo = null;
	}

	/**
	 * 析构函数
	 */
	public function __destruct()
	{
		$this->close();
	}
}