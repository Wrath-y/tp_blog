/**
* create_time 2018/02/04 00:22
* 提交评论
*/

function ajax(obj,id=false,type='post')
{
    var data = $(obj).serialize();
    var url = $(obj).attr('action');
    var load;
    if (id) {
    	data = data + '&id=' + id;
    }
    $.ajax({
    	url: url,
    	data: data,
    	type: type,
    	dataType: 'json',
    	beforeSend: function() {
    		$('button').attr({disabled: "disabled"});
			load = layer.load(1, {shade: [0.1,'#fff']}); 
		},
		success: function(data) {
			layer.close(load);
			if (data.code == 2000) {
				layer.msg(data.msg,{icon: 6,time:1000});
			} else {
				layer.msg(data.msg,{icon: 5,time:1000});
			}
			$("button").removeAttr("disabled");
		}
    })
}