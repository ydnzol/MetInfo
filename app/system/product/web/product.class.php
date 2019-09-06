<?php
# MetInfo Enterprise Content Management System
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved.

defined('IN_MET') or exit('No permission');

load::mod_class('news/web/news');

class product extends news {
	public function __construct() {
		global $_M;
		parent::__construct();
	}

  public function doproduct() {
      global $_M;
      
      if($this->listpage('product') == 'list'){
          //列表页缩略图尺寸
          $_M['config']['met_productimg_x'] = $this->input['thumb_list_x'];
          $_M['config']['met_productimg_y'] = $this->input['thumb_list_y'];
          $_M['config']['met_product_list'] = $this->input['list_length'] ;

          load::sys_class('handle', 'new')->redirectUrl($this->input); //伪静态时动态链接跳转
          $this->view('product',$this->input);
      }else{
          $this->doshowproduct();
      }
  }

	public function doshowproduct(){
        global $_M;
        $this->showpage('product');
        //详情页缩略图尺寸
        $_M['config']['met_productdetail_x'] = $this->input['thumb_detail_x'];
        $_M['config']['met_productdetail_y'] = $this->input['thumb_detail_y'];

        //内容选项卡
        /*$_M['config']['met_productTabok']     = $this->input['tab_num'];
        $_M['config']['met_productTabname']   = $this->input['tab_name_0'] ;
        $_M['config']['met_productTabname_1'] = $this->input['tab_name_1'] ;
        $_M['config']['met_productTabname_2'] = $this->input['tab_name_2'] ;
        $_M['config']['met_productTabname_3'] = $this->input['tab_name_3'] ;
        $_M['config']['met_productTabname_4'] = $this->input['tab_name_4'] ;*/
        $shop_plugin_file = PATH_ALL_APP.'shop/plugin/plugin_shop.class.php';
        if($_M['config']['shopv2_open']  && file_exists($shop_plugin_file)){
            define('MET_SHOP_PARA', 1);
            load::plugin('doproduct_show',0,$this->input);
        }else{
            load::sys_class('handle', 'new')->redirectUrl($this->input); //伪静态时动态链接跳转
            $this->view('showproduct',$this->input);
        }
    }
}

# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>
