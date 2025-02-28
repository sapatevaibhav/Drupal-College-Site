document.addEventListener("DOMContentLoaded", async () => {
    const profileContainer = document.querySelector(".student-profile");

    if (!profileContainer) {
        return;
    }

    const currentPath = window.location.pathname;


    // Step 1: Fetch all students with their aliases
    const response = await fetch("/jsonapi/node/student?fields[node--student]=title,path,id", {
        headers: { "Accept": "application/vnd.api+json" },
    });

    if (!response.ok) {
        throw new Error(`Failed to fetch students list. Status: ${response.status}`);
    }

    const data = await response.json();
    const students = data.data;


    // Step 2: Find student by path alias
    const student = students.find(s => s.attributes.path.alias === currentPath);

    if (!student) {
        return;
    }


    const studentUUID = student.id; // Use UUID instead of nid

    // Step 3: Fetch student details using UUID
    const studentResponse = await fetch(`/jsonapi/node/student/${studentUUID}?include=field_dept,field_profile_picture`, {
        headers: { "Accept": "application/vnd.api+json" },
    });

    if (!studentResponse.ok) {
        throw new Error(`Failed to fetch student data. Status: ${studentResponse.status}`);
    }

    const studentData = await studentResponse.json();
    const studentDetails = studentData.data;

    if (!studentDetails) {
        return;
    }

    // Extract student details
    const title = studentDetails.attributes.title;
    const course = studentDetails.attributes.field_course;
    const yearOfStudy = studentDetails.attributes.field_year_of_study;
    const personalStatement = studentDetails.attributes.field_personal_statement;

    // Fetch profile picture
    let profilePicture = "";
    const profilePictureId = studentDetails.relationships.field_profile_picture?.data?.id;
    if (profilePictureId) {
        const profileResponse = await fetch(`/jsonapi/file/file/${profilePictureId}`);
        const profileData = await profileResponse.json();
        profilePicture = profileData.data.attributes.uri.url;
    }

    // Fetch department name
    let department = "Unknown";
    if (studentDetails.relationships.field_dept?.data?.id) {
        const deptResponse = await fetch(`/jsonapi/taxonomy_term/department/${studentDetails.relationships.field_dept.data.id}`);
        const deptData = await deptResponse.json();
        department = deptData.data.attributes.name;
    }

    // Inject data into the component
    profileContainer.innerHTML = `
        <div class="max-w-3xl mx-auto">
          <div class="flex items-center space-x-6">
            ${profilePicture ? `<img src="${profilePicture}" alt="${title}" class="w-24 h-24 rounded-full border-4 border-blue-500 shadow-md">` : ""}
            <div>
              <h1 class="text-3xl font-bold text-gray-900">${title}</h1>
              <span class="text-sm bg-green-500 text-white px-3 py-1 rounded-lg">${department}</span>
            </div>
          </div>
          <div class="mt-6">
            <h2 class="text-xl font-semibold text-gray-800">Course: ${course}</h2>
            <h3 class="text-lg text-gray-700">Year of Study: ${yearOfStudy}</h3>
            <p class="mt-4 text-gray-600">${personalStatement}</p>
          </div>
        </div>
      `;

}
);
