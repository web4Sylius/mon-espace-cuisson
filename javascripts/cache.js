/*!
 * Bootstrap v3.3.7 (http://getbootstrap.com)
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under the MIT license
 */
if("undefined"==typeof jQuery)throw new Error("Bootstrap's JavaScript requires jQuery");+function(a){"use strict";var b=a.fn.jquery.split(" ")[0].split(".");if(b[0]<2&&b[1]<9||1==b[0]&&9==b[1]&&b[2]<1||b[0]>3)throw new Error("Bootstrap's JavaScript requires jQuery version 1.9.1 or higher, but lower than version 4")}(jQuery),+function(a){"use strict";function b(){var a=document.createElement("bootstrap"),b={WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd otransitionend",transition:"transitionend"};for(var c in b)if(void 0!==a.style[c])return{end:b[c]};return!1}a.fn.emulateTransitionEnd=function(b){var c=!1,d=this;a(this).one("bsTransitionEnd",function(){c=!0});var e=function(){c||a(d).trigger(a.support.transition.end)};return setTimeout(e,b),this},a(function(){a.support.transition=b(),a.support.transition&&(a.event.special.bsTransitionEnd={bindType:a.support.transition.end,delegateType:a.support.transition.end,handle:function(b){if(a(b.target).is(this))return b.handleObj.handler.apply(this,arguments)}})})}(jQuery),+function(a){"use strict";function b(b){return this.each(function(){var c=a(this),e=c.data("bs.alert");e||c.data("bs.alert",e=new d(this)),"string"==typeof b&&e[b].call(c)})}var c='[data-dismiss="alert"]',d=function(b){a(b).on("click",c,this.close)};d.VERSION="3.3.7",d.TRANSITION_DURATION=150,d.prototype.close=function(b){function c(){g.detach().trigger("closed.bs.alert").remove()}var e=a(this),f=e.attr("data-target");f||(f=e.attr("href"),f=f&&f.replace(/.*(?=#[^\s]*$)/,""));var g=a("#"===f?[]:f);b&&b.preventDefault(),g.length||(g=e.closest(".alert")),g.trigger(b=a.Event("close.bs.alert")),b.isDefaultPrevented()||(g.removeClass("in"),a.support.transition&&g.hasClass("fade")?g.one("bsTransitionEnd",c).emulateTransitionEnd(d.TRANSITION_DURATION):c())};var e=a.fn.alert;a.fn.alert=b,a.fn.alert.Constructor=d,a.fn.alert.noConflict=function(){return a.fn.alert=e,this},a(document).on("click.bs.alert.data-api",c,d.prototype.close)}(jQuery),+function(a){"use strict";function b(b){return this.each(function(){var d=a(this),e=d.data("bs.button"),f="object"==typeof b&&b;e||d.data("bs.button",e=new c(this,f)),"toggle"==b?e.toggle():b&&e.setState(b)})}var c=function(b,d){this.$element=a(b),this.options=a.extend({},c.DEFAULTS,d),this.isLoading=!1};c.VERSION="3.3.7",c.DEFAULTS={loadingText:"loading..."},c.prototype.setState=function(b){var c="disabled",d=this.$element,e=d.is("input")?"val":"html",f=d.data();b+="Text",null==f.resetText&&d.data("resetText",d[e]()),setTimeout(a.proxy(function(){d[e](null==f[b]?this.options[b]:f[b]),"loadingText"==b?(this.isLoading=!0,d.addClass(c).attr(c,c).prop(c,!0)):this.isLoading&&(this.isLoading=!1,d.removeClass(c).removeAttr(c).prop(c,!1))},this),0)},c.prototype.toggle=function(){var a=!0,b=this.$element.closest('[data-toggle="buttons"]');if(b.length){var c=this.$element.find("input");"radio"==c.prop("type")?(c.prop("checked")&&(a=!1),b.find(".active").removeClass("active"),this.$element.addClass("active")):"checkbox"==c.prop("type")&&(c.prop("checked")!==this.$element.hasClass("active")&&(a=!1),this.$element.toggleClass("active")),c.prop("checked",this.$element.hasClass("active")),a&&c.trigger("change")}else this.$element.attr("aria-pressed",!this.$element.hasClass("active")),this.$element.toggleClass("active")};var d=a.fn.button;a.fn.button=b,a.fn.button.Constructor=c,a.fn.button.noConflict=function(){return a.fn.button=d,this},a(document).on("click.bs.button.data-api",'[data-toggle^="button"]',function(c){var d=a(c.target).closest(".btn");b.call(d,"toggle"),a(c.target).is('input[type="radio"], input[type="checkbox"]')||(c.preventDefault(),d.is("input,button")?d.trigger("focus"):d.find("input:visible,button:visible").first().trigger("focus"))}).on("focus.bs.button.data-api blur.bs.button.data-api",'[data-toggle^="button"]',function(b){a(b.target).closest(".btn").toggleClass("focus",/^focus(in)?$/.test(b.type))})}(jQuery),+function(a){"use strict";function b(b){return this.each(function(){var d=a(this),e=d.data("bs.carousel"),f=a.extend({},c.DEFAULTS,d.data(),"object"==typeof b&&b),g="string"==typeof b?b:f.slide;e||d.data("bs.carousel",e=new c(this,f)),"number"==typeof b?e.to(b):g?e[g]():f.interval&&e.pause().cycle()})}var c=function(b,c){this.$element=a(b),this.$indicators=this.$element.find(".carousel-indicators"),this.options=c,this.paused=null,this.sliding=null,this.interval=null,this.$active=null,this.$items=null,this.options.keyboard&&this.$element.on("keydown.bs.carousel",a.proxy(this.keydown,this)),"hover"==this.options.pause&&!("ontouchstart"in document.documentElement)&&this.$element.on("mouseenter.bs.carousel",a.proxy(this.pause,this)).on("mouseleave.bs.carousel",a.proxy(this.cycle,this))};c.VERSION="3.3.7",c.TRANSITION_DURATION=600,c.DEFAULTS={interval:5e3,pause:"hover",wrap:!0,keyboard:!0},c.prototype.keydown=function(a){if(!/input|textarea/i.test(a.target.tagName)){switch(a.which){case 37:this.prev();break;case 39:this.next();break;default:return}a.preventDefault()}},c.prototype.cycle=function(b){return b||(this.paused=!1),this.interval&&clearInterval(this.interval),this.options.interval&&!this.paused&&(this.interval=setInterval(a.proxy(this.next,this),this.options.interval)),this},c.prototype.getItemIndex=function(a){return this.$items=a.parent().children(".item"),this.$items.index(a||this.$active)},c.prototype.getItemForDirection=function(a,b){var c=this.getItemIndex(b),d="prev"==a&&0===c||"next"==a&&c==this.$items.length-1;if(d&&!this.options.wrap)return b;var e="prev"==a?-1:1,f=(c+e)%this.$items.length;return this.$items.eq(f)},c.prototype.to=function(a){var b=this,c=this.getItemIndex(this.$active=this.$element.find(".item.active"));if(!(a>this.$items.length-1||a<0))return this.sliding?this.$element.one("slid.bs.carousel",function(){b.to(a)}):c==a?this.pause().cycle():this.slide(a>c?"next":"prev",this.$items.eq(a))},c.prototype.pause=function(b){return b||(this.paused=!0),this.$element.find(".next, .prev").length&&a.support.transition&&(this.$element.trigger(a.support.transition.end),this.cycle(!0)),this.interval=clearInterval(this.interval),this},c.prototype.next=function(){if(!this.sliding)return this.slide("next")},c.prototype.prev=function(){if(!this.sliding)return this.slide("prev")},c.prototype.slide=function(b,d){var e=this.$element.find(".item.active"),f=d||this.getItemForDirection(b,e),g=this.interval,h="next"==b?"left":"right",i=this;if(f.hasClass("active"))return this.sliding=!1;var j=f[0],k=a.Event("slide.bs.carousel",{relatedTarget:j,direction:h});if(this.$element.trigger(k),!k.isDefaultPrevented()){if(this.sliding=!0,g&&this.pause(),this.$indicators.length){this.$indicators.find(".active").removeClass("active");var l=a(this.$indicators.children()[this.getItemIndex(f)]);l&&l.addClass("active")}var m=a.Event("slid.bs.carousel",{relatedTarget:j,direction:h});return a.support.transition&&this.$element.hasClass("slide")?(f.addClass(b),f[0].offsetWidth,e.addClass(h),f.addClass(h),e.one("bsTransitionEnd",function(){f.removeClass([b,h].join(" ")).addClass("active"),e.removeClass(["active",h].join(" ")),i.sliding=!1,setTimeout(function(){i.$element.trigger(m)},0)}).emulateTransitionEnd(c.TRANSITION_DURATION)):(e.removeClass("active"),f.addClass("active"),this.sliding=!1,this.$element.trigger(m)),g&&this.cycle(),this}};var d=a.fn.carousel;a.fn.carousel=b,a.fn.carousel.Constructor=c,a.fn.carousel.noConflict=function(){return a.fn.carousel=d,this};var e=function(c){var d,e=a(this),f=a(e.attr("data-target")||(d=e.attr("href"))&&d.replace(/.*(?=#[^\s]+$)/,""));if(f.hasClass("carousel")){var g=a.extend({},f.data(),e.data()),h=e.attr("data-slide-to");h&&(g.interval=!1),b.call(f,g),h&&f.data("bs.carousel").to(h),c.preventDefault()}};a(document).on("click.bs.carousel.data-api","[data-slide]",e).on("click.bs.carousel.data-api","[data-slide-to]",e),a(window).on("load",function(){a('[data-ride="carousel"]').each(function(){var c=a(this);b.call(c,c.data())})})}(jQuery),+function(a){"use strict";function b(b){var c,d=b.attr("data-target")||(c=b.attr("href"))&&c.replace(/.*(?=#[^\s]+$)/,"");return a(d)}function c(b){return this.each(function(){var c=a(this),e=c.data("bs.collapse"),f=a.extend({},d.DEFAULTS,c.data(),"object"==typeof b&&b);!e&&f.toggle&&/show|hide/.test(b)&&(f.toggle=!1),e||c.data("bs.collapse",e=new d(this,f)),"string"==typeof b&&e[b]()})}var d=function(b,c){this.$element=a(b),this.options=a.extend({},d.DEFAULTS,c),this.$trigger=a('[data-toggle="collapse"][href="#'+b.id+'"],[data-toggle="collapse"][data-target="#'+b.id+'"]'),this.transitioning=null,this.options.parent?this.$parent=this.getParent():this.addAriaAndCollapsedClass(this.$element,this.$trigger),this.options.toggle&&this.toggle()};d.VERSION="3.3.7",d.TRANSITION_DURATION=350,d.DEFAULTS={toggle:!0},d.prototype.dimension=function(){var a=this.$element.hasClass("width");return a?"width":"height"},d.prototype.show=function(){if(!this.transitioning&&!this.$element.hasClass("in")){var b,e=this.$parent&&this.$parent.children(".panel").children(".in, .collapsing");if(!(e&&e.length&&(b=e.data("bs.collapse"),b&&b.transitioning))){var f=a.Event("show.bs.collapse");if(this.$element.trigger(f),!f.isDefaultPrevented()){e&&e.length&&(c.call(e,"hide"),b||e.data("bs.collapse",null));var g=this.dimension();this.$element.removeClass("collapse").addClass("collapsing")[g](0).attr("aria-expanded",!0),this.$trigger.removeClass("collapsed").attr("aria-expanded",!0),this.transitioning=1;var h=function(){this.$element.removeClass("collapsing").addClass("collapse in")[g](""),this.transitioning=0,this.$element.trigger("shown.bs.collapse")};if(!a.support.transition)return h.call(this);var i=a.camelCase(["scroll",g].join("-"));this.$element.one("bsTransitionEnd",a.proxy(h,this)).emulateTransitionEnd(d.TRANSITION_DURATION)[g](this.$element[0][i])}}}},d.prototype.hide=function(){if(!this.transitioning&&this.$element.hasClass("in")){var b=a.Event("hide.bs.collapse");if(this.$element.trigger(b),!b.isDefaultPrevented()){var c=this.dimension();this.$element[c](this.$element[c]())[0].offsetHeight,this.$element.addClass("collapsing").removeClass("collapse in").attr("aria-expanded",!1),this.$trigger.addClass("collapsed").attr("aria-expanded",!1),this.transitioning=1;var e=function(){this.transitioning=0,this.$element.removeClass("collapsing").addClass("collapse").trigger("hidden.bs.collapse")};return a.support.transition?void this.$element[c](0).one("bsTransitionEnd",a.proxy(e,this)).emulateTransitionEnd(d.TRANSITION_DURATION):e.call(this)}}},d.prototype.toggle=function(){this[this.$element.hasClass("in")?"hide":"show"]()},d.prototype.getParent=function(){return a(this.options.parent).find('[data-toggle="collapse"][data-parent="'+this.options.parent+'"]').each(a.proxy(function(c,d){var e=a(d);this.addAriaAndCollapsedClass(b(e),e)},this)).end()},d.prototype.addAriaAndCollapsedClass=function(a,b){var c=a.hasClass("in");a.attr("aria-expanded",c),b.toggleClass("collapsed",!c).attr("aria-expanded",c)};var e=a.fn.collapse;a.fn.collapse=c,a.fn.collapse.Constructor=d,a.fn.collapse.noConflict=function(){return a.fn.collapse=e,this},a(document).on("click.bs.collapse.data-api",'[data-toggle="collapse"]',function(d){var e=a(this);e.attr("data-target")||d.preventDefault();var f=b(e),g=f.data("bs.collapse"),h=g?"toggle":e.data();c.call(f,h)})}(jQuery),+function(a){"use strict";function b(b){var c=b.attr("data-target");c||(c=b.attr("href"),c=c&&/#[A-Za-z]/.test(c)&&c.replace(/.*(?=#[^\s]*$)/,""));var d=c&&a(c);return d&&d.length?d:b.parent()}function c(c){c&&3===c.which||(a(e).remove(),a(f).each(function(){var d=a(this),e=b(d),f={relatedTarget:this};e.hasClass("open")&&(c&&"click"==c.type&&/input|textarea/i.test(c.target.tagName)&&a.contains(e[0],c.target)||(e.trigger(c=a.Event("hide.bs.dropdown",f)),c.isDefaultPrevented()||(d.attr("aria-expanded","false"),e.removeClass("open").trigger(a.Event("hidden.bs.dropdown",f)))))}))}function d(b){return this.each(function(){var c=a(this),d=c.data("bs.dropdown");d||c.data("bs.dropdown",d=new g(this)),"string"==typeof b&&d[b].call(c)})}var e=".dropdown-backdrop",f='[data-toggle="dropdown"]',g=function(b){a(b).on("click.bs.dropdown",this.toggle)};g.VERSION="3.3.7",g.prototype.toggle=function(d){var e=a(this);if(!e.is(".disabled, :disabled")){var f=b(e),g=f.hasClass("open");if(c(),!g){"ontouchstart"in document.documentElement&&!f.closest(".navbar-nav").length&&a(document.createElement("div")).addClass("dropdown-backdrop").insertAfter(a(this)).on("click",c);var h={relatedTarget:this};if(f.trigger(d=a.Event("show.bs.dropdown",h)),d.isDefaultPrevented())return;e.trigger("focus").attr("aria-expanded","true"),f.toggleClass("open").trigger(a.Event("shown.bs.dropdown",h))}return!1}},g.prototype.keydown=function(c){if(/(38|40|27|32)/.test(c.which)&&!/input|textarea/i.test(c.target.tagName)){var d=a(this);if(c.preventDefault(),c.stopPropagation(),!d.is(".disabled, :disabled")){var e=b(d),g=e.hasClass("open");if(!g&&27!=c.which||g&&27==c.which)return 27==c.which&&e.find(f).trigger("focus"),d.trigger("click");var h=" li:not(.disabled):visible a",i=e.find(".dropdown-menu"+h);if(i.length){var j=i.index(c.target);38==c.which&&j>0&&j--,40==c.which&&j<i.length-1&&j++,~j||(j=0),i.eq(j).trigger("focus")}}}};var h=a.fn.dropdown;a.fn.dropdown=d,a.fn.dropdown.Constructor=g,a.fn.dropdown.noConflict=function(){return a.fn.dropdown=h,this},a(document).on("click.bs.dropdown.data-api",c).on("click.bs.dropdown.data-api",".dropdown form",function(a){a.stopPropagation()}).on("click.bs.dropdown.data-api",f,g.prototype.toggle).on("keydown.bs.dropdown.data-api",f,g.prototype.keydown).on("keydown.bs.dropdown.data-api",".dropdown-menu",g.prototype.keydown)}(jQuery),+function(a){"use strict";function b(b,d){return this.each(function(){var e=a(this),f=e.data("bs.modal"),g=a.extend({},c.DEFAULTS,e.data(),"object"==typeof b&&b);f||e.data("bs.modal",f=new c(this,g)),"string"==typeof b?f[b](d):g.show&&f.show(d)})}var c=function(b,c){this.options=c,this.$body=a(document.body),this.$element=a(b),this.$dialog=this.$element.find(".modal-dialog"),this.$backdrop=null,this.isShown=null,this.originalBodyPad=null,this.scrollbarWidth=0,this.ignoreBackdropClick=!1,this.options.remote&&this.$element.find(".modal-content").load(this.options.remote,a.proxy(function(){this.$element.trigger("loaded.bs.modal")},this))};c.VERSION="3.3.7",c.TRANSITION_DURATION=300,c.BACKDROP_TRANSITION_DURATION=150,c.DEFAULTS={backdrop:!0,keyboard:!0,show:!0},c.prototype.toggle=function(a){return this.isShown?this.hide():this.show(a)},c.prototype.show=function(b){var d=this,e=a.Event("show.bs.modal",{relatedTarget:b});this.$element.trigger(e),this.isShown||e.isDefaultPrevented()||(this.isShown=!0,this.checkScrollbar(),this.setScrollbar(),this.$body.addClass("modal-open"),this.escape(),this.resize(),this.$element.on("click.dismiss.bs.modal",'[data-dismiss="modal"]',a.proxy(this.hide,this)),this.$dialog.on("mousedown.dismiss.bs.modal",function(){d.$element.one("mouseup.dismiss.bs.modal",function(b){a(b.target).is(d.$element)&&(d.ignoreBackdropClick=!0)})}),this.backdrop(function(){var e=a.support.transition&&d.$element.hasClass("fade");d.$element.parent().length||d.$element.appendTo(d.$body),d.$element.show().scrollTop(0),d.adjustDialog(),e&&d.$element[0].offsetWidth,d.$element.addClass("in"),d.enforceFocus();var f=a.Event("shown.bs.modal",{relatedTarget:b});e?d.$dialog.one("bsTransitionEnd",function(){d.$element.trigger("focus").trigger(f)}).emulateTransitionEnd(c.TRANSITION_DURATION):d.$element.trigger("focus").trigger(f)}))},c.prototype.hide=function(b){b&&b.preventDefault(),b=a.Event("hide.bs.modal"),this.$element.trigger(b),this.isShown&&!b.isDefaultPrevented()&&(this.isShown=!1,this.escape(),this.resize(),a(document).off("focusin.bs.modal"),this.$element.removeClass("in").off("click.dismiss.bs.modal").off("mouseup.dismiss.bs.modal"),this.$dialog.off("mousedown.dismiss.bs.modal"),a.support.transition&&this.$element.hasClass("fade")?this.$element.one("bsTransitionEnd",a.proxy(this.hideModal,this)).emulateTransitionEnd(c.TRANSITION_DURATION):this.hideModal())},c.prototype.enforceFocus=function(){a(document).off("focusin.bs.modal").on("focusin.bs.modal",a.proxy(function(a){document===a.target||this.$element[0]===a.target||this.$element.has(a.target).length||this.$element.trigger("focus")},this))},c.prototype.escape=function(){this.isShown&&this.options.keyboard?this.$element.on("keydown.dismiss.bs.modal",a.proxy(function(a){27==a.which&&this.hide()},this)):this.isShown||this.$element.off("keydown.dismiss.bs.modal")},c.prototype.resize=function(){this.isShown?a(window).on("resize.bs.modal",a.proxy(this.handleUpdate,this)):a(window).off("resize.bs.modal")},c.prototype.hideModal=function(){var a=this;this.$element.hide(),this.backdrop(function(){a.$body.removeClass("modal-open"),a.resetAdjustments(),a.resetScrollbar(),a.$element.trigger("hidden.bs.modal")})},c.prototype.removeBackdrop=function(){this.$backdrop&&this.$backdrop.remove(),this.$backdrop=null},c.prototype.backdrop=function(b){var d=this,e=this.$element.hasClass("fade")?"fade":"";if(this.isShown&&this.options.backdrop){var f=a.support.transition&&e;if(this.$backdrop=a(document.createElement("div")).addClass("modal-backdrop "+e).appendTo(this.$body),this.$element.on("click.dismiss.bs.modal",a.proxy(function(a){return this.ignoreBackdropClick?void(this.ignoreBackdropClick=!1):void(a.target===a.currentTarget&&("static"==this.options.backdrop?this.$element[0].focus():this.hide()))},this)),f&&this.$backdrop[0].offsetWidth,this.$backdrop.addClass("in"),!b)return;f?this.$backdrop.one("bsTransitionEnd",b).emulateTransitionEnd(c.BACKDROP_TRANSITION_DURATION):b()}else if(!this.isShown&&this.$backdrop){this.$backdrop.removeClass("in");var g=function(){d.removeBackdrop(),b&&b()};a.support.transition&&this.$element.hasClass("fade")?this.$backdrop.one("bsTransitionEnd",g).emulateTransitionEnd(c.BACKDROP_TRANSITION_DURATION):g()}else b&&b()},c.prototype.handleUpdate=function(){this.adjustDialog()},c.prototype.adjustDialog=function(){var a=this.$element[0].scrollHeight>document.documentElement.clientHeight;this.$element.css({paddingLeft:!this.bodyIsOverflowing&&a?this.scrollbarWidth:"",paddingRight:this.bodyIsOverflowing&&!a?this.scrollbarWidth:""})},c.prototype.resetAdjustments=function(){this.$element.css({paddingLeft:"",paddingRight:""})},c.prototype.checkScrollbar=function(){var a=window.innerWidth;if(!a){var b=document.documentElement.getBoundingClientRect();a=b.right-Math.abs(b.left)}this.bodyIsOverflowing=document.body.clientWidth<a,this.scrollbarWidth=this.measureScrollbar()},c.prototype.setScrollbar=function(){var a=parseInt(this.$body.css("padding-right")||0,10);this.originalBodyPad=document.body.style.paddingRight||"",this.bodyIsOverflowing&&this.$body.css("padding-right",a+this.scrollbarWidth)},c.prototype.resetScrollbar=function(){this.$body.css("padding-right",this.originalBodyPad)},c.prototype.measureScrollbar=function(){var a=document.createElement("div");a.className="modal-scrollbar-measure",this.$body.append(a);var b=a.offsetWidth-a.clientWidth;return this.$body[0].removeChild(a),b};var d=a.fn.modal;a.fn.modal=b,a.fn.modal.Constructor=c,a.fn.modal.noConflict=function(){return a.fn.modal=d,this},a(document).on("click.bs.modal.data-api",'[data-toggle="modal"]',function(c){var d=a(this),e=d.attr("href"),f=a(d.attr("data-target")||e&&e.replace(/.*(?=#[^\s]+$)/,"")),g=f.data("bs.modal")?"toggle":a.extend({remote:!/#/.test(e)&&e},f.data(),d.data());d.is("a")&&c.preventDefault(),f.one("show.bs.modal",function(a){a.isDefaultPrevented()||f.one("hidden.bs.modal",function(){d.is(":visible")&&d.trigger("focus")})}),b.call(f,g,this)})}(jQuery),+function(a){"use strict";function b(b){return this.each(function(){var d=a(this),e=d.data("bs.tooltip"),f="object"==typeof b&&b;!e&&/destroy|hide/.test(b)||(e||d.data("bs.tooltip",e=new c(this,f)),"string"==typeof b&&e[b]())})}var c=function(a,b){this.type=null,this.options=null,this.enabled=null,this.timeout=null,this.hoverState=null,this.$element=null,this.inState=null,this.init("tooltip",a,b)};c.VERSION="3.3.7",c.TRANSITION_DURATION=150,c.DEFAULTS={animation:!0,placement:"top",selector:!1,template:'<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',trigger:"hover focus",title:"",delay:0,html:!1,container:!1,viewport:{selector:"body",padding:0}},c.prototype.init=function(b,c,d){if(this.enabled=!0,this.type=b,this.$element=a(c),this.options=this.getOptions(d),this.$viewport=this.options.viewport&&a(a.isFunction(this.options.viewport)?this.options.viewport.call(this,this.$element):this.options.viewport.selector||this.options.viewport),this.inState={click:!1,hover:!1,focus:!1},this.$element[0]instanceof document.constructor&&!this.options.selector)throw new Error("`selector` option must be specified when initializing "+this.type+" on the window.document object!");for(var e=this.options.trigger.split(" "),f=e.length;f--;){var g=e[f];if("click"==g)this.$element.on("click."+this.type,this.options.selector,a.proxy(this.toggle,this));else if("manual"!=g){var h="hover"==g?"mouseenter":"focusin",i="hover"==g?"mouseleave":"focusout";this.$element.on(h+"."+this.type,this.options.selector,a.proxy(this.enter,this)),this.$element.on(i+"."+this.type,this.options.selector,a.proxy(this.leave,this))}}this.options.selector?this._options=a.extend({},this.options,{trigger:"manual",selector:""}):this.fixTitle()},c.prototype.getDefaults=function(){return c.DEFAULTS},c.prototype.getOptions=function(b){return b=a.extend({},this.getDefaults(),this.$element.data(),b),b.delay&&"number"==typeof b.delay&&(b.delay={show:b.delay,hide:b.delay}),b},c.prototype.getDelegateOptions=function(){var b={},c=this.getDefaults();return this._options&&a.each(this._options,function(a,d){c[a]!=d&&(b[a]=d)}),b},c.prototype.enter=function(b){var c=b instanceof this.constructor?b:a(b.currentTarget).data("bs."+this.type);return c||(c=new this.constructor(b.currentTarget,this.getDelegateOptions()),a(b.currentTarget).data("bs."+this.type,c)),b instanceof a.Event&&(c.inState["focusin"==b.type?"focus":"hover"]=!0),c.tip().hasClass("in")||"in"==c.hoverState?void(c.hoverState="in"):(clearTimeout(c.timeout),c.hoverState="in",c.options.delay&&c.options.delay.show?void(c.timeout=setTimeout(function(){"in"==c.hoverState&&c.show()},c.options.delay.show)):c.show())},c.prototype.isInStateTrue=function(){for(var a in this.inState)if(this.inState[a])return!0;return!1},c.prototype.leave=function(b){var c=b instanceof this.constructor?b:a(b.currentTarget).data("bs."+this.type);if(c||(c=new this.constructor(b.currentTarget,this.getDelegateOptions()),a(b.currentTarget).data("bs."+this.type,c)),b instanceof a.Event&&(c.inState["focusout"==b.type?"focus":"hover"]=!1),!c.isInStateTrue())return clearTimeout(c.timeout),c.hoverState="out",c.options.delay&&c.options.delay.hide?void(c.timeout=setTimeout(function(){"out"==c.hoverState&&c.hide()},c.options.delay.hide)):c.hide()},c.prototype.show=function(){var b=a.Event("show.bs."+this.type);if(this.hasContent()&&this.enabled){this.$element.trigger(b);var d=a.contains(this.$element[0].ownerDocument.documentElement,this.$element[0]);if(b.isDefaultPrevented()||!d)return;var e=this,f=this.tip(),g=this.getUID(this.type);this.setContent(),f.attr("id",g),this.$element.attr("aria-describedby",g),this.options.animation&&f.addClass("fade");var h="function"==typeof this.options.placement?this.options.placement.call(this,f[0],this.$element[0]):this.options.placement,i=/\s?auto?\s?/i,j=i.test(h);j&&(h=h.replace(i,"")||"top"),f.detach().css({top:0,left:0,display:"block"}).addClass(h).data("bs."+this.type,this),this.options.container?f.appendTo(this.options.container):f.insertAfter(this.$element),this.$element.trigger("inserted.bs."+this.type);var k=this.getPosition(),l=f[0].offsetWidth,m=f[0].offsetHeight;if(j){var n=h,o=this.getPosition(this.$viewport);h="bottom"==h&&k.bottom+m>o.bottom?"top":"top"==h&&k.top-m<o.top?"bottom":"right"==h&&k.right+l>o.width?"left":"left"==h&&k.left-l<o.left?"right":h,f.removeClass(n).addClass(h)}var p=this.getCalculatedOffset(h,k,l,m);this.applyPlacement(p,h);var q=function(){var a=e.hoverState;e.$element.trigger("shown.bs."+e.type),e.hoverState=null,"out"==a&&e.leave(e)};a.support.transition&&this.$tip.hasClass("fade")?f.one("bsTransitionEnd",q).emulateTransitionEnd(c.TRANSITION_DURATION):q()}},c.prototype.applyPlacement=function(b,c){var d=this.tip(),e=d[0].offsetWidth,f=d[0].offsetHeight,g=parseInt(d.css("margin-top"),10),h=parseInt(d.css("margin-left"),10);isNaN(g)&&(g=0),isNaN(h)&&(h=0),b.top+=g,b.left+=h,a.offset.setOffset(d[0],a.extend({using:function(a){d.css({top:Math.round(a.top),left:Math.round(a.left)})}},b),0),d.addClass("in");var i=d[0].offsetWidth,j=d[0].offsetHeight;"top"==c&&j!=f&&(b.top=b.top+f-j);var k=this.getViewportAdjustedDelta(c,b,i,j);k.left?b.left+=k.left:b.top+=k.top;var l=/top|bottom/.test(c),m=l?2*k.left-e+i:2*k.top-f+j,n=l?"offsetWidth":"offsetHeight";d.offset(b),this.replaceArrow(m,d[0][n],l)},c.prototype.replaceArrow=function(a,b,c){this.arrow().css(c?"left":"top",50*(1-a/b)+"%").css(c?"top":"left","")},c.prototype.setContent=function(){var a=this.tip(),b=this.getTitle();a.find(".tooltip-inner")[this.options.html?"html":"text"](b),a.removeClass("fade in top bottom left right")},c.prototype.hide=function(b){function d(){"in"!=e.hoverState&&f.detach(),e.$element&&e.$element.removeAttr("aria-describedby").trigger("hidden.bs."+e.type),b&&b()}var e=this,f=a(this.$tip),g=a.Event("hide.bs."+this.type);if(this.$element.trigger(g),!g.isDefaultPrevented())return f.removeClass("in"),a.support.transition&&f.hasClass("fade")?f.one("bsTransitionEnd",d).emulateTransitionEnd(c.TRANSITION_DURATION):d(),this.hoverState=null,this},c.prototype.fixTitle=function(){var a=this.$element;(a.attr("title")||"string"!=typeof a.attr("data-original-title"))&&a.attr("data-original-title",a.attr("title")||"").attr("title","")},c.prototype.hasContent=function(){return this.getTitle()},c.prototype.getPosition=function(b){b=b||this.$element;var c=b[0],d="BODY"==c.tagName,e=c.getBoundingClientRect();null==e.width&&(e=a.extend({},e,{width:e.right-e.left,height:e.bottom-e.top}));var f=window.SVGElement&&c instanceof window.SVGElement,g=d?{top:0,left:0}:f?null:b.offset(),h={scroll:d?document.documentElement.scrollTop||document.body.scrollTop:b.scrollTop()},i=d?{width:a(window).width(),height:a(window).height()}:null;return a.extend({},e,h,i,g)},c.prototype.getCalculatedOffset=function(a,b,c,d){return"bottom"==a?{top:b.top+b.height,left:b.left+b.width/2-c/2}:"top"==a?{top:b.top-d,left:b.left+b.width/2-c/2}:"left"==a?{top:b.top+b.height/2-d/2,left:b.left-c}:{top:b.top+b.height/2-d/2,left:b.left+b.width}},c.prototype.getViewportAdjustedDelta=function(a,b,c,d){var e={top:0,left:0};if(!this.$viewport)return e;var f=this.options.viewport&&this.options.viewport.padding||0,g=this.getPosition(this.$viewport);if(/right|left/.test(a)){var h=b.top-f-g.scroll,i=b.top+f-g.scroll+d;h<g.top?e.top=g.top-h:i>g.top+g.height&&(e.top=g.top+g.height-i)}else{var j=b.left-f,k=b.left+f+c;j<g.left?e.left=g.left-j:k>g.right&&(e.left=g.left+g.width-k)}return e},c.prototype.getTitle=function(){var a,b=this.$element,c=this.options;return a=b.attr("data-original-title")||("function"==typeof c.title?c.title.call(b[0]):c.title)},c.prototype.getUID=function(a){do a+=~~(1e6*Math.random());while(document.getElementById(a));return a},c.prototype.tip=function(){if(!this.$tip&&(this.$tip=a(this.options.template),1!=this.$tip.length))throw new Error(this.type+" `template` option must consist of exactly 1 top-level element!");return this.$tip},c.prototype.arrow=function(){return this.$arrow=this.$arrow||this.tip().find(".tooltip-arrow")},c.prototype.enable=function(){this.enabled=!0},c.prototype.disable=function(){this.enabled=!1},c.prototype.toggleEnabled=function(){this.enabled=!this.enabled},c.prototype.toggle=function(b){var c=this;b&&(c=a(b.currentTarget).data("bs."+this.type),c||(c=new this.constructor(b.currentTarget,this.getDelegateOptions()),a(b.currentTarget).data("bs."+this.type,c))),b?(c.inState.click=!c.inState.click,c.isInStateTrue()?c.enter(c):c.leave(c)):c.tip().hasClass("in")?c.leave(c):c.enter(c)},c.prototype.destroy=function(){var a=this;clearTimeout(this.timeout),this.hide(function(){a.$element.off("."+a.type).removeData("bs."+a.type),a.$tip&&a.$tip.detach(),a.$tip=null,a.$arrow=null,a.$viewport=null,a.$element=null})};var d=a.fn.tooltip;a.fn.tooltip=b,a.fn.tooltip.Constructor=c,a.fn.tooltip.noConflict=function(){return a.fn.tooltip=d,this}}(jQuery),+function(a){"use strict";function b(b){return this.each(function(){var d=a(this),e=d.data("bs.popover"),f="object"==typeof b&&b;!e&&/destroy|hide/.test(b)||(e||d.data("bs.popover",e=new c(this,f)),"string"==typeof b&&e[b]())})}var c=function(a,b){this.init("popover",a,b)};if(!a.fn.tooltip)throw new Error("Popover requires tooltip.js");c.VERSION="3.3.7",c.DEFAULTS=a.extend({},a.fn.tooltip.Constructor.DEFAULTS,{placement:"right",trigger:"click",content:"",template:'<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'}),c.prototype=a.extend({},a.fn.tooltip.Constructor.prototype),c.prototype.constructor=c,c.prototype.getDefaults=function(){return c.DEFAULTS},c.prototype.setContent=function(){var a=this.tip(),b=this.getTitle(),c=this.getContent();a.find(".popover-title")[this.options.html?"html":"text"](b),a.find(".popover-content").children().detach().end()[this.options.html?"string"==typeof c?"html":"append":"text"](c),a.removeClass("fade top bottom left right in"),a.find(".popover-title").html()||a.find(".popover-title").hide()},c.prototype.hasContent=function(){return this.getTitle()||this.getContent()},c.prototype.getContent=function(){var a=this.$element,b=this.options;return a.attr("data-content")||("function"==typeof b.content?b.content.call(a[0]):b.content)},c.prototype.arrow=function(){return this.$arrow=this.$arrow||this.tip().find(".arrow")};var d=a.fn.popover;a.fn.popover=b,a.fn.popover.Constructor=c,a.fn.popover.noConflict=function(){return a.fn.popover=d,this}}(jQuery),+function(a){"use strict";function b(c,d){this.$body=a(document.body),this.$scrollElement=a(a(c).is(document.body)?window:c),this.options=a.extend({},b.DEFAULTS,d),this.selector=(this.options.target||"")+" .nav li > a",this.offsets=[],this.targets=[],this.activeTarget=null,this.scrollHeight=0,this.$scrollElement.on("scroll.bs.scrollspy",a.proxy(this.process,this)),this.refresh(),this.process()}function c(c){return this.each(function(){var d=a(this),e=d.data("bs.scrollspy"),f="object"==typeof c&&c;e||d.data("bs.scrollspy",e=new b(this,f)),"string"==typeof c&&e[c]()})}b.VERSION="3.3.7",b.DEFAULTS={offset:10},b.prototype.getScrollHeight=function(){return this.$scrollElement[0].scrollHeight||Math.max(this.$body[0].scrollHeight,document.documentElement.scrollHeight)},b.prototype.refresh=function(){var b=this,c="offset",d=0;this.offsets=[],this.targets=[],this.scrollHeight=this.getScrollHeight(),a.isWindow(this.$scrollElement[0])||(c="position",d=this.$scrollElement.scrollTop()),this.$body.find(this.selector).map(function(){var b=a(this),e=b.data("target")||b.attr("href"),f=/^#./.test(e)&&a(e);return f&&f.length&&f.is(":visible")&&[[f[c]().top+d,e]]||null}).sort(function(a,b){return a[0]-b[0]}).each(function(){b.offsets.push(this[0]),b.targets.push(this[1])})},b.prototype.process=function(){var a,b=this.$scrollElement.scrollTop()+this.options.offset,c=this.getScrollHeight(),d=this.options.offset+c-this.$scrollElement.height(),e=this.offsets,f=this.targets,g=this.activeTarget;if(this.scrollHeight!=c&&this.refresh(),b>=d)return g!=(a=f[f.length-1])&&this.activate(a);if(g&&b<e[0])return this.activeTarget=null,this.clear();for(a=e.length;a--;)g!=f[a]&&b>=e[a]&&(void 0===e[a+1]||b<e[a+1])&&this.activate(f[a])},b.prototype.activate=function(b){
this.activeTarget=b,this.clear();var c=this.selector+'[data-target="'+b+'"],'+this.selector+'[href="'+b+'"]',d=a(c).parents("li").addClass("active");d.parent(".dropdown-menu").length&&(d=d.closest("li.dropdown").addClass("active")),d.trigger("activate.bs.scrollspy")},b.prototype.clear=function(){a(this.selector).parentsUntil(this.options.target,".active").removeClass("active")};var d=a.fn.scrollspy;a.fn.scrollspy=c,a.fn.scrollspy.Constructor=b,a.fn.scrollspy.noConflict=function(){return a.fn.scrollspy=d,this},a(window).on("load.bs.scrollspy.data-api",function(){a('[data-spy="scroll"]').each(function(){var b=a(this);c.call(b,b.data())})})}(jQuery),+function(a){"use strict";function b(b){return this.each(function(){var d=a(this),e=d.data("bs.tab");e||d.data("bs.tab",e=new c(this)),"string"==typeof b&&e[b]()})}var c=function(b){this.element=a(b)};c.VERSION="3.3.7",c.TRANSITION_DURATION=150,c.prototype.show=function(){var b=this.element,c=b.closest("ul:not(.dropdown-menu)"),d=b.data("target");if(d||(d=b.attr("href"),d=d&&d.replace(/.*(?=#[^\s]*$)/,"")),!b.parent("li").hasClass("active")){var e=c.find(".active:last a"),f=a.Event("hide.bs.tab",{relatedTarget:b[0]}),g=a.Event("show.bs.tab",{relatedTarget:e[0]});if(e.trigger(f),b.trigger(g),!g.isDefaultPrevented()&&!f.isDefaultPrevented()){var h=a(d);this.activate(b.closest("li"),c),this.activate(h,h.parent(),function(){e.trigger({type:"hidden.bs.tab",relatedTarget:b[0]}),b.trigger({type:"shown.bs.tab",relatedTarget:e[0]})})}}},c.prototype.activate=function(b,d,e){function f(){g.removeClass("active").find("> .dropdown-menu > .active").removeClass("active").end().find('[data-toggle="tab"]').attr("aria-expanded",!1),b.addClass("active").find('[data-toggle="tab"]').attr("aria-expanded",!0),h?(b[0].offsetWidth,b.addClass("in")):b.removeClass("fade"),b.parent(".dropdown-menu").length&&b.closest("li.dropdown").addClass("active").end().find('[data-toggle="tab"]').attr("aria-expanded",!0),e&&e()}var g=d.find("> .active"),h=e&&a.support.transition&&(g.length&&g.hasClass("fade")||!!d.find("> .fade").length);g.length&&h?g.one("bsTransitionEnd",f).emulateTransitionEnd(c.TRANSITION_DURATION):f(),g.removeClass("in")};var d=a.fn.tab;a.fn.tab=b,a.fn.tab.Constructor=c,a.fn.tab.noConflict=function(){return a.fn.tab=d,this};var e=function(c){c.preventDefault(),b.call(a(this),"show")};a(document).on("click.bs.tab.data-api",'[data-toggle="tab"]',e).on("click.bs.tab.data-api",'[data-toggle="pill"]',e)}(jQuery),+function(a){"use strict";function b(b){return this.each(function(){var d=a(this),e=d.data("bs.affix"),f="object"==typeof b&&b;e||d.data("bs.affix",e=new c(this,f)),"string"==typeof b&&e[b]()})}var c=function(b,d){this.options=a.extend({},c.DEFAULTS,d),this.$target=a(this.options.target).on("scroll.bs.affix.data-api",a.proxy(this.checkPosition,this)).on("click.bs.affix.data-api",a.proxy(this.checkPositionWithEventLoop,this)),this.$element=a(b),this.affixed=null,this.unpin=null,this.pinnedOffset=null,this.checkPosition()};c.VERSION="3.3.7",c.RESET="affix affix-top affix-bottom",c.DEFAULTS={offset:0,target:window},c.prototype.getState=function(a,b,c,d){var e=this.$target.scrollTop(),f=this.$element.offset(),g=this.$target.height();if(null!=c&&"top"==this.affixed)return e<c&&"top";if("bottom"==this.affixed)return null!=c?!(e+this.unpin<=f.top)&&"bottom":!(e+g<=a-d)&&"bottom";var h=null==this.affixed,i=h?e:f.top,j=h?g:b;return null!=c&&e<=c?"top":null!=d&&i+j>=a-d&&"bottom"},c.prototype.getPinnedOffset=function(){if(this.pinnedOffset)return this.pinnedOffset;this.$element.removeClass(c.RESET).addClass("affix");var a=this.$target.scrollTop(),b=this.$element.offset();return this.pinnedOffset=b.top-a},c.prototype.checkPositionWithEventLoop=function(){setTimeout(a.proxy(this.checkPosition,this),1)},c.prototype.checkPosition=function(){if(this.$element.is(":visible")){var b=this.$element.height(),d=this.options.offset,e=d.top,f=d.bottom,g=Math.max(a(document).height(),a(document.body).height());"object"!=typeof d&&(f=e=d),"function"==typeof e&&(e=d.top(this.$element)),"function"==typeof f&&(f=d.bottom(this.$element));var h=this.getState(g,b,e,f);if(this.affixed!=h){null!=this.unpin&&this.$element.css("top","");var i="affix"+(h?"-"+h:""),j=a.Event(i+".bs.affix");if(this.$element.trigger(j),j.isDefaultPrevented())return;this.affixed=h,this.unpin="bottom"==h?this.getPinnedOffset():null,this.$element.removeClass(c.RESET).addClass(i).trigger(i.replace("affix","affixed")+".bs.affix")}"bottom"==h&&this.$element.offset({top:g-b-f})}};var d=a.fn.affix;a.fn.affix=b,a.fn.affix.Constructor=c,a.fn.affix.noConflict=function(){return a.fn.affix=d,this},a(window).on("load",function(){a('[data-spy="affix"]').each(function(){var c=a(this),d=c.data();d.offset=d.offset||{},null!=d.offsetBottom&&(d.offset.bottom=d.offsetBottom),null!=d.offsetTop&&(d.offset.top=d.offsetTop),b.call(c,d)})})}(jQuery);


var bloc_fermeture_option = true;
var showtimer=false;
var mobile=false;

var hparams=hashparams();
if(hparams!==false)
	if(hparams['utm_source']=="mobile") setCookie("mobile","0",365);

var mobile_url=mobile_alternate();
if(isMobile() && mobile_url!=false && getCookie("mobile")==="")
	document.location=mobile_url;

// ===== Scroll to Top ==== 
$(window).scroll(function() {
    if ($(this).scrollTop() >= 50) {        // If page is scrolled more than 50px
        $('#return-to-top').fadeIn(200);    // Fade in the arrow
    } else {
        $('#return-to-top').fadeOut(200);   // Else fade out the arrow
    }
});
$(window).scroll(function() {
    if ($(this).scrollTop() >= 300 && $(this).scrollTop() <= 1650) {        // If page is scrolled more than 50px
        $('#articlesimu').fadeIn(200);    // Fade in the arrow
    } else {
        $('#articlesimu').fadeOut(200);   // Else fade out the arrow
    }
});
$('#return-to-top').click(function() {      // When arrow is clicked
	$('body,html').animate({
        scrollTop : 0                       // Scroll to top of body
    }, 500);
});



function typemodal(){ // modal
	$( ".overlay" ).css('display', "block");
	$( ".modal" ).css({"opacity":"0", "display":"block"}).show("slow").animate({opacity:1});
	window.scrollTo(0,500); 
}

/*$( "#btn-back-err" ).click(function() {
  $( ".overlay" ).hide();
  $( ".modal" ).hide();
}); //fin du modal*/

function valid_devis(type,facturid){
	$.ajax({
		type : "GET",
		url : "/ajax/back_devis_ajax.php",
		dataType : "text",
		data : {
			facturid : facturid,
			type : type
		}
	}).done(function(){
		$("#devis_step1").hide();
		$("#devis_step2").show();
	});
}

function overlayDisplay(){
	if($('#barrecookies').css('display') === 'none') {
		$('.modaldisplay').hide();
		$('.overlay').hide();
	}
	else {
		$('.overlay').show();
	}
}

function barrecookieShow(){
	$("#barrecookies").show();
}

$(window).resize(function(){
	var iWindowsSize = $(document).width();
	//Rollover sur vignettes de la page d'accueil
	$('.index_vignette').unbind();
	if(iWindowsSize > 1182){
		$('.index_vignette').mouseenter(function(){
			$(this).find('.index_hover').show();
			$(this).find('.img_ambiance').hide();
		}).mouseleave(function(){
			$(this).find('.index_hover').hide();
			$(this).find('.img_ambiance').show();
		});
	}else{
		$(this).find('.index_hover').hide();
		$(this).find('.img_ambiance').show();
	}
	
	
	$('.vignette').unbind();
	$('.encart_option').unbind();
	if(iWindowsSize > 974 ){
        //Rollover sur vignette de la page catégorie
        $('.vignette').mouseenter(function(){
        	$(this).find('img.visuel').css('display','none');
        	$(this).find('.categorie_hover').show();
        	$(this).find('.vfimg').hide();
        }).mouseleave(function(){
        	$(this).find('img.visuel').css('display','block');
        	$(this).find('.categorie_hover').hide();
        	$(this).find('.vfimg').show();
        });

		//survol des options avec zoom d'un article
		$('.encart_option').mousemove(function(event){
			var objDivZoom = $(this).next('div.visuel_zoom');
			var Hzoom = objDivZoom.css('height');
			if(Hzoom){
				Hzoom = Hzoom.substr(0, Hzoom.length - 2);
				var middleWindow = $(window).height() / 2;
	               //if(event.screenY <= middleWindow){
	               	var positionTop = parseInt(event.pageY) ;
	              // } else {
	                 //   var positionTop = parseInt(event.pageY) - parseInt(Hzoom) ;
	               //}

	               objDivZoom.css({
	               	'left' : event.pageX *0.5,
	               	'top' : 'unset'
	               });
	               objDivZoom.show();
	           }
	       }).mouseleave(function(event){
	       	if($(this).next('div.visuel_zoom')){
	       		$(this).next('div.visuel_zoom').hide();
	       	}
	       });

	   }else{
	   	$(this).find('img.visuel').css('display','block');
	   	$(this).find('.categorie_hover').hide();
	   	$(this).find('.vfimg').show();
	   }
	});

$( document ).ready(function() {
	var iWindowsHeight = $(window).height();
	var iWindowsWidth = $(window).width();
	vignscrollSize();
	if(getCookie('cookieconsent') == '' && getCookie('cookieinfo') == '') {
		setTimeout(barrecookieShow,5000);
		setTimeout(overlayDisplay,5000);
	}
	updateConsent();
	//gestion du gclid
	$('#gclid1').val($('#gclid_field').val());
	$('#gclidc').val($('#gclid_field').val()); //gclid page contacter.html

	$('#ddown').on('click', function(){
		var elm = $('#ipid');
		var totalHeight = $('#ipid')[0].scrollHeight;
		var height = elm.height();
		var toScroll = height / 2 ;
		elm.scrollTop(elm.scrollTop() + toScroll);
	});
	$('#dup').on('click', function(){
		var elm = $('#ipid');
		var totalHeight = $('#ipid')[0].scrollHeight;
		var height = elm.height();
		var toScroll = height / 2 ;
		elm.scrollTop(elm.scrollTop() - toScroll);
	});

	function affichageFirst(){
		var idLigne = $('#affichage_first').val();
		$('#acces_panier_title_'+idLigne).show();
		$('#scroll_accessoires_'+idLigne).show();
		$('#scrollbtnsparent_'+idLigne).show();
		$('#text_vignscroll_'+idLigne).css('display','table');
		$('#chev_left_'+idLigne).removeClass('fa-chevron-down').addClass('fa-chevron-up');
		$('#chev_right_'+idLigne).removeClass('fa-chevron-down').addClass('fa-chevron-up');
	}
	if($('#affichage_first')) affichageFirst($('#affichage_first').val());

	$('#devisrap').click(function(){
		var artid = $('#devisrapartid').val();
		var gclid = $('#gclid_field').val();
		$.ajax({      
			type : 'GET',
			url : '/devisrapide.php',
			dataType : 'text',
			data : { artid : artid, gclid: gclid },
			success: function(result){ 
				$("#test-popup").html(result);
			}
		});
	});


	//si le navigateur est internet explorer, resserer le menu en format tablette
	var isIE = /*@cc_on!@*/false || !!document.documentMode;
	if(isIE) {
	$( window ).resize(function() { // si c'est une redimension manuelle de la fenêtre

		if($(window).width() < 1591 && $(window).width() > 1199) {
			$(".menu .niveau1").css("margin-left", "-10px");
		}
		if($(window).width() < 1199 && $(window).width() > 991) {
			$(".menu .niveau1 a").css("font-size", "12px");
		}
		if($(window).width() < 991){
			$(".menu .niveau1 a").css("font-size", "10px");
			$(".menu .niveau1").css("margin-left", "0px");
		}

	});
	if($(window).width() < 1591 && $(window).width() > 991) {
		$(".menu .niveau1").css("margin-left", "-10px");
	}
	if($(window).width() < 1200 && $(window).width() > 991) {
		$(".menu .niveau1 a").css("font-size", "12px");
	}
	if ($(window).width() < 991){
		$(".menu .niveau1 a").css("font-size", "10px");
		$(".menu .niveau1").css("margin-left", "0px");
	}
}

/*bouton Accessoire mobile*/
$('#access-btn-xs').click(function(){
	if($('#access-btn-xs').hasClass('check')){
		$('#access-btn-xs').removeClass('check');
		$('#access_index_xs').hide();
	}else{
		$('#access-btn-xs').addClass('check');			
		$('#access_index_xs').show();
	}
});

/* menu responsive */
$('#menu_resp_close').click(function(){
		//$('#menu_resp').hide();
		$('#menu_resp_b').show();
		$('#site_pusher').toggleClass('push');
		$('#menu_resp').toggleClass('width-menu');
		$('.container_site').toggleClass('ovhidden');
	});
$('#menu_resp_b').click(function(){
		//$('#menu_resp').show();
		$('#menu_resp_b').hide();
		$('#site_pusher').toggleClass('push');
		$('#menu_resp').toggleClass('width-menu');
		$('.container_site').toggleClass('ovhidden');
	});

$('.container_menu_2').click(function(){
	$(this).find('.arrow-img').toggleClass('arrow-img-rotate');
})



  	//creation d'un timer pour l'apparition de l'overlay
  	var myTimer = new Date();
  	myTimer = myTimer.getTime();

    //Gestion rollover sur bouton panier
    $('#btn_panier').mouseover(function(){

    	$('.nb_articles_panier').css('background-color', '#a88d5c');

    }).mouseout(function(){

    	$('.nb_articles_panier').css('background-color', 'black');

    });

    $('.btn-menu').click(function(){
    	if($('.menu').hasClass("visible"))
    		$('.menu').removeClass("visible");
    	else
    		$('.menu').addClass("visible");
    });

    $('.niveau1-1').click(function(){
    	var id = $(this).attr('id');
    	var num = id.substring(id.lastIndexOf('-') + 1, id.length);
    	if(num>=3){
    		$('#menu_resp').toggleClass('move-up');
    	}	

    });

	/*$('.niveau1-1').click(function(){
		var idName = 'sous-menu-2';
		var hgt = 0;
		var marginhgt = 0;
		var id = $(this).attr('id');
		var num = id.substring(id.lastIndexOf('-') + 1, id.length);
		if(num>=4)
		$('#menu_resp').animate({top: -102 });

});*/


////// permet de cacher les souslibelle du menu, si la taille de la fenêtre est réduite
	$( window ).resize(function() { // si c'est une redimension manuelle de la fenêtre
		for(i=1; i<11; i++) {

			if($(window).width() < 1199 && $('#sous-menu-'+i).find('.souslibelle').css('display') === 'block') {
				$('#sous-menu-'+i).find('.souslibelle').css('display', 'none');
			}
			else if($(window).width() >= 1199 && $('#sous-menu-'+i).find('.souslibelle').css('display') === 'none') {
				$('#sous-menu-'+i).find('.souslibelle').css('display', 'block');
			}
		}
	});
	for(i=1; i<11; i++) {

		if($(window).width() < 1199 && $('#sous-menu-'+i).find('.souslibelle').css('display') === 'block') {
			$('#sous-menu-'+i).find('.souslibelle').css('display', 'none');
		}
		else if($(window).width() >= 1199 && $('#sous-menu-'+i).find('.souslibelle').css('display') === 'none') {
			$('#sous-menu-'+i).find('.souslibelle').css('display', 'block');
		}
	}

	$('.fond-sombre-flou').hide();
	//Gestion rollover sur boutons menu niveau 1
	$('.menu .niveau1').mouseenter(function(){

		$('.barrefixed').css('position', 'relative');

//si le navigateur est internet explorer, mettre une image pour faire le fond transparent
var isIE = /*@cc_on!@*/false || !!document.documentMode;
if(isIE) {
	$('.fond-sombre-flou').css('background-color', 'transparent');
	$('.fond-sombre-flou').css('background-image', 'url("/images/templates/fond-transparent.png")');
	$('.fond-sombre-flou').css('opacity', '0.5');
}
$('.container.container-main').css('-webkit-filter', 'blur(1px)');
$('.fond_footer1').css('-webkit-filter', 'blur(1px)');
$('.container.container-main').css('-ms-filter', 'blur(1px)');
$('.fond-sombre-flou').show();

for(i=1; i<11; i++) {

	$('#sous-menu-'+i).find('.hide-bloc').remove();
	$('#sous-menu-'+i).css('display', 'none');
}

var nbniv2 = $('#sous-menu-1').find('.niveau2').length;
$('.souscat').prev('.souslibelle').css('margin-bottom', '8px');





		if(typeof timer != 'undefined') clearTimeout(timer);

		$('.menu .niveau1').css('background-color', 'white');
		$(this).css('background-color', '#DCD1BE');

		var urlpro = window.location.href;

		if (urlpro.indexOf('pro.') != -1) {
			$(this).css('background-color', '#ADC0CE');
			$(this).find('a').css('color', 'black !important');
		}

		//Activation du bouton survolé
		toggleButtonMenu(this, true);

		//Masquage des sous-menu
		hideClass('sous-menu');
		hideClass('sous-sous-menu');

		if($(this).attr('id') == 'menu-promo') {
			$(this).find('a').css('color', 'white');
		}


		//Affichage du sous menu demandé
		showMenu(this, 'sous-menu');
		showMenu(this, 'sous-menu-bloc');
	}).mouseleave(function(){

		var isIE = /*@cc_on!@*/false || !!document.documentMode;
		if(isIE) {
			$('.menu .niveau1').css('background-color', 'white');
			$('.fond-sombre-flou').hide();
		}
		else {
			var nbhover = 0;

			for(i=1; i<11; i++) {

				if ($('#sous-menu-'+i+':hover').length === 0) {
					nbhover += 0;
				}
				else {
					nbhover += 1;
				}
			}
			if(nbhover === 0) {
				if ($('.nav.navbar-nav.blocks-justify:hover').length === 0) {
					$('.container.container-main').css('-webkit-filter', 'none');
					$('.fond_footer1').css('-webkit-filter', 'none');
					$('.fond-sombre-flou').hide();
				}
				$('.menu .niveau1').css('background-color', 'white');
				for(i=1; i<11; i++) {
					$('#sous-menu-'+i).find('.hide-bloc').remove();
					$('#sous-menu-'+i).css('display', 'none');
					$('.container.container-main').css('-webkit-filter', 'none');
					$('.fond_footer1').css('-webkit-filter', 'none');
					$('.fond-sombre-flou').hide();
					$('.barrefixed').css('position', 'fixed');
				}
			}
		}

		$(this).find('a').css('color', 'black');
		
		timer = setTimeout(function(){
			toggleButtonMenu(this, false);
			hideClass('sous-menu');
			hideClass('sous-sous-menu');
		},300);
	}).click(function(){

		//Clique sur la div entraine clic sur le lien de la balise <a>
		$(this).find('a').get(0).click();

	});
	
	/*$('.niveau1').mouseleave(function(){
		timer = setTimeout(function(){
		  toggleButtonMenu(this, false);
		  hideClass('sous-menu');
		},100);
	});*/

	$('#sous-menu-1').mouseleave(function(){
		$('.container.container-main').css('-webkit-filter', 'none');
		$('.fond_footer1').css('-webkit-filter', 'none');
		$('.fond-sombre-flou').hide();
		$('#sous-menu-1').find('.hide-bloc').remove();
		$('#sous-menu-1').css('display', 'none');
		$('.menu .niveau1').css('background-color', 'white');
		$('.barrefixed').css('position', 'fixed');
	});
	$('#sous-menu-2').mouseleave(function(){
		$('.container.container-main').css('-webkit-filter', 'none');
		$('.fond_footer1').css('-webkit-filter', 'none');
		$('.fond-sombre-flou').hide();
		$('#sous-menu-2').find('.hide-bloc').remove();
		$('#sous-menu-2').css('display', 'none');
		$('.menu .niveau1').css('background-color', 'white');
		$('.barrefixed').css('position', 'fixed');
	});
	$('#sous-menu-3').mouseleave(function(){
		$('.container.container-main').css('-webkit-filter', 'none');
		$('.fond_footer1').css('-webkit-filter', 'none');
		$('.fond-sombre-flou').hide();
		$('#sous-menu-3').css('display', 'none');
		$('.menu .niveau1').css('background-color', 'white');
		$('.barrefixed').css('position', 'fixed');
	});
	$('#sous-menu-4').mouseleave(function(){
		$('.container.container-main').css('-webkit-filter', 'none');
		$('.fond_footer1').css('-webkit-filter', 'none');
		$('.fond-sombre-flou').hide();
		$('#sous-menu-4').find('.hide-bloc').remove();
		$('#sous-menu-4').css('display', 'none');
		$('.menu .niveau1').css('background-color', 'white');
		$('.barrefixed').css('position', 'fixed');
	});
	$('#sous-menu-5').mouseleave(function(){
		$('.container.container-main').css('-webkit-filter', 'none');
		$('.fond_footer1').css('-webkit-filter', 'none');
		$('.fond-sombre-flou').hide();
		$('#sous-menu-5').find('.hide-bloc').remove();
		$('#sous-menu-5').css('display', 'none');
		$('.menu .niveau1').css('background-color', 'white');
		$('.barrefixed').css('position', 'fixed');
	});
	$('#sous-menu-6').mouseleave(function(){
		$('.container.container-main').css('-webkit-filter', 'none');
		$('.fond_footer1').css('-webkit-filter', 'none');
		$('.fond-sombre-flou').hide();
		$('#sous-menu-6').find('.hide-bloc').remove();
		$('#sous-menu-6').css('display', 'none');
		$('.menu .niveau1').css('background-color', 'white');	
		$('.barrefixed').css('position', 'fixed');
	});
	$('#sous-menu-7').mouseleave(function(){
		$('.container.container-main').css('-webkit-filter', 'none');
		$('.fond_footer1').css('-webkit-filter', 'none');
		$('.fond-sombre-flou').hide();
		$('#sous-menu-7').css('display', 'none');
		$('.menu .niveau1').css('background-color', 'white');
		$('.barrefixed').css('position', 'fixed');
	});
	$('#sous-menu-8').mouseleave(function(){
		$('.container.container-main').css('-webkit-filter', 'none');
		$('.fond_footer1').css('-webkit-filter', 'none');
		$('.fond-sombre-flou').hide();
		$('#sous-menu-8').find('.hide-bloc').remove();
		$('#sous-menu-8').css('display', 'none');
		$('.menu .niveau1').css('background-color', 'white');
		$('.barrefixed').css('position', 'fixed');
	});

	//Gestion de la fermeture du menu
	$('.menu').mouseenter(function(){

		if(typeof timer != 'undefined') clearTimeout(timer);

	});
	
	$('.sous-sous-menu').mouseenter(function(){
		if(typeof timer != 'undefined') clearTimeout(timer);
	});
	
	$('.sous-sous-menu').mouseleave(function(){

		//Timer pour fermer le sous-menu, au cas ou il faudrait un dÃ©lai (0 = fermeture instantanÃ©e)
		timer = setTimeout(function(){
			$('.menu').removeClass("visible");
			hideClass('sous-menu');
			hideClass('sous-sous-menu');
			toggleButtonMenu(this, false);
			toggleButtonSousMenu(this, false);
		}, 100);

	});
	
	/*$('.niveau1').mouseleave(function(){
		timer = setTimeout(function(){
		  toggleButtonMenu(this, false);
		  hideClass('sous-menu');
		},100);
	});*/

	

	$('.sous-menu').mouseenter(function(){
		if(typeof timer != 'undefined') clearTimeout(timer);
	});
	
	//Gestion rollover sur boutons menu niveau 2
	$('.niveau2').mouseenter(function(){
		if(typeof timer != 'undefined') clearTimeout(timer);
		//Activation du bouton du sous-menu
		toggleButtonSousMenu(this, true);

	}).click(function(){

		//Clique sur la div entraine clic sur le lien de la balise <a>
		$(this).find('a').get(0).click();

	});
	
	//$('.niveau2').mouseleave(function(){
	//	timer = setTimeout(function(){	
	//		hideClass('sous-menu');
	//		hideClass('sous-sous-menu');
	//		toggleButtonMenu(this, false);
	//	},200);

	//});

	var iWindowsSize = $(document).width();

	//Rollover sur vignettes de la page d'accueil
	if(iWindowsSize > 1182){
		$('.index_vignette').mouseenter(function(){
			$(this).find('.index_hover').show();
			$(this).find('.img_ambiance').hide();
		}).mouseleave(function(){
			$(this).find('.index_hover').hide();
			$(this).find('.img_ambiance').show();
		});
	}
	//Rollover sur vignette de la page catégorie
	if(iWindowsSize > 974 ){
		$('.vignette').mouseenter(function(){
			$(this).find('img.visuel').css('display','none');
			$(this).find('.categorie_hover').show();
			$(this).find('.vfimg').hide();
		}).mouseleave(function(){
			$(this).find('img.visuel').css('display','block');
			$(this).find('.categorie_hover').hide();
			$(this).find('.vfimg').show();
		});
	}

	/* FAUX SELECT */
	$(document).on('click',function(e){
		if($(e.target).closest('.faux_select').length===0){
			$('.select-choix').css({borderTop: '1px solid #E6E2E0', boxShadow: 'none'})
			closeFauxSelects();
		}
	});
	
	$('.select-choix').click(function(){

		var listeChoix = $(this).next('ul');

		//Affichage de la liste de choix lors du clic
		if(listeChoix.css('display') == 'none'){
		//Fermeture de tous les autres select
		closeFauxSelects();
		$('.select-choix').css({borderTop: '1px solid #E6E2E0', boxShadow: 'none'})
		$(this).css('border-top', 'none');
		$(this).css('box-shadow', '1px 1px 9px rgba(0, 0, 0, 0.38)');
		listeChoix.css('display', 'block');
	} else {
		listeChoix.css('display', 'none');
		$(this).css({borderTop: '1px solid #E6E2E0', boxShadow: 'none'})
	}
});

	$("li").find(".choice").hover(
		function() {
			$( this ).find(".far.fa-square").css('background-color', '#C6C5C4');
		}, function() {
			$( this ).find(".far.fa-square").css('background-color', 'white');
		}
		);



	$('.choice').parent().mouseenter(function(){

		//Changement de couleur au mouseover
		//$(this).css("background-color", "#a1a1a1");

	}).mouseleave(function(){

		//Changement de couleur au mouseover
		//$(this).css("background-color", "#e3e2e0");

	});

	$('.choice').prev('input').click(function(){

		var element        = $(this).next('a');
		var contentElement = $(this).parent().parent().prev('p').find('a');
		var classElement   = $(this).parent().attr('class');
		var input          = $(this);

		if(input.prop('checked')){

			//Gestion contenu champs principal
			cocheOption(element, input, contentElement);
		} else {

			//Gestion contenu champs principal
			decocheOption(input, contentElement, classElement);
		}
	});

	$('.choice').click(function(){

		var element        = $(this);
		var contentElement = $(this).parent().parent().prev('p').find('a');
		var classElement   = $(this).parent().attr('class');
		var input          = $(this).prev('input');

		if(input.prop('checked')){
			//Décochage checkbox
			input.prop('checked', false);

			//Gestion contenu champs principal
			decocheOption(input, contentElement, classElement);
		} else {
			//Cochage checkbox
			input.prop('checked', true);

			//Gestion contenu champs principal
			cocheOption(element, input, contentElement);
		}
	});

	$('a.valider').click(function(){

		//Récupération de l'url
		var div   = $(this).closest('div');
		var input = div.prev('p').prev('input');
		
		var url = input.val();

		//Récupération des paramètres cochés
		var liste_val = '';
		div.find('li').each(function(){
			if($(this).attr('class') == 'filtre_dimensions'){
				var input_min = $(this).find('input');
				var input_max = $(this).find('input').next('input');

				var min_value = input_min.val();
				var max_value = input_max.val();

				if(input_min.attr('id') == 'larg_min') var libelle = 'largeur';
				if(input_min.attr('id') == 'haut_min') var libelle = 'hauteur';
				if(input_min.attr('id') == 'prof_min') var libelle = 'profondeur';

				url = url.replace('%5B%3Fdim_min%5D',min_value);
				url = url.replace('%5B%3Fdim_max%5D',max_value);
			}

			if($(this).find('input').prop('checked')){
				classLi = $(this).attr('class');
				var aTemp = classLi.split('-');
				if(aTemp[1]){
					if(liste_val != '') liste_val += ',';
					liste_val += aTemp[1];
				} 
			}
		});

		location.href = url.replace('%5B%3Furl_values%5D',liste_val);
	});

	//LISTE DE CHOIX DE L'ORDRE DE TRI (PAGE CATEGORIE)
	$('.tri_page').on('change', function(){
		location.href = $(this).val();
	});

	//LISTE DE CHOIX DU NOMBRE DE RESULTATS PAR PAGE (PAGE CATEGORIE)
	$('.nb_page').on('change', function(){
		location.href = $(this).val();
	});

	//PAGE DETAIL PRODUIT
     //Script permettant l'ouverture/fermeture d'une DIV contenant des options
     $('.libelle_option').each(function(){
     	$(this).click(function(){
     		var objTxtEnfant = $(this).children('p#txt_options');
     		var objLienEnfant = objTxtEnfant.children('i');
     		
     		$(this).next('div').slideToggle();
     		
     		if($(objLienEnfant).hasClass('fa-chevron-down')){
     			$(objTxtEnfant).empty();
     			$(objTxtEnfant).html($(objTxtEnfant).data('close')+' <i class="fa fa-chevron-up fa-lg"></i>');
     		} else {
     			$(objTxtEnfant).empty();
     			$(objTxtEnfant).html($(objTxtEnfant).data('open')+' <i class="fa fa-chevron-down fa-lg"></i>');
     		}
     	});
     });
     
     //PAGE DETAIL PRODUIT
     //Script permettant l'ouverture de toutes les options
     $('#libelle_encart').click(function(){
     	var opened = false;
     	var objParentFleche = $(this).children('a');
     	
     	$(this).nextAll('.centre_options').each(function(){
     		if($(this).css('height') === '1px' || $(this).css('display') === 'none'){
     			var objDivPrev = $(this).prev('.libelle_option');
     			var objTxt = $(objDivPrev).children('p#txt_options');
     			var objFleche = objTxt.children('i');
     			
     			if($(objFleche).hasClass('fa-chevron-down')){
     				$(objTxt).empty();
     				$(objTxt).html($(objTxtEnfant).data('close')+' <i class="fa fa-chevron-up fa-lg"></i>');
     			} else {
     				$(objTxt).empty();
     				$(objTxt).html($(objTxtEnfant).data('open')+' <i class="fa fa-chevron-down fa-lg"></i>');
     			}
     			
     			$(this).slideToggle();
     			opened = true;
     		}
     	});
     	
          //Si tout est déjà ouvert, on referme tout
          if(!opened){
          	$(this).nextAll('.centre_options').each(function(){
          		var objDivPrev = $(this).prev('.libelle_option');
          		var objTxt = $(objDivPrev).children('p#txt_options');
          		var objFleche = objTxt.children('i');
          		
          		if($(objFleche).hasClass('fa-chevron-down')){
          			$(objTxt).empty();
          			$(objTxt).html($(objTxt).data('close')+' <i class="fa fa-chevron-up fa-lg"></i>');
          		} else {
          			$(objTxt).empty();
          			$(objTxt).html($(objTxt).data('open')+' <i class="fa fa-chevron-down fa-lg"></i>');
          		}
          		
          		$(this).slideToggle();
          	});
          	
          	$(objParentFleche).removeClass();
          	$(objParentFleche).addClass('fleche');
          	
          	$('#action_bouton').empty();
          	$('#action_bouton').html('Afficher toutes les caractéristiques');
          	
          	bloc_fermeture_option = false;
          } else {
          	$(objParentFleche).removeClass();
          	$(objParentFleche).addClass('fleche_on');
          	
          	$('#action_bouton').empty();
          	$('#action_bouton').html('Masquer toutes les caractéristiques');
          	
          	bloc_fermeture_option = true;
          }
      });

     //fonction pour faire afficher l overlay lorsque l'utilisateur sors la souris du navigateur.
     if(iWindowsSize > 1182){
     	$(document).mousemove(function(e) {
     		var y = e.pageY - (document.body.scrollTop || document.documentElement.scrollTop);
     		var date = new Date();
     		var heure = date.getHours();
     		var jour = date.getDay();
			var time = date.getTime() - myTimer ; //myTimer est déclaré en haut
			$('#exitpopup').css('left', (window.innerWidth/2 - $('#exitpopup').width()/2));
			$('#exitpopup').css('top', (window.innerHeight/2 - $('#exitpopup').height()/2));
			if(getCookie('admin_username') == ""){
				if( y <= 5 && getCookie('popup_exit') == '' && ((heure>=9 && heure <= 13) || (heure>=14 && heure <= 19)) && (jour >= 1 && jour <= 5) && time>=3000) {
					tb_show('', '/rappelimmediat.php?width=400&height=300',false);
					setCookie('popup_exit',1,1);		
				}	
			}
		});
     }

     $('#rappel_immediat_non').click(tb_remove);
     

     if(iWindowsSize > 974 ){
     	$('.encart_option').mousemove(function(event){
     		var objDivZoom = $(this).next('div.visuel_zoom');
     		var Hzoom = objDivZoom.css('height');
     		if(Hzoom){
     			Hzoom = Hzoom.substr(0, Hzoom.length - 2);
     			var middleWindow = $(window).height() / 2;
	               //if(event.screenY <= middleWindow){
	               	var positionTop = parseInt(event.pageY) ;
	              // } else {
	                 //   var positionTop = parseInt(event.pageY) - parseInt(Hzoom) ;
	               //}

	               objDivZoom.css({
	               	'left' : event.pageX * 0.5,
	               	'top' : 'unset'
	               });
	               objDivZoom.show();
	           }
	       }).mouseleave(function(event){
	       	if($(this).next('div.visuel_zoom')){
	       		$(this).next('div.visuel_zoom').hide();
	       	}
	       });
	   }
	   
	   /*$(window).scroll(function() {
	   	//if(!$('.menu').hasClass("fixed") && ($(window).scrollTop() > 125)) {
	   	//	$('.menu').addClass("fixed");
	   	//	$('.sous-menu').addClass("fixed");
	   	//	$('.sous-sous-menu').addClass("fixed");
	   		$('.sous-menu').each(function(){
	   			if($(this).css('display')=="block"){
	   				$('.menu').addClass("visible");
	   				return false;
	   			}
	   		});

	   // Si activé, ça inverse les icons "panier" et "contact" dans le menu du Header
	      //if($(window).width()>1000){
		 	//	$('#menu_sm').find('#bouton_panier').detach().appendTo('#navbar-nav');
		 	//	$('.minilogo').show();
	      //}

	  }
	  else if ($('.menu').hasClass("fixed") && ($(window).scrollTop() < 125)) {
	  	$('.menu').removeClass("fixed");
	  	$('.sous-menu').removeClass("fixed");
	  	$('.sous-sous-menu').removeClass("fixed");
	  	$('#navbar-nav').find('#bouton_panier').detach().prependTo('.icons');
	  	$('.minilogo').hide();
	  }
	  
	  if($('.faux_select:hover').length==0) closeFauxSelects();
	});
	*/



      // footer
      $('.footerbastop').find('.close').hide();
      $('.open-footerbas').click(function(){
      	$('.footerbas').show();
      	$('.footerbastop').find('.close').show();
      	$('.lazyf').lazy();			 
      	$('.open-footerbas').hide();
      });
      $('.footerbastop').find('.close').click(function(){
      	$('.footerbas').hide();
      	$('.open-footerbas').show();	
      	$('.footerbastop').find('.close').hide();		 
      });
      
      if($('.reassurance.slide').length>0){
      	change_reassurance();
      }
      
      if($('#encart_options').length>0)
      	arrange_options();


	//Affichage de l'encart Sofinco sur les pages articles du FRONT
	if($('#artid_sofinco').length){

		var artid = $('#artid_sofinco').val();

		$.ajax({
			
			type : 'GET',
			url : '/ajax/sofinco.php',
			dataType : 'text',
			data : { 
				artid : artid, 
				type : 'article' 
			}

		}).done(function(responseText){

			$('#sofinco_content').css('text-align', 'left'); //Le centrage est utile pour le loader
			$('#sofinco_content').html(responseText);
		});
	}

	//Affichage de l'encart Sofinco sur les pages commandes du FRONT
	if($('#facturid_sofinco').length){

		var facturid = $('#facturid_sofinco').val();
		var mensualites = $('#mensualites_sofinco').val();
		var moyenpaiement = $('#moyenpaiement_sofinco').val();

		$.ajax({
			
			type : 'GET',
			url : '/ajax/sofinco.php',
			dataType : 'text',
			data : { 
				facturid : facturid,
				mensualites : mensualites,
				moyenpaiement : moyenpaiement,
				type : 'commande' 
			}

		}).done(function(responseText){

			$('#sofinco_content'+moyenpaiement).css('text-align', 'left'); //Le centrage est utile pour le loader
			$('#sofinco_content'+moyenpaiement).html(responseText);
		});
	}

	/********************/
	/* CHARGEMENTS AJAX */
	/********************/
	//loadAjaxPanier();
	//loadAjaxCompte();
	//loadAjaxNbArticlesVus();
	//loadAjaxArticlesVus();
	//loadAjaxNbPdtsComparateur();
	//loadAjaxConfig();
	//loadAjaxNbConfig();
	loadAjax();
	loadAjaxDelaisLivraison();
});

function loadAjaxDelaisLivraison()
{
	//Nombre de produits au panier (header)
	if($('#delais_livraison_ajax').length)
	{
		//$('#delais_livraison_ajax').html('<img src="/images/loader.gif" style="width: 10px;">');

		$.ajax({
			
			type : 'GET',
			url : '/ajax/article/delais_livraison.php',
			dataType : 'text',
			data : { 
				artid : $('#article_id').val(),
			}

		}).done(function(responseText){

			$('#delais_livraison_ajax').html(responseText);

		});
	}
	/*
	//Nombre de produits au panier (header)
	if($('#date_livraison_ajax').length)
	{
		$('#date_livraison_ajax').html('<img src="/images/loader.gif" style="width: 10px;">');

		$.ajax({
	          
          	type : 'GET',
			url : '/ajax/article/date_livraison.php',
			dataType : 'text',
			data : { 
	          	artid : $('#article_id').val(),
			}

		}).done(function(responseText){

			$('#date_livraison_ajax').html(responseText);

		});
	}
	*/
}

function loadAjaxConfig()
{
	//Articles vus (barre footer)
	$.ajax({
		
		url : '/ajax/footer_barre/configurations.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#popup_config').html(responseText);
	});
}

function loadAjax()
{
	/*
	$('#nb_configs').html('<img src="/images/loader.gif" style="width: 10px;">');
	$('#nb_pdts_comparateur').html('<img src="/images/loader.gif" style="width: 10px;">');
	$('#articlevusbarbtn').html('<img src="/images/loader.gif" style="width: 10px;">');
	*/
	var gclid = $('#gclid_field').val();
	if(gclid!=''){
		var gclidtxt='&gclid='+gclid;
	} else {
		var gclidtxt='';
	}
	$.ajax({
		
		url : '/ajax/loadAjax.php?r=' + encodeURIComponent(window.location.pathname + window.location.search + window.location.hash + gclidtxt),
		dataType : 'json'

	}).done(function(data){
		$(data['popup_panier']).insertAfter('#bouton_panier');
		$('.nb_articles_panier').html(data['nb_articles_panier']);
		if(data['nb_articles_panier']>0){
			$('.titre-mobile').addClass('marge-titre-mobile');
			$('.nb_articles_panier').css('display','inline-block');
			$("#bouton_panier").hover(function(){
				popup_header('popup_panier',true);
			}, function(){
				popup_header('popup_panier',false);
			});
		}
		$(data['popup_compte']).insertAfter('#bouton_compte');
		//pour palier au cache du changement d'icon de connexion :
		var cookie_user = document.cookie;
		var cookie_userid = cookie_user.match(/userid=[0-9]+/);
		var cookie_prenom = cookie_user.match(/prenom=[a-zA-Z- ]+/);
		var src = $(".picto-site.picto-compte").attr("src");
		var src_mobile = $("a.picto-compte").find(".picto-site").attr("src");
		if(cookie_prenom){
			var cookie_prenom = cookie_prenom[0].match(/=[a-zA-Z- ]+/)[0];
			if(cookie_userid[0].match(/[0-9]+/)[0]){
				$(".picto-site.picto-compte").attr("src",src.replace(/compte\.svg/,'compteconnecte.svg'));
				$("a.picto-compte").find(".picto-site").attr("src",src.replace(/compte\.svg/,'compteconnecte.svg'));
				$("#prenom_compte").text(cookie_prenom.match(/[a-zA-Z- ]+/)[0]);
			}
		}
		else{
			$(".picto-site.picto-compte").attr("src",src.replace(/compteconnecte\.svg/,'compte.svg'));
			$("a.picto-compte").find(".picto-site").attr("src",src.replace(/compteconnecte\.svg/,'compte.svg'));
			$("#prenom_compte").text('compte');
			
		}

		$('#script_analytics').html(data['analytics']);
		/*$('#script_gtm1').attr('src',data['analytics_gtm']);*/
		$('#script_gtm').html(data['gtm']);
		$('#script_gtm2').html(data['gtm2']);
		$('#script_gclid').html(data['gclid']);
		$('#script_nextperformance').html(data['nextperformance']);
		$('#script_remarketing').html(data['remarketing']);
		$('#pixel_fb').html(data['pixel_fb']);
		$('#barrecookies').html(data['barrecookies']);

	});
	toggleCompare(0);
}

function loadAjaxNnconfig(){
	$('#nb_configs').html('<img src="/images/loader.gif" style="width: 10px;">');

	$.ajax({
		
		url : '/ajax/loadAjax.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#nb_configs').html(responseText);
		
	});
}

function loadAjaxNbPdtsComparateur()
{
	$('#nb_pdts_comparateur').html('<img src="/images/loader.gif" style="width: 10px;">');

	//Articles vus (barre footer)
	$.ajax({
		
		url : '/ajax/footer_barre/nb_pdts_comparateur.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#nb_pdts_comparateur').html(responseText);
	});
}

function loadAjaxArticlesVus()
{
	//Articles vus (barre footer)
	$.ajax({
		
		url : '/ajax/footer_barre/articles_vus.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#articlevusbar').html(responseText);
	});
}

function loadAjaxNbArticlesVus()
{
	$('#articlevusbarbtn').html('<img src="/images/loader.gif" style="width: 10px;">');

	//Nb articles vus (barre footer)
	$.ajax({
		
		url : '/ajax/footer_barre/nb_articles_vus.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#articlevusbarbtn').html(responseText);
	});
}

function loadAjaxPanier()
{
	//Nombre de produits au panier (header)
	//if($('#nb_articles_panier').length)
	//{
		$('.nb_articles_panier').html('<img src="/images/loader.gif" style="width: 7px;">');
		
		
		
		$.ajax({
			
			url : '/ajax/panier/nb_produits.php?r=' + window.location.pathname + window.location.search + window.location.hash,
			dataType : 'text'

		}).done(function(responseText){
			if(parseInt(responseText) > 0){
				$('.titre-mobile').addClass('marge-titre-mobile');
				$('.nb_articles_panier').css('display','inline-block');
			}
			$('.nb_articles_panier').html(responseText);

		});
	//}

	//Popup contenu panier (header)
	$.ajax({
		
		url : '/ajax/panier/popup_produits.php',
		dataType : 'text'

	}).done(function(responseText){

		$(responseText).insertAfter('#bouton_panier');

	});
}

function loadAjaxCompte()
{
	//Popup "mon compte" (header)
	$.ajax({
		
		url : '/ajax/compte/popup_compte.php',
		dataType : 'text'

	}).done(function(responseText){

		$(responseText).insertAfter('#bouton_compte');

	});

	var cookie_user = document.cookie;
	var cookie_user = document.cookie;
	var cookie_userid = cookie_user.match(/userid=[0-9]+/);
	var cookie_prenom = cookie_user.match(/prenom=[a-zA-Z- ]+/);
	var src = $(".picto-site.picto-compte").attr("src");
	if(cookie_prenom){
		var cookie_prenom = cookie_prenom[0].match(/=[a-zA-Z- ]+/)[0];
		if(cookie_userid[0].match(/[0-9]+/)[0]){
			$(".picto-site.picto-compte").attr("src",src.replace(/compte\.svg/,'compteconnecte.svg'));
			$("#prenom_compte").text(cookie_prenom.match(/[a-zA-Z- ]+/)[0]);
		}
	}
	else{
		$(".picto-site.picto-compte").attr("src",src.replace(/compteconnecte\.svg/,'compte.svg'));
		$("#prenom_compte").text('compte');
	}
}

function decocheOption(input, contentElement, classElement){
	
	//Gestion contenu champs principal
	removedElement = contentElement.find('.'+classElement);
	removedElement.remove();

	//Si le champs est vide, on réécrit "tous"
	
	var defaultChoice = contentElement.find('.choix-0');
	var content = defaultChoice.next('span');
	if(typeof(content.attr('class')) == 'undefined') defaultChoice.css('display','inline');

	//Nettoyage du contenu (virgules au bon endroit)
	if(defaultChoice.css('display') == 'none'){
		var i = 0;
		contentElement.find('span').each(function(){

			currentElementClass = $(this).attr('class');
			nextElementClass = $(this).next('span').attr('class');

			//Début de chaine : pas de virgule
			if(i == 1 && currentElementClass == 'sep'){
				$(this).remove();
			}

			//Fin de chaine : pas de virgule
			if(typeof(nextElementClass) == 'undefined' && currentElementClass == 'sep'){
				$(this).remove();
			}

			//2 libellés à la suite sans virgule, on en ajoute une
			if(i > 0 && currentElementClass != 'sep' && nextElementClass != 'sep') {
				$(this).after('<span class="sep">, </span>');
			}

			//2 virgules à la suite, on en supprime une
			if(i > 0 && currentElementClass == 'sep' && nextElementClass == 'sep') {
				$(this).remove();
			}

			i += 1;
		});
	}
}

function cocheOption(element, input, contentElement){

	//Gestion contenu champs principal
	var spanContenu = contentElement.find('.choix-0');
	var displaySpan = spanContenu.css('display');

	spanContenu.css('display','none');
	contentElement.html('<span class="'+element.parent().attr('class')+'">'+element.text()+'</span>');

}

function toggleButtonMenu(obj, state){
	
	$('.niveau1').each(function(){
		$(this).children('.tiret').removeClass('selected');
		$(this).find('a').removeClass('selected');
	});

	if(state){
		$(obj).children('.tiret').addClass('selected');
		$(obj).css('cursor','pointer');
		$(obj).find('a').addClass('selected');
	}
}

function toggleButtonSousMenu(obj, state){

	$('.niveau2').each(function(){
		$(this).removeClass('selected');
	});

	if(state){
		$(obj).addClass('selected');
	}
}

function showMenuResp(id){
	var idName = 'sous-menu-2';
	var hgt = 0;
	var marginhgt = 0;
	var num = id.substring(id.lastIndexOf('-') + 1, id.length);
	if(num>=4)
		$('#menu_resp').animate({top: -102 });	
}



function showMenu(obj, idName){
	var id = $(obj).attr('id');
	var num = id.substring(id.lastIndexOf('-') + 1, id.length);


	var menu1_pos = $('#'+id).position();
	var menu2_pos = $('#'+idName+'-'+num).position();
	var fond_menu_pos = $('#fond-menus').position();
	var left = 0;

	if(menu2_pos != undefined){
		menu2_pos.left = menu1_pos.left - fond_menu_pos.left;
		left = menu2_pos.left;
	}

	$('#'+idName+'-'+num).show().css("left",left);
	
	if(idName=="sous-menu"){
		var autoHeight=55;
		var width=0;
		$('#'+idName+'-'+num).find('.niveau2').each(function(){
			width ++;
		});
		width = 100 / width;
		var childwidth = Math.floor(width);
		childwidth =childwidth - ((1/width)*2.8);
		childwidth = childwidth+'%';
		/*$('#'+idName+'-'+num).find('.niveau2').each(function(){
		  //$(this).width(childwidth);
		});*/
	}
	/*if(idName=="sous-sous-menu"){
	    var autoHeight =$('#'+idName+'-'+num).find('.ssmenu-inner').height();
	}*/
	
	//$('#'+idName+'-'+num).delay(200).animate({height: autoHeight}, 500);
}

function hideClass(className){
	$('.'+className).hide();
}

function closeFauxSelects(){
	$('.faux_select').each(function(){
		$(this).find('ul').css('display', 'none');
	});
}

function toggleSlide(obj, id){
	var divid = '#'+id;
	$(divid).slideToggle('fast',function(){
		if($(divid).css('display') == 'none'){
			$(obj).removeClass('opened');
		} else {
			$(obj).addClass('opened');
		}
	});
}

// fonction à modifier aussi dans mobile.js si nécessaire
function checkOption(obj, artid, caractid, caractvalid, numOption, ral, color){
	
	setLoaderOptions();
	
          /********************\
           * INCOMPATIBILITES *
           \********************/

          //Si l'option n'est pas désactivée, on lance les actions
          if($(obj).prop('class').indexOf('disabled') == '-1'){

               /*if($(obj).prop('class').indexOf('selected') == '-1'){ //Si l'utilisateur active une option

		               var option_unchecked = '';

		               //On détermine si une autre option est déjà sélectionnée dans la même caractéristique
		               $.ajax({

		                    type : 'GET',
		                    url : '/ajax/get_options_caracteristiques.php',
		                    dataType : 'text',
		                    data : { artid: artid, caractid: caractid },
		                    async : false

		               }).done(function(chaine){

		                    var tabOptions = chaine.split('|');
		                    jQuery.each(tabOptions, function(i, option_id){

		                         var lien_id = '#option'+option_id;
		                         if($(lien_id)){ 
		                              var is_checked = $(lien_id).prop('class').indexOf('selected');
		                              if(is_checked != '-1' && caractvalid != option_id){ 

		                                   option_unchecked = option_id;

		                              }
		                         }
		                    });

		               });
		               
		               //Si une autre option de la même caractéristique est cochée, on la décoche, et on réactive les incompatibilités associées
		               if(option_unchecked != ''){
		                    active_incompatibilite(artid, caractid, option_unchecked);
		               }

		               desactive_incompatibilite(artid, caractid, caractvalid);

		          } else { //Si l'utilisateur désactive une option

		               active_incompatibilite(artid, caractid, caractvalid);

		           }*/
		           
          /************************\
           * FIN INCOMPATIBILITES *
           \************************/
           
           if($(obj).prop('class').indexOf('selected') != '-1'){
           	var etat_bouton = 'selected';
           } else {
           	var etat_bouton = 'free';
           }
           
          //Si action = cochage option
          if(etat_bouton == 'free'){
               //Récupération de la DIV parent de l'option sélectionnée
               var objParent = $(obj).parent().parent();

               //Récupération de l'encart précédent à fermer
               var objPrevBtn = $(objParent).prev('div').find('div.centre_options');

               //Récupération de l'encart suivant à ouvrir
               var objNextBtn = $(objParent).next('div').find('div.centre_options');

               //Ouverture de l'option suivante / Fermeture de la précédente
               if(!bloc_fermeture_option){
               	$(objPrevBtn).slideUp();
               }
               $(objNextBtn).slideDown();

               //Changement de sens des flèches de l'encart précédent | Changement texte "afficher/masquer les options"
               if(!bloc_fermeture_option){ 
               	var objPrevTxt = objParent.next('div').find('div.libelle_option').children('p#txt_options');
               	$(objPrevTxt).empty();
               	$(objPrevTxt).html($(objPrevTxt).data('open')+' <i class="fa fa-chevron-down fa-lg"></i>');
               }

               //Changement de sens des flèches de l'encart suivant | Changement texte "afficher/masquer les options"
               var objNextTxt = objParent.next('div').find('div.libelle_option').children('p#txt_options');
               $(objNextTxt).empty();
               $(objNextTxt).html($(objNextTxt).data('close')+' <i class="fa fa-chevron-up fa-lg"></i>');

               //Décochage de toutes les options
               $(objParent).find('div.encart_option').each(function(i){
                    //Suppression du contour orange sur la DIV
                    $(this).removeClass('selected');

                    //Décochage du bouton
                    $(this).children('input:radio').prop('checked', false);
                });

               //Ajout du contour orange sur la DIV cliquée
               $(obj).addClass('selected');

               //Cochage du bouton
               $(obj).find('input:radio').prop('checked', true);

               //Recalcul du prix total
               doRequest(artid); 

               //Affichage de l'option sélectionné dans l'encart preview
               option_preview_new(artid, caractid, caractvalid, false, ral, color);

               //enregistrment des options pour envoi de la demande de contact
               var checked_opt = $('#checked_opt_'+caractid);
               checked_opt.val(caractvalid);


               
          //Si action = décochage option
      } else {
      	
               //suppression des options enregistrées pour envoi de la demande de contact
               var checked_opt = $('#checked_opt_'+caractid);
               checked_opt.val('');

               //Suppression du contour orange sur la DIV cliquée
               $(obj).removeClass('selected');

               //Décochage du bouton
               $(obj).find('input:radio').prop('checked', false);
               
               //Recalcul du prix total
               doRequest(artid); 
               
               //Affichage de l'option sélectionné dans l'encart preview
               option_preview_new(artid, caractid, caractvalid, true);

           }
           
       }
       
     //Mise à jour des informations affichées pour les options désactivées
     set_infos_disabled(artid);
     
     /*if(!bloc_fermeture_option){
          if(numOption > 1){
               var goToOption = numOption - 1;
          }
          location.href = '#choix_options'+goToOption;
      }*/
  }
  function checkOptionSimu(artid,caractid,caractvalid,type,time){
  	$(".calque"+type).attr("src","/configurations/www.ma-cave-a-vin.fr/images/simulateur/"+artid+"_"+caractid+"_"+caractvalid+".png?"+time);
  	$(".calque"+type).attr("value",caractid+"_"+caractvalid);
  	var d = new Date();
  	d.setTime(d.getTime() + (1*24*60*60*1000));
  	document.cookie = "calque"+type+"="+caractid+"_"+caractvalid+";expires="+d.toUTCString()+";";
  }

  function option_preview_new(artid, caractid, caractvalid, remove, ral, color){
  	$.ajax({
  		
  		type : 'GET',
  		url : '/ajax/checkOption.php',
  		dataType : 'text',
  		data : { caractvalid: caractvalid, artid: artid }
  		
  	}).done(function(html){
  		
  		$("#previewopt"+caractid).empty();
  		if(!remove){
  			$("#previewopt"+caractid).append(html);
  		} else {
  			$("#previewopt"+caractid).append("<span style=\"color:#DC081E;\">A définir</span>");
  		}
  		
  		if(!remove && ral){
  			var Element = $('#previewopt'+caractid).find('div');
  			Element.find('img').remove();
  			Element.prepend('<div class="ral-couleur" style="background-color:'+color+';"></div>');
  		}
  	});
  }

  function active_incompatibilite(artid, caractid, caractvalid){
  	$.ajax({

  		type : 'GET',
  		url : '/ajax/get_incompatibilites.php',
  		dataType : 'xml',
  		data : { caractvalid: caractvalid, artid: artid },
  		async : false

  	}).done(function(xml){

  		xmlDoc = $(xml);
  		
  		xmlDoc.find('incompatibilite').each(function(){
  			
  			var incompatibilite_id = $(this).attr('value');
  			
  			$(this).find('caracteristique').each(function(){
  				
  				var caracteristique_id = $(this).attr('value');
  				
  				$(this).find('option').each(function(){

  					var option_id = $(this).text();
  					
                         //Association de l'identifiant incompatibilité à l'option
                         var tab_incomp = new Array();
                         var lst_incomp = $('#incomp_opt'+option_id).attr('value');
                         
                         tab_incomp = lst_incomp.split('|');
                         
                         var j = 0;
                         var recons_incomp = new Array();
                         for(i=0;i<tab_incomp.length;i++){
                         	if(tab_incomp[i] != '' && tab_incomp[i] != incompatibilite_id){
                         		recons_incomp[j] = tab_incomp[i];
                         	}
                         }
                         
                         var activation = true;
                         if(recons_incomp.length > 0 && recons_incomp[0] != ''){
                         	activation = false;
                         	var texte_input = recons_incomp.join('|');
                         	
                         	$('#incomp_opt'+option_id).attr('value', texte_input);
                         } else {
                         	$('#incomp_opt'+option_id).attr('value', '');
                         }
                         
                         //Activation de l'option si nécessaire
                         if(activation){
                         	$('#option'+option_id).removeClass('disabled');
                         	$('#lst_'+caracteristique_id+'_'+option_id).prop('disabled', false);
                         }
                     });
  				
  			});
  		});
  	});
  }

  function desactive_incompatibilite(artid, caractid, caractvalid){
  	
     //Récupération de la liste des options potentiellement incompatibles
     $.ajax({
     	
     	type : 'GET',
     	url : '/ajax/get_incompatibilites.php',
     	dataType : 'xml',
     	data : { caractvalid: caractvalid, artid: artid },
     	async : false
     	
     }).done(function(xml){
     	
     	xmlDoc = $(xml);
     	
     	xmlDoc.find('incompatibilite').each(function(){
     		
     		var incompatibilite_id = $(this).attr('value');
     		
     		$(this).find('caracteristique').each(function(){
     			
     			var caracteristique_id = $(this).attr('value');
     			
     			$(this).find('option').each(function(){

     				var option_id = $(this).text();
     				
                         //Association de l'identifiant incompatibilité à l'option
                         var tab_incomp = new Array();
                         var lst_incomp = $('#incomp_opt'+option_id).attr('value');
                         
                         tab_incomp = lst_incomp.split('|'); 
                         if(tab_incomp.length >= 1 && tab_incomp[0] != ''){
                         	tab_incomp[tab_incomp.length] = incompatibilite_id;
                         } else {
                         	tab_incomp[0] = incompatibilite_id;
                         }
                         
                         var texte_input = tab_incomp.join('|');
                         
                         $('#incomp_opt'+option_id).attr('value', texte_input);
                         
                         if(caracteristique_id != caractid){
                              //Désactivation de l'option
                              $('#option'+option_id).addClass('disabled');
                              $('#lst_'+caracteristique_id+'_'+option_id).prop('disabled', true);
                          }
                          
                      });
     			
     		});
     	});
     });
 }

 function set_infos_disabled(artid){
 	
     //Bouclage sur la liste des input cachés, afin de récupérer les identifiants d'incompatibilités affichées
     $('.input_hidden_option').each(function(){
     	
     	var text_incomp = $(this).prop('value');
     	
          //Si l'option vérifiée est désactivée
          if(text_incomp != ''){
          	
          	var tab_incomp = new Array();
          	tab_incomp = text_incomp.split('|');
          	
               //Pour chaque incompatibilité actuellement en cours, on cherche celle qui n'est désactivée
               tab_incomp.each(function(value){
               	
               	$.getJSON( "/ajax/get_options_incompatibles.php?incompatibilite="+value+"&artid="+artid, function( data ) {
               		
               		var opt_intitule_selected = '';
               		$.each( data, function( key, val ) {
               			if($('#option'+key).prop('class').indexOf('selected') != '-1'){
               				opt_intitule_selected = val;
               			}
               		});
               		
               		$.each( data, function( key, val ) {
               			if($('#option'+key).prop('class').indexOf('selected') == '-1'){
               				$('#info_disabled_'+key).css('display','block');
               				$('#lst_opt_dis_'+key).text(opt_intitule_selected);
               			}
               		});
               	});
               });
           } else {
           	
           	var tab_names = $(this).prop('name').split('_');
           	var opt_id = tab_names[2];
           	$('#info_disabled_'+opt_id).css('display','none');
           	$('#lst_opt_dis_'+opt_id).empty();
           }
       });
     
     setLoaderOptions();
 }

 function setLoaderOptions(){
 	if($('#loader_options')){
 		
 		var div_loader = $('#loader_options');
 		var div_options = $('#encart_options');
 		
 		if(div_loader.css('display') == 'none'){
 			
 			div_loader.html('<img src="/images/loader.gif">');
 			div_loader.css('display', 'block');
 			div_options.css('opacity', '0.2');
 			
 		} else {
 			
 			div_loader.empty();
 			div_loader.css('display', 'none');
 			div_options.css('opacity', '1');
 			
 		}
 	}
 }

 function show_ral(caractvalid){
 	$('.ral_panel').each(function(){
 		if($(this).attr('id') != 'ral_panel_'+caractvalid){
 			$(this).hide();
 		}
 	});

 	if(caractvalid){
 		$('#ral_panel_'+caractvalid).toggle();
 	}
 }

 function close_ral(){
 	$('.ral_panel').each(function(){
 		$(this).hide();
 	});
 }

 function filter_link(link,name){
 	$('#filtre_'+name).find('ul').hide();
 	link.href+='#'+name+document.documentElement.scrollTop;

 	//$('.wait').removeClass('hidden');
 }


 function scroll_search(){
 	var h=document.location.hash;
 	var re=/\#([a-z]+)([0-9]+)/;
 	var infos=re.exec(h);
 	if(infos!=undefined){
 		window.scrollTo(0,infos[2]);
 		setTimeout(function(){
 			$('#filtre_'+infos[1]).find('ul').css('display', 'block');
 		},100);
 	}
 	//$('.wait').addClass('hidden');
 }

 function change_reassurance(){
 	var imgclass=$('.reassurance.slide').children("div:first")[0].className;
 	var c2=/img([0-9])/.exec(imgclass);

 	if(c2==undefined){
 		$('.reassurance.slide').children("div:first").addClass("img1");
 	}else{
 		var nextimg=parseInt(c2[1])+1;
 		if(nextimg>8) nextimg=1;
 		$('.reassurance.slide').children("div:first").removeClass("img"+c2[1]).addClass("img"+nextimg);
 	}
 	
 	setTimeout(change_reassurance,5000);
 }


 function dimvalues(url,name){
 	var dimurl='';
 	var dimfields=new Array();
 	var dimparam_ini=new Array("min_largeur","max_largeur","min_hauteur","max_hauteur","min_profondeur","max_profondeur");
 	var dimparam=new Array("larg_min","larg_max","haut_min","haut_max","prof_min","prof_max");
	var verif_val = true;

	//boucle sur chaque valeur de dimension
 	for(i=0;i<dimparam.length;i++){ 
 		if($('#'+dimparam[i]).val()){
 			var valeur = parseInt($('#'+dimparam[i]).val().replace(' mm',''));
 			//si la dimension choisie par l'utilisateur est inférieure à la valeur minimale possible :
 			if(dimparam[i].match('_min')){
 				if(valeur < $('.'+dimparam_ini[i]).val()){
 					dimfields[dimparam[i]]=$('.'+dimparam_ini[i]).val();
 					verif_val = false;
 				}
 				else{
 					dimfields[dimparam[i]]=$('#'+dimparam[i]).val();
 				}
 			}
 			//si la dimension choisie par l'utilisateur est supérieure à la valeur maximale possible :
 			else{
				if(valeur > $('.'+dimparam_ini[i]).val()){
 					dimfields[dimparam[i]]=$('.'+dimparam_ini[i]).val();
 					verif_val = false;
 				}
 				else{
 					dimfields[dimparam[i]]=$('#'+dimparam[i]).val();
 				}
 			}		
 		}
 		for(index in dimfields) 
 			if(dimfields.hasOwnProperty(index) && dimfields[index]!='') 
 				dimurl+=index+'='+dimfields[index]+'&';
 			
 			url+=(url.indexOf("?")>-1?"&":"?");

 			//si la recherche de l'utilisateur ne correspond pas aux dimensions min/max des produits :
 			if(verif_val == false){
 				alert('Aucun résultat. Veuillez modifier votre recherche.');
 				return false;
 			}
 			else {
 				document.location=url+dimurl+'#'+name+document.documentElement.scrollTop;
 			}
 	}
 			//$('.wait').removeClass('hidden');
 }

/**
	Cette fonction ajoute la class selected au boutons vert pour les accessoires conseillés en fonction de la taille de l'écran
	**/
	function vignscrollSize(){
		var iWindowsSize = $(document).width();
		var i;
		if(iWindowsSize > 1182 && iWindowsSize <1574){
			for(i=0;i<4;i++){
				$("#scroll_accessoires_btns > span.btn:eq("+i+")").addClass("selected");
				$("#scroll_similaire_btns > span.btn:eq("+i+")").addClass("selected");

			}
		}else if(iWindowsSize > 1573){
			for(i=0;i<5;i++){
				$("#scroll_accessoires_btns > span.btn:eq("+i+")").addClass("selected");
				$("#scroll_similaire_btns > span.btn:eq("+i+")").addClass("selected");
			}
		}
	}

	function vignscroll(divid,action){

		var vignette = $('#scroll_'+divid).children().first();
		var step = $(vignette).width() + parseInt($(vignette).css('margin-right'));
		var scroller=document.getElementById("scroll_"+divid);
		if(!scroller) return false;

		var elemnb=$('#scroll_'+divid+'_btns').find('.btn').length;
		var elemnbSelected = $('#scroll_'+divid+'_btns').find('.selected').length;
		var maxpos = elemnb - elemnbSelected;
		var scrollleft=scroller.scrollLeft;
		var pos=Math.round(scrollleft/step);
		
		var newpos=parseInt(action);
		var rel=action.substr(0,1);
		if(rel=="-") newpos=pos-1;
		if(rel=="+") newpos=pos+1;
		
		if(newpos>maxpos) newpos=maxpos;
		if(newpos<0) newpos=0;
		var btnpos=0;
		$('#scroll_'+divid).animate({scrollLeft:parseInt(step*newpos)},750);
		$('#scroll_'+divid).scrollLeft=parseInt(step*newpos);
		
		$('#scroll_'+divid+'_btns').find('.btn').each(function(){
			if(btnpos >= newpos && btnpos < newpos + elemnbSelected)
				$(this).addClass("selected");
			else
				$(this).removeClass("selected");
			
			btnpos++;
		});
      /*if(newpos==0)
	  $('#scroll_'+divid+'_btns').find('.toleft').css("visibility","hidden");
      else
	  $('#scroll_'+divid+'_btns').find('.toleft').css("visibility","");
      if(newpos==maxpos)
	  $('#scroll_'+divid+'_btns').find('.toright').css("visibility","hidden");
      else
	  $('#scroll_'+divid+'_btns').find('.toright').css("visibility","");
	*/
}

function change_artpic(src){
	document.getElementById('artpic').src=src;
}

function arrange_options(){
	$('.centre_options').each(function(){
		var heights=new Array();
		var display=$(this).css('display');
		$(this).css({
			position:   'absolute',
			visibility: 'hidden',
			display:    'block'
		});
		$(this).find('.encart_option .title').each(function(){
			heights.push($(this).outerHeight());
		});
		var max=Math.max.apply(Math,heights);
		$(this).find('.encart_option .title').each(function(){
			$(this).height(max);
		});
		$(this).css({
			position:   '',
			visibility: '',
			display: display
		});
	});
}

function mobile_alternate(){
	var links=document.getElementsByTagName('link');
	for(x in links){
		if(links.hasOwnProperty(x) && links[x].getAttribute("rel")=="alternate" && links[x].getAttribute("media")=="handheld")
			return links[x].getAttribute("href");
	}
	return false;
}

function hashparams(){
	var url=document.location.href;
	var hindex=url.indexOf("#");
	if(hindex==-1) return false;
	var s=url.substring(hindex+1);
	var p=s.split("&");
	var ret=new Array();
	for(i=0;i<p.length;i++){
		var d=p[i].split("=");
		ret[d[0]]=d[1];
	}
	
	return ret;
}

function afficheAccessoiresPanier(id,last_ligne, first_ligne){
	var acces = $('#scroll_accessoires_'+id).is(':visible');
	if(acces){
		$('#scroll_accessoires_'+id).hide();
		$('#scrollbtnsparent_'+id).hide();
		$('#acces_panier_title_'+id).hide();
		$('#text_vignscroll_'+id).css('display','none');
		$('#chev_left_'+id).removeClass('fa-chevron-up').addClass('fa-chevron-down');
		$('#chev_right_'+id).removeClass('fa-chevron-up').addClass('fa-chevron-down');
		if(last_ligne ){
			$('#borderTab').show();
		}
	}
	else{
		$('#scroll_accessoires_'+id).show();
		$('#scrollbtnsparent_'+id).show();
		$('#acces_panier_title_'+id).show();
		$('#text_vignscroll_'+id).css('display','table');
		$('#chev_left_'+id).removeClass('fa-chevron-down').addClass('fa-chevron-up');
		$('#chev_right_'+id).removeClass('fa-chevron-down').addClass('fa-chevron-up');
		//$('chev_left_'+id).addClass('fa-chevron-up');
		if(last_ligne){
			$('#borderTab').hide();
		}
	}
}

function afficheBoutonAccesPanier(){
	$('#panier_accessoires').show();
}

function display_criteres() {
	if($('.bloc_filtres').find('.ligne').css('display') === 'none') {
		$('.bloc_filtres').find('.selection.row').css('display', 'block');
		$('.bloc_filtres').find('.ligne').show();
		$('.bloc_filtres').find('.ligne').css('display', 'flex');
		$('#affinage_xs').find('i').toggleClass('fa-chevron-down fa-chevron-up');
	}
	else {
		$('.bloc_filtres').find('.selection.row').hide();
		$('.bloc_filtres').find('.ligne').css('display', 'none');
		$('#affinage_xs').find('i').toggleClass('fa-chevron-up fa-chevron-down');
	}
}

function display_affinage() {
	if($('.bloc_filtres').find('.ligne').css('display') === 'none') {
		$('.bloc_filtres').find('.ligne').css('display', 'flex');
		$('#affinage').find('i').toggleClass('fa-chevron-down fa-chevron-up');
	}
	else {
		$('.bloc_filtres').find('.ligne').css('display', 'none');
		$('#affinage').find('i').toggleClass('fa-chevron-up fa-chevron-down');
	}
}

$(window).resize(function(){
	if($('#affinage').css('display') === 'block') {
		$('.bloc_filtres').find('.selection.row').css('display', 'block');
	}
});

// Met un fond de couleur quand un critère est choisi
$(".choice.checked").parent().parent().parent().find('.select-choix').find('.fleche').css('color', 'black');
$(".choice.checked").parent().parent().parent().find('.select-choix').css('background-color', '#DCD1BE');
if($(".libellesuppr").hasClass("libellesuplargeurids")) {
	$("#filtre_largeurids").find('.select-choix').find('.fleche').css('color', 'black');
	$("#filtre_largeurids").find('.select-choix').css('background-color', '#DCD1BE');
}
if($(".libellesuppr").hasClass("libellesupprofondeurids")) {
	$("#filtre_profondeurids").find('.select-choix').find('.fleche').css('color', 'black');
	$("#filtre_profondeurids").find('.select-choix').css('background-color', '#DCD1BE');
}
if($(".libellesuppr").hasClass("libellesuphauteurids")) {

	$("#filtre_hauteurids").find('.select-choix').find('.fleche').css('color', 'black');
	$("#filtre_hauteurids").find('.select-choix').css('background-color', '#DCD1BE');
}
if($(".choice.checked").parent().parent().parent().find('.style_check_pro')) {
	$(".choice.checked").parent().parent().parent().find('.style_check_pro').css('background-color', '#ADC0CE');
}




/////// slider range pour les filtres de recherche
$(function() {
	var haut_min = $( "#haut_min" ).val();
	var haut_max = $( "#haut_max" ).val();
	var maxhaut = $( ".max_hauteur" ).val();
	var minhaut = $( ".min_hauteur" ).val();

//si une case est cochée dans le critère, afficher son "libellesuppr"
if($("#filtre_hauteurids").find(".faux_select").find("ul").find("li").find("a").hasClass("checked") === true) {
	var max_init = maxhaut;
}
	// sinon, l'enlever (sinon il se met automatiquement)
	else {
		if((haut_max === "" && haut_min === "") || haut_max > maxhaut) {
			var max_init = maxhaut;
		}
		else {
			var max_init = haut_max;
		}
		//si la valeur choisie est inférieure à la valeur minimale initiale, on garde la valeur initiale
		if(haut_min < minhaut){
			var min_init = minhaut;
		}
		else{
			var min_init = haut_min;
		}

		if(haut_max === maxhaut && haut_min == 0) {
			$(".libellesuppr.libellesuphauteurids").remove();
			$("#filtre_hauteurids").find('.select-choix').css('background-color', 'white');
			$("#filtre_hauteurids").find('.select-choix').find('.fleche').css('color', '#cfcfcf');
		}
	}
	$( ".slider-3-hauteurids" ).slider({
		range:true,
		min: 0,
		max: maxhaut,
		values: [ min_init, max_init ],
		slide: function( event, ui ) {
			$( "#haut_min" ).val( ui.values[ 0 ] + " mm");
			$( "#haut_max" ).val( ui.values[ 1 ] + " mm");
		}
	});

	$( "#haut_min" ).val($( ".slider-3-hauteurids" ).slider( "values", 0 ) + " mm");
	$( "#haut_max" ).val($( ".slider-3-hauteurids" ).slider( "values", 1 ) + " mm");
});
$(function() {
	var larg_min = $( "#larg_min" ).val();
	var larg_max = $( "#larg_max" ).val();
	var maxlarg = $( ".max_largeur" ).val();
	var minlarg = $( ".min_largeur" ).val();

	if($("#filtre_largeurids").find(".faux_select").find("ul").find("li").find("a").hasClass("checked") === true) {
		var max_init = maxlarg;
	}
	else {
		if((larg_max === "" && larg_min === "") || larg_max > maxlarg) {
			var max_init = maxlarg;
		}
		else {
			var max_init = larg_max;
		}

		if(larg_min < minlarg){
			var min_init = minlarg;
		}
		else{
			var min_init = larg_min;
		}

		if(larg_max === maxlarg && larg_min == 0) {
			
			$(".libellesuppr.libellesuplargeurids").remove();
			$("#filtre_largeurids").find('.select-choix').css('background-color', 'white');
			$("#filtre_largeurids").find('.select-choix').find('.fleche').css('color', '#cfcfcf');
		}
	}
	$( ".slider-3-largeurids" ).slider({
		range:true,
		min: 0,
		max: maxlarg,
		values: [ min_init, max_init ],
		slide: function( event, ui ) {
			$( "#larg_min" ).val( ui.values[ 0 ] + " mm");
			$( "#larg_max" ).val( ui.values[ 1 ] + " mm");
		}
	});
	$( "#larg_min" ).val($( ".slider-3-largeurids" ).slider( "values", 0 ) + " mm");
	$( "#larg_max" ).val($( ".slider-3-largeurids" ).slider( "values", 1 ) + " mm");
});
$(function() {
	var profmin = $( "#prof_min" ).val();
	var profmax = $( "#prof_max" ).val();
	var maxprof = $( ".max_profondeur" ).val();
	var minprof = $( ".min_profondeur" ).val();

	if($("#filtre_profondeurids").find(".faux_select").find("ul").find("li").find("a").hasClass("checked") === true) {

		var max_init = maxprof;
	}
	else {

		if((profmax === "" && profmin === "") || profmax > maxprof) {
			var max_init = maxprof;
		}
		else {
			var max_init = profmax;
		}

		if(profmin < minprof){
			var min_init = minprof;
		}
		else{
			var min_init = profmin;
		}

		if(profmax === maxprof && profmin == 0) {
			$(".libellesuppr.libellesupprofondeurids").remove();
			$("#filtre_profondeurids").find('.select-choix').css('background-color', 'white');
			$("#filtre_profondeurids").find('.select-choix').find('.fleche').css('color', '#cfcfcf');
		}
	}
	$( ".slider-3-profondeurids" ).slider({
		range:true,
		min: 0,
		max: maxprof,
		values: [ min_init, max_init ],
		slide: function( event, ui ) {
			$( "#prof_min" ).val( ui.values[ 0 ] + " mm");
			$( "#prof_max" ).val( ui.values[ 1 ] + " mm");
		}
	});
	$( "#prof_min" ).val($( ".slider-3-profondeurids" ).slider( "values", 0 ) + " mm");
	$( "#prof_max" ).val($( ".slider-3-profondeurids" ).slider( "values", 1 ) + " mm");
});


$(function() {
	if($(".criteres_filtration").find(".libellesuppr").css('display') === undefined) {
		$(".criteres_filtration").remove();
	}
});

/// dans le configurateur si on clique sur un article, ça ajoute l'ancre 'config' à l'url de l'article
/// et le btn 'retour config' apparait dans la barre fixe
$(function() {
	var ancre = window.location.hash.replace('#','');
	if(ancre === 'config') {
	
			$( window ).resize(function() {
				console.log($(window).width());
			if($(window).width() < 971){
				$(".btn_retour_config").css('display','inline');
			}
			if($(window).width() < 1590 && $(window).width() > 970){
				$(".btn_retour_config").css('display','none');
			}
			else {
				$(".btn_retour_config").css('display','inline');
			}
		});

		if($(window).width() < 971){
			$(".btn_retour_config").css('display','inline');
		}
		if($(window).width() < 1590 && $(window).width() > 970){
			$(".btn_retour_config").css('display','none');
		}
		else {
			$(".btn_retour_config").css('display','inline');
		}	
	}
});

// pour le menu de la page faq et conseil
function deroule_ss_menu(menu,conseil=null){
	if(conseil !== null){
		$(menu).parent().next('li').toggle();
		$(menu).parent().next('li').find('ul').find('li').toggle();
	}
	else{
		$(menu).parent().nextAll('li').toggle();
	}

	if($(menu).parent().next('li').css('display')==='list-item'){
		$(menu).text('▲');
	}
	else {
		$(menu).text('▼');
	}
}

//tâche 7231
$('#bouton_compte').hover(
	function() {
		$('.popup_compte').show();
	}
);
$('.container.container-main ').hover(
	function() {
		$('.popup_compte').hide();
	}
);


var tb_pathToImage="/javascripts/thickbox/loadingAnimation.gif";function tb_init(e){$(e).click(function(){return tb_show(this.title||this.name||null,this.href||this.alt,this.rel||!1),this.blur(),!1})}function tb_show(e,t,n){try{var i;void 0===document.body.style.maxHeight?($("body","html").css({height:"100%",width:"100%"}),$("html").css("overflow","hidden"),null===document.getElementById("TB_HideSelect")&&($("body").append("<iframe id='TB_HideSelect'></iframe><div id='TB_overlay'></div><div id='TB_window'></div>"),$("#TB_overlay").click(tb_remove))):null===document.getElementById("TB_overlay")&&($("body").append("<div id='TB_overlay'></div><div id='TB_window'></div>"),$("#TB_overlay").click(tb_remove)),tb_detectMacXFF()?$("#TB_overlay").addClass("TB_overlayMacFFBGHack"):$("#TB_overlay").addClass("TB_overlayBG"),null===e&&(e=""),$("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' /></div>"),$("#TB_load").show(),i=-1!==t.indexOf("?")?t.substr(0,t.indexOf("?")):t;var o=/\.jpg$|\.jpeg$|\.png$|\.gif$|\.bmp$/,d=i.toLowerCase().match(o);if(".jpg"==d||".jpeg"==d||".png"==d||".gif"==d||".bmp"==d){if(TB_PrevCaption="",TB_PrevURL="",TB_PrevHTML="",TB_NextCaption="",TB_NextURL="",TB_NextHTML="",TB_imageCount="",TB_FoundURL=!1,n)for(TB_TempArray=$("a[@rel="+n+"]").get(),TB_Counter=0;TB_Counter<TB_TempArray.length&&""===TB_NextHTML;TB_Counter++){TB_TempArray[TB_Counter].href.toLowerCase().match(o);TB_TempArray[TB_Counter].href!=t?TB_FoundURL?(TB_NextCaption=TB_TempArray[TB_Counter].title,TB_NextURL=TB_TempArray[TB_Counter].href,TB_NextHTML="<span id='TB_next'>&nbsp;&nbsp;<a href='#'>suivante &gt;</a></span>"):(TB_PrevCaption=TB_TempArray[TB_Counter].title,TB_PrevURL=TB_TempArray[TB_Counter].href,TB_PrevHTML="<span id='TB_prev'>&nbsp;&nbsp;<a href='#'>&lt; pr�c�dente</a></span>"):(TB_FoundURL=!0,TB_imageCount="[ "+TB_Counter+" sur "+(TB_TempArray.length-1)+" ] &nbsp;&nbsp;&nbsp; ")}imgPreloader=new Image,imgPreloader.onload=function(){imgPreloader.onload=null;var i=tb_getPageSize(),o=i[0]-150,d=i[1]-150,a=imgPreloader.width,r=imgPreloader.height;if(a>o?(r*=o/a,a=o,r>d&&(a*=d/r,r=d)):r>d&&(a*=d/r,r=d,a>o&&(r*=o/a,a=o)),TB_WIDTH=a+30,TB_HEIGHT=r+60,$("#TB_window").remove(),$("#TB_load").remove(),$("body").append("<div id='TB_window'></div>"),$("#TB_window").append("<a href='' id='TB_ImageOff' title='Fermer'><img id='TB_Image' src='"+t+"' width='"+a+"' height='"+r+"' alt='"+e+"'></a><div id='TB_caption'>"+TB_imageCount+e+"<div id='TB_secondLine'>"+TB_PrevHTML+TB_NextHTML+"</div></div><div id='TB_closeWindow'><a href='#' id='TB_closeWindowButton'></a></div>"),$("#TB_closeWindowButton").click(tb_remove),""!==TB_PrevHTML){function T(){return $(document).unbind("click",T)&&$(document).unbind("click",T),$("#TB_window").remove(),$("body").append("<div id='TB_window'></div>"),tb_show(TB_PrevCaption,TB_PrevURL,n),!1}$("#TB_prev").click(T)}if(""!==TB_NextHTML){function _(){return $("#TB_window").remove(),$("body").append("<div id='TB_window'></div>"),tb_show(TB_NextCaption,TB_NextURL,n),!1}$("#TB_next").click(_)}document.onkeydown=function(e){keycode=null==e?event.keyCode:e.which,27==keycode?tb_remove():190==keycode?""!=TB_NextHTML&&(document.onkeydown="",_()):188==keycode&&""!=TB_PrevHTML&&(document.onkeydown="",T())},tb_position(),$("#TB_load").remove(),$("#TB_ImageOff").click(tb_remove),$("#TB_window").css({display:"block"})},imgPreloader.src=t}else{var a=tb_parseQuery(t.replace(/^[^\?]+\??/,"")),r=document.documentElement.clientHeight-20;r>720&&(r=720),TB_WIDTH=1*a.width+30||930,TB_HEIGHT=1*a.height+40||r,ajaxContentW=TB_WIDTH,ajaxContentH=TB_HEIGHT-75,-1!=t.indexOf("TB_iframe")?(urlNoQuery=t.split("TB_"),$("#TB_iframeContent").remove(),"true"!=a.modal?$("#TB_window").append("<div id='TB_title'><div id='TB_ajaxWindowTitle'>"+e+"</div><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton' title='Close'></a></div></div><iframe frameborder='0' hspace='0' src='"+urlNoQuery[0]+"' id='TB_iframeContent' name='TB_iframeContent"+Math.round(1e3*Math.random())+"' onload='tb_showIframe()' style='width:"+(ajaxContentW+29)+"px;height:"+(ajaxContentH+17)+"px;' > </iframe>"):($("#TB_overlay").unbind(),$("#TB_window").append("<iframe frameborder='0' hspace='0' src='"+urlNoQuery[0]+"' id='TB_iframeContent' name='TB_iframeContent"+Math.round(1e3*Math.random())+"' onload='tb_showIframe()' style='width:"+(ajaxContentW+29)+"px;height:"+(ajaxContentH+17)+"px;'> </iframe>"))):"block"!=$("#TB_window").css("display")?"true"!=a.modal?$("#TB_window").append("<div id='TB_title'><div id='TB_ajaxWindowTitle'>"+e+"</div><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton'></a></div></div><div id='TB_ajaxContent' style='width:"+ajaxContentW+"px;height:"+ajaxContentH+"px'></div>"):($("#TB_overlay").unbind(),$("#TB_window").append("<div id='TB_ajaxContent' class='TB_modal' style='width:"+ajaxContentW+"px;height:"+ajaxContentH+"px;'></div>")):($("#TB_ajaxContent")[0].style.width=ajaxContentW+"px",$("#TB_ajaxContent")[0].style.height=ajaxContentH+"px",$("#TB_ajaxContent")[0].scrollTop=0,$("#TB_ajaxWindowTitle").html(e)),$("#TB_closeWindowButton").click(tb_remove),-1!=t.indexOf("TB_inline")?($("#TB_ajaxContent").append($("#"+a.inlineId).children()),$("#TB_window").unload(function(){$("#"+a.inlineId).append($("#TB_ajaxContent").children())}),tb_position(),$("#TB_load").remove(),$("#TB_window").css({display:"block"})):-1!=t.indexOf("TB_iframe")?(tb_position(),$.browser.safari&&($("#TB_load").remove(),$("#TB_window").css({display:"block"}))):$("#TB_ajaxContent").load(t+="&random="+(new Date).getTime(),function(){tb_position(),$("#TB_load").remove(),tb_init("#TB_ajaxContent a.thickbox"),$("#TB_window").css({display:"block"})}),1==a.notitle&&($("#TB_title")[0].style.visibility="hidden",$("#TB_title")[0].style.position="absolute",$("#TB_ajaxContent")[0].style.top="0px",$("#TB_ajaxContent")[0].style.overflow="hidden")}a.modal||(document.onkeyup=function(e){keycode=null==e?event.keyCode:e.which,27==keycode&&tb_remove(),keydown(keycode)})}catch(e){}}function tb_showIframe(){$("#TB_load").remove(),$("#TB_window").css({display:"block"})}function tb_remove(){return $("#TB_imageOff").unbind("click"),$("#TB_closeWindowButton").unbind("click"),$("#TB_window").fadeOut("fast",function(){$("#TB_window,#TB_overlay,#TB_HideSelect").trigger("unload").unbind().remove()}),$("#TB_load").remove(),void 0===document.body.style.maxHeight&&($("body","html").css({height:"auto",width:"auto"}),$("html").css("overflow","")),document.onkeydown="",document.onkeyup="",!1}function tb_position(){$("#TB_window").css({marginLeft:"-"+parseInt(TB_WIDTH/2,10)+"px",width:TB_WIDTH+"px"}),$("#TB_window").css({marginTop:"-"+parseInt(TB_HEIGHT/2,10)+"px"})}function tb_parseQuery(e){var t={};if(!e)return t;for(var n=e.split(/[;&]/),i=0;i<n.length;i++){var o=n[i].split("=");if(o&&2==o.length){var d=unescape(o[0]),a=unescape(o[1]);a=a.replace(/\+/g," "),t[d]=a}}return t}function tb_getPageSize(){var e=document.documentElement,t=window.innerWidth||self.innerWidth||e&&e.clientWidth||document.body.clientWidth,n=window.innerHeight||self.innerHeight||e&&e.clientHeight||document.body.clientHeight;return arrayPageSize=[t,n],arrayPageSize}function tb_detectMacXFF(){var e=navigator.userAgent.toLowerCase();if(-1!=e.indexOf("mac")&&-1!=e.indexOf("firefox"))return!0}$(document).ready(function(){tb_init("a.thickbox, area.thickbox, input.thickbox"),imgLoader=new Image,imgLoader.src=tb_pathToImage});
function submitrappel_immediat() {
    var e = document.getElementById("rappel_immediat_telephone").value,
        t = document.getElementById("rappel_immediat_pays").value,
        n = document.getElementById("rappel_immediat_nom").value,
        o = document.getElementById("cgv").checked,
        a = document.getElementById("gclid_field").value;
    if (document.getElementById("rappel_immediat_error").style.display = "none", "" != e && "" != n && o) {
        var l = new Array;
        l.gclid = a, l.rappelinst_tel = e, l.rappelinst_nom = n, l.rappelinst_mail = document.getElementById("rappel_immediat_mail").value, l.rappelinst_pays = t, l.msg = "Formulaire Rappel immediat sortie site", l.url = document.URL, document.getElementById("rappel_immediat_form").innerHTML = '<img src="/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif" align="absmiddle">', document.getElementById("rappel_immediat").getElementsByTagName("a")[2].style.display = "none", document.getElementById("rappel_immediat").getElementsByTagName("a")[1].style.display = "none", document.getElementById("rappel_immediat_cgv").style.display = "none", postcontact(l, rappel_immediat_cb)
    } else document.getElementById("rappel_immediat_error").style.display = "block"
}

function rappel_immediat_cb(e) {
    document.getElementById("rappel_immediat_form").innerHTML = e.responseText, setTimeout("hidebarform('rappel_immediat')", 5e3)
}

function __doPostBack(e, t) {
    theForm.onsubmit && 0 == theForm.onsubmit() || (theForm.__EVENTTARGET.value = e, theForm.__EVENTARGUMENT.value = t, theForm.submit())
}

function litnombre(aChaineNombre) {
    for (var li = 0, lChaine = "" + aChaineNombre; li < eval(lChaine.length);)(lChaine.charCodeAt(li) < 48 || lChaine.charCodeAt(li) > 57) && (lChaine = lChaine.substring(0, li) + lChaine.substring(li + 1, lChaine.length), li--), li++;
    return lChaine
}

function ouvrircaract() {
    document.getElementById("caract_complet2_block") && (document.getElementById("caract_complet2_block").style.display = "block"), document.getElementById("caract_complet2_none") && (document.getElementById("caract_complet2_none").style.display = "none"), document.getElementById("caract_complet3_block") && (document.getElementById("caract_complet3_block").style.display = "block"), document.getElementById("caract_complet3_none") && (document.getElementById("caract_complet3_none").style.display = "none"), document.getElementById("caract_complet4_block") && (document.getElementById("caract_complet4_block").style.display = "block"), document.getElementById("caract_complet4_none") && (document.getElementById("caract_complet4_none").style.display = "none"), document.getElementById("caract_complet5_block") && (document.getElementById("caract_complet5_block").style.display = "block"), document.getElementById("caract_complet5_none") && (document.getElementById("caract_complet5_none").style.display = "none")
}

function litnombreCompte(aChaineNombre) {
    for (var li = 0, lNb, lChaine = "" + aChaineNombre; li < eval(lChaine.length);)(lChaine.charCodeAt(li) < 48 || lChaine.charCodeAt(li) > 57) && (lChaine.charCodeAt(li) >= 65 || lChaine.charCodeAt(li) <= 90 ? (lNb = lChaine.charCodeAt(li) - 64, lNb > 9 && (lNb -= 9), lNb > 9 && (lNb -= 8), lChar = "" + lNb, lChaine = lChaine.substring(0, li) + lChar + lChaine.substring(li + 1, lChaine.length)) : lChaine.charCodeAt(li) >= 97 || lChaine.charCodeAt(li) <= 122 ? (lNb = lChaine.charCodeAt(li) - 96, lNb > 9 && (lNb -= 9), lNb > 9 && (lNb -= 8), lChar = "" + lNb, lChaine = lChaine.substring(0, li) + lChar + lChaine.substring(li + 1, lChaine.length)) : (lChaine = lChaine.substring(0, li) + lChaine.substring(li + 1, lChaine.length), li--)), li++;
    return lChaine
}

function litnombreIBAN(aChaineNombre) {
    for (var li = 0, lNb, lChaine = "" + aChaineNombre, lChaineRes = ""; li < eval(lChaine.length);) lChaine.charCodeAt(li) < 48 || lChaine.charCodeAt(li) > 57 ? lChaine.charCodeAt(li) >= 65 || lChaine.charCodeAt(li) <= 90 ? (lNb = lChaine.charCodeAt(li) - 55, lChar = "" + lNb, lChaineRes += lChar) : (lChaine.charCodeAt(li) >= 97 || lChaine.charCodeAt(li) <= 122) && (lNb = lChaine.charCodeAt(li) - 87, lChar = "" + lNb, lChaineRes += lChar) : lChaineRes += lChaine.substring(li, li + 1), li++;
    return lChaineRes
}

function fCalcRib(e, t, n) {
    var o, a, l, c;
    return o = litnombre(e), a = litnombre(t), l = litnombreCompte(n), 5 != o.length ? 0 : 5 != a.length ? 0 : l.length > 11 ? 0 : 0 == (c = (8 * parseFloat(o) % 97 + (97 - 15 * parseFloat(a) % 97) + (97 - 3 * parseFloat(l) % 97)) % 97) ? 97 : c
}

function fCalcIBAN(acodepays, aRIB) {
    var lcodepays, lRIB, lConcat, lNb, lIBAN, lCodeNum, lCodeStr, li, lRetenue, lCle, lNbInterm, lStrInterm;
    if (lRIB = litnombreIBAN(aRIB), lcodepays = litnombreIBAN(acodepays), 2 != acodepays.length || 4 != lcodepays.length) return "";
    for (lConcat = lRIB + lcodepays + "00", li = 0, lRetenue = ""; li < eval(lConcat.length);) lStrInterm = lRetenue + lConcat.substring(li, li + 9), lNbInterm = parseFloat(lStrInterm), lCle = lStrInterm % 97, lRetenue = "" + lCle, li += 9;
    return lCodeNum = 98 - lCle % 97, lCodeStr = lCodeNum < 10 ? "0" + lCodeNum : "" + lCodeNum, lIBAN = acodepays + lCodeStr + aRIB, lIBAN
}

function fCopierRib(e, t, n, o) {
    var a, l, c, i, m;
    return a = litnombre(e), l = litnombre(t), c = n, i = litnombre(o), 5 != a.length ? 0 : 5 != l.length ? 0 : 11 != c.length ? 0 : i.length > 2 || 0 == i.length ? 0 : (m = parseFloat(i) % 100) < 1 || m > 97 ? 0 : a + l + c + (i < 10 ? "0" + m : "" + m)
}

function cliqueaffiliation() {
    document.rib.clerib.value = fCalcRib(document.rib.codebanque.value, document.rib.codeguichet.value, document.rib.nocompte.value), document.rib.submit()
}

function arrondir(e, t) {
    if (isNaN(e)) return "";
    if (0 == t) return (a = Math.round(e)).toString();
    if (t > 0) {
        if (document.all);
        var n = Math.pow(10, t),
            o = (a = (a = Math.round(e * n)).toString()).length - t;
        a = o >= 0 ? a.substring(0, o) + "." + a.substring(o) : "0." + formater(a, t)
    } else {
        var a;
        n = Math.pow(10, -t);
        a = (a = Math.round(e / n) * n).toString()
    }
    return null == e.signe_decimal ? a : a.replace(".", e.signe_decimal)
}

function recommander(e) {
    window.open("recommander.php?artid=" + e, "Recommander", "Width=600, Height=450, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no")
}

function agrandir(e, t, n) {
    var o = 3.3 * t,
        a = 2.7 * n;
    window.open("photos.php?artid=" + e, "Photos", "Width=" + o + ", Height=" + a + ", toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no")
}

function recalculer() {
    document.formu.submit()
}

function etape2() {
    var e = document.getElementsByName("hostpanier")[0].value;
    document.formu.action = "" != e ? e + "/recapitulatif.html" : "recapitulatif.html", document.formu.submit()
}

function continuer() {
    window.location = "cde1.php?action=delpanier_complet"
}

function ferme(e) {
    document.getElementById(e).style.display = "none"
}

function ouvre(e, t) {
    "" != e && ferme(e), document.getElementById(t).style.display = "block"
}

function voir(e) {
    document.getElementById(e).style.display = "block"
}

function cacher(e) {
    document.getElementById(e).style.display = "none"
}

function MM_preloadImages() {
    var e = document;
    if (e.images) {
        e.MM_p || (e.MM_p = new Array);
        var t, n = e.MM_p.length,
            o = MM_preloadImages.arguments;
        for (t = 0; t < o.length; t++) 0 != o[t].indexOf("#") && (e.MM_p[n] = new Image, e.MM_p[n++].src = o[t])
    }
}

function changeImgAnnonce(e, t, n) {
    2 == n && (e == t ? (document.getElementById(e).style.display = "none", document.getElementById("suivant" + e).style.display = "none", document.getElementById("1").style.display = "block", document.getElementById("suivant1").style.display = "block") : (document.getElementById(e).style.display = "none", document.getElementById("suivant" + e).style.display = "none", e += 1, document.getElementById(e).style.display = "block", document.getElementById("suivant" + e).style.display = "block"))
}

function changeImgAnnoncenew(e) {
    document.getElementById("diapo").innerHTML = "<img src=" + e + " name=big>"
}

function toggle(e, t) {
    var n = document.getElementById(e);
    "block" != n.style.display ? (n.style.display = "block", document.getElementById(t).className = "folderOpen") : (n.style.display = "none", document.getElementById(t).className = "folder")
}

function vider(e) {
    "recherche" != e.value && "Adresse mail" != e.value && "passe" != e.value && "Votre e-mail" != e.value && "Mot de passe" != e.value || (e.value = "")
}

function viderpanier() {
    window.location = "cde1.php?action=delpanier_complet"
}

function changeImgAnnonce2(e) {
    document.getElementById("big").src = e
}

function clic_onglet(e) {
    switch (e) {
        case "cdc":
            document.getElementById("promo").style.color = "#808080", document.getElementById("top_vente").style.color = "#808080", document.getElementById("nouveaute").style.color = "#808080", document.getElementById("cdc").style.color = "#A5CB32", document.getElementById("tops_contents").className = "tops_contents0", affiche(0);
            break;
        case "promo":
            document.getElementById("cdc").style.color = "#808080", document.getElementById("top_vente").style.color = "#808080", document.getElementById("nouveaute").style.color = "#808080", document.getElementById("promo").style.color = "#A5CB32", document.getElementById("tops_contents").className = "tops_contents1", affiche(1);
            break;
        case "top_vente":
            document.getElementById("cdc").style.color = "#808080", document.getElementById("promo").style.color = "#808080", document.getElementById("nouveaute").style.color = "#808080", document.getElementById("top_vente").style.color = "#A5CB32", document.getElementById("tops_contents").className = "tops_contents2", affiche(2);
            break;
        case "nouveaute":
            document.getElementById("cdc").style.color = "#808080", document.getElementById("promo").style.color = "#808080", document.getElementById("top_vente").style.color = "#808080", document.getElementById("nouveaute").style.color = "#A5CB32", document.getElementById("tops_contents").className = "tops_contents3", affiche(3)
    }
}

function montre(e) {
    for (var t = document.getElementById(e), n = document.getElementsByTagName("dd"), o = 0; o < n.length; o++) document.getElementById(n[o].id).style.display = "none";
    t && (t.style.display = "block")
}

function affiche(e) {
    for (i = 0; i < 4; i++) valeur = "calque" + i, document.getElementById(valeur).style.display = "none";
    valeur = "calque" + e, document.getElementById(valeur).style.display = "block"
}

function poplink(e) {
    var t = '<img src="' + e + '">';
    document.getElementById("topdecklink").style.left = MouseX + 5 + "px", document.getElementById("topdecklink").style.top = MouseY + 5 + "px", document.getElementById("topdecklink").innerHTML = t, document.getElementById("topdecklink").style.display = "block"
}

function get_mouse(e) {
    var t = "Net" == navigator.appName.substring(0, 3) ? e.pageX : event.x + document.documentElement.scrollLeft,
        n = "Net" == navigator.appName.substring(0, 3) ? e.pageY : event.y + document.documentElement.scrollTop;
    skn.left = t - 15 + "px", skn.top = n + 20 + "px"
}

function killlink() {
    document.getElementById("topdecklink").style.display = "none"
}

function affiche_conseil(e) {
    var t = document.getElementById(e);
    "block" != t.style.display ? t.style.display = "block" : t.style.display = "none"
}

function ferme_caract(e) {
    document.getElementById("caract_complet" + e + "_block").style.display = "none", document.getElementById("caract_complet" + e + "_none").style.display = "block"
}

function ouvre_caract(e) {
    document.getElementById("caract_complet" + e + "_none").style.display = "none", document.getElementById("caract_complet" + e + "_block").style.display = "block"
}

function ajoute1(e) {
    e ? document.getElementById(e).value = parseFloat(document.getElementById(e).value) + 1 : document.getElementById("qte").value = parseFloat(document.getElementById("qte").value) + 1
}

function enleve1(e) {
    e ? parseFloat(document.getElementById(e).value) > 1 && (document.getElementById(e).value = parseFloat(document.getElementById(e).value) - 1) : parseFloat(document.getElementById("qte").value) > 1 && (document.getElementById("qte").value = parseFloat(document.getElementById("qte").value) - 1)
}

function accsrc(e) {
    if (0 == e || "" == e) return document.getElementsByName("modele_cave_fab")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", document.getElementsByName("modele_cave_fab")[0].options[0].innerHTML = "Modèle avec référence constructeur", document.getElementsByName("modele_cave_fab_xs")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci_xs")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci_xs")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", void(document.getElementsByName("modele_cave_fab_xs")[0].options[0].innerHTML = "Modèle avec référence constructeur");
    document.getElementsByName("modele_cave_fab")[0].disabled = "", document.getElementsByName("modele_cave_aci")[0].disabled = "", document.getElementsByName("modele_cave_aci")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", document.getElementsByName("modele_cave_fab")[0].options[0].innerHTML = "Modèle avec référence constructeur", document.getElementsByName("modele_cave_fab_xs")[0].disabled = "", document.getElementsByName("modele_cave_aci_xs")[0].disabled = "", document.getElementsByName("modele_cave_aci_xs")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", document.getElementsByName("modele_cave_fab_xs")[0].options[0].innerHTML = "Modèle avec référence constructeur", ajaxrequest("/accsrc.php?mid=" + e, accsrccb)
}

function accsrc_lg(e) {
    if (0 == e || "" == e) return document.getElementsByName("modele_cave_fab_lg")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci_lg")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci_lg")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", void(document.getElementsByName("modele_cave_fab_lg")[0].options[0].innerHTML = "Modèle avec référence constructeur");
    document.getElementsByName("modele_cave_fab_lg")[0].disabled = "", document.getElementsByName("modele_cave_aci_lg")[0].disabled = "", document.getElementsByName("modele_cave_aci_lg")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", document.getElementsByName("modele_cave_fab_lg")[0].options[0].innerHTML = "Modèle avec référence constructeur", ajaxrequest("/accsrc.php?mid=" + e, accsrccb_lg)
}

