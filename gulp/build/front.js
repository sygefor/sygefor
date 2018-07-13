'use strict';

var gulp = require('gulp'),
    del = require('del');
var $ = require('gulp-load-plugins')({
    pattern: ['gulp-*']
});

// clean
gulp.task('front:clean', function () {
    return del.sync(['web/assets/front']);
});

// build styles
gulp.task('front', ['front:clean'], function () {
    // scss
    gulp.src([
        'src/FrontBundle/Resources/scss/**/*.scss',
        'src/FrontBundle/Resources/css/**/*.css'
    ])
        .pipe($.sass({
            precision: 10
        }).on('error', $.util.log))
        .pipe($.include().on('error', $.util.log))
        .pipe($.concat('styles.css'))
        .pipe(gulp.dest('web/assets/front'))
        .pipe($.livereload())
        .pipe($.size());

    // scripts
    gulp.src(
        [
            'node_modules/jquery/dist/jquery.js',
            'src/FrontBundle/Resources/scripts/**/*.js',
            'node_modules/bootstrap-sass/assets/javascripts/bootstrap.js'
        ])
        .pipe($.include().on('error', $.util.log))
        .pipe($.concat('scripts.js'))
        .pipe(gulp.dest('web/assets/front'))
        .pipe($.livereload());
});

// build dist styles
gulp.task('front:dist', ['front'], function() {
    // scss
    gulp.src('web/assets/front/*.css')
        .pipe($.cssmin().on('error', $.util.log))
        .pipe(gulp.dest('web/assets/front'))
        .pipe($.size());

    // scripts
    return gulp.src(['web/assets/front/*.js'])
        .pipe($.ngAnnotate())
        .pipe($.uglify().on('error', $.util.log))
        .pipe(gulp.dest('web/assets/front'))
        .pipe($.size());
});