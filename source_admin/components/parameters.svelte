<script>
	import Preloader from '../components/preloader.svelte';
	import { link } from 'svelte-spa-router';
	import Editor from '../components/editor.svelte';

	let controlParameters = [];
	let parameters = [];
	let pale = false;

	async function getParameters() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/parameters`
		})}`);
		if (res.ok) {
			const data = await res.json();
			parameters = data.parameters;
			controlParameters = JSON.parse(JSON.stringify(parameters));
		}
	}
	let asyncParameters = getParameters();

	async function saveParameters() {
		pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({
				parameters: parameters.filter((parameter) => {
					return parameter.value !== controlParameters.find((item) => item.name === parameter.name).value;
				}),
				page: `/parameters`
			})
		});
		if (res.ok) {
			const data = await res.json();
			if (data.error) {
				alert(data.error);
			} else {
				window.location.reload();
			}
		} else {
			alert('Произошла ошибка. Повторите отправку.');
			pale = false;
		}
	}

	function refresh() {
		asyncParameters = getParameters();
	}
</script>

<svelte:head>
	<title>Сайты</title>
</svelte:head>

<section class="section">
	<h2>Глобальные параметры</h2>
	{#await asyncParameters}
		<Preloader/>
	{:then res}
		<form class="{pale ? `pale` : ``}" on:submit|preventDefault={saveParameters}>
			{#each parameters as parameter (parameter.name)}
				<label class="field">
					<input class="field__input" bind:value={parameter.value}>
					<span class="field__label" title={parameter.name}>{parameter.description}</span>
				</label>
			{/each}

			<button class="button" type="submit" disabled={pale}>Сохранить</button>
		</form>
	{/await}
</section>

<style>
	.section {
		display: table;
		margin: 0 auto;
	}

	h2 {
		margin: 1.5rem 0 1rem;
	}
</style>
