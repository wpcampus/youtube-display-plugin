// Grab our dependencies.
var autoprefixer = require('gulp-autoprefixer');
var gulp = require('gulp');
var phpcs = require('gulp-phpcs');
var sass = require('gulp-sass');

// Mark our source files.
var src = {
	php: ['**/*.php','!vendor/**','!node_modules/**','!tests/**'],
	scss: ['assets/scss/**/*']
};

// Define the destination paths for each file type
var dest = {
	scss: 'assets/css'
}

// Sass is pretty awesome, right?
gulp.task('sass', function() {
    return gulp.src(src.scss)
        .pipe(sass({
			outputStyle: 'compressed'
		})
		.on('error', sass.logError))
        .pipe(autoprefixer({
        	browsers: ['last 2 versions'],
			cascade: false
		}))
		.pipe(gulp.dest(dest.scss));
});

// Check our PHP.
gulp.task('php',function() {
	gulp.src(src.php)
		.pipe(phpcs({
			bin: 'vendor/bin/phpcs',
			standard: 'WordPress-Core'
		}))
		.pipe(phpcs.reporter('log'));
});

// Watch the files.
gulp.task('watch',function() {
	gulp.watch(src.scss, ['sass']);
    gulp.watch(src.php,['php']);
});

// Our default tasks.
gulp.task('default',['compile','test']);

// Compile all the things.
gulp.task('compile',['sass']);

// Test all the things.
gulp.task('test',['php']);