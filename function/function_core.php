<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once AUT_PATH."/function/function_category.php";
require_once AUT_PATH."/function/function_editor.php";
require_once AUT_PATH."/function/function_permission.php";
require_once AUT_PATH."/function/function_validation.php";

function jumpto_list_page($http_referer) {
	global $_G;
	if(strpos($http_referer, "&act=post") !== false) {
		$jump_url = AUT_INDEX_PATH."&home=".$_G['aut_controller'];
	} else {
		$jump_url = $http_referer;
	}
	header("Location: ".$jump_url);
	exit;
}


function get_testing_point($flag_list, $score_list, $usedtime_list) {
	if(empty($flag_list) || empty($score_list) || empty($usedtime_list)) return;
	$flagArr = explode(",", $flag_list);
	$scoreArr = explode(",", $score_list);
	$usedtimeArr = explode(",", $usedtime_list);
	if(count($flagArr) == count($scoreArr) && count($scoreArr) == count($usedtimeArr)) {
		foreach($flagArr as $k=>$flag) {
			$data[$k] = array(
				'flag' => $flag,
				'score' => $scoreArr[$k],
				'usedtime' => $usedtimeArr[$k],
			);
		}
		return $data;
	}
	return;
}

function jump_if_empty($array) {
	if(empty($array) || count($array) == 0) {
		showresult("-1", lang('plugin/aut', "no_right_record"));
	}
}

function page404() {
	include_once template('aut:common/page404');exit;
}
function page500() {
	include_once template('aut:common/page500');exit;
}

function pass_rate_format($pass_rate) {
	return score_format($pass_rate*100);
}

function score_format($score) {
	return round($score, 2);
}

function get_users_score_from_pidlist($pidlist, $startdatetime, $enddatetime) {
	$startdatetime = addslashes($startdatetime);
	$enddatetime = addslashes($enddatetime);
	$pid_str = get_id_str($pidlist);
	$where = "problem_id IN ($pid_str) AND dateline>=$startdatetime AND dateline<=$enddatetime";
	$query = DB::query("SELECT problem_id,user_name,`pass_rate` FROM `solution` WHERE {$where} ORDER BY dateline ASC");
	//fetch all the solutions
	while($value = DB::fetch($query)) {
		//old solution pass_rate will be covered by the new one
		$data[$value['user_name']][$value['problem_id']] = $value['pass_rate'];
	}
	
	//echo '<pre>';
	//print_r($data);
	
	$data2 = array();
	foreach($data as $user_name => $val) {
		//sum all problem score for each user
		$data2[$user_name] = array_sum($val)*100;
	}
	//order the array by score value
	arsort($data2, SORT_NUMERIC);
	
	return $data2;
}

function get_problem_href_for_competition($pid, $uid) {
	$where = "problem_id='$pid' AND user_id='$uid'";
	$rs = DB::fetch_first("SELECT solution_id FROM `solution` WHERE {$where} ORDER BY dateline DESC LIMIT 1");
	if(empty($rs)) {
		return getStaticUrl(AUT_INDEX_PATH.'&home=problem&act=view&pid='.$pid); 
	} else {
		return AUT_INDEX_PATH."&home=solution&act=post&op=edit&sid=$rs[solution_id]&pid=$pid"; 
	}
}


function get_final_score($pid, $uid) {
	$where = "problem_id='$pid' AND user_id='$uid'";
	
	$rs = DB::fetch_first("SELECT `pass_rate` FROM `solution` WHERE {$where} ORDER BY dateline DESC LIMIT 1");
	return pass_rate_format($rs['pass_rate']);
}


function get_problem_count($pid, $uid, $type) {
	$where = "problem_id='$pid' AND user_id='$uid'";
	if($type == 'ac') {
		$where .= " AND result=4";
	} elseif($type == 'submit') {
		
	} else {
		return;
	}
	$rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `solution` WHERE {$where}");
	return $rs['count'];
}


function linefeed2br($str) {
	return preg_replace("/\r{0,1}\n/", "<br />", $str);
}

function get_id_str($idlist, $delimiter = ",") {
	$idArr = explode($delimiter, $idlist);
	$id_str = dimplode($idArr);
	return $id_str;
}

