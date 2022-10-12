import $ from 'jquery';

let longPressTimer;

$(document).ready(function () {
	deviceTools();
	updateStatus();

	if (refreshtime) {
		console.log("[Global][Refreshtime]" + refreshtime + "ms");
		setInterval(function () {
			updateStatus();
		}, refreshtime);
	} else {
		console.log("[Global][Refreshtime]Dont refresh");
	}
});

function updateStatus() {
	$("#content .box_device:not(#all_off)").each(function (key, box) {
		let device_ip = $(box).data("device_ip");
		let device_id = $(box).data("device_id");
		let device_relais = $(box).data("device_relais");
		let device_group = $(box).data("device_group");

		if (!$(box).hasClass("updating")) {
			console.log("[Start][updateStatus]get status from " + $(box).data("device_ip"));

			if (device_group === "multi" && device_relais > 1) {
				console.log("[Start][updateStatus]skip multi " + $(box).data("device_ip"));
				return; //relais 1 will update all others
			}

			$(box).addClass("updating");

			sonoff.getStatus(device_ip, device_id, function (data) {
				if (data && !data.ERROR && !data.WARNING && data !== "" && data !== undefined && data.statusText === undefined) {
					if (device_group === "multi") {
						$('#content .box_device[data-device_group="multi"][data-device_ip="' + device_ip + '"]').each(function (key, groupbox) {
							//TODO: make function to set image
							let img = $(groupbox).find("img");
							let src = config.resource_url + "img/device_icons/" + img.data("icon") + "_%pw.png?v=160";

							let device_relais = $(groupbox).data("device_relais");
							let device_status = sonoff.parseDeviceStatus(data, device_relais);

							console.log(device_status.toLowerCase());
							src = src.replace("%pw", device_status.toLowerCase());
							img.attr("src", src).parent().removeClass("animated");
							updateBox($(groupbox), data, device_status);
							$(groupbox).removeClass("error").find(".animated").removeClass("animated");
							$(groupbox).removeClass("updating");
						});
					} else {
						let img = $(box).find("img");
						let src = config.resource_url + "img/device_icons/" + img.data("icon") + "_%pw.png?v=160";

						let device_status = sonoff.parseDeviceStatus(data, 1);

						console.log("device_status", device_status);
						if (device_status !== undefined) {
							$(box).data("device_state", device_status.toLowerCase());

							src = src.replace("%pw", device_status.toLowerCase());
							img.attr("src", src).parent().removeClass("animated");

							console.log("$( box )", $(box));
							if (device_status === "NONE") {
								//$( box ).attr( "data-device_group", "sensor" );
								$(box).data("device_group", "sensor");
							}
						}
						updateBox($(box), data, device_status);
						$(box).removeClass("error").find(".animated").removeClass("animated");
						$(box).removeClass("updating");
					}
				} else {
					console.log("ERROR => " + JSON.stringify(data));

					if (device_group === "multi") {
						$('#device-list tbody tr[data-device_group="multi"][data-device_ip="' + device_ip + '"]').each(function (key, groupbox) {
							$(groupbox).addClass("error").find(".animated").removeClass("animated");
							$(groupbox).removeClass("updating");
							let img = $(groupbox).find("img");
							let src = config.resource_url + "img/device_icons/" + img.data("icon") + "_error.png?v=160";
							img.attr("src", src);
						});
					} else {
						$(box).addClass("error").find(".animated").removeClass("animated");
						$(box).removeClass("updating");
						let img = $(box).find("img");
						let src = config.resource_url + "img/device_icons/" + img.data("icon") + "_error.png?v=160";
						img.attr("src", src);
					}
				}
				//console.log( result );
			});
		}
	});
}

