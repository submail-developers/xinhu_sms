<?php


define('IN_SYS', true);

$config = require './webmain/webmainConfig.php';

if ($config['db_host']) {

	$mysql_server_name = $config['db_host'];
	$mysql_username = $config['db_user'];
	$mysql_password = $config['db_pass'];
	$mysql_DB_BASE = $config['db_base'];
	$mysql_tablepre = $config['perfix'];
} else {
	$mysql_server_name = $config['db_host'];
	$mysql_username = $config['db_user'];
	$mysql_password = $config['db_pass'];
	$mysql_DB_BASE = $config['db_base'];
	$mysql_tablepre = $config['perfix'];
}

$link = new mysqli($mysql_server_name, $mysql_username, $mysql_password);

// 获取错误信息
$error = $link->connect_error;
if (!is_null($error)) {
	// 转义防止和alert中的引号冲突
	$error = addslashes($error);
	die("<script>alert('数据库链接失败:$error');history.go(-1)</script>");
}
// 设置字符集
$link->query("SET NAMES 'utf8'");
$link->server_info > 5.0 or die("<script>alert('请将您的mysql升级到5.0以上');history.go(-1)</script>");
// 创建数据库并选中
if (!$link->select_db($mysql_DB_BASE)) {
	$create_sql = 'CREATE DATABASE IF NOT EXISTS ' . $mysql_DB_BASE . ' DEFAULT CHARACTER SET utf8;';
	$link->query($create_sql) or die('创建数据库失败');
	$link->select_db($mysql_DB_BASE);
}
// 导入sql数据并创建表
$shujuku_str = file_get_contents('./submail_install.sql');
$sql_array = preg_split("/;[\r\n]+/", str_replace('ls_', $mysql_tablepre, $shujuku_str));
foreach ($sql_array as $k => $v) {
	if (!empty($v)) {
		$link->query($v);
	}
}
$link->close();

echo "<h4>信呼OA赛邮云短信插件安装成功，请删除此文件。</h4>";

function sreadfile($filename)
{
	$content = '';
	if (function_exists('file_get_contents')) {
		@$content = file_get_contents($filename);
	} else {
		if (@$fp = fopen($filename, 'r')) {
			@$content = fread($fp, filesize($filename));
			@fclose($fp);
		}
	}
	return $content;
}

?>
