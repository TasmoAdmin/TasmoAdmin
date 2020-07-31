$(document).on("ready", function ()
{
	deviceTools();
	updateAllStatus();

	initCommandHelper();


	$(".showmore").on("change", function (e)
	{
		if ($(this).prop("checked"))
		{
			$(".showmore").prop("checked", true);
			Cookies.set("devices_show_more", "1");
			$("#device-list .more:not(.hidden)").show();
			$("label[for=" + $(".showmore").attr("id") + "]").removeClass("btn-secondary").addClass(
				"btn-primary");
		} else
		{
			$(".showmore").prop("checked", false);
			Cookies.set("devices_show_more", "0");
			$("#device-list .more").hide();
			$("label[for=" + $(".showmore").attr("id") + "]").removeClass("btn-primary").addClass(
				"btn-secondary");
		}
		$(".doubleScroll-scroll").css({
										  width: $("#device-list").width()
									  }).parent().trigger("resize");
	});

	if (Cookies.get("devices_show_more") !== undefined && Cookies.get("devices_show_more") == "1")
	{
		$(".showmore").prop("checked", true);
		$("#device-list .more:not(.hidden)").show();
		$("label[for=" + $(".showmore").attr("id") + "]").removeClass("btn-secondary").addClass(
			"btn-primary");
		$(".doubleScroll-scroll").css({
										  width: $("#device-list").width()
									  }).parent().trigger("resize");
	}

	$(".table-responsive").attachDragger();
	//console.log( "5.10.0 => " + parseVersion( "5.10.0" ) );
	//console.log( "5.10.0g => " + parseVersion( "5.10.0g" ) );
	//console.log( "5.10.0h => " + parseVersion( "5.10.0h" ) );
	//console.log( "5.10.0i => " + parseVersion( "5.10.0i" ) );
	//console.log( "====" );
	//console.log( "5.10.0j => " + parseVersion( "5.10.0j" ) );
	//console.log( "5.10.0z => " + parseVersion( "5.10.0z" ) );
	//console.log( "5.11.1 => " + parseVersion( "5.11.1" ) );
	//console.log( "5.11.0 => " + parseVersion( "v5.11.0" ) );
	//console.log( "5.11.1b => " + parseVersion( "5.11.1b" ) );
	//console.log( "5.11.1d => " + parseVersion( "5.11.1d" ) );
	//console.log( "5.11.1z => " + parseVersion( "5.11.1z" ) );

	if (refreshtime)
	{
		console.log("[Global][Refreshtime]" + refreshtime + "ms");
		setInterval(function ()
					{
						console.log("[Global][Refreshtime] updateStatus now");
						//updateStatus();
						updateStatus();
					}, refreshtime);
	} else
	{
		console.log("[Global][Refreshtime] " + $.i18n("NO_REFRESH") + "");

	}

});

