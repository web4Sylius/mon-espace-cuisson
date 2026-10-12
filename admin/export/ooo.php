<?php

include_once("zip.lib.php");

class ooo{

  var $name;

  var $styles=array();
  var $tables=array();
  var $images=array();

  var $cursorpos_x=1;
  var $cursorpos_y=1;
  var $type="application/vnd.oasis.opendocument.spreadsheet";

  var $tmp_content;

  function addtable($table){
      $this->tables[]=$table;
  }

  function addstyle($style){
      $this->styles[]=$style;
  }

  function addimage($image){
      $imcount=count($this->images)+1;
      $image->name="graphic".$imcount;
      $image->style="grstyle".$imcount;
      $this->images[]=$image;
      
  }

  function link($text,$url,$style=""){
	$ret="<text:a xlink:href=\"".$url."\"";
	if($style=="") $ret.=" text:style-name=\"none\"";
	$ret.=">".$text."</text:a>";

	return $ret;
    
  }

  function generate(){
      $zip=new zipfile();

      $this->tmp_content="/tmp/".uniqid();
      mkdir($this->tmp_content);
      //$zip->addfile($this->generate_style(),"style.xml");
//       $zip->addfile($this->generate_settings(),"settings.xml");
      file_put_contents($this->tmp_content."/settings.xml",$this->generate_settings());
//       $zip->addfile($this->generate_meta(),"meta.xml");
      file_put_contents($this->tmp_content."/meta.xml",$this->generate_meta());
      $this->generate_content();
//       $zip->addfile($this->type,"mimetype");
      file_put_contents($this->tmp_content."/mimetype",$this->type);
//       $zip->addfile($this->manifest(),"META-INF/manifest.xml");
      mkdir($this->tmp_content."/META-INF");
      file_put_contents($this->tmp_content."/META-INF/manifest.xml",$this->manifest());
//       $zip->addfile("","Thumbnails/");
      mkdir($this->tmp_content."/Thumbnails");
//       $zip->addfile("","Configurations2/statusbar/");
      mkdir($this->tmp_content."/Configurations2");
      mkdir($this->tmp_content."/Configurations2/statusbar");
//       $zip->addfile("","Configurations2/accelerator/current.xml");
      mkdir($this->tmp_content."/Configurations2/accelerator");
      file_put_contents($this->tmp_content."/Configurations2/accelerator/current.xml","");
//       $zip->addfile("","Configurations2/floater/");
      mkdir($this->tmp_content."/Configurations2/floater");
//       $zip->addfile("","Configurations2/popupmenu/");
      mkdir($this->tmp_content."/Configurations2/popupmenu/");
//       $zip->addfile("","Configurations2/progressbar/");
      mkdir($this->tmp_content."/Configurations2/progressbar");
//       $zip->addfile("","Configurations2/menubar/");
      mkdir($this->tmp_content."/Configurations2/menubar");
//       $zip->addfile("","Configurations2/toolbar/");
      mkdir($this->tmp_content."/Configurations2/toolbar");
//       $zip->addfile("","Configurations2/images/Bitmaps/");
      mkdir($this->tmp_content."/Configurations2/images");
      mkdir($this->tmp_content."/Configurations2/images/Bitmaps");

      if(count($this->images)>0){
	  mkdir($this->tmp_content."/Pictures");
	  foreach($this->images as $image){
		  $data="";
		  copy($image->url,"Pictures/".$image->filename);
// 		  $f=fopen($image->url,"r");
// 		  while(!feof($f)) $data.=fgets($f,1024);
// 		  fclose($f);
// 		  $zip->addfile($data,"Pictures/".$image->filename);
		  
	  }
      }


//       $archive=$zip->file();

      header("Content-Type: ".$this->type);
   
      header("Content-Disposition: inline; filename=\"".$this->name.".ods\"");

      exec("cd ".$this->tmp_content." && zip -r tmp.ods .");
     $fp=fopen($this->tmp_content."/tmp.ods","r");
      $buff="";
      while(!feof($fp)){
	   $buff=fread($fp,8192);
	   echo $buff;
	   flush();
      }
      fclose($fp);
      $this->clean($this->tmp_content);
    
//       echo $archive;//print $this->generate_content();
   }

