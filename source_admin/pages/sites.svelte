<script>
	import Preloader from '../components/preloader.svelte';
	import { link } from 'svelte-spa-router';
	import Editor from '../components/editor.svelte';

	let sites = [];

	async function getSites() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/sites`
		})}`);
		if (res.ok) {
			const data = await res.json();
			sites = data.sites;
		}
	}
	let asyncSites = getSites();

	async function saveSite(id) {
		const i = sites.findIndex((site) => site.id === id);
		sites[i].pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({ page: `/site-edit`, site: sites[i] })
		});
		if (res.ok) {
			sites[i].pale = false;
		}
	}

	function refresh() {
		asyncSites = getSites();
	}
</script>

<svelte:head>
	<title>Сайты</title>
</svelte:head>

<section class="section">
	<h2>Настройки сайтов</h2>
	{#await asyncSites}
		<Preloader/>
	{:then res}
		<div>
			{#each sites as site (site.id)}
				<div class="item {site.pale ? `pale` : ``} {site.choosed ? `item--choosed` : ``}">
					<p class="site-title">
						<small class="item__number">№ {site.id}</small>
						<small class="item__number">{site.alias}.ru</small>
						{#if !site.choosed}
							<button class="item__title link" type="button" on:click={() => {
								sites.forEach((item) => item.choosed = false);
								site.choosed = !site.choosed;
							}}>
								{site.title}
							</button>
						{/if}
					</p>
					{#if site.choosed}
						<form class="item__form" on:submit|preventDefault={saveSite(site.id)}>
							<div class="cols">
								<label class="field cols__subtotal">
									<input class="field__input" bind:value={site.title}>
									<span class="field__label">Название сайта</span>
								</label>
								<label class="field cols__quart">
									<input class="field__input" bind:value={site.alias}>
									<span class="field__label">Домен (без .ru)</span>
								</label>
								<label class="field cols__half">
									<input class="field__input" bind:value={site.description}>
									<span class="field__label">Описание сайта</span>
								</label>
								<label class="field cols__quart">
									<input class="field__input" type="email" bind:value={site.mail}>
									<span class="field__label">E-mail в шапке или подвале</span>
								</label>
								<label class="field cols__quart">
									<input class="field__input" bind:value={site.tel}>
									<span class="field__label">Телефон в шапке или подвале</span>
								</label>
								<label class="field cols__quart">
									<input class="field__input" bind:value={site.shop}>
									<span class="field__label">Код магазина</span>
								</label>
								<label class="checker cols__quart item__checker">
									<input class="sr-only" type="checkbox" bind:checked={site.has_place_chooser}>
									<span class="checker__label">Добавлять ли выбор городов</span>
								</label>
								<label class="checker cols__quart item__checker">
									<input class="sr-only" type="checkbox" bind:checked={site.has_master_chooser}>
									<span class="checker__label">Добавлять ли выбор мастеров</span>
								</label>
								<label class="checker cols__quart item__checker">
									<input class="sr-only" type="checkbox" bind:checked={site.has_place_events_page}>
									<span class="checker__label">Страницы городов как расписания</span>
								</label>
								<label class="field cols__total">
									<Editor value={site.head_code} on:change={({ detail }) => site.head_code = detail.value}/>
									<span class="field__label">Код перед шапкой сайта</span>
								</label>
								<label class="field cols__total">
									<Editor value={site.foot_code} on:change={({ detail }) => site.foot_code = detail.value}/>
									<span class="field__label">Код после подвала сайта</span>
								</label>
								<label class="field cols__total">
									<Editor value={site.og_image} on:change={({ detail }) => site.og_image = detail.value}/>
									<span class="field__label">Параметры изображения OpenGraph</span>
								</label>
								<label class="field cols__total">
									<Editor value={site.socials} on:change={({ detail }) => site.socials = detail.value}/>
									<span class="field__label">Параметры иконок соцсетей</span>
								</label>
								<label class="field cols__total">
									<Editor value={site.menu} on:change={({ detail }) => site.menu = detail.value}/>
									<span class="field__label">Параметры меню</span>
								</label>
								<label class="field cols__quart">
									<input class="field__input" type="email" bind:value={site.sender}>
									<span class="field__label">E-mail отправителя писем</span>
								</label>
								<label class="field cols__quart">
									<input class="field__input" bind:value={site.sender_name}>
									<span class="field__label">Имя отправителя писем</span>
								</label>
								<div class="item__submit cols__half">
									<button class="button" type="submit">Сохранить</button>
								</div>
							</div>
						</form>
					{/if}
				</div>
			{/each}
		</div>
	{/await}
</section>

<style>
	.site-title {
		line-height: 25px;
	}

	h2 {
		margin: 1.5rem 0 1rem;
	}
</style>
