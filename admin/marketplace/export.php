<?php

include("../../config.php");

header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: text/plain\n"); // Surtout ne pas enlever le \n
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
header("Expires: 0"); 

// price miniter

// EAN (code barre)
// référence fabricant (ACI-XXXX-1)
// fabricant
// votre référence (ACI-XXXX-1)
// titre => détail + type + fabricant + ref. fabricant originale + (ref. commerciale originale)
// type de produit inkjet => cartouche d'encre, laser => cartouche de toner, ruban => cartouche d'encre, papier => Etiquettes
// fiche technique Détail + ESPACE + Type + ESPACE + Fabricant + ESPACE + Ref. Fabriquant + ESPACE + (+Ref.Commerciale) + ) + ESPACE-ESPACE + "Capacité" + Capacité (nom colonne) + infos addtionnelles  ex: 1 Toner Laser Monobloc Noir Remanufacturé Panasonic UG-3350 - Capacité 7500 pages
// URL image Principale
// URL image Secondaire
// Prix de vente
// Quantité (* pour tous)
// Qualité (N pour tous)
// commentaire annonce => fiche technique + Achetez chez 1 Specialiste en cartouches encre et toners laser compatibles. Livraison rapide par TNT demain avant 13H avec facture TVA + ref. + ref_comp
// Commentaire privé => vide

// amazon

// sku => ref compatible (aci-xxx-x)
// standard-product-id => Code barre
// product-id-type => EAN
// title =>  Détails + ESPACE + Type + ESPACE + Marque + ESPACE + Ref Commerciale + ESPACE + ( + Ref constructeur + ) + - + Capacité + Infos additionnelles
// manufacturer => GENERIQUE
// brand => GENERIQUE
// product_type => ConsumerElectrics
// item-price => Prix de vente HT
// currency => EUR
// quantity => 1000
// main-image-url => URL image
// recommended-browse-node1
// description => Détails + ESPACE + Type + ESPACE + Marque + ESPACE + Ref Commerciale + ESPACE + ( + Ref constructeur + ) + - + Capacité + Infos additionnelles
// controle longueur description (<2000)
// bullet-point1
// bullet-point2 => Technologie d'impression : jet d'encre, laser, ruban transfert thermique
// bullet-point3 => Couleur d'impression : couleur
// bullet-point4 => Contenance : capacité
// bullet-point5 => Quantité : 1
// leadtime-to-ship
// item-condition => New ou Refurbished
// condition-note => Achetez chez un specialiste en cartouches encre et toners laser 100% compatibles. Normes ISO 14001.  Livraison rapide en 24h par TNT Express avec facture TVA.
// search-terms1
// search-terms2 => référence originales
// search-terms3 => références originales sans espace
// search-terms4 =>
// search-terms5 =>
// shipping-weight-unit-of-measure
// shipping-weight
// merchant-catalog-number
// recommended-browse-node2
// legal-disclaimer
// other-image-url1
// other-image-url2
// other-image-url3
// other-image-url4
// other-image-url5
// other-image-url6
// other-image-url7
// other-image-url8
// item-weight-unit-of-measure
// item-weight
// item-length-unit-of-measure
// item-length
// item-height
// item-width
// release-date
// sale-price
// fulfillment-center-id
// sale-from-date
// sale-through-date
// restock-date
// max-aggregate-ship-quantity
// is-gift-message-available
// is-giftwrap-available
// is-discontinued-by-manufacturer
// registered-parameter
// update-delete




$tech=array("Inkjet"=>"Cartouche d'encre","Laser"=>"Cartouche de toner","Ruban"=>"Cartouche d'encre","Papier"=>"Etiquettes");

$cart=new cartridge();
$list=$cart->listcompatibility();

