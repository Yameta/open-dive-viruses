$(document).ready(function ()
{
	BX.addCustomEvent('onAjaxSuccess', setReadonly);
	//BX.addCustomEvent('onAjaxSuccess', consoleMessage);
	setReadonly();
});

function setReadonly()
{
	console.log('WORK CRM');
	var personTypeFiled = $('input[name="PERSON_TYPE_ID"]');
	console.log(personTypeFiled);
	var personTypeId = $(personTypeFiled).val();
	console.log(personTypeId);
	var propsIds = {};
	if(personTypeId > 0)
	{
		$.ajax({
			type: "POST",
			url: "/bitrix/js/russianpost.post/orders_codes.php",
			//dataType: "json",
			data: {
				"person_type_id": personTypeId,
			},
			//data: "DO=MAKE_ORDER&sessid=<?=bitrix_sessid();?>&sessions="+strSelectSessions+"&dops="+strSelectDops+"&pay_system="+paySystemId,
			success: function(msg){

				if (msg != null && msg != undefined){
					if(msg.status == 'success')
					{
						propsIds = msg;
						var zip = $('input[name="PROPERTY_'+propsIds.zip+'"]').val();
						var loc = $('input[name="PROPERTY_'+propsIds.location+'"]').val();
						var priceOrder = $('input[name="PRICE"]').val();
						var address = $('input[name=PROPERTY_'+propsIds.address+']').val();
						var index_shipment = 0;
						var id_delivery = 0;
						$("input[name ^= 'SHIPMENT']").each(function ()
						{
							var strName = $(this).attr('name');
							var index_delivery = strName.indexOf('[DELIVERY_ID]');
							if(index_delivery != -1)
							{
								var index_s = strName.indexOf('[');
								var index_e = strName.indexOf(']');
								index_shipment = strName.substr(index_s+1, index_e - (index_s+1));
								id_delivery = $(this).val();
								if(id_delivery > 0)
								{
									$.ajax({
										type: "POST",
										url: "/bitrix/js/russianpost.post/crm_ajax.php",
										//dataType: "json",
										data: {
											"price_basket": priceOrder,
											"weight_basket": 0,
											"person_type_id": personTypeId,
											"delivery_id": id_delivery,
											"index_delivery": index_shipment,
											"location": loc,
										},
										//data: "DO=MAKE_ORDER&sessid=<?=bitrix_sessid();?>&sessions="+strSelectSessions+"&dops="+strSelectDops+"&pay_system="+paySystemId,
										success: function(msgAnsw){
											if (msgAnsw != null && msgAnsw != undefined){
												if(msgAnsw.status == 'success')
												{
													var link_set = $('#link_set').val();
													console.log(link_set);
													if(link_set == undefined)
													{
														$('#crm-order-shipment-discounts-'+index_shipment).append(msgAnsw.message);
													}
													else if(msgAnsw.message == '')
													{
														$('#crm-order-shipment-discounts-'+index_shipment).html(msgAnsw.message);
													}
												}
											}
										}
									});
								}
							}
						});
					}
				}
			}
		});
	}
}

var contentRussianpost = '<div id="ecom-widget" style="height: 100%"></div>';

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
			// объект со стил€ми дл€ иконки закрыти€, при null - иконки не будет
			opacity: 1
		},
		titleBar: '¬ыбор пункта выдачи',
		closeByEsc: true, // закрытие окна по esc
		darkMode: false, // окно будет светлым или темным
		autoHide: false, // закрытие при клике вне окна
		draggable: true, // можно двигать или нет
		resizable: true, // можно ресайзить
		min_height: 100, // минимальна€ высота окна
		min_width: 100, // минимальна€ ширина окна
		lightShadow: true, // использовать светлую тень у окна
		angle: true, // по€витс€ уголок
		overlay: {
			// объект со стил€ми фона
			// backgroundColor: 'black',
			// opacity: 500
		},
		buttons: [],
		events: {
			onPopupShow: function ()
			{
				// —обытие при показе окна
				console.log('POPUP SHOW');
			},
			onPopupClose: function ()
			{
				// —обытие при закрытии окна
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
		'/bitrix/js/russianpost.post/ajax_crm_map.php',
		post,
		function (data)
		{
			//console.log(data);
		}
	);


	popupRussianpost.show();
};

function callbackCrmFunction(data)
{
	var name_type = 'PROPERTY_'+$('#russianpost_delivery_type_prop').val();
	var name_zip = 'PROPERTY_'+$('#russianpost_zip_prop').val();
	var name_address = 'PROPERTY_'+$('#russianpost_address_prop').val();
	var name_street = 'PROPERTY_'+$('#russianpost_street_prop').val();
	var index_delivery = $('#russianpost_index_delivery').val();
	var name_price = 'SHIPMENT['+index_delivery+'][PRICE_DELIVERY]';

	$('[name=\''+name_type+'\']').val(data.mailType);
	$('[name=\''+name_price+'\']').val(data.cashOfDelivery / 100);
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
	$('#russianpost_result_price').val(data.cashOfDelivery);
	if(data.deliveryDescription != null)
	{
		if(typeof data.deliveryDescription['description'] != "undefined")
		{
			$('#russianpost_delivery_description').val(data.deliveryDescription['description']);
		}
	}

	popupRussianpost.close();
	//BX.Sale.OrderAjaxComponent.sendRequest();
	//for old
	//submitForm();
}