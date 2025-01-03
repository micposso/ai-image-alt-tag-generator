async function runImageRecognition(imageUrl, postId) {
  const model = await tf.loadGraphModel("path/to/model.json");

  // Load image
  const image = new Image();
  image.src = imageUrl;
  image.onload = async () => {
    const tensor = tf.browser
      .fromPixels(image)
      .resizeNearestNeighbor([224, 224]) // Adjust size for the model
      .toFloat()
      .expandDims();

    const predictions = await model.predict(tensor).data();
    const altText = generateAltText(predictions); // Create a function to map predictions to text

    // Send alt text back to server
    saveAltText(postId, altText);
  };
}

function saveAltText(postId, altText) {
  jQuery.ajax({
    url: ajaxurl, // WordPress AJAX endpoint
    type: "POST",
    data: {
      action: "irat_save_alt_text",
      post_id: postId,
      alt_text: altText,
    },
  });
}
