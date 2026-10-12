<?php

require "admin_global.php";

// télécharger un document	
if (isset($action) and $action == "telechargerdoc")
	{
	$docu=$DB_site2->query_first("select extension from document where documentid = '$doc'") ;
	$chemin = "/home/boutique/www/configurations/$host/images/docs/";
	$fichier = "$doc.$docu[0]";
	switch ($docu[0])  // définition de l'extension
		{
		case "application/php":	
			$file = "php" ;
			break ;
		case "application/x-php":
			$file = "php" ;
			break ;
		case "pls":
			$file = "audio/scpls" ;
			break;
		case "m3u":
			$file = "audio/x-mpegurl" ;
			break;
		case "swf":
			$file = "application/x-shockwave-flash" ;
			break;
		case "eps":
			$file = "application/postscript" ;
			break;
		case "doc":
			$file = "application/msword" ;
			break;
		case "rtf":
			$file = "application/msword" ;
			break;
		case "xls":
			$file = "application/excel" ;
			break;
		case "pps":
			$file = "application/vnd.ms-powerpoint" ;
			break;
		case "pdf":
			$file = "application/pdf" ;
			break;
		case "gzip":
			$file = "application/x-gzip" ;
			break;
		case "dwg":
			$file = "application/acad" ;
			break;
		case "ccad":
			$file = "application/clariscad" ;
			break;
		case "drw":
			$file = "application/drafting" ;
		   break;
		case "dxf":
			$file = "application/dxf" ;
		   break;
		case "unv":
			$file = "application/i-deas" ;
		   break;
		case "igs":
			$file = "application/iges" ;
		   break;
		case "oda":
			$file = "application/oda" ;
		   break;
		case "dwg":
			$file = "application/pdf" ;
		   break;
		case "ai":
			$file = "application/postscript" ;
		   break;
		case "prt":
			$file = "application/pro" ;
		   break;
		case "rtf":
			$file = "application/rtf" ;
		   break;
		case "set":
			$file = "application/set" ;
		   break;
		case "stl":
			$file = "application/sla" ;
		   break;
		case "dwg":
			$file = "application/solids" ;
		   break;
		case "step":
			$file = "application/step" ;
		   break;
		case "vda":
			$file = "application/vda" ;
		   break;
		case "mif":
			$file = "application/x-mif" ;
		   break;
		case "dwg":
			$file = "application/x-csh" ;
		   break;
		case "dvi":
			$file = "application/x-dvi" ;
		   break;
		case "hdf":
			$file = "application/hdf" ;
		   break;
		case "latex":
			$file = "application/x-latex" ;
		   break;
		case "nc":
			$file = "application/x-netcdf" ;
		   break;
		case "dwg":
			$file = "application/x-sh" ;
		   break;
		case "tcl":
			$file = "application/x-tcl" ;
		   break;
		case "tex":
			$file = "application/x-tex" ;
		   break;
		case "texi":
			$file = "application/x-texinfo" ;
		   break;
		case "troff":
			$file = "application/x-troff" ;
		   break;
		case "man":
			$file = "application/x-troff-man" ;
		   break;
		case "me":
			$file = "application/x-troff-me" ;
		   break;
	
		case "ms":
			$file = "application/x-troff-ms" ;
		   break;
		case "src":
			$file = "application/x-wais-source" ;
		   break;
		case "bcpio":
			$file = "application/x-bcpio" ;
		   break;
		case "cpio":
			$file = "application/x-cpio" ;
		   break;
		case "gtar":
			$file = "application/x-gtar" ;
		   break;
		case "shar":
			$file = "application/x-shar" ;
		   break;
		case "sv4cpio":
			$file = "application/x-sv4cpio" ;
		   break;
		case "sc4crc":
			$file = "application/x-sv4crc" ;
		   break;
		case "tar":
			$file = "application/x-tar" ;
		   break;
		case "tar":
			$file = "application/x-ustar" ;
		   break;
		case "ZIP":
			$file = "application/zip" ;
		   break;
		case "bz2":
			$file = "application/x-bzip" ;
		   break;
		case "ra":
			$file = "audio/vnd.rn-realaudio" ;
		   break;
		case "ram":
			$file = "audio/x-pn-realaudio" ;
		   break;
		case "rm":
			$file = "application/vnd.rn-realmedia" ;
		   break;
		case "rv":
			$file = "video/vnd.rn-realvideo" ;
		   break;
		case "wav":
			$file = "audio/wav" ;
		   break;
		case "ogg":
			$file = "audio/ogg" ;
		   break;
		case "mp3":
			$file = "audio/mpeg" ;
		   break;
		case "aif":
			$file = "audio/aiff" ;
			break;
		case "midi":
			$file = "audio/mid" ;
			break;
		case "snd":
			$file = "audio/basic" ;
		   break;
		case "aif":
			$file = "audio/x-aiff" ;
		   break;
		case "wav":
			$file = "audio/x-wav" ;
		   break;
		case "psd":
			$file = "image/psd" ;
		   break;
		case "png":
			$file = "image/png" ;
		   break;
		case "bmp":
			$file = "image/bmp" ;
		   break;
		case "gif":
			$file = "image/gif" ;
		   break;
		case "ief":
			$file = "image/ief" ;
		   break;
		case "jpg":
			$file = "image/jpeg" ;
		   break;
		case "jpg":
			$file = "image/pjpeg" ;
		   break;
		case "tiff":
			$file = "image/tiff" ;
		   break;
		case "cmu":
			$file = "image/x-cmu-raster" ;
		   break;
		case "pnm":
			$file = "image/x-portable-anymap" ;
		   break;
		case "pbm":
			$file = "image/x-portable-bitmap" ;
		   break;
		case "pgm":
			$file = "image/x-portable-graymap" ;
		   break;
		case "ppm":
			$file = "image/x-portable-pixmap" ;
		   break;
		case "rgb":
			$file = "image/x-rgb" ;
		   break;
		case "xbm":
			$file = "image/x-xbitmap" ;
		   break;
		case "xpm":
			$file = "image/x-xpixmap" ;
		   break;
		case "dump":
			$file = "image/x-xwindowdump" ;
		   break;
		case "zip":
			$file = "multipart/x-zip" ;
		   break;
		case "gzip":
			$file = "multipart/x-gzip" ;
		   break;
		case "xml":
			$file = "text/xml" ;
		   break;
		case "xsl":
			$file = "text/xsl" ;
		   break;
		case "html":
			$file = "text/html" ;
		   break;
		case "txt":
			$file = "text/plain" ;
		   break;
		case "rtx":
			$file = "text/richtext" ;
		   break;
		case "tsv":
			$file = "text/tab-separated-value" ;
		   break;
		case "etx":
			$file = "text/x-setext" ;
		   break;
		case "css":
			$file = "text/css" ;
		   break;
		case "wmv":
			$file = "video/x-ms-wmv" ;
		   break;
		case "wmx":
			$file = "video/x-ms-wmx" ;
		   break;
		case "avi":
			$file = "video/avi" ;
		   break;
		case "asx":
			$file = "video/x-ms-asf" ;
		   break;
		case "mpeg":
			$file = "video/mpeg" ;
		   break;
		case "mov":
			$file = "video/quicktime" ;
		   break;
		case "avi":
			$file = "video/msvideo" ;
		   break;
		case "movie":
			$file = "video/x-sgi-movie" ;
		   break;
		}
	header("Content-disposition: attachment; filename=$fichier");
	header("Content-Type: application/force-download");
	header("Content-Transfer-Encoding: $file");
	header("Pragma: no-cache");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
	header("Expires: 0");
	readfile($chemin . $fichier);
	}
?>
