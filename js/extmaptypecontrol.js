/*
* ExtMapTypeControl Class
*	Copyright (c) 2007, Google
*	Author: Pamela Fox, others
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*			 http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* This class lets you add a control to the map which mimics GMapTypeControl
*	and allows for the addition of a traffic button/traffic key.
*/

/*
 * Constructor for ExtMapTypeControl, which uses an option hash
 * to decide what elements to put in the control.
 * @param {opt_opts	} Named optional arguments:
 *	 opt_opts.showTraffic {Boolean	} Controls whether traffic button is shown
 *	 opt_opts.showTrafficKey {Boolean	} Controls whether traffic key is shown
 */
function ExtMapTypeControl(opt_opts) {
	this.options = opt_opts || {	};
	}


ExtMapTypeControl.prototype = new GControl();

/**
 * Is called by GMap2's addOverlay method. Creates the button
 *	and appends to the map div.
 * @param {GMap2	} map The map that has had this ExtMapTypeControl added to it.
 * @return {DOM Object	} Div that holds the control
 */
ExtMapTypeControl.prototype.initialize = function(map) {
	var container = document.createElement("div");
	var me = this;

	var satDiv = me.createButton_("Satellite");
	var mapDiv = me.createButton_("Map");
	var hybDiv = me.createButton_("Hybrid");

	me.assignButtonEvent_(satDiv, map, G_SATELLITE_MAP, [mapDiv, hybDiv]);
	me.assignButtonEvent_(hybDiv, map, G_HYBRID_MAP, [satDiv, mapDiv]);
	me.assignButtonEvent_(mapDiv, map, G_NORMAL_MAP, [satDiv, hybDiv]);
	GEvent.addListener(map, "maptypechanged", function() {
		if (map.getCurrentMapType() == G_NORMAL_MAP) {
			GEvent.trigger(mapDiv, "click");
			} else if (map.getCurrentMapType() == G_SATELLITE_MAP) {
			GEvent.trigger(satDiv, "click");
			} else if (map.getCurrentMapType() == G_HYBRID_MAP) {
			GEvent.trigger(hybDiv, "click");
			}
		});

 if (me.options.showTraffic) {
	 var trafficDiv = me.createButton_("Traffic");
	 trafficDiv.style.marginRight = "8px";
	 trafficDiv.firstChild.style.cssFloat = "left";
	 trafficDiv.firstChild.style.styleFloat = "left";
		GEvent.addDomListener(trafficDiv.firstChild, "click", function() {
			if (me.trafficInfo) {
				if (me.trafficInfo.hidden) {
					me.trafficInfo.hidden = false;
					me.trafficInfo.show();
					} else {
					me.trafficInfo.hidden = true;
					me.trafficInfo.hide();
					}
				} else {
				me.trafficInfo = new GTrafficOverlay();
				me.trafficInfo.hidden = false;
				map.addOverlay(me.trafficInfo);
				}
			me.toggleButton_(trafficDiv.firstChild, !me.trafficInfo.hidden);
			});

		if (me.options.showTrafficKey) {
			keyDiv = document.createElement("div");
			keyDiv.style.cssFloat = "left";
			keyDiv.style.styleFloat = "left";
			keyDiv.innerHTML = "&nbsp;?&nbsp;";

			var keyExpandedDiv = document.createElement("div");
			keyExpandedDiv.style.clear = "both";
			keyExpandedDiv.style.padding = "2px";
			var keyInfo = [{"color": "#30ac3e", "text": "&gt; 50 MPH"	},
										 {"color": "#ffcf00", "text": "25-50 MPH"	},
										 {"color": "#ff0000", "text": "&lt; 25 MPH"	},
										 {"color": "#c0c0c0", "text": "No data"	}];
			for (var i = 0; i < keyInfo.length; i++) {
				keyExpandedDiv.innerHTML += "<div style='text-align: left'><span style='background-color: " + keyInfo[i].color + "'>&nbsp;&nbsp</span>"
					+	"<span style='color: " + keyInfo[i].color + "'> " + keyInfo[i].text + " </span>" + "</div>";
				}
			keyExpandedDiv.style.display = "none";

			GEvent.addDomListener(keyDiv, "click", function() {
				if (me.keyExpanded) {
					me.keyExpanded = false;
					keyExpandedDiv.style.display = "none";
					} else {
					me.keyExpanded = true;
					keyExpandedDiv.style.display = "block";
					}
				me.toggleButton_(keyDiv, me.keyExpanded);
				});

			me.toggleButton_(keyDiv, me.keyExpanded);
			}

		var separatorDiv = document.createElement("div");
		separatorDiv.style.clear = "both";

		if (me.options.showTrafficKey) trafficDiv.appendChild(keyDiv);
		trafficDiv.appendChild(separatorDiv);
		if (me.options.showTrafficKey) trafficDiv.appendChild(keyExpandedDiv);
		me.toggleButton_(trafficDiv.firstChild, false);

		container.appendChild(trafficDiv);
		}

	container.appendChild(satDiv);
	container.appendChild(mapDiv);
	container.appendChild(hybDiv);

	map.getContainer().appendChild(container);
	return container;
	}

