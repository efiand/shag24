<script>
import { ADMINZONE, ADMINZONES } from '../js/const';
import { onMount } from 'svelte';
import { fade } from 'svelte/transition';
import { location, link, push } from 'svelte-spa-router';
import { DESKTOP_WIDTH, TOGGLE_DELAY } from '../js/const';

let openFlag = false; // открыто ли мобильное меню
let headerWidth;
let adminzone = ADMINZONE;

let menu = [
	{
		href: `/`,
		text: `Главная`
	},
	{
		href: `/sites`,
		text: `Сайты`
	},
	{
		href: `/pages`,
		text: `Страницы`
	},
	{
		href: `/images`,
		text: `Изображения`
	},
	{
		href: `/forms`,
		text: `Формы`
	},
	{
		href: `/events`,
		text: `Мероприятия`
	},
	{
		href: `/people`,
		text: `Люди`
	},
	{
		href: `/places`,
		text: `Места`
	},
	{
		href: `/tickets`,
		text: `Заявки`
	}
];

onMount(() => {
	document.body.classList.add(adminzone);
	resizeHandler();
});

function setAdminzone() {
	window.location.replace(window.location.href.replace(/^(.*admin.)(.*)([.-]ru.*)$/, (match, pre, oldAdminzone, post) => {
		return pre + (adminzone === `khara` ? `khara` : `shag24`) + post;
	}));
}

function toggleMenu() {
	openFlag = !openFlag;
}

function closeMenu() {
	if (headerWidth < DESKTOP_WIDTH) {
		openFlag = false;
	}
}

function resizeHandler() {
	if (headerWidth >= DESKTOP_WIDTH) {
		openFlag = true;
	}
}
</script>

<svelte:window on:resize={resizeHandler}/>

<header class="header js-fullwidth" bind:clientWidth={headerWidth}>
	<button class="toggler {openFlag ? `closer` : `opener`}" on:click={toggleMenu} aria-label="Закрыть меню"/>
	{#if openFlag}
		<nav class="{openFlag ? `opened` : `closed`}" transition:fade>
			<ul>
				{#each menu as {href, text}}
					<li>
						{#if href === $location}
							<span class="link">{text}</span>
						{:else}
							<a class="link" {href} use:link on:click={closeMenu}>{text}</a>
						{/if}
					</li>
				{/each}
			</ul>
			<form>
				<label class="field">
					<select class="field__input" bind:value={adminzone} on:change={setAdminzone}>
						{#each ADMINZONES as item}
							<option>{item}</option>
						{/each}
					</select>
					<span class="field__label">Админзона:</span>
				</label>
			</form>
		</nav>
	{/if}
</header>
