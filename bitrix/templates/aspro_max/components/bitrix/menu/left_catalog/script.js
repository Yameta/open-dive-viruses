$(document).ready(function(){
	InitLeftMenuAim = function() {
		let $block = $('.menu.dropdown:not(.aim-init)');
		let $isBlockHover = $block.find('.full.has-child.v_hover').length
		if ($isBlockHover) {
			$block.addClass("aim-init");
			loadScripts(arAsproOptions["SITE_TEMPLATE_PATH"] + "/vendor/js/jquery.menu-aim.js", function() {
				$block.menuAim({
					tolerance: 75,
					rowSelector: "> .full.has-child.v_hover",
					activate: function (a) {
						$(a).find(".dropdown-block").show();
	
						let $this = $(a),
							menu = $this.find('> .dropdown-block'),
							winHeight = $(window).height();
	
						// menu.css('max-height', 'none');
						menu.find('.mCustomScrollBox').css('max-height', 'none');
						
						if ($this.hasClass('m_line')) {
							let mt = parseInt($this.height());
							let pos = BX.pos(menu[0], true);							
							if (pos.height) {
								let cmt = parseInt(menu[0].style.marginTop);
								cmt = isNaN(cmt) ? 0 : cmt;
								
								let bottom = pos.bottom - cmt - mt;
								if (bottom >= winHeight) {
									mt = mt + bottom - winHeight;
								}

								let top = pos.top - cmt - mt;
								if (top < 0) {
									mt = mt + top;
								}
							}

							menu.css('margin-top',  '-' + mt + 'px');
						}
				
						$('body').addClass('menu-hovered');
				
						$this.one('mouseleave', function () {
							$('body').removeClass('menu-hovered');
						});
					},
					deactivate: function (a) {
						$(a).find(".dropdown-block").hide();
					},
					exitMenu: function (a) {
						$(a).find(".dropdown-block").hide();
						return true;
					},
				});
			});
		}
	}

	BX.loadScript(
		[
		  arAsproOptions["SITE_TEMPLATE_PATH"] + "/css/jquery.mCustomScrollbar.min.css",
		  arAsproOptions["SITE_TEMPLATE_PATH"] + "/js/jquery.mousewheel.min.js",
		  arAsproOptions["SITE_TEMPLATE_PATH"] + "/js/jquery.mCustomScrollbar.js",
		],
		function () {
			$('.sidebar_menu .sidebar_menu_inner .menu-wrapper').mCustomScrollbar({
				mouseWheel: {
					scrollAmount: 150,
					preventDefault: true
				}
			});

			$('.sidebar_menu .sidebar_menu_inner .menu-wrapper .menu_top_block .menu > li.v_hover > .dropdown-block').mCustomScrollbar({
				mouseWheel: {
					scrollAmount: 150,
					preventDefault: true
				}
			});
		}
	);

	InitLeftMenuAim();
});
