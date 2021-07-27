import { ERRORS, IE_FLAG, MIN_DELAY, PREPAYMENT_DELAY, SUCCESS_CODE, TOGGLE_DELAY } from '../const';
import { applyAll, renderTemplate } from '../utils';
import Popup from './popup';

export default class TicketForm extends Popup {
	constructor(form) {
		super(form);

		this._form = form;
		this._id = form.dataset.id;
		this._dataFields = form.querySelectorAll(`[name]`);
		this._fieldset = form.querySelector(`.form__fieldset`);

		this._submitControl = this._fieldset.querySelector(`.form__submit`);
		this._submitHandler = this._submitHandler.bind(this);
		this._submitControl.addEventListener(`click`, this._submitHandler);

		this._preloader = form.querySelector(`.form__preloader`);
		this._statusBlock = form.querySelector(`.form__status-block`);
		this._statusString = this._statusBlock.querySelector(`.form__status`);

		// Кнопка ОК
		this._statusCloser = this._statusBlock.querySelector(`.form__status-closer`);
		this._statusCloser.addEventListener(`click`, () => this._clearHandler());

		this._res = null;
		this._xhr = new XMLHttpRequest();
		this._xhr.addEventListener(`load`, () => this._loadHandler());
		this._xhr.addEventListener(`error`, () => this._errorHandler());
		this._xhr.addEventListener(`timeout`, () => this._timeoutHandler());

		this._hasPayment = form.getAttribute(`name`) === `TinkoffPayForm`;
		if (this._hasPayment) {
			this._orderField = form.querySelector(`[name="order"]`);
			this._amountField = form.querySelector(`[name="amount"]`);
		}

		// Обработка формы с привязанным тестом
		this._testField = form.querySelector(`[name="test"]`);
		if (this._testField) {
			this._initTest();
		}
	}

	_clearHandler() {
		setTimeout(() => {
			this._form.classList.remove(`form--validable`);
			this._submitControl.disabled = false;
			this._statusBlock.classList.add(`hidden`);
			this._fieldset.classList.remove(`hidden`);
		}, TOGGLE_DELAY);
	}

	_collectFields(mode) {
		applyAll(this._dataFields, (field) => {
			if (field.dataset.mode && field.dataset.mode !== mode) {
				field.disabled = true;
			}
		});
		return new FormData(this._form);
	}

	_errorHandler() {
		this._res = {
			error: true,
			status: ERRORS.connect
		};
		this._resHandler();
	}

	_initTest() {
		let testValue = 0;
		applyAll(this._form.querySelectorAll(`[name="test-q"]`), (checker) => {
			if (checker.checked) {
				testValue++;
			}

			checker.addEventListener(`change`, (evt) => {
				if (evt.currentTarget.checked) {
					testValue++;
				} else {
					testValue--;
				}
				this._testField.value = testValue;
			});
		});
		this._testField.value = testValue;
	}

	_loadHandler() {
		if (this._xhr.status === SUCCESS_CODE) {
			this._res = IE_FLAG ? JSON.parse(this._xhr.response) : this._xhr.response;
		} else {
			this._res = {
				error: true,
				status: renderTemplate(ERRORS.status, [this._xhr.status, this._xhr.statusText])
			};
		}
		this._resHandler();
	}

	_resHandler() {
		// Если форма платежная и в ходе обработки запроса стратегия не изменилась
		if (this._hasPayment && this._res.do_pay) {
			// Прячем кнопку ОК
			this._statusCloser.classList.add(`hidden`);

			// Смена стоимости для передачи банку (может поменяться по скидке)
			this._amountField.value = this._res.price;

			// Получение страницы для редиректа на оплату
			setTimeout(() => {
				// Собираем данные для платежа
				this._collectFields(`pay`);

				// Функция из внешнего скрипта
				pay(this._form); // eslint-disable-line
			}, PREPAYMENT_DELAY);
		}

		this._statusString.innerHTML = this._res.status;
		this._preloader.classList.add(`hidden`);
		this._statusBlock.classList.remove(`hidden`);
	}

	_submitHandler(evt) {
		evt.preventDefault();

		this._form.classList.remove(`form--validable`);
		setTimeout(() => {
			this._form.classList.add(`form--validable`);
		}, MIN_DELAY);

		if (!this._form.checkValidity()) {
			return;
		}

		this._submitControl.disabled = true;
		this._preloader.classList.remove(`hidden`);
		this._fieldset.classList.add(`hidden`);

		if (this._orderField) {
			this._orderField.value = Number(this._orderField.value) + Math.floor((Math.random() + 1) * MIN_DELAY);
		}

		this._xhr.open(`POST`, `/tickets`);
		this._xhr.responseType = `json`;
		this._xhr.send(this._collectFields(`ticket`));
	}

	_timeoutHandler() {
		this._res = {
			error: true,
			status: renderTemplate(ERRORS.timeout, [this._xhr.timeout])
		};
		this._resHandler();
	}
}
