<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class category_controller {

	public function __construct() {
		allow_admin();
	}

	public function index_action() {
		global $_G;
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('category_list', 0, AUT_ADMIN_TEMPLATE_DIR);
		include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);

	}
	
	public function updatestatus_action() {
		$cid = addslashes(getgpc('cid'));
		$status = addslashes(getgpc('status'));
		DB::query("UPDATE ".DB::table('aut_category')." SET status=$status WHERE cid=$cid");
		$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'update_category_status_success')));
	}
	
	public function post_action() {
		global $_G;
		$op = getgpc('op');
		$opArr = array('new', 'edit');
		if(empty($op) || !in_array($op, $opArr)) {
			$op = 'new';
		}
		
		$displayorder = 0;
		if(!submitcheck('submit')) {
			$status = 1;
			if($op == 'edit') {
				$cid = addslashes(getgpc('cid'));
				$category = DB::fetch_first("SELECT * FROM ".DB::table('aut_category')." WHERE cid=$cid");
				$status = $category['status'];
				$displayorder = $category['displayorder'];
			}
			
			$category_html = init_category($_G['aut_cache']['categorytree_merge'], $category[fid], 0, 0);
			
			
			
			
			
			include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			include template('category_post', 0, AUT_ADMIN_TEMPLATE_DIR);
			include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			
		} else {
			
			chkLength(lang('plugin/aut', 'category_name'), trim(getgpc('name')), 0, 100);
			chkDigit(lang('plugin/aut', 'vieworder'), trim(getgpc('displayorder')), 0, 3);
			validation_start();
			
			$name = addslashes(trim(getgpc('name')));
			$description = addslashes(trim(getgpc('description')));
			$fid = addslashes(getgpc('fid'));
			$displayorder = addslashes(trim(getgpc('displayorder')));
			$ctype = addslashes(getgpc('ctype'));
			$http_referer = getgpc('http_referer');
			
			if($op == 'new') {
				
				DB::query("INSERT INTO ".DB::table('aut_category')." (`name`,`description`,`fid`,`status`,`displayorder`,`ctype`) VALUES ('$name','$description','$fid',1,'$displayorder','$ctype')");
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'addcategory_success')));
				
			} elseif($op == 'edit') {
				$cid = addslashes(getgpc('cid'));
				$status = addslashes(getgpc('status'));
				
				DB::query("UPDATE ".DB::table('aut_category')." SET `name`='$name',`description`='$description',`fid`='$fid',`status`='$status',`displayorder`='$displayorder',`ctype`='$ctype' WHERE cid=$cid");
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'editcategory_success')));
			}
			
			unlink(DISCUZ_ROOT."data/sysdata/cache_category.php");
			unlink(DISCUZ_ROOT."data/sysdata/cache_categorytree.php");
			
			jumpto_list_page($http_referer);
		}
	}

	public function delete_action() {
		global $_G;
		$cid = addslashes(getgpc('cid'));
		delete_category($cid);
		
		$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'deletecategory_success')));
		
		unlink(DISCUZ_ROOT."data/sysdata/cache_category.php");
		unlink(DISCUZ_ROOT."data/sysdata/cache_categorytree.php");
	}
}

function delete_category($cid) {
	DB::query("DELETE FROM ".DB::table('aut_category')." WHERE cid=$cid");
	$query = DB::query("SELECT cid FROM ".DB::table('aut_category')." WHERE fid='$cid'");
	while($value = DB::fetch($query)) {
		delete_category($value['cid']);
	}
}
?>