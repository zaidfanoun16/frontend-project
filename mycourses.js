function loadMyCourses() {
  let table = document.getElementById("myCoursesTable");
  if (!table) return;

  let user = JSON.parse(localStorage.getItem("user"));
  let registrations = JSON.parse(localStorage.getItem("registrations")) || [];

  table.innerHTML = "";

  let myCourses = registrations.filter(r => r.student === user.email);

  if (myCourses.length === 0) {
    table.innerHTML = `<tr><td colspan="5">No courses registered</td></tr>`;
    return;
  }

  myCourses.forEach(r => {
    let course = courses.find(c => c.id === r.course);

    if (course) {
      table.innerHTML += `
        <tr>
          <td>${course.id}</td>
          <td>${course.name}</td>
          <td>${course.instructor}</td>
          <td>${course.credits}</td>
          <td>
            <button onclick="dropCourse('${course.id}')">Drop</button>
          </td>
        </tr>
      `;
    }
  });
}
function dropCourse(courseId) {
  let user = JSON.parse(localStorage.getItem("user"));
  let registrations = JSON.parse(localStorage.getItem("registrations")) || [];

  registrations = registrations.filter(
    r => !(r.student === user.email && r.course === courseId)
  );

  localStorage.setItem("registrations", JSON.stringify(registrations));

  loadMyCourses();
}
window.onload = function () {
  if (document.getElementById("coursesTable")) loadCourses();
  if (document.getElementById("manageCoursesTable")) loadManageCourses();
  if (document.getElementById("prTable")) loadPrerequisites();
  if (document.getElementById("overviewTable")) loadOverview();
  if (document.getElementById("myCoursesTable")) loadMyCourses();
};