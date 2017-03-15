<?php
$dirs = scandir(APP_PATH);

$rules = array(
    'news/:year/:month/:day' => array(
        'News/archive',
        'status=1'
    ),
    'news/:id'               => 'News/read',
    'news/read/:id'          => '/news/:1',
    'login'                  => 'Ucenter/index/nmLogin',
    'logout'                 => 'Ucenter/index/nmLogout',
    'ucenter-list'           => 'Ucenter/List/index',
    'ucenter-logs'           => 'Ucenter/Logs/index',

    'player' => 'Player/Index/index',


);


foreach ($dirs as $dir) {
    if(!in_array($dir,array('.','..','Common','Runtime','index.html')) && is_dir(APP_PATH . $dir)) {
        if(file_exists(APP_PATH . $dir . '/Conf/rules.php')) {
            $_tmpArray = include_once APP_PATH . $dir . '/Conf/rules.php';
            $rules = array_merge($rules,$_tmpArray);
        }
    }
}

$database = include_once dirname(__FILE__) . '/database.php';
$config = array(
    'DEFAULT_MODULE'     => 'Ucenter',
    // 默认模块
    'DEFAULT_CONTROLLER' => 'Index',
    // 默认控制器名称
    'DEFAULT_ACTION'     => 'nmMain',
    // 默认操作名称
    'URL_MODEL'      => 2,
    //    'APP_DEBUG'=>true,
    'DB_FIELD_CACHE' => false,
    'HTML_CACHE_ON'  => false,

    'URL_ROUTER_ON'   => true,
    //开启路由规则
    'URL_ROUTE_RULES' => $rules,

);

return array_merge($config,$database);