function accsrc_sm(e) {
    if (0 == e || "" == e) return document.getElementsByName("modele_cave_fab_sm")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci_sm")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci_sm")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", void(document.getElementsByName("modele_cave_fab_sm")[0].options[0].innerHTML = "Modèle avec référence constructeur");
    document.getElementsByName("modele_cave_fab_sm")[0].disabled = "", document.getElementsByName("modele_cave_aci_sm")[0].disabled = "", document.getElementsByName("modele_cave_aci_sm")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", document.getElementsByName("modele_cave_fab_sm")[0].options[0].innerHTML = "Modèle avec référence constructeur", ajaxrequest("/accsrc.php?mid=" + e, accsrccb_sm)
}

function accsrc_xs(e) {
    if (0 == e || "" == e) return document.getElementsByName("modele_cave_fab_xs")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci_xs")[0].disabled = "disabled", document.getElementsByName("modele_cave_aci_xs")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", void(document.getElementsByName("modele_cave_fab_xs")[0].options[0].innerHTML = "Modèle avec référence constructeur");
    document.getElementsByName("modele_cave_fab_xs")[0].disabled = "", document.getElementsByName("modele_cave_aci_xs")[0].disabled = "", document.getElementsByName("modele_cave_aci_xs")[0].options[0].innerHTML = "Modèle avec référence MON ESPACE CUISSON", document.getElementsByName("modele_cave_fab_xs")[0].options[0].innerHTML = "Modèle avec référence constructeur", ajaxrequest("/accsrc.php?mid=" + e, accsrccb_xs)
}

