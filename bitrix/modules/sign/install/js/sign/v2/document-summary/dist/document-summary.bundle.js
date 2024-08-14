this.BX = this.BX || {};
this.BX.Sign = this.BX.Sign || {};
(function (exports,main_core,main_core_events,main_popup,sign_v2_api,ui_buttons,sign_v2_langSelector) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10;
	const buttonClassList = ['ui-btn', 'ui-btn-sm', 'ui-btn-light-border', 'ui-btn-round'];
	const menuPrefix = 'sign-member-communication';
	var _editDocumentBtn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("editDocumentBtn");
	var _api = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("api");
	var _blocks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("blocks");
	var _filledBlocks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filledBlocks");
	var _menus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menus");
	var _summaryContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("summaryContainer");
	var _langContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("langContainer");
	var _config = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("config");
	var _langSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("langSelector");
	var _createDocumentDetails = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createDocumentDetails");
	var _createTitleEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createTitleEditor");
	var _toggleTitleEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleTitleEditor");
	var _focusInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("focusInput");
	var _modifyDocumentTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("modifyDocumentTitle");
	var _attachMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("attachMenu");
	var _formatPhoneNumberForUi = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formatPhoneNumberForUi");
	var _getNormalizedPhoneNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNormalizedPhoneNumber");
	var _updatePhoneAttr = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updatePhoneAttr");
	var _showMemberInfo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showMemberInfo");
	var _createParties = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createParties");
	var _updateCommunications = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateCommunications");
	class DocumentSummary extends main_core_events.EventEmitter {
	  constructor(config) {
	    super();
	    Object.defineProperty(this, _updateCommunications, {
	      value: _updateCommunications2
	    });
	    Object.defineProperty(this, _createParties, {
	      value: _createParties2
	    });
	    Object.defineProperty(this, _showMemberInfo, {
	      value: _showMemberInfo2
	    });
	    Object.defineProperty(this, _updatePhoneAttr, {
	      value: _updatePhoneAttr2
	    });
	    Object.defineProperty(this, _getNormalizedPhoneNumber, {
	      value: _getNormalizedPhoneNumber2
	    });
	    Object.defineProperty(this, _formatPhoneNumberForUi, {
	      value: _formatPhoneNumberForUi2
	    });
	    Object.defineProperty(this, _attachMenu, {
	      value: _attachMenu2
	    });
	    Object.defineProperty(this, _modifyDocumentTitle, {
	      value: _modifyDocumentTitle2
	    });
	    Object.defineProperty(this, _focusInput, {
	      value: _focusInput2
	    });
	    Object.defineProperty(this, _toggleTitleEditor, {
	      value: _toggleTitleEditor2
	    });
	    Object.defineProperty(this, _createTitleEditor, {
	      value: _createTitleEditor2
	    });
	    Object.defineProperty(this, _createDocumentDetails, {
	      value: _createDocumentDetails2
	    });
	    Object.defineProperty(this, _editDocumentBtn, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _api, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _blocks, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _filledBlocks, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _menus, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _summaryContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _langContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _config, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _langSelector, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config] = config;
	    this.setEventNamespace('BX.Sign.V2.DocumentSummary');
	    babelHelpers.classPrivateFieldLooseBase(this, _editDocumentBtn)[_editDocumentBtn] = main_core.Tag.render(_t || (_t = _`
			<span
				class="${0}"
				onclick="${0}"
			>
				${0}
			</span>
		`), buttonClassList.join(' '), () => this.emit('showEditor'), main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_EDIT'));
	    babelHelpers.classPrivateFieldLooseBase(this, _api)[_api] = new sign_v2_api.Api();
	    babelHelpers.classPrivateFieldLooseBase(this, _blocks)[_blocks] = main_core.Tag.render(_t2 || (_t2 = _`
			<p class="sign-document-info__details_structure"></p>
		`));
	    babelHelpers.classPrivateFieldLooseBase(this, _filledBlocks)[_filledBlocks] = main_core.Tag.render(_t3 || (_t3 = _`
			<p class="sign-document-info__details_structure"></p>
		`));
	    babelHelpers.classPrivateFieldLooseBase(this, _langSelector)[_langSelector] = new sign_v2_langSelector.LangSelector(babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].region, babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].languages);
	    babelHelpers.classPrivateFieldLooseBase(this, _langContainer)[_langContainer] = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="sign-blank-selector__lang-container">
				${0}
				<span data-hint="${0}"></span>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _langSelector)[_langSelector].getLayout(), main_core.Loc.getMessage('SIGN_DOCUMENT_LANGUAGE_BUTTON_INFO'));
	    BX.UI.Hint.init(babelHelpers.classPrivateFieldLooseBase(this, _langContainer)[_langContainer]);
	    babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus] = [];
	    this.documentData = {};
	    this.entityData = {};
	    this.communications = {};
	  }
	  getLayout() {
	    babelHelpers.classPrivateFieldLooseBase(this, _langSelector)[_langSelector].setDocumentUid(this.documentData.uid);
	    babelHelpers.classPrivateFieldLooseBase(this, _summaryContainer)[_summaryContainer] = main_core.Tag.render(_t5 || (_t5 = _`
			<div class="sign-document-info">
				<div class="sign-document-indo__summary-title-wrapper">
					<p class="sign-document-info__title">
						${0}
					</p>
					${0}
				</div>
				<div class="sign-document-info__summary">
					${0}
					${0}
				</div>
				${0}
			</div>
		`), main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_SEND'), babelHelpers.classPrivateFieldLooseBase(this, _langContainer)[_langContainer], babelHelpers.classPrivateFieldLooseBase(this, _createDocumentDetails)[_createDocumentDetails](), babelHelpers.classPrivateFieldLooseBase(this, _editDocumentBtn)[_editDocumentBtn], babelHelpers.classPrivateFieldLooseBase(this, _createParties)[_createParties]());
	    return babelHelpers.classPrivateFieldLooseBase(this, _summaryContainer)[_summaryContainer];
	  }
	  highlightCommunicationWithError(phoneNumber) {
	    const aIdMeans = babelHelpers.classPrivateFieldLooseBase(this, _summaryContainer)[_summaryContainer].querySelectorAll(`.sign-document-info__party_id-means[data-phone-normalized="${phoneNumber}"]`);
	    aIdMeans.forEach(elem => {
	      const wrapper = elem.closest('.sign-document-info__party');
	      main_core.Dom.addClass(wrapper, '--validation-error');
	    });
	  }
	  resetCommunicationErrors() {
	    const elems = babelHelpers.classPrivateFieldLooseBase(this, _summaryContainer)[_summaryContainer].querySelectorAll('.sign-document-info__party');
	    elems.forEach(elem => {
	      main_core.Dom.removeClass(elem, '--validation-error');
	    });
	  }
	  renderDocumentDetails() {
	    var _this$documentData$bl;
	    const blocks = (_this$documentData$bl = this.documentData.blocks) != null ? _this$documentData$bl : [];
	    const addedBlocks = blocks.filter(block => block.party === 1).length;
	    const addedFilledBlocks = blocks.filter(block => block.party >= 2).length;
	    const addedBlocksTitle = main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_ADDED_BLOCKS', {
	      '#CNT#': addedBlocks
	    });
	    const filledBlocksTitle = main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_ADDED_FILLED_FIELDS', {
	      '#CNT#': addedFilledBlocks
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _blocks)[_blocks].textContent = addedBlocksTitle;
	    babelHelpers.classPrivateFieldLooseBase(this, _filledBlocks)[_filledBlocks].textContent = filledBlocksTitle;
	  }
	}
	function _createDocumentDetails2() {
	  var _this$documentData$ti;
	  this.renderDocumentDetails();
	  return main_core.Tag.render(_t6 || (_t6 = _`
			<div class="sign-document-info__details">
				<div class="sign-document-info__details_title">
					<span>${0}</span>
					<span
						class="sign-document-info__details_edit-title-btn"
						onclick="${0}"
					>
					</span>
				</div>
				${0}
				${0}
			</div>
		`), main_core.Text.encode((_this$documentData$ti = this.documentData.title) != null ? _this$documentData$ti : ''), ({
	    target: button
	  }) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _toggleTitleEditor)[_toggleTitleEditor](button, true);
	  }, babelHelpers.classPrivateFieldLooseBase(this, _blocks)[_blocks], babelHelpers.classPrivateFieldLooseBase(this, _filledBlocks)[_filledBlocks]);
	}
	function _createTitleEditor2() {
	  var _this$documentData$ti2;
	  const okButtonClassName = [...buttonClassList.slice(0, 2), 'ui-btn-primary', 'sign-document-info__title-editor_ok-btn'].join(' ');
	  const discardButtonClassName = [...buttonClassList.slice(0, 3), 'sign-document-info__title-editor_discard-btn'].join(' ');
	  const input = main_core.Tag.render(_t7 || (_t7 = _`<input type="text" class="ui-ctl-element" />`));
	  input.value = (_this$documentData$ti2 = this.documentData.title) != null ? _this$documentData$ti2 : '';
	  babelHelpers.classPrivateFieldLooseBase(this, _focusInput)[_focusInput](input);
	  return main_core.Tag.render(_t8 || (_t8 = _`
			<div class="sign-document-info__title-editor">
				<div class="sign-document-info__title-editor_controls">
					<span class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						${0}
					</span>
					<span
						class="${0}"
						onclick="${0}"
					>
					</span>
					<span
						class="${0}"
						onclick="${0}"
					>
					</span>
				</div>
				<p class="sign-document-info__title-editor_help">
					${0}
				</p>
			</div>
		`), input, okButtonClassName, async ({
	    target
	  }) => {
	    main_core.Dom.addClass(target, 'ui-btn-wait');
	    await babelHelpers.classPrivateFieldLooseBase(this, _modifyDocumentTitle)[_modifyDocumentTitle](input.value);
	    main_core.Dom.removeClass(target, 'ui-btn-wait');
	    babelHelpers.classPrivateFieldLooseBase(this, _toggleTitleEditor)[_toggleTitleEditor](target, false);
	  }, discardButtonClassName, ({
	    target
	  }) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _toggleTitleEditor)[_toggleTitleEditor](target, false);
	  }, main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_TITLE_EDITOR_HELP'));
	}
	function _toggleTitleEditor2(button, shouldShow) {
	  const summaryNode = button.closest('.sign-document-info__summary');
	  if (shouldShow) {
	    main_core.Dom.clean(summaryNode);
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _createTitleEditor)[_createTitleEditor](), summaryNode);
	    return;
	  }
	  main_core.Dom.replace(summaryNode.firstElementChild, babelHelpers.classPrivateFieldLooseBase(this, _createDocumentDetails)[_createDocumentDetails]());
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _editDocumentBtn)[_editDocumentBtn], summaryNode);
	}
	function _focusInput2(input) {
	  const observer = new MutationObserver(() => {
	    if (input.isConnected) {
	      input.focus();
	      observer.disconnect();
	    }
	  });
	  observer.observe(document.body, {
	    childList: true,
	    subtree: true
	  });
	}
	async function _modifyDocumentTitle2(newValue) {
	  const {
	    title = '',
	    uid = ''
	  } = this.documentData;
	  if (title === newValue) {
	    return;
	  }
	  try {
	    await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].modifyTitle(uid, newValue);
	    this.documentData = {
	      ...this.documentData,
	      title: newValue
	    };
	    this.emit('changeTitle', {
	      newValue
	    });
	  } catch (ex) {
	    console.error(ex);
	  }
	}
	function _attachMenu2(idMeans, entityData) {
	  let menuItems = [];
	  const menuId = `${menuPrefix}-${entityData.entityTypeId}-${entityData.id}`;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId]) {
	    let items = babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId].getMenuItems();
	    const swap = (array, from, to) => {
	      const tmp = array[to];
	      // eslint-disable-next-line no-param-reassign
	      array[to] = array[from];
	      // eslint-disable-next-line no-param-reassign
	      array[from] = tmp;
	    };
	    while (items.length > 1) {
	      if (items[0].id === 'show-member') {
	        swap(items, 0, 1);
	        continue;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId].removeMenuItem(items[0].id);
	      items = babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId].getMenuItems();
	    }
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].loadCommunications(entityData.uid).then(async multiFields => {
	    var _selectedCommunicatio, _selectedCommunicatio2, _selectedCommunicatio3, _selectedCommunicatio4, _selectedCommunicatio5;
	    let selectedCommunication = {};
	    const restrictions = await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].loadRestrictions();
	    const mapper = communication => {
	      let text = communication.VALUE;
	      if ((communication == null ? void 0 : communication.TYPE) === 'PHONE' && BX.PhoneNumberParser) {
	        BX.PhoneNumberParser.getInstance().parse(communication.VALUE).then(parsedNumber => {
	          text = parsedNumber.format(BX.PhoneNumber.Format.INTERNATIONAL);
	        });
	      }
	      if ((communication == null ? void 0 : communication.TYPE) === 'PHONE' && restrictions.smsAllowed || (communication == null ? void 0 : communication.TYPE) === 'EMAIL' && Object.keys(selectedCommunication).length === 0) {
	        selectedCommunication = communication;
	      }
	      return {
	        text,
	        onclick: ({
	          target
	        }) => {
	          babelHelpers.classPrivateFieldLooseBase(this, _updateCommunications)[_updateCommunications](entityData, communication);
	          // eslint-disable-next-line no-param-reassign
	          idMeans.firstElementChild.textContent = text;
	          babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId].close();
	          babelHelpers.classPrivateFieldLooseBase(this, _updatePhoneAttr)[_updatePhoneAttr](idMeans, (communication == null ? void 0 : communication.TYPE) === 'PHONE' ? communication.VALUE : null);
	        }
	      };
	    };
	    menuItems = [
	    // eslint-disable-next-line no-unsafe-optional-chaining
	    ...(multiFields != null && multiFields.EMAIL ? multiFields == null ? void 0 : multiFields.EMAIL.map(element => mapper(element)) : []),
	    // eslint-disable-next-line no-unsafe-optional-chaining
	    ...(multiFields != null && multiFields.PHONE ? await Promise.all(multiFields == null ? void 0 : multiFields.PHONE.map(async element => {
	      const item = mapper(element);
	      item.text = await babelHelpers.classPrivateFieldLooseBase(this, _formatPhoneNumberForUi)[_formatPhoneNumberForUi](item.text);
	      return item;
	    })) : [])];
	    menuItems.map(item => babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId].addMenuItem(item, null));
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCommunications)[_updateCommunications](entityData, selectedCommunication);
	    idMeans.firstElementChild.textContent = ((_selectedCommunicatio = selectedCommunication) == null ? void 0 : _selectedCommunicatio.TYPE) === 'PHONE' ? await babelHelpers.classPrivateFieldLooseBase(this, _formatPhoneNumberForUi)[_formatPhoneNumberForUi]((_selectedCommunicatio2 = selectedCommunication) == null ? void 0 : _selectedCommunicatio2.VALUE) : (_selectedCommunicatio3 = selectedCommunication) == null ? void 0 : _selectedCommunicatio3.VALUE;
	    babelHelpers.classPrivateFieldLooseBase(this, _updatePhoneAttr)[_updatePhoneAttr](idMeans, ((_selectedCommunicatio4 = selectedCommunication) == null ? void 0 : _selectedCommunicatio4.TYPE) === 'PHONE' ? await babelHelpers.classPrivateFieldLooseBase(this, _getNormalizedPhoneNumber)[_getNormalizedPhoneNumber]((_selectedCommunicatio5 = selectedCommunication) == null ? void 0 : _selectedCommunicatio5.VALUE) : null);
	  }).catch(() => {});
	  menuItems.push({
	    id: 'show-member',
	    text: main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_OPEN_VIEW'),
	    onclick: () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _showMemberInfo)[_showMemberInfo](idMeans, entityData);
	      babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId].close();
	    }
	  });
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId] = main_popup.MenuManager.create({
	      id: menuId,
	      items: menuItems
	    });
	    const popup = babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId].getPopupWindow();
	    popup.setBindElement(idMeans);
	  }

	  // eslint-disable-next-line no-param-reassign
	  idMeans.firstElementChild.textContent = menuItems[0].text;
	  return babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus][menuId];
	}
	async function _formatPhoneNumberForUi2(phone) {
	  let phoneFormatted = phone;
	  if (BX.PhoneNumberParser) {
	    phoneFormatted = await BX.PhoneNumberParser.getInstance().parse(phone).then(parsedNumber => {
	      return parsedNumber.format(BX.PhoneNumber.Format.INTERNATIONAL);
	    });
	  }
	  return phoneFormatted;
	}
	async function _getNormalizedPhoneNumber2(phone) {
	  if (BX.PhoneNumberParser) {
	    const parsedNumber = await BX.PhoneNumberParser.getInstance().parse(phone);
	    return parsedNumber.isValid() ? parsedNumber.format(BX.PhoneNumber.Format.E164) : parsedNumber.rawNumber;
	  }
	  return phone;
	}
	function _updatePhoneAttr2(elem, phone = null) {
	  if (main_core.Type.isStringFilled(phone)) {
	    main_core.Dom.attr(elem, 'data-phone-normalized', phone);
	  } else {
	    elem.removeAttribute('data-phone-normalized');
	  }
	}
	function _showMemberInfo2(idMeans, entityData) {
	  const {
	    Instance: slider
	  } = main_core.Reflection.getClass('BX.SidePanel');
	  slider.open(entityData.url, {
	    cacheable: false,
	    allowChangeHistory: false,
	    events: {
	      onClose: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _attachMenu)[_attachMenu](idMeans, entityData);
	      }
	    }
	  });
	}
	function _createParties2() {
	  const parties = [{
	    partyTitle: main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_FIRST_PARTY'),
	    entityData: this.entityData.company
	  }, {
	    partyTitle: main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_PARTNER'),
	    entityData: this.entityData.contact
	  }];
	  Object.keys(main_popup.MenuManager.Data).forEach(menuId => {
	    if (menuId.includes(menuPrefix)) {
	      main_popup.MenuManager.destroy(menuId);
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _menus)[_menus] = [];
	  return parties.map(party => {
	    const {
	      partyTitle,
	      entityData
	    } = party;
	    const {
	      title
	    } = entityData;
	    const idMeans = main_core.Tag.render(_t9 || (_t9 = _`
				<span
					class="sign-document-info__party_id-means"
					onclick="${0}"
				>
					<span></span>
				</span>
			`), () => menu.show());
	    const menu = babelHelpers.classPrivateFieldLooseBase(this, _attachMenu)[_attachMenu](idMeans, entityData);
	    return main_core.Tag.render(_t10 || (_t10 = _`
				<div class="sign-document-info__party">
					<div class="sign-document-info__party_summary">
						<p class="sign-document-info__party_title">${0}</p>
						<span class="sign-document-info__party_member-name">
							${0}
						</span>
						<span class="sign-document-info__party_status">
							${0}
						</span>
					</div>
					<div class="sign-document-info__party_id">
						<p class="sign-document-info__party_title">
							${0}
						</p>
						${0}
					</div>
				</div>
			`), partyTitle, main_core.Text.encode(title), main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_NOT_SIGNED'), main_core.Loc.getMessage('SIGN_DOCUMENT_INFO_ID'), idMeans);
	  });
	}
	function _updateCommunications2(entityData, communication) {
	  // eslint-disable-next-line @bitrix24/bitrix24-rules/no-typeof
	  if (typeof communication === 'undefined') {
	    return;
	  }
	  const {
	    TYPE: type,
	    VALUE: value
	  } = communication;
	  this.communications = {
	    ...this.communications,
	    [entityData.type]: {
	      type,
	      value
	    }
	  };
	  this.resetCommunicationErrors();
	}

	exports.DocumentSummary = DocumentSummary;

}((this.BX.Sign.V2 = this.BX.Sign.V2 || {}),BX,BX.Event,BX.Main,BX.Sign.V2,BX.UI,BX.Sign.V2));
//# sourceMappingURL=document-summary.bundle.js.map
