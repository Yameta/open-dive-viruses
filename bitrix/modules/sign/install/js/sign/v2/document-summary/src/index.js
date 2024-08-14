import { Dom, Loc, Reflection, Tag, Text, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MenuManager } from 'main.popup';
import { Api } from 'sign.v2.api';
import { Button } from 'ui.buttons';
import { LangSelector } from 'sign.v2.lang-selector';
import './style.css';

import type { DocumentSummaryConfig } from './types/config';

const buttonClassList = [
	'ui-btn',
	'ui-btn-sm',
	'ui-btn-light-border',
	'ui-btn-round',
];
const menuPrefix = 'sign-member-communication';

export class DocumentSummary extends EventEmitter
{
	#editDocumentBtn: HTMLElement;
	#api: Api;
	#blocks: HTMLElement;
	#filledBlocks: HTMLElement;
	#menus: Array<Menu>;
	#summaryContainer: HTMLElement;
	documentData;
	entityData;
	communications;
	#langContainer: HTMLElement;
	#config: DocumentSummaryConfig;
	#langSelector: LangSelector;

	constructor(config: DocumentSummaryConfig)
	{
		super();
		this.#config = config;
		this.setEventNamespace('BX.Sign.V2.DocumentSummary');
		this.#editDocumentBtn = Tag.render`
			<span
				class="${buttonClassList.join(' ')}"
				onclick="${() => this.emit('showEditor')}"
			>
				${Loc.getMessage('SIGN_DOCUMENT_INFO_EDIT')}
			</span>
		`;
		this.#api = new Api();
		this.#blocks = Tag.render`
			<p class="sign-document-info__details_structure"></p>
		`;
		this.#filledBlocks = Tag.render`
			<p class="sign-document-info__details_structure"></p>
		`;
		this.#langSelector = new LangSelector(
			this.#config.region,
			this.#config.languages
		);
		this.#langContainer = Tag.render`
			<div class="sign-blank-selector__lang-container">
				${this.#langSelector.getLayout()}
				<span data-hint="${Loc.getMessage('SIGN_DOCUMENT_LANGUAGE_BUTTON_INFO')}"></span>
			</div>
		`;
		BX.UI.Hint.init(this.#langContainer);
		this.#menus = [];
		this.documentData = {};
		this.entityData = {};
		this.communications = {};
	}

	getLayout(): HTMLElement
	{
		this.#langSelector.setDocumentUid(this.documentData.uid);

		this.#summaryContainer = Tag.render`
			<div class="sign-document-info">
				<div class="sign-document-indo__summary-title-wrapper">
					<p class="sign-document-info__title">
						${Loc.getMessage('SIGN_DOCUMENT_INFO_SEND')}
					</p>
					${this.#langContainer}
				</div>
				<div class="sign-document-info__summary">
					${this.#createDocumentDetails()}
					${this.#editDocumentBtn}
				</div>
				${this.#createParties()}
			</div>
		`;

		return this.#summaryContainer;
	}

	#createDocumentDetails(): HTMLElement
	{
		this.renderDocumentDetails();

		return Tag.render`
			<div class="sign-document-info__details">
				<div class="sign-document-info__details_title">
					<span>${Text.encode(this.documentData.title ?? '')}</span>
					<span
						class="sign-document-info__details_edit-title-btn"
						onclick="${({ target: button }) => {
							this.#toggleTitleEditor(button, true);
						}}"
					>
					</span>
				</div>
				${this.#blocks}
				${this.#filledBlocks}
			</div>
		`;
	}

	#createTitleEditor(): HTMLElement
	{
		const okButtonClassName = [
			...buttonClassList.slice(0, 2),
			'ui-btn-primary',
			'sign-document-info__title-editor_ok-btn',
		].join(' ');
		const discardButtonClassName = [
			...buttonClassList.slice(0, 3),
			'sign-document-info__title-editor_discard-btn',
		].join(' ');
		const input = Tag.render`<input type="text" class="ui-ctl-element" />`;
		input.value = this.documentData.title ?? '';
		this.#focusInput(input);

		return Tag.render`
			<div class="sign-document-info__title-editor">
				<div class="sign-document-info__title-editor_controls">
					<span class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						${input}
					</span>
					<span
						class="${okButtonClassName}"
						onclick="${async ({ target }) => {
							Dom.addClass(target, 'ui-btn-wait');
							await this.#modifyDocumentTitle(input.value);
							Dom.removeClass(target, 'ui-btn-wait');
							this.#toggleTitleEditor(target, false);
						}}"
					>
					</span>
					<span
						class="${discardButtonClassName}"
						onclick="${({ target }) => {
							this.#toggleTitleEditor(target, false);
						}}"
					>
					</span>
				</div>
				<p class="sign-document-info__title-editor_help">
					${Loc.getMessage('SIGN_DOCUMENT_INFO_TITLE_EDITOR_HELP')}
				</p>
			</div>
		`;
	}

	#toggleTitleEditor(button: HTMLElement, shouldShow: boolean)
	{
		const summaryNode = button.closest('.sign-document-info__summary');
		if (shouldShow)
		{
			Dom.clean(summaryNode);
			Dom.append(this.#createTitleEditor(), summaryNode);

			return;
		}

		Dom.replace(summaryNode.firstElementChild, this.#createDocumentDetails());
		Dom.append(this.#editDocumentBtn, summaryNode);
	}

	#focusInput(input: HTMLElement)
	{
		const observer = new MutationObserver(() => {
			if (input.isConnected)
			{
				input.focus();
				observer.disconnect();
			}
		});
		observer.observe(document.body, { childList: true, subtree: true });
	}

	async #modifyDocumentTitle(newValue: string)
	{
		const { title = '', uid = '' } = this.documentData;
		if (title === newValue)
		{
			return;
		}

		try
		{
			await this.#api.modifyTitle(uid, newValue);
			this.documentData = {
				...this.documentData,
				title: newValue,
			};
			this.emit('changeTitle', { newValue });
		}
		catch (ex)
		{
			console.error(ex);
		}
	}

	#attachMenu(idMeans: HTMLElement, entityData): Menu
	{
		let menuItems = [];
		const menuId = `${menuPrefix}-${entityData.entityTypeId}-${entityData.id}`;
		if (this.#menus[menuId])
		{
			let items = this.#menus[menuId].getMenuItems();
			const swap = (array, from, to) => {
				const tmp = array[to];
				// eslint-disable-next-line no-param-reassign
				array[to] = array[from];
				// eslint-disable-next-line no-param-reassign
				array[from] = tmp;
			};

			while (items.length > 1)
			{
				if (items[0].id === 'show-member')
				{
					swap(items, 0, 1);
					continue;
				}
				this.#menus[menuId].removeMenuItem(items[0].id);
				items = this.#menus[menuId].getMenuItems();
			}
		}

		this.#api.loadCommunications(entityData.uid).then(async (multiFields) => {
			let selectedCommunication = {};
			const restrictions = await this.#api.loadRestrictions();
			const mapper = (communication) => {
				let text = communication.VALUE;
				if (communication?.TYPE === 'PHONE' && BX.PhoneNumberParser)
				{
					BX.PhoneNumberParser.getInstance().parse(communication.VALUE).then((parsedNumber) => {
						text = parsedNumber.format(BX.PhoneNumber.Format.INTERNATIONAL);
					});
				}

				if ((communication?.TYPE === 'PHONE' && restrictions.smsAllowed)
					|| (communication?.TYPE === 'EMAIL' && Object.keys(selectedCommunication).length === 0))
				{
					selectedCommunication = communication;
				}

				return {
					text,
					onclick: ({ target }) => {
						this.#updateCommunications(entityData, communication);
						// eslint-disable-next-line no-param-reassign
						idMeans.firstElementChild.textContent = text;
						this.#menus[menuId].close();
						this.#updatePhoneAttr(idMeans, communication?.TYPE === 'PHONE' ? communication.VALUE : null);
					},
				};
			};

			menuItems = [
				// eslint-disable-next-line no-unsafe-optional-chaining
				...(multiFields?.EMAIL ? multiFields?.EMAIL.map((element) => mapper(element)) : []),
				// eslint-disable-next-line no-unsafe-optional-chaining
				...(multiFields?.PHONE ? await Promise.all(multiFields?.PHONE.map(async (element) => {
					const item = mapper(element);
					item.text = await this.#formatPhoneNumberForUi(item.text);
					return item;
				})) : []),
			];

			menuItems.map((item) => this.#menus[menuId].addMenuItem(item, null));
			this.#updateCommunications(entityData, selectedCommunication);

			idMeans.firstElementChild.textContent = selectedCommunication?.TYPE === 'PHONE'
				? await this.#formatPhoneNumberForUi(selectedCommunication?.VALUE)
				: selectedCommunication?.VALUE
			;

			this.#updatePhoneAttr(idMeans, selectedCommunication?.TYPE === 'PHONE'
				? await this.#getNormalizedPhoneNumber(selectedCommunication?.VALUE)
				: null
			);
		}).catch(() => {});

		menuItems.push({
			id: 'show-member',
			text: Loc.getMessage('SIGN_DOCUMENT_INFO_OPEN_VIEW'),
			onclick: () => {
				this.#showMemberInfo(idMeans, entityData);
				this.#menus[menuId].close();
			},
		});

		if (!this.#menus[menuId])
		{
			this.#menus[menuId] = MenuManager.create({
				id: menuId,
				items: menuItems,
			});
			const popup = this.#menus[menuId].getPopupWindow();
			popup.setBindElement(idMeans);
		}

		// eslint-disable-next-line no-param-reassign
		idMeans.firstElementChild.textContent = menuItems[0].text;

		return this.#menus[menuId];
	}

	async #formatPhoneNumberForUi(phone: string): Promise<string>
	{
		let phoneFormatted = phone;
		if (BX.PhoneNumberParser)
		{
			phoneFormatted = await BX.PhoneNumberParser.getInstance().parse(phone).then((parsedNumber) => {
				return parsedNumber.format(BX.PhoneNumber.Format.INTERNATIONAL);
			});
		}

		return phoneFormatted;
	}

	async #getNormalizedPhoneNumber(phone: string): Promise<string>
	{
		if (BX.PhoneNumberParser)
		{
			const parsedNumber = await BX.PhoneNumberParser.getInstance().parse(phone);

			return parsedNumber.isValid()
				? parsedNumber.format(BX.PhoneNumber.Format.E164)
				: parsedNumber.rawNumber
			;
		}

		return phone;
	}

	highlightCommunicationWithError(phoneNumber: string): void
	{
		const aIdMeans = this.#summaryContainer.querySelectorAll(
			`.sign-document-info__party_id-means[data-phone-normalized="${phoneNumber}"]`,
		);
		aIdMeans.forEach((elem) => {
			const wrapper = elem.closest('.sign-document-info__party');
			Dom.addClass(wrapper, '--validation-error');
		});
	}

	#updatePhoneAttr(elem: Element, phone: string = null): void
	{
		if (Type.isStringFilled(phone))
		{
			Dom.attr(elem, 'data-phone-normalized', phone);
		}
		else
		{
			elem.removeAttribute('data-phone-normalized');
		}
	}

	resetCommunicationErrors(): void
	{
		const elems = this.#summaryContainer.querySelectorAll('.sign-document-info__party');
		elems.forEach((elem) => {
			Dom.removeClass(elem, '--validation-error');
		});
	}

	#showMemberInfo(idMeans: HTMLElement, entityData: Object)
	{
		const { Instance: slider } = Reflection.getClass('BX.SidePanel');
		slider.open(entityData.url, {
			cacheable: false,
			allowChangeHistory: false,
			events: {
				onClose: () => {
					this.#attachMenu(idMeans, entityData);
				},
			},
		});
	}

	#createParties(): Array<HTMLElement>
	{
		const parties = [
			{
				partyTitle: Loc.getMessage('SIGN_DOCUMENT_INFO_FIRST_PARTY'),
				entityData: this.entityData.company,
			},
			{
				partyTitle: Loc.getMessage('SIGN_DOCUMENT_INFO_PARTNER'),
				entityData: this.entityData.contact,
			},
		];
		Object.keys(MenuManager.Data).forEach((menuId) => {
			if (menuId.includes(menuPrefix))
			{
				MenuManager.destroy(menuId);
			}
		});

		this.#menus = [];

		return parties.map((party) => {
			const { partyTitle, entityData } = party;
			const { title } = entityData;
			const idMeans = Tag.render`
				<span
					class="sign-document-info__party_id-means"
					onclick="${() => menu.show()}"
				>
					<span></span>
				</span>
			`;
			const menu = this.#attachMenu(idMeans, entityData);

			return Tag.render`
				<div class="sign-document-info__party">
					<div class="sign-document-info__party_summary">
						<p class="sign-document-info__party_title">${partyTitle}</p>
						<span class="sign-document-info__party_member-name">
							${Text.encode(title)}
						</span>
						<span class="sign-document-info__party_status">
							${Loc.getMessage('SIGN_DOCUMENT_INFO_NOT_SIGNED')}
						</span>
					</div>
					<div class="sign-document-info__party_id">
						<p class="sign-document-info__party_title">
							${Loc.getMessage('SIGN_DOCUMENT_INFO_ID')}
						</p>
						${idMeans}
					</div>
				</div>
			`;
		});
	}

	#updateCommunications(entityData, communication)
	{
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-typeof
		if (typeof communication === 'undefined')
		{
			return;
		}

		const { TYPE: type, VALUE: value } = communication;
		this.communications = {
			...this.communications,
			[entityData.type]: { type, value },
		};

		this.resetCommunicationErrors();
	}

	renderDocumentDetails()
	{
		const blocks = this.documentData.blocks ?? [];
		const addedBlocks = blocks.filter((block): boolean => block.party === 1).length;
		const addedFilledBlocks = blocks.filter((block): boolean => block.party >= 2).length;
		const addedBlocksTitle = Loc.getMessage('SIGN_DOCUMENT_INFO_ADDED_BLOCKS', {
			'#CNT#': addedBlocks,
		});
		const filledBlocksTitle = Loc.getMessage('SIGN_DOCUMENT_INFO_ADDED_FILLED_FIELDS', {
			'#CNT#': addedFilledBlocks,
		});
		this.#blocks.textContent = addedBlocksTitle;
		this.#filledBlocks.textContent = filledBlocksTitle;
	}
}
