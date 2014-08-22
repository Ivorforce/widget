var gulp        = require('gulp');
var rev         = require('gulp-rev');
var gutil       = require('gulp-util');
var autoprefix  = require('gulp-autoprefixer');
var minifyCSS   = require('gulp-minify-css');
var sass        = require('gulp-sass');
var uglify      = require('gulp-uglify');
var concat      = require('gulp-concat');
var rename      = require('gulp-rename');
var extend      = require('gulp-extend');
var del         = require('del');
var exec        = require('child_process').exec;
var sys         = require('sys');

gulp.task('clean', function (cb) {
    del(['build/**/*', 'public/assets/css/*.css', 'public/assets/js/*.js'], cb);
});

gulp.task('css', function () {
    return gulp.src(['assets/css/*.scss'])
        .pipe(sass().on('error', gutil.log))
        .pipe(autoprefix('last 10 version'))
        // .pipe(minifyCSS())
        .pipe(rev())
        .pipe(gulp.dest('public/assets/css'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('build/css'))
        .pipe(rename('css.json'))
        .pipe(gulp.dest('build'))
});

gulp.task('javascript', function () {
    return gulp.src(['bower_components/jquery/dist/jquery.min.js', 'bower_components/mustache.js/mustache.js', 'assets/js/wizard.js', 'assets/js/*.js'])
        .pipe(concat('website.js'))
        .pipe(rev())
        .pipe(gulp.dest('public/assets/js'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('build/js'))
        .pipe(rename('js.json'))
        .pipe(gulp.dest('build'))
});

gulp.task('assets', ['clean', 'css', 'javascript'], function (cb) {
    return gulp.src('build/*.json')
        .pipe(extend('assets.json', true, 1))
        .pipe(gulp.dest('public/assets'));
});

gulp.task('watch', function () {
    gulp.watch('assets/**/*', ['assets']);
});

gulp.task('default', ['assets', 'watch']);
