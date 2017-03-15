<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-7
 * Time: 上午1:28
 * 【用户中心-用户列表】
 */

namespace Ucenter\Controller;


use Rbac\Model\RbacModel;
use Think\Controller;
use Ucenter\Model\UcenterModel;

/**
 * Class ListController
 * @package Ucenter\Controller
 * @alias 列表
 */
class ListController extends Controller
{
    /**
     * @alias   用户列表
     * @author  saive@cneli.com
     * @date    2017-02-28
     */
    public function index() {
        $model = new UcenterModel();
        $rbacModel = new RbacModel();
        $roles = $rbacModel->findAllRoles();
        $res = $model->findAllUser();
        $this->assign('page',$res['page']);
        $this->assign('items',$res['items']);
        $this->assign('roles',$roles);
        $this->assign('itemsObj',json_encode($res['items']));

        $this->display();
    }

    /**
     * @alias   添加用户
     * @author  saive@cneli.com
     * @date    2017-02-28
     */
    public function ajaxAddUser() {
        $user = array(
            'username' => I('post.username'),
            'name'  => I('post.name'),
            'role_id'   => I('post.role_id'),
        );
        $model = new UcenterModel();
        $_user = $model->findUserByUsername($user['username']);
        if(!empty($_user)) {
            $this->ajaxReturn(array(
                'code' => 50200,
                'data' => null,
                'msg'  => '用户名已经存在～',
            ));
        } else {
            $user['salt'] = rand(10000,99999);
            $user['password'] = md5($user['username'].$user['salt']);
            $user['create_time']    = time();
            $user['update_time'] = $user['create_time'];
            $user['state'] = 1;
            if($model->addUser($user)) {
                unset($user['salt'],$user['password']);
                addLog("添加用户，用户信息：".json_encode($user));
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => null,
                    'msg'   => '用户添加成功～',
                ));
            } else {
                $this->ajaxReturn(array(
                    'code' => 50300,
                    'data' => null,
                    'msg'   => '用户添加失败~',
                ));
            }
        }
    }

    /**
     * @alias   修改用户状态
     * @author  saive@cneli.com
     * @date    2017-02-28
     */
    public function ajaxChangeUser() {
        $id = I('post.id');
        $state = I('post.state');
        $model = new UcenterModel();
        if($model->changeUserByUid($id,$state)) {
            $this->ajaxReturn(array(
                'code' => 2000,
                'data' => null,
                'msg'  => '用户状态修改成功~',
            ));
        } else {
            $this->ajaxReturn(array(
                'code' => 50100,
                'data' => null,
                'msg'  => '用户状态修改失败～',
            ));
        }

    }
}