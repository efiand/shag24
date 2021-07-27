const { series, watch } = require(`gulp`);
const tasks = require(`require-dir`)(`.`);
const { twighint, stylelint, eslint, phphint, css, js, sprite } = tasks;
const browserSync = require(`browser-sync`).create();
const { jsLintOnlySources, phpSources, twigSources } = require(`../const`);

const opts = {
	cors: true,
	notify: false,
	port: 3000,
	ui: false
};
const ARG_DASHES_COUNT = 2;
const siteArg = process.argv.find((item) => {
	return item.slice(0, ARG_DASHES_COUNT) === `--` && item.length > ARG_DASHES_COUNT;
});
if (siteArg) {
	// Пример запуска с проксированием домена (Open Server etc.): `npm start -- --khara-ru`
	opts.proxy = `https://${siteArg.slice(ARG_DASHES_COUNT)}`;
} else {
	opts.server = `public_html`;
	opts.open = false;
}

const reload = (done) => {
	browserSync.reload();
	done();
};

const server = () => {
	browserSync.init(opts);

	watch(twigSources, series(twighint));
	watch(phpSources, series(phphint));
	watch(`source_html/less/**/*.less`, series(stylelint, css, reload));
	watch(`source_admin/less/**/*.less`, series(stylelint));
	watch(`source_html/js/**/*.js`, series(eslint, js, reload));
	watch(jsLintOnlySources, series(eslint));
	watch(`source_html/svg/*.svg`, series(sprite, reload));
};

module.exports = server;
