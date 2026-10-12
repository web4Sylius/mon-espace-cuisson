<?php
require_once("admin_global.php");
include_once(ROOT_PATH."configurations/www.ma-cave-a-vin.fr/config.php");
include_once("./header.php");
include_once("./menu.php");

//Mise à jour configuration
if(!empty($_POST)){
     if(isset($_POST['maj_config']) && $_POST['maj_config'] == 1){
          
          //On efface tous les champs pour les recréer
          $sql = "DELETE FROM market_place_catid";
          $sql .= " WHERE market_place = '".$_POST['nom2']."'";
          $sql .= " AND catid = ".$_POST['catid'];
          $DB_site2->query($sql);
          
          //Remplissage des champs
          foreach($_POST as $name => $value){
               if(substr($name,0,6)=="field_"){
                    $name=substr($name,6);
                    
                    $sql = "INSERT INTO market_place_catid (";
                    $sql .= "market_place";
                    $sql .= ", champ";
                    $sql .= ", valeur";
                    $sql .= ", catid";
                    $sql .= ") VALUES (";
                    $sql .= "'".$_POST['nom2']."'";
                    $sql .= ", '".$name."'";
                    $sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$value)."'";
                    $sql .= ", '".$_POST['catid']."'";
                    $sql .= ")";
                    $DB_site2->query($sql);
               }
          }
     }
}



//Récupération de la liste des market place
$sql = "SELECT *";
$sql .= " FROM market_place WHERE market_place_nom = 'Fnac' OR market_place_nom = 'CDiscount' OR market_place_nom = 'Amazon' OR market_place_nom = 'RDC' OR market_place_nom = 'Redoute' OR market_place_nom = 'Pixmania' OR market_place_nom = 'PriceM' OR market_place_nom='delamaison' OR market_place_nom='conforama' ORDER BY market_place_nom";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $tabPDM[] = $row;
}

?>

<style> 
select {
    width: 25%;
    padding: 10px 10px;
    border: 2px solid rgb(168,141,91);
    border-radius: 4px;
    background-color: #f1f1f1;
}
th {
  background-color:rgb(168,141,91);
  border: 1px solid rgb(168,141,91);
  color: white;
  font-family: OpenSans;
  padding: 1px 20px;
}
.titre {
  color:black;
  font-family:OpenSans;
  font-size: 15;
  text-decoration:underline ;
}
.ligne {
  padding: 1px 10px 1px 10px;
  border: 1px solid rgb(168,141,91);
  text-align: center;
  margin: auto;
}
.jourbutton {
  background-color:rgb(168,141,91);
  color: white;
  font-family: OpenSans;
  font-size: 15;
  padding: 10px 10px;
  border: none;
  cursor: pointer;
}
.dlbutton{
  padding: 7px 10px;
  background: url(images/icon/export.png) no-repeat;
  border: none;
  cursor: pointer;
}
.flotte{
  float:left;
}
.suprbutton{
  padding: 3px 7px;
  background: url(images/icon/erreur.png) no-repeat;
  border: none;
  cursor: pointer;
}
</style>

<script type="text/javascript">
     function toggleCategorie(num){
          for(i=1;i<=<?php echo $nbCat; ?>;i++){
               if(i != num && document.getElementById('cat-'+i)){
                    document.getElementById('cat-'+i).style.display = 'none';
               }
          }
          
          if(document.getElementById('cat-'+num).style.display == 'block'){
               document.getElementById('cat-'+num).style.display = 'none';
          } else {
               document.getElementById('cat-'+num).style.display = 'block';
          }
     }
    
    function download(sFile)
    {
     var s  = document.createElement("script");
     s.src  = "download.php?sFilename="+sFile;
     s.type = "text/javascript";
     document.body.appendChild(s);
    }
     function testfields(form){
          var post = new Array();
          post['catid'] = form.catid.value;
          for(i=0;i<form.getElementsByTagName("TEXTAREA").length;i++){
               var oi=form.getElementsByTagName("TEXTAREA")[i];
               post[oi.name]=oi.value;     
          }
          for(i=0;i<form.getElementsByTagName("INPUT").length;i++){
               var oi=form.getElementsByTagName("INPUT")[i];
               post[oi.name]=oi.value;     
          }
	
          ajaxrequest('mp_cats_ajax.php',testcb,post);
     }

     function testcb(httprequest){
          document.getElementById('preview').innerHTML=httprequest.responseText;
          document.getElementById('previewdiv').style.display='block';
          document.getElementById('previewdiv').style.top=document.body.scrollTop+'px';
     }
