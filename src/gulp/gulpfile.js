'use strict';

var gulp = require('gulp');
var concat = require('gulp-concat');
var sass = require('gulp-sass');
var cleanCSS = require('gulp-clean-css');
var minifyJS = require('gulp-minify');

async function defaultTask() {
  // Gets all files with .scss extension inside 'scss' folder and childrens.
  gulp.src('scss/**/*.scss')
    .pipe(sass())
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(concat('style.min.css'))
    .pipe(gulp.dest('assets/css'));
  // Gets all files with .js extension inside 'js' folder and childrens.
  gulp.src(['js/**/*.js'])
    .pipe(minifyJS({
        // Do not include the source files.
        noSource: true
    }))
    .pipe(concat('script.min.js'))
    .pipe(gulp.dest('assets/js'));
}

exports.default = defaultTask;
