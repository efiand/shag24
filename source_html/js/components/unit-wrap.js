const getTemplate = ({ id = null, name, description = `` } = {}) => {
	if (!id) {
		return;
	}

	const descriptionTpl = description ? `
		<div class="center people-list__description">
			${description}
		</div>
	` : ``;
	return `
		<img alt="Фото: ${name}" src="/media/people/${id}.jpg?version=${window.VERSION}">
		<h3 class="people-list__heading">${name}</h3>
		${descriptionTpl}
	`;
};

export default class UnitWrap {
	constructor(wrap) {
		this._wrap = wrap;
		this._unitNumber = Number(this._wrap.textContent.replace(/\D/g, ``));
	}

	getNumber() {
		return this._unitNumber;
	}

	init(unit) {
		this._wrap.classList.add(`people-list`);
		this._wrap.innerHTML = getTemplate(unit);
		this._wrap.classList.remove(`unit-wrap`);
	}
}
