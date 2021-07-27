export default class Switchable {
	constructor(el) {
		this._el = el;
		this._toggler = el.querySelector(`.switchable__toggler`);

		this._clickHandler = this._clickHandler.bind(this);

		this._toggler.addEventListener(`click`, this._clickHandler);
	}

	_clickHandler() {
		this._el.classList.toggle(`switchable_open`);
	}
}