function accsrccb(e) {
    var t = e.responseXML.documentElement,
        n = document.getElementsByName("modele_cave_aci")[0],
        o = n.options[0];
    if (n.innerHTML = "", n.style.display = "block", document.getElementsByName("modele_cave_aci")[0].options[0] = o, t.getElementsByTagName("artcode")[0])
        for (i = 0; i < t.getElementsByTagName("artcode").length; i++) {
            var a = t.getElementsByTagName("artcode")[i].firstChild.nodeValue,
                l = t.getElementsByTagName("artcode")[i].getAttribute("url");
            document.getElementsByName("modele_cave_aci")[0].options[i + 1] = new Option(a, l)
        }
    var c = document.getElementsByName("modele_cave_fab")[0];
    o = c.options[0];
    if (c.innerHTML = "", c.style.display = "block", document.getElementsByName("modele_cave_fab")[0].options[0] = o, t.getElementsByTagName("ref")[0])
        for (i = 0; i < t.getElementsByTagName("ref").length; i++) {
            a = t.getElementsByTagName("ref")[i].firstChild.nodeValue, l = t.getElementsByTagName("ref")[i].getAttribute("url");
            document.getElementsByName("modele_cave_fab")[0].options[i + 1] = new Option(a, l)
        }
}

function accsrccb_lg(e) {
    var t = e.responseXML.documentElement,
        n = document.getElementsByName("modele_cave_aci_lg")[0],
        o = n.options[0];
    if (n.innerHTML = "", n.style.display = "block", document.getElementsByName("modele_cave_aci_lg")[0].options[0] = o, t.getElementsByTagName("artcode")[0])
        for (i = 0; i < t.getElementsByTagName("artcode").length; i++) {
            var a = t.getElementsByTagName("artcode")[i].firstChild.nodeValue,
                l = t.getElementsByTagName("artcode")[i].getAttribute("url");
            document.getElementsByName("modele_cave_aci_lg")[0].options[i + 1] = new Option(a, l)
        }
    var c = document.getElementsByName("modele_cave_fab_lg")[0];
    o = c.options[0];
    if (c.innerHTML = "", c.style.display = "block", document.getElementsByName("modele_cave_fab_lg")[0].options[0] = o, t.getElementsByTagName("ref")[0])
        for (i = 0; i < t.getElementsByTagName("ref").length; i++) {
            a = t.getElementsByTagName("ref")[i].firstChild.nodeValue, l = t.getElementsByTagName("ref")[i].getAttribute("url");
            document.getElementsByName("modele_cave_fab_lg")[0].options[i + 1] = new Option(a, l)
        }
}

