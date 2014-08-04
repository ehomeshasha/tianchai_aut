<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class knowledge_controller {

	private $editorid = 'e';
	
	

	public function index_action() {
		
		global $_G;
		
		include_once AUT_PATH."/inc/paginator.class.php";
		
		$count = getcount("aut_knowledge", "1=1");
		
		$paginator = new paginator($count, "", true);
		$perpage = $paginator->get_perpage();
		$limit = $paginator->get_limit();
		$multi = $paginator->get_multi();
		
		$query = DB::query("SELECT a.kid,a.content,b.name FROM ".DB::table('aut_knowledge')." AS a LEFT JOIN ".DB::table('aut_category')." AS b ON a.cid=b.cid WHERE 1 ORDER BY a.kid ASC");
		while($value = DB::fetch($query)) {
			$value['content'] = cutstr($value['content'], 500);
			$knowledge_list[] = $value; 
		}
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('knowledge_list', 0, AUT_ADMIN_TEMPLATE_DIR);
		include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		
	}
	
	public function post_action() {
		
		
		global $_G;
		$op = getgpc('op');
		$opArr = array('new', 'edit');
		if(empty($op) || !in_array($op, $opArr)) {
			$op = 'new';
		}
		
		
		if(!submitcheck('submit')) {
			
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
					'textarea' => 'knowledge_content',
					'simplemode' => !isset($_G['cookie']['editormode_'.$editorid]) ? 1 : $_G['cookie']['editormode_'.$editorid],
			);
				
			loadcache('bbcodes_display');
				
			if($op == 'edit') {
				$kid = addslashes(getgpc('kid'));
				$knowledge = DB::fetch_first("SELECT kid,content,cid FROM ".DB::table('aut_knowledge')." WHERE kid=$kid");
				$discuz_editor_content = dhtmlspecialchars($knowledge['content']);
			}
				
			$category_html = init_category($_G['aut_cache']['categorytree']['learning'], $knowledge['cid']);
			
			include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			include template('knowledge_post', 0, AUT_ADMIN_TEMPLATE_DIR);
			include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			
			
		} else {
			
			chkEmpty(lang('plugin/aut', 'knowledge_content'), trim(getgpc('knowledge_content')));
			validation_start();
			
			$category = addslashes(getgpc('category'));
			$content = addslashes(censor(trim(getgpc('knowledge_content'))));
			$http_referer = getgpc('http_referer');
			
			
			$curtime = $_G['timestamp'];
			
			if($op == 'new') {
				
				DB::query("INSERT INTO ".DB::table('aut_knowledge')." (cid,content,dateline) VALUES ('$category','$content','$curtime')");
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'addknowledge_success')));
				
			} elseif($op == 'edit') {
				$kid = addslashes(getgpc('kid'));
				DB::query("UPDATE ".DB::table('aut_knowledge')." SET cid='$category',content='$content' WHERE kid=$kid");
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'updateknowledge_success')));
			}
			
			jumpto_list_page($http_referer);
			
		}
	}
	
	public function delete_action() {
		
		global $_G;
		$kid = addslashes(getgpc('kid'));
		DB::query("DELETE FROM ".DB::table('aut_knowledge')." WHERE kid=$kid");
		$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'deleteknowledge_success')));
		
	}
		
}






?>