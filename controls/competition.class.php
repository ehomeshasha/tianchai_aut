<?php

class competition_controller {
	
	public function index_action() {
		global $_G;
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'competition')),
		);
		
		include_once AUT_PATH."/inc/paginator.class.php";
		$where = "displayorder>=0";
		$count = getcount('aut_competition', $where);
		
		$paginator = new paginator($count, "", true);
		$perpage = $paginator->get_perpage();
		$limit = $paginator->get_limit();
		$multi = $paginator->get_multi();
				
		$query = DB::query("SELECT * FROM ".DB::table('aut_competition')." WHERE {$where} ORDER BY dateline DESC $limit");
		$competitionlist = array();
		while($value = DB::fetch($query)) {
			$value['problem_count'] = count(explode(",", $value['pid_list']));
			if($value['displayorder'] == "-1") {
				$value['display_text'] = lang("plugin/aut", "no");	
			} elseif($value['displayorder'] == "0") {
				$value['display_text'] = lang("plugin/aut", "yes");
			}
			 
			$value['status_word'] = get_competition_status_word($value['startdatetime'], $value['enddatetime'], $_G['timestamp']);
			$value['startdatetime'] = dgmdate($value['startdatetime'], "Y-m-d H:i");
			$value['enddatetime'] = dgmdate($value['enddatetime'], "Y-m-d H:i");
			$solution_problem_ids = get_solution_problem_ids_from_pidlist($value['pid_list']);
			$value['ispost'] = empty($solution_problem_ids) ? 0 : 1;
			$competitionlist[] = $value; 
		}
		
		include template('aut:common/header');
		include template('aut:competition_list');
		include template('aut:common/footer');
	}
	
	
	
	public function view_action() {
		global $_G;
		
		$id = addslashes(getgpc('competition_id'));
		$competition = DB::fetch_first("SELECT * FROM ".DB::table('aut_competition')." WHERE id=$id AND displayorder>=0");
		
		jump_if_empty($competition);
		
		$competition_status = get_competition_status($competition['startdatetime'], $competition['enddatetime'], $_G['timestamp']);
		if($competition_status == -2 || $competition_status == -3) {
			showresult("-1", lang('plugin/aut', 'competition_not_start_yet'));
		}
		
		$competition['short_description'] = linefeed2br($competition['short_description']);
		$competition['description'] = linefeed2br($competition['description']);
		$competition['startdatetime'] = dgmdate($competition['startdatetime'], "Y-m-d H:i");
		$competition['enddatetime'] = dgmdate($competition['enddatetime'], "Y-m-d H:i");
		
		$problems = get_problems_from_pidlist($competition['pid_list']);
		$solution_problem_ids = get_solution_problem_ids_from_pidlist($competition['pid_list']);
		//print_r($solution_problem_ids);
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'competition'), 'href' => AUT_INDEX_PATH."&home=competition"),
				array('text' => htmlspecialchars($competition['title'])),
		);
		
		include template('aut:common/header');
		include template('aut:competition_view');
		include template('aut:common/footer');
		
	}
	
	public function view_result_action() {
		global $_G;
		
		$id = addslashes(getgpc('competition_id'));
		$competition = DB::fetch_first("SELECT * FROM ".DB::table('aut_competition')." WHERE id=$id AND displayorder>=0");
		
		jump_if_empty($competition);
		
		
		$competition['startdatetime_text'] = dgmdate($competition['startdatetime'], "Y-m-d H:i");
		$competition['enddatetime_text'] = dgmdate($competition['enddatetime'], "Y-m-d H:i");
		$competition_status_word = get_competition_status_word($competition['startdatetime'], $competition['enddatetime'], $_G['timestamp'], true);
		
		$op = getgpc('op');
		$opArr = array('all', 'my', 'others');
		if(empty($op) || !in_array($op, $opArr)) {
			$op = 'all';
		}
		
		
		include_once AUT_PATH."/inc/paginator.class.php";
	
		$problemlist = get_problems_from_pidlist($competition['pid_list']);
		
		$total_score = 0;
		$n = 0;
		foreach($problemlist as $p) {
			$p['ac_count'] = get_problem_count($p['problem_id'], $_G['uid'], 'ac');
			$p['submit_count'] = get_problem_count($p['problem_id'], $_G['uid'], 'submit');
			$p['final_score'] = get_final_score($p['problem_id'], $_G['uid']);
			$total_score += $p['final_score'];
			$problems[] = $p;
			$n++;
		}
		$score = $total_score; 
		
		//if competition is end
		if($competition['status'] == 1) {
			//get user score 
			$users_score = get_users_score_from_pidlist($competition['pid_list'], $competition['startdatetime'], $competition['enddatetime']);
			
			//get solutions
			$pid_str = get_id_str($competition['pid_list']);
			$where = "a.problem_id IN ($pid_str)";
			if($op == 'all') {
				$where .= " ";
			} elseif($op == 'my') {
				$where .= " AND a.user_id='$_G[uid]'";
			} elseif($op == 'my') {
				$where .= " AND a.user_id!='$_G[uid]'";
			}
			
			$rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `solution` AS a WHERE {$where}");
			$url = preg_replace("/[&\?]page=[\d]*/", "", $_SERVER['REQUEST_URI']);
			$paginator = new paginator($rs['count'], $url);
			$perpage = $paginator->get_perpage();
			$limit = $paginator->get_limit();
			$multi = $paginator->get_multi();
			
			$query = DB::query("SELECT a.*,b.title FROM `solution` AS a LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id 
			 WHERE {$where} ORDER BY a.solution_id DESC $limit");
			while($value = DB::fetch($query)) {
				$value['result'] = getresultArr($value['result'], $value['solution_id']);
				$solutions[] = $value;
			}
		}

		
		
		
		
		include template('aut:common/header');
		include template('aut:competition_view_result');
		include template('aut:common/footer');
	}
	
}

?>