<?php

require "admin_global.php";

$i=0;
$r=$DB_site2->query("select efl.id AS eflid,ef.id,ef.facturid,ef.type,ef.destinataire from envoi_fournisseur_ligne efl inner join envoi_fournisseur ef on(ef.id=efl.efid) inner join facture f where (efl.date between 1293899468 and 1314895150) AND efl.facture='' and efl.efid>0 and f.valide=7 and ef.destinataire<>'0' and ef.type in ('trspt','sls') group by ef.id");
while($efl=$DB_site2->fetch_array($r)){
    $r2=$DB_site2->query_first("SELECT COUNT(id),efl.facture FROM envoi_fournisseur_ligne efl WHERE efl.facture<>'' and efl.efid=".$efl['id']);
    if($r2[0]>0){
//       $DB_site2->query("UPDATE envoi_fournisseur_ligne SET facture='".$r2['facture']."' WHERE id=".$efl['eflid']);
      print "<br>".$efl['type']." ".$efl['destinataire']." ".$efl['facturid']." ".$r2['facture'];
      $i++;
    }
}
print "<br>".$i;

?>