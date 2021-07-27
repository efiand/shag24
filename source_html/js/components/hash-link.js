import { applyAll, scrollToHash } from '../utils';

export default class HashLink {
	constructor(hashLink, hashLinks) {
		this._hashLink = hashLink;
		this._hashLinks = hashLinks;
		this.href = hashLink.getAttribute(`href`);
		this.hash = this.href.slice(1);
		this.isInHeader = hashLink.classList.contains(`header__link`);
		if (this.isInHeader) {
			this.mainLink = hashLink;
		} else {
			this.mainLink = document.querySelector(`.header__link[href="${this.href}"]`) || null;
		}
		if (this.mainLink && window.location.hash === this.href) {
			this.mainLink.classList.add(`header__link--active`);
		}

		this._afterScrollCallback = this._afterScrollCallback.bind(this);

		hashLink.addEventListener(`click`, (evt) => {
			evt.preventDefault();
			scrollToHash({
				callback: () => applyAll(this._hashLinks, this._afterScrollCallback),
				hash: this.hash,
				payloadNode: null
			});
		});
	}

	_afterScrollCallback(item) {
		item.classList.remove(`header__link--active`);
		if (this.mainLink && item === this._hashLink) {
			this.mainLink.classList.add(`header__link--active`);
		}
	}
}