function get_solution_problem_ids_from_pidlist($pidlist) {
	global $_G;
	$pid_str = get_id_str($pidlist);
	$query = DB::query("SELECT problem_id FROM `solution` WHERE problem_id IN ($pid_str) AND user_id='$_G[uid]' GROUP BY problem_id");
	while($value = DB::fetch($query)) {
		$solution_problem_ids[] = $value['problem_id'];
	}
	return $solution_problem_ids;
}


function get_solutions_from_pidlist($pidlist, $op) {
	global $_G;
	
	if($op == 'all') {
		$where = " ";
	} elseif($op == 'my') {
		$where = " AND a.user_id='$_G[uid]'";
	} elseif($op == 'my') {
		$where = " AND a.user_id!='$_G[uid]'";
	}
	$pid_str = get_id_str($pidlist);
	$query = DB::query("SELECT a.*,b.title FROM `solution` AS a LEFT JOIN `problem` AS b ON a.problem_id=b.problem_id 
	 WHERE a.problem_id IN ($pid_str) {$where} ORDER BY a.solution_id DESC");
	while($value = DB::fetch($query)) {
		$value['result'] = getresultArr($value['result'], $value['solution_id']);
		$solutions[] = $value;
	}
	return $solutions;
	
}

function get_problems_from_pidlist($pidlist) {
	$pid_str = get_id_str($pidlist);
	$query = DB::query("SELECT * FROM `problem` WHERE problem_id IN ($pid_str)");
	while($value = DB::fetch($query)) {
		$problems[] = $value;
	}
	return $problems;
}

function get_competition_status($startdatetime, $enddatetime, $now, $countdown = false) {
	if($startdatetime >= $enddatetime) {
		return -3;
	}
	if($startdatetime > $now) {
		return -2;
	} elseif($enddatetime < $now) {
		return -1;
	} else {
		return 0;
	}
}

function get_competition_status_word($startdatetime, $enddatetime, $now, $countdown = false) {
	if($startdatetime >= $enddatetime) {
		return lang('plugin/aut', 'Unknown');
	}
	if($startdatetime > $now) {
		return lang('plugin/aut', 'not_start_yet');
	} elseif($enddatetime < $now) {
		return lang('plugin/aut', 'already_end');
	} else {
		if($countdown) {
			return calculate_countdown($enddatetime, $now);
		} else {
			return lang('plugin/aut', 'ongoing');
		}
	}
}

function calculate_countdown($enddatetime, $now) {
	if($now > $enddatetime) return;
	$off = $enddatetime - $now;
	$day = intval($off/86400);
	$hour = intval($off%86400/3600);
	$minute = intval($off%3600/60);
	$second = intval($off%60/1);
	
	$word = lang('plugin/aut', 'shengyu_pinyin');
	if($day != 0) $word .= $day.lang('plugin/aut', 'tian_pinyin')." ";
	if($hour != 0 || $day != 0) $word .= $hour.lang('plugin/aut', 'xiaoshi_pinyin')." ";
	if($minute != 0 || $hour != 0 || $day != 0) $word .= $minute.lang('plugin/aut', 'fen_pinyin');
	if($day != 0 || $minute != 0 || $hour != 0 || $day != 0) $word .= $second.lang('plugin/aut', 'miao_pinyin');
	return $word;
}

function getAhaleiDB() {
	global $_G;
	return getSqlModel($_G['aut_settings']['ahalei']['SqlConnect']);
}

function getSqlModel($connect) {
	$db = new mysql();
	$db->connect($connect['dbhost'],$connect['dbuser'],$connect['dbpw'],$connect['dbname']);
	return $db;
}

function split_numeric_array($arr, $n) {
	$split_arr = array();
	foreach($arr as $k=>$v) {
		$mod = $k%$n;
		$split_arr[$mod][] = $v;
	}
	return $split_arr;
}


//循环删除目录和文件函数
function removeDir($dirName , $deleteDir = false) {
	if ($handle = opendir("$dirName")) {
		while (false !== ($item = readdir($handle))) {
			if($item != "." && $item != "..") {
				if(is_dir("$dirName/$item")) {
					rmDir("$dirName/$item", true);
				} else {
					unlink("$dirName/$item");
				}
			}
		}
		closedir($handle);
		if($deleteDir) {
			rmdir($dirName);
		}
	}
}


function unicode2utf8($str){
	if(!$str) return $str;
	$decode = json_decode($str);
	if($decode) return $decode;
	$str = '["' . $str . '"]';
	$decode = json_decode($str);
	if(count($decode) == 1){
		return $decode[0];
	}
	return $str;
}


function getchallengeidArr($cid) {
	$query = DB::query("SELECT problem_id,title FROM `problem` WHERE category='$cid' AND ptype='challenge' ORDER BY problem_id ASC");
	while($value = DB::fetch($query)) {
		$challengeidArr[] = $value;
	}
	return $challengeidArr;
}

function getStaticUrl($url) {
	global $_G;
	if($_G['aut_settings']['rewrite']) {
		if(array_key_exists($url,$_G['aut_settings']['UrlRules'][0])) {
			return $_G['aut_settings']['UrlRules'][0][$url];
		} else {
			foreach($_G['aut_settings']['UrlRules'][1] as $pattern=>$value) {
				if(preg_match("/^$pattern$/", $url)) {
					return preg_replace_callback("/^$pattern$/", function($matches) use ($value) {
						#/solution-$1-$2-$3.html
						for($i=1;$i<count($matches);$i++) {
							$value = str_replace("$".$i, $matches[$i], $value);
						}
						return $value;
					}, $url);
				}
			}
			return $url;
		}
	}
	return $url;
}


function getbreadcrumb() {
	global $_G;
	if(!empty($_G['breadcrumb'])) {
		$html = "<ul class='breadcrumb'>";
		foreach($_G['breadcrumb'] as $b) {
			if(!empty($b['href']))
				$html .= "<li class='active'><a href='{$b['href']}'>$b[text]</a> <span class='divider'>/</span></li>";
			else
				$html .= "<li>$b[text]</li>";
		}
		$html .= "</ul>";
	}
	return $html;
}

function showresult($code, $message, $ajax='', $jump_url='') {
	
	global $_G;
	if(empty($ajax)) {
		include template('aut:common/header');
		include template('aut:common/result');
		include template('aut:common/footer');
		exit;
	} elseif($ajax == 'noheader') {
		$_SESSION['aut_message'] = array('code' => -1, 'content' => array($message));
		header("Location: ".$_G['aut_ajaxurl']);
		exit;
	} elseif($ajax == 'json'){
		$message = iconv("GB2312", "UTF-8//IGNORE", $message);
		exit(json_encode(array('code' => $code, 'content' => array($message))));
	} elseif($ajax == '1') {
		exit($message);
	}
}


function aut_multi($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $autogoto = FALSE, $simple = FALSE) {

	$lang['prev'] = "<";
	$lang['next'] = ">";
	$dot = '...';
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') !== FALSE ? '&amp;' : '?';

	$realpages = 1;
	$page -= strlen($curpage) - 1;
	if($page <= 0) {
		$page = 1;
	}
	if($num > $perpage) {

		$offset = floor($page * 0.5);

		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}
		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<li><a onclick=\'jumpto("'.$mpurl.'page=1");\' href="javascript:;">1 '.$dot.'</a></li>' : '').
		($curpage > 1 && !$simple ? '<li><a onclick=\'jumpto("'.$mpurl.'page='.($curpage - 1).'");\' href="javascript:;">'.$lang['prev'].'</a></li>' : '<li class="disabled"><a href="javascript:;">'.$lang['prev'].'</a></li>');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<li class="active"><a href="javascript:;">'.$i.'</a></li>' :
			'<li><a onclick=\'jumpto("'.$mpurl.'page='.$i.'");\' href="javascript:;">'.$i.'</a></li>';
		}
		$multipage .= ($to < $pages ? '<li><a onclick=\'jumpto("'.$mpurl.'page='.$pages.'");\' href="javascript:;">'.$dot.' '.$realpages.'</a></li>' : '').
		($curpage < $pages && !$simple ? '<li><a onclick=\'jumpto("'.$mpurl.'page='.($curpage + 1).'");\' href="javascript:;">'.$lang['next'].'</a></li>' : '<li class="disabled"><a href="javascript:;">'.$lang['next'].'</a></li>');

		$multipage = $multipage ? '<ul>'.($shownum && !$simple ? '<em>&nbsp;'.$num.'&nbsp;</em>' : '').$multipage.'</ul>' : '';
	}
	$maxpage = $realpages;
	return $multipage;
}

