this.BX = this.BX || {};
this.BX.Sign = this.BX.Sign || {};
(function (exports,main_core,ui_notification) {
	'use strict';

	var _post = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("post");
	var _request = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("request");
	class Api {
	  constructor() {
	    Object.defineProperty(this, _request, {
	      value: _request2
	    });
	    Object.defineProperty(this, _post, {
	      value: _post2
	    });
	  }
	  register(blankId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.register', {
	      blankId
	    });
	  }
	  upload(uid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.upload', {
	      uid
	    });
	  }
	  getPages(uid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.pages.list', {
	      uid
	    }, false);
	  }
	  loadBlanks(page) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.blank.list', {
	      page
	    });
	  }
	  createBlank(files) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.blank.create', {
	      files
	    });
	  }
	  saveBlank(documentUid, blocks) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.blank.block.save', {
	      documentUid,
	      blocks
	    }, false);
	  }
	  loadBlocksData(documentUid, blocks) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.blank.block.loadData', {
	      documentUid,
	      blocks
	    });
	  }
	  changeDocument(uid, blankId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.changeBlank', {
	      uid,
	      blankId
	    });
	  }
	  changeDocumentLanguages(uid, lang) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.changeDocumentLanguages', {
	      uid,
	      lang
	    });
	  }
	  loadDocument(uid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.load', {
	      uid
	    });
	  }
	  configureDocument(uid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.configure', {
	      uid
	    });
	  }
	  loadBlocksByDocument(documentUid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.blank.block.loadByDocument', {
	      documentUid
	    });
	  }
	  startSigning(uid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.signing.start', {
	      uid
	    });
	  }
	  addMember(documentUid, entityType, entityId, party, presetId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.member.add', {
	      documentUid,
	      entityType,
	      entityId,
	      party,
	      presetId
	    });
	  }
	  removeMember(uid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.member.remove', {
	      uid
	    });
	  }
	  loadMembers(documentUid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.member.load', {
	      documentUid
	    });
	  }
	  modifyCommunicationChannel(uid, channelType, channelValue) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.member.modifyCommunicationChannel', {
	      uid,
	      channelType,
	      channelValue
	    });
	  }
	  loadCommunications(uid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.member.loadCommunications', {
	      uid
	    });
	  }
	  modifyTitle(uid, title) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.modifyTitle', {
	      uid,
	      title
	    });
	  }
	  modifyInitiator(uid, initiator) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.modifyInitiator', {
	      uid,
	      initiator
	    });
	  }
	  modifyLanguageId(uid, langId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.modifyLangId', {
	      uid,
	      langId
	    });
	  }
	  loadLanguages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.loadLanguage');
	  }
	  refreshEntityNumber(documentUid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.refreshEntityNumber', {
	      documentUid
	    });
	  }
	  changeDomain() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.portal.changeDomain');
	  }
	  loadRestrictions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.portal.hasRestrictions');
	  }
	  saveStamp(memberUid, fileId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _post)[_post]('sign.api_v1.document.member.saveStamp', {
	      memberUid,
	      fileId
	    });
	  }
	}
	function _post2(endpoint, data = null, notifyError) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _request)[_request]('POST', endpoint, data, notifyError);
	}
	async function _request2(method, endpoint, data, notifyError = true) {
	  const config = {
	    method
	  };
	  if (method === 'POST') {
	    Object.assign(config, {
	      data
	    }, {
	      preparePost: false,
	      headers: [{
	        name: 'Content-Type',
	        value: 'application/json'
	      }]
	    });
	  }
	  try {
	    var _response$errors;
	    const response = await main_core.ajax.runAction(endpoint, config);
	    if (((_response$errors = response.errors) == null ? void 0 : _response$errors.length) > 0) {
	      throw new Error(response.errors[0].message);
	    }
	    return response.data;
	  } catch (ex) {
	    var _errors$0$message, _errors$;
	    if (!notifyError) {
	      return ex;
	    }
	    const {
	      message = `Error in ${endpoint}`,
	      errors = []
	    } = ex;
	    const content = (_errors$0$message = (_errors$ = errors[0]) == null ? void 0 : _errors$.message) != null ? _errors$0$message : message;
	    ui_notification.UI.Notification.Center.notify({
	      content: main_core.Text.encode(content),
	      autoHideDelay: 4000
	    });
	    throw ex;
	  }
	}

	exports.Api = Api;

}((this.BX.Sign.V2 = this.BX.Sign.V2 || {}),BX,BX));
//# sourceMappingURL=api.bundle.js.map
