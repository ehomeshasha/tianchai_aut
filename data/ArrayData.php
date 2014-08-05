<?php
//Array("gray","gray","orange","orange","green","red","red","red","red","red","red","navy ","navy");
/*
 * $MSG_Pending="等待";
	$MSG_Pending_Rejudging="等待重判";
	$MSG_Compiling="编译中";
	$MSG_Running_Judging="运行并评判";
	$MSG_Accepted="正确";
	$MSG_Presentation_Error="格式错误";
	$MSG_Wrong_Answer="答案错误";
	$MSG_Time_Limit_Exceed="时间超限";
	$MSG_Memory_Limit_Exceed="内存超限";
	$MSG_Output_Limit_Exceed="输出超限";
	$MSG_Runtime_Error="运行错误";
	$MSG_Compile_Error="编译错误";
	$MSG_Runtime_Click="运行错误(点击看详细)";
	$MSG_Compile_Click="编译错误(点击看详细)";
	$MSG_Compile_OK="编译成功";
($MSG_Pending,
$MSG_Pending_Rejudging,
$MSG_Compiling,
$MSG_Running_Judging,
$MSG_Accepted,
$MSG_Presentation_Error,
$MSG_Wrong_Answer,
$MSG_Time_Limit_Exceed,
$MSG_Memory_Limit_Exceed,
$MSG_Output_Limit_Exceed,
$MSG_Runtime_Error,
$MSG_Compile_Error,
$MSG_Compile_OK,
$MSG_TEST_RUN);
$judge_result=Array($MSG_Pending,
$MSG_Pending_Rejudging,
$MSG_Compiling,
$MSG_Running_Judging,$MSG_Accepted,$MSG_Presentation_Error,$MSG_Wrong_Answer,$MSG_Time_Limit_Exceed,$MSG_Memory_Limit_Exceed,$MSG_Output_Limit_Exceed,$MSG_Runtime_Error,$MSG_Compile_Error,$MSG_Compile_OK,$MSG_TEST_RUN);
 */
global $_G;
$page_selection = array('5','10','20','50','100','500','1000');
if(!in_array($_G['aut_settings']['perpage'], $page_selection)) {
	array_push($page_selection, $_G['aut_settings']['perpage']);
	sort($page_selection);
}




