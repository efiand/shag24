<script>
	import { push } from 'svelte-spa-router';
	import { ADMINZONE } from '../js/const';
	import { allowFlag, shodhanFlag } from '../js/stores';

	let form;
	let validableClass = ``;
	let formData = {
		page: `/login`,
		login: ``,
		password: ``,
		site: ADMINZONE
	};
	let statusText;

	async function submitHandler() {
		if (!form.checkValidity()) {
			validableClass = `validable`;
			return;
		}

		const res = await fetch(`/api/?payload=${JSON.stringify(formData)}`);
		if (res.ok) {
			const { allow, shodhan, status, login, id } = await res.json();

			allowFlag.set(allow);
			shodhanFlag.set(shodhan);
			localStorage.setItem(`people`, id);
			if (allow) {
				localStorage.setItem(`allow`, `true`);
				localStorage.setItem(`shodhan`, ``);
				localStorage.setItem(`site`, formData.site);
			} else {
				localStorage.setItem(`allow`, ``);
				localStorage.setItem(`shodhan`, shodhan ? `true` : ``);
				formData.login = login;
				statusText = status;
			}
		}
	}
</script>

<svelte:head>
	<title>Вход</title>
</svelte:head>

<section class="section container">
	<h2 class="section__title">Вход в панель управления</h2>
	{#if statusText}
		<p class="section__item center">{statusText}</p>
	{/if}

	<div class="cols">
		<form class="cols__half {validableClass}" bind:this={form} on:submit|preventDefault={submitHandler} novalidate>
			<label class="field">
				<input class="field__input" bind:value="{formData.login}" required>
				<span class="field__label field__label--required">Логин</span>
			</label>
			<label class="field">
				<input class="field__input" bind:value="{formData.password}" type="password" required>
				<span class="field__label field__label--required">Пароль</span>
			</label>
			<button class="button button--{ADMINZONE}" type="submit">Вход</button>
		</form>
	</div>
</section>

<style>
	form {
		margin: 0 auto;
	}

	button {
		width: 100%;
		margin-top: 1em;
	}
</style>
