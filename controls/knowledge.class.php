<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class knowledge_controller {

	
	
	
	public function view_action() {
		
		require DISCUZ_ROOT."./source/function/function_discuzcode.php";
		global $_G;
		$cid = addslashes(getgpc('cid'));
		$knowledge = DB::fetch_first("SELECT content FROM ".DB::table('aut_knowledge')." WHERE cid='$cid'");
		$knowledge['content'] = aut_decode(discuzcode($knowledge['content'], 0, 0));
		
		
		if(empty($_G['aut_ajax'])) include template("aut:common/header");
		include template("aut:knowledge_view");
		if(empty($_G['aut_ajax'])) include template("aut:common/footer");
		
	}
	
	
}






?>