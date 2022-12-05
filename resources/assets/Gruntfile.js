'use strict';
module.exports = function(grunt) {

  grunt.initConfig({
    jshint: {
      options: {
        jshintrc: '.jshintrc'
      },
      all: [
        'Gruntfile.js',
        'js/**/*.js',
        '!js/script.min.js'
      ]
    },
    sass: {
      dist: {
        options: {
          style: 'compressed',
          compass: false,
          sourcemap: false
        },
        files: {
          'css/style.min.css': [
              'scss/style.scss'
          ]
        }
      }
    },
    uglify: {
      dist: {
        files: {
          'js/script.min.js': [
            'js/script.js'
          ]
        },
        options: {
          sourceMap: 'js/script.min.js',
          sourceMappingURL: '/js/script.min.js.map'
        }
      }
    },
    watch: {
      options: {
        livereload: true
      },
      sass: {
        files: [
          'scss/**/*.scss'
        ],
        tasks: ['sass']
      },
      js: {
        files: [
          'js/**/*.js'
        ],
        tasks: ['jshint', 'uglify']
      }
    },
    clean: {
      dist: [
        'css/style.min.css',
        'js/script.min.js'
      ]
    }
  });

  // Load tasks
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-sass');

  // Register tasks
  grunt.registerTask('default', [
    'clean',
    'sass',
    'uglify'
  ]);
  grunt.registerTask('dev', [
    'watch'
  ]);

};