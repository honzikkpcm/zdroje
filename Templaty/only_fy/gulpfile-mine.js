var gulp         = require('gulp'),
    sass         = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    pixrem       = require('gulp-pixrem'),
    concat       = require('gulp-concat'),
    uglify       = require('gulp-uglify'),
    del          = require('del'),
    plumber      = require('gulp-plumber'),
    svgstore     = require('gulp-svgstore'),
    rename       = require("gulp-rename"),
    svgmin       = require('gulp-svgmin'),
    pump         = require('pump'),
    gutil        = require('gulp-util'),
    through2     = require('through2'),
    fileinclude = require('gulp-file-include'),
    cheerio      = require('cheerio');


gulp.task('fileinclude', function () {
    gulp.src(['*.html'])
        .pipe(fileinclude({
            prefix: '@@',
            basepath: '@file'
        }))
        .pipe(gulp.dest('dist'));
});

gulp.task('stylesheets', function() {
    return gulp.src('stylesheets/main.scss')
        .pipe(plumber({
            errorHandler: function(error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(sass({
            outputStyle: 'compressed'
        }))
        .pipe(autoprefixer({
            browsers: ['last 2 versions'],
            cascade: false
        }))
        .pipe(pixrem())
        .pipe(gulp.dest('../css'));
});

gulp.task('images', function() {
    return gulp.src('assets/images/**/*')
        .pipe(gulp.dest('../assets/images/'));
});

gulp.task('webfonts', function() {
    return gulp.src('assets/webfonts/**')
        .pipe(gulp.dest('../assets/webfonts/'));
});

gulp.task('javascript', function() {
    return gulp.src('javascript/*.js')
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error);
                this.emit('end');
            }
        }))
        .pipe(concat('script.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('../js'));
});

gulp.task('vendor', function () {
    return gulp.src('javascript/vendors/*.js')
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error);
                this.emit('end');
            }
        }))
        .pipe(concat('vendors.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('../js/vendors'));
});

gulp.task('clean', function(cb) {
    del(['css', 'assets/images', 'js', 'partials', '*.html'], cb)
});

gulp.task('default', ['clean'], function() {
    gulp.start('stylesheets', 'images', 'webfonts', 'javascript');
});

gulp.task('svgstore', function () {
    return gulp
        .src('assets/images/svg/**/*.svg')
        .pipe(svgmin())
        .pipe(svgstore())
        .pipe(through2.obj(function (file, encoding, cb) {
            var $ = cheerio.load(file.contents.toString(), {xmlMode: true});
            var data = $('svg > symbol').map(function () {
                return {
                    name: $(this).attr('id'),
                    viewBox: $(this).attr('viewBox')
                };
            }).get();
            var jsonFile = new gutil.File({
                path: 'metadata.json',
                contents: new Buffer(JSON.stringify(data))
            });
            this.push(jsonFile);
            this.push(file);
            cb();
        }))
        .pipe(rename({basename: 'svgSprite'}))
        .pipe(gulp.dest('../assets/images/svg'));
});

gulp.task('watch', ['stylesheets', 'images', 'svgstore', 'webfonts', 'javascript', 'vendor' ], function() {
// gulp.task('watch', ['fileinclude', 'stylesheets', 'images', 'svgstore', 'webfonts', 'javascript', 'vendor' ], function() {
    
    
    gulp.watch('**/*.html', ['fileinclude']);

    gulp.watch('stylesheets/**/*.scss', ['stylesheets']);

    gulp.watch('assets/images/**/*', ['images']);
    
    gulp.watch('assets/images/svg/**/*.svg', ['svgstore']);

    gulp.watch('assets/webfonts/*', ['webfonts']);

    gulp.watch('javascript/*.js', ['javascript']);

    gulp.watch('javascript/vendors/*.js', ['vendor']);

});