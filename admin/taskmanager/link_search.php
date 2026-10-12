<?php
  require "../admin_global.php";

  $s = mysqli_real_escape_string($DB_site2->link_id,$_GET['term']);

  $q['article'] = $DB_site2->query('
    SELECT `artid` AS `id`, `artcode` AS `label`
    FROM `article`
    WHERE `artcode` LIKE "%'.$s.'%"
    ORDER BY `artcode`
    LIMIT 20
    ');
  $q['facture'] = $DB_site2->query('
    SELECT
      `facturid` AS `id`,
      `facturid` AS `value`,
      CONCAT_WS(" ", `facturid`, "-", `nom`, `prenom`) AS `label`
    FROM `facture`
    WHERE
      UPPER(CONCAT_WS(" ", `facturid`, `nom`, `prenom`))
      LIKE UPPER("%'.$s.'%")
    ORDER BY `facturid`, `nom`, `prenom`
    LIMIT 20
    ');
  $q['fournisseur'] = $DB_site2->query('
    SELECT `societe` AS `id`, `societe` AS `label`
    FROM `fournisseur`
    WHERE `societe` LIKE "%'.$s.'%"
    GROUP BY `societe`
    ORDER BY `societe`
    LIMIT 20
    ');
  $q['utilisateur'] = $DB_site2->query('
    SELECT
      `userid` AS `id`,
      CONCAT_WS(" ", `nom`, `prenom`) AS `label`
    FROM `utilisateur`
    WHERE UPPER(CONCAT_WS(" ", `nom`, `prenom`)) LIKE UPPER("%'.$s.'%")
    ORDER BY `nom`, `prenom`
    LIMIT 20
    ');
  $d = array();
  foreach ($q as $type => $query)
  while ($r=$DB_site2->fetch_assoc($query)) {
    $r['type'] = $type;
    if (is_null($r['value']))
      $r['value'] = $r['label'];
    $r['label']=utf8_encode($r['label']);
    $d[] = $r;
  }
  print json_encode($d);
?>
