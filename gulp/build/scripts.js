'use strict';

var gulp = require('gulp'),
    del = require('del');
var $ = require('gulp-load-plugins')({
    pattern: ['gulp-*']
});

// clean
gulp.task('scripts:clean', ['ckeditor:clean'], function () {
    return del.sync(['web/assets/scripts']);
});

// build scripts
gulp.task('scripts', ['scripts:clean', 'templates', 'ckeditor'], function() {
    gulp.src(['app/Resources/scripts/*.js', 'web/assets/scripts/templates.js'])
        .pipe($.include().on('error', $.util.log))
        .pipe($.concat('scripts.js'))
        .pipe(gulp.dest('web/assets/scripts'))
        .pipe($.livereload());
});

// build dist scripts
gulp.task('scripts:dist', ['scripts'], function() {
    return gulp.src(['web/assets/scripts/*.js', '!web/assets/scripts/ckeditor.js'])
        .pipe($.ngAnnotate())
        .pipe($.uglify().on('error', $.util.log))
        .pipe(gulp.dest('web/assets/scripts'))
        .pipe($.size());
});
