<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>
<script
	src="https://widget.pochta.ru/map/widget/widget.js">
</script>
<script>
	function callbackFunction(data)
	 {
	 console.log('ajax data');
	 console.log(data);
	 }
</script>

<div id="ecom-widget" style="height: 500px">
	<script>
		ecomStartWidget({
			accountId: 'c6eb0aab-5bc9-46fc-8780-f1c163d89233',
			accountType: 'bitrix_cms',
			weight: 280,
			sumoc: 107000,
			//startZip: [<?=$strZip;?>],
			startZip: ['344'],
			callbackFunction: callbackFunction,
			containerId: 'ecom-widget'
		});
	</script>
</div>