function initdate($offset = '-1 month') {
	global $_G;

	$default_starttime = strtotime($offset, $_G['timestamp']);
	$default_startdate = dgmdate($default_starttime, "Y-m-d");
	$default_endtime = $_G['timestamp'];
	$default_enddate = dgmdate($default_endtime, "Y-m-d");
	$request_startdate = addslashes(trim(getgpc('startdate')));
	$request_enddate = addslashes(trim(getgpc('enddate')));
	$regex = "/^[\d]{4}-[\d]{2}-[\d]{2}$/";

	if(empty($request_startdate)) {
		$startdate = $default_startdate;
		$starttime = $default_starttime;
	} else {
		if(!preg_match($regex, $request_startdate)) exit(lang('plugin/aut', 'startdate_invalid'));
		$startdate = $request_startdate;
		$starttime = strtotime($startdate);
	}
	if(empty($request_enddate)) {
		$enddate = $default_enddate;
		$endtime = $default_endtime;
	} else {
		if(!preg_match($regex, $request_enddate)) exit(lang('plugin/aut', 'enddate_invalid'));
		$enddate = $request_enddate;
		$endtime = strtotime($enddate) + 86399;
	}

	$arr = array(
			'startdate' => $startdate,
			'enddate' => $enddate,
			'starttime' => $starttime,
			'endtime' => $endtime
	);
	return $arr;
}

