import { STORAGE_FLAG } from '../const';

export default class Field {
	constructor(inputGroup) {
		this._input = inputGroup.querySelector(`.field__input`);

		if (this._input.type === `tel`) {
			this._input.addEventListener(`input`, this._maskPhone.bind(this));
		}

		if (STORAGE_FLAG) {
			this._input.value = this._input.value || localStorage.getItem(this._input.name);

			this._input.addEventListener(`input`, () => {
				if (this._input.checkValidity()) {
					localStorage.setItem(this._input.name, this._input.value);
				}
			});
		}
	}

	// Маскирование по +7 777 777 77 77
	_maskPhone() {
		this._input.value = this._input.value.replace(/[^+()\- \d]/g, ``);
	}
}