function deviceTools() {
	$("#content .box_device:not(#all_off)")
		.on("mousedown touchstart", function () {
			clearTimeout(longPressTimer);
			let device_box = $(this);

			if (device_box.hasClass("disabled")) {
				longPressTimer = setTimeout(function () {
					device_box.addClass("ignoreProtection");
					clearTimeout(longPressTimer);
					setTimeout(function () {
						device_box.addClass("ignoreProtection");
					}, 60 * 1000);
				}, 5 * 1000);
			}
		})
		.on("mouseup mouseleave touchend touchmove", function () {
			clearTimeout(longPressTimer);
		});

	$("#content .box_device:not(#all_off)").on("click", function (e) {
		e.preventDefault();

		let device_box = $(this);
		let device_ip = device_box.data("device_ip");
		let device_id = device_box.data("device_id");
		let device_relais = device_box.data("device_relais");
		let device_group = device_box.data("device_group");
		let device_state = device_box.data("device_state");

		let device_protect_on = device_box.data("device_protect_on");
		let device_protect_off = device_box.data("device_protect_off");

		if (device_group === "sensor") {
			console.log("[Start][updateStatus]skip sensor " + device_box.data("device_ip"));
			return; //relais 1 will update all others
		}

		if ($(this).hasClass("toggled")) {
			console.log("[Start][updateStatus]is toggling " + device_box.data("device_ip"));
			return;
		}

		if (device_state === "on" && device_protect_off === 1 && !device_box.hasClass("ignoreProtection")) {
			return;
		}
		if (device_state === "off" && device_protect_on === 1 && !device_box.hasClass("ignoreProtection")) {
			return;
		}

		$(this).addClass("toggled");
		device_box.find("img").shake(3, 5, 500);

		sonoff.toggle(device_ip, device_id, device_relais, function (data) {
			if (data && !data.ERROR && !data.WARNING) {
				let img = device_box.find("img");
				let src = config.resource_url + "img/device_icons/" + img.data("icon") + "_%pw.png?v=160";

				let device_status = sonoff.parseDeviceStatus(data, device_relais);

				if (device_status !== undefined) {
					device_box.data("device_state", device_status.toLowerCase());

					src = src.replace("%pw", device_status.toLowerCase());
					img.attr("src", src);

					//updateBox(device_box, data, device_status);

					updateStatus();
				}

				img.parent().removeClass("animated");
				device_box.removeClass("error");
			} else {
				device_box.addClass("error");
				let img = device_box.find("img");
				let src = config.resource_url + "img/device_icons/" + img.data("icon") + "_error.png?v=160";
				img.attr("src", src).parent().removeClass("animated");
				console.log("[Start][toggle]ERROR " + device_ip + " => " + data.ERROR || "Unknown Error");
			}
			$("#content .box_device").removeClass("toggled");
		});
	});

	$("#all_off").on("click", function (e) {
		e.preventDefault();
		$("#content .box_device:not(#all_off)").each(function (key, box) {
			let device_ip = $(box).data("device_ip");
			let device_id = $(box).data("device_id");
			let device_relais = $(box).data("device_relais");
			let device_group = $(box).data("device_group");

			let device_all_off = $(box).data("device_all_off");
			let device_protect_on = $(box).data("device_protect_on");
			let device_protect_off = $(box).data("device_protect_off");

			console.log("[Start][updateStatus]get status from " + $(box).data("device_ip"));

			if (device_group === "multi" && device_relais > 1) {
				console.log("[Start][updateStatus]skip multi " + $(box).data("device_ip"));
				return; //relais 1 will update all others
			}
			if (device_group === "sensor") {
				console.log("[Start][updateStatus]skip sensor " + $(box).data("device_ip"));
				return;
			}

			if (device_all_off !== 1) {
				console.log("[Start][updateStatus]skip excluded " + $(box).data("device_ip"));
				return;
			}

			sonoff.off(device_ip, device_id, device_relais, function (data) {
				if (data && !data.ERROR && !data.WARNING) {
					let img = $(box).find("img");
					let src = config.resource_url + "img/device_icons/" + img.data("icon") + "_%pw.png?v=160";

					let device_status = sonoff.parseDeviceStatus(data, device_relais);

					if (device_status !== undefined) {
						$(box).data("state", device_status);

						src = src.replace("%pw", device_status.toLowerCase());
						img.attr("src", src);
					}
					img.parent().removeClass("animated");
					$(box).removeClass("error");
				} else {
					$(box).addClass("error");
					let img = device_box.find("img");
					let src = config.resource_url + "img/device_icons/" + img.data("icon") + "_error.png?v=160";
					img.attr("src", src).parent().removeClass("animated");
					console.log("[Start][toggle]ERROR " + device_ip + " => " + data.ERROR || "Unknown Error");
				}
			});
		});
	});
}

