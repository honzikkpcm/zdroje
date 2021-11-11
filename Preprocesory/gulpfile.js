const mozjpeg = require('imagemin-mozjpeg')
const pngquant = require('imagemin-pngquant');
const imagemin = require('gulp-imagemin');
let gulp = require('gulp')
let sftp = require('gulp-sftp-up4');
let rev = require('gulp-rev')
let concat = require('gulp-concat')
let rename = require('gulp-rename')
let replace = require('gulp-replace')
let clean = require('gulp-clean')
let uglify = require('gulp-uglify')
let autoprefixer = require('gulp-autoprefixer')
let cssnano = require('gulp-cssnano')
let htmlmin = require('gulp-htmlmin')
let inject = require('gulp-inject')
let file = require('gulp-file')
let stylus = require('gulp-stylus')
let pug = require('gulp-pug')
let rollup = require('rollup')
let babel = require('rollup-plugin-babel')
let alias = require('rollup-plugin-alias')
let commonjs = require('rollup-plugin-commonjs')
let noderesolve = require('rollup-plugin-node-resolve')
let nib = require('nib')
let browserSync = require('browser-sync')
let server = browserSync.create()
let path = require('path')

let resolve = (...paths) => path.join(__dirname, ...paths)

let browsersyncServe = (cb) => browserSync
	.init({
		 server: {
            baseDir: "./"
        }
	})

let browsersyncReload = (cb) => browserSync
	.reload()

let rollme = () => rollup
	.rollup({
		input: paths.scripts.entry,
		plugins: [
			babel({
				presets: [ [ 'env', { modules: false } ] ],
				exclude: 'node_modules/**',
				babelrc: false
			}),
			alias({
				resolve: [ '.js' ],
				'@': resolve('src/js')
			}),
			commonjs(),
			noderesolve()
		]
	})
	.then(bundle => bundle.generate({
		format: 'iife',
		name: 'main'
	}))


let dirs = {
	src: resolve('src'),
	temp: resolve('temp'),
	dist: resolve('dist'),
	img: resolve('img'),
	css: resolve('css'),
	js: resolve('js')
}

let paths = {
	html: {
		entry: '*.html'
	},
	images: {
		all: ['./images/**/*.jpg', './images/**/*.png']
	}, 
	scripts: {
		entry: './scripts/Zzzmain.js',
		all: './scripts/**/*.js'
	},
	styles: {
		all: './styles/**/*.styl',
		entry: './styles/main.styl'
	},
	drive: {
		entry: 'b:/web/',
		css: 'b:/web/css',
		js: 'b:/web/js'
	},
	server: {
		entry: 'localhost',
		port: '80',
		mrdopoly: 'localhost/web'
	},
	ftp: {
		server: '',
		user: '',
		pass: ''
	}
}


let packJs = done => gulp    
    .src(paths.scripts.all)
    .pipe(concat('app.js'))
	//.pipe(uglify())
    .pipe(gulp.dest(dirs.js));

let buildJs = done => rollme()
	.then(gen => file('app.js', gen.code, { src: true })
		.pipe(uglify())
		.pipe(rev())
		.pipe(gulp.dest(dirs.js)))

let buildImg = done => gulp
	.src(paths.images.all)
	.pipe(imagemin([
		pngquant({quality: [0.5, 0.5]}),
		mozjpeg({quality: 50})
	]))
	.pipe(gulp.dest(dirs.img))

let buildStylus = done => gulp
	.src(paths.styles.all)
	.pipe(stylus({
    	'include css': true
    }))
	.pipe(autoprefixer())
	.pipe(concat('main.css'))
	.pipe(cssnano())
	.pipe(rev())
	.pipe(gulp.dest(dirs.css))
	/*.pipe(sftp({
		host: paths.ftp.server,
		user: paths.ftp.user,
		pass: paths.ftp.pass
	}))*/


const watch = function () {
	gulp.watch(
		paths.html.entry
	).on('change',browserSync.reload);
	gulp.watch(
		paths.images.all,
		buildImg
	).on('change',browserSync.reload);
	gulp.watch(
		paths.scripts.all,
		packJs
	).on('change',browserSync.reload);
	gulp.watch(
		paths.styles.all,
		buildStylus
	).on('change',browserSync.reload);
};

const build = gulp.parallel(
	buildImg,
	packJs,
	buildJs,
	buildStylus,
	browsersyncServe,
	gulp.series(watch)
);

exports.default = build