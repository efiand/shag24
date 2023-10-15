<script>
	import { ADMINZONE, DOMAIN_ZONE } from '../js/const';
	import { copyToClipboard } from '../js/utils';
	import Icon from 'svelte-icon';
	import PaymentIcon from '../../source_html/sprite/payment.svg';
	import CopyIcon from '../../source_html/sprite/copy.svg';
	import Preloader from '../components/preloader.svelte';
	import TelLinks from '../components/tel-links.svelte';
	import Del from '../components/del.svelte';
	import Editor from '../components/editor.svelte';
	import MCE from '../components/mce.svelte';
	import { link, push } from 'svelte-spa-router';

	export let params = {};

	let pageId = params.id;
	let clonePageId = pageId;
	let cloneMode = false;
	let changeCloneParams = false;
	let fragments = [];
	let page = {};
	let pages = [];
	let sites = [];
	let forms = {};
	let events = {};
	let people = {};
	let formNumbers;
	let eventNumbers;
	let personNumbers;
	let createFragmentMode = false;
	let liteMode = true;
	let defaultForm = ~+(ADMINZONE === `khara`);

	const wysiwygTypes = [`visual`, `visual-auto`, `section`];
	const pageform = {
		opened: false,
		pale: false
	};

	const fragmentStructure = {
		choosed: true,
		id: -1,
		sort_order: 1,
		alias: null,
		page_id: pageId,
		value: ``,
		description: `Новый фрагмент`,
		type: `text`,
		unsaved: true,
		is_published: true
	};

	async function getPage() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/page`, pageId, getPages: false
		})}`);
		if (res.ok) {
			const data = await res.json();
			if (data.page) {
				page = data.page;
				fragments = data.fragments;
				clonePageId = pageId;
			} else {
				// если страница с несуществующим номером или из другой админзоны, переходим на список
				push(`/pages`);
			}
		}
		createFragmentMode = false;
	}
	let asyncPage = getPage();

	async function getPages() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/page`, pageId, getPages: true
		})}`);
		if (res.ok) {
			const data = await res.json();
			pages = data.pages;
			people = data.people;
			sites = data.sites;
			forms = data.forms;
			events = data.events;
			formNumbers = Object.keys(forms);
			eventNumbers = Object.keys(events);
			personNumbers = Object.keys(people);
		}
	}
	let asyncPages = getPages();

	async function savePage() {
		pageform.pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({ page: `/page-edit`, pagedata: page })
		});
		if (res.ok) {
			const data = await res.json();
			asyncPages = getPages();
			pageform.pale = false;
			page.site_alias = data.site_alias;
		}
	}

	async function clonePage() {
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({
				page: `/page-clone`,
				pageId,
				clonePageId,
				changeCloneParams
			})
		});
		if (res.ok) {
			cloneMode = false;
			asyncPage = getPage();
		}
	}

	async function saveFragment(id) {
		const i = fragments.findIndex((fragment) => fragment.id === id);
		fragments[i].pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({
				page: `/fragment-edit`,
				pageId,
				fragment: fragments[i]
			})
		});
		if (res.ok) {
			asyncPage = getPage();
		}
	}

	function changeTypeHandler(id) {
		const i = fragments.findIndex((fragment) => fragment.id === id);
		const fragment = fragments[i];

		if (fragment.type === `plugin`) {
			fragments[i].value = ``;
		} else if (fragment.type === `youtube`) {
			fragments[i].value = fragment.value.replace(`.be/`, `be.com/embed/`);
		} else if (fragment.type === `copy`) {
			fragment.alias = `_COPY`;
		}
	}

	function insertFragment() {
		fragments = [JSON.parse(JSON.stringify(fragmentStructure)), ...fragments];
		fragments.forEach((item, i) => item.choosed = !i);
		createFragmentMode = true;
		fragmentStructure.id--; // Чтобы ключи были уникальны
	}

	function changePage() {
		pageId = pageId;
		push(`/pages/${pageId}`);
		asyncPage = getPage();
	}
</script>

<svelte:head>
	<title>Управление страницей</title>
</svelte:head>

