module.exports = function(grunt) {

    grunt.initConfig({

        jshint: {
            files: [
                'Gruntfile.js',
                'src/assets/js/*.js'
            ],
            options: {
                loopfunc: true,
                globals: {
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },
        uglify: {
            options: {
                mangle: true,
                compress: true,
                beautify: false,
            },
            files: {
                expand: true,
                cwd: 'src/assets/js',
                src: [
                    '**/*.js',
                    '!**/*.min.js'
                ],
                dest: 'public/js',
                ext: '.min.js',
                flatten: true,
                filter: 'isFile',
                rename: function(base, src) {
                    return base+'/'+src.replace(/\/([^\/]*)$/, '/../$1');
                }
            }
        },
        less: {
            options: {
                cleancss: true,
                compress: true,
            },
            files: {
                expand: true,
                cwd: 'src/assets/less',
                src: ['*.less'],
                dest: 'public/css',
                ext: '.css',
                flatten: true,
                filter: 'isFile',
                rename: function(base, src) {
                    return base+'/'+src.replace(/\/([^\/]*)$/, '/../$1');
                }
            }
        },
        watch: {
            less: {
                files: ['src/assets/less/*.less'],
                tasks: ['less'],
            },
            js: {
                files: [
                    'src/assets/js/*.js',
                ],
                tasks: ['uglify'],
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    
    grunt.registerTask('default', function() {
        grunt.task.run([
            'jshint',
            'uglify',
            'less'
        ]);
    });
};