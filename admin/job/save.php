<?php
# MetInfo Enterprise Content Management System 
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved.
require_once '../login/login_check.php';
$filename=preg_replace("/\s/","_",trim($filename)); 
$filenameold=preg_replace("/\s/","_",trim($filenameold)); 
if($action=="add"){
$query = "INSERT INTO $met_job SET
                      position           = '$position',
					  count              = '$count',
					  place              = '$place',
					  deal               = '$deal',
					  content            = '$content',
					  useful_life        = '$useful_life',
					  addtime            = '$addtime',
					  access			 = '$access',
					  lang			     = '$lang',
					  filename           = '$filename',
					  top_ok             = '$top_ok'";
         $db->query($query);
//HTML		 
$later_job=$db->get_one("select * from $met_job where lang='$lang' order by id desc");
$id=$later_job[id];
indexhtm();
contenthtm($class1,$id,'showjob',$filename,0,'job');
classhtm($class1,0,0);
okinfo('index.php?lang='.$lang.'&class1='.$class1,$lang_jsok);
}

if($action=="editor"){
$query = "update $met_job SET 
                      position           = '$position',
					  place              = '$place',
					  deal               = '$deal',
					  content            = '$content',
					  count              = '$count',
					  useful_life        = '$useful_life',
					  addtime            = '$addtime',
					  access			 = '$access',";
if($metadmin[pagename])$query .= "
					  filename       	 = '$filename',";
					  $query .= "
					  top_ok             = '$top_ok'
					  where id='$id'";
$db->query($query);

//HTML
indexhtm();
contenthtm($class1,$id,'showjob',$filename,0,'job');
classhtm($class1,0,0);
if($filenameold<>$filename and $metadmin[pagename])deletepage($met_class[$class1][foldername],$id,'showjob',$updatetimeold,$filenameold);
okinfo('index.php?lang='.$lang.'&class1='.$class1,$lang_jsok);
}
# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>
