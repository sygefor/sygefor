'use strict';

var gulp = require('gulp'),
    livereload = require('gulp-livereload');

gulp.task('watch', ['styles', 'scripts', 'front', 'assets'] ,function () {
    livereload.listen();
    gulp.watch([
        'app/Resources/styles/**/*.scss',
        'common/sygefor/core-bundle/Resources/styles/**/*.scss',
        'src/**/Resources/assets/css/*.css',
        'src/**/Resources/scss/**/*.scss'
    ], ['styles']);

    gulp.watch([
        'app/Resources/scripts/**/*.js',
        'common/sygefor/core-bundle/Resources/scripts/**/*.js',
        'src/**/Resources/scripts/**/*.js',

        'app/Resources/public/ng/**/*.js',
        'common/sygefor/core-bundle/Resources/public/ng/**/*.js',

        'app/Resources/public/ng/**/*.html',
        'common/sygefor/*/Resources/public/ng/**/*.html',
        'src/*/Resources/public/ng/**/*.html'
    ], ['scripts']);
});
