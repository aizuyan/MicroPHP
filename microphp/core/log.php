<?php
/**
 * 日志写入类
 * @author jasydong
 * @package qipai
 */

/**
 * 日志写入类
 */
class Log {
	// Log message levels - Windows users see PHP Bug #18090
	const EMERGENCY = LOG_EMERG;    // 0，紧急情况，需要立即通知技术人员
	const ALERT     = LOG_ALERT;    // 1，应该立即被修正的问题
	const CRITICAL  = LOG_CRIT;     // 2，重要情况
	const ERROR     = LOG_ERR;      // 3，错误
	const WARNING   = LOG_WARNING;  // 4，警告信息，不是错误
	const NOTICE    = LOG_NOTICE;   // 5，不是错误情况，不需要立即处理
	const INFO      = LOG_INFO;     // 6，情报信息，正常的系统信息
	const DEBUG     = LOG_DEBUG;	// 7，调试信息
	
	

	/**
	 * @var  string  timestamp
	 */
	public static $timestamp = 'Y-m-d H:i:s';

	/**
	 * @var  boolean  immediately write when logs are added
	 */
	public static $writeOnAdd = false;

	/**
	 * @var  Log  Singleton instance container
	 */
	protected static $_instance;

	/**
	 * Get the singleton instance of this class and enable writing at shutdown.
	 *
	 *     $log = Log::instance();
	 *
	 * @return  Log
	 */
	public static function instance() {
		if (Log::$_instance === null) {
			// Create a new instance
			Log::$_instance = new Log;

			// Write the logs at shutdown
 			register_shutdown_function(array(Log::$_instance, 'write'));
		}

		return Log::$_instance;
	}

	/**
	 * @var  array  list of added messages
	 */
	protected $_messages = array();
	
	/**
	 * @var  array  list of log writers
	*/
	protected $_writers = array();

	/**
	 * Attaches a log writer, and optionally limits the levels of messages that
	 * will be written by the writer.
	 *
	 *     $log->attach($writer);
	 *
	 * @param   object   Log_Writer instance
	 * @param   mixed    array of messages levels to write OR max level to write
	 * @param   integer  min level to write IF $levels is not an array
	 * @return  Log
	*/
	public function attach(Logger $writer, $levels = array(), $min_level = 0) {
		if ( ! is_array($levels)) {
			$levels = range($min_level, $levels);
		}
	
		$this->_writers["{$writer}"] = array (
				'object' => $writer,
				'levels' => $levels
		);
	
		return $this;
	}

	/**
	 * Detaches a log writer. The same writer object must be used.
	 *
	 *     $log->detach($writer);
	 *
	 * @param   object  Log_Writer instance
	 * @return  Log
	 */
	public function detach(Logger $writer) {
		// Remove the writer
		unset($this->_writers["{$writer}"]);
	
		return $this;
	}

	/**
	 * Adds a message to the log. Replacement values must be passed in to be
	 * replaced using [strtr](http://php.net/strtr).
	 *
	 *     $log->add(Log::ERROR, 'Could not locate user: :user', array(
	 *         ':user' => $username,
	 *     ));
	 *
	 * @param   string  level of message
	 * @param   string  message body
	 * @param   array   values to replace in the message
	 * @return  Log
	 */
	public function add($level, $message, array $values = null) {
		if ($values) {
			// Insert the values into the message
			$message = strtr($message, $values);
		}

		// Create a new message and timestamp it
		$this->_messages[] = array (
				'time'  => date(Log::$timestamp, time()),
				'level' => $level,
				'body'  => $message,
		);
		if (Log::$writeOnAdd) {
			// Write logs as they are added
			$this->write();
		}

		return $this;
	}
	
	/**
	 * Write and clear all of the messages.
	 *
	 *     $log->write();
	 *
	 * @return  void
	 */
	public function write() {
		if (empty($this->_messages)) {
			// There is nothing to write, move along
			return;
		}
	
		// Import all messages locally
		$messages = $this->_messages;
	
		// Reset the messages array
		$this->_messages = array();
		foreach ($this->_writers as $writer) {
			if (empty($writer['levels'])) {
				// Write all of the messages
				$writer['object']->write($messages);
			} else {
				// Filtered messages
				$filtered = array();
				foreach ($messages as $message) {
					if (in_array($message['level'], $writer['levels'])) {
						// Writer accepts this kind of message
						$filtered[] = $message;
					}
				}
				// Write the filtered messages
				$writer['object']->write($filtered);
			}
		}
	}
}

