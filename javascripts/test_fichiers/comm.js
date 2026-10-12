/*
	Classe d'abstraction de l'objet XMLHttpRequest pour Internet Explorer et Mozilla Firefox
	(c) 01.2005, Robloche & poof65
*/

/*
	Comment ça marche ?

	Il suffit d'instancier un objet comme ceci :
	var req = new CreateXMLHTTPRequestObject();
	
	Ensuite, vous disposez d'un certain nombre de méthodes pour réaliser des requêtes vers le serveur. Vous pouvez, si vous instanciez plusieurs objets, réaliser plusieurs requêtes concurrentes.
	
	Vous disposez des méthodes suivantes :
	 - getFileGet(url, data) :
		Fais une requête sur le fichier dont l'URL est passée en paramètre.
		On peut éventuellement passer des données en paramètre. Celles-ci seront transmises via la méthode GET.
		Renvoie true si la requête a démarré, false sinon.
		Exemples : req.getFileGet("foo.php");
		           req.getFileGet("foo.php", "var1=bar&var2=666");
				   if(!req.getFileGet("foo.php")) alert("Erreur : Impossible d'effectuer la requête...");

	 - getFile(url, data) :
	    getFile est juste un alias de getFileGet qui évite de préciser la méthode à employer dans le cas, par exemple, où l'on ne désire pas transmettre de données.
		
	 - getFilePost(url, data) :
		Fais une requête sur le fichier dont l'URL est passée en paramètre.
		On peut éventuellement passer des données en paramètre. Celles-ci seront transmises via la méthode POST.
		Renvoie true si la requête a démarré, false sinon.
		Exemples : req.getFilePost("foo.php");
		           req.getFilePost("foo.php", "var1=bar&var2=666");
				   if(!req.getFilePost("foo.php")) alert("Erreur : Impossible d'effectuer la requête...");

	 - getFileHeader(url, header) :
	 	Récupère tous les headers correspondant à l'URL passée en paramètre.
		Si un paramètre header est donné, seul le header demandé sera récupéré.
		Renvoie true si la requête a démarré, false sinon.
		Exemples : req.getFileHeader("foo.php");
				   if(!req.getFileHeader("foo.php")) alert("Erreur : Impossible d'effectuer la requête...");

	 - hasResponse() :
	 	Renvoie true quand une réponse est arrivée et false sinon. À utiliser en combinaison avec getFile() ou postData() et un setTimeout().
		Exemple :
		var req = new CreateXMLHTTPRequestObject();
		function getAFile() {
			if(!req.getFile("foo.php")) alert("Une requête est déjà en cours...");
			else   			            setTimeout("check()", 200);
		}
		function check() {
			if(!req.hasResponse()) {
				// Réponse pas arrivée
				setTimeout("check()", 200);
				return;
			}
	
			var a = req.getResponse();  // Réponse obtenue (on peut évidemment utiliser directement la variable req.response mais
			                            // en procédant ainsi, on "libère" l'objet qui peut alors exécuter une nouvelle requête)
			req.validateRequest();  // Cf. ci-dessous pour l'explication
			alert(a);
		}
		
	 - getResponse() :
		Retourne le résultat de la dernière requête.
		Exemple : Cf. exemple précédent.

	 - validateRequest() :
		Indique que le résultat de la requête a bien été pris en compte et qu'une nouvelle requête peut être lancée.
		Exemple : Cf. exemple précédent.
		
	 - cancelRequest() :
	 	Annule une requête en cours.

	 - setSynchronous() :
	    Passe en mode synchrone. Tant que la requête n'est pas complètement terminée, l'utilisateur n'a plus la main.

	 - setAsynchronous() :
	    Passe en mode asynchrone. La requête s'effectue en tâche de fond.

	 - setIndicatorFunction(func) :
	 	Cette fonction prend en paramètre une autre fonction. La fonction passé en paramètre sert à indiquer quand a lieu une communication avec le serveur. Elle doit recevoir un booléen en paramètre : true, la communication débute et false, elle est terminée.
		Utile pour faire un truc dans le style du "Sending" blanc sur fond rouge qui s'affiche lorsque vous envoyez un mail, dans GMail.
	
*/

