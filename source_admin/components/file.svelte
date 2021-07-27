<script>
	import Icon from 'svelte-icon';
	import CopyIcon from '../../source_html/sprite/copy.svg';
	import DelIcon from '../../source_html/sprite/del.svg';
	import { MEDIA_DIR } from '../js/const';
	import { copyToClipboard } from '../js/utils';
	import { createEventDispatcher } from 'svelte';

	export let name = ``;
	export let imageFlag = false;
	const dispatch = createEventDispatcher();
	let url = name ? `${MEDIA_DIR}${name}?${new Date().getTime()}` : ``;
	let src = url;
	let fileData = {
		name,
		oldName: name, // нужно в случае замены изображения
		delete: ``,
		modified: false
	};
	let fileField = null; // поля выбора файла

	async function editHandler() {
		fileData.disabled = true;
		fileData.pale = true;

		const data = new FormData();
		data.append(`file`, fileData.file);
		data.append(`payload`, JSON.stringify({
			page: `/file-edit`,
			fileData
		}));

		const res = await fetch(`/api/`, {
			method: `post`,
			body: data
		});
		if (res.ok) {
			fileData.disabled = false;
			fileData.pale = false;
			dispatch(`edit`, { value: fileData.name });
		}
	}

	async function deleteHandler() {
		fileData.disabled = true;
		fileData.pale = true;

		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/file-edit`,
			fileData: {
				delete: fileData.oldName
			}
		})}`);
		if (res.ok) {
			fileData.name = ``;
			fileData.disabled = false;
			fileData.pale = false;
			dispatch(`del`);
		}
	}

	function pickHandler() {
		const file = fileField.files[0];
		const fileName = file.name.toLowerCase().replace(/\s/g, '-');

		if (fileName.slice(-4) === `.jpg` || fileName.slice(-4) === `.png`) {
			const reader = new FileReader();

			reader.addEventListener(`load`, () => {
				src = reader.result;
			});

			reader.readAsDataURL(file);
		}

		fileData.file = file;
		fileData.modified = true;
		if (!fileData.oldName) {
			fileData.name = fileName;
		}

		if (!imageFlag) {
			// для attach-файлов сохраняем сразу по выбору
			editHandler();
			fileData.oldName = fileName;
		}
	}
</script>

<div class="file-form {fileData.pale ? `pale` : ``} {imageFlag ? `` : `file-form--attach`}">
	{#if imageFlag && src}
		<div class="img-wrap">
			<label>
				<input class="sr-only" type="file" bind:this={fileField} on:change={pickHandler} disabled={fileData.disabled}>
				<img {src} alt="" loading="lazy">
			</label>
		</div>
	{:else}
		<label class="choose">
			<input class="sr-only" type="file" bind:this={fileField} on:change={pickHandler} disabled={fileData.disabled}>
			<span class="link">
				{fileData.file ? `Перевыбрать` : `Выбрать`}
				{#if imageFlag}
					{' (jpg, png)'}
				{/if}
			</span>
		</label>
	{/if}
	<div class="wrap">
		<label class="field" aria-label="Имя файла">
			<input class="field__input" bind:value={fileData.name} disabled={fileData.disabled}>
		</label>

		<button class="button" type="button" title="Сохранить изменения" on:click={editHandler} disabled={fileData.disabled}>
			Save
		</button>
		{#if fileData.oldName}
			{#if url}
				<button class="link copy" type="button" title="Копировать путь к картинке для вставки"
					on:click={() => copyToClipboard(url.slice(0, url.indexOf(`?`)))}>
					<Icon data={CopyIcon}/>
				</button>
			{/if}
			<button class="link" type="button" aria-label="Удалить изображение"
				on:click={deleteHandler} disabled={fileData.disabled}>
				<Icon data={DelIcon}/>
			</button>
		{/if}
	</div>
</div>

<style lang="less">
	.file-form {
		position: relative;
		width: 100%;
	}

	.img-wrap {
		label {
			cursor: pointer;
		}

		img {
			display: block;
			margin: 0 auto;
			max-height: 300px;
		}
	}

	.wrap {
		display: flex;
		align-items: center;
		padding: 0.5em 1em;
		background-color: var(--color-grey);

		.field {
			margin-bottom: 0;
		}

		.button {
			margin-left: -1px;
			padding-right: 12px;
			padding-left: 12px;
			border-top-left-radius: 0;
			border-bottom-left-radius: 0;

			& + .link {
				margin-left: 0.5em;
			}
		}
	}

	.copy {
		margin: 0 0.5em;
	}

	.choose {
		position: absolute;
		bottom: calc(100% + 4px);
		right: 0;
		cursor: pointer;
	}

	.file-form--attach {
		.wrap {
			padding: 0;
			background-color: transparent;
		}
	}
</style>