function updateBox(row, data, device_status) {
	let device_protect_on = $(row).data("device_protect_on");
	let device_protect_off = $(row).data("device_protect_off");

	let version = "n/A";
	let rssi, ssid, uptime;
	if (data.StatusFWR !== undefined) {
		version = parseVersion(data.StatusFWR.Version);
	}

	if (version >= 510009) {
		//no json translations since 5.10.0j
		rssi = data.StatusSTS.Wifi.RSSI;
		ssid = data.StatusSTS.Wifi.SSId;
		uptime = data.StatusSTS.Uptime;
	} else {
		//try german else use english
		rssi = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.RSSI : data.StatusSTS.Wifi.RSSI;
		ssid = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.SSID : data.StatusSTS.Wifi.SSId;
		uptime = data.StatusSTS.Laufzeit !== "undefined" ? data.StatusSTS.Laufzeit : data.StatusSTS.Uptime;
		//console.log( uptime );
	}

	let infoBoxCounter = 1;

	//let fakeData = JSON.parse(
	//	"{\"StatusSNS\":{\"Time\":\"2018-02-10T22:46:34\",\"BMP280\":{\"Temperature\":80.9,\"Pressure\":984.4}}}" );

	let temp = getTemp(data, ", ");

	if (temp !== "") {
		$(row)
			.find(".info-" + infoBoxCounter + " span")
			.html(temp)
			.parent()
			.removeClass("hidden");
		infoBoxCounter++;
	}
	let humidity = getHumidity(data, ", ");

	if (humidity !== "") {
		$(row)
			.find(".info-" + infoBoxCounter + " span")
			.html(humidity)
			.parent()
			.removeClass("hidden");
		infoBoxCounter++;
	}
	let pressure = getPressure(data, ", ");

	if (pressure !== "") {
		$(row)
			.find(".info-" + infoBoxCounter + " span")
			.html(pressure)
			.parent()
			.removeClass("hidden");
		infoBoxCounter++;
	}

	let seapressure = getSeaPressure(data, ", ");

	if (seapressure !== "") {
		$(row)
			.find(".info-" + infoBoxCounter + " span")
			.html(seapressure)
			.parent()
			.removeClass("hidden");
		infoBoxCounter++;
	}

	let distance = getDistance(data, ", ");

	if (distance !== "") {
		$(row)
			.find(".info-" + infoBoxCounter + " span")
			.html(distance)
			.parent()
			.removeClass("hidden");
		infoBoxCounter++;
	}

	let energyPower = getEnergyPower(data, ", ");

	if (energyPower !== "") {
		$(row)
			.find(".info-" + infoBoxCounter + " span")
			.html(energyPower)
			.parent()
			.removeClass("hidden");
		infoBoxCounter++;
	}

	//let energyTodayYesterday = getEnergyTodayYesterday( data );
	//
	//if ( energyTodayYesterday !== "" ) {
	//	$( row )
	//		.find( ".info-" + infoBoxCounter + " span" )
	//		.html( energyTodayYesterday )
	//		.parent()
	//		.removeClass( "hidden" );
	//	infoBoxCounter++;
	//}

	let gas = getGas(data, ", ");

	if (gas !== "") {
		$(row)
			.find(".info-" + infoBoxCounter + " span")
			.html(gas)
			.parent()
			.removeClass("hidden");
		infoBoxCounter++;
	}

	let idx = data.idx ? data.idx : "";

	if (idx !== "") {
		$(row).find(".idx span").html(idx);
		$("#device-list .idx").removeClass("hidden").show();
	}

	$(row).find(".version span").html(data.StatusFWR.Version);

	$(row).data("device_state", device_status.toLowerCase());

	if (device_status === "ON") {
		$(row).find(".status").find("input").prop("checked", "checked").parent().removeClass("error");

		if (device_protect_off === 1) {
			$(row).addClass("disabled");
		} else {
			$(row).removeClass("disabled");
		}
	} else {
		$(row).find(".status").find("input").removeProp("checked").parent().removeClass("error");

		if (device_protect_on === 1) {
			$(row).addClass("disabled");
		} else {
			$(row).removeClass("disabled");
		}
	}

	$(row)
		.find(".rssi span")
		.html(rssi + "%")
		.attr("title", ssid);
	$(row)
		.find(".runtime span")
		.html("~" + uptime + "h");

	//MORE
	$(row)
		.find(".hostname span")
		.html(data.StatusNET.Hostname !== undefined ? data.StatusNET.Hostname : "?");
	$(row)
		.find(".mac span")
		.html(data.StatusNET.Mac !== undefined ? data.StatusNET.Mac : "?");
	$(row)
		.find(".mqtt span")
		.html(data.StatusMQT !== undefined ? "1" : "0");
	$(row)
		.find(".poweronstate span")
		.html(data.Status.PowerOnState !== undefined ? data.Status.PowerOnState : "?");
	$(row)
		.find(".ledstate span")
		.html(data.Status.LedState !== undefined ? data.Status.LedState : "?");
	$(row)
		.find(".savedata span")
		.html(data.Status.SaveData !== undefined ? data.Status.SaveData : "?");
	$(row)
		.find(".sleep span")
		.html(data.StatusPRM.Sleep !== undefined ? data.StatusPRM.Sleep + "ms" : "?");
	$(row)
		.find(".bootcount span")
		.html(data.StatusPRM.BootCount !== undefined ? data.StatusPRM.BootCount : "?");
	$(row)
		.find(".savecount span")
		.html(data.StatusPRM.SaveCount !== undefined ? data.StatusPRM.SaveCount : "?");
	$(row)
		.find(".log span")
		.html(
			(data.StatusLOG.SerialLog !== undefined ? data.StatusLOG.SerialLog : "?") + "|" + (data.StatusLOG.WebLog !== undefined ? data.StatusLOG.WebLog : "?") + "|" + (data.StatusLOG.SysLog !== undefined ? data.StatusLOG.SysLog : "?")
		);
	$(row)
		.find(".wificonfig span")
		.html(data.StatusNET.WifiConfig !== undefined ? data.StatusNET.WifiConfig : "?");
	$(row)
		.find(".vcc span")
		.html(data.StatusSTS.Vcc !== undefined ? data.StatusSTS.Vcc + "V" : "?");

	$(row).removeClass("updating");
}
