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
var flyingSpeed = 10;
var url_addProductToBasket = '/addProduct.php';
var url_removeProductFromBasket = '/removeProduct.php';
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

var artid=0;


function shoppingCart_getTopPos2(inputObj)
{		
  var returnValue = inputObj.offsetTop;
  while((inputObj = inputObj.offsetParent) != null){
  	if(inputObj.tagName!='HTML')returnValue += inputObj.offsetTop;
  }
  return returnValue;
}

function shoppingCart_getLeftPos2(inputObj)
{
  var returnValue = inputObj.offsetLeft;
  while((inputObj = inputObj.offsetParent) != null){
  	if(inputObj.tagName!='HTML')returnValue += inputObj.offsetLeft;
  }
  return returnValue;
}
	

function addToBasket2(productId)
{
	if(!shopping_cart_div)shopping_cart_div = document.getElementById('shopping_cart');
	if(!flyingDiv){
		flyingDiv = document.createElement('DIV');
		flyingDiv.style.position = 'absolute';
		document.body.appendChild(flyingDiv);
	}
	
	shopping_cart_x = shoppingCart_getLeftPos2(shopping_cart_div);
	shopping_cart_y = shoppingCart_getTopPos2(shopping_cart_div);

	currentProductDiv = document.getElementById('slidingProduct' + productId);
	
	currentXPos = shoppingCart_getLeftPos2(currentProductDiv);
	currentYPos = shoppingCart_getTopPos2(currentProductDiv);
	
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
	flyToBasket2(productId);
	
}


function flyToBasket2(productId)
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
		setTimeout('flyToBasket2("' + productId + '")',10); 
		}
	else 
		{
		ajaxAddProduct2(productId);	
		}
}

function ajaxAddProduct2(productId,noopt ,accesPanier ){

	/* ajout pour récupérer les cractéristiques et la quantité */
	var maliste = '' ;
	var identifiant = new RegExp('lst_', "g");
	var mesDiv = document.getElementsByTagName("input");
	if(typeof(accesPanier) == 'undefined'){
		accesPanier = 0;
	}
	for(var j=0; j<mesDiv.length; j++){
		var libelle = mesDiv[j].id;
		if (libelle.match(identifiant)){
			var name = mesDiv[j].name;
      		var idinput = libelle.split('_');
      		var caractid = idinput[1];
      		var caractvalid = document.getElementById(libelle).value;
            if(!option_checked(name)){
                document.getElementById('opterror').style.display='block';
		  		htmlalert("<i class='fa fa-exclamation-triangle'></i>"+document.getElementById("opterror").innerHTML);
	            return;
	        }
	        if (document.getElementById(libelle).checked){
				if(caractid == 'ral'){
					caractid = "ral[]";
				} 
	            maliste += '&lst_'+caractid+'='+caractvalid;
	        }
	    }
	}

	if(noopt==true) maliste="";		
		
	if (document.getElementById('qte')){
		var qte = document.getElementById('qte').value;
	}
	else
	{
		if(document.getElementById('qte'+productId)){
			var qte = document.getElementById('qte'+productId).value;
		}else{
			var qte = 1;
		}
		
	}
	/* ajout pour récupérer les cractéristiques et la quantité */

	artid=productId;

	/* PIXEL FACEBOOK pour ajout panier*/
	if(getCookie("_fbp") != ""){
		if(typeof(fbq) === "function"){
			fbq('track', 'AddToCart', {content_ids: ['\''+artid+'\''],content_type: 'product',currency: 'EUR'});
		}	
	}
	
	ajaxrequest(url_addProductToBasket + '?accesPanier='+accesPanier+'&productId=' + productId + '&qte=' + qte + maliste,showAjaxBasketContent2);

}

