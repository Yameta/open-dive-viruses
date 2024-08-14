$(document).ready(function ()
{
	BX.addCustomEvent('onAjaxSuccess', setReadonly);
	//BX.addCustomEvent('onAjaxSuccess', consoleMessage);
	setReadonly();
});

function callbackFunctionPostMap(data)
{
	var name_type = 'PROPERTIES['+$('#russianpost_delivery_type_prop').val()+']';
	var name_zip = 'PROPERTIES['+$('#russianpost_zip_prop').val()+']';
	var name_address = 'PROPERTIES['+$('#russianpost_address_prop').val()+']';
	var name_street = 'PROPERTIES['+$('#russianpost_street_prop').val()+']';
	$('[name=\''+name_type+'\']').val(data.mailType);
	$('#PRICE_DELIVERY_'+$('#russianpost_index_delivery').val()).val(data.cashOfDelivery / 100);
	$('[name=\''+name_zip+'\']').val(data.indexTo);

	var fullAddress = '';
	if (data.regionTo != null)
		fullAddress = fullAddress + data.regionTo + ' ';
	if (data.areaTo != null)
		fullAddress = fullAddress + data.areaTo + ' ';
	if (data.cityTo != null)
		fullAddress = fullAddress + data.cityTo + ' ';
	if (data.addressTo != null)
		fullAddress = fullAddress + data.addressTo;

	var splitAddress = $('#russianpost_split_address').val();
	if(splitAddress == 'true')
	{
		if($('#russianpost_street_prop_type').val() == 'TEXTAREA')
		{
			$('[name=\''+name_street+'\']').html(data.addressTo);
		}
		else
		{
			$('[name=\''+name_street+'\']').val(data.addressTo);
		}
	}
	else
	{
		if($('#russianpost_street_prop_type').val() == 'TEXTAREA')
		{
			$('[name=\''+name_address+'\']').html(fullAddress);
		}
		else
		{
			$('[name=\''+name_address+'\']').val(fullAddress);
		}
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

	var personTypeId = BX.Sale.Admin.OrderBuyer.getBuyerTypeId();

	var propertyCollection = BX.Sale.Admin.OrderBuyer.savedPropsCollections[personTypeId];

	if(!propertyCollection)
	{
		console.log('ERROR');
		BX.debug('Error! Can\'t initialize property collection!');
		//return;
	}
	groupIterator = propertyCollection.getGroupIterator();
	var ar_property = [];
	while (group = groupIterator())
	{
		var name = group.getName() ? BX.util.htmlspecialchars(group.getName()) : BX.message('SALE_ORDER_BUYER_UNKNOWN_GROUP'),
			propsIterator =  group.getIterator();

		property = propsIterator();
		if (!property)
		{
			console.log('NO PORP')
			continue;
		}

		while (property)
		{
			//console.log(property);
			var prAr = [];
			prAr.push(property.getName());
			prAr.push(property.getId());
			prAr.push(property.getValue());
			ar_property.push(prAr);
			property = propsIterator();
		}
	}
	console.log(ar_property);
	//console.log(BX.Sale.Admin.ShipmentBasketObj);
	//console.log(BX.Sale.Admin.OrderBasketObj.totalBlock.fields['PRICE_BASKET'].value);
	//console.log(BX.Sale.Admin.OrderBasketObj.totalBlock.fields['WEIGHT'].value);
	//console.log(BX.Sale.Admin.OrderBasketObj.weightUnit);
	//BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters({'MAP': '<a href="http://www.mail.ru" target="_blank">Выбрать отделение</a>'});
	//BX.Sale.Admin.OrderShipment.updateMap('THIS IS MAP');
	var profile_length = 8;
	var delivery_length = 9;
	var price_basket = BX.Sale.Admin.OrderBasketObj.totalBlock.fields['PRICE_BASKET'].value;
	var weight_basket = BX.Sale.Admin.OrderBasketObj.totalBlock.fields['WEIGHT'].value;
	var weight_unit = BX.Sale.Admin.OrderBasketObj.weightUnit;
	$("select[name ^= 'SHIPMENT']").each(function ()
	{
		var ship_id = $(this).attr('id');
		var index_profile = ship_id.indexOf('PROFILE_');
		var index_delivery = ship_id.indexOf('DELIVERY_');
		var index_shipment = 0;
		var id_delivery = 0;
		if(index_profile != -1)
		{
			index_shipment = ship_id.substr(profile_length);
			id_delivery = $(this).val();
			console.log(ar_property);
			if(id_delivery > 0)
			{
				$.ajax({
					type: "POST",
					url: "/bitrix/js/russianpost.post/admin_ajax.php",
					//dataType: "json",
					data: {
						"price_basket": price_basket,
						"weight_basket": weight_basket,
						"weight_unit": weight_unit,
						"props": ar_property,
						"person_type_id": personTypeId,
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
		console.log($(this).attr('name'));
		console.log($(this).attr('id'));
		console.log('SHIP');
	})
}