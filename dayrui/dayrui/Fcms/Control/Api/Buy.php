<?php namespace Phpcmf\Control\Api;
/**
 * https://www.junke158.cn
 * 君科云CMS
 * 本文件是框架系统文件，二次开发时不可以修改本文件
 **/

$file = dr_get_app_dir('pay').'Controllers/Buy.php';
if (is_file($file)) {
    require_once $file;
} else {
    \dr_exit_msg(0, '没有安装「支付系统」插件');
}

// 交易下单
class Buy extends \Phpcmf\Controllers\Buy {


}