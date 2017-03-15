<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-15
 * Time: 下午5:45
 */

namespace Rbac\Controller;


use Rbac\Model\RbacModel;
use Think\Controller;

/**
 * Class GroupsController
 * @package Rbac\Controller
 * @alias 分组管理
 */
class GroupsController extends Controller
{
    /**
     * @alias 导航分组
     */
    public function index() {
        $model = new RbacModel();
        $items = $model->findAllGroups();
        $this->assign('itemsObj',json_encode($items));
        $this->display();
    }

    /**
     * @alias 修改导航分组
     */
    public function ajaxSaveGroups() {
        $items = I('items');
        $model = new RbacModel();
        $model->reAddAllGroups($items);
        session('menus',null);
        $this->ajaxReturn(array(
            'code' => 1,
            'data' => null,
            'msg'  => '分组信息保存成功~'
        ));
    }

}