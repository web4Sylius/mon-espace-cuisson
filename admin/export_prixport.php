<?php

require "admin_global.php";

header('Content-type: text/csv');

$datedebut=1356994800;

$pays=array();
$req=$DB_site2->query("SELECT paysid,libelle FROM pays WHERE 1");
while($p=$DB_site2->fetch_array($req)) $pays[$p['paysid']]=$p['libelle'];

$sql="SELECT ef.id,efl.date,ef.destinataire,efl.paht,efl.qte AS qteefl,lf.qte,lf.factureid,a.emballage_poids,f.ville,f.paysid,f.codepostal FROM envoi_fournisseur_ligne efl inner join envoi_fournisseur ef on(ef.id=efl.efid) inner join lignefacture lf on (lf.lignefactureid=efl.ligne_facture_id) inner join article a using(artid) inner join facture f on(f.facturid=lf.factureid) WHERE efl.date BETWEEN $datedebut AND ".(time())." AND ef.type LIKE 'trspt' and destinataire<>'0'";

$envoi=array();
$req=$DB_site2->query($sql);
while($stat=$DB_site2->fetch_array($req)){
    if(!isset($envoi[$stat['id']])) $envoi[$stat['id']]=$stat;
    $envoi[$stat['id']]['total']+=$stat['paht']*$stat['qteefl'];
    $envoi[$stat['id']]['poids']+=$stat['emballage_poids']*$stat['qte'];
}
    
foreach($envoi as $efid=>$data)
    print sprintf("\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",date("Y-m-d",$data['date']),$data['factureid'],$data['destinataire'],$data['poids'],$data['total'],$pays[$data['paysid']],$data['ville'],$data['codepostal']);
    
?>