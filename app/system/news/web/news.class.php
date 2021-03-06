<?php
# MetInfo Enterprise Content Management System
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved.

defined('IN_MET') or exit('No permission');

load::sys_class('web');

class news extends web
{
    public function __construct()
    {
        global $_M;
        parent::__construct();
    }

    /**
     * 详情页data数据
     * @param $module
     */
    public function showpage($module)
    {
        global $_M;
        $data = load::sys_class('label', 'new')->get($module)->get_one_list_contents($_M['form']['id']);
        
        $classnow = $data['class3'] ? $data['class3'] : ($data['class2'] ? $data['class2'] : $data['class1']);
        $this->input_class($classnow);
        if ($module == 'product') {
            $data['contents'][] = array('title' => $this->input['tab_name_0'], 'content' => $data['content']);
            if ($this->input['tab_num'] > 1) {
                for ($i = 1; $i < $this->input['tab_num']; $i++) {
                    $data['contents'][] = array('title' => $this->input['tab_name_' . $i], 'content' => $data['content' . $i]);
                }
            }
        }

        //添加评论插件内容
        if ($_M['config']['comment_global_setup']) {
            $comment_data = load::app_class('met_comment/include/class/CommentHtml', 'new')->doget_content($classnow);
            if ($module == 'product') {
                foreach ($data['contents'] as $key => $value) {
                    $data['contents'][$key]['content'] .= $comment_data;
                }
            } elseif ($module == 'news') {
                $data['content'] .= $comment_data;
            }
        }

        $data['updatetime'] = date($_M['config']['met_contenttime'], strtotime($data['original_updatetime']));
        $data['addtime'] = date($_M['config']['met_contenttime'], strtotime($data['original_addtime']));
        $this->check($data['access']);
        $this->add_array_input($data);
        $this->seo($data['title'], $data['keywords'], $data['description']);
        $this->seo_title($data['ctitle']);
    }

    /**
     * 列表data数据
     * @param $module
     * @return string
     */
    public function listpage($module)
    {
        global $_M;
        if ($_M['form']['pseudo_jump'] && $_M['form']['list'] != 1) {
            if (!is_numeric($_M['form']['metid'])) {
                $custom = load::sys_class('label', 'new')->get($module)->database->get_list_by_filename($_M['form']['metid']);
                $_M['form']['metid'] = $custom['0']['id'];
            }
            $_M['form']['id'] = $_M['form']['metid'];
            return 'show';
        }

        $classnow = $_M['form']['class3'] ? $_M['form']['class3'] : ($_M['form']['class2'] ? $_M['form']['class2'] : $_M['form']['class1']);
        $classnow = $classnow ? $classnow : $_M['form']['metid'];
        if (!is_numeric($classnow)) {
            $custom = load::sys_class('label', 'new')->get('column')->get_column_folder($_M['form']['metid']);
            $classnow = $custom['0']['id'];
        }
        $classnow = $this->input_class($classnow);
        $data = load::sys_class('label', 'new')->get('column')->get_column_id($classnow);
        $this->check($data['access']);
        unset($data['id']);
        unset($data['list_length']);
        $this->add_array_input($data);
        $this->seo($data['name'], $data['keywords'], $data['description']);
        $this->seo_title($data['ctitle']);
        $this->add_input('page', $_M['form']['page']);
        $this->add_input('list', 1);
        return 'list';
    }

    public function donews()
    {
        global $_M;
        
        if ($this->listpage('news') == 'list') {
            //列表页缩略图尺寸
            $_M['config']['met_newsimg_x'] = $this->input['thumb_list_x'];
            $_M['config']['met_newsimg_y'] = $this->input['thumb_list_y'];
            $_M['config']['met_news_list'] = $this->input['list_length'];

            load::sys_class('handle', 'new')->redirectUrl($this->input); //伪静态时动态链接跳转
            $this->view('news', $this->input);
        } else {
            $this->doshownews();
        }
    }

    public function doshownews()
    {
        global $_M;
        $this->showpage('news');

        //详情页缩略图尺寸
        $_M['config']['met_newsdetail_x'] = $this->input['thumb_detail_x'];
        $_M['config']['met_newsdetail_y'] = $this->input['thumb_detail_y'];


        load::sys_class('handle', 'new')->redirectUrl($this->input); //伪静态时动态链接跳转
        $this->view('shownews', $this->input);
    }

}

# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>