function get_result_detail($solution_id, $type) {
	$rs = DB::fetch_first("SELECT error FROM `{$type}info` WHERE solution_id='$solution_id'");
	return $rs['error'];
}



function getresultArr($result, $sid){//, $passrate=0.00) {
	global $_G;
	
	$data = $_G['ArrayData']['result'][$result]; 
	
	$html = $type = "";
	if ($result == 11) {
		$type = 'compile';
		//$html = "<a href='".AUT_INDEX_PATH."&home=solution&act=viewerror&type=$type&sid=$sid&result=$result' class='btn btn-".$data['style']."'  title='".lang('plugin/aut', MSG_Click_Detail)."'>".$data['text']."</a>";
	} elseif(in_array($result, array('6','10','12'))) {
		$type = 'runtime';
		//$html = "<a href='".AUT_INDEX_PATH."&home=solution&act=viewerror&type=$type&sid=$sid&result=$result' class='btn btn-".$data['style']."' title='".lang('plugin/aut', MSG_Click_Detail)."'>".$data['text']."</a>";
	}else{
		//$html = "<span class='btn btn-".$data['style']."'>".$data['text']."</span>";
	}
	//if (isset($passrate) && $passrate > 0 && $passrate <.98) {
		//$html ="<span class='btn btn-info'>". (100-$passrate*100)."%</span>";
	//}
	
	$extra = $_G['in_admin'] == 1 ? "&admin=1" : "";
	$html = "<a href='".AUT_INDEX_PATH."&home=solution&act=detail&sid={$sid}{$extra}' class='btn btn-".$data['style']."'>".$data['text']."</a>";
	
	//$data = $_G['ArrayData']['result'][$result];
	//$html = "<span class='btn btn-{$data['style']}'>{$data['text']}</span>";
	
	return array('code' => $result, 'type' => $type, 'html' => $html);
	
}





function get_insertsql($tbname, $data, $replace=false) {
	
	$f=$v='';
	foreach($data as $key=>$val) {
		$f.=',`'.$key.'`';
		$v.=",'".$val."'";
	}
	$type=$replace?'REPLACE':'INSERT';
	return $type." INTO ".$tbname." (".substr($f, 1).") VALUES (".substr($v, 1).")";
}

function get_updatesql($tbname, $data, $container, $quot = "'") {

	$f = '';
	foreach($data as $key=>$val) {
		$f.=",`".$key."`=".$quot.$val.$quot;
	}
	return "UPDATE $tbname SET ".substr($f, 1)." WHERE ".$container;
}


