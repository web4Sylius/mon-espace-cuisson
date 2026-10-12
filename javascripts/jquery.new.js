'use strict';
/** @type {boolean} */
var bloc_fermeture_option = true;
/** @type {boolean} */
var showtimer = false;
/** @type {boolean} */
var mobile = false;
var hparams = hashparams();
if (false !== hparams && "mobile" == hparams.utm_source) {
  setCookie("mobile", "0", 365);
}
var mobile_url = mobile_alternate();
/**
 * @return {undefined}
 */
function loadAjaxDelaisLivraison() {
  if ($("#delais_livraison_ajax").length) {
    $.ajax({
      type : "GET",
      url : "/ajax/article/delais_livraison.php",
      dataType : "text",
      data : {
        artid : $("#article_id").val()
      }
    }).done(function(usersLayoutTemplate) {
      $("#delais_livraison_ajax").html(usersLayoutTemplate);
    });
  }
}


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
  $('#devis_step1').hide();
  $('#devis_step2').hide();
  $('#overlay').hide();
}

/**
 * @return {undefined}
 */
function loadAjaxConfig() {
  $.ajax({
    url : "/ajax/footer_barre/configurations.php",
    dataType : "text"
  }).done(function(usersLayoutTemplate) {
    $("#popup_config").html(usersLayoutTemplate);
  });
}
/**
 * @return {undefined}
 */
function loadAjax() {
  $("#nb_configs").html('<img src="/images/loader.gif" style="width: 10px;">');
  $("#nb_pdts_comparateur").html('<img src="/images/loader.gif" style="width: 10px;">');
  $("#articlevusbarbtn").html('<img src="/images/loader.gif" style="width: 10px;">');
  var iconValue = $("#gclid_field").val();
  if ("" != iconValue) {
    /** @type {string} */
    var contentUrl = "&gclid=" + iconValue;
  } else {
    /** @type {string} */
    contentUrl = "";
  }
  $.ajax({
    url : "/ajax/loadAjax.php?r=" + encodeURIComponent(window.location.pathname + window.location.search + window.location.hash + contentUrl),
    dataType : "json"
  }).done(function(opts) {
    if ("" != opts.list_pdts_comparateur) {
      opts.list_pdts_comparateur.split(",").forEach(function(domRootID) {
        $("body #btn_comparaison" + domRootID).children("i").remove();
        $("body #btn_comparaison" + domRootID).prepend('<i class="fas fa-check"></i>');
        $("body #btn_comparaison" + domRootID).removeClass("btn_comparer_off").addClass("btn_comparer_on");
        $("body #span_comparaison" + domRootID).text(opts.bouton_comparateur);
      });
    }
    $("#nb_configs").html(opts.nb_configs);
    $("#nb_pdts_comparateur").html(opts.nb_pdts_comparateur);
    $("#articlevusbarbtn").html(opts.nb_articles_vus);
    $("#articlevusbar").html(opts.articles_vus);
    $("#popup_config").html(opts.config);
    $(opts.popup_panier).insertAfter("#bouton_panier");
    $("#nb_articles_panier").html(opts.nb_articles_panier);
    if (opts.nb_articles_panier > 0) {
      $("#bouton_panier").hover(function() {
        popup_header("popup_panier", true);
      }, function() {
        popup_header("popup_panier", false);
      });
    }
    $(opts.popup_compte).insertAfter("#bouton_compte");
  });
  toggleCompare(0);
}
/**
 * @return {undefined}
 */
function loadAjaxNnconfig() {
  $("#nb_configs").html('<img src="/images/loader.gif" style="width: 10px;">');
  $.ajax({
    url : "/ajax/loadAjax.php",
    dataType : "text"
  }).done(function(usersLayoutTemplate) {
    $("#nb_configs").html(usersLayoutTemplate);
  });
}
/**
 * @return {undefined}
 */
function loadAjaxNbPdtsComparateur() {
  $("#nb_pdts_comparateur").html('<img src="/images/loader.gif" style="width: 10px;">');
  $.ajax({
    url : "/ajax/footer_barre/nb_pdts_comparateur.php",
    dataType : "text"
  }).done(function(usersLayoutTemplate) {
    $("#nb_pdts_comparateur").html(usersLayoutTemplate);
  });
}
/**
 * @return {undefined}
 */
function loadAjaxArticlesVus() {
  $.ajax({
    url : "/ajax/footer_barre/articles_vus.php",
    dataType : "text"
  }).done(function(usersLayoutTemplate) {
    $("#articlevusbar").html(usersLayoutTemplate);
  });
}
/**
 * @return {undefined}
 */
function loadAjaxNbArticlesVus() {
  $("#articlevusbarbtn").html('<img src="/images/loader.gif" style="width: 10px;">');
  $.ajax({
    url : "/ajax/footer_barre/nb_articles_vus.php",
    dataType : "text"
  }).done(function(usersLayoutTemplate) {
    $("#articlevusbarbtn").html(usersLayoutTemplate);
  });
}
/**
 * @return {undefined}
 */
function loadAjaxPanier() {
  $("#nb_articles_panier").html('<img src="/images/loader.gif" style="width: 7px;">');
  $.ajax({
    url : "/ajax/panier/nb_produits.php?r=" + window.location.pathname + window.location.search + window.location.hash,
    dataType : "text"
  }).done(function(usersLayoutTemplate) {
    $("#nb_articles_panier").html(usersLayoutTemplate);
  });
  $.ajax({
    url : "/ajax/panier/popup_produits.php",
    dataType : "text"
  }).done(function(html) {
    $(html).insertAfter("#bouton_panier");
  });
}
/**
 * @return {undefined}
 */
