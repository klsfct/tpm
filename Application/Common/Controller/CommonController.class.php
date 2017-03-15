<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-5
 * Time: 下午11:44
 */

namespace Common;
use Think\Controller;

class CommonController extends Controller
{

    public function _index() {
        echo 'I am common index .';
    }

}