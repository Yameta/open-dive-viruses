import { Tag, Dom, Text } from 'main.core';

type NodeSettings = {
	events?: { [key: string]: (event: Event) => void };
	children?: Array<HTMLElement>;
};

type ListItemProps = {
	title: string;
	description?: string;
	modifier: string;
};

class ListItem
{
	props: ListItemProps;
	layout: HTMLElement;

	constructor(props: ListItemProps)
	{
		this.props = props;
	}

	createListItem(settings: ?NodeSettings = {}): HTMLElement
	{
		const {
			events = {},
			children = [],
		} = settings;
		const { title, description = '', modifier } = this.props;
		const titleNode = Tag.render`
			<span
				class="sign-blank-selector__list_item-title"
				title="${Text.encode(title)}"
			>
				${Text.encode(title)}
			</span>
		`;
		const descriptionNode = Tag.render`
			<span class="sign-blank-selector__list_item-info">
				${Text.encode(description).replaceAll('[br]', '<br />')}
			</span>
		`;

		return Dom.create('div', {
			attrs: {
				className: `sign-blank-selector__list_item --${modifier}`,
			},
			events,
			children: [...children, titleNode, descriptionNode],
		});
	}

	getLayout(): HTMLElement
	{
		if (!this.layout)
		{
			this.layout = this.createListItem();
		}

		return this.layout;
	}
}

export { ListItem, ListItemProps };
