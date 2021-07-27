const { src, dest } = require(`gulp`);

const js = () => src(`source_html/js/entries/*.js`)
	.pipe(require(`vinyl-named`)())
	.pipe(require(`webpack-stream`)(require(`../../webpack.config`), require(`webpack`)))
	.pipe(dest(`public_html/js`));

module.exports = js;
