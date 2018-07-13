'use strict';

var gulp = require('gulp'),
    del = require('del');
var $ = require('gulp-load-plugins')({
    pattern: ['gulp-*']
});

/**
 * angular templates
 **/
gulp.task('templates', function () {
    return gulp.src(
        [
            'app/Resources/public/ng/**/*.html',
            'common/sygefor/core-bundle/Resources/public/ng/**/*.html',
            'src/ActivityReportBundle/Resources/public/ng/**/*.html'
        ])
        //.pipe($.size())
        .pipe($.angularHtmlify())
        .pipe($.htmlmin({
            removeComments: true,
            collapseWhitespace: true
        }))
        .pipe($.ngHtml2js({
            moduleName: 'conjecto.sygefor.app',
            prefix: '',
            declareModule: false
        }))
        .pipe($.concat('templates.js'))
        .pipe(gulp.dest('web/assets/scripts'));
});

/*
 * Templates

gulp.task('templates', function(){
    return gulp.src(assets.templates)
        .pipe(templateCache({
            module: 'conjecto.sygefor.app',
            base: function(file) {
                var path = file.path.replace(file.base, '');
                if (process.platform === 'win32') {
                    path = path.replace(/\\/g, '/');
                }
                //return path.replace(/^.+\/(\w+Bundle)\/Resources\/public\/ng\//g, '$1/');
                var regex = /^.+\/(\w+Bundle)\/Resources\/public\/ng\/(.*)$/g;
                var result = regex.exec(path);
                if(result) {
                    return result[1].toLowerCase() + '/' + result[2];
                } else {
                    return path;
                }
            }
        }))
        .pipe(gulp.dest('web/build/'));
    //.pipe(notify({ message: 'templated!' }));
});
*/