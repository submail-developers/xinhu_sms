<?php
header("Content-type:text/html;charset=utf-8");
$config = include('./common/config/config.php');

$conn = mysqli_connect($config['db_host'],$config['db_user'],$config['db_pwd'],$config['db_name']);


$newsql = sreadfile("submail.sql");
$sqls = explode(";", $newsql);

foreach ($sqls as $sql) {
	if (empty($sql)) {
		continue;
	}
	if (!$con = $con = mysqli_query($conn,$sql)) {
		echo "执行sql语句成功".mysqli_error($conn);
		exit();
	}
}

echo "<h4>赛邮云短信插件安装成功，请删除此文件。</h4>";

function sreadfile($filename){
	$content = '';
	if(function_exists('file_get_contents')) {
		@$content = file_get_contents($filename);
	} else {
		if(@$fp = fopen($filename, 'r')) {
			@$content = fread($fp, filesize($filename));
			@fclose($fp);
		}
	}
	return $content;
}
