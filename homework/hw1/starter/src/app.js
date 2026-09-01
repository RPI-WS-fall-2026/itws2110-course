// Course Background Survey
// Client-side validation (intro course style: alert boxes and inline handlers)

var GOALS_PLACEHOLDER = "Please tell us what you hope to get from this course.";

// Validate the survey: names, email, LAMP comfort, and goals are required.
function validateForm() {
  var fields = ["firstName", "lastName", "email"];
  var labels = ["First name", "Last name", "RPI email"];

  for (var i = 0; i < fields.length; i++) {
    var value = document.getElementById(fields[i]).value;
    if (value.trim() === "") {
      alert(labels[i] + " must be filled out.");
      document.getElementById(fields[i]).focus();
      return false;
    }
  }

  var lamp = document.querySelector('input[name="lampComfort"]:checked');
  if (lamp === null) {
    alert("Please rate your comfort with the LAMP stack.");
    return false;
  }

  var goals = document.getElementById("goals").value;
  if (goals.trim() === "" || goals === GOALS_PLACEHOLDER) {
    alert("Please tell us what you want to get out of the course.");
    document.getElementById("goals").focus();
    return false;
  }

  alert("Thank you! Sending your survey to the server.");
  return true;
}

// Clear the placeholder text when the user clicks into the textarea,
// but never delete anything the user typed themselves.
function clearGoals() {
  var box = document.getElementById("goals");
  if (box.value === GOALS_PLACEHOLDER) {
    box.value = "";
  }
}

// If the user leaves the textarea empty, put the placeholder back.
function restoreGoals() {
  var box = document.getElementById("goals");
  if (box.value.trim() === "") {
    box.value = GOALS_PLACEHOLDER;
  }
}

// Focus the first form element on load.
window.onload = function () {
  document.getElementById("firstName").focus();
};
