<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class rank_controller {

	public function __construct() {

	}

	public function index_action() {
		global $_G;
		
		include_once AUT_PATH."/inc/paginator.class.php";
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'pinyin_paihangbang')),
		);
		
		
		//$where = "ptype='tiku' AND problem_id<20000";
		
		$query = DB::query("SELECT a.user_id,a.user_name,a.result FROM `solution` AS a LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id WHERE a.result!=0 AND b.ptype='tiku' AND b.problem_id<20000");
		//$user_solved = array();
		//$user_did = array();
		$user_solution = array();
		while($value = DB::fetch($query)) {
			if(!isset($user_solved[$value['user_id']])) {
				$user_solved[$value['user_id']] = 0;	
			}
			if(!isset($user_did[$value['user_id']])) {
				$user_did[$value['user_id']] = 0;	
			}
			if($value['result'] == "4") {
				$user_solved[$value['user_id']]++;
			}
			$user_did[$value['user_id']]++;
			//$user_solution[$value['user_id']] = array();
			$user_solution[$value['user_id']] = json_encode(array(
				'solved' => $user_solved[$value['user_id']],
				'did' => $user_did[$value['user_id']],
				'user_name' => iconv("GB2312", "UTF-8//IGNORE", $value['user_name']),
			)); 
		}
		
		
		
		//arsort($user_solved);
		//print_r($user_solved);
		//print_r($user_did);
		uasort($user_solution, "user_solution_sort");
		$count = count($user_solution);
		$paginator = new paginator($count);
		$perpage = $paginator->get_perpage();
		$multi = $paginator->get_multi();
		$start = $paginator->get_start();
		
		$user_list = array();
		$i = 0;
		foreach ($user_solution as $key => $value) {
			$i++;
			if($i < $start+1 || $i > ($start + $perpage)) {
				continue;
			}
			$array = json_decode($value, true);
			$user_list[] = array(
				'user_id' => $key,
				'user_name' => iconv("UTF-8", "GB2312//IGNORE", $array['user_name']),
				'solved' => $array['solved'],
				'did' => $array['did'], 
			);
			
		}
		
		//echo '<pre>';
		//print_r($user_list);
		
		
		
		include template("aut:common/header");
		include template("aut:rank_list");
		include template("aut:common/footer");
		
		
		
		
	}
	
	

	
}


function user_solution_sort($a, $b) {
	$ar = json_decode($a, true);
	$br = json_decode($b, true);
	if($ar['solved'] == $br['solved']) {
		if($ar['did'] == $br['did']) return 0;
		return ($ar['did'] > $br['did']) ? 1 : -1;
	}
	return ($ar['solved'] > $br['solved']) ? -1 : 1;
}
?>