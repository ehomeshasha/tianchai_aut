<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class paginator {
	
	private $perpage;
	private $page;
	private $start;
	private $limit;
	private $multi;
	private $count;
	private $total_page;
	
	function __construct($count, $url = "", $admin = false, $perpage = null) {
		global $_G;
		$this->count = $count;
		$this->perpage = empty($perpage) ? getgpc('perpage') : $perpage;
		
		if(!empty($this->perpage)) {
			$_SESSION['perpage'] = $this->perpage;
		} else {
			$this->perpage = empty($_SESSION['perpage'])? $_G['aut_settings']['perpage'] : $_SESSION['perpage'];
		}
		
		$this->page = empty($_GET['page'])?0:intval($_GET['page']);
		if($this->page<1) $this->page=1;
		$this->start = ($this->page-1)*$this->perpage;
		
		$this->total_page = ceil($this->count / $this->perpage);
		if($this->total_page < 1) $this->total_page = 1;
		
		if($url == "") {
			$url = AUT_INDEX_PATH."&home=".$_G['aut_controller'];
		}
		if($admin == true) {
			$url .= strpos($url, '?') !== FALSE ? '&amp;' : '?';
			$url .= "admin=1";
		}
		
		if(!$_G['mobile']) {
			$this->limit = "LIMIT $this->start, $this->perpage";
			$this->multi = aut_multi($this->count, $this->perpage, $this->page, $url);
		} else {
			$this->multi = "";
		}
		
		
	}
	function get_total_page() {
		return $this->total_page;
	}
	function get_perpage() {
		return $this->perpage;
	}
	function get_page() {
		return $this->page;
	}
	function get_start() {
		return $this->start;
	}
	function get_limit() {
		return $this->limit;
	}
	function get_count() {
		return $this->count;
	}
	function get_multi() {
		return $this->multi;
	}
	
}