<script>
	import Del from '../components/del.svelte';
	import Preloader from '../components/preloader.svelte';

	const eventTypeStructure = {
		choosed: true,
		id: -1,
		title: ``,
		unsaved: true
	};

	let createMode = false;
	let eventTypes = [];

	async function getEventTypes() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/event-types`
		})}`);
		if (res.ok) {
			const data = await res.json();
			eventTypes = data.eventTypes;
		}
	}
	let asyncEventTypes = getEventTypes();

	async function saveEventType(id) {
		const i = eventTypes.findIndex((eventType) => eventType.id === id);
		eventTypes[i].pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({ page: `/event-type-edit`, eventType: eventTypes[i] })
		});
		if (res.ok) {
			eventTypes[i].pale = false;
			createMode = false;
			refresh();
		}
	}

	function insertEventType() {
		eventTypes = [JSON.parse(JSON.stringify(eventTypeStructure)), ...eventTypes];
		eventTypes.forEach((item, i) => item.choosed = !i);
		createMode = true;
		eventTypeStructure.id--; // Чтобы ключи были уникальны
	}

	function refresh() {
		asyncEventTypes = getEventTypes();
	}
</script>

<svelte:head>
	<title>Типы мероприятий</title>
</svelte:head>

<section class="section">
	<div class="event-types-header">
		<h2>Настройки типов мероприятий</h2>
		{#if !createMode}
			<button class="new link" on:click={insertEventType}>Добавить</button>
		{/if}
	</div>
	{#await asyncEventTypes}
		<Preloader/>
	{:then res}
		<div>
			{#each eventTypes as eventType (eventType.id)}
				{#if !eventType.del}
					<div class="item {eventType.pale ? `pale` : ``} {eventType.choosed ? `item--choosed` : ``}">
						{#if ~eventType.id}
							<p class="event-type-title">
								{#if !eventType.choosed}
									<button class="link" type="button" on:click={() => {
										eventTypes.forEach((item) => item.choosed = false);
										eventType.choosed = !eventType.choosed;
									}}>
										{eventType.title}
									</button>
								{/if}
							</p>
							<p class="services">
								<Del dbtable="data_event_types" colvalue={eventType.id}
									on:del={() => eventType.del = true} on:start={() => eventType.pale = true}/>
							</p>
						{/if}
						{#if eventType.choosed}
							<form class="item__form" on:submit|preventDefault={saveEventType(eventType.id)}>
								<div class="cols">
									<label class="field cols__subtotal">
										<input class="field__input" bind:value={eventType.title}>
										<span class="field__label">Название типа мероприятия</span>
									</label>
									<div class="item__submit cols__quart">
										<button class="button" type="submit">Сохранить</button>
									</div>
								</div>
							</form>
						{/if}
					</div>
				{/if}
			{:else}
				<p class="t-gap">Типов мероприятий по заданным фильтрам не найдено.</p>
			{/each}
		</div>
	{/await}
</section>

<style lang="less">
	.event-types-header {
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
		margin: 1.5rem 0 0;
	}

	.event-type-title {
		line-height: 25px;
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
