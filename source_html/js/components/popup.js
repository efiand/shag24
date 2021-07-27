import { applyAll, getScrollBarWidth, getTargetPath } from '../utils';
import { ESC_CODE } from '../const';

const FOCUSABLE_SELECTORS = `a[href], input:not([type="hidden"]):not(:disabled), button:not(:disabled)`;

export default class Popup {
	constructor(popup) {
		this._popup = popup;
		this._alias = popup.dataset.id;
		this._closers = this._popup.querySelectorAll(`.popup__closer`);
		this._inners = this._popup.querySelectorAll(`.popup__inner`);
		this._cross = this._popup.querySelector(`.popup__cross`);
		this._firstFocusable = this._popup.querySelector(FOCUSABLE_SELECTORS);
		this._resizers = this._popup.querySelectorAll(`.popup__resizer`);

		this._keyCloseHandler = this._keyCloseHandler.bind(this);
		this._focusHandler = this._focusHandler.bind(this);
		this._openHandler = this._openHandler.bind(this);
		this._overflowHandler = this._overflowHandler.bind(this);

		// До прогрузки скрипта кнопки открытия могут быть скрыты
		applyAll(`[data-open="${this._alias}"]`, (opener) => {
			opener.classList.remove(`hidden`);
		});

		this._setListeners();
	}

	get _inner() {
		return this._popup.querySelector(`.popup__inner:not(.hidden)`);
	}

	_setListeners() {
		document.addEventListener(`click`, (evt) => {
			const { open } = evt.target.dataset;
			if (open && open === this._alias) {
				this._openHandler();

				if (this._initHandler) {
					this._initHandler(evt.target);
				}
				evt.target.blur();
			}
		});

		applyAll(this._resizers, (resizer) => {
			resizer.addEventListener(`click`, () => setTimeout(this._overflowHandler, 0));
		});

		applyAll(this._closers, (closer) => {
			closer.addEventListener(`click`, () => this._closeHandler());
		});

		// Закрытие по клику вне области содержимого
		this._popup.addEventListener(`click`, (evt) => {
			const path = evt.target.path || getTargetPath(evt.target);

			let innersFlag = false;
			applyAll(this._inners, (inner) => {
				if (path.indexOf(inner) > -1) {
					innersFlag = true;
				}
			});
			if (!innersFlag) {
				this._closeHandler();
			}
		});
	}

	_closeHandler() {
		this._cross.focus();
		this._popup.classList.remove(`popup--ready`);
		document.body.classList.remove(`popup-mode`);
		window.scrollTo(0, this._offset);

		for (let i = 0; i < this.fullWidths.length; i++) {
			this.fullWidths[i].style.width = `100%`;
		}

		document.removeEventListener(`keydown`, this._keyCloseHandler);
		window.removeEventListener(`resize`, this._overflowHandler);
		document.removeEventListener(`focus`, this._focusHandler);
	}

	// Защита модального блока от потери фокуса
	_focusHandler(evt) {
		const path = evt.path || getTargetPath(evt.target);

		if (path.indexOf(this._form) === -1) {
			this._firstFocusable.focus();
		}
	}

	// Закрытие модального окна по Esc
	_keyCloseHandler(evt) {
		if (evt.keyCode === ESC_CODE) {
			this._closeHandler();
		}
	}

	_openHandler() {
		const offset = window.pageYOffset;
		this._offset = offset;
		setTimeout(this._overflowHandler, 0);

		this._popup.classList.add(`popup--ready`);
		document.body.classList.add(`popup-mode`);

		this.fullWidths = document.querySelectorAll(`.js-fullwidth`);
		for (let i = 0; i < this.fullWidths.length; i++) {
			this.fullWidths[i].style.width = `calc(100% - ${getScrollBarWidth()}px)`;
		}

		document.body.style.top = `-${offset}px`;

		document.addEventListener(`keydown`, this._keyCloseHandler);
		window.addEventListener(`resize`, this._overflowHandler);
		document.addEventListener(`focus`, this._focusHandler, true);
	}

	_overflowHandler() {
		if (this._inner.clientHeight > window.innerHeight) {
			this._inner.classList.add(`popup__inner--overflowed`);
		} else {
			this._inner.classList.remove(`popup__inner--overflowed`);
		}

		const innerBox = this._inner.getBoundingClientRect();
		const closerBox = this._cross.getBoundingClientRect();

		if (innerBox.top < closerBox.bottom && innerBox.right > closerBox.left) {
			this._cross.classList.add(`popup__cross--on-inner`);
		} else {
			this._cross.classList.remove(`popup__cross--on-inner`);
		}
	}
}
