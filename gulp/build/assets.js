'use strict';

var gulp = require('gulp'),
    del = require('del');
var $ = require('gulp-load-plugins')({
  pattern: ['gulp-*']
});

// clean
gulp.task('assets:clean', function () {
  return del.sync(['web/assets/*', '!web/assets/scripts', '!web/assets/styles']);
});

// build copy assets
gulp.task('assets', ['assets:clean'], function () {
  return gulp.src('app/Resources/assets/**/*')
    .pipe(gulp.dest('web/assets'))
    .pipe($.size());
});
