<?php


$contenu_onglet.=<<<HTML
<script language="javascript">

function listcaract(){
    list=new Array();
    for(i=0;i<document.forms.addform.getElementsByTagName('INPUT').length;i++){
	var elem=document.forms.addform.getElementsByTagName('INPUT')[i];
	if(elem.name.substr(0,7)=='caract_' && elem.checked && elem.value!='') list.push(elem.value);
    }
    document.getElementsByName('caracts')[0].value=list.join(',');
}


function saveprice(){
    document.getElementsByName('old')[0].value='pack';
    document.getElementsByName('action2')[0].value='pack';
    document.forms.formu.action='ajout_pack.php?updateprice=1';
    document.forms.formu.submit();
}
</script>
HTML;

$contenu_onglet.= "
<h3>Composition du pack</h3>";

$pack=new pack($DB_site2);
$produits=$pack->getProduits($artid);

$realart=0;
if(count($produits)>0){
  $artnb=0;
  //$contenu_onglet.="<br><input type=\"checkbox\" name=\"facture_diss\" value=\"1\" onclick=\"document.forms.addform.action='ajout_pack.php?facture_diss='+(this.checked?1:0);document.forms.addform.submit();\" ".($article['facture_diss']==1?"checked":"")."> Dissocier dans la facture<br><br>";
  $contenu_onglet.="<table><tr>";
  foreach($produits as $lart){
    $typeoffre=explode('-', $lart['offre'])[0];
    $chiffreoffre=explode('-', $lart['offre'])[1];

    $opt="";
    if($lart['caractvalids']!=""){
      $requete2=$DB_site2->query("SELECT cv.libelleFR AS caractval,c.libelleFR AS caract FROM caracteristiquevaleur cv INNER JOIN caracteristique c USING(caractid) WHERE cv.caractvalid IN (".$lart['caractvalids'].")");
      while($caract=$DB_site2->fetch_array($requete2)) $opt.=$caract['caract'].":".$caract['caractval']."<br>";
    }

    if($lart["prixpack"]>0){
      $contenu_onglet.= <<<HTML
      <td style="width:160px;border:solid 1px #CCC;padding:5px;">
        <img src="images/supprimer.gif" style="cursor:pointer;position:absolute;" onclick="document.forms.addform.action='ajout_pack.php?delartid=$lart[id]';document.forms.addform.submit();">
        <img src="/configurations/$host/images/produits/$lart[artid].jpg" alt="$larticle[libelleFR]" style="display:block;max-height:100px;max-width:100px;margin:auto;">
        <br><span style="font-size:18px;">$lart[qte]x</span> $lart[artcode] ($lart[artcode_eurocave])
      </td>
HTML;
      
	  $artnb++;
	  if($artnb<4)
      $contenu_onglet.=<<<HTML
      <td style="width:20px;text-align:center;font-size:20px;">+</td>
HTML;
    }else{
      $kdoartid=$lart['artid'];
      $kdoartcode=$lart['artcode'];
      $realart++;
    }
  }
}

if(count($realart)<4)
  $contenu_onglet.=<<<HTML
  <td style="width:160px;border:solid 1px #CCC;padding:5px;">
	Ajouter un article :
	<br><input type="text" id="src" onkeyup="srcarticle(this.value)">
	<form name="addform" action="ajout_pack.php" method="post">
  	<input type="hidden" name="old" value="pack">
  	<input type="hidden" name="delartid">
  	<input type="hidden" name="artid" value="$artid">
  	<input type="hidden" name="addartid">
  	<input type="hidden" name="caracts">
  	<div id="addprod" style="display:none;">
      <p align="center">Ajouter <span id="addartcode"></span> au pack</p>
      Quantité : <input type="text" name="qte" value="1" size="2">
      <br>Prix de vente : <input type="text" name="addprix" size="5" readonly>&euro;
      <br>Prix d'achat : <input type="text" name="addpaht" size="5" readonly>&euro;
      <div id="addopt" style=""></div>
      <input type="submit" id="submitbtn" value="Ajouter l'article" style="display:none;">
  	</div>
	</form>
  </td>
HTML;
else
  $contenu_onglet.=<<<HTML
  <form name="addform" action="ajout_pack.php" method="post">
    <input type="hidden" name="old" value="pack">
    <input type="hidden" name="delartid">
    <input type="hidden" name="artid" value="$artid">
  </form>
HTML;

switch($typeoffre){
    case "euro":
    $euro = "checked";
    $euroValue = $chiffreoffre;
    $txtvaleur=$chiffreoffre."&euro;";
    break;
    
    case "pourcent":
    $pourcent = "checked";
    $pourcentValue = $chiffreoffre;
    $txtvaleur=$chiffreoffre."%";
    break;
    
    case "pdtoffert":
    $pdtoffert = "checked";
    $txtvaleur="<img src=\"../configurations/".$host."/images/produits/".$kdoartid.".jpg\" style=\"max-width:100px;max-height:100px;\"><br>".$kdoartcode;
    break;
}

if(count($produits)>1){
  $contenu_onglet.=<<<HTML
      <td style="width:20px;text-align:center;font-size:20px;">=</td>
      <td style="width:160px;border:solid 1px #CCC;padding:5px;">
          <form name="formu" action="ajout_pack.php" method="post">
            <input type="hidden" name="modif" value="0">
            <input type="hidden" name="old" value="prix">
            <input type="hidden" name="artid" value="$artid">
            <input type="hidden" name="kdoartid" value="$kdoartid">
            <h4>Offre pack :</h4>
            <ul>
              <li><input type="radio" name="offrePack" value="euro" $euro onchange="setmodif();showOffers(this.value);"/> Remise en ¤</li>
              <li><input type="radio" name="offrePack" value="pourcent" $pourcent onchange="setmodif();showOffers(this.value);"/> Remise en %</li>
              <li><input type="radio" name="offrePack" value="pdtoffert" $pdtoffert onchange="setmodif();showOffers(this.value);"/> Produit offert</li>
            </ul>
            <div id="txtvaleur" onclick="showOffers()" style="font-size:18px;text-align:center;">$txtvaleur</div>
            <div name="offrePackText" id="euro" style="display:none;">
              Valeur : <input type="text" onchange="setmodif();" name="offrePack_euro" onblur="changeRemise('euro',this.value);" value="$euroValue" size="2"/>&euro;
            </div>
            <div name="offrePackText" id="pourcent" style="display:none;">
              Valeur : <input type="text" onchange="setmodif();" name="offrePack_pourcent" onblur="changeRemise('pourcent',this.value);" value="$pourcentValue" size="2"/>%
            </div>
            <div name="offrePackText" id="pdtoffert" style="display:none;">
              <input type="text" style="display:$displayOffrePack;" onchange="setmodif();" name="offrePack_pdtoffert" onkeyup="srcarticle(this.value,'selectkdo')" value="$kdoartcode"/>
            </div>
            <input type="submit" id="saveoffre" value="Enregistrer" style="display:none;">
          </form>
      </td>
    </tr>
  </table>
  <div id="resultlist"></div>
HTML;
}else{
  $contenu_onglet.=<<<HTML
      <td style="width:20px;text-align:center;font-size:20px;">=</td>
      <td style="width:160px;border:solid 1px #CCC;padding:5px;">
        <p><img src="images/fraude.png"> Veuillez d'abord ajouter un article !</p>
      </td>
    </tr>
  </table>
  <div id="resultlist"></div>
HTML;
}

?>