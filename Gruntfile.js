module.exports = function( grunt ) {
	'use strict';

	// Load all grunt tasks
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	// Project configuration
	grunt.initConfig( {
		pkg:    grunt.file.readJSON( 'package.json' ),
		concat: {
			options: {
				stripBanners: true,
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			citris: {
				src: [
					'assets/js/src/citris.js',
					'assets/js/src/navigation.js',
					'assets/js/src/skip-link-focus-fix.js'
				],
				dest: 'assets/js/citris.js'
			},
			admin: {
				src: [
					'assets/js/admin/curation.js'
				],
				dest: 'assets/js/citris.admin.js'
			}
		},
		jshint: {
			all    : [
				'Gruntfile.js',
				'assets/js/src/**/*.js',
				'assets/js/admin/**/*.js',
				'assets/js/test/**/*.js'
			],
			options: {
				boss     : true,
				curly    : true,
				eqeqeq   : true,
				eqnull   : true,
				immed    : true,
				jquery   : true,
				latedef  : true,
				newcap   : true,
				noarg    : true,
				node     : true,
				quotmark : 'single',
				sub      : true,
				undef    : true,
				browser  : true,
				validthis: true,
				globals  : {
					exports: true,
					module : false
				}
			}
		},
		uglify: {
			all: {
				files: {
					'assets/js/citris.min.js': ['assets/js/citris.js'],
					'assets/js/citris.admin.min.js': ['assets/js/citris.admin.js']
				},
				options: {
					banner: '/*! <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
						' * <%= pkg.homepage %>\n' +
						' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
						' * Licensed GPLv2+' +
						' */\n',
					mangle: {
						except: ['jQuery']
					}
				}
			}
		},
		test: {
			files: ['assets/js/test/**/*.js']
		},

		sass: {
			all: {
				files: [{
					'assets/css/citris.css': 'assets/css/src/citris.scss'
				}]
			}
		},

		cssmin: {
			options: {
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			minify: {
				expand: true,

				cwd: 'assets/css/',
				src: ['citris.css'],

				dest: 'assets/css/',
				ext: '.min.css'
			}
		},
		watch:  {

			sass: {
				files: ['assets/css/src/*.scss'],
				tasks: ['sass','cssmin'],
				options: {
					debounceDelay: 500
				}
			},

			scripts: {
				files: ['assets/js/src/**/*.js', 'assets/js/vendor/**/*.js'],
				tasks: ['jshint', 'concat', 'uglify'],
				options: {
					debounceDelay: 500
				}
			}
		}
	} );

	// Default task.

	grunt.registerTask( 'default', ['jshint', 'concat', 'sass', 'uglify', 'cssmin'] );


	grunt.util.linefeed = '\n';
};