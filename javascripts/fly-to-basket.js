/************************************************************************************************************
(C) www.dhtmlgoodies.com, March 2006

This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	

Terms of use:
You are free to use this script as long as the copyright message is kept intact. However, you may not
redistribute, sell or repost it without our permission.

Version:
	1.0	Released	March. 3rd 2006

Thank you!

www.dhtmlgoodies.com
Alf Magne Kalleland

************************************************************************************************************/
var flyingSpeed = 6;
var url_addProductToBasket = 'addProduct.php';
var url_removeProductFromBasket = 'removeProduct.php';
var txt_totalPrice = 'Total: ';


var shopping_cart_div = false;
var flyingDiv = false;
var currentProductDiv = false;

var shopping_cart_x = false;
var shopping_cart_y = false;

var slide_xFactor = false;
var slide_yFactor = false;

var diffX = false;
var diffY = false;

var currentXPos = false;
var currentYPos = false;

var ajaxObjects = new Array();


function shoppingCart_getTopPos(inputObj)
{		
  var returnValue = inputObj.offsetTop;
  while((inputObj = inputObj.offsetParent) != null){
  	if(inputObj.tagName!='HTML')returnValue += inputObj.offsetTop;
  }
  return returnValue;
}

function shoppingCart_getLeftPos(inputObj)
{
  var returnValue = inputObj.offsetLeft;
  while((inputObj = inputObj.offsetParent) != null){
  	if(inputObj.tagName!='HTML')returnValue += inputObj.offsetLeft;
  }
  return returnValue;
}
	

function addToBasket(productId)
{
	if(!shopping_cart_div) shopping_cart_div = document.getElementById('shopping_cart');
	if(!flyingDiv){
		flyingDiv = document.createElement('DIV');
		flyingDiv.style.position = 'absolute';
		document.body.appendChild(flyingDiv);
	}
	
	shopping_cart_x = shoppingCart_getLeftPos(shopping_cart_div);
	shopping_cart_y = shoppingCart_getTopPos(shopping_cart_div);

	currentProductDiv = document.getElementById('slidingProduct' + productId);
	
	currentXPos = shoppingCart_getLeftPos(currentProductDiv);
	currentYPos = shoppingCart_getTopPos(currentProductDiv);
	
	diffX = shopping_cart_x - currentXPos;
	diffY = shopping_cart_y - currentYPos;
	
	
	var shoppingContentCopy = currentProductDiv.cloneNode(true);
	shoppingContentCopy.id='';
	flyingDiv.innerHTML = '';
	flyingDiv.style.left = currentXPos + 'px';
	flyingDiv.style.top = currentYPos + 'px';
	flyingDiv.appendChild(shoppingContentCopy);
	flyingDiv.style.display='block';
	flyingDiv.style.width = currentProductDiv.offsetWidth + 'px';
	flyToBasket(productId);
	
}


function flyToBasket(productId)
{
	var maxDiff = Math.max(Math.abs(diffX),Math.abs(diffY));
	var moveX = (diffX / maxDiff) * flyingSpeed;;
	var moveY = (diffY / maxDiff) * flyingSpeed;	
	
	currentXPos = currentXPos + moveX;
	currentYPos = currentYPos + moveY;
	
	flyingDiv.style.left = Math.round(currentXPos) + 'px';
	flyingDiv.style.top = Math.round(currentYPos) + 'px';	
	
	
	if(moveX>0 && currentXPos > shopping_cart_x){
		flyingDiv.style.display='none';		
	}
	if(moveX<0 && currentXPos < shopping_cart_x){
		flyingDiv.style.display='none';		
	}
		
	if (flyingDiv.style.display == 'block')
		{
		setTimeout('flyToBasket("' + productId + '")',10); 
		}
	else 
		{
		ajaxAddProduct(productId);	
		}
}

