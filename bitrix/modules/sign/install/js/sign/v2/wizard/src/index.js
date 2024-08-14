import { Tag, Loc, Dom, Reflection, Type } from 'main.core';
import { DocumentSetup } from 'sign.v2.document-setup';
import { Requisites } from 'sign.v2.requisites';
import { DocumentSummary } from 'sign.v2.document-summary';
import { Preview } from 'sign.v2.preview';
import { Api } from 'sign.v2.api';
import './style.css';

type StepsMetadata = {
	setup: {
		step: DocumentSetup;
		title: string;
		beforeCompletion: () => Promise<boolean>;
	},
	requisites: {
		step: Requisites;
		title: string;
		beforeCompletion: () => Promise<boolean>;
	},
	send: {
		step: DocumentSummary;
		title: string;
	}
};
type WizardOptions = {
	uid: ?string;
	containerId: string;
};

export class Wizard
{
	#documentSetup: DocumentSetup;
	#uid: ?string;
	#containerId: string;
	#requisites: Requisites;
	#documentSummary: DocumentSummary;
	#preview: Preview;
	#editor: Editor;
	#api: Api;
	#stepNode: HTMLElement;
	#stepsMetadata: StepsMetadata;
	#stepIndex: number;
	#order: Array<string>;
	#stages: Map<string, HTMLElement>;
	#navigationButtons: Array<HTMLElement>;

