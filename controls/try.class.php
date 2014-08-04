<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class try_controller {
	
	
	
	public function __construct() {
		
	}

	public function index_action() {
		
	}
	
	public function view_action() {
		require DISCUZ_ROOT."./source/function/function_discuzcode.php";
		global $_G;
		
		$cid = addslashes(getgpc('cid'));
		$problem = DB::fetch_first("SELECT * FROM `problem` WHERE category='$cid' AND ptype='try'");
		$problem['description'] = aut_decode(discuzcode($problem['description'], 0, 0));
		
		if(empty($_G['aut_ajax'])) include template("aut:common/header");
		include template("aut:try_view");
		if(empty($_G['aut_ajax'])) include template("aut:common/footer");
		
		
	}
}