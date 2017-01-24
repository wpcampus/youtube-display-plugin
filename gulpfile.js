// Grab our dependencies.
var gulp = require('gulp');
var phpcs = require('gulp-phpcs');

// Mark our source files.
var src = {
	php: ['**/*.php','!vendor/**','!node_modules/**','!tests/**']
};

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
    gulp.watch(src.php,['php']);
});

// Our default tasks.
gulp.task('default',['test']);

// Test all the things.
gulp.task('test',['php']);