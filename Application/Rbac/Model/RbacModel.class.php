<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-16
 * Time: 上午11:45
 * 角色权限数据模型
 */

namespace Rbac\Model;


use Think\Model;

class RbacModel extends Model
{

    protected $tableName = 'rbac_node';

    /**
     * 查找全部节点
     * @return mixed
     */
    public function findAllNodes() {
        return $this->select();
    }

    /**
     * 查找分组
     * @return mixed
     */
    public function findAllGroups() {
        return M('RbacGroup')->order('sort ASC')->select();
    }

    /**
     * @alias 查找系统角色列表
     * @param array $params 查询条件【数组】
     * @return mixed    返回列表
     * @date    2017-02-28
     * @author  saive@cneli.com
     */
    public function findAllRoles($params = array()) {
        $map = array();
        if(isset($params['state'])) {
            $map['state'] = $params['state'];
        }

        return M('RbacRole')->where($map)->select();
    }

    /**
     * @alias   根据标题查询角色
     * @author  saive@cneli.com
     * @date    2017-03-01
     * @param string $title
     * @return mixed
     */
    public function findRoleByTitle($title = '') {
        $map = array(
            'title' => $title
        );
        
        return M('RbacRole')->where($map)->find();
    }

    /**
     * @alias   添加角色
     * @author  saive@cneli.com
     * @date    2017-03-01
     * @param array $role
     * @return mixed
     */
    public function addRole($role = array()) {
        $role['state'] = 1;

        return M('RbacRole')->add($role);
    }

    /**
     * @alias   删除角色
     * @author  saive@cneli.com
     * @date    2017-03-01
     * @param int $id
     * @return mixed
     */
    public function delRoleById($id = 0) {
        $map = array(
            'id' => $id
        );

        return M('RbacRole')->where($map)->delete();
    }

    /**
     * @alias   修改角色状态
     * @author  saive@cneli.com
     * @date    2017-03-01
     * @param $id   角色ID
     * @param $params
     * @return bool
     */
    public function changeRoleState($id,$params) {
        $map = array(
            'id' => $id
        );

        return M('RbacRole')->where($map)->save($params);
    }

    /**
     * @param array $groups   分组数据
     * @param $params   删除条件
     * @return bool|string
     * @alias   重新添加分组
     * @date    2017-02-27
     * @author  saive@cneli.com
     */
    public function reAddAllGroups($groups = array(),$params) {
        $model = M('RbacGroup');
        $model->where('id > 0')->delete();
        return $model->addAll($groups);
    }

    /**
     * @alias   重新安装关联关系
     * @author  saive@cneli.com
     * @date    2017-03-06
     * @param int $roleId
     * @param array $nodes
     * @return bool|string
     */
    public function reAddAccess($roleId=0,$nodes=array()) {
        $model = M('RbacAccess');
        $model->where(array('role_id'=>$roleId))->delete();

        $items = array();
        foreach($nodes as $node) {
            $items[] = array(
                'role_id' => $roleId,
                'node_id' => $node
            );
        }

        return $model->addAll($items);
    }

    /**
     * 重新批量添加导航
     * @param $menus
     * @return bool|string
     * 先清空后添加【谨慎使用】
     */
    public function reAddAllMenus($menus) {
        $model = M('RbacMenu');
        $model->where('id > 0')->delete();
        return $model->addAll($menus);
    }

    /**
     * @alias   根据角色查找配置列表
     * @author  saive@cneli.com
     * @date    2017-03-06
     * @param int $roleId
     * @return mixed
     */
    public function findAllAccessByRoleId($roleId = 0) {
        $map = array(
            'role_id' => $roleId
        );

        $prex = C('DB_PREFIX');
        return M('RbacAccess')
                ->where($map)
                ->join("{$prex}rbac_node AS rn ON {$prex}rbac_access.node_id = rn.id",'left')
                ->select();
    }

}