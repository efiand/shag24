<script>
	import { link } from 'svelte-spa-router';
	import debounce from 'lodash.debounce';
	import { TOGGLE_DELAY } from '../js/const';
	import Preloader from '../components/preloader.svelte';
	import Del from '../components/del.svelte';

	let newTopic = ``;
	let forms = [];
	let filter = ``;
	let pages = [];
	let pageId = ``;
	let paymentsOnly = false;

	async function getForms() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/forms`,
			filter,
			pageId,
			paymentsOnly
		})}`);
		if (res.ok) {
			const data = await res.json();
			pages = data.pages;
			forms = data.forms;
		}
	}
	let asyncForms = getForms();

	function refresh() {
		asyncForms = getForms();
	}

	function getAll(evt) {
		evt.currentTarget.blur();

		filter = ``;
		pageId = ``;
		paymentsOnly = false;
		refresh();
	}

	async function createForm() {
		const res = await fetch(`/api/?payload=${JSON.stringify({ page: `/new-form`, newTopic })}`);
		if (res.ok) {
			refresh();
		}
	}
</script>

<svelte:head>
	<title>Формы</title>
</svelte:head>

<section class="section">
	<div class="filter">
		<form on:submit|preventDefault={refresh}>
			<label class="field">
				<input class="field__input" bind:value={filter} on:input={debounce(refresh, TOGGLE_DELAY)}>
				<span class="field__label">Поиск по номерам (через запятую), теме или описанию</span>
			</label>
			<label class="field">
				<select class="field__input" bind:value={pageId} on:change={refresh}>
					<option value="">Любая</option>
					{#each pages as page}
						<option value={page.id}>{page.site_alias}.ru{page.alias}</option>
					{/each}
				</select>
				<span class="field__label">Страница</span>
			</label>
			<label class="filter-check checker item__checker">
				<input class="sr-only" type="checkbox" bind:checked={paymentsOnly} on:change={refresh}>
				<span class="checker__label">Только&nbsp;платёжные</span>
			</label>
			<button class="button" type="button" on:click={getAll}>Все</button>
		</form>

		<form class="new" on:submit|preventDefault={createForm}>
			<label class="field">
				<input class="field__input" bind:value={newTopic}>
				<span class="field__label">Тема заявки</span>
			</label>

			<button class="button" type="submit">Создать&nbsp;форму</button>
		</form>
	</div>

	{#await asyncForms}
		<Preloader/>
	{:then res}
		<p class="count">Найдено форм: {forms.length}</p>
		<div>
			{#each forms as form (form.id)}
				{#if !form.del}
					<div class="item {form.pale ? `pale` : ``}">
						<p>
							<small class="item__number">№ {form.id}</small>
							<a class="item__title link" href="/forms/{form.id}" use:link>{form.topic}</a>
							{#if form.description}
								<small class="description item__description">{form.description}</small>
							{/if}
						</p>
						<p class="item__del">
							<Del dbtable="data_forms" colvalue={form.id} on:del={() => form.del = true} on:start={() => form.pale = true}/>
						</p>
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
	}

	form {
		@media @tablet {
			display: flex;
		}

		@media @desktop {
			width: 1000px;
			max-width: 70%;
		}

		.button {
			padding-right: 12px;
			padding-left: 12px;
		}
	}

	.filter-check {
		margin-bottom: 12px;
	}

	.new {
		margin-left: auto;

		@media @mobile-only {
			margin: 2em 0;
		}

		@media @desktop {
			width: 500px;
			max-width: 30%;
			padding-left: 1em;
		}
	}

	.count {
		margin-bottom: 1em;
	}

	.description {
		margin-top: 2px;
	}
</style>
