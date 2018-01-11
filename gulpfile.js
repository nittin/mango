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
        'app/constant/**/*.js',
        'app/app.js',
        'app/components/**/*.js',
        'app/ctrl/home.js',
        'app/ctrl/map.js',
        'app/ctrl/**/*.js'
    ],
    html: 'app/view/**/*.html',
    oauth: 'app/oauth/**/*',
    json: 'app/i18n/**/*.json',
    asset: 'app/asset/**/*',
    index: 'app/index.html',
    mobile: 'mobile/www/components/*',
    bower: ['bower.json', '.bowerrc']
};

var getBuild = function (environment) {
    var env = environment !== '' ? environment : 'default';
    return {
        root: 'build/*',
        branch: 'build/' + env,
        mobile: 'mobile/www',
        all: 'app.js',
        html: 'build/' + env + '/view/',
        vendor: 'build/' + env + '/lib/',
        asset: 'build/' + env + '/asset/',
        json: 'build/' + env + '/i18n/',
        oauth: 'build/' + env + '/oauth/',
        mobile_html: 'mobile/www/view/',
        mobile_asset: 'mobile/www/asset/',
        mobile_json: 'mobile/www/i18n/',
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
    var build = getBuild();
    return gulp.src([build.root]).pipe(clean({force: true}));
};

// concat *.js to `vendor.js` and *.css to `vendor.css`
var builder = function () {
    var prefix = this.environment ? '_' + this.environment : '';
    var build = getBuild(this.environment);

    var now = new Date();
    var version = prefix + now.toISOString().replace(/T/, ' ').replace(/\..+/, '')
            .replace(/-/g, '').replace(/:/g, '').replace(/ /g, '_');
    var suffix = '?v=app' + version;
    /*var vendorStream = {
        js: gulp.src(mainBowerFiles('**!/!*.js'))
            .pipe(concat(build.js.vendors))

            .pipe(angularFilesort())
            // .pipe(uglify({mangle: false}))
            .pipe(gulp.dest(build.branch))
            .pipe(gulp.dest(build.mobile)),
        css: gulp.src(mainBowerFiles('**!/!*.css'))
            .pipe(concat(build.css.vendors))
            .pipe(cssmin())
            .pipe(gulp.dest(build.branch))
            .pipe(gulp.dest(build.mobile))
    };*/
    var appJs = ['app/env/env.' + this.environment + '.js', 'app/env/env.js'].concat(src.js);
    var appStream = {
        //find init file
        js: gulp.src(appJs)
            // .pipe(angularFilesort())

            //append all *.js which ignore init file
            .pipe(concat(build.js.app))
            // .pipe(uglify({mangle: false}))
            .pipe(insert.prepend('/*' + version + '*/\n'))
            .pipe(gulp.dest(build.branch))
            .pipe(gulp.dest(build.mobile)),
        css: gulp.src(src.less)
            .pipe(less())
            .pipe(cssmin())
            .pipe(replace(/url\(\.\.\/img/g, 'url(img'))
            .pipe(rename({basename: 'app', suffix: '.min'}))
            .pipe(insert.prepend('/*' + version + '*/\n'))
            .pipe(gulp.dest(build.branch))
            .pipe(gulp.dest(build.mobile))
    };
    
    var mobileStream = gulp.src(src.mobile);
    gulp.src([src.html]).pipe(gulp.dest(build.mobile_html));
    gulp.src([src.json]).pipe(gulp.dest(build.mobile_json));
    gulp.src([src.asset]).pipe(gulp.dest(build.mobile_asset));
    gulp.src(src.index)
        // .pipe(inject(series(vendorStream.js, vendorStream.css),
        //     {name: 'inject-bower', ignorePath: build.mobile, addRootSlash: false, addSuffix: suffix}))
        .pipe(inject(series(appStream.js, appStream.css),
            {name: 'inject-app', ignorePath: build.mobile, addRootSlash: false, addSuffix: suffix}))
        .pipe(inject(mobileStream,
            { name: 'inject-mobile', ignorePath: build.mobile, addRootSlash: false, addSuffix: suffix}))
        .pipe(gulp.dest(build.mobile));

    gulp.src(mainBowerFiles()).pipe(gulp.dest(build.vendor));
    gulp.src([src.html]).pipe(gulp.dest(build.html));
    gulp.src([src.json]).pipe(gulp.dest(build.json));
    gulp.src([src.asset]).pipe(gulp.dest(build.asset));
    gulp.src([src.oauth]).pipe(gulp.dest(build.oauth));
    return gulp.src(src.index)
        // .pipe(inject(series(vendorStream.js, vendorStream.css),
        //     {name: 'inject-bower', ignorePath: build.mobile, addRootSlash: false, addSuffix: suffix}))
        .pipe(inject(series(appStream.js, appStream.css),
            {name: 'inject-app', ignorePath: build.mobile, addRootSlash: false, addSuffix: suffix}))
        .pipe(gulp.dest(build.branch));
};

// build tasks
gulp.task('build_ci', builder.bind({environment: 'ci'}));
gulp.task('build_product', builder.bind({environment: 'product'}));

// clean tasks
gulp.task('clean_all', cleaner);

//make dev-lib the same as bower-config
gulp.task('copy-lib', function () {
    return gulp.src(mainBowerFiles()).pipe(gulp.dest(src.lib));
});