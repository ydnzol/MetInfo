<?php
# MetInfo Enterprise Content Management System
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved.

defined('IN_MET') or exit('No permission');

load::sys_class('common.class.php');
load::sys_func('web');

/**
 * 前台基类
 */
class web extends common
{
    /**
     * 初始化
     */
    /**
     * 获取url传递的参数
     * @var [type]
     */
    protected $input;

    public function __construct()
    {
        parent::__construct();
        global $_M;
        // 可视化窗口语言栏跳转后，整个可视化页面跳转到新语言
        $admin_folder=array_slice(explode('/',$_M['url']['site_admin']), -2, 1);
        $url_site=str_replace($admin_folder[0].'/', '', $_M['url']['site_admin']);
        if (strpos($_SERVER['HTTP_REFERER'], 'n=ui_set') !== false && strpos($_SERVER['HTTP_REFERER'], $url_site) !== false) {
            preg_match('/lang=(\w+)/', $_COOKIE['page_iframe_url'], $prev_lang);
            if ($prev_lang && $prev_lang[1] != $_M['lang']) {
                $new_url = "{$_M['url']['site_admin']}?lang={$_M['lang']}&n=ui_set";
                echo "<script>
					parent.document.getElementsByClassName('page-iframe')[0].setAttribute('data-dynamic','{$url_site}index.php?lang={$_M['lang']}');
					parent.window.location.href='{$new_url}';
				</script>";
                die;
            }
        }
        // 非可视化状态下pageset=1页面跳转
        if (strpos($_SERVER['HTTP_REFERER'], $url_site) === false && strpos($_SERVER['QUERY_STRING'], 'pageset=1') !== false) {
            echo "<script>
					if(self == top) window.location.href=location.href.replace('&pageset=1','').replace('?pageset=1','');
				</script>";
        }

        $this->tem_dir();//确定模板根目录
        $this->load_domain();//加载绑定域名的语言
        $this->load_language();//语言加载
        // $this->load_publuc_data();//加载公共数据
        $this->sys_input();

        load::sys_class('user', 'new')->get_login_user_info();//会员登录
        load::plugin('doweb');//加载插件
        load::mod_class('user/user_url', 'new')->insert_m();//会员模块url

        // 页面基本信息，应用页面专用
        if ($_M['config']['met_title_type'] == 0) {
            $this->add_input('page_title', '');
        } else if ($_M['config']['met_title_type'] == 1) {
            $this->add_input('page_title', '-' . $_M['config']['met_keywords']);
        } else if ($_M['config']['met_title_type'] == 2) {
            $this->add_input('page_title', '-' . $_M['config']['met_webname']);
        } else if ($_M['config']['met_title_type'] == 3) {
            $this->add_input('page_title', '-' . $_M['config']['met_keywords'] . '-' . $_M['config']['met_webname']);
        }
    }

    /**
     * 重写common类的load_form方法，前台对提交的GET，POST，COOKIE进行安全的过滤处理
     */
    protected function load_form()
    {
        global $_M;
        parent::load_form();
        foreach ($_M['form'] as $key => $val) {
            $_M['form'][$key] = sqlinsert($val);
        }
        if ($_M['form']['id'] != '' && !is_numeric($_M['form']['id'])) {
            $_M['form']['id'] = '';
        }
        if ($_M['form']['class1'] != '' && !is_numeric($_M['form']['class1'])) {
            $_M['form']['class1'] = '';
        }
        if ($_M['form']['class2'] != '' && !is_numeric($_M['form']['class2'])) {
            $_M['form']['class2'] = '';
        }
        if ($_M['form']['class3'] != '' && !is_numeric($_M['form']['class3'])) {
            $_M['form']['class3'] = '';
        }

        $_M['form']['content'] = load::sys_class('label', 'new')->get('tags')->getTagName($_M['form']['content']);
        $_M['form']['searchword'] = load::sys_class('label', 'new')->get('tags')->getTagName($_M['form']['searchword']);

    }

