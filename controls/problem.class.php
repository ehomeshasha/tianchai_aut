<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class problem_controller {
	
	public function __construct() {
		
	}
	
	public function index_action() {
		global $_G;
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'challenge')),
		);
		
		
		$where = "ptype='challenge'";
		$query = DB::query("SELECT id,problem_id,title,accepted,submit,category,ptype FROM `problem` WHERE {$where} ORDER BY problem_id ASC");
		$problemlist = array();
		$category = "";
		while($value = DB::fetch($query)) {
			$value['category_text'] = $_G['aut_cache']['category'][$value['category']]['name']; 
			$value['module'] = $_G['ArrayData']['module_map'][$value['ptype']];
			$problemlist[$value['category']][] = $value;
			
		}
		include template("aut:common/header");
		include template("aut:problem_list");
		include template("aut:common/footer");
	}
	

	public function view_action() {
		require DISCUZ_ROOT."./source/function/function_discuzcode.php";
		global $_G;
		
		$scope = getgpc('scope');
		
		$curlanguage = 'C';
		$pid = addslashes(getgpc('pid'));
		allow_pid($pid);
		
		
		$problem = DB::fetch_first("SELECT * FROM `problem` WHERE problem_id=$pid");
		
		jump_if_empty($problem);
		
		$problem['inputformat_desc'] = getbr($problem['inputformat_desc']);
		$problem['outputformat_desc'] = getbr($problem['outputformat_desc']);
		 
		$problem['description'] = aut_decode(discuzcode($problem['description'], 0, 0));
		
		
		if(!empty($problem['exampledisplay'])) {
			$exampledisplay = init_example_display($problem['exampledisplay'], $problem['example_shownum'], null);
		} else {
			$exampledisplay = init_example_display($problem['example'], $problem['example_shownum'], $pid);
		}
		
		$operation = getgpc('operation');
		$operationArr = array('new', 'edit');
		if(empty($operation) || !in_array($operation, $operationArr)) {
			$operation = 'new';
		}
		if($operation == 'edit') {
			$sid = addslashes(getgpc('sid'));
			$solution = DB::fetch_first("SELECT a.language, b.source FROM `solution` AS a LEFT JOIN `source_code` AS b ON a.solution_id=b.solution_id WHERE a.solution_id='$sid'");
		}
		$op = $operation;
		
		if(strpos($_SERVER['HTTP_REFERER'], "home=problem") !== false) {
			$sub_breadcrumb_text = lang('plugin/aut', 'challenge');
		} elseif(strpos($_SERVER['HTTP_REFERER'], "home=tiku") !== false) {
			$sub_breadcrumb_text = lang('plugin/aut', 'tiku');
		} else {
			$sub_breadcrumb_text = lang('plugin/aut', 'challenge');
		}
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => $sub_breadcrumb_text, 'href' => $_SERVER['HTTP_REFERER']),
				array('text' => $problem['title']),
		);
		
		
		if(empty($_G['aut_ajax'])) include template("aut:common/header");
		include template("aut:problem_view");
		if(empty($_G['aut_ajax'])) include template("aut:common/footer");
		
	}
	
}






?>