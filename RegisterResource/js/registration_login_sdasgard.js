var isProcessing = false;


function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function submitenterSignup(myfield,e) {
  var keycode;
  if (window.event) {
    keycode = window.event.keyCode;
  } else if (e) {
    keycode = e.which;
  } else {
    return true;
  }

  if (keycode == 13) {
    submitSignUpStepwise();
    return true;
  } else {
    return true;
  }
}

function submitSignUpStepwise() {
  flag=true;


  if(!checkConfrmPswd()) {
    flag=false;
  }



  if(!chkPrivacy()) {
    flag=false;
  }



  if(flag) {
 
	document.getElementById('username').classList.add("locked");
	document.getElementById('email').classList.add("locked");
	document.getElementById('password').classList.add("locked");
	document.getElementById('confirm').classList.add("locked");
	document.getElementById('captcha').classList.add("locked");
	document.getElementById('chkPolicy').classList.add("locked");
	isProcessing = true;

    document.getElementById('register_button').disabled = 'true';
    document.getElementById('register_button').style.backgroundPosition = "0 0";

    document.forms["registrationForm_sd"].action="./Index.php";

    document.forms["registrationForm_sd"].submit();
  } else {
    return flag;
  }
}

function checkAvailablity() {
	if(isProcessing)
		return true;

  var username = document.getElementById("username").value;
  if(username=="") {
    document.getElementById('userErrors').style.display = "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('userPart').style.display = "block";
    //document.getElementById('username').style.border= "3px #FF0000 solid";
    //document.getElementById('username').style.backgroundColor = "#FFFBF9";
    //document.getElementById('userAvail').style.display = "none";
    document.getElementById('userNotAvail').style.display = "none";
    document.getElementById('userLPart').style.display = "none";
    document.getElementById('userChars').style.display = "none";
    document.getElementById('userAlpha').style.display= "none";
    return false;
  } else if((username.length<6)||(username.length>12)) {
    document.getElementById('userErrors').style.display = "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('userLPart').style.display = "block";
    //document.getElementById('username').style.border= "3px #FF0000 solid";
    //document.getElementById('username').style.backgroundColor = "#FFFBF9";
    document.getElementById('userPart').style.display = "none";
    //document.getElementById('userAvail').style.display = "none";
    document.getElementById('userNotAvail').style.display = "none";
    document.getElementById('userChars').style.display = "none";
    document.getElementById('userAlpha').style.display= "none";
    return false;
  } else if(!alphabetCheck(username)) {
    document.getElementById('userErrors').style.display = "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('userAlpha').style.display= "block";
    //document.getElementById('username').style.border= "3px #FF0000 solid";
    //document.getElementById('username').style.backgroundColor = "#FFFBF9";
    document.getElementById('userPart').style.display = "none";
    //document.getElementById('userAvail').style.display = "none";
    document.getElementById('userNotAvail').style.display = "none";
    document.getElementById('userLPart').style.display = "none";
    document.getElementById('userChars').style.display = "none";
    return false;
  } else if(!alphaNumCheck(username)) {
    document.getElementById('userErrors').style.display = "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('userChars').style.display = "block";
    //document.getElementById('username').style.border= "3px #FF0000 solid";
    //document.getElementById('username').style.backgroundColor = "#FFFBF9";
    document.getElementById('userPart').style.display = "none";
    //document.getElementById('userAvail').style.display = "none";
    document.getElementById('userNotAvail').style.display = "none";
    document.getElementById('userLPart').style.display = "none";
    document.getElementById('userAlpha').style.display= "none";
    return false;
  } else {
    document.getElementById('userErrors').style.display = "none";
    //document.getElementById('Registration_Error').style.display = "none";
    document.getElementById('userPart').style.display = "none";
    //document.getElementById('username').style.border= "3px #97b5cd solid";
    //document.getElementById('username').style.backgroundColor = "#FFFFFF";
    //document.getElementById('userAvail').style.display = "none";
    document.getElementById('userNotAvail').style.display = "none";
    document.getElementById('userLPart').style.display = "none";
    document.getElementById('userChars').style.display = "none";
    document.getElementById('userAlpha').style.display= "none";
  }
  var url = "./index.php?username=" + encodeURIComponent(username);
  req=GetXmlHttpObject();
  req.open("GET.html", url, true);
  req.onreadystatechange = callback_username;
  req.send(null);
  return true;
}

