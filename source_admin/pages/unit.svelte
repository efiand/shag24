<script>
	import { ADMINZONE, CLEAR_TITLE, MEDIA_DIR } from '../js/const';
	import MCE from '../components/mce.svelte';
	import Preloader from '../components/preloader.svelte';
	import Del from '../components/del.svelte';
	import { link, push } from 'svelte-spa-router';

	export let params = {};

	const AVATAR_DIR = MEDIA_DIR.replace(`${ADMINZONE}/`, `people/`);

	let unitId = params.id;
	let unit = {};
	let videos = [];
	let places = [];
	let createMode = false;

	const videoStructure = {
		choosed: true,
		id: -1,
		people_id: unitId,
		name: ``,
		url: ``,
		sort_order: 1,
		unsaved: true
	};

	async function getUnit() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/unit`, unitId
		})}`);
		if (res.ok) {
			const data = await res.json();
			if (data.unit) {
				unit = data.unit;
				videos = data.videos;
				places = data.places;

				if (unit.img) {
					unit.img = `${AVATAR_DIR}${unit.img}?${new Date().getTime()}`;
				}
			} else {
				// если человек с несуществующим номером или из другой админзоны, переходим на список людей
				push(`/people`);
			}
		}
	}
	let asyncUnit = getUnit();

	function refresh() {
		asyncUnit = getUnit();
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
			unit
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

	function pickHandler() {
		const file = unit.fileField.files[0];

		if (file.name.toLowerCase().slice(-4) === `.jpg`) {
			const reader = new FileReader();
			unit.file = file;

			reader.addEventListener(`load`, () => {
				unit.img = reader.result;
				unit.modifiedImage = true;
				unit = unit;
			});

			reader.readAsDataURL(file);
		}
	}

	async function saveVideo(id) {
		const i = videos.findIndex((place) => place.id === id);
		videos[i].pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({ page: `/video-edit`, video: videos[i] })
		});
		if (res.ok) {
			videos[i].pale = false;
			createMode = false;
			refresh();
		}
	}

	function insertVideo() {
		videos = [JSON.parse(JSON.stringify(videoStructure)), ...videos];
		videos.forEach((item, i) => item.choosed = !i);
		createMode = true;
		videoStructure.id--; // Чтобы ключи были уникальны
	}
</script>

<svelte:head>
	<title>Управление персоной</title>
</svelte:head>

<section class="section">
	{#await asyncUnit}
		<Preloader/>
	{:then res}
		<div class="filter">
			<a class="link filter__endlink" href="/people" use:link>К списку</a>
		</div>

		<div class="fragment item item--choosed {unit.pale ? `pale` : ``}">
			<p class="fragment-title">
				<small class="item__number">№ {unit.id}</small>
				{unit.name}
			</p>
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
		</div>

		<div class="videos-header">
			<h2>Видеозаписи</h2>
			{#if !createMode}
				<button class="new link" on:click={insertVideo}>Добавить</button>
			{/if}
		</div>
		<div>
			{#each videos as video (video.id)}
				{#if !video.del}
					<div class="item {video.pale ? `pale` : ``} {video.choosed ? `item--choosed` : ``}">
						{#if ~video.id}
							<p class="video-title">
								{#if !video.choosed}
									<button class="link" type="button" on:click={() => {
										videos.forEach((item) => item.choosed = false);
										video.choosed = !video.choosed;
									}}>
										{video.name}
									</button>
								{/if}
							</p>
							<p class="services">
								<Del dbtable="people_video" colvalue={video.id}
									on:del={() => video.del = true} on:start={() => video.pale = true}/>
							</p>
						{/if}
						{#if video.choosed}
							<form class="item__form" on:submit|preventDefault={saveVideo(video.id)}>
								<div class="cols">
									<label class="field cols__half">
										<input class="field__input" bind:value={video.name} required>
										<span class="field__label field__label--required">Название</span>
									</label>
									<label class="field cols__half">
										<input class="field__input" bind:value={video.url} required>
										<span class="field__label field__label--required">Ссылка</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" bind:value={video.sort_order}>
										<span class="field__label field__label--required">Порядок</span>
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
	{/await}
</section>

<style lang="less">
	@import "../../source_html/less/vars";

	.save-button {
		@media @tablet {
			max-width: 210px;
		}
	}

	.videos-header {
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
		margin: 1.5rem 0 0;
	}

	.video-title {
		line-height: 25px;
	}
</style>
