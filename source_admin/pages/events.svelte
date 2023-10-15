<script>
	import debounce from 'lodash.debounce';
	import { link } from 'svelte-spa-router';
	import { ADMINZONE, CLEAR_TITLE, MEDIA_DIR, TOGGLE_DELAY } from '../js/const';
	import Preloader from '../components/preloader.svelte';
	import Datepicker from '../components/datepicker.svelte';
	import Del from '../components/del.svelte';

	const defaultEventType = ``;
	const enableTime = true; // для передачи в дейтпикеры, где нужно указать время
	const eventStructure = {
		choosed: true,
		id: -1,
		type_id: ``,
		title: ``,
		people_id: ``,
		datetime_start: ``,
		datetime_end: ``,
		place_id: ``,
		venue: ``,
		link: ``,
		price: 0,
		is_published: 1,
		adminzone: ADMINZONE,
		unsaved: true
	};
	const AVATAR_DIR = MEDIA_DIR.replace(`${ADMINZONE}/`, `events/`);

	let events = [];
	let keywords = ``;
	let eventType = defaultEventType;
	let eventTypes = [];
	let place = ``;
	let places = [];
	let people = [];
	let filterPlaces = [];
	let startDate = ``;
	let endDate = ``;
	let publishMode = true;
	let createEventMode = false;

	async function getEvents() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/events`, keywords, eventType, place, startDate, endDate, publishMode
		})}`);
		if (res.ok) {
			const data = await res.json();
			events = data.events;
			eventTypes = data.eventTypes;
			places = data.places;
			filterPlaces = data.filterPlaces;
			people = data.people;

			events.forEach((event) => {
				if (event.img) {
					event.img = `${AVATAR_DIR}${event.img}?${new Date().getTime()}`;
				}
				event.modifiedImage = false;
			});
		}
	}
	let asyncEvents = getEvents();

	function getDefault(evt) {
		evt.currentTarget.blur();

		keywords = ``;
		eventType = defaultEventType;
		place = ``;
		startDate = ``;
		endDate = ``;
		publishMode = true;
		refresh();
	}

	function refresh() {
		asyncEvents = getEvents();
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
		const data = new FormData();
		events[i].pale = true;
		events[i].img = null; // чтобы не передавать дважды содержимое файла
		if (events[i].file) {
			data.append(`file`, events[i].file);
		}
		data.append(`payload`, JSON.stringify({
			page: `/event-edit`,
			event: events[i]
		}));
		const res = await fetch(`/api/`, {
			method: `post`,
			body: data
		});
		if (res.ok) {
			if (id > 0) {
				events[i].pale = false;
			} else {
				createEventMode = false;
			}
			refresh();
		}
	}

	function pickHandler(id) {
		const i = events.findIndex((event) => event.id === id);
		const event = events[i];
		const file = event.fileField.files[0];

		if (file.name.toLowerCase().slice(-4) === `.jpg`) {
			const reader = new FileReader();
			events[i].file = file;

			reader.addEventListener(`load`, () => {
				event.img = reader.result;
				event.modifiedImage = true;
				events[i] = event;
			});

			reader.readAsDataURL(file);
		}
	}
</script>

<svelte:head>
	<title>Мероприятия</title>
</svelte:head>

<section class="section">
	<form class="filter" on:submit|preventDefault={refresh}>
		<label class="field keywords">
			<input class="field__input keywords" bind:value={keywords} on:input={debounce(refresh, TOGGLE_DELAY)}>
			<span class="field__label">Номера через запятую, название, адрес, ссылка</span>
		</label>

		<label class="field">
			<select class="field__input" bind:value={eventType} on:change={refresh}>
				<option value="">Любой</option>
				{#if ADMINZONE === `khara`}
					<option value="-1">Не Шодхан</option>
					<option value="0">Шодхан</option>
				{/if}
				{#if eventTypes.length}
					<optgroup label="Отдельные типы">
						{#each eventTypes as item}
							<option value={item.id}>{item.title}</option>
						{/each}
					</optgroup>
				{/if}
			</select>
			<span class="field__label">
				Тип (<a class="link" href="/event-types" use:link>редактировать</a>)
			</span>
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
				Место проведения
			</span>
		</label>

		<label class="field flatpickr-wrap">
			<Datepicker value={startDate} on:change={({ detail }) => {
				startDate = detail.value;
				refresh();
			}}/>
			<span class="field__label">С</span>
		</label>

		<label class="field flatpickr-wrap">
			<Datepicker value={endDate} on:change={({ detail }) => {
				endDate = detail.value;
				refresh();
			}}/>
			<span class="field__label">По</span>
		</label>

		<label class="filter-check checker item__checker">
			<input class="sr-only" type="checkbox" bind:checked={publishMode} on:change={refresh}>
			<span class="checker__label">Опубликованные</span>
		</label>

		<button class="button" type="button" on:click={getDefault}>Очистить</button>
	</form>

	{#await asyncEvents}
		<Preloader/>
	{:then res}
		<p class="count">Найдено мероприятий: {events.length}</p>
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
											<option value="">Не задан</option>
											{#each eventTypes as item}
												<option value={item.id}>{item.title}</option>
											{/each}
										</select>
										<span class="field__label">Тип мероприятия</span>
									</label>
									<label class="field cols__half">
										<input class="field__input" bind:value={event.title} required>
										<span class="field__label field__label--required">Название мероприятия</span>
									</label>
									<label class="field cols__quart">
										<select class="field__input" bind:value={event.people_id}>
											<option value="">Не задан</option>
											{#each people as unit}
												<option value={unit.id}>{unit.name}</option>
											{/each}
										</select>
										<span class="field__label">Ведущий мероприятия</span>
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
									<label class="checker cols__quart item__checker">
										<input class="sr-only" type="checkbox" bind:checked={event.is_published}>
										<span class="checker__label">Мероприятие опубликовано</span>
									</label>

									<label class="field cols__half">
										<input class="field__input" bind:value={event.venue}>
										<span class="field__label">Адрес проведения (если место выбрано из списка, то не включая его название)</span>
									</label>
									<label class="field cols__half">
										<input class="field__input" bind:value={event.link}>
										<span class="field__label">Полная ссылка на страницу мероприятия</span>
									</label>

									<label class="field cols__quart">
										<input class="sr-only" type="file" bind:this={event.fileField} on:change={pickHandler(event.id)}>
										{#if event.img}
											<img src={event.img} alt="" loading="lazy">
										{:else}
											<span class="link">Выбрать (jpg, png)</span>
										{/if}
										<span class="field__label clear-wrap">
											Иллюстрация в расписание (приоритетнее аватарки ведущего)
											{#if event.img}
												<button class="link clear" type="button" title={CLEAR_TITLE} on:click={() => {
													event.file = ``;
													event.img = ``;
													event.modifiedImage = true;
												}}>
													<span class="sr-only">{CLEAR_TITLE}</span>
												</button>
											{/if}
										</span>
									</label>
									<div class="cols__subtotal">&nbsp;</div>

									<label class="field cols__quart">
										<input class="field__input" type="number" bind:value={event.price}>
										<span class="field__label">Стоимость мероприятия</span>
									</label>
									<div class="item__submit cols__subtotal">
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

	.filter {
		margin-bottom: 1em;

		.button {
			padding-right: 15px;
			padding-left: 15px;
		}

		.flatpickr-wrap {
			flex-shrink: 0;

			@media @desktop {
				width: 120px;
			}
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
	}

	.save-button {
		@media @tablet {
			max-width: 210px;
		}
	}
</style>
