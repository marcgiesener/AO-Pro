/*
Based in part on Flickr Gallery 0.7 by Ramon Darrow - http://www.worrad.com/
Based in part on DAlbum by Alexei Shamov, DeltaX Inc. - http://www.dalbum.org/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//-----------------------------------------------------------------------------

var falbum_prefetch_image;
var falbum_prefetch_image_src;

function falbum_prefetch(imgsrc) {
	if (imgsrc.length>0 && document.getElementById)	{
		falbum_prefetch_image=new Image();
		// Find flickr-photo object and start prefetching once its loaded
		if (document.getElementById("flickr-photo")) {
			falbum_prefetch_image_src=imgsrc;
			
			if (document.getElementById("flickr-photo").complete) {
				falbum_prefetch_image.src=falbum_prefetch_image_src;
			} else {
				document.getElementById("flickr-photo").onload=new function(e) { falbum_prefetch_image.src=falbum_prefetch_image_src; }
			}
		}
	}
}

/* Annotations */
var aI = {

	init: function() {
		if (!document.getElementById ||
		!document.createElement ||
		!document.getElementsByTagName)
		return;
		var anni = document.getElementsByTagName('img');
		for (var i=0;i<anni.length;i++) {
			if ((anni[i].className.search(/\bannotated\b/) != -1) &&
			(anni[i].getAttribute('usemap') != null)) {
				aI.prepImage(anni[i]);
			}
		}
	},

	prepImage: function(img) {
		var mapName = img.getAttribute('usemap');
		var mapObj = document.getElementById('imgmap');
		var areas  = [];
		if (mapObj != null) {
			areas = mapObj.getElementsByTagName('area');
		}
		img.areas = [];
		for (var j=areas.length-1;j>=0;j--) {
			if (areas[j].getAttribute('shape').toLowerCase() == 'rect') {
				var coo = areas[j].getAttribute('coords').split(',');
				if (coo.length != 4) break;
				var a = document.createElement('a');
				a.associatedCoords = coo;
				a.style.width = (parseInt(coo[2]) - parseInt(coo[0])) + 'px';
				a.style.height = (parseInt(coo[3]) - parseInt(coo[1])) + 'px';
				var thisAreaPosition = aI.__getAreaPosition(img,coo);
				a.style.left = thisAreaPosition[0] + 'px';
				a.style.top = thisAreaPosition[1] + 'px';
				a.className = 'annotation';
				var href = areas[j].getAttribute('href');
				if (href) {
					a.href = href;
				} else {
					// set an explicit href, otherwise it doesn't count as a link
					// for IE
					a.href = "#"+j;
				}
				var s = document.createElement('span');
				s.appendChild(document.createTextNode(''));
				a.appendChild(s);

				img.areas[img.areas.length] = a;
				document.getElementsByTagName('body')[0].appendChild(a);

				aI.addEvent(a,"mouseover",
				function() {
					clearTimeout(aI.hiderTimeout);
				}
				);

				//eval("var fn"+j+" = function() {overlib( aI.getTitle("+j+"), STICKY, MOUSEOFF, BELOW, WRAP, CELLPAD, 5, FGCOLOR, '#FFFFCC', BGCOLOR, '#FFFF44', BORDER, 2, TEXTCOLOR, '#000000', TEXTSIZE, 2, TIMEOUT, 2000, DELAY, 50);}");
				eval("var fn"+j+" = function() {overlib( aI.getTitle("+j+"), STICKY, MOUSEOFF, BELOW, WRAP, CSSCLASS, TEXTFONTCLASS,'annotation-fontClass',FGCLASS,'annotation-fgClass', BGCLASS,'annotation-bgClass',CAPTIONFONTCLASS,'annotation-capfontClass', TIMEOUT, 2000, DELAY, 50);}");

				aI.addEvent(a,"mouseover", eval("fn"+j));
				aI.addEvent(a,"mouseout",function() {
					nd();
				});
			}
		}

		aI.addEvent(img,"mouseover",aI.showAreas);
		aI.addEvent(img,"mouseout",aI.hideAreas);
	},

	__getAreaPosition: function(img,coo) {
		var aleft = (img.offsetLeft + parseInt(coo[0]));
		var atop = (img.offsetTop + parseInt(coo[1]));
		var oo = img;
		while (oo.offsetParent) {
			oo = oo.offsetParent;
			aleft += oo.offsetLeft;
			atop += oo.offsetTop;
		}
		return [aleft,atop];
	},

	__setAreas: function(t,disp) {
		if (!t || !t.areas) return;
		for (var i=0;i<t.areas.length;i++) {
			t.areas[i].style.display = disp;
		}
	},

	showAreas: function(e) {
		var t = null;
		if (e && e.target) t = e.target;
		if (window.event && window.event.srcElement) t = window.event.srcElement;
		// Recalculate area positions
		for (var k=0;k<t.areas.length;k++) {
			var thisAreaPosition = aI.__getAreaPosition(t,t.areas[k].associatedCoords);
			t.areas[k].style.left = thisAreaPosition[0] + 'px';
			t.areas[k].style.top = thisAreaPosition[1] + 'px';

		}
		aI.__setAreas(t,'block');
	},

	hideAreas: function(e) {
		var t = null;
		if (e && e.target) t = e.target;
		if (window.event && window.event.srcElement) t = window.event.srcElement;
		clearTimeout(aI.hiderTimeout);
		aI.hiderTimeout = setTimeout(
		function() { aI.__setAreas(t,'none') }, 300);
	},

	addEvent: function(elm, evType, fn, useCapture) {
		// cross-browser event handling for IE5+, NS6 and Mozilla
		// By Scott Andrew
		if (elm.addEventListener){
			elm.addEventListener(evType, fn, useCapture);
			return true;
		} else if (elm.attachEvent){
			var r = elm.attachEvent("on"+evType, fn);
			return r;
		} else {
			elm['on'+evType] = fn;
		}
	},

	getTitle: function(j) {
		var mapObj = document.getElementById('imgmap');
		var areas  = [];
		if (mapObj != null) {
			areas = mapObj.getElementsByTagName('area');
		}
		var t = areas[j].getAttribute('title');
		re = /(\n|\r|\r\n)/gi;
		t=t.replace(re, "");

		return t;
	}
}

