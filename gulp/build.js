'use strict';

require('require-dir')('./build');
var gulp = require('gulp');
var $ = require('gulp-load-plugins')({
    pattern: ['gulp-*']
});

/**
 * build
 **/
gulp.task('build:dist', ['clean', 'styles:dist', 'scripts:dist', 'front:dist', 'assets']);