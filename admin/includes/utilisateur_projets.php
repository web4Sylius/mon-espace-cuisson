<?php

$now=new datetime();

$projets=new projets();

$encour=$clo=array();

$etatstat=array(0=>0,1=>0);
$utilisateur['userid'] = $_GET['userid'];
$user=new utilisateur($DB_site2,$utilisateur['userid']);
foreach($user->projets() as $projet){
    $date=$projet->date->format("d/m/Y");
    $rappel="Aucun rappel";
    $rappelcolor="red";
    if(!is_null($projet->rappel)){
        $format="d/m/Y";
        if($projet->rappel->format("G")!="0") $format.=" H:i";
        $rappel="Rappel : ".$projet->rappel->format($format);
        $rappelcolor=$projet->rappel<$now?"red":"";
    }
    
    $besoin="";
    if($projet->besoin!=""){
        $besoin="<div class=\"besoin\" onclick=\"this.classList.toggle('open');\">
        <div>".nl2br($projet->besoin)."</div>
        </div>";
    }
    
    $budget=number_format($projet->budget,2,',','&nbsp;')." &euro;";
    $etatcolor=$projet->etatcolor[$projet->etatid];
    $typebudget=$projet->budget_type_lib();
    
    $echeancecolor="";
    $dateecheance="Inconnue";
    if(!is_null($projet->dateecheance)){
    $dateecheance=$projet->dateecheance->format("d/m/Y");
        $chkecheance="";
        $styleecheance="";
        if(!in_array($projet->etatid,$projets->clos))
            $echeancecolor=$projet->dateecheance<new datetime('today 0:0:0')?"red":"green";
    }
    
    $trrelations="";
    $users=$projet->users();
    if(count($users)>1){
        $titre="<b>Relations :</b>";
        foreach($users as $relation){
            if($user->userid!=$relation->userid){
                $trrelations.="<tr>
                    <td>".$titre."</td>
                    <td colspan=\"2\"><a href=\"utilisateur.php?action=editer&amp;userid=".$relation->userid."\" target=\"_blank\">".$relation->prenom." ".$relation->nom." ".$relation->raisonsociale."</a> (".$relation->userid.")</td>
                </tr>";
                $titre="";
            }
        }
    }
    
    $encours=intval(in_array($projet->etatid,$projets->encours));
    //$display=$encours==1?"":"none";
    $etatstat[$encours]++;
    if($encours==1){
        $encour[]=<<<HTML
    <div data-encours="$encours" style="border: 1px solid #000; margin:5px; padding:5px;">
        <table width="100%">
            <tr>
                <td>N° <b>$projet->pjt</b> du <b>$date</b></td>
                <td><b>Commercial :</b> $projet->commercial</td>
            </tr>
            <tr>
                <td colspan="2"><b>Libellé :</b> <a href="projet.php?projetid=$projet->projetid">$projet->libelle</a></td>
                <td style="color:$rappelcolor">$rappel</td>
            </tr>
            <tr>
                <td><b>Budget :</b> $budget</td>
                <td><b>Etat :</b> <span style="color:$etatcolor;">$projet->etat</span></td>
            </tr>
            <tr>
                <td><b>Type projet :</b> $typebudget</td>
                <td><b>Échéance livr. :</b> <span style="color:$echeancecolor">$dateecheance</span></td>
                <td><b>Délai pour cmd :</b> $projet->delai_cmd jours</td>
            </tr>
            $trrelations
        </table>
    </div>
HTML;
    }else{
        $clo[] = <<<HTML
    <div data-encours="$encours" style="border: 1px solid #000; margin:5px; padding:5px;">
        <table width="100%">
            <tr>
                <td>N° <b>$projet->pjt</b> du <b>$date</b></td>
                <td><b>Commercial :</b> $projet->commercial</td>
            </tr>
            <tr>
                <td colspan="2"><b>Libellé :</b> <a href="projet.php?projetid=$projet->projetid">$projet->libelle</a></td>
                <td style="color:$rappelcolor">$rappel</td>
            </tr>
            <tr>
                <td><b>Budget :</b> $budget</td>
                <td><b>Etat :</b> <span style="color:$etatcolor;">$projet->etat</span></td>
            </tr>
        </table>
    </div>
HTML;
    }
    
}

?>
<a name="propositions_articles"></a>
<div class="commande_detail" id="utilisateur_projets">
    <h2>PROJETS</h2>
    <?php if($etatstat[1]>0): ?>
        <div class="encoursprojet_titre">En cours (<?=$etatstat[1]; ?>)</div>
        <div id="projet_infos" class="table_modules_boutiques no_border">
    		<?=implode("",$encour); ?>
        </div>
    <?php endif; ?>
    <?php if($etatstat[0]>0) :?>
        <div class="encoursprojet_titre">Clos (<?=$etatstat[0]; ?>)</div>
        <div id="projet_infos" class="table_modules_boutiques no_border">
            <?=implode("",$clo); ?>
        </div>
    <?php endif; ?>
    <h3><a href="projet.php?foruserid=<?=$utilisateur['userid']; ?>">Ajouter un projet</a></h3>
</div>
<div class="clear"></div>
