/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
// import 'filepond/dist/filepond.min.css';

// start the Stimulus application
// import * as FilePond from 'filepond';


// We want to preview images, so we register
// the Image Preview plugin, We also register
// exif orientation (to correct mobile image
// orientation) and size validation, to prevent
// large files from being added

// FilePond.registerPlugin(
//     FilePondPluginFileValidateSize,
//     FilePondPluginImageEdit
// );
//
// // Select the file input and use
// // create() to turn it into a pond
// FilePond.create(
//     document.querySelector('#xlsx')
// );