function initCommandHelper()
{
	$(".showCommandInput").on("click", function (e)
	{
		$(this).toggleClass("btn-secondary").toggleClass("btn-primary");
		$(".command-hidden").toggleClass("d-none");
		$(".cmd_cb").toggleClass("d-none").find("input").prop("checked", false);
		$(".cmdContainer ").removeClass("has-error");
		$(".cmdContainer ").find("input").val("");
		$(".cmdContainer ").find("#commandInputError").html("");
	});

	$(".cmdContainer ").find("input").keypress(function (e)
											   {
												   if (e.which == 13)
												   {//Enter key pressed
													   $(".sendCommand").click();//Trigger search button click event
												   }
											   });


	$(".select_all").change(function ()
							{  //"select all" change
								var status = this.checked; // "select all" checked status
								$(".device_checkbox").each(function ()
														   { //iterate all listed checkbox items
															   this.checked = status; //change ".checkbox" checked status
														   });

								$(".select_all").each(function ()
													  { //iterate all listed checkbox items
														  this.checked = status; //change ".checkbox" checked status
													  });

							});

	$(".device_checkbox").change(function ()
								 { //".checkbox" change
									 //uncheck "select all", if one of the listed checkbox item is unchecked
									 if (this.checked == false)
									 { //if this item is unchecked
										 $(".select_all").each(function ()
															   { //iterate all listed checkbox items
																   this.checked = false; //change ".checkbox" checked status
															   });
									 }

									 //check "select all" if all checkbox items are checked
									 if ($(".device_checkbox:checked").length == $(".device_checkbox").length)
									 {
										 $(".select_all").each(function ()
															   { //iterate all listed checkbox items
																   this.checked = true; //change ".checkbox" checked status
															   });
									 }
								 });


	$(".sendCommand").on("click", function (e)
	{
		$(this)
			.parent()
			.parent().removeClass("has-error")
			.find("#commandInputError")
			.addClass("d-none").html("");

		var selectedDevices = $.map($(".cmd_cb:not(.link ) input:not(.select_all):checked"), function (elem, idx)
		{
			var d = new Array($(elem).val());
			return d;
		});
		//console.log( selectedDevices );
		if (selectedDevices.length === 0)
		{
			$(this)
				.parent()
				.parent().addClass("has-error")
				.find("#commandInputError")
				.removeClass("d-none")
				.html(
					$.i18n("ERROR_COMMAND_NO_DEVICE_SELECTED")
				);
			return false;
		}

		var cmnd = $(this)
			.parent()
			.parent().find(".commandInput").val();

		//console.log( cmnd );
		if (cmnd === "")
		{
			$(this)
				.parent()
				.parent().addClass("has-error")
				.find("#commandInputError")
				.removeClass("d-none")
				.html(
					$.i18n("ERROR_PLS_ENTER_COMMAND")
				);
			return false;
		}

		$.each(selectedDevices, function (idx, device_id)
		{
			console.log(device_id);
			Sonoff.generic(device_id, cmnd, undefined, function (result)
			{
				console.log(result);
				var device_name = $("[data-device_id=" + device_id + "]:first")
					.find(".device_name a")
					.text()
					.trim();
				$("#commandInputError").append("ID " + device_id + " (" + device_name + ") => " + JSON.stringify(
					result) + "<br/>");
			});
		});

		$(this)
			.parent()
			.parent()
			.find("#commandInputError")
			.removeClass("d-none")
			.append(
				$.i18n("SUCCESS_COMMAND_SEND") + "</br>"
			);

	});
}


function updateStatus()
{
	$("#device-list tbody tr").each(function (key, tr)
									{

										var device_ip = $(tr).data("device_ip");
										var device_id = $(tr).data("device_id");
										var device_relais = $(tr).data("device_relais");
										var device_group = $(tr).data("device_group");
										if (!$(tr).hasClass("updating"))
										{
											console.log("[Devices][updateStatus]get status from " + $(tr).data("device_ip"));
											$(tr).addClass("updating");

											if (device_group === "multi" && device_relais > 1)
											{
												console.log("[Devices][updateStatus]SKIP multi " + $(tr).data("device_ip"));
												return; //relais 1 will update all others
											}

											Sonoff.getStatus(device_ip, device_id, device_relais, function (data)
											{
												if (data
													&& !data.ERROR
													&& !data.WARNING
													&& data !== ""
													&& data !== undefined
													&& data.statusText === undefined
												)
												{
													//console.log( "DATA => " + JSON.stringify( data ) );
													if (device_group === "multi")
													{
														$("#device-list tbody tr[data-device_group=\"multi\"][data-device_ip=\"" + device_ip + "\"]")
															.each(function (key, grouptr)
																  {

																	  var device_relais = $(grouptr).data("device_relais");
																	  var device_status = Sonoff.parseDeviceStatus(data, device_relais);

																	  updateRow($(grouptr), data, device_status);
																	  $(grouptr).removeClass("updating");
																  });
													} else
													{

														var device_status = Sonoff.parseDeviceStatus(data, device_relais);

														console.log(device_status);
														updateRow($(tr), data, device_status);
													}
												} else
												{
													console.log("ERROR => " + JSON.stringify(data));
													if (device_group === "multi")
													{
														$("#device-list tbody tr[data-device_group=\"multi\"][data-device_ip=\"" + device_ip + "\"]")
															.each(function (key, grouptr)
																  {

																	  $(grouptr)
																		  .find(".status")
																		  .find("input")
																		  //.removeProp( "checked" )
																		  .parent()
																		  .addClass("error");
																	  //$( grouptr ).find( ".rssi span" ).html( $.i18n( 'ERROR' ) );
																	  //$( grouptr ).find( ".runtime span" ).html( $.i18n( 'ERROR' ) );
																	  //$( grouptr ).find( ".version span" ).html( $.i18n( 'ERROR' ) );

																	  $(grouptr).find("td").each(function (key, td)
																								 {
																									 //console.log( key );
																									 //console.log( td );
																									 if ($(td).find(".loader").length > 0)
																									 {
																										 $(td).find("span").html("-");
																									 }
																								 });

																	  $(grouptr).removeClass("updating");
																  });
													} else
													{

														$(tr).find(".status").find("input")
															//.removeProp( "checked" )
															 .parent().addClass("error");
														//$( tr ).find( ".rssi span" ).html( $.i18n( 'ERROR' ) );
														//$( tr ).find( ".runtime span" ).html( $.i18n( 'ERROR' ) );
														//$( tr ).find( ".version span" ).html( $.i18n( 'ERROR' ) );
														$(tr).removeClass("updating");
													}
												}

											});
										} else
										{
											console.log("[Devices][updateStatus]SKIP get status from " + $(tr).data("device_ip"));
										}

									});


};


