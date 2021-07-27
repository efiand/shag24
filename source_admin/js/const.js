export * from '../../source_html/js/const';

export const TICKETS_LIMIT = 200;

export const CLEAR_TITLE = `Очистить поле`;

export const ADMINZONES = [`khara`, `shag24`];

export const ADMINZONE = (({ hostname }) => {
	let currentZone = ``;
	ADMINZONES.forEach((alias) => {
		if (hostname.indexOf(alias) !== -1) {
			currentZone = alias;
		}
	});
	return currentZone;
})(window.location);

export const DOMAIN_ZONE = (({ hostname }) => {
	return `${hostname.indexOf(`-ru`) === -1 ? `.` : `-`}ru`;
})(window.location);

export const MEDIA_DIR = `//${ADMINZONE}${DOMAIN_ZONE}/media/${ADMINZONE}/`;