<section class="section">
	{#await asyncPage}
		<Preloader/>
		{:then res}
		<div class="filter">
			<label class="field page-choose">
				<select class="field__input" bind:value={pageId} on:change={changePage}>
					{#each pages as item}
						<option value={item.id}>{item.site_alias}.ru{item.alias}</option>
					{/each}
				</select>
				<span class="field__label">Страница</span>
			</label>
			<a class="link filter__endlink" href="//{page.site_alias}{DOMAIN_ZONE}{page.alias}" target="_blank">Просмотр страницы</a>
		</div>

		<div>
			<div class="item {pageform.pale ? `pale` : ``} {pageform.opened ? `item--choosed` : ``}">
				<p>
					<button class="link" type="button" on:click={() => {
						fragments.forEach((item) => item.choosed = false);
						pageform.opened = !pageform.opened;
					}}>
						Параметры страницы
					</button>
				</p>
				{#if pageform.opened}
					<form class="item__form" on:submit|preventDefault={savePage}>
						<div class="cols">
							<label class="field cols__quart">
								<input class="field__input" bind:value={page.alias}>
								<span class="field__label">Адрес страницы</span>
							</label>
							<label class="field cols__subtotal">
								<input class="field__input" bind:value={page.title}>
								<span class="field__label">Название страницы</span>
							</label>
							<label class="field cols__quart">
								<select class="field__input" bind:value={page.site_id}>
									{#each sites as item}
										<option value={item.id}>{item.alias}.ru</option>
									{/each}
								</select>
								<span class="field__label">Сайт</span>
							</label>
							<label class="field cols__subtotal">
								<input class="field__input" maxlength="500" bind:value={page.description}>
								<span class="field__label">Описание страницы (если оно отличается от того, что задано для <a href="/sites" use:link>сайта</a>)</span>
							</label>
							<label class="field cols__half">
								<input class="field__input" bind:value={page.receivers}>
								<span class="field__label">E-mail получателей уведомлений о заявках через запятую</span>
							</label>
							<label class="checker cols__quart item__checker">
								<input class="sr-only" type="checkbox" bind:checked={page.search_flag}>
								<span class="checker__label">Страница участвует в поиске</span>
							</label>
							<div class="item__submit cols__quart">
								<button class="button" type="submit">Сохранить</button>
							</div>
						</div>
					</form>
				{/if}
			</div>
		</div>

		<div class="fragments-header">
			<h2>Фрагменты страницы</h2>
			{#if !createFragmentMode && !liteMode}
				<button class="new-fragment link" on:click={insertFragment}>Новый фрагмент</button>
			{/if}
			<div class="content">
				<p>Эти параметры встраиваются в определенное программой сайта место в шапке или подвале. Кроме того, можно создавать произвольные фрагменты для включения в основную часть страницы (см. Параметры страницы).</p>
			</div>
			<label class="checker item__checker">
				<input class="sr-only" type="checkbox" bind:checked={liteMode}>
				<span class="checker__label">Показать только визуально редактируемые</span>
			</label>
		</div>
		<div>
			{#each fragments as fragment}
				{#if !fragment.del && !(liteMode && !~wysiwygTypes.indexOf(fragment.type))}
					<div class="fragment item {fragment.pale ? `pale` : ``} {fragment.choosed ? `item--choosed` : ``}">
						<p class="fragment-title">
							<small class="item__number">{fragment.sort_order}</small>
							{#if fragment.choosed}
								{#if !fragment.unsaved}
									<small>
										<span class="item__number">Фрагмент № {fragment.id}</span>
										<a class="link" href="/fragments/{fragment.id}" target="_blank" use:link>Открыть в новой вкладке</a>
									</small>
								{/if}
							{:else}
								<button class="item__title link" type="button" on:click={() => {
									fragments.forEach((item) => item.choosed = false);
									fragment.choosed = !fragment.choosed;
								}}>
									{fragment.description}
									{#if fragment.unsaved}
										<span class="red">(не сохранено)</span>
									{/if}
								</button>
							{/if}
							{#if ~wysiwygTypes.indexOf(fragment.type)}
								<small class="mce-wrap">
									<MCE value={fragment.value} siteAlias={fragment.site} on:change={({ detail }) => {
										fragment.value = detail.value;
										saveFragment(fragment.id);
									}}/>
								</small>
							{/if}
						</p>
						<p class="item__del services">
							{#if fragment.alias}
								<span>
									{`{{ ${fragment.alias} }}`}
									<button class="link" title="Копировать имя фрагмента для вставки"
										on:click={copyToClipboard(`{{ ${fragment.alias} }}`)}>
										<Icon data={CopyIcon}/>
									</button>
								</span>
							{/if}
							{#if ~fragment.id && fragment.deletable}
								<Del dbtable="data_fragments" colvalue={fragment.id}
									on:del={() => fragment.del = true} on:start={() => fragment.pale = true}/>
							{:else}
								<div style="width: 20px"/>
							{/if}
						</p>
						{#if fragment.choosed}
							<form class="item__form" on:submit|preventDefault={saveFragment(fragment.id)}>
								<div class="cols">
									<label class="field cols__{~wysiwygTypes.indexOf(fragment.type) ? `sub` : ``}total">
										<input class="field__input" bind:value={fragment.description}>
										<span class="field__label">Описание фрагмента</span>
									</label>
									{#if ~wysiwygTypes.indexOf(fragment.type)}
										<label class="checker cols__quart item__checker">
											<input class="sr-only" type="checkbox" bind:checked={fragment.is_published}>
											<span class="checker__label">Фрагмент опубликован на странице</span>
										</label>
									{:else}
										<div class="field cols__total">
											{#if ~[`html`, `html-auto`, `json`].indexOf(fragment.type)}
												<Editor value={fragment.value} on:change={({ detail }) => fragment.value = detail.value}/>
											{:else if fragment.type === `ticket-button`}
												<select class="field__input" bind:value={fragment.value}>
													<option class="placeholder" value="">Выберите для данной админзоны</option>
													{#each formNumbers as n}
														<option value={n}>
															№ {n}. {forms[n].topic}
															{#if forms[n].description}
																&nbsp;({forms[n].description})
															{/if}
														</option>
													{/each}
												</select>
												{#if forms[fragment.value]}
													<div class="item__media">
														<button class="button {forms[fragment.value].attach ? `button--alter` : ``}" type="button">
															{#if forms[fragment.value].payment_target}
																<Icon data={PaymentIcon} size="20"/>&nbsp;
															{/if}
															{forms[fragment.value].opener_title}
														</button>
													</div>
												{/if}
											{:else if fragment.type === `event`}
												<select class="field__input" bind:value={fragment.value}>
													<option class="placeholder" value="">Выберите для данной админзоны</option>
													{#each eventNumbers as n}
														<option value={n}>
															№ {n}. &lt;{events[n].datetime_start}&gt;: {events[n].title}
														</option>
													{/each}
												</select>
											{:else if fragment.type === `person`}
												<select class="field__input" bind:value={fragment.value}>
													<option class="placeholder" value="">Выберите для данной админзоны</option>
													{#each personNumbers as n}
														<option value={n}>
															№ {n}. {people[n].name}
														</option>
													{/each}
												</select>
											{:else if fragment.type === `img`}
												<input class="field__input" bind:value={fragment.value} placeholder="Введите имя файла">
												{#if fragment.value}
													<div class="cols item__media">
														<div class="cols__quart">
															<img alt="" src="//{page.site_alias}.ru/media/{ADMINZONE}/{fragment.value}?version={window.VERSION}">
														</div>
													</div>
												{/if}
											{:else if fragment.type === `youtube`}
												<input class="field__input" bind:value={fragment.value} placeholder="Введите адрес видеоролика">
												{#if fragment.value}
													<div class="cols item__media">
														<div class="cols__quart">
															<div class="media">
																<iframe class="media__content" title="Видео" allowfullscreen
																	src={fragment.value.replace(`.be/`, `be.com/embed/`)}>
															</div>
														</div>
													</div>
												{/if}
											{:else if fragment.type === `tel-link`}
												<input class="field__input" type=tel bind:value={fragment.value} placeholder="Введите номер телефона">
												{#if fragment.value}
													<div class="item__media tel-links"><TelLinks tel={fragment.value}/></div>
												{/if}
											{:else if fragment.type === `plugin`}
												<p>Будет построено плагином с именем фрагмента</p>
											{:else if ~[`link`, `copy`].indexOf(fragment.type)}
												<input class="field__input" type="number" bind:value={fragment.value} placeholder="Введите номер другого фрагмента" required>
											{:else}
												<input class="field__input" bind:value={fragment.value}>
											{/if}
											<span class="field__label">Содержимое фрагмента</span>
										</div>
									{/if}
									<label class="field cols__quart">
										<input class="field__input" bind:value={fragment.alias} pattern={`[A-Z0-9_]{3,}`} required>
										<span class="field__label find__label--required">Кодовое имя фрагмента (A-Z, 0-9, _)</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" type="number" min="1" bind:value={fragment.sort_order} required>
										<span class="field__label">Порядок фрагмента</span>
									</label>
									<label class="field cols__quart">
										<select class="field__input" bind:value={fragment.type} on:change={changeTypeHandler(fragment.id)}>
											<option value="text">Строка</option>
											<option value="html">Шаблон (код со вставками)</option>
											<option value="html-auto">Шаблон автособираемый</option>
											<option value="visual">Визуальный</option>
											<option value="visual-auto">Визуальный автособираемый</option>
											<option value="section">Секция (визуальный с центровкой содержимого и якорем)</option>
											<option value="img">Картинка</option>
											<option value="youtube">Видео Youtube</option>
											<option value="ticket-button">Кнопка открытия формы (по номеру)</option>
											<option value="tel-link">Телефон, viber, whatsapp, telegram</option>
											<option value="person">Персона (по номеру)</option>
											<option value="event">Мероприятие (по номеру)</option>
											<option value="json">JSON (структура)</option>
											<option value="plugin">Плагин</option>
											<option value="link">Межстраничная ссылка на другой фрагмент (по номеру)</option>
											<option value="copy">Копия фрагмента (по номеру)</option>
										</select>
										<span class="field__label">Тип фрагмента</span>
									</label>
									<div class="item__submit cols__quart">
										<button class="button" type="submit">Сохранить</button>
									</div>
								</div>
							</form>
						{/if}
					</div>
				{/if}
			{/each}
		</div>

		{#if cloneMode}
			<h2 class="t-gap">Импорт информации с другой страницы</h2>
			<p><b>ВНИМАНИЕ!!!</b></p>
			<p class="b-gap">Фрагменты страницы-источника будут <b>добавлены</b> к существующим фрагментам. Дубли следует удалять вручную во избежание потери данных. Если страница должна быть <b>полностью</b> перезаписана - следует сначала удалить с неё все фрагменты, а потом клонировать в неё другую страницу.</p>
			<div class="filter">
				<label class="field page-choose">
					<select class="field__input" bind:value={clonePageId} on:change={clonePage}>
						{#each pages as item}
							<option value={item.id}>{item.site_alias}.ru{item.alias}</option>
						{/each}
					</select>
					<span class="field__label">Страница-источник</span>
				</label>
				<label class="checker item__checker">
					<input class="sr-only" type="checkbox" bind:checked={changeCloneParams}>
					<span class="checker__label">Заменить параметры текущей страницы на параметры источника</span>
				</label>
			</div>
		{:else}
			<p class="t-gap">
				<button class="link" on:click={()=> cloneMode = true}>Импорт информации с другой страницы</button>
			</p>
		{/if}
	{/await}
</section>

<style lang="less">
	@import "../../source_html/less/vars";

	.page-choose {
		@media @desktop {
			max-width: 500px;
		}
	}

	.fragments-header {
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
		margin: 2em 0 0.5em;

		.content {
			width: 100%;
			margin-bottom: 1em;
		}
	}

	.fragment-title {
		line-height: 25px;
	}

	.preview {
		height: 400px;
		background-color: var(--color-white);
		overflow-y: auto;
	}

	.services {
		display: flex;
		min-height: 25px;

		span {
			margin-right: 1em;

			@media @tablet {
				margin-right: 2em;
			}

			button {
				vertical-align: middle;
			}
		}
	}

	.tel-links {
		margin-bottom: 0.5em;
	}

	.field {
		.button {
			pointer-events: none;
		}
	}

	.mce-wrap {
		@media @mobile-only {
			display: block;
			margin-top: -0.5em;
		}

		@media @tablet {
			margin-left: 1em;
		}
	}

	.new-fragment {
		display: inline-block;
		vertical-align: baseline;
		margin: 1em 0;

		@media @tablet {
			margin: 0.5em 0;
		}
	}

	.filter .item__checker {
		margin-bottom: 12px;

		@media @tablet-only {
			margin: 16px 0 0;
		}
	}
</style>
