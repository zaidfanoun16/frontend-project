function loadPrerequisites() {
  let table = document.getElementById("prTable");

  if (!table) return;

  table.innerHTML = "";

  prerequisites.forEach((p, index) => {
    table.innerHTML += `
      <tr>
        <td>${p.course}</td>
        <td>${p.prerequisite}</td>
        <td>
          <button onclick="deletePrerequisite(${index})">Delete</button>
        </td>
      </tr>
    `;
  });
}
function addPrerequisite() {
  let course = document.getElementById("course").value;
  let prerequisite = document.getElementById("prerequisite").value;

  if (!course || !prerequisite) {
    alert("Fill all fields!");
    return;
  }

  prerequisites.push({
    course,
    prerequisite
  });

  loadPrerequisites();
}
function deletePrerequisite(index) {
  prerequisites.splice(index, 1);
  loadPrerequisites();
}
window.onload = function () {
  if (document.getElementById("coursesTable")) {
    loadCourses();
  }

  if (document.getElementById("manageCoursesTable")) {
    loadManageCourses();
  }

  if (document.getElementById("prTable")) {
    loadPrerequisites();
  }
};
function logout() {
  localStorage.removeItem("user");
  window.location.href = "index.html";
}