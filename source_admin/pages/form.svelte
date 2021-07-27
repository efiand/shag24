<script>
	import { ADMINZONE } from '../js/const';
	import Preloader from '../components/preloader.svelte';
	import Editor from '../components/editor.svelte';
	import MCE from '../components/mce.svelte';
	import File from '../components/file.svelte';
	import { push } from 'svelte-spa-router';

	export let params = {};

	let formId = params.id;
	let form = {};
	let forms = [];
	let pages = [];

	const editform = {
		opened: false,
		pale: false
	};

	async function getForm() {
		const res = await fetch(`/api/?payload=${JSON.stringify({
			page: `/form`, id: formId
		})}`);
		if (res.ok) {
			const data = await res.json();
			if (data.form) {
				form = data.form;
				forms = data.forms;
				pages = data.pages;
			} else {
				// если форма с несуществующим номером или из другой админзоны, переходим на список
				push(`/forms`);
			}
		}
	}
	let asyncForm = getForm();

	async function saveForm() {
		editform.pale = true;
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({ page: `/form-edit`, form })
		});
		if (res.ok) {
			editform.pale = false;
			asyncForm = getForm();
		}
	}

	function changeForm() {
		formId = formId;
		push(`/forms/${formId}`);
		asyncForm = getForm();
	}
</script>

<svelte:head>
	<title>Управление настройками формы</title>
</svelte:head>