    protected function input_class($id=0)
    {
        global $_M;
        $column_lable = load::sys_class('label', 'new')->get('column');

        if (!$id) {
            $REQUEST_URIs = explode('/', REQUEST_URI);
            if(in_array('tag',$REQUEST_URIs)){
                $folder = $REQUEST_URIs[count($REQUEST_URIs) - 3];
                 if($id === 0){
                    // 兼容老tag伪静态
                    $folder = 'search';
                }
            }else{
                $folder = $REQUEST_URIs[count($REQUEST_URIs) - 2];
            }

            $c = $column_lable->get_column_folder($folder);
            $id = $c['id'];
        }

        if (!$column_lable->get_column_id($id)) {
            abort();
        }

        //获取三级栏目信息
        $c123_releclass = $column_lable->get_class123_reclass($id);
        $this->add_input('releclass1', $c123_releclass['class1']['id']);
        $this->add_input('releclass2', $c123_releclass['class2']['id']);
        $this->add_input('releclass3', $c123_releclass['class3']['id']);
        $c123 = $column_lable->get_class123_no_reclass($id);
        $this->add_input('classnow', 0);
        $this->add_input('class1', $c123['class1']['id']);
        $this->add_input('class2', $c123['class2']['id']);
        $this->add_input('class3', $c123['class3']['id']);
        if ($c123['class3']['id']) {
            $classnow = $c123['class3']['id'];
        } else {
            if ($c123['class2']['id']) {
                $classnow = $c123['class2']['id'];
            } else {
                $classnow = $c123['class1']['id'];
            }
        }
        $this->add_input('classnow', $classnow);
        $c = $column_lable->get_column_id($classnow);

        //额外栏目信息
        self::classExt($c);

        //栏目
        $this->add_input('module', $c['module']);

        //此处修改
        return $c['id'];

    }

