<?php
/**
 * Created by PhpStorm.
 * User: saive
 * Date: 17-2-25
 * Time: 下午2:52
 */
/**
 * 解析注释信息
 * @param $doc
 * @return array
 */
function parseDoc($doc) {
    $par = '#(@.*?\r?\n)#';
    preg_match_all($par,$doc,$res);
    $varMap = array(
        'int','array','string','bool'
    );
    $docs = array();
    foreach($res[0] as $_res) {
        if(substr($_res,0,6) == '@alias') {
            $docs['alias'] = trim(substr($_res,6));
        } else if(substr($_res,0,5) == '@date'){
            $docs['date'] = trim(substr($_res,5));
        } else if(substr($_res,0,6) == '@param') {
            $_params = explode(' ',trim(substr($_res,6)));
            $_params = array_values(array_filter($_params));
            $__params = array(
                'type' => in_array(strtolower($_params[0]),$varMap) ? $_params[0] : '未声明',
            );

            if($__params['type'] != '未声明') {
                $__params['var'] = substr($_params[1],0,1) == '$' ? substr($_params[1],1) : null;
                $__params['desc'] = $_params[2];
            } else {
                $__params['var'] = substr($_params[0],0,1) == '$' ? substr($_params[0],1) : null;
                $__params['desc'] = $_params[1];
            }
            $docs['params'][] = $__params;
        } else if(substr($_res,0,7) == '@author') {
            $docs['author'] = trim(substr($_res,7));
        } else if(substr($_res,0,7) == '@return') {
            $docs['return'] = trim(substr($_res,7));
        } else if(substr($_res,0,7) == '@waring') {
            $docs['waring'] = trim(substr($_res,7));
        }
    }

    return $docs;
}

/**
 * @alias   添加日志
 * @author  saive@cneli.com
 * @date    2017-03-09
 * @param $content
 * @return mixed
 */
function addLog($content) {
    $_ucenter = session('ucenter');
    $log = array(
        'group' => MODULE_NAME,
        'controller' => CONTROLLER_NAME,
        'function' => ACTION_NAME,
        'desc' => $content,
        'uid' => $_ucenter['id'],
        'create_time' => time(),
        'ip' => get_client_ip(),
    );

    return M('AdminLog')->add($log);
}
