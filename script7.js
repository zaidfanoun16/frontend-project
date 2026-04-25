function loadOverview() {
  let table = document.getElementById("overviewTable");

  if (!table) return;

  table.innerHTML = "";

  registrations.forEach(r => {
    table.innerHTML += `
      <tr>
        <td>${r.student}</td>
        <td>${r.course}</td>
      </tr>
    `;
  });
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

  if (document.getElementById("overviewTable")) {
    loadOverview();
  }
};
function logout() {
  localStorage.removeItem("user");
  window.location.href = "index.html";
}