switch($_GET['marketplace']){
case "priceminister":
header("Content-disposition: attachment; filename=BDD_priceminister_".date("Y-m-d").".csv");
print "\"EAN\",\"Référence fabricant\",\"Fabricant\",\"Votre référence\",\"Titre\",\"Type de produit\",\"Fiche technique\",\"URL image principale\",\"URL image principale bis\",\"URL image secondaire\",\"Prix de vente\",\"Quantité\",\"Qualité\",\"Commentaire annonce\",\"Commentaire privé\"\n";
foreach($list as $comp){
              $fiche=$comp['details']." ".$comp['type']." ".$comp['manufacturer']." ".$comp['ref_com'].($comp['ref_com']!=$comp['ref_man']?" (".$comp['ref_man'].")":"")." - Capacité ".$comp['capacity']." ".$comp['additional_info'];
              $line= "\"".$comp['barcode']."\",
\"".$comp['ref_comp']."\",
\"".$comp['manufacturer']."\",
\"".$comp['ref_comp']."\",
\"".trim($comp['details']." ".$comp['type']." ".$comp['manufacturer']." ".$comp['ref_com'].($comp['ref_com']!=$comp['ref_man']?" (".$comp['ref_man'].")":""))."\",
\"".$tech[$comp['technology']]."\",
\"".$fiche."\",
\"".$comp['image']."\",
\"http://".$_SERVER['HTTP_HOST']."/image_cartouche_".$comp['id'].".jpg\",
\"\",
\"".number_format(ceil(($comp['price']*TVA_COEFF)*10)/10,2,",","")."\",
\"*\",
\"N\",
\"".$fiche." - Achetez chez 1 Specialiste en cartouches encre et toners laser 100% compatibles. Qualité ISO 14001. Livraison rapide par FEDEX Express demain avant 13H avec facture TVA - Ref. ".$comp['ref_comp']."\",
\"\"";

              $line=str_replace("\n","",$line);
              if($comp['show']==true) print $line."\n";

}
break;

case "amazon":
     $tech=array("inkjet"=>"jet d'encre","laser"=>"laser","ruban"=>"ruban transfert thermique","papier"=>"papier");
     $browsecode=array("inkjet"=>323684011,"laser"=>323684011,"ruban"=>323684011,"papier"=>205438031);
     $pub="Achetez chez un specialiste en cartouches encre et toners laser 100% compatibles. Normes ISO 14001.  Livraison rapide en 24h par FEDEX Express avec facture TVA.";

     header("Content-disposition: attachment; filename=BDD_amazon_".date("Y-m-d").".csv");
     print "\"TemplateType=ConsumerElectronics\",\"Version=1.4\",\"This row for Amazon.com use only.  Do not modify or delete.\",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,\"Rebates\",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,\"PC\",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,\"PDA\",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,\"Misc Consumer Electronics\"\n";
     print "\"sku\",\"standard-product-id\",\"product-id-type\",\"title\",\"manufacturer\",\"brand\",\"product_type\",\"item-price\",\"currency\",\"quantity\",\"main-image-url\",\"main-image-url bis\",\"recommended-browse-node1\",\"description\",\"controle longueur description (<2000)\",\"bullet-point1\",\"bullet-point2\",\"bullet-point3\",\"bullet-point4\",\"bullet-point5\",\"leadtime-to-ship\",\"item-condition\",\"condition-note\",\"search-terms1\",\"search-terms2\",\"search-terms3\",\"search-terms4\",\"search-terms5\",\"shipping-weight-unit-of-measure\",\"shipping-weight\",\"merchant-catalog-number\",\"recommended-browse-node2\",\"legal-disclaimer\",\"other-image-url1\",\"other-image-url2\",\"other-image-url3\",\"other-image-url4\",\"other-image-url5\",\"other-image-url6\",\"other-image-url7\",\"other-image-url8\",\"item-weight-unit-of-measure\",\"item-weight\",\"item-length-unit-of-measure\",\"item-length\",\"item-height\",\"item-width\",\"release-date\",\"sale-price\",\"fulfillment-center-id\",\"sale-from-date\",\"sale-through-date\",\"restock-date\",\"max-aggregate-ship-quantity\",\"is-gift-message-available\",\"is-giftwrap-available\",\"is-discontinued-by-manufacturer\",\"registered-parameter\",\"update-delete\"";
     print ",\"rebate-start-date1\",\"rebate-end-date1\",\"rebate-message1\",\"rebate-name1\",\"rebate-start-date2\",\"rebate-end-date2\",\"rebate-message2\",\"rebate-name2\"";
     print ",\"\",\"PC_AdditionalDrives1\",\"PC_AdditionalDrives2\",\"PC_AdditionalDrives3\",\"PC_AdditionalDrives4\",\"PC_AdditionalDrives5\",\"PC_AdditionalDrives6\",\"PC_AdditionalDrives7\",\"PC_AdditionalDrives8\",\"PC_AdditionalDrives9\",\"PC_AdditionalDrives10\",\"PC_HardDriveSize1\",\"PC_HardDriveSize2\",\"PC_HardDriveSize3\",\"PC_HardDriveSize4\",\"PC_HardDriveSize5\",\"PC_HardDriveSize6\",\"PC_HardDriveSize7\",\"PC_HardDriveSize8\",\"PC_HardDriveSizeUnitOfMeasure\",\"PC_HardDriveInterface1\",\"PC_HardDriveInterface2\",\"PC_HardDriveInterface3\",\"PC_HardDriveInterface4\",\"PC_SoftwareIncluded\",\"PC_ComputerMemoryType1\",\"PC_ComputerMemoryType2\",\"PC_ComputerMemoryType3\",\"PC_ComputerMemoryType4\",\"PC_ComputerMemoryType5\",\"PC_ComputerMemoryType6\",\"PC_ComputerMemoryType7\",\"PC_ComputerMemoryType8\",\"PC_ComputerMemoryType9\",\"PC_ComputerMemoryType10\",\"PC_RAMSize\",\"PC_RAMSizeUnitOfMeasure\",\"PC_MemorySlotsAvailable\",\"PC_ProcessorBrand\",\"PC_ProcessorSpeed\",\"PC_ProcessorSpeedUnitOfMeasure\",\"PC_ProcessorType\",\"PC_ProcessorCount\",\"PC_ScreenResolution\",\"PC_ScreenSize\",\"PC_ScreenSizeUnitOfMeasure\",\"PC_OperatingSystem1\",\"PC_OperatingSystem2\",\"PC_HardwarePlatform\",\"PC_U-RackSize\",\"PC_WirelessType1\",\"PC_WirelessType2\",\"PC_WirelessType3\",\"PC_GraphicsDescription1\",\"PC_GraphicsRAMSize1\",\"PC_GraphicsCardInterface1\",\"PC_GraphicsDescription2\",\"PC_GraphicsRAMSize2\",\"PC_GraphicsCardInterface2\",\"PC_GraphicsRAMSizeUnitOfMeasure\",\"PC_AdditionalFeatures\"";
     print ",\"\",\"PDA_HardDriveSize\",\"PDA_HardDriveSizeUnitOfMeasure\",\"PDA_SoftwareIncluded\",\"PDA_ComputerMemoryType1\",\"PDA_ComputerMemoryType2\",\"PDA_ComputerMemoryType3\",\"PDA_ComputerMemoryType4\",\"PDA_ComputerMemoryType5\",\"PDA_ComputerMemoryType6\",\"PDA_ComputerMemoryType7\",\"PDA_ComputerMemoryType8\",\"PDA_ComputerMemoryType9\",\"PDA_ComputerMemoryType10\",\"PDA_MemorySlotsAvailable\",\"PDA_RAMSize\",\"PDA_RAMSizeUnitOfMeasure\",\"PDA_ProcessorSpeed\",\"PDA_ProcessorSpeedUnitOfMeasure\",\"PDA_ProcessorType\",\"PDA_ROMSize\",\"PDA_ROMSizeUnitOfMeasure\",\"PDA_ScreenResolution\",\"PDA_ColorScreen\",\"PDA_ScreenSize\",\"PDA_ScreenSizeUnitOfMeasure\",\"PDA_OperatingSystem1\",\"PDA_OperatingSystem2\",\"PDA_OperatingSystem3\",\"PDA_OperatingSystem4\",\"PDA_OperatingSystem5\",\"PDA_WirelessType1\",\"PDA_WirelessType2\",\"PDA_WirelessType3\",\"PDA_AdditionalFeatures\",\"PDA_PDABaseModel1\",\"PDA_PDABaseModel2\",\"PDA_PDABaseModel3\",\"PDA_PDABaseModel4\",\"PDA_PDABaseModel5\"";
     print ",\"\",\"CE_Color\",\"CE_SpeakerDiameter\",\"CE_SpeakerDiameterUnitOfMeasure\",\"CE_ColorMap\",\"CE_Voltage\",\"CE_Wattage\",\"CE_PowerSource\",\"CE_AdditionalFeatures\",\"CE_VehicleSpeakerSize\",\"CE_TelephoneType1\",\"CE_TelephoneType2\",\"CE_TelephoneType3\",\"CE_TelephoneType4\",\"CE_PDABaseModel1\",\"CE_PDABaseModel2\",\"CE_PDABaseModel3\",\"CE_PDABaseModel4\",\"CE_PDABaseModel5\",\"CE_DigitalMediaFormat\",\"CE_HomeAutomationCommunicationDevice\",\"CE_DigitalAudioCapacity\",\"CE_HolderCapacity\",\"CE_MemorySlotsAvailable\",\"CE_RAMSize\",\"CE_RAMSizeUnitOfMeasure\",\"CE_ScreenResolution\",\"CE_ColorScreen\",\"CE_ScreenSize\",\"CE_ScreenSizeUnitOfMeasure\",\"CE_WirelessType1\",\"CE_WirelessType2\",\"CE_WirelessType3\",\"CE_HardDriveSize1\",\"CE_HardDriveSize2\",\"CE_HardDriveSize3\",\"CE_HardDriveSize4\",\"CE_HardDriveSize5\",\"CE_HardDriveSize6\",\"CE_HardDriveSize7\",\"CE_HardDriveSize8\",\"CE_HardDriveSizeUnitOfMeasure\",\"CE_HardDriveInterface1\",\"CE_HardDriveInterface2\",\"CE_HardDriveInterface3\",\"CE_HardDriveInterface4\",\"CE_OperatingSystem1\",\"CE_OperatingSystem2\",\"CE_OperatingSystem3\",\"CE_OperatingSystem4\",\"CE_OperatingSystem5\",\"CE_HardwarePlatform\",\"CE_ComputerMemoryType1\",\"CE_ComputerMemoryType2\",\"CE_ComputerMemoryType3\",\"CE_ComputerMemoryType4\",\"CE_ComputerMemoryType5\",\"CE_ComputerMemoryType6\",\"CE_ComputerMemoryType7\",\"CE_ComputerMemoryType8\",\"CE_ComputerMemoryType9\",\"CE_ComputerMemoryType10\",\"CE_ItemPackageQuantity\"\n";

     foreach($list as $comp){
           $new="New";
           if($comp['type']=="Remanufacturé") $new="Refurbished";
           $title=$comp['details']." ".$comp['type']." ".$comp['manufacturer']." ".$comp['ref_com'].($comp['ref_com']!=$comp['ref_man']?" (".$comp['ref_man'].")":"")." - ".$comp['capacity']." ".$comp['additional_info'];
           $line="\"".$comp['ref_comp']."\",\"".$comp['barcode']."\",\"EAN\",\"".$title."\",\"GENERIQUE\",\"GENERIQUE\",\"ConsumerElectrics\",\"".number_format(ceil(($comp['price']*TVA_COEFF)*10)/10,2,",","")."\",\"EUR\",\"1000\",\"".$comp['image']."\",\"http://".$_SERVER['HTTP_HOST']."/image_cartouche_".$comp['id'].".jpg\",\"".$browsecode[strtolower($comp['technology'])]."\",\"".$title."\",\"\",\"Descriptif : ".$comp['details']." ".$comp['type']." ".$comp['manufacturer']." ".$comp['ref_com']." ".$comp['ref_man']."\",\"Technologie d'impression : ".$tech[strtolower($comp['technology'])]."\",\"Couleur d'impression : ".$comp['color']."\",\"Contenance : ".$comp['capacity']."\",\"Quantité : ".$comp['details']."\",\"\",\"".$new."\",\"".$pub."\",\"".$comp['ref_com']." ".$comp['ref_man']."\",\"".trim($comp['ref_com']." ".$comp['ref_man'])."\",\"".trim(str_replace(" ","",$comp['ref_com'])." ".str_replace(" ","",$comp['ref_man']))."\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"\n";

           if($comp['show']==true) print $line;
      }
break;
}