    /**
     * 额外栏目信息
     * @param array $c
     */
    protected function classExt($c = array())
    {
        global $_M;
        $column_lable = load::sys_class('label', 'new')->get('column');
        $c123 = $column_lable->get_class123_no_reclass($c['id']);

        $thumb_list_default = array(400, 400);
        $thumb_detail_default = array(600, 600);
        //新闻
        if ($c['module'] == 2) {
            $thumb_list_default = array($_M['config']['met_newsimg_x'], $_M['config']['met_newsimg_y']);
        }

        //产品
        if ($c['module'] == 3) {
            $thumb_list_default = array($_M['config']['met_productimg_x'], $_M['config']['met_productimg_y']);
            $thumb_detail_default = array($_M['config']['met_productdetail_x'], $_M['config']['met_productdetail_y']);
        }

        //图片
        if ($c['module'] == 5) {
            $thumb_list_default = array($_M['config']['met_imgs_x'], $_M['config']['met_imgs_y']);
            $thumb_detail_default = array($_M['config']['met_imgdetail_x'], $_M['config']['met_imgdetail_y']);
        }

        //栏目配置分页条数及说略图尺寸信息
        $c_lev = $c['classtype'];

        //三级栏目
        if ($c_lev == 3) {
            //list_length
            $list_length = $c123['class3']['list_length'] ? $c123['class3']['list_length'] : ($c123['class2']['list_length'] ? $c123['class2']['list_length'] : ($c123['class1']['list_length'] ? $c123['class1']['list_length'] : 8));

            //thumb_list
            if ($c123['class3']['thumb_list'] && $c123['class3']['thumb_list'] != '|') {
                $thumb_list = explode("|", $c123['class3']['thumb_list']);
            }else{
                if ($c123['class2']['thumb_list'] && $c123['class2']['thumb_list'] != '|') {
                    $thumb_list = explode("|", $c123['class2']['thumb_list']);
                }else{
                    if ($c123['class1']['thumb_list'] && $c123['class1']['thumb_list'] != '|') {
                        $thumb_list = explode("|", $c123['class1']['thumb_list']);
                    }else{
                        #$thumb_list = array(400, 400);
                        $thumb_list = $thumb_list_default;
                    }
                }
            }

            //thumb_detail
            if ($c123['class3']['thumb_detail'] && $c123['class3']['thumb_detail'] != '|') {
                $thumb_detail = explode("|", $c123['class3']['thumb_detail']);
            }else{
                if ($c123['class2']['thumb_detail'] && $c123['class2']['thumb_detail'] != '|') {
                    $thumb_detail = explode("|", $c123['class2']['thumb_detail']);
                }else{
                    if ($c123['class1']['thumb_detail'] && $c123['class1']['thumb_detail'] != '|') {
                        $thumb_detail = explode("|", $c123['class1']['thumb_detail']);
                    }else{
                        #$thumb_detail = array(600, 600);
                        $thumb_detail = $thumb_detail_default;
                    }
                }
            }

            //tab_num
            $tab_num = $c123['class3']['tab_num'] ? $c123['class3']['tab_num'] : ($c123['class2']['tab_num'] ? $c123['class2']['tab_num'] : ($c123['class1']['tab_num'] ? $c123['class1']['tab_num'] : 3));

            //tab_name
            if ($c123['class3']['tab_name'] && $c123['class3']['tab_name'] != '|') {
                $tab_name = explode("|", $c123['class3']['tab_name']);
            }else{
                if ($c123['class2']['tab_name'] && $c123['class2']['tab_name'] != '|') {
                    $tab_name = explode("|", $c123['class2']['tab_name']);
                }else{
                    if ($c123['class1']['tab_name'] && $c123['class1']['tab_name'] != '|') {
                        $tab_name = explode("|", $c123['class1']['tab_name']);
                    }else{
                        $tab_name = array(
                            $_M['config']['met_productTabname'],
                            $_M['config']['met_productTabname_1'],
                            $_M['config']['met_productTabname_2'],
                            $_M['config']['met_productTabname_3'],
                            $_M['config']['met_productTabname_4']
                            );
                    }
                }
            }
        }

        //二级栏目将
        if($c_lev == 2){
            //list_length
            $list_length = $c123['class2']['list_length'] ? $c123['class2']['list_length'] : ($c123['class1']['list_length'] ? $c123['class1']['list_length'] : 8);

            //thumb_list
            if ($c123['class2']['thumb_list'] && $c123['class2']['thumb_list'] != '|') {
                $thumb_list = explode("|", $c123['class2']['thumb_list']);
            }else{
                if ($c123['class1']['thumb_list'] && $c123['class1']['thumb_list'] != '|') {
                    $thumb_list = explode("|", $c123['class1']['thumb_list']);
                }else{
                    #$thumb_list = array(400, 400);
                    $thumb_list = $thumb_list_default;
                }
            }

            //thumb_detail
            if ($c123['class2']['thumb_detail'] && $c123['class2']['thumb_detail'] != '|') {
                $thumb_detail = explode("|", $c123['class2']['thumb_detail']);
            }else{
                if ($c123['class1']['thumb_detail'] && $c123['class1']['thumb_detail'] != '|') {
                    $thumb_detail = explode("|", $c123['class1']['thumb_detail']);
                }else{
                    #$thumb_detail = array(600, 600);
                    $thumb_detail = $thumb_detail_default;
                }
            }

            //tab_num
            $tab_num = $c123['class2']['tab_num'] ? $c123['class2']['tab_num'] : ($c123['class1']['tab_num'] ? $c123['class1']['tab_num'] : 3);

            //tab_name
            if ($c123['class2']['tab_name'] && $c123['class2']['tab_name'] != '|') {
                $tab_name = explode("|", $c123['class2']['tab_name']);
            }else{
                if ($c123['class1']['tab_name'] && $c123['class1']['tab_name'] != '|') {
                    $tab_name = explode("|", $c123['class1']['tab_name']);
                }else{
                    $tab_name = array(
                        $_M['config']['met_productTabname'],
                        $_M['config']['met_productTabname_1'],
                        $_M['config']['met_productTabname_2'],
                        $_M['config']['met_productTabname_3'],
                        $_M['config']['met_productTabname_4']
                    );
                }
            }
        }

        //一级栏目
        if ($c_lev == 1) {
            //
            $list_length = $c123['class1']['list_length'] ? $c123['class1']['list_length'] : 8;

            //thumb_list
            if ($c123['class1']['thumb_list'] && $c123['class1']['thumb_list'] != '|') {
                $thumb_list = explode("|", $c123['class1']['thumb_list']);
            }else{
                #$thumb_list = array(400, 400);
                $thumb_list = $thumb_list_default;
            }

            //thumb_detail
            if ($c123['class1']['thumb_detail'] && $c123['class1']['thumb_detail'] != '|') {
                $thumb_detail = explode("|", $c123['class1']['thumb_detail']);
            }else{
                #$thumb_detail = array(600, 600);
                $thumb_detail = $thumb_detail_default;
            }

            //tab_num
            $tab_num = $c123['class1']['tab_num'] ? $c123['class1']['tab_num'] : 3;

            //tab_name
            if ($c123['class1']['tab_name'] && $c123['class1']['tab_name'] != '|') {
                $tab_name = explode("|", $c123['class1']['tab_name']);
            }else{
                $tab_name = array(
                    $_M['config']['met_productTabname'],
                    $_M['config']['met_productTabname_1'],
                    $_M['config']['met_productTabname_2'],
                    $_M['config']['met_productTabname_3'],
                    $_M['config']['met_productTabname_4']
                );
            }
        }


        //分页条数
        #$list_length = $c['list_length']  ? $c['list_length'] : 8;
        $list_length = $list_length ? $list_length : 8;
        $this->add_input('list_length', $list_length);

        //列表页说略图尺寸
        #$thumb_list = explode("|", $c['thumb_list']);
        $thumb_list_x = $thumb_list[0];
        $thumb_list_y = $thumb_list[1];
        $this->add_input('thumb_list_x', $thumb_list_x);
        $this->add_input('thumb_list_y', $thumb_list_y);

        //详情页说略图尺寸
        #$thumb_detail = explode("|", $c['thumb_detail']);
        $thumb_detail_x = $thumb_detail[0];
        $thumb_detail_y = $thumb_detail[1];
        $this->add_input('thumb_detail_x', $thumb_detail_x);
        $this->add_input('thumb_detail_y', $thumb_detail_y);

        //内容选项卡&&显示个数
        #$tab_num = $c['tab_num'] ? $c['tab_num'] : 3;
        $this->add_input('tab_num', $tab_num);

        #$tab_name = explode('|', $c['tab_name']);
        $tab_name_0 = $tab_name[0];
        $tab_name_1 = $tab_name[1];
        $tab_name_2 = $tab_name[2];
        $tab_name_3 = $tab_name[3];
        $tab_name_4 = $tab_name[4];
        $this->add_input('tab_name_0', $tab_name_0);
        $this->add_input('tab_name_1', $tab_name_1);
        $this->add_input('tab_name_2', $tab_name_2);
        $this->add_input('tab_name_3', $tab_name_3);
        $this->add_input('tab_name_4', $tab_name_4);
    }

