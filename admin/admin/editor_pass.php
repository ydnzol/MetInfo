<?php
require_once '../login/login_check.php';
$query = "select * from $met_column where bigclass=0 and if_in='0' order by no_order";
$result = $db->query($query);
while($list = $db->fetch_array($result)){$column_list[]=$list;}

$admin_list = $db->get_one("SELECT * FROM $met_admin_table WHERE id='$id'");
if(!$admin_list){
okinfo('index.php',$lang[noid]);
}
if($admin_list[admin_issueok]==1)$admin_issue_ok="checked";
$admin_op=explode('-',$admin_list[admin_op]);
if($admin_op[0]=="metinfo"||$admin_list[admin_op]=="metinfo"){
$admin_op_0="checked";
}else{
if($admin_op[1]=="add")$admin_op_1="checked";
if($admin_op[2]=="editor")$admin_op_2="checked";
if($admin_op[3]=="del")$admin_op_3="checked";
}
if($admin_list[admin_type]=="metinfo"){
$admin_pop="checked";
}else{
$admin_pop=explode('-',$admin_list[admin_type]);
$admin_poptext="admin_pop";
foreach($admin_pop as $key=>$val){
$admin_poptext1=$admin_poptext.$val=$val;
$$admin_poptext1="checked";
}
$admin_pop="";
}
$css_url="../templates/".$met_skin."/css";
$img_url="../templates/".$met_skin."/images";
include template('admin_pass');
footer();
?>