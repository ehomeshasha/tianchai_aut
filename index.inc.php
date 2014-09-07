<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
session_start();

define("AUT_PATH", 'source/plugin/aut');
define("AUT_ADMIN_PATH", 'source/plugin/aut/admin');
define("AUT_ADMIN_TEMPLATE_DIR", './source/plugin/aut/admin/template');
define("AUT_ADMIN_COMMON_TEMPLATE_DIR", './source/plugin/aut/admin/template/common');
define("AUT_INDEX_PATH", 'plugin.php?id=aut:index');
define("AUT_CHARSET", "GB2312");

require_once DISCUZ_ROOT."source/function/function_cache.php";

require_once AUT_PATH."/config/config_global.php";
require_once  AUT_PATH."/function/function_core.php";
require_once  AUT_PATH."/data/ArrayData.php";
require_once AUT_PATH."/class/mysql.class.php";

include AUT_PATH."/inc/common.inc.php";


$_G['aut_message'] = initmessage();
$_G['ArrayData'] = $ArrayData;

$home = getgpc('home');
$act  = getgpc('act');
$admin = getgpc('admin');
$_G['space'] = getgpc('space');
if($_G['space'] == 1) $admin=2;


if($admin != 1 && $admin != 2) {
	$folder = "";	
} elseif($admin == 1) {
	allow_admin();
	$folder = "/admin";
	$_G['in_admin'] = 1;
} elseif($admin == 2) {
	allow_log_in();
	$folder = "/space";
	$_G['in_space'] = 1;
}



$controller = empty($home) ? 'index' : $home;
$action		= empty($act) ? 'index' : $act;

if(!is_file(AUT_PATH.$folder.'/controls/'.$controller.'.class.php')) {
	$controller='index';
	$action='index';
}

$_G['aut_controller'] = $controller;
$_G['aut_action'] = $action;
$_G['OJ_DATA'] = "/home/judge/data";
$_G['aut_ajax'] = getgpc('ajax');
$_G['validcate'] = getValidCategory();


if( !(in_array($_G['groupid'], $_G['aut_settings']['admingroup']) || $_G['groupid']==22 || $_G['groupid']==21 || $_G['groupid']==23) ) {
	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=gbk" /><title>�����</title><meta name="keywords" content="�����,C���Կγ�,�ƶ�����,��ѱ����Ƶ��php�����̳�,webǰ�˽̳�,���߱��ѧϰ,html5��Ƶ�̳�,css+div�̳�,ios������ѵ,��׿�����̳�,onlinejudge,noi,noip,acm,��Ϣѧ��̾���,����C,�����㷨" /><meta name="description" content="���������̴����￪ʼ" /></head><body>';

	echo '<head><div style="line-height:20px;text-align:center;margin-top:300px;"><h1>��������վδ�����ܻ��ţX</h1><br/><a href="http://www.tianchai.org/member.php?mod=logging&action=login" style="text-decoration:none; color:#1abc9c;">Alpha User Login</a></div>';

	echo '<body></html>';
	exit;
}
require_once AUT_PATH.$folder.'/controls/'.$controller.'.class.php';	


$conclass = $controller.'_controller';
$actfunc  = $action.'_action';
$views    = new $conclass();

$views->$actfunc();