   function getParam(p) {
     var match = RegExp('[?&]' + p + '=([^&]*)').exec(window.location.search);
     return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
   }

   function addToStorage(key, value) {
     var expiryPeriod = 90 * 24 * 60 * 60 * 1000; // 90 day expiry in milliseconds
     var expiryDate = new Date().getTime() + expiryPeriod;
     var record = { value: value, expiryDate: expiryDate };
     localStorage.setItem(key, JSON.stringify(record));
   }
 
   function storeGclid() {
     var gclidParam = getParam('gclid');

     if (gclidParam) {
       addToStorage('gclid', gclidParam);
     }
   }

   function addGclid() {
     storeGclid(); // store gclid param to localstorage

     var gclidFormField = document.getElementById('gclid_field');
     var currDate = new Date().getTime();

     var gclsrcParam = getParam('gclsrc');
     var isGclsrcValid = !gclsrcParam || gclsrcParam.indexOf('aw') !== -1;
 
     var gclid = JSON.parse(localStorage.getItem('gclid'));
     var isGclidValid = gclid && currDate < gclid.expiryDate;


     if (gclidFormField && isGclidValid && isGclsrcValid) {
       gclidFormField.value = gclid.value;
     }
   }

  window.addEventListener('load', addGclid);

  //on vérifie si un gclid a deja ete enregistré
  if(localStorage.gclid != undefined){
    var gclid = JSON.parse(localStorage.gclid) ;
    document.getElementById('gclid_field').value = gclid.value;

    //connexion popup
    if(document.getElementById('gclidlogpop') != undefined && document.getElementById('gclidlogpop') != null) {
      document.getElementById('gclidlogpop').value = gclid.value;
    }
	
    //connexion page client
    if(document.getElementById('gclidlog') != undefined && document.getElementById('gclidlog') != null) {
      document.getElementById('gclidlog').value = gclid.value;
    }
	
    //connexion configurateur et devis
    if(document.getElementById('gclid1') != undefined && document.getElementById('gclid1') != null) {
      document.getElementById('gclid1').value = gclid.value;
    }
	
    //inscription
    if(document.getElementById('gclid') != undefined && document.getElementById('gclid') != null) {
      document.getElementById('gclid').value = gclid.value;
    }
  }