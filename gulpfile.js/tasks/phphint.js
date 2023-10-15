const { src } = require(`gulp`);
const { lintspaces } = require(`gulp-load-plugins`)();
const { phpSources } = require(`../const`);

const phphint = () => src(phpSources)
	.pipe(lintspaces({
		editorconfig: `.editorconfig`
	}))
	.pipe(lintspaces.reporter());

module.exports = phphint;
