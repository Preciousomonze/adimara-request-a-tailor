//Gruntttttt :)
module.exports = function(grunt) {

	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				compress: {
					global_defs: {
						"EO_SCRIPT_DEBUG": false
					},
					dead_code: true
				},
				banner: '/*! <%= pkg.name %> <%= pkg.version %> */\n'
			},
			build: {
				files: [{
					expand: true, // Enable dynamic expansion.
					src: ['assets/js/frontend.js'], // Actual pattern(s) to match.
					ext: '.min.js', // Dest filepaths will have this extension.
				}, ]
			}
		},
		jshint: {
			options: {
				reporter: require('jshint-stylish'),
				globals: {
					"EO_SCRIPT_DEBUG": false,
				},
				'-W020': true, //Read only - error when assigning EO_SCRIPT_DEBUG a value.
			},
			all: ['js/*.js', '!js/*.min.js']
		},

        // Generate git readme from readme.txt
		wp_readme_to_markdown: {
			convert: {
				files: {
					'readme.md': 'readme.txt'
				},
			},
		},

        // # Build and release 

		// Remove any files in zip destination and build folder
		clean: {
			main: ['build/**'],
		},

		// Copy the plugin into the build directory
		copy: {
			main: {
				src: [
					'**',
					'!node_modules/**',
					'!build/**',
					'!deploy/**',
					'!svn/**',
					'!**/*.zip',
					'!wp-assets/**',
					'!package-lock.json',
					'!screenshots/**',
					'!.git/**',
					'!**.md',
					'!**/*.bak',
					'!Gruntfile.js',
					'!package.json',
          			'!gitcreds.json',
          			'!.gitcreds',
					'!.gitignore',
					'!.gitmodules',
					'!sftp-config.json',
					'!**.sublime-workspace',
					'!**.sublime-project',
					'!deploy.sh',
					'!**/*~'
				],
				dest: 'build/'
			}
		},

		// Make a zipfile.
		compress: {
		  main: {
		    options: {
		      mode: 'zip',
		      archive: 'deploy/<%= pkg.version %>/<%= pkg.name %>.zip'
		    },
		    expand: true,
		    cwd: 'build/',
		    src: ['**/*'],
		    dest: '/<%= pkg.name %>'
		  }
		},

	});

	// makepot and addtextdomain tasks
	//grunt.loadNpmTasks('grunt-wp-i18n');

	// Default task(s).
	grunt.registerTask('default', ['jshint', 'uglify']);

	grunt.registerTask('docs', ['wp_readme_to_markdown']);

	grunt.registerTask('test', ['jshint']);

	grunt.registerTask('zip', ['clean', 'copy', 'compress']);

	grunt.registerTask('build', ['uglify']);

	grunt.registerTask('release', ['build', 'zip', 'clean']);

};