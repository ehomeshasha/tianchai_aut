<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class release_controller {

	private $editorid = 'e';
	
	

	public function index_action() {
		
		global $_G;
		
		include_once AUT_PATH."/inc/paginator.class.php";
		
		$count = getcount("aut_release", "1=1");
		
		$paginator = new paginator($count, "", true);
		$perpage = $paginator->get_perpage();
		$limit = $paginator->get_limit();
		$multi = $paginator->get_multi();
		
		$query = DB::query("SELECT * FROM ".DB::table('aut_release')." WHERE 1 ORDER BY dateline DESC");
		while($value = DB::fetch($query)) {
			$value['description'] = nl2br($value['description']);
			$release_list[] = $value; 
		}
		
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('release_list', 0, AUT_ADMIN_TEMPLATE_DIR);
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
					'textarea' => 'release_content',
					'simplemode' => !isset($_G['cookie']['editormode_'.$editorid]) ? 1 : $_G['cookie']['editormode_'.$editorid],
			);
				
			loadcache('bbcodes_display');
				
			if($op == 'edit') {
				$rid = addslashes(getgpc('rid'));
				$release = DB::fetch_first("SELECT * FROM ".DB::table('aut_release')." WHERE id=$rid");
				$discuz_editor_content = dhtmlspecialchars($release['content']);
			}
				
			include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			include template('release_post', 0, AUT_ADMIN_TEMPLATE_DIR);
			include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
			
			
		} else {
			chkLength(lang('plugin/aut', 'title'), trim(getgpc('title')), 0, 255);
			chkLength(lang('plugin/aut', 'brief'), trim(getgpc('description')), 0, 255);
			chkEmpty(lang('plugin/aut', 'release_content'), trim(getgpc('release_content')));
			validation_start();
			
			$title = addslashes(trim(getgpc('title')));
			$description = addslashes(trim(getgpc('description')));
			$content = addslashes(censor(trim(getgpc('release_content'))));
			$http_referer = getgpc('http_referer');
			
			
			$curtime = $_G['timestamp'];
			
			
			$data = array(
				'title' => $title,
				'description' => $description,
				'content' => $content,
				'dateline' => $curtime,
			);
			
			
			if($op == 'new') {
				
				$data['authorid'] = $_G['uid'];
				$data['author'] = $_G['username'];
				
				DB::insert('aut_release', $data);
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'addrelease_success')));
				
			} elseif($op == 'edit') {
				$rid = addslashes(getgpc('rid'));
				DB::update('aut_release', $data, "id='$rid'");
				
				$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'updaterelease_success')));
			}
			jumpto_list_page($http_referer);
		}
	}
	
	public function view_action() {
		require DISCUZ_ROOT."./source/function/function_discuzcode.php";
		
		global $_G;
		$rid = addslashes(getgpc('rid'));
		$release = DB::fetch_first("SELECT * FROM ".DB::table('aut_release')." WHERE id=$rid");
		
		jump_if_empty($release);

		$release['description'] = nl2br($release['description']);
		$release['content'] = aut_decode(discuzcode($release['content'], 0, 0));
	
		include template('header', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
		include template('release_view', 0, AUT_ADMIN_TEMPLATE_DIR);
		include template('footer', 0, AUT_ADMIN_COMMON_TEMPLATE_DIR);
	}
	
	
	
	
	
	
	
	public function delete_action() {
		
		global $_G;
		$rid = addslashes(getgpc('rid'));
		DB::query("DELETE FROM ".DB::table('aut_release')." WHERE id=$rid");
		$_SESSION['aut_message'] = array('code' => 1, 'content' => array(lang('plugin/aut', 'deleterelease_success')));
		
	}
		
}






?>