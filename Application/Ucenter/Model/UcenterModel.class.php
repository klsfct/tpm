<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-6
 * Time: 上午12:20
 * 用户中心数据模型
 */

namespace Ucenter\Model;

use Think\Model;
use Think\Page;

class UcenterModel extends Model
{

    protected $tableName = 'admin_user';

    /**
     * 查找用户
     * @param $params
     * @return mixed
     */
    public function findAllUser($params) {
        if($params['status'] != 999 && $params['status']) {
            $query[] = " state = {$params['status']}";
        }
        $query = implode(' AND ',$query);
        $params['itemcount'] = $this->where($query)->count();
        $page = new Page($params['itemcount'],$params['pagesize']);
        $res['show'] = trim($page->show());
        $prex = C('DB_PREFIX');
        $res['items'] = $this
                        ->where($query)
                        ->field("{$prex}admin_user.id,{$prex}admin_user.username,{$prex}admin_user.name,{$prex}admin_user.role_id,{$prex}admin_user.state,FROM_UNIXTIME({$prex}admin_user.update_time,'%Y-%m-%d %h:%i:%s') AS update_date,rr.title AS role")
                        ->join("{$prex}rbac_role rr ON rr.id={$prex}admin_user.role_id",'left')
                        ->limit($page->firstRow.',',$page->listRows)
                        ->order('id DESC')
                        ->select();

        return $res;
    }

    /**
     * 查找用户
     * @param $username
     * @return mixed
     */
    public function findUserByUsername($username) {
        $map = array(
            'username' => $username,
        );

        return M('AdminUser')->where($map)->find();
    }

    /**
     * @alias   修改用户密码～
     * @author  saive@cneli.com
     * @date    2017-02-28
     * @param $data
     * @param $username
     * @return bool
     */
    public function updatePwdByUserName($data,$username) {
        $map = array(
            'username' => $username,
        );

        return $this->where($map)->save($data);
    }

    /**
     * @alias   根据用户ID修改用户状态
     * @author  saive@cneli.com
     * @date    2017-02-28
     * @param $uid  用户ID
     * @param $state    状态【0,1】
     * @return bool
     */
    public function changeUserByUid($uid,$state) {
        $map = array(
            'id' => $uid,
        );

        $data = array(
            'state' => $state,
            'update_time'   => $state
        );

        return $this->where($map)->save($data);
    }

    /**
     * @alias 添加用户
     * @param array $user   用户数组
     * @return mixed
     * @date    2017-02-28
     * @author  saive@cneli.com
     */
    public function addUser($user = array()){
        return $this->add($user);
    }

    /**
     * @alias   查询全部日志
     * @author  saive@cneli.com
     * @date    2017-03-10
     * @param array $params
     * @return mixed
     */
    public function findAllLog($params = array()) {
        $model = M('AdminLog');
        $query = array();
        if($params['uid']) {
            $query[] = "uid = {$params['uid']}";
        }

        $query = implode(' AND ',$query);
        $params['itemcount'] = $model->where($query)->count();
        $page = new Page($params['itemcount'],$params['pagesize']);
        $res['show'] = trim($page->show());
        $prex = C('DB_PREFIX');

        $res['items'] = $model
            ->field("au.username,au.name,{$prex}admin_log.*")
            ->join("{$prex}admin_user au ON au.id={$prex}admin_log.uid",'left')
            ->where($query)
            ->limit($page->firstRow.',',$page->listRows)
            ->order('id DESC')
            ->select();

        return $res;
    }



}