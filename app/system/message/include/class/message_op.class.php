<?php
# MetInfo Enterprise Content Management System
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved.

defined('IN_MET') or exit('No permission');

load::mod_class('news/news_op');

/**
 * news标签类
 */

class message_op extends news_op {

	/**
		* 初始化
		*/
	public function __construct() {
		global $_M;
    $this->database = load::mod_class('message/message_database', 'new');
	}

  //删除
  public function del_by_class($classnow) {
    global $_M;
    parent::del_by_class($classnow);
    //删除字段
  }
  
	/*复制*/
	public function list_copy($classnow = '', $toclass1 ='', $toclass2 = '', $toclass3 = '', $tolang = '', $paras = array()){
    return true;
	}

	/*移动产品*/
	public function list_move($id,$class1,$class2,$class3){
    return true;
  }
  
}

# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>
