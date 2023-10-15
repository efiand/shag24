<script>
	import debounce from 'lodash.debounce';
	import { TICKETS_LIMIT, TOGGLE_DELAY } from '../js/const';
	import Preloader from '../components/preloader.svelte';
	import TelLinks from '../components/tel-links.svelte';
	import Datepicker from '../components/datepicker.svelte';
	import Del from '../components/del.svelte';

	let keywords = ``;
	let ticketPage = ``;
	let startDate = ``;
	let endDate = ``;
	let tickets = [];
	let pages = [];
	let	limit = TICKETS_LIMIT; // ограничение на число выводимых записей
	let paymentStatus = ``;

	$: exportParam = JSON.stringify({ ticketPage, keywords, startDate, endDate, limit });

	async function getTickets() {
		const res = await fetch(`/api/`, {
			method: `post`,
			body: JSON.stringify({
				page: `/tickets`,
				ticketPage,
				keywords,
				startDate,
				endDate,
				limit,
				paymentStatus
			})
		});
		if (res.ok) {
			const data = await res.json();
			tickets = data.tickets;
			pages = data.pages;
			ticketPage = pages.find((page) => page === ticketPage) || ``;
		}
	}
	let asyncTickets = getTickets();

	function getAll(evt) {
		evt.currentTarget.blur();

		keywords = ``;
		startDate = ``;
		endDate = ``;
		ticketPage = ``;
		paymentStatus = ``;
		limit = TICKETS_LIMIT;
		refresh();
	}

	function refresh() {
		asyncTickets = getTickets();
	}
</script>

<svelte:head>
	<title>Заявки</title>
</svelte:head>

<section class="section">
	<form class="filter" on:submit|preventDefault={refresh}>
		<label class="field keywords">
			<input class="field__input keywords" bind:value={keywords} on:input={debounce(refresh, TOGGLE_DELAY)}>
			<span class="field__label">Имя, тема, город, тел., mail, insta, сообщ., промокод, № заказа</span>
		</label>

		<label class="field">
			<select class="field__input" bind:value={ticketPage} on:change={refresh}>
				<option value="">Все</option>
				{#each pages as item}
					<option>{item}</option>
				{/each}
			</select>
			<span class="field__label">Страница</span>
		</label>

		<label class="field flatpickr-wrap">
			<Datepicker value={startDate} on:change={({ detail }) => {
				startDate = detail.value;
				refresh();
			}}/>
			<span class="field__label">С</span>
		</label>

		<label class="field flatpickr-wrap">
			<Datepicker value={endDate} on:change={({ detail }) => {
				endDate = detail.value;
				refresh();
			}}/>
			<span class="field__label">По</span>
		</label>

		<label class="field">
			<select class="field__input" bind:value={paymentStatus} on:change={refresh}>
				<option value="">Все</option>
				<option value="-">Неплатежная заявка</option>
				<option value="no">Не подтвержден</option>
				<option value="yes">Подтвержден</option>
			</select>
			<span class="field__label">Статус платежа</span>
		</label>

		<label class="field limit">
			<input class="field__input" type="number" min="50" step="50" bind:value={limit} on:input={debounce(refresh, TOGGLE_DELAY)}>
			<span class="field__label" title="Максимум на страницу">Не более</span>
		</label>

		<button class="button" type="button" on:click={getAll}>Очистить</button>
		<a class="button" href="/export?mode=xlsx&payload={exportParam}" download title="Экспорт по фильтрам">Excel</a>
		<a class="button" href="/export?mode=csv&payload={exportParam}" download title="Экспорт по фильтрам">CSV</a>
	</form>

	{#await asyncTickets}
		<Preloader/>
	{:then res}
		<p class="count">Найдено заявок: {tickets.length}</p>
		<table class="table">
			<thead>
				<tr>
					<th>Время</th>
					<th>Заявка</th>
					<th>Имя</th>
					<th>Город</th>
					<th>Контакты</th>
					<th>Прочие данные</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				{#each tickets as ticket (ticket.id)}
					{#if !ticket.del}
						<tr class={ticket.pale ? `pale` : ``}>
							<td class="table__first-cell">{ticket.create_time}</td>
							<td>
								<p>{ticket.topic}</p>
								<p><a href="//{ticket.page}" target="_blank">{ticket.page}</a></p>
							</td>
							<td>{ticket.name}</td>
							<td>
								{#if ticket.city}
									<p>{ticket.city}</p>
								{/if}
							</td>
							<td>
								<p><a href="mailto:{ticket.mail}">{ticket.mail}</p>
								{#if ticket.phone}
									<TelLinks tel={ticket.phone}/>
								{/if}
								{#if ticket.instagram}
									<a href="//www.instagram.com/{ticket.instagram}/" target="_blank">@{ticket.instagram}</a>
								{/if}
							</td>
							<td class="message">
								{#if ticket.order_id}
									<p>Номер заказа: {ticket.order_id}</p>
								{/if}
								{#if ticket.payment_status}
									<p>Статус платежа: {ticket.payment_status}</p>
								{/if}
								{#if ticket.promocode}
									<p>Промокод: {ticket.promocode}</p>
								{/if}
								{#if ticket.message}
									<p>Сообщение:<br><small>{ticket.message}</small></p>
								{/if}
							</td>
							<td class="del-cell">
								<Del dbtable="data_tickets" colvalue={ticket.id} on:del={() => ticket.del = true} on:start={() => ticket.pale = true}/>
							</td>
						</tr>
					{/if}
				{:else}
					<tr>
						<td colspan="7">
							<p class="center">
								{keywords || startDate || endDate ? `Ничего не найдено` : `Заявок нет`}
							</p>
						</td>
					</tr>
				{/each}
			</tbody>
		</table>
	{/await}
</section>

<style lang="less">
	@import "../../source_html/less/vars";

	.filter {
		margin-bottom: 1em;

		.button {
			padding-right: 15px;
			padding-left: 15px;
		}

		.flatpickr-wrap {
			flex-shrink: 0;

			@media @desktop {
				width: 120px;
			}
		}
	}

	.count {
		margin-bottom: 1em;
	}

	.keywords {
		@media @lg-desktop {
			min-width: 540px;
		}
	}

	.limit {
		@media @desktop {
			max-width: 120px;
		}
	}

	.message {
		@media @lg-desktop {
			width: 400px;
		}
	}
</style>