    /**
     * 对页面的class1进行处理
     */
    protected function seo($title = '', $keywords = '', $description = '')
    {
        global $_M;
        // 标签聚合页面TDK
        if(isset($_M['form']['search']) && ($_M['form']['search'] == 'tag' || @$_GET['search'] == 'tag')){
            $tag = load::sys_class('label','new')->get('tags')->getTagInfo($_M['form']['content'],$this->input);

            if(!$tag){
                $tag = load::sys_class('label', 'new')->get('tags')->getTagInfo($_M['form']['searchword'], $this->input);
            }
            if($tag){
                $title = $tag['title'] ? $tag['title'] : $tag['tag_name'];
                if($tag['title']){
                    $_M['config']['met_title_type'] = 0;
                }
                if($tag['keywords']){
                    $keywords = $_M['config']['met_keywords'] = $tag['keywords'];
                }

                if($tag['description']){
                    $description = $_M['config']['met_keywords'] =  $tag['description'];
                }
            }else{
                $title = $_M['form']['searchword'] ? $_M['form']['searchword'] : $_M['form']['content'];
            }
        }
        switch ($_M['config']['met_title_type']) {
            case '0':
                $this->add_input('page_title', $title);
                break;
            case '1':
                $this->add_input('page_title', $title . '-' . $_M['config']['met_keywords']);
                break;
            case '2':
                $this->add_input('page_title', $title . '-' . $_M['config']['met_webname']);
                break;
            case '3':
                $this->add_input('page_title', $title . '-' . $_M['config']['met_keywords']. '-' . $_M['config']['met_webname']) ;
                break;
        }
        if ($keywords) {
            $this->add_input('page_keywords', $keywords);
        } else {
            $this->add_input('page_keywords', $_M['config']['met_keywords']);
        }

        if ($description) {
            $this->add_input('page_description', $description);
        } else {
            $this->add_input('page_description', $_M['config']['met_description']);
        }

    }

