<?php

defined('IN_MET') or exit('No permission');
defined('IN_ADMIN') or exit('No permission');

load::sys_class('common');
load::sys_class('nav');
load::sys_func('admin');

class admin extends common
{
    public function __construct()
    {
        global $_M;
        parent::__construct();
        met_cooike_start();
        $this->load_language();
        $this->check();
        $this->lang_switch();
        $this->load_help_url();
        load::plugin('doadmin');
        if ($_M['user']['cookie'] && $_M['form']['sysui_pack']) {
            require PATH_WEB . 'public/ui/v2/static/library.php';
            die;
        }
    }

    protected function load_url_site()
    {
        global $_M;

        if ($_SERVER['SERVER_PORT'] == 443 || $_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1 || $_SERVER['HTTP_X_CLIENT_SCHEME'] == 'https' || $_SERVER['HTTP_FROM_HTTPS'] == 'on' || $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_M['url']['site_admin'] = 'https://' . str_replace(array('/index.php'), '', HTTP_HOST . PHP_SELF) . '/';
        } else {
            $_M['url']['site_admin'] = 'http://' . str_replace(array('/index.php'), '', HTTP_HOST . PHP_SELF) . '/';
        }
        $_M['url']['site'] = preg_replace('/(\/[^\/]*\/$)/', '', $_M['url']['site_admin']) . '/';
        $_M['config']['met_weburl'] = $_M['url']['site'];
    }

    protected function load_url_unique()
    {
        global $_M;
        $_M['url']['ui'] = $_M['url']['site'] . 'app/system/include/public/ui/admin/';
        $_M['url']['adminurl'] = $_M['url']['site_admin'] . "index.php?lang={$_M['lang']}" . '&';
        $_M['url']['own_name'] = $_M['url']['adminurl'] . 'n=' . M_NAME . '&';
        $_M['url']['own_form'] = $_M['url']['own_name'] . 'c=' . M_CLASS . '&';
    }

    protected function load_help_url()
    {
        global $_M;
        $code = @file_get_contents(PATH_WEB . 'config/code.txt');
        $str = '';
        if ($code) {
            $str .= "&metinfo_code=" . trim($code);
        }
        $_M['config']['metinfo_code'] = $code;
        $fields = array('help', 'edu', 'kf', 'qa', 'templates', 'app', 'market');
        foreach ($fields as $val) {
            $_M['config'][$val . '_url'] = "https://u.mituo.cn/api/metinfo?type={$val}" . $str;
        }
    }

    protected function load_language()
    {
        global $_M;
        $admin = admin_information();
        $_M['langset'] = $_M['form']['langset'] ? $_M['form']['langset'] : ($admin['admin_login_lang'] ? $admin['admin_login_lang'] : get_met_cookie('languser'));
        if (!$_M['langset'] || $_M['langset'] == 'metinfo') {
            $_M['langset'] = $_M['config']['met_admin_type'];
        }

        $this->load_word($_M['langset'], 1);
        $this->load_agent_word($_M['langset']);
    }

    protected function load_agent_word($lang)
    {
        global $_M;
        if ($_M['config']['met_agents_type'] >= 2) {
            $query = "SELECT * FROM {$_M['table']['config']} WHERE lang='{$lang}-metinfo'";
            $result = DB::query($query);
            while ($list_config = DB::fetch_array($result)) {
                $lang_agents[$list_config['name']] = $list_config['value'];
            }
            $_M['word']['indexthanks'] = $lang_agents['met_agents_thanks'];
            $_M['word']['metinfo'] = $lang_agents['met_agents_name'];
            $_M['word']['copyright'] = $lang_agents['met_agents_copyright'];
            $_M['word']['oginmetinfo'] = $lang_agents['met_agents_depict_login'];
        }
    }

    protected function filter_config($value)
    {
        $value = str_replace('"', '&#34;', str_replace("'", "&#39;", $value));
        return $value;
    }

