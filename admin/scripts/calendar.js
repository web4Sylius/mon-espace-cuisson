var monthInYear = new Array("janvier", "f&eacute;vrier", "mars", "avril", "mai", "juin", "juillet", "ao&ucirc;t", "septembre", "octobre", "novembre", "d&eacute;cembre");
var dayInWeek = new Array("dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi");

var calendar = new Calendar();

function Calendar() {
	this.elementId = "";
	this.element = null;

	this.weekday = 0;
	this.day = 0;
	this.month = 0;
	this.year = 0;

	this.cday = 0;
	this.cmonth = 0;
	this.cyear = 0;
	this.cyear2 = 0;

	this.mappingArray = new Array();

	this.cal = document.getElementById("cal");
	this.cald = document.getElementById("cald");
	this.calm = document.getElementById("calm");
	this.caly = document.getElementById("caly");
}

function initCalendar(date) {
	calendar.weekday = date.getDay();
	calendar.day = date.getDate();
	calendar.month = date.getMonth() + 1;
	calendar.year = date.getFullYear();

	calendar.cmonth = calendar.month;
	calendar.cyear = calendar.year;

	fillMonthChoice();
	fillYearChoice();
}

function initElement(id, evt) {
	if (!evt) {
		evt = window.event;
	}
	calendar.elementId = "";
	calendar.element = document.getElementById(id);

	var hGap = -92;
	var vGap = 12;
	var elementStyle = calendar.cal.style;
	var clientX = evt.clientX;
	var clientY = evt.clientY;
	var rightedge = document.body.clientWidth - clientX - hGap;
	var bottomedge = document.body.clientHeight - clientY - vGap;
	if (rightedge < calendar.cal.offsetWidth) {
		elementStyle.left = document.body.scrollLeft + clientX - hGap - calendar.cal.offsetWidth;
	} else {
		elementStyle.left = document.body.scrollLeft + clientX + hGap;
	}
	if (bottomedge < calendar.cal.offsetHeight) {
		elementStyle.top = document.body.scrollTop + clientY - vGap - calendar.cal.offsetHeight;
	} else {
		elementStyle.top = document.body.scrollTop + clientY + vGap;
	}

	calendar.calm.style.left = parseInt(elementStyle.left) + 6;
	calendar.calm.style.top = parseInt(elementStyle.top) + 28;
	calendar.caly.style.left = parseInt(elementStyle.left) + 12;
	calendar.caly.style.top = parseInt(elementStyle.top) + 33;
}

function displayCalendar(id, ev) {
	calendar = new Calendar();

	var i;
	var element;
	for (i = 0; i < 4; i++) {
		element = document.getElementById("cal_m_0" + i);
		element.onmouseover = highlightHead;
		element.onmouseout = unhighlightHead;
	}
	for (i = 0; i < 4; i++) {
		element = document.getElementById("cal_y_0" + i);
		element.onmouseover = highlightHead;
		element.onmouseout = unhighlightHead;
	}
	element = document.getElementById("cal_c");
	element.onmouseover = highlightHead;
	element.onmouseout = unhighlightHead;
	
	initElement(id, ev);
	var dateValue = calendar.element.value;
	var validDate = false;
	if (dateValue != "") {
		var s = dateValue.split("/");
		var d;
		var m;
		var y;
		if (s.length == 3) {
			d = s[0];
			m = s[1];
			y = s[2];
			if (!isNaN(d) && !isNaN(m) && !isNaN(y)) {
				if (d >= 1 && d <= 31 && m >= 1 && m <= 12) {
					validDate = true;
					var y2 = parseInt(y);
					if (y2 < 100) {
						y2 += 2000;
					}
					initCalendar(new Date(y2, parseInt(m) - 1, parseInt(d)));
				}
			}
		}
	}
	if (!validDate) {
		initCalendar(new Date());
	}
	fillCalendar();
	showCalendar();
}

function showCalendar() {
	calendar.cal.style.display = "block";
	showDayChoice();
	hideMonthChoice();
	hideYearChoice();
}

function hideCalendar() {
	calendar.cal.style.display = "none";
	hideDayChoice();
	hideMonthChoice();
	hideYearChoice();
}