	constructor(options: WizardOptions)
	{
		const { uid = null, containerId, config = {} } = options;

		this.#uid = uid;
		this.#containerId = containerId;
		this.#documentSetup = new DocumentSetup(config.blankSelectorConfig);
		this.#requisites = new Requisites();
		this.#documentSummary = new DocumentSummary(config.documentSummaryConfig);
		this.#preview = new Preview();
		this.#api = new Api();
		this.#stepsMetadata = {
			setup: {
				step: this.#documentSetup,
				title: Loc.getMessage('SIGN_WIZARD_LOAD_DOCUMENT'),
				beforeCompletion: async () => {
					const [, nextButton] = this.#navigationButtons;
					const renderPages = (urls = [], blocks = []) => {
						this.#editor.urls = urls;
						this.#preview.urls = urls;
						this.#preview.setBlocks(blocks);
					};
					renderPages();
					Dom.addClass(nextButton, 'ui-btn-wait');
					await this.#documentSetup.setup();
					Dom.removeClass(nextButton, 'ui-btn-wait');
					const setupData = this.#documentSetup.setupData;
					if (!setupData)
					{
						return false;
					}

					const { uid, entityId, blocks, initiator } = setupData;
					this.#requisites.documentData = { uid, entityId, initiator };
					this.#preview.ready = false;
					this.#documentSetup.waitForPagesList((urls) => {
						renderPages(urls, blocks);
						this.#preview.ready = true;
					});

					return true;
				},
			},
			requisites: {
				step: this.#requisites,
				title: Loc.getMessage('SIGN_WIZARD_PREPARING_DOCUMENT'),
				beforeCompletion: async () => {
					const [, nextButton] = this.#navigationButtons;
					Dom.addClass(nextButton, 'ui-btn-wait');
					const { uid, isTemplate, title, initiator } = this.#documentSetup.setupData;
					const valid = this.#requisites.checkInitiator(initiator);
					if (!valid)
					{
						Dom.removeClass(nextButton, 'ui-btn-wait');

						return false;
					}

					const entityData = await this.#requisites.processMembers();
					if (!entityData)
					{
						Dom.removeClass(nextButton, 'ui-btn-wait');

						return false;
					}

					const blocks = await this.#documentSetup.loadBlocks(uid);
					this.#editor.documentData = { isTemplate, uid, blocks };
					this.#editor.entityData = entityData;
					this.#documentSummary.documentData = { uid, title, blocks };
					this.#documentSummary.entityData = entityData;
					await this.#editor.renderDocument();
					Dom.removeClass(nextButton, 'ui-btn-wait');
					await this.#editor.show();

					return true;
				},
			},
			send: {
				step: this.#documentSummary,
				title: Loc.getMessage('SIGN_WIZARD_SEND_DOCUMENT'),
			},
		};
		this.#stepIndex = 0;
		this.#order = ['setup', 'requisites', 'send'];
		this.#stages = this.#createStages();
		this.#navigationButtons = this.#createNavigationButtons();
		this.#stepNode = Tag.render`<div class="sign-wizard__step"></div>`;
		// eslint-disable-next-line no-shadow
		const Editor = Reflection.getClass('top.BX.Sign.V2.Editor');
		this.#editor = new Editor();
		this.#subscribeOnEvents();
	}

	async #loadData()
	{
		const loadedData = this.#uid ? await this.#api.loadDocument(this.#uid) : null;
		if (!loadedData)
		{
			return;
		}

		this.#documentSetup.finishSetup(loadedData, loadedData.blankId);
		this.#requisites.documentData = loadedData;
		this.#editor.documentData = loadedData;
		this.#documentSummary.documentData = loadedData;
		if (
			loadedData.status === 'uploading'
			|| loadedData.status === 'uploaded'
			|| loadedData.status === 'ready'
		)
		{
			this.#documentSetup.waitForPagesList((urls) => {
				this.#editor.urls = urls;
				this.#preview.urls = urls;
				this.#preview.setBlocks(loadedData.blocks);
			});
			this.#stepIndex = 1;
		}
	}

	async show()
	{
		try
		{
			await this.#loadData();
			this.#moveOnStep(this.#stepIndex);
			const container = document.getElementById(this.#containerId);
			Dom.append(this.#getLayout(), container);
		}
		catch (ex)
		{
			// eslint-disable-next-line no-console
			console.log(ex);
		}
	}

	#subscribeOnStagesEvents()
	{
		const [, nextButton] = this.#navigationButtons;
		const events = [
			{
				type: 'toggleSelection',
				stage: 'setup',
				method: ({ data }) => {
					const { relatedTarget, selected, blankId } = data;
					if (selected)
					{
						Dom.removeClass(nextButton, 'ui-btn-disabled');

						return;
					}

					if (relatedTarget !== nextButton)
					{
						Dom.addClass(nextButton, 'ui-btn-disabled');

						return;
					}

					this.#documentSetup.blankSelector.selectBlank(blankId);
				},
			},
			{
				type: 'addFile',
				stage: 'setup',
				method: () => {
					if (this.#documentSetup.blankSelector.isFilesReadyForUpload())
					{
						Dom.removeClass(nextButton, 'ui-btn-disabled');
					}
					else
					{
						Dom.addClass(nextButton, 'ui-btn-disabled');
					}
				},
			},
			{
				type: 'removeFile',
				stage: 'setup',
				method: () => {
					if (this.#documentSetup.blankSelector.isFilesReadyForUpload())
					{
						Dom.removeClass(nextButton, 'ui-btn-disabled');
					}
					else
					{
						Dom.addClass(nextButton, 'ui-btn-disabled');
					}
				},
			},
			{
				type: 'clearFiles',
				stage: 'setup',
				method: () => Dom.addClass(nextButton, 'ui-btn-disabled'),
			},
			{
				type: 'changeInitiator',
				stage: 'requisites',
				method: ({ data }) => {
					this.#documentSetup.setupData = {
						...this.#documentSetup.setupData,
						initiator: data.initiator,
					};
				},
			},
			{
				type: 'showEditor',
				stage: 'send',
				method: () => this.#editor.show(),
			},
			{
				type: 'changeTitle',
				stage: 'send',
				method: ({ data }) => {
					this.#documentSetup.setupData = {
						...this.#documentSetup.setupData,
						title: data.newValue,
					};
				},
			},
		];
		events.forEach(({ type, method, stage }) => {
			const { step } = this.#stepsMetadata[stage];
			step.subscribe(type, method);
		});
	}

	#subscribeOnEvents()
	{
		this.#subscribeOnStagesEvents();
		this.#editor.subscribe('save', ({ data }) => {
			const blocks = data.blocks;
			this.#preview.setBlocks(blocks);
			this.#documentSetup.setupData = {
				...this.#documentSetup.setupData,
				blocks,
			};
			this.#documentSummary.documentData = {
				...this.#documentSummary.documentData,
				blocks,
			};
			this.#documentSummary.renderDocumentDetails();
		});
	}

	#createNavigationButtons(): Array<HTMLElement>
	{
		const classList = [
			'ui-btn-primary',
			'ui-btn',
			'ui-btn-lg',
			'ui-btn-round',
			'sign-wizard__footer_button',
		];
		const buttons = [
			{
				title: Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_BACK'),
				method: () => this.#onPrevStep(),
				buttonClassList: [
					...classList.slice(1),
					'ui-btn-light-border',
				],
			},
			{
				title: Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_NEXT'),
				method: () => this.#onNextStep(),
			},
			{
				title: Loc.getMessage('SIGN_WIZARD_FOOTER_SEND_FOR_SIGN'),
				method: () => this.#sendForSign(),
			},
		];

		return buttons.map((button) => {
			const {
				title,
				method,
				buttonClassList = classList,
			} = button;

			return Tag.render`
				<button
					class="${buttonClassList.join(' ')}"
					title="${title}"
					onclick="${method}"
				>
					${title}
				</button>
			`;
		});
	}

	#createStages(): HTMLElement
	{
		const entries = Object.entries(this.#stepsMetadata);
		const stages = new Map();
		entries.forEach(([stepName, step]) => {
			const stage = Tag.render`
				<span class="sign-wizard__stages_item">
					${step.title}
				</span>
			`;
			stages.set(stepName, stage);
		});

		return stages;
	}

	#renderNavigationButtons()
	{
		const [
			backButton,
			nextButton,
			sendForSignButton,
		] = this.#navigationButtons;
		const firstStep = this.#stepIndex === 0;
		const lastStep = this.#stepIndex + 1 === this.#order.length;
		const nextDisabled = this.#order[this.#stepIndex] === 'setup';
		Dom.removeClass(backButton, '--hide');
		Dom.removeClass(nextButton, '--hide');
		Dom.addClass(sendForSignButton, '--hide');
		if (nextDisabled)
		{
			Dom.addClass(nextButton, 'ui-btn-disabled');
		}

		if (firstStep)
		{
			Dom.addClass(backButton, '--hide');
		}

		if (lastStep)
		{
			Dom.addClass(nextButton, '--hide');
			Dom.removeClass(sendForSignButton, '--hide');
		}
	}

	#renderActiveStage()
	{
		this.#stages.forEach((stageNode) => {
			Dom.removeClass(stageNode, '--active');
		});
		const stepName = this.#order[this.#stepIndex];
		const stageNode = this.#stages.get(stepName);
		Dom.addClass(stageNode, '--active');
	}

	#renderStep()
	{
		const stepName = this.#order[this.#stepIndex];
		const { step } = this.#stepsMetadata[stepName];
		Dom.clean(this.#stepNode);
		Dom.append(step.getLayout(), this.#stepNode);
	}

	#onPrevStep()
	{
		this.#stepIndex -= 1;
		this.#moveOnStep(this.#stepIndex);
	}

	async #onNextStep()
	{
		const stepName = this.#order[this.#stepIndex];
		const { beforeCompletion } = this.#stepsMetadata[stepName];
		const shouldComplete = await beforeCompletion?.() ?? true;
		if (!shouldComplete)
		{
			return;
		}

		this.#stepIndex += 1;
		this.#moveOnStep(this.#stepIndex);
	}

	#moveOnStep(step: number)
	{
		this.#stepIndex = step;
		this.#renderActiveStage();
		this.#renderNavigationButtons();
		this.#renderStep();
	}

	async #sendForSign()
	{
		const sendForSignButton = this.#navigationButtons[2];
		try
		{
			const { communications, entityData } = this.#documentSummary;
			Dom.addClass(sendForSignButton, 'ui-btn-wait');
			const entries = Object.entries(communications);
			let allowToComplete = true;
			const restrictions = await this.#api.loadRestrictions();

			for (const [entityType, item] of entries)
			{
				const { type, value } = item;
				const { uid: memberUid } = entityData[entityType];

				if (!restrictions.smsAllowed && type === 'PHONE')
				{
					top.BX.UI.InfoHelper.show('limit_crm_sign_messenger_identification');
					allowToComplete = false;

					continue;
				}

				this.#api.modifyCommunicationChannel(memberUid, type, value);
			}

			const {
				uid: documentUid,
				initiator,
			} = this.#documentSetup.setupData;
			await this.#api.modifyInitiator(documentUid, initiator);

			if (!allowToComplete)
			{
				return;
			}
			await this.#api.configureDocument(documentUid);
			await this.#api.startSigning(documentUid);
			this.#complete();
		}
		catch (e)
		{
			if (Type.isArrayFilled(e?.errors))
			{
				const firstError = e.errors[0];
				e.errors.forEach((error) => {
					if (
						error?.code === firstError.code
						&& (error?.code === 'MEMBER_INVALID_PHONE' || error?.code === 'MEMBER_PHONE_UNSUPPORTED_COUNTRY_CODE')
						&& Type.isStringFilled(error?.customData?.phone)
					)
					{
						this.#documentSummary.highlightCommunicationWithError(error.customData.phone);
					}
				});
			}
		}
		finally
		{
			Dom.removeClass(sendForSignButton, 'ui-btn-wait');
		}
	}

	#getLayout(): HTMLElement
	{
		const headerTitle = Loc.getMessage('SIGN_WIZARD_HEADER_TITLE');
		const headerTitleSub = Loc.getMessage('SIGN_WIZARD_HEADER_TITLE_SUB');
		const header = Tag.render`
			<div class="sign-wizard__head">
				<div>
					<p class="sign-wizard__head_title">${headerTitle}</p>
					<p class="sign-wizard__head_title --sub">
						${headerTitleSub}
					</p>
				</div>
			</div>
		`;
		const content = Tag.render`
			<div class="sign-wizard__content">
				<div class="sign-wizard__stages">
					${[...this.#stages.values()]}
				</div>
				${this.#stepNode}
			</div>
		`;
		const footer = Tag.render`
			<div class="sign-wizard__footer">
				${this.#navigationButtons}
			</div>
		`;

		return Tag.render`
			<div class="sign-wizard sign-wizard__scope">
				<div class="sign-wizard__sidebar">
					${header}
					${content}
					${footer}
				</div>
				${this.#preview.getLayout()}
			</div>
		`;
	}

	#complete()
	{
		const queryString = window.location.search;
		const urlParams = new URLSearchParams(queryString);
		BX.SidePanel.Instance.close();

		if (!urlParams.has('noRedirect'))
		{
			const { entityTypeId, entityId } = this.#documentSetup.setupData;
			const detailsUrl = `/crm/type/${entityTypeId}/details/${entityId}/`;
			BX.SidePanel.Instance.open(detailsUrl);
		}
	}
}
