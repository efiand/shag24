<script>
	import Parameters from '../components/parameters.svelte';
	import { allowFlag, shodhanFlag } from '../js/stores';
	import { backupDb } from '../js/utils';

	let showFlag = true;
	let backupProcess = false;
	let backupControl = null;

	async function logout() {
		showFlag = false;

		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/logout`
		})}`);

		if (res.ok) {
			localStorage.setItem(`allow`, ``);
			localStorage.setItem(`shodhan`, ``);
			allowFlag.set(false);
			shodhanFlag.set(false);
		}
	}

	async function backup() {
		backupControl.blur();
		backupProcess = true;
		await backupDb();
		backupProcess = false;
	}
</script>

<svelte:head>
	<title>Панель управления</title>
</svelte:head>

{#if showFlag}
	<section class="section">
		<h2 class="section__title">Панель управления</h2>
		<button class="button button_progressible center" bind:this={backupControl} disabled={backupProcess} on:click={backup}>
			Резервное копирование данных
		</button>
		<br>
		<button class="button center" on:click={logout}>Выход</button>
		<Parameters/>
	</section>

	{#if backupProcess}
		<div class="bottom-notify">Выполняется резервное копирование данных…</div>
	{/if}
{/if}
