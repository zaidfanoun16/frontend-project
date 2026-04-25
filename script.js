function login(event) {
  event.preventDefault();

  let email = document.getElementById("email").value;
  let password = document.getElementById("password").value;
  let role = document.getElementById("role").value;

  
  let user = {
    email: email,
    role: role
  };

  
  localStorage.setItem("user", JSON.stringify(user));

  
  if (role === "admin") {
    window.location.href = "admin-dashboard.html";
  } else {
    window.location.href = "student-dashboard.html";
  }
}
function logout() {
  localStorage.removeItem("user");
  window.location.href = "index.html";
}