function init_example_display($example_str, $example_shownum, $pid) {

	global $_G;
	$html .= "<tr><td>".lang('plugin/aut', 'example').":</td><td>";
	$examples = json_decode($example_str, true);
	$exampleArr = array();
	
	if($pid != null) {
		foreach($examples as $key=>$value) {
			$num = intval(preg_replace("/\..+$/s", "", preg_replace("/^[^\d]+/", "",$value['input'])));
			$exampleArr[$num] = $value;
		}
		ksort($exampleArr);
	} else {
		$exampleArr = $examples;
	}
	$i = 0;
	//echo '<pre>';
	//print_r($exampleArr);
	foreach ($exampleArr as $key=>$example) {
		 
		if($i >= $example_shownum) break;
		if(!isSubArrayEmpty($example)) {
			if($pid != null) {
				$input = iconv("UTF-8", "GB2312//IGNORE", getbr(file_get_contents($_G['OJ_DATA']."/".$pid."/".$example['input'])));
				$output = iconv("UTF-8", "GB2312//IGNORE", getbr(file_get_contents($_G['OJ_DATA']."/".$pid."/".$example['output'])));
			} else {
				$input = empty($example['input'])? "" : getbr(iconv("UTF-8", "GB2312//IGNORE", $example['input']));
				$output = empty($example['output'])? "" : getbr(iconv("UTF-8", "GB2312//IGNORE", $example['output']));
			}
			
			
			$html .= "<table class='examplewell mbw' cellspacing='0' cellpadding='0'>";
			
			$html .= "	<tr>
							<td width='95%'>
								<div class='wellin'>
									<p>".$_G['ArrayData']['exampletext']['input']."</p>
									<div>".$input."</div>
								</div>
							</td>
						</tr>
						<tr>
							<td width='95%'>
								<div class='wellin'>
									<p>".$_G['ArrayData']['exampletext']['output']."</p>
									<div>".$output."</div>
								</div>
							</td>
						</tr>";
			if(!empty($example['explanation'])) {
				if($pid != null) {
					$explanation = getbr(iconv("UTF-8", "GB2312//IGNORE", $example['explanation']));
				} else {
					$explanation = getbr(iconv("UTF-8", "GB2312//IGNORE", $example['explanation']));
				}
				$html .= "<tr>
							<td width='95%'>
								<div class='wellin'>
									<p>".$_G['ArrayData']['exampletext']['explanation']."</p>
									<div>".$explanation."</div>
								</div>
							</td>
						</tr>";
			}			
			
			
			$html .= "</table>";
			$i++;
		}
	}
	$html .= "</td></tr>";
	return $html;
}
function getbr($str) {
	return preg_replace("/\r{0,1}\n/", "<br/>", $str);
}

function isSubArrayEmpty($arr) {
	$j = true;
	foreach ($arr as $v) {
		if(!empty($v)) {		
			$j = false;
			break; 
		}
	}
	return $j;
}
function initmessage() {
	$str = "";
  	if($_SESSION['aut_message']) {
		$message = $_SESSION['aut_message']; 
		$code = $message['code'];
		if($code == -1) {
			$alertclass = 'alert-error';
		} elseif($code == 0) {
			$alertclass = 'alert-info';
		} elseif($code == 1) {
			$alertclass = 'alert-success';
		}
		$str .= "<div class='alert $alertclass' id='aut_message'>
					<button type='button'' class='close clear_session' data-dismiss='alert'>&times;</button>
					<ul style='margin-bottom:0;'>";
		foreach ($message['content'] as $v) {
			$str .= "<li>$v</li>";
		}
		$str .= "</ul></div>";
	}
	return $str;
}
function initexample($example_str, $max, $pid) {
	global $_G;
	$example_data_next = 2;
	if(!empty($example_str)) {
		$examples = json_decode($example_str, true);
		$example_data_next = count($examples) + 1;
	}
	
	echo '<legend class="mtm">'.lang('plugin/aut', 'example_title').'</legend>';
	$show = true;
	$size_limit = 1024*100;
	
	for($i=1;$i<=$max;$i++) {
		$j = $i-1;
		if(filesize($_G['OJ_DATA']."/".$pid."/".$examples[$j]['input']) > $size_limit || filesize($_G['OJ_DATA']."/".$pid."/".$examples[$j]['output']) > $size_limit) {
			$show = false;
			echo "<div class='well'>".lang('plugin/aut', 'file_too_large')."</div>";
			break;
		}
	}
	
	for($i=1;$i<=$max;$i++) {
		$j = $i-1;
		$hide = $i>=$example_data_next ? "hide_example " : "";
		
		echo '<label class="'.$hide.'example'.$i.'" id="examplelabel_input_'.$i.'">'.lang('plugin/aut', 'testdata_input').$i.'</label>
			<textarea class="large_inputbox '.$hide.'example'.$i.'" name="example'.$i.'_inputformat">';
		if($show && !empty($pid) && !empty($examples[$j]['input'])) {
			echo iconv("utf-8", "gb2312", file_get_contents($_G['OJ_DATA']."/".$pid."/".$examples[$j]['input']));  
		}
		echo '</textarea>
			<label class="'.$hide.'example'.$i.'">'.lang('plugin/aut', 'testdata_output').$i.'</label>
			<textarea class="large_inputbox '.$hide.'example'.$i.'" name="example'.$i.'_outputformat">';
		if($show && !empty($pid) && !empty($examples[$j]['output'])) {
			echo iconv("utf-8", "gb2312", file_get_contents($_G['OJ_DATA']."/".$pid."/".$examples[$j]['output']));  
		}
		echo '</textarea>';
	}
	echo '<a href="javascript:;" class="add_example mbw" data-next="'.$example_data_next.'">'.lang('plugin/aut', 'addexample').'</a>';
}


