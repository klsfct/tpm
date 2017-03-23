<?php
header("Content-type: text/html; charset=utf-8");
session_start();

    if(@$_POST['app_name']) {
        $data = array(
            'app' => array(
                'name' => trim($_POST['app_name']),
                'admin' => trim($_POST['username']),
                'role_name' => trim($_POST['role_name']),
                'pwd' => trim($_POST['pwd']),
                'repwd' => trim($_POST['repwd']),
                'csrf_key' => trim($_POST['csrf_key']),
            ),
            'db' => array(
                'host' => trim($_POST['db_host']),
                'port' => trim($_POST['db_port']),
                'user' => trim($_POST['db_admin']),
                'prex' => trim($_POST['db_prex']),
                'pwd'  => trim($_POST['db_pwd']),
                'database' => trim($_POST['db_database']),
            ),
        );

        if($data['app']['pwd'] != $data['app']['repwd']) {
            echo json_encode(array(
                'code' => 50100,
                'data' => '',
                'msg'  => '账号密码和确认密码不一致～',
            ));
        } else {
            @$link = mysqli_connect($data['db']['host'] . ':' . $data['db']['port'],$data['db']['user'],$data['db']['pwd']);
            if(!$link) {
                echo json_encode(array(
                    'code' => 50101,
                    'data' => null,
                    'msg'  => '数据库账号和密码不正确～',
                ));
            } else {
                @$db = mysqli_select_db($link,$data['db']['database']);
                @mysqli_set_charset($link,'utf8');
                if(!$db) {
                    $sql = "CREATE DATABASE {$data['db']['database']} DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
                    if(!mysqli_query($link,$sql)) {

                        echo json_encode(array(
                            'code' => 50102,
                            'data' => null,
                            'msg' => '数据库创建失败~',
                        ));

                        exit;
                    }
                }

                /* 创建所需数据库 */
                mysqli_select_db($link,$data['db']['database']);
                $sql = "CREATE TABLE `{$data['db']['prex']}admin_user` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `username` varchar(50) NOT NULL COMMENT '登录名',
                          `password` varchar(50) NOT NULL COMMENT '密码',
                          `name` varchar(50) NOT NULL COMMENT '名称',
                          `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限组',
                          `salt` varchar(32) NOT NULL COMMENT '密码随机数',
                          `create_time` int(10) NOT NULL COMMENT '创建时间',
                          `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
                          `last_login_time` int(10) DEFAULT NULL COMMENT '最后一次登录时间',
                          `last_login_ip` varchar(20) DEFAULT NULL COMMENT '最后一次登录IP',
                          `email` varchar(200) DEFAULT NULL COMMENT '邮箱',
                          `mobile` varchar(11) DEFAULT NULL COMMENT '手机',
                          `state` int(2) NOT NULL DEFAULT '1',
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC";
                mysqli_query($link,$sql);
                $salt = rand(1000,9999);
                $sql = "INSERT INTO {$data['db']['prex']}admin_user (username, password, name, role_id, state, salt, create_time, update_time) VALUES
                        ('{$data['app']['admin']}',md5('{$data['app']['pwd']}{$salt}'),'{$data['app']['admin']}',1,1,'{$salt}',unix_timestamp() ,unix_timestamp() )";
                mysqli_query($link,$sql);

                $sql = "CREATE TABLE `{$data['db']['prex']}admin_log` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `group` varchar(32) DEFAULT NULL,
                          `controller` varchar(32) DEFAULT NULL,
                          `function` varchar(32) DEFAULT NULL,
                          `desc` text,
                          `uid` int(11) DEFAULT NULL,
                          `create_time` int(10) DEFAULT NULL,
                          `ip` varchar(32) DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
                mysqli_query($link,$sql);

                $sql = "CREATE TABLE `{$data['db']['prex']}rbac_role` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `title` varchar(32) DEFAULT NULL,
                      `state` int(2) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色表'";
                mysqli_query($link,$sql);
                $sql = "INSERT INTO {$data['db']['prex']}rbac_role (title,state) VALUES ('{$data['app']['role_name']}',1)";
                mysqli_query($link,$sql);

                $sql = "CREATE TABLE `{$data['db']['prex']}rbac_node` (
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
                mysqli_query($link,$sql);

                $sql = "CREATE TABLE `{$data['db']['prex']}rbac_access` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `node_id` int(11) DEFAULT NULL COMMENT '用户节点ID',
                      `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='关联表'";
                mysqli_query($link,$sql);

                $sql = "CREATE TABLE `{$data['db']['prex']}rbac_group` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` varchar(32) DEFAULT NULL,
                  `key` varchar(32) DEFAULT NULL,
                  `sort` int(5) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `{$data['db']['prex']}rbac_group_key_uindex` (`key`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色权限分组'";
                mysqli_query($link,$sql);

//                $sql = "CREATE TABLE `{$data['db']['prex']}rbac_menu` (
//                  `id` int(11) NOT NULL AUTO_INCREMENT,
//                  `alias` varchar(32) DEFAULT NULL COMMENT '别名',
//                  `href` varchar(50) DEFAULT NULL COMMENT '导航链接',
//                  `title` varchar(32) DEFAULT NULL COMMENT '导航文案',
//                  `gid` int(11) DEFAULT NULL COMMENT '分组ID',
//                  `sort` int(11) DEFAULT NULL COMMENT '排序',
//                  PRIMARY KEY (`id`),
//                  UNIQUE KEY `tb_rbac_menu_id_uindex` (`id`)
//                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='导航表'";
                $sql = "CREATE TABLE `{$data['db']['prex']}rbac_menu` (
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
                mysqli_query($link,$sql);

                /* 写文件配置 */
                $path = dirname(__FILE__);
                $config = $path . '/Application/Common/Conf/database.php';
                $time = time();
                $content = "<?php 
    return array(
  
        'DB_TYPE'        => 'mysql',
        // 数据库类型
        'DB_HOST'        => '{$data['db']['host']}',
        // 服务器地址
        'DB_NAME'        => '{$data['db']['database']}',
        // 数据库名
        'DB_USER'        => '{$data['db']['user']}',
        // 用户名
        'DB_PWD'         => '{$data['db']['pwd']}',
        // 密码
        'DB_PORT'        => {$data['db']['port']},
        // 端口
        'DB_PREFIX'      => '{$data['db']['prex']}',
        // 数据库表前缀
        'DB_CHARSET'     => 'utf8',
        // 字符集
        // 防止scrf的key
        'CSRF_KEY' => '{$data['app']['csrf_key']}',
        'CREATE_TIME' => {$time},
    );
                ";
                file_put_contents($config,$content);
                $dirname = dirname(__FILE__);
                rename($dirname.'/install.php',$dirname.'/'.md5($time).'.php');
                $_SESSION['ucenter'] = array(
                        'login' => $data['app']['admin'],
                        'name' => $data['app']['admin'],
                        'logintime' => $time,
                        'csrf_key' => md5($time . '_' . $data['app']['csrf_key']),
                        'token' => md5($time . '+' . $data['app']['csrf_key']),
                );

                echo json_encode(array(
                    'code' => 2000,
                    'data' => $_SESSION['ucenter']['token'],
                    'msg'  => '数据库账号信息正确~',
                ));
            }
        }
        exit;
    }
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <!-- Bootstrap Styles-->
    <link href="/Public/assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FontAwesome Styles-->
    <link href="/Public/assets/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom Styles-->
    <link href="/Public/assets/css/custom-styles.css" rel="stylesheet" />
    <!-- Google Fonts-->
</head>
<body>
<div id="wrapper">

    <div id="page-wrapper" >
        <div id="page-inner">
            <div class="row">
                <div class="col-md-6 col-sm-6 col-md-offset-3 col-sm-offset-3">
                    <h4 class="page-header">
                        TMP <small>基于ThinkPHP3.2的后台模块～</small>
                    </h4>
                </div>
            </div>
            <!-- /. ROW  -->

            <div class="row">
                <div class="col-md-6 col-sm-6 col-md-offset-3 col-sm-offset-3">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            数据库配置
                        </div>
                        <div class="panel-body">
                            <table class="table table-bordered table-responsive">
                                <tr>
                                    <td width="120">数据库地址</td>
                                    <td>
                                        <input type="text" class="form-control" id="host" placeholder="请输入数据库地址" value="127.0.0.1">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="120">数据库端口</td>
                                    <td>
                                        <input type="text" class="form-control" id="port" placeholder="请输入数据库端口号" value="3306">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="120">数据库账号</td>
                                    <td>
                                        <input type="text" class="form-control" id="account" placeholder="请输入数据库账号" value="">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="120">密码</td>
                                    <td>
                                        <input type="password" class="form-control" id="password" placeholder="请输入密码">
                                    </td>
                                </tr>

                                <tr>
                                    <td width="120">数据库</td>
                                    <td>
                                        <input type="text" class="form-control" id="database" placeholder="请输入数据库名称" value="">
                                    </td>
                                </tr>

                                <tr>
                                    <td width="120">表前缀</td>
                                    <td>
                                        <input type="text" class="form-control" id="prex" placeholder="请输入表前缀" value="tb_">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-6 col-md-offset-3 col-sm-offset-3">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            初始值
                        </div>
                        <div class="panel-body">
                            <table class="table table-bordered table-responsive">
                                <tr>
                                    <td width="120">项目名称</td>
                                    <td>
                                        <input type="text" class="form-control" id="app_name" placeholder="请输入后台项目名称" value="Master后台">
                                    </td>
                                </tr>

                                <tr>
                                    <td width="120">默认角色名称</td>
                                    <td>
                                        <input type="text" class="form-control" id="role_name" placeholder="请输入默认的角色名称" value="超级管理员">
                                    </td>
                                </tr>

                                <tr>
                                    <td width="120">管理员名称</td>
                                    <td>
                                        <input type="text" class="form-control" id="username" placeholder="请输入管理员名称" value="">
                                    </td>
                                </tr>

                                <tr>
                                    <td width="120">管理员密码</td>
                                    <td>
                                        <input type="password" class="form-control" id="userpwd" placeholder="请输入管理员密码">
                                    </td>
                                </tr>

                                <tr>
                                    <td width="120">确认管理员密码</td>
                                    <td>
                                        <input type="password" class="form-control" id="reuserpwd" placeholder="请确认管理员密码">
                                    </td>
                                </tr>

                                <tr>
                                    <td width="120">防攻击字符串</td>
                                    <td>
                                        <input type="text" class="form-control" id="csrf_key" placeholder="请输入防攻击字符串" value="<?php echo md5(time() . rand(1,9999) ); ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-6 col-md-offset-3 col-sm-offset-3">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            注意事项
                        </div>
                        <div class="panel-body">
                            <ol>
                                <li>方法名自动解析</li>
                            </ol>
                        </div>
                        <div class="panel-footer">
                            <button class="btn btn-sm btn-default" type="button" id="installCommonModule">安装</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- /. ROW  -->
            <footer><p> <center> Copyright &copy; 2016.Company name All rights reserved.<a target="_blank" href="http://sc.chinaz.com/moban/">&#x7F51;&#x9875;&#x6A21;&#x677F;</a></center></p></footer>
        </div>
        <!-- /. PAGE INNER  -->
    </div>
    <!-- /. PAGE WRAPPER  -->
</div>
<!-- /. WRAPPER  -->
<!-- JS Scripts-->
<!-- jQuery Js -->
<script src="/Public/assets/js/jquery-1.10.2.js"></script>
<!-- Bootstrap Js -->
<script src="/Public/assets/js/bootstrap.min.js"></script>
<!-- Metis Menu Js -->
<script src="/Public/assets/js/jquery.metisMenu.js"></script>
<!-- Custom Js -->
<script src="/Public/assets/js/custom-scripts.js"></script>

<script>
    $(function(){

        $('#installCommonModule').click(function(){
            var obj = {
                db_host : $('#host').val(),
                db_port : $('#port').val(),
                db_admin: $('#account').val(),
                db_pwd : $('#password').val(),
                db_prex : $('#prex').val(),
                db_database : $('#database').val(),

                app_name : $('#app_name').val(),
                role_name : $('#role_name').val(),
                username : $('#username').val(),
                pwd : $('#userpwd').val(),
                repwd : $('#reuserpwd').val(),
                csrf_key : $('#csrf_key').val(),

                cate : 1,   // 1 安装数据库    2 扫描节点初始化数据
            };

            $.post('/install.php',obj,function(callback) {
                if(callback.code == 2000) {

                    if(callback.code === 2000) {    //
                        window.location.href = '/Rbac/InitRbac/index?token=' + callback.data;
                    } else {
                        alert(callback.msg);
                    }


                } else {
                    alert(callback.msg);
                }
            },'json');

        });

    });
</script>
</body>
</html>