function CreateXMLHTTPRequestObject() {
	// Propriétés
	
	this.xhr_object    = null;
	this.response      = null;
	this.ready         = true;
	this.asynchronous  = true;

	// Création de l'objet XMLHTTpRequest
	if(window.XMLHttpRequest) // Firefox
		this.xhr_object = new XMLHttpRequest();
	else if(window.ActiveXObject) // Internet Explorer
		this.xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
	else // XMLHttpRequest non supporté par le navigateur
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");



	// Méthodes

	// Appelle la fonction censée indiquer qu'une communication est en cours
	// La fonction doit être fournie par l'utilisateur de la classe et doit prendre un booléen en paramètre :
	//  - true  : la communication commence
	//  - false : la communication est terminée
	this.indicatorFunction = null;
	
	// Permet de définir la fonction qui servira d'indicateur de communication
	this.setIndicatorFunction = function(func) {
		if(typeof(func) == "function") this.indicatorFunction = func;
	}
	
	// Passe en mode synchrone
	this.setSynchronous = function() {
		this.asynchronous = false;
	}

	// Passe en mode asynchrone
	this.setAsynchronous = function() {
		this.asynchronous = true;
	}

	// Lance une requête sur un fichier du serveur en passant éventuellement des paramètres, avec la méthode GET
	this.getFileGet = function(url, data) {
		return this.doRequest(url, "GET", data);
	}

	// Alias de this.getFileGet
	this.getFile = this.getFileGet;
	
	// Lance une requête sur un fichier du serveur en passant éventuellement des paramètres, avec la méthode POST
	this.getFilePost = function(url, data) {
		return this.doRequest(url, "POST", data);
	}

	// Récupère tous les header associés à l'URL passée en paramètre, ou juste le header passé en paramètre s'il est précisé
	this.getFileHeader = function(url, header) {
		return this.doRequest(url, "HEAD", header);
	}

	// Effectue la requête proprement dite
	//  - method : GET, POST ou HEAD
	//  - url    : chemin vers un fichier
	//  - data   : données à transmettre (ex : a=5&foo=bar)
	this.doRequest = function(url, method, data) {
		if(!this.ready || !this.xhr_object) return false;

		// Recherche header_name dans tous les headers et retourne la valeur correspondante
		// ou "Header inconnu..." si header_name n'a pas été trouvé
		function _getResponseHeader(headers, header_name) {
			var tmp = headers.split("\n");
			for(var i=0, n=tmp.length, t=[]; i<n-1; ++i) {
				t = tmp[i].split(": ");
				if(t[0].toLowerCase() == header_name.toLowerCase()) return t[1];
			}
			return "Header inconnu...";
		}

		if(this.indicatorFunction) this.indicatorFunction(true);
		this.ready = false;

		// On copie la référence à l'objet courant car il ne sera plus "dans le contexte"
		// au moment où la fonction onreadystatechange sera exécutée
		var obj = this;
		function onreadystatechangeFunction() {
			if(obj.xhr_object.readyState != 4) return;
			
			if(obj.indicatorFunction) obj.indicatorFunction(false);

			var all_headers = obj.xhr_object.getAllResponseHeaders();
			if(method == "HEAD") {
				obj.response = data ? _getResponseHeader(all_headers, data) : all_headers;
			}
			else {
				var content_type = _getResponseHeader(all_headers, "Content-Type");
				if (content_type != "Header inconnu..." && (new RegExp("^text/xml.*$", "gi")).test(content_type))
					obj.response = obj.xhr_object.responseXML;
				else
					obj.response = obj.xhr_object.responseText;
			}
		}

		if(method == "GET" && typeof(data) != "undefined" && data != "") url += "?"+data;
		this.xhr_object.open(method, url, this.asynchronous);

		if(this.asynchronous)
			this.xhr_object.onreadystatechange = onreadystatechangeFunction;
		
		if(data) this.xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		else     data = null;
		this.xhr_object.send(data);

		if(!this.asynchronous)
			onreadystatechangeFunction();

		return true;
	}

	// Retourne true si la réponse est arrivée, false sinon
	this.hasResponse = function() {
		return this.response != null;
	}

	// Retourne la réponse à la dernière requête
	this.getResponse = function() {
		return this.response;
	}

	// Valide la requête, une nouvelle requête peut être faite avec ce même objet
	this.validateRequest = function() {
		this.ready    = true;
		this.response = null;
	}

	// Annule la requête en cours
	this.cancelRequest = function() {
		this.xhr_object.abort();
		if(this.indicatorFunction) this.indicatorFunction(false);
		this.validateRequest();
	}
}
