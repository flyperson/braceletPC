var myhost = window.location.host;
var myhref = "http://"+myhost+"/braceletPC/";


$.ajax({
	type:"get",
	url:myhref+"handlelogin.php",
	data:{
		flag:"JudgeLogin"
	},
	dataType:"json",
	success:function(data){
		if(data.state){
			$("body").removeClass("hidden");
		}else{
			window.open(myhref+"login.html","_self");
		}
	},
	error:function(x,s,t){
		alert("系统错误");
		console.log(s+": "+t);
	}
});


