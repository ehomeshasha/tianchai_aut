<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class solution_controller {
	
	private $solution_where = " AND (a.problem_id<20000 OR b.displayorder=1)";
	
	public function __construct() {
		allow_log_in();
	}
	
	public function index_action() {
		
		global $_G;
		
		include_once AUT_PATH."/inc/paginator.class.php";
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'solution')),
		);
		
		$solutionlist = array();
		
		$date = initdate();
		$username = addslashes(trim(getgpc('username')));
		$result = addslashes(trim(getgpc('result')));
		$language = addslashes(trim(getgpc('language')));
		$displayorder = addslashes(trim(getgpc('displayorder')));
		$problem_title = addslashes(trim(getgpc('problem_title')));
		$view = addslashes(trim(getgpc('view'))); 
		
		$filter_type = getgpc('filter_type');
		$filter_type_arr = array('competition', 'tiku', 'challenge');
		if(empty($filter_type) || !in_array($filter_type, $filter_type_arr)) {
			$filter_type = '';
		}
		
		
		$where .= "a.displayorder>=0".$this->solution_where;
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
		$paginator = new paginator($count_rs['count']);
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
		
		include template("aut:common/header");
		include template("aut:solution_list");
		include template("aut:common/footer");
		
		
	}
	
	
	public function try_post_action() {
		
		global $_G;
		$pid = addslashes(getgpc('pid'));
		$language=addslashes(getgpc('language'));
			
		$source=$_POST['source'];
		$source=addslashes($source);
		
		$len=strlen($source);
		
		$ip=$_G['client_ip'];
		
		if ($len<2){
			showresult(-1, 'Error: Code too short', $_G['aut_ajax']);
		}
		if ($len>65536){
			showresult(-1, 'Error: Code too long', $_G['aut_ajax']);
		}
		
		// last submit
		$now=strftime("%Y-%m-%d %X",time()-10);
		$sql="SELECT `in_date` from `solution` where `user_id`='{$_G[uid]}' and in_date>'$now' order by `in_date` desc limit 1";
		$res=mysql_query($sql);
		if (mysql_num_rows($res)==1){
			showresult(-1, lang('plugin/aut', 'submit_interval_too_short'), $_G['aut_ajax']);
		}
		

		$curtime = $_G['timestamp'];
		$curdate = dgmdate($curtime, "Y-m-d H:i:s");
		
		$data = array(
				'problem_id' => $pid,
				'user_id' => $_G['uid'],
				'user_name' => $_G['username'],
				'in_date' => $curdate,
				'language' => $language,
				'ip' => $_G['clientip'],
				'code_length' => $len,
				'dateline' => $curtime,
				'result' => 0,
				'ptype' => 'try',
		);
		
		
		
		
			
		$insertsql = get_insertsql('solution', $data);
		$sid = DB::query($insertsql);
		$insert_id = mysql_insert_id();
		DB::query("INSERT INTO `source_code` (`solution_id`, `source`) VALUES ('$sid', '$source')");
		
		exit(json_encode(array('code' => 1, 'content' => $insert_id)));
	}
	
	public function getresult_action() {
		global $_G;
		
		$sid = addslashes(getgpc('sid'));
		$solution = DB::fetch_first("SELECT user_id,result FROM `solution` WHERE solution_id='$sid'");
		allow_self($solution['user_id']);
		$result = $solution['result'];
	
		$return = array();
		
		
		if ($result == 11) {
			$compile = DB::fetch_first("SELECT error FROM `compileinfo` WHERE solution_id='$sid'");
			$return['error'] = iconv('gb2312','utf-8//IGNORE', $compile['error']);
			
		} elseif(in_array($result, array('6','10','12'))) {
			$runtime = DB::fetch_first("SELECT error FROM `runtimeinfo` WHERE solution_id='$sid'");
			$return['error'] = iconv('gb2312','utf-8//IGNORE', $runtime['error']);
		}
		
		$return['type'] = iconv('gb2312','utf-8//IGNORE', $_G['ArrayData']['result'][$result]['text']);
		$return['stop'] = $result < 4 ? 0 : 1;
		//print_r($return);
		echo json_encode($return);
		
		
	}
	
	public function post_action() {
		require DISCUZ_ROOT."./source/function/function_discuzcode.php";
		global $_G;
		
		
		
		
		$op = getgpc('op');
		$opArr = array('new', 'edit');
		if(empty($op) || !in_array($op, $opArr)) {
			$op = 'new';
		}
		
		$pid = addslashes(getgpc('pid'));
		$sid = addslashes(getgpc('sid'));
		$scope = getgpc('scope');
		$problem = DB::fetch_first("SELECT id,category,title,problem_id,groupid,description FROM `problem` WHERE problem_id=$pid");
		$problem['description'] = aut_decode(discuzcode($problem['description'], 0, 0));
		
		if(!submitcheck('submit')) {
			
			$curlanguage = "C";
			if($op == 'edit') {
				$solution = DB::fetch_first("SELECT a.user_id, a.solution_id, a.language, c.source FROM `solution` AS a
					LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id 
					LEFT JOIN `source_code` AS c ON a.solution_id=c.solution_id 
					WHERE a.solution_id=$sid ");
				if(empty($solution)) showresult("-1", lang('plugin/aut', "no_right_record"));
				allow_self($solution['user_id']);
				
				$curlanguage = $_G['ArrayData']['language_name'][$solution['language']];
			}
			
			$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'solution'), 'href' => AUT_INDEX_PATH."&home=solution&view=me"),
				array('text' => $problem['title']),	
			);
			
			
			include template("aut:common/header");
			include template("aut:solution_post");
			include template("aut:common/footer");
			
		} else {
			
			if($_G['aut_ajax'] == 'noheader') {
				$_G['aut_ajaxurl'] = AUT_INDEX_PATH."&home=course&act=view&op=solution&cid={$problem['category']}&pid={$problem['problem_id']}&scope=$scope";
			}
			
			$language=addslashes(getgpc('language'));
			
			$source=$_POST['source'];
			$source=addslashes($source);
			
			$len=strlen($source);
			
			
			$ip=$_G['client_ip'];
			
			if ($len<2){
				showresult(-1, 'Code too short', $_G['aut_ajax']);
			}
			if ($len>65536){
				showresult(-1, 'Code too long', $_G['aut_ajax']);
			}
			
			// last submit
			$now=strftime("%Y-%m-%d %X",time()-10);
			$sql="SELECT `in_date` from `solution` where `user_id`='{$_G[uid]}' and in_date>'$now' order by `in_date` desc limit 1";
			$res=mysql_query($sql);
			if (mysql_num_rows($res)==1){
				showresult(-1, lang('plugin/aut', 'submit_interval_too_short'), $_G['aut_ajax']);
			}
			

			$curtime = $_G['timestamp'];
			$curdate = dgmdate($curtime, "Y-m-d H:i:s");
			
			$data = array(
					'problem_id' => $problem['problem_id'],
					'user_id' => $_G['uid'],
					'user_name' => $_G['username'],
					'in_date' => $curdate,
					'language' => $language,
					'ip' => $_G['clientip'],
					'code_length' => $len,
					'dateline' => $curtime,
					'result' => 0,
					//'ptype' => 'challenge',
			);
			
			
			
			if($op == 'new') {
			
				
				$insertsql = get_insertsql('solution', $data);
				$sid = DB::query($insertsql);
				
				DB::query("INSERT INTO `source_code` (`solution_id`, `source`) VALUES ('$sid', '$source')");
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'code_submit_success')));
			} else {
				
				
				$rs = DB::fetch_first("SELECT user_id FROM `solution` WHERE solution_id=$sid");
				allow_self($rs['user_id']);
				unset($data['dateline']);
				unset($data['problem_id']);
				unset($data['user_id']);
				unset($data['user_name']);
				unset($data['ptype']);
				$updatesql = get_updatesql('solution', $data, "solution_id=$sid");
				DB::query($updatesql);
				DB::query("UPDATE `source_code` SET source='$source' WHERE solution_id=$sid");
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'code_submit_success')));
			}
			
			if($_G['aut_ajax'] == 'noheader') {
				header("Location: ".$_G['aut_ajaxurl']);
			} else {
				header("Location: ".getStaticUrl(AUT_INDEX_PATH.'&home=solution'));
			}
			
			
			
			
		}
	}
	
	public function view_action() {
		global $_G;
		
		$scope = getgpc('scope');
		$solutionlist = array();
		
		$cid = addslashes(getgpc('cid'));
		$pid = addslashes(getgpc('pid'));
		
		$where .= "a.displayorder>=0".$this->solution_where;
		$where .= " AND a.user_id='{$_G[uid]}' AND a.user_name='{$_G[username]}'";
		$where .= " AND a.problem_id='$pid'";
		
		$query = DB::query("SELECT a.*,b.title FROM `solution` AS a LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id WHERE {$where} ORDER BY a.solution_id DESC");
		while($value = DB::fetch($query)) {
			$value['result'] = getresultArr($value['result'], $value['solution_id']);
			
			$value['testing_point'] = get_testing_point($value['flag_list'], $value['score_list'], $value['usedtime_list']);
			
			$solutionlist[] = $value;
		}
		//echo '<pre>';
		//print_r($solutionlist);
		
		if(empty($_G['aut_ajax'])) include template("aut:common/header");
		include template("aut:solution_view");
		if(empty($_G['aut_ajax'])) include template("aut:common/footer");
		
		
	}

	public function delete_action() {
		global $_G;
		$sid = addslashes(getgpc('sid'));
		$ptype = getgpc('ptype');
		$ptypeArr = array('challenge', 'try');
		if(empty($ptype) || !in_array($ptype, $ptypeArr)) {
			$ptype = 'challenge';
		}
		$delete_tables = array('solution', 'source_code', 'compileinfo', 'runtimeinfo');
		
		
		$rs = DB::fetch_first("SELECT user_id FROM `solution` WHERE solution_id=$sid");
		if($ptype == 'challenge') {
			allow_self($rs['user_id']);
		} else {
			allow_self($rs['user_id'], 1);
		}
		
		foreach($delete_tables as $table) {
			DB::query("DELETE FROM `$table` WHERE solution_id=$sid");
		}
		
		if($ptype == 'challenge') {
			showresult(1, lang('plugin/aut', 'deletesolution'), $_G['aut_ajax']);
		} else {
			$last = DB::fetch_first("SELECT solution_id FROM `solution` WHERE 1 ORDER BY solution_id DESC LIMIT 1");
			$nextAutoIndex = intval($last['solution_id']) + 1;
			DB::query("ALTER TABLE `solution` AUTO_INCREMENT = $nextAutoIndex");
		}
	}
	
	public function viewerror_action() {
		global $_G;
		
		$_G['breadcrumb'] = array(
			array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
			array('text' => lang('plugin/aut', 'solution'), 'href' => AUT_INDEX_PATH."&home=solution&view=me"),
			array('text' => lang('plugin/aut', 'error_info')),	
		);
		
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
	
	
	public function detail_action() {
		global $_G;
		
		$sid = addslashes(getgpc('sid'));
		$where = "a.solution_id='$sid'".$this->solution_where;
		
		$solution = DB::fetch_first("SELECT a.*,b.title,b.spj,c.source FROM `solution` AS a 
			LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id 
			LEFT JOIN `source_code` AS c ON a.solution_id=c.solution_id 
			WHERE {$where} ORDER BY a.solution_id DESC");
		
		jump_if_empty($solution);
		
		if(empty($solution)) showresult("-1", lang('plugin/aut', "no_right_record"));
		
		$solution['result'] = getresultArr($solution['result'], $solution['solution_id']);
		$solution['source'] = htmlspecialchars($solution['source']);
		
		$solution['testing_point'] = get_testing_point($solution['flag_list'], $solution['score_list'], $solution['usedtime_list']);
		
		include template("aut:common/header");
		include template("aut:solution_detail");
		include template("aut:common/footer");
	}
	
}
?>