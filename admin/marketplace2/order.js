var prices=new Array();
var buyprices=new Array();
var descriptions=new Array();
var barcodes=new Array();
var stocks=new Array();
var strike_out_prices=new Array();
var artids=new Array();

function copybilling(){
               if(document.getElementsByName('copy')[0].checked){
                   document.getElementsByName('shipping_company')[0].value=document.getElementsByName('billing_company')[0].value;
                   document.getElementsByName('shipping_last_name')[0].value=document.getElementsByName('billing_last_name')[0].value;
                   document.getElementsByName('shipping_first_name')[0].value=document.getElementsByName('billing_first_name')[0].value;
                   document.getElementsByName('shipping_address_1')[0].value=document.getElementsByName('billing_address_1')[0].value;
                   document.getElementsByName('shipping_address_2')[0].value=document.getElementsByName('billing_address_2')[0].value;
                   document.getElementsByName('shipping_zip')[0].value=document.getElementsByName('billing_zip')[0].value;
                   document.getElementsByName('shipping_city')[0].value=document.getElementsByName('billing_city')[0].value;
		   document.getElementsByName('shipping_country')[0].value=document.getElementsByName('billing_country')[0].value;
                   document.getElementsByName('shipping_phone')[0].value=document.getElementsByName('billing_phone')[0].value;
                   document.getElementsByName('shipping_fax')[0].value=document.getElementsByName('billing_fax')[0].value;
               }else{
                   document.getElementsByName('shipping_company')[0].value='';
                   document.getElementsByName('shipping_last_name')[0].value='';
                   document.getElementsByName('shipping_first_name')[0].value='';
                   document.getElementsByName('shipping_address_1')[0].value='';
                   document.getElementsByName('shipping_address_2')[0].value='';
                   document.getElementsByName('shipping_zip')[0].value='';
                   document.getElementsByName('shipping_city')[0].value='';
		   document.getElementsByName('shipping_city')[0].value='';
                   document.getElementsByName('shipping_phone')[0].value='';
                   document.getElementsByName('shipping_fax')[0].value=''
               }
      }

      function srcprod(){
               var src=document.getElementById('newsrc').value;
               if(src=='') return;
               var url="src.php?txt="+src;
               ajaxrequest(url,srcCallback);
      }

      function srcCallback(httprequest){
               var xml=httprequest.responseXML.documentElement;
               var html="";
               if(xml.getElementsByTagName('prod')[0]){
                    html+="<table width='100%' style='border:solid thin #808080;' class='txt'><tr>";
                    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
                         var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
                         var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
                         var price=xml.getElementsByTagName('prod')[i].getAttribute('price');
                         var strike_out=xml.getElementsByTagName('prod')[i].getAttribute('strike_out');
                         var buyprice=xml.getElementsByTagName('prod')[i].getAttribute('buyprice');
                         var barcode=xml.getElementsByTagName('prod')[i].getAttribute('barcode');
                         var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
                         var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
                         html+="<tr><td style=\"border-bottom:solid thin #cccccc;\">"+id+"</td><td style=\"border-bottom:solid thin #cccccc;\">"+description+"<br><span style=\"color:red\">"+stock+"</span></td><td style=\"border-bottom:solid thin #cccccc;\"><a href=\"javascript:selectprod('"+id+"')\">Sélectionner</a></td></tr>";
                         prices[id]=Math.round(price*tva_coeff*100)/100;
                         strike_out_prices[id]=Math.round(strike_out*tva_coeff*100)/100;
                         buyprices[id]=buyprice;
                         descriptions[id]=description;
                         barcodes[id]=barcode;
                         stocks[id]=stock;
                         artids[id]=artid;
                    }
                    html+="</table>";
               }else
                   html="Pas de résultat";

               document.getElementById('srcresults').innerHTML=html;
               document.getElementById('srcresults').style.visibility='visible';
               document.getElementById('srcresults').style.position='relative';
      }

      function selectprod(comp_id){
              document.getElementById('srcresults').style.visibility='hidden';
              document.getElementById('srcresults').style.position='absolute';
              document.getElementById('addprod').style.visibility='visible';
              document.getElementById('addprod').style.position='relative';
              document.getElementsByName('prod')[0].value=comp_id;
              document.getElementsByName('buyprice')[0].value=buyprices[comp_id];
              document.getElementsByName('price')[0].value=prices[comp_id];
              document.getElementsByName('strikeout_price')[0].value=strike_out_prices[comp_id];
              document.getElementsByName('description')[0].value=descriptions[comp_id];
              document.getElementsByName('barcode')[0].value=barcodes[comp_id];

              document.getElementById('divopts').innerHTML="<p align=\"center\"><img src=\"../../configurations/www.ma-cave-a-vin.fr/images/templates/circle_animation.gif\"></p>";
              var url="src.php?artid="+artids[comp_id];
              ajaxrequest(url,caractCallback);
      }

      function caractCallback(httprequest){
               document.getElementById('divopts').innerHTML="";
               var options=new Array();
               var xml=httprequest.responseXML.documentElement;
               if(xml.getElementsByTagName('caract')[0]){
                     for(i=0;i<xml.getElementsByTagName('caract').length;i++){
                           var id=xml.getElementsByTagName('caract')[i].getAttribute('id');
                           var name=xml.getElementsByTagName('caract')[i].getAttribute('name');
                           var libelle=xml.getElementsByTagName('caract')[i].getAttribute('value');
                           if(!options[name]) options[name]=new Array();
                           options[name][id]=libelle;
                     }


                     var i=0;

                     for(name in options){
                              var newselect = document.createElement('select');
                              newselect.name="caract_"+i;
                              var newname=document.createElement('input');
                              newname.type='hidden';
                              newname.name='caractname_'+i;
                              newname.value=name;
                              for(id in options[name]){
                                     var opt=new Option(options[name][id],id);
                                     newselect.options[newselect.length]=opt;
                              }

                              i++;

                              var p=document.createElement('p');
                              p.style.margin='0px;'
                              var txt=document.createElement('span');
                              txt.innerHTML=name+" : ";
                              p.appendChild(txt);
                              p.appendChild(newname);
                              p.appendChild(newselect);
                              document.getElementById('divopts').appendChild(p);
                     }
               }
      }

      function submitline(){
              document.forms.addline.submit();

      }

      function sendcmd(){
             document.location='index.php?order='+document.getElementsByName('orderid')[0].value+'&sendcmd=1';
      }

      function delivered_state(){
             if(confirm("Confirmez-vous que la commande à bien été envoyée au client ?"))
                        document.location='index.php?order='+document.getElementsByName('orderid')[0].value+'&state=2';
      }

      function srccmd(){
               document.location="index.php?order="+document.getElementById('src').value;
      }