    protected function lang_switch()
    {
        global $_M;
        if ($_M['form']['switch']) {
            $url .= "{$_M['url']['site_admin']}index.php?lang={$_M['lang']}";
            if ($_M['form']['a'] != 'dohome') {
                $url .= "&switchurl=" . urlencode(HTTP_REFERER) . "#metnav_" . $_M['form']['anyid'];
            }
            echo "
			<script>
				window.parent.location.href='{$url}';
			</script>
			";
            die();
        }
    }
    protected function gologin()
    {
        global $_M;
        if (M_NAME == 'index') {
            load::mod_class('login/admin/login', 'new')->doindex();
        } else {
            if (is_mobile()) {
                //http_response_code(401);
                $this->error('', 401);
            }

            Header("Location: " . $_M['url']['site_admin']);
        }
    }

    protected function check()
    {
        global $_M;
        $http = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
        $current_url = $http . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (strstr($current_url, $_M['url']['site_admin'] . "index.php")) {
            $admin_index = 1;
        } else {
            $admin_index = '';
        }

        $metinfo_admin_name = get_met_cookie('metinfo_admin_name');
        $metinfo_admin_pass = get_met_cookie('metinfo_admin_pass');
        $query = "SELECT * FROM {$_M['table']['admin_table']} WHERE admin_id = '{$metinfo_admin_name}' AND admin_pass = '{$metinfo_admin_pass}'";
        $admin_info = DB::get_one($query);

        if (!$metinfo_admin_name || !$metinfo_admin_pass) {
            if (!$admin_index) {
                $this->refereCooike();
            }
            met_cooike_unset();
            $this->gologin();
            exit;
        } else {
            if (!$admin_info) {
                if (!$admin_index) {
                    $this->refereCooike();
                }

                met_cooike_unset();
                $this->gologin();
                exit;
            }
        }
        //如果是pc端则跳转链接
        $this->checkAuth($admin_info['admin_op'], $admin_info['admin_type']);
    }

    //检测权限
    protected function checkAuth($admin_op, $admin_type, $m_type = M_TYPE, $m_name = M_NAME, $m_class = M_CLASS, $m_action = M_ACTION, $url = '')
    {
        global $_M;
        if (!strstr($admin_op, "metinfo")) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                $return_url = "";
            } else {
                $return_url = "javascript:window.history.back();";
            }
            if (stristr($m_action, 'add')) {
                if (!strstr($admin_op, "add")) {
                    return $this->returnData('-1', $_M['word']['loginadd']);
                }
            }
            if (stristr($m_action, 'editor') || stristr($_M['form']['sub_type'], 'editor')) {
                if (!strstr($admin_op, "editor")) {
                    return $this->returnData($return_url, $_M['word']['loginedit']);
                }
            }
            if (stristr($m_action, 'del') || stristr($_M['form']['submit_type'], 'del')) {
                if (!strstr($admin_op, "del")) {
                    return $this->returnData($return_url, $_M['word']['logindelete']);
                }
            }
            if (stristr($m_action, 'all')) {
                if (!strstr($admin_op, "metinfo")) {
                    return $this->returnData($return_url, $_M['word']['loginall']);
                }
            }
            if (stristr($m_action, 'save')) {
                if ($_M['form']['submit_type'] == 'del') {
                    if (!strstr($admin_op, "del")) {
                        return $this->returnData($return_url, $_M['word']['logindelete']);
                    }
                } else {
                    if (isset($_M['form']['id']) && $_M['form']['id']) {
                        if (!strstr($admin_op, "editor")) {
                            return $this->returnData($return_url, $_M['word']['loginadd']);
                        }
                    } else {
                        if (!strstr($admin_op, "add")) {
                            return $this->returnData($return_url, $_M['word']['loginadd']);
                        }
                    }
                }
            }
            if (stristr($m_action, 'table')) {
                if (stristr($_M['form']['submit_type'], 'save')) {
                    if ($_M['form']['allid']) {
                        $power_ids = explode(',', $_M['form']['allid']);
                        $e = 0;
                        $a = 0;
                        foreach ($power_ids as $val) {
                            if ($val) {
                                if (is_numeric($val)) {
                                    $e++;
                                } else {
                                    $a++;
                                }
                            }
                            if ($e > 0) {
                                if (!strstr($admin_op, "editor")) {
                                    return $this->returnData($return_url, $_M['word']['loginedit']);
                                }
                            }
                            if ($a > 0) {
                                if (!strstr($admin_op, "add")) {
                                    return $this->returnData($return_url, $_M['word']['loginadd']);
                                }
                            }
                        }
                    }
                }
                if (stristr($_M['form']['submit_type'], 'del')) {
                    if (!strstr($admin_op, "del")) {
                        return $this->returnData($return_url, $_M['word']['logindelete']);
                    }
                }
            }