function ajaxAddProduct(productId) {
	/* ajout pour récupérer les cractéristiques et la quantité */
	var maliste = '' ;
	if (document.getElementById('listes'))
		{
		var caracteristiques = document.getElementById('listes').value;
		}
	else
		{
		var caracteristiques = document.getElementById('listes'+productId).value;
		}
	var montableaudecaracteristiques=caracteristiques.split(',');
	var x;
	for (x=1; x<montableaudecaracteristiques.length; x++) 
		{
		var caractvalid = document.getElementById('lst_'+montableaudecaracteristiques[x]).options[document.getElementById('lst_'+montableaudecaracteristiques[x]).selectedIndex].value ;
		maliste += '&lst_'+montableaudecaracteristiques[x]+' = '+caractvalid;
		}
	if (document.getElementById('qte'))
		{
		var qte = document.getElementById('qte').value;
		}
	else
		{
		var qte = document.getElementById('qte'+productId).value;
		}
s
	/* ajout pour récupérer les cractéristiques et la quantité */
	var ajaxIndex = ajaxObjects.length;
	ajaxObjects[ajaxIndex] = new sack();
	ajaxObjects[ajaxIndex].requestFile = url_addProductToBasket + '?productId=' + productId + '&qte=' + qte + maliste; // Saving product in this file
	ajaxObjects[ajaxIndex].onCompletion = function(){ showAjaxBasketContent(ajaxIndex); };	// Specify function that will be executed after file has been found
	ajaxObjects[ajaxIndex].runAJAX();		// Execute AJAX function		
	}

function showAjaxBasketContent(ajaxIndex)
	{
	// Getting a reference to the shopping cart items table
	var itemBox = document.getElementById('shopping_cart_items');
	
	var resultat = ajaxObjects[ajaxIndex].response;	// Breaking response from Ajax into tokens
	myresultat = resultat.split('|||');

	if (resultat.substring(0,2) == 'OK')
		{// A product with this id is allready in the basket - just add number items
		myresultat = resultat.split('|||');
		//alert('0 : '+myresultat[0]+' - 1 : '+myresultat[1]+' - 2 : '+myresultat[2]+' - 3 : '+myresultat[3]);
		if (myresultat[1] == 'OUI')
			{
			var nb = document.getElementById('contenupanier').value;
			//alert('ok document.getElementById(contenupanier).value : '+document.getElementById('contenupanier').value);
			nnb = parseFloat(nb) + parseFloat(1) ;
			document.getElementById('contenupanier').value = nnb ;
			alert('Article ajouté au panier.\nPour visualiser votre commande, cliquez sur le bouton commander en haut à droite.');
			}
		else if (myresultat[1] == 'NON')
			{
			alert('Un article identique existe déjà dans votre panier, la quantité a été augmentée.\nPour visualiser votre commande, cliquez sur le bouton commander en haut à droite.');
			}
		else if (myresultat[1] == 'STOCK')
			{
			alert('Le stock n est pas assez important pour honorer votre commande.\nPour visualiser votre commande, cliquez sur le bouton commander en haut à droite.');	
			}
		else
			{
			alert('Une erreur s\'est produite pendant l\'ajout au panier.\nPour visualiser votre commande, cliquez sur le bouton commander en haut à droite.');
			}
		if (myresultat[2] != '')
			{
			document.getElementById('montantpanier').value = myresultat[2] ;
			}
	
		if (itemBox) {
			var productItems = myresultat[4].split('&&');
			var x2;
			for (x2 = 1 ; x2 < productItems.length ; x2++) {
				if (productItems[x2] != "") {
					var productItemsValues = productItems[x2].split('<>');
					if (!document.getElementById('shopping_cart_items_product' + productItemsValues[0])) {
						var tableau = document.getElementById('tabcontenupanier');
						var tr = tableau.insertRow(-1);
						tr.id = 'shopping_cart_items_product' + productItemsValues[0]
						
						var td = tr.insertCell(-1);
						//td.style.textAlign = 'right';
						td.innerHTML = productItemsValues[1]; // libelle
						
						var td = tr.insertCell(-1);
						td.innerHTML = "<b>"+productItemsValues[2]+" €</b>"; // prix
						
						var td = tr.insertCell(-1);
						var a = document.createElement('A');
						td.appendChild(a);
						a.href = '#';
						a.onclick = function(){ removeProductFromBasket(productItemsValues[0], productItemsValues[3]); };
						var img = document.createElement('IMG');
						img.src = myresultat[3];
						a.appendChild(img);
						}
					}
				}
			}
		}
	else
		{
		alert('Une erreur s\'est produite pendant l\'ajout au panier.');
		} 
	ajaxObjects[ajaxIndex] = false;		
	}

		//htmlbox += '</table>' ;
		
			/*
					if (document.getElementById('shopping_cart_items_product' + productItems[0])) {	// A product with this id is allready in the basket - just add number items
						var row = document.getElementById('shopping_cart_items_product' + productItems[0]);
						var items = row.cells[0].innerHTML /1;
						items = items + 1;
						row.cells[0].innerHTML = items;
						}
					else
						{	// Product isn't allready in the basket - add a new row
						var tr = itemBox.insertRow(-1);
						tr.id = 'shopping_cart_items_product' + productItems[0]
						
						var td = tr.insertCell(-1);
						td.innerHTML = '1'; 	// Number of items
						
						var td = tr.insertCell(-1);
						td.innerHTML = productItems[1]; 	// Description
						
						var td = tr.insertCell(-1);
						td.style.textAlign = 'right';
						td.innerHTML = productItems[2]; 	// Price	
						
						var td = tr.insertCell(-1);
						var a = document.createElement('A');
						td.appendChild(a);
						a.href = '#';
						a.onclick = function(){ removeProductFromBasket(productItems[0]); };
						var img = document.createElement('IMG');
						img.src = 'images/remove.gif';
						a.appendChild(img);
						//td.innerHTML = '<a href="#" onclick="removeProductFromBasket("' + productItems[0] + '");return false;"><img src="images/remove.gif"></a>';	
						} 
				
					updateTotalPrice();
					ajaxObjects[ajaxIndex] = false;		*/


