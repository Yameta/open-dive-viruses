$(document).ready(function ()
{
	BX.addCustomEvent('onAjaxSuccess', setReadonly);
	//BX.addCustomEvent('onAjaxSuccess', consoleMessage);
	setReadonly();
});

function callbackFunctionPostMap(data)
{
	$('#russianpost_result_type').val(data.mailType);
	$('#russianpost_result_price').val(data.cashOfDelivery);
	$('#russianpost_result_zip').val(data.indexTo);

	$('#PRICE_DELIVERY_'+$('#russianpost_index_delivery').val()).val(data.cashOfDelivery / 100);
	$('#BASE_PRICE_DELIVERY_'+$('#russianpost_index_delivery').val()).val(data.cashOfDelivery / 100);
	$('#CALCULATED_PRICE_'+$('#russianpost_index_delivery').val()).val(data.cashOfDelivery / 100);

	var fullAddress = '';
	if (data.regionTo != null)
		fullAddress = fullAddress + data.regionTo + ' ';
	if (data.areaTo != null)
		fullAddress = fullAddress + data.areaTo + ' ';
	if (data.cityTo != null)
		fullAddress = fullAddress + data.cityTo + ' ';
	if (data.addressTo != null)
		fullAddress = fullAddress + data.addressTo;
	$('#russianpost_result_address').val(fullAddress);
	var splitAddress = $('#russianpost_split_address').val();
	if(splitAddress == 'true')
	{
		$('#russianpost_street_address').val(data.addressTo);
	}

	popupRussianpost.close();
	//BX.Sale.OrderAjaxComponent.sendRequest();
	//for old
	//submitForm();
}
var contentRussianpost = '<div id="ecom-widget" style="height: 500px"></div>';

var popupRussianpost = '';
function openMap(guidId, price, weight, zip, location)
{
	let width = 600;
	let heigth = 600;

	if (document.body.clientWidth < 600)
	{
		width = document.body.clientWidth;
	}
	if (document.body.clientHeight < 600)
	{
		width = document.body.clientHeight - 30;
	}

	popupRussianpost = BX.PopupWindowManager.create("popup-message", BX('element'), {
		content: contentRussianpost,

		width: width, // ширина окна
		height: heigth, // высота окна
		zIndex: 1300, // z-index
		closeIcon: {
			// объект со стилями для иконки закрытия, при null - иконки не будет
			opacity: 1
		},
		titleBar: 'Выбор пункта выдачи',
		closeByEsc: true, // закрытие окна по esc
		darkMode: false, // окно будет светлым или темным
		autoHide: false, // закрытие при клике вне окна
		draggable: true, // можно двигать или нет
		resizable: true, // можно ресайзить
		min_height: 100, // минимальная высота окна
		min_width: 100, // минимальная ширина окна
		lightShadow: true, // использовать светлую тень у окна
		angle: true, // появится уголок
		overlay: {
			// объект со стилями фона
			// backgroundColor: 'black',
			// opacity: 500
		},
		buttons: [],
		events: {
			onPopupShow: function ()
			{
				// Событие при показе окна
				console.log('POPUP SHOW');
			},
			onPopupClose: function ()
			{
				// Событие при закрытии окна
			}
		}
	});

	$('#ecom-widget').html('');
	//if(weight == 0) weight = 10;
	var post = {};
	post['guidId'] = guidId;
	post['price'] = price;
	post['weight'] = weight;
	post['location'] = location;


	BX.ajax.post(
		'/bitrix/js/russianpost.post/ajax_admin_map.php',
		post,
		function (data)
		{
			//console.log(data);
		}
	);


	popupRussianpost.show();
};

function setReadonly()
{
	console.log('WORK !!!');
	//console.log(BX.Sale.Admin.OrderBasketObj);
	//console.log(BX.Sale.Admin.OrderBuyer.savedPropsCollections);
console.log(BX.Sale.Admin.OrderEditPage.orderId);
	console.log(BX.Sale.Admin.ShipmentBasketObj);
	//console.log(BX.Sale.Admin.ShipmentBasketObj);
	//console.log(BX.Sale.Admin.OrderBasketObj.totalBlock.fields['PRICE_BASKET'].value);
	//console.log(BX.Sale.Admin.OrderBasketObj.totalBlock.fields['WEIGHT'].value);
	//console.log(BX.Sale.Admin.OrderBasketObj.weightUnit);
	//BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters({'MAP': '<a href="http://www.mail.ru" target="_blank">Выбрать отделение</a>'});
	//BX.Sale.Admin.OrderShipment.updateMap('THIS IS MAP');
	var profile_length = 8;
	var delivery_length = 9;
var order_id = BX.Sale.Admin.OrderEditPage.orderId;
	$("select[name ^= 'SHIPMENT']").each(function ()
	{
		var ship_id = $(this).attr('id');
		if(typeof ship_id != "undefined")
		{
			var index_profile = ship_id.indexOf('PROFILE_');
			var index_delivery = ship_id.indexOf('DELIVERY_');
			var index_shipment = 0;
			var id_delivery = 0;
			if(index_profile != -1)
			{
				index_shipment = ship_id.substr(profile_length);
				id_delivery = $(this).val();

				if(id_delivery > 0)
				{
					$.ajax({
						type: "POST",
						url: "/bitrix/js/russianpost.post/admin_edit_ajax.php",
						//dataType: "json",
						data: {
							"order_id": order_id,
							"delivery_id": id_delivery,
							"index_delivery": index_shipment,
						},
						//data: "DO=MAKE_ORDER&sessid=<?=bitrix_sessid();?>&sessions="+strSelectSessions+"&dops="+strSelectDops+"&pay_system="+paySystemId,
						success: function(msg){
							if (msg != null && msg != undefined){
								if(msg.status == 'success')
								{
									var link_set = $('#link_set').val();
									console.log(link_set);
									if(link_set == undefined)
									{
										$('#section_map_'+index_shipment).append(msg.message);
									}
									else if(msg.message == '')
									{
										$('#section_map_'+index_shipment).html(msg.message);
									}
								}
							}
						}
					});
				}
			}
			else if(index_delivery != -1)
			{
				//console.log(ship_id.substr(delivery_length));
				//index_shipment = ship_id.substr(delivery_length);
			}
		}
		console.log($(this).attr('name'));
		console.log($(this).attr('id'));
		console.log('SHIP');
	})
}