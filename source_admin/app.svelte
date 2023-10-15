<script>
	import Router, { location, push } from 'svelte-spa-router';
	import { allowFlag, shodhanFlag } from './js/stores';
	import Index from './pages/index.svelte';
	import Login from './pages/login.svelte';
	import Tickets from './pages/tickets.svelte';
	import Sites from './pages/sites.svelte';
	import Pages from './pages/pages.svelte';
	import Page from './pages/page.svelte';
	import Fragment from './pages/fragment.svelte';
	import Events from './pages/events.svelte';
	import EventTypes from './pages/event-types.svelte';
	import People from './pages/people.svelte';
	import Unit from './pages/unit.svelte';
	import Forms from './pages/forms.svelte';
	import Form from './pages/form.svelte';
	import Places from './pages/places.svelte';
	import Images from './pages/images.svelte';
	import Shodhan from './pages/shodhan.svelte';
	import Header from './components/header.svelte';

	let allowMode;
	let shodhanMode;
	const allowHandler = allowFlag.subscribe((value) => {
		allowMode = value;
	});
	const shodhanHandler = shodhanFlag.subscribe((value) => {
		shodhanMode = value;
	});

	const routes = {
		'/sites': Sites,
		'/pages': Pages,
		'/pages/:id': Page,
		'/fragments/:id': Fragment,
		'/events': Events,
		'/event-types': EventTypes,
		'/people': People,
		'/people/:id': Unit,
		'/forms': Forms,
		'/forms/:id': Form,
		'/places': Places,
		'/images': Images,
		'/tickets': Tickets,
		'*': Index
	};

	// Узнаем ширину полосы прокрутки
	const scrollWidth = window.innerWidth - document.documentElement.clientWidth;
	document.documentElement.style.setProperty(`--scrollbar-width`, `${scrollWidth}px`);
</script>

{#if allowMode}
	<Header/>
	<main>
		<Router {routes}/>
	</main>
{:else if shodhanMode}
	<Shodhan/>
{:else}
	<Login/>
{/if}

<style global lang="less">
  @import "less/style";
</style>