function loadAjaxCompte() {
  $.ajax({
    url : "/ajax/compte/popup_compte.php",
    dataType : "text"
  }).done(function(html) {
    $(html).insertAfter("#bouton_compte");
  });
}
/**
 * @param {?} i
 * @param {!Object} list
 * @param {string} type
 * @return {undefined}
 */
function decocheOption(i, list, type) {
  removedElement = list.find("." + type);
  removedElement.remove();
  var box = list.find(".choix-0");
  if (void 0 === box.next("span").attr("class") && box.css("display", "inline"), "none" == box.css("display")) {
    /** @type {number} */
    var worker = 0;
    list.find("span").each(function() {
      currentElementClass = $(this).attr("class");
      nextElementClass = $(this).next("span").attr("class");
      if (1 == worker && "sep" == currentElementClass) {
        $(this).remove();
      }
      if ("undefined" == typeof nextElementClass && "sep" == currentElementClass) {
        $(this).remove();
      }
      if (worker > 0 && "sep" != currentElementClass && "sep" != nextElementClass) {
        $(this).after('<span class="sep">, </span>');
      }
      if (worker > 0 && "sep" == currentElementClass && "sep" == nextElementClass) {
        $(this).remove();
      }
      worker = worker + 1;
    });
  }
}
/**
 * @param {!Object} instance
 * @param {?} i
 * @param {!Object} t
 * @return {undefined}
 */
function cocheOption(instance, i, t) {
  var inpel = t.find(".choix-0");
  if ("inline" == inpel.css("display")) {
    inpel.css("display", "none");
    t.append('<span class="' + instance.parent().attr("class") + '">' + instance.text() + "</span>");
  } else {
    t.append('<span class="sep">, </span><span class="' + instance.parent().attr("class") + '">' + instance.text() + "</span>");
  }
}
/**
 * @param {?} e
 * @param {boolean} noSize
 * @return {undefined}
 */
function toggleButtonMenu(e, noSize) {
  $(".niveau1").each(function() {
    $(this).children(".tiret").removeClass("selected");
    $(this).find("a").removeClass("selected");
  });
  if (noSize) {
    $(e).children(".tiret").addClass("selected");
    $(e).css("cursor", "pointer");
    $(e).find("a").addClass("selected");
  }
}
/**
 * @param {?} e
 * @param {boolean} noSize
 * @return {undefined}
 */
function toggleButtonSousMenu(e, noSize) {
  $(".niveau2").each(function() {
    $(this).removeClass("selected");
  });
  if (noSize) {
    $(e).addClass("selected");
  }
}
/**
 * @param {string} url
 * @return {undefined}
 */
function showMenuResp(url) {
  if (url.substring(url.lastIndexOf("-") + 1, url.length) >= 4) {
    $("#menu_resp").animate({
      top : -102
    });
  }
}
/**
 * @param {?} anchor
 * @param {string} index
 * @return {undefined}
 */
function showMenu(anchor, index) {
  var output = $(anchor).attr("id");
  var propertyNameAttribute = output.substring(output.lastIndexOf("-") + 1, output.length);
  var r = $("#" + output).position();
  var el_rect = $("#" + index + "-" + propertyNameAttribute).position();
  var s = $("#fond-menus").position();
  /** @type {number} */
  var l = 0;
  if (null != el_rect && (el_rect.left = r.left - s.left, l = el_rect.left), $("#" + index + "-" + propertyNameAttribute).show().css("left", l), "sous-menu" == index) {
    /** @type {number} */
    var r = 0;
    $("#" + index + "-" + propertyNameAttribute).find(".niveau2").each(function() {
      r++;
    });
    /** @type {number} */
    r = 100 / r;
    Math.floor(r);
    1 / r * 2.8;
    "%";
  }
}
/**
 * @param {string} domRootID
 * @return {undefined}
 */
function hideClass(domRootID) {
  $("." + domRootID).hide();
}
/**
 * @return {undefined}
 */
function closeFauxSelects() {
  $(".faux_select").each(function() {
    $(this).find("ul").css("display", "none");
  });
}
/**
 * @param {?} el
 * @param {string} id
 * @return {undefined}
 */
function toggleSlide(el, id) {
  /** @type {string} */
  var target = "#" + id;
  $(target).slideToggle("fast", function() {
    if ("none" == $(target).css("display")) {
      $(el).removeClass("opened");
    } else {
      $(el).addClass("opened");
    }
  });
}
/**
 * @param {?} val
 * @param {string} opts
 * @param {string} t
 * @param {?} name
 * @param {?} value
 * @param {!Function} context
 * @param {string} message
 * @return {undefined}
 */
