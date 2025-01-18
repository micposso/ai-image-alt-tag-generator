console.log("Alt Tag Image Generator Plugin Loaded!");

function logMessage() {
  console.log("from js function");
}


function runImageRecognition(imageUrl, postId) {
  console.log(
    "Running image recognition for:",
    imageUrl,
    "with post ID:",
    postId
  );

  // Simulate fetching image information
  const imageInfo = {
    url: imageUrl,
    id: postId,
    alt: "Sample alt text",
    title: "Sample title",
  };

  console.log("Image information:", imageInfo);
}



// Export the function to ensure it's available globally
window.runImageRecognition = runImageRecognition;
window.logMessage = logMessage;