function accsrccb_sm(e) {
    var t = e.responseXML.documentElement,
        n = document.getElementsByName("modele_cave_aci_sm")[0],
        o = n.options[0];
    if (n.innerHTML = "", n.style.display = "block", document.getElementsByName("modele_cave_aci_sm")[0].options[0] = o, t.getElementsByTagName("artcode")[0])
        for (i = 0; i < t.getElementsByTagName("artcode").length; i++) {
            var a = t.getElementsByTagName("artcode")[i].firstChild.nodeValue,
                l = t.getElementsByTagName("artcode")[i].getAttribute("url");
            document.getElementsByName("modele_cave_aci_sm")[0].options[i + 1] = new Option(a, l)
        }
    var c = document.getElementsByName("modele_cave_fab_sm")[0];
    o = c.options[0];
    if (c.innerHTML = "", c.style.display = "block", document.getElementsByName("modele_cave_fab_sm")[0].options[0] = o, t.getElementsByTagName("ref")[0])
        for (i = 0; i < t.getElementsByTagName("ref").length; i++) {
            a = t.getElementsByTagName("ref")[i].firstChild.nodeValue, l = t.getElementsByTagName("ref")[i].getAttribute("url");
            document.getElementsByName("modele_cave_fab_sm")[0].options[i + 1] = new Option(a, l)
        }
}

