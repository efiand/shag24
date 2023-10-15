import { DOMAIN_ZONE } from './const';
export { formatWithLead0, numberize, formatTel } from '../../source_html/js/utils';

export const getUrl = ({ site, page }) => `${site}${DOMAIN_ZONE}/${page}`;

export const copyToClipboard = (str) => {
	try {
		navigator.clipboard.writeText(str);
	} catch (err) {
		alert(`Ваш браузер не поддерживает копирование по клику`);
	}
};

export const backupDb = async () => {
	await fetch(`/api/?payload=${JSON.stringify({
		page: `/backup`
	})}`);
};
