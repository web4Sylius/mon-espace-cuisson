<?php
include_once($_SERVER['DOCUMENT_ROOT']."/global.php");
header('Content-Type: text/html; charset=utf-8');

$ctx = stream_context_create(array('http'=>	array(	'timeout' => 10  /* 10 secondes */ 	)	));
$sofincostr = file_get_contents($sofinco_url_pt.'/sofgate.asp?p0=WSACCROCHE&q6='.$sofinco_site_id.'&p4='.$_GET['montant'].'&d1='.$_GET['mensualites'], false, $ctx);
$objXmlCredit = @simplexml_load_string($sofincostr);

?>
<div id="sofinco_sans_frais">
     <p class="sofinco_desc gras"><?php echo $objXmlCredit->ML->LIB_MENTION_SANITAIRE; ?></p>
     <p class="sofinco"><?php echo $objXmlCredit->OFFRE->LIB_LL_STITRE_OFFRE; ?></p>
     <p class="sofinco">
          <?php echo $objXmlCredit->SIMU->LIB_MT_A_FINANCER; ?>

          <?php echo $objXmlCredit->SIMU->LIB_MT_APPORT; ?>

          <?php echo $objXmlCredit->SIMU->LIB_MT_CREDIT_RECALCULE; ?>

          <?php echo $objXmlCredit->SIMU->LIB_NB_ECHEANCES; ?>

          <?php echo $objXmlCredit->SIMU->LIB_MT_ECHEANCE_AVEC_ASSURANCE; ?>
     </p>
     <p class="sofinco"><?php echo $objXmlCredit->SIMU->LIB_TX_TEG_AN; ?></p>
     <p class="sofinco"><?php echo $objXmlCredit->SIMU->LIB_MONTANT_TOTAL_AVEC_ASSURANCE; ?></p>
     <p class="sofinco"><?php echo $objXmlCredit->SIMU->LIB_COUT_TOT_ACHAT; ?></p>
     <p class="sofinco"><?php echo $objXmlCredit->SIMU->LIB_DESCRIPTIF; ?></p>
     <p class="sofinco">
          <?php echo $objXmlCredit->SIMU->LIB_MT_ECHEANCE_SANS_ASSURANCE; ?>

          <?php echo $objXmlCredit->SIMU->LIB_MONTANT_TOTAL_SANS_ASSURANCE; ?>

          <?php echo $objXmlCredit->SIMU->LIB_COUT_TOTAL_SANS_ASSURANCE; ?>
     </p>
     <p class="sofinco"><?php echo 'Mentions légales : '.$objXmlCredit->ML->LIB_MENTIONS_LEGALES; ?></p>
</div>
