<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function allow_admin() {
	global $_G;
	if(!in_array($_G['groupid'], $_G['aut_settings']['admingroup'])) {
		showresult(-1, lang('plugin/aut', 'only_admin_can_do_this_job'), $_G['aut_ajax']);
	}
}

function allow_log_in() {
	global $_G;
	if(!$_G['uid']) {
		header("Location: /member.php?mod=logging&action=login");
	}
}

function allow_operate($uid) {
	global $_G;
	if((!$_G['uid'] || $_G['uid'] != $uid) && !in_array($_G['groupid'], $_G['aut_settings']['admingroup'])) {
		return false;
	}
	return true;
}

function allow_self($uid, $ajax) {
	global $_G;
	if((!$_G['uid'] || $_G['uid'] != $uid) && !in_array($_G['groupid'], $_G['aut_settings']['admingroup'])) {
		showresult(-1, lang('plugin/aut', 'allow_self_todo_this_job'), $_G['aut_ajax']);
	}
}

function allow_pid($pid) {
	global $_G;
	if($pid < 20000 || in_array($_G['groupid'], $_G['aut_settings']['admingroup'])) return;
	$query = DB::query("SELECT pid_list FROM ".DB::table('aut_competition')." WHERE startdatetime<'$_G[timestamp]'");
	while($value = DB::fetch($query)) {
		if(in_array($pid, explode(",", $value['pid_list']))) return;
	}
	
	
	
	showresult(-1, lang('plugin/aut', 'this_problem_is_not_visible'), $_G['aut_ajax']);
}

?>