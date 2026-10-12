<?php

// MULTISITE

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/garantie.class.php";
include "../class/DAOgarantie.class.php";
include "../class/DAOtype.class.php";

/*$req=$DB_site2->query("SELECT * FROM article_garantie_liste WHERE artid<>1");
while($ag=$DB_site2->fetch_array($req)){
    $DB_site2->query("UPDATE article_garantie_liste SET prix='".serialize(array("base"=>$ag['prix'],"uk"=>$ag['prix']))."', ppi='".serialize(array("base"=>$ag['ppi'],"uk"=>$ag['ppi']))."' WHERE id=".$ag['id']);
}*/


if (isset($_GET["action"])) {
	$action = $_GET["action"];
} else {
	$action='liste';
}

if (isset($_GET["garantieid"]) && $action=="modifier") {
	$garantieid = $_GET["garantieid"];
}

if ($_POST["addBDD"]=="addBDD") {
	$laGarantie = new garantie($DB_site2,'FR','',$_POST["fournisseur"],$_POST["marques"],$_POST["types_produits"],$_POST["utilisation_garantie"],$_POST["pays"],$_POST["duree"],$_POST["type_garantie"],$_POST["paht"],$_POST['prix'],$_POST['ppi'],$_POST["cumul"],$_POST['comm'],$_POST['devise'], $_POST['destinataire_garantie'],$_POST['start']);
	$laGarantie->save();
	$action="liste";
	echo "<span style=\"color:green\">Garantie ajoutée !</span>";
	
	unset($_POST);
}

if ($_POST["modifBDD"]=="modifBDD") {
	$laGarantie = new garantie($DB_site2,'FR',$_POST["garantieid"],$_POST["fournisseur"],$_POST["marques"],$_POST["types_produits"],$_POST["utilisation_garantie"],$_POST["pays"],$_POST["duree"],$_POST["type_garantie"],$_POST["paht"],$_POST["prix"],$_POST["ppi"],$_POST["cumul"],$_POST['comm'],$_POST['devise'], $_POST['destinataire_garantie'],$_POST['start']);
	$laGarantie->update();
	
	echo "<span style=\"color:green\">Garantie modifiée !</span>";
}


if ($_POST["modifCode"]=="modifCode") {
	$DB_site2->query("DELETE FROM fournisseur_code;");
	foreach ($_POST['code'] as $fournisseur => $code) {
		if ($code!="") {
			$DB_site2->query("INSERT INTO fournisseur_code SET fournisseur='".$fournisseur."', code='".$code."';");
		}
	}
}

if($action=="copier"){
	$garantieid = $_GET["garantieid"];

	$base = $DB_site2->query_first("SELECT * FROM garantie WHERE garantieid = '$garantieid'");
	$base_tranche = $DB_site2->query("SELECT * FROM garantie_tranche WHERE garantieid='$garantieid'");

	$DB_site2->query("INSERT INTO garantie SET fournisseur='$base[fournisseur]', marques='$base[marques]', types_produits='$base[types_produits]', utilisation_garantie='$base[utilisation_garantie]', pays='$base[pays]', duree='$base[duree]', type_garantie='$base[type_garantie]', paht='$base[paht]', devise='$base[devise]', prix='$base[prix]', ppi='$base[ppi]', cumul='$base[cumul]', start='$base[start]', comm='$base[comm]', garantie_client_id='$base[garantie_client_id]'");
	$new_id = $DB_site2->insert_id();

	while($tranche = $DB_site2->fetch_array($base_tranche)){

		$DB_site2->query("INSERT INTO garantie_tranche SET garantie_tranche_prix_mini='$tranche[garantie_tranche_prix_mini]', garantie_tranche_prix_maxi='$tranche[garantie_tranche_prix_maxi]', garantie_tranche_paht='$tranche[garantie_tranche_paht]', garantie_tranche_prix='$tranche[garantie_tranche_prix]', garantie_tranche_ppi='$tranche[garantie_tranche_ppi]', garantieid='$new_id'");
	}
	$action = "liste";
}

