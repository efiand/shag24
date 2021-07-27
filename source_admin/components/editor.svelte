<script>
	import { createEventDispatcher } from 'svelte';
	export let value = ``;
	export let placeholder = ``;

	let isOpened = false;
	let offset = 0;
	const dispatch = createEventDispatcher();

	function toggleHandler() {
		isOpened = !isOpened;

		if (isOpened) {
			offset = window.pageYOffset;
			document.body.classList.add(`popup-mode`);
		} else {
			document.body.classList.remove(`popup-mode`);
			setTimeout(() => window.scrollTo(0, offset), 100);
		}
	}
</script>

<div class="editor {isOpened ? `opened` : ``}">
	<button class="link" type="button" on:click={toggleHandler}>
		{isOpened ? `Свернуть` : `Развернуть`}
	</button>
	<textarea class="field__input field__input--message" bind:value={value} spellcheck="false"
		{placeholder} on:change={dispatch(`change`, { value })}/>
</div>

<style lang="less">
	@import "../../source_html/less/vars";

	.editor {
		position: relative;

		@media @mobile-only {
			margin-top: 2em;
		}
	}

	.opened {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		padding: 40px 0 0;
		background-color: var(--color-grey);
		z-index: 2;

		textarea {
			height: calc(100vh - 40px);
		}

		.link {
			top: 10px;
			right: 10px;
		}
	}

	.link {
		position: absolute;
		top: -24px;
		right: 0;
	}
</style>