function checkOption(val, opts, t, name, value, context, message) {
  if (setLoaderOptions(), "-1" == $(val).prop("class").indexOf("disabled")) {
    if ("-1" != $(val).prop("class").indexOf("selected")) {
      /** @type {string} */
      var classes = "selected";
    } else {
      /** @type {string} */
      classes = "free";
    }
    if ("free" == classes) {
      var body = $(val).parent().parent();
      var $thisOdd = $(body).prev("div").find("div.centre_options");
      var moreSection = $(body).next("div").find("div.centre_options");
      if (bloc_fermeture_option || $($thisOdd).slideUp(), $(moreSection).slideDown(), !bloc_fermeture_option) {
        var panel = body.next("div").find("div.libelle_option").children("p#txt_options");
        $(panel).empty();
        $(panel).html($(panel).data("open") + ' <i class="fa fa-chevron-down fa-lg"></i>');
      }
      var panel = body.next("div").find("div.libelle_option").children("p#txt_options");
      $(panel).empty();
      $(panel).html($(panel).data("close") + ' <i class="fa fa-chevron-up fa-lg"></i>');
      $(body).find("div.encart_option").each(function(canCreateDiscussions) {
        $(this).removeClass("selected");
        $(this).children("input:radio").prop("checked", false);
      });
      $(val).addClass("selected");
      $(val).find("input:radio").prop("checked", true);
      doRequest(opts);
      option_preview_new(opts, t, name, false, context, message);
      $("#checked_opt_" + t).val(name);
    } else {
      $("#checked_opt_" + t).val("");
      $(val).removeClass("selected");
      $(val).find("input:radio").prop("checked", false);
      doRequest(opts);
      option_preview_new(opts, t, name, true);
    }
  }
  set_infos_disabled(opts);
}
/**
 * @param {string} pkgNameSlug
 * @param {string} i
 * @param {string} t
 * @param {string} n
 * @param {string} s
 * @return {undefined}
 */
function checkOptionSimu(pkgNameSlug, i, t, n, s) {
  $(".calque" + n).attr("src", "/configurations/www.ma-cave-a-vin.fr/images/simulateur/" + pkgNameSlug + "_" + i + "_" + t + ".png?" + s);
  $(".calque" + n).attr("value", i + "_" + t);
  /** @type {!Date} */
  var date = new Date;
  date.setTime(date.getTime() + 864E5);
  /** @type {string} */
  document.cookie = "calque" + n + "=" + i + "_" + t + ";expires=" + date.toUTCString() + ";";
}
/**
 * @param {string} done
 * @param {string} size
 * @param {?} i
 * @param {boolean} b
 * @param {!Function} a
 * @param {string} f
 * @return {undefined}
 */
function option_preview_new(done, size, i, b, a, f) {
  $.ajax({
    type : "GET",
    url : "/ajax/checkOption.php",
    dataType : "text",
    data : {
      caractvalid : i,
      artid : done
    }
  }).done(function(e) {
    if ($("#previewopt" + size).empty(), b ? $("#previewopt" + size).append('<span style="color:#DC081E;">A d\u00e9finir</span>') : $("#previewopt" + size).append(e), !b && a) {
      var t = $("#previewopt" + size).find("div");
      t.find("img").remove();
      t.prepend('<div class="ral-couleur" style="background-color:' + f + ';"></div>');
    }
  });
}
/**
 * @param {?} eta
 * @param {?} lmbda
 * @param {?} n
 * @return {undefined}
 */
function active_incompatibilite(eta, lmbda, n) {
  $.ajax({
    type : "GET",
    url : "/ajax/get_incompatibilites.php",
    dataType : "xml",
    data : {
      caractvalid : n,
      artid : eta
    },
    async : false
  }).done(function(text) {
    xmlDoc = $(text);
    xmlDoc.find("incompatibilite").each(function() {
      var m = $(this).attr("value");
      $(this).find("caracteristique").each(function() {
        var colix = $(this).attr("value");
        $(this).find("option").each(function() {
          var index = $(this).text();
          /** @type {!Array} */
          var p = new Array;
          p = $("#incomp_opt" + index).attr("value").split("|");
          /** @type {!Array} */
          var o = new Array;
          /** @type {number} */
          i = 0;
          for (; i < p.length; i++) {
            if ("" != p[i] && p[i] != m) {
              o[0] = p[i];
            }
          }
          /** @type {boolean} */
          var ret = true;
          if (o.length > 0 && "" != o[0]) {
            /** @type {boolean} */
            ret = false;
            /** @type {string} */
            var classesLine = o.join("|");
            $("#incomp_opt" + index).attr("value", classesLine);
          } else {
            $("#incomp_opt" + index).attr("value", "");
          }
          if (ret) {
            $("#option" + index).removeClass("disabled");
            $("#lst_" + colix + "_" + index).prop("disabled", false);
          }
        });
      });
    });
  });
}
/**
 * @param {?} rawPads
 * @param {?} i
 * @param {?} forceOptional
 * @return {undefined}
 */
function desactive_incompatibilite(rawPads, i, forceOptional) {
  $.ajax({
    type : "GET",
    url : "/ajax/get_incompatibilites.php",
    dataType : "xml",
    data : {
      caractvalid : forceOptional,
      artid : rawPads
    },
    async : false
  }).done(function(text) {
    xmlDoc = $(text);
    xmlDoc.find("incompatibilite").each(function() {
      var valueElement = $(this).attr("value");
      $(this).find("caracteristique").each(function() {
        var j = $(this).attr("value");
        $(this).find("option").each(function() {
          var index = $(this).text();
          /** @type {!Array} */
          var p = new Array;
          if ((p = $("#incomp_opt" + index).attr("value").split("|")).length >= 1 && "" != p[0]) {
            p[p.length] = valueElement;
          } else {
            p[0] = valueElement;
          }
          var classesLine = p.join("|");
          $("#incomp_opt" + index).attr("value", classesLine);
          if (j != i) {
            $("#option" + index).addClass("disabled");
            $("#lst_" + j + "_" + index).prop("disabled", true);
          }
        });
      });
    });
  });
}
/**
 * @param {string} apiKey
 * @return {undefined}
 */
