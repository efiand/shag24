<script>
	import { ADMINZONE, DOMAIN_ZONE } from '../js/const';
	import Icon from 'svelte-icon';
	import PaymentIcon from '../../source_html/sprite/payment.svg';
	import Preloader from '../components/preloader.svelte';
	import TelLinks from '../components/tel-links.svelte';
	import Del from '../components/del.svelte';
	import Editor from '../components/editor.svelte';
	import MCE from '../components/mce.svelte';
	import { link, push } from 'svelte-spa-router';

	export let params = {};

	let fragmentId = params.id;
	let fragment = {};
	let forms = {};
	let events = {};
	let people = {};
	let formNumbers;
	let eventNumbers;
	let personNumbers;

	const wysiwygTypes = [`visual`, `visual-auto`, `section`];

	async function getFragment() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/fragment`, fragmentId
		})}`);
		if (res.ok) {
			const data = await res.json();
			if (data.fragment) {
				fragment = data.fragment;
				forms = data.forms;
				events = data.events;
				people = data.people;
				formNumbers = Object.keys(forms);
				eventNumbers = Object.keys(events);
				personNumbers = Object.keys(people);
			} else {
				// если фрагмент с несуществующим номером или из другой админзоны, переходим на список страниц
				push(`/pages`);
			}
		}
	}
	let asyncFragment = getFragment();

	async function saveFragment() {
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({
				page: `/fragment-edit`,
				pageId: fragment.page_id,
				fragment
			})
		});
		if (res.ok) {
			asyncFragment = getFragment();
		}
	}

	function changeTypeHandler() {
		if (fragment.type === `plugin`) {
			fragment.value = ``;
		} else if (fragment.type === `youtube`) {
			fragment.value = fragment.value.replace(`.be/`, `be.com/embed/`);
		} else if (fragment.type === `copy`) {
			fragment.alias = `_COPY`;
		}
	}
</script>

<svelte:head>
	<title>Управление фрагментом</title>
</svelte:head>

<section class="section">
	{#await asyncFragment}
		<Preloader/>
		{:then res}
		<div class="filter">
			<a class="link filter__endlink" href="/pages/{fragment.page_id}" use:link>К странице</a>
		</div>

		<div class="fragment item item--choosed">
			<p class="fragment-title">
				<small class="item__number">№ {fragment.id}</small>
				{#if ~wysiwygTypes.indexOf(fragment.type)}
					<small class="mce-wrap">
						<MCE value={fragment.value} siteAlias={fragment.site} on:change={({ detail }) => {
							fragment.value = detail.value;
							saveFragment(fragment.id);
						}}/>
					</small>
				{/if}
			</p>
			{#if ~fragment.id && fragment.deletable}
				<p class="item__del services">
					<Del dbtable="data_fragments" colvalue={fragment.id} on:del={() => push(`/pages`)}/>
				</p>
			{/if}
			<form class="item__form" on:submit|preventDefault={saveFragment}>
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
							{:else if fragment.type === `person`}
								<select class="field__input" bind:value={fragment.value}>
									<option class="placeholder" value="">Выберите для данной админзоны</option>
									{#each personNumbers as n}
										<option value={n}>
											№ {n}. {people[n].name}
										</option>
									{/each}
								</select>
							{:else if fragment.type === `event`}
								<select class="field__input" bind:value={fragment.value}>
									<option class="placeholder" value="">Выберите для данной админзоны</option>
									{#each eventNumbers as n}
										<option value={n}>
											№ {n}. &lt;{events[n].datetime_start}&gt;: {events[n].title}
										</option>
									{/each}
								</select>
							{:else if fragment.type === `img`}
								<input class="field__input" bind:value={fragment.value} placeholder="Введите имя файла">
								{#if fragment.value}
									<div class="cols item__media">
										<div class="cols__quart">
											<img alt="" src="//{fragment.site}.ru/media/{ADMINZONE}/{fragment.value}?version={window.VERSION}">
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
								<p>Будет построено одноименным модулем движка</p>
							{:else if ~[`link`, `copy`].indexOf(fragment.type)}
								<input class="field__input" type="number" bind:value={fragment.value} placeholder="Введите номер другого фрагмента" required>
							{:else}
								<input class="field__input" bind:value={fragment.value}>
							{/if}
							<span class="field__label">Содержимое фрагмента</span>
						</div>
					{/if}
					<label class="field cols__quart">
						<input class="field__input" bind:value={fragment.alias} pattern={`[A-Z_]{1}[A-Z0-9_]{2,}`} required>
						<span class="field__label find__label--required">Кодовое имя фрагмента (A-Z, 0-9, _)</span>
					</label>
					<label class="field cols__quart">
						<input class="field__input" type="number" min="1" bind:value={fragment.sort_order} required>
						<span class="field__label">Порядок фрагмента</span>
					</label>
					<label class="field cols__quart">
						<select class="field__input" bind:value={fragment.type} on:change={changeTypeHandler}>
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
		</div>
	{/await}
</section>

<style lang="less">
	@import "../../source_html/less/vars";

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
</style>
