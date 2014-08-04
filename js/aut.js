/*validate functions*/
var digit_regex = /^[1-9]{1}\d*$/
function is_empty(val) {
	if(typeof val === 'undefined' || val == null || val == "") {
		return true;
	}
	return false;
}
function chkEmpty(n, v) {
	if(is_empty(v)) {
		alert(n+'不能为空');
		return false;
	}
	return true;
}
function chkRegEx(n, v, reg) {
	if(!reg.test(v)) {
		alert(n+'的格式不正确');
		return false;
	}
	return true;
}
function chkNumber(n, v) {
	if(v == "" || isNaN(v)) {
		alert(n+'必须为数字');
		return false;
	}
	return true;
}
function chkDigit(n, v, min, max) {
	if(chkLength(n, v, min, max) === false) {
		return false;
	}
	if(v == 0) {
		return true;
	}
	if(!digit_regex.test(v)) {
		alert(n+'必须为整数');
		return false;
	}
	return true;
}
function chkLength(n, v, min, max) {
	if(min == 0 && v == "") {
		alert(n+'不能为空');
		return false;
	}
	if((v.length < min && min != null) || (v.length > max && max != null)) {
		alert(n+'的长度必须在'+min+'~'+max+'字之间');
		return false;
	}
	return true;
}
function chkUploadExist(n,o) {
	if(o.length == 0 || o.val() == "") {
		alert("请上传"+n);
		return false;
	}
	return true;
}






jQuery(function(){
	jQuery(".checkAll").click(function(){
		if(jQuery(this).attr("checked") == "checked") {
			jQuery(".checkAllChildren").attr("checked", "checked");
		} else {
			jQuery(".checkAllChildren").removeAttr("checked");
		}
	});
	jQuery(".collapse_expand").live("click", function() {
		var obj = jQuery(this);
		var action = obj.attr("data-action");
		var control = obj.attr("data-control");
		if(action == 'collapse') {
			obj.attr("data-action", "expand"); 
			obj.html("[收起]");
			jQuery(control).fadeIn();
		} else if(action == 'expand') {
			obj.attr("data-action", "collapse"); 
			obj.html("[展开]");
			jQuery(control).fadeOut();
		}
	});
	jQuery(".clear_session").live("click",function() {
		jQuery.post(aut_index_path+'&home=misc&act=clear_session');
	});
	jQuery(".deletelink").click(function(){
		var id = jQuery(this).attr("data-id");
		var type = jQuery(this).attr("data-type");
		var url, confirm_msg, style;
		var msg = '';
		var jump_url = location.href;
		if(type == 'problem') {
			url = aut_index_path + "&home=problem&act=delete&pid="+id+"&ajax=1&admin=1";
			confirm_msg = "确定要删除这个题目?"; 
			
		} else if(type == 'solution') {
			url = aut_index_path + "&home=solution&act=delete&sid="+id+"&ajax=1";
			confirm_msg = "确定要删除这个题解?";
		} else if(type == 'category') {
			url = aut_index_path + "&home=category&act=delete&cid="+id+"&ajax=1&admin=1";
			confirm_msg = "确定要删除这个类别?";
		} else if(type == 'knowledge') {
			url = aut_index_path + "&home=knowledge&act=delete&kid="+id+"&ajax=1&admin=1";
			confirm_msg = "确定要删除这个知识点?";
		} else if(type == 'competition') {
			url = aut_index_path + "&home=competition&act=delete&competition_id="+id+"&ajax=1&admin=1";
			confirm_msg = "确定要删除这个比赛?";
		} else if(type == 'release') {
			url = aut_index_path + "&home=release&act=delete&rid="+id+"&ajax=1&admin=1";
			confirm_msg = "确定要删除这个Release?";
		}
		
		if(confirm(confirm_msg)) {
			jQuery.ajax({
				url: url,
	            type:'POST',
	            complete :function(){},
	            //dataType: 'json', 
	            error: function() { alert('Please try again');},
	            
	            success: function() {
	            	location.href = location.href;
	            	/*
	            	switch (data.code) {
	            		case -1:
	            			style = "alert-error";
	            			break;
	            		case 0:
	            			style = "alert-info";
	            			break;
	            		case 1:
	            			style = "alert-success";
	            			break;
	            	}
	            	/*
	            	msg += "<div class='alert "+style+"' id='aut_message'><button type='button'' class='close clear_session' data-dismiss='alert'>&times;</button><ul style='margin-bottom:0;'>";
	            	for(i=0;i<data.content.length;i++) {
	            		msg += "<li>"+data.content[i]+"</li>";
	            	}
	            	msg += "</ul></div>"
	            	jQuery(".aut_message").html(msg);
	            	setTimeout(function() {
	            		jQuery("#aut_message").fadeOut(1000);
	            	}, 3000);*/
	            }
			});
			
		}
	});
	
	
	
	
	jQuery('[data-toggle="modal"]').bind('click',function(e) {
		
		e.preventDefault();
		var obj = jQuery(this);
		var url = obj.attr('data-href');
		if(obj.attr("data-modal-size") == "large") {
			jQuery('#response_modal').addClass("large");
		}
		if (url.indexOf('#') == 0 || obj.attr("data-open") == "true") {
			jQuery('#response_modal').modal('show');
		} else {
			jQuery.get(url, function(data) {
	                        jQuery('#response_modal').html(data);
	                        jQuery('#response_modal').modal();
			}).success(function() {
				jQuery('#response_modal input:text:visible:first').focus();
				obj.attr("data-open", "true");
			});
		}
	});
	jQuery(".expandicon").click(function(){
		var role = jQuery(this).attr("data-role");
		var imgpath = jQuery(this).attr("src");
		var cssdisplay;
		var datarole;
		var expandtotallevel = 2;
		
		if(role == "expand") {
			datarole = "collapse"
			jQuery(this).parents().next().children(".subnav").fadeIn(400);
		} else if(role == "collapse") {
			jQuery(this).parents().next().children(".subnav").fadeOut(400);
			datarole = "expand"
		}
		jQuery(this).attr("data-role", datarole);
		jQuery(this).attr("src", imgpath.replace(role, datarole));
		
	});
	
});


function jumpto(url) {
	var form = jQuery("#search_form");
	if(form.length > 0) {
		form.attr("action", url);
		form.submit();
	} else {
		location.href = url;
	}
}