'use strict';

var gulp = require('gulp');
var concat = require('gulp-concat');
var sass = require('gulp-sass');
var cleanCss = require('gulp-clean-css');
var minifyJs = require('gulp-minify');

async function defaultTask() {
  // Gets all files with .scss extension inside 'scss' folder and childrens.
  gulp.src('scss/**/*.scss')
    .pipe(sass())
    .pipe(cleanCss({
        compatibility: 'ie8'
    }))
    .pipe(concat('style.min.css'))
    .pipe(gulp.dest('assets/css'));
  // Gets all files with .js extension inside 'js' folder and childrens.
  gulp.src(['js/**/*.js'])
    .pipe(minifyJs({
        // Do not include the source files.
        noSource: true
    }))
    .pipe(concat('script.min.js'))
    .pipe(gulp.dest('assets/js'));
}

exports.default = defaultTask;
