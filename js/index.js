console.log("Alt Tag Image Generator Plugin Loaded! 4");
console.log("Checking Media Events:", wp.media.events);

jQuery(document).ready(function ($) {
  // Check if wp.media is available for modern WordPress Media Library handling
  if (typeof wp !== "undefined" && wp.media && wp.media.events) {
    // Primary method: Listen for the 'attachment:uploaded' event
    wp.media.events.on("attachment:uploaded", function (attachment) {
      if (
        attachment &&
        attachment.attributes &&
        attachment.attributes.type &&
        attachment.attributes.type.startsWith("image/")
      ) {
        console.log(
          "Image successfully uploaded (via wp.media):",
          attachment.attributes.filename
        );

        // Call your custom function
        myCustomFunction(attachment.attributes);
      }
    });
  } else if (typeof wp !== "undefined" && wp.Uploader && wp.Uploader.queue) {
    // Fallback method: Use wp.Uploader to listen for 'fileuploaded' events
    wp.Uploader.queue.on("fileuploaded", function (file, response) {
      if (
        file &&
        file.attributes &&
        file.attributes.type &&
        file.attributes.type.startsWith("image/")
      ) {
        console.log(
          "Image successfully uploaded (via wp.Uploader):",
          file.attributes.filename
        );

        // Call your custom function
        myCustomFunction(file.attributes);
      }
    });
  } else {
    console.warn("wp.media and wp.Uploader are not available.");
  }
});

function myCustomFunction(fileAttributes) {
  // Your custom logic here
  console.log("Custom function triggered for:", fileAttributes.filename);
}
