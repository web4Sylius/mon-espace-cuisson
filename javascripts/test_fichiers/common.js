// Si la page apparaît dans une frame, on repasse dans la frame principale
if (top.frames.length != 0) top.location.href = self.document.location.href;

// Détection du navigateur (enfin, presque)
var browser   = navigator.userAgent;
var isOpera   = (browser.indexOf("Opera") != -1);
var isIE      = (!isOpera && browser.indexOf("MSIE") != -1);
var isMozilla = (!isOpera && !isIE && browser.indexOf("Mozilla") != -1);

// Donne le focus à l'élément dont l'ID est passé en paramètre
function giveFocus(theId) {
	document.getElementById(theId).focus();
}

// Tableau qui stocke les couleurs des boutons désactivés
// (Uniquement utile sous Opera qui ne désactive pas "visuellement" les boutons)
var saved_colors = new Array;

// Stocke la couleur de l'objet
function saveColor(itemId, color) {
	saved_colors[itemId] = color;
}

// Récupère la couleur de l'objet
function restoreColor(itemId) {
	if(typeof(saved_colors[itemId]) == "undefined") return "#000000";
	if(saved_colors[itemId] == "") return "#000000";
	return saved_colors[itemId];
}

// Désactive les éléments dont les ID sont passés en paramètres
function disableItem() {
	var items = disableItem.arguments;
	if(items.length == 0) return;
	for(var d=0; d<items.length; ++d) {
		if(document.getElementById(items[d]).disabled) continue;
		document.getElementById(items[d]).disabled = true;
		if(isOpera) {
			// Opera ne "montre" pas les boutons désactivés
			var el = document.getElementById(items[d])
			if(el.type == "button" || el.type == "reset" || el.type == "submit" || el.type == "file")
				saveColor(el.id, el.style.color);
				el.style.color = "#808080";
		}
	}
}

// Active les éléments dont les ID sont passés en paramètres
function enableItem() {
	var items = enableItem.arguments;
	if(items.length == 0) return;
	for(var d=0; d<items.length; ++d) {
		if(!document.getElementById(items[d]).disabled) continue;
		document.getElementById(items[d]).disabled = false;
		if(isOpera) {
			// Opera ne "montre" pas les boutons désactivés
			var el = document.getElementById(items[d])
			if(el.type == "button" || el.type == "reset" || el.type == "submit" || el.type == "file")
				el.style.color = restoreColor(el.id);
		}
	}
}

// Ouvre une nouvelle fenêtre pour afficher l'image imgFile, de dimension imgWidth x imgHeight
function myPopup(imgFile, imgWidth, imgHeight) {
	var imgLeft = (screen.width-imgWidth)/2;
	var imgTop  = (screen.height-imgHeight)/2;
	var wPopup  = window.open("", "_blank", "width="+imgWidth+", height="+imgHeight+", left="+imgLeft+", top="+imgTop);
	wPopup.document.write('<html><head><title>Cliquez dans cette fenêtre pour la fermer</title></head><body style="margin: 0px; padding: 0px;" onclick="window.close();"><img src="'+imgFile+'" alt="" title="Cliquez dans cette fenêtre pour la fermer" style="width: '+imgWidth+'; height: '+imgHeight+';" /></body></html>');
	wPopup.document.close();
	wPopup.focus();
}
