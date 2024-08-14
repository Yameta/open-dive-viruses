import { Tag, Reflection, Loc, Dom } from 'main.core';
import { BlocksManager } from './blocks/blocksManager';
import { EventEmitter } from 'main.core.events';
import './style.css';

const buttonClassList = [
	'ui-btn',
	'ui-btn-sm',
	'ui-btn-round',
	'ui-btn-light-border',
];
const firstPartyBlocks = {
	myreference: {
		title: 'SIGN_EDITOR_BLOCK_CRM',
		hint: 'SIGN_EDITOR_BLOCK_CRM_HINT',
	},
	myrequisites: {
		title: 'SIGN_EDITOR_BLOCK_REQUISITES',
		hint: 'SIGN_EDITOR_BLOCK_FIRST_PARTY_REQUISITES_HINT',
	},
	mysign: {
		title: 'SIGN_EDITOR_BLOCK_SIGNATURE',
		hint: 'SIGN_EDITOR_BLOCK_FIRST_PARTY_SIGNATURE_HINT',
	},
	mystamp: {
		title: 'SIGN_EDITOR_BLOCK_STAMP_MSGVER_1',
		hint: 'SIGN_EDITOR_BLOCK_FIRST_PARTY_STAMP_HINT',
	},
};
const partnerBlocks = {
	reference: { ...firstPartyBlocks.myreference },
	requisites: {
		...firstPartyBlocks.myrequisites,
		hint: 'SIGN_EDITOR_BLOCK_PARTNER_REQUISITES_HINT',
	},
	sign: {
		...firstPartyBlocks.mysign,
		hint: 'SIGN_EDITOR_BLOCK_PARTNER_SIGNATURE_HINT',
	},
	stamp: {
		...firstPartyBlocks.mystamp,
		hint: 'SIGN_EDITOR_BLOCK_PARTNER_STAMP_HINT',
	},
};
const generalBlocks = {
	text: {
		title: 'SIGN_EDITOR_BLOCK_TEXT',
		hint: 'SIGN_EDITOR_BLOCK_TEXT_HINT',
	},
	date: {
		title: 'SIGN_EDITOR_BLOCK_DATE',
		hint: 'SIGN_EDITOR_BLOCK_DATE_HINT',
	},
	number: {
		title: 'SIGN_EDITOR_BLOCK_NUMBER',
		hint: 'SIGN_EDITOR_BLOCK_NUMBER_HINT',
	},
};
const sections = [
	{
		title: 'SIGN_EDITOR_BLOCKS_FIRST_PARTY',
		blocks: firstPartyBlocks,
		part: 1,
	},
	{
		title: 'SIGN_EDITOR_BLOCKS_PARTNER',
		blocks: partnerBlocks,
		part: 2,
	},
	{
		title: 'SIGN_EDITOR_BLOCKS_GENERAL',
		blocks: generalBlocks,
		part: 0,
	},
];
const helpArticleCode = '16571388';

