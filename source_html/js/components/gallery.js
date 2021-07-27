import Popup from './popup';

export default class Gallery extends Popup {
	_initHandler(popup) {
		this._inner.src = popup.querySelector(`img`).src;
		this._textNode = popup.parentNode.querySelector(`p`);
		this._inner.alt = this._textNode ? this._textNode.textContent : ``;
	}
}