/*
 * Creates simple buttons with text nodes.
 * @param {String	} text Text to display in button
 * @return {DOM Object	} The div for the button.
 */
ExtMapTypeControl.prototype.createButton_ = function(text) {
	var buttonDiv = document.createElement("div");
	this.setButtonStyle_(buttonDiv);
	buttonDiv.style.cssFloat = "left";
	buttonDiv.style.styleFloat = "left";
	var textDiv = document.createElement("div");
	textDiv.appendChild(document.createTextNode(text));
	textDiv.style.width = "6em";
	buttonDiv.appendChild(textDiv);
	return buttonDiv;
	}

/*
 * Assigns events to MapType buttons to change maptype
 *	and toggle button styles correctly for all buttons
 *	when button is clicked.
 *	@param {DOM Object	} div Button's div to assign click to
 *	@param {GMap2	} Map object to change maptype of.
 *	@param {Object	} mapType GMapType to change map to when clicked
 *	@param {Array	} otherDivs Array of other button divs to toggle off
 */
ExtMapTypeControl.prototype.assignButtonEvent_ = function(div, map, mapType, otherDivs) {
	var me = this;

	GEvent.addDomListener(div, "click", function() {
		for (var i = 0; i < otherDivs.length; i++) {
			me.toggleButton_(otherDivs[i].firstChild, false);
			}
		me.toggleButton_(div.firstChild, true);
		map.setMapType(mapType);
		});
	}

/*
 * Changes style of button to appear on/off depending on boolean passed in.
 * @param {DOM Object	} div	Button div to change style of
 * @param {Boolean	} boolCheck Used to decide to use on style or off style
 */
ExtMapTypeControl.prototype.toggleButton_ = function(div, boolCheck) {
	 div.style.fontWeight = boolCheck ? "bold" : "";
	 div.style.border = "1px solid white";
	 var shadows = boolCheck ? ["Top", "Left"] : ["Bottom", "Right"];
	 for (var j = 0; j < shadows.length; j++) {
		 div.style["border" + shadows[j]] = "1px solid #b0b0b0";
		}
	 	}

/*
 * Required by GMaps API for controls.
 * @return {GControlPosition	} Default location for control
 */
ExtMapTypeControl.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 7));
	}

/*
 * Sets the proper CSS for the given button element.
 * @param {DOM Object	} button Button div to set style for
 */
ExtMapTypeControl.prototype.setButtonStyle_ = function(button) {
	button.style.color = "#000000";
	button.style.backgroundColor = "white";
	button.style.font = "small Arial";
	button.style.border = "1px solid black";
	button.style.padding = "0px";
	button.style.margin= "0px";
	button.style.textAlign = "center";
	button.style.fontSize = "12px";
	button.style.cursor = "pointer";
	}
