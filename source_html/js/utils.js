import { SCROLL_SPEED } from './const';

// Применение функции ко всем элементам DOM-коллекции
export const applyAll = (payload, cb) => {
	// Можно передать или готовую DOM-коллекцию, или селектор
	const nodeList = typeof payload === `string` ? document.querySelectorAll(payload) : payload;

	for (let i = 0; i < nodeList.length; i++) {
		cb(nodeList[i], i, nodeList);
	}
};

export const applyClass = (Class, payload, addition = null) => {
	applyAll(payload, (item) => new Class(item, addition));
};

// Выводит число с ведущим нулём
export const formatWithLead0 = (num) => `${num < 10 ? 0 : ``}${num}`;

// Форматирует с сохранением только цифр
export const numberize = (str) => str.replace(/[^0-9]/g, ``);

// Форматирует как телефон для ссылки (с сохранением только цифр)
export const formatTel = (str) => str.replace(/[^0-9+]/g, ``);

export const getScrollBarWidth = () => {
	let scrollbarWidth = 0;

	// Создание временного элемента с прокруткой
	const div = document.createElement(`div`);
	div.style.overflowY = `scroll`;
	div.style.width = `50px`;
	div.style.height = `50px`;
	div.style.visibility = `hidden`;
	document.body.appendChild(div);

	scrollbarWidth = div.offsetWidth - div.clientWidth;
	document.body.removeChild(div);
	return scrollbarWidth;
};

export const getTargetPath = (target) => {
	const path = [];

	while (target) {
		path.push(target);
		target = target.parentElement;
	}
	return path;
};

// Заменяет в строке вхождение вида {{something}} на элемент массива data
// для полного преобразования число элементов должно быть равно числу вхождений
export const renderTemplate = (str, data) => {
	let i = -1;
	return str.replace(/{{.*?}}/g, (match) => {
		i++;
		return data[i] || match;
	});
};

// Плавная прокрутка к селектору по id
export const scrollToHash = ({ hash, speed = SCROLL_SPEED, payloadNode = null, callback = null }) => {
	// Если DOM-узел не передан, то подразумевается, что его можно найти по hash
	const node = payloadNode || document.getElementById(hash) || document.documentElement;

	const offset = window.pageYOffset;
	const distance = node.getBoundingClientRect().top;

	let start = null;
	const step = (time) => {
		start = start || time;
		const progress = (time - start) * speed;


		let scrollPos = Math.min(offset + progress, offset + distance);
		if (distance < 0) {
			scrollPos = Math.max(offset - progress, offset + distance);
		}
		window.scrollTo(0, scrollPos);

		if (scrollPos === offset + distance) {
			location.hash = `#${hash}`;
			if (callback) {
				callback();
			}
		} else {
			requestAnimationFrame(step);
		}

		return true;
	};

	requestAnimationFrame(step);
};

// Возвращает строковое представление числа с отделением тысячных разрядов пробелом
export const splitNumByGroups = (num) => num.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, `$1,`);
