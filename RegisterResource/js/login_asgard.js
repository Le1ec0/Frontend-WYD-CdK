
 
function f_login()
{
	registrationForm_sd.submit();
}
 







var ref=null;
var usera=null;
function submitSignUplogin(refvalue){
	ref=refvalue;
		flag=true;

			if(!checkAvailabilityUname()){
						flag=false;
			}
			if(!checkAvailabilityPassword()){
					flag=false;
			}

			if(flag){
				var user=document.getElementById("usrname").value;
				var pass=document.getElementById("pwd").value;
				var checkField="notchecked";
				usera=user;

				var url ="http://games.vibrant3g.com/web1/home/SDAdenLoginServlet?username="+encodeURIComponent(user)+"&password="+encodeURIComponent(pass)+"&check="+encodeURIComponent(checkField);


				req=GetXmlHttpObject();
				req.open("GET.html", url, true);
				req.onreadystatechange = callbackLogin;
				req.send(null);

				return true;
			}// flag fails
			else{
				return flag;
			}
}

function callbackLogin() {

		if (req.readyState == 4) {

			if (req.status == 200) {


				if(req.responseXML!=null)
				{

					parseMessageLogin(req.responseXML);
				}
			}
		}
		else{
				//document.getElementById('userProgress').style.display = "block";
		}
	}


function GetXmlHttpObject(){
		var xmlHttp=null;
		try{
				  // Firefox, Opera 8.0+, Safari
				  xmlHttp=new XMLHttpRequest();

		}
		catch (e){
				  // Internet Explorer
				  try{
						xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");

					}
				  catch (e){
						xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");

					}
		 }
	return xmlHttp;
}


 function parseMessageLogin(responseXML) {

		var message = null;
		message = responseXML.getElementsByTagName("message")[0];


		if(message!=null)
		{
			var checkmsg =message.childNodes[0].nodeValue;

			if(checkmsg=="invalid")
			{

					document.getElementById('errormsg').style.display="block";
					document.getElementById('usrname').style.border= "1px #FF0000 solid";
					//document.getElementById('usrname').style.backgroundColor = "#FFFBF9";
					document.getElementById('pwd').style.border= "1px #FF0000 solid";
					//document.getElementById('pwd').style.backgroundColor = "#FFFBF9";
					//document.getElementById('loginok').style.display="none";
					//document.getElementById('pswdok').style.display="none";
					document.getElementById('invalidlogin').style.display="block";
					document.getElementById('pwd').value="";
			}
			else if (checkmsg =="notactivated")
			{
				document.getElementById('errormsg').style.display="block";
				document.getElementById('usrname').style.border= "1px #FF0000 solid";
				//document.getElementById('usrname').style.backgroundColor = "#FFFBF9";
				document.getElementById('invalidlogin').style.display="none";
				document.getElementById('Notactive').style.display="block";
			}

		}
		else
		{

			window.location.href="http://games.vibrant3g.com/web1/sdasgardconnect.jsp";
		}
	}


	function clearfields(){
		document.getElementById("usrname").value="";
		document.getElementById("pwd").value="";
	}

function checkAvailabilityUname(){
		var username = document.getElementById("usrname").value;
		if(username==""||username=='username'){
		document.getElementById('errormsg').style.display="block";
		//document.getElementById('loginerror').style.display="block";
		document.getElementById('Nousername').style.display="block";
		//document.getElementById('loginok').style.display="none";
		return false;
		}else{
		document.getElementById('errormsg').style.display="none";
		//document.getElementById('loginerror').style.display="none";
		document.getElementById('Nousername').style.display="none";
		//document.getElementById('loginok').style.display="block";
		//document.getElementById('username').style.border= "1px #52D017 solid";
		 return true;
		 }return true;

}

function checkAvailabilityPassword(){
	var password = document.getElementById("pwd").value;
		if(password==""||password=='Password'){
		document.getElementById('errormsg_pwd').style.display="block";
		//document.getElementById('pswderror').style.display="block";
		document.getElementById('Nopassword').style.display="block";
		//document.getElementById('pswdok').style.display="none";
		return false;
		}
		else{
		//document.getElementById('pswderror').style.display="none";
		document.getElementById('errormsg_pwd').style.display="none";
		document.getElementById('Nopassword').style.display="none";
		//document.getElementById('pswdok').style.display="block";
		}

	return true;
}