function callback_username() {
	if(isProcessing)
		return true;

  if (req.readyState == 4) {
    if (req.status == 200) {
      var message = req.responseXML.getElementsByTagName("message")[0];
      mesUser=message.childNodes[0].nodeValue;
      //document.getElementById('userProgress').style.display = "none";
      //#FDFFFD  #FBFFFB
      if(mesUser=="Available") {
        document.getElementById('userErrors').style.display = "none";
        //document.getElementById('Registration_Error').style.display = "none";
        document.getElementById('userAlpha').style.display= "none";
        //document.getElementById('username').style.border= "3px #66CC00 solid";
        //document.getElementById('username').style.backgroundColor = "#FBFFFB";
        document.getElementById('userPart').style.display = "none";
        document.getElementById('userNotAvail').style.display = "none";
        document.getElementById('userLPart').style.display = "none";
        document.getElementById('userChars').style.display = "none";
        //document.getElementById('usernameok').style.visibility = "visible";
        //document.getElementById('userProgress').style.display = "none";
      } else {
        document.getElementById('userErrors').style.display = "table-row";
        //document.getElementById('Registration_Error').style.display = "block";
        document.getElementById('userAlpha').style.display= "none";
        //document.getElementById('username').style.border= "3px #FF0000 solid";
        //document.getElementById('username').style.backgroundColor = "#FFFBF9";
        document.getElementById('userPart').style.display = "none";
        document.getElementById('userNotAvail').style.display = "block";
        document.getElementById('userLPart').style.display = "none";
        document.getElementById('userChars').style.display = "none";
        //document.getElementById('usernameok').style.visiblity = "hidden";
        //document.getElementById('userAvail').style.display = "none";
        //document.getElementById("username").value="";
        document.getElementById("username").focus();
      }
    }
  } else {
      //document.getElementById('userProgress').style.display = "block";
  }
}

function alphabetCheck(pwd) {
	if(isProcessing)
		return true;

  var flag=false;
  for (i=0;i<pwd.length;i++) {
    if((pwd.charCodeAt(i)>96 && pwd.charCodeAt(i)<123) || (pwd.charCodeAt(i)>64 && pwd.charCodeAt(i)<91)) {
      return true;
    }
  }
  return flag;
}

function alphaNumCheck(uname) {
	if(isProcessing)
		return true;

  var flag=false;
  for(i=0;i<uname.length;i++) {
    if (((uname.charCodeAt(i)>96) && (uname.charCodeAt(i)<123)) || ((uname.charCodeAt(i)>64) && (uname.charCodeAt(i)<91)) || ((uname.charCodeAt(i)>=48)&&(uname.charCodeAt(i)<=57))) {
      flag=true;
    } else {
      return false;
    }
  }
  return flag
}

function digitCheck(pwd) {
	if(isProcessing)
		return true;

  var flag=false;
  for(i=0;i<pwd.length;i++) {
    if(pwd.charCodeAt(i)>=48 && pwd.charCodeAt(i)<=57) {
      return true;
    }
  }
  return flag;
}

