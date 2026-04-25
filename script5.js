function loadManageCourses() {
  let table = document.getElementById("manageCoursesTable");

  if (!table) return;

  table.innerHTML = "";

  courses.forEach((course, index) => {
    table.innerHTML += `
      <tr>
        <td>${course.id}</td>
        <td>${course.name}</td>
        <td>${course.instructor}</td>
        <td>${course.credits}</td>
        <td>
          <button onclick="deleteCourse(${index})">Delete</button>
        </td>
      </tr>
    `;
  });
}
function addCourse() {
  let id = document.getElementById("courseId").value;
  let name = document.getElementById("courseName").value;
  let instructor = document.getElementById("instructor").value;
  let credits = document.getElementById("credits").value;

  if (!id || !name || !instructor || !credits) {
    alert("Fill all fields!");
    return;
  }

  courses.push({
    id,
    name,
    instructor,
    credits: parseInt(credits)
  });

  loadManageCourses();
}
function deleteCourse(index) {
  courses.splice(index, 1);
  loadManageCourses();
} 
window.onload = function () {
  if (document.getElementById("coursesTable")) {
    loadCourses();
  }

  if (document.getElementById("manageCoursesTable")) {
    loadManageCourses();
  }
};
function logout() {
  localStorage.removeItem("user");
  window.location.href = "index.html";
}