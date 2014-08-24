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
    del(['build/**/*', 'public/assets/css/*.css'], cb);
});

gulp.task('css', ['clean'], function () {
    return gulp.src(['assets/css/*.scss', 'assets/css/widgets/*.scss'])
        .pipe(sass().on('error', gutil.log))
        .pipe(autoprefix('last 10 version'))
        // .pipe(minifyCSS())
        .pipe(rev())
        .pipe(gulp.dest('public/assets/css'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('public/assets'))
        .pipe(rename('assets.json'))
        .pipe(gulp.dest('public/assets'))
});

gulp.task('watch', function () {
    gulp.watch('assets/**/*', ['css']);
});

gulp.task('default', ['css', 'watch']);
