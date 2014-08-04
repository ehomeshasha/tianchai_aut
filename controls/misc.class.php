<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class misc_controller {
	
	public function __construct() {
		
	}
	
	public function index_action() {
		
		
		
		
	}
	
	public function clear_session_action() {
		unset($_SESSION['aut_message']);
	}
}