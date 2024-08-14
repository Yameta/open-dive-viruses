import { Tag, Dom, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { BlankSelector } from 'sign.v2.blank-selector';
import { Api } from 'sign.v2.api';
import type { BlankSelectorConfig } from '../../blank-selector/src/types/config';
import './style.css';

export class DocumentSetup extends EventEmitter
{
	blankSelector: BlankSelector;
	setupData: ?Object;
	#layout: HTMLElement;
	#api: Api;
	#lastBlankId: number;
	#uids: Map<number, string>;

	constructor(blankSelectorConfig: BlankSelectorConfig)
	{
		super();
		this.setEventNamespace('BX.Sign.V2.DocumentSetup');
		this.blankSelector = new BlankSelector({
			...blankSelectorConfig,
			events: {
				toggleSelection: ({ data }) => {
					this.emit('toggleSelection', data);
				},
				addFile: () => this.emit('addFile'),
				removeFile: () => this.emit('removeFile'),
				clearFiles: () => this.emit('clearFiles'),
			},
		});
		this.#layout = Tag.render`
			<div class="sign-document-setup">
				${this.blankSelector.getLayout()}
			</div>
		`;
		this.#api = new Api();
		this.#lastBlankId = 0;
		this.#uids = new Map();
		this.setupData = null;
	}

	async #register(blankId: string): Promise<string>
	{
		const data = await this.#api.register(blankId);

		return data?.uid ?? '';
	}

	async #change(uid: string, blankId: number): Promise<string>
	{
		const data = await this.#api.changeDocument(uid, blankId);

		return data?.uid ?? '';
	}

	async #getPages(uid: string): Promise<{url: string;}[]>
	{
		const data = await this.#api.getPages(uid);

		return this.#convertToBase64(data?.pages);
	}

	async #convertToBase64(pages): Promise<string[]>
	{
		if (!pages)
		{
			return [];
		}

		const promises = pages.map(async (page) => {
			const data = await fetch(page.url);
			const blob = await data.blob();
			const fileReader = new FileReader();
			await new Promise((resolve) => {
				Event.bindOnce(fileReader, 'loadend', resolve);
				fileReader.readAsDataURL(blob);
			});

			return fileReader.result;
		});

		return Promise.all(promises);
	}

	#waitForConnect()
	{
		const observer = new MutationObserver(() => {
			if (!this.#layout.isConnected)
			{
				return;
			}

			const { blanks, selectedBlankId } = this.blankSelector;
			const blank = blanks.get(selectedBlankId);
			blank?.focus();
			observer.disconnect();
		});
		observer.observe(document.body, {
			childList: true,
			subtree: true,
		});
	}

	finishSetup(setupData, blankId: number)
	{
		this.setupData = setupData;
		this.#lastBlankId = blankId;
		this.blankSelector.clearFiles({ removeFromServer: false });
	}

	async setup()
	{
		const { selectedBlankId } = this.blankSelector;
		if (this.#lastBlankId > 0 && this.#lastBlankId === selectedBlankId)
		{
			this.setupData = {
				...this.setupData,
				isTemplate: true,
			};

			return;
		}

		let blankId = 0;
		try
		{
			Dom.addClass(this.#layout, '--pending');
			blankId = selectedBlankId || await this.blankSelector.createBlank();
			const isRegistered = this.#uids.has(blankId);
			const uid = isRegistered
				? await this.#change(this.#uids.get(blankId), blankId)
				: await this.#register(blankId);
			this.#uids.set(blankId, uid);
			await this.#api.upload(uid);
			const [loadedData, blocks] = await Promise.all([
				this.#api.loadDocument(uid),
				this.loadBlocks(uid),
			]);
			const isTemplate = Boolean(selectedBlankId);
			this.finishSetup({ ...loadedData, blocks, isTemplate }, blankId);
		}
		catch
		{
			this.finishSetup(null, 0);
			this.blankSelector.deleteBlank(blankId);
		}

		Dom.removeClass(this.#layout, '--pending');
	}

	loadBlocks(uid: string)
	{
		return this.#api.loadBlocksByDocument(uid);
	}

	getLayout(): HTMLElement
	{
		this.#waitForConnect();

		return this.#layout;
	}

	async waitForPagesList(cb: (urls: string[]) => void)
	{
		const requestTime = 10 * 1000;
		this.blankSelector.resetSelectedBlank();
		const uid = this.setupData.uid;

		let promiseResolve = null;
		let interval = null;
		let isPagesReady = false;

		const promise = new Promise((resolve) => {
			promiseResolve = resolve;
			interval = setInterval(async () => {
				if (isPagesReady)
				{
					return;
				}

				const urls = await this.#getPages(uid);
				if (urls.length > 0)
				{
					resolve(urls);
				}
			}, requestTime);
		});

		BX.PULL?.subscribe({
			moduleId: 'sign',
			command: 'blankIsReady',
			callback: async (result) => {
				if (isPagesReady)
				{
					return;
				}

				const urls = await this.#convertToBase64(result?.pages);
				if (urls.length > 0)
				{
					promiseResolve(urls);
				}
			},
		});

		const urls = await promise;
		clearInterval(interval);
		isPagesReady = true;

		cb(urls);
		this.blankSelector.selectBlank(this.#lastBlankId);
	}
}
