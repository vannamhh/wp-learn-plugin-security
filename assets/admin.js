jQuery(document).ready(function ($) {
  console.log("WP Learn Plugin Security admin.js loaded");
  $(".delete-submission").on("click", function (event) {
    console.log("Delete button clicked");
    let this_button = $(this);
    event.preventDefault();

    let id = this_button.data("id");
    console.log("Delete submission id: " + id);

    $.post(
      wp_learn_ajax.ajax_url,
      {
        action: "delete_form_submission",
        id: id,
        nonce: wp_learn_ajax.nonce // Add the nonce to the AJAX request
      },
      function (response) {
        console.log(response);
        alert("Form submission deleted");
        document.location.reload();
      },
    );
  });
});