function accsrccb_xs(e) {
    var t = e.responseXML.documentElement,
        n = document.getElementsByName("modele_cave_aci_xs")[0],
        o = n.options[0];
    if (n.innerHTML = "", n.style.display = "block", document.getElementsByName("modele_cave_aci_xs")[0].options[0] = o, t.getElementsByTagName("artcode")[0])
        for (i = 0; i < t.getElementsByTagName("artcode").length; i++) {
            var a = t.getElementsByTagName("artcode")[i].firstChild.nodeValue,
                l = t.getElementsByTagName("artcode")[i].getAttribute("url");
            document.getElementsByName("modele_cave_aci_xs")[0].options[i + 1] = new Option(a, l)
        }
    var c = document.getElementsByName("modele_cave_fab_xs")[0];
    o = c.options[0];
    if (c.innerHTML = "", c.style.display = "block", document.getElementsByName("modele_cave_fab_xs")[0].options[0] = o, t.getElementsByTagName("ref")[0])
        for (i = 0; i < t.getElementsByTagName("ref").length; i++) {
            a = t.getElementsByTagName("ref")[i].firstChild.nodeValue, l = t.getElementsByTagName("ref")[i].getAttribute("url");
            document.getElementsByName("modele_cave_fab_xs")[0].options[i + 1] = new Option(a, l)
        }
}

function accsrc_submit() {
    var e = document.getElementsByName("modele_cave_aci")[0];
    if (e.selectedIndex > 0) document.location = e.options[e.selectedIndex].value;
    else {
        var t = document.getElementsByName("modele_cave_fab")[0];
        t.selectedIndex > 0 ? document.location = t.options[t.selectedIndex].value : 0 == document.getElementsByName("marque_clayette")[0].selectedIndex ? alert(document.getElementById("access_index").dataset.msgbrand) : alert(document.getElementById("access_index").dataset.msgmodel)
    }
}

function accsrc_lg_submit() {
    var e = document.getElementsByName("modele_cave_aci_lg")[0];
    if (e.selectedIndex > 0) document.location = e.options[e.selectedIndex].value;
    else {
        var t = document.getElementsByName("modele_cave_fab_lg")[0];
        t.selectedIndex > 0 ? document.location = t.options[t.selectedIndex].value : 0 == document.getElementsByName("marque_clayette_lg")[0].selectedIndex ? alert(document.getElementById("access_index_lg").dataset.msgbrand) : alert(document.getElementById("access_index_lg").dataset.msgmodel)
    }
}

function accsrc_sm_submit() {
    var e = document.getElementsByName("modele_cave_aci_sm")[0];
    if (e.selectedIndex > 0) document.location = e.options[e.selectedIndex].value;
    else {
        var t = document.getElementsByName("modele_cave_fab_sm")[0];
        t.selectedIndex > 0 ? document.location = t.options[t.selectedIndex].value : 0 == document.getElementsByName("marque_clayette_sm")[0].selectedIndex ? alert(document.getElementById("access_index_sm").dataset.msgbrand) : alert(document.getElementById("access_index_sm").dataset.msgmodel)
    }
}

function accsrc_xs_submit() {
    var e = document.getElementsByName("modele_cave_aci_xs")[0];
    if (e.selectedIndex > 0) document.location = e.options[e.selectedIndex].value;
    else {
        var t = document.getElementsByName("modele_cave_fab_xs")[0];
        t.selectedIndex > 0 ? document.location = t.options[t.selectedIndex].value : 0 == document.getElementsByName("marque_clayette_xs")[0].selectedIndex ? alert(document.getElementById("access_index_xs").dataset.msgbrand) : alert(document.getElementById("access_index_xs").dataset.msgmodel)
    }
}

function popdevis(e,client=null) {
    ///un alert pour les clients, si pas cookie admin (tache 7095) :
    if(client!==null){
        ajaxrequest("maildevis.php?id=" + e, function(f) {
            alert(f.responseText.match(/<texte_devis>(.+)<\/texte_devis>/)[1]);
        })
    }
    ///une fenetre spéciale si cookie admin :
    else{
        var t = "maildevis.php?id=" + e,
        n = document.getElementsByName("nbpaiements");
        if (n.length > 0 && n[0].value > 0) {
            var o = [],
            a = parseInt(n[0].value);
            for (i = 0; i < a; i++) {
                var l = {};
                l.value = document.getElementsByName("paiement[" + i + "]")[0].value, l.moyen = document.getElementsByName("moyens[" + i + "]")[0].value, l.delais = document.getElementsByName("delais[" + i + "]")[0].value, l.date = document.getElementsByName("date[" + i + "]")[0].value, o.push(l)
            }
            t += "&paiements=" + JSON.stringify(o)
        }

        window.open(t, "", "toolbar=no, location=no, directories=no, status=no, scrollbars=yes, resizable=no, copyhistory=yes, width=900, height=500")
    }
}

function devisajax(e) {
    ajaxrequest("maildevis.php?id=" + e, function(e) {
        if (document.getElementById("devismsg")) {
            var t = e.responseText.match(/<p\b[^>]*>([\s\S]+)<\/p>/i)[1];
            document.getElementById("devismsg").innerHTML = t
        }
    })
}

function waitsrc() {
    "" != document.getElementsByName("catid")[0].value ? (document.getElementById("rech_btn").className = "wait_src", document.getElementById("rech_btn").innerHTML = '<img src="configurations/www.mon-espace-cuisson.com/images/templates/ajax_wait.gif" width="27">', document.getElementById("txtrecherche").innerHTML = "Recherche en cours", document.forms.rechercheexpert.submit()) : alert("Vous devez choisir l'usage de votre cave")
}

function openfooter() {
    var e = document.getElementById("footerlinks").getElementsByTagName("li");
    for (i = 0; i < e.length; i++) "hidden" == e[i].className && (e[i].className = "");
    document.getElementById("plusfooter").style.display = "none"
}

function testmail(e) {
    return !!new RegExp("^[a-z0-9]+([_|.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|.|-]{1}[a-z0-9]+)*[.]{1}[a-z]{2,6}$", "i").test(e)
}

function upddecompte() {
    if (document.getElementById("vfdecompte")) {
        dn.setTime(dn.getTime() + 1e3);
        var e = d.getTime() - dn.getTime();
        e <= 1e3 && location.reload(), document.getElementById("vfdecompte").innerHTML = conversion_seconde_heure(e / 1e3), setTimeout("upddecompte()", 1e3)
    }
}

function conversion_seconde_heure(e) {
    var t = e,
        n = "",
        o = Math.floor(t / 86400);
    t -= 24 * o * 3600;
    var a = Math.floor(t / 3600);
    t -= 3600 * a;
    var l = Math.floor(t / 60),
        c = t -= 60 * l;
    return o > 0 && (n = n + o + dstr.days + " "), a > 0 && (n = n + a + dstr.hours + " "), l > 0 && (n = n + l + dstr.minutes + " "), c > 0 && (n = n + Math.round(c) + dstr.seconds + " "), n
}

function nbonly(e, t, n) {
    !e && window.event && (e = window.event), null == t && (t = !1), e.keyCode > 0 && (e.keyCode < 48 || e.keyCode > 57) && 8 != e.keyCode && 9 != e.keyCode && (!t || 46 != e.keyCode || n.value.indexOf(".") > 0) && (e.returnValue = !1, e.cancelBubble = !0), 0 != e.which && (e.which < 48 || e.which > 57) && 8 != e.which && (!t || 46 != e.which || n.value.indexOf(".") > 0) && (e.preventDefault(), e.stopPropagation())
}

function setCookie(e, t, n) {
    var o;
    if (n) {
        var a = new Date;
        a.setTime(a.getTime() + 24 * n * 60 * 60 * 1e3), o = "; expires=" + a.toGMTString()
    } else o = "";
    return document.cookie = e + "=" + t + o + "; path=/", !0
}

function getCookie(e) {
    for (var t = e + "=", n = document.cookie.split(";"), o = 0; o < n.length; o++) {
        for (var a = n[o];
            " " == a.charAt(0);) a = a.substring(1);
        if (0 == a.indexOf(t)) return a.substring(t.length, a.length)
    }
    return ""
}

function movefsmsg() {
    var e = document.getElementById("fsmsg").offsetHeight,
        t = document.getElementById("fsmsg").offsetWidth,
        n = window.innerHeight,
        o = window.innerWidth,
        a = n / 2 - e / 2 + document.documentElement.scrollTop,
        l = o / 2 - t / 2;
    document.getElementById("fsmsg").style.top = a + "px", document.getElementById("fsmsg").style.left = l + "px"
}

function window_size() {
    var e = new Array;
    return void 0 !== window.innerWidth ? (e.width = window.innerWidth, e.height = window.innerHeight) : void 0 !== document.documentElement && void 0 !== document.documentElement.clientWidth && 0 != document.documentElement.clientWidth ? (e.width = document.documentElement.clientWidth, e.height = document.documentElement.clientHeight) : (e.width = document.getElementsByTagName("body")[0].clientWidth, e.height = document.getElementsByTagName("body")[0].clientHeight), e
}
window.onload = montre;
var timerpc = new Array;

function popup_header(e, t) {
    if (t)
        for (h in hideClass("sous-menu"), hideClass("sous-sous-menu"), clearTimeout(timerpc[e]), $("." + e).removeClass("hidden"), timerpc[e] = void 0, timerpc) h != e && $("." + h).addClass("hidden");
    else timerpc[e] = setTimeout(function() {
        $("." + e).addClass("hidden")
    })

}

function reloadcache() {
    window.onpageshow = function(e) {
        e.persisted && (document.body.style.display = "none", location.reload())
    }
}

function htmlalert(e) {
    var t = document.createElement("div");
    t.id = Date.now(), t.className = "wait";
    var n = document.createElement("div");
    n.className = "bg";
    var o = document.createElement("div");
    o.className = "msg";
    var a = document.createElement("div");
    a.className = "ok", a.innerHTML = "OK", a.onclick = function() {
        removealert(t.id)
    }, o.innerHTML = e, t.appendChild(n), o.appendChild(a), t.appendChild(o), document.body.appendChild(t)
}

function removealert(e) {
    var t = document.getElementById(e);
    t && t.parentNode.removeChild(t)
}

function isMobile() {
    return /iphone|ipad|ipod|android|iemobile|windows phone|blackberry|BB10|opera mini|sailfish/i.test(navigator.userAgent.toLowerCase())
}

function mobile_cookie(e) {
    setCookie("mobile", e = parseInt(e), 30)
}

function comptePopupEmailCheck(e) {
    var t = document.getElementById("popup_email").value;
    $.get("/ajax/panier/checkEmail.php", {
        email: t
    }, function(n) {
        "no" !== n ? (document.querySelector("#popup_register_error").innerHTML = "en" === e ? "Your e-mail is already associated with an account" : "Cet e-mail est déjà associé à un compte", document.getElementById("popup_email_log").value = t) : document.forms.popupregister.submit()
    })
}

function affichageFirst(e) {
    $("#acces_panier_title_" + e).show(), $("#scroll_accessoires_" + e).show(), $("#scrollbtnsparent_" + e).show(), $("#text_vignscroll_" + e).css("display", "table"), $("#chev_left_" + e).removeClass("fa-chevron-down").addClass("fa-chevron-up"), $("#chev_right_" + e).removeClass("fa-chevron-down").addClass("fa-chevron-up")
}

function next_part() {
    $("#register_part_pro").show(), $("#register_next_btn1").hide()
}

function next_mail_devis() {
    $("#register_part_pro").show(), $("#register_next_btn1").hide()
}

function nextpro() {
    registerResetPart(), $("#register_part_pro p.pro").addClass("btn_pro_hover"), $("#register_part_pro p.part").removeClass("btn_part_hover"), $("#register_coordonnees_part").hide(), $("#register_conseils").hide(), $("#register_coordonnees_pro").show(), $("#register_newsletter").show(), $("#register_btn_valide").css("display", "block"), $("span.mentions_pro").css("visibility", "visible"), $("#propart").val(2)
}

function nextpart() {
    registerResetPro(), $("#register_part_pro p.part").addClass("btn_part_hover"), $("#register_part_pro p.pro").removeClass("btn_pro_hover"), $("#register_coordonnees_pro").hide(), $("#register_coordonnees_part").show(), /*$("#register_conseils").show(),*/ $("#register_newsletter").show(), $("#register_btn_valide").css("display", "block"), $("#propart").val(1), $("#conseils1").prop("checked", !0)
}

function registerResetPro() {
    document.getElementById("civilite1pro").checked = !1, document.getElementById("civilite2pro").checked = !1, document.getElementsByName("nompro")[0].value = "", document.getElementsByName("prenompro")[0].value = "", document.getElementsByName("raisonsociale")[0].value = "", document.getElementsByName("tvaintracom")[0].value = "", document.getElementsByName("adressepro")[0].value = "", document.getElementsByName("codepostalpro")[0].value = "", document.getElementsByName("villepro")[0].value = "", document.getElementsByName("paysidpro")[0].value = "", document.getElementsByName("telephonepro")[0].value = "", document.getElementsByName("connupro")[0].value = "", $("#paysid").val(57)
}

function registerResetPart() {
    document.getElementById("civilite1").checked = !1, document.getElementById("civilite2").checked = !1, document.getElementsByName("nom")[0].value = "", document.getElementsByName("prenom")[0].value = "", document.getElementsByName("adresse")[0].value = "", document.getElementsByName("codepostal")[0].value = "", document.getElementsByName("ville")[0].value = "", document.getElementsByName("paysid")[0].value = "", document.getElementsByName("telephone")[0].value = "", document.getElementsByName("connu")[0].value = "", $("#paysidpro").val(57)
}

function nextdevispro() {
    $("#register-devis").hide(), $(".retour-devis p").show(), $("#register-devis-part").hide(), $("#register-devis-pro").show(), $("#propartdevis").val(2)
}

function nextdevispart() {
    $("#register-devis").hide(), $(".retour-devis p").show(), $("#register-devis-pro").hide(), $("#register-devis-part").show(), $("#propartdevis").val(1), $("#conseils1").prop("checked", !0)
}

function retourdevis() {
    devisReset(), $(".retour-devis p").hide(), $("#register-devis-pro").hide(), $("#register-devis-part").hide(), $("#register-devis").show(), $("#propartdevis").val(0)
}

function devisReset() {
    $(".line input").val(""), $(".line select").val(""), $("#conseils1").prop("checked", !1), $("#conseils0").prop("checked", !1), $(".checkbox input").prop("checked", !1)
}

function opendevisrapide(e) {
    $.ajax({
        type: "GET",
        url: "/devisrapide.php",
        dataType: "text",
        data: {
            artid: e
        },
        success: function(e) {
            $("#test-popup").html(e)
        }
    })
}

function openpopudevis(raison) {
    $.ajax({
        type: "GET",
        url: "/devisrapide.php",
        dataType: "text",
        data: {
            artid: $("#devisrapartid").val(),
            raison: raison
        },
        success: function(e) {
            $("#test-popup").html(e)
        }
    }), $(".open-popup-link").magnificPopup("open")
}
$(function() {
    1 == getCookie("cookieinfo") && $("#barrecookies").hide()
});

function loadGA(){
    var e = document.getElementsByName('ga1');
    var e1 = document.getElementsByName('ga2')[0];
    var content="";
    console.log(e[0]);
    for (var i = 0;  i < e.length ; i++) {
        content+="ga('ec:addProduct', {\r'id': '"+e[i].getAttribute('id')+"',\r'name': \""+e[i].getAttribute('libelle')+"\",\r'category': \""+e[i].getAttribute('category')+"\",\r'brand': '"+e[i].getAttribute('brand')+"',\r'variant': '',\r'price': "+e[i].getAttribute('price')+",\r'quantity': "+e[i].getAttribute('quantity')+"\r});\r";
    }
    console.log(content);
    $("#test1").append(content);
}
function cookie_infos() {
    setCookie("cookieinfo", 1, 365), document.getElementById("barrecookies").className = "hidden", document.getElementById("return-to-top").style.bottom = "47px"
}

function cookie_choice(rep){

	var MD5 = function(d){var r = M(V(Y(X(d),8*d.length)));return r.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_}
	var consentID = MD5(Math.random()+"AAA");

	var content = "consentID="+consentID+"&date="+Date.now()+"&rep=";

	switch (rep){

		case 'yes':
			consentGranted(); 
			statcookie(0);
			content = content+"all&group1=1&group2=1";
			setCookie("cookieconsent", content, 365);
			$('#barrecookies').hide();
			document.getElementById("return-to-top").style.bottom = "47px";
			$('.overlay').hide();
			$('.modaldisplay').hide();
		
			break;
		case 'no':
			content = content+"none&group1=0&group2=0";
			disableCookies(0);
			statcookie(1);
			setCookie("cookieconsent", content, 1);
			$('#barrecookies').hide();
			document.getElementById("return-to-top").style.bottom = "47px";
			
			$('.overlay').hide();
			$('.modaldisplay').hide();

			break;

		case 'param':
			statcookie(2);
			if(getCookie('cookieconsent') != ''){
				var cookieconsent = getCookie('cookieconsent');
				if(cookieconsent.indexOf('group1=1') > 0) $('#cookies_analytiques').prop('checked',true);
				if(cookieconsent.indexOf('group2=1') > 0) $('#cookies_rs').prop('checked',true);
			}
			$('.overlay').show();
			$('.modaldisplay').show();
			break;
	}



}

function cookie_saveparam(){
	var MD5 = function(d){var r = M(V(Y(X(d),8*d.length)));return r.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_}
	var consentID = MD5(Math.random()+"AAA");

	var content = "consentID="+consentID+"&date="+Date.now()+"&rep=param&";
	var group1 = '0';
	var group2 = '0';
	if($('#cookies_analytiques').prop('checked')) group1='1';
	if($('#cookies_rs').prop('checked')) group2='1';

	content = content + 'group1='+group1+'&group2='+group2;
	setCookie("cookieconsent", content, 365);

	if(group1=='0' && group2=='0'){
		disableCookies(0);
	}else{
		if(group1 == '0'){
			disableCookies(1);
		}else if(group1 == '1'){
			consentGranted();
		}
		if(group2 == '0'){
			disableCookies(2);
		}
	}

	setTimeout(hide_barrecookies,1000);	
}

function hide_barrecookies(){
	$('#barrecookies').hide();
}

function disableCookies(choice){

	var domain = getDomainName(window.location.hostname);

	switch (choice){
		case 0:
			//on enleve TOUS les traçeurs du code
			$('#script_nextperformance').remove();
			$('#script_remarketing').remove();
			$('#script_gclid').remove();
			//$('#script_gtm').remove();
			//$('#script_gtm2').remove();
			$('#script_analytics').remove();
			$('#pixel_fb').remove();

			//on supprime TOUS les cookies de tracking en fonction du choix
			//if(getCookie('_ga') != '') document.cookie = '_ga=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('_gat') != '') document.cookie = '_gat=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('_gid') != '') document.cookie = '_gid=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			if(getCookie('_uetvid') != '') document.cookie = '_uetvid=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			if(getCookie('_uetsid') != '') document.cookie = '_uetsid=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			if(getCookie('_gcl_au') != '') document.cookie = '_gcl_au=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			if(getCookie('_fbp') != '') document.cookie = '_fbp=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__utma') != '') document.cookie = '__utma=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__utmb') != '') document.cookie = '__utmb=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__utmc') != '') document.cookie = '__utmc=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__utmz') != '') document.cookie = '__utmz=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';	
			//if(getCookie('__cfduid') != '') document.cookie = '__cfduid=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';

		break;

		case 1:
			//on enlève juste analytiques
			$('#script_nextperformance').remove();
			$('#script_remarketing').remove();
			$('#script_gclid').remove();
			//$('#script_gtm').remove();
			//$('#script_gtm2').remove();
			$('#script_analytics').remove();
			//if(getCookie('_ga') != '') document.cookie = '_ga=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('_gat') != '') document.cookie = '_gat=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('_gid') != '') document.cookie = '_gid=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			if(getCookie('_uetvid') != '') document.cookie = '_uetvid=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			if(getCookie('_uetsid') != '') document.cookie = '_uetsid=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			if(getCookie('_gcl_au') != '') document.cookie = '_gcl_au=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__utma') != '') document.cookie = '__utma=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__utmb') != '') document.cookie = '__utmb=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__utmc') != '') document.cookie = '__utmc=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__utmz') != '') document.cookie = '__utmz=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
			//if(getCookie('__cfduid') != '') document.cookie = '__cfduid=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
		break;
		
		case 2:
			//on enlève juste RS

			$('#pixel_fb').remove();
			if(getCookie('_fbp') != '') document.cookie = '_fbp=0;path=/;domain='+domain+';expires=Thu, 01 Jan 1970 00:00:00 UTC';
		break;
		
		case 3:
			// on garde tout
		break;	
	}

}

function getDomainName(hostName)
{
    return hostName.substring(hostName.lastIndexOf(".", hostName.lastIndexOf(".") - 1) + 1);
}


function consentGranted() {
	/*gtag('consent', 'update', {
	  'ad_storage': 'granted',
	  'analytics_storage': 'granted'
	});*/
	
}

function updateConsent(){
	if(getCookie('cookieconsent') != ''){
		cookieconsent = getCookie('cookieconsent');
		if(cookieconsent.indexOf('group1=1')>0){
			consentGranted();
		}
	}
}
function statcookie(choix){
	$.ajax({
        
        url : '/ajax/statcookies.php',
        type: 'POST',
        data: 'action=stats&choix='+choix,
        dataType : 'text'

    });
}

function ajaxrequest(url,callback,post,async){
          
          if(async){
               var async = false;
          } else {
               var async = true;
          }
                  
        var httpRequest = false;

        // creation d'un objet HtmlRequest pour executer la fonction contenue dans la variable callback
        if (window.XMLHttpRequest) { // Mozilla, Safari,... version recentes d'IE ?
            httpRequest = new XMLHttpRequest();
            if (httpRequest.overrideMimeType) {
//                 httpRequest.overrideMimeType('text/xml');
                // Voir la note ci-dessous � propos de cette ligne
            }
        }
        else if (window.ActiveXObject) { // anciennes versions IE 5, IE 6 utilisent activeX
            try {
                httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
                try {
                    httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e) {}
            }
        }
        // probleme lors de la creation de l'objet
        if (!httpRequest) {
            return false;
        }

        // execution de la requete lorsque la propri�t� onready change
        httpRequest.onreadystatechange = function() {
            // execution qd le traitement (ci-dessous?) est fini et que la reponse est prete
            if (httpRequest.readyState == 4) {
               if (httpRequest.status == 200) {
                  callback(httpRequest);     // appel de la fonction recu en parametre
               }
            }
        };

 
  
	if(post==undefined){
  
    if (url=='ajax/vf_maj.php?action=close&name=toto')
    {
      confirm("Before call 2: "+url+" !!");
    
      //httpRequest.open('GET', url, async);
  	  //httpRequest.send(null);
    }
	  httpRequest.open('GET', url, async);
	  httpRequest.send();
    
	}else{
	  var parameters="";
	  for(name in post)
	    if (post.hasOwnProperty(name)) parameters+="&"+name+"="+encodeURIComponent(post[name]);

	  httpRequest.open('POST',url,async);
	  httpRequest.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	  httpRequest.send(parameters.substr(1));
	}
}

var ral_vars=new Array();

