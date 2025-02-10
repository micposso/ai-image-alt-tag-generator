console.log("Alt Tag Generator: Script loaded 2");

jQuery(document).ready(function($) {
    console.log("Alt Tag Generator: DOM Ready");

    // Listen for WordPress media upload complete
    $(document).on('wp-upload-complete', function(event, response) {
        console.log("Alt Tag Generator: Upload complete detected", response);

        if (response && response.length > 0) {
            response.forEach(function(attachment) {
                if (attachment.type === 'image') {
                    console.log("Alt Tag Generator: Processing uploaded image:", attachment);
                    
                    const imageID = attachment.id;
                    // Generate placeholder alt text (you'll replace this with AI generation)
                    let generatedAltText = "Auto-generated description for image ID " + imageID;
                    
                    // Update the alt text
                    updateAltText(imageID, generatedAltText);
                }
            });
        }
    });

    function updateAltText(imageID, newAltText) {
        console.log("Alt Tag Generator: Updating alt text for image:", {
            imageID: imageID,
            newAltText: newAltText
        });

        $.ajax({
            url: AutoAltTextSettings.restUrl + imageID,
            method: "POST",
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-WP-Nonce", AutoAltTextSettings.nonce);
            },
            data: {
                alt_text: newAltText
            },
            success: function(response) {
                console.log("Alt Tag Generator: Successfully updated alt text:", response);
            },
            error: function(error) {
                console.error("Alt Tag Generator: Error updating alt text:", {
                    status: error.status,
                    statusText: error.statusText,
                    responseText: error.responseText
                });
            }
        });
    }
});
