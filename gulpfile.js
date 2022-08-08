var gulp = require('gulp'),
    sass = require('gulp-sass')

gulp.task('sass', () => {
    return gulp.src(['sass/**/*.sass', 'sass/**/*.scss'])
        .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
        .pipe(gulp.dest('css'))
})

gulp.task('watch', () => {
    gulp.watch(
        ['sass/**/*.sass', 'sass/**/*.scss'],
        gulp.parallel('sass')
    )
})

gulp.task('default', gulp.series('sass', 'watch'))