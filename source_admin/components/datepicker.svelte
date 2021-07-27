<script>
	import { onMount } from 'svelte';
	import flatpickr from 'flatpickr';
	import { createEventDispatcher } from 'svelte';

	export let enableTime = false; // выбор даты со временем
	export let value = ``;
	export let required = false;
	let datePicker = null;

	const dispatch = createEventDispatcher();

	onMount(() => {
		datePicker.flatpickr({
			dateFormat: enableTime ? `Y-m-d H:i` : `Y-m-d`,
			defaultHour: 0,
			enableTime,
			minuteIncrement: 15,
			time_24hr: true,
			allowInput: required
		});
	});
</script>

<input class="field__input" placeholder="Нажмите" bind:this={datePicker} bind:value={value}
	on:input={dispatch(`change`, { value })} {required}>

<style>
	:global(.flatpickr-input[readonly]) {
		color: var(--color-blacky);
		background-color: var(--color-white);
		cursor: pointer;
	}
</style>