function updateAllStatus()
{

	var device_holder = $("#device-list");


	if (!device_holder.hasClass("updating"))
	{
		device_holder.addClass("updating");

		console.log("[Devices][updateAllStatus]START");

		var timeout = device_holder.find("tbody tr").length * 15; //max 12 sec per device

		Sonoff.getAllStatus(timeout, function (result)
							{
								device_holder.find("tbody tr").each(function (key, tr)
																	{
																		var device_id = $(tr).data("device_id");
																		var device_relais = $(tr).data("device_relais");
																		var device_group = $(tr).data("device_group");
																		var data = result[device_id] || undefined;
																		if (data !== undefined
																			&& !$.isEmptyObject(data)
																			&& !data.ERROR
																			&& !data.WARNING
																			&& data !== ""
																			&& data.statusText === undefined
																		)
																		{
																			console.log("[LIST][updateAllStatus][" + device_id + "]MSG => " + JSON.stringify(data));

																			var device_status = Sonoff.parseDeviceStatus(data, device_relais);

																			$(tr).removeAttr(
																				"data-original-title"
																			).removeAttr("data-toggle");

																			updateRow($(tr), data, device_status);
																			$(tr).find(".status").find("input").parent().removeClass("error");
																		} else
																		{
																			console.log("[LIST][updateAllStatus]["
																						+ device_id
																						+ "][ERROR] DATA => "
																						+ JSON.stringify(data));


																			if ($(tr).hasClass("toggled"))
																			{
																				$(tr).removeClass("toggled");
																			} else
																			{
																				$(tr).find(".status").find("input")
																					//.removeProp( "checked" )
																					 .parent().addClass("error");
																			}

																			var msg = $.i18n("ERROR");
																			if (data !== undefined)
																			{
																				if (data.ERROR !== undefined)
																				{
																					msg = data.ERROR;
																				} else if (data.WARNING !== undefined)
																				{
																					msg = data.WARNING;
																				} else if (data.statusText !== undefined)
																				{
																					msg = data.statusText;
																				}
																			} else
																			{
																				msg = "data is empty";
																			}

																			$(tr).attr(
																				"data-original-title",
																				msg
																			).attr("data-toggle", "tooltip").tooltip({
																														 html: true,
																														 delay: 700
																													 });

																			//$( tr ).find( ".rssi span" ).html( $.i18n( 'ERROR' ) );
																			//$( tr ).find( ".runtime span" ).html( "-" );
																			//$( tr ).find( ".version span" ).html( "-" );
																			//$( tr ).find( "td.more:not(.static) span" ).html( "-" );
																			$(tr).find("td").each(function (key, td)
																								  {
																									  if ($(td).find(".loader").length > 0)
																									  {
																										  $(td).find("span").html("-");
																									  }
																								  });
																		}


																	});

								device_holder.removeClass("updating");

							}
		);
	} else
	{
		console.log("[Devices][updateAllStatus]SKIP");
	}

}
;


