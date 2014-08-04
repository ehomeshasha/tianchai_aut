<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

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




?>