function set_infos_disabled(apiKey) {
  $(".input_hidden_option").each(function() {
    var sValue = $(this).prop("value");
    if ("" != sValue) {
      new Array;
      sValue.split("|").each(function(i) {
        $.getJSON("/ajax/get_options_incompatibles.php?incompatibilite=" + i + "&artid=" + apiKey, function(arrayOfOptions) {
          /** @type {string} */
          var i = "";
          $.each(arrayOfOptions, function(index, maxAtomIndex) {
            if ("-1" != $("#option" + index).prop("class").indexOf("selected")) {
              /** @type {string} */
              i = maxAtomIndex;
            }
          });
          $.each(arrayOfOptions, function(index, canCreateDiscussions) {
            if ("-1" == $("#option" + index).prop("class").indexOf("selected")) {
              $("#info_disabled_" + index).css("display", "block");
              $("#lst_opt_dis_" + index).text(i);
            }
          });
        });
      });
    } else {
      var conid = $(this).prop("name").split("_")[2];
      $("#info_disabled_" + conid).css("display", "none");
      $("#lst_opt_dis_" + conid).empty();
    }
  });
  setLoaderOptions();
}
/**
 * @return {undefined}
 */
function setLoaderOptions() {
  if ($("#loader_options")) {
    var cooltime = $("#loader_options");
    var $continueButton = $("#encart_options");
    if ("none" == cooltime.css("display")) {
      cooltime.html('<img src="/images/loader.gif">');
      cooltime.css("display", "block");
      $continueButton.css("opacity", "0.2");
    } else {
      cooltime.empty();
      cooltime.css("display", "none");
      $continueButton.css("opacity", "1");
    }
  }
}
/**
 * @param {string} domRootID
 * @return {undefined}
 */
function show_ral(domRootID) {
  $(".ral_panel").each(function() {
    if ($(this).attr("id") != "ral_panel_" + domRootID) {
      $(this).hide();
    }
  });
  if (domRootID) {
    $("#ral_panel_" + domRootID).toggle();
  }
}
/**
 * @return {undefined}
 */
function close_ral() {
  $(".ral_panel").each(function() {
    $(this).hide();
  });
}
/**
 * @param {!Object} e
 * @param {string} i
 * @return {undefined}
 */
function filter_link(e, i) {
  e.href += "#" + i + document.documentElement.scrollTop;
  $(".wait").removeClass("hidden");
}
/**
 * @return {undefined}
 */
function scroll_search() {
  /** @type {string} */
  var hash = document.location.hash;
  /** @type {(Array<string>|null)} */
  var pos = /#([a-z]+)([0-9]+)/.exec(hash);
  if (null != pos) {
    window.scrollTo(0, pos[2]);
    setTimeout(function() {
      $("#filtre_" + pos[1]).find("ul").css("display", "block");
    }, 100);
  }
  $(".wait").addClass("hidden");
}
/**
 * @return {undefined}
 */
function change_reassurance() {
  var classes = $(".reassurance.slide").children("div:first")[0].className;
  /** @type {(Array<string>|null)} */
  var anchor = /img([0-9])/.exec(classes);
  if (null == anchor) {
    $(".reassurance.slide").children("div:first").addClass("img1");
  } else {
    /** @type {number} */
    var dir = parseInt(anchor[1]) + 1;
    if (dir > 8) {
      /** @type {number} */
      dir = 1;
    }
    $(".reassurance.slide").children("div:first").removeClass("img" + anchor[1]).addClass("img" + dir);
  }
  setTimeout(change_reassurance, 5E3);
}
/**
 * @param {string} prefix
 * @param {string} t
 * @return {undefined}
 */
function dimvalues(prefix, t) {
  /** @type {string} */
  var name = "";
  /** @type {!Array} */
  var params = new Array;
  /** @type {!Array} */
  var parts = new Array("larg_min", "larg_max", "haut_min", "haut_max", "prof_min", "prof_max");
  /** @type {number} */
  i = 0;
  for (; i < parts.length; i++) {
    if (document.getElementById(parts[i])) {
      params[parts[i]] = document.getElementById(parts[i]).value;
    }
  }
  for (index in params) {
    if (params.hasOwnProperty(index) && "" != params[index]) {
      /** @type {string} */
      name = name + (index + "=" + params[index] + "&");
    }
  }
  /** @type {string} */
  prefix = prefix + (prefix.indexOf("?") > -1 ? "&" : "?");
  /** @type {string} */
  document.location = prefix + name + "#" + t + document.documentElement.scrollTop;
  $(".wait").removeClass("hidden");
}
/**
 * @return {undefined}
 */
function vignscrollSize() {
  var e;
  var value = $(document).width();
  if (value > 1182 && value < 1574) {
    /** @type {number} */
    e = 0;
    for (; e < 4; e++) {
      $("#scroll_accessoires_btns > span.btn:eq(" + e + ")").addClass("selected");
      $("#scroll_similaire_btns > span.btn:eq(" + e + ")").addClass("selected");
    }
  } else {
    if (value > 1573) {
      /** @type {number} */
      e = 0;
      for (; e < 5; e++) {
        $("#scroll_accessoires_btns > span.btn:eq(" + e + ")").addClass("selected");
        $("#scroll_similaire_btns > span.btn:eq(" + e + ")").addClass("selected");
      }
    }
  }
}
/**
 * @param {string} d
 * @param {string} tx
 * @return {?}
 */