function deviceTools()
{
	$("#device-list tbody tr td.status").on("click", function (e)
	{
		e.preventDefault();
		var statusField = $(this);
		var device_ip = $(this).closest("tr").data("device_ip");
		var device_id = $(this).closest("tr").data("device_id");
		var device_relais = $(this).closest("tr").data("device_relais");

		if (statusField.find("input").prop("checked"))
		{
			statusField.find("input").removeProp("checked");
		} else
		{
			statusField.find("input").prop("checked", "checked");
		}

		$(this).closest("tr").addClass("toggled");

		Sonoff.toggle(device_ip, device_id, device_relais, function (data)
		{
			if (data && !data.ERROR && !data.WARNING)
			{

				var device_status = Sonoff.parseDeviceStatus(data, device_relais);
				//if ( device_status == "ON" ) {
				//	statusField.find( "input" ).prop( "checked", "checked" );
				//} else {
				//	statusField.find( "input" ).removeProp( "checked" );
				//}
			} else
			{
				statusField.find("input")
					//.removeProp( "checked" )
						   .parent().addClass("error");
			}
		});


	});

	$("#deleteDeviceModal").on("show.bs.modal", function (event)
	{

		var modal = $(this);

		var button = $(event.relatedTarget); // Button that triggered the modal

		console.log(modal.find(".btn-ok"));
		modal.find(".btn-ok").attr("href", button.attr("href"));

		var body = button.data("dialog-text");
		modal.find(".modal-body").html(body);
	});

	$("#device-list tbody tr td a.restart-device").on("click", function (e)
	{
		e.preventDefault();
		var device_id = $(this).closest("tr").data("device_id");
		Sonoff.generic(device_id, "Restart", 1);

	});


	$(document).on("dblclick", ".dblcEdit span", function ()
	{
		oriVal = $(this).text().toString().trim();
		$(this).text("").addClass("dont-update");
		var w = oriVal.toString().length * 10 + 20;
		input = $("<input class='dblEdit-Input form-control' type='text' style='width: "
				  + w
				  + "px; padding: 3px;'>");
		input.appendTo($(this)).focus();

	});


	$(document).on("focusout keypress", ".dblEdit-Input", function (e)
	{
		if (e.type === "keypress" && e.which !== 13)
		{
			return;
		}
		if (input.val() !== "")
		{
			var newvalue = input.val();
			var device_id = $(this).closest("tr").data("device_id");
			var target = $(this).closest("td").data("target") || "device";
			var cmnd = $(this).closest("td").data("cmnd") || "";
			var field = $(this).closest("td").data("field") || "";
			$(this).hide();
			var td = $(this).parent();
			$(this)
				.parent()
				.removeClass("dont-update")
				.html($.i18n("TEXT_LOADING"))
				.removeClass("dont-update");
			if (target == "device")
			{
				Sonoff.updateConfig(device_id, cmnd, newvalue, updateStatus);
			} else if (target == "csv")
			{
				Sonoff.setDeviceValue(device_id, field, newvalue, td);
			}
		} else
		{
			$(this).parent().removeClass("dont-update").text(oriVal);
		}

	});
}