function GetXmlHttpObject() {
  var xmlHttp=null;
  try {
    // Firefox, Opera 8.0+, Safari
    xmlHttp=new XMLHttpRequest();
  } catch (e) {
    // Internet Explorer
    try {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
  return xmlHttp;
}

function checkAvailabilityPwd() {
	if(isProcessing)
		return true;

  var password = document.getElementById("password").value;
  if (password=="") {
    document.getElementById('passwordErrors').style.display= "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('pwdPart').style.display = "block";

    //document.getElementById('password').style.border= "3px #FF0000 solid";
    //document.getElementById('password').style.backgroundColor = "#FFFBF9";
    document.getElementById('pwdLPart').style.display = "none";
    document.getElementById('pwdChars').style.display = "none";
    //document.getElementById('pwdValid').style.display = "none";
    document.getElementById('pwdAlpha').style.display= "none";
    document.getElementById('pwdDigit').style.display= "none";
    return false;
  } else if((password.length<6)||(password.length>10)) {
    document.getElementById('passwordErrors').style.display= "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('pwdLPart').style.display = "block";

    //document.getElementById('password').style.border= "3px #FF0000 solid";
    //document.getElementById('password').style.backgroundColor = "#FFFBF9";
    document.getElementById('pwdPart').style.display = "none";
    document.getElementById('pwdChars').style.display = "none";
    //document.getElementById('pwdValid').style.display = "none";
    document.getElementById('pwdAlpha').style.display= "none";
    document.getElementById('pwdDigit').style.display= "none";
    return false;
  } else if (!alphaNumCheck(password)) {
    document.getElementById('passwordErrors').style.display= "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('pwdChars').style.display= "block";

    //document.getElementById('password').style.border= "3px #FF0000 solid";
    //document.getElementById('password').style.backgroundColor = "#FFFBF9";
    document.getElementById('pwdLPart').style.display = "none";
    document.getElementById('pwdPart').style.display = "none";
    //document.getElementById('pwdValid').style.display = "none";
    document.getElementById('pwdAlpha').style.display= "none";
    document.getElementById('pwdDigit').style.display= "none";
    return false;
  } else if(!alphabetCheck(password)) {
    document.getElementById('passwordErrors').style.display= "table-row";
    document.getElementById('pwdAlpha').style.display= "block";

    //document.getElementById('password').style.border= "3px #FF0000 solid";
    //document.getElementById('password').style.backgroundColor = "#FFFBF9";
    document.getElementById('pwdChars').style.display= "none";
    document.getElementById('pwdLPart').style.display = "none";
    document.getElementById('pwdPart').style.display = "none";
    //document.getElementById('pwdValid').style.display = "none";
    document.getElementById('pwdDigit').style.display= "none";
    return false;
  } else if(!digitCheck(password)) {
    document.getElementById('passwordErrors').style.display= "table-row";
    document.getElementById('pwdDigit').style.display= "block";
    //document.getElementById('password').style.border= "3px #FF0000 solid";
    //document.getElementById('password').style.backgroundColor = "#FFFBF9";
    document.getElementById('pwdAlpha').style.display= "none";
    document.getElementById('pwdChars').style.display= "none";
    document.getElementById('pwdLPart').style.display = "none";
    document.getElementById('pwdPart').style.display = "none";
    //document.getElementById('pwdValid').style.display = "none";
    return false;
  } else {
    document.getElementById('passwordErrors').style.display= "none";
    //document.getElementById('Registration_Error').style.display = "none";
    document.getElementById('pwdDigit').style.display= "none";
    document.getElementById('pwdPart').style.display= "none";
    document.getElementById('pwdLPart').style.display = "none";
    document.getElementById('pwdChars').style.display = "none";
    document.getElementById('pwdAlpha').style.display= "none";
    //document.getElementById('password').style.border= "3px #66CC00 solid";

    //document.getElementById('password').style.backgroundColor = "#FBFFFB";
  }
  //document.getElementById('pwdValid').style.display = "block";
  return true;
}

function checkConfrmPswd() {
	if(isProcessing)
		return true;

  var password = document.getElementById("password").value;
  var confirm = document.getElementById("confirm").value;
  if (!checkAvailabilityPwd()) {
    document.getElementById('confirmPasswordErrors').style.display= "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('confrmPart').style.display = "block";
    document.getElementById('confrmNotEq').style.display = "none";
    //document.getElementById('confirm').style.border= "3px #FF0000 solid";
    //document.getElementById('confirm').style.backgroundColor = "#FFFBF9";
    //document.getElementById("confirm").value = "";
    document.getElementById("password").focus();
    return false;
  } else if(password!=confirm) {
    document.getElementById('confirmPasswordErrors').style.display= "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('confrmNotEq').style.display = "block";
    document.getElementById('confrmPart').style.display = "none";
    //document.getElementById('confirm').style.border= "3px #FF0000 solid";
    //document.getElementById('confirm').style.backgroundColor = "#FFFBF9";
    //document.getElementById("confirm").value = "";
    return false;
  } else {
    document.getElementById('confirmPasswordErrors').style.display= "none";
    //document.getElementById('Registration_Error').style.display = "none";
    document.getElementById('confrmPart').style.display= "none";
    document.getElementById('confrmNotEq').style.display = "none";
    //document.getElementById('confirm').style.border= "3px #66CC00 solid";
    //document.getElementById('confirm').style.backgroundColor = "#FBFFFB";
  }
  //document.getElementById('pwdValid').style.display = "block";
  return true;
}

function checkAvailabilityEmail() {
	if(isProcessing)
		return true;

  var email=document.getElementById("email").value;
  var at="@";
  var dot=".";
  var lat=email.indexOf(at);
  var lstr=email.length;
  var ldot=email.indexOf(dot);
  var lastdot=email.lastIndexOf(dot);
  var emailfirst=email.substring(0,lat);
  var emailmiddle=email.substring(lat+1,ldot);
  var emaillast=email.substring(lastdot+1,lstr);
  if (email=="") {
    document.getElementById('emailErrors').style.display = "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('emailPart').style.display = "block";
    document.getElementById('emailVPart').style.display = "none";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
    return false;
  } else if(email.indexOf(at)==-1) {
    document.getElementById('emailErrors').style.display = "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('emailVPart').style.display = "block";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    document.getElementById('emailPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
    return false;
  } else if (email.indexOf(at)==-1 || email.indexOf(at)==0 || email.indexOf(at)==lstr) {
    document.getElementById('emailErrors').style.display = "table-row";
    document.getElementById('emailVPart').style.display = "block";
    //document.getElementById('Registration_Error').style.display = "block";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    document.getElementById('emailPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
    return false;
  } else if (email.indexOf(dot)==-1 || email.indexOf(dot)==0 || email.indexOf(dot)==lstr || email.indexOf(dot)==(lstr-1)) {
    document.getElementById('emailErrors').style.display = "table-row";
    document.getElementById('emailVPart').style.display = "block";
    //document.getElementById('Registration_Error').style.display = "block";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    document.getElementById('emailPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
    return false;
  } else if (email.indexOf(at,(lat+1))!=-1) {
    document.getElementById('emailErrors').style.display = "table-row";
    document.getElementById('emailVPart').style.display = "block";
    //document.getElementById('Registration_Error').style.display = "block";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    document.getElementById('emailPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
    return false;
  } else if (email.substring(lat-1,lat)==dot || email.substring(lat+1,lat+2)==dot) {
    document.getElementById('emailErrors').style.display = "table-row";
    document.getElementById('emailVPart').style.display = "block";
    //document.getElementById('Registration_Error').style.display = "block";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    document.getElementById('emailPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
    return false;
  } else if (email.indexOf(dot,(lat+2))==-1) {
    document.getElementById('emailErrors').style.display = "table-row";
    document.getElementById('emailVPart').style.display = "block";
    //document.getElementById('Registration_Error').style.display = "block";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    document.getElementById('emailPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
    return false;
  } else if (email.indexOf(" ")!=-1) {
    document.getElementById('emailErrors').style.display = "table-row";
    document.getElementById('emailVPart').style.display = "block";
    //document.getElementById('Registration_Error').style.display = "block";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    document.getElementById('emailPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
      return false;
  } else if(!alphaNumCheck(emaillast)) {
    document.getElementById('emailErrors').style.display = "table-row";
    document.getElementById('emailVPart').style.display = "block";
    //document.getElementById('Registration_Error').style.display = "block";
    //document.getElementById('email').style.border= "3px #FF0000 solid";
    //document.getElementById('email').style.backgroundColor = "#FFFBF9";
    document.getElementById('emailPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
    return false;
  } else {
    document.getElementById('emailErrors').style.display = "none";
    //document.getElementById('Registration_Error').style.display = "none";
    //document.getElementById('email').style.border= "3px #97b5cd solid";
    //document.getElementById('email').style.backgroundColor = "#FFFFFF";
    document.getElementById('emailPart').style.display = "none";
    document.getElementById('emailVPart').style.display = "none";
    //document.getElementById('emailAvail').style.display = "none";
    document.getElementById('emailNotAvail').style.display = "none";
  }
  var urlEmail="./Index.php?email="+encodeURIComponent(email)+"&emailF=addProfile";
  req=GetXmlHttpObject();
  req.open("GET.html", urlEmail, true);
  req.onreadystatechange = callbackEmail;
  req.send(null);
  return true;
}

function callbackEmail() {
	if(isProcessing)
		return true;

  if (req.readyState == 4) {
    if (req.status == 200) {
      var message = req.responseXML.getElementsByTagName("message")[0];
      mes=message.childNodes[0].nodeValue;
      //document.getElementById('emailProgress').style.display = "none";
      if (mes=="Available") {
        document.getElementById('emailErrors').style.display = "none";
        //document.getElementById('Registration_Error').style.display = "none";
        //document.getElementById('emailok').style.visibility = "visible";
        //document.getElementById('email').style.border= "3px #66CC00 solid";
        //document.getElementById('email').style.backgroundColor = "#FBFFFB";
        document.getElementById('emailPart').style.display = "none";
        document.getElementById('emailVPart').style.display = "none";
        document.getElementById('emailNotAvail').style.display = "none";
      } else {
        document.getElementById('emailErrors').style.display = "table-row";
        //document.getElementById('Registration_Error').style.display = "block";
        document.getElementById('emailNotAvail').style.display = "block";
        document.getElementById('emailPart').style.display = "none";
        document.getElementById('emailVPart').style.display = "none";
        //document.getElementById('emailok').style.visibility = "hidden";
        //document.getElementById("email").value="";
        document.getElementById("email").focus();
      }
    }
  } else{
    //document.getElementById('Registration_Error').style.display = "block";
    //document.getElementById('emailProgress').style.display = "block";
  }
}

function chkPrivacy() {
	if(isProcessing)
		return true;

  if (!document.getElementById('chkPolicy').checked) {
    document.getElementById('checkPolicyErrors').style.display = "table-row";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('chkPart').style.display = "block";
    return false;
  } else {
    document.getElementById('checkPolicyErrors').style.display = "none";
    //document.getElementById('Registration_Error').style.display = "block";
    document.getElementById('chkPart').style.display = "none";
    return true;
  }
}

function displayUsernameInfo() {
  document.getElementById("UsernameInfo").style.visibility="visible";
}

function displayPasswordInfo() {
  document.getElementById("PasswordInfo").style.visibility="visible";
}

function displayEmailInfo() {
  document.getElementById("EmailInfo").style.visibility="visible";
}

function displayReferreridInfo() {
  document.getElementById("refferidInfo").style.visibility="visible";
}

function checkPromotionCode() {
  document.getElementById("refferidInfo").style.visibility="hidden";
  var promoField=document.getElementById("promotionCode").value;
  promoField=promoField.trim();

  var index = promoField.indexOf('@');
  if(index>0) {
    document.getElementById("promotionCodeType").value="email";
  } else {
    document.getElementById("promotionCodeType").value="";
  }
}

function checkImg() {
  var val=document.getElementById("captcha").value;
  if (val=="") {
    document.getElementById('captchaErrors').style.display = "table-row";
    document.getElementById('captchaerror').style.display = "block";
    document.getElementById('captchaerror').focus();
    return false;
  } else {
    document.getElementById('captchaErrors').style.display = "none";
    document.getElementById('captchaerror').style.display = "none";
  }
  var url = "./Index.php?urlval="+encodeURIComponent(val);
  req=GetXmlHttpObject();
  req.open("POST.html", url, true);
  req.onreadystatechange = callbackCaptcha;
  req.send(null);
  return true;
}

function callbackCaptcha() {
  if (req.readyState == 4) {
    if (req.status == 200) {
      document.getElementById('captchaErrors').style.display = "none";
      document.getElementById('captchaerror').style.display = "none";
      if(req.responseXML!=null) {
        document.getElementById('captchaErrors').style.display = "none";
        document.getElementById('captchaerror').style.display = "none";
        parseMessageCaptcha(req.responseXML);
      }
    }
  }
}

function parseMessageCaptcha(responseXML) {
  var message = null;
  message = responseXML.getElementsByTagName("message")[0];
  if(message!=null) {
    var checkmsg =message.childNodes[0].nodeValue;
    if(checkmsg=="invalid") {
      document.getElementById('captchaErrors').style.display = "table-row";
      document.getElementById('captchaerror').style.display = "block";    }
    if (checkmsg =="valid"){
      document.getElementById('captchaErrors').style.display = "none";
      document.getElementById('captchaerror').style.display = "none";
    }
  } else{
  }
}

function reloadImg(id)  {
  document.getElementById('captchaErrors').style.display = "none";
  document.getElementById('captchaerror').style.display = "none";
  var obj = document.getElementById(id);
  var src = obj.src;
  var pos = src.indexOf('?');
  if (pos >= 0) {
    src = src.substr(0, pos);
  }
  var date = new Date();
  obj.src = src + '?v=' + date.getTime();
  return false;
}
