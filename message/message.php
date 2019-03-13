<?php
# MetInfo Enterprise Content Management System 
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved. 
require_once '../include/common.inc.php';
$settings = parse_ini_file('config_'.$lang.'.inc.php');
@extract($settings);
$ip=$m_user_ip;
$message_column=$db->get_one("select * from $met_column where module='7' and lang='$lang'");
$metaccess=$message_column[access];
$class1=$message_column[id];
require_once '../include/head.php';
	$class1_info=$class_list[$class1][releclass]?$class_list[$class_list[$class1][releclass]]:$class_list[$class1];
	$class2_info=$class_list[$class1][releclass]?$class_list[$class1]:$class_list[$class2];
$navtitle=$message_column[name];
if($action=="add"){
$addtime=$m_now_date;
$ipok=$db->get_one("select * from $met_message where ip='$ip' order by addtime desc");
$time1 = strtotime($ipok[addtime]);
$time2 = strtotime($m_now_date);
$timeok= (float)($time2-$time1);
if($timeok<=$met_fd_time){

$fd_time="{$lang_Feedback1} ".$met_fd_time." {$lang_Feedback2}";

okinfo('javascript:history.back();',$fd_time);
}

$fdstr = $met_fd_word; 
$fdarray=explode("|",$fdstr);
$fdarrayno=count($fdarray);
$fdok=false;
$content=$content."-".$pname."-".$tel."-".$email."-".$contact."-".$info;
for($i=0;$i<$fdarrayno;$i++){ 
if(strstr($content, $fdarray[$i])){
$fdok=true;
$fd_word=$fdarray[$i];
break;
}
}

$fd_word="[".$fd_word."] {$lang_Feedback3} ";

if($fdok==true)okinfo('javascript:history.back();',$fd_word);

$from=$met_fd_usename;
$fromname=$met_fd_fromname;
$to=$met_fd_to;
$usename=$met_fd_usename;
$usepassword=$met_fd_password;
$smtp=$met_fd_smtp;

if($met_fd_email==1){
$fromurl=$_SERVER['HTTP_REFERER'];
$title=$pname."{$lang_MessageInfo1}";
$body=$body."<b>{$lang_Name}</b>:".$pname."<br>";
$body=$body."<b>{$lang_Phone}</b>:".$tel."<br>";
$body=$body."<b>{$lang_Email}</b>:".$email."<br>";
$body=$body."<b>{$lang_OtherContact}</b>:".$contact."<br>";
$body=$body."<b>{$lang_SubmitContent}</b>:".$info."<br>";
$body=$body."<b>{$lang_IP}</b>:".$ip."<br>";
$body=$body."<b>{$lang_AddTime}</b>:".$addtime."<br>";
$body=$body."<b>{$lang_SourcePage}</b>:".$fromurl;
jmailsend($from,$fromname,$to,$title,$body,$usename,$usepassword,$smtp,$email);
}
if($met_fd_back==1 and $email!=""){
jmailsend($from,$fromname,$email,$met_fd_title,$met_fd_content,$usename,$usepassword,$smtp);
}
$customerid=$metinfo_member_name!=''?$metinfo_member_name:0;
$query = "INSERT INTO $met_message SET
					  ip                 = '$ip',
					  addtime            = '$addtime',
					  lang               = '$lang', 
					  name               = '$pname', 
					  email              = '$email', 
					  tel                = '$tel', 
					  contact            = '$contact', 
					  customerid 		 = '$customerid',
					  info               = '$info'";
         $db->query($query);
$returnurl=($met_webhtm==2)?($met_htmlistname?"message_list_1":"index_list_1").$met_htmtype:"index.php?lang=".$lang;
okinfo($returnurl,"{$lang_MessageInfo2}");

}
else{


$fdjs="<script language='javascript'>";
$fdjs=$fdjs."function Checkmessage(){ ";
$fdjs=$fdjs."if (document.myform.pname.value.length == 0) {";

$fdjs=$fdjs."alert('{$lang_MessageInfo3}');";

$fdjs=$fdjs."document.myform.pname.focus();";
$fdjs=$fdjs."return false;}";
$fdjs=$fdjs."if (document.myform.info.value.length == 0) {";
$fdjs=$fdjs."alert('{$lang_MessageInfo4}');";
$fdjs=$fdjs."document.myform.info.focus();";
$fdjs=$fdjs."return false;}";
$fdjs=$fdjs."}</script>";
$class2=$class_list[$class1][releclass]?$class1:$class2;
$class1=$class_list[$class1][releclass]?$class_list[$class1][releclass]:$class1;
$class_info=$class2?$class2_info:$class1_info;
if($class2!=""){
$class_info[name]=$class2_info[name]."--".$class1_info[name];
}
     $show[description]=$class_info[description]?$class_info[description]:$met_keywords;
     $show[keywords]=$class_info[keywords]?$class_info[keywords]:$met_keywords;
	 $met_title=$navtitle."--".$met_title;
	 
$message[listurl]=($met_webhtm==2)?($met_htmlistname?"message_list_1":"index_list_1").$met_htmtype:"index.php?lang=".$lang;
if(count($nav_list2[$message_column[id]])){
$k=count($nav_list2[$class1]);
$nav_list2[$class1][$k]=$class1_info;
$nav_list2[$class1][$k][name]=$lang_messageview;
$k++;
$nav_list2[$class1][$k]=array('url'=>$addmessage_url,'name'=>$lang_messageadd);
}else{
$k=count($nav_list2[$class1]);
  if(!$k){
   $nav_list2[$class1][0]=array('url'=>$addmessage_url,'name'=>$lang_messageadd);
   $nav_list2[$class1][1]=$class1_info;
   $nav_list2[$class1][1][name]=$lang_messageview;
   }
}	 
require_once '../public/php/methtml.inc.php';
$methtml_message.=$fdjs;
$methtml_message.="<form method='POST' name='myform' onSubmit='return Checkmessage();' action='message.php?action=add' target='_self'>\n";
$methtml_message.="<table width='90%' cellpadding='2' cellspacing='1' bgcolor='#F2F2F2' align='center' class='message_table'>\n";
$methtml_message.="<tr class='message_tr'>\n";
$methtml_message.="<td width='20%' height='25' align='right' bgcolor='#FFFFFF' class='message_td1'>".$lang_Name."&nbsp;</td>\n";
$methtml_message.="<td width='70%' bgcolor='#FFFFFF' class='message_input'><input name='pname' type='text' size='30' /></td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' style='color:#990000' class='message_info'>*</td>\n";
$methtml_message.="</tr>\n";
$methtml_message.="<tr class='message_tr'>\n";
$methtml_message.="<td align='right' bgcolor='#FFFFFF' class='message_td1'>".$lang_Phone."&nbsp;</td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' class='message_input'><input name='tel' type='text' size='30' /></td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' style='color:#990000' class='message_info'></td>\n";
$methtml_message.="</tr>\n";
$methtml_message.="<tr class='message_tr'>\n";
$methtml_message.="<td align='right' bgcolor='#FFFFFF' class='message_td1'>".$lang_Email."&nbsp;</td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' class='message_input'><input name='email' type='text' size='30' /></td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' style='color:#990000' class='message_info'></td>\n";
$methtml_message.="</tr>\n";
$methtml_message.="<tr class='message_tr'>\n";
$methtml_message.="<td align='right' bgcolor='#FFFFFF' class='message_td1'>".$lang_OtherContact."&nbsp;</td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' class='message_input'><input name='contact' type='text' size='30' />".$lang_Info5."</td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' style='color:#990000' class='message_info'></td>\n";
$methtml_message.="</tr>\n";
$methtml_message.="<tr class='message_tr'>\n";
$methtml_message.="<td align='right' bgcolor='#FFFFFF' class='message_td1'>".$lang_SubmitContent."&nbsp;</td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' class='message_text'><textarea name='info' cols='50' rows='6'></textarea></td>\n";
$methtml_message.="<td bgcolor='#FFFFFF' style='color:#990000' class='message_info'>*</td>\n";
$methtml_message.="</tr>\n";
$methtml_message.="<tr class='message_tr'><td colspan='3' bgcolor='#FFFFFF' class='message_submint' align='center'>\n";
$methtml_message.="<input type='hidden' name='fromurl' value='".$fromurl."' />\n";
$methtml_message.="<input type='hidden' name='ip' value='".$ip."' />\n";
$methtml_message.="<input type='hidden' name='lang' value='".$lang."' />\n";
$methtml_message.="<input type='submit' name='Submit' value='".$lang_SubmitInfo."' class='tj'>\n";
$methtml_message.="<input type='reset' name='Submit' value='".$lang_Reset."' class='tj'></td></tr>\n";
$methtml_message.="</table>\n";
$methtml_message.="</form>\n";

include template('message');
footer();
}
# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>