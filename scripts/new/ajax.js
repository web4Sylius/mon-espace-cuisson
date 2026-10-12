var http_request=false;function send_request(url)
{http_request=false;if(window.XMLHttpRequest)
{http_request=new XMLHttpRequest();if(http_request.overrideMimeType)
{http_request.overrideMimeType("text/xml");}}
else if(window.ActiveXObject)
{try
{http_request=new ActiveXObject("Msxml2.XMLHttp");}
catch(e)
{try
{http_request=new ActiveXobject("Microsoft.XMLHttp");}
catch(e)
{}}}
if(!http_request)
{window.alert("创建XMLHttp对象失败！");return false;}
http_request.onreadystatechange=processrequest;http_request.open("GET",url,true);http_request.send(null);}
function processrequest()
{if(http_request.readyState==4)
{if(http_request.status==200)
{document.getElementById(reobj).innerHTML=http_request.responseText;}
else
{alert("您所请求的页面不正常！");}}}
function getclass(obj,url)
{document.getElementById(obj).innerHTML="<img src='images/loading.gif' width='18' height='18' alt='loading...' />&nbsp;Please Wate...";reobj=obj;send_request(url);}