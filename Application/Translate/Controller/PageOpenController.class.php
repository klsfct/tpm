<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-3-22
 * Time: 下午4:45
 */

namespace Translate\Controller;


use Think\Controller;

/**
 * @alias   页面打开次数
 * Class PageOpenController
 * @package Translate\Controller
 */
class PageOpenController extends Controller
{
    /**
     * @alias   页面打开次数
     * @author  saive@cneli.com
     * @date    2017-03-14
     */
    public function index() {
        $this->display();
    }
}