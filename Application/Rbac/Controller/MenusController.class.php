<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-15
 * Time: 下午5:46
 */

namespace Rbac\Controller;


use Rbac\Model\RbacModel;
use Think\Controller;

/**
 * Class MenusController
 * @package Rbac\Controller
 * @alias 菜单管理
 */
class MenusController extends Controller
{
    /**
     * @alias 导航列表
     */
    public function index() {
        $menus = array();
        foreach($this->menus as $menu) {
            $menus = array_merge($menus,$menu);
        }
        $this->assign('menusObj',json_encode($menus));
        $this->display();
    }

    /**
     * @alias 修改导航
     */
    public function ajaxSaveMenus() {
        $items = I('items','post');
        $menus = array();
        foreach($items as $item) {
            $menus[] = array(
                'gid' => $item['gid'],
                'alias' => $item['alias'],
                'title' => $item['subtitle'],
                'href' => $item['href'],
                'sort' => $item['sort'],
            );
        }

        $model = new RbacModel();
        $model->reAddAllMenus($menus);
        session('menus',null);
        $this->ajaxReturn(array(
            'code' => 2000,
            'data' => null,
            'msg'  => '导航保存成功~',
        ));
    }
}