//Ouvre une popup pour le lecteur vidéo

function popvideo(file, width, height) {
	var left = (screen.width-width)/2;
	var top  = (screen.height-height)/2;
	var wPopup  = window.open("video.php?video="+file, "_blank", "width="+width+", height="+height+", left="+left+", top="+top);
	
	wPopup.focus();
}
