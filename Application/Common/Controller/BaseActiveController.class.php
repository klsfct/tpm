<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-17
 * Time: 上午11:07
 * 公告与活动基类
 */

namespace Common\Controller;

use Active\Model\ActiveConfigModel;
use Think\Controller;

class BaseActiveController extends Controller
{
    /**
     * 分页数目
     * @var int
     */
    protected $pagesize = 20;

    /**
     * 删除活动
     */
    public function ajaxDeleteActive() {
        if(IS_AJAX && IS_POST) {
            $active_id = I('post.aid',0,'intval');
            $model = new ActiveConfigModel();
            $maxID = $model->findMaxActiveId();
            if($maxID == $active_id) {
                $this->ajaxReturn(array(
                    'code' => 50201,
                    'data' => null,
                    'msg'  => '该活动是最新的活动不能执行删除操作~',
                ));
            } else if($model->deleteActiveByActiveId($active_id)) {
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => null,
                    'msg'  => '删除成功~',
                ));
            } else {
                $this->ajaxReturn(array(
                    'code' => 50200,
                    'data' => null,
                    'msg'  => '活动删除失败~',
                ));
            }
        }
    }

    /**
     * 复制活动
     */
    public function ajaxCopyActive() {
        if(IS_AJAX && IS_POST) {
            $active_id = I('post.aid',0,'intval');
            $model = new ActiveConfigModel();
            $items = $model->findAllActiveByActiveId($active_id);
            $maxAID = $model->findMaxActiveId() + 1;
            foreach($items as &$item) {
                $item['active_id'] = $maxAID;
                $item['status'] = 0;
                unset($item['auto_id']);
            }
            if($model->addActiveBatch($items)) {
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => null,
                    'msg'  => '活动复制成功~',
                ));
            } else {
                $this->ajaxReturn(array(
                    'code' => 50200,
                    'data' => null,
                    'msg'  => '活动复制失败~',
                ));
            }
        }
    }

    /**
     * 修改活动状态
     */
    public function ajaxChangeSource() {
        if(IS_AJAX && IS_POST) {
            $active_id = I('post.aid',0,'intval');
            $status = I('post.status',0,'intval');
            $model = new ActiveConfigModel();
            if($model->saveActiveStatusByActiveId($active_id,$status)) {
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => null,
                    'msg'  => '状态修改成功~'
                ));
            } else {
                $this->ajaxReturn(array(
                    'code' => 50200,
                    'data' => null,
                    'msg'  => '状态修改失败~',
                ));
            }
        }
    }

    /**
     * 获取活动信息
     * {"text":"这是按钮名","page":"1","data":"这个是按钮参数","url":"这个是按钮地址","showtime":""}
     */
    public function ajaxFindActive()
    {
        if (IS_AJAX && IS_POST) {
            $active_id = I('post.aid', 0, 'intval');
            $model = new ActiveConfigModel();
            $items = $model->findAllActiveByActiveId($active_id);
            foreach($items as $key=>$item) {
                if($item['button'] != '') {
                    $item['_buttons'] = json_decode($item['button'],1);
                } else {
                    $item['_buttons'] = array(
                        'text' => '',
                        'page' => 1,
                        'data' => '',
                        'url'  => '',
                        'showtime' => 0
                    );
                }
                $items[$key] = $item;
            }

            if (empty($items)) {
                $this->ajaxReturn(array(
                    'code' => 50200,
                    'data' => null,
                    'msg' => '活动不存在~',
                ));
            } else {
                $res = array(
                    'common' => array(),
                    'items' => array(),
                    'other' => array(),
                );
                if (count($items) > 1) { //批量记录活动
                    foreach ($items as $item) {
                        if ($item['is_reward'] == 1) {
                            $res['items'][] = $item;
                        } else {
                            $res['common'] = $item;
                        }
                    }
                } else {    //单个记录
                    $res['common'] = $items[0];
                }
                $this->ajaxReturn(array(
                    'code' => 2000,
                    'data' => $res,
                    'msg' => '查找活动成功~',
                ));
            }
        } else {
            if (!IS_AJAX) {
                echo '请求方式异常~';
            } else {
                $this->ajaxReturn(array(
                    'code' => 50100,
                    'data' => null,
                    'msg' => '请求方式错误～',
                ));
            }
        }
    }

    /**
     * 复制活动
     * @param $activeId
     */
    protected function _copyActive($activeId)
    {

    }

    /**
     * 删除活动
     * @param $activeId
     */
    protected function _delActive($activeId)
    {

    }

    /**
     * 查找活动
     * @param $activeId
     */
    protected function _findActive($activeId)
    {
        $model = new ActiveConfigModel();

        $active = $model->find();

    }

    /**
     * 查询活动
     * @param $activeType
     * @return mixed
     */
    protected function _findAllActiveByActiveType($activeType)
    {
        $params = array(
            'status' => I('get.status',999),
            'begin_time' => I('get.begin_time',''),
            'end_time' => I('end_time',''),
            'page' => I('page',1),
            'pagesize' => $this->pagesize,
            'itemcount' => 0,
            'pagecount' => 0,
        );
        $model = new ActiveConfigModel();
        $res = $model->findAllActiveByActiveType($activeType,$params);
        $this->assign('itemsObj', json_encode($res['items']));
        $this->assign('items', $res['items']);
        $this->assign('status',$params['status']);
        $this->assign('begin_time',$params['begin_time']);
        $this->assign('end_time',$params['end_time']);
        $this->assign('jumpMap', array());
        $this->assign('active',json_encode(array()));
        $this->assign('page',$res['show']);
    }

}