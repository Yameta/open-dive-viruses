import { ajax, Text } from 'main.core';
import { UI } from 'ui.notification';
import type {
	LoadedDocumentData,
	Communication,
	BlockData,
	LoadedBlock,
} from './type';

export class Api
{
	#post(endpoint: string, data: Object = null, notifyError: boolean): Promise<Object>
	{
		return this.#request('POST', endpoint, data, notifyError);
	}

	async #request(
		method: string,
		endpoint: string,
		data: ?Object,
		notifyError: ?boolean = true,
	): Promise<Object>
	{
		const config = { method };
		if (method === 'POST')
		{
			Object.assign(config, { data }, {
				preparePost: false,
				headers: [{
					name: 'Content-Type',
					value: 'application/json',
				}],
			});
		}

		try
		{
			const response = await ajax.runAction(endpoint, config);
			if (response.errors?.length > 0)
			{
				throw new Error(response.errors[0].message);
			}

			return response.data;
		}
		catch (ex)
		{
			if (!notifyError)
			{
				return ex;
			}

			const { message = `Error in ${endpoint}`, errors = [] } = ex;
			const content = errors[0]?.message ?? message;
			UI.Notification.Center.notify({
				content: Text.encode(content),
				autoHideDelay: 4000,
			});

			throw ex;
		}
	}

	register(blankId: string): Promise<{ uid: string; }>
	{
		return this.#post('sign.api_v1.document.register', { blankId });
	}

	upload(uid: string): Promise<[]>
	{
		return this.#post('sign.api_v1.document.upload', { uid });
	}

	getPages(uid: string): Promise<Array<{ url: string; }>>
	{
		return this.#post('sign.api_v1.document.pages.list', { uid }, false);
	}

	loadBlanks(page: number): Promise<Array<{ title: string; id: number }>>
	{
		return this.#post('sign.api_v1.document.blank.list', { page });
	}

	createBlank(files: Array<string>): Promise<{ id: number; }>
	{
		return this.#post('sign.api_v1.document.blank.create', { files });
	}

	saveBlank(documentUid: string, blocks: []): Promise<[]>
	{
		return this.#post('sign.api_v1.document.blank.block.save', { documentUid, blocks }, false);
	}

	loadBlocksData(documentUid: string, blocks: []): Promise<BlockData>
	{
		return this.#post('sign.api_v1.document.blank.block.loadData', { documentUid, blocks });
	}

	changeDocument(uid: string, blankId: number): Promise<{ uid: string; }>
	{
		return this.#post('sign.api_v1.document.changeBlank', { uid, blankId });
	}

	changeDocumentLanguages(uid: string, lang: string): Promise
	{
		return this.#post('sign.api_v1.document.changeDocumentLanguages', { uid, lang });
	}

	loadDocument(uid: string): Promise<LoadedDocumentData>
	{
		return this.#post('sign.api_v1.document.load', { uid });
	}

	configureDocument(uid: string): Promise<[]>
	{
		return this.#post('sign.api_v1.document.configure', { uid });
	}

	loadBlocksByDocument(documentUid: string): Promise<Array<LoadedBlock>>
	{
		return this.#post('sign.api_v1.document.blank.block.loadByDocument', {
			documentUid,
		});
	}

	startSigning(uid: string): Promise<[]>
	{
		return this.#post('sign.api_v1.document.signing.start', { uid });
	}

	addMember(
		documentUid: string,
		entityType: string,
		entityId: number,
		party: number,
		presetId: number,
	): Promise<{ uid: string; }>
	{
		return this.#post('sign.api_v1.document.member.add', {
			documentUid,
			entityType,
			entityId,
			party,
			presetId,
		});
	}

	removeMember(uid: string): Promise<[]>
	{
		return this.#post('sign.api_v1.document.member.remove', { uid });
	}

	loadMembers(documentUid: string): Promise<Array<{ entityId: number; uid: string; }>>
	{
		return this.#post('sign.api_v1.document.member.load', { documentUid });
	}

	modifyCommunicationChannel(
		uid: string,
		channelType: string,
		channelValue: string,
	): Promise<[]>
	{
		return this.#post('sign.api_v1.document.member.modifyCommunicationChannel', {
			uid,
			channelType,
			channelValue,
		});
	}

	loadCommunications(uid: String): Promise<Array<Communication>>
	{
		return this.#post('sign.api_v1.document.member.loadCommunications', { uid });
	}

	modifyTitle(uid: string, title: string): Promise<[]>
	{
		return this.#post('sign.api_v1.document.modifyTitle', {
			uid,
			title,
		});
	}

	modifyInitiator(uid: string, initiator: string): Promise<[]>
	{
		return this.#post('sign.api_v1.document.modifyInitiator', {
			uid,
			initiator,
		});
	}

	modifyLanguageId(uid: string, langId: string): Promise
	{
		return this.#post('sign.api_v1.document.modifyLangId', {
			uid,
			langId
		});
	}

	loadLanguages(): Promise
	{
		return this.#post('sign.api_v1.document.loadLanguage');
	}

	refreshEntityNumber(documentUid: string): Promise<[]>
	{
		return this.#post('sign.api_v1.document.refreshEntityNumber', {
			documentUid,
		});
	}

	changeDomain(): Promise
	{
		return this.#post('sign.api_v1.portal.changeDomain');
	}

	loadRestrictions(): Promise<{ smsAllowed: boolean; }>
	{
		return this.#post('sign.api_v1.portal.hasRestrictions');
	}

	saveStamp(memberUid: String, fileId: string): Promise<{ id: number; srcUri: string; }>
	{
		return this.#post('sign.api_v1.document.member.saveStamp', {
			memberUid, fileId,
		});
	}
}
