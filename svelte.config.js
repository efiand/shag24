const { globalStyle, less, postcss } = require(`svelte-preprocess`);

const dev = Boolean(process.env.ROLLUP_WATCH);

module.exports = {
	dev,
	preprocess: [
		less(),
		globalStyle(), // важно выполнять после less
		postcss({
			plugins: [
				require(`mqpacker`)(),
				require(`autoprefixer`)(),
				require(`cssnano`)()
			]
		})
	],
	css: (css) => css.write(`style.min.css`, dev),
	onwarn: (warning, handler) => {
		if (/a11y/.test(warning.code)) {
			return;
		}
		handler(warning);
	}
};