</script>

<div id="previewdiv" style="display:none;position:absolute;background-color:#EEE;border:solid thin black;">
     <div id="preview" style="float:left;"></div>
     <div style="float:right;color:red;font-weight:bold;cursor:pointer;text-align:center;width:20px;" onclick="document.getElementById('previewdiv').style.display='none';">X</div>
</div>

<td valign="top">
     <table width="100%" border="0" cellpadding="0" cellspacing="0" style="float:left;">
          <tr>
               <td>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                         <tr>
                              <td>
                                   <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                             <td height="25" style="color:rgb(168,141,91);font-family:OpenSans;font-size:28" nowrap>&nbsp;&nbsp;Configuration des contenus</font></td>
                                        </tr>
                                   </table>
                              </td>
                         </tr>
                    </table>               </td>
          </tr>
          <tr>
               <td>
                    <table class="tableau_pdm" cellspacing="0">
                         <tr>
                              <td colspan="5">
                                 <p style="font-family:OpenSans;font-size:20">&nbsp;&nbsp;Place de marché :</p>
                                   <!-- <div class="filtres">-->
                                        &nbsp;&nbsp;<select onchange="if(this.value != ''){ location.href = '?mpid='+this.value; } else { location.href = ''; }">
                                             <option value="">- Choisir une place de marché -</option>
                                             <?php foreach($tabPDM as $la_pdm){ ?>
                                             <option value="<?php echo $la_pdm['market_place_id']; ?>" <?php echo isset($_GET['mpid']) && $_GET['mpid'] == $la_pdm['market_place_id'] ? 'selected' : ''; ?>><?php echo $la_pdm['market_place_nom_complet']; ?></option>
                                             <?php } ?>
                                        </select>
                                   <!--</div>-->
                              </td>
                         </tr> 
                              <?php 
                                  if (!isset($_GET['mpid']))
                                  {
                                    ?>                     
                                      <tr>
                                        <td colspan="24" class="information">Veuillez sélectionner une place de marché</td>
                                      </tr>
                                      <?php } ?>
                    </table>
                </td>
          </tr>
          <tr>
                <td>
                    <table>
                         <tr> 
                            <?php
                              if (isset($_GET['mpid']))
                                { ?>
                                 <div>
                                
                                  <?php
                                    $NB = $_GET['mpid'];
                                    if ($NB == 7) //pour Cdiscount
                                    {
                                      include (BACK_PATH."/includes/market_place/Cdiscount_export.php");
                                    }
                                    else if ($NB == 2) //pour la Fnac
                                    {
                                     include (BACK_PATH."/includes/market_place/Fnac_export.php");                                          
                                    }
                                    else if ($NB == 3) //pour amazon
                                    {
                                      include (BACK_PATH."/includes/market_place/Amazon_export.php");
                                    }
                                    else if ($NB == 1)//pour Rue du commerce
                                    {
                                      include (BACK_PATH."/includes/market_place/RDC_export.php");
                                    } 
                                    else if ($NB == 6)//pour LaRedoute
                                    {
                                      include (BACK_PATH."/includes/market_place/LaRedoute_export.php");
                                    }
                                    else if ($NB == 9) //pour Pixmania
                                    {
                                      include (BACK_PATH."/includes/market_place/Pixmania_export.php");
                                    }
                                    else if ($NB == 4)
                                    {
                                      include (BACK_PATH."/includes/market_place/Cdiscount_api.php");
                                    }
                                    else if ($NB == 13)
                                    {
                                        include (BACK_PATH."/includes/market_place/Delamaison_export.php");
                                    }
                                    else if ($NB == 14)
                                    {
                                        include (BACK_PATH."/includes/market_place/Conforama_export.php");
                                    }
                                    ?>         
                                 </div> 
                                 <?php
                                }    ?>                      
                         </tr>
                        
                        
                    </table>
               </td>
          </tr>
     </table>
</td>