<section class="section">
	{#await asyncForm}
		<Preloader/>
		{:then res}
		<div class="filter">
			<label class="field form-choose">
				<select class="field__input" bind:value={formId} on:change={changeForm}>
					{#each forms as item}
						<option value={item.id}>№ {item.id}. {item.topic}{#if item.description}&nbsp;({item.description}){/if}</option>
					{/each}
				</select>
				<span class="field__label">Форма</span>
			</label>
			<button class="save button" on:click={saveForm}>Сохранить</button>
		</div>

		<div class="item item--choosed {editform.pale ? `pale` : ``}">
			<form class="item__form" on:submit|preventDefault={saveForm}>
				<div class="cols">
					<label class="field cols__half">
						<input class="field__input" bind:value={form.topic}>
						<span class="field__label">Тема заявки</span>
					</label>
					<label class="field cols__quart">
						<input class="field__input" bind:value={form.opener_title}>
						<span class="field__label">Текст кнопки открытия</span>
					</label>
					<label class="field cols__quart">
						<input class="field__input" bind:value={form.submit_title}>
						<span class="field__label">Текст кнопки отправки</span>
					</label>
					<label class="field cols__subtotal">
						<input class="field__input" bind:value={form.description}>
						<span class="field__label">Описание формы (для админки)</span>
					</label>
					<label class="checker cols__quart item__checker">
						<input class="sr-only" type="checkbox" bind:checked={form.has_city}>
						<span class="checker__label">Поле «Город»</span>
					</label>
					<label class="field cols__quart">
						<input class="field__input" bind:value={form.payment_target}>
						<span class="field__label">Назначение платежа (делает форму платёжной)</span>
					</label>
					<label class="field cols__quart">
						<input class="field__input" type="number" bind:value={form.price}>
						<span class="field__label">Стоимость заказа</span>
					</label>
					<label class="field cols__quart">
						<input class="field__input" type="number" bind:value={form.price_promocode}>
						<span class="field__label">Стоимость по промокоду (0 – не учитывать)</span>
					</label>
					<label class="checker cols__quart item__checker">
						<input class="sr-only" type="checkbox" bind:checked={form.has_instagram}>
						<span class="checker__label">Поле «Инстаграм»</span>
					</label>
					<label class="field cols__quart">
						<input class="field__input" bind:value={form.message_field}>
						<span class="field__label">Название поля сообщения (не задано – поля нет)</span>
					</label>
					<label class="checker cols__quart item__checker">
						<input class="sr-only" type="checkbox" bind:checked={form.require_message}>
						<span class="checker__label">Поле сообщения обязательно для заполнения</span>
					</label>
					<label class="field cols__quart">
						<input class="field__input" bind:value={form.promocode}>
						<span class="field__label">Значение промокода</span>
					</label>
					<label class="checker cols__quart item__checker">
						<input class="sr-only" type="checkbox" bind:checked={form.has_promocode}>
						<span class="checker__label">Поле «Промокод»</span>
					</label>
					<div class="field cols__total">
						<MCE value={form.res_status} on:change={({ detail }) => {
							form.res_status = detail.value;
							saveForm();
						}}/>
						<span class="field__label">Ответ пользователю на сайте</span>
					</div>
					<div class="field cols__total">
						<MCE value={form.promocode_status} on:change={({ detail, saveFlag }) => {
							if (saveFlag) {
								form.promocode_status = detail.value;
								saveForm();
							}
						}}/>
						<span class="field__label">Ответ пользователю на сайте <b>при совпадении контрольного промокода</b></span>
					</div>
					<div class="field cols__total">
						<Editor value={form.choose_field} on:change={({ detail }) => form.choose_field = detail.value}/>
						<span class="field__label">Структура списка выбора (пустое значение – нет такого списка)</span>
					</div>
					<label class="field cols__total">
						<input class="field__input" bind:value={form.payment_doc}>
						<span class="field__label">Название услуги в договоре</span>
					</label>
					<label class="field cols__total">
						<input class="field__input" bind:value={form.mail_promo_topic}
							placeholder="По умолчанию – тема письма, отправляемого по завершении регистрации/оплаты/теста">
						<span class="field__label">Тема письма, отправляемого по совпадению промокода</span>
					</label>
					<div class="field cols__total">
						<MCE value={form.mail_promo_body} on:change={({ detail }) => {
							form.mail_promo_body = detail.value;
							saveForm();
						}}/>
						<span class="field__label">Тело письма, отправляемого по совпадению промокода</span>
					</div>
					<label class="field cols__total">
						<input class="field__input" bind:value={form.mail_topic}>
						<span class="field__label">Тема письма, отправляемого по завершении регистрации/оплаты/теста</span>
					</label>
					<div class="field cols__total">
						{#if form.test}
							<!-- Для тестовых форм много условной логики, не нужно визуальный редактор -->
							<Editor value={form.mail_body} on:change={({ detail }) => form.mail_body = detail.value}/>
						{:else}
							<MCE value={form.mail_body} on:change={({ detail }) => {
								form.mail_body = detail.value;
								saveForm();
							}}/>
						{/if}
						<span class="field__label">Тело письма, отправляемого по завершении регистрации/оплаты/теста</span>
					</div>
					<label class="field cols__quart">
						<input class="field__input" bind:value={form.attach_name}>
						<span class="field__label">Отображаемое название вложения к письму</span>
					</label>
					<div class="field cols__quart">
						<!-- <input class="field__input" bind:value={form.attach}> -->
						<File name={form.attach} on:del={() => form.attach = ``} on:edit={({ detail }) => {
							form.attach = detail.value;
						}}/>
						<span class="field__label">Имя файла-вложения</span>
					</div>
					<label class="checker cols__quart item__checker">
						<input class="sr-only" type="checkbox" bind:checked={form.notify_uncompl_flag}>
						<span class="checker__label">Присылать уведомления о незавершённых платежах</span>
					</label>
					<div class="field cols__total">
						<MCE value={form.test} on:change={({ detail }) => {
							form.test = detail.value;
							saveForm();
						}}/>
						<span class="field__label">Разметка теста (для тестовых форм)</span>
					</div>
					<div class="cols__subtotal"/>
					<div class="item__submit cols__quart">
						<button class="button" type="submit">Сохранить</button>
					</div>
				</div>
			</form>
		</div>
	{/await}
</section>

<style lang="less">
	@import "../../source_html/less/vars";

	.form-choose {
		@media @desktop {
			max-width: 500px;
		}
	}

	.save {
		@media @desktop {
			width: 210px;
			margin-left: auto;
		}
	}
</style>
