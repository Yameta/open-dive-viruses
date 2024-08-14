this.BX = this.BX || {};
this.BX.Sign = this.BX.Sign || {};
(function (exports,main_core,main_core_events,sign_v2_blankSelector,sign_v2_api) {
	'use strict';

	let _ = t => t,
	  _t;
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _api = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("api");
	var _lastBlankId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastBlankId");
	var _uids = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uids");
	var _register = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("register");
	var _change = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("change");
	var _getPages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPages");
	var _convertToBase = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("convertToBase64");
	var _waitForConnect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("waitForConnect");
	class DocumentSetup extends main_core_events.EventEmitter {
	  constructor(blankSelectorConfig) {
	    super();
	    Object.defineProperty(this, _waitForConnect, {
	      value: _waitForConnect2
	    });
	    Object.defineProperty(this, _convertToBase, {
	      value: _convertToBase2
	    });
	    Object.defineProperty(this, _getPages, {
	      value: _getPages2
	    });
	    Object.defineProperty(this, _change, {
	      value: _change2
	    });
	    Object.defineProperty(this, _register, {
	      value: _register2
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _api, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _lastBlankId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _uids, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Sign.V2.DocumentSetup');
	    this.blankSelector = new sign_v2_blankSelector.BlankSelector({
	      ...blankSelectorConfig,
	      events: {
	        toggleSelection: ({
	          data
	        }) => {
	          this.emit('toggleSelection', data);
	        },
	        addFile: () => this.emit('addFile'),
	        removeFile: () => this.emit('removeFile'),
	        clearFiles: () => this.emit('clearFiles')
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = main_core.Tag.render(_t || (_t = _`
			<div class="sign-document-setup">
				${0}
			</div>
		`), this.blankSelector.getLayout());
	    babelHelpers.classPrivateFieldLooseBase(this, _api)[_api] = new sign_v2_api.Api();
	    babelHelpers.classPrivateFieldLooseBase(this, _lastBlankId)[_lastBlankId] = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _uids)[_uids] = new Map();
	    this.setupData = null;
	  }
	  finishSetup(setupData, blankId) {
	    this.setupData = setupData;
	    babelHelpers.classPrivateFieldLooseBase(this, _lastBlankId)[_lastBlankId] = blankId;
	    this.blankSelector.clearFiles({
	      removeFromServer: false
	    });
	  }
	  async setup() {
	    const {
	      selectedBlankId
	    } = this.blankSelector;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _lastBlankId)[_lastBlankId] > 0 && babelHelpers.classPrivateFieldLooseBase(this, _lastBlankId)[_lastBlankId] === selectedBlankId) {
	      this.setupData = {
	        ...this.setupData,
	        isTemplate: true
	      };
	      return;
	    }
	    let blankId = 0;
	    try {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout], '--pending');
	      blankId = selectedBlankId || (await this.blankSelector.createBlank());
	      const isRegistered = babelHelpers.classPrivateFieldLooseBase(this, _uids)[_uids].has(blankId);
	      const uid = isRegistered ? await babelHelpers.classPrivateFieldLooseBase(this, _change)[_change](babelHelpers.classPrivateFieldLooseBase(this, _uids)[_uids].get(blankId), blankId) : await babelHelpers.classPrivateFieldLooseBase(this, _register)[_register](blankId);
	      babelHelpers.classPrivateFieldLooseBase(this, _uids)[_uids].set(blankId, uid);
	      await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].upload(uid);
	      const [loadedData, blocks] = await Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].loadDocument(uid), this.loadBlocks(uid)]);
	      const isTemplate = Boolean(selectedBlankId);
	      this.finishSetup({
	        ...loadedData,
	        blocks,
	        isTemplate
	      }, blankId);
	    } catch {
	      this.finishSetup(null, 0);
	      this.blankSelector.deleteBlank(blankId);
	    }
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout], '--pending');
	  }
	  loadBlocks(uid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].loadBlocksByDocument(uid);
	  }
	  getLayout() {
	    babelHelpers.classPrivateFieldLooseBase(this, _waitForConnect)[_waitForConnect]();
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout];
	  }
	  async waitForPagesList(cb) {
	    var _BX$PULL;
	    const requestTime = 10 * 1000;
	    this.blankSelector.resetSelectedBlank();
	    const uid = this.setupData.uid;
	    let promiseResolve = null;
	    let interval = null;
	    let isPagesReady = false;
	    const promise = new Promise(resolve => {
	      promiseResolve = resolve;
	      interval = setInterval(async () => {
	        if (isPagesReady) {
	          return;
	        }
	        const urls = await babelHelpers.classPrivateFieldLooseBase(this, _getPages)[_getPages](uid);
	        if (urls.length > 0) {
	          resolve(urls);
	        }
	      }, requestTime);
	    });
	    (_BX$PULL = BX.PULL) == null ? void 0 : _BX$PULL.subscribe({
	      moduleId: 'sign',
	      command: 'blankIsReady',
	      callback: async result => {
	        if (isPagesReady) {
	          return;
	        }
	        const urls = await babelHelpers.classPrivateFieldLooseBase(this, _convertToBase)[_convertToBase](result == null ? void 0 : result.pages);
	        if (urls.length > 0) {
	          promiseResolve(urls);
	        }
	      }
	    });
	    const urls = await promise;
	    clearInterval(interval);
	    isPagesReady = true;
	    cb(urls);
	    this.blankSelector.selectBlank(babelHelpers.classPrivateFieldLooseBase(this, _lastBlankId)[_lastBlankId]);
	  }
	}
	async function _register2(blankId) {
	  var _data$uid;
	  const data = await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].register(blankId);
	  return (_data$uid = data == null ? void 0 : data.uid) != null ? _data$uid : '';
	}
	async function _change2(uid, blankId) {
	  var _data$uid2;
	  const data = await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].changeDocument(uid, blankId);
	  return (_data$uid2 = data == null ? void 0 : data.uid) != null ? _data$uid2 : '';
	}
	async function _getPages2(uid) {
	  const data = await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].getPages(uid);
	  return babelHelpers.classPrivateFieldLooseBase(this, _convertToBase)[_convertToBase](data == null ? void 0 : data.pages);
	}
	async function _convertToBase2(pages) {
	  if (!pages) {
	    return [];
	  }
	  const promises = pages.map(async page => {
	    const data = await fetch(page.url);
	    const blob = await data.blob();
	    const fileReader = new FileReader();
	    await new Promise(resolve => {
	      main_core.Event.bindOnce(fileReader, 'loadend', resolve);
	      fileReader.readAsDataURL(blob);
	    });
	    return fileReader.result;
	  });
	  return Promise.all(promises);
	}
	function _waitForConnect2() {
	  const observer = new MutationObserver(() => {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].isConnected) {
	      return;
	    }
	    const {
	      blanks,
	      selectedBlankId
	    } = this.blankSelector;
	    const blank = blanks.get(selectedBlankId);
	    blank == null ? void 0 : blank.focus();
	    observer.disconnect();
	  });
	  observer.observe(document.body, {
	    childList: true,
	    subtree: true
	  });
	}

	exports.DocumentSetup = DocumentSetup;

}((this.BX.Sign.V2 = this.BX.Sign.V2 || {}),BX,BX.Event,BX.Sign.V2,BX.Sign.V2));
//# sourceMappingURL=document-setup.bundle.js.map
