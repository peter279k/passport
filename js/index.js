$(function() {
	alertify.set({
		labels:{
			ok: "確定",
			cancel: "取消"
		}
	});
	
	$("#pass-action").click(function() {
		if($("#myName").val()=="") {
			alertify.alert("請輸入姓名!");
		}
		else if($("#myID").val()=="") {
			alertify.alert("請輸入身分證字號!");
		}
		else if($("#myDate").val()=="") {
			alertify.alert("請輸入出生年月日!");
		}
		else {
			var res = "";
			var name = $("#myName").val();
			var id = $("#myID").val();
			var date = $("#myDate").val();
			$.post("/passport/php/insert_data.php", {data: [{"name": name,"id": id, "date": date}]}, function(response) {
				res = $.parseJSON(response);
				if(res=="store-success") {
					alertify.alert("儲存成功!");
				}
				else if(res=="has-inserted") {
					alertify.alert("你已經填過資料了!");
				}
				else {
					console.log(res);
					alertify.alert("儲存失敗!");
				}
			});
		}
		
	});
	
	
});