/**
 * File log writer. Writes out messages and stores them in a YYYY/MM directory.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Logger {

	const FILE_EXT = '.php';

	/**
	 * @var  string  Directory to place log files in
	 */
	protected $_directory;

	/**
	 * Creates a new file logger. Checks that the directory exists and
	 * is writable.
	 *
	 *     $writer = new Log_File($directory);
	 *
	 * @param   string  log directory
	 * @return  void
	 */
	public function __construct($directory) {
		if (!is_dir($directory) || !is_writable($directory)) {
			if(!mkdir($directory, 0777, true))
			{
				echo "Log directory must be writable\n";
				exit();
			}
		}

		// Determine the directory path
		$this->_directory = realpath($directory).DIRECTORY_SEPARATOR;
	}

	/**
	 * Writes each of the messages into the log file. The log file will be
	 * appended to the `YYYY/MM/DD.log.php` file, where YYYY is the current
	 * year, MM is the current month, and DD is the current day.
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array   messages
	 * @return  void
	 */
	public function write(array $messages) {
		// Set the yearly directory name
		$directory = $this->_directory.date('Y');

		if ( ! is_dir($directory)) {
			// Create the yearly directory
			mkdir($directory, 02777);

			// Set permissions (must be manually set to fix umask issues)
			chmod($directory, 02777);
		}

		// Add the month to the directory
		$directory .= DIRECTORY_SEPARATOR.date('m');

		if ( ! is_dir($directory)) {
			// Create the monthly directory
			mkdir($directory, 02777);

			// Set permissions (must be manually set to fix umask issues)
			chmod($directory, 02777);
		}

		// Set the name of the log file
		$filename = $directory.DIRECTORY_SEPARATOR.date('d').self::FILE_EXT;

		if ( ! file_exists($filename)) {
			// Create the log file
			touch($filename);
			// Allow anyone to write to log files
			chmod($filename, 0666);
		}

		foreach ($messages as $message) {
			// Write each message into the log file
			file_put_contents($filename, PHP_EOL.$message['time'].' --- '.$message['level'].': '.$message['body'], FILE_APPEND);
		}
	}

	public function __toString() {
		return spl_object_hash($this);
	}
}

final class UserLog {
	private $level;
	private $maxFileNum;
	private $maxFileSize;
	private $logPath;
	private $file;

	private static $isinit = false;

	//用户日志的级别DEBUG，MSG，ERR
	const LOGS_DEBUG = 0;
	const LOGS_MSG = 1;
	const LOGS_ERR = 2;

	private static $instance = null;

	private function __construct()
	{
	}

	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function isInit()
	{
		return self::$isinit;
	}

	/**
	 * @Desc 初始化
	 * @Param $level int 记录级别
	 * @Param $maxNum int 最大日志文件数目
	 * @Param $maxSize int 最大日志文件大小
	 * @Param $logPath string 日志文件保存路径
	 * @Param $file string 日志文件名称前缀
	 * @Return boolean
	 */
	public function init($level, $maxNum, $maxSize, $logPath, $file)
	{
		$level = intval($level);
		$maxNum = intval($maxNum);
		$maxSize = intval($maxSize);
		!is_dir($logPath) && mkdir($logPath, 0777, true);
		if(!in_array($level, array(self::LOGS_DEBUG, self::LOGS_MSG, self::LOGS_ERR)) || $maxNum <= 0 || $maxSize <= 0 || !is_dir($logPath))
		{
			return false;
		}
		$this->level = $level;
		$this->maxFileNum = $maxNum;
		$this->maxFileSize = $maxSize;
		$this->logPath = $logPath;
		$this->file = $file;
		self::$isinit = true;
		return true;
	}

	/**
	 * @Desc 获取格式化时间串
	 */
	public function formatTime()
	{
        $ustime = explode ( " ", microtime () );
        return "[" . date('Y-m-d H:i:s', time()) .".". ($ustime[0] * 1000) . "]";
	}

	/**	
	 * @Desc 滚动方式记录日志文件
	 */
	public function log($str)
	{
		$path = $this->logPath.DIRECTORY_SEPARATOR.$this->file.".log";
		clearstatcache();
		if(file_exists($path))
		{
			if(filesize($path) >= $this->maxFileSize)
			{
				$index = 1;
				//获取最大的滚动日志数目
				for(;$index < $this->maxFileNum; $index++)
				{
					if(!file_exists($this->logPath.DIRECTORY_SEPARATOR.$this->file."_".$index.".log"))
					{
						break;
					}
				}
				//已经存在maxFileNum个日志文件了
				if($index == $this->maxFileNum)
				{
					$index--;
				}
				//滚动日志
				for(;$index > 1; $index--)
				{
					$new = $this->logPath.DIRECTORY_SEPARATOR.$this->file."_".$index.".log";
					$old = $this->logPath.DIRECTORY_SEPARATOR.$this->file."_".($index - 1).".log";
					rename($old, $new);
				}

				$newFile = $this->logPath.DIRECTORY_SEPARATOR.$this->file."_1.log";
				rename($path, $newFile);
			}
		}
		$fp = fopen($path, "a+b");
		fwrite($fp, $str, strlen($str));
		fclose($fp);
		return true;
	}

	/**
	 * @Desc 记录调试信息
	 * @Param string 日志信息
	 * @Param string 日志所在文件
	 * @Param string 日志所在行
	 */
	public function debug($msg, $file, $line)
	{
		if($this->level <= self::LOGS_DEBUG)
		{
			$this->log($this->formatTime()."[{$file}:{$line}]DEBUG: ${msg}\n");
		}
	}

	/**
	 * @Desc 记录信息
	 * @Param string 日志信息
	 * @Param string 日志所在文件
	 * @Param string 日志所在行
	 */
	public function msg($msg, $file, $line)
	{
		if($this->level <= self::LOGS_MSG)
		{
			$this->log($this->formatTime()."[{$file}:{$line}]MSG: ${msg}\n");
		}
	}

	/**
	 * @Desc 记录错误信息
	 * @Param string 日志信息
	 * @Param string 日志所在文件
	 * @Param string 日志所在行
	 */
	public function err($msg, $file, $line)
	{
		if($this->level <= self::LOGS_ERR)
		{
			$this->log($this->formatTime()."[{$file}:{$line}]ERR: ${msg}\n");
		}
	}
}