function ral_show(raldiv,state,sender){
    var display=(state?"block":"none");
    ral_vars['div']=raldiv;

    if(sender!=undefined){
    	var pos=findPos(sender);
      pos.top = 0;
    	document.getElementById(raldiv).style.left="33%";
    	document.getElementById(raldiv).style.top=pos.top+"px";
	    if(document.documentElement.clientWidth<pos.left+600) document.getElementById(raldiv).style.left=(pos.left-550)+"px";
    }
    document.getElementById(raldiv).style.display=display;
    document.body.onmousedown = function (e) { 
      var target = (e && e.target) || (event && event.srcElement);
      if(!checkParent(target,raldiv)) ral_show(raldiv,false); 
    }
}

function article_ral_select(caractid,caractvalid,ral_caractvalid,color,artid){
	document.getElementById('lst_ral_'+caractid+'_'+caractvalid).value=ral_caractvalid;
    document.getElementById(ral_vars['div']).style.display="none";
    checkOption($('#option'+caractvalid), artid, caractid, caractvalid, '', true, color);
    //document.getElementById('lst_ral_'+caractid+'_'+caractvalid).checked=true;
} 

function panier_ral_select(caractvalid,caractvalid,ral_caractvalid,color){
    var ral_select=document.getElementsByName("lst_caract["+ral_vars['lignepanierid']+"]["+ral_vars['caractid']+"]");

    for(i=0;i<ral_select.length;i++) if(ral_select[i].checked) ral_select[i].value=ral_caractvalid;
//     ral_select.options[ral_select.selectedIndex].value=ral_caractvalid;
    document.formu.submit();
}

function set_ral_vars(a){
    ral_vars=a;
}

function findPos(obj){
    var curleft=curtop=0;
    if(obj.offsetParent){
      do {
	      curleft+=obj.offsetLeft;
	      curtop+=obj.offsetTop;
      } while(obj=obj.offsetParent); 
    }
    return {"left":curleft,"top":curtop};
}

function checkParent(t,id) {
        while(t.parentNode) { 
          if( t == document.getElementById(id) ) {return true;} 
          t = t.parentNode;
        } 
        return false;
}

/************************************************************************************************************
(C) www.dhtmlgoodies.com, March 2006

This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	

Terms of use:
You are free to use this script as long as the copyright message is kept intact. However, you may not
redistribute, sell or repost it without our permission.

Version:
	1.0	Released	March. 3rd 2006

Thank you!

www.dhtmlgoodies.com
Alf Magne Kalleland

************************************************************************************************************/
var flyingSpeed = 10;
var url_addProductToBasket = '/addProduct.php';
var url_removeProductFromBasket = '/removeProduct.php';
var txt_totalPrice = 'Total: ';


var shopping_cart_div = false;
var flyingDiv = false;
var currentProductDiv = false;

var shopping_cart_x = false;
var shopping_cart_y = false;

var slide_xFactor = false;
var slide_yFactor = false;

var diffX = false;
var diffY = false;

var currentXPos = false;
var currentYPos = false;

var ajaxObjects = new Array();

var artid=0;


function shoppingCart_getTopPos2(inputObj)
{		
  var returnValue = inputObj.offsetTop;
  while((inputObj = inputObj.offsetParent) != null){
  	if(inputObj.tagName!='HTML')returnValue += inputObj.offsetTop;
  }
  return returnValue;
}

function shoppingCart_getLeftPos2(inputObj)
{
  var returnValue = inputObj.offsetLeft;
  while((inputObj = inputObj.offsetParent) != null){
  	if(inputObj.tagName!='HTML')returnValue += inputObj.offsetLeft;
  }
  return returnValue;
}
	

function addToBasket2(productId)
{
	if(!shopping_cart_div)shopping_cart_div = document.getElementById('shopping_cart');
	if(!flyingDiv){
		flyingDiv = document.createElement('DIV');
		flyingDiv.style.position = 'absolute';
		document.body.appendChild(flyingDiv);
	}
	
	shopping_cart_x = shoppingCart_getLeftPos2(shopping_cart_div);
	shopping_cart_y = shoppingCart_getTopPos2(shopping_cart_div);

	currentProductDiv = document.getElementById('slidingProduct' + productId);
	
	currentXPos = shoppingCart_getLeftPos2(currentProductDiv);
	currentYPos = shoppingCart_getTopPos2(currentProductDiv);
	
	diffX = shopping_cart_x - currentXPos;
	diffY = shopping_cart_y - currentYPos;
	
	
	var shoppingContentCopy = currentProductDiv.cloneNode(true);
	shoppingContentCopy.id='';
	flyingDiv.innerHTML = '';
	flyingDiv.style.left = currentXPos + 'px';
	flyingDiv.style.top = currentYPos + 'px';
	flyingDiv.appendChild(shoppingContentCopy);
	flyingDiv.style.display='block';
	flyingDiv.style.width = currentProductDiv.offsetWidth + 'px';
	flyToBasket2(productId);
	
}


function flyToBasket2(productId)
{
	var maxDiff = Math.max(Math.abs(diffX),Math.abs(diffY));
	var moveX = (diffX / maxDiff) * flyingSpeed;;
	var moveY = (diffY / maxDiff) * flyingSpeed;	
	
	currentXPos = currentXPos + moveX;
	currentYPos = currentYPos + moveY;
	
	flyingDiv.style.left = Math.round(currentXPos) + 'px';
	flyingDiv.style.top = Math.round(currentYPos) + 'px';	
	
	
	if(moveX>0 && currentXPos > shopping_cart_x){
		flyingDiv.style.display='none';		
	}
	if(moveX<0 && currentXPos < shopping_cart_x){
		flyingDiv.style.display='none';		
	}
		
	if (flyingDiv.style.display == 'block')
		{
		setTimeout('flyToBasket2("' + productId + '")',10); 
		}
	else 
		{
		ajaxAddProduct2(productId);	
		}
}

function ajaxAddProduct2(productId,noopt ,accesPanier ){

	/* ajout pour r�cup�rer les cract�ristiques et la quantit� */
	var maliste = '' ;
	var identifiant = new RegExp('lst_', "g");
	var mesDiv = document.getElementsByTagName("input");
	if(typeof(accesPanier) == 'undefined'){
		accesPanier = 0;
	}
	for(var j=0; j<mesDiv.length; j++){
		var libelle = mesDiv[j].id;
		if (libelle.match(identifiant)){
			var name = mesDiv[j].name;
      		var idinput = libelle.split('_');
      		var caractid = idinput[1];
      		var caractvalid = document.getElementById(libelle).value;
            if(!option_checked(name)){
                document.getElementById('opterror').style.display='block';
		  		htmlalert("<i class='fa fa-exclamation-triangle'></i>"+document.getElementById("opterror").innerHTML);
	            return;
	        }
	        if (document.getElementById(libelle).checked){
				if(caractid == 'ral'){
					caractid = "ral[]";
				} 
	            maliste += '&lst_'+caractid+'='+caractvalid;
	        }
	    }
	}

	if(noopt==true) maliste="";		
		
	if (document.getElementById('qte')){
		var qte = document.getElementById('qte').value;
	}
	else
	{
		if(document.getElementById('qte'+productId)){
			var qte = document.getElementById('qte'+productId).value;
		}else{
			var qte = 1;
		}
		
	}
	/* ajout pour r�cup�rer les cract�ristiques et la quantit� */

	artid=productId;

	/* PIXEL FACEBOOK pour ajout panier*/
	if(getCookie("_fbp") != ""){
		if(typeof(fbq) === "function"){
			fbq('track', 'AddToCart', {content_ids: ['\''+artid+'\''],content_type: 'product',currency: 'EUR'});
		}	
	}
	
	ajaxrequest(url_addProductToBasket + '?accesPanier='+accesPanier+'&productId=' + productId + '&qte=' + qte + maliste,showAjaxBasketContent2);

}

function ajaxAddProduct2Mobile(productId,noopt,accesPanier)
{
	/* ajout pour r�cup�rer les cract�ristiques et la quantit� */
	var maliste = '' ;
	var identifiant = new RegExp('lst_', "g");
	mesDiv = document.getElementsByTagName("input");
	if(typeof(accesPanier) == 'undefined'){
		accesPanier = 0;
	}
	for(var j=0; j<mesDiv.length; j++)
		{
		var libelle = mesDiv[j].id;

			if (libelle.match(identifiant)){           
	              var name = mesDiv[j].name;
	              var idinput = libelle.split('_');
	              var caractid = idinput[1];
	              var caractvalid = document.getElementById(libelle).value;

	              if(!option_checked(name)){
	                   document.getElementById('opterror').style.display='block';
			   htmlalert("<i class='fa fa-exclamation-triangle'></i>"+document.getElementById("opterror").innerHTML);
	                   return;
	              }
	              
	              if (document.getElementById(libelle).checked){
					  if(caractid == 'ral'){
							caractid = "ral[]";
						} 
	                   maliste += '&lst_'+caractid+'='+caractvalid;
	              }
	         }
		}

	if(noopt==true) maliste="";		
		
	if (document.getElementById('qte'))
		{
		var qte = document.getElementById('qte').value;
		}
	else
		{
		var qte = document.getElementById('qte'+productId).value;
		}
	/* ajout pour r�cup�rer les cract�ristiques et la quantit� */

	artid=productId;

	/* PIXEL FACEBOOK pour ajout panier*/
	if(getCookie("_fbp") != ""){
		if(typeof(fbq) === "function"){
			fbq('track', 'AddToCart', {content_ids: ['\''+artid+'\''],content_type: 'product',currency: 'EUR'});
		}
	}
	
	ajaxrequest(url_addProductToBasket + '?accesPanier='+accesPanier+'&productId=' + productId + '&qte=' + qte + maliste,showAjaxBasketContent2Mobile);
		
}




function copy_qte_option(artid){
	$("#qte"+artid).val($("#qte2"+artid).val());
}
function copy_qte_from_option(artid){
	$("#qte2"+artid).val($("#qte"+artid).val());
}




function option_checked(inputname){
     var btns=document.getElementsByName(inputname);
     
     for(i=0;i<btns.length;i++){
          if((btns[i].type=="radio" && btns[i].checked) || btns[i].disabled) return true;
     }
         
     return false;
}

function showAjaxBasketContent2(ajaxIndex)
	{
	// Getting a reference to the shopping cart items table
	var itemBox = document.getElementById('shopping_cart_items');
	
	var resultat=ajaxIndex.responseText;
	//var resultat = ajaxObjects[ajaxIndex].response;	// Breaking response from Ajax into tokens
	myresultat = resultat.split('|||');

	if (resultat.substring(0,2) == 'OK')
	{// A product with this id is allready in the basket - just add number items
		myresultat = resultat.split('|||');
		var accesPanier = parseInt(myresultat[7]);
		if(myresultat[6]==1 && accesPanier == 0){
			var prixtt = document.getElementById('prix_par_caract').textContent;
			prixtt = prixtt.match(/[0-9,]+/)[0];
		    tb_show('','/ajax/ajout_panier.php?artid='+artid+'&prix='+prixtt+'&width=970&height=400',false);
		}else    
		    document.location="/cde1.php";

		//Mise � jour du panier
		loadAjaxPanier();
	}
	else
	{
		alert('Une erreur s\'est produite pendant l\'ajout au panier.\nPour visualiser votre commande, cliquez sur le bouton commander en haut � droite.');
	} 

		ajaxObjects[ajaxIndex] = false;
	}

function showAjaxBasketContent2Mobile(ajaxIndex)
	{
	// Getting a reference to the shopping cart items table
	var itemBox = document.getElementById('shopping_cart_items');
	
	var resultat=ajaxIndex.responseText;
	//var resultat = ajaxObjects[ajaxIndex].response;	// Breaking response from Ajax into tokens

	if (resultat.substring(0,2) == 'OK')
	{// A product with this id is allready in the basket - just add number items
		  
		document.location="/cde1.php";

		//Mise � jour du panier
		loadAjaxPanier();
	}
	else
	{
		alert('Une erreur s\'est produite pendant l\'ajout au panier.\nPour visualiser votre commande, cliquez sur le bouton commander en haut � droite.');
	} 

		ajaxObjects[ajaxIndex] = false;
	}	
var ss={fixAllLinks:function(){for(var e=document.getElementsByTagName("a"),t=0;t<e.length;t++){var o=e[t];!o.href||-1==o.href.indexOf("#")||o.pathname!=location.pathname&&"/"+o.pathname!=location.pathname||o.search!=location.search||ss.addEvent(o,"click",ss.smoothScroll)}},smoothScroll:function(e){if(window.event)target=window.event.srcElement;else{if(!e)return;target=e.target}if("a"!=target.nodeName.toLowerCase()&&(target=target.parentNode),"a"==target.nodeName.toLowerCase()){anchor=target.hash.substr(1);for(var t=document.getElementsByTagName("a"),o=null,s=0;s<t.length;s++){var n=t[s];if(n.name&&n.name==anchor){o=n;break}}if(o||(o=document.getElementById(anchor)),!o)return!0;o.offsetLeft;for(var r=o.offsetTop,a=o;a.offsetParent&&a.offsetParent!=document.body;)(a=a.offsetParent).offsetLeft,r+=a.offsetTop;clearInterval(ss.INTERVAL),cypos=ss.getCurrentYPos(),ss_stepsize=parseInt((r-cypos)/ss.STEPS),ss.INTERVAL=setInterval("ss.scrollWindow("+ss_stepsize+","+r+',"'+anchor+'")',10),window.event&&(window.event.cancelBubble=!0,window.event.returnValue=!1),e&&e.preventDefault&&e.stopPropagation&&(e.preventDefault(),e.stopPropagation())}},scrollWindow:function(e,t,o){wascypos=ss.getCurrentYPos(),isAbove=wascypos<t,window.scrollTo(0,wascypos+e),iscypos=ss.getCurrentYPos(),isAboveNow=iscypos<t,(isAbove!=isAboveNow||wascypos==iscypos||wascypos>=ss.getScrollTopMax())&&(window.scrollTo(0,t),clearInterval(ss.INTERVAL))},getCurrentYPos:function(){return document.body&&document.body.scrollTop?document.body.scrollTop:document.documentElement&&document.documentElement.scrollTop?document.documentElement.scrollTop:window.pageYOffset?window.pageYOffset:0},getScrollTopMax:function(){return"scrollMaxX"in window?window.scrollMaxY:document.documentElement.scrollHeight-document.documentElement.clientHeight},addEvent:function(e,t,o,s){return e.addEventListener?(e.addEventListener(t,o,s),!0):e.attachEvent?e.attachEvent("on"+t,o):void alert("Handler could not be removed")},scrollToDiv:function(e,t){null==t&&(t=0);for(var o=document.getElementById(e),s=(o.offsetLeft,o.offsetTop+t),n=o;n.offsetParent&&n.offsetParent!=document.body;)(n=n.offsetParent).offsetLeft,s+=n.offsetTop;clearInterval(ss.INTERVAL),cypos=ss.getCurrentYPos(),ss_stepsize=parseInt((s-cypos)/ss.STEPS),ss.INTERVAL=setInterval("ss.scrollWindow("+ss_stepsize+","+s+',"'+e+'")',10)},STEPS:50};ss.addEvent(window,"load",ss.fixAllLinks);
var rrint=0;function rrprop(e){var s=$(".rechercherapide")[0].value;s.length<=3?$(".btn-rechercher").prop("disabled",!0):($(".btn-rechercher").prop("disabled",!1),13!=(e.keyCode?e.keyCode:e.charCode)?($("#divprop").html='<div class="cadre_recherche"></div><img class="loader" src="/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif" align="absmiddle"><p class="loader">Recherche en cours...</p>',0!=rrint&&clearTimeout(rrint),rrint=setTimeout('rrrequest("'+s+'")',100)):rechercherapide())}function rrrequest(e){ajaxrequest("/rr.php?txt="+e,rrcallback)}function rrcallback(e){""!=e.responseText?$("#divprop").html(e.responseText):$("#divprop").html='<div class="cat" onclick="document.getElementById(\'divprop\').style.display=\'none\';"><span class="line2"></span><strong>Aucune suggestion</strong><span class="line2"></span></div>',$("#divprop").css("display","block"),$("#divprop").css("padding-top","40px"),$(window).width()<767&&$(".croix").css("display","block"),$(window).width()<991?($(".item.cat-recherche.item-nb4").css("display","none"),$(".item.cat-recherche.item-nb5").css("display","none"),$(".item.cat-recherche.item-nb6").css("display","none"),$(".articles-result.item-nb4").css("display","none"),$(".articles-result.item-nb5").css("display","none"),$(".articles-result.item-nb6").css("display","none"),$(".item.item-marques.item-nb5").css("display","none"),$(".item.item-marques.item-nb6").css("display","none")):($(".item.cat-recherche.item-nb4").css("display","block"),$(".item.cat-recherche.item-nb5").css("display","block"),$(".item.cat-recherche.item-nb6").css("display","block"),$(".articles-result.item-nb4").css("display","flex"),$(".articles-result.item-nb5").css("display","flex"),$(".articles-result.item-nb6").css("display","flex"),$(".item.item-marques.item-nb5").css("display","block"),$(".item.item-marques.item-nb6").css("display","block"))}function selectrr(e){$(".rechercherapide")[0].value=e,$("#divprop").css("display","none"),$("#ok_rr").css("display","none"),$("#wait_rr").css("display","inline"),document.forms.rechercheform.submit()}function rrclick(){"none"===$("#boxrrstatic").css("display")?($("#menu-niveau-1-6 ").css("z-index","0"),$("#whiteboxrr").prop("disabled",!0),$("#boxrrstatic").show()):($("#whiteboxrr").prop("disabled",!1),$("#boxrrstatic").hide(),$("#menu-niveau-1-6 ").css("z-index","100")),$(".bloc-recherche").toggle(),$(".fond-sombre").toggle(),$(window).width()>767&&$(".croix").css("display","block"),$(window).width()<767&&"block"===$("#divprop").css("display")&&$(".croix").css("display","none"),$(window).width()<767&&""!=$("#divprop").html()&&$(".croix").css("display","block"),"block"===$("#divprop").css("display")&&""!=$("#divprop").html()?$("#divprop").css("display","none"):"none"===$("#divprop").css("display")&&""!=$("#divprop").html()&&($("#divprop").css("display","block"),$("#divprop").css("padding-top","40px !important"),$("#divprop").css("padding-bottom","27px !important")),$(".rechercherapide").focus()}function rrsearch(){document.getElementsByName("rechercherapide")[0].value;document.getElementsByName("rechercherapide")[0].style.color="black"}function rechercherapide(){var e=$(".rechercherapide")[0].value;e.length<3||(document.location="/recherche-rapide.php?action="+e)}function saveMotCle(e){$.ajax({url:"/ajax/saveMotCle.php",dataType:"text",type:"GET",data:"txt="+e})}$(window).width()<991?($(".item.cat-recherche.item-nb4").css("display","none"),$(".item.cat-recherche.item-nb5").css("display","none"),$(".item.cat-recherche.item-nb6").css("display","none"),$(".articles-result.item-nb4").css("display","none"),$(".articles-result.item-nb5").css("display","none"),$(".articles-result.item-nb6").css("display","none"),$(".item.item-marques.item-nb5").css("display","none"),$(".item.item-marques.item-nb6").css("display","none")):$(window).width()<767?("block"===$(".bloc-recherche").css("display")&&"none"===$("#divprop").css("display")&&$(".croix").css("display","none"),"block"===$("#divprop").css("display")&&$(".croix").css("display","block")):($(".item.cat-recherche.item-nb4").css("display","block"),$(".item.cat-recherche.item-nb5").css("display","block"),$(".item.cat-recherche.item-nb6").css("display","block"),$(".articles-result.item-nb4").css("display","flex"),$(".articles-result.item-nb5").css("display","flex"),$(".articles-result.item-nb6").css("display","flex"),$(".item.item-marques.item-nb5").css("display","block"),$(".item.item-marques.item-nb6").css("display","block"),"block"===$(".bloc-recherche").css("display")?$(".croix").css("display","block"):$(".croix").css("display","none")),$(window).resize(function(){$(window).width()<991?($(".item.cat-recherche.item-nb4").css("display","none"),$(".item.cat-recherche.item-nb5").css("display","none"),$(".item.cat-recherche.item-nb6").css("display","none"),$(".articles-result.item-nb4").css("display","none"),$(".articles-result.item-nb5").css("display","none"),$(".articles-result.item-nb6").css("display","none"),$(".item.item-marques.item-nb5").css("display","none"),$(".item.item-marques.item-nb6").css("display","none")):$(window).width()<767?("block"===$(".bloc-recherche").css("display")&&"none"===$("#divprop").css("display")&&$(".croix").css("display","none"),"block"===$("#divprop").css("display")&&$(".croix").css("display","block")):($(".item.cat-recherche.item-nb4").css("display","block"),$(".item.cat-recherche.item-nb5").css("display","block"),$(".item.cat-recherche.item-nb6").css("display","block"),$(".articles-result.item-nb4").css("display","flex"),$(".articles-result.item-nb5").css("display","flex"),$(".articles-result.item-nb6").css("display","flex"),$(".item.item-marques.item-nb5").css("display","block"),$(".item.item-marques.item-nb6").css("display","block"),"block"===$(".bloc-recherche").css("display")?$(".croix").css("display","block"):$(".croix").css("display","none"))}),$(".fond-sombre").click(function(e){"bloc-recherche"!=e.target.class&&rrclick()});var url=window.location.href;function redirection_choix(e){var s;"produits"===e&&(s=(s=(s=window.location.href).replace("marques","produits",s)).replace("cats","produits",s),window.history.pushState("","",s),$(".barre_filtre").show(),$(".resultats_par_marques").hide(),$(".resultats_par_cats").hide(),$(".resultats_par_articles").show(),$("#choix_produits").css({backgroundColor:"black",color:"white"}),$("#choix_cat").css({backgroundColor:"rgba(0,0,0,0)",color:"black"}),$("#choix_marques").css({backgroundColor:"rgba(0,0,0,0)",color:"black"}),$(window).width()<767&&$(".container.container-main").css("padding","0px"));"marques"===e&&(s=(s=(s=window.location.href).replace("cats","marques",s)).replace("produits","marques",s),window.history.pushState("","",s),$(".barre_filtre").hide(),$(".resultats_par_marques").show(),$(".resultats_par_cats").hide(),$(".resultats_par_articles").hide(),$("#choix_produits").css({backgroundColor:"rgba(0,0,0,0)",color:"black"}),$("#choix_cat").css({backgroundColor:"rgba(0,0,0,0)",color:"black"}),$("#choix_marques").css({backgroundColor:"black",color:"white"}),$(window).width()<767&&$(".container.container-main").css("padding","0px"));"cats"===e&&(s=(s=(s=window.location.href).replace("marques","cats",s)).replace("produits","cats",s),window.history.pushState("","",s),$(".barre_filtre").hide(),$(".resultats_par_marques").hide(),$(".resultats_par_cats").show(),$(".resultats_par_articles").hide(),$("#choix_produits").css({backgroundColor:"rgba(0,0,0,0)",color:"black"}),$("#choix_cat").css({backgroundColor:"black",color:"white"}),$("#choix_marques").css({backgroundColor:"rgba(0,0,0,0)",color:"black"}),$(window).width()<767&&$(".container.container-main").css("padding","0px"))}url.includes("&actif=marques")?redirection_choix("marques"):url.includes("&actif=produits")?redirection_choix("produits"):url.includes("&actif=cats")?redirection_choix("cats"):url.includes("/recherche-rapide.php?action=")&&!1===url.includes("&actif=")&&(redirection_choix("produits"),$(window).width()<767&&$(".container.container-main").css("padding","0px"));
var check_delay = 200;
var tab_req     = new Array();
var file        = "articleprix_ajax.php";
var max_req     = 1;
var max_var     = 3;

/*
 * R�cup�re toutes les options coch�es pour un produit
 * afin de les transmettre � un fichier (ajax) pour 
 * recalculer le prix total (en cas d'option payante)
 * 
 * @param {integer} artid : Identifiant d'un produit/pack
 * @returns N/A
 */
function doRequest(artid, packid) {
    /*if(tab_req.length > 0) alert("Pas pr�t...");*/

    max_req = 1;

    // Transmission des donn�es
    data = "";
	
    //data += 'qte='+qte+'&';
    data += '?artid='+artid;
    
    //Si l'option concerne un pack, on l'ajoute dans l'url ajax
    if(packid && packid != ''){
        data += '&packid='+packid;
    }
    var regexpr = 'lst_';
    var liste_caractvalid = "0";
    var identifiant = new RegExp(regexpr, "g");
    mesDiv = document.getElementsByTagName("input");
    
    for(var j=0; j<mesDiv.length; j++){
        var libelle = mesDiv[j].id;
        if (libelle.match(identifiant)){
            if (document.getElementById(libelle).checked){
                var caractvalid = document.getElementById(libelle).value;
                liste_caractvalid += ','+caractvalid;
            }
        }
    }	
			
    data += '&liste_caractvalid='+liste_caractvalid;

    var garinput=document.getElementsByTagName('gar');
    if(garinput.length>0){
          for(i=0;i<garinput.length;i++)
               if(garinput.checked) data += "&garantieid="+garinput[i].value;
    }
    //alert(data);
    ajaxrequest(file+data,checkResponse);
}

/*
 * Interpr�te le r�sultat du calcul du nouveau prix total d'un produit
 * suite � l'ajout d'options.
 * 
 * @param {string} httprequest : Prix s�par�s par '|||'
 * @returns N/A
 */
function checkResponse(httprequest) {
     var rep = httprequest.responseText;

     myresultat = rep.split('|||');

     var oldprice = document.getElementById('prix_par_caract').innerHTML;
     oldprice = oldprice.replace(",",".");
     oldprice = parseFloat(oldprice.substring(0,oldprice.length - 1));
     document.getElementById('prix_par_caract').innerHTML=myresultat[1];
     if(document.getElementById('prix_par_caract_new')){
          document.getElementById('prix_par_caract_new').innerHTML=myresultat[1];
     }

     var newprice = document.getElementById('prix_par_caract').innerHTML;
     newprice = newprice.replace(",",".");
     newprice = newprice.replace("�","");
     newprice = parseFloat(newprice.substring(0,newprice.length - 1));

     if(document.getElementById('prixbarrecaract')){
          var prixbarrecaract = document.getElementById('prixbarrecaract').innerHTML;
          prixbarrecaract = prixbarrecaract.replace(",",".");
          prixbarrecaract = parseFloat(prixbarrecaract.substring(0,prixbarrecaract.length - 1));
          prixbarrecaract = prixbarrecaract+(newprice-oldprice);
          prixbarrecaract = prixbarrecaract.toFixed(2).replace(".",",");
          
          if(document.getElementById('prixbarrecaract')){
               document.getElementById('prixbarrecaract').innerHTML=prixbarrecaract+'�';
          }
          
          if(document.getElementById('prix_par_caract_promo')){
               $('.strikeout').each(function(){
                    $(this).empty();
                    $(this).html(prixbarrecaract+'�');
               });
          }         
     }
}

function addToDiv(txt) {
	document.getElementById("dData").innerHTML = txt;
}

function changeSrc(txt) {
	var ibig = document.getElementById("big") ;
	if (ibig)
		ibig.src = txt;
	//document.getElementById("dData").scrollTop = document.getElementById("dData").scrollHeight;
}

function toggleCode(b, d) {
    if(b.value == "montrer") {
        b.value = "masquer";
        document.getElementById(d).style.display = "block";
    }
    else {
        b.value = "montrer";
        document.getElementById(d).style.display = "none";
    }
}

var d,dd,bissextile,dcount=new Array(31,28,31,30,31,30,31,31,30,31,30,31),monthes=new Array;monthes.FR=new Array("Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Décembre"),monthes.EN=new Array("January","February","March","April","May","June","July","August","September","October","November","December"),monthes.SP=new Array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");var montharray=new Array,weekdays=new Array;weekdays.FR=new Array("D","L","M","M","J","V","S"),weekdays.EN=new Array("Su","Mo","Tu","We","Th","Fr","Sa"),weekdays.SP=new Array("D","L","M","M","J","V","S");var returninput,caldiv="cccal",bgdiv=!1,firstyear=0,cal_timestamp=0,cal_callback=void 0,showtime=void 0,timemode=1,hoursrange=void 0,selectuser=void 0,mints=void 0,mobileDevice=mobileDevice||!1,dfirst=0;function JoursFeries(e){var t=new Date(e,"00","01"),a=new Date(e,"04","01"),l=new Date(e,"04","08"),r=new Date(e,"06","14"),i=new Date(e,"07","15"),d=new Date(e,"10","01"),n=new Date(e,"10","11"),c=new Date(e,"11","25"),s=new Date(e,"11","26"),o=e%19,u=Math.floor(e/100),g=(u-Math.floor(u/4)-Math.floor((8*u+13)/25)+19*o+15)%30,m=g-Math.floor(g/28)*(1-Math.floor(g/28)*Math.floor(29/(g+1))*Math.floor((21-o)/11)),v=m-(1*e+Math.floor(e/4)+m+2-u+Math.floor(u/4))%7,y=3+Math.floor((v+40)/44),h=v+28-31*Math.floor(y/4),b=new Date(e,y-1,h),f=(new Date(e,y-1,h-2),new Date(e,y-1,h+1)),p=new Date(e,y-1,h+39),w=new Date(e,y-1,h+49),D=new Date(e,y-1,h+50);return new Array(t,b,f,a,l,p,w,D,r,i,d,n,c,s)}function initcalendrier(e,t){null==t&&(t="FR"),montharray=monthes[t];var a=document.createElement("div");a.setAttribute("id",caldiv),document.body.appendChild(a);var l="cal.css";mobileDevice&&(l="calmob.css",(bgdiv=document.createElement("div")).className="calbg",document.body.appendChild(bgdiv));var r=document.createElement("link");r.setAttribute("rel","stylesheet"),r.setAttribute("type","text/css"),r.setAttribute("href","css/"+l),document.getElementsByTagName("head")[0].appendChild(r)}function calendrier_show(e,t,a,l,r){if(returninput=e,showtime=a,curday=-1,null==r&&(r="FR"),cal_callback=void 0,null!=l&&(cal_callback=l),!(n=document.getElementById(caldiv))){initcalendrier(!0,r);var n=document.getElementById(caldiv)}null!=t&&""!=t?(matches=t.match(/\d+/g),null==matches[3]&&(matches[3]=0),null==matches[4]&&(matches[4]=0),d=new Date(matches[2],matches[1]-1,matches[0],matches[3],matches[4],0)):(d=new Date,curday=d.getDate());var c=new Date(d.getFullYear(),d.getMonth(),1);for(dd=c,tmp=c.getTime()-864e5,prevd1=new Date,prevd1.setTime(tmp),prevd1=new Date(prevd1.getFullYear(),prevd1.getMonth(),1),tmp=c.getTime()+27648e5,nextd1=new Date,nextd1.setTime(tmp),day1=c.getDay(),days=daycount(d),grille='<table id="cal">',grille+='<tr align="center" class="cal_weekdays">',i=0;i<=6;i++)grille+="<td>"+weekdays[r][i]+"</td>";for(grille+='</tr><tr align="center">',i=0;i<day1-1;i++)grille+="<td></td>";var s=0,o=JoursFeries(d.getFullYear());for($.each(o,function(e){var t=o[e];t.getMonth()==dd.getMonth()&&t.getDate()==dd.getDate()&&t.getFullYear()==dd.getFullYear()&&(dfirst=1)}),dprev=new Date,dprev.setTime(c.getTime()-864e4),0!=day1&&(grille+="<td></td>"),i=1;i<=days;i++){7==day1&&(day1=0,grille+='</tr><tr align="center">'),bg="",i==curday&&(bg=' bgcolor="#C70000"');var u=(c=new Date(d.getFullYear(),d.getMonth(),i)).getDay();$.each(o,function(e){var t=o[e];t.getMonth()==c.getMonth()&&t.getDate()==c.getDate()&&t.getFullYear()==c.getFullYear()&&(s=1)}),null==mints||c.getTime()>=mints&&0!=u&&6!=u&&0==s?grille+='<td id="td_'+i+'"'+bg+' class="cal_days" onclick="calcdatetime('+i+');" style="cursor:pointer;">'+i+"</td>":grille+='<td id="td_'+i+'"'+bg+' class="cal_days_lock">'+i+"</td>",day1++,s=0}if(fdate=1,grille+="</tr>",grille+="</table>",n.innerHTML='<div class="cal_date"><a href="javascript:calendrier_show(returninput,\'1/\'+(prevd1.getMonth()+1)+\'/\'+prevd1.getFullYear(),showtime,cal_callback)" class="cal_prec"></a><a href="javascript:calmonth('+d.getFullYear()+')" class="cal_datelink">'+montharray[d.getMonth()]+"</a><a href=\"javascript:calyear('"+(d.getMonth()+1)+'\')" class="cal_datelink">'+d.getFullYear()+'</a><a href="javascript:calendrier_show(returninput,\'1/\'+(nextd1.getMonth()+1)+\'/\'+nextd1.getFullYear(),showtime,cal_callback)" class="cal_suiv"></a><a href="javascript:calendrier_close()" class="cal_close"></a></div>',n.innerHTML+='<div class="cal"><div id="userdiv"></div>'+grille+'<div id="datetime" class="cal_datetime"></div><div id="cal_ok" class="cal_ok" onclick="returndate();"></div>',calcdatetime(),dfirst=0,curvisibility=n.style.display,"block"!=curvisibility){if(n.style.display="block",n.style.position="absolute",bgdiv&&(bgdiv.style.display="block"),mobileDevice){var g=window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth;n.style.left=Math.round((g-n.clientWidth)/2)+"px"}else{var m=getPosition(document.getElementById(e));n.style.left=m[0]+"px",n.style.top=m[1]+"px"}n.style.zIndex=100}}function calmonth(e){var t='<div class="cal_date">'+e+'<a href="javascript:calendrier_close()"class="cal_close"></a></div>';for(t+='<table class="cal"><tr>',i=0;i<12;i++)3!=i&&6!=i&&9!=i||(t+="</tr><tr>"),t+='<td class="cal_days"><a href="javascript:calendrier_show(\''+returninput+"','1/"+(i+1)+"/"+e+"',showtime,cal_callback)\">"+montharray[i]+"</a></td>";t+="</table>",document.getElementById(caldiv).innerHTML='<div class="cal">'+t+"</div>"}function calyear(e){var t='<div class="cal_date"><a href="javascript:calendrier_close()"class="cal_close"></a></div>';for(t+="<table>",cd=new Date,i=cd.getFullYear()-1;i<=cd.getFullYear()+2;i++)t+='<tr><td class="cal_days"><a href="javascript:calendrier_show(\''+returninput+"','1/"+e+"/"+i+"',showtime,cal_callback)\">"+i+"</a></td></tr>";t+="</table>",document.getElementById(caldiv).innerHTML='<div class="cal">'+t+"</div>"}function selectday(e){var t=document.getElementsByTagName("td");for(i=0;i<t.length;i++)"td_"==t[i].id.substr(0,3)&&(t[i].style.backgroundColor="");document.getElementById("td_"+e).style.backgroundColor="red"}function daycount(e){return days=dcount[e.getMonth()],bissextile=e.getFullYear()%4,1==e.getMonth()&&0==bissextile&&(days=29),days}function calcdatetime(e){null!=e&&(d.setDate(e),selectday(e));JoursFeries(d.getFullYear());var t=d.getDay();0==t&&d.setDate(d.getDate()+1),6==t&&d.setDate(d.getDate()+2),null!=mints&&d.getTime()<mints&&d.setTime(mints),d.getDate()==dd.getDate()&&d.getMonth()==dd.getMonth()&&d.getFullYear()==dd.getFullYear()&&1==dfirst?selectday(d.getDate()+1):selectday(d.getDate()),document.getElementById("datetime").innerHTML=d.getDate()+"/"+(d.getMonth()+1)+"/"+d.getFullYear()}function returndate(){if(""!=returninput&&document.getElementById(returninput)){var e=d.getMonth()+1;e<10&&(e="0"+e),document.getElementById(returninput).value=d.getDate()+"/"+e+"/"+d.getFullYear()}var t=new Date(d.getFullYear(),d.getMonth(),d.getDate());cal_timestamp=t.getTime()/1e3,calendrier_close(),null!=cal_callback&&cal_callback()}function calendrier_close(){document.getElementById(caldiv).style.display="none",bgdiv&&(bgdiv.style.display="none")}function round5(e){return e%5>=2.5?5*parseInt(e/5)+5:5*parseInt(e/5)}function getPosition(e){for(var t=0,a=0;e;)a+=e.offsetLeft,t+=e.offsetTop,e=e.offsetParent;return new Array(a,t)}
function showbarform(id, left){
  /*if (rappelsihiding[id])
  return;*/
  if(!document.getElementById(id)) return;
  //document.getElementById(id+'span').className = 'fa fa-caret-right fa-lg';

  showaide(false);

  $('#popup_comparerbtn').css('width', '169px');
  $('#popup_comparerbtn').css('opacity', '1');
  $('#popup_comparerbtn').css('bottom', '0');
  $('#popup_configbtn').css('opacity', '1');
  $('#popup_configbtn').css('bottom', '0');
  $('#articlevusbarbtn').css('width', '169px');
  $('#articlevusbarbtn').css('opacity', '1');
  $('#articlevusbarbtn').css('bottom', '0');
  $('#articlevusbar').hide();
  $('#popup_config').hide();
  $('#popup_comparer').hide();

  if(id === "popup_comparer") {
   if($(window).width() < 1199) {
    $('#popup_comparerbtn').css('width', '169px');
  }
  else {
    $('#popup_comparerbtn').css('width', '330px');
    $('#popup_comparerbtn').css('bottom', '-30px');
  }
  $('#popup_comparerbtn').css('opacity', '0');
  $('#popup_comparer').show();
}

if(id === "articlevusbar") {
  if($(window).width() < 1199) {
    $('#articlevusbarbtn').css('width', '169px');
  }
  else {
   $('#articlevusbarbtn').css('width', '329px');
   $('#articlevusbarbtn').css('bottom', '-30px');
 }

 $('#articlevusbarbtn').css('opacity', '0');
 $('#articlevusbar').show();
}

if(id === "popup_config") {
  $('#popup_configbtn').css('opacity', '0');
  $('#popup_config').css('bottom', '0px');
  $('#popup_config').show();
}

  //document.getElementById(id+"fleche").className="fleche2";
  document.getElementById(id).style.bottom="10px";

}

