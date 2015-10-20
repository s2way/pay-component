module.exports = function(grunt) {
    grunt.initConfig({
        phpunit: {
            classes: {
                dir: 'test/'
            },
            options: {
                colors: true
            }
        },
        watch: {
            test: {
                files: ['src/**/*.php', 'src/*.php', 'test/**/*.php', 'test/*.php'],
                tasks: ['phpunit']
            }
        }
    });

 
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-phpunit');

    grunt.registerTask('default', 'Watch', function() {
        grunt.task.run('watch');
    });
};