export class Editor extends EventEmitter
{
	#sidePanel: BX.SidePanel;
	#closeSidePanel: Function;
	#dom: HTMLElement;
	#documentLayout: HTMLElement;
	#blocksManager: BlocksManager;
	urls: string[];
	#popupHint: BX.UI.Hint;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Sign.V2.Editor');
		const { Instance } = Reflection.getClass('BX.SidePanel');
		this.#sidePanel = Instance;
		this.#dom = Tag.render`<div class="sign-wizard__scope sign-editor"></div>`;
		this.#documentLayout = Tag.render`
			<div class="sign-editor__document"></div>
		`;
		this.#blocksManager = new BlocksManager({
			documentLayout: this.#documentLayout,
			disableEdit: false,
		});
		const Hint = Reflection.getClass('BX.UI.Hint');
		this.#popupHint = Hint?.createInstance({
			popupParameters: {
				autoHide: true,
				bindOptions: { position: 'top' },
				angle: { position: 'bottom' },
			},
		});
	}

	set entityData(entityData)
	{
		const members = Object.values(entityData).map((member) => {
			const {
				id: cid,
				title: name,
				part,
				presetId,
				entityTypeId,
				uid,
			} = member;

			return {
				cid,
				name,
				part,
				presetId,
				entityTypeId,
				uid,
			};
		});
		this.#blocksManager.addMembers(members);
	}

	async renderDocument()
	{
		Dom.clean(this.#documentLayout);
		if (this.urls.length === 0)
		{
			return;
		}

		const { uid = '', isTemplate } = this.documentData;
		!isTemplate ? this.#toggleEditMode(true) : this.#toggleEditMode(false);
		this.#blocksManager.setDocumentUid(uid);
		const { promises, fragment } = this.urls.reduce((acc, url) => {
			const { fragment, promises } = acc;
			const promise = new Promise((resolve) => {
				const page = Tag.render`
					<div class="sign-editor__document_page">
						<img src="${url}" onload="${resolve}" />
					</div>
				`;
				Dom.append(page, fragment);
			});
			promises.push(promise);

			return acc;
		}, {
			fragment: new DocumentFragment(),
			promises: [],
		});
		Dom.append(fragment, this.#documentLayout);
		const { resizeArea } = this.#blocksManager;
		Dom.append(resizeArea.getLayout(), this.#documentLayout);
		await Promise.all(promises);
		EventEmitter.unsubscribeAll('SidePanel.Slider:onOpenComplete');
		EventEmitter.subscribeOnce('SidePanel.Slider:onOpenComplete', (event) => {
			const editorContent = this.#dom.querySelector('.sign-editor__content');
			if (editorContent)
			{
				editorContent.scrollTop = 0;
			}
			this.#blocksManager.initPagesRect();
			this.#blocksManager.initBlocks(this.documentData.blocks);
		});
	}

	show(): Promise
	{
		return new Promise((resolve) => {
			this.#closeSidePanel = resolve;
			this.#sidePanel.open('editor', {
				contentCallback: () => {
					this.#render();

					return this.#dom;
				},
				events: {
					onCloseComplete: () => this.#closeSidePanel(),
				},
			});
		});
	}

	#render()
	{
		Dom.clean(this.#dom);
		Dom.append(this.#createHeader(), this.#dom);
		Dom.append(this.#createContent(), this.#dom);
	}

	#createHeader(): HTMLElement
	{
		const editButtonClassName = [
			'sign-editor__header_edit-btn',
			...buttonClassList,
		].join(' ');
		const saveButtonClassName = [
			'ui-btn-success',
			...buttonClassList.slice(0, -1),
		].join(' ');
		const editButtonTitle = Loc.getMessage('SIGN_EDITOR_EDIT');
		const saveButtonTitle = Loc.getMessage('SIGN_EDITOR_SAVE');

		return Tag.render`
			<div class="sign-editor__header">
				<p class="sign-editor__header_title">
					${Loc.getMessage('SIGN_EDITOR_EDITING')}
				</p>
				<div class="sign-editor__header_right">
					<span
						onclick="${(event: PointerEvent) => {
							const Helper = Reflection.getClass('BX.Helper');
							Helper.show(`redirect=detail&code=${helpArticleCode}`);
						}}"
						class="sign-editor__header_help"
					></span>
					<span
						class="${editButtonClassName}"
						title="${editButtonTitle}"
						onclick="${() => {
							this.#toggleEditMode(true);
						}}"
					>
						${editButtonTitle}
					</span>
					<span
						class="${saveButtonClassName}"
						title="${saveButtonTitle}"
						onclick="${async ({ target }) => {
							Dom.addClass(target, 'ui-btn-wait');
							const blocks = await this.#blocksManager.save();
							Dom.removeClass(target, 'ui-btn-wait');
							if (!blocks)
							{
								return;
							}
							EventEmitter.subscribeOnce('SidePanel.Slider:onCloseComplete', () => {
								this.emit('save', { blocks });
							});
							this.#sidePanel.close();
							this.#toggleEditMode(false);
						}}"
					>
						${saveButtonTitle}
					</span>
				</div>
			</div>
		`;
	}

	#createContent(): HTMLElement
	{
		const editorContent = Tag.render`
			<div class="sign-editor__content">
				<div class="sign-editor__document-container">
					${this.#documentLayout}
				</div>
				${this.#createSections()}
			</div>
		`;
		this.#blocksManager.setEditorContent(editorContent);
		this.#popupHint.popupParameters = {
			...this.#popupHint.popupParameters,
			targetContainer: editorContent,
		};

		return editorContent;
	}

	#createSections(): HTMLElement
	{
		const sectionsNodes = sections.map((section) => {
			const entries = Object.entries(section.blocks);
			const blocks = entries.map(([code, block]) => {
				const { title, hint } = block;

				return Tag.render`
					<div
						class="sign-editor__section_block"
						data-code="${code}"
						data-part="${section.part}"
					>
						<span>
							<span>${Loc.getMessage(title)}</span>
							<span data-hint="${Loc.getMessage(hint)}"></span>
						</span>
						<span class="sign-editor__section_add-block-btn">
							${Loc.getMessage('SIGN_EDITOR_BLOCK_ADD_TO_DOCUMENT')}
						</span>
					</div>
				`;
			});
			this.#blocksManager.initRepository(blocks);

			return Tag.render`
				<div class="sign-editor__section">
					<p class="sign-editor__section_title">
						${Loc.getMessage(section.title)}
					</p>
					${blocks}
				</div>
			`;
		});
		const layout = Tag.render`
			<div class="sign-editor__sections">
				${sectionsNodes}
			</div>
		`;
		this.#popupHint.init(layout);

		return layout;
	}

	#toggleEditMode(isEdit: boolean)
	{
		if (isEdit)
		{
			Dom.addClass(this.#dom, '--editable');

			return;
		}

		Dom.removeClass(this.#dom, '--editable');
	}
}
