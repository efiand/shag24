'use strict';

const { series, parallel } = require(`gulp`);
const tasks = require(`require-dir`)(`tasks`);
const { phphint, twighint, stylelint, eslint, css, js, sprite, tinymce, server } = tasks;

const test = parallel(phphint, twighint, stylelint, eslint);
const build = series(test, parallel(css, js, sprite, tinymce));

exports.test = test;
exports.build = build;
exports.default = series(build, server);
