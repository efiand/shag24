<script>
	import Preloader from '../components/preloader.svelte';
	import { link } from 'svelte-spa-router';
	import Del from '../components/del.svelte';

	let currentSite = ``;
	let newPage = ``;
	let sites = [];
	let pages = [];

	async function getPages() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/pages`, currentSite
		})}`);
		if (res.ok) {
			const data = await res.json();
			if (data.sites) {
				sites = data.sites;
				currentSite = (sites.find((site) => site.id === currentSite) || sites[0]).id;
			}
			pages = data.pages;
		}
	}

	async function createPage() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/new-page`, newPage, currentSite
		})}`);
		if (res.ok) {
			refresh();
		}
	}

	let asyncPages = getPages();

	function refresh() {
		asyncPages = getPages();
	}
</script>

<svelte:head>
	<title>Страницы</title>
</svelte:head>

<section class="section">
	<div class="filter">
		<label class="field site-choose">
			<select class="field__input" bind:value={currentSite} on:change={refresh}>
				{#each sites as item}
					<option value={item.id}>{item.alias}.ru</option>
				{/each}
			</select>
			<span class="field__label">Сайт</span>
		</label>

		<form class="new" on:submit|preventDefault={createPage}>
			<label class="field">
				<input class="field__input" bind:value={newPage}>
				<span class="field__label">Новая страница</span>
			</label>

			<button class="button" type="submit">Создать</button>
		</form>
	</div>

	{#await asyncPages}
		<Preloader/>
	{:then res}
		<div>
			{#each pages as page (page.id)}
				{#if !page.del}
					<div class="item {page.pale ? `pale` : ``}">
						<p>
							<span class="alias item__title">
								<a class="link" href="/pages/{page.id}" use:link>{page.alias}</a>
							</span>
							{#if page.title}
								<a class="item__title" href="/pages/{page.id}" use:link>{page.title}</a>
							{/if}
						</p>
						<p class="item__del">
							<Del dbtable="data_pages" colvalue={page.id} on:del={() => page.del = true} on:start={() => page.pale = true}/>
						</p>
					</div>
				{/if}
			{/each}
		</div>
	{/await}
</section>

<style lang="less">
	@import "../../source_html/less/vars";

	.site-choose {
		@media @desktop {
			max-width: 160px;
		}
	}

	form {
		margin-left: auto;

		@media @tablet {
			display: flex;
		}

		@media @desktop {
			width: 500px;
		}
	}

	.alias {
		display: inline-block;
		vertical-align: baseline;

		@media @tablet {
			min-width: 250px;
		}
	}

	.new {
		margin-left: auto;

		@media @mobile-only {
			margin: 2em 0;
		}

		@media @desktop {
			padding-left: 1em;
		}
	}
</style>
