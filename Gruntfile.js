/* global module, require */
module.exports = function (grunt) {
	'use strict';

    // Load grunt tasks automatically
    require('load-grunt-tasks')(grunt);

	var fs = require('fs')
		/*,glob = require('glob')*/
		// config
		,sFolderWPRepo = 'wprepo/trunk/'
	;

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json')


		// Increment versions
		,version_git: {
			main: {
				files: {src: [
					'package.json'
					,'PocketWidgetPlugin.php'
				]}
			}
			,readme: {
				options: {
					regex: /(Stable tag: )(\d+\.\d+\.\d+)/
					,prefix: 'Stable tag: '
				}
				,files: {src: [
					'readme.txt'
				]}
			}
		}

		// Copy all the things!
		,copy: {
			wprepo: {
				files: [
					{
						expand: true
						,cwd: ''
						,src: ['*.php','LICENSE','readme.txt']
						,dest: sFolderWPRepo
						,filter: 'isFile'
					},
					{
						expand: true
						,cwd: ''
						,src: ['inc/**']
						,dest: sFolderWPRepo
						,filter: 'isFile'
					},
					{
						expand: true
						,cwd: ''
						,src: ['js/**']
						,dest: sFolderWPRepo
						,filter: 'isFile'
					},
					{
						expand: true
						,cwd: ''
						,src: ['lang/**']
						,dest: sFolderWPRepo
						,filter: 'isFile'
					}
				]
			}
		}
	});

	grunt.registerTask('default',[
		'version_git'
	]);

};