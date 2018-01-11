var gulp = require('gulp');

gulp.task('rjs', function () {
    var requirejsOptimize = require('gulp-requirejs-optimize');
    return gulp.src('src/assets/js/src/main.js')
        .pipe(requirejsOptimize(function (file) {
            return {
                preserveLicenseComments: false,
                optimize: 'uglify',
                wrap: true,
                baseUrl: './src/assets/js/src',
                name: "almond",
                include: "main",
                out: "mailoptin.min.js"
            };
        }))
        .pipe(gulp.dest('src/assets/js'));
});

gulp.task('optimizecss', function () {
    var concatCss = require('gulp-concat-css');
    var cleanCSS = require('gulp-clean-css');
    return gulp.src(['src/assets/css/mailoptin.css', 'src/assets/css/animate.css'])
        .pipe(concatCss("mailoptin.min.css"))
        .pipe(cleanCSS({compatibility: 'ie8'}))
        .pipe(gulp.dest('src/assets/css'));
});

gulp.task('default', ['rjs', 'optimizecss']);