    protected function seo_title($title = '')
    {
        if ($title) {
            $this->add_input('page_title', $title);
        }
    }

    /**
     * 对页面的class1进行处理
     */
    protected function class_handle()
    {
        global $_M;

        if ($_M['form']['class3']) {
            $class2_array = load::sys_class('label', 'new')->get('column')->get_parent_column($_M['form']['class3']);
            $class1_array = load::sys_class('label', 'new')->get('column')->get_parent_column($class2_array['id']);
            $_M['form']['class2'] = $class2_array['id'];
            $_M['form']['class1'] = $class1_array['id'];
            $_M['form']['classnow'] = $_M['form']['class3'];
            return true;
        }

        if ($_M['form']['class2']) {
            $class1_array = load::sys_class('label', 'new')->get('column')->get_parent_column($_M['form']['class2']);
            $_M['form']['class1'] = $class1_array['id'];
            $_M['form']['class3'] = 0;
            $_M['form']['classnow'] = $_M['form']['class2'];
            return true;
        }

        if ($_M['form']['class1']) {
            $_M['form']['class2'] = 0;
            $_M['form']['class3'] = 0;
            $_M['form']['classnow'] = $_M['form']['class1'];
            return true;
        }

        $REQUEST_URIs = explode('/', REQUEST_URI);
        $c = load::sys_class('label', 'new')->get('column')->get_column_folder($REQUEST_URIs[count($REQUEST_URIs) - 2]);
        if ($c) {
            $_M['form']['class2'] = 0;
            $_M['form']['class3'] = 0;
            $_M['form']['class1'] = $c['id'];
            $_M['form']['classnow'] = $c['id'];
        } else {
            $_M['form']['class2'] = 0;
            $_M['form']['class3'] = 0;
            $_M['form']['class1'] = 10001;
            $_M['form']['classnow'] = 10001;
        }
        return true;
    }

    /**
     * 重写common类的load_url_unique方法，获取前台特有URL
     */
    protected function load_url_unique()
    {
        global $_M;
        $_M['url']['ui'] = $_M['url']['site'] . 'app/system/include/public/ui/web/';
        $_M['url']['own_name'] = "{$_M['url']['site']}app/index.php?lang={$_M['lang']}&n=" . M_NAME . '&';
        $_M['url']['own_form'] = $_M['url']['own_name'] . 'c=' . M_CLASS . '&';
    }

