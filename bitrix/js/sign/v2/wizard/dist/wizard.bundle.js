this.BX = this.BX || {};
this.BX.Sign = this.BX.Sign || {};
(function (exports,main_core,sign_v2_documentSetup,sign_v2_requisites,sign_v2_documentSummary,sign_v2_preview,sign_v2_api) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7;
	var _documentSetup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentSetup");
	var _uid = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uid");
	var _containerId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("containerId");
	var _requisites = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requisites");
	var _documentSummary = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentSummary");
	var _preview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preview");
	var _editor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("editor");
	var _api = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("api");
	var _stepNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stepNode");
	var _stepsMetadata = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stepsMetadata");
	var _stepIndex = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stepIndex");
	var _order = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("order");
	var _stages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stages");
	var _navigationButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("navigationButtons");
	var _loadData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadData");
	var _subscribeOnStagesEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOnStagesEvents");
	var _subscribeOnEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOnEvents");
	var _createNavigationButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createNavigationButtons");
	var _createStages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createStages");
	var _renderNavigationButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNavigationButtons");
	var _renderActiveStage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderActiveStage");
	var _renderStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderStep");
	var _onPrevStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPrevStep");
	var _onNextStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNextStep");
	var _moveOnStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moveOnStep");
	var _sendForSign = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendForSign");
	var _getLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLayout");
	var _complete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("complete");
	class Wizard {
	  constructor(options) {
	    Object.defineProperty(this, _complete, {
	      value: _complete2
	    });
	    Object.defineProperty(this, _getLayout, {
	      value: _getLayout2
	    });
	    Object.defineProperty(this, _sendForSign, {
	      value: _sendForSign2
	    });
	    Object.defineProperty(this, _moveOnStep, {
	      value: _moveOnStep2
	    });
	    Object.defineProperty(this, _onNextStep, {
	      value: _onNextStep2
	    });
	    Object.defineProperty(this, _onPrevStep, {
	      value: _onPrevStep2
	    });
	    Object.defineProperty(this, _renderStep, {
	      value: _renderStep2
	    });
	    Object.defineProperty(this, _renderActiveStage, {
	      value: _renderActiveStage2
	    });
	    Object.defineProperty(this, _renderNavigationButtons, {
	      value: _renderNavigationButtons2
	    });
	    Object.defineProperty(this, _createStages, {
	      value: _createStages2
	    });
	    Object.defineProperty(this, _createNavigationButtons, {
	      value: _createNavigationButtons2
	    });
	    Object.defineProperty(this, _subscribeOnEvents, {
	      value: _subscribeOnEvents2
	    });
	    Object.defineProperty(this, _subscribeOnStagesEvents, {
	      value: _subscribeOnStagesEvents2
	    });
	    Object.defineProperty(this, _loadData, {
	      value: _loadData2
	    });
	    Object.defineProperty(this, _documentSetup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _uid, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _containerId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _requisites, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentSummary, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _preview, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _editor, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _api, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stepNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stepsMetadata, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stepIndex, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _order, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stages, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _navigationButtons, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      uid = null,
	      containerId,
	      config = {}
	    } = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _uid)[_uid] = uid;
	    babelHelpers.classPrivateFieldLooseBase(this, _containerId)[_containerId] = containerId;
	    babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup] = new sign_v2_documentSetup.DocumentSetup(config.blankSelectorConfig);
	    babelHelpers.classPrivateFieldLooseBase(this, _requisites)[_requisites] = new sign_v2_requisites.Requisites();
	    babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary] = new sign_v2_documentSummary.DocumentSummary(config.documentSummaryConfig);
	    babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview] = new sign_v2_preview.Preview();
	    babelHelpers.classPrivateFieldLooseBase(this, _api)[_api] = new sign_v2_api.Api();
	    babelHelpers.classPrivateFieldLooseBase(this, _stepsMetadata)[_stepsMetadata] = {
	      setup: {
	        step: babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup],
	        title: main_core.Loc.getMessage('SIGN_WIZARD_LOAD_DOCUMENT'),
	        beforeCompletion: async () => {
	          const [, nextButton] = babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons];
	          const renderPages = (urls = [], blocks = []) => {
	            babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].urls = urls;
	            babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview].urls = urls;
	            babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview].setBlocks(blocks);
	          };
	          renderPages();
	          main_core.Dom.addClass(nextButton, 'ui-btn-wait');
	          await babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setup();
	          main_core.Dom.removeClass(nextButton, 'ui-btn-wait');
	          const setupData = babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData;
	          if (!setupData) {
	            return false;
	          }
	          const {
	            uid,
	            entityId,
	            blocks,
	            initiator
	          } = setupData;
	          babelHelpers.classPrivateFieldLooseBase(this, _requisites)[_requisites].documentData = {
	            uid,
	            entityId,
	            initiator
	          };
	          babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview].ready = false;
	          babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].waitForPagesList(urls => {
	            renderPages(urls, blocks);
	            babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview].ready = true;
	          });
	          return true;
	        }
	      },
	      requisites: {
	        step: babelHelpers.classPrivateFieldLooseBase(this, _requisites)[_requisites],
	        title: main_core.Loc.getMessage('SIGN_WIZARD_PREPARING_DOCUMENT'),
	        beforeCompletion: async () => {
	          const [, nextButton] = babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons];
	          main_core.Dom.addClass(nextButton, 'ui-btn-wait');
	          const {
	            uid,
	            isTemplate,
	            title,
	            initiator
	          } = babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData;
	          const valid = babelHelpers.classPrivateFieldLooseBase(this, _requisites)[_requisites].checkInitiator(initiator);
	          if (!valid) {
	            main_core.Dom.removeClass(nextButton, 'ui-btn-wait');
	            return false;
	          }
	          const entityData = await babelHelpers.classPrivateFieldLooseBase(this, _requisites)[_requisites].processMembers();
	          if (!entityData) {
	            main_core.Dom.removeClass(nextButton, 'ui-btn-wait');
	            return false;
	          }
	          const blocks = await babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].loadBlocks(uid);
	          babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].documentData = {
	            isTemplate,
	            uid,
	            blocks
	          };
	          babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].entityData = entityData;
	          babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary].documentData = {
	            uid,
	            title,
	            blocks
	          };
	          babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary].entityData = entityData;
	          await babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].renderDocument();
	          main_core.Dom.removeClass(nextButton, 'ui-btn-wait');
	          await babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].show();
	          return true;
	        }
	      },
	      send: {
	        step: babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary],
	        title: main_core.Loc.getMessage('SIGN_WIZARD_SEND_DOCUMENT')
	      }
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _order)[_order] = ['setup', 'requisites', 'send'];
	    babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages] = babelHelpers.classPrivateFieldLooseBase(this, _createStages)[_createStages]();
	    babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons] = babelHelpers.classPrivateFieldLooseBase(this, _createNavigationButtons)[_createNavigationButtons]();
	    babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode] = main_core.Tag.render(_t || (_t = _`<div class="sign-wizard__step"></div>`));
	    // eslint-disable-next-line no-shadow
	    const Editor = main_core.Reflection.getClass('top.BX.Sign.V2.Editor');
	    babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor] = new Editor();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeOnEvents)[_subscribeOnEvents]();
	  }
	  async show() {
	    try {
	      await babelHelpers.classPrivateFieldLooseBase(this, _loadData)[_loadData]();
	      babelHelpers.classPrivateFieldLooseBase(this, _moveOnStep)[_moveOnStep](babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]);
	      const container = document.getElementById(babelHelpers.classPrivateFieldLooseBase(this, _containerId)[_containerId]);
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getLayout)[_getLayout](), container);
	    } catch (ex) {
	      // eslint-disable-next-line no-console
	      console.log(ex);
	    }
	  }
	}
	async function _loadData2() {
	  const loadedData = babelHelpers.classPrivateFieldLooseBase(this, _uid)[_uid] ? await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].loadDocument(babelHelpers.classPrivateFieldLooseBase(this, _uid)[_uid]) : null;
	  if (!loadedData) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].finishSetup(loadedData, loadedData.blankId);
	  babelHelpers.classPrivateFieldLooseBase(this, _requisites)[_requisites].documentData = loadedData;
	  babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].documentData = loadedData;
	  babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary].documentData = loadedData;
	  if (loadedData.status === 'uploading' || loadedData.status === 'uploaded' || loadedData.status === 'ready') {
	    babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].waitForPagesList(urls => {
	      babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].urls = urls;
	      babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview].urls = urls;
	      babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview].setBlocks(loadedData.blocks);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] = 1;
	  }
	}
	function _subscribeOnStagesEvents2() {
	  const [, nextButton] = babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons];
	  const events = [{
	    type: 'toggleSelection',
	    stage: 'setup',
	    method: ({
	      data
	    }) => {
	      const {
	        relatedTarget,
	        selected,
	        blankId
	      } = data;
	      if (selected) {
	        main_core.Dom.removeClass(nextButton, 'ui-btn-disabled');
	        return;
	      }
	      if (relatedTarget !== nextButton) {
	        main_core.Dom.addClass(nextButton, 'ui-btn-disabled');
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].blankSelector.selectBlank(blankId);
	    }
	  }, {
	    type: 'addFile',
	    stage: 'setup',
	    method: () => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].blankSelector.isFilesReadyForUpload()) {
	        main_core.Dom.removeClass(nextButton, 'ui-btn-disabled');
	      } else {
	        main_core.Dom.addClass(nextButton, 'ui-btn-disabled');
	      }
	    }
	  }, {
	    type: 'removeFile',
	    stage: 'setup',
	    method: () => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].blankSelector.isFilesReadyForUpload()) {
	        main_core.Dom.removeClass(nextButton, 'ui-btn-disabled');
	      } else {
	        main_core.Dom.addClass(nextButton, 'ui-btn-disabled');
	      }
	    }
	  }, {
	    type: 'clearFiles',
	    stage: 'setup',
	    method: () => main_core.Dom.addClass(nextButton, 'ui-btn-disabled')
	  }, {
	    type: 'changeInitiator',
	    stage: 'requisites',
	    method: ({
	      data
	    }) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData = {
	        ...babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData,
	        initiator: data.initiator
	      };
	    }
	  }, {
	    type: 'showEditor',
	    stage: 'send',
	    method: () => babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].show()
	  }, {
	    type: 'changeTitle',
	    stage: 'send',
	    method: ({
	      data
	    }) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData = {
	        ...babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData,
	        title: data.newValue
	      };
	    }
	  }];
	  events.forEach(({
	    type,
	    method,
	    stage
	  }) => {
	    const {
	      step
	    } = babelHelpers.classPrivateFieldLooseBase(this, _stepsMetadata)[_stepsMetadata][stage];
	    step.subscribe(type, method);
	  });
	}
	function _subscribeOnEvents2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _subscribeOnStagesEvents)[_subscribeOnStagesEvents]();
	  babelHelpers.classPrivateFieldLooseBase(this, _editor)[_editor].subscribe('save', ({
	    data
	  }) => {
	    const blocks = data.blocks;
	    babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview].setBlocks(blocks);
	    babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData,
	      blocks
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary].documentData = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary].documentData,
	      blocks
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary].renderDocumentDetails();
	  });
	}
	function _createNavigationButtons2() {
	  const classList = ['ui-btn-primary', 'ui-btn', 'ui-btn-lg', 'ui-btn-round', 'sign-wizard__footer_button'];
	  const buttons = [{
	    title: main_core.Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_BACK'),
	    method: () => babelHelpers.classPrivateFieldLooseBase(this, _onPrevStep)[_onPrevStep](),
	    buttonClassList: [...classList.slice(1), 'ui-btn-light-border']
	  }, {
	    title: main_core.Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_NEXT'),
	    method: () => babelHelpers.classPrivateFieldLooseBase(this, _onNextStep)[_onNextStep]()
	  }, {
	    title: main_core.Loc.getMessage('SIGN_WIZARD_FOOTER_SEND_FOR_SIGN'),
	    method: () => babelHelpers.classPrivateFieldLooseBase(this, _sendForSign)[_sendForSign]()
	  }];
	  return buttons.map(button => {
	    const {
	      title,
	      method,
	      buttonClassList = classList
	    } = button;
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<button
					class="${0}"
					title="${0}"
					onclick="${0}"
				>
					${0}
				</button>
			`), buttonClassList.join(' '), title, method, title);
	  });
	}
	function _createStages2() {
	  const entries = Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _stepsMetadata)[_stepsMetadata]);
	  const stages = new Map();
	  entries.forEach(([stepName, step]) => {
	    const stage = main_core.Tag.render(_t3 || (_t3 = _`
				<span class="sign-wizard__stages_item">
					${0}
				</span>
			`), step.title);
	    stages.set(stepName, stage);
	  });
	  return stages;
	}
	function _renderNavigationButtons2() {
	  const [backButton, nextButton, sendForSignButton] = babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons];
	  const firstStep = babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] === 0;
	  const lastStep = babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] + 1 === babelHelpers.classPrivateFieldLooseBase(this, _order)[_order].length;
	  const nextDisabled = babelHelpers.classPrivateFieldLooseBase(this, _order)[_order][babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]] === 'setup';
	  main_core.Dom.removeClass(backButton, '--hide');
	  main_core.Dom.removeClass(nextButton, '--hide');
	  main_core.Dom.addClass(sendForSignButton, '--hide');
	  if (nextDisabled) {
	    main_core.Dom.addClass(nextButton, 'ui-btn-disabled');
	  }
	  if (firstStep) {
	    main_core.Dom.addClass(backButton, '--hide');
	  }
	  if (lastStep) {
	    main_core.Dom.addClass(nextButton, '--hide');
	    main_core.Dom.removeClass(sendForSignButton, '--hide');
	  }
	}
	function _renderActiveStage2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages].forEach(stageNode => {
	    main_core.Dom.removeClass(stageNode, '--active');
	  });
	  const stepName = babelHelpers.classPrivateFieldLooseBase(this, _order)[_order][babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]];
	  const stageNode = babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages].get(stepName);
	  main_core.Dom.addClass(stageNode, '--active');
	}
	function _renderStep2() {
	  const stepName = babelHelpers.classPrivateFieldLooseBase(this, _order)[_order][babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]];
	  const {
	    step
	  } = babelHelpers.classPrivateFieldLooseBase(this, _stepsMetadata)[_stepsMetadata][stepName];
	  main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode]);
	  main_core.Dom.append(step.getLayout(), babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode]);
	}
	function _onPrevStep2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] -= 1;
	  babelHelpers.classPrivateFieldLooseBase(this, _moveOnStep)[_moveOnStep](babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]);
	}
	async function _onNextStep2() {
	  var _await$beforeCompleti;
	  const stepName = babelHelpers.classPrivateFieldLooseBase(this, _order)[_order][babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]];
	  const {
	    beforeCompletion
	  } = babelHelpers.classPrivateFieldLooseBase(this, _stepsMetadata)[_stepsMetadata][stepName];
	  const shouldComplete = (_await$beforeCompleti = await (beforeCompletion == null ? void 0 : beforeCompletion())) != null ? _await$beforeCompleti : true;
	  if (!shouldComplete) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] += 1;
	  babelHelpers.classPrivateFieldLooseBase(this, _moveOnStep)[_moveOnStep](babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]);
	}
	function _moveOnStep2(step) {
	  babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] = step;
	  babelHelpers.classPrivateFieldLooseBase(this, _renderActiveStage)[_renderActiveStage]();
	  babelHelpers.classPrivateFieldLooseBase(this, _renderNavigationButtons)[_renderNavigationButtons]();
	  babelHelpers.classPrivateFieldLooseBase(this, _renderStep)[_renderStep]();
	}
	async function _sendForSign2() {
	  const sendForSignButton = babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons][2];
	  try {
	    const {
	      communications,
	      entityData
	    } = babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary];
	    main_core.Dom.addClass(sendForSignButton, 'ui-btn-wait');
	    const entries = Object.entries(communications);
	    let allowToComplete = true;
	    const restrictions = await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].loadRestrictions();
	    for (const [entityType, item] of entries) {
	      const {
	        type,
	        value
	      } = item;
	      const {
	        uid: memberUid
	      } = entityData[entityType];
	      if (!restrictions.smsAllowed && type === 'PHONE') {
	        top.BX.UI.InfoHelper.show('limit_crm_sign_messenger_identification');
	        allowToComplete = false;
	        continue;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].modifyCommunicationChannel(memberUid, type, value);
	    }
	    const {
	      uid: documentUid,
	      initiator
	    } = babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData;
	    await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].modifyInitiator(documentUid, initiator);
	    if (!allowToComplete) {
	      return;
	    }
	    await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].configureDocument(documentUid);
	    await babelHelpers.classPrivateFieldLooseBase(this, _api)[_api].startSigning(documentUid);
	    babelHelpers.classPrivateFieldLooseBase(this, _complete)[_complete]();
	  } catch (e) {
	    if (main_core.Type.isArrayFilled(e == null ? void 0 : e.errors)) {
	      const firstError = e.errors[0];
	      e.errors.forEach(error => {
	        var _error$customData;
	        if ((error == null ? void 0 : error.code) === firstError.code && ((error == null ? void 0 : error.code) === 'MEMBER_INVALID_PHONE' || (error == null ? void 0 : error.code) === 'MEMBER_PHONE_UNSUPPORTED_COUNTRY_CODE') && main_core.Type.isStringFilled(error == null ? void 0 : (_error$customData = error.customData) == null ? void 0 : _error$customData.phone)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _documentSummary)[_documentSummary].highlightCommunicationWithError(error.customData.phone);
	        }
	      });
	    }
	  } finally {
	    main_core.Dom.removeClass(sendForSignButton, 'ui-btn-wait');
	  }
	}
	function _getLayout2() {
	  const headerTitle = main_core.Loc.getMessage('SIGN_WIZARD_HEADER_TITLE');
	  const headerTitleSub = main_core.Loc.getMessage('SIGN_WIZARD_HEADER_TITLE_SUB');
	  const header = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="sign-wizard__head">
				<div>
					<p class="sign-wizard__head_title">${0}</p>
					<p class="sign-wizard__head_title --sub">
						${0}
					</p>
				</div>
			</div>
		`), headerTitle, headerTitleSub);
	  const content = main_core.Tag.render(_t5 || (_t5 = _`
			<div class="sign-wizard__content">
				<div class="sign-wizard__stages">
					${0}
				</div>
				${0}
			</div>
		`), [...babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages].values()], babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode]);
	  const footer = main_core.Tag.render(_t6 || (_t6 = _`
			<div class="sign-wizard__footer">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons]);
	  return main_core.Tag.render(_t7 || (_t7 = _`
			<div class="sign-wizard sign-wizard__scope">
				<div class="sign-wizard__sidebar">
					${0}
					${0}
					${0}
				</div>
				${0}
			</div>
		`), header, content, footer, babelHelpers.classPrivateFieldLooseBase(this, _preview)[_preview].getLayout());
	}
	function _complete2() {
	  const queryString = window.location.search;
	  const urlParams = new URLSearchParams(queryString);
	  BX.SidePanel.Instance.close();
	  if (!urlParams.has('noRedirect')) {
	    const {
	      entityTypeId,
	      entityId
	    } = babelHelpers.classPrivateFieldLooseBase(this, _documentSetup)[_documentSetup].setupData;
	    const detailsUrl = `/crm/type/${entityTypeId}/details/${entityId}/`;
	    BX.SidePanel.Instance.open(detailsUrl);
	  }
	}

	exports.Wizard = Wizard;

}((this.BX.Sign.V2 = this.BX.Sign.V2 || {}),BX,BX.Sign.V2,BX.Sign.V2,BX.Sign.V2,BX.Sign.V2,BX.Sign.V2));
//# sourceMappingURL=wizard.bundle.js.map
