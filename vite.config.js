import { defineConfig } from 'vite';
import { resolve } from 'path';

// Generate both minified and non-minified versions
export default defineConfig( ( { mode } ) => {
	const isProduction = mode === 'production';

	return {
		build: {
			// Output to dist directory
			outDir: 'dist/js',
			emptyOutDir: false, // Don't empty to allow both versions

			// Library mode for WordPress plugin assets
			lib: {
				entry: {
					'admin-field-editor': resolve(
						__dirname,
						'assets/js/admin-field-editor.js'
					),
				},
				formats: [ 'iife' ],
				name: 'GFRegexValidation',
			},

			rollupOptions: {
				// Externalize jQuery (provided by WordPress)
				external: [ 'jquery' ],
				output: {
					// Map jQuery to the global $ variable
					globals: {
						jquery: 'jQuery',
					},
					// Custom file naming - add .min suffix for production builds
					entryFileNames: isProduction
						? '[name].min.js'
						: '[name].js',
				},
			},

			// Generate sourcemaps for debugging
			sourcemap: true,

			// Minification only for production
			minify: isProduction ? 'terser' : false,
			terserOptions: isProduction
				? {
						format: {
							comments: false,
						},
				  }
				: undefined,
		},
	};
} );
