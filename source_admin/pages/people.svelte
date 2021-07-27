<script>
	import debounce from 'lodash.debounce';
	import { ADMINZONE, CLEAR_TITLE, MEDIA_DIR, TOGGLE_DELAY } from '../js/const';
	import Preloader from '../components/preloader.svelte';
	import MCE from '../components/mce.svelte';
	import Del from '../components/del.svelte';
	import { link } from 'svelte-spa-router';

	const unitStructure = {
		choosed: true,
		id: -1,
		login: ``,
		name: ``,
		description: ``,
		email: ``,
		url: ``,
		tel: ``,
		place_id: ``,
		password: ``, // хэшированный хранимый вариант
		rawPassword: ``, // пароль для создания и перезаписи
		trainer_flag: 1,
		shodhan_flag: 0,
		shodhan_cert_flag: 0,
		adminzone: ADMINZONE,
		img: ``,
		unsaved: true
	};
	const AVATAR_DIR = MEDIA_DIR.replace(`${ADMINZONE}/`, `people/`);

	let people = [];
	let keywords = ``;
	let place = ``;
	let places = [];
	let filterPlaces = [];
	let showTrainers = false;
	let showShodhan1 = false;
	let showShodhan2 = false;
	let createUnitMode = false;

	async function getPeople() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/people`, keywords, place, showTrainers, showShodhan1, showShodhan2
		})}`);
		if (res.ok) {
			const data = await res.json();
			people = data.people;
			places = data.places;
			filterPlaces = data.filterPlaces;

			people.forEach((unit) => {
				if (unit.img) {
					unit.img = `${AVATAR_DIR}${unit.img}?${new Date().getTime()}`;
				}
				unit.rawPassword = ``;
				unit.modifiedImage = false;
			});
		}
	}
	let asyncPeople = getPeople();

	function getDefault(evt) {
		evt.currentTarget.blur();

		keywords = ``;
		place = ``;
		showTrainers = false;
		showShodhan1 = false;
		showShodhan2 = false;
		refresh();
	}

	function refresh() {
		asyncPeople = getPeople();
	}

	function insertUnit() {
		people = [JSON.parse(JSON.stringify(unitStructure)), ...people];
		people.forEach((unit, i) => unit.choosed = !i);
		createUnitMode = true;
		unitStructure.id--; // Чтобы ключи были уникальны
	}

	async function saveUnit(id) {
		const i = people.findIndex((unit) => unit.id === id);
		const data = new FormData();
		people[i].pale = true;
		people[i].img = null; // чтобы не передавать дважды содержимое файла
		if (people[i].file) {
			data.append(`file`, people[i].file);
		}
		data.append(`payload`, JSON.stringify({
			page: `/unit-edit`,
			unit: people[i]
		}));

		const res = await fetch(`/api/`, {
			method: `post`,
			body: data
		});
		if (res.ok) {
			people[i].pale = false;
			createUnitMode = false;
			refresh();
		}
	}

	function pickHandler(id) {
		const i = people.findIndex((unit) => unit.id === id);
		const unit = people[i];
		const file = unit.fileField.files[0];

		if (file.name.toLowerCase().slice(-4) === `.jpg`) {
			const reader = new FileReader();
			people[i].file = file;

			reader.addEventListener(`load`, () => {
				unit.img = reader.result;
				unit.modifiedImage = true;
				people[i] = unit;
			});

			reader.readAsDataURL(file);
		}
	}
</script>

<svelte:head>
	<title>Люди</title>
</svelte:head>

