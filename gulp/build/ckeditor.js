'use strict';

var gulp = require('gulp'),
    del = require('del');
var $ = require('gulp-load-plugins')({
    pattern: ['gulp-*']
});

// clean
gulp.task('ckeditor:clean', function () {
    return del.sync(['web/assets/ckeditor', 'web/assets/scripts/ckeditor.js']);
});

// build scripts
gulp.task('ckeditor', ['ckeditor:clean'], function() {
    gulp.src(['bower_components/ckeditor/*','bower_components/ckeditor/*/**'])
        .pipe(gulp.dest('web/ckeditor'));

    gulp.src(['bower_components/base64image/*','bower_components/base64image/*/**'])
        .pipe(gulp.dest('web/ckeditor/plugins/base64image'));

    gulp.src(['bower_components/ckeditor/ckeditor.js', 'bower_components/ckeditor/lang/fr.js'])
        .pipe($.concat('ckeditor.js'))
        .pipe($.include().on('error', $.util.log))
        .pipe(gulp.dest('web/assets/scripts'));
});