function updateRow(row, data, device_status)
{
	var version = "n/A";
	if (data.StatusFWR !== undefined)
	{
		version = parseVersion(data.StatusFWR.Version);
	}
	//console.log( "version => " + version );

	if (version >= 510009)
	{//no json translations since 5.10.0j
		var rssi = data.StatusSTS.Wifi.RSSI;
		var ssid = data.StatusSTS.Wifi.SSId;
		var uptime = data.StatusSTS.Uptime;
	} else
	{ //try german else use english
		var rssi = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.RSSI : data.StatusSTS.Wifi.RSSI;
		var ssid = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.SSID : data.StatusSTS.Wifi.SSId;
		var uptime = data.StatusSTS.Laufzeit ? data.StatusSTS.Laufzeit : data.StatusSTS.Uptime;

	}

	var energyPower = getEnergyPower(data);

	if (energyPower !== "")
	{
		$(row).find(".energyPower span").html(energyPower);
		$("#device-list .energyPower").removeClass("hidden");
	}

	var temp = getTemp(data);

	if (temp !== "")
	{
		$(row).find(".temp span").html(temp);
		$("#device-list .temp").removeClass("hidden");
	}


	var humidity = getHumidity(data);

	if (humidity !== "")
	{
		$(row).find(".humidity span").html(humidity);
		$("#device-list .humidity").removeClass("hidden");
	}

	var pressure = getPressure(data);

	if (pressure !== "")
	{
		$(row).find(".pressure span").html(pressure);
		$("#device-list .pressure").removeClass("hidden");
	}

	var seapressure = getSeaPressure(data);

	if (seapressure !== "")
	{
		$(row).find(".seapressure span").html(seapressure);
		$("#device-list .seapressure").removeClass("hidden");
	}

	var distance = getDistance(data);

	if (distance !== "")
	{
		$(row).find(".distance span").html(distance);
		$("#device-list .distance").removeClass("hidden");
	}

	var gas = getGas(data);

	if (gas !== "")
	{
		$(row).find(".gas span").html(gas);
		$("#device-list .gas").removeClass("hidden");
	}

	var idx = (
		data.idx ? data.idx : ""
	);
	if (idx !== "")
	{
		$(row).find(".idx span").html(idx);
		$("#device-list .idx").removeClass("hidden").show();
	}

	$(row).find(".version span").html(data.StatusFWR.Version);


	if ($(row).hasClass("toggled"))
	{
		$(row).removeClass("toggled");
	} else
	{
		if (device_status === "ON")
		{
			$(row).find(".status").find("input").prop("checked", "checked").parent().removeClass("error");
		} else if (device_status === "NONE")
		{
			$(row).find(".status").find("input").removeProp("checked").parent().removeClass("error");
			$(row).find(".status").find("label").addClass("d-none");
		} else
		{
			$(row).find(".status").find("input").removeProp("checked").parent().removeClass("error");
		}
	}
	$(row).find(".rssi span").html(rssi + "%").attr(
		"data-original-title", ssid).attr("data-toggle", "tooltip").tooltip({
																				html: true,
																				delay: 700
																			});


	var startup = (
		(
			data.StatusPRM.StartupDateTimeUtc !== undefined
			? data.StatusPRM.StartupDateTimeUtc
			: (
				data.StatusPRM.StartupUTC !== undefined
				? data.StatusPRM.StartupUTC
				: ""
			)
		)
	);
	//console.log( startup );
	if (startup !== "")
	{

		//var startupdatetime = startup.replace( 'T', ' ' );
		var startupdatetime = startup + "Z".replace(/-/g, "/");
		//console.log( startupdatetime );
		startupdatetime = new Date(startupdatetime);
		//console.log( startupdatetime );
		//startupdatetime.setTime( startupdatetime.getTime() + (
		//	startupdatetime.getTimezoneOffset()
		//) * -1 * 60 * 1000 );
		//console.log( startupdatetime );
		var now = new Date();
		var sec_num = (
						  now - startupdatetime
					  ) / 1000;
		var days = Math.floor(sec_num / (
			3600 * 24
		));
		var hours = Math.floor((
								   sec_num - (
									   days * (
									   3600 * 24
									   )
								   )
							   ) / 3600);
		var minutes = Math.floor((
									 sec_num - (
										 days * (
										 3600 * 24
										 )
									 ) - (
										 hours * 3600
									 )
								 ) / 60);
		var seconds = Math.floor(sec_num - (
			days * (
			3600 * 24
			)
		) - (
									 hours * 3600
								 ) - (
									 minutes * 60
								 ));

		uptime = (
					 days !== 0 ? days + $.i18n("UPTIME_SHORT_DAY") : ""
				 ) + " " + (
					 hours !== 0 || days !== 0 ? hours + $.i18n("UPTIME_SHORT_HOUR") : ""
				 ) + " " + (
					 minutes !== 0 || hours !== 0 || days !== 0 ? minutes + $.i18n("UPTIME_SHORT_MIN") : ""
				 ) + " " + (
					 seconds
					 !== 0
					 || minutes
					 !== 0
					 || hours
					 !== 0
					 ? seconds
				 + $.i18n("UPTIME_SHORT_SEC")
					 : "-"
				 );

		uptime = $.trim(uptime);

		$(row).find(".runtime span").html(uptime).attr(
			"data-original-title",
			startupdatetime.toLocaleString($("html").attr("lang") + "-" + $("html")
				.attr("lang")
				.toUpperCase(), {hour12: false}
			)
		).attr("data-toggle", "tooltip").tooltip({
													 html: true,
													 delay: 700
												 });

	} else
	{
		console.log(uptime);
		$(row).find(".runtime span").html(uptime + "h");
	}


	//MORE
	if (!$(row).find(".hostname span").hasClass("dont-update"))
	{
		$(row).find(".hostname span").html(data.StatusNET.Hostname
										   !== undefined
										   ? data.StatusNET.Hostname
										   : "?");
	}

	if (!$(row).find(".mac span").hasClass("dont-update"))
	{
		$(row).find(".mac span").html(data.StatusNET.Mac !== undefined ? data.StatusNET.Mac : "?");
	}

	if (!$(row).find(".mqtt span").hasClass("dont-update"))
	{
		$(row).find(".mqtt span").html(data.StatusMQT !== undefined ? "1" : "0");
	}

	if (!$(row).find(".poweronstate span").hasClass("dont-update"))
	{
		$(row).find(".poweronstate span").html(data.Status.PowerOnState
											   !== undefined
											   ? data.Status.PowerOnState
											   : "?");
	}

	if (!$(row).find(".ledstate span").hasClass("dont-update"))
	{
		$(row).find(".ledstate span").html(data.Status.LedState !== undefined ? data.Status.LedState : "?");
	}


	if (!$(row).find(".savedata span").hasClass("dont-update"))
	{
		$(row).find(".savedata span").html(data.Status.SaveData !== undefined ? data.Status.SaveData : "?");
	}


	if (!$(row).find(".sleep span").hasClass("dont-update"))
	{
		$(row).find(".sleep span").html(data.StatusPRM.Sleep
										!== undefined
										? data.StatusPRM.Sleep
										  + "ms"
										: "?");
	}


	$(row).find(".bootcount span").html(data.StatusPRM.BootCount
										!== undefined
										? data.StatusPRM.BootCount
										: "?");
	$(row).find(".savecount span").html(data.StatusPRM.SaveCount
										!== undefined
										? data.StatusPRM.SaveCount
										: "?");
	$(row).find(".log span").html((
									  data.StatusLOG.SerialLog !== undefined ? data.StatusLOG.SerialLog : "?"
								  )
								  + "|"
								  + (
									  data.StatusLOG.WebLog !== undefined ? data.StatusLOG.WebLog : "?"
								  )
								  + "|"
								  + (
									  data.StatusLOG.SysLog !== undefined ? data.StatusLOG.SysLog : "?"
								  ));


	if (!$(row).find(".wificonfig span").hasClass("dont-update"))
	{
		$(row).find(".wificonfig span").html(data.StatusNET.WifiConfig
											 !== undefined
											 ? data.StatusNET.WifiConfig
											 : "?");
	}

	$(row).find(".vcc span").html(data.StatusSTS.Vcc !== undefined ? data.StatusSTS.Vcc + "V" : "?");

	$(".doubleScroll-scroll").css({
									  width: $("#device-list").width()
								  }).parent().trigger("resize");


	$(row).removeClass("updating");
}


