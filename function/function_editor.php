<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
//´úÂë½âÎö
function aut_decode_callback($matches) {
	return "<pre class='brush: $matches[1];toolbar: true;'>".strip_tags($matches[2])."</pre>";
}

function aut_decode ($content) {
	$content = preg_replace_callback("/\[(cpp|pascal|java)\](.+?)\[\/(cpp|pascal|java)\]/s", 'aut_decode_callback', $content);
	return $content;
}
?>