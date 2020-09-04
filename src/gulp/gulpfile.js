'use strict';

var gulp = require('gulp');
var concat = require('gulp-concat');
var sass = require('gulp-sass');
var minifyCSS = require('gulp-minify-css');
var minifyJS = require('gulp-minify');

gulp.task('minify-scss_to_css', function() {
// Gets all files ending with .scss inside 'scss' folder and childrens.
  return gulp.src('scss/**/*.scss') 
    .pipe(sass())
    .pipe(minifyCSS())
    .pipe(concat('style.min.css'))
    .pipe(gulp.dest('assets/css'));
});

gulp.task('minify-js', function() {
  return gulp.src(['js/**/*.js'])
    .pipe(minifyJS({
        noSource: true
    }))
    .pipe(concat('script.min.js'))
    .pipe(gulp.dest('assets/js'));
});