function vignscroll(d, tx) {
  var barcss = $("#scroll_" + d).children().first();
  var number = $(barcss).width() + parseInt($(barcss).css("margin-right"));
  /** @type {(Element|null)} */
  var entry = document.getElementById("scroll_" + d);
  if (!entry) {
    return false;
  }
  var y = $("#scroll_" + d + "_btns").find(".btn").length;
  var b = $("#scroll_" + d + "_btns").find(".selected").length;
  /** @type {number} */
  var z = y - b;
  /** @type {number} */
  var t = entry.scrollLeft;
  /** @type {number} */
  var perfectShotX = Math.round(t / number);
  /** @type {number} */
  var x = parseInt(tx);
  var chrs = tx.substr(0, 1);
  if ("-" == chrs) {
    /** @type {number} */
    x = perfectShotX - 1;
  }
  if ("+" == chrs) {
    /** @type {number} */
    x = perfectShotX + 1;
  }
  if (x > z) {
    /** @type {number} */
    x = z;
  }
  if (x < 0) {
    /** @type {number} */
    x = 0;
  }
  /** @type {number} */
  var intersect = 0;
  $("#scroll_" + d).animate({
    scrollLeft : parseInt(number * x)
  }, 750);
  /** @type {number} */
  $("#scroll_" + d).scrollLeft = parseInt(number * x);
  $("#scroll_" + d + "_btns").find(".btn").each(function() {
    if (intersect >= x && intersect < x + b) {
      $(this).addClass("selected");
    } else {
      $(this).removeClass("selected");
    }
    intersect++;
  });
}
/**
 * @param {string} twitterWidgetURL
 * @return {undefined}
 */
function change_artpic(twitterWidgetURL) {
  /** @type {string} */
  document.getElementById("artpic").src = twitterWidgetURL;
}
/**
 * @return {undefined}
 */
function arrange_options() {
  $(".centre_options").each(function() {
    /** @type {!Array} */
    var size = new Array;
    var tableDisplayCss = $(this).css("display");
    $(this).css({
      position : "absolute",
      visibility : "hidden",
      display : "block"
    });
    $(this).find(".encart_option .title").each(function() {
      size.push($(this).outerHeight());
    });
    /** @type {number} */
    var nHeight = Math.max.apply(Math, size);
    $(this).find(".encart_option .title").each(function() {
      $(this).height(nHeight);
    });
    $(this).css({
      position : "",
      visibility : "",
      display : tableDisplayCss
    });
  });
}
/**
 * @return {?}
 */
function mobile_alternate() {
  /** @type {!NodeList<Element>} */
  var columns = document.getElementsByTagName("link");
  for (x in columns) {
    if (columns.hasOwnProperty(x) && "alternate" == columns[x].getAttribute("rel") && "handheld" == columns[x].getAttribute("media")) {
      return columns[x].getAttribute("href");
    }
  }
  return false;
}
/**
 * @return {?}
 */
function hashparams() {
  /** @type {string} */
  var url = document.location.href;
  /** @type {number} */
  var anchorIndex = url.indexOf("#");
  if (-1 == anchorIndex) {
    return false;
  }
  /** @type {!Array<string>} */
  var paramsSplit = url.substring(anchorIndex + 1).split("&");
  /** @type {!Array} */
  var img_envs = new Array;
  /** @type {number} */
  i = 0;
  for (; i < paramsSplit.length; i++) {
    /** @type {!Array<string>} */
    var env_data = paramsSplit[i].split("=");
    /** @type {string} */
    img_envs[env_data[0]] = env_data[1];
  }
  return img_envs;
}



/**
 * @param {string} quizId
 * @param {?} courseId
 * @param {?} canCreateDiscussions
 * @return {undefined}
 */
function afficheAccessoiresPanier(quizId, courseId, canCreateDiscussions) {
  if ($("#scroll_accessoires_" + quizId).is(":visible")) {
    $("#scroll_accessoires_" + quizId).hide();
    $("#scrollbtnsparent_" + quizId).hide();
    $("#acces_panier_title_" + quizId).hide();
    $("#text_vignscroll_" + quizId).css("display", "none");
    $("#chev_left_" + quizId).removeClass("fa-chevron-up").addClass("fa-chevron-down");
    $("#chev_right_" + quizId).removeClass("fa-chevron-up").addClass("fa-chevron-down");
    if (courseId) {
      $("#borderTab").show();
    }
  } else {
    $("#scroll_accessoires_" + quizId).show();
    $("#scrollbtnsparent_" + quizId).show();
    $("#acces_panier_title_" + quizId).show();
    $("#text_vignscroll_" + quizId).css("display", "table");
    $("#chev_left_" + quizId).removeClass("fa-chevron-down").addClass("fa-chevron-up");
    $("#chev_right_" + quizId).removeClass("fa-chevron-down").addClass("fa-chevron-up");
    if (courseId) {
      $("#borderTab").hide();
    }
  }
}
/**
 * @return {undefined}
 */