function initexample_display($exampledisplay) {
	global $_G;
	$arr = json_decode($exampledisplay, true);
	//echo '<pre>';
	//print_r($arr);
	echo '<legend class="mtm">'.lang('plugin/aut', 'example').'</legend>';
	$show = true;
	
	
	for($i=0;$i<3;$i++) {
		$j = $i+1;
		echo '<label class="example'.$i.'">'.lang('plugin/aut', 'example_input').$j.'</label>
			<textarea class="large_inputbox example'.$i.'" name="exampledisplay'.$i.'_inputformat">';
		if(!empty($arr[$i]['input'])) {
			echo htmlspecialchars(iconv("UTF-8", "GB2312//IGNORE", $arr[$i]['input']));  
		}
		echo '</textarea>
			<label class="example'.$i.'">'.lang('plugin/aut', 'example_output').$j.'</label>
			<textarea class="large_inputbox example'.$i.'" name="exampledisplay'.$i.'_outputformat">';
		if(!empty($arr[$i]['output'])) {
			echo htmlspecialchars(iconv("UTF-8", "GB2312//IGNORE", $arr[$i]['output']));  
		}
		echo '</textarea>
			<label class="example'.$i.'">'.lang('plugin/aut', 'example_explanation').$j.'</label>
			<textarea class="large_inputbox example'.$i.'" name="exampledisplay'.$i.'_explanation">';
		if(!empty($arr[$i]['explanation'])) {
			echo htmlspecialchars(iconv("UTF-8", "GB2312//IGNORE", $arr[$i]['explanation']));  
		}
		echo '</textarea>';
	}
}


function initusergroup($groupid, $lang) {
	
	$usergrouparr = explode(",", $groupid);
	$usergroupstr = '<select style="width:256px;" name="usergroup[]" size="10" multiple="multiple"><option value=""'.(@in_array('', $usergrouparr) ? ' selected' : '').'>'.$lang['plugins_empty'].'</option>';
	$query = C::t('common_usergroup')->range_orderby_credit();
	$groupselect = array();
	foreach($query as $group) {
		$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
		$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $usergrouparr) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
	}
	
	
	$usergroupstr .= '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';
	return $usergroupstr;
}

function global_addslashes($str) {
	return addslashes(htmlspecialchars(trim($str), ENT_COMPAT , AUT_CHARSET));
}
function global_getgpc($str) {
	return global_addslashes(getgpc($str));
}

function mkdata($pid,$filename,$input,$oj_data){
	
	$basedir = "$oj_data/$pid";
	
	$fp = @fopen ( $basedir . "/$filename", "w" );
	if($fp){
		fputs ( $fp, str_replace ( "\\n", "\n", $input ) );
		fclose ( $fp );
	}else{
		echo "Error while opening".$basedir . "/$filename ,try [chgrp -R www $oj_data] and [chmod -R 771 $oj_data ] ";
		
	}
	
}
?>