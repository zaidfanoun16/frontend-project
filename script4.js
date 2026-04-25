if (user.role === "student") {
  window.location.href = "student-dashboard.html";
} else {
  window.location.href = "admin-dashboard.html";
}
function logout() {
  localStorage.removeItem("user");
  window.location.href = "index.html";
}