import svelte from 'rollup-plugin-svelte';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import browsersync from 'rollup-plugin-browsersync';
import { terser } from 'rollup-plugin-terser';
import { string } from 'rollup-plugin-string';
import del from 'rollup-plugin-delete';

const production = !process.env.ROLLUP_WATCH;

export default {
	input: `source_admin/js/main.js`,
	output: {
		sourcemap: !production,
		format: `iife`,
		name: `app`,
		file: `public_admin/js/script.min.js`
	},
	plugins: [
		commonjs(),
		svelte(require(`./svelte.config`)),
		resolve({
			browser: true,
			dedupe: (importee) => importee === `svelte` || importee.startsWith(`svelte/`)
    }),
    string({
      include: 'source_html/sprite/**/*.svg'
    }),
		!production && browsersync({
			cors: true,
			notify: false,
			open: false,
			port: 5000,
			proxy: `https://admin.shag24-ru`,
			ui: false
		}),
    production && terser(),
    production && del({
      targets: [`**/*.map`, `!node_modules/**/*.map`]
    })
	],
	watch: {
		clearScreen: false
	}
};
