$(document).ready(function () {

  $("#repeatedPassword").on('keyup', function () {
    var pw = $("#edit_participant_password").val();
    var repeatPw = $(this).val();

    if(repeatPw != pw) {
      $(".repeatedPasswordError").html("Die beiden Passwörter stimmen nicht überein.");
    } else {
      $(".repeatedPasswordError").html("");
    }
  });

  $("#edit_participant_password").on('keyup', function () {
    var pw = $(this).val();

    if(pw.length < 8) {
      $(".passwordError").html("Das Password muss mindestens 8 Zeichen lang sein.");
    } else {
      $(".passwordError").html("");
    }
  });

  $("#edit_participant_submit").click(function (event) {
    var pw = $("#edit_participant_password").val();
    var repeatPw = $("#repeatedPassword").val();

    if(repeatPw != pw) {
      event.preventDefault();
      event.preventBubble();
    }
  });
});