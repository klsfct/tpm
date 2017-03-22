<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think;
/**
 * ThinkPHP 控制器基类 抽象类
 */
abstract class Controller {

    /**
     * 视图实例对象
     * @var view
     * @access protected
     */    
    protected $view     =  null;

    /**
     * 控制器参数
     * @var config
     * @access protected
     */      
    protected $config   =   array();

    /**
     * 导航数组
     * @var array
     * @date    2017-02-16
     * @author  saive
     * @email   saive@cneli.com
     */
    protected $menus = array();

    /**
     * 分页数据
     * @var int
     */
    protected $pagesize = 20;

   /**
     * 架构函数 取得模板对象实例
     * @access public
     */
    public function __construct() {
        Hook::listen('action_begin',$this->config);
        //实例化视图类
        $this->view     = Think::instance('Think\View');
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();

        $requestName = strtolower(MODULE_NAME.CONTROLLER_NAME.ACTION_NAME);
        $this->menus = session('menus');

        if(empty($this->menus) && !empty(session('ucenter'))) {
            $this->_buildMenus();
        }
        if(!in_array($requestName,array('ucenterindexnmlogin','ucenterindexnmlogout'))) {
            $_map = array();
            if(empty($_map)) {
                $_nodes = M('RbacNode')->order('level ASC')->select();
                $__nodes = array();
                $_map = array();
                foreach($_nodes as $_node) {
                    if($_node['level'] == 1) {  //分组
                        $__nodes['groups'][$_node['id']] = $_node;
                    } else if($_node['level'] == 2){    //控制器
                        $_node['p_gid'] = $__nodes['groups'][$_node['pid']]['id'];  //控制器数组的分组ID
                        $__nodes['controllers'][$_node['id']] = $_node;
                    } else if($_node['level'] == 3) {   //方法
                        $_cid = $_node['pid'];
                        $_controller = $__nodes['controllers'][$_cid];
                        $_gid = $_controller['p_gid'];
                        $_group = $__nodes['groups'][$_gid];

                        $_key = strtolower($_group['key'].$_controller['key'].$_node['key']);
                        $_map[$_key] = array(
                            'gtitle' => $_group['title'],
                            'ctitle' => $_controller['title'],
                            'ftitle' => $_node['title'],
                        );
                    }
                }

                session('_map',$_map);
            }

            $_navKey = strtolower(MODULE_NAME . CONTROLLER_NAME . ACTION_NAME);
            $nav = $_map[$_navKey];
            $this->assign('nav',$nav);
            $this->_ucenter();
        }

        $ucenter = session('ucenter');
        if(IS_AJAX && !empty($ucenter)) {
            $md5scrf = $_SERVER['HTTP_AUTHORIZATION'];
            $_md5scrf = md5($ucenter['logintime'] . '_' . C('csrf_key'));
            if($md5scrf !== $_md5scrf) {
                $this->ajaxReturn(array(
                    'code' => 50400,
                    'data' => null,
                    'msg'  => '非法请求～'
                ));
            }
        }
        $this->assign('cur_href',strtolower(MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME));
        $this->assign('module_name',strtolower(MODULE_NAME));   //分组名称
        $this->assign('menus',$this->menus);
    }

    /**
     * 用户事件
     */
    protected function _ucenter() {
        $ucenter = session('ucenter');
        if(empty($ucenter)) {
            $this->redirect('/login');
        } else {
            $key = md5(strtolower(MODULE_NAME . '_' . CONTROLLER_NAME . '_' . ACTION_NAME));
            $map = session('nodesMap');
            $_key = strtolower(MODULE_NAME.CONTROLLER_NAME);
            if(($_key == 'rbacinitrbac' && (empty($ucenter['token'])) || $ucenter['token'] != md5(C('CREATE_TIME').'+'.C('csrf_key'))) || $_key != 'rbacinitrbac') { //如果token不存在，并token不存在验证
                if(!isset($map[$key])) {
                    if(IS_AJAX) {
                        $this->ajaxReturn(array(
                            'code' => 50410,
                            'data' => array(),
                            'msg'  => '权限不足~',
                        ));
                    } else {
                        $this->error('权限不足～');
                    }
                }
            }
        }
    }

