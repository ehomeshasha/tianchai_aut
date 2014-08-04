<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class index_controller {

	public function __construct() {
		
	}

	public function index_action() {
		global $_G;
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'space_center')),
		);
		
		include template('aut:common/header');
		include template('aut:index');
		include template('aut:common/footer');
	}
}