<?php
/*
	[Phpup.Net!] (C)2009-2011 Phpup.net.
	This is NOT a freeware, use is subject to license terms

	$Id: mysql.class.php 2010-08-24 10:42 $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
//mysql操作类
class mysql {

	var $version = ''; //mysql版本

	var $querynum = 0; //查询次数

	var $link = null;	//连接
	
	var $lastsql = ''; //最后执行的sql

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $halt = TRUE, $dbcharset2 = '') {

		$func = empty($pconnect) ? 'mysql_connect' : 'mysql_pconnect';
		if(!$this->link = @$func($dbhost, $dbuser, $dbpw, 1)) {
			$halt && $this->halt('Can not connect to MySQL server');
		} else {
			if($this->version() > '4.1') {
				global $charset, $dbcharset;
				$dbcharset = $dbcharset2 ? $dbcharset2 : $dbcharset;
				$dbcharset = !$dbcharset && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8')) ? str_replace('-', '', $charset) : $dbcharset;
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $this->link);
			}
			$dbname && @mysql_select_db($dbname, $this->link);
		}

	}
	
	//选择数据库
	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}
	
	//返回sql执行的结果
	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}
	
	//返回sql执行的结果
	function fetch_all($sql) {
		 $query= $this->query($sql);
		 if(!$query)	
		 	return NULL;
		 while($r = mysql_fetch_assoc($query)){
		 	$rs[]=$r;
		 }
		 return $rs;
	}	

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}
	
	//查询
	function query($sql, $type = '') {


		$this->lastsql = $sql;

		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ?
			'mysql_unbuffered_query' : 'mysql_query';

		if(!($query = $func($sql, $this->link))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->close();
				require ROOT_PATH.'/inc/config.inc.php';
				$this->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
				return $this->query($sql, 'RETRY'.$type);
			} elseif($type != 'SILENT' && substr($type, 5) != 'SILENT') {

				//$this->halt('MySQL Query Error', $sql);
			}
		}

		$this->querynum++;
		return $query;
	}
	
	//返回最后执行的sql
	function getLastSql() {
		return $this->lastsql;
	}
	
	//返回sql影响的行数
	function affected_rows() {
		return mysql_affected_rows($this->link);
	}
	
	//错误字符串提示
	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}
	
	//错误code
	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}
	
	
	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}
	
	function list_fields($query) {
		$fields=array();
		$columns = mysql_num_fields($query);
		for ($i = 0; $i < $columns; $i++) {
			$fields[]=mysql_field_name($query, $i);
		}
		return $fields;
	}

	function version() {
		if(empty($this->version)) {
			$this->version = mysql_get_server_info($this->link);
		}
		return $this->version;
	}

	function close() {
		return mysql_close($this->link);
	}
	
	function __destruct() {
		$this->close();
	}
	
	function halt($message = '', $sql = '') {
		$timestamp=time();
		if($message) {
		$errmsg = "<b>PHPUP info</b>: $message<br/>";
		}
		$errmsg .= "<b>Time</b>: ".gmdate("Y-n-j g:ia", $timestamp + ($GLOBALS['timeoffset'] * 3600))."<br/>";
		$errmsg .= "<b>Script</b>: ".$GLOBALS['PHP_SELF']."<br/>";
		if($sql) {
			$errmsg .= "<b>SQL</b>: ".htmlspecialchars($sql)."<br/>";
		}
		$errmsg .= "<b>Error</b>:  ".$this->error()."<br/>";
		$errmsg .= "<b>Errno.</b>:  ".$this->errno();

		echo $errmsg;
		//exit($errmsg);
	}

	function escapeval($val) {
		if(is_numeric($val)) {
			return $val;
		} elseif(is_string($val)) {
			return "'".$val."'";
		} else if(is_null($value)) {
			return 'null';
		}
	}

	function escapekey($key) {
		return '`'.$key.'`';
	}
	
}

?>
