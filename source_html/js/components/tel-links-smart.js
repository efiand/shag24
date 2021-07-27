import { numberize } from '../utils';

export default class TelLinkSmart {
	constructor(container) {
		this._container = container;

		this._tel = container.textContent.trim();
		this._number = numberize(this._tel);

		this._container.classList.add(`tel-links`);
		this._container.innerHTML = this._getTemplate();
	}

	_getTemplate() {
		return `
			<a class="tel-links__icon" href="//wa.me/${this._number}"
				target="_blank" title="Написать в&nbsp;WhatsApp">
				<svg aria-hidden="true">
					<use xlink:href="#whatsapp">
					</use>
				</svg>
			</a>
			<a class="tel-links__icon" href="viber://chat?number=${this._number}"
				target="_blank" title="Написать в&nbsp;Viber">
				<svg aria-hidden="true">
					<use xlink:href="#viber">
					</use>
				</svg>
			</a>
			<a class="tel-links__icon" href="//t.me/joinchat/${this._number}"
				target="_blank" title="Написать в&nbsp;Telegram">
				<svg aria-hidden="true">
					<use xlink:href="#telegram">
					</use>
				</svg>
			</a>
			<a class="tel-links__link" href="tel:+${this._number}" title="Позвонить">
				${this._tel}
			</a>
		`;
	}
}
