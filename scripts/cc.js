/***********************************************************************************************
Copyright (c) 2005 - Alf Magne Kalleland post@dhtmlgoodies.com
UPDATE LOG:
March, 10th, 2006 : Added support for a message while large image is loading
Get this and other scripts at www.dhtmlgoodies.com
You can use this script freely as long as this copyright message is kept intact.
***********************************************************************************************/ 


var displayWaitMessage=true;	// Display a please wait message while images are loading?
	
var activeImage = false;
var imageGalleryLeftPos = false;
var imageGalleryWidth = false;
var imageGalleryObj = false;
var maxGalleryXPos = false;
var slideSpeed = 0;
var imageGalleryCaptions = new Array();

function startSlide(e){
	if(document.all)e = event;
	var id = this.id;
	//this.getElementsByTagName('IMG')[0].src = '/images/' + this.id + '_over.gif';	
	if(this.id=='arrow_right'){
		slideSpeedMultiply = 6;
		slideSpeed = -1*slideSpeedMultiply;
		slideSpeed = Math.max(-10,slideSpeed);
	}else{
					
		slideSpeedMultiply = 6;
		slideSpeed = 1*slideSpeedMultiply;
		slideSpeed = Math.min(10,slideSpeed);
		if(slideSpeed<0)slideSpeed=10;
	}
}

function releaseSlide(){
	var id = this.id;
	slideSpeed=0;
	if(this.id=='arrow_right'){
		slideSpeedMultiply = 1;
		slideSpeed = -1*slideSpeedMultiply;
		slideSpeed = Math.max(-1,slideSpeed);		
	}
	else
	{
		slideSpeedMultiply = 1;
		slideSpeed = 1*slideSpeedMultiply;
		slideSpeed = Math.max(1,slideSpeed);				
	}
}
	
function gallerySlide(){
	if(slideSpeed!=0){
		var leftPos = imageGalleryObj.offsetLeft;
		leftPos = leftPos/1 + slideSpeed;
		if(leftPos>maxGalleryXPos){
			slideSpeedMultiply = 1;
			slideSpeed = -1*slideSpeedMultiply;
			slideSpeed = Math.max(-1,slideSpeed);				
			//leftPos = maxGalleryXPos;
			//slideSpeed = 0;
		}
		if(leftPos<=minGalleryXPos){
			slideSpeedMultiply = 1;
			slideSpeed = 1*slideSpeedMultiply;
			slideSpeed = Math.max(1,slideSpeed);
			//leftPos = minGalleryXPos;
			//slideSpeed=0;
		}
		imageGalleryObj.style.left = leftPos + 'px';
	}
	setTimeout('gallerySlide()',20);
}

function initSlideShow(){
	document.getElementById('arrow_left').onmousemove = startSlide;
	document.getElementById('arrow_left').onmouseout = releaseSlide;
	document.getElementById('arrow_right').onmousemove = startSlide;
	document.getElementById('arrow_right').onmouseout = releaseSlide;
	
	imageGalleryObj = document.getElementById('theImages');
	imageGalleryLeftPos = imageGalleryObj.offsetLeft;
	imageGalleryWidth = document.getElementById('galleryContainer').offsetWidth - 80;
	maxGalleryXPos = imageGalleryObj.offsetLeft; 
	minGalleryXPos = imageGalleryWidth - document.getElementById('slideEnd').offsetLeft;
	
	slideSpeedMultiply = 1;
	slideSpeed = -1*slideSpeedMultiply;
	slideSpeed = Math.max(-10,slideSpeed);
	gallerySlide();
}

















var displayWaitMessage2=true;	// Display a please wait message while images are loading?
	
var activeImage2 = false;
var imageGalleryLeftPos2 = false;
var imageGalleryWidth2 = false;
var imageGalleryObj2 = false;
var maxGalleryXPos2 = false;
var slideSpeed2 = 0;
var imageGalleryCaptions2 = new Array();

