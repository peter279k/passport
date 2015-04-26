$(function() {
	
	alertify.set({
		labels:{
			ok: "確定",
			cancel: "取消"
		}
	});
	
	var res = "";
	$("#facebook-logon").hide();
	
	window.fbAsyncInit = function() {
		FB.init({
			appId      : '343935009137957', // Set YOUR APP ID
			//channelUrl : 'http://hayageek.com/examples/oauth/facebook/oauth-javascript/channel.html', // Channel File
			status     : true, // check login status
			cookie     : true, // enable cookies to allow the server to access the session
			xfbml      : true  // parse XFBML
		});
		
		FB.Event.subscribe('auth.authResponseChange', function(response) {
			if (response.status === 'connected') {
				// the user is logged in and has authenticated your
				// app, and response.authResponse supplies
				// the user's ID, a valid access token, a signed
				// request, and the time the access token 
				// and signed request each expire
				$("#download-excel").show();
				$("#facebook-login-list").hide();
				console.log("Connected to Facebook");
			}
			else if (response.status === 'not_authorized') {
				// the user is logged in to Facebook, 
				// but has not authenticated your app
				console.log('not_authorized');
				$("#facebook-login-list").show();
			}
			else {
				// the user isn't logged in to Facebook.
				console.log('not_logged');
				$("#facebook-login-list").show();
			}
		});
    };
	
	$("#facebook-login").click(Login);
	$("#facebook-logon").click(Logout);
	
	$("#download-excel").click(function() {
		alertify.prompt("請輸入下載key: ", function(e,str) {
			if(e) {
				if(str==="") {
					alertify.alert("輸入錯誤!");
				}
				else if(str.length<64 || str.length>64) {
					alertify.alert("輸入錯誤!");
				}
				else {
					$.post("/passport/php/get_file.php", {"data": [{"key": str, "accessToken": getToken()}]}, function(response) {
						res = $.parseJSON(response);
						if(res=="token-error") {
							alertify.alert("下載出錯了!");
						}
						else if(res=="key-error") {
							alertify.alert("key輸入錯了!");
						}
						else {
							location.href = res;
						}
					});
				}
			}
		});
		
	});
	
	$("#download-excel").hide();
	
	// Load the SDK asynchronously
	(function(d){
		var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement('script'); js.id = id; js.async = true;
		js.src = "//connect.facebook.net/zh_TW/all.js";
		ref.parentNode.insertBefore(js, ref);
	}(document));
});

function Login() {
	FB.login(function(response) {
		if (response.authResponse) {
			//顯示logout button
			$("#facebook-login").hide();
			$("#download-excel").show();
			$("#facebook-logon").show();
		}
		else {
			console.log('User cancelled login or did not fully authorize.');
		}
	},{scope: 'email,public_profile'});
}

function getToken() {
	FB.getLoginStatus(function(response) {
		if (response.status === 'connected') {
			// the user is logged in and has authenticated your
			// app, and response.authResponse supplies
			// the user's ID, a valid access token, a signed
			// request, and the time the access token 
			// and signed request each expire
			//var uid = response.authResponse.userID;
			accessToken = response.authResponse.accessToken;
			result = accessToken;
		}
		else if (response.status === 'not_authorized') {
			// the user is logged in to Facebook, 
			// but has not authenticated your app
			result = 'not_authorized';
		}
		else {
			// the user isn't logged in to Facebook.
			result = 'not_logged';
		}
	});
	return result;
}

function Logout() {
    FB.logout(function(){location.href="admin.html";});
	location.href="admin.html";
}