    protected function load_domain()
    {
        global $_M;

        $domain = trim(str_replace($_SERVER['REQUEST_SCHEME'] . '://', '', $_M['url']['site']), '/');

        if ($domain) {
            foreach ($_M['langlist']['web'] as $key => $val) {
                if($val['link'] == $domain){
                    $_M['lang'] = $val['mark'];
                }
            }
        }
    }

    /**
     * 获取当前语言参数
     */
    protected function load_language()
    {
        global $_M;
        $this->load_word($_M['lang'], 0);
        $this->load_template_lang();
    }

    /**
     * 获取前台公用数据
     */
    // protected function load_publuc_data()
    // {
    //     global $_M;
    //     //$this->class_handle();//确认栏目id
    //     $this->load_flashset_data();
    // }

    /**
     * 获取前台模板的语言参数配置，存放在$_M['word']中，系统语言参数数组。
     */
    protected function load_template_lang()
    {
        global $_M;
        $tmpincfile = PATH_WEB . "templates/{$_M['config']['met_skin_user']}/metinfo.inc.php";
        if (is_file($tmpincfile)) require $tmpincfile;
        $_M['config']['metinfover'] = $metinfover;
        $_M['config']['temp_frame_version'] = $temp_frame_version;
        if($template_type == 'tag'){
            $sys_compile = load::sys_class('view/sys_compile', 'new');
            $templates = $sys_compile->list_templates_config();
            $_M['word'] = array_merge($_M['word'], $templates);
        }
    }

    /**
     * 前台权限检测
     * @param int 会员组编号
     * 如果会员拥有权限则，程序代码向后正常执行，如果没有则提示没有权限。
     */
    protected function check($groupid = 0)
    {
        global $_M;
        $power = load::sys_class('user', 'new')->check_power($groupid);
        if ($power < 0) {
            $gourl = $_M['gourl'] ? base64_encode($_M['gourl']) : base64_encode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            $gourl = $gourl == -1 ? "" : $gourl;
            if ($_M['lang'] != $_M['config']['met_index_type']) {
                $lang = "&lang={$_M['lang']}";
            }
            if ($power == -2) {
                okinfo($_M['url']['site'] . 'member/index.php?gourl=' . $gourl . $lang, $_M['word']['systips1']);
            }
            if ($power == -1) {
                okinfo($_M['url']['site'].'index.php?lang='.$_M['lang'], $_M['word']['systips2']);
            }
        }
        // if($groupid != 0 && !get_met_cookie('metinfo_admin_name')){
        // 	$user = $this->get_login_user_info();
        // 	$gourl = $_M['gourl'] ? urlencode($_M['gourl']) : urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        // 	$gourl = $gourl == -1 ? "":$gourl;
        // 	if($_M['lang'] != $_M['config']['met_index_type']){
        // 		$lang = "&lang={$_M['lang']}";
        // 	}
        // 	if($groupid == 0 && !$user){
        // 		okinfo($_M['url']['site'].'member/login.php?gourl='.$gourl.$lang, '您没有权限访问这个内容！请登录后访问！');
        // 	}

        // 	$group = load::sys_class('group', 'new')->get_group($groupid);
        // 	if($user['access'] < $group['access']){
        // 		okinfo($_M['url']['site'].'index.php?gourl='.$gourl.$lang, '您没有权限访问这个内容！');
        // 	}
        // }
    }

    /**
     * 前台权限检测
     * @param string m_auth 会员登录授权码
     * @param string m_key  会员登录密钥
     * 如果会员拥有权限则，程序代码向后正常执行，如果没有则提示没有权限。
     * get_met_cookie函数兼容也调用login_by_auth,如果修改请一并修改。
     */
    protected function get_login_user_info($met_auth = '', $met_key = '')
    {
        global $_M;
        return load::sys_class('user', 'new')->get_login_user_info($met_auth, $met_key);
    }

