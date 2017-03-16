<?php
namespace Ucenter\Controller;
use Rbac\Model\RbacModel;
use Think\Controller;
use Ucenter\Model\UcenterModel;

/**
 * Class IndexController
 * @package Ucenter\Controller
 * @alias 用户首页
 */
class IndexController extends Controller {
    /**
     * 用户登录模块
     * @alias 登录
     */
    public function nmLogin() {
        $_ucenter = session('ucenter');
        if(!empty($_ucenter)) {
            $this->redirect('/Ucenter/Index/nmMain');
        } else if(IS_AJAX && IS_POST) {    //ajax提交
            $model = new UcenterModel();
            $username = I('post.username','trim');
            $password = I('post.password','trim');
            $user = $model->findUserByUsername($username);
            if(empty($user)) {
                $this->ajaxReturn(array(
                    'code' => 40400,
                    'data' => null,
                    'msg'  => '用户未找到～',
                ));
            } else if(md5($password.$user['salt']) !== $user['password']) {
                $this->ajaxReturn(array(
                    'code' => 50400,
                    'data' => $user,
                    'msg'  => '密码不正确～',
                ));
            } else {
                $time = time();
                $ucenter = array(
                    'id' => $user['id'],
                    'login' => $user['username'],
                    'name'  => $user['name'],
                    'role_id' => $user['role_id'],
                    'logintime' => $time,
                    'csrf_key' => md5($time . '_' . C('csrf_key')),
                    'salt' => $user['salt'],
                    'password' => $user['password'],
                    'role_title' => $user['role_title'],
                    'email' => $user['email'],
                    'last_login_time' => $user['last_login_time'],
                    'last_login_ip' => $user['last_login_ip'],
                    'mobile' => $user['mobile'],
                );
                $model->updateUserLogin($user['id']);
                $model = new RbacModel();
                $_nodes = $model->findAllAccessByRoleId($ucenter['role_id']);
                $nodes = array();
                foreach($_nodes as $_node) {
                    $nodes[$_node['unique_key']] = $_node;
                }
                session('nodesMap',$nodes);
                session('ucenter',$ucenter);
                addLog('登录系统');
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => null,
                    'msg'  => '登录成功~',
                ));
            }
        } else {
            $this->display();
        }
    }

    /**
     * 退出系统
     * @alias 退出
     */
    public function nmLogout() {
        session('ucenter',null);
        session('menus',null);

        $this->success('退出成功～','/login');
    }

    /**
     * 用户中心
     * @alias 用户中心
     */
    public function nmMain() {
        $ucenter = session('ucenter');
        $cate = I('get.cate','default');
        $model = new UcenterModel();
        if($cate == 'info' && IS_POST) {
            $params = array(
                'email' => I('post.email','','trim,strtolower'),
                'mobile' => I('post.mobile','','trim'),
            );

            if($model->updateUcenterByUid($ucenter['id'],$params)) {
                $ucenter['email'] = $params['email'];
                $ucenter['mobile'] = $params['mobile'];
                session('ucenter',$ucenter);
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => null,
                    'msg'  => '更新用户信息～',
                ));
            } else {
                $this->ajaxReturn(array(
                    'code' => 50100,
                    'data' => null,
                    'msg'  => '信息更新失败～',
                ));
            }
        } else {
            $params = array(
                'uid' => $ucenter['id'],
                'pagesize' => $this->pagesize,
            );
            $logs = $model->findAllLog($params);
            $this->assign('logs',$logs);
            $this->assign('cate',$cate);
            $this->display();
        }
    }

    /**
     * @alias 修改密码
     */
    public function nmPassword() {
        parent::_ucenter();
        $this->display();
    }

    /**
     * @alias 修改密码逻辑
     */
    public function ajaxPassword() {
        if(IS_AJAX && IS_POST) {
            $oldPwd = I('post.oldPwd','trim');
            $pwd = I('post.pwd','trim');
            $ucenter = session('ucenter');
            if($ucenter['password'] != md5($oldPwd.$ucenter['salt'])) {
                $this->ajaxReturn(array(
                    'code' => 50100,
                    'data' => null,
                    'msg'  => '用户密码不正确~',
                ));
            } else {
                $model = new UcenterModel();
                $salt = rand(10000,99999);
                $data = array(
                    'salt' => $salt,
                    'password' => md5($pwd.$salt),
                );
                if($model->updatePwdByUserName($data,$ucenter['login'])) {
                    addLog('修改用户密码！');
                    session('ucenter',null);
                    session('menus',null);
                    $this->ajaxReturn(array(
                        'code' => 2000,
                        'data' => null,
                        'msg'  => '用户密码修改成功~',
                    ));
                } else {
                    $this->ajaxReturn(array(
                        'code' => 50101,
                        'data' => null,
                        'msg'  => '用户密码修改失败～',
                    ));
                }
            }
        }
    }
}