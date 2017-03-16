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
        $params = array(
            'level' => I('get.level',999,'intval'),
            'pagesize' => $this->pagesize,
        );
        $res = $model->findAllNodes($params);

        $this->assign('level',$params['level']);
        $this->assign('page',$res['show']);
        $this->assign('items',$res['items']);
        $this->display();
    }

    /**
     * @alias   修改节点信息
     * @author  saive@cneli.com
     * @date    2017-03-14
     */
    public function ajaxNode() {
        $id = I('post.id',0,'intval');
        $params = array(
            'title' => I('post.title','','trim'),
            'update_time' => time(),
        );

        $oldvalue = I('post.oldtitle','','trim');
        if($params['title'] == $oldvalue) {
            $this->ajaxReturn(array(
                'code' => 2000,
                'data' => null,
                'msg' => '节点信息修改成功～',
            ));
        }

        addLog("修改节点名称由“{$oldvalue}”改为“{$params['title']}”");
        $model = new RbacModel();
        if($model->updateNodeByNid($id,$params)) {
            $this->ajaxReturn(array(
                'code' => 2000,
                'data' => null,
                'msg' => '节点信息修改成功～',
            ));
        } else {
            $this->ajaxReturn(array(
                'code' => 50100,
                'data' => null,
                'msg' => '节点信息修改失败～',
            ));
        }
    }
}