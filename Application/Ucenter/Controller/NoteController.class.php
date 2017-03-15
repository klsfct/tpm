<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-7
 * Time: 上午1:29
 */

namespace Ucenter\Controller;


use Think\Controller;

/**
 * Class NoteController
 * @package Ucenter\Controller
 * @alias 便利贴
 */
class NoteController extends Controller
{
    /**
     * @alias 便利贴管理
     * @demo  http://test.com/ucenter/note/index.html
     */
    public function index() {


        $_tmpClassName = '\\Rbac\\Model\\RbacModel';
        $_tmpClass = new \ReflectionMethod($_tmpClassName, 'reAddAllGroups');
        $_doc = $_tmpClass->getDocComment();

        $docs = parseDoc($_doc);

        echo '<pre>';
        print_r($docs);
        echo '</pre>';
        exit;

        $this->display();
    }
}