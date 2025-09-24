import gulp from 'gulp';
import gulpSass from 'gulp-sass';
import dartSass from 'sass';
import autoprefixer from 'autoprefixer';
import postcss from 'gulp-postcss';
import sourcemaps from 'gulp-sourcemaps';
import cssnano from 'cssnano';
import webp from 'gulp-webp';
import terser from 'gulp-terser-js';

const { src, dest, watch, parallel } = gulp;
const sass = gulpSass(dartSass);

const paths = {
    scss: 'src/scss/**/*.scss',
    js: 'src/js/**/*.js',
    images: 'src/img/**/*'
};

function css() {
    return src(paths.scss)
        .pipe(sourcemaps.init())
        .pipe(sass())
        .pipe(postcss([autoprefixer(), cssnano()]))
        .pipe(sourcemaps.write('.'))
        .pipe(dest('public/build/css'));
}

function javascript() {
    return src(paths.js)
        .pipe(terser())
        .pipe(sourcemaps.write('.'))
        .pipe(dest('public/build/js'));
}

// CORREGIDO: Procesar imágenes a public/build/img/ (no uploads)
function processImages() {
    return src(paths.images)
        .pipe(dest('public/build/img')); // Copiar originales primero
}

// CORREGIDO: Generar WebP automáticamente desde public/build/img/
function versionWebp() {
    return src('public/build/img/**/*.{jpg,jpeg,png}')
        .pipe(webp({ quality: 85 }))
        .pipe(dest('public/build/img')); // Mismo directorio que originales
}

function watchFiles() {
    watch(paths.scss, css);
    watch(paths.js, javascript);
    watch(paths.images, parallel(processImages, versionWebp));
    // NUEVO: Watch también public/build/img para generar WebP de uploads
    watch('public/build/img/**/*.{jpg,jpeg,png}', versionWebp);
}

export { css, javascript, processImages, versionWebp, watchFiles };
export default parallel(css, javascript, processImages, versionWebp, watchFiles);
export const build = parallel(css, javascript, processImages, versionWebp);