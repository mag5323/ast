/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
    {name: 'basicstyles', groups: ['basicstyles']},
    {name: 'links', groups: ['links']},
    {name: 'paragraph', groups: ['list', 'blocks']},
    {name: 'document', groups: ['mode']},
    {name: 'insert', groups: ['insert']},
    {name: 'styles', groups: ['styles']},
  ];

  config.language = 'ja';
  config.allowedContent = true;

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';
};
