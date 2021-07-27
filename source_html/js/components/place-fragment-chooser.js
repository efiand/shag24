import Chooser from './chooser';

const { hash, hostname, origin, pathname } = window.location;
const pageUrl = origin + pathname;

export default class PlaceFragmentChooser extends Chooser {
	constructor(chooser) {
		super(chooser);

		this._pageInputs = document.querySelectorAll(`.form__page-holder-choosable`);
		this._fragmentsSelector = ``;
		this._buttons = this._chooser.querySelectorAll(`.chooser__item`);

		this._initialButton = hash ? this._chooser.querySelector(`[data-fragment=${hash.slice(1)}]`) : null;

		this._clickHandler = this._clickHandler.bind(this);
		this._chooser.addEventListener(`click`, this._clickHandler);
		this._choose(this._initialButton);
	}

	_clickHandler({ target }) {
		if (!target.classList.contains(`chooser__item`)) {
			return;
		}
		this._choose(target);
		this._close();
	}

	_choose(target) {
		for (const button of this._buttons) {
			document.querySelector(`#${button.dataset.fragment}`).classList.add(`hidden`);
			button.classList.remove(`chooser__item--active`);
		}

		if (target) {
			const fragmentId = `#${target.dataset.fragment}`;
			const targetFragment = document.querySelector(fragmentId);

			targetFragment.classList.remove(`hidden`);
			target.classList.add(`chooser__item--active`);

			this._setURLToForms(fragmentId);
			history.pushState({}, document.title, pageUrl + fragmentId);

			if (target === this._initialButton) {
				targetFragment.scrollIntoView();
			}
		} else {
			this._setURLToForms();
		}
	}

	_setURLToForms(hashValue = ``) {
		for (const input of this._pageInputs) {
			input.value = hostname + pathname + hashValue;
		}
	}
}
