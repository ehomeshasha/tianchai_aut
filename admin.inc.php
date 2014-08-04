<?php 
/**
 *	Author: 张知严    
 *  Date:   2013.4.9
 *  Description: Page Guider后台设置程序文件
 *  Detail: 语言包位置data/plugindata/pageguider.lang.php
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$opt = getgpc('opt');
$opt = in_array($opt, array('add', 'edit', 'close', 'open', 'delete')) ? $opt : 'list';

if($opt == 'list') {
	if(!submitcheck('ordersubmit')) {
		
		$confirmstr = lang('plugin/pageguider','confirmstr');
		$adminscript = ADMINSCRIPT;
		echo "<style type='text/css'>.guider_operation a {margin:0 3px;}</style>";
		echo <<<EOF
<script type="text/javascript">
function confirmdelete(id) {
	if(confirm("{$confirmstr}")) {
		location.href='{$adminscript}?action=plugins&operation=config&identifier=pageguider&pmod=admin&opt=delete&guider_id=' + id;
	}
}
</script>
EOF;
		$query = DB::query("SELECT id,orders,guidername,apppage,isopen,dateline FROM ".DB::table('plugin_pageguider')." WHERE 1 ORDER BY dateline DESC");
		while($value = DB::fetch($query)) {
			$guiderarr[] = $value;
		}
		
		echo "<h3>".lang('plugin/pageguider','pageguiderlist')."</h3>";
		$tdstyle = array('width="32"', '', 'width="100"','width="100"', 'width="120"', 'width="150"');
		showformheader('plugins&operation=config&identifier=pageguider&pmod=admin');
		//echo '<div style="height:30px;line-height:30px;"><a href="javascript:;" onclick="show_all()">'.cplang('show_all').'</a> | <a href="javascript:;" onclick="hide_all()">'.cplang('hide_all').'</a> <input type="text" id="srchforumipt" class="txt" /> <input type="submit" class="btn" value="'.cplang('search').'" onclick="return srchforum()" /></div>';
		showtableheader('', '', 'id="pageguider_header" style="min-width:900px;*width:900px;"');
		showsubtitle(array(lang('plugin/pageguider','orders'), lang('plugin/pageguider','pageguidername'), lang('plugin/pageguider','pageguiderapppage'), lang('plugin/pageguider','status'), lang('plugin/pageguider','createtime'), lang('plugin/pageguider','operation')), 'header', $tdstyle);
		foreach ($guiderarr as $v) {
			echo guider_showrows($v);
		}
		echo '<tbody><tr><td>&nbsp;</td><td colspan="6"><div><a class="addtr" href="'.ADMINSCRIPT.'?action=plugins&operation=config&identifier=pageguider&pmod=admin&opt=add">'.lang('plugin/pageguider','addnewpageguider').'</a></div></td><td colspan="3">&nbsp;</td></tr></tbody>';
		showsubmit('ordersubmit');
		showtablefooter();
		showformfooter();

	} else {
		$orders = getgpc('orders');
		foreach ($orders as $k=>$v) {
			DB::query("UPDATE ".DB::table('plugin_pageguider')." SET `orders`='$v' WHERE id='$k'");
		}
		cpmsg(lang('plugin/pageguider','ordersuccess'), 'action=plugins&operation=config&identifier=pageguider&pmod=admin', 'succeed');
	}
	
} elseif($opt == 'add' || $opt == 'edit') {
	
	if(!submitcheck('addsubmit')) {
		
		$deletestr = "<tr><td><a href='javascript:;' onclick='jQuery(this).parent().parent().remove();'>".lang('plugin/pageguider','delete')."<a></td>";
		echo "<script type='text/javascript' src='source/plugin/pageguider/js/jquery-1.7.2.min.js'></script>";
		$blockstr = "<td><input type='text' name='blockid[]' value='' maxlength='50' /></td><td><input type='text' name='blockclass[]' value='' maxlength='50' /></td><td><input type='text' name='blocktitle[]' value='' maxlength='100' /></td><td><input type='text' name='blockcontent[]' value='' style='width:100%;' maxlength='255' /></td><td><input type='text' name='blockbuttontext[]' value='' maxlength='50' /></td><td><select name='blocklocation[]'><option value='top'>".lang('plugin/pageguider','top')."</option><option value='bottom'>".lang('plugin/pageguider','bottom')."</option></select></td></tr>";
		
		echo <<<EOF
<script type="text/javascript">
jQuery(function(){
	jQuery("#addnewblock").click(function(){
		jQuery("#guiderbox_header").append("$deletestr.$blockstr");
	});
});
</script>
EOF;
		$pageoption = "";
		if($opt == 'add') {
			$navstr = "<h3>".lang('plugin/pageguider','addnewpageguider')."</h3>";
			$actionstr = "plugins&operation=config&identifier=pageguider&pmod=admin&opt=$opt";
			$block_init = "<tr><td></td>".$blockstr;
		} else {
			$id = getgpc('guider_id');
			$guider = DB::fetch_first("SELECT * FROM ".DB::table('plugin_pageguider')." WHERE id='$id'");
			$navstr = "<h3>".lang('plugin/pageguider','editnewpageguider')."</h3>";
			$actionstr = "plugins&operation=config&identifier=pageguider&pmod=admin&opt=$opt&guider_id=$guider[id]";
			$blockdescriptionarr = json_decode($guider['blockdescription'], true); 
			$block_init = "";
			foreach ($blockdescriptionarr as $key => $val) {
				$topstr = $bottomstr = "";
				if($val['blocklocation'] == 'top') { $topstr = "selected='selected'"; } elseif($val['blocklocation'] == 'bottom') { $bottomstr = "selected='selected'"; }
				$blockstr_edit = "<td><input type='text' name='blockid[]' value='".guider_urldecode($val['blockid'])."' maxlength='50' /></td>
					<td><input type='text' name='blockclass[]' value='".guider_urldecode($val['blockclass'])."' maxlength='50' /></td>
					<td><input type='text' name='blocktitle[]' value='".guider_urldecode($val['blocktitle'])."' maxlength='100' /></td>
					<td><input type='text' name='blockcontent[]' value='".guider_urldecode($val['blockcontent'])."' style='width:100%;' maxlength='255' /></td>
					<td><input type='text' name='blockbuttontext[]' value='".guider_urldecode($val['blockbuttontext'])."' maxlength='50' /></td>
					<td><select name='blocklocation[]'><option value='top' $topstr>".lang('plugin/pageguider','top')."</option><option value='bottom' $bottomstr>".lang('plugin/pageguider','bottom')."</option></select>
					</td></tr>"; 
				$block_init .= $deletestr.$blockstr_edit;
				//echo iconv('utf-8', 'gb2312', urldecode($val['blocktitle']));
			}
		}
		echo '<pre>';
		//print_r($guider['appgroups']);
		$usergrouparr = explode(",", $guider['appgroups']);
		$usergroupstr = '<select style="width:256px;" name="appgroups[]" size="10" multiple="multiple"><option value=""'.(@in_array('', $usergrouparr) ? ' selected' : '').'>'.cplang('plugins_empty').'</option>';
		
		$query = C::t('common_usergroup')->range_orderby_credit();
		$groupselect = array();
		foreach($query as $group) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $usergrouparr) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
		}
		print_r($groupselect);
		$usergroupstr .= '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';
		
		
		
		
		$pageoptionArr = array(
							'all' => lang('plugin/pageguider','allpages'),
							'portal' => lang('plugin/pageguider','portal'),
							'forum' => lang('plugin/pageguider','forum'),
							'space' => lang('plugin/pageguider','space'),
							'forumdisplay' => lang('plugin/pageguider','forumdisplay'),
							'viewthread' => lang('plugin/pageguider','viewthread')					
						);
		
		foreach ($pageoptionArr as $k => $v) {
			if($guider['apppage'] == $k) {
				$pageoption .= "<option value='$k' selected='selected'>$v</option>";
			} else {
				$pageoption .= "<option value='$k'>$v</option>";	
			}
		}
		
		$tdstyle = array('width="25"', 'width="70"', 'width="90"', 'width="90"', '', 'width="80"', 'width="80"');
		
		echo $navstr;
		showformheader($actionstr);
		showtableheader('');
		showsetting(lang('plugin/pageguider','pageguidername'), 'guidername', $guider[guidername], 'text');
		showsetting(lang('plugin/pageguider','pageguiderapppage'), '', '', '<select name="apppage">'.$pageoption.'</select>');
		showsetting(lang('plugin/pageguider','pageguiderappurl'), 'appurl', $guider[appurl], 'text','','',lang('plugin/pageguider','appurltips'));
		showsetting(lang('plugin/pageguider','pageguiderappgroups'), '', '', $usergroupstr);
		
		
		
		
		//showsetting(lang('plugin/pageguider','pageguiderappgroups'), '', '', '<select name="appgroups" >'.$pageoption.'</select>');
		
		showtableheader('', '', 'id="guiderbox_header" style="min-width:900px;*width:900px;"');
		echo "<br /><h3>".lang('plugin/pageguider','blocksetting')."</h3>";
		showsubtitle(array(
			'',
			lang('plugin/pageguider','blockid').'<a href="#" title="'.lang('plugin/pageguider','blockidtips').'">[?]</a>',
			lang('plugin/pageguider','blockclass').'<a href="#" title="'.lang('plugin/pageguider','blockclasstips').'">[?]</a>',
			lang('plugin/pageguider','blocktitle').'<a href="#" title="'.lang('plugin/pageguider','blocktitletips').'">[?]</a>',
			lang('plugin/pageguider','blockcontent').'<a href="#" title="'.lang('plugin/pageguider','blockcontenttips').'">[?]</a>',
			lang('plugin/pageguider','blockbuttontext').'<a href="#" title="'.lang('plugin/pageguider','blockbuttontexttips').'">[?]</a>',
			lang('plugin/pageguider','blocklocation').'<a href="#" title="'.lang('plugin/pageguider','blocklocationtips').'">[?]</a>',
			), 'header', $tdstyle);
		echo $block_init;
		showtablefooter();
		echo '<div><a class="addtr" href="javascript:;" id="addnewblock">'.lang('plugin/pageguider','addnewblock').'</a></div><br />';
		showtablefooter();
		showsubmit('addsubmit');
		showformfooter();
	} else {
		
		$guidername = cutstr(addslashes(htmlspecialchars(trim(getgpc('guidername')))), 255);
		$apppage = getgpc('apppage');
		$appurl = getgpc('appurl');
		$appgroups = implode(",", getgpc('appgroups'));
		
		$blockidarr = getgpc('blockid');
		$blockclassarr = getgpc('blockclass');
		$blocktitlearr = getgpc('blocktitle');
		$blockcontentarr = getgpc('blockcontent');
		$blockbuttontextarr = getgpc('blockbuttontext');
		$blocklocationarr = getgpc('blocklocation');
		$blockarr = array();
		foreach ($blocklocationarr as $k => $v) {
			$arr = array($k => array('blockid' => checksqltxt($blockidarr[$k]),
									'blockclass' => checksqltxt($blockclassarr[$k]),
									'blocktitle' => checksqltxt($blocktitlearr[$k]),
									'blockcontent' => checksqltxt($blockcontentarr[$k], false),
									'blockbuttontext' => checksqltxt($blockbuttontextarr[$k]),
									'blocklocation' => $v,
			));
			$blockarr = array_merge($blockarr, $arr);
		}
		//echo '<pre>';
		//print_r($blockarr);
		$blockdescription = json_encode($blockarr);
		if($opt == 'add') {
			$sql = "INSERT INTO ".DB::table('plugin_pageguider')." (guidername,apppage,appurl,appgroups,blockdescription,dateline) VALUES
			('$guidername','$apppage','$appurl','$appgroups','$blockdescription','$_G[timestamp]');";
			$message = lang('plugin/pageguider','addmessage');
		} elseif($opt == 'edit') {
			$id = getgpc('guider_id');
			$sql = "UPDATE ".DB::table('plugin_pageguider')." SET guidername='$guidername',apppage='$apppage',appurl='$appurl',appgroups='$appgroups',blockdescription='$blockdescription' WHERE id='$id'";
			$message = lang('plugin/pageguider','editmessage');
		}
		//echo $sql;
		DB::query($sql);
		guider_delete_cache();
		cpmsg($message, 'action=plugins&operation=config&identifier=pageguider&pmod=admin', 'succeed');
	}
	
} elseif($opt == 'open' || $opt == 'close' || $opt == 'delete') {
	
	$id = getgpc('guider_id');
	if($opt == 'open') {
		$sql = "UPDATE ".DB::table('plugin_pageguider')." SET `isopen`=1 WHERE id='$id'";	
	} elseif($opt == 'close') {
		$sql = "UPDATE ".DB::table('plugin_pageguider')." SET `isopen`=0 WHERE id='$id'";
	} elseif($opt == 'delete') {
		$sql = "DELETE FROM ".DB::table('plugin_pageguider')." WHERE id='$id'";
	}
	DB::query($sql);
	guider_delete_cache();
	cpmsg(lang('plugin/pageguider','operateback'), 'action=plugins&operation=config&identifier=pageguider&pmod=admin', 'succeed');
}







/*入库前进行处理,可设置是否进行html转义*/
function checksqltxt($str, $dohtmlspecialchars = true) {
	$str = trim($str);
	if($dohtmlspecialchars) {
		if(!get_magic_quotes_gpc()) {
			return guider_urlencode(addslashes(htmlspecialchars($str)));
		}
		return guider_urlencode(htmlspecialchars($str));
	} else {
		if(!get_magic_quotes_gpc()) {
			return guider_urlencode(addslashes($str));
		}
		return guider_urlencode($str);
	}
}
/*对中文字符进行utf8转换并进行urlencode编码*/
function guider_urlencode($str) {
	return urlencode(iconv('gb2312', 'utf-8', $str));
}
/*对url进行解码并转换成gbk*/
function guider_urldecode($str) {
	return iconv('utf-8', 'gb2312', urldecode($str));
}
/*返回page guider table row的html代码*/
function guider_showrows($v) {
	$str = "<tr>";
	if($v['isopen'] == 0) {
		$str .= "<td><input type='text' name='orders[$v[id]]' value='$v[orders]' style='width:20px'/></td><td><span style='color:#aaa;'>$v[guidername]</span></td>";
	} elseif($v['isopen'] == 1) {
		$str .= "<td><input type='text' name='orders[$v[id]]' value='$v[orders]' style='width:20px'/></td><td>$v[guidername]</td>";
	}
	$str .= "<td>$v[apppage]</td>";
	if($v['isopen'] == 1) {
		$str .= "<td>".lang('plugin/pageguider','open')."</td>";
	} elseif($v['isopen'] == 0) {
		$str .= "<td>".lang('plugin/pageguider','close')."</td>";
	}
	$str .= "<td>".dgmdate($v['dateline'], "Y-m-d H:j")."</td>";
	$str .= "<td class='guider_operation'>
				<a href='".ADMINSCRIPT."?action=plugins&operation=config&identifier=pageguider&pmod=admin&opt=edit&guider_id=$v[id]'>".lang('plugin/pageguider','edit')."</a>";
	if($v['isopen'] == 1) {
		$str .= "<a href='".ADMINSCRIPT."?action=plugins&operation=config&identifier=pageguider&pmod=admin&opt=close&guider_id=$v[id]'>".lang('plugin/pageguider','close')."</a>";
	} elseif($v['isopen'] == 0) {
		$str .= "<a href='".ADMINSCRIPT."?action=plugins&operation=config&identifier=pageguider&pmod=admin&opt=open&guider_id=$v[id]'>".lang('plugin/pageguider','open')."</a>";
	}
	$str .= "	<a href='javascript:;' onclick='confirmdelete(\"$v[id]\");'>".lang('plugin/pageguider','delete')."</a>
			</td>";
	$str .= "</tr>";
	return $str;
}
/*删除缓存文件*/
function guider_delete_cache() {
	$arr = getFileList("/var/www/wh/data/cache/");
	foreach ($arr as $v) {
		if(strpos($v['name'], "guider_cache_") === false) continue;
		unlink($v['path']);
	}
}
/**
 * 获取指定路径下的文件列表，如果第二个参数为true，
 * 则会递归的列出子目录下的文件
 * @param String $dir 目录
 * @param String $recursion
 */
function getFileList($dir, $recursion = false){
    $filelist = array();
    $real_path = realpath($dir);
    if (is_dir($real_path)) {
        if ($dh = opendir($real_path)) {
            while (($file = readdir($dh)) !== false) {
                if (strpos($file, '.') === 0) {
                    continue;
                }
                $full_path = $real_path . DIRECTORY_SEPARATOR . $file;
                $filetype = filetype($full_path);
                $is_dir = $filetype == 'dir';
                $relative_path = str_ireplace(BASEPATH, '', $full_path);
                $relative_path = str_replace('', '/', $relative_path);
                $filelist[] = array(
                    'name'=>$file,
                    'path'=>$full_path,
                    'relative_path'=>$relative_path,
                    'is_dir'=>$is_dir,
                );
                if($is_dir == true && $recursion == true){
                    $subdir = getFileList($real_path . DIRECTORY_SEPARATOR . $file, true);
                    $filelist = array_merge($filelist, $subdir);
                }
            }
            closedir($dh);
        }
    }
    return $filelist;
}
?>