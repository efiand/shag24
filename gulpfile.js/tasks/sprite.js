const { src, dest } = require(`gulp`);
const { imagemin, svgstore, rename } = require(`gulp-load-plugins`)();
const { svgoConfig } = require(`../const`);

const sprite = () => src(`source_html/sprite/*.svg`)
	.pipe(imagemin([imagemin.svgo(svgoConfig)]))
	.pipe(svgstore({
		inlineSvg: true
	}))
	.pipe(rename(`sprite.min.svg`))
	.pipe(dest(`app_html/templates`))
	.pipe(dest(`public_admin/img`))
	.pipe(dest(`public_html/img`));

module.exports = sprite;
