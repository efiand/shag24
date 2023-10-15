<script>
	import { onMount } from 'svelte';
	import { allowFlag, shodhanFlag } from '../js/stores';
	import { ADMINZONE, MEDIA_DIR, CLEAR_TITLE } from '../js/const';
	import Preloader from '../components/preloader.svelte';
	import Datepicker from '../components/datepicker.svelte';
	import Del from '../components/del.svelte';

	const id = localStorage.getItem(`people`);
	const AVATAR_DIR = MEDIA_DIR.replace(`${ADMINZONE}/`, `people/`);
	const enableTime = true; // для передачи в дейтпикеры, где нужно указать время
	const eventStructure = {
		choosed: true,
		id: -1,
		type_id: 1,
		title: ``,
		people_id: id,
		datetime_start: ``,
		datetime_end: ``,
		place_id: ``,
		venue: ``,
		link: ``,
		price: 0,
		is_published: 0,
		adminzone: ADMINZONE,
		unsaved: true
	};

	let unit = {};
	let events = [];
	let eventTypes = [];
	let places = [];
	let createEventMode = false;

	async function getData() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/shodhan`,
			id
		})}`);
		if (res.ok) {
			const data = await res.json();
			unit = data.unit;
			events = data.events;
			eventTypes = data.eventTypes;
			places = data.places;
			eventStructure.place_id = unit.place_id;
			if (unit.img) {
				unit.img = `${AVATAR_DIR}${unit.img}?${new Date().getTime()}`;
			}
		}
	}
	let asyncData = getData();

	function refresh() {
		asyncData = getData();
	}

	function getPlaceAlias(placeId) {
		if (placeId) {
			const place = places.find((item) => item.id === placeId);
			return place.alias;
		}
		return ``;
	}

	function insertEvent() {
		events = [JSON.parse(JSON.stringify(eventStructure)), ...events];
		events.forEach((item, i) => item.choosed = !i);
		createEventMode = true;
		eventStructure.id--; // Чтобы ключи были уникальны
	}

	async function saveEvent(id) {
		const i = events.findIndex((event) => event.id === id);
		events[i].pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({
				page: `/event-edit`,
				event: events[i]
			})
		});
		if (res.ok) {
			if (id > 0) {
				events[i].pale = false;
			} else {
				createEventMode = false;
				refresh();
			}
		}
	}

	async function saveUnit() {
		const data = new FormData();
		unit.pale = true;
		unit.img = null; // чтобы не передавать дважды содержимое файла
		if (unit.file) {
			data.append(`file`, unit.file);
		}
		data.append(`payload`, JSON.stringify({
			page: `/unit-edit`,
			unit: unit
		}));

		const res = await fetch(`/api/`, {
			method: `post`,
			body: data
		});
		if (res.ok) {
			unit.pale = false;
			refresh();
		}
	}

	function pickHandler(id) {
		const file = unit.fileField.files[0];

		if (file.name.toLowerCase().slice(-4) === `.jpg`) {
			const reader = new FileReader();
			unit.file = file;

			reader.addEventListener(`load`, () => {
				unit.img = reader.result;
				unit.modifiedImage = true;
			});

			reader.readAsDataURL(file);
		}
	}

	async function logout() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/logout`
		})}`);

		if (res.ok) {
			localStorage.setItem(`allow`, ``);
			localStorage.setItem(`shodhan`, ``);
			allowFlag.set(false);
			shodhanFlag.set(false);
		}
	}

	onMount(() => {
		document.body.classList.add(ADMINZONE);
	});
</script>

<svelte:head>
	<title>Кабинет инструктора: {unit.name}</title>
</svelte:head>

