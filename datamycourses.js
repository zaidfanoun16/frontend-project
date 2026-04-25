
const users = [
  {
    email: "student@test.com",
    password: "1234",
    role: "student"
  },
  {
    email: "admin@test.com",
    password: "1234",
    role: "admin"
  }
];


let courses = [
  {
    id: "CS101",
    name: "Introduction to Programming",
    instructor: "Dr. Ahmed",
    credits: 3
  },
  {
    id: "CS102",
    name: "Web Development",
    instructor: "Dr. Sara",
    credits: 4
  },
  {
    id: "CS103",
    name: "Database Systems",
    instructor: "Dr. Omar",
    credits: 3
  }
];


let prerequisites = [
  {
    course: "CS102",
    prerequisite: "CS101"
  },
  {
    course: "CS103",
    prerequisite: "CS102"
  }
];


let registrations = JSON.parse(localStorage.getItem("registrations")) || [];