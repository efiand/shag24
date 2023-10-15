import { getTargetPath } from '../utils';

export default class Chooser {
	constructor(chooser) {
		this._chooser = chooser;
		this._toggler = chooser.querySelector(`.chooser__checker`);

		this._toggler.addEventListener(`click`, () => {
			chooser.classList.toggle(`is-open`);
		});

		document.addEventListener(`click`, (evt) => {
			const path = evt.target.path || getTargetPath(evt.target);

			if (path.indexOf(chooser) === -1) {
				this._close();
			}
		});
	}

	_close() {
		this._chooser.classList.remove(`is-open`);
	}
}
