var jssor_slider1;

function loadjssor() {
//     if(!document.getElementById("slider1_container")) return false;
  
    $.getScript("/javascripts/jssor.slider.mini.js",loadsliders);

}

function loadsliders() {

      //SLIDER selection HOMEPAGE ***************************************************************************************/
      $(".sliderwait").hide();
      $("#selection_slide_index").show();
      var windowSize = $(document).width();
      var widthSelection = 1300 ;
      if(windowSize > 751){
        widthSelection = widthSelection *0.98 ;
        var options = {$FillMode:0,$AutoPlay: true,$AutoPlaySteps: 1,$AutoPlayInterval: 4000,$PauseOnHover: 1,$ArrowKeyNavigation: true,$SlideDuration: 160,$MinDragOffsetToSlide: 20,                          
          $SlideWidth: 500,/*$SlideHeight: 300,*/$SlideSpacing: 0,$DisplayPieces:2,$ParkingPosition: 0, $UISearchMode: 1,$PlayOrientation: 1,$DragOrientation: 1,                                
          $ArrowNavigatorOptions: {$Class: $JssorArrowNavigator$,$ChanceToShow: 2,$AutoCenter: 0,$Steps: 1}
          };
        var jssor_slider4 = new $JssorSlider$("selection_slide_index", options);

      }else{
        var options = {$FillMode:5,$AutoPlay: true,$AutoPlaySteps: 1,$AutoPlayInterval: 4000,$PauseOnHover: 1,$ArrowKeyNavigation: true,$SlideDuration: 160,$MinDragOffsetToSlide: 20,                          
          $SlideWidth: widthSelection,/*$SlideHeight: 300,*/$SlideSpacing: 0,$DisplayPieces: 1,$ParkingPosition: 0, $UISearchMode: 1,$PlayOrientation: 1,$DragOrientation: 1,                                
          $ArrowNavigatorOptions: {$Class: $JssorArrowNavigator$,$ChanceToShow: 2,$AutoCenter: 0,$Steps: 1}
          };
        var height = (widthSelection / 1) + 'px';
         if(widthSelection < 344){height = (widthSelection / 1)+40 + 'px';}
         if(widthSelection > 590){height = (widthSelection / 1)-100 + 'px';}
        var jssor_slider4 = new $JssorSlider$("selection_slide_index", options);
        $("#selection_slide_index").css('max-height', height);
        $("#selection_slide_index").children().first().css({'transform': 'none', 'width' :'100%'});   
      }
      jssor_slider4.$ScaleWidth(widthSelection);


            //SLIDER MARQUES HOMEPAGE ??? ariel : je ne sais pas à quel slider cela correspond
      var options = {
          $FillMode: 5,                                       //[Optional] The way to fill image in slide, 0 stretch, 1 contain (keep aspect ratio and put all inside slide), 2 cover (keep aspect ratio and cover whole slide), 4 actual size, 5 contain for large image, actual size for small image, default value is 0
          $AutoPlay: true,                                    //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
          $AutoPlaySteps: 6,                                  //[Optional] Steps to go for each navigation request (this options applys only when slideshow disabled), the default value is 1
          $AutoPlayInterval: 4000,                            //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 3000
          $PauseOnHover: 1,                               //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1

          $ArrowKeyNavigation: true,                    //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
          $SlideDuration: 160,                                //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
          $MinDragOffsetToSlide: 20,                          //[Optional] Minimum drag offset to trigger slide , default value is 20
          $SlideWidth: 150,                                   //[Optional] Width of every slide in pixels, default value is width of 'slides' container
          //$SlideHeight: 150,                                //[Optional] Height of every slide in pixels, default value is height of 'slides' container
          $SlideSpacing: 10,                           //[Optional] Space between each slide in pixels, default value is 0
          $DisplayPieces: 6,                                  //[Optional] Number of pieces to display (the slideshow would be disabled if the value is set to greater than 1), the default value is 1
          $ParkingPosition: 0,                              //[Optional] The offset position to park slide (this options applys only when slideshow disabled), default value is 0.
          $UISearchMode: 1,                                   //[Optional] The way (0 parellel, 1 recursive, default value is 1) to search UI components (slides container, loading screen, navigator container, arrow navigator container, thumbnail navigator container etc).
          $PlayOrientation: 1,                                //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
          $DragOrientation: 1,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
          $ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
              $Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
              $ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
              $AutoCenter: 0,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
              $Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
          }
      };

      if ($('#clients_index').length > 0) {
        var jssor_slider5 = new $JssorSlider$("clients_index", options);
      }
}