function ajaxAddProduct2Mobile(productId,noopt,accesPanier)
{
	/* ajout pour récupérer les cractéristiques et la quantité */
	var maliste = '' ;
	var identifiant = new RegExp('lst_', "g");
	mesDiv = document.getElementsByTagName("input");
	if(typeof(accesPanier) == 'undefined'){
		accesPanier = 0;
	}
	for(var j=0; j<mesDiv.length; j++)
		{
		var libelle = mesDiv[j].id;

			if (libelle.match(identifiant)){           
	              var name = mesDiv[j].name;
	              var idinput = libelle.split('_');
	              var caractid = idinput[1];
	              var caractvalid = document.getElementById(libelle).value;

	              if(!option_checked(name)){
	                   document.getElementById('opterror').style.display='block';
			   htmlalert("<i class='fa fa-exclamation-triangle'></i>"+document.getElementById("opterror").innerHTML);
	                   return;
	              }
	              
	              if (document.getElementById(libelle).checked){
					  if(caractid == 'ral'){
							caractid = "ral[]";
						} 
	                   maliste += '&lst_'+caractid+'='+caractvalid;
	              }
	         }
		}

	if(noopt==true) maliste="";		
		
	if (document.getElementById('qte'))
		{
		var qte = document.getElementById('qte').value;
		}
	else
		{
		var qte = document.getElementById('qte'+productId).value;
		}
	/* ajout pour récupérer les cractéristiques et la quantité */

	artid=productId;

	/* PIXEL FACEBOOK pour ajout panier*/
	if(getCookie("_fbp") != ""){
		if(typeof(fbq) === "function"){
			fbq('track', 'AddToCart', {content_ids: ['\''+artid+'\''],content_type: 'product',currency: 'EUR'});
		}
	}
	
	ajaxrequest(url_addProductToBasket + '?accesPanier='+accesPanier+'&productId=' + productId + '&qte=' + qte + maliste,showAjaxBasketContent2Mobile);
		
}




function copy_qte_option(artid){
	$("#qte"+artid).val($("#qte2"+artid).val());
}
function copy_qte_from_option(artid){
	$("#qte2"+artid).val($("#qte"+artid).val());
}




function option_checked(inputname){
     var btns=document.getElementsByName(inputname);
     
     for(i=0;i<btns.length;i++){
          if((btns[i].type=="radio" && btns[i].checked) || btns[i].disabled) return true;
     }
         
     return false;
}

function showAjaxBasketContent2(ajaxIndex)
	{
	// Getting a reference to the shopping cart items table
	var itemBox = document.getElementById('shopping_cart_items');
	
	var resultat=ajaxIndex.responseText;
	//var resultat = ajaxObjects[ajaxIndex].response;	// Breaking response from Ajax into tokens
	myresultat = resultat.split('|||');

	if (resultat.substring(0,2) == 'OK')
	{// A product with this id is allready in the basket - just add number items
		myresultat = resultat.split('|||');
		var accesPanier = parseInt(myresultat[7]);
		if(myresultat[6]==1 && accesPanier == 0){
			var prixtt = document.getElementById('prix_par_caract').textContent;
			prixtt = prixtt.match(/[0-9,]+/)[0];
		    tb_show('','/ajax/ajout_panier.php?artid='+artid+'&prix='+prixtt+'&width=970&height=400',false);
		}else    
		    document.location="/cde1.php";

		//Mise à jour du panier
		loadAjaxPanier();
	}
	else
	{
		alert('Une erreur s\'est produite pendant l\'ajout au panier.\nPour visualiser votre commande, cliquez sur le bouton commander en haut à droite.');
	} 

		ajaxObjects[ajaxIndex] = false;
	}

function showAjaxBasketContent2Mobile(ajaxIndex)
	{
	// Getting a reference to the shopping cart items table
	var itemBox = document.getElementById('shopping_cart_items');
	
	var resultat=ajaxIndex.responseText;
	//var resultat = ajaxObjects[ajaxIndex].response;	// Breaking response from Ajax into tokens

	if (resultat.substring(0,2) == 'OK')
	{// A product with this id is allready in the basket - just add number items
		  
		document.location="/cde1.php";

		//Mise à jour du panier
		loadAjaxPanier();
	}
	else
	{
		alert('Une erreur s\'est produite pendant l\'ajout au panier.\nPour visualiser votre commande, cliquez sur le bouton commander en haut à droite.');
	} 

		ajaxObjects[ajaxIndex] = false;
	}	