$ArrayData = array(
	'page_selection' => $page_selection,	











	'admin_module' => array(
		'judgeonline' => array('problem', 'category', 'knowledge', 'tiku', 'solution'),
	),

	'module_map' => array(
		'challenge' => lang('plugin/aut', 'challenge'),
		'tiku' => lang('plugin/aut', 'tiku'),
		'try' => lang('plugin/aut', 'try'),
	),

	'showresult' => array(
		'-1' => array('alert_style' => 'alert-error', 'button_style' => 'btn-danger'),
		'0' => array('alert_style' => 'alert-info', 'button_style' => 'btn-info'),
		'1' => array('alert_style' => 'alert-success', 'button_style' => 'btn-success'),
	),
	'ctype' => array(
		'learning' => lang('plugin/aut', 'learning_system'),
		'tiku' => lang('plugin/aut', 'tiku_system'),
	),
	'digitsmap' => array(1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'七',8=>'八',9=>'九'),
	'coursenav' => array(
		'knowledge' => lang('plugin/aut', 'knowledge_teach'),
		'try' => lang('plugin/aut', 'try'),
		'challenge' => lang('plugin/aut', 'challenge_once'),
	),
	
	'problemtype' => array(
		0 => array('name' => 'challenge', 'title' => lang('plugin/aut', 'challenge_text')),
		1 => array('name' => 'try', 'title' => lang('plugin/aut', 'try_text')),
		2 => array('name' => 'tiku', 'title' => lang('plugin/aut', 'tiku_text')),
	),
	/*
	'breadcrumb' => array(
		'problem' => lang('plugin/aut', 'challenge_text'),
		'problem_post_new' => lang('plugin/aut', 'problem_post_new'),
		'problem_post_edit' => lang('plugin/aut', 'problem_post_edit'),
		'problem_view' => lang('plugin/aut', 'problem_view'),
		'solution' => lang('plugin/aut', 'solution_list'),
		'solution_post_new' => lang('plugin/aut', 'solution_post_new'),
		'solution_post_edit' => lang('plugin/aut', 'solution_post_edit'),
		'solution_view' => lang('plugin/aut', 'solution_view'),
		'solution_viewerror' => lang('plugin/aut', 'solution_viewerror'),
		'managesolution' => lang('plugin/aut', 'managesolution'),
		'category' => lang('plugin/aut', 'category_list'),
		'category_post_new' => lang('plugin/aut', 'category_post_new'),
		'category_post_edit' => lang('plugin/aut', 'category_post_edit'),
		'knowledge' => lang('plugin/aut', 'knowledge_list'),
		'knowledge_post_new' => lang('plugin/aut', 'knowledge_post_new'),
		'knowledge_post_edit' => lang('plugin/aut', 'knowledge_post_edit'),
		'course' => lang('plugin/aut', 'code_learning'),
		'course_view' => lang('plugin/aut', 'course_view'),
		'course_view_knowledge' => lang('plugin/aut', 'knowledge'),
		'course_view_try' => lang('plugin/aut', 'try'),
		'course_view_challenge' => lang('plugin/aut', 'challenge'),
		'course_view_solution' => lang('plugin/aut', 'solution'),
		'tiku' => lang('plugin/aut', 'tiku_text'),
	),*/
	'result' => array(
		0 => array('style' => 'gray','text' => lang('plugin/aut', 'MSG_Pending')),
		1 => array('style' => 'gray','text' => lang('plugin/aut', 'MSG_Pending_Rejudging')),
		2 => array('style' => 'info','text' => lang('plugin/aut', 'MSG_Compiling')),
		3 => array('style' => 'info','text' => lang('plugin/aut', 'MSG_Running_Judging')),
		4 => array('style' => 'success','text' => lang('plugin/aut', 'MSG_Accepted')),
		5 => array('style' => 'danger','text' => lang('plugin/aut', 'MSG_Presentation_Error')),
		6 => array('style' => 'danger','text' => lang('plugin/aut', 'MSG_Wrong_Answer')),
		7 => array('style' => 'danger','text' => lang('plugin/aut', 'MSG_Time_Limit_Exceed')),
		8 => array('style' => 'danger','text' => lang('plugin/aut', 'MSG_Memory_Limit_Exceed')),
		9 => array('style' => 'danger','text' => lang('plugin/aut', 'MSG_Output_Limit_Exceed')),
		10 => array('style' => 'danger','text' => lang('plugin/aut', 'MSG_Runtime_Error')),
		11 => array('style' => 'warning','text' => lang('plugin/aut', 'MSG_Compile_Error')),
		12 => array('style' => 'info','text' => lang('plugin/aut', 'MSG_Compile_OK')),
		13 => array('style' => 'info','text' => lang('plugin/aut', 'MSG_TEST_RUN')),
	),
	'ace_mode' => array('c_cpp', 'c_cpp', 'pascal', 'java'),
	'solution_language' => array('C', 'C++', 'Pascal', 'Java'),
	'exampletext' => array(
		'input' => '输入', 
		'output' => '输出', 
		'explanation' => '解释'
	),
	'language2syntaxhighlighter' => array(
		0 => "c",
		1 => "cpp",
		2 => "pascal",
		3 => "java",
		4 => "ruby",
		5 => "bash",
		6 => "python",
		7 => "php",
		8 => "perl",
		9 => "cpp",
		10 => "cpp",
		11 => "vb",
		12 => "", 
	),
	
	'language_name' => array(
		0 => "C",
		1 => "C++",
		2 => "Pascal",
		3 => "Java",
		4 => "Ruby",
		5 => "Bash",
		6 => "Python",
		7 => "PHP",
		8 => "Perl",
		9 => "C#",
		10 => "Obj-C",
		11 => "FreeBasic",
		12 => "Other Language"
	),
	'language_ext' => array(
		"c",
		"cc",
		"pas",
		"java",
		"rb",
		"sh",
		"py",
		"php",
		"pl",
		"cs",
		"m",
		"bas"
	),
);
?>