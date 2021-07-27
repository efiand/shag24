<script>
	import Icon from 'svelte-icon';
	import SaveIcon from '../../source_html/sprite/save.svg';
	import { ADMINZONE, DOMAIN_ZONE, TOGGLE_DELAY } from '../js/const';
	import { createEventDispatcher } from 'svelte';
	import tinymce from 'tinymce';

	export let value = ``;
	export let siteAlias = ``;

	const { VERSION = `` } = window;
	const id = new Date().getTime() + Math.floor((Math.random() + 1) * TOGGLE_DELAY);
	const hashId = `#${id}`;
	const dispatch = createEventDispatcher();
	const closeTitle = `Закрыть без изменений`;
	let isOpened = false;
	let offset = 0;
	let initialValue = value;

	function toggleHandler(evt, saveFlag = true) {
		evt.currentTarget.blur();

		isOpened = !isOpened;
		if (isOpened) {
			offset = window.pageYOffset;
			document.body.classList.add(`popup-mode`);

			tinymce.init({
				body_class: `wysiwyg content ${siteAlias || ADMINZONE}`,
				branding: false,
				content_css: `//${ADMINZONE}${DOMAIN_ZONE}/css/style.min.css?version=${VERSION}`,
				extended_valid_elements: `svg[*],defs[*],g[*],mask[*],path[*],line[*],rect[*],circle[*],ellipse[*],polygon[*],polyline[*],linearGradient[*],radialGradient[*],stop[*],image[*],text[*],textPath[*],title[*],tspan[*],glyph[*],symbol[*],use[*]`,
				menubar: ``,
				object_resizing: false,
				toolbar: `undo redo | cut copy paste | styleselect | template code | h2 h3 numlist bullist clear | bold italic strikethrough charmap | alignnone aligncenter alignright indent outdent | link unlink image | removeformat`,
				paste_auto_cleanup_on_paste: true,
        paste_postprocess(plugin, args) {
					args.content = `<p>${args.content.replace(`<br />`, `</p><p>`)}</p>`;
				},
				plugins: `template code link charmap lists image`,
				relative_urls: false,
  			resize: false,
				selector: hashId,
				style_formats: [
					{
						title: `Главный цвет выделения`,
						inline: `span`,
						classes: `color-primary`
					},
					{
						title: `Дополнительный цвет выделения`,
						inline: `span`,
						classes: `color-secondary`
					},
					{
						title: `Белый цвет (виден не в админке)`,
						inline: `span`,
						classes: `white`
					},
					{
						title: `Телефон / telegram / viber / watsapp`,
						inline: `span`,
						classes: `tel-links-smart`
					}
				],
				templates: [
					{
						title: `Центрируемый блок`,
						description: `Центрируется сам блок, текст в нем не выравнивается`,
						content: `<div class="center"><p></p></div>`
					},
					{
						title: `Выделение`,
						description: `Немного увеличенный текст си${ADMINZONE === `shag24` ? `ренево` : `не`}го цвета`,
						content: `<p class="alter big"></p>`
					},
					{
						title: `Двухколоночная сетка`,
						description: ``,
						content: `<div class="grid"><div class="col"><p></p></div><div class="col"><p></p></div></div>`
					},
					{
						title: `Трехколоночная сетка`,
						description: ``,
						content: `<div class="grid"><div class="col-3"><p></p></div><div class="col-3"><p></p></div><div class="col-3"><p></p></div></div>`
					},
					{
						title: `Ссылка-кнопка`,
						description: ``,
						content: `<a class="button" href="#nowhere">Подробнее</a>`
					},
					{
						title: `Кнопка открытия формы`,
						description: `Позволяет подключать произвольную форму по номеру. Номер формы меняется через исходный код`,
						content: `<button class="button" data-open="form-59">Подать заявку</button>`
					},
					{
						title: `Кнопка открытия платежной формы`,
						description: `Позволяет подключать произвольную платежную форму по номеру. Добавляется плаНомер формы меняется через исходный код`,
						content: `<button class="button" data-open="form-999"><svg aria-hidden="true" height="22" width="22"><use xlink:href="#payment".></svg>&nbsp; 5 000&nbsp;рублей</button>`
					},
					{
						title: `Плейсхолдер кнопки`,
						description: `Стилизуется под кнопку только в админке, нужен для визуализации вставки кнопок открытия форм`,
						content: `<div class="button-wrap"></div>`
					},
					{
						title: `Медиа-содержимое`,
						description: `Подходит для вставки youtube-видео, интерактивных карт`,
						content: `<div class="media"><iframe src="#" allowfullscreen></iframe></div>`
					},
					{
						title: `Плейсхолдер медиасодержимого`,
						description: `Стилизуется только в админке для визуализации места вставки медиаматериала из админки`,
						content: `<div class="media-wrap"></div>`
					},
					{
						title: `Плейсхолдер человека`,
						description: `На сайте на этом месте появится фото и описание человека по его номеру`,
						content: `<div class="unit-wrap"></div>`
					}
				]
			});
		} else {
			document.body.classList.remove(`popup-mode`);
			setTimeout(() => window.scrollTo(0, offset), 100);
			value = saveFlag ? tinymce.activeEditor.getContent() : initialValue;
			if (value) {
				value = value.replace(/(color|font.*?):.*?;\s*/g, '');
				value = value.replace(/style="\s"\s/g, '');
				value = value.replace(/data-sheets.*?=".*?"\s*/g, '');
				value = value.replace(/<span\s*>(.*?)<\/span>/g, (match, p) => p);
			}
			dispatch(`change`, { value, saveFlag });
			tinymce.remove(hashId);
		}
	}
</script>

<span class="editor {isOpened ? `mce-opened` : ``}">
	<button class="link save-link" type="button" on:click={(evt) => toggleHandler(evt)}>
		{#if isOpened}
			<span class="sr-only">Сохранить изменения</span>
			<Icon data={SaveIcon}/>
		{:else}
			Открыть редактор
		{/if}
	</button>
	{#if isOpened}
		<button class="link esc-link" type="button" title={closeTitle} on:click={(evt) => toggleHandler(evt, false)}>
			<span class="sr-only">{closeTitle}</span>
		</button>
	{/if}
	<textarea class="hidden" {id} bind:value tabindex="-1"/>
</span>

<style lang="less">
	.editor {
		position: relative;
	}

	:global(.editor.mce-opened) {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		z-index: 2;
		color: var(--color-white);

		:global(.tox) {
			height: 100vh !important;
		}

		.save-link,
		.esc-link {
			position: absolute;
			z-index: 3;
			font-size: initial !important;
		}

		.save-link {
			top: 8px;
			right: 36px;

			:global(svg) {
				margin-top: 2px;
			}
		}

		.esc-link {
			top: 10px;
			right: 10px;
			width: 20px;
			height: 20px;

			&::before,
			&::after {
				content: "";
				position: absolute;
				top: 50%;
				left: 50%;
				width: 24px;
				height: 2px;
				background-color: currentColor;
			}

			&::before {
				transform: translate(-50%, -50%) rotate(45deg);
			}

			&::after {
				transform: translate(-50%, -50%) rotate(-45deg);
			}
		}
	}

	:global(.link:hover) {
		color: var(--color-blacky) !important;

		.khara & {
			color: var(--color-blacky) !important;
		}

		.shag24 & {
			color: var(--color-blacky) !important;
		}
	}

	:global(.link:focus) {
		color: var(--color-primary) !important;
	}

	:global(.tox-toolbar__primary) {
		padding-right: 64px !important;
	}
</style>
