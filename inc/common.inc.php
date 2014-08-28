<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
global $_G;
/*---------------------------readcache-----------------------------*/
//category
$cachefile = DISCUZ_ROOT.'data/sysdata/cache_category.php';
	
if(file_exists($cachefile)){
	include_once $cachefile;
} else {
	$query = DB::query("SELECT cid,name,fid FROM ".DB::table('aut_category')." WHERE 1 ORDER BY displayorder ASC, cid ASC");
	while($value = DB::fetch($query)) {
		$categoryArr[$value[cid]] = array('name'=>$value['name'], 'fid'=>$value['fid']);
	}
	$category_contents = "\$categoryArr=".arrayeval($categoryArr).";\n";
	writetocache('category', $category_contents);
}
$_G['aut_cache']['category'] = $categoryArr;
//categorytree
$cachefile = DISCUZ_ROOT.'data/sysdata/cache_categorytree.php';

if(file_exists($cachefile)){
	include_once $cachefile;
} else {
	$categorytreeArr = get_categorytree();
	$categorytree_contents = "\$categorytreeArr=".arrayeval($categorytreeArr).";\n";
	writetocache('categorytree', $categorytree_contents);
}
$_G['aut_cache']['categorytree'] = $categorytreeArr;

$_G['aut_cache']['categorytree_merge'] = array();
foreach($_G['aut_cache']['categorytree'] as $v) {
	$_G['aut_cache']['categorytree_merge'] = array_merge($_G['aut_cache']['categorytree_merge'], $v);
}

//每隔一段时间判断Problem的solve情况
//if($_G['timestamp'] % 3600 == 0) {
if(!empty($_G['uid']) && true) {
	
	$where = "a.user_id='$_G[uid]' AND a.result!=0 AND b.solved!=1";//条件为当前用户未成功解决且解题结果不是初始结果
	$query = DB::query("SELECT a.result,b.problem_id,b.solved_userlist,b.did_userlist  
				FROM `solution` AS a LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id 
				WHERE {$where}");
	$result = array();
	
	while($value = DB::fetch($query)) {
		if(empty($result[$value['problem_id']])) {
			$result[$value['problem_id']]['result'] = array($value['result']);
			$result[$value['problem_id']]['solved_userlist'] = $value['solved_userlist'];
			$result[$value['problem_id']]['did_userlist'] = $value['did_userlist'];
		} else {
			array_push($result[$value['problem_id']]['result'], $value['result']);			
		}
		
		
	}
	
	//echo '<pre>';
	//print_r($result);
	foreach($result as $key => $value) {
		$solved_userlist = array_filter(json_decode($value['solved_userlist'], true), "problem_status_userlist_filter");
		$did_userlist = array_filter(json_decode($value['did_userlist'], true), "problem_status_userlist_filter");
		if(in_array("4", $value['result'])) {
			$solved_userlist[] = $_G['uid'];
			$solved = "solved=1,";	
		} else {
			$did_userlist[] = $_G['uid'];
		}
		
		$solved_userlist_sql = empty($solved_userlist) ? "" : json_encode($solved_userlist);
		$did_userlist_sql = empty($did_userlist) ? "" : json_encode($did_userlist);
		
		DB::query("UPDATE `problem` SET {$solved}  
				solved_userlist='".$solved_userlist_sql."',
				did_userlist='".$did_userlist_sql."' 
				WHERE problem_id='$key'");
	}
	
	
	
	
	
}







function problem_status_userlist_filter($v) {
	global $_G;
	if ($v != $_G['uid']) {
		return true;
	}
	return false;
}






?>