$(document).ready(function ()
{
	console.log('ADD');
	BX.addCustomEvent('onAjaxSuccess', setReadonly);
	BX.addCustomEvent('onAjaxSuccess', consoleMessage);
	BX.addCustomEvent('onAjaxSuccess', openPopUpMap);
    setReadonly();
	var target = $('#bx-soa-delivery')[0];
	if(typeof target != "undefined")
	{
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if(mutation.type == 'childList')
                {
                    openPopUpMap();
                }
            });
        });
        var config = { attributes: true, childList: true, characterData: true };
        observer.observe(target,  config);
	}
});

var contentRussianpost = '<div id="ecom-widget" style="height: 100%"></div>';

var popupRussianpost = '';


function callbackFunctionMap(data)
{
	console.log(data);
	$('#russianpost_result_type').val(data.mailType);
	$('#russianpost_result_price').val(data.cashOfDelivery);
	$('#russianpost_result_zip').val(data.indexTo);
	if(data.deliveryDescription != null)
	{
		if(typeof data.deliveryDescription['description'] != "undefined")
		{
			$('#russianpost_delivery_description').val(data.deliveryDescription['description']);
		}
	}
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
	if(splitAddress == 'true' || splitAddress == '1')
	{
		$('#russianpost_street_address').val(data.addressTo);
	}
	$('#russianpost_select_pvz').val('Y');
	popupRussianpost.close();
	BX.Sale.OrderAjaxComponent.sendRequest();
	//for old
	//submitForm();
}

function setReadonly()
{
	var setReadonly = $('#russianpost_set_readonly').val();
	if (typeof setReadonly != "undefined" && setReadonly == 'Y')
	{
		var addressProp = $('#russianpost_address_prop').val();
		$('#zipProperty').attr('readonly', true);
		$('#zipProperty').css("background-color", "rgb(238, 238, 238)");
		$('#soa-property-' + addressProp).attr('readonly', true);
		$('#soa-property-' + addressProp).css("background-color", "rgb(238, 238, 238)");
		var address = $('#soa-property-' + addressProp).val();
		var addressRussianpost = $('#russianpost_result_address').val();
		if(typeof address != "undefined" && address == '')
		{
			$('#soa-property-' + addressProp).val(addressRussianpost);
		}
		var splitAddress = $('#russianpost_split_address').val();
		if(splitAddress == 'true' || splitAddress == '1')
		{
			var streetProp = $('#russianpost_street_prop').val();
			$('#soa-property-' + streetProp).attr('readonly', true);
		}
	}
	var selectPvz = $('#russianpost_select_pvz').val();
	bxpost_errors=[];
	if (typeof selectPvz != "undefined" && selectPvz != 'Y' ){
		//$('#bx-soa-orderSave a').hide();
		bxpost_errors[0]=(BX.message('SALE_DLV_RUSSIANPOST_PVZ_EMPTY'));
	}
	var errMessage = $('#russianpost_error_txt').val();
	if(typeof errMessage != "undefined" && errMessage != '' && errMessage != 'null')
	{
		errMessage = errMessage.replace(/\\/g, '');
		bxpost_errors.push(errMessage);
	}
	if(typeof BX.Sale.OrderAjaxComponent != 'undefined')
	{
		if (typeof (BX.Sale.OrderAjaxComponent.showBlockErrors) === 'function'){
			BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY = bxpost_errors;
			BX.Sale.OrderAjaxComponent.showBlockErrors(BX.Sale.OrderAjaxComponent.deliveryBlockNode);
		}else if (typeof (BX.Sale.OrderAjaxComponent.showError)  === 'function' && bxpost_errors.length >0){
			BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.deliveryBlockNode, bxpost_errors[0]);
		}
	}
}

function openPopUpMap()
{
	if(typeof BX.Sale.OrderAjaxComponent != 'undefined')
	{
		var activeSection = BX.Sale.OrderAjaxComponent.activeSectionId;
		//console.log(activeSection);
		var openMapPr = $('#russianpost_open_map').val();
		if (typeof openMapPr != "undefined" && openMapPr == 'Y' && typeof activeSection != "undefined" && activeSection == 'bx-soa-delivery')
		{
			$('#russianpost_open_map').val('N');
			$('#russianpost_btn_openmap').click();
		}
	}
}

function consoleMessage()
{
	var errMessage = $('#russianpost_error_tarif').val();
	if (errMessage != null && typeof (errMessage) != undefined)
	{
		console.log('CALCULATE ERROR DETAILED ANSWER');
		errMessage = errMessage.replace(/\\/g, '');
		console.log(JSON.parse(errMessage));
	}
}

function openMap(guidId, price, weight, zip, location, prodIds)
{
	let width = 600;
	let heigth = 600;

	if (document.body.clientWidth < 600)
	{
		width = document.body.clientWidth;
	}
	if (document.body.clientHeight < 600)
	{
		heigth = document.body.clientHeight - 30;
	}
	var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
	popupRussianpost = BX.PopupWindowManager.create("popup-message", BX('element'), {
		content: contentRussianpost,

		width: width, // ширина окна
		height: heigth, // высота окна
		zIndex: 1300, // z-index
		closeIcon: {
			// объект со стилями для иконки закрытия, при null - иконки не будет
			opacity: 1
		},
		titleBar: BX.message('SALE_DLV_RUSSIANPOST_JS_TITLE'),
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
		buttons: [
			new BX.PopupWindowButton({
				text: BX.message('SALE_DLV_RUSSIANPOST_JS_BTN_FULL'), // текст кнопки
				id: 'full-btn', // идентификатор
				className: 'ui-btn ui-btn-success', // доп. классы
				events: {
					click: function() {
						// Событие при клике на кнопку
						var fullMap = $('#russianpost_full_map').val();
						if(fullMap == 'N')
						{
							console.log('FULL MAP');
							$('#popup-message').css('width', '100%');
							$('#popup-message').css('height', '100%');
							$('#popup-message').css('left', 0);
							$('#popup-message').css('top',window.pageYOffset || document.documentElement.scrollTop);
							$('#russianpost_full_map').val('Y');
							$('#full-btn').html(BX.message('SALE_DLV_RUSSIANPOST_JS_BTN_WND'));
						}
						else
						{
							let widthN = 600;
							let heigthN = 600;

							if (document.body.clientWidth < 600)
							{
								widthN = document.body.clientWidth;
							}
							if (document.body.clientHeight < 600)
							{
								heigthN = document.body.clientHeight - 30;
							}
							$('#popup-message').css('width', widthN);
							$('#popup-message').css('height', heigthN);
							$('#full-btn').html(BX.message('SALE_DLV_RUSSIANPOST_JS_BTN_FULL'));
							$('#russianpost_full_map').val('N');
						}

					}
				}
			}),
		],
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
	post['prodIds'] = prodIds;

	BX.ajax.post(
		'/bitrix/js/russianpost.post/ajax.php',
		post,
		function (data)
		{
			//console.log(data);
		}
	);


	popupRussianpost.show();
};

