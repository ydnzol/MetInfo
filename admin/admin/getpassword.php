<?php
# 文件名称:getpassword.php 2009-08-15 16:34:57
# MetInfo企业网站管理系统 
# Copyright (C) 长沙米拓信息技术有限公司 (http://www.metinfo.cn). All rights reserved.
require_once '../include/common.inc.php';
if($action=="getpassword"){
$admin_list = $db->get_one("SELECT * FROM $met_admin_table WHERE admin_id='$admin_name'");
if(!$admin_list){
okinfo('getpassword.php',$lang_NoidJS);
}
else{
$from=$met_fd_usename;
$fromname=$met_fd_fromname;
$to=$admin_list[admin_email];
$usename=$met_fd_usename;
$usepassword=$met_fd_password;
$smtp=$met_fd_smtp;

$random = mt_rand(1000, 9999);
$passwords=date('Ymd').$random;
$getpass=$passwords;
$passwords=md5($passwords);

$query = "update $met_admin_table SET
          admin_pass         = '$passwords' 
		  where admin_id='$admin_name'";
$db->query($query);

$title=$met_c_webname.$lang_getNotice;
$body="$lang_getTip1 [".$met_c_webname."]".$met_weburl.$lang_getTip2.$getpass.$lang_getTip3;
require_once '../../include/jmail.php';
jmailsend($from,$fromname,$to,$title,$body,$usename,$usepassword,$smtp);
okinfo('../index.php',$lang_NewPassJS);
}
}else{
echo "<html xmlns='http://www.w3.org/1999/xhtml' lang='zh-cn'><head><title>MetInfo";
echo $lang_metinfo;
echo "</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/></head><body>";
echo "<br>{$lang_getTip4}：<form method='post' action='getpassword.php?action=getpassword'><input type='text' name='admin_name' size='20'/><input   type='submit' name='Submit' value=' $lang_getTip5 '> <form>";
echo "</body></html>";
}
# 本程序是一个开源系统,使用时请你仔细阅读使用协议,商业用途请自觉购买商业授权.
# Copyright (C) 长沙米拓信息技术有限公司 (http://www.metinfo.cn). All rights reserved.
?>