// Import TensorFlow.js and MobileNet
import * as tf from "@tensorflow/tfjs";
import * as mobilenet from "@tensorflow-models/mobilenet";

console.log("Image recognition script loaded");

tf.ready().then(() => {
  console.log("TensorFlow.js is ready.");
  mobilenet.load().then(() => {
    console.log("MobileNet model is loaded.");
  });
});

async function runImageRecognition(imageUrl, postId) {
  console.log("from image reco function", imageUrl, postId);
  try {
    console.log("Loading model...");
    const model = await mobilenet.load();
    console.log("Model loaded successfully.");

    const image = new Image();
    image.crossOrigin = "anonymous"; // Ensure the image is loaded from a different origin
    image.src = imageUrl;

    image.onload = async () => {
      console.log("Image loaded successfully.");

      try {
        console.log("Converting image to tensor...");
        const tensor = tf.browser
          .fromPixels(image)
          .resizeNearestNeighbor([224, 224])
          .toFloat()
          .div(255.0)
          .expandDims();

        console.log("Tensor created:", tensor);

        console.log("Running predictions...");
        const predictions = await model.classify(tensor);
        console.log("Predictions received:", predictions);

        console.log("Generating alt text...");
        const altText = predictions[0].className; // Top prediction
        console.log("Generated alt text:", altText);

        saveAltText(postId, altText);
      } catch (tensorError) {
        console.error("Error processing the image:", tensorError);
      }
    };

    image.onerror = (error) => {
      console.error("Error loading the image:", error);
    };
  } catch (modelError) {
    console.error("Error loading the model:", modelError);
  }
}

function saveAltText(postId, altText) {
  console.log("Preparing to send AJAX request with alt text:", altText);

  jQuery.ajax({
    url: irat_ajax.ajax_url,
    type: "POST",
    data: {
      action: "irat_save_alt_text",
      post_id: postId,
      alt_text: altText,
    },
    success: (response) => {
      if (response.success) {
        console.log("Alt text saved successfully:", response);
      } else {
        console.error("Server error:", response.data.message);
      }
    },
    error: (xhr, status, error) => {
      console.error("AJAX request failed:", {
        status,
        error,
        responseText: xhr.responseText,
      });
    },
  });
}

export { runImageRecognition };
