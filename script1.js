document.addEventListener("DOMContentLoaded", function () {
// Dummy data
document.getElementById("totalCourses").innerText = 10;
document.getElementById("myCourses").innerText = 3;
document.getElementById("availableCourses").innerText = 7;
});
function logout() {
  localStorage.removeItem("user");
  window.location.href = "index.html";
}