<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");
require_once(BACK_PATH."/adv/envoi_fournisseur.php");

$adv=new adv($DB_site2);
$adv->load_from_efid($_GET['envoi']);
$ef=new envoi_fournisseur($DB_site2,$_GET['envoi']);
$ef->loadlignes();
$listar=$adv->list_ar();

$arcolors=array(0=>"#F99",1=>"#99dd99");

foreach($listar as $ar){
	$return = "</form> <form name=\"arformedit\" method=\"post\">";

	$return .=	"<table width=\"100%\">
			<tr>
				<td id=\"edit_ar\" style=\"background:#aaaaaa;\" colspan=\"6\">
					<b>AR n°</b> <input type=\"text\" name=\"ar_num2\" size=\"10\" value=\"".$ar->numero."\"> &nbsp;&nbsp;&nbsp;
					<b>Du</b> <input name=\"ar_date2\" id=\"ar_date\" value=\"".date("d/m/Y",$ar->date_ar)."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined);\" type=\"text\"> &nbsp;&nbsp;&nbsp;
					<b>Reçu le</b> <input name=\"ar_date_recu2\" id=\"ar_date_recu\" value=\"".date("d/m/Y",$ar->date)."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined);\" type=\"text\">
					<input type=\"hidden\" name=\"ar_valide2\" value=\"1\">
					<input type=\"hidden\" name=\"arlfids2\" value=\"".$_GET['lignes']."\">
					<input type=\"hidden\" name=\"adv_id2\" value=\"".$_GET['adv']."\">
					<input type=\"hidden\" name=\"ar_id2\" value=\"".$_GET['ar']."\">
				</td>
			</tr>
			<tr style=\"background:#CCC;font-weight:bold;\" align=\"center\">
				<td>Quantité</td>
				<td>Référence</td>
				<td>P.U HT</td>
				<td>Enlèvement</td>
				<td>Livraison</td>
				<td>Infos</td>
			</tr>";

	foreach($ef->lignes as $efl){
		$lfid=$efl->lignefactureid;
		$fields=$ar->line_fields($lfid);
		$optvalue=1;
		$options="";
		if(isset($fields["options"])){
			foreach($fields["options"] as $caractvalid=>$value){
				$caract=$DB_site2->query_first("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
				$options.="<div style=\"color:".($value==1?"green":"red").";\">".$caract[0]."</div>";
				if($value==0) $optvalue=0;
			}
		}

		$tdarlivr="<td style=\"background:".$arcolors[$fields["paht"]].";\"></td>";
		if(isset($fields["livr"])){
			$tdarlivr="<td style=\"background:".$arcolors[floor(($fields["livr"]+$fields['trspt'])/2)].";\">
				<input name=\"arlivr2_".$lfid."\" id=\"arlivr_".$lfid."\" value=\"".date("d/m/Y",$efl->outdate)."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined,undefined);\" type=\"text\">
			</td>";
		}

		$return.="<tr align=\"center\">
			<td style=\"background:".$arcolors[$fields["qte"]].";white-space: nowrap;\">".$efl->qte."</td>
			<td style=\"background:".$arcolors[$fields["artcode"] && $optvalue].";color:".($fields["artcode"]==0?"red":"").";\">".$ligne['artcode']." <i>".$adv->fields["ref_".$lfid]."</i>".$options."</td>
			<td style=\"background:".$arcolors[$fields["paht"]].";\"><input name=\"newpriceval2_".$lfid."\" size=\"7\" type=\"text\" value=\"".$efl->paht."\"><br>+".$efl->ecopart."&euro; Ecopart</td>
			<td style=\"background:".$arcolors[$ar->fields["date_".$lfid]].";\"><input name=\"ardate2_".$lfid."\" id=\"ardate_".$lfid."\" value=\"".date("d/m/Y",$efl->indate)."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined,undefined);\" type=\"text\"></td>
			$tdarlivr
			<td style=\"background:".($ar->valide==1?"#0D0":"#F22")."\"><textarea name=\"info2_".$lfid."\" style=\"height:100%;\">".$fields["info"]."</textarea></td>
		</tr>";
	}
	$return.="<tr>
			<td style=\"background:".$arcolors[floor(($fields["livr"]+$fields['trspt'])/2)].";\" colspan=\"6\"><b>Coût Transport :</b> <input type=\"text\" name=\"newtrsptval2_".$_GET['ar']."\" size=\"8\" value=\"".$adv->fields['mazetcout']."\"> HT<input type=\"button\" id=\"valid_ar\" value=\"Modifier\" style=\"float:right;\" onclick=\"modif_ar()\"></td>
		</tr>
	</table>
	</form>";
}

print $return;


?>

