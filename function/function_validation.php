<?php
function validation_start() {
	global $_G;
	if(!empty($_G['error_msg'])) {
		$_SESSION['aut_message'] = array('code' => '-1', 'content' => $_G['error_msg']);
		header('Cache-control: private, must-revalidate, max-age');
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	}
}
function chkEmpty($n, $v) {
	global $_G;
	if(empty($v)) {
		$_G['error_msg'][] = $n.lang('plugin/aut', 'cannot_be_empty');
		return false;
	}
	return $v;
}
function chkRegEx($n, $v, $reg) {
	global $_G;
	if(!preg_match($reg, $v)) {
		$_G['error_msg'][] = $n.lang('plugin/aut', 'format_is_not_correct');
		return false;
	}
	return $v;
}
function chkNumber($n, $v) {
	if($v == 0) {
		return $v;
	}
	global $_G;
	if(empty($v) || !preg_match("/^[1-9]\d*\.{0,1}\d+$/", $v)) {
		$_G['error_msg'][] = $n.lang('plugin/aut', 'must_be_number');
		return false;
	}
	return $v;
}

function chkDigit($n, $v, $min, $max) {
	if($v == 0) {
		return $v;
	}
	global $_G;
	if(chkLength($n, $v, $min, $max) === false) {
		return false;
	}
	if(!preg_match("/^[1-9]{1}\d*$/", $v)) {
		$_G['error_msg'][] = $n.lang('plugin/aut', 'must_be_digit');
		return false;
	}
	return $v;
}
function chkLength($n, $v, $min, $max) {
	global $_G;
	if($min == 0 && empty($v)) {
		$_G['error_msg'][] = $n.lang('plugin/aut', 'cannot_be_empty'); 
		return false;		
	}
	$len = mb_strlen($v, "GB2312");
	if(($len < intval($min) && !empty($min)) || ($len > intval($max) && !empty($max))) {
		$_G['error_msg'][] = $n.sprintf(lang('plugin/aut', 'length_must_between%d~%d_inmiddle'), $min, $max);
		return false;
	}
	return $v;
}


function chkUploadExist($n,$v) {
	global $_G;
	if(is_array($v)) {
		$val = $v[0];
	} else {
		$val = $v;
	}
	if(empty($val)) {
		$_G['error_msg'][] = lang("Please upload ").$n;
		return false;
	}
	if(is_array($v) && count($v) == 1) {
		return $v[0];
	}
	return $v;
}
?>