   function clean($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
	if ($object != "." && $object != "..") {
	  if (filetype($dir."/".$object) == "dir") $this->clean($dir."/".$object); else unlink($dir."/".$object);
	}
      }
      reset($objects);
      rmdir($dir);
    }
  }

  function manifest(){
      return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
      <manifest:manifest xmlns:manifest=\"urn:oasis:names:tc:opendocument:xmlns:manifest:1.0\">
      <manifest:file-entry manifest:media-type=\"application/vnd.oasis.opendocument.spreadsheet\" manifest:version=\"1.2\" manifest:full-path=\"/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/statusbar/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/accelerator/current.xml\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/accelerator/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/floater/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/popupmenu/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/progressbar/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/menubar/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/toolbar/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/images/Bitmaps/\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Configurations2/images/\"/>
      <manifest:file-entry manifest:media-type=\"application/vnd.sun.xml.ui.configuration\" manifest:full-path=\"Configurations2/\"/>
      <manifest:file-entry manifest:media-type=\"text/xml\" manifest:full-path=\"content.xml\"/>
      <manifest:file-entry manifest:media-type=\"text/xml\" manifest:full-path=\"styles.xml\"/>
      <manifest:file-entry manifest:media-type=\"text/xml\" manifest:full-path=\"meta.xml\"/>
      <manifest:file-entry manifest:media-type=\"\" manifest:full-path=\"Thumbnails/\"/>
      <manifest:file-entry manifest:media-type=\"text/xml\" manifest:full-path=\"settings.xml\"/>
      </manifest:manifest>";
  }

  function generate_content(){
      $f=fopen($this->tmp_content."/content.xml","w");

      fputs($f,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>
      <office:document-content xmlns:office=\"urn:oasis:names:tc:opendocument:xmlns:office:1.0\" xmlns:style=\"urn:oasis:names:tc:opendocument:xmlns:style:1.0\" xmlns:text=\"urn:oasis:names:tc:opendocument:xmlns:text:1.0\" xmlns:table=\"urn:oasis:names:tc:opendocument:xmlns:table:1.0\" xmlns:draw=\"urn:oasis:names:tc:opendocument:xmlns:drawing:1.0\" xmlns:fo=\"urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:meta=\"urn:oasis:names:tc:opendocument:xmlns:meta:1.0\" xmlns:number=\"urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0\" xmlns:presentation=\"urn:oasis:names:tc:opendocument:xmlns:presentation:1.0\" xmlns:svg=\"urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0\" xmlns:chart=\"urn:oasis:names:tc:opendocument:xmlns:chart:1.0\" xmlns:dr3d=\"urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0\" xmlns:math=\"http://www.w3.org/1998/Math/MathML\" xmlns:form=\"urn:oasis:names:tc:opendocument:xmlns:
form:1.0\" xmlns:script=\"urn:oasis:names:tc:opendocument:xmlns:script:1.0\" xmlns:ooo=\"http://openoffice.org/2004/office\" xmlns:ooow=\"http://openoffice.org/2004/writer\" xmlns:oooc=\"http://openoffice.org/2004/calc\" xmlns:dom=\"http://www.w3.org/2001/xml-events\" xmlns:xforms=\"http://www.w3.org/2002/xforms\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:rpt=\"http://openoffice.org/2005/report\" xmlns:of=\"urn:oasis:names:tc:opendocument:xmlns:of:1.2\" xmlns:rdfa=\"http://docs.oasis-open.org/opendocument/meta/rdfa#\" xmlns:field=\"urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0\" xmlns:formx=\"urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0\" office:version=\"1.2\">
      <office:scripts/>\n");

      fputs($f,"<office:font-face-decls>
      <style:font-face style:name=\"Arial\" svg:font-family=\"Arial\" style:font-family-generic=\"swiss\" style:font-pitch=\"variable\"/>
      <style:font-face style:name=\"Nimbus Sans L\" svg:font-family=\"&apos;Nimbus Sans L&apos;\" style:font-family-generic=\"swiss\" style:font-pitch=\"variable\"/>
      <style:font-face style:name=\"DejaVu Sans\" svg:font-family=\"&apos;DejaVu Sans&apos;\" style:font-family-generic=\"system\" style:font-pitch=\"variable\"/>
      <style:font-face style:name=\"Lucida Sans Unicode\" svg:font-family=\"&apos;Lucida Sans Unicode&apos;\" style:font-family-generic=\"system\" style:font-pitch=\"variable\"/>
      <style:font-face style:name=\"Tahoma\" svg:font-family=\"Tahoma\" style:font-family-generic=\"system\" style:font-pitch=\"variable\"/>
      </office:font-face-decls>\n");

      fputs($f,"<office:automatic-styles>\n");
      foreach($this->tables as $table){
	  fputs($f,$table->generate_style());
      }
      foreach($this->styles as $style){
	  fputs($f,$style->generate());
      }
      foreach($this->images as $image){
	  fputs($f,$image->generate_style());
      }
      fputs($f,"</office:automatic-styles>");

      fputs($f,"<office:body>\n");
      fputs($f,"<office:spreadsheet>\n");

      foreach($this->tables as $table){
	  fputs($f,$table->generate());
      }
      foreach($this->images as $image){
	//  $ret.=$image->generate();
      }

      fputs($f," </office:spreadsheet>
      </office:body>
      </office:document-content>");

      fclose($f);
  }

function generate_style(){
    $ret= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<office:document-styles xmlns:office=\"urn:oasis:names:tc:opendocument:xmlns:office:1.0\" xmlns:style=\"urn:oasis:names:tc:opendocument:xmlns:style:1.0\" xmlns:text=\"urn:oasis:names:tc:opendocument:xmlns:text:1.0\" xmlns:table=\"urn:oasis:names:tc:opendocument:xmlns:table:1.0\" xmlns:draw=\"urn:oasis:names:tc:opendocument:xmlns:drawing:1.0\" xmlns:fo=\"urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:meta=\"urn:oasis:names:tc:opendocument:xmlns:meta:1.0\" xmlns:number=\"urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0\" xmlns:presentation=\"urn:oasis:names:tc:opendocument:xmlns:presentation:1.0\" xmlns:svg=\"urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0\" xmlns:chart=\"urn:oasis:names:tc:opendocument:xmlns:chart:1.0\" xmlns:dr3d=\"urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0\" xmlns:math=\"http://www.w3.org/1998/Math/MathML\" xmlns:form=\"urn:oasis:names:tc:opendocument:xmlns:form:1.
0\" xmlns:script=\"urn:oasis:names:tc:opendocument:xmlns:script:1.0\" xmlns:ooo=\"http://openoffice.org/2004/office\" xmlns:ooow=\"http://openoffice.org/2004/writer\" xmlns:oooc=\"http://openoffice.org/2004/calc\" xmlns:dom=\"http://www.w3.org/2001/xml-events\" xmlns:rpt=\"http://openoffice.org/2005/report\" xmlns:of=\"urn:oasis:names:tc:opendocument:xmlns:of:1.2\" xmlns:rdfa=\"http://docs.oasis-open.org/opendocument/meta/rdfa#\" office:version=\"1.2\">
<office:font-face-decls>
  <style:font-face style:name=\"Arial\" svg:font-family=\"Arial\" style:font-family-generic=\"swiss\" style:font-pitch=\"variable\"/>
  <style:font-face style:name=\"Nimbus Sans L\" svg:font-family=\"&apos;Nimbus Sans L&apos;\" style:font-family-generic=\"swiss\" style:font-pitch=\"variable\"/>
  <style:font-face style:name=\"DejaVu Sans\" svg:font-family=\"&apos;DejaVu Sans&apos;\" style:font-family-generic=\"system\" style:font-pitch=\"variable\"/>
  <style:font-face style:name=\"Lucida Sans Unicode\" svg:font-family=\"&apos;Lucida Sans Unicode&apos;\" style:font-family-generic=\"system\" style:font-pitch=\"variable\"/>
  <style:font-face style:name=\"Tahoma\" svg:font-family=\"Tahoma\" style:font-family-generic=\"system\" style:font-pitch=\"variable\"/>
</office:font-face-decls>
<office:styles>
  <style:default-style style:family=\"table-cell\">
    <style:table-cell-properties style:decimal-places=\"2\"/>
    <style:paragraph-properties style:tab-stop-distance=\"1.25cm\"/>
    <style:text-properties style:font-name=\"Arial\" fo:language=\"fr\" fo:country=\"FR\" style:font-name-asian=\"Lucida Sans Unicode\" style:language-asian=\"zxx\" style:country-asian=\"none\" style:font-name-complex=\"Tahoma\" style:language-complex=\"zxx\" style:country-complex=\"none\"/>
  </style:default-style>
  <number:number-style style:name=\"N0\">
    <number:number number:min-integer-digits=\"1\"/>
  </number:number-style>
  <number:currency-style style:name=\"N108P0\" style:volatile=\"true\">
    <number:number number:decimal-places=\"0\" number:min-integer-digits=\"1\" number:grouping=\"true\"/>
    <number:text></number:text>
    <number:currency-symbol number:language=\"fr\" number:country=\"FR\">€</number:currency-symbol>
  </number:currency-style>
  <number:currency-style style:name=\"N108\">
    <style:text-properties fo:color=\"#ff0000\"/>
    <number:text>-</number:text>
    <number:number number:decimal-places=\"0\" number:min-integer-digits=\"1\" number:grouping=\"true\"/>
    <number:text> </number:text>
    <number:currency-symbol number:language=\"fr\" number:country=\"FR\">€</number:currency-symbol>
    <style:map style:condition=\"value()&gt;>=0\" style:apply-style-name=\"N108P0\"/>
  </number:currency-style>
  <number:currency-style style:name=\"N106P0\" style:volatile=\"true\">
    <number:number number:decimal-places=\"2\" number:min-integer-digits=\"1\" number:grouping=\"true\"/>
    <number:text></number:text>
    <number:currency-symbol number:language=\"fr\" number:country=\"FR\">€</number:currency-symbol>
  </number:currency-style>
  <number:currency-style style:name=\"N109P0\" style:volatile=\"true\">
    <number:number number:decimal-places=\"0\" number:min-integer-digits=\"1\" number:grouping=\"true\"/>
    <number:text> </number:text>
    <number:currency-symbol number:language=\"fr\" number:country=\"FR\">€</number:currency-symbol>
  </number:currency-style>
  <number:currency-style style:name=\"N109\">
    <number:text>-</number:text>
    <number:number number:decimal-places=\"0\" number:min-integer-digits=\"1\" number:grouping=\"true\"/>
    <number:text> </number:text>
    <number:currency-symbol number:language=\"fr\" number:country=\"FR\">€</number:currency-symbol>
    <style:map style:condition=\"value()>=0\" style:apply-style-name=\"N109P0\"/>
  </number:currency-style>
  <number:currency-style style:name=\"N106\">
    <style:text-properties fo:color=\"#ff0000\"/>
    <number:text>-</number:text>
    <number:number number:decimal-places=\"2\" number:min-integer-digits=\"1\" number:grouping=\"true\"/>
    <number:text> </number:text>
    <number:currency-symbol number:language=\"fr\" number:country=\"FR\">€</number:currency-symbol>
    <style:map style:condition=\"value()&gt;=0\" style:apply-style-name=\"N106P0\"/>
  </number:currency-style>
  <style:style style:name=\"Default\" style:family=\"table-cell\"/>
  <style:style style:name=\"Result\" style:family=\"table-cell\" style:parent-style-name=\"Default\">
    <style:text-properties fo:font-style=\"italic\" style:text-underline-style=\"solid\" style:text-underline-width=\"auto\" style:text-underline-color=\"font-color\" fo:font-weight=\"bold\"/>
  </style:style>
  <style:style style:name=\"Result2\" style:family=\"table-cell\" style:parent-style-name=\"Result\" style:data-style-name=\"N108\"/>
  <style:style style:name=\"Heading\" style:family=\"table-cell\" style:parent-style-name=\"Default\">
    <style:table-cell-properties style:text-align-source=\"fix\" style:repeat-content=\"false\"/>
    <style:paragraph-properties fo:text-align=\"center\"/>
    <style:text-properties fo:font-size=\"16pt\" fo:font-style=\"italic\" fo:font-weight=\"bold\"/>
  </style:style>
  <style:style style:name=\"Heading1\" style:family=\"table-cell\" style:parent-style-name=\"Heading\">
    <style:table-cell-properties style:rotation-angle=\"90\"/>
  </style:style>
 </office:styles>
 <office:automatic-styles>
  <style:page-layout style:name=\"Mpm1\">
    <style:page-layout-properties style:writing-mode=\"lr-tb\"/>
    <style:header-style>
      <style:header-footer-properties fo:min-height=\"0.751cm\" fo:margin-left=\"0cm\" fo:margin-right=\"0cm\" fo:margin-bottom=\"0.25cm\"/>
    </style:header-style>
    <style:footer-style>
      <style:header-footer-properties fo:min-height=\"0.751cm\" fo:margin-left=\"0cm\" fo:margin-right=\"0cm\" fo:margin-top=\"0.25cm\"/>
    </style:footer-style>
  </style:page-layout>
  <style:page-layout style:name=\"Mpm2\">
    <style:page-layout-properties style:writing-mode=\"lr-tb\"/>
    <style:header-style>
      <style:header-footer-properties fo:min-height=\"0.751cm\" fo:margin-left=\"0cm\" fo:margin-right=\"0cm\" fo:margin-bottom=\"0.25cm\" fo:border=\"0.088cm solid #000000\" fo:padding=\"0.018cm\" fo:background-color=\"#c0c0c0\">
	<style:background-image/>
      </style:header-footer-properties>
    </style:header-style>
    <style:footer-style>
      <style:header-footer-properties fo:min-height=\"0.751cm\" fo:margin-left=\"0cm\" fo:margin-right=\"0cm\" fo:margin-top=\"0.25cm\" fo:border=\"0.088cm solid #000000\" fo:padding=\"0.018cm\" fo:background-color=\"#c0c0c0\">
	<style:background-image/>
      </style:header-footer-properties>
    </style:footer-style>
    </style:page-layout>
   </office:automatic-styles>
   <office:master-styles>
    <style:master-page style:name=\"Default\" style:page-layout-name=\"Mpm1\">
      <style:header>
	<text:p>
	  <text:sheet-name>???</text:sheet-name>
	</text:p>
      </style:header>
      <style:header-left style:display=\"false\"/>
      <style:footer>
	<text:p>
	  Page <text:page-number>1</text:page-number>
	</text:p>
      </style:footer>
      <style:footer-left style:display=\"false\"/>
    </style:master-page>
    <style:master-page style:name=\"Report\" style:page-layout-name=\"Mpm2\">
      <style:header>
	<style:region-left>
	  <text:p><text:sheet-name>???</text:sheet-name> (<text:title>???</text:title>)</text:p>
	</style:region-left>
	<style:region-right>
	  <text:p>
	    <text:date style:data-style-name=\"N2\" text:date-value=\"".date("Y-m-d")."\">".date("d/m/Y")."</text:date>, <text:time>".date("h:i:s")."</text:time>
	  </text:p>
	</style:region-right>
      </style:header>
      <style:header-left style:display=\"false\"/>
      <style:footer>
	<text:p>Page <text:page-number>1</text:page-number> / <text:page-count>99</text:page-count></text:p>
      </style:footer>
      <style:footer-left style:display=\"false\"/>
    </style:master-page>
   </office:master-styles>
</office:document-styles>";

  //  $ret=str_replace("\t","",$ret);
  //  $ret=str_replace("\r\n","",$ret);

    return $ret;
}


function generate_settings(){
    if($this->activesheet=="") $this->activesheet=$this->tables[0]->name;

    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<office:document-settings xmlns:office=\"urn:oasis:names:tc:opendocument:xmlns:office:1.0\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:config=\"urn:oasis:names:tc:opendocument:xmlns:config:1.0\" xmlns:ooo=\"http://openoffice.org/2004/office\" office:version=\"1.2\">
  <office:settings>
    <config:config-item-set config:name=\"ooo:view-settings\">
      <config:config-item config:name=\"VisibleAreaTop\" config:type=\"int\">0</config:config-item>
      <config:config-item config:name=\"VisibleAreaLeft\" config:type=\"int\">0</config:config-item>
      <config:config-item config:name=\"VisibleAreaWidth\" config:type=\"int\">12772</config:config-item>
      <config:config-item config:name=\"VisibleAreaHeight\" config:type=\"int\">10194</config:config-item>
      <config:config-item-map-indexed config:name=\"Views\">
	<config:config-item-map-entry>
	  <config:config-item config:name=\"ViewId\" config:type=\"string\">View1</config:config-item>
	  <config:config-item-map-named config:name=\"Tables\">
	    <config:config-item-map-entry config:name=\"".$this->activesheet."\">
	      <config:config-item config:name=\"CursorPositionX\" config:type=\"int\">".$this->cursorpos_x."</config:config-item>
	      <config:config-item config:name=\"CursorPositionY\" config:type=\"int\">".$this->cursorpos_y."</config:config-item>
	      <config:config-item config:name=\"HorizontalSplitMode\" config:type=\"short\">0</config:config-item>
	      <config:config-item config:name=\"VerticalSplitMode\" config:type=\"short\">0</config:config-item>
	      <config:config-item config:name=\"HorizontalSplitPosition\" config:type=\"int\">0</config:config-item>
	      <config:config-item config:name=\"VerticalSplitPosition\" config:type=\"int\">0</config:config-item>
	      <config:config-item config:name=\"ActiveSplitRange\" config:type=\"short\">2</config:config-item>
	      <config:config-item config:name=\"PositionLeft\" config:type=\"int\">0</config:config-item>
	      <config:config-item config:name=\"PositionRight\" config:type=\"int\">0</config:config-item>
	      <config:config-item config:name=\"PositionTop\" config:type=\"int\">0</config:config-item>
	      <config:config-item config:name=\"PositionBottom\" config:type=\"int\">0</config:config-item>
	      <config:config-item config:name=\"ZoomType\" config:type=\"short\">0</config:config-item>
	      <config:config-item config:name=\"ZoomValue\" config:type=\"int\">100</config:config-item>
	      <config:config-item config:name=\"PageViewZoomValue\" config:type=\"int\">60</config:config-item>
	      <config:config-item config:name=\"ShowGrid\" config:type=\"boolean\">true</config:config-item>
	    </config:config-item-map-entry>
	  </config:config-item-map-named>
	  <config:config-item config:name=\"ActiveTable\" config:type=\"string\">".$this->activesheet."</config:config-item>
	  <config:config-item config:name=\"HorizontalScrollbarWidth\" config:type=\"int\">270</config:config-item>
	  <config:config-item config:name=\"ZoomType\" config:type=\"short\">0</config:config-item>
	  <config:config-item config:name=\"ZoomValue\" config:type=\"int\">100</config:config-item>
	  <config:config-item config:name=\"PageViewZoomValue\" config:type=\"int\">60</config:config-item>
	  <config:config-item config:name=\"ShowPageBreakPreview\" config:type=\"boolean\">false</config:config-item>
	  <config:config-item config:name=\"ShowZeroValues\" config:type=\"boolean\">true</config:config-item>
	  <config:config-item config:name=\"ShowNotes\" config:type=\"boolean\">true</config:config-item>
	  <config:config-item config:name=\"ShowGrid\" config:type=\"boolean\">true</config:config-item>
	  <config:config-item config:name=\"GridColor\" config:type=\"long\">12632256</config:config-item>
	  <config:config-item config:name=\"ShowPageBreaks\" config:type=\"boolean\">true</config:config-item>
	  <config:config-item config:name=\"HasColumnRowHeaders\" config:type=\"boolean\">true</config:config-item>
	  <config:config-item config:name=\"HasSheetTabs\" config:type=\"boolean\">true</config:config-item>
	  <config:config-item config:name=\"IsOutlineSymbolsSet\" config:type=\"boolean\">true</config:config-item>
	  <config:config-item config:name=\"IsSnapToRaster\" config:type=\"boolean\">false</config:config-item>
	  <config:config-item config:name=\"RasterIsVisible\" config:type=\"boolean\">false</config:config-item>
	  <config:config-item config:name=\"RasterResolutionX\" config:type=\"int\">1000</config:config-item>
	  <config:config-item config:name=\"RasterResolutionY\" config:type=\"int\">1000</config:config-item>
	  <config:config-item config:name=\"RasterSubdivisionX\" config:type=\"int\">1</config:config-item>
	  <config:config-item config:name=\"RasterSubdivisionY\" config:type=\"int\">1</config:config-item>
	  <config:config-item config:name=\"IsRasterAxisSynchronized\" config:type=\"boolean\">true</config:config-item>
	</config:config-item-map-entry>
      </config:config-item-map-indexed>
    </config:config-item-set>
    <config:config-item-set config:name=\"ooo:configuration-settings\">
      <config:config-item config:name=\"ShowZeroValues\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"ShowNotes\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"ShowGrid\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"GridColor\" config:type=\"long\">12632256</config:config-item>
      <config:config-item config:name=\"ShowPageBreaks\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"LinkUpdateMode\" config:type=\"short\">3</config:config-item>
      <config:config-item config:name=\"HasColumnRowHeaders\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"HasSheetTabs\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"IsOutlineSymbolsSet\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"IsSnapToRaster\" config:type=\"boolean\">false</config:config-item>
      <config:config-item config:name=\"RasterIsVisible\" config:type=\"boolean\">false</config:config-item>
      <config:config-item config:name=\"RasterResolutionX\" config:type=\"int\">1000</config:config-item>
      <config:config-item config:name=\"RasterResolutionY\" config:type=\"int\">1000</config:config-item>
      <config:config-item config:name=\"RasterSubdivisionX\" config:type=\"int\">1</config:config-item>
      <config:config-item config:name=\"RasterSubdivisionY\" config:type=\"int\">1</config:config-item>
      <config:config-item config:name=\"IsRasterAxisSynchronized\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"AutoCalculate\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"PrinterName\" config:type=\"string\"/>
      <config:config-item config:name=\"PrinterSetup\" config:type=\"base64Binary\"/>
      <config:config-item config:name=\"ApplyUserData\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"CharacterCompressionType\" config:type=\"short\">0</config:config-item>
      <config:config-item config:name=\"IsKernAsianPunctuation\" config:type=\"boolean\">false</config:config-item>
      <config:config-item config:name=\"SaveVersionOnClose\" config:type=\"boolean\">false</config:config-item>
      <config:config-item config:name=\"UpdateFromTemplate\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"AllowPrintJobCancel\" config:type=\"boolean\">true</config:config-item>
      <config:config-item config:name=\"LoadReadonly\" config:type=\"boolean\">false</config:config-item>
      <config:config-item config:name=\"IsDocumentShared\" config:type=\"boolean\">false</config:config-item>
    </config:config-item-set>
  </office:settings>
</office:document-settings>";
}

function generate_meta(){
    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
    <office:document-meta xmlns:office=\"urn:oasis:names:tc:opendocument:xmlns:office:1.0\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:meta=\"urn:oasis:names:tc:opendocument:xmlns:meta:1.0\" xmlns:ooo=\"http://openoffice.org/2004/office\" office:version=\"1.2\">
    <office:meta>
    <meta:creation-date>".date("Y-m-d")."T".date("h:i:s.00")."</meta:creation-date>
    <dc:date>".date("Y-m-d")."T".date("h:i:s")."</dc:date>
    <meta:editing-duration>PT02H08M38S</meta:editing-duration>
    <meta:editing-cycles>5</meta:editing-cycles>
    <meta:generator>OpenOffice.org/3.1\$Linux OpenOffice.org_project/310m19\$Build-9420</meta:generator>
    <meta:document-statistic meta:table-count=\"1\" meta:cell-count=\"1\" meta:object-count=\"0\"/>
    <meta:user-defined meta:name=\"Info 1\"/>
    <meta:user-defined meta:name=\"Info 2\"/>
    <meta:user-defined meta:name=\"Info 3\"/>
    <meta:user-defined meta:name=\"Info 4\"/>
    </office:meta>
    </office:document-meta>";
    
}


}

class ooo_table{
    
  var $name;
  var $style;

  var $colproperties;
  var $rowproperties;
  var $cells;
  
  var $rowmax=0;
  var $colmax=0;

  function ooo_table($name=""){
      $this->name=$name;
  }
  
  function addcell($x,$y,$cell){
      $this->cells[$y][$x]=$cell;
      if($y>$this->rowmax) $this->rowmax=$y;
      if($x>$this->colmax) $this->colmax=$x;
  }
  function addrow($y,$cells){
      $x=0;
      foreach($cells as $cell){
	  $x++;
	  $this->addcell($x,$y,$cell);
      }
  }

  function set_row($id,$height){
      $this->rowproperties[$id]=$height;
  }

  function set_col($id,$width){
      $this->colproperties[$id]=$width;
  }

  function generate_style(){

      $ret="";

      for($x=1;$x<=$this->colmax;$x++){
	   $ret.="<style:style style:name=\"".$this->name."_co".$x."\" style:family=\"table-column\">
	   <style:table-column-properties style:column-width=\"".$this->colproperties[$x]."\" fo:break-before=\"auto\"/>
	   </style:style>\n";
      }

      for($y=1;$y<=$this->rowmax;$y++){
	   $ret.="<style:style style:name=\"".$this->name."_ro".$y."\" style:family=\"table-row\">
	   <style:table-row-properties style:row-height=\"".$this->rowproperties[$y]."\" fo:break-before=\"auto\" style:use-optimal-row-height=\"false\"/>
	   </style:style>\n";
      }

      return $ret;
  }

  function generate(){
      $ret="<table:table table:name=\"".$this->name."\"";
      if($this->style!="") $ret.=" table:style-name=\"".$this->style."\"";
      $ret.=" table:print=\"false\">";
      $c=0;
      for($c=1;$c<=$this->colmax;$c++){

	  $ret.="<table:table-column table:style-name=\"".$this->name."_co".$c."\"/>\n";
      }

      for($y=1;$y<=$this->rowmax;$y++){
	  $thiscellcols=1;
	  if(is_array($this->cells[$y])){
	    $keys=array_keys($this->cells[$y]);
	    $thiscellcols=max($keys);
	  }
	  $ret.="<table:table-row table:style-name=\"".$this->name."_ro".$y."\">\n";

	  for($x=1;$x<=$thiscellcols;$x++){
	    $cell=$this->cells[$y][$x];
	    if(!is_a($cell,"ooo_cell")) $cell=new ooo_cell("");
	    $ret.=$cell->generate();
	    
	  }
	  $ret.="</table:table-row>\n";
      }
      $ret.="</table:table>\n";

      return $ret;
  }

}

class ooo_cell{
    
    var $style;
    var $type="string";
    var $formula;
    var $currencysign="EUR";
    
    var $content;

    function ooo_cell($content,$style="",$type="string"){
	  if($content=="") $content=" ";
	  $this->content=htmlspecialchars($content);
	  $this->style=$style;
	  $this->type=$type;
    }

    function generate(){
	if($this->content=="") return "<table:table-cell/>\n";
	$ret="<table:table-cell";
	if($this->style!="") $ret.=" table:style-name=\"".$this->style."\"";
	if($this->formula!="") $ret.=" table:formula=\"".$this->formula."\"";
	$ret.=" office:value-type=\"".$this->type."\"";
	
	switch($this->type){
	    case "float":
	    $ret.=" office:value=\"".floatval($this->content)."\"";
	    break;
	    case "currency":
	    $ret.=" office:currency=\"".$this->currencysign."\" office:value=\"".floatval($this->content)."\"";
	    $this->content=number_format(floatval($this->content),2,',','')." €";
	    break;
	    case "date":
	    $ret.=" office:date-value=\"".$this->content."\"";
	    break;
	}
	$ret.=">";
	
	$ret.="<text:p>".$this->content."</text:p>";
	$ret.="</table:table-cell>\n";

	return $ret;
    }
}

class ooo_image{
    
    var $name;
    var $style;
    var $url;
    var $width;
    var $height;
    var $x="0cm";
    var $y="0cm";
    var $z=0;

    function ooo_image($url){
	$this->url=$url;
	$file=pathinfo($url);
	$this->filename=$file['basename'];
    }

    function generate(){
	return "<draw:frame draw:z-index=\"".$this->z."\" draw:name=\"".$this->name."\" draw:style-name=\"".$this->style."\" svg:width=\"".$this->width."\" svg:height=\"".$this->height."\" svg:x=\"".$this->x."\" svg:y=\"".$this->y."\">
	<draw:image xlink:href=\"Pictures/".$this->filename."\" xlink:type=\"simple\" xlink:show=\"embed\" xlink:actuate=\"onLoad\"/>
	</draw:frame>\n";
    }

    function generate_style(){
	return "<style:style style:name=\"".$this->style."\" style:family=\"graphic\">
	<style:graphic-properties style:vertical-pos=\"from-top\" style:vertical-rel=\"paragraph\" style:horizontal-pos=\"from-left\" style:horizontal-rel=\"paragraph\" draw:stroke=\"none\" draw:fill=\"none\" draw:textarea-horizontal-align=\"center\" draw:textarea-vertical-align=\"middle\" draw:color-mode=\"standard\" draw:luminance=\"0%\" draw:contrast=\"0%\" draw:gamma=\"100%\" draw:red=\"0%\" draw:green=\"0%\" draw:blue=\"0%\" fo:clip=\"rect(0cm, 0cm, 0cm, 0cm)\" draw:image-opacity=\"100%\" style:mirror=\"none\"/>
	</style:style>\n";
    }
}


class ooo_style{
  var $name;
  var $family;
  
  var $border_bottom="none";
  var $border_top="none";
  var $border_left="none";
  var $border_right="none";

  var $text_align;
  var $vertical_align;

  var $margin_left="0cm";

  var $font_weight;
  var $font_color;
  
  var $background_color;

  var $currencydigits;
  var $percentagedigits;

  function ooo_style($name){
      $this->name=$name;
  }
  
  function generate(){
      $ret="";
      $datastyle="";
      if(!is_null($this->currencydigits)){
	  $ret.="<number:currency-style style:name=\"".$this->name."N0\">\n
	  <number:number number:decimal-places=\"".$this->currencydigits."\" number:min-integer-digits=\"1\" number:grouping=\"true\"/>
	  <number:text> </number:text>
	  <number:currency-symbol number:language=\"fr\" number:country=\"FR\">€</number:currency-symbol>
	  </number:currency-style>\n";
	  $datastyle=" style:data-style-name=\"".$this->name."N0\"";
      }
      if(!is_null($this->percentagedigits)){
	  $ret.="<number:percentage-style style:name=\"".$this->name."N5\">
	  <number:number number:decimal-places=\"".$this->percentagedigits."\" number:min-integer-digits=\"1\" number:grouping=\"true\"/>
	  <number:text>%</number:text>
	  </number:percentage-style>";
	  $datastyle=" style:data-style-name=\"".$this->name."N5\"";
      }

      $ret.="<style:style style:name=\"".$this->name."\" style:family=\"".$this->family."\" style:parent-style-name=\"Default\"$datastyle>\n";
      if($this->family=="table-cell") $ret.="<style:table-cell-properties fo:border-bottom=\"".$this->border_bottom."\" fo:border-top=\"".$this->border_top."\" fo:border-left=\"".$this->border_left."\" fo:border-right=\"".$this->border_right."\" style:text-align-source=\"fix\" style:repeat-content=\"false\" fo:background-color=\"".$this->background_color."\"/>\n";
      $ret.="<style:paragraph-properties fo:text-align=\"".$this->text_align."\" fo:margin-left=\"".$this->margin_left."\"/>\n";	
      $ret.="<style:text-properties fo:color=\"".$this->font_color."\" fo:font-weight=\"".$this->font_weight."\" style:font-weight-asian=\"".$this->font_weight."\" style:font-weight-complex=\"".$this->font_weight."\"/>\n";
      $ret.="</style:style>\n";

      return $ret;

  }
  
  function setborders($borders){
      $this->border_bottom=$this->border_left=$this->border_right=$this->border_top=$borders;
  }

}

?>