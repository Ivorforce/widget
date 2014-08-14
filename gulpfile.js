var gulp        = require('gulp');
    rev         = require('gulp-rev');
    gutil       = require('gulp-util');
    autoprefix  = require('gulp-autoprefixer');
    minifyCSS   = require('gulp-minify-css');
    less        = require('gulp-less');
    del         = require('del');
    exec        = require('child_process').exec;
    sys         = require('sys');

gulp.task('clean', function (cb) {
    del(['public/assets/*.css', 'public/assets/*.js'], cb);
});

gulp.task('css', ['clean'], function () {
    return gulp.src(['assets/less/*.less'])
        .pipe(less({ style: 'compressed' }).on('error', gutil.log))
        .pipe(autoprefix('last 10 version'))
        .pipe(minifyCSS())
        .pipe(rev())
        .pipe(gulp.dest('public/assets'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('public/assets'))
});

gulp.task('watch', function () {
    gulp.watch('assets/less/**/*.less', ['css']);
});

gulp.task('default', ['css', 'watch']);
