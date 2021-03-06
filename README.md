﻿## 简介

基于ThinkPHP3.2的开源模块化代码，系统集成Ucenter（用户中心），RBAC（角色权限管理）等基础模块，用于快速搭建后台系统

## 扩展注释
```angular2html
    @alias  类方法注释，用于扫描类，方法功能描述
```
### 类注释
```angular2html
    <?php
    namespace Rbac\Controller;
    
    use Think\Controller;
    
    /**
     * Class InitRbacController
     * @package Rbac\Controller
     * @alias 初始化角色管理
     */
    class InitRbacController extends Controller
    {}
    ?>
```
### 方法注释
```angular2html
    /**
     * @alias 初始化
     */
    public function index ()
    {
        $map = $this->_getDB();
        $this->assign('db_map',$map);
        $this->display();
    }
```

## 约定
    - ajax方法使用ajax开头，修改用户信息：ajaxUpdateUser()
    - 不生成菜单的方法使用nm开头，用户首页nmMain()
    - 不对外的方法使用‘protected’或‘private’关键字

## 全面的WEB开发特性支持

最新的ThinkPHP为WEB应用开发提供了强有力的支持，这些支持包括：

*  MVC支持-基于多层模型（M）、视图（V）、控制器（C）的设计模式
*  ORM支持-提供了全功能和高性能的ORM支持，支持大部分数据库
*  模板引擎支持-内置了高性能的基于标签库和XML标签的编译型模板引擎
*  RESTFul支持-通过REST控制器扩展提供了RESTFul支持，为你打造全新的URL设计和访问体验
*  云平台支持-提供了对新浪SAE平台和百度BAE平台的强力支持，具备“横跨性”和“平滑性”，支持本地化开发和调试以及部署切换，让你轻松过渡，打造全新的开发体验。
*  CLI支持-支持基于命令行的应用开发
*  RPC支持-提供包括PHPRpc、HProse、jsonRPC和Yar在内远程调用解决方案
*  MongoDb支持-提供NoSQL的支持
*  缓存支持-提供了包括文件、数据库、Memcache、Xcache、Redis等多种类型的缓存支持

## 大道至简的开发理念

ThinkPHP从诞生以来一直秉承大道至简的开发理念，无论从底层实现还是应用开发，我们都倡导用最少的代码完成相同的功能，正是由于对简单的执着和代码的修炼，让我们长期保持出色的性能和极速的开发体验。在主流PHP开发框架的评测数据中表现卓越，简单和快速开发是我们不变的宗旨。

## 安全性

框架在系统层面提供了众多的安全特性，确保你的网站和产品安全无忧。这些特性包括：

*  XSS安全防护
*  表单自动验证
*  强制数据类型转换
*  输入数据过滤
*  表单令牌验证
*  防SQL注入
*  图像上传检测

## 商业友好的开源协议

ThinkPHP遵循Apache2开源协议发布。Apache Licence是著名的非盈利开源组织Apache采用的协议。该协议和BSD类似，鼓励代码共享和尊重原作者的著作权，同样允许代码修改，再作为开源或商业软件发布。