            //可视化
            if ($m_action == 'doset_text_content') {
                if (!strstr($admin_op, "editor")) {
                    return $this->returnData($return_url, $_M['word']['loginedit']);
                }
            }
        }
        $n = $m_name;

        if ($n == 'index') {
            $n = 'manage';
        }
        $field = '-';

        if ($n == 'myapp' && $m_class == 'index' && $m_action == 'doAction') {
            if ($_M['form']['handle'] == 'install') {
                $n = 'appinstall';
            } else {
                $n = 'appuninstall';
            }
        }

        if ($m_type == 'app') {
            $query = "SELECT no FROM {$_M['table']['applist']} WHERE m_name = '{$n}'  AND m_class = '{$m_class}'";
            $applist = DB::get_one($query);
            if ($applist) {
                $field = $applist['no'];
            }
        } else {
            if (is_mobile()) {
                $route = "(url='{$n}' OR url='{$n}/')";
            } else {
                if (!$url) {
                    $route = "(url='{$n}' OR url='{$n}/')";
                } else {
                    $route = "(url='{$url}' OR url='{$n}')";
                }
            }
            $query = "SELECT field FROM {$_M['table']['admin_column']} WHERE {$route}";
            $admin_column = DB::get_one($query);

            if ($admin_column) {
                $field = $admin_column['field'];
            }
        }
        if (!stristr($admin_type, $field) && $admin_type != 'metinfo') {
            return $this->returnData('-1', $_M['word']['js81']);
        }
        if (stristr($m_name, 'appstore')) {
            if (!stristr($admin_type, '1507') && $admin_type != 'metinfo') {
                return $this->returnData('-1', $_M['word']['appmarket_jurisdiction']);
            }
        }
        if (stristr($m_name, 'theme')) {
            if ($_M['form']['mobile']) {
                if (!stristr($admin_type, '1102') && $admin_type != 'metinfo') {
                    return $this->returnData('-1', $_M['word']['setup_permissions']);
                }
            } else {
                if (!stristr($admin_type, '1101') && $admin_type != 'metinfo') {
                    return $this->returnData('-1', $_M['word']['setup_permissions']);
                }
            }
        }
        if (stristr($m_name, 'column') && stristr($m_action, 'add')) {
            if (!stristr($admin_type, 's9999') && $admin_type != 'metinfo') {
                return $this->returnData('-1', $_M['word']['js81']);
            }
        }
        $redata = array(
            'status' => 1,
        );
        return $redata;
    }

    /**
     * 使用JS方式页面跳转
     * @param  string $url      跳转地址
     * @param  string $langinfo 跳转时alert弹窗内容
     * @param  string $type 1：pc端 2：手机端
     */
    protected function returnData($url, $langinfo)
    {
        if (M_CLASS == 'loadtemp') {
            $redata = array(
                'status' => 0,
                'msg' => $langinfo,
            );
            return $redata;
        } else {
            if ($_SERVER["HTTP_X_REQUESTED_WITH"] && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
                $this->error($langinfo, 403);
            } else {
                if ($langinfo) {
                    $langstr = "alert('{$langinfo}');";
                }

                if ($url == '-1') {
                    $js = "window.history.back();";
                } else {
                    $js = "location.href='{$url}';";
                }
                echo("<script type='text/javascript'>{$langstr} {$js} </script>");
                die();
            }
        }
    }

    protected function refereCooike()
    {
        global $_M;

        $met_adminfile = $_M['config']['met_adminfile'];
        $http = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
        $http_referer = $_SERVER['HTTP_REFERER'];
        $referer_url = explode('?', $http_referer);
        $admin_file_len1 = strlen("/{$met_adminfile}/");
        $admin_file_len2 = strlen("/{$met_adminfile}/index.php");
        if (strrev(substr(strrev($referer_url[0]), 0, $admin_file_len1)) == "/{$met_adminfile}/" || strrev(substr(strrev($referer_url[0]), 0, $admin_file_len2)) == "/{$met_adminfile}/index.php" || !$referer_url[0]) {
            $referer_url = "{$http}://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
        }

        if (!strstr($referer_url, "return.php")) {
            if (!$_COOKIE['re_url']) {
                met_setcookie("re_url", $referer_url, time() + 3600);
            }
        }
    }

    public function access_option($name = '', $value = '')
    {
        global $_M;
        $group = load::sys_class('group', 'new')->get_group_list();
        $list = array();
        $list[] = array('name' => $_M['word']['unrestricted'], 'val' => 0);
        foreach ($group as $key => $val) {
            $arr = array('name' => $val['name'], 'val' => $val['id']);
            $list[] = $arr;
        }
        $admin_id = $val['id'] + 1;
        $list[] = array('name' => $_M['word']['metadmin'], 'val' => $admin_id);

        /*foreach ($list as $key => $val) {
        if ($value == $val['val']) {
        $list[$key]['checked'] = 1;
        }
        }*/
        return $list;
    }

    /**
     *
     * @return array
     */
    public function getMetAdmin()
    {
        global $_M;
        $metinfo_admin_name = get_met_cookie('metinfo_admin_name');
        $met_admin = DB::get_one("select * from {$_M['table']['admin_table']} where admin_id='{$metinfo_admin_name}'");
        return $met_admin;
    }
    /**
     * [js系统变量]
     * @return [type] [description]
     */
    public function sys_json()
    {
        global $_M;
        $_M['config']['metinfo_version'] = str_replace('.', '', $_M['config']['metcms_v']);
        $arrlanguage = $_COOKIE['arrlanguage'];
        $arrlanguage = explode('|', $arrlanguage);
        if (in_array('metinfo', $arrlanguage) || in_array('1002', $arrlanguage)) {
            $langprivelage = 1;
        } else {
            $langprivelage = 0;
        }

        $met_para = array(
            'met_editor' => $_M['config']['met_editor'],
            'met_keywords' => $_M['config']['met_keywords'],
            'met_alt' => $_M['config']['met_alt'],
            'met_atitle' => $_M['config']['met_atitle'],
            'metcms_v' => $_M['config']['metcms_v'],
            'patch' => $_M['config']['patch'],
            'langprivelage' => $langprivelage,
            'url' => array(
                'admin' => $_M['url']['site_admin'],
                'api' => $_M['url']['api'],
                'own_form' => $_M['url']['own_form'],
                'own_name' => $_M['url']['own_name'],
                'own' => $_M['url']['own'],
                'own_tem' => $_M['url']['own_tem'],
            ),
        );
        $met_para = jsonencode($met_para);

        $copyright = str_replace('$metcms_v', $_M['config']['metcms_v'], $_M['config']['met_agents_copyright_foot']);
        $copyright = str_replace('$m_now_year', date('Y', time()), $copyright);
        $copyright = str_replace('&#34;', '', $copyright);
        if (strstr($copyright, 'www.mituo.cn') || strstr($copyright, 'www.metinfo.cn')) {
            $copyright = preg_replace_callback('/\/\/([a-zA-Z0-9-_\.\?&]+)/', function ($match) use ($_M) {
                if ($match && $match[1]) {
                    if (strstr($match[1], '?')) {
                        $type = '&';
                    } else {
                        $type = '?';
                    }

                    return $match[1] . $type . 'metinfo_code=' . $_M['config']['metinfo_code'];
                }
            }, $copyright);
        }

        $sys_json = array(
            'copyright' => $copyright,
            'met_para' => $met_para,
        );
        return $sys_json;
    }

    /**
     * 判断后台目录是否安全
     */
    public function admin_folder_safe()
    {
        global $_M;
        $result = 1;
        if (!$_M['config']['met_safe_prompt']) {
            //判断后来路径是否包含admin和网站关键词
            if (preg_match("/\/admin\/$/", $_M['url']['site_admin'])) {
                $result = 0;
            }

            $site_arr = explode('/', rtrim($_M['url']['site_admin'], '/'));
            $admin_name = array_pop($site_arr);
            if ($admin_name == $_M['config']['met_keywords'] && $_M['config']['met_keywords']) {
                $result = 0;
            }
        }
        return $result;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        #buffer::clearConfig();
    }
}
