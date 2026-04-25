
function loadCourses() {
  let table = document.getElementById("coursesTable");

  table.innerHTML = "";

  courses.forEach(course => {
    table.innerHTML += `
      <tr>
        <td>${course.id}</td>
        <td>${course.name}</td>
        <td>${course.instructor}</td>
        <td>${course.credits}</td>
        <td><button onclick="registerCourse('${course.id}')">Register</button></td>
      </tr>
    `;
  });
}


function registerCourse(courseId) {
  let user = JSON.parse(localStorage.getItem("user"));

  if (!user) {
    alert("You must login first!");
    return;
  }

  
  let registrations = JSON.parse(localStorage.getItem("registrations")) || [];

  
  let already = registrations.find(r => r.student === user.email && r.course === courseId);

  if (already) {
    alert("Already registered!");
    return;
  }

 
  registrations.push({
    student: user.email,
    course: courseId
  });

  localStorage.setItem("myCoursesTable", JSON.stringify(registrations));

  alert("Registered successfully!");
}
function filterCourses() {
  let input = document.getElementById("search").value.toLowerCase();
  let rows = document.querySelectorAll("#coursesTable tr");

  rows.forEach(row => {
    let text = row.innerText.toLowerCase();
    row.style.display = text.includes(input) ? "" : "none";
  });
}
window.onload = function () {
  if (document.getElementById("coursesTable")) {
    loadCourses();
  }
};

function logout() {
  localStorage.removeItem("user");
  window.location.href = "index.html";
}