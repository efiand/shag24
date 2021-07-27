export const DESKTOP_WIDTH = 1280;
export const MIN_DELAY = 33;
export const TOGGLE_DELAY = 600;
export const PREPAYMENT_DELAY = 3000;
export const ESC_CODE = 27;
export const SCROLL_SPEED = 3;
export const SITE_URL = `${window.location.protocol}//${window.location.host}`;
export const SUCCESS_CODE = 200;
export const URL = SITE_URL + window.location.pathname;

const currentDate = new Date();
const currentMonthVal = currentDate.getMonth() + 1;
const currentDayVal = currentDate.getDate();
export const CURRENT_DAY = `${currentDayVal < 10 ? 0 : ``}${currentDayVal}`;
export const CURRENT_MONTH = `${currentMonthVal < 10 ? 0 : ``}${currentMonthVal}`;
export const CURRENT_YEAR = currentDate.getFullYear();
export const DAYS = [...Array(31)].map((item, i) => i + 1);
export const MONTHS = [
	`январь`,
	`февраль`,
	`март`,
	`апрель`,
	`май`,
	`июнь`,
	`июль`,
	`август`,
	`сентябрь`,
	`октябрь`,
	`ноябрь`,
	`декабрь`
];

export const ERRORS = {
	connect: `Ошибка соединения. Попробуйте ещё раз.`,
	status: `Ошибка {{ status }}: {{ statusText }}`,
	timeout: `Запрос не успел выполниться за {{ timeout }} мс. Попробуйте ещё раз.`
};

export const IE_FLAG = (() => {
	if ((/Trident/).test(navigator.userAgent)) {
		document.body.classList.add(`is-ie`);
		return true;
	}
	return false;
})();

export const STORAGE_FLAG = (() => {
	try {
		localStorage.getItem(`test`);
	} catch (error) {
		return false;
	}
	return true;
})();
