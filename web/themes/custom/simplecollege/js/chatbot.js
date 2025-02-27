(function (Drupal, once) {
    Drupal.behaviors.chatbot = {
      attach: function (context, settings) {
        once('chatbot', 'body', context).forEach(() => {
          // Create chatbot button
          const chatbotButton = document.createElement("button");
          chatbotButton.innerText = "Chatbot";
          chatbotButton.classList.add("fixed", "bottom-4", "right-4", "bg-blue-600", "text-white", "p-3", "rounded-full", "shadow-lg");
          chatbotButton.setAttribute("id", "chatbot-toggle");

          document.body.appendChild(chatbotButton);

          // Event listener for chatbot popup
          chatbotButton.addEventListener("click", function () {
            let chatbot = document.getElementById("chatbot-container");
            if (!chatbot) {
              chatbot = document.createElement("div");
              chatbot.id = "chatbot-container";
              chatbot.classList.add("fixed", "bottom-16", "right-4", "bg-white", "shadow-lg", "rounded-lg", "w-80", "h-96", "flex", "flex-col", "border");

              chatbot.innerHTML = `
                <div class="bg-blue-600 text-white p-3 rounded-t-lg text-center flex justify-between items-center">
                  <strong>Chatbot</strong>
                  <button onclick="document.getElementById('chatbot-container').remove()" class="text-white">✖</button>
                </div>
                <div id="chatbot-messages" class="flex-1 p-3 overflow-y-auto space-y-2 h-1/2">
                  <p class="text-gray-500">Ask a question below.</p>
                </div>
                <div class="p-3 border-t h-1/2 overflow-y-auto">
                  <ul class="space-y-2">
                    ${getQuestionsHTML()}
                  </ul>
                </div>
              `;

              document.body.appendChild(chatbot);
            }
          });
        });
      }
    };

    function getQuestionsHTML() {
      const questions = [
        { text: "Who is the most recently joined student?", url: "/jsonapi/node/student?sort=-created&page[limit]=1" },
        { text: "List 5 students from the Tech department", url: "/jsonapi/node/student?filter[field_dept.meta.drupal_internal__target_id]=1&page[limit]=5" },
        { text: "List all students from the Cloclugidauipal course", url: "/jsonapi/node/student?filter[field_course]=Cloclugidauipal" },
        { text: "What are the names of all departments?", url: "/jsonapi/node/department" },
        { text: "Give a short description of the Tech department", url: "/jsonapi/node/department?filter[field_department_name]=Tech" },
        { text: "List teachers specializing in Dignissim ex", url: "/jsonapi/node/teacher?filter[field_subject_specialization]=Dignissim%20ex" },
        { text: "Which teachers are available during Ro office hours?", url: "/jsonapi/node/teacher?filter[field_office_hours]=Ro" },
        { text: "Which teachers belong to the HR department?", url: "/jsonapi/node/teacher?filter[field_department.meta.drupal_internal__target_id]=9" },
        { text: "Show the latest department added", url: "/jsonapi/node/department?sort=-created&page[limit]=1" },
        { text: "What is the personal statement of Hendrerit Ideo?", url: "/jsonapi/node/student?filter[title]=Hendrerit%20Ideo" }
      ];

      return questions.map(q =>
        `<li>
          <button onclick="askQuestion('${q.url}', '${q.text}')" class="w-full text-right bg-blue-500 text-white px-4 py-2 rounded-lg">
            ${q.text}
          </button>
        </li>`
      ).join("");
    }

    window.askQuestion = function (url, questionText) {
      const messages = document.getElementById("chatbot-messages");
      messages.innerHTML += `<div class="text-right"><p class="bg-blue-500 text-white px-4 py-2 rounded-lg inline-block">${questionText}</p></div>`;

      fetch(url, { headers: { "Accept": "application/vnd.api+json" } })
        .then(response => response.json())
        .then(data => {
          let result = "No data found.";
          if (data.data && data.data.length > 0) {
            result = data.data.map(item => item.attributes.title || item.attributes.field_department_name).join(", ");
          }

          messages.innerHTML += `<div class="text-left"><p class="bg-gray-200 text-gray-900 px-4 py-2 rounded-lg inline-block">${result}</p></div>`;
          messages.scrollTop = messages.scrollHeight;
        })
        .catch(error => console.error("Fetch Error:", error));
    };
  })(Drupal, once);
