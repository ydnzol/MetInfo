<?php
# MetInfo Enterprise Content Management System 
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved. 
require_once '../login/login_check.php';
   if($action=="add"){
   if($_FILES['skin_files']['name']!=''){
   require_once '../include/upfile.class.php';
   if(!is_writable('../../templates/'))@chmod('../../templates/',0777);
   $f = new upfile('zip','../../templates/','','');
   $filenamearray=explode('.zip',$_FILES['skin_files']['name']);
   $skin_if=$db->get_one("SELECT * FROM $met_skin_table WHERE skin_file='$filenamearray[0]'");
   if($skin_if)okinfo('javascript:history.back();',$lang_loginSkin);
   if(file_exists('../../templates/'.$filenamearray[0].'.zip'))$filenamearray[0]='metinfo'.$filenamearray[0];
        $skin_file   = $f->upload('skin_files',$filenamearray[0]); 
   include "pclzip.lib.php";
   $archive = new PclZip('../../templates/'.$filenamearray[0].'.zip');
   if($archive->extract(PCLZIP_OPT_PATH, '../../templates/') == 0)die("Error : ".$archive->errorInfo(true));
   $skin_file=$filenamearray[0];
   @unlink('../../templates/'.$filenamearray[0].'.zip');
   }
   $query="insert into $met_skin_table set
           skin_name='$skin_name',
		   skin_file='$skin_file',
		   skin_info='$skin_info'";
   $db->query($query);
   okinfo('skin_manager.php?lang='.$lang,$lang_jsok);
   }
elseif($action=="modify"){
$skin_m=$db->get_one("SELECT * FROM $met_skin_table WHERE id='$id'");
$css_url="../templates/".$met_skin."/css";
$img_url="../templates/".$met_skin."/images";
include template('skin');
footer();
}
elseif($action=="editor"){
$skin_m=$db->get_one("SELECT * FROM $met_skin_table WHERE id='$id'");
if(!$skin_m){okinfo('skin_manager.php?lang='.$lang,$lang_dataerror);}
$query="update $met_skin_table set
           skin_name='$skin_name',
		   skin_file='$skin_file',
		   skin_info='$skin_info'
		   where id='$id'";
   $db->query($query);
   okinfo('skin_manager.php?lang='.$lang,$lang_jsok);
}
elseif($action=="delete"){

function deldir($dir) {
  $dh=opendir($dir);
  while ($file=readdir($dh)) {
    if($file!="." && $file!="..") {
      $fullpath=$dir."/".$file;
      if(!is_dir($fullpath)) {
          unlink($fullpath);  
      } else {
          deldir($fullpath);
      }
    }
  }
  closedir($dh);
    if(rmdir($dir)){
    return true;
    } else {
    return false;
    }
  }
  
  if($action_type=="del"){
   $allidlist=explode(',',$allid);
    foreach($allidlist as $key=>$val){
    $query = "delete from $met_skin_table where id='$val'";
    $db->query($query);
    }
    okinfo('skin_manager.php?lang='.$lang,$lang_jsok);
 }
  else{
      $skin_m=$db->get_one("SELECT * FROM $met_skin_table WHERE id='$id'");
      if(!$skin_m){okinfo('skin_manager.php?lang='.$lang,$lang_dataerror);}
      $query="delete from $met_skin_table where id='$id'";
      $db->query($query);
	  $filedir="../../templates/".$skin_m[skin_file];
      deldir($filedir);
      okinfo('skin_manager.php?lang='.$lang,$lang_jsok);
	  }
}
else{
    $total_count = $db->counter($met_skin_table, "", "*");
    require_once 'include/pager.class.php';
    $page = (int)$page;
	if($page_input){$page=$page_input;}
    $list_num = 16;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $query = "SELECT * FROM $met_skin_table order BY id LIMIT $from_record, $list_num";
    $result = $db->query($query);
	 while($list = $db->fetch_array($result)) {
     $skin_list[]=$list;
    }
$page_list = $rowset->link("skin_manager.php?lang=".$lang."&page=");
$css_url="../templates/".$met_skin."/css";
$img_url="../templates/".$met_skin."/images";
include template('skin');
footer();
}
# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>