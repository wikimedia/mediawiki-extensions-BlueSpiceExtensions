/*jshint node:true */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-jsonlint' );

	grunt.initConfig( {
		jsonlint: {
			all: [
				'*.json',
				'**/*.json',
				'!node_modules/**',
				'!vendor/**'
			]
		}
	} );

	grunt.registerTask( 'test', [ 'jsonlint' ] );
	grunt.registerTask( 'default', 'test' );
};
