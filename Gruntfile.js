//Gruntttttt :)
module.exports = function(grunt) {

	const sass = require('node-sass');
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
					src: ['assets/js/name-your-price.js', 'assets/js/admin/nyp-metabox.js', 'assets/js/admin/nyp-quick-edit.js'], // Actual pattern(s) to match.
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
			all: ['js/*.js', '!js/*.min.js', 'admin/js/*.js', '!admin/js/*.min.js']
		},

		// Sass
		sass: {
			options: {
				implementation: sass,
				sourceMap: false,
				outputStyle: 'compact'
			},
			dist: {
				files: { // Dictionary of files
					'assets/css/name-your-price.css': 'assets/css/name-your-price.scss'       // 'destination': 'source'
				}
			}
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
					'!nyp-logo.png',
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

		'github-release': {
		  options: {
		    repository: 'woocommerce/<%= pkg.name %>',
		    release: {
		      tag_name: '<%= pkg.version %>',
		      name: '<%= pkg.version %>',
		      body: 'Description of the release'
		    }
		  },
		  files: {
		    src: ['deploy/<%= pkg.version %>/<%= pkg.name %>.zip']
		  }
		},

		// # Internationalization 

		// Add text domain
		addtextdomain: {
			options: {
	            textdomain: '<%= pkg.domain %>',    // Project text domain.
	            updateDomains: [ '<%= pkg.domain %>', '<%= pkg.name %>', 'woocommerce' ]  // List of text domains to replace.
	        },
			target: {
				files: {
					src: ['*.php', '**/*.php', '!node_modules/**', '!build/**']
				}
			}
		},

		// Generate .pot file
		makepot: {
			target: {
				options: {
					domainPath: '/languages', // Where to save the POT file.
					exclude: ['build/.*', 'svn/.*'], // List of files or directories to ignore.
					mainFile: '<%= pkg.name %>.php', // Main project file.
					potFilename: '<%= pkg.domain %>.pot', // Name of the POT file.
					type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
					potHeaders: {
	                    'Report-Msgid-Bugs-To': 'https://woocommerce.com/my-account/tickets/'
	                }
				}
			}
		},

		// bump version numbers
		replace: {
			Version: {
				src: [
					'readme.txt',
					'readme.md',
					'<%= pkg.name %>.php'
				],
				overwrite: true,
				replacements: [
					{ 
						from: /\*\*Stable tag:\*\* '.*.'/m,
						to: "*Stable tag:* '<%= pkg.version %>'"
					},
					{
						from: /Stable tag:.*$/m,
						to: "Stable tag: <%= pkg.version %>"
					},
					{ 
						from: /Version:.*$/m,
						to: "Version: <%= pkg.version %>"
					},
					{ 
						from: /public \$version = \'.*.'/m,
						to: "public $version = '<%= pkg.version %>'"
					}
				]
			}
		}

	});

	// makepot and addtextdomain tasks
	//grunt.loadNpmTasks('grunt-wp-i18n');

	// Default task(s).
	grunt.registerTask('default', ['jshint', 'uglify']);

	grunt.registerTask('docs', ['wp_readme_to_markdown']);

	grunt.registerTask('test', ['jshint', 'addtextdomain']);

	grunt.registerTask('zip', ['clean', 'copy', 'compress']);

	grunt.registerTask('build', ['test', 'replace', 'uglify']);

	grunt.registerTask('release', ['build', 'zip', 'clean']);

};