<section class="section">
	<form class="filter" on:submit|preventDefault={refresh}>
		<label class="field keywords">
			<input class="field__input keywords" bind:value={keywords} on:input={debounce(refresh, TOGGLE_DELAY)}>
			<span class="field__label">Имя, логин, e-mail, url</span>
		</label>

		<label class="field">
			<select class="field__input" bind:value={place} on:change={refresh}>
				<option value="">Любое</option>
				{#each filterPlaces as item}
					<option value={item.id}>
						{item.title}
						{#if item.country}
							({item.country})
						{/if}
					</option>
				{/each}
			</select>
			<span class="field__label">
				Место жительства
			</span>
		</label>

		<label class="filter-check checker item__checker">
			<input class="sr-only" type="checkbox" bind:checked={showTrainers} on:change={refresh}>
			<span class="checker__label">Тренеры</span>
		</label>

		{#if ADMINZONE === `khara`}
			<label class="filter-check checker item__checker">
				<input class="sr-only" type="checkbox" bind:checked={showShodhan1} on:change={refresh}>
				<span class="checker__label">Шодхан I ур.</span>
			</label>

			<label class="filter-check checker item__checker">
				<input class="sr-only" type="checkbox" bind:checked={showShodhan2} on:change={refresh}>
				<span class="checker__label">Шодхан II ур.</span>
			</label>
		{/if}

		<button class="button" type="button" on:click={getDefault}>Очистить</button>
	</form>

	{#await asyncPeople}
		<Preloader/>
	{:then res}
		<p class="count">Найдено: {people.length}</p>
		{#if !createUnitMode}
			<p class="b-gap">
				<button class="link" on:click={insertUnit}>Добавить</button>
			</p>
		{/if}
		<div>
			{#each people as unit (unit.id)}
				{#if !unit.del}
					<div class="fragment item {unit.pale ? `pale` : ``} {unit.choosed ? `item--choosed` : ``}">
						<p class="fragment-title">
							{#if unit.id > -1}
								<small class="item__number">
									№ {unit.id}
								</small>
							{/if}
							{#if !unit.choosed}
								<button class="item__title link" type="button" on:click={() => {
									people.forEach((item) => item.choosed = false);
									unit.choosed = !unit.choosed;
								}}>
									{unit.name}
									{#if unit.unsaved}
										<span class="red">(не сохранено)</span>
									{/if}
								</button>
								<small style="margin-left: 24px">
									<a class="link" href="/people/{unit.id}" target="_blank" use:link>Открыть в новой вкладке</a>
								</small>
							{/if}
						</p>
						<p class="item__del services">
							{#if ~unit.id}
								<Del dbtable="data_people" colvalue={unit.id}
									on:del={() => unit.del = true} on:start={() => unit.pale = true}/>
							{/if}
						</p>
						{#if unit.choosed}
							<form class="item__form" on:submit|preventDefault={saveUnit(unit.id)}>
								<div class="cols">
									<label class="field cols__half">
										<input class="field__input" bind:value={unit.name} required>
										<span class="field__label field__label--required">Имя</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" bind:value={unit.login}>
										<span class="field__label">Логин (если требуется доступ к админзоне)</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" bind:value={unit.rawPassword}>
										<span class="field__label">
											Пароль
											{#if unit.password}
												<b>(для перезаписи существующего)</b>
											{:else}
												(если требуется доступ к админзоне)
											{/if}
										</span>
									</label>
									<div class="field cols__total">
										<MCE value={unit.description} on:change={({ detail }) => {
											if (detail.saveFlag) {
												unit.description = detail.value;
												saveUnit(unit.id);
											}
										}}/>
										<span class="field__label">Описание</span>
									</div>
									<label class="field cols__quart">
										<select class="field__input" bind:value={unit.place_id}>
											<option value="">Не задана</option>
											{#each places as item}
												<option value={item.id}>
													{item.title}
													{#if item.country}
														({item.country})
													{/if}
												</option>
											{/each}
										</select>
										<span class="field__label">Локация</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" type="email" bind:value={unit.email}>
										<span class="field__label">E-mail</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" bind:value={unit.url}>
										<span class="field__label">Сайт</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" type="tel" bind:value={unit.tel}>
										<span class="field__label">Телефон</span>
									</label>
									<label class="field cols__quart">
										<input class="sr-only" type="file" bind:this={unit.fileField} on:change={pickHandler(unit.id)}>
										{#if unit.img}
											<img src={unit.img} alt="" loading="lazy">
										{:else}
											<span class="link">Выбрать (jpg, png)</span>
										{/if}
										<span class="field__label clear-wrap">
											Аватар
											{#if unit.img}
												<button class="link clear" type="button" title={CLEAR_TITLE} on:click={() => {
													unit.file = ``;
													unit.img = ``;
													unit.modifiedImage = true;
												}}>
													<span class="sr-only">{CLEAR_TITLE}</span>
												</button>
											{/if}
										</span>
									</label>
									<div class="cols__subtotal">&nbsp;</div>
									<label class="checker cols__quart item__checker">
										<input class="sr-only" type="checkbox" bind:checked={unit.trainer_flag}>
										<span class="checker__label">Тренер (ведущий)</span>
									</label>
									{#if ADMINZONE === `khara`}
										<label class="checker cols__quart item__checker">
											<input class="sr-only" type="checkbox" bind:checked={unit.shodhan_flag}>
											<span class="checker__label">Шодхан I ур.</span>
										</label>
										<label class="checker cols__quart item__checker">
											<input class="sr-only" type="checkbox" bind:checked={unit.shodhan_cert_flag}>
											<span class="checker__label">Шодхан II ур.</span>
										</label>
										<div class="item__submit cols__quart">
											<button class="button" type="submit">Сохранить</button>
										</div>
									{:else}
										<div class="item__submit cols__subtotal">
											<button class="save-button button" type="submit">Сохранить</button>
										</div>
									{/if}
								</div>
							</form>
						{/if}
					</div>
				{/if}
			{/each}
		</div>
	{/await}
</section>

<style lang="less">
	@import "../../source_html/less/vars";

	.filter {
		margin-bottom: 1em;

		.button {
			padding-right: 15px;
			padding-left: 15px;
		}
	}

	.count {
		margin-bottom: 1em;
	}

	.keywords {
		@media @lg-desktop {
			min-width: 540px;
		}
	}

	.filter-check {
		margin-bottom: 10px;
		white-space: nowrap;
	}

	.save-button {
		@media @tablet {
			max-width: 210px;
		}
	}
</style>
