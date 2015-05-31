<?php
/**
 * @FileName: index.php
 * @Author 	: yanruitao(luluyrt@163.com)
 * @date  	: 2015-05-25
 * @Desc 	: 入口文件
 */
//系统文件夹
define('SYSTEM_FOLDER', 'microphp');
//应用文件夹
define('APP_FOLDER', 'application');
//默认控制器文件夹名称
define('CONTROLLER_FOLDER', 'controller');
define('MODEL_FOLDER', 'model');
define('VIEW_FOLDER', 'view');
define('CONFIG_FOLDER', 'config');
define('EXT', '.php');



//系统文件夹路径
define('SYSTEM_PATH', __DIR__.DIRECTORY_SEPARATOR.SYSTEM_FOLDER.DIRECTORY_SEPARATOR);
//应用文件夹路径
define('APP_PATH', __DIR__.DIRECTORY_SEPARATOR.APP_FOLDER.DIRECTORY_SEPARATOR);

//判断是否存在路径，不存在直接返回
if(!is_dir(SYSTEM_PATH) || !is_dir(APP_PATH))
{
	exit("system path or application path or system or app variable doesn't exists!!please set them!!");
}
else
{
	//加载核心框架文件
	require_once(SYSTEM_PATH."core/microphp.php");


	MicroPHP::run(SYSTEM_PATH);
}
