<script>
	import Icon from 'svelte-icon';
	import DelIcon from '../../source_html/sprite/del.svg';
	import { createEventDispatcher } from 'svelte';

	export let dbtable; // имя таблицы из БД
	export let colname = `id`; // имя столбца, по которому ищется запись
	export let colvalue; // значение ячейки, по которому ищется запись
	export let stringFlag = false; // значение не строковое

	const dispatch = createEventDispatcher();
	let disabled = false;
	let isOpened = false;
	let offset = 0;

	function toggleHandler() {
		isOpened = !isOpened;

		if (isOpened) {
			offset = window.pageYOffset;
			document.body.classList.add(`popup-mode`);
			document.body.style.top = `-${offset}px`;
		} else {
			document.body.classList.remove(`popup-mode`);
			window.scrollTo(0, offset);
		}
	}

	async function deleteHandler() {
		toggleHandler();

		dispatch(`start`);

		disabled = true;
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/delete`,
			dbtable,
			colname,
			colvalue,
			stringFlag
		})}`);

		dispatch(`del`);
	}
</script>

<button class="link" type="button" aria-label="Удалить запись" on:click={toggleHandler} {disabled}>
	<Icon data={DelIcon}/>
</button>

<span class="popup {isOpened ? `popup--ready` : ``}">
	<span class="del-popup popup__inner">
		<span class="heading">Вы действительно хотите удалить запись?</span>
		<span class="buttons">
			<button class="button" type="button" on:click={deleteHandler}>
				Да
			</button>
			<button class="button" type="button" on:click={toggleHandler}>
				Нет
			</button>
		</span>
	</span>
</span>

<style lang="less">
	@import "../../source_html/less/vars";

	.link {
		display: table;
		width: 20px;
		height: 20px;
		margin-left: auto;
	}

	.heading {
		display: block;
		margin-bottom: 1rem;
	}

	.buttons {
		display: grid;
		grid-gap: 0.5rem;

		@media @tablet {
			grid-template-columns: 1fr 1fr;
		}

		.button {
			color: var(--color-white);

			&:hover,
			&:focus {
				color: var(--color-primary);
			}
		}
	}

	.del-popup {
		padding: 2rem;
	}

	:global(.popup-mode),
	:global(.popup-mode .js-fullwidth) {
		width: calc(100vw - var(--scrollbar-width));
	}
</style>
