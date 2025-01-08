const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const uglify = require('gulp-uglify');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const browserSync = require('browser-sync').create();
const cleanCSS = require('gulp-clean-css');  // Para limpiar y minificar CSS
const rename = require('gulp-rename');       // Para cambiar el nombre de los archivos minificados

// Rutas
const paths = {
  scss: './source/scss/**/*.scss',  // Carpeta fuente de SCSS
  js: './source/js/**/*.js',        // Carpeta fuente de JS
  cssDest: './assets/css/',         // Carpeta de salida para CSS
  jsDest: './assets/js/'            // Carpeta de salida para JS
};

// Compilar y minificar Sass
gulp.task('sass', () => {
  return gulp
    .src(paths.scss)
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))  // Compilación y minificación de SCSS
    .pipe(postcss([autoprefixer()])) // Usando autoprefixer para manejar prefijos
    .pipe(cleanCSS({ level: 2 }))    // Limpieza y minificación de CSS, eliminando comentarios y espacios innecesarios
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(paths.cssDest))
    .pipe(browserSync.stream());
});

// Minificar JS sin concatenar
gulp.task('scripts', () => {
  return gulp
    .src(paths.js)
    .pipe(sourcemaps.init())
    .pipe(uglify({
      output: {
        comments: false, // Eliminar comentarios
      },
      mangle: {
        toplevel: true, // Ofuscar nombres de variables y funciones a nivel superior
      },
    }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(paths.jsDest))
    .pipe(browserSync.stream());
});

// Servidor con BrowserSync
gulp.task('serve', () => {
  browserSync.init({
    proxy: "https://adonitrans.mkt/" // Cambia esto por la URL de tu WordPress
  });

  gulp.watch(paths.scss, gulp.series('sass'));
  gulp.watch(paths.js, gulp.series('scripts'));
  gulp.watch('./**/*.php').on('change', browserSync.reload);
});

// Tarea por defecto
gulp.task('default', gulp.series('sass', 'scripts', 'serve'));