?>
<script type="text/javascript">
     function affiche_div(obj, id){
          
          document.getElementById('garantie').style.display       = 'none';
          document.getElementById('tarifs').style.display         = 'none';
          document.getElementById('marques').style.display        = 'none';
          document.getElementById('types_articles').style.display = 'none';
          document.getElementById('pays').style.display           = 'none';
          
          document.getElementById('onglet_garantie').className      = 'admin_onglet';
          document.getElementById('onglet_tarifs').className        = 'admin_onglet';
          document.getElementById('onglet_marques').className       = 'admin_onglet';
          document.getElementById('onglet_typesarticles').className = 'admin_onglet';
          document.getElementById('onglet_pays').className          = 'admin_onglet';
          
          if(document.getElementById(id)){
               document.getElementById(id).style.display = 'block';
               obj.className = 'admin_onglet onglet_actif';
          }
     }
     
     function addTranche(){
          
          var tranche_mini = document.getElementById('prix_mini_tranche').value;
          var tranche_maxi = document.getElementById('prix_maxi_tranche').value;
          var paht = document.getElementById('paht_tranche[0]').value;
          var prix_base = document.getElementsByName('prix_tranche[0][base]')[0].value;
          var prix_uk = document.getElementsByName('prix_tranche[0][uk]')[0].value;
          var ppi_base = document.getElementsByName('ppi_tranche[0][base]')[0].value;
          var ppi_uk = document.getElementsByName('ppi_tranche[0][uk]')[0].value;
          var garantie_id = document.getElementById('garantieid').value;
          
          ajaxrequest('ajax/tarifs_tranche.php?action=add&garantie_id='+garantie_id+'&mini='+tranche_mini+'&maxi='+tranche_maxi+'&paht='+paht+'&prix[base]='+prix_base+'&prix[uk]='+prix_uk+'&ppi[base]='+ppi_base+'&ppi[uk]='+ppi_uk, update_tarifs);  
     }
     
     function deleteTranche(tranche_id){
          
          if(confirm('Supprimer cette tranche de prix ?')){
               
               var garantie_id = document.getElementById('garantieid').value;
               
               ajaxrequest('ajax/tarifs_tranche.php?action=delete&tranche_id='+tranche_id+'&garantie_id='+garantie_id, update_tarifs);  
          }
          
          return false;
     }
     
     function update_tarifs(httprequest){
          
          document.getElementById('tarifs').innerHTML = httprequest.responseText;
     }
     
     function afficheTranche(id){
          
          if(document.getElementById('tr_tranche').style.display === 'none'){
               document.getElementById('tr_tranche').style.display = 'block';
          } else {
               document.getElementById('tr_tranche').style.display = 'none';
          }
     }
	 
	function valideFrmGarantie(){
		var valid=true;
		var txtError="";
		if(document.frmGarantie.fournisseur.value == "") {
			valid=false;
			txtError+="Veuillez sélectionner un fournisseur.\n";
		};
		
		/*if(document.frmGarantie.marques.value == "") {
			valid=false;
			txtError+="Veuillez sélectionner une marque.\n";
		};*/
		
		var i = 1;
		check=false;
		while(document.getElementById('types_produits_' + i) && !check)
		{
			if(document.getElementById('types_produits_' + i).checked) {
				check=true;
			};
			i++;
		};
		if (!check) {
			valid=false;
			txtError+="Veuillez sélectionner au moins un type de produit.\n";
		};
		
		i = 1;
		check=false;
		while(document.getElementById('pays_' + i) && !check)
		{
			if(document.getElementById('pays_' + i).checked) {
				check=true;
			};
			i++;
		};
		if (!check) {
			valid=false;
			txtError+="Veuillez sélectionner au moins un pays.\n";
		};
		if (!document.frmGarantie.utilisation_garantie[0].checked && !document.frmGarantie.utilisation_garantie[1].checked && !document.frmGarantie.utilisation_garantie[2].checked) {
			valid=false;
			txtError+="Veuillez sélectionner le type de garantie.\n";
		};
		if(document.frmGarantie.duree.value == "") {
			valid=false;
			txtError+="Veuillez saisir la durée de la garantie.\n";
		};
		if(document.frmGarantie.type_garantie.value == "") {
			valid=false;
			txtError+="Veuillez sélectionner les services couverts par cette garantie.\n";
		};
		if (!document.frmGarantie.cumul[0].checked && !document.frmGarantie.cumul[1].checked) {
			valid=false;
			txtError+="Veuillez choisir si c'est une extension.\n";
		};
		if(document.frmGarantie.paht && document.frmGarantie.paht.value == "") {
			valid=false;
			txtError+="Veuillez saisir le prix HT.\n";
		};
		if(document.frmGarantie.prix && document.frmGarantie.prix.value == "") {
			valid=false;
			txtError+="Veuillez saisir le prix TTC.\n";
		};
		if(document.frmGarantie.ppi && document.frmGarantie.ppi.value == "") {
			valid=false;
			txtError+="Veuillez saisir le PPI.\n";
		};
		if (!valid) {
			alert(txtError);
		};

		return valid;
	}
	
	function selectfournisseur(data){
		var spl=data.split("|");
		document.getElementsByName('fournisseur')[0].value=spl[0];
		document.getElementsByName('devise')[0].value=spl[1];
		//document.getElementById('paht_devise').innerHTML=spl[2];
	}
</script>

<h1>Garanties</h1>

<?php
include "garanties_$action.php";

include "./footer.php";
?>
