<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class course_controller {

	public function __construct() {
		
	}

	public function index_action() {
		global $_G;
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'code_learning')),
		);
		
		include template('aut:common/header');
		include template('aut:courselist');
		include template('aut:common/footer');
	}
	
	public function view_action() {
		global $_G;
		
		
		
		$op = getgpc('op');
		$scope = getgpc('scope');
		$opArr = array('knowledge', 'try', 'challenge', 'solution');
		if(empty($op) || !in_array($op, $opArr)) {
			$op = 'knowledge';
		}
		
		$cid = addslashes(getgpc('cid'));
		//challenge
		$challengeidArr = array();
		$query = DB::query("SELECT problem_id FROM `problem` WHERE category='$cid' AND ptype='challenge' ORDER BY problem_id ASC");
		while($value = DB::fetch($query)) {
			$challengeidArr[] = $value['problem_id'];
		}
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'code_learning'), 'href' => getStaticUrl(AUT_INDEX_PATH."&home={$_G['aut_controller']}")),
				array('text' => $_G['aut_cache']['category'][$cid]['name'], 'href' => AUT_INDEX_PATH."&home={$_G['aut_controller']}&act={$_G['aut_action']}&cid={$cid}&scope={$scope}"),
		);
		
		
		if($op == 'knowledge') {
			$_G['breadcrumb'][] = array('text' => lang('plugin/aut', 'knowledge_teach'));
			$course_content_url = AUT_INDEX_PATH."&home=knowledge&act=view&cid={$cid}&ajax=1&scope={$scope}";
		} elseif($op == 'try') {
			$_G['breadcrumb'][] = array('text' => lang('plugin/aut', 'try'));
			$course_content_url = AUT_INDEX_PATH."&home=try&act=view&cid={$cid}&ajax=1&scope={$scope}";
		} elseif($op == 'challenge') {
			$_G['breadcrumb'][] = array('text' => lang('plugin/aut', 'challenge_once'));
			$pid = addslashes(getgpc('pid'));
			
			$operation = getgpc('operation');
			$operationArr = array('new', 'edit');
			if(empty($operation) || !in_array($operation, $operationArr)) {
				$operation = 'new';
			}
			if($operation == 'edit') {
				$sid = addslashes(getgpc('sid'));
				
			}
			$course_content_url = AUT_INDEX_PATH."&home=problem&act=view&cid={$cid}&pid={$pid}&ajax=1&operation={$operation}&sid={$sid}&scope={$scope}&ajax=noheader";
		} elseif($op == 'solution') {
			$_G['breadcrumb'][] = array('text' => lang('plugin/aut', 'my_solution'));
			$pid = addslashes(getgpc('pid'));
			$course_content_url = AUT_INDEX_PATH."&home=solution&act=view&cid={$cid}&pid={$pid}&ajax=noheader&scope={$scope}";
		}
		//echo '<pre>';
		//print_r($_G['breadcrumb']);
		
		include template('aut:common/header');
		include template('aut:courseview');
		include template('aut:common/footer');
	}
}