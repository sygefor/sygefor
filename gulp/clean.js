'use strict';

var gulp = require('gulp'),
    livereload = require('gulp-livereload');

gulp.task('clean', ['styles:clean', 'scripts:clean', 'assets:clean', 'front:clean'] );
