console.log("Alt Tag Image Generator Plugin Loaded!");


jQuery(document).ready(function ($) {
  // Listen for the Media Library uploader event
  if (typeof wp !== "undefined" && wp.Uploader && wp.Uploader.queue) {
    wp.Uploader.queue.on("add", function (file) {
      // Ensure the uploaded file is an image
      if (
        file &&
        file.attributes &&
        file.attributes.type.startsWith("image/")
      ) {
        console.log("Image uploaded:", file.attributes.filename);

        // Call your custom function
        myCustomFunction(file.attributes);
      }
    });
  }
});

function myCustomFunction(fileAttributes) {
  // Your custom logic here
  console.log("Custom function triggered for:", fileAttributes.filename);
}