function showDayChoice() {
	calendar.cald.style.display = "block";
}

function hideDayChoice() {
	calendar.cald.style.display = "none";
}

function showMonthChoice() {
	calendar.calm.style.display = "block";
}

function hideMonthChoice() {
	calendar.calm.style.display = "none";
}

function showYearChoice() {
	calendar.caly.style.display = "block";
}

function hideYearChoice() {
	calendar.caly.style.display = "none";
}

function updateMonthChoicedisplay() {
	if (calendar.calm.style.display == "block") {
		showDayChoice();
		hideMonthChoice();
	} else {
		hideDayChoice();
		hideYearChoice();
		showMonthChoice();
	}
}

function updateYearChoicedisplay() {
	if (calendar.caly.style.display == "block") {
		showDayChoice();
		hideYearChoice();
	} else {
		calendar.cyear2 = calendar.cyear;
		hideDayChoice();
		hideMonthChoice();
		fillYearChoice();
		showYearChoice();
	}
}

function fillCalendar() {
	document.getElementById("cal_m_02").innerHTML = getStringMonth(calendar.cmonth);
	document.getElementById("cal_y_02").innerHTML = calendar.cyear;
	var element;

	var i = 0;
	var j = 0;
	for (i = 0; i < 6; i++) {
		for (j = 0; j < 7; j++) {
			element = document.getElementById("cal_d_" + i + j);
			element.innerHTML = "";
			element.onclick = "";
			element.onmouseover = "";
			element.onmouseout = "";
		}
	}

	i = 0;
	j = (new Date(calendar.cyear, calendar.cmonth-1, 1)).getDay();
	j = (j + 6) % 7;
	var count = 1;
	var days = getMonthLastDay();
	while (count <= days) {
		element = document.getElementById("cal_d_" + i + j);
		element.innerHTML = count;
		element.onclick = selectDay;
		element.onmouseover = highlightDay;
		element.onmouseout = unhighlightDay;
		calendar.mappingArray[10 * i + j] = count;
		j++;
		if (j == 7) {
			i++;
			j = 0;
		}
		count++;
	}
}

function fillMonthChoice() {
	var i;
	var element;
	for (i = 1; i <= 12; i++) {
		element = document.getElementById("calm_" + i);
		element.innerHTML = getStringMonth(i);
		element.onclick = selectMonth;
		element.onmouseover = highlightHead;
		element.onmouseout = unhighlightHead;
	}
}

function fillYearChoice() {
	var i;
	var element;
	var gap = calendar.cyear2 - 11;
	for (i = 1; i <= 25; i++) {
		element = document.getElementById("caly_" + i);
		element.innerHTML = gap + i;
		element.onclick = selectYear;
		element.onmouseover = highlightHead;
		element.onmouseout = unhighlightHead;
	}
}

function highlightDay(evt) {
	getEventElement(evt).className = "sel";
}

function unhighlightDay(evt) {
	getEventElement(evt).className = "";
}

function highlightHead(evt) {
	var element = getEventElement(evt);
	if (element.tagName == "IMG") {
		element = element.parentNode;
	}
	element.className = "sel";
	if (element.id == "cal_m_02") {
		document.getElementById("cal_m_03").className = "sel";
	} else if (element.id == "cal_m_03") {
		document.getElementById("cal_m_02").className = "sel";
	} else if (element.id == "cal_y_02") {
		document.getElementById("cal_y_03").className = "sel";
	} else if (element.id == "cal_y_03") {
		document.getElementById("cal_y_02").className = "sel";
	}
}

function unhighlightHead(evt) {
	var element = getEventElement(evt);
	if (element.tagName == "IMG") {
		element = element.parentNode;
	}
	element.className = "";
	if (element.id == "cal_m_02") {
		document.getElementById("cal_m_03").className = "";
	} else if (element.id == "cal_m_03") {
		document.getElementById("cal_m_02").className = "";
	} else if (element.id == "cal_y_02") {
		document.getElementById("cal_y_03").className = "";
	} else if (element.id == "cal_y_03") {
		document.getElementById("cal_y_02").className = "";
	} 
}

