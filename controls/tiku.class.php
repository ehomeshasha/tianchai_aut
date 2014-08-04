<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class tiku_controller {

	public function __construct() {

	}

	public function index_action() {
		global $_G;
		
		include_once AUT_PATH."/inc/paginator.class.php";
		
		$_G['breadcrumb'] = array(
				array('text' => lang('plugin/aut', 'homepage'), 'href' => AUT_INDEX_PATH),
				array('text' => lang('plugin/aut', 'tiku')),
		);
		
		
		$category = addslashes(getgpc('category'));
		$cateid_1 = addslashes(getgpc('cateid_1'));
		$cateid_2 = addslashes(getgpc('cateid_2'));
		
		
		$where = "ptype='tiku' AND problem_id<20000";
		$where .= empty($category) ? "" : " AND category='$category'";
		$where .= empty($cateid_1) ? "" : " AND cateid_1 LIKE '%,{$cateid_1},%'";
		$where .= empty($cateid_2) ? "" : " AND cateid_2 LIKE '%,{$cateid_2},%'";
		
		$count_rs = DB::fetch_first("SELECT COUNT(*) AS count FROM `problem` WHERE {$where}");
		$paginator = new paginator($count_rs['count']);
		$perpage = $paginator->get_perpage();
		$limit = $paginator->get_limit();
		$multi = $paginator->get_multi();
		
		
		$query = DB::query("SELECT id,problem_id,title,accepted,submit,category,ptype FROM `problem` WHERE {$where} ORDER BY problem_id ASC $limit");
		$problemlist = array();
		while($value = DB::fetch($query)) {
			$problemlist[] = $value;
		}
		
		
		include template("aut:common/header");
		include template("aut:tiku_list");
		include template("aut:common/footer");
		
	}
	
	
	public function tiku_category_action() {
		
		global $_G;
		$op = getgpc('op');
		$opArr = array('new', 'edit');
		if(empty($op) || !in_array($op, $opArr)) {
			$op = 'new';
		}
		$cateidArr_1 = $cateidArr_2 = array();
		if($op == 'edit') {
			$pid = addslashes(getgpc('pid'));
			$problem = DB::fetch_first("SELECT cateid_1,cateid_2,category FROM `problem` WHERE problem_id='$pid'");
			$cateidArr_1 = explode(",", substr($problem['cateid_1'], 1));
			$cateidArr_2 = explode(",", substr($problem['cateid_2'], 1));
		}
		$contest_category_text = lang('plugin/aut', 'contest_category_text');
		$first_category_text = lang('plugin/aut', 'first_category_text');
		$second_category_text = lang('plugin/aut', 'second_category_text');
		$not_select_text = lang('plugin/aut', 'not_select_text');
		
		$submit_javascript = <<<EOF
<script type="text/javascript">
jQuery(function(){
	jQuery("#response_modal").on('hidden', function () {
		jQuery("#modal_form_post_btn").trigger('click');
	});
	jQuery("#modal_form_post_btn").click(function(){
		
		var cateid_1 = cateid_2 = catename_1 = catename_2 = categoryname = "";
		jQuery(".cateid_2_checkbox:checked").each(function(){
			cateid_2 += ","+jQuery(this).val();
			catename_2 += ","+jQuery(this).next().html();
			var parent = jQuery(this).parent().parent().siblings(".cateid_1_checkbox");
			parent.attr("checked", "checked");
		});
		jQuery(".cateid_1_checkbox:checked").each(function(){
			cateid_1 += ","+jQuery(this).val();
			catename_1 += ","+jQuery(this).next().html();
		});
		jQuery("#category").val(jQuery(".cate_radiobox:checked").val());
		jQuery("#cateid_1").val(cateid_1+",");
		jQuery("#cateid_2").val(cateid_2+",");
		jQuery(".dismiss_btn").trigger("click");
		categoryname = jQuery(".cate_radiobox:checked").next().html();
		categoryname = categoryname == null ? "$not_select_text" : categoryname;
		catename_1 = catename_1 == "" ? "$not_select_text" : catename_1.substr(1, catename_1.length-1);
		catename_2 = catename_2 == "" ? "$not_select_text" : catename_2.substr(1, catename_2.length-1); 
		jQuery(".categroy_info").html("<span class='contest_category_info mrm'>$contest_category_text: "+categoryname+"</span><br/><span class='first_category_info mrm'>$first_category_text: "+catename_1+"</span><br/><span class='second_category_info'>$second_category_text: "+catename_2+"</span>")
		jQuery("#response_modal").modal('hide');
	});
	jQuery("#modal_form").submit(function(){
		return false;
	});
});
</script>
EOF;
		
		
		$cfm_box = array(
				'submit_javascript' => $submit_javascript,
				'title' => lang('plugin/aut', 'choose_category'),
				'body_include' => 'aut:tiku_category',
				'button1' => lang('plugin/aut', 'submit'),
		);
	
		include template('aut:common/confirmbox');
	}
	
}
?>