<section class="section">
	{#await asyncData}
		<Preloader/>
	{:then res}
		<div class="header">
			<h1>Кабинет инструктора: {unit.name}</h1>
			<button class="button" on:click={logout}>Выход</button>
		</div>

		<div class="unit fragment item {unit.pale ? `pale` : ``} item--choosed v-gap">
			<form class="item__form" on:submit|preventDefault={saveUnit}>
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
						<span class="field__label">Пароль <b>(для перезаписи существующего)</b></span>
					</label>
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
					<label class="checker cols__quart item__checker">
						<input class="sr-only" type="checkbox" bind:checked={unit.shodhan_flag}>
						<span class="checker__label">Шодхан I ур.</span>
					</label>
					<label class="checker cols__quart item__checker">
						<input class="sr-only" type="checkbox" bind:checked={unit.shodhan_cert_flag}>
						<span class="checker__label">Шодхан II ур.</span>
					</label>
					<div class="item__submit cols__quart">
						<button class="save-button button" type="submit">Сохранить</button>
					</div>
				</div>
			</form>
		</div>
		<h2>Ваши мероприятия</h2>
		{#if !createEventMode}
			<p class="b-gap">
				<button class="link" on:click={insertEvent}>Новое мероприятие</button>
			</p>
		{/if}
		<div>
			{#each events as event (event.id)}
				{#if !event.del}
					<div class="fragment item {event.pale ? `pale` : ``} {event.choosed ? `item--choosed` : ``}">
						<p class="fragment-title">
							<small class="item__number">{event.datetime_start.slice(0, 10)}</small>
							{#if event.id > 0}
								<small class="item__number">№ {event.id}</small>
							{/if}
							<small class="item__number item__city-alias">{getPlaceAlias(event.place_id)}</small>
							{#if !event.choosed}
								<button class="item__title link" type="button" on:click={() => {
									events.forEach((item) => item.choosed = false);
									event.choosed = !event.choosed;
								}}>
									{event.title}
									{#if event.unsaved}
										<span class="red">(не сохранено)</span>
									{/if}
								</button>
							{/if}
						</p>
						<p class="item__del services">
							{#if ~event.id && event.deletable}
								<Del dbtable="data_events" colvalue={event.id}
									on:del={() => event.del = true} on:start={() => event.pale = true}/>
							{/if}
						</p>
						{#if event.choosed}
							<form class="item__form" on:submit|preventDefault={saveEvent(event.id)}>
								<div class="cols">
									<label class="field cols__quart">
										<select class="field__input" bind:value={event.type_id}>
											{#each eventTypes as item}
												<option value={item.id}>{item.title}</option>
											{/each}
										</select>
										<span class="field__label">Тип мероприятия</span>
									</label>
									<label class="field cols__quart">
										<Datepicker value={event.datetime_start} required={true} enableTime on:change={({ detail }) => {
											event.datetime_start = detail.value;
										}}/>
										<span class="field__label field__label--required">Дата и время начала (00:00 – игнор времени)</span>
									</label>
									<label class="field cols__quart">
										<Datepicker value={event.datetime_end} enableTime on:change={({ detail }) => {
											event.datetime_end = detail.value;
										}}/>
										<span class="field__label clear-wrap">
											Дата и время окончания (00:00 – игнор времени)
											{#if event.datetime_end}
												<button class="link clear" type="button" title={CLEAR_TITLE} on:click={() => event.datetime_end = ``}>
													<span class="sr-only">{CLEAR_TITLE}</span>
												</button>
											{/if}
										</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" type="number" bind:value={event.price}>
										<span class="field__label">Стоимость мероприятия</span>
									</label>
									<label class="field cols__quart">
										<select class="field__input" bind:value={event.place_id}>
											<option value="">Не задано</option>
											{#each places as item}
												<option value={item.id}>
													{item.title}
													{#if item.country}
														({item.country})
													{/if}
												</option>
											{/each}
										</select>
										<span class="field__label">Место проведения</span>
									</label>
									<label class="field cols__half">
										<input class="field__input" bind:value={event.venue}>
										<span class="field__label">Адрес проведения (если место выбрано из списка, то не включая его название)</span>
									</label>
									<div class="item__submit cols__quart">
										<button class="save-button button" type="submit">Сохранить</button>
									</div>
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

	.unit {
		@media @no-desktop {
			margin-top: 5em !important;
		}
	}

	.save-button {
		@media @tablet {
			width: 100%;
			max-width: 210px;
		}
	}

	.header {
		@media @desktop {
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		h1 {
			margin: 6px 0 16px;
		}

		.button {
			margin-top: -12px;
		}
	}
</style>
