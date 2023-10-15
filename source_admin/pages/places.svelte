<script>
	import Del from '../components/del.svelte';
	import Preloader from '../components/preloader.svelte';

	const placeStructure = {
		choosed: true,
		id: -1,
		alias: ``,
		title: ``,
		country: ``,
		unsaved: true
	};

	let createMode = false;
	let places = [];

	async function getPlaces() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/places`
		})}`);
		if (res.ok) {
			const data = await res.json();
			places = data.places;
		}
	}
	let asyncPlaces = getPlaces();

	async function savePlace(id) {
		const i = places.findIndex((place) => place.id === id);
		places[i].pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({ page: `/place-edit`, place: places[i] })
		});
		if (res.ok) {
			places[i].pale = false;
			createMode = false;
			refresh();
		}
	}

	function insertPlace() {
		places = [JSON.parse(JSON.stringify(placeStructure)), ...places];
		places.forEach((item, i) => item.choosed = !i);
		createMode = true;
		placeStructure.id--; // Чтобы ключи были уникальны
	}

	function refresh() {
		asyncPlaces = getPlaces();
	}
</script>

<svelte:head>
	<title>Места проведения</title>
</svelte:head>

<section class="section">
	<div class="places-header">
		<h2>Настройки мест проведения</h2>
		{#if !createMode}
			<button class="new link" on:click={insertPlace}>Добавить</button>
		{/if}
	</div>
	{#await asyncPlaces}
		<Preloader/>
	{:then res}
		<div>
			{#each places as place (place.id)}
				{#if !place.del}
					<div class="item {place.pale ? `pale` : ``} {place.choosed ? `item--choosed` : ``}">
						{#if ~place.id}
							<p class="place-title">
								<small class="item__number item__city-alias">{place.alias}</small>
								{#if !place.choosed}
									<button class="link" type="button" on:click={() => {
										places.forEach((item) => item.choosed = false);
										place.choosed = !place.choosed;
									}}>
										{place.title}
									</button>
									{#if place.country}
										<small class="item__number">({place.country})</small>
									{/if}
								{/if}
							</p>
							<p class="services">
								<Del dbtable="data_places" colvalue={place.id}
									on:del={() => place.del = true} on:start={() => place.pale = true}/>
							</p>
						{/if}
						{#if place.choosed}
							<form class="item__form" on:submit|preventDefault={savePlace(place.id)}>
								<div class="cols">
									<label class="field cols__quart">
										<input class="field__input" bind:value={place.alias} required>
										<span class="field__label">Код места проведения</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" bind:value={place.title} required>
										<span class="field__label">Название места проведения</span>
									</label>
									<label class="field cols__quart">
										<input class="field__input" bind:value={place.country}>
										<span class="field__label">Страна (для отображения в списке)</span>
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
	.places-header {
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
		margin: 1.5rem 0 0;
	}

	.place-title {
		line-height: 25px;

		button {
			font-size: inherit;
		}
	}

	.services {
		display: flex;
		min-height: 25px;

		span {
			margin-right: 2em;

			button {
				vertical-align: middle;
			}
		}
	}
</style>
