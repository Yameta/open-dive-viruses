<?
\Bitrix\Main\Loader::includeModule('aspro.max');
class CAsproMaxItemCustom extends \Aspro\Functions\CAsproMaxItem
{
	public static function showSectionImg($arParams = array(), $arItems = array(), $bIcons = false, $width=500, $height=500){
		if($arItems):?>
			<?ob_start();?>
				<?if($bIcons && $arItems["UF_CATALOG_ICON"]):?>
					<?$img = \CFile::ResizeImageGet($arItems["UF_CATALOG_ICON"], array( "width" => 40, "height" => 40 ), BX_RESIZE_IMAGE_EXACT, true );?>
					<a href="<?=$arItems["SECTION_PAGE_URL"]?>" class="thumb">
						<?if(strpos($img["src"], ".svg") !== false):?>
							<?=file_get_contents($_SERVER["DOCUMENT_ROOT"].$img["src"]);?>
						<?else:?>
							<img class="lazy img-responsive" data-src="<?=$img["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img["src"]);?>" alt="<?=($arItems["PICTURE"]["ALT"] ? $arItems["PICTURE"]["ALT"] : $arItems["NAME"])?>" title="<?=($arItems["PICTURE"]["TITLE"] ? $arItems["PICTURE"]["TITLE"] : $arItems["NAME"])?>" />
						<?endif;?>
					</a>
				<?else:?>
					<?if($arItems["PICTURE"]["SRC"]):?>
						<?$img = \CFile::ResizeImageGet($arItems["PICTURE"]["ID"], array( "width" => $width, "height" => $height ), BX_RESIZE_IMAGE_EXACT, true );?>
						<a href="<?=$arItems["SECTION_PAGE_URL"]?>" class="thumb"><img class="lazy img-responsive" data-src="<?=$img["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img["src"]);?>" alt="<?=($arItems["PICTURE"]["ALT"] ? $arItems["PICTURE"]["ALT"] : $arItems["NAME"])?>" title="<?=($arItems["PICTURE"]["TITLE"] ? $arItems["PICTURE"]["TITLE"] : $arItems["NAME"])?>" /></a>
					<?elseif($arItems["~PICTURE"]):?>
						<?$img = \CFile::ResizeImageGet($arItems["~PICTURE"], array( "width" => $width, "height" => $height ), BX_RESIZE_IMAGE_EXACT, true );?>
						<a href="<?=$arItems["SECTION_PAGE_URL"]?>" class="thumb"><img class="lazy img-responsive" data-src="<?=$img["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img["src"]);?>" alt="<?=($arItems["PICTURE"]["ALT"] ? $arItems["PICTURE"]["ALT"] : $arItems["NAME"])?>" title="<?=($arItems["PICTURE"]["TITLE"] ? $arItems["PICTURE"]["TITLE"] : $arItems["NAME"])?>" /></a>
					<?else:?>
						<a href="<?=$arItems["SECTION_PAGE_URL"]?>" class="thumb"><img class="lazy img-responsive" data-src="<?=SITE_TEMPLATE_PATH?>/images/svg/noimage_product.svg" src="<?=\Aspro\Functions\CAsproMax::showBlankImg(SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');?>" alt="<?=$arItems["NAME"]?>" title="<?=$arItems["NAME"]?>" /></a>
					<?endif;?>
				<?endif;?>
			<?$html = ob_get_contents();
			ob_end_clean();

			foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproShowSectionImg', true) as $arEvent) // event for manipulation item img
				ExecuteModuleEventEx($arEvent, array($arParams, $arItem, $bShowFW, &$html));

			echo $html;?>
		<?endif;?>
	<?}
}
?>