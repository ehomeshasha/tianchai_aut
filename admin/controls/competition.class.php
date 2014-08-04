<?php

class competition_controller {
	
	private $problem_total_page;
	private $problem_perpage;
	
	public function index_action() {
		global $_G;
		include_once AUT_PATH."/inc/paginator.class.php";
		$where = "1=1";
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
			$competitionlist[] = $value; 
		}
		
		
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('competition_list', 0, AUT_ADMIN_TEMPLATE_DIR);
		include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
	}
	public function post_action() {
		global $_G;
		require DISCUZ_ROOT."./source/language/lang_admincp.php";
		$op = getgpc('op');
		$opArr = array('new', 'edit');
		if(empty($op) || !in_array($op, $opArr)) {
			$op = 'new';
		}
		
		if(!submitcheck('submit')) {
			
			if($op == 'edit') {
				$id = addslashes(getgpc('competition_id'));
				$competition = DB::fetch_first("SELECT * FROM ".DB::table('aut_competition')." WHERE id=$id");
				$competition['startdatetime'] = dgmdate($competition['startdatetime'], "Y-m-d H:i");
				$competition['enddatetime'] = dgmdate($competition['enddatetime'], "Y-m-d H:i");
				$problems = get_problems_from_pidlist($competition['pid_list']);
				//print_r($problems);
			}
			
			include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			include template('competition_post', 0, AUT_ADMIN_TEMPLATE_DIR);
			include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);	
		} else {

			$datetime_regex = "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/";
			chkLength(lang('plugin/aut', 'competition_title'), trim(getgpc('competition_title')), 0, 255);
			chkRegEx(lang('plugin/aut', 'startdatetime'), trim(getgpc('startdatetime')), $datetime_regex);
			chkRegEx(lang('plugin/aut', 'enddatetime'), trim(getgpc('enddatetime')), $datetime_regex);
			chkLength(lang('plugin/aut', 'competition_short_description'), trim(getgpc('short_description')), 0, 255);
			chkEmpty(lang('plugin/aut', 'competition_description'), trim(getgpc('description')));
			chkEmpty(lang('plugin/aut', 'competition_problem'), trim(getgpc('pid_list')));
			validation_start();
			
			
			$title = addslashes(trim(getgpc('competition_title')));
			$pid_list = addslashes(trim(getgpc('pid_list')));
			$short_description = addslashes(trim(getgpc('short_description')));
			$description = addslashes(trim(getgpc('description')));
			$startdatetime = strtotime(trim(addslashes(getgpc('startdatetime'))));
			$enddatetime = strtotime(trim(addslashes(getgpc('enddatetime'))));
			$displayorder = addslashes(getgpc('displayorder'));
			$http_referer = getgpc('http_referer');
			
			$data = array(
				'title' => $title,
				'pid_list' => $pid_list,
				'short_description' => $short_description,
				'description' => $description,
				'startdatetime' => $startdatetime,
				'enddatetime' => $enddatetime,
				'displayorder' => $displayorder,
			);
			
			if($op == 'new') {
				$data['dateline'] = $_G['timestamp'];
				DB::insert('aut_competition', $data);
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'add_competition_success')));
			} elseif($op == 'edit') {
				$id = addslashes(getgpc('competition_id'));
				DB::update('aut_competition', $data, "id='$id'");
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'update_competition_success')));
			}
			
			jumpto_list_page($http_referer);
		}
	}
	
	
	function get_problemlist_action($op) {
		include_once AUT_PATH."/inc/paginator.class.php";
		global $_G;
		
		$start_num = intval(addslashes(getgpc('start_num')));
		$end_num = intval(addslashes(getgpc('end_num')));
		$ptype = addslashes(getgpc('module'));
		$title = addslashes(getgpc('title'));
		
		$where = " problem_id>=20000";
		//if($op == "new") $where .= " AND displayorder=0";
		$where .= empty($start_num) ? "" : " AND problem_id>=".$start_num;
		$where .= empty($end_num) ? "" : " AND problem_id<=".$end_num;
		$where .= empty($ptype) ? "" : " AND ptype='".$ptype."'";
		$where .= empty($title) ? "" : " AND title LIKE '%".$title."%'";
		
		
		$rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `problem` WHERE {$where}");
		//$perpage = 15;
		$paginator = new paginator($rs['count'], "", true);
		$this->problem_perpage = $paginator->get_perpage();
		$limit = $paginator->get_limit();
		$multi = $paginator->get_multi();
		$this->problem_total_page = $paginator->get_total_page();
				
		$query = DB::query("SELECT problem_id,title,ptype FROM `problem` WHERE {$where} ORDER BY problem_id ASC $limit");
		$problemlist = array();
		while($value = DB::fetch($query)) {
			//if(in_array($value['problem_id'], $pidArr)) continue;
			$value['module'] = $_G['ArrayData']['module_map'][$value['ptype']];
			$problemlist[] = $value; 
		}
		
		include template('problem_list_ajax', 0, AUT_ADMIN_TEMPLATE_DIR);
		
	}
	
	
	public function delete_action() {
		global $_G;
		$id = addslashes(getgpc('competition_id'));
		$competition = DB::fetch_first("SELECT title FROM ".DB::table('aut_competition')." WHERE id=$id");
		DB::query("DELETE FROM ".DB::table('aut_competition')." WHERE id=$id");
		$_SESSION['aut_message'] = array('code' => 1, 'content' => array(sprintf(lang('plugin/aut', 'deletecompetition_p'), $competition['title'])));
	}
	
	public function publish_result_action() {
		global $_G;
		$id = addslashes(getgpc('competition_id'));
		DB::query("UPDATE ".DB::table('aut_competition')." SET status=1 WHERE id=$id");
		$competition = DB::fetch_first("SELECT pid_list FROM ".DB::table('aut_competition')." WHERE id=$id");
		$id_str = get_id_str($competition['pid_list']);
		DB::query("UPDATE `problem` SET displayorder=1 WHERE problem_id IN ($id_str)");
	}
}

?>