function removeProductFromBasket(productId, ligneid) {
	var productRow = document.getElementById('shopping_cart_items_product' + productId);
	productRow.parentNode.removeChild(productRow);	
	ajaxRemoveProduct(ligneid);	
	}


function ajaxRemoveProduct(ligneid) {
	var ajaxIndex = ajaxObjects.length;
	ajaxObjects[ajaxIndex] = new sack();
	ajaxObjects[ajaxIndex].requestFile = url_removeProductFromBasket + '?artpanierid=' + ligneid;	// Saving product in this file
	ajaxObjects[ajaxIndex].onCompletion = function(){ ajaxValidateRemovedProduct(ajaxIndex); };	// Specify function that will be executed after file has been found
	ajaxObjects[ajaxIndex].runAJAX();		// Execute AJAX function		
	}


function ajaxValidateRemovedProduct(ajaxIndex) {
	if (ajaxObjects[ajaxIndex].response.substring(0,5) == 'LIGNE') {
		myresultat = ajaxObjects[ajaxIndex].response.split('|||');
		myresultat[1] = parseFloat(myresultat[1]) ;
		document.getElementById('contenupanier').value = myresultat[1] ;
		document.getElementById('montantpanier').value = myresultat[2] ;	
		}
	else
		alert('Erreur pendant la suppression');
	//if(ajaxObjects[ajaxIndex].response!='OK')
	}

/*
function updateTotalPrice()
{
	var itemBox = document.getElementById('shopping_cart_items');
	// Calculating total price and showing it below the table with basket items
	var totalPrice = 0;
	if(document.getElementById('shopping_cart_totalprice')){
		for(var no=1;no<itemBox.rows.length;no++){
			totalPrice = totalPrice + (itemBox.rows[no].cells[0].innerHTML.replace(/[^0-9]/g) * itemBox.rows[no].cells[2].innerHTML);
			
		}		
		document.getElementById('shopping_cart_totalprice').innerHTML = txt_totalPrice + totalPrice.toFixed(2);
		
	}	
	
}
*/


