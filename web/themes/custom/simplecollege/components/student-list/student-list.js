document.addEventListener("DOMContentLoaded", () => {
    console.log("Checking if #student-list-items exists...");
    const listContainer = document.querySelector("#student-list-items");

    if (!listContainer) {
      console.error("Error: #student-list-items element not found.");
      return;
    }

    console.log("Found #student-list-items, proceeding with data fetching...");

    (async () => {
      try {
        const response = await fetch("/jsonapi/node/student?sort=-created&page[limit]=10", {
          headers: { "Accept": "application/vnd.api+json" },
        });

        const data = await response.json();
        listContainer.innerHTML = "";

        const studentPromises = data.data.map(async (student) => {
          const title = student.attributes.title;
          const nodeId = student.attributes.drupal_internal__nid; // Get numeric Node ID
          const studentPath = `/node/${nodeId}`; // Correct URL

          const departmentId = student.relationships.field_dept?.data?.id;
          let departmentName = "Unknown";

          if (departmentId) {
            try {
              const deptResponse = await fetch(`/jsonapi/taxonomy_term/department/${departmentId}`);
              const deptData = await deptResponse.json();
              departmentName = deptData.data.attributes.name;
            } catch (error) {
              console.error(`Error fetching department name for ID ${departmentId}:`, error);
            }
          }

          // Create clickable student card with Tailwind styling
          const listItem = document.createElement("li");
          listItem.className = "flex items-center justify-between bg-gray-100 p-3 rounded-lg shadow-sm hover:bg-gray-200 transition cursor-pointer";
          listItem.innerHTML = `
            <span class="font-medium text-gray-700">${title}</span>
            <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded-lg">${departmentName}</span>
          `;

          // Add click event to navigate to the student profile
          listItem.addEventListener("click", () => {
            window.location.href = studentPath;
          });

          return listItem;
        });

        const listItems = await Promise.all(studentPromises);
        listItems.forEach((item) => listContainer.appendChild(item));

      } catch (error) {
        console.error("Error fetching student data:", error);
        listContainer.innerHTML = "<li class='text-red-500'>Failed to load students.</li>";
      }
    })();
  });