aI.addEvent(window,"load",aI.init);

///

var element = null;
var req = null;

function showExif(photo_id, secret, remote_url){
	element = document.getElementById("exif");
	element.innerHTML='Retrieving Data ...';

	var url = remote_url + '?action=exif&photo_id=' + photo_id + '&secret=' + secret;

	// branch for native XMLHttpRequest object
	if (window.XMLHttpRequest) {
		req = new XMLHttpRequest();
		req.onreadystatechange = processReqChange;
		req.open("GET", url, true);
		req.send(null);
		// branch for IE/Windows ActiveX version
	} else if (window.ActiveXObject) {
		req = new ActiveXObject("Microsoft.XMLHTTP");
		if (req) {
			req.onreadystatechange = processReqChange;
			req.open("GET", url, true);
			req.send();
		}
	}
}

function processReqChange() {
	// only if req shows "complete"
	if (req.readyState == 4) {
		// only if "OK"
		if (req.status == 200) {
			element.innerHTML=req.responseText;
		} else {
			alert("There was a problem retrieving the XML data:\n" + req.statusText);
		}
	}
}

function falbum_resize() {	

	var image = document.getElementById('flickr-photo');

			
	var maxY=200;
	var maxX=200;

	var srcX=image.width;
	var srcY=image.height;

	var ratio= Math.min(maxX/srcX,maxY/srcY);

	var destX = Math.floor(srcX*ratio+0.5);
	var destY = Math.floor(srcY*ratio+0.5);

	image.width=destX;
	image.height=destY;

	//alert(
	//"srcX-"+srcX+"\n"+
	//"srcY-"+srcY+"\n"+
	//"ratio-"+ratio+"\n"+
	//"destX-"+destX+"\n"+
	//"destY-"+destY+"\n"
	//);
	
}
