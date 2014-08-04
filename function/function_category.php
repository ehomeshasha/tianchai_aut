<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


function getcategorycount($cid, $type = '') {
	if($type == 'cateid_1' || $type == 'cateid_2') {
		$rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `problem` WHERE `$type` LIKE '%,{$cid},%'");
	} else {
		$rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `problem` WHERE category='$cid'");
	}
	return $rs['count'];
}

function getcatename($cateid) {
	global $_G;
	$catename = "";
	$cateidArr = explode(",", substr($cateid, 1));
	foreach($cateidArr as $cid) {
		$catename .= ",".$_G['aut_cache']['category'][$cid]['name'];
	}
	return substr($catename, 1);
}

function getValidCategory() {
	global $_G;
	$validcate = array();
	foreach($_G['aut_cache']['categorytree']['learning'] as $cate0) {
		
		$i = 0;
		foreach($cate0['subcate'] as $cate1) {
			$j = 0;
			foreach($cate1['subcate'] as $k=>$cate2) {
				$soluted = DB::fetch_first("SELECT COUNT(*) AS count FROM `solution` AS a
						LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id
						WHERE b.category='{$cate2['cid']}' AND b.ptype='challenge' AND a.result=4 AND a.user_id='$_G[uid]'");
						$total = DB::fetch_first("SELECT COUNT(*) AS count FROM `problem` WHERE category='{$cate2['cid']}' AND ptype='challenge'");
						$soluted_count = $soluted['count'];
						$total_count = $total['count'];

						if($i == 0 && $j == 0 && $soluted_count/$total_count < $_G['aut_settings']['passrate']) {
							$validcate[] = $cate2['cid'];
							break;
						} elseif($soluted_count/$total_count >= $_G['aut_settings']['passrate']) {
							$validcate[] = $cate2['cid'];
							if($k != count($cate1['subcate']) - 1) {
								$validcate[] = $cate1['subcate'][$k+1]['cid'];
							}
						}
						$j++;
			}
			$i++;
		}
	}
	return $validcate;
}

function get_categorytree($fid = 0, $level = 0 ,$ctype = '') {
	$tree = array();
	$level++;
	
	if($ctype == '') {
		$query = DB::query("SELECT ctype FROM ".DB::table('aut_category')." WHERE status>0 GROUP BY ctype");
		while($value = DB::fetch($query)) {
			$tree[$value['ctype']] = get_categorytree(0, 0, $value['ctype']);
		}
		return $tree;
	}
	$query = DB::query("SELECT cid,fid,name,description FROM ".DB::table('aut_category')." WHERE status>0 AND ctype='$ctype' AND fid=$fid ORDER BY displayorder ASC, cid ASC");
	while($value = DB::fetch($query)) {
		$tree[] = array(
				'cid' => $value['cid'],
				'fid' => $value['fid'],
				'name' => $value['name'],
				'description' => $value['description'],
				'subcate' => get_categorytree($value[cid], $level, $ctype),
		);
	}
	
	return $tree;
}

function init_categorylist($categoryArr) {
	$html = "";
	foreach ($categoryArr as $value) {
		$html .= "<li>
		<p>
		<span>$value[name]</span>
		<a href='".AUT_INDEX_PATH."&home=category&act=post&op=edit&cid=$value[cid]&admin=1'>[edit]</a>
		<a href='javascript:;' class='deletelink' data-id='$value[cid]' data-type='category'>[delete]</a></p><ul>";
		$html .= init_categorylist($value['subcate']);
		$html .= "</ul></li>";
	}
	return $html;
}


function init_category_optgroup($categoryArr, $cid = "", $level = 0) {
$level++;
$html = "";

		foreach ($categoryArr as $value) {
		$selected = $value[cid] == $cid ? "selected='selected'" : "";
		if($level <= 2) {
			$html .= "<optgroup label='$value[name]'>";
			$html .= str_repeat("&nbsp;", (level-1)*4).init_category_optgroup($value['subcate'], $cid, $level);
			$html .= "</optgroup>";
		} else {
			$html .= "<option value='$value[cid]' $selected>$value[name]</option>";
			$html .= init_category_optgroup($value['subcate'], $cid, $level);
		}
		}

		return $html;
}

function init_category($categoryArr, $cid = "", $level = 0, $offset = 1, $invalid_count = 2) {
	$level++;
	$html = "";

	foreach ($categoryArr as $value) {
		if($offset == 1 && $level <= $invalid_count) {
			$extattr = "disabled='disabled' style='color:#000;'";
		} else {
			$extattr = "";
		}
		$selected = $value[cid] == $cid ? "selected='selected'" : "";
		$html .= "<option value='$value[cid]' $selected $extattr>".str_repeat("&nbsp;", ($level-$offset)*4).$value[name]."</option>";
		$html .= init_category($value['subcate'], $cid, $level, $offset, $invalid_count);
	}

	return $html;
}
?>