import { SITE_URL, URL } from '../const';

const CONTENT = {
	description: document.querySelector(`meta[name="description"]`).getAttribute(`content`),
	title: document.querySelector(`title`).textContent,
	url: URL
};

const ogImageBlock = document.querySelector(`meta[property="og:image"]`);
if (ogImageBlock) {
	CONTENT.image = SITE_URL + ogImageBlock.getAttribute(`content`);
}

const THEME = {
	copy: `hidden`,
	lang: `ru`,
	services: `vkontakte,facebook,odnoklassniki,telegram`
};

export default class YaShare {
	constructor(container) {
		if (window.Ya && window.Ya.share2) {
			this._share = window.Ya.share2(container, {
				content: CONTENT,
				hooks: {
					// Действие по нажатию
					// onshare: (name) => {
					//   window.ajaxHandler({
					//     mode: `share`,
					//     fields: {
					//       topic: `Нажата кнопка Поделиться: ${name}`
					//     }
					//   });
					// }
				},
				theme: THEME
			});
		}
	}
}