    /**
     * 生成菜单存储与session中，后期可做成生成文件的类型
     *
     * 检测是否生成menus表，如果没有的话就直接显示默认值
     *
     */
    protected function _buildMenus() {
        $prex = C('DB_PREFIX');
        $menuSql = "SHOW TABLES LIKE '{$prex}rbac_menu'";
        $res = M()->query($menuSql);
        $_nodes_map = session('nodesMap');

        if(empty($res)) {
            $menus = array(
                '/' => '控制台',
            );
        } else {
            $res =  M('RbacMenu')
                ->field("{$prex}rbac_menu.id,{$prex}rbac_menu.node_unique_key,{$prex}rbac_menu.sort,{$prex}rbac_menu.gid,{$prex}rbac_menu.title AS subtitle,rg.title,rg.key,{$prex}rbac_menu.href,{$prex}rbac_menu.alias")
                ->join("{$prex}rbac_group AS rg ON rg.id={$prex}rbac_menu.gid",'left')
                ->order("rg.sort ASC,{$prex}rbac_menu.sort ASC")
                ->select();
            if(empty($res)) {
                $sql = "
                        SELECT
                          rn.title,
                          rn.`key`,
                          rn2.title,
                          rn2.`key` AS ckey,
                          rn3.title,
                          rn3.`key` AS fkey,
                          rn.title AS title,
                          concat_ws('/',rn3.`key`,rn2.`key`,rn.`key`) AS href,
                          concat_ws('/',rn3.`key`,rn2.`key`,rn.`key`) AS alias
                        FROM {$prex}rbac_node rn LEFT JOIN {$prex}rbac_node rn2 ON rn.pid = rn2.id
                          LEFT JOIN {$prex}rbac_node rn3 ON rn2.pid = rn3.id
                        WHERE rn.level = 3 AND rn.state = 1 AND rn.is_ajax = 0 AND rn.is_menu = 1
                        ";
                $items = M()->query($sql);
                $groups = M('RbacGroup')->field('id,key')->select();
                $gmap = array();
                foreach($groups as $group) {
                    $gmap[$group['key']] = $group['id'];
                }

                foreach($items as $key=>$item) {
                    $items[$key]['gid'] = $gmap[$item['fkey']];
                    $items[$key]['sort'] = 0;
                    $items[$key]['node_unique_key'] = md5(strtolower($items[$key]['fkey'] . '_' . $items[$key]['ckey'] . '_' . $items[$key]['key']));
                    unset($items[$key]['ckey'],$items[$key]['fkey'],$items[$key]['key']);
                }

                M('RbacMenu')->addAll($items);
                $res =  M('RbacMenu')
                    ->field("{$prex}rbac_menu.id,{$prex}rbac_menu.node_unique_key,{$prex}rbac_menu.sort,{$prex}rbac_menu.gid,{$prex}rbac_menu.title AS subtitle,rg.title,rg.key,{$prex}rbac_menu.href,{$prex}rbac_menu.alias")
                    ->join("{$prex}rbac_group AS rg ON rg.id={$prex}rbac_menu.gid",'left')
                    ->order("rg.sort ASC,{$prex}rbac_menu.sort ASC")
                    ->select();
            }
        }

        foreach($res as $key=>$item) {
            if(!isset($_nodes_map[$item['node_unique_key']])) {
                unset($res[$key]);
            }
        }
        $menus = array();
        foreach($res as $item) {
            $menus[$item['title']][] = $item;
        }
        $this->menus = $menus;
        session('menus',$menus);
        $this->assign('menus',$menus);
    }

    /**
     * 模板显示 调用内置的模板引擎显示方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $content 输出内容
     * @param string $prefix 模板缓存前缀
     * @return void
     */
    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        $this->view->display($templateFile,$charset,$contentType,$content,$prefix);
    }

    /**
     * 输出内容文本可以包括Html 并支持内容解析
     * @access protected
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */
    protected function show($content,$charset='',$contentType='',$prefix='') {
        $this->view->display('',$charset,$contentType,$content,$prefix);
    }

    /**
     *  获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀* 
     * @return string
     */
    protected function fetch($templateFile='',$content='',$prefix='') {
        return $this->view->fetch($templateFile,$content,$prefix);
    }

    /**
     *  创建静态页面
     * @access protected
     * @htmlfile 生成的静态文件名称
     * @htmlpath 生成的静态文件路径
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @return string
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
        $content    =   $this->fetch($templateFile);
        $htmlpath   =   !empty($htmlpath)?$htmlpath:HTML_PATH;
        $htmlfile   =   $htmlpath.$htmlfile.C('HTML_FILE_SUFFIX');
        Storage::put($htmlfile,$content,'html');
        return $content;
    }

    /**
     * 模板主题设置
     * @access protected
     * @param string $theme 模版主题
     * @return Action
     */
    protected function theme($theme){
        $this->view->theme($theme);
        return $this;
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    protected function assign($name,$value='') {
        $this->view->assign($name,$value);
        return $this;
    }

    public function __set($name,$value) {
        $this->assign($name,$value);
    }

    /**
     * 取得模板显示变量的值
     * @access protected
     * @param string $name 模板显示变量
     * @return mixed
     */
    public function get($name='') {
        return $this->view->get($name);      
    }

    public function __get($name) {
        return $this->get($name);
    }

    /**
     * 检测模板变量的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name) {
        return $this->get($name);
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME.C('ACTION_SUFFIX'))) {
            if(method_exists($this,'_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method,$args);
            }elseif(file_exists_case($this->view->parseTemplate())){
                // 检查是否存在默认模版 如果有直接输出模版
                $this->display();
            }else{
                E(L('_ERROR_ACTION_').':'.ACTION_NAME);
            }
        }else{
            E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data,$type='',$json_option=0) {
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data,$json_option).');');  
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);            
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return',$data);
        }
    }

    /**
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        if(true === $ajax || IS_AJAX) {// AJAX提交
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(is_int($ajax)) $this->assign('waitSecond',$ajax);
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // 提示标题
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // 状态
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            $this->assign('message',$message);// 提示信息
            // 成功操作后默认停留1秒
            if(!isset($this->waitSecond))    $this->assign('waitSecond','1');
            // 默认操作成功自动返回操作前页面
            if(!isset($this->jumpUrl)) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }else{
            $this->assign('error',$message);// 提示信息
            //发生错误时候默认停留3秒
            if(!isset($this->waitSecond))    $this->assign('waitSecond','3');
            // 默认发生错误的话自动返回上页
            if(!isset($this->jumpUrl)) $this->assign('jumpUrl',"javascript:history.back(-1);");
            $this->display(C('TMPL_ACTION_ERROR'));
            // 中止执行  避免出错后继续执行
            exit ;
        }
    }

   /**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        // 执行后续操作
        Hook::listen('action_end');
    }
}
// 设置控制器别名 便于升级
class_alias('Think\Controller','Think\Action');
