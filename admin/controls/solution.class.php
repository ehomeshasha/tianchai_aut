<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class solution_controller {
	
	
	
	public function __construct() {
		allow_log_in();
	}

	public function index_action() {
		
		global $_G;
		
		include_once AUT_PATH."/inc/paginator.class.php";
		
		$solutionlist = array();
		
		
		$date = initdate();
		$username = addslashes(getgpc('username'));
		$result = addslashes(getgpc('result'));
		$language = addslashes(getgpc('language'));
		$displayorder = addslashes(getgpc('displayorder'));
		$problem_title = addslashes(getgpc('problem_title'));
		$view = addslashes(getgpc('view')); 
		
		$filter_type = getgpc('filter_type');
		$filter_type_arr = array('competition', 'tiku', 'challenge');
		if(empty($filter_type) || !in_array($filter_type, $filter_type_arr)) {
			$filter_type = '';
		}
		
		$where .= "a.displayorder>=0";
		$where .= in_array($_G['groupid'], $_G['aut_settings']['admingroup']) && $view != "me" ? "" : " AND a.user_id='{$_G[uid]}'";
		$where .= " AND a.dateline>'{$date['starttime']}' AND a.dateline<'{$date['endtime']}'";
		$where .= empty($username) ? "" : " AND a.user_name='$username'";
		$where .= "$result" === "" ? "" : " AND a.result='$result'";
		$where .= "$language" === "" ? "" : " AND a.language='$language'";
		$where .= empty($displayorder) ? "" : " AND a.displayorder='$displayorder'";
		$where .= empty($problem_title) ? "" : " AND b.title LIKE '%$problem_title%'";
		
		if($filter_type == 'competition') {
			$where .= " AND a.problem_id>=20000";
		} elseif($filter_type == 'tiku') {
			$where .= " AND a.problem_id<20000 AND b.ptype='tiku'";
		} elseif($filter_type == 'challenge') {
			$where .= " AND a.problem_id<20000 AND b.ptype='challenge'";
		}
		//echo $where;
		
		$count_rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `solution` AS a 
			LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id WHERE {$where}");
		$paginator = new paginator($count_rs['count'], "", true);
		$perpage = $paginator->get_perpage();
		$limit = $paginator->get_limit();
		$multi = $paginator->get_multi();
		
		$query = DB::query("SELECT a.*,b.title 
				FROM `solution` AS a LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id 
				WHERE {$where} ORDER BY a.solution_id DESC $limit");
		while($value = DB::fetch($query)) {
			$value['result'] = getresultArr($value['result'], $value['solution_id']);
			$solutionlist[] = $value;
		}
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('solution_list', 0, AUT_ADMIN_TEMPLATE_DIR);
		include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		
		
	}
	
	
	public function detail_action() {
		global $_G;
		
		$sid = addslashes(getgpc('sid'));
		$where = "a.solution_id='$sid'";
		
		$solution = DB::fetch_first("SELECT a.*,b.title,b.spj,c.source FROM `solution` AS a 
			LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id 
			LEFT JOIN `source_code` AS c ON a.solution_id=c.solution_id 
			WHERE {$where} ORDER BY a.solution_id DESC");
		
		jump_if_empty($solution);
		
		$solution['result'] = getresultArr($solution['result'], $solution['solution_id']);
		$solution['source'] = htmlspecialchars($solution['source']);
		
		$solution['testing_point'] = get_testing_point($solution['flag_list'], $solution['score_list'], $solution['usedtime_list']);
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template("aut:solution_detail");
		include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		
		
		
	}

	public function viewerror_action() {
		global $_G;
		$sid = addslashes(getgpc('sid'));
		$rs = DB::fetch_first("SELECT user_id FROM `solution` WHERE solution_id=$sid");
		allow_self($rs['user_id']);
		
		$type = addslashes(getgpc('type'));
		$result = addslashes(getgpc('result'));
		
		$error = get_result_detail($sid, $type);
		
		include template("aut:common/header");
		include template("aut:error_info");
		include template("aut:common/footer");
	}
	
	public function rejudge_action() {
		global $_G;
		$sid = addslashes(getgpc('sid'));
		$rs = DB::fetch_first("SELECT result FROM `solution` WHERE solution_id=$sid");
		if($rs['result'] < 4 || $rs['result'] > 11) {
			exit(lang('plugin/aut', 'error_message').": ".lang('plugin/aut', 'program_is_already_runing_cannot_rejudge'));
		}
		DB::query("UPDATE `solution` SET result=0 WHERE solution_id='$sid'");
	}
}
?>