function selectDay(evt) {
	var id = getEventElement(evt).id;
	calendar.cday = calendar.mappingArray[parseInt(id.substr(id.length - 2))];
	hideCalendar();
	var d = calendar.cday;
	var m = calendar.cmonth;
	var y = calendar.cyear;
	window.status = d + " - " + m + " - " + y;
	calendar.element.value = (d < 10 ? "0" : "") + d + "/" + (m < 10 ? "0" : "") + m + "/" + y;
}

function selectMonth(evt) {
	var id = getEventElement(evt).id;
	var s = id.split("_");
	calendar.cmonth = parseInt(s[s.length - 1]);
	hideMonthChoice();
	showDayChoice();
	fillCalendar();
}

function selectYear(evt) {
	var id = getEventElement(evt).id;
	var s = id.split("_");
	calendar.cyear = parseInt(s[s.length - 1]) + calendar.cyear2 - 11;
	hideYearChoice();
	showDayChoice();
	fillCalendar();
}

function incrementMonth() {
	if (calendar.cmonth == 12) {
		calendar.cmonth = 1;
		calendar.cyear = calendar.cyear + 1;
	} else {
		calendar.cmonth = calendar.cmonth + 1;
	}
	fillCalendar();
}

function decrementMonth() {
	if (calendar.cmonth == 1) {
		calendar.cmonth = 12;
		calendar.cyear = calendar.cyear - 1;
	} else {
		calendar.cmonth = calendar.cmonth - 1;
	}
	fillCalendar();
}

function incrementYear() {
	calendar.cyear = calendar.cyear + 1;
	fillCalendar();
}

function decrementYear() {
	calendar.cyear = calendar.cyear - 1;
	fillCalendar();
}

function increaseYear() {
	calendar.cyear2 = calendar.cyear2 + 20;
	fillYearChoice();
}

function decreaseYear() {
	calendar.cyear2 = calendar.cyear2 - 20;
	fillYearChoice();
}

function getMonthLastDay() {
	var testDate = new Date(calendar.cyear, calendar.cmonth-1, 29);
	if (testDate.getDate() == 29) {
		testDate.setDate(30);
		if (testDate.getDate() == 30) {
			testDate.setDate(31);
			if (testDate.getDate() == 31) {
				return 31
			} else {
				return 30;
			}
		} else {
			return 29;
		}
	} else {
		return 28;
	}
}

function getStringWeekDay(index) {
	return dayInWeek[index];
}

function getStringMonth(index) {
	return monthInYear[index - 1];
}

var checkClickEnabled = true;
var clickedElement = null;

function checkClick(evt) {
	if ((checkClickEnabled)
			&& (calendar.cal != null)
			&& (calendar.cal.style.display == "block")) {
		checkClickEnabled = false;
		if (evt.target != null) {
			clickedElement = evt.target;
		} else {
			clickedElement = window.event.srcElement;
		}
		setTimeout("checkClickAnalysis()", 200);
	}
}

function checkClickAnalysis() {
	var element = clickedElement;
	var calendarElement = false;
	var id;
	var tagName = element.tagName;
	// Vérification que le clic courant n'est pas relatif à une image dont le rôle est justement
	// d'afficher le calendrier.
	if (tagName == "IMG") {
		var imgOnClickFunction = "" + element.onclick;
		calendarElement = (imgOnClickFunction.indexOf("displayCalendar") != -1);
	}
	// Vérification de l'appartenance ou non de l'élément ayant déclenché l'événement 'onclick' au
	// calendrier.
	if (!calendarElement) {
		while (element != null && tagName != "FORM") {
			id = element.id;
			if (id != null && (id == "cal" || id == "calm" || id == "caly") && tagName == "DIV") {
				// L'élément ayant déclenché l'événement 'onclick' appartient au calendrier : on ne
				// doit donc pas masquer ce dernier.
				calendarElement = true;
			}
			element = element.parentNode;
			if (element != null) {
				tagName = element.tagName;
			}
		}
		if (!calendarElement) {
			hideCalendar();
		}
	}
	checkClickEnabled = true;
}

function getEventElement(evt) {
	if (!evt) {
		return window.event.srcElement;
	} else {
		return evt.target;
	}
}
