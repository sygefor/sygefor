'use strict';

var gulp = require('gulp'),
    del = require('del');
var $ = require('gulp-load-plugins')({
    pattern: ['gulp-*']
});

// clean
gulp.task('styles:clean', function () {
    return del.sync(['web/assets/styles']);
});

// build styles
gulp.task('styles', ['styles:clean'], function () {
    // pdf.scss
    gulp.src('app/Resources/scss/pdf.scss')
        .pipe($.sass({
            precision: 10
        }).on('error', $.util.log))
        .pipe(gulp.dest('web/assets/styles'))
        .pipe($.livereload())
        .pipe($.size());

    // styles.scss
    return gulp.src(['app/Resources/scss/**/*.scss', '!app/Resources/scss/pdf.scss'])
    //.pipe($.cached('less'))
        .pipe($.sass({
            precision: 10
        }).on('error', $.util.log))
        .pipe(gulp.dest('web/assets/styles'))
        .pipe($.livereload())
        .pipe($.size());
});

// build dist styles
gulp.task('styles:dist', ['styles'], function() {
    return gulp.src('web/assets/styles/*.css')
        .pipe($.cssmin().on('error', $.util.log))
        .pipe(gulp.dest('web/assets/styles'))
        .pipe($.size());
});