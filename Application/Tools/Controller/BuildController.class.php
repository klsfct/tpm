<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-3-13
 * Time: 下午2:22
 */

namespace Tools\Controller;


use Think\Controller;

class BuildController extends Controller
{

    public function index() {

        $groups = array();

        $gname = I('get.gname','','trim');
        if(!$gname) {
            $dirs = scandir(APP_PATH);
            foreach($dirs as $dir) {
                $_dir = APP_PATH . $dir;
                if(is_dir($_dir) && !in_array($dir,array('Common','Runtime','.','..'))) {
                    $groups[] = $dir;
                }
            }
        } else {

        }

//        echo '<pre>';
//        print_r($groups);
//        echo '</pre>';
        $this->assign('groups',$groups);

        $this->display('index');
    }

    /**
     * @alias   创建分组
     * @author  saive@cneli.com
     * @date    2017-03-13
     * demo ： http://test.com/Tools/Build/ajaxGroup?title=Demo
     */
    public function ajaxGroup() {
        $params = array(
            'dirname' => I('post.dirname','Demo','trim'),
            'desc'  => I('post.desc','','trim'),
        );
        if(!$params['dirname']) {
            $this->ajaxReturn(array(
                'code' => 50100,
                'data' => array(),
                'msg'  => '文件夹名称不能为空～',
            ));
        } else if(is_dir(APP_PATH . $params['dirname'])) {
            $this->ajaxReturn(array(
                'code' => 50101,
                'data' => array(),
                'msg'  => '分组已经存在~',
            ));
        } else {
            $dirs = array(
                'groupDir' => APP_PATH . $params['dirname'],
                'commonDir' => APP_PATH . $params['dirname'] . '/Common',
                'confDir' => APP_PATH . $params['dirname'] . '/Conf',
                'controllerDir' => APP_PATH . $params['dirname'] . '/Controller',
                'modelDir' => APP_PATH . $params['dirname'] . '/Model',
                'viewDir' => APP_PATH . $params['dirname'] . '/View',
            );

            foreach($dirs as $dir) {
                mkdir($dir);
                touch($dir.'/index.html');
            }

            $this->ajaxReturn(array(
                'code' => 2000,
                'data' => null,
                'msg'  => '创建分组',
            ));
        }
    }

    /**
     * @alias
     * @author  saive@cneli.com
     * @date
     * @demo http://test.com/Tools/Build/ajaxController?title=Demo
     */
    public function ajaxController() {
        $controller = I('get.controller','Index','trim') . 'Controller';

        $group = I('get.group','Demo','trim');
        $functions = array(
            'index','ajaxDemo'
        );

        $descs = array(
            '默认活动',
            '添加模块'
        );
        $_controller = substr($controller,0,-10);
        mkdir(APP_PATH . $group . "/View/{$_controller}");
        $content = "<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-3-13
 * Time: 下午2:22
 */

namespace {$group}\\Controller;
use Think\\Controller;    
class {$controller} extends Controller {
        ";
        $date = date('Y-m-d');
        foreach($functions as $key=>$function) {
            if(substr($function,0,4) != 'ajax') {
                touch(APP_PATH . $group . "/View/{$_controller}/{$function}.html");
                $content .= "
    /**
     * @alias {$descs[$key]}
     * @author  系统构建
     * @date {$date}
     */
    public function {$function}() {
        \$this->display('{$function}');
    }
                ";
            } else {
                $content .= "
    /**
     * @alias {$descs[$key]}
     * @author  系统构建
     * @date {$date}
     */
    public function {$function}() {
        \$this->ajaxReturn(
            array(
                'code' => 2000,
                'data' => null,
                'msg'  => '这个是案例～',
            )
        );
    }
                ";
            }

        }
    $content .= "
}
?>
    ";



        file_put_contents(APP_PATH . $group . "/Controller/{$controller}.class.php" ,$content );

        echo $content;
    }
}