function startSlide2(e){
	if(document.all)e = event;
	var id = this.id;
	//this.getElementsByTagName('IMG')[0].src = '/images/' + this.id + '_over.gif';	
	if(this.id=='arrow_right2'){
		slideSpeedMultiply2 = 6;
		slideSpeed2 = -1*slideSpeedMultiply2;
		slideSpeed2 = Math.max(-10,slideSpeed2);
	}else{	
		slideSpeedMultiply2 = 6;
		slideSpeed2 = 1*slideSpeedMultiply2;
		slideSpeed2 = Math.min(10,slideSpeed2);
		if(slideSpeed2<0)slideSpeed2=10;
	}
}

function releaseSlide2(){
	var id = this.id;
	slideSpeed2=0;
	if(this.id=='arrow_right2'){
		slideSpeedMultiply2 = 1;
		slideSpeed2 = -1*slideSpeedMultiply2;
		slideSpeed2 = Math.max(-1,slideSpeed2);		
	}else{
		slideSpeedMultiply2 = 1;
		slideSpeed2 = 1*slideSpeedMultiply2;
		slideSpeed2 = Math.max(1,slideSpeed2);				
	}
}
	
function gallerySlide2(){
	if(slideSpeed2!=0){
		var leftPos2 = imageGalleryObj2.offsetLeft;
		leftPos2 = leftPos2/1 + slideSpeed2;
		if(leftPos2>maxGalleryXPos2){
			slideSpeedMultiply2 = 1;
			slideSpeed2 = -1*slideSpeedMultiply2;
			slideSpeed2 = Math.max(-1,slideSpeed2);				
		}
		if(leftPos2<minGalleryXPos2){
			slideSpeedMultiply2 = 1;
			slideSpeed2 = 1*slideSpeedMultiply2;
			slideSpeed2 = Math.max(1,slideSpeed2);
		}
		
		imageGalleryObj2.style.left = leftPos2 + 'px';
	}
	setTimeout('gallerySlide2()',20);
}

function initSlideShow2(){
	document.getElementById('arrow_left2').onmousemove = startSlide2;
	document.getElementById('arrow_left2').onmouseout = releaseSlide2;
	document.getElementById('arrow_right2').onmousemove = startSlide2;
	document.getElementById('arrow_right2').onmouseout = releaseSlide2;
	
	imageGalleryObj2 = document.getElementById('theImages2');
	imageGalleryLeftPos2 = imageGalleryObj2.offsetLeft;
	imageGalleryWidth2 = document.getElementById('galleryContainer2').offsetWidth - 80;
	maxGalleryXPos2 = imageGalleryObj2.offsetLeft; 
	minGalleryXPos2 = imageGalleryWidth2 - document.getElementById('slideEnd2').offsetLeft;
	
	slideSpeedMultiply2 = 1;
	slideSpeed2 = -1*slideSpeedMultiply2;
	slideSpeed2 = Math.max(-10,slideSpeed2);
	gallerySlide2();
}
/*
function showPreview2(imagePath2,imageIndex2,note2){
	var subImages2 = document.getElementById('previewPane2').getElementsByTagName('IMG');
	if(subImages2.length==0){
		var img = document.createElement('IMG');
		document.getElementById('previewPane2').appendChild(img);
	}else img = subImages2[0];
	
	if(displayWaitMessage){
		document.getElementById('waitMessage2').style.display='inline';
	}
	document.getElementById('largeImageCaption2').style.display='none';
	img.onload = function() { hideWaitMessageAndShowCaption2(imageIndex2-1); };
	img.src = imagePath2;
	
	document.getElementById('idMedia2').value=imageIndex2;
	
	if (note2) {
		if (note2<=0.5) document.getElementById('smiley').src="/charte/smileys/smiley3.gif";
		else {
			if (note2<=1) document.getElementById('smiley').src="/charte/smileys/smiley2.gif";
			else document.getElementById('smiley').src="/charte/smileys/smiley1.gif";
		}
	}	
	
}


function hideWaitMessageAndShowCaption(imageIndex){
	document.getElementById('waitMessage').style.display='none';	
	document.getElementById('largeImageCaption').innerHTML = imageGalleryCaptions[imageIndex];
	document.getElementById('largeImageCaption').style.display='block';
}
*/
//window.onload = initSlideShow;