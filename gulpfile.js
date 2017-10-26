var gulp = require('gulp'),
    sass = require('gulp-sass'),
    concat = require('gulp-concat'),
    minifyCSS = require('gulp-minify-css'),
    browserSync = require('browser-sync').create()
    ;

gulp.task('default', ['css', 'js', 'fonts']);

gulp.task('css', function () {
    gulp.src(['assets/scss/master.scss'])
        .pipe(concat('master.scss'))
        .pipe(sass('master.scss'))
        .pipe(minifyCSS())
        .pipe(gulp.dest('web/css/'))
        .pipe(browserSync.stream());
});

gulp.task('js', function () {
    gulp.src([
        'node_modules/jquery/dist/jquery.min.js',
        'assets/js/passwordCheck.js'
    ])
        .pipe(concat('app.js'))
        .pipe(gulp.dest('web/js/'))
        .pipe(browserSync.stream());

    // copy jquery.min.map
    gulp.src('node_modules/jquery/dist/jquery.min.map')
        .pipe(gulp.dest('web/js/'));
});

gulp.task('fonts', function () {
    gulp.src('assets/fonts/*')
        .pipe(gulp.dest('web/fonts/'));
});

gulp.task('watch', ['default'], function () {
    browserSync.init({
        proxy: 'localhost'
    });

    gulp.watch('assets/scss/*.scss', ['css']);
    gulp.watch('assets/js/*.js', ['js']);
    gulp.watch('src/**/*.html.twig').on('change', browserSync.reload);
});