function afficheBoutonAccesPanier() {
  $("#panier_accessoires").show();
}
isMobile() && 0 != mobile_url && "" === getCookie("mobile") && (document.location = mobile_url), $(window).scroll(function() {
  if ($(this).scrollTop() >= 50) {
    $("#return-to-top").fadeIn(200);
  } else {
    $("#return-to-top").fadeOut(200);
  }
}), $(window).scroll(function() {
  if ($(this).scrollTop() >= 300 && $(this).scrollTop() <= 1650) {
    $("#articlesimu").fadeIn(200);
  } else {
    $("#articlesimu").fadeOut(200);
  }
}), $("#return-to-top").click(function() {
  $("body,html").animate({
    scrollTop : 0
  }, 500);
}), $(window).resize(function() {
  var e = $(document).width();
  $(".index_vignette").unbind();
  if (e > 1182) {
    $(".index_vignette").mouseenter(function() {
      $(this).find(".index_hover").show();
      $(this).find(".img_ambiance").hide();
    }).mouseleave(function() {
      $(this).find(".index_hover").hide();
      $(this).find(".img_ambiance").show();
    });
  } else {
    $(this).find(".index_hover").hide();
    $(this).find(".img_ambiance").show();
  }
  $(".vignette").unbind();
  $(".encart_option").unbind();
  if (e > 974) {
    $(".vignette").mouseenter(function() {
      $(this).find("img.visuel").css("display", "none");
      $(this).find(".categorie_hover").show();
      $(this).find(".vfimg").hide();
    }).mouseleave(function() {
      $(this).find("img.visuel").css("display", "block");
      $(this).find(".categorie_hover").hide();
      $(this).find(".vfimg").show();
    });
    $(".encart_option").mousemove(function(event) {
      var i = $(this).next("div.visuel_zoom");
      var blockHeight = i.css("height");
      if (blockHeight) {
        blockHeight = blockHeight.substr(0, blockHeight.length - 2);
        $(window).height();
        parseInt(event.pageY);
        i.css({
          left : .5 * event.pageX,
          top : "unset"
        });
        i.show();
      }
    }).mouseleave(function(canCreateDiscussions) {
      if ($(this).next("div.visuel_zoom")) {
        $(this).next("div.visuel_zoom").hide();
      }
    });
  } else {
    $(this).find("img.visuel").css("display", "block");
    $(this).find(".categorie_hover").hide();
    $(this).find(".vfimg").show();
  }
}), $(document).ready(function() {
  var conid;
  $(window).height();
  $(window).width();
  vignscrollSize();
  $("#gclid1").val($("#gclid_field").val());
  $("#gclidc").val($("#gclid_field").val());
  $("#ddown").on("click", function() {
    var $container = $("#ipid");
    /** @type {number} */
    var slideTop = ($("#ipid")[0].scrollHeight, $container.height() / 2);
    $container.scrollTop($container.scrollTop() + slideTop);
  });
  $("#dup").on("click", function() {
    var area = $("#ipid");
    /** @type {number} */
    var offset = ($("#ipid")[0].scrollHeight, area.height() / 2);
    area.scrollTop(area.scrollTop() - offset);
  });
  if ($("#affichage_first")) {
    $("#affichage_first").val();
    conid = $("#affichage_first").val();
    $("#acces_panier_title_" + conid).show();
    $("#scroll_accessoires_" + conid).show();
    $("#scrollbtnsparent_" + conid).show();
    $("#text_vignscroll_" + conid).css("display", "table");
    $("#chev_left_" + conid).removeClass("fa-chevron-down").addClass("fa-chevron-up");
    $("#chev_right_" + conid).removeClass("fa-chevron-down").addClass("fa-chevron-up");
  }
  $("#devisrap").click(function() {
    var e = $("#devisrapartid").val();
    var i = $("#gclid_field").val();
    $.ajax({
      type : "GET",
      url : "/devisrapide.php",
      dataType : "text",
      data : {
        artid : e,
        gclid : i
      },
      success : function(htmlExercise) {
        $("#test-popup").html(htmlExercise);
      }
    });
  });
  $("#access-btn-xs").click(function() {
    if ($("#access-btn-xs").hasClass("check")) {
      $("#access-btn-xs").removeClass("check");
      $("#access_index_xs").hide();
    } else {
      $("#access-btn-xs").addClass("check");
      $("#access_index_xs").show();
    }
  });
  $("#menu_resp_close").click(function() {
    $("#menu_resp_b").show();
    $("#site_pusher").toggleClass("push");
    $("#menu_resp").toggleClass("width-menu");
    $(".container_site").toggleClass("ovhidden");
  });
  $("#menu_resp_b").click(function() {
    $("#menu_resp_b").hide();
    $("#site_pusher").toggleClass("push");
    $("#menu_resp").toggleClass("width-menu");
    $(".container_site").toggleClass("ovhidden");
  });
  $(".container_menu_2").click(function() {
    $(this).find(".arrow-img").toggleClass("arrow-img-rotate");
  });
  /** @type {!Date} */
  var date = new Date;
  /** @type {number} */
  date = date.getTime();
  $("#btn_panier").mouseover(function() {
    $("#nb_articles_panier").css("background-color", "#a88d5c");
  }).mouseout(function() {
    $("#nb_articles_panier").css("background-color", "black");
  });
  $(".btn-menu").click(function() {
    if ($(".menu").hasClass("visible")) {
      $(".menu").removeClass("visible");
    } else {
      $(".menu").addClass("visible");
    }
  });
  $(".niveau1-1").click(function() {
    var url = $(this).attr("id");
    if (url.substring(url.lastIndexOf("-") + 1, url.length) >= 3) {
      $("#menu_resp").toggleClass("move-up");
    }
  });
  $(".niveau1").mouseenter(function() {
    if ("undefined" != typeof timer) {
      clearTimeout(timer);
    }
    toggleButtonMenu(this, true);
    hideClass("sous-menu");
    hideClass("sous-sous-menu");
    showMenu(this, "sous-menu");
  }).mouseleave(function() {
    /** @type {number} */
    timer = setTimeout(function() {
      toggleButtonMenu(this, false);
      hideClass("sous-menu");
      hideClass("sous-sous-menu");
    }, 300);
  }).click(function() {
    $(this).find("a").get(0).click();
  });
  $(".menu").mouseenter(function() {
    if ("undefined" != typeof timer) {
      clearTimeout(timer);
    }
  });
  $(".sous-sous-menu").mouseenter(function() {
    if ("undefined" != typeof timer) {
      clearTimeout(timer);
    }
  });
  $(".sous-sous-menu").mouseleave(function() {
    /** @type {number} */
    timer = setTimeout(function() {
      $(".menu").removeClass("visible");
      hideClass("sous-menu");
      hideClass("sous-sous-menu");
      toggleButtonMenu(this, false);
      toggleButtonSousMenu(this, false);
    }, 100);
  });
  $(".sous-menu").mouseenter(function() {
    if ("undefined" != typeof timer) {
      clearTimeout(timer);
    }
  });
  $(".niveau2").mouseenter(function() {
    if ("undefined" != typeof timer) {
      clearTimeout(timer);
    }
    toggleButtonSousMenu(this, true);
  }).click(function() {
    $(this).find("a").get(0).click();
  });
  $(".niveau2").mouseleave(function() {
    /** @type {number} */
    timer = setTimeout(function() {
      hideClass("sous-menu");
      hideClass("sous-sous-menu");
      toggleButtonMenu(this, false);
    }, 200);
  });
  var t = $(document).width();
  if (t > 1182 && $(".index_vignette").mouseenter(function() {
    $(this).find(".index_hover").show();
    $(this).find(".img_ambiance").hide();
  }).mouseleave(function() {
    $(this).find(".index_hover").hide();
    $(this).find(".img_ambiance").show();
  }), t > 974 && $(".vignette").mouseenter(function() {
    $(this).find("img.visuel").css("display", "none");
    $(this).find(".categorie_hover").show();
    $(this).find(".vfimg").hide();
  }).mouseleave(function() {
    $(this).find("img.visuel").css("display", "block");
    $(this).find(".categorie_hover").hide();
    $(this).find(".vfimg").show();
  }), $(document).on("click", function(jEvent) {
    if (0 === $(jEvent.target).closest(".faux_select").length) {
      closeFauxSelects();
    }
  }), $(".select-choix").click(function() {
    var targetabselection = $(this).next("ul");
    if ("none" == targetabselection.css("display")) {
      closeFauxSelects();
      targetabselection.css("display", "block");
    } else {
      targetabselection.css("display", "none");
    }
  }), $(".choice").parent().mouseenter(function() {
    $(this).css("background-color", "#a1a1a1");
  }).mouseleave(function() {
    $(this).css("background-color", "#e3e2e0");
  }), $(".choice").prev("input").click(function() {
    var a = $(this).next("a");
    var i = $(this).parent().parent().prev("p").find("a");
    var t = $(this).parent().attr("class");
    var n = $(this);
    if (n.prop("checked")) {
      cocheOption(a, n, i);
    } else {
      decocheOption(n, i, t);
    }
  }), $(".choice").click(function() {
    var hierarchy = $(this);
    var i = $(this).parent().parent().prev("p").find("a");
    var t = $(this).parent().attr("class");
    var n = $(this).prev("input");
    if (n.prop("checked")) {
      n.prop("checked", false);
      decocheOption(n, i, t);
    } else {
      n.prop("checked", true);
      cocheOption(hierarchy, n, i);
    }
  }), $("a.valider").click(function() {
    var e = $(this).closest("div");
    var events = e.prev("p").prev("input").val();
    /** @type {string} */
    var url = "";
    e.find("li").each(function() {
      if ("filtre_dimensions" == $(this).attr("class")) {
        var e = $(this).find("input");
        var knob_elem = $(this).find("input").next("input");
        var getEventMap = e.val();
        var filtered = knob_elem.val();
        if ("larg_min" == e.attr("id")) {
        }
        if ("haut_min" == e.attr("id")) {
        }
        if ("prof_min" == e.attr("id")) {
        }
        events = (events = events.replace("%5B%3Fdim_min%5D", getEventMap)).replace("%5B%3Fdim_max%5D", filtered);
      }
      if ($(this).find("input").prop("checked")) {
        classLi = $(this).attr("class");
        var names = classLi.split("-");
        if (names[1]) {
          if ("" != url) {
            url = url + ",";
          }
          url = url + names[1];
        }
      }
    });
    location.href = events.replace("%5B%3Furl_values%5D", url);
  }), $(".tri_page").on("change", function() {
    location.href = $(this).val();
  }), $(".nb_page").on("change", function() {
    location.href = $(this).val();
  }), $(".libelle_option").each(function() {
    $(this).click(function() {
      var panel = $(this).children("p#txt_options");
      var ind = panel.children("i");
      $(this).next("div").slideToggle();
      if ($(ind).hasClass("fa-chevron-down")) {
        $(panel).empty();
        $(panel).html($(panel).data("close") + ' <i class="fa fa-chevron-up fa-lg"></i>');
      } else {
        $(panel).empty();
        $(panel).html($(panel).data("open") + ' <i class="fa fa-chevron-down fa-lg"></i>');
      }
    });
  }), $("#libelle_encart").click(function() {
    /** @type {boolean} */
    var e = false;
    var i = $(this).children("a");
    $(this).nextAll(".centre_options").each(function() {
      if ("1px" === $(this).css("height") || "none" === $(this).css("display")) {
        var slideset = $(this).prev(".libelle_option");
        var t = $(slideset).children("p#txt_options");
        var ind = t.children("i");
        if ($(ind).hasClass("fa-chevron-down")) {
          $(t).empty();
          $(t).html($(objTxtEnfant).data("close") + ' <i class="fa fa-chevron-up fa-lg"></i>');
        } else {
          $(t).empty();
          $(t).html($(objTxtEnfant).data("open") + ' <i class="fa fa-chevron-down fa-lg"></i>');
        }
        $(this).slideToggle();
        /** @type {boolean} */
        e = true;
      }
    });
    if (e) {
      $(i).removeClass();
      $(i).addClass("fleche_on");
      $("#action_bouton").empty();
      $("#action_bouton").html("Masquer toutes les caract\u00e9ristiques");
      /** @type {boolean} */
      bloc_fermeture_option = true;
    } else {
      $(this).nextAll(".centre_options").each(function() {
        var slideset = $(this).prev(".libelle_option");
        var panel = $(slideset).children("p#txt_options");
        var ind = panel.children("i");
        if ($(ind).hasClass("fa-chevron-down")) {
          $(panel).empty();
          $(panel).html($(panel).data("close") + ' <i class="fa fa-chevron-up fa-lg"></i>');
        } else {
          $(panel).empty();
          $(panel).html($(panel).data("open") + ' <i class="fa fa-chevron-down fa-lg"></i>');
        }
        $(this).slideToggle();
      });
      $(i).removeClass();
      $(i).addClass("fleche");
      $("#action_bouton").empty();
      $("#action_bouton").html("Afficher toutes les caract\u00e9ristiques");
      /** @type {boolean} */
      bloc_fermeture_option = false;
    }
  }), t > 1182 && $(document).mousemove(function(event) {
    /** @type {number} */
    var t = event.pageY - (document.body.scrollTop || document.documentElement.scrollTop);
    /** @type {!Date} */
    var monday = new Date;
    /** @type {number} */
    var e = monday.getHours();
    /** @type {number} */
    var a = monday.getDay();
    /** @type {number} */
    var interval = monday.getTime() - date;
    $("#exitpopup").css("left", window.innerWidth / 2 - $("#exitpopup").width() / 2);
    $("#exitpopup").css("top", window.innerHeight / 2 - $("#exitpopup").height() / 2);
    if ("" == getCookie("admin_username") && t <= 5 && "" == getCookie("popup_exit") && (e >= 9 && e <= 13 || e >= 14 && e <= 19) && a >= 1 && a <= 5 && interval >= 3E3) {
      tb_show("", "/rappelimmediat.php?width=400&height=300", false);
      setCookie("popup_exit", 1, 1);
    }
  }), $("#rappel_immediat_non").click(tb_remove), t > 974 && $(".encart_option").mousemove(function(event) {
    var i = $(this).next("div.visuel_zoom");
    var blockHeight = i.css("height");
    if (blockHeight) {
      blockHeight = blockHeight.substr(0, blockHeight.length - 2);
      $(window).height();
      parseInt(event.pageY);
      i.css({
        left : .5 * event.pageX,
        top : "unset"
      });
      i.show();
    }
  }).mouseleave(function(canCreateDiscussions) {
    if ($(this).next("div.visuel_zoom")) {
      $(this).next("div.visuel_zoom").hide();
    }
  }), $(window).scroll(function() {
    if (!$(".menu").hasClass("fixed") && $(window).scrollTop() > 125) {
      $(".menu").addClass("fixed");
      $(".sous-menu").addClass("fixed");
      $(".sous-sous-menu").addClass("fixed");
      $(".sous-menu").each(function() {
        if ("block" == $(this).css("display")) {
          return $(".menu").addClass("visible"), false;
        }
      });
      $("#divprop").css("display", "none");
      if ($(window).width() > 1E3) {
        $("#menu_sm").find("#bouton_panier").detach().appendTo("#navbar-nav");
        $(".minilogo").show();
      }
    } else {
      if ($(".menu").hasClass("fixed") && $(window).scrollTop() < 125) {
        $(".menu").removeClass("fixed");
        $(".sous-menu").removeClass("fixed");
        $(".sous-sous-menu").removeClass("fixed");
        $("#navbar-nav").find("#bouton_panier").detach().prependTo(".icons");
        $(".minilogo").hide();
      }
    }
    if (0 == $(".faux_select:hover").length) {
      closeFauxSelects();
    }
  }), $(".footerbastop").click(function() {
    if ($(".footerbastop").hasClass("open")) {
      $(".footerbas").hide();
      $(".footerbastop").removeClass("open");
    } else {
      $(".footerbas").show();
      $(".lazyf").lazy();
      $(".footerbastop").addClass("open");
    }
  }), $(".reassurance.slide").length > 0 && change_reassurance(), $("#encart_options").length > 0 && arrange_options(), $("#artid_sofinco").length) {
    var n = $("#artid_sofinco").val();
    $.ajax({
      type : "GET",
      url : "/ajax/sofinco.php",
      dataType : "text",
      data : {
        artid : n,
        type : "article"
      }
    }).done(function(usersLayoutTemplate) {
      $("#sofinco_content").css("text-align", "left");
      $("#sofinco_content").html(usersLayoutTemplate);
    });
  }
  if ($("#facturid_sofinco").length) {
    var s = $("#facturid_sofinco").val();
    var a = $("#mensualites_sofinco").val();
    var conid = $("#moyenpaiement_sofinco").val();
    $.ajax({
      type : "GET",
      url : "/ajax/sofinco.php",
      dataType : "text",
      data : {
        facturid : s,
        mensualites : a,
        moyenpaiement : conid,
        type : "commande"
      }
    }).done(function(usersLayoutTemplate) {
      $("#sofinco_content" + conid).css("text-align", "left");
      $("#sofinco_content" + conid).html(usersLayoutTemplate);
    });
  }
  loadAjax();
  loadAjaxDelaisLivraison();
});