    /**
     * 模板文件 (兼容老应用  新方法$this->view('temp_name',$this->>input))
     * @param string $file 模板主文件
     * @param int $mod 标签数组
     */
    protected function template($file)
    {
        global $_M;
        if (!$_M['config']['metinfover'] || $_M['config']['metinfover'] == 'v1') {
            if ($_M['custom_template']['file']) {
                return parent::template($file);
            } else {
                $_M['custom_template']['file'] = $file;
                return parent::template('ui/metv5');
            }
        } else {
            return $this->view(parent::template($file), $this->input);
        }
    }


    /**
     * 应用兼容模式加载前台模板，会自动加载当前选定模板的顶部，尾部，左侧导航(可选)，只有内容主题可以自定义。
     * @param string $content 页面主体内容部分调用的文件名，为自定的应用模板文件
     * @param int $left 收加载模板的左侧栏，2：加载会员左侧导航 1:加载一般页面左侧导航，0:不加载
     */
    protected function custom_template($content, $left)
    {
        global $_M;
        $_M['custom_template']['content'] = $content;
        $_M['custom_template']['left'] = $left;
        return $this->template('ui/app');
    }

    /**
     * 确定模板根目录
     */
    protected function tem_dir()
    {
        global $_M;
        if ($_M['config']['met_wap']) {//兼容手机版
            $is_mobile = intval(is_mobile());
            if ($_M['form']['met_mobileok']) {
                $_M['config']['met_skin_user'] = $_M['config']['wap_skin_user'];
            } else {
                if ($is_mobile == 1) {
                    $_M['config']['met_skin_user'] = $_M['config']['wap_skin_user'];
                } else {
                    $_M['config']['met_skin_user'] = $_M['config']['met_skin_user'];
                }
            }
            if ($is_mobile == 1) {
                met_setcookie("met_mobileok", '1');
            } else {
                met_setcookie("met_mobileok", '0');
            }
        } else {
            $_M['config']['met_skin_user'] = $_M['config']['met_skin_user'];
        }
        define('PATH_TEM', PATH_WEB . "templates/" . $_M['config']['met_skin_user'] . '/');//模板根目录
    }

    /**
     * input变量处理
     */
    protected function sys_input()
    {
        global $_M;
        if ($_M['form']['pseudo_jump'] && $_M['form']['list']) {
            if (!is_numeric($_M['form']['metid'])) {
                $column = load::sys_class('label', 'new')->get('column')->get_column_by_filename($_M['form']['metid']);
                if ($column) {
                    $_M['form']['class' . $column['classtype']] = $column['id'];
                }
            }
        }
        $this->input['class1'] = $_M['form']['class1'];
        $this->input['class2'] = $_M['form']['class2'];
        $this->input['class3'] = $_M['form']['class3'];
        $this->input['classnow'] = $_M['form']['classnow'];
        $this->input['page'] = $_M['form']['page'] ? $_M['form']['page'] : 1;
        $this->input['id'] = isset($_M['form']['id']) ? intval($_M['form']['id']) : 0;
        $this->input['lang'] = isset($_M['form']['lang']) ? $_M['form']['lang'] : $_M['config']['met_index_type'];
        $this->input['synchronous'] = $_M['langlist']['web'][$this->input['lang']]['synchronous'];
        $column = load::sys_class('label', 'new')->get('column')->get_column_id($this->input['classnow']);

        $this->input['module'] = $column['module'] ? $column['module'] : 10001;

        //unset($_M['form']);
    }

    /**
     * 添加input
     * @param string $key $key
     * @param string $val $val
     */
    protected function add_input($key, $val)
    {
        global $_M;
        $this->input[$key] = $val;
    }

    /**
     * 添加input
     * @param string $key $key
     * @param string $val $val
     */
    protected function add_array_input($data)
    {
        global $_M;
        foreach ($data as $key => $val) {
            $this->add_input($key, $val);
        }
    }

