var gulp = require('gulp');
var uglify = require('gulp-uglify');
var cssmin = require('gulp-minify-css');
var rename = require('gulp-rename');
var less = require('gulp-less');
var concat = require('gulp-concat');
var inject = require('gulp-inject');
var angularFilesort = require('gulp-angular-filesort');
var series = require('stream-series');
var clean = require('gulp-clean');
var insert = require('gulp-insert');
var replace = require('gulp-replace');
var mainBowerFiles = require('main-bower-files');

var src = {
    root: 'app/',
    lib: 'app/lib',
    css: 'app/**/*.css',
    less: 'app/app.less',
    js: [
        'app/app.js',
        'app/components/**/*.js',
        'app/ctrl/**/*.js'
    ],
    html: 'app/view/**/*.html',
    json: 'app/i18n/**/*.json',
    asset: 'app/asset/**/*',
    index: 'app/index.html',
    bower: ['bower.json', '.bowerrc']
};

var getBuildConfig = function (environment) {
    var env = environment !== '' ? environment : 'default';
    return {
        envFiles: 'build/*',
        root: 'build/' + env,
        root_child: 'build/' + env + '/*',
        all: 'app.js',
        html: 'build/' + env + '/view/',
        asset: 'build/' + env + '/asset/',
        json: 'build/' + env + '/i18n/',
        js: {
            app: 'app.min.js',
            vendors: 'vendors.min.js'
        },
        css: {
            app: 'app.min.css',
            vendors: 'vendors.min.css'
        }
    };
};

// remove all file and sub folder which are inner 'build' folder
var cleaner = function () {
    var build = getBuildConfig();
    return gulp.src([build.envFiles]).pipe(clean({force: true}));
};

var cleanerEnv = function () {
    var build = getBuildConfig(this.environment);
    return gulp.src([build.root_child]).pipe(clean({force: true}));
};

// concat *.js to `vendor.js` and *.css to `vendor.css`
var builder = function () {
    var env = this.environment ? '_' + this.environment : '';
    var build = getBuildConfig(this.environment);

    var now = new Date();
    var version = env + now.toISOString().replace(/T/, ' ').replace(/\..+/, '')
            .replace(/-/g, '').replace(/:/g, '').replace(/ /g, '_');
    var suffix = '?v=app' + version;
    var vendorStream = {
        js: gulp.src(mainBowerFiles('**/*.js'))
            .pipe(concat(build.js.vendors))

            .pipe(angularFilesort())
            .pipe(uglify({mangle: false}))
            .pipe(gulp.dest(build.root)),
        css: gulp.src(mainBowerFiles('**/*.css'))
            .pipe(concat(build.css.vendors))
            .pipe(cssmin())
            .pipe(gulp.dest(build.root))
    };

    var appStream = {
        //find init file
        js: gulp.src(src.js)
            .pipe(angularFilesort())

            //append all *.js which ignore init file
            .pipe(concat(build.js.app))
            .pipe(uglify({mangle: false}))
            .pipe(insert.prepend('/*' + version + '*/\n'))
            .pipe(gulp.dest(build.root)),
        css: gulp.src(src.less)
            .pipe(less())
            .pipe(cssmin())
            .pipe(replace(/url\(\.\.\/img/g, 'url(img'))
            .pipe(rename({basename: 'app', suffix: '.min'}))
            .pipe(insert.prepend('/*' + version + '*/\n'))
            .pipe(gulp.dest(build.root))
    };
    gulp.src([src.html]).pipe(gulp.dest(build.html));
    gulp.src([src.json]).pipe(gulp.dest(build.json));
    gulp.src([src.asset]).pipe(gulp.dest(build.asset));
    return gulp.src(src.index)
        .pipe(inject(series(vendorStream.js, vendorStream.css),
            {name: 'inject-bower', ignorePath: build.root, addRootSlash: false, addSuffix: suffix}))
        .pipe(inject(series(appStream.js, appStream.css),
            {name: 'inject-app', ignorePath: build.root, addRootSlash: false, addSuffix: suffix}))
        .pipe(gulp.dest(build.root));
};

// build tasks
gulp.task('_build_ci', builder.bind({environment: 'ci'}));
gulp.task('_build_default', builder.bind({environment: ''}));

// clean tasks
gulp.task('clean_all', cleaner);

//make dev-lib the same as bower-config
gulp.task('copy-lib', function () {
    return gulp.src(mainBowerFiles()).pipe(gulp.dest(src.lib));
});