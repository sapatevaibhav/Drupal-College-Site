document.addEventListener("DOMContentLoaded", () => {

    const listContainer = document.querySelector("#student-list-items");

    if (!listContainer) {
        return;
    }


    (async () => {
        try {
            const response = await fetch("/jsonapi/node/student?sort=-created&page[limit]=10&fields[node--student]=title,path,drupal_internal__nid", {
                headers: { "Accept": "application/vnd.api+json" },
            });

            if (!response.ok) {
                throw new Error(`HTTP Error! Status: ${response.status}`);
            }

            const data = await response.json();

            if (!data.data || data.data.length === 0) {
                throw new Error("No students found in API response.");
            }

            listContainer.innerHTML = "";

            const studentPromises = data.data.map(async (student) => {
                const title = student.attributes.title;
                const studentPath = student.attributes.path?.alias || `/node/${student.attributes.drupal_internal__nid}`;

                const departmentId = student.relationships?.field_dept?.data?.id;
                let departmentName = "Unknown";

                if (departmentId) {
                    try {
                        const deptResponse = await fetch(`/jsonapi/taxonomy_term/department/${departmentId}`);
                        if (!deptResponse.ok) throw new Error("Department fetch failed.");
                        const deptData = await deptResponse.json();
                        departmentName = deptData.data.attributes.name;
                    } catch (error) {
                    }
                }

                // Create student card
                const listItem = document.createElement("li");
                listItem.className = "flex items-center justify-between bg-gray-100 p-3 rounded-lg shadow-sm hover:bg-gray-200 transition cursor-pointer";
                listItem.innerHTML = `
            <span class="font-medium text-gray-700">${title}</span>
            <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded-lg">${departmentName}</span>
          `;

                // Redirect on click
                listItem.addEventListener("click", () => {
                    window.location.href = studentPath;
                });

                return listItem;
            });

            const listItems = await Promise.all(studentPromises);
            listItems.forEach((item) => listContainer.appendChild(item));

        } catch (error) {
            listContainer.innerHTML = "<li class='text-red-500'>Failed to load students.</li>";
        }
    })();
});
