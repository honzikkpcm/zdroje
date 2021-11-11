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
let plugins = require('gulp-load-plugins')
let autoprefixer = require('gulp-autoprefixer')
let cssnano = require('gulp-cssnano')
let htmlmin = require('gulp-htmlmin')
let inject = require('gulp-inject')
let file = require('gulp-file')
let stylus = require('gulp-stylus')
let alias = require('rollup-plugin-alias')
let newer = require('gulp-newer')
let commonjs = require('rollup-plugin-commonjs')
let noderesolve = require('rollup-plugin-node-resolve')
let browserSync = require('browser-sync')
let FtpDeploy = require("ftp-deploy")
let ftpHelper = require('./lib/ftpHelper')
let minify = require('gulp-minify');
let path = require('path')
let server = browserSync.create()



let resolve = (...paths) => path.join(__dirname, ...paths)

let dirs = {
	img:  resolve('img'),
	csses:  resolve('css'),
	cssdb:  resolve('php/css'),
	jses: resolve('js'),
	jsdb: resolve('php/js')
}

let paths = {
	phpecka: {
		all: ['php/*'],
	}, 
	images: {
		all: ['./images/**/*.jpg', './images/**/*.png'],
	}, 
	scripts: {
		entryes: 'scripts/main.js',
		entrydb: 'php/scripts/main.js',
		exites:  'scripts/*.js',
		exitdb:  'php/scripts/*.js'
	},
	styles: {
		entryes: 'styles/main.styl',
		entrydb: 'php/styles/main.styl',
		exites:  'styles/**/*.styl',
		exitdb:  'php/styles/*.styl'
	},
	ftpdb: {
		server: '9999999.w40.wedos.net',
		user:   '99999_user',
		pass:   'Pass-',
	},
	system: {
		npm: ["node_modules/**", "node_modules/**/.*"],
		git: ".git/**",
	},
	databaseJs: ['php/scripts/jquery.js', 'php/scripts/modalx.js', 'php/scripts/seeThru.js'],
	}


let buildJsEshop = done => gulp
	.src(paths.scripts.exites)
	.pipe(concat('app.js'))
    .pipe(newer('js/app.js'))
	.pipe(minify())
    .pipe(gulp.dest(dirs.jses))

let buildJsData = done => gulp
	.src(paths.scripts.exitdb)
//    .pipe(newer('php/js/app.js'))
	.pipe(concat('app.js'))
	.pipe(minify())
    .pipe(gulp.dest(dirs.jsdb))

let buildImg = done => gulp
	.src(paths.images.all)
	.pipe(imagemin([
		pngquant({quality: [0.5, 0.5]}),
		mozjpeg({quality: 50})
	]))
	.pipe(gulp.dest(dirs.img))

let buildCssEshop = done => gulp
	.src(paths.styles.entryes)
    .pipe(newer('css/main.css'))
	.pipe(stylus())
	.pipe(concat('main.css'))
	.pipe(autoprefixer())
	.pipe(cssnano())
	.pipe(gulp.dest(dirs.csses))

let buildCssData = done => gulp
	.src(paths.styles.entrydb)
    .pipe(newer('php/css/main.css'))
	.pipe(stylus())
	.pipe(concat('main.css'))
	.pipe(autoprefixer())
	.pipe(cssnano())
	.pipe(gulp.dest(dirs.cssdb))

function reload(done) {
  browserSrv.reload();
  done();
}

function serve(done) {
  browserSrv.init({
    open: 'external',
    host: '999999.w40.wedos.ws',
    proxy: '999999.w40.wedos.ws',
    port: 80
  });
  done();
}

function uploadToFTP(done) {
	ftpHelper.setup(plugins);
	ftpHelper.upload({
		user: paths.ftpdb.user, 
		password: paths.ftpdb.pass, 
		host: paths.ftpdb.server, 
		port: 21, 
		remoteRoot: '/', 
//    	include: ["*", "**/*"], 
		include: ["css/*", "js/*", "img/*", "php/*", "php/**/*"],
    	exclude: ["php/styles/*", "php/scripts/*", "dist/**/*.map", "node_modules/**", "node_modules/**/.*", ".git/**"],
	    localRoot: __dirname,
    	deleteRemote: false,
    	forcePasv: true,
    	sftp: false
	}, done);
	done();
}


function uploadToShoptet(done) {
	/*
	ftpHelper.setup(plugins);
	ftpHelper.upload({
		user: paths.ftpdb.user, 
		password: paths.ftpdb.pass, 
		host: paths.ftpdb.server, 
		port: 21, 
		remoteRoot: '/', 
		include: ["css/*", "js/*", "img/*", "php/*.php"],
	    localRoot: __dirname,
    	deleteRemote: false,
    	forcePasv: true,
    	sftp: false
	}, done);
	*/
	done();
}


const browserSrv = browserSync.create();
const watchCSSEshop = () => gulp.watch(paths.styles.entryes, gulp.series(buildCssEshop, uploadToShoptet, reload));
const watchCSSData = () => gulp.watch(paths.styles.entrydb, gulp.series(buildCssData, uploadToFTP, reload));
const watchJSEshop  = () => gulp.watch(paths.scripts.entryes, gulp.series(buildJsEshop, uploadToShoptet, reload));
const watchJSData  = () => gulp.watch(paths.scripts.entrydb, gulp.series(buildJsData, uploadToFTP, reload));
const watchPHP = () => gulp.watch(paths.phpecka.all, gulp.series(uploadToFTP, reload));
const watchIMG = () => gulp.watch(paths.images.all, reload);

const build = gulp.series(
    uploadToFTP,
    serve,
    gulp.parallel(buildImg, buildJsEshop, buildJsData, buildCssEshop, buildCssData),
    gulp.parallel(watchCSSEshop, watchCSSData, watchJSEshop, watchJSData, watchPHP, watchIMG)
);
exports.default = build
