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