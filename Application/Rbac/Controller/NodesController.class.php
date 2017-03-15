<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-16
 * Time: 下午4:35
 * 角色权限节点
 */

namespace Rbac\Controller;


use Rbac\Model\RbacModel;
use Think\Controller;

/**
 * Class NodesController
 * @package Rbac\Controller
 * @alias 节点管理
 */
class NodesController extends Controller
{
    /**
     * @alias 节点列表
     */
    public function index() {
        $model = new RbacModel();
        $_nodes = $model->findAllNodes();
        $nodes = array();
        foreach($_nodes as $_node) {
            $nodes[$_node['id']] = $_node;
        }

        $this->assign('items',$nodes);
        $this->display();
    }
}