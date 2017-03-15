<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-9
 * Time: 下午6:12
 */

return array(
    'rbac-init'          => 'Rbac/InitRbac/index',  //导入节点
    'rbac-ajaxscannodes' => 'Rbac/InitRbac/ajaxScanNodes',  //扫描节点
    'rbac-importnodes' => 'Rbac/InitRbac/ajaxImportNodes',  //导入节点
    'rbac.html' => 'rbac/index/index',   //角色权限首页
    'rbac-installdb' => 'Rbac/InitRbac/ajaxInstallDB',  //安装rbac表
    'rbac-initmenu'  => 'Rbac/InitRbac/ajaxInitMenu', //创建导航菜单

    'rbac-groups' => 'Rbac/Groups/index',   //导航分组列表
    'rbac-savegroups' => 'Rbac/Groups/ajaxSaveGroups',  //保存分组信息
    'rbac-menus'    => 'Rbac/Menus/index',  //导航列表
    'rbac-savemenus'    => 'Rbac/Menus/ajaxSaveMenus',  //保存导航配置
    'rbac-init' => 'Rbac/InitRbac/index',   //初始化
    'rbac-node' => 'Rbac/Nodes/index',  //角色权限节点
    'rbac-group'    => 'Rbac/Groups/index', //角色权限分组
    'rbac-menu' => 'Rbac/Menus/index',  //角色权限导航
    'rbac-access'   => 'Rbac/Access/index', //角色权限配置

    'rbac-addrole'  => 'Rbac/Role/ajaxAddRole', //添加角色
    'rbac-delrole'  => 'Rbac/Role/ajaxDelRole', //删除角色
    'rbac-changerole' => 'Rbac/Role/ajaxChangeRole',    //修改角色状态
);