$(window).on("scroll", function () {
  $(window).resize(function(){
    if($(window).width() < 1580 && $(window).width() > 971) {
      if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
        $('#articlebar').hide();
      }
      else {
        $('#articlebar').show();
      }
    }
    else {
      $('#articlebar').show();
    }
  });
  if($(window).width() < 1580 && $(window).width() > 971) {
    if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
      $('#articlebar').hide();
    }
    else {
      $('#articlebar').show();
    }
  }
  else {
    $('#articlebar').show();
  }
});

$(window).on("scroll", function () {
  $(window).resize(function(){
    if($(window).width() < 991 && $(window).width() > 760) {
      if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
        $('.btnvf').hide();
      }
      else {
         $('.btnvf').show();
      }
      }
        else {
         $('.btnvf').show();
      }
    })
   if($(window).width() < 991 && $(window).width() > 760) {
      if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
        $('.btnvf').hide();
      }
        else {
         $('.btnvf').show();
      }
      }
        else {
         $('.btnvf').show();
      }
});

function closebarform(id) {
 if(id === "popup_comparer") {
  $('#popup_comparerbtn').css('width', '169px');
  $('#popup_comparerbtn').css('opacity', '1');
  $('#popup_comparerbtn').css('bottom', '0');
  $('#popup_comparer').hide();
}
if(id === "popup_config") {
  $('#popup_configbtn').css('opacity', '1');
  $('#popup_configbtn').css('bottom', '0');
  $('#popup_config').hide();
}
if(id === "articlevusbar") {
  $('#articlevusbarbtn').css('width', '169px');
  $('#articlevusbarbtn').css('opacity', '1');
  $('#articlevusbarbtn').css('bottom', '0');
  $('#articlevusbar').hide();
}
}

function autoaide(delai){
 setTimeout("showaide(true)",parseInt(delai*1000));
}

function showaide(state){
  showhelpform();
  if(!state) setCookie("aide","1",1);
  if(document.getElementById('bulle_aide')) document.getElementById('bulle_aide').style.display=(state?"block":"none");
}


function toggleaide(){
  if(document.getElementById('bulle_aide').style.display == 'block'){
    $("#bulle_aide").slideUp(1000);
    $(".popup_contact").show(1000);
    $(".popup_contact_en").show(1000);
  } else {

    $("#bulle_aide").css('bottom', '0px');
    $(".popup_contact").hide();
    $(".popup_contact_en").hide();
    $("#bulle_aide").slideDown(1000);
    $("#bulle_aide").css('display', 'block');
    showhelpform();
  }
}

$(document).on("click", function(event){
  var $trigger = $("#bulle_aide");
  var $trigger2 = $("#prix_sur_demande");
  if($trigger !== event.target && !$trigger.has(event.target).length){
    var $trigger = $(".aide");
    if($trigger !== event.target && !$trigger.has(event.target).length && !$trigger2.has(event.target).length && $trigger2 !== event.target){
      $("#bulle_aide").slideUp(1000);
      $(".popup_contact").show();
      $(".popup_contact_en").show();
    }
  }
});

function showhelpform(id){
  if(id!=undefined) var display=document.getElementById(id).style.display;
  document.getElementById('rappelinst').style.display="none";
  // document.getElementById('minicontact').style.display="none";

  if(id!=undefined && display=="none"){
    document.getElementById(id).style.display="block";
    var h=$('#bulle_aide').height();
    $('#'+id).height(h).width(0).animate({width:335},500);
  }
}

function hidebarform(id) {
  //document.getElementById(id+'span').className = 'fa fa-caret-up fa-lg';
  rappelsihiding[id] = true;
  var max=document.getElementById(id).offsetHeight;
  if(document.getElementById(id).style.bottom=="") document.getElementById(id).style.bottom='0px';
  var h=parseInt(document.getElementById(id).style.bottom,10);
  
  if(h>-max){
    h-=15;
//     document.getElementById(id).style.height=h+"px";
document.getElementById(id).style.bottom=h+'px';
setTimeout("hidebarform('"+id+"')",10);
return;
}
  // document.getElementById(id).style.height="0px";
  // document.getElementById(id+"fleche").className="fleche";//
  rappelsihiding[id] = false;
}

function hideallbarforms(event){
  if(!document.getElementById('rappelinst')) return;
  var h=parseInt(document.getElementById('rappelinst').style.bottom,10);
  if (h>-100) hidebarform('rappelinst');

  // var h=parseInt(document.getElementById('minicontact').style.bottom,10);
  // if (h>-100) hidebarform('minicontact');

  if(document.getElementById('popup_comparer')){
    var h=parseInt(document.getElementById('popup_comparer').style.bottom,10);
    if(h>-25) hidebarform('popup_comparer');

  }
  if(document.getElementById('popup_config')){
    var h=parseInt(document.getElementById('popup_config').style.bottom,10);
    if(h>-50) hidebarform('popup_config');

  }
  if(document.getElementById('articlevusbar')){
    var h=parseInt(document.getElementById('articlevusbar').style.bottom,10);
    if (h>-25) hidebarform('articlevusbar');
  }
}

function donthideform(event){
  if(event.stopPropagation)
    event.stopPropagation();
  else
    window.event.cancelBubble = true;
}

var rappelsihiding = new Array();
var html=document.getElementsByTagName('html')[0];
if(html.addEventListener)
  html.addEventListener("click", hideallbarforms, false);
else
  html.attachEvent('onclick',hideallbarforms);


function showrappelinst(){
  var h=document.getElementById('rappelinst').offsetHeight;
  if(h<340){
    h+=20;
    document.getElementById('rappelinst').style.height=h+"px";
    setTimeout("showrappelinst()",50);
    return;
  }
  document.getElementById('rappelinst').style.height="";
  document.getElementById('rappelinst_form').style.display='block';
}

function checked_options(){
  var regexpr = 'checked_opt_';
  var liste_caractvalid = '';
  var identifiant = new RegExp(regexpr, "g");
  mesDiv = document.getElementsByTagName("input");

  for(var j=0; j<mesDiv.length; j++){
    var libelle = mesDiv[j].id;
    if (libelle.match(identifiant)){
      var caractvalid = document.getElementById(libelle).value;
      liste_caractvalid += ','+caractvalid;
    }
  }
  return liste_caractvalid;
}

function submitbarrappel(){
  var tel=document.getElementById('rappelinst_tel').value;
  var ville=document.getElementById('rappelinst_ville').value;
  var societe=document.getElementById('rappelinst_societe').value;
  var cp=document.getElementById('rappelinst_cp').value;
  var adresse=document.getElementById('rappelinst_adresse').value;
  var pays=document.getElementById('rappelinst_pays').value;
  var mail=document.getElementById('rappelinst_mail').value;
  var cgv =document.getElementById('rappelinst_cgv').getElementsByTagName('input')[0].checked;
  var gclid = document.getElementById('gclid_field').value;
  document.getElementById('rappelinst_error').style.display="none";
  if(tel=="" || pays=="" || ville=="" || !cgv || !testmail(mail)){
    document.getElementById('rappelinst_error').style.display="block";
    return;
  }

    //recuperation de options du produit, si demande sur fiche produit
    var liste_caractvalid = '';
    liste_caractvalid = checked_options();


    var post=new Array();
    if(liste_caractvalid != '') post['checked_opt'] = liste_caractvalid;
    post['gclid'] = gclid;
    post['rappelinst_tel']=tel;
    post['rappelinst_ville']=ville;
    post['rappelinst_adresse']=adresse;
    post['rappelinst_cp']=cp;
    post['rappelinst_societe']=societe;
    post['rappelinst_nom']=document.getElementById('rappelinst_nom').value;
    post['rappelinst_pays']=pays;
    post['rappelinst_mail']=document.getElementById('rappelinst_mail').value;
    post['url']=document.URL;
    document.getElementById('rappelinst_msg').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif\" align=\"absmiddle\">";
    document.getElementById('rappelinst').getElementsByTagName('a')[1].style.display="none";
    document.getElementById('rappelinst_cgv').style.display="none";
    postcontact(post,rappelinstcb);
    
  }

  function rappelinstcb(httprequest){
    document.getElementById('rappelinst_msg').innerHTML=httprequest.responseText;
    setTimeout("hidebarform('rappelinst')",5000);
  }


  function submitbarcontact(){
    var tel=document.getElementById('minicontact_telephone').value;
    var ville=document.getElementById('minicontact_ville').value;
    var societe=document.getElementById('minicontact_societe').value;
    var cp=document.getElementById('minicontact_cp').value;
    var adresse=document.getElementById('minicontact_adresse').value;
    var pays=document.getElementById('minicontact_pays').value;
    var message=document.getElementById('minicontact_message').value;
    var cgv =document.getElementById('minicontact_cgv').getElementsByTagName('input')[0].checked;
    var gclid = document.getElementById('gclid_field').value;
    //document.getElementById('minicontact_besoin').style.display="none";
    document.getElementById('minicontact_error').style.display="none";
    if(tel=="" || pays=="" || ville=="" || message=="" || !cgv){
      document.getElementById('minicontact_error').style.display="block";
      return;
    }

    //recuperation de options du produit, si demande sur fiche produit
    var liste_caractvalid = '';
    liste_caractvalid = checked_options();

    var post=new Array();
    if(liste_caractvalid != '') post['checked_opt'] = liste_caractvalid;
    post['gclid'] = gclid;
    post['rappelinst_tel']=tel;
    post['rappelinst_nom']=document.getElementById('minicontact_nom').value;
    post['rappelinst_mail']=document.getElementById('minicontact_mail').value;
    post['rappelinst_ville']=ville;
    post['rappelinst_adresse']=adresse;
    post['rappelinst_cp']=cp;
    post['rappelinst_societe']=societe;
    post['rappelinst_pays']=pays;
    post['msg']=message
    post['url']=document.URL;
    document.getElementById('minicontact_form').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif\" align=\"absmiddle\">";
    document.getElementById('minicontact').getElementsByTagName('a')[1].style.display="none";
    document.getElementById('minicontact_cgv').style.display='none';
    postcontact(post,minicontactcb);
  }

  function minicontactcb(httprequest){
    document.getElementById('minicontact_form').innerHTML=httprequest.responseText;
    setTimeout("hidebarform('minicontact')",5000);
    document.getElementById('minicontact_txt').style.display='none';
  }


  function submitrappelrem(){
    var tel=document.getElementById('rappelrem_tel').value;
    if(tel=="") return;
    
    var post=new Array();
    post['rappelinst_tel']=tel;
    post['rappelinst_nom']=document.getElementById('rappelrem_nom').value;
    if(document.getElementById('rappelinst_prenom')) post['rappelrem_prenom']=document.getElementById('rappelrem_prenom').value;
    post['rappelinst_mail']=document.getElementById('rappelrem_mail').value;
    post['url']=document.URL;
    document.getElementById('rappelrem_form').style.display='none';
    document.getElementById('rappelrem_msg').style.display='block';
    document.getElementById('rappelrem_msg').innerHTML="<img src=\"/images/templates/wait.gif\">";
    
    postcontact(post,rappelremcb);
  }

  function rappelremcb(httprequest){
    document.getElementById('rappelrem_msg').innerHTML=httprequest.responseText;
  }


  function postcontact(post,cb){
    ajaxrequest("/contact_ajax.php",cb,post);
  }

//Ajout/Suppression d'un produit au/du comparateur
function toggleCompare(artid){
    //clearTimeout(timeout);
    var rand = Math.floor(Math.random()*Math.pow(10,9))+Math.pow(10,6);
    ajaxrequest('/ajax/toggle_compare.php?artid='+artid+'&rand='+rand,compare_cb);

  }

  function compare_cb(httprequest){
    var arrayResponse = httprequest.responseText.split('|||');
    var artid = arrayResponse[0];
    var className = arrayResponse[1];
    var catComparateur = arrayResponse[2];
    var txtBtnCompare = arrayResponse[3];
    var catPdt = arrayResponse[4];
    var nbPdtsCat = arrayResponse[5];
    var nbPdtsTot = arrayResponse[6];
    var checkAjout = arrayResponse[7];
    var btn_article_txt = arrayResponse[8];
    var comparateur_txt = arrayResponse[9];
    var check = arrayResponse[10];

  //Changement du style du bouton en fonction du résultat
  if(artid > 0){
    if(document.getElementById('btn_comparaison')){
      var btn1 = document.getElementById('btn_comparaison');
      btn1.className=className+' btn_comparer';
      var btn_check = document.createElement('i');
      btn_check.className = check;
      btn1.firstChild.remove();
      btn1.prepend(btn_check);
      var span=btn1.getElementsByTagName('span')[0];
      span.innerHTML=btn_article_txt;
    }
    if(document.getElementById('btn_comparaison'+artid)){
      $('body #btn_comparaison'+artid).children('i').remove();
      $('body #btn_comparaison'+artid).prepend('<i class="'+check+'"></i>');
      $('body #btn_comparaison'+artid).removeClass('btn_comparer_off btn_comparer_on').addClass( className );
      $('body #btn_comparaison_p'+artid).children('i').remove();
      $('body #btn_comparaison_p'+artid).prepend('<i class="'+check+'"></i>');
      $('body #btn_comparaison_p'+artid).removeClass('btn_comparer_off btn_comparer_on').addClass( className );
      $('body #span_comparaison'+artid).text(btn_article_txt);
      $('body #span_comparaison_p'+artid).text(btn_article_txt);
    }

            //Ouverture de la popup quand un produit lors de l'ajout d'un produit, si la popup est fermée
            if(checkAjout === '1'){
              clearTimeout(timeout);
              var timeout = setTimeout('hidebarform("popup_comparer")', 4000);

              var popup_pos=parseInt(document.getElementById('popup_comparer').style.bottom);
              if(isNaN(popup_pos) || popup_pos<=0){
                showbarform('popup_comparer');
              }
            }
            
            //Fermeture de la popup lors de la suppression d'un produit, si la popup est ouverte
            if(checkAjout === '2' && document.getElementById('popup_comparer').style.display === 'block'){
              hidebarform('popup_comparer');
            }
          }

          $('#nb_pdts_comparateur').html(txtBtnCompare);

        //Changement des intitulés en fonctions du nombre de produits encore présents dans le comparateur
        if(document.getElementById('txt_comparateur'))
          document.getElementById('txt_comparateur').innerHTML=comparateur_txt;

        //Si ajout de produit, on affiche le nouveau produit ajouté dans la popup
        if(artid > 0 && document.getElementById('article_ajoute')){
          ajaxrequest('/ajax/affiche_article_compare.php?artid='+artid,affiche_article_cb);
          document.getElementById('ajout_comparateur').style.display=(checkAjout == '1' ? "block" : "none");
        }

        $('#trop').hide();
        if(checkAjout==='3' && document.getElementById('trop')){
          $('#trop').show();
          clearTimeout(timeout);
          var timeout = setTimeout('hidebarform("popup_comparer")', 4000);

          var popup_pos=parseInt(document.getElementById('popup_comparer').style.bottom);
          if(isNaN(popup_pos) || popup_pos<=0){
            showbarform('popup_comparer');
          }
        }
        
        //Remplacement du contenu de la div comparaison footer
        if(document.getElementById('comparer_categorie'))
         document.getElementById('comparer_categorie').innerHTML=catComparateur;

       if(document.getElementById('comparateur')){
        if(nbPdtsCat > 0){
          var tbtitle=document.getElementById('TB_ajaxWindowTitle').innerHTML;
          tb_show(tbtitle,'/comparateur.php?width=945&typearticleid='+catPdt,false);
        } else {
          tb_remove();
        }
      }
    }

    function affiche_article_cb(httprequest){
     document.getElementById('article_ajoute').innerHTML = httprequest.responseText;
   }

   function vider_comparateur(typearticleid){
    var rand = Math.floor(Math.random()*Math.pow(10,9))+Math.pow(10,6);
    ajaxrequest("/ajax/clean_compare.php?typearticleid="+typearticleid+"&rand="+rand, vider_cb);

  }

  function vider_config(s){
    ajaxrequest("/ajax/clean_config.php?s="+s, vider_div_config);
  }

  function vider_artvu(s){
    ajaxrequest("/ajax/clean_artvu.php?s="+s, vider_div_artvu);
  }

  function vider_div_config(httprequest) {
    $("#"+httprequest.responseText).find('.msg').hide();
    $('#popup_configbtn').find('#nb_configs').text('0');
  }

  function vider_div_artvu(httprequest) {
    $('#articlevusbarbtn').css('width', '169px');
    $('#articlevusbarbtn').css('opacity', '1');
    $('#articlevusbarbtn').css('bottom', '0');
    $('#articlevusbar').hide();
    $("#"+httprequest.responseText).find('.artvu').hide();
    $('#articlevusbarbtn').find('#nb_configs').text('0');
  }


  function vider_cb(httprequest){
    var data=httprequest.responseText.split("|||");
    var del=data[0].split(",");
    var btn_txt=data[1];
    
    for(i=0;i<del.length;i++){
      if(document.getElementById('btn_comparaison'+del[i])){
        document.getElementById('btn_comparaison'+del[i]).className="btn_comparer_off btn_comparer";
        document.getElementById('span_comparaison'+del[i]).innerHTML=btn_txt;
        $('body #btn_comparaison'+del[i]).children('i').remove();
        $('body #btn_comparaison'+del[i]).prepend('<i class="far fa-square"></i>');
      }
    }
    
    if(document.getElementById('btn_comparaison')){
      document.getElementById('btn_comparaison').className="btn_comparer_off btn_comparer";
      document.getElementById('btn_comparaison').innerHTML=btn_txt;

    }
    
  //Mise à jour de la popup du footer
  toggleCompare(0);

  //Fermeture du comparateur
  tb_remove();
  $("#ajout_comparateur").css('display', 'none');
}

function comparateur_load(){
  toggleCompare(0);
}

  if($(".btnvf").css('display') === 'block') {
     $(window).resize(function(){
       if($(window).width() < 1199) {
    $("#articlebar .center .button").css('margin', '0 2px 0 0');
  }
  else {
     $("#articlebar .center .button").css('margin', '0 10px 0 0');
  }
});
          if($(window).width() < 1199) {
    $("#articlebar .center .button").css('margin', '0 2px 0 0');
  }
  else {
     $("#articlebar .center .button").css('margin', '0 10px 0 0');
  }
}

/*! nouislider - 8.0.2 - 2015-07-06 13:22:09 */

