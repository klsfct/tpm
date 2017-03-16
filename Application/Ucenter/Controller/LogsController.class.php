<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-7
 * Time: 上午1:29
 */

namespace Ucenter\Controller;


use Think\Controller;
use Ucenter\Model\UcenterModel;

/**
 * Class LogsController
 * @package Ucenter\Controller
 * @alias 日志
 */
class LogsController extends Controller
{
    /**
     * @alias 日志列表
     */
    public function index() {

        $model = new UcenterModel();
        $params = array(
            'pagesize' => $this->pagesize,
        );
        $logs = $model->findAllLog($params);
        $this->assign('logs',$logs);

        $this->display();
    }
}