import { Tag, Dom } from 'main.core';
import { Loader } from 'main.loader';
import { ListItem, type ListItemProps } from './list-item';

export class Blank extends ListItem
{
	#statusNode: HTMLElement;
	#loader: Loader;

	constructor(props: ListItemProps)
	{
		super({ ...props, modifier: 'blank' });
		this.#statusNode = Tag.render`
			<div class="sign-blank-selector__list_item-status"></div>
		`;
		this.#loader = new Loader({ size: 30, target: this.#statusNode });
	}

	createListItem(): HTMLElement
	{
		return super.createListItem({
			children: [this.#statusNode],
		});
	}

	focus()
	{
		this.getLayout().focus();
	}

	select()
	{
		Dom.addClass(this.layout, '--active');
	}

	deselect(relatedTarget: ?HTMLElement)
	{
		Dom.removeClass(this.layout, '--active');
	}

	remove()
	{
		Dom.remove(this.getLayout());
	}

	set id(id: number)
	{
		this.getLayout().dataset.id = id;
	}

	set ready(isReady: boolean)
	{
		if (!isReady)
		{
			this.#loader.show();

			return;
		}

		const layout = this.getLayout();
		layout.tabIndex = '0';
		this.#loader.hide();
		Dom.addClass(layout, '--loaded');
	}
}
