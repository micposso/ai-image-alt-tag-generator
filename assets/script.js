const DEBUG = false; // Set to true to enable debug logging

function log(...args) {
  if (DEBUG) log(...args);
}

function error(...args) {
  if (DEBUG) error(...args);
}

document.addEventListener("DOMContentLoaded", function () {
  let model = null;

  // Load MobileNet model
  async function loadModel() {
    log("Starting model load...");
    try {
      model = await mobilenet.load();
      if (DEBUG) {
        log("Model loaded successfully:", model);
      }
    } catch (error) {
      if (DEBUG) {
        error("Detailed error loading model:", error);
        error("Error stack:", error.stack);
      }
    }
  }

  loadModel();

  // Function to create notification
  function createNotification(attachmentId, imageUrl) {
    error("Error stack:", error.stack);
    log("Creating notification with:", { attachmentId, imageUrl });

    const notification = document.createElement("div");
    notification.className = "notice notice-info is-dismissible";
    notification.id = "wpiag-notice";
    notification.innerHTML = `
            <p>${wpiagData.confirmText}</p>
            <p>
                <button type="button" class="button button-primary wpiag-generate" 
                        data-id="${attachmentId}" 
                        data-url="${imageUrl}">
                    ${wpiagData.generateText}
                </button>
                <button type="button" class="button wpiag-skip">
                    ${wpiagData.skipText}
                </button>
            </p>
        `;

    // Insert at the top of the page
    const wpBodyContent = document.querySelector(".wrap");
    if (wpBodyContent) {
      wpBodyContent.insertBefore(notification, wpBodyContent.firstChild);
      log("Notification inserted, button should be visible");

      // Verify button exists
      const button = notification.querySelector(".wpiag-generate");
      if (button) {
        log("Generate button created successfully");
        // Add direct click handler as backup
        button.addEventListener("click", function (e) {
          log("Direct button click detected");
        });
      } else {
        error("Failed to find generate button after creation");
      }
    } else {
      error("Could not find .wrap element to insert notification");
    }
  }

  // Handle generate button click
  document.body.addEventListener("click", async function (e) {
    log("Click detected on:", e.target);
    log("Target classes:", e.target.className);
    log(
      "Is generate button?",
      e.target.classList.contains("wpiag-generate")
    );

    // Check if button exists
    const generateButtons = document.querySelectorAll(".wpiag-generate");
    log("Found generate buttons:", generateButtons.length);

    if (e.target && e.target.classList.contains("wpiag-generate")) {
      log("Generate button clicked - handler starting");
      e.preventDefault();
      log("Generate button clicked");

      if (!model) {
        alert("Please wait, AI model is still loading...");
        return;
      }

      const button = e.target;
      const imageUrl = button.dataset.url;
      const attachmentId = button.dataset.id;

      // Debug log for button data
      log("Image URL:", imageUrl);
      log("Attachment ID:", attachmentId);

      if (!imageUrl || !attachmentId) {
        error("Missing image URL or attachment ID");
        alert("Error: Missing image data");
        return;
      }

      button.disabled = true;
      const originalText = button.textContent;
      button.textContent = "Generating...";

      try {
        // Create temporary image element
        const img = document.createElement("img");
        img.crossOrigin = "anonymous";
        img.src = imageUrl;

        log("Loading image:", imageUrl);

        // Wait for image to load
        await new Promise((resolve, reject) => {
          img.onload = () => {
            log("Image loaded successfully");
            resolve();
          };
          img.onerror = (error) => {
            error("Image load error:", error);
            reject(new Error("Failed to load image"));
          };
        });

        // Classify image
        log("Starting image classification...");
        let predictions;
        try {
          predictions = await model.classify(img);
          log("Raw predictions result:", predictions);
        } catch (classifyError) {
          error("Error during classification:", classifyError);
          alert("Error during image classification. Please try again.");
          button.disabled = false;
          button.textContent = originalText;
          return;
        }

        if (!predictions || !predictions.length) {
          error("No predictions returned from model");
          alert(
            "Could not generate description for this image. Please try again."
          );
          button.disabled = false;
          button.textContent = originalText;
          return;
        }

        try {
          log("Processing prediction result...");
          const altText = predictions[0].className
            .split(",")[0]
            .trim()
            .replace(/^a /i, "")
            .replace(/^an /i, "");

          if (!altText) {
            throw new Error("Generated alt text is empty");
          }

          log("Generated altText:", altText);

          // Create confirmation popup
          const confirmPopup = document.createElement("div");
          confirmPopup.className =
            "notice notice-info is-dismissible wpiag-confirm-popup";
          confirmPopup.style.cssText =
            "position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:200000; padding:20px; background:white; box-shadow:0 0 10px rgba(0,0,0,0.5); border-radius:5px; width:400px;";
          confirmPopup.innerHTML = `
                        <h3 style="margin-top:0;">Generated Alt Tag</h3>
                        <p>Your generated alt tag is:</p>
                        <p style="font-weight:bold; margin:10px 0; padding:10px; background:#f0f0f0; border-radius:3px;">${altText}</p>
                        <p>Would you like to use this alt tag?</p>
                        <div style="text-align:right; margin-top:15px;">
                            <button type="button" class="button wpiag-confirm-no" style="margin-right:10px;">No</button>
                            <button type="button" class="button button-primary wpiag-confirm-yes">Yes</button>
                        </div>
                    `;

          document.body.appendChild(confirmPopup);

          // Create overlay
          const overlay = document.createElement("div");
          overlay.style.cssText =
            "position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9998;";
          document.body.appendChild(overlay);

          // Handle Yes/No clicks
          return new Promise((resolve, reject) => {
            confirmPopup
              .querySelector(".wpiag-confirm-yes")
              .addEventListener("click", () => {
                confirmPopup.remove();
                overlay.remove();
                resolve(true);
              });

            confirmPopup
              .querySelector(".wpiag-confirm-no")
              .addEventListener("click", () => {
                confirmPopup.remove();
                overlay.remove();
                resolve(false);
              });
          }).then(async (confirmed) => {
            if (!confirmed) {
              button.disabled = false;
              button.textContent = originalText;
              return;
            }

            // Create success notification
            const successNotice = document.createElement("div");
            successNotice.className = "notice notice-success is-dismissible";
            successNotice.innerHTML = `
                            <p>Generated Alt Tag: <strong>${altText}</strong></p>
                        `;

            // Save via AJAX
            log("Saving alt text via AJAX...");
            const response = await fetch(wpiagData.ajaxUrl, {
              method: "POST",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
              body: new URLSearchParams({
                action: "generate_alt_tag",
                nonce: wpiagData.nonce,
                attachment_id: attachmentId,
                alt_text: altText,
              }),
            });

            const data = await response.json();
            log("AJAX response:", data);

            if (data.success) {
              // Update the alt text field if it exists
              const altTextField = document.querySelector(
                'input[name="attachments[' + attachmentId + '][alt]"]'
              );
              if (altTextField) {
                altTextField.value = altText;
                log("Alt text field updated");
              }

              // Show success message
              const notice =
                button.closest("#wpiag-notice") ||
                document.querySelector(".wrap");
              if (notice) {
                notice.parentNode.insertBefore(successNotice, notice);
              }

              button.textContent = "Alt Tag Generated!";
              setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                successNotice.remove();
              }, 3000);
            } else {
              throw new Error("Failed to save alt text");
            }
          });
        } catch (error) {
          error("Error in generate process:", error);
          alert("Error generating alt tag: " + error.message);
          button.disabled = false;
          button.textContent = originalText;
        }
      } catch (error) {
        error("Error in generate process:", error);
        alert("Error generating alt tag: " + error.message);
        button.disabled = false;
        button.textContent = originalText;
      }
    }

    // Handle skip button
    if (e.target && e.target.classList.contains("wpiag-skip")) {
      const notice = e.target.closest("#wpiag-notice");
      if (notice) {
        notice.remove();
      }
    }
  });

  // Check for new uploads
  function checkNewUpload() {
    fetch(wpiagData.ajaxUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "check_new_upload",
        nonce: wpiagData.nonce,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.data) {
          createNotification(data.data.id, data.data.url);
        }
      });
  }

  // Check for new uploads periodically
  setInterval(checkNewUpload, 2000);

  // Also check when Media Library updates
  if (wp.media) {
    wp.media.view.Modal.prototype.on("close", checkNewUpload);
  }
});
