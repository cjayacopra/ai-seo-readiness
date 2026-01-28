/** Plugin Custom Script File  */
jQuery(document).ready(function($){

    // Form Submit via Ajax
    $("#webform").on("submit", function(event){

        event.preventDefault();
        $("#website").prop("disabled", true);
        $(".btn-crawl").prop("disabled", true).text("Crawling...");
        const websiteURL = $("#website").val();

        $("#results").html('<div class="loading-wrap"><p>⏳ Crawling started... please wait to get reports</p></div>');

        // Trigger Ajax Request
        $.ajax({
            url: wcVars.ajaxurl,
            data: {
                action: 'website_crawl',
                website_url: websiteURL,
                security: wcVars.nonce // Nonce verification
            },
            method: 'POST',
            dataType: 'JSON',
            success: function(response) {
                console.log("Crawl Response:", response);
                if(response.status == true || (response.success && response.data && response.data.html)){
                    const html = response.html || response.data.html;
                    $("#results").html(html);
                } else {
                    const errorMsg = (response.data && response.data.error) ? response.data.error : (response.error ? response.error : 'Invalid URL or crawl blocked. Please try again.');
                    $("#results").html('<p class="error">' + errorMsg + '</p>');
                }
                $("#website").prop("disabled", false);
                $(".btn-crawl").prop("disabled", false).text("Crawl Website");
            },
            error: function(xhr, status, error){
                console.error("AJAX Error Details:");
                console.error("Status Text:", status);
                console.error("HTTP Error:", error);
                console.error("Response Body:", xhr.responseText);
                
                let detailedError = "Something went wrong (Connection Error). Please try again.";
                
                if (xhr.status === 403) {
                    detailedError = "Security Error (403 Forbidden): Nonce verification failed. This usually happens if your session expired or you logged in/out in another tab. Please refresh the page and try again.";
                } else if (xhr.status === 404) {
                    detailedError = "Error (404): AJAX endpoint not found. Please check if the plugin is activated.";
                } else if (xhr.status === 500) {
                    detailedError = "Server Error (500): The crawler encountered a fatal error. This might be due to a memory limit or a PHP crash. Please check the server logs.";
                } else if (status === 'timeout') {
                    detailedError = "Request Timeout: The server took too long to respond. The website you are crawling might be very slow.";
                }
                
                $("#results").html('<p class="error">' + detailedError + '</p>');
                
                $("#website").prop("disabled", false);
                $(".btn-crawl").prop("disabled", false).text("Crawl Website");
            }
        });
    });

    // Toggle Offending Elements
    $(document).on("click", ".snippet-toggle", function(){
        const targetId = $(this).data("target");
        const $target = $("#" + targetId);
        
        $target.slideToggle(200);
        
        // Optional: Toggle arrow icon text if needed
        // const currentText = $(this).text();
        // $(this).text(currentText.includes("▼") ? currentText.replace("▼", "▲") : currentText.replace("▲", "▼"));
    });
});