    public function view($file, $data)
    {
        global $_M;
        if ($_M['config']['met_skin_user']) {
            parent::view($file, $data);
        }else{
            $cache = "<div class='' style='margin: 200px auto; text-align: center;'>
                        <span style='background-color: #ff8571;padding: 20px 20px'>{$_M['word']['notemptips']}</span>
                    </div>";
            echo $cache;
            die();
        }
    }

    /**
     * 销毁
     */
    public function __destruct()
    {
        global $_M;
        //读取缓冲区数据
        $output = str_replace(array('<!--<!---->', '<!---->', '<!--fck-->', '<!--fck', 'fck-->', '', "\r", substr($admin_url, 0, -1)), '', ob_get_contents());
        ob_end_clean();//清空缓冲区
        /*$output = $this->video_replace('/(<video.*?edui-upload-video.*?>).*?<\/video>/', $output);*/
        $output = $this->video_replace('/(<embed.*?edui-faked-video.*?>)/', $output);
        if ($_M['config']['met_qiniu_cloud']) {
            $output = load::plugin('dofooter_replace', 1, array('data' => $output));
        }
        /**
         * 标签数据处理
         */
        $compile = load::sys_class('view/sys_compile', 'new');
        $output = $compile->replace_attr($output);

        if ($_M['form']['html_filename'] && $_M['form']['metinfonow'] == $_M['config']['met_member_force']) {
            //生成静态页
            $filename = urldecode($_M['form']['html_filename']);
            if (stristr(PHP_OS, "WIN")) {
                $filename = @iconv("utf-8", "GBK", $filename);
            }
            if (stristr($filename, '.php')) {
                jsoncallback(array('suc' => 0));
            }

            if ($filename == '404.html') {
                $output = str_replace('../', '', $output);
                $output = str_replace(array('href=""',"href=''"), "href='{$_M['url']['web_site']}'", $output);
            }

            if (file_put_contents(PATH_WEB . $filename, $output)) {
                jsoncallback(array('suc' => 1));
            } else {
                jsoncallback(array('suc' => 0));
            }
        } else {
            echo $output;//输出内容
        }

        DB::close();//关闭数据库连接
        exit;
    }

    /**
     * 视频插件替换
     * @param string $preg 替换的正则规则
     * @param string $content 被替换内容
     */
    function video_replace($preg, $content)
    {
        preg_match_all($preg, $content, $out);
        foreach ($out[1] as $key => $val) {
            preg_match_all('/width=(\'|")([0-9]+)(\'|")/', $val, $w_out);
            $width = $w_out[2][0];

            preg_match_all('/height=(\'|")([0-9]+)(\'|")/', $val, $h_out);
            $height = $h_out[2][0];

            preg_match_all('/poster=(\'|")(.+?)(\'|")/', $val, $poster_out);
            $poster = $poster_out[2][0];

            preg_match_all('/autoplay=(\'|")(.+?)(\'|")/', $val, $autoplay_out);
            $autoplay = $autoplay_out[2][0];
            if($autoplay){
                $autoplay='autoplay';
            }

            preg_match_all('/src=(\'|")(.+?)(\'|")/', $val, $src_out);
            $src = $src_out[2][0];

            preg_match_all('/style=(\'|")(.+?)(\'|")/', $val, $style_out);
            $style = $style_out[2][0];

            $str = "<video class=\"edui-upload-video vjs-default-skin video-js\" controls=\"\" poster=\"{$poster}\" {$autoplay} width=\"{$width}\" height=\"{$height}\" src=\"{$src}\" data-setup=\"{}\" style=\"{$style}\">
                <source src=\"{$src}\" type=\"video/mp4\"/>
            </video>";

            $content = str_replace($out[0][$key], $str, $content);
        }
        return $content;
    }

}

# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>
