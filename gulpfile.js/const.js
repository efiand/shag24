const svgoPrecision = {
	floatPrecision: 2
};

module.exports = {
	jsLintOnlySources: [
		`source_admin/js/**/*.js`,
		`gulpfile.js/**/*.js`
	],
	lessLintOnlySources: [
		`source_admin/less/**/*.less`,
		`source_html/less/**/*.less`
	],
	phpSources: [
		`app_admin/**/*.php`,
		`public_admin/**/*.php`,
		`app_html/**/*.php`,
		`public_html/**/*.php`
	],
	svgoConfig: {
		plugins: [
			{ removeViewBox: false },
			{ removeTitle: true },
			{ cleanupNumericValues: svgoPrecision },
			{ convertPathData: svgoPrecision },
			{ transformsWithOnePath: svgoPrecision },
			{ convertTransform: svgoPrecision },
			{ cleanupListOfValues: svgoPrecision }
		]
	},
	twigSources: [
		`app_html/**/*.twig`,
		`app_admin/**/*.twig`
	]
};
