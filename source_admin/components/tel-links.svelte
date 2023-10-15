<script>
	import Icon from 'svelte-icon';
	import Telegram from '../../source_html/sprite/telegram.svg';
	import Viber from '../../source_html/sprite/viber.svg';
	import WhatsApp from '../../source_html/sprite/whatsapp.svg';
	import {formatTel, numberize} from '../js/utils';

	export let tel;
	let link = numberize(tel);
</script>

<p>
	<a class="icon-link" href="//wa.me/{link}" target="_blank" title="Написать в WhatsApp">
		<Icon data={WhatsApp}/>
	</a>
	<a class="icon-link" href="viber://chat?number={link}" target="_blank" title="Написать в Viber">
		<Icon data={Viber}/>
	</a>
	<a class="icon-link" href="//t.me/joinchat/{link}" target="_blank" title="Написать в Telegram">
		<Icon data={Telegram}/>
	</a>
	<a class="tel-link" href="tel:{formatTel(tel)}" title="Позвонить">{tel}</a>
</p>

<style lang="less">
	p {
		display: flex;

		a {
			display: block;
			text-decoration: none;
		}
	}

	.icon-link {
		line-height: 0.75;

		& + & {
			margin-left: 0.25em;
		}
	}

	.tel-link {
		margin-left: 0.5em;
	}
</style>
