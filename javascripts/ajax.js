
function ajaxrequest(url,callback,post,async){
          
          if(async){
               var async = false;
          } else {
               var async = true;
          }
                  
        var httpRequest = false;

        // creation d'un objet HtmlRequest pour executer la fonction contenue dans la variable callback
        if (window.XMLHttpRequest) { // Mozilla, Safari,... version recentes d'IE ?
            httpRequest = new XMLHttpRequest();
            if (httpRequest.overrideMimeType) {
//                 httpRequest.overrideMimeType('text/xml');
                // Voir la note ci-dessous à propos de cette ligne
            }
        }
        else if (window.ActiveXObject) { // anciennes versions IE 5, IE 6 utilisent activeX
            try {
                httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
                try {
                    httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e) {}
            }
        }
        // probleme lors de la creation de l'objet
        if (!httpRequest) {
            return false;
        }

        // execution de la requete lorsque la propriété onready change
        httpRequest.onreadystatechange = function() {
            // execution qd le traitement (ci-dessous?) est fini et que la reponse est prete
            if (httpRequest.readyState == 4) {
               if (httpRequest.status == 200) {
                  callback(httpRequest);     // appel de la fonction recu en parametre
               }
            }
        };

 
  
	if(post==undefined){
  
    if (url=='ajax/vf_maj.php?action=close&name=toto')
    {
      confirm("Before call 2: "+url+" !!");
    
      //httpRequest.open('GET', url, async);
  	  //httpRequest.send(null);
    }
	  httpRequest.open('GET', url, async);
	  httpRequest.send();
    
	}else{
	  var parameters="";
	  for(name in post)
	    if (post.hasOwnProperty(name)) parameters+="&"+name+"="+encodeURIComponent(post[name]);

	  httpRequest.open('POST',url,async);
	  httpRequest.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	  httpRequest.send(parameters.substr(1));
	}
}
