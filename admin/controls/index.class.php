<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class index_controller {

	public function __construct() {
		
	}

	public function index_action() {
		global $_G;
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('index', 0, AUT_ADMIN_TEMPLATE_DIR);
		include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
	}
}