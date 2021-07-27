const { src } = require(`gulp`);
const { eslint: eslintPlugin, lintspaces } = require(`gulp-load-plugins`)();

const eslint = () => src([`source_html/js/**/*.js`, `source_admin/js/**/*.js`, `gulpfile.js/**/*.js`])
	.pipe(eslintPlugin({
		fix: false
	}))
	.pipe(eslintPlugin.format())
	.pipe(lintspaces({
		editorconfig: `.editorconfig`
	}))
	.pipe(lintspaces.reporter());

module.exports = eslint;
