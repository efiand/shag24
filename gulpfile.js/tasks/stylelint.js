const { src } = require(`gulp`);
const { stylelint: stylelintPlugin, lintspaces } = require(`gulp-load-plugins`)();
const { lessLintOnlySources } = require(`../const`);

const stylelint = () => src(lessLintOnlySources)
	.pipe(stylelintPlugin({
		reporters: [
			{
				console: true,
				formatter: `string`
			}
		]
	}))
	.pipe(lintspaces({
		editorconfig: `.editorconfig`
	}))
	.pipe(lintspaces.reporter());

module.exports = stylelint;
