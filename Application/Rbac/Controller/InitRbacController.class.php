<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-8
 * Time: 下午10:14
 * 导入已经存在的节点
 */

namespace Rbac\Controller;

use Think\Controller;

/**
 * Class InitRbacController
 * @package Rbac\Controller
 * @alias 初始化角色管理
 */
class InitRbacController extends Controller
{

    private $protectedFunctionMap = array(
        '__construct', // 构造方法
        'display', // TP方法
        'show', // TP方法
        'fetch', // TP方法
        'buildHtml', // TP方法
        'theme', // TP方法
        'assign', // TP方法
        '__set', // TP方法
        'get', // TP方法
        '__get', // TP方法
        '__isset', // TP方法
        '__call', // TP方法
        'error', // TP方法
        'success', // TP方法
        'ajaxReturn', // TP方法
        'redirect', // TP方法
        '__destruct', // TP方法
        'dispatchJump', // TP方法
    );

    /**
     * @alias 节点初始化
     */
    public function index ()
    {
        $map = $this->_getDB();
        $this->assign('db_map',$map);
        $this->display();
    }

    /**
     * 扫描项目节点
     * @alias 扫描节点
     */
    public function ajaxScanNodes ()
    {
        $dirs      = scandir(APP_PATH);
        $nodes     = array();
        $protected = array( //受保护的文件名common（THinkPHP占用）,runtime（缓存目录）,base(基累名)
                            'common',
                            'runtime',
                            '.',
                            '..',
                            'base',
        );
        foreach ($dirs as $dir) {
            $_dir = strtolower($dir);
            if (!in_array($_dir, $protected) && is_dir(APP_PATH . $dir)) {
                $controllerDir   = APP_PATH . $dir . '/Controller';
                $controllerFiles = scandir($controllerDir);
                foreach ($controllerFiles as $cfile) {
                    if (!in_array($cfile, $protected)) {
                        $_tmpControllerName = substr($cfile, 0, strpos($cfile, 'Controller'));
                        if (!empty($_tmpControllerName) && !in_array($_tmpControllerName, $this->protectedFunctionMap)) {
                            $_tmpClassName = "\\{$dir}\\Controller\\" . substr($cfile, 0, strpos($cfile, '.class.php'));
                            $_tmpClass     = new \ReflectionClass($_tmpClassName);

                            $_cdoc = $_tmpClass->getDocComment();
                            $_cdoc = parseDoc($_cdoc);

                            $methods       = $_tmpClass->getMethods();
                            foreach ($methods as $method) {
                                $method = $method->getName();
                                if ($method && !in_array($method, $this->protectedFunctionMap)) {
                                    $_tmpClass = new \ReflectionMethod($_tmpClassName, $method);
                                    $_doc = $_tmpClass->getDocComment();
                                    $_docs = parseDoc($_doc);
                                    if ($_tmpClass->isPublic()) {
                                        $nodes[ $dir ][ $_tmpControllerName ][] = array(
                                            'method' => $method,
                                            'isAjax' => strtolower(substr($method, 0, 4)) == 'ajax' ? 1 : 0,
                                            'isMenu' => strtolower(substr($method, 0, 2)) == 'nm' ? 0 : 1,
                                            'alias'  => $_docs['alias'] ? : $method,
                                            'calias' => $_cdoc['alias'] ? : $_tmpControllerName,    //控制器解析
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->ajaxReturn(array(
            'code' => 2000,
            'data' => $nodes,
            'msg'  => '扫描成功~',
        ));
    }

    /**
     * 检测RBAC相关表
     * @return array
     */
    private function _getDB() {
        $sql = "SHOW tables LIKE '%rbac_%'";
        $res = M()->query($sql);
        $map = array(
            'isnode' => 0,
            'isaccess' => 0,
            'isgroup' => 0,
            'isrole' => 0,
            'ismenu' => 0,
        );
        $prex = C('DB_PREFIX');

        foreach($res as $v) {
            $_tmpName = end($v);
            $key = str_replace("{$prex}rbac_",'is',$_tmpName);
            $map[$key] = 1;
        }

        return $map;
    }

    /**
     * @alias 重新安装数据库
     */
    public function ajaxInstallDB() {
        $cate = I('post.cate','diffinstall');
        $prex = C('DB_PREFIX');
        if($cate == 'reinstall') {  //重新安装
            $map = $this->_getDB();

            $sql = '';
            if($map['isnode']) {
                $sql  .= "DROP TABLE {$prex}rbac_node;";
            }

            if($map['isaccess']) {
                $sql  .= "DROP TABLE {$prex}rbac_access;";
            }

            if($map['isrole']) {
                $sql  .= "DROP TABLE {$prex}rbac_role;";
            }

            if($map['isgroup']) {
                $sql  .= "DROP TABLE {$prex}rbac_group;";
            }

            if($map['ismenu']) {
                $sql  .= "DROP TABLE {$prex}rbac_menu;";
            }

            if($sql) {
                M()->execute($sql);
            }
            $roleSql = "CREATE TABLE `{$prex}rbac_role` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `title` varchar(32) DEFAULT NULL,
                      `state` int(2) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色表'";
            M()->execute($roleSql);

            $role = array(
                'title' => '超级管理员组',
                'state' => 1,
                'id'    => 1
            );
            M('RbacRole')->add($role);  //添加超级管理员~

            $nodeSql = "CREATE TABLE `tb_rbac_node` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `title` varchar(32) DEFAULT NULL COMMENT '显示标题',
                      `key` varchar(32) DEFAULT NULL COMMENT 'key',
                      `level` int(2) DEFAULT NULL COMMENT '等级:0：项目\n1：分组（TP分组）\n2：控制器\n3：操作名称',
                      `state` int(3) DEFAULT '1' COMMENT '状态：0：禁用\n1：正常使用',
                      `pid` int(11) DEFAULT NULL,
                      `is_ajax` int(2) DEFAULT '0',
                      `unique_key` varchar(32) DEFAULT NULL,
                      `create_time` int(10) DEFAULT NULL,
                      `update_time` int(10) DEFAULT NULL,
                      `is_menu` int(2) DEFAULT '1',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `tb_admin_node_id_uindex` (`id`),
                      UNIQUE KEY `tb_rbac_node_unique_key_uindex` (`unique_key`)
                    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色权限节点表'";

            M()->execute($nodeSql);

            $accessSql = "CREATE TABLE `{$prex}rbac_access` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `node_id` int(11) DEFAULT NULL COMMENT '用户节点ID',
                      `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
            M()->execute($accessSql);

            $groupSql = "CREATE TABLE `{$prex}rbac_group` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` varchar(32) DEFAULT NULL,
                  `key` varchar(32) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色权限分组'";
            M()->execute($groupSql);

            M('RbacGroup')->add(array(
                'title' => '控制台',
                'key'   => '控制台',
            ));

            $menuSql = "CREATE TABLE `{$prex}rbac_menu` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `alias` varchar(32) DEFAULT NULL COMMENT '别名',
                  `href` varchar(50) DEFAULT NULL COMMENT '导航链接',
                  `title` varchar(32) DEFAULT NULL COMMENT '导航文案',
                  `gid` int(11) DEFAULT NULL COMMENT '分组ID',
                  `sort` int(11) DEFAULT NULL COMMENT '排序',
                  `node_unique_key` varchar(32) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `tb_rbac_menu_id_uindex` (`id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='导航表'";
            M()->execute($menuSql);

            $this->ajaxReturn(array(
                'code' => 2000,
                'data' => null,
                'msg'  => '角色权限表初始化成功~',
            ));
        }
    }

    /**
     * @alias 导入节点
     */
    public function ajaxImportNodes() {
        $items = I('items','post');
        $prex = C('DB_PREFIX');
        $values = array(
            array(
                'id' => 1,
                'title' => 'MASTER后台项目',
                'key'   => 'MASTER后台项目',
                'level' => 0,
                'state' => 1,
                'pid'   => 0,
                'is_ajax' => 0,
                'unique_key' => md5(strtolower('MASTER后台项目')),
                'is_menu' => 1
            ),

        );
        $i = 2;
        foreach($items as $key=>$item) {
            $pid = $i;  //分组id，控制器的pid
            /* 分组 */
            $values[] = array(
                'id' => $i,
                'title' => $key,
                'key'   => $key,
                'level' => 1,
                'state' => 1,
                'pid'   => 1,
                'is_ajax' => 0,
                'unique_key' => md5(strtolower($key)),
                'is_menu' => 1,
            );
            $i++;
            /* 控制器 */
            foreach($item as $_key => $_item) {
                $_pid = $i; //控制器id，操作的pid
                $values[] = array(
                    'id' => $i,
                    'title' => isset($_item[0]['calias']) ? $_item[0]['calias'] : $_key,
                    'key'   => $_key,
                    'level' => 2,
                    'state' => 1,
                    'pid'   => $pid,
                    'is_ajax' => 0,
                    'unique_key' => md5(strtolower($key . '_' . $_key)),    //活动key
                    'is_menu' => 1,
                );
                $i++;
                foreach($_item as $__item) {
                    $values[] = array(
                        'id'    => $i,
                        'title' => $__item['alias'],
                        'key'   => $__item['method'],
                        'level' => 3,
                        'state' => 1,
                        'pid'   => $_pid,
                        'is_ajax' => $__item['isAjax'],
                        'unique_key' => md5(strtolower($key . '_' . $_key . '_' . $__item['method'])),
                        'is_menu' => $__item['isMenu'],
                    );
                    $i++;
                }
            }
        }
        $time = time();
        $sql = "INSERT INTO `{$prex}rbac_node` (`id`,`title`,`key`,`level`,`state`,`pid`,`is_ajax`,create_time,update_time,unique_key,is_menu) VALUES ";
        foreach($values as $value) {
            $sql .= "({$value['id']},'{$value['title']}','{$value['key']}',{$value['level']},{$value['state']},{$value['pid']},{$value['is_ajax']},{$time},{$time},'{$value['unique_key']}',{$value['is_menu']}),";
        }

        $sql = substr($sql,0,-1) . " ON DUPLICATE KEY UPDATE update_time = {$time}";
        M()->execute("DELETE FROM `{$prex}rbac_node`");
        M()->execute($sql);

        /* 导入导航 */
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
        M('RbacMenu')->delete();
        M('RbacMenu')->addAll($items);

        $groups = array_keys($items);
        array_unique($groups);

        $data = array();
        foreach($groups as $group) {
            $data[] = array(
                'title' => $group,
                'key'   => $group,
                'sort'  => 1
            );
        }
        M('RbacGroup')->addAll($data);

        $nodes = M('RbacNode')->field('id')->select();
        $access = array();
        foreach($nodes as $node) {
            $access[] = array(
                'node_id' => $node['id'],
                'role_id' => 1,
            );
        }
        M('RbacAccess')->addAll($access);

        $this->ajaxReturn(array(
            'code' => 2000,
            'data' => array(
                'sql' => M('RbacAccess')->getLastSql(),
                'nodes' => $nodes
            ),
            'msg'  => '节点导入成功~',
        ));
    }

}