import { DESKTOP_WIDTH, TOGGLE_DELAY } from '../const';
import { applyAll } from '../utils';

export default class Header {
	constructor(header) {
		this._header = header;
		this._menu = header.querySelector(`.header__menu`);
		this._links = header.querySelectorAll(`.header__link`);
		this._toggler = header.querySelector(`.header__toggler`);
		this._top = header.querySelector(`.header__top`);

		// Открыто ли мобильное меню
		this._openFlag = false;

		if (this._toggler) {
			this._fixMenu = this._fixMenu.bind(this);
			setTimeout(this._fixMenu, TOGGLE_DELAY);

			this._toggler.addEventListener(`click`, () => {
				this._toggler.classList.toggle(`header__toggler--closer`);

				if (this._toggler.classList.contains(`header__toggler--closer`)) {
					this._toggler.classList.remove(`header__toggler--opener`);
					this._menu.classList.add(`header__menu--opened`);
					this._menu.classList.remove(`header__menu--closed`);
					this._openFlag = true;
					this._fixMenu();
				} else {
					this._menu.classList.add(`header__menu--closed`);
					setTimeout(() => {
						this._menu.classList.remove(`header__menu--opened`);
						this._toggler.classList.add(`header__toggler--opener`);
						this._openFlag = false;
						this._fixMenu();
					}, TOGGLE_DELAY);
				}
			});

			window.addEventListener(`scroll`, this._fixMenu);
			window.addEventListener(`resize`, this._fixMenu);
		}

		applyAll(this._links, (link) => {
			if (link.getAttribute(`href`) === window.location.pathname) {
				link.removeAttribute(`href`);
			} else if (this._toggler) {
				link.addEventListener(`click`, () => this._toggler.click());
			}
		});
	}

	get _offset() {
		return this._top ? this._top.clientHeight : -1;
	}

	_fixMenu() {
		if (window.innerWidth >= DESKTOP_WIDTH) {
			this._openFlag = false;
		} else if (this._toggler.classList.contains(`header__toggler--closer`)) {
			this._openFlag = true;
		}

		if (window.pageYOffset > this._offset || this._openFlag) {
			this._header.style.top = `-${this._offset + 1}px`;
			this._header.classList.add(`header--fixed`);
		} else if (!this._openFlag) {
			this._header.style.top = `0`;
			this._header.classList.remove(`header--fixed`);
		}
	}
}
