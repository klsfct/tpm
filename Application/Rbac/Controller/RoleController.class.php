<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-16
 * Time: 下午4:39
 * 角色权限角色
 */

namespace Rbac\Controller;


use Rbac\Model\RbacModel;
use Think\Controller;

/**
 * Class RoleController
 * @package Rbac\Controller
 * @alias 角色管理
 */
class RoleController extends Controller
{
    /**
     * @alias   角色列表
     * @author  saive@cneli.com
     * @date    2017-02-28
     */
    public function index()
    {
        $model = new RbacModel();
        $items = $model->findAllRoles();
        $params = array(
            'level' => 999,
            'pagesize' => 9999,
        );
        $res = $model->findAllNodes($params);
        $_nodes = $res['items'];
        $nodes = array();
        $map = array(
            0 => 'app',
            1 => 'group',
            2 => 'controller',
            3 => 'function'
        );

        foreach ($_nodes as $_node) {
            if ($_node['level'] > 1) {
                $nodes[$map[$_node['level']]][$_node['pid']][] = $_node;
            } else {
                $nodes[$map[$_node['level']]][] = $_node;
            }
        }

        $ucenter = session('ucenter');
        $_map = $model->findAllAccessByRoleId($ucenter['role_id']);
        $map = array();
        foreach ($_map as $_item) {
            $map[$_item['node_id']] = $_item['id'];
        }

        $this->assign('map', $map);
        $this->assign('items', $items);
        $this->assign('nodes', $nodes);
        $this->assign('nodesObj', json_encode($nodes));
        $this->assign('itemsObj', json_encode($items));
        $this->display();
    }

    /**
     * @alias   添加角色
     * @author  saive@cneli.com
     * @date    2017-02-28
     */
    public function ajaxAddRole()
    {
        $title = I('post.title', '', 'trim');
        $model = new RbacModel();
        $role = $model->findRoleByTitle($title);
        if (empty($role)) {
            $role = array(
                'title' => $title
            );
            if ($model->addRole($role)) {
                addLog('添加角色：'.$title);
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => null,
                    'msg' => '角色添加成功～',
                ));
            } else {
                $this->ajaxReturn(array(
                    'code' => 50400,
                    'data' => null,
                    'msg' => '角色添加失败～',
                ));
            }
        } else {
            $this->ajaxReturn(array(
                'code' => 50300,
                'data' => null,
                'msg' => '角色已经存在~',
            ));
        }
    }

    /**
     * @alias   删除角色
     * @author  saive@cneli.com
     * @date    2017-02-28
     */
    public function ajaxDelRole()
    {
        $id = I('post.id', 0, 'intval');
        if ($id) {
            $model = new RbacModel();
            if ($model->delRoleById($id)) {
                addLog('删除角色，ID为：'.$id);
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => null,
                    'msg' => '删除成功~',
                ));
            } else {
                $this->ajaxReturn(array(
                    'code' => 50100,
                    'data' => null,
                    'msg' => '删除失败~',
                ));
            }
        }
    }

    /**
     * @alias   修改角色状态
     * @author  saive@cneli.com
     * @date    2017-03-01
     */
    public function ajaxChangeRole()
    {
        $id = I('post.id');
        $state = I('post.state');
        $model = new RbacModel();
        $params = array(
            'state' => !$state
        );

        if ($model->changeRoleState($id, $params)) {
            addLog('修改角色状态，ID为：'.$id);
            $this->ajaxReturn(array(
                'code' => 2000,
                'data' => null,
                'msg' => '角色状态修改成功～',
            ));
        } else {
            $this->ajaxReturn(array(
                'code' => 50100,
                'data' => null,
                'msg' => '角色状态修改失败~',
            ));
        }
    }

    /**
     * @alias   保存关联关系
     * @author  saive@cneli.com
     * @date    2017-03-06
     */
    public function ajaxSaveAccess()
    {
        $nodes = I('nodes');
        $role_id = I('role_id', 0, 'intval');
        $model = new RbacModel();
        $oldNodes = $model->findAllAccessByRoleId($role_id);
        $_oldNodes = array();
        foreach ($oldNodes as $oldNode) {
            $_oldNodes[] = $oldNode['id'];
        }
        if ($model->reAddAccess($role_id, $nodes)) {
            addLog('配置角色，ID为：'.$role_id.'，由'.json_encode($_oldNodes).',改为：'.json_encode($nodes));
            $this->ajaxReturn(array(
                'code' => 2000,
                'data' => null,
                'msg' => '关联关系保存成功~',
            ));
        } else {
            $this->ajaxReturn(array(
                'code' => 50400,
                'data' => null,
                'msg' => '关联关系保存失败~',
            ));
        }
    }

    /**
     * @alias   查询关联关系
     * @author  saive@cneli.com
     * @date    2017-03-06
     */
    public function ajaxFindAccess()
    {
        $id = I('id', 0, 'intval');
        $model = new RbacModel();
        $nodes = $model->findAllAccessByRoleId($id);
        $nodes = $this->_buildAccessMap($nodes);

        $this->ajaxReturn(array(
            'code' => 2000,
            'data' => array_values($nodes),
            'msg' => '查询成功~',
        ));
    }

    /**
     * @alias   格式化关联关系
     * @author  saive@cneli.com
     * @date    2017-03-06
     * @param $nodes
     * @return array
     */
    private function _buildAccessMap($nodes)
    {
        $res = array();
        foreach ($nodes as $node) {
            $res[$node['node_id']] = $node['node_id'];
        }

        return $res;
    }
}