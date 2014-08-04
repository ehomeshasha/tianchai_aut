<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}




class problem_controller {
	
	private $maxexample = 10;
	private $editorid = 'e';
	
	
	public function index_action() {
		
		global $_G;
		include_once AUT_PATH."/inc/paginator.class.php";
		$where = "1=1";
		$rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `problem` WHERE {$where}");
		
		$paginator = new paginator($rs['count'], "", true);
		$perpage = $paginator->get_perpage();
		$limit = $paginator->get_limit();
		$multi = $paginator->get_multi();
				
		$query = DB::query("SELECT * FROM `problem` WHERE {$where} ORDER BY problem_id ASC $limit");
		$problemlist = array();
		while($value = DB::fetch($query)) {
			$value['category'] = $_G['aut_cache']['category'][$value['category']]['name']; 
			$value['module'] = $_G['ArrayData']['module_map'][$value['ptype']];
			$problemlist[] = $value; 
		}
		
		$competition_where = "startdatetime<=$_G[timestamp] AND enddatetime>=$_G[timestamp]";
		$competition_count = getcount('aut_competition', $competition_where);
		if($competition_count > 0) {
			
			$query = DB::query("SELECT id FROM ".DB::table('aut_competition')." WHERE {$competition_where} ORDER BY dateline DESC");
			$competition_idsArr = array();
			while($value = DB::fetch($query)) {
				$competition_idsArr[] = $value['id']; 
			}
			$competition_ids = implode(",", $competition_idsArr);
		}
		
		
		
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('problem_list', 0, AUT_ADMIN_TEMPLATE_DIR);
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
		$type = getgpc('type');
		$typeArr = array('challenge', 'try', 'tiku');
		if(empty($type) || !in_array($type, $typeArr)) {
			$type = 'challenge';
		}
		
		if(!submitcheck('submit')) {
			
			$time_limit = 1;
			$memory_limit = 128;
			
			$editorid = $this->editorid;
			$_G['setting']['editoroptions'] = str_pad(decbin($_G['setting']['editoroptions']), 2, 0, STR_PAD_LEFT);
			$editormode = $_G['setting']['editoroptions']{0};
			$allowswitcheditor = $_G['setting']['editoroptions']{1};
			$editor = array(
				'editormode' => $editormode,
				'allowswitcheditor' => $allowswitcheditor,
				'allowhtml' => 1,
				'allowsmilies' => 1,
				'allowbbcode' => 1,
				'allowimgcode' => 1,
				'allowcustombbcode' => 0,
				'allowresize' => 1,
				'textarea' => 'problem_description',
				'simplemode' => !isset($_G['cookie']['editormode_'.$editorid]) ? 1 : $_G['cookie']['editormode_'.$editorid],
			);
			
			loadcache('bbcodes_display');
			
			if($op == 'edit') {
				$pid = addslashes(getgpc('pid'));
				$problem = DB::fetch_first("SELECT * FROM `problem` WHERE problem_id=$pid");
				$groupid = $problem['groupid'];
				$discuz_editor_content = dhtmlspecialchars($problem['description']);
				$time_limit = $problem['time_limit'];
				$memory_limit = $problem['memory_limit'];
			} else {
				$rs = DB::fetch_first("SELECT problem_id FROM `problem` WHERE ptype='$type' ORDER BY problem_id DESC");
				if($type == 'challenge') {
					$problem['problem_id'] = empty($rs) ? 1000 : intval($rs['problem_id']) + 1;
				} elseif($type == 'try') {
					$problem['problem_id'] = empty($rs) ? 9000 : intval($rs['problem_id']) + 1;
					$problem['title'] = lang('plugin/aut', 'try');
				} elseif($type == 'tiku') {
					$problem['problem_id'] = empty($rs) ? 10000 : intval($rs['problem_id']) + 1;
				}
				 
				$problem['ptype'] = $type;
			}
			
			
			if($type == 'challenge' || $type == 'try') {
				$category_html = init_category($_G['aut_cache']['categorytree']['learning'], $problem[category]);
			} elseif($type == 'tiku') {
				$categoryname = $catename_1 = $catename_2 = lang('plugin/aut', 'not_select_text');
				if(!empty($problem['category'])) $categoryname = $_G['aut_cache']['category'][$problem['category']]['name'];
				if(!empty($problem['cateid_1'])) $catename_1 = getcatename($problem['cateid_1']);
				if(!empty($problem['cateid_2'])) $catename_2 = getcatename($problem['cateid_2']);
			}
			
			
			
			//$examplehtml = initexample($problem['example'], $this->maxexample, $pid);
			$usergroupstr = initusergroup($groupid, $lang);
			
			include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			include template('problem_post', 0, AUT_ADMIN_TEMPLATE_DIR);
			include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			
		} else {
			set_time_limit(0);
			
			chkLength(lang('plugin/aut', 'problem_title'), trim(getgpc('title')), 0, 200);
			chkDigit(lang('plugin/aut', 'problem_id'), trim(getgpc('problem_id')), 0, 11);
			chkDigit(lang('plugin/aut', 'problem_timelimit'), trim(getgpc('timelimit')), 0, 11);
			chkDigit(lang('plugin/aut', 'problem_memorylimit'), trim(getgpc('memorylimit')), 0, 11);
			validation_start();
			
			
			$title = addslashes(trim(getgpc('title')));
			//$name = addslashes(getgpc('name'));
			$category = addslashes(getgpc('category'));
			$problem_id = addslashes(trim(getgpc('problem_id')));
			$cateid_1 = addslashes(getgpc('cateid_1'));
			$cateid_2 = addslashes(getgpc('cateid_2'));
			$http_referer = getgpc('http_referer');
			
			
			$rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `problem` WHERE `problem_id`='$problem_id'");
			if($rs['count'] != 0 && $op == 'new') showresult(-1, lang('plugin/aut', 'problemid_duplicated'), $_G['aut_ajax']);
			
			$groupidArr = getgpc('usergroup');
			$groupid = global_addslashes((implode(",", $groupidArr)));
			$timelimit = addslashes(trim(getgpc('timelimit')));
			$memorylimit = addslashes(trim(getgpc('memorylimit')));
			$defunct = 'Y';
			$ptype = addslashes(getgpc('ptype'));
			
			$description = trim(getgpc('problem_description'));
			
			$input = addslashes(trim(getgpc('input')));
			$output = addslashes(trim(getgpc('output')));
			$hint = addslashes(trim(getgpc('hint')));
			$restrict = addslashes(trim(getgpc('restrict')));
			$example_shownum = addslashes(getgpc('example_shownum'));
			
			$exampledisplay = array();
			for($l=0;$l<3;$l++) {
				if(getgpc('exampledisplay'.$l.'_inputformat') == "" || getgpc('exampledisplay'.$l.'_outputformat') == "") {
					continue;
				}
				$exampledisplay_inputformat = iconv("gb2312", "utf-8//IGNORE", preg_replace("/\r{0,1}\n/", '\n', getgpc('exampledisplay'.$l.'_inputformat')));
				$exampledisplay_outputformat = iconv("gb2312", "utf-8//IGNORE", preg_replace("/\r{0,1}\n/", '\n', getgpc('exampledisplay'.$l.'_outputformat')));
				$exampledisplay_explanation = iconv("gb2312", "utf-8//IGNORE", preg_replace("/\r{0,1}\n/", '\n', getgpc('exampledisplay'.$l.'_explanation')));
					
				$exampledisplay[$l]['input'] = $exampledisplay_inputformat;
				$exampledisplay[$l]['output'] = $exampledisplay_outputformat;
				$exampledisplay[$l]['explanation'] = $exampledisplay_explanation;
			}
			
			$exampledisplay_str = empty($exampledisplay) ? "" : str_replace('\u', '\\\u', json_encode($exampledisplay));
			
			$input_method = addslashes(getgpc('input_method'));
			$example = array();
			$example_ischange = true;
			
			
			$basedir = $_G['OJ_DATA']."/".$problem_id;
			if(!($input_method == 'file' && empty($_FILES['exampleupload']['name'][0]))) {
				removeDir($basedir);
			}
			mkdir($basedir);
			
			if($input_method == 'file' && !empty($_FILES['exampleupload']['name'][0])) {
				//echo '<pre>';
				//print_r($_FILES);
				
				
				
				$uploadArr = array();
				foreach($_FILES['exampleupload']['name'] as $k=>$name) {
					if(strpos($name, ".in") !== false) {
						$keyname = "input";
					} elseif(strpos($name, ".out") !== false) {
						$keyname = "output";
					} else {
						showresult(-1, lang('plugin/aut', 'invalid_upload_file_suffix'), $_G['aut_ajax']);
					}
					$nm = preg_replace("/\.(in|out)/", "", $name);
					$example[$nm][$keyname] = $name;
					$example[$nm]['explanation'] = "";
					move_uploaded_file($_FILES['exampleupload']['tmp_name'][$k], $basedir."/".$name);
					//$content = preg_replace("/\r{0,1}\n/", '\n', file_get_contents($_FILES['exampleupload']['tmp_name'][$k]));
				}
				$example = array_values($example);
				
			} elseif($input_method == 'text') {
				for($i=1;$i<=$this->maxexample;$i++) {
					$j = $i-1;
					/*
					$subexample = array(
							'inputformat' => preg_replace("/\r{0,1}\n/", '\n', addslashes(getgpc('example'.$i.'_inputformat'))),
							'outputformat' => preg_replace("/\r{0,1}\n/", '\n', addslashes(getgpc('example'.$i.'_outputformat'))),
							'explanation' => preg_replace("/\r{0,1}\n/", '\n', addslashes(getgpc('example'.$i.'_explanation'))),
					);
					array_push($example, $subexample);*/
					$inputformat = iconv("gb2312", "utf-8//IGNORE", preg_replace("/\r{0,1}\n/", '\n', getgpc('example'.$i.'_inputformat')));
					$outputformat = iconv("gb2312", "utf-8//IGNORE", preg_replace("/\r{0,1}\n/", '\n', getgpc('example'.$i.'_outputformat')));
					$explanation = iconv("gb2312", "utf-8//IGNORE", preg_replace("/\r{0,1}\n/", '\n', getgpc('example'.$i.'_explanation')));
					if(strlen($outputformat)&&!strlen($inputformat)) $inputformat="0";
					if(strlen($inputformat)) {
						mkdata($problem_id,"test_$j.in",$inputformat,$_G['OJ_DATA']);
					} else {continue;}
					if(strlen($outputformat)) {
						mkdata($problem_id,"test_$j.out",$outputformat,$_G['OJ_DATA']);
					} else {continue;}
					
					$example[$j]['input'] = "test_$j.in";
					$example[$j]['output'] = "test_$j.out";
					//$example[$j]['explanation'] = $explanation;
					
				}
			} elseif($input_method == 'file') {
				$example_ischange = false;
			} else {
				showresult(-1, lang('plugin/aut', 'invalid_input_method'), $_G['aut_ajax']);
			}
			
			
			
			//echo '<pre>';
			//print_r($example);
			$example_str = str_replace('\u', '\\\u', json_encode($example));
			
			//$sample_input = $example[0]['inputformat'];
			//$sample_output = $example[0]['outputformat'];
			
			
			
			$curtime = $_G['timestamp'];
			$curdate = dgmdate($curtime, "Y-m-d H:i:s"); 
			
			
			$data = array(
						'title' => $title,
						'category' => $category,
						'problem_id' => $problem_id,
						'description' => $description,
						'input' => $input,
						'output' => $output,
						'hint' => $hint,
						'restrict' => $restrict,
						//'sample_input' => $sample_input,
						//'sample_output' => $sample_output,
						'cateid_1' => $cateid_1,
						'cateid_2' => $cateid_2,
						'example' => $example_str,
						'example_shownum' => $example_shownum,
						'exampledisplay' => $exampledisplay_str,
						'groupid' => $groupid,
						'dateline' => $curtime,
						'in_date' => $curdate,
						'defunct' => $defunct,
						'time_limit' => $timelimit,
						'memory_limit' => $memorylimit,
						'lastedit' => $curtime,
						'authorid' => $_G['uid'],
						'author' => $_G['username'],
						'ptype' => $ptype,
					);
			
			//spj
			$spj = addslashes(getgpc('spj'));
			
			if($example_ischange == false) {
				//unset($data['sample_input']);
				//unset($data['sample_output']);
				unset($data['example']);
			}
			
			if($op == 'new') {
				if($spj == 1 && !empty($_FILES['spj_upload']['name'])) {
					move_uploaded_file($_FILES['spj_upload']['tmp_name'], $basedir."/spj.cc");
					
				}
				$data['spj'] = $spj;
				
				
				$insertsql = get_insertsql('problem', $data);
				DB::query($insertsql);
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(sprintf(lang('plugin/aut', 'addproblem_p'), $problem_id)));
			} elseif($op == 'edit') {
				
				if($spj == 0) {
					//remove if exist
					if(file_exists($basedir."/spj.cc")) {
						unlink($basedir."/spj.cc");
					}
				} else if($spj == 1 && !empty($_FILES['spj_upload']['name'])) {
					move_uploaded_file($_FILES['spj_upload']['tmp_name'], $basedir."/spj.cc");
				}
				
				
				$data['spj'] = $spj;
				
				$pid = addslashes(getgpc('pid'));
				unset($data['in_date']);
				unset($data['dateline']);
				unset($data['authorid']);
				unset($data['author']);
				$updatesql = get_updatesql('problem', $data, "problem_id=".$pid);
				DB::query($updatesql);
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(sprintf(lang('plugin/aut', 'editproblem_p'), $problem_id)));
				
			}
			//echo '<pre>';
			//print_r($_FILES);
			
			if($spj == 1 && file_exists($basedir."/spj")) {
				unlink($basedir."/spj");
			}
			
			
			jumpto_list_page($http_referer);
			
		}
	}
	
	public function delete_action() {
		
		global $_G;
		$pid = addslashes(getgpc('pid'));
		DB::query("DELETE FROM `problem` WHERE problem_id=$pid");
		$_SESSION['aut_message'] = array('code' => 1, 'content' => array(sprintf(lang('plugin/aut', 'deleteproblem_p'), $pid)));
	}
	
	public function view_action() {
		require DISCUZ_ROOT."./source/function/function_discuzcode.php";
		global $_G;
		
		
		
		$scope = getgpc('scope');
		
		$curlanguage = 'C';
		$pid = addslashes(getgpc('pid'));
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
		
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('problem_view', 0, AUT_ADMIN_TEMPLATE_DIR);
		include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		
	}
}
?>