function responsive_slider(container,infinite) {
    if(container==undefined) return
    var _this=this;
    this.container=container;
    this.slider_to=null;
    this.sens=1;
    this.waittime=5000;
    this.scrolltime=400;
    this.infinite=infinite;
    this.autoscroll=true;
    
    this.scrollslider=function(sens){
        var offset=container.clientWidth;
        var maxscroll=this.container.scrollWidth-this.container.clientWidth;
        var scrollleft=this.container.scrollLeft;

        if(maxscroll>0){
            _this.leftarrow.show();
            _this.rightarrow.show();
        }else{
            _this.leftarrow.hide();
            _this.rightarrow.hide();
            return;
        }
        
        if(sens==undefined){
            sens=_this.sens;
            if(_this.infinite!=true && sens==1 && scrollleft>=maxscroll){
                _this.sens=-1;
            }
        
            if(_this.infinite!=true && sens==-1 && scrollleft==0)
                _this.sens=1;
        }else{
            if(sens==-1 && _this.infinite){
                var slides=_this.getSlides();
                var moveposition=0;
                for(i=0;i<slides.length;i++){
                    var slides=_this.getSlides();
                    var lastslide=slides[slides.length-1];
                    moveposition+=$(lastslide).outerWidth(true);
                    _this.container.removeChild(lastslide);
                    _this.container.insertBefore(lastslide,slides[0]);
                    if(moveposition>=offset) break;
                }
                _this.container.scrollLeft=offset;
            }
        }
        
        var scrollto="+="+parseInt(sens*offset);
        $(this.container).animate({scrollLeft:scrollto},_this.scrolltime,"swing",function(){
            if(_this.infinite==true && sens==1){// && this.scrollLeft>=maxscroll){
                var slides=_this.getSlides();
                var moveposition=0;
                for(i=0;i<slides.length;i++){
                    moveposition+=$(slides[i]).outerWidth(true);
                    _this.container.removeChild(slides[i]);
                    _this.container.appendChild(slides[i]);
                    if(moveposition>=offset) break;
                }
                this.scrollLeft=0;
            }
        });
        clearInterval(this.slider_to);
    }
    
    this.resize=function(){
        _this.container.scrollLeft=0;
    }

    this.mslide=function(){
        if(_this.container){
            _this.scrollslider();
            _this.slider_to=setTimeout(function(){ _this.mslide(); },_this.waittime);
        }
    }
    
    this.getSlides=function(){
        var ret=[];
        var children=_this.container.childNodes;
        for(x=0;x<children.length;x++){
            if(children[x].nodeName=="DIV")
                ret.push(children[x]);
        }
        return ret;
    }
    
    _this.container.onmouseenter=function(){
        clearInterval(_this.slider_to);
    }
    
    _this.container.onmouseleave=function(){
        if(_this.autoscroll)
            _this.slider_to=setTimeout(function(){ _this.mslide(); },_this.waittime);
    }
    
    $(window).resize(function(){
        _this.resize();
    });
    
    swipedetect(_this.container,function(dir,e){
        _this.autoscroll=false;
        if(dir=="left"){
            _this.scrollslider(1);
            e.preventDefault()
        }
        if(dir=="right"){
            _this.scrollslider(-1);
            e.preventDefault()
        }
    });
    
    
    _this.leftarrow=$(_this.container).find("[data-u=arrowleft]");
    if(_this.leftarrow.length>0){
        _this.leftarrow.click(function(){
            _this.autoscroll=false;
            _this.scrollslider(-1);
        });
    }
    
    _this.rightarrow=$(_this.container).find("[data-u=arrowright]");
    if(_this.rightarrow.length>0){
        _this.rightarrow.click(function(){
            _this.autoscroll=false;
            _this.scrollslider(1);
        });
    }
    
    if(_this.infinite==true){
//         var last=_this.container.getElementsByTagName("div")[0].cloneNode(true);
//         _this.container.appendChild(last);
    }
    
    if(_this.autoscroll){
        setTimeout(function(){
            _this.mslide();
        },_this.waittime);
    }
}

function swipedetect(el, callback){
  
    var touchsurface = el,
    swipedir,
    startX,
    startY,
    distX,
    distY,
    threshold = 100, //required min distance traveled to be considered swipe
    restraint = 100, // maximum distance allowed at the same time in perpendicular direction
    allowedTime = 500, // maximum time allowed to travel that distance
    elapsedTime,
    startTime,
    handleswipe = callback || function(swipedir){}
  
    touchsurface.addEventListener('touchstart', function(e){
        var touchobj = e.changedTouches[0]
        swipedir = 'none'
        dist = 0
        startX = touchobj.pageX
        startY = touchobj.pageY
        startTime = new Date().getTime() // record time when finger first makes contact with surface
//         e.preventDefault()
    }, {passive: true})
  
    touchsurface.addEventListener('touchmove', function(e){
        //e.preventDefault() // prevent scrolling when inside DIV
    }, {passive: true})
  
    touchsurface.addEventListener('touchend', function(e){
        var touchobj = e.changedTouches[0]
        distX = touchobj.pageX - startX // get horizontal dist traveled by finger while in contact with surface
        distY = touchobj.pageY - startY // get vertical dist traveled by finger while in contact with surface
        elapsedTime = new Date().getTime() - startTime // get time elapsed
        if (elapsedTime <= allowedTime){ // first condition for awipe met
            if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint){ // 2nd condition for horizontal swipe met
                swipedir = (distX < 0)? 'left' : 'right' // if dist traveled is negative, it indicates left swipe
            }
            else if (Math.abs(distY) >= threshold && Math.abs(distX) <= restraint){ // 2nd condition for vertical swipe met
                swipedir = (distY < 0)? 'up' : 'down' // if dist traveled is negative, it indicates up swipe
            }
        }
        handleswipe(swipedir,e)
        
    }, false)
}

$( document ).ready(function() {
//     loadjssor();
    var slider1=new responsive_slider(document.querySelector('#slider1_container'),true);
    var slider2=new responsive_slider(document.querySelector('#marques_index'),true);
    var slider3=new responsive_slider(document.querySelector('#selection_slide_index'),true);
});

