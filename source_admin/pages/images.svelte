<script>
	import debounce from 'lodash.debounce';
	import { CLEAR_TITLE, TOGGLE_DELAY } from '../js/const';
	import Preloader from '../components/preloader.svelte';
	import File from '../components/file.svelte';

	let images = [];
	let filter = ``;
	let sortByDate = false;
	let imagesLength = 0;
	let imageFlag = true;

	async function getImages() {

		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/files`,
			filter,
			sortByDate,
			imagesOnly: true
		})}`);
		if (res.ok) {
			const imageTemplate = { name: `` };
			const data = await res.json();
			if (data.files.length) {
				images = data.files.map((name) => ({ name }));
				images.unshift(imageTemplate);
			} else {
				images = [imageTemplate];
			}
			imagesLength = images.length - 1;
		}
	}
	let asyncImages = getImages();

	function refresh() {
		asyncImages = getImages();
	}

	function getDefault(evt) {
		evt.currentTarget.blur();

		filter = ``;
		refresh();
	}
</script>

<svelte:head>
	<title>Изображения</title>
</svelte:head>

<section class="section">
	<form class="filter" on:submit|preventDefault={refresh}>
		<label class="field search">
			<input class="field__input" bind:value={filter} on:input={debounce(refresh, TOGGLE_DELAY)}>
			<span class="field__label clear-wrap">
				Поиск по имени файла
				{#if filter}
					<button class="link clear" type="button" title={CLEAR_TITLE} on:click={getDefault}>
						<span class="sr-only">{CLEAR_TITLE}</span>
					</button>
				{/if}
			</span>
		</label>

		<label class="filter-check checker">
			<input class="sr-only" type="checkbox" bind:checked={sortByDate} on:change={refresh}>
			<span class="checker__label">Сортировка по дате</span>
		</label>
	</form>

	{#await asyncImages}
		<Preloader/>
	{:then res}
		<p class="b-gap">Найдено изображений: {imagesLength}</p>
		<div class="cols">
			{#each images as image}
				{#if !image.deleted}
					<div class="image-block cols__quart">
						<File name={image.name} {imageFlag} on:del={() => {
							image.deleted = true;
							imagesLength--;
						}} on:edit={refresh}/>
					</div>
				{/if}
			{/each}
		</div>
	{/await}
</section>

<style lang="less">
	@import "../../source_html/less/vars";

	.filter {
		@media @desktop {
			display: flex;
			margin-bottom: 1em;
		}
	}

	.search {
		@media @desktop {
			flex-grow: 0;
			width: calc(50% - 15px);
		}
	}

	.filter-check {
		@media @desktop {
			margin: 30px 0 0 16px;
		}
	}

	.image-block {
		display: flex;
		align-items: flex-end;
		margin-top: 24px;

		@media @tablet {
			height: 348px;
		}
	}
</style>
