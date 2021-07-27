// Копирование готовых дополнительных бандлов tinymce в билд

const { src, dest } = require(`gulp`);
const { changed } = require(`gulp-load-plugins`)();

const copySource = [
	`node_modules/tinymce/{icons,plugins,themes,skins}/**/*.min.{js,css}`,
	`node_modules/tinymce/**/*.woff`
];

const tinymce = () => src(copySource)
	.pipe(changed(`public_admin/js`))
	.pipe(dest(`public_admin/js`));

module.exports = tinymce;