!function(a){if("function"==typeof define&&define.amd)define([],a);else if("object"==typeof exports){var b=require("fs");module.exports=a(),module.exports.css=function(){return b.readFileSync(__dirname+"/nouislider.min.css","utf8")}}else window.noUiSlider=a()}(function(){"use strict";function a(a){return a.filter(function(a){return this[a]?!1:this[a]=!0},{})}function b(a,b){return Math.round(a/b)*b}function c(a){var b=a.getBoundingClientRect(),c=a.ownerDocument,d=c.defaultView||c.parentWindow,e=c.documentElement,f=d.pageXOffset;return/webkit.*Chrome.*Mobile/i.test(navigator.userAgent)&&(f=0),{top:b.top+d.pageYOffset-e.clientTop,left:b.left+f-e.clientLeft}}function d(a){return"number"==typeof a&&!isNaN(a)&&isFinite(a)}function e(a){var b=Math.pow(10,7);return Number((Math.round(a*b)/b).toFixed(7))}function f(a,b,c){j(a,b),setTimeout(function(){k(a,b)},c)}function g(a){return Math.max(Math.min(a,100),0)}function h(a){return Array.isArray(a)?a:[a]}function i(a){var b=a.split(".");return b.length>1?b[1].length:0}function j(a,b){a.classList?a.classList.add(b):a.className+=" "+b}function k(a,b){a.classList?a.classList.remove(b):a.className=a.className.replace(new RegExp("(^|\\b)"+b.split(" ").join("|")+"(\\b|$)","gi")," ")}function l(a,b){a.classList?a.classList.contains(b):new RegExp("(^| )"+b+"( |$)","gi").test(a.className)}function m(a,b){return 100/(b-a)}function n(a,b){return 100*b/(a[1]-a[0])}function o(a,b){return n(a,a[0]<0?b+Math.abs(a[0]):b-a[0])}function p(a,b){return b*(a[1]-a[0])/100+a[0]}function q(a,b){for(var c=1;a>=b[c];)c+=1;return c}function r(a,b,c){if(c>=a.slice(-1)[0])return 100;var d,e,f,g,h=q(c,a);return d=a[h-1],e=a[h],f=b[h-1],g=b[h],f+o([d,e],c)/m(f,g)}function s(a,b,c){if(c>=100)return a.slice(-1)[0];var d,e,f,g,h=q(c,b);return d=a[h-1],e=a[h],f=b[h-1],g=b[h],p([d,e],(c-f)*m(f,g))}function t(a,c,d,e){if(100===e)return e;var f,g,h=q(e,a);return d?(f=a[h-1],g=a[h],e-f>(g-f)/2?g:f):c[h-1]?a[h-1]+b(e-a[h-1],c[h-1]):e}function u(a,b,c){var e;if("number"==typeof b&&(b=[b]),"[object Array]"!==Object.prototype.toString.call(b))throw new Error("noUiSlider: 'range' contains invalid value.");if(e="min"===a?0:"max"===a?100:parseFloat(a),!d(e)||!d(b[0]))throw new Error("noUiSlider: 'range' value isn't numeric.");c.xPct.push(e),c.xVal.push(b[0]),e?c.xSteps.push(isNaN(b[1])?!1:b[1]):isNaN(b[1])||(c.xSteps[0]=b[1])}function v(a,b,c){return b?void(c.xSteps[a]=n([c.xVal[a],c.xVal[a+1]],b)/m(c.xPct[a],c.xPct[a+1])):!0}function w(a,b,c,d){this.xPct=[],this.xVal=[],this.xSteps=[d||!1],this.xNumSteps=[!1],this.snap=b,this.direction=c;var e,f=[];for(e in a)a.hasOwnProperty(e)&&f.push([a[e],e]);for(f.sort(function(a,b){return a[0]-b[0]}),e=0;e<f.length;e++)u(f[e][1],f[e][0],this);for(this.xNumSteps=this.xSteps.slice(0),e=0;e<this.xNumSteps.length;e++)v(e,this.xNumSteps[e],this)}function x(a,b){if(!d(b))throw new Error("noUiSlider: 'step' is not numeric.");a.singleStep=b}function y(a,b){if("object"!=typeof b||Array.isArray(b))throw new Error("noUiSlider: 'range' is not an object.");if(void 0===b.min||void 0===b.max)throw new Error("noUiSlider: Missing 'min' or 'max' in 'range'.");a.spectrum=new w(b,a.snap,a.dir,a.singleStep)}function z(a,b){if(b=h(b),!Array.isArray(b)||!b.length||b.length>2)throw new Error("noUiSlider: 'start' option is incorrect.");a.handles=b.length,a.start=b}function A(a,b){if(a.snap=b,"boolean"!=typeof b)throw new Error("noUiSlider: 'snap' option must be a boolean.")}function B(a,b){if(a.animate=b,"boolean"!=typeof b)throw new Error("noUiSlider: 'animate' option must be a boolean.")}function C(a,b){if("lower"===b&&1===a.handles)a.connect=1;else if("upper"===b&&1===a.handles)a.connect=2;else if(b===!0&&2===a.handles)a.connect=3;else{if(b!==!1)throw new Error("noUiSlider: 'connect' option doesn't match handle count.");a.connect=0}}function D(a,b){switch(b){case"horizontal":a.ort=0;break;case"vertical":a.ort=1;break;default:throw new Error("noUiSlider: 'orientation' option is invalid.")}}function E(a,b){if(!d(b))throw new Error("noUiSlider: 'margin' option must be numeric.");if(a.margin=a.spectrum.getMargin(b),!a.margin)throw new Error("noUiSlider: 'margin' option is only supported on linear sliders.")}function F(a,b){if(!d(b))throw new Error("noUiSlider: 'limit' option must be numeric.");if(a.limit=a.spectrum.getMargin(b),!a.limit)throw new Error("noUiSlider: 'limit' option is only supported on linear sliders.")}function G(a,b){switch(b){case"ltr":a.dir=0;break;case"rtl":a.dir=1,a.connect=[0,2,1,3][a.connect];break;default:throw new Error("noUiSlider: 'direction' option was not recognized.")}}function H(a,b){if("string"!=typeof b)throw new Error("noUiSlider: 'behaviour' must be a string containing options.");var c=b.indexOf("tap")>=0,d=b.indexOf("drag")>=0,e=b.indexOf("fixed")>=0,f=b.indexOf("snap")>=0;a.events={tap:c||f,drag:d,fixed:e,snap:f}}function I(a,b){if(a.format=b,"function"==typeof b.to&&"function"==typeof b.from)return!0;throw new Error("noUiSlider: 'format' requires 'to' and 'from' methods.")}function J(a){var b,c={margin:0,limit:0,animate:!0,format:U};b={step:{r:!1,t:x},start:{r:!0,t:z},connect:{r:!0,t:C},direction:{r:!0,t:G},snap:{r:!1,t:A},animate:{r:!1,t:B},range:{r:!0,t:y},orientation:{r:!1,t:D},margin:{r:!1,t:E},limit:{r:!1,t:F},behaviour:{r:!0,t:H},format:{r:!1,t:I}};var d={connect:!1,direction:"ltr",behaviour:"tap",orientation:"horizontal"};return Object.keys(d).forEach(function(b){void 0===a[b]&&(a[b]=d[b])}),Object.keys(b).forEach(function(d){var e=b[d];if(void 0===a[d]){if(e.r)throw new Error("noUiSlider: '"+d+"' is required.");return!0}e.t(c,a[d])}),c.pips=a.pips,c.style=c.ort?"top":"left",c}function K(a,b,c){var d=a+b[0],e=a+b[1];return c?(0>d&&(e+=Math.abs(d)),e>100&&(d-=e-100),[g(d),g(e)]):[d,e]}function L(a){a.preventDefault();var b,c,d=0===a.type.indexOf("touch"),e=0===a.type.indexOf("mouse"),f=0===a.type.indexOf("pointer"),g=a;return 0===a.type.indexOf("MSPointer")&&(f=!0),d&&(b=a.changedTouches[0].pageX,c=a.changedTouches[0].pageY),(e||f)&&(b=a.clientX+window.pageXOffset,c=a.clientY+window.pageYOffset),g.points=[b,c],g.cursor=e||f,g}function M(a,b){var c=document.createElement("div"),d=document.createElement("div"),e=["-lower","-upper"];return a&&e.reverse(),j(d,T[3]),j(d,T[3]+e[b]),j(c,T[2]),c.appendChild(d),c}function N(a,b,c){switch(a){case 1:j(b,T[7]),j(c[0],T[6]);break;case 3:j(c[1],T[6]);case 2:j(c[0],T[7]);case 0:j(b,T[6])}}function O(a,b,c){var d,e=[];for(d=0;a>d;d+=1)e.push(c.appendChild(M(b,d)));return e}function P(a,b,c){j(c,T[0]),j(c,T[8+a]),j(c,T[4+b]);var d=document.createElement("div");return j(d,T[1]),c.appendChild(d),d}function Q(b,d){function e(a,b,c){if("range"===a||"steps"===a)return M.xVal;if("count"===a){var d,e=100/(b-1),f=0;for(b=[];(d=f++*e)<=100;)b.push(d);a="positions"}return"positions"===a?b.map(function(a){return M.fromStepping(c?M.getStep(a):a)}):"values"===a?c?b.map(function(a){return M.fromStepping(M.getStep(M.toStepping(a)))}):b:void 0}function m(b,c,d){var e=M.direction,f={},g=M.xVal[0],h=M.xVal[M.xVal.length-1],i=!1,j=!1,k=0;return M.direction=0,d=a(d.slice().sort(function(a,b){return a-b})),d[0]!==g&&(d.unshift(g),i=!0),d[d.length-1]!==h&&(d.push(h),j=!0),d.forEach(function(a,e){var g,h,l,m,n,o,p,q,r,s,t=a,u=d[e+1];if("steps"===c&&(g=M.xNumSteps[e]),g||(g=u-t),t!==!1&&void 0!==u)for(h=t;u>=h;h+=g){for(m=M.toStepping(h),n=m-k,q=n/b,r=Math.round(q),s=n/r,l=1;r>=l;l+=1)o=k+l*s,f[o.toFixed(5)]=["x",0];p=d.indexOf(h)>-1?1:"steps"===c?2:0,!e&&i&&(p=0),h===u&&j||(f[m.toFixed(5)]=[h,p]),k=m}}),M.direction=e,f}function n(a,b,c){function e(a){return["-normal","-large","-sub"][a]}function f(a,b,c){return'class="'+b+" "+b+"-"+h+" "+b+e(c[1])+'" style="'+d.style+": "+a+'%"'}function g(a,d){M.direction&&(a=100-a),d[1]=d[1]&&b?b(d[0],d[1]):d[1],i.innerHTML+="<div "+f(a,"noUi-marker",d)+"></div>",d[1]&&(i.innerHTML+="<div "+f(a,"noUi-value",d)+">"+c.to(d[0])+"</div>")}var h=["horizontal","vertical"][d.ort],i=document.createElement("div");return j(i,"noUi-pips"),j(i,"noUi-pips-"+h),Object.keys(a).forEach(function(b){g(b,a[b])}),i}function o(a){var b=a.mode,c=a.density||1,d=a.filter||!1,f=a.values||!1,g=a.stepped||!1,h=e(b,f,g),i=m(c,b,h),j=a.format||{to:Math.round};return I.appendChild(n(i,d,j))}function p(){return G["offset"+["Width","Height"][d.ort]]}function q(a,b){void 0!==b&&(b=Math.abs(b-d.dir)),Object.keys(R).forEach(function(c){var d=c.split(".")[0];a===d&&R[c].forEach(function(a){a(h(B()),b,r(Array.prototype.slice.call(Q)))})})}function r(a){return 1===a.length?a[0]:d.dir?a.reverse():a}function s(a,b,c,e){var f=function(b){return I.hasAttribute("disabled")?!1:l(I,T[14])?!1:(b=L(b),a===S.start&&void 0!==b.buttons&&b.buttons>1?!1:(b.calcPoint=b.points[d.ort],void c(b,e)))},g=[];return a.split(" ").forEach(function(a){b.addEventListener(a,f,!1),g.push([a,f])}),g}function t(a,b){var c,d,e=b.handles||H,f=!1,g=100*(a.calcPoint-b.start)/p(),h=e[0]===H[0]?0:1;if(c=K(g,b.positions,e.length>1),f=y(e[0],c[h],1===e.length),e.length>1){if(f=y(e[1],c[h?0:1],!1)||f)for(d=0;d<b.handles.length;d++)q("slide",d)}else f&&q("slide",h)}function u(a,b){var c=G.getElementsByClassName(T[15]),d=b.handles[0]===H[0]?0:1;c.length&&k(c[0],T[15]),a.cursor&&(document.body.style.cursor="",document.body.removeEventListener("selectstart",document.body.noUiListener));var e=document.documentElement;e.noUiListeners.forEach(function(a){e.removeEventListener(a[0],a[1])}),k(I,T[12]),q("set",d),q("change",d)}function v(a,b){var c=document.documentElement;if(1===b.handles.length&&(j(b.handles[0].children[0],T[15]),b.handles[0].hasAttribute("disabled")))return!1;a.stopPropagation();var d=s(S.move,c,t,{start:a.calcPoint,handles:b.handles,positions:[J[0],J[H.length-1]]}),e=s(S.end,c,u,{handles:b.handles});if(c.noUiListeners=d.concat(e),a.cursor){document.body.style.cursor=getComputedStyle(a.target).cursor,H.length>1&&j(I,T[12]);var f=function(){return!1};document.body.noUiListener=f,document.body.addEventListener("selectstart",f,!1)}}function w(a){var b,e,g=a.calcPoint,h=0;return a.stopPropagation(),H.forEach(function(a){h+=c(a)[d.style]}),b=h/2>g||1===H.length?0:1,g-=c(G)[d.style],e=100*g/p(),d.events.snap||f(I,T[14],300),H[b].hasAttribute("disabled")?!1:(y(H[b],e),q("slide",b),q("set",b),q("change",b),void(d.events.snap&&v(a,{handles:[H[h]]})))}function x(a){var b,c;if(!a.fixed)for(b=0;b<H.length;b+=1)s(S.start,H[b].children[0],v,{handles:[H[b]]});a.tap&&s(S.start,G,w,{handles:H}),a.drag&&(c=[G.getElementsByClassName(T[7])[0]],j(c[0],T[10]),a.fixed&&c.push(H[c[0]===H[0]?1:0].children[0]),c.forEach(function(a){s(S.start,a,v,{handles:H})}))}function y(a,b,c){var e=a!==H[0]?1:0,f=J[0]+d.margin,h=J[1]-d.margin,i=J[0]+d.limit,l=J[1]-d.limit;return H.length>1&&(b=e?Math.max(b,f):Math.min(b,h)),c!==!1&&d.limit&&H.length>1&&(b=e?Math.min(b,i):Math.max(b,l)),b=M.getStep(b),b=g(parseFloat(b.toFixed(7))),b===J[e]?!1:(a.style[d.style]=b+"%",a.previousSibling||(k(a,T[17]),b>50&&j(a,T[17])),J[e]=b,Q[e]=M.fromStepping(b),q("update",e),!0)}function z(a,b){var c,e,f;for(d.limit&&(a+=1),c=0;a>c;c+=1)e=c%2,f=b[e],null!==f&&f!==!1&&("number"==typeof f&&(f=String(f)),f=d.format.from(f),(f===!1||isNaN(f)||y(H[e],M.toStepping(f),c===3-d.dir)===!1)&&q("update",e))}function A(a){var b,c,e=h(a);for(d.dir&&d.handles>1&&e.reverse(),d.animate&&-1!==J[0]&&f(I,T[14],300),b=H.length>1?3:1,1===e.length&&(b=1),z(b,e),c=0;c<H.length;c++)q("set",c)}function B(){var a,b=[];for(a=0;a<d.handles;a+=1)b[a]=d.format.to(Q[a]);return r(b)}function C(){T.forEach(function(a){a&&k(I,a)}),I.innerHTML="",delete I.noUiSlider}function D(){var a=J.map(function(a,b){var c=M.getApplicableStep(a),d=i(String(c[2])),e=Q[b],f=100===a?null:c[2],g=Number((e-c[2]).toFixed(d)),h=0===a?null:g>=c[1]?c[2]:c[0]||!1;return[h,f]});return r(a)}function E(a,b){R[a]=R[a]||[],R[a].push(b),"update"===a.split(".")[0]&&H.forEach(function(a,b){q("update",b)})}function F(a){var b=a.split(".")[0],c=a.substring(b.length);Object.keys(R).forEach(function(a){var d=a.split(".")[0],e=a.substring(d.length);b&&b!==d||c&&c!==e||delete R[a]})}var G,H,I=b,J=[-1,-1],M=d.spectrum,Q=[],R={};if(I.noUiSlider)throw new Error("Slider was already initialized.");return G=P(d.dir,d.ort,I),H=O(d.handles,d.dir,G),N(d.connect,I,H),x(d.events),d.pips&&o(d.pips),{destroy:C,steps:D,on:E,off:F,get:B,set:A}}function R(a,b){if(!a.nodeName)throw new Error("noUiSlider.create requires a single element.");var c=J(b,a),d=Q(a,c);d.set(c.start),a.noUiSlider=d}var S=window.navigator.pointerEnabled?{start:"pointerdown",move:"pointermove",end:"pointerup"}:window.navigator.msPointerEnabled?{start:"MSPointerDown",move:"MSPointerMove",end:"MSPointerUp"}:{start:"mousedown touchstart",move:"mousemove touchmove",end:"mouseup touchend"},T=["noUi-target","noUi-base","noUi-origin","noUi-handle","noUi-horizontal","noUi-vertical","noUi-background","noUi-connect","noUi-ltr","noUi-rtl","noUi-dragable","","noUi-state-drag","","noUi-state-tap","noUi-active","","noUi-stacking"];w.prototype.getMargin=function(a){return 2===this.xPct.length?n(this.xVal,a):!1},w.prototype.toStepping=function(a){return a=r(this.xVal,this.xPct,a),this.direction&&(a=100-a),a},w.prototype.fromStepping=function(a){return this.direction&&(a=100-a),e(s(this.xVal,this.xPct,a))},w.prototype.getStep=function(a){return this.direction&&(a=100-a),a=t(this.xPct,this.xSteps,this.snap,a),this.direction&&(a=100-a),a},w.prototype.getApplicableStep=function(a){var b=q(a,this.xPct),c=100===a?2:1;return[this.xNumSteps[b-2],this.xVal[b-c],this.xNumSteps[b-c]]},w.prototype.convert=function(a){return this.getStep(this.toStepping(a))};var U={to:function(a){return a.toFixed(2)},from:Number};return{create:R}});
!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.bowser=t():e.bowser=t()}(this,(function(){return function(e){var t={};function r(i){if(t[i])return t[i].exports;var n=t[i]={i:i,l:!1,exports:{}};return e[i].call(n.exports,n,n.exports,r),n.l=!0,n.exports}return r.m=e,r.c=t,r.d=function(e,t,i){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:i})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(r.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)r.d(i,n,function(t){return e[t]}.bind(null,n));return i},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=90)}({17:function(e,t,r){"use strict";t.__esModule=!0,t.default=void 0;var i=r(18),n=function(){function e(){}return e.getFirstMatch=function(e,t){var r=t.match(e);return r&&r.length>0&&r[1]||""},e.getSecondMatch=function(e,t){var r=t.match(e);return r&&r.length>1&&r[2]||""},e.matchAndReturnConst=function(e,t,r){if(e.test(t))return r},e.getWindowsVersionName=function(e){switch(e){case"NT":return"NT";case"XP":return"XP";case"NT 5.0":return"2000";case"NT 5.1":return"XP";case"NT 5.2":return"2003";case"NT 6.0":return"Vista";case"NT 6.1":return"7";case"NT 6.2":return"8";case"NT 6.3":return"8.1";case"NT 10.0":return"10";default:return}},e.getMacOSVersionName=function(e){var t=e.split(".").splice(0,2).map((function(e){return parseInt(e,10)||0}));if(t.push(0),10===t[0])switch(t[1]){case 5:return"Leopard";case 6:return"Snow Leopard";case 7:return"Lion";case 8:return"Mountain Lion";case 9:return"Mavericks";case 10:return"Yosemite";case 11:return"El Capitan";case 12:return"Sierra";case 13:return"High Sierra";case 14:return"Mojave";case 15:return"Catalina";default:return}},e.getAndroidVersionName=function(e){var t=e.split(".").splice(0,2).map((function(e){return parseInt(e,10)||0}));if(t.push(0),!(1===t[0]&&t[1]<5))return 1===t[0]&&t[1]<6?"Cupcake":1===t[0]&&t[1]>=6?"Donut":2===t[0]&&t[1]<2?"Eclair":2===t[0]&&2===t[1]?"Froyo":2===t[0]&&t[1]>2?"Gingerbread":3===t[0]?"Honeycomb":4===t[0]&&t[1]<1?"Ice Cream Sandwich":4===t[0]&&t[1]<4?"Jelly Bean":4===t[0]&&t[1]>=4?"KitKat":5===t[0]?"Lollipop":6===t[0]?"Marshmallow":7===t[0]?"Nougat":8===t[0]?"Oreo":9===t[0]?"Pie":void 0},e.getVersionPrecision=function(e){return e.split(".").length},e.compareVersions=function(t,r,i){void 0===i&&(i=!1);var n=e.getVersionPrecision(t),s=e.getVersionPrecision(r),o=Math.max(n,s),a=0,u=e.map([t,r],(function(t){var r=o-e.getVersionPrecision(t),i=t+new Array(r+1).join(".0");return e.map(i.split("."),(function(e){return new Array(20-e.length).join("0")+e})).reverse()}));for(i&&(a=o-Math.min(n,s)),o-=1;o>=a;){if(u[0][o]>u[1][o])return 1;if(u[0][o]===u[1][o]){if(o===a)return 0;o-=1}else if(u[0][o]<u[1][o])return-1}},e.map=function(e,t){var r,i=[];if(Array.prototype.map)return Array.prototype.map.call(e,t);for(r=0;r<e.length;r+=1)i.push(t(e[r]));return i},e.getBrowserAlias=function(e){return i.BROWSER_ALIASES_MAP[e]},e.getBrowserTypeByAlias=function(e){return i.BROWSER_MAP[e]||""},e}();t.default=n,e.exports=t.default},18:function(e,t,r){"use strict";t.__esModule=!0,t.ENGINE_MAP=t.OS_MAP=t.PLATFORMS_MAP=t.BROWSER_MAP=t.BROWSER_ALIASES_MAP=void 0;t.BROWSER_ALIASES_MAP={"Amazon Silk":"amazon_silk","Android Browser":"android",Bada:"bada",BlackBerry:"blackberry",Chrome:"chrome",Chromium:"chromium",Epiphany:"epiphany",Firefox:"firefox",Focus:"focus",Generic:"generic","Google Search":"google_search",Googlebot:"googlebot","Internet Explorer":"ie","K-Meleon":"k_meleon",Maxthon:"maxthon","Microsoft Edge":"edge","MZ Browser":"mz","NAVER Whale Browser":"naver",Opera:"opera","Opera Coast":"opera_coast",PhantomJS:"phantomjs",Puffin:"puffin",QupZilla:"qupzilla",QQ:"qq",QQLite:"qqlite",Safari:"safari",Sailfish:"sailfish","Samsung Internet for Android":"samsung_internet",SeaMonkey:"seamonkey",Sleipnir:"sleipnir",Swing:"swing",Tizen:"tizen","UC Browser":"uc",Vivaldi:"vivaldi","WebOS Browser":"webos",WeChat:"wechat","Yandex Browser":"yandex",Roku:"roku"};t.BROWSER_MAP={amazon_silk:"Amazon Silk",android:"Android Browser",bada:"Bada",blackberry:"BlackBerry",chrome:"Chrome",chromium:"Chromium",epiphany:"Epiphany",firefox:"Firefox",focus:"Focus",generic:"Generic",googlebot:"Googlebot",google_search:"Google Search",ie:"Internet Explorer",k_meleon:"K-Meleon",maxthon:"Maxthon",edge:"Microsoft Edge",mz:"MZ Browser",naver:"NAVER Whale Browser",opera:"Opera",opera_coast:"Opera Coast",phantomjs:"PhantomJS",puffin:"Puffin",qupzilla:"QupZilla",qq:"QQ Browser",qqlite:"QQ Browser Lite",safari:"Safari",sailfish:"Sailfish",samsung_internet:"Samsung Internet for Android",seamonkey:"SeaMonkey",sleipnir:"Sleipnir",swing:"Swing",tizen:"Tizen",uc:"UC Browser",vivaldi:"Vivaldi",webos:"WebOS Browser",wechat:"WeChat",yandex:"Yandex Browser"};t.PLATFORMS_MAP={tablet:"tablet",mobile:"mobile",desktop:"desktop",tv:"tv"};t.OS_MAP={WindowsPhone:"Windows Phone",Windows:"Windows",MacOS:"macOS",iOS:"iOS",Android:"Android",WebOS:"WebOS",BlackBerry:"BlackBerry",Bada:"Bada",Tizen:"Tizen",Linux:"Linux",ChromeOS:"Chrome OS",PlayStation4:"PlayStation 4",Roku:"Roku"};t.ENGINE_MAP={EdgeHTML:"EdgeHTML",Blink:"Blink",Trident:"Trident",Presto:"Presto",Gecko:"Gecko",WebKit:"WebKit"}},90:function(e,t,r){"use strict";t.__esModule=!0,t.default=void 0;var i,n=(i=r(91))&&i.__esModule?i:{default:i},s=r(18);function o(e,t){for(var r=0;r<t.length;r++){var i=t[r];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var a=function(){function e(){}var t,r,i;return e.getParser=function(e,t){if(void 0===t&&(t=!1),"string"!=typeof e)throw new Error("UserAgent should be a string");return new n.default(e,t)},e.parse=function(e){return new n.default(e).getResult()},t=e,i=[{key:"BROWSER_MAP",get:function(){return s.BROWSER_MAP}},{key:"ENGINE_MAP",get:function(){return s.ENGINE_MAP}},{key:"OS_MAP",get:function(){return s.OS_MAP}},{key:"PLATFORMS_MAP",get:function(){return s.PLATFORMS_MAP}}],(r=null)&&o(t.prototype,r),i&&o(t,i),e}();t.default=a,e.exports=t.default},91:function(e,t,r){"use strict";t.__esModule=!0,t.default=void 0;var i=u(r(92)),n=u(r(93)),s=u(r(94)),o=u(r(95)),a=u(r(17));function u(e){return e&&e.__esModule?e:{default:e}}var d=function(){function e(e,t){if(void 0===t&&(t=!1),null==e||""===e)throw new Error("UserAgent parameter can't be empty");this._ua=e,this.parsedResult={},!0!==t&&this.parse()}var t=e.prototype;return t.getUA=function(){return this._ua},t.test=function(e){return e.test(this._ua)},t.parseBrowser=function(){var e=this;this.parsedResult.browser={};var t=i.default.find((function(t){if("function"==typeof t.test)return t.test(e);if(t.test instanceof Array)return t.test.some((function(t){return e.test(t)}));throw new Error("Browser's test function is not valid")}));return t&&(this.parsedResult.browser=t.describe(this.getUA())),this.parsedResult.browser},t.getBrowser=function(){return this.parsedResult.browser?this.parsedResult.browser:this.parseBrowser()},t.getBrowserName=function(e){return e?String(this.getBrowser().name).toLowerCase()||"":this.getBrowser().name||""},t.getBrowserVersion=function(){return this.getBrowser().version},t.getOS=function(){return this.parsedResult.os?this.parsedResult.os:this.parseOS()},t.parseOS=function(){var e=this;this.parsedResult.os={};var t=n.default.find((function(t){if("function"==typeof t.test)return t.test(e);if(t.test instanceof Array)return t.test.some((function(t){return e.test(t)}));throw new Error("Browser's test function is not valid")}));return t&&(this.parsedResult.os=t.describe(this.getUA())),this.parsedResult.os},t.getOSName=function(e){var t=this.getOS().name;return e?String(t).toLowerCase()||"":t||""},t.getOSVersion=function(){return this.getOS().version},t.getPlatform=function(){return this.parsedResult.platform?this.parsedResult.platform:this.parsePlatform()},t.getPlatformType=function(e){void 0===e&&(e=!1);var t=this.getPlatform().type;return e?String(t).toLowerCase()||"":t||""},t.parsePlatform=function(){var e=this;this.parsedResult.platform={};var t=s.default.find((function(t){if("function"==typeof t.test)return t.test(e);if(t.test instanceof Array)return t.test.some((function(t){return e.test(t)}));throw new Error("Browser's test function is not valid")}));return t&&(this.parsedResult.platform=t.describe(this.getUA())),this.parsedResult.platform},t.getEngine=function(){return this.parsedResult.engine?this.parsedResult.engine:this.parseEngine()},t.getEngineName=function(e){return e?String(this.getEngine().name).toLowerCase()||"":this.getEngine().name||""},t.parseEngine=function(){var e=this;this.parsedResult.engine={};var t=o.default.find((function(t){if("function"==typeof t.test)return t.test(e);if(t.test instanceof Array)return t.test.some((function(t){return e.test(t)}));throw new Error("Browser's test function is not valid")}));return t&&(this.parsedResult.engine=t.describe(this.getUA())),this.parsedResult.engine},t.parse=function(){return this.parseBrowser(),this.parseOS(),this.parsePlatform(),this.parseEngine(),this},t.getResult=function(){return Object.assign({},this.parsedResult)},t.satisfies=function(e){var t=this,r={},i=0,n={},s=0;if(Object.keys(e).forEach((function(t){var o=e[t];"string"==typeof o?(n[t]=o,s+=1):"object"==typeof o&&(r[t]=o,i+=1)})),i>0){var o=Object.keys(r),a=o.find((function(e){return t.isOS(e)}));if(a){var u=this.satisfies(r[a]);if(void 0!==u)return u}var d=o.find((function(e){return t.isPlatform(e)}));if(d){var c=this.satisfies(r[d]);if(void 0!==c)return c}}if(s>0){var f=Object.keys(n).find((function(e){return t.isBrowser(e,!0)}));if(void 0!==f)return this.compareVersion(n[f])}},t.isBrowser=function(e,t){void 0===t&&(t=!1);var r=this.getBrowserName().toLowerCase(),i=e.toLowerCase(),n=a.default.getBrowserTypeByAlias(i);return t&&n&&(i=n.toLowerCase()),i===r},t.compareVersion=function(e){var t=[0],r=e,i=!1,n=this.getBrowserVersion();if("string"==typeof n)return">"===e[0]||"<"===e[0]?(r=e.substr(1),"="===e[1]?(i=!0,r=e.substr(2)):t=[],">"===e[0]?t.push(1):t.push(-1)):"="===e[0]?r=e.substr(1):"~"===e[0]&&(i=!0,r=e.substr(1)),t.indexOf(a.default.compareVersions(n,r,i))>-1},t.isOS=function(e){return this.getOSName(!0)===String(e).toLowerCase()},t.isPlatform=function(e){return this.getPlatformType(!0)===String(e).toLowerCase()},t.isEngine=function(e){return this.getEngineName(!0)===String(e).toLowerCase()},t.is=function(e){return this.isBrowser(e)||this.isOS(e)||this.isPlatform(e)},t.some=function(e){var t=this;return void 0===e&&(e=[]),e.some((function(e){return t.is(e)}))},e}();t.default=d,e.exports=t.default},92:function(e,t,r){"use strict";t.__esModule=!0,t.default=void 0;var i,n=(i=r(17))&&i.__esModule?i:{default:i};var s=/version\/(\d+(\.?_?\d+)+)/i,o=[{test:[/googlebot/i],describe:function(e){var t={name:"Googlebot"},r=n.default.getFirstMatch(/googlebot\/(\d+(\.\d+))/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/opera/i],describe:function(e){var t={name:"Opera"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:opera)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/opr\/|opios/i],describe:function(e){var t={name:"Opera"},r=n.default.getFirstMatch(/(?:opr|opios)[\s/](\S+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/SamsungBrowser/i],describe:function(e){var t={name:"Samsung Internet for Android"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:SamsungBrowser)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/Whale/i],describe:function(e){var t={name:"NAVER Whale Browser"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:whale)[\s/](\d+(?:\.\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/MZBrowser/i],describe:function(e){var t={name:"MZ Browser"},r=n.default.getFirstMatch(/(?:MZBrowser)[\s/](\d+(?:\.\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/focus/i],describe:function(e){var t={name:"Focus"},r=n.default.getFirstMatch(/(?:focus)[\s/](\d+(?:\.\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/swing/i],describe:function(e){var t={name:"Swing"},r=n.default.getFirstMatch(/(?:swing)[\s/](\d+(?:\.\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/coast/i],describe:function(e){var t={name:"Opera Coast"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:coast)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/yabrowser/i],describe:function(e){var t={name:"Yandex Browser"},r=n.default.getFirstMatch(/(?:yabrowser)[\s/](\d+(\.?_?\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/ucbrowser/i],describe:function(e){var t={name:"UC Browser"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:ucbrowser)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/Maxthon|mxios/i],describe:function(e){var t={name:"Maxthon"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:Maxthon|mxios)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/epiphany/i],describe:function(e){var t={name:"Epiphany"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:epiphany)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/puffin/i],describe:function(e){var t={name:"Puffin"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:puffin)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/sleipnir/i],describe:function(e){var t={name:"Sleipnir"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:sleipnir)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/k-meleon/i],describe:function(e){var t={name:"K-Meleon"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/(?:k-meleon)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/micromessenger/i],describe:function(e){var t={name:"WeChat"},r=n.default.getFirstMatch(/(?:micromessenger)[\s/](\d+(\.?_?\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/qqbrowser/i],describe:function(e){var t={name:/qqbrowserlite/i.test(e)?"QQ Browser Lite":"QQ Browser"},r=n.default.getFirstMatch(/(?:qqbrowserlite|qqbrowser)[/](\d+(\.?_?\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/msie|trident/i],describe:function(e){var t={name:"Internet Explorer"},r=n.default.getFirstMatch(/(?:msie |rv:)(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/\sedg\//i],describe:function(e){var t={name:"Microsoft Edge"},r=n.default.getFirstMatch(/\sedg\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/edg([ea]|ios)/i],describe:function(e){var t={name:"Microsoft Edge"},r=n.default.getSecondMatch(/edg([ea]|ios)\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/vivaldi/i],describe:function(e){var t={name:"Vivaldi"},r=n.default.getFirstMatch(/vivaldi\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/seamonkey/i],describe:function(e){var t={name:"SeaMonkey"},r=n.default.getFirstMatch(/seamonkey\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/sailfish/i],describe:function(e){var t={name:"Sailfish"},r=n.default.getFirstMatch(/sailfish\s?browser\/(\d+(\.\d+)?)/i,e);return r&&(t.version=r),t}},{test:[/silk/i],describe:function(e){var t={name:"Amazon Silk"},r=n.default.getFirstMatch(/silk\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/phantom/i],describe:function(e){var t={name:"PhantomJS"},r=n.default.getFirstMatch(/phantomjs\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/slimerjs/i],describe:function(e){var t={name:"SlimerJS"},r=n.default.getFirstMatch(/slimerjs\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/blackberry|\bbb\d+/i,/rim\stablet/i],describe:function(e){var t={name:"BlackBerry"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/blackberry[\d]+\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/(web|hpw)[o0]s/i],describe:function(e){var t={name:"WebOS Browser"},r=n.default.getFirstMatch(s,e)||n.default.getFirstMatch(/w(?:eb)?[o0]sbrowser\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/bada/i],describe:function(e){var t={name:"Bada"},r=n.default.getFirstMatch(/dolfin\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/tizen/i],describe:function(e){var t={name:"Tizen"},r=n.default.getFirstMatch(/(?:tizen\s?)?browser\/(\d+(\.?_?\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/qupzilla/i],describe:function(e){var t={name:"QupZilla"},r=n.default.getFirstMatch(/(?:qupzilla)[\s/](\d+(\.?_?\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/firefox|iceweasel|fxios/i],describe:function(e){var t={name:"Firefox"},r=n.default.getFirstMatch(/(?:firefox|iceweasel|fxios)[\s/](\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/chromium/i],describe:function(e){var t={name:"Chromium"},r=n.default.getFirstMatch(/(?:chromium)[\s/](\d+(\.?_?\d+)+)/i,e)||n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/chrome|crios|crmo/i],describe:function(e){var t={name:"Chrome"},r=n.default.getFirstMatch(/(?:chrome|crios|crmo)\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/GSA/i],describe:function(e){var t={name:"Google Search"},r=n.default.getFirstMatch(/(?:GSA)\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:function(e){var t=!e.test(/like android/i),r=e.test(/android/i);return t&&r},describe:function(e){var t={name:"Android Browser"},r=n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/playstation 4/i],describe:function(e){var t={name:"PlayStation 4"},r=n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/safari|applewebkit/i],describe:function(e){var t={name:"Safari"},r=n.default.getFirstMatch(s,e);return r&&(t.version=r),t}},{test:[/.*/i],describe:function(e){var t=-1!==e.search("\\(")?/^(.*)\/(.*)[ \t]\((.*)/:/^(.*)\/(.*) /;return{name:n.default.getFirstMatch(t,e),version:n.default.getSecondMatch(t,e)}}}];t.default=o,e.exports=t.default},93:function(e,t,r){"use strict";t.__esModule=!0,t.default=void 0;var i,n=(i=r(17))&&i.__esModule?i:{default:i},s=r(18);var o=[{test:[/Roku\/DVP/],describe:function(e){var t=n.default.getFirstMatch(/Roku\/DVP-(\d+\.\d+)/i,e);return{name:s.OS_MAP.Roku,version:t}}},{test:[/windows phone/i],describe:function(e){var t=n.default.getFirstMatch(/windows phone (?:os)?\s?(\d+(\.\d+)*)/i,e);return{name:s.OS_MAP.WindowsPhone,version:t}}},{test:[/windows/i],describe:function(e){var t=n.default.getFirstMatch(/Windows ((NT|XP)( \d\d?.\d)?)/i,e),r=n.default.getWindowsVersionName(t);return{name:s.OS_MAP.Windows,version:t,versionName:r}}},{test:[/macintosh/i],describe:function(e){var t=n.default.getFirstMatch(/mac os x (\d+(\.?_?\d+)+)/i,e).replace(/[_\s]/g,"."),r=n.default.getMacOSVersionName(t),i={name:s.OS_MAP.MacOS,version:t};return r&&(i.versionName=r),i}},{test:[/(ipod|iphone|ipad)/i],describe:function(e){var t=n.default.getFirstMatch(/os (\d+([_\s]\d+)*) like mac os x/i,e).replace(/[_\s]/g,".");return{name:s.OS_MAP.iOS,version:t}}},{test:function(e){var t=!e.test(/like android/i),r=e.test(/android/i);return t&&r},describe:function(e){var t=n.default.getFirstMatch(/android[\s/-](\d+(\.\d+)*)/i,e),r=n.default.getAndroidVersionName(t),i={name:s.OS_MAP.Android,version:t};return r&&(i.versionName=r),i}},{test:[/(web|hpw)[o0]s/i],describe:function(e){var t=n.default.getFirstMatch(/(?:web|hpw)[o0]s\/(\d+(\.\d+)*)/i,e),r={name:s.OS_MAP.WebOS};return t&&t.length&&(r.version=t),r}},{test:[/blackberry|\bbb\d+/i,/rim\stablet/i],describe:function(e){var t=n.default.getFirstMatch(/rim\stablet\sos\s(\d+(\.\d+)*)/i,e)||n.default.getFirstMatch(/blackberry\d+\/(\d+([_\s]\d+)*)/i,e)||n.default.getFirstMatch(/\bbb(\d+)/i,e);return{name:s.OS_MAP.BlackBerry,version:t}}},{test:[/bada/i],describe:function(e){var t=n.default.getFirstMatch(/bada\/(\d+(\.\d+)*)/i,e);return{name:s.OS_MAP.Bada,version:t}}},{test:[/tizen/i],describe:function(e){var t=n.default.getFirstMatch(/tizen[/\s](\d+(\.\d+)*)/i,e);return{name:s.OS_MAP.Tizen,version:t}}},{test:[/linux/i],describe:function(){return{name:s.OS_MAP.Linux}}},{test:[/CrOS/],describe:function(){return{name:s.OS_MAP.ChromeOS}}},{test:[/PlayStation 4/],describe:function(e){var t=n.default.getFirstMatch(/PlayStation 4[/\s](\d+(\.\d+)*)/i,e);return{name:s.OS_MAP.PlayStation4,version:t}}}];t.default=o,e.exports=t.default},94:function(e,t,r){"use strict";t.__esModule=!0,t.default=void 0;var i,n=(i=r(17))&&i.__esModule?i:{default:i},s=r(18);var o=[{test:[/googlebot/i],describe:function(){return{type:"bot",vendor:"Google"}}},{test:[/huawei/i],describe:function(e){var t=n.default.getFirstMatch(/(can-l01)/i,e)&&"Nova",r={type:s.PLATFORMS_MAP.mobile,vendor:"Huawei"};return t&&(r.model=t),r}},{test:[/nexus\s*(?:7|8|9|10).*/i],describe:function(){return{type:s.PLATFORMS_MAP.tablet,vendor:"Nexus"}}},{test:[/ipad/i],describe:function(){return{type:s.PLATFORMS_MAP.tablet,vendor:"Apple",model:"iPad"}}},{test:[/kftt build/i],describe:function(){return{type:s.PLATFORMS_MAP.tablet,vendor:"Amazon",model:"Kindle Fire HD 7"}}},{test:[/silk/i],describe:function(){return{type:s.PLATFORMS_MAP.tablet,vendor:"Amazon"}}},{test:[/tablet(?! pc)/i],describe:function(){return{type:s.PLATFORMS_MAP.tablet}}},{test:function(e){var t=e.test(/ipod|iphone/i),r=e.test(/like (ipod|iphone)/i);return t&&!r},describe:function(e){var t=n.default.getFirstMatch(/(ipod|iphone)/i,e);return{type:s.PLATFORMS_MAP.mobile,vendor:"Apple",model:t}}},{test:[/nexus\s*[0-6].*/i,/galaxy nexus/i],describe:function(){return{type:s.PLATFORMS_MAP.mobile,vendor:"Nexus"}}},{test:[/[^-]mobi/i],describe:function(){return{type:s.PLATFORMS_MAP.mobile}}},{test:function(e){return"blackberry"===e.getBrowserName(!0)},describe:function(){return{type:s.PLATFORMS_MAP.mobile,vendor:"BlackBerry"}}},{test:function(e){return"bada"===e.getBrowserName(!0)},describe:function(){return{type:s.PLATFORMS_MAP.mobile}}},{test:function(e){return"windows phone"===e.getBrowserName()},describe:function(){return{type:s.PLATFORMS_MAP.mobile,vendor:"Microsoft"}}},{test:function(e){var t=Number(String(e.getOSVersion()).split(".")[0]);return"android"===e.getOSName(!0)&&t>=3},describe:function(){return{type:s.PLATFORMS_MAP.tablet}}},{test:function(e){return"android"===e.getOSName(!0)},describe:function(){return{type:s.PLATFORMS_MAP.mobile}}},{test:function(e){return"macos"===e.getOSName(!0)},describe:function(){return{type:s.PLATFORMS_MAP.desktop,vendor:"Apple"}}},{test:function(e){return"windows"===e.getOSName(!0)},describe:function(){return{type:s.PLATFORMS_MAP.desktop}}},{test:function(e){return"linux"===e.getOSName(!0)},describe:function(){return{type:s.PLATFORMS_MAP.desktop}}},{test:function(e){return"playstation 4"===e.getOSName(!0)},describe:function(){return{type:s.PLATFORMS_MAP.tv}}},{test:function(e){return"roku"===e.getOSName(!0)},describe:function(){return{type:s.PLATFORMS_MAP.tv}}}];t.default=o,e.exports=t.default},95:function(e,t,r){"use strict";t.__esModule=!0,t.default=void 0;var i,n=(i=r(17))&&i.__esModule?i:{default:i},s=r(18);var o=[{test:function(e){return"microsoft edge"===e.getBrowserName(!0)},describe:function(e){if(/\sedg\//i.test(e))return{name:s.ENGINE_MAP.Blink};var t=n.default.getFirstMatch(/edge\/(\d+(\.?_?\d+)+)/i,e);return{name:s.ENGINE_MAP.EdgeHTML,version:t}}},{test:[/trident/i],describe:function(e){var t={name:s.ENGINE_MAP.Trident},r=n.default.getFirstMatch(/trident\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:function(e){return e.test(/presto/i)},describe:function(e){var t={name:s.ENGINE_MAP.Presto},r=n.default.getFirstMatch(/presto\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:function(e){var t=e.test(/gecko/i),r=e.test(/like gecko/i);return t&&!r},describe:function(e){var t={name:s.ENGINE_MAP.Gecko},r=n.default.getFirstMatch(/gecko\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}},{test:[/(apple)?webkit\/537\.36/i],describe:function(){return{name:s.ENGINE_MAP.Blink}}},{test:[/(apple)?webkit/i],describe:function(e){var t={name:s.ENGINE_MAP.WebKit},r=n.default.getFirstMatch(/webkit\/(\d+(\.?_?\d+)+)/i,e);return r&&(t.version=r),t}}];t.default=o,e.exports=t.default}})}));
function visiteur(mail,provenance){
    var result = bowser.getParser(window.navigator.userAgent);
    var browser = result.parsedResult.browser.name;
    var bversion = result.parsedResult.browser.version;
    var device = result.parsedResult.os.name;

    $.ajax({
        
        url : '/ajax/visiteur.php?action=new&browser='+browser+'&v='+bversion+'&device='+device+'&mail='+mail+'&provenance='+provenance,
        dataType : 'text'

    });
}

function saveform(name, content){
    $.ajax({
        
        url : '/ajax/visiteur.php',
        type: 'POST',
        data: 'action=update&name='+name+'&content='+content,
        dataType : 'text'

    });
}

var MD5 = function(d){var r = M(V(Y(X(d),8*d.length)));return r.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_}
