const { src, dest } = require(`gulp`);
const { less, postcss, rename } = require(`gulp-load-plugins`)();

const css = () => src(`source_html/less/entries/*.less`)
	.pipe(less())
	.pipe(postcss([
		require(`mqpacker`),
		require(`autoprefixer`),
		require(`cssnano`)
	]))
	.pipe(rename({
		suffix: `.min`
	}))
	.pipe(dest(`public_html/css`));

module.exports = css;
