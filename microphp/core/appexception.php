<?php 
!defined('SYSTEM_PATH') && die("can not access direct！！");
/**
 * @FileName: appexception.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-03-30
 * @Desc 	: 异常类
 */
class AppException extends Exception {
	/**
	 * 构造函数
	 * @param string $message 错误消息
	 * @param int $code 错误类型
	 * @param mixed $previous 未知
	 */
	public function __construct($message = null, $code = E_USER_ERROR, $previous = null) {
		parent::__construct($message, $code);
	}

	/**
	 * 错误捕获处理
	 * @param string $code
	 * @param string $error
	 * @param string $file
	 * @param string $line
	 * @throws AppException
	 */
	public static function errorHandler($code, $error, $file = null, $line = null) {
		AppException::exceptionHandler(new ErrorException($error, $code, 0, $file, $line));
		return true;
	}

	/**
	 * shutdown处理函数，获取fatal错误
	 */
	public static function shutdownHandler(){
		$error = error_get_last();
		if($error){
			AppException::exceptionHandler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}
	}

	/**
	 * 异常处理函数
	 */
	public static function exceptionHandler($e) {
		$file = $e->getFile();
		$line = $e->getline();
		$code = $e->getCode();
		$message = $e->getMessage();
		if (MicroPHP::$log != null) {
			Log::$writeOnAdd = true;
			MicroPHP::$log->add(LOG_ERR, sprintf("[%s/%s.%s@%s#%d] (%d) %s", MicroPHP::$actions['mod'], MicroPHP::$actions['controller'], MicroPHP::$actions['method'], $file, $line, $code, $message));
		}
	}
}
