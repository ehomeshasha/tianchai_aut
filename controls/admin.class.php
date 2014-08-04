<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class admin_controller {

	public function __construct() {
		allow_admin();
	}

	public function index_action() {


	}

	public function bulkemail_action() {
		global $_G;
		
		
		if($_POST['submit'] != "true") {
				
			include template("aut:common/header");
			include template("aut:bulkemail_post");
			include template("aut:common/footer");
				
		} else {
			$curtime = dgmdate(time(), "Ymd_His");
			header('Content-Description: File Transfer');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Pragma: no-cache");
			header("Expires: 0");
			header('Content-type: text/html;charset=UTF-8');
			header('Content-Disposition: attachment; filename="BulkEmailInfomation-'.$curtime.'.htm"');
			
			
			
			$sender = "3982843517@qq.com";
			
			$title = trim(getgpc('title'));
			$content = getgpc('content');
			$start = getgpc('start');
			$end = getgpc('end');
			if(!is_numeric($start) || !is_numeric($end)) {
				showresult(-1, lang('plugin/aut', 'startnum_or_endnum_is_invalid'), $_G['aut_ajax']);
			}

			$db = getAhaleiDB();
			$users = $db->fetch_all("SELECT email FROM `pre_ucenter_members` WHERE uid>=$start AND uid<=$end");
			//$users = array(0=>array('email'=>'693946948@qq.com'),1=>array('email'=>'ehomeshasha@msn.com'));
			$i = 0;
			foreach ($users as $k=>$user) {
				$j = floor($k/50);
				if($j > $i) {
					$i = $j;
				}
				$data[$i][] = $user['email']; 
			}
			echo '<pre>';
			echo "<h3>Title</h3>";
			echo $title;
			echo "<h3>Content</h3>";
			echo $content;
			echo "<h3>Uid Range</h3>";
			echo $start."~".$end;
			echo "<h3>Email From</h3>";
			echo $sender;
			
			foreach($data as $key => $value) {
				$emailto = $value[0];
				unset($value[0]);
				$bcc = implode(",", $value);	
				$headers = "From: {$sender}\n";
				$headers .= "Bcc: {$bcc}\n";
				$headers .= "X-Sender: Support@www.ahalei.com\n";
				$headers .= "Content-type: text/html; charset=UTF-8\n";
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-Transfer-Encoding: 7bit\n";

				echo "<h3>Email To</h3>";
				echo $emailto;
				echo "<h3>Header Information</h3>";
				echo "<pre>$headers</pre>";	
				//print_r($value);
				mail($emailto, $title, $content, $headers);
			}
			exit();
		}
	}
}









?>