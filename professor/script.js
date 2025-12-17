

function makeEditable(cell, thesisId, field) {
    const oldValue = cell.innerText.trim(); // Trim any unnecessary whitespace

    // If the cell is empty, set the oldValue to an empty string
    const initialValue = oldValue === "" ? "" : oldValue;

    const input = document.createElement("input");
    input.type = "text";
    input.value = initialValue;  // Set the input field's value to either the old value or empty

    input.onblur = function() {
        updateCell(cell, thesisId, field, oldValue, this.value);
    };

    input.onkeydown = function(e) {
        if (e.key === "Enter") {
            updateCell(cell, thesisId, field, oldValue, this.value);
        } else if (e.key === "Escape") {
            cancelEdit(cell, oldValue);
        }
    };

    cell.innerHTML = ""; // Clear the cell content
    cell.appendChild(input); // Add the input element to the cell
    input.focus(); // Focus on the input field
}

function updateCell(cell, thesisId, field, oldValue, newValue) {
    if (oldValue === newValue) {
        cell.innerText = oldValue;
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            if (xhr.responseText === "success") {
                cell.innerText = newValue === "" ? "(empty)" : newValue; // Display "(empty)" if the value is empty
            } else {
                alert("Error updating value: " + xhr.responseText);
                cell.innerText = oldValue;
            }
        }
    };
    xhr.send("thesis_id=" + thesisId + "&field=" + field + "&value=" + encodeURIComponent(newValue));
}

function cancelEdit(cell, oldValue) {
    cell.innerText = oldValue === "" ? "(empty)" : oldValue; // Show "(empty)" if it was originally empty
}


function updateStudent(thesis_id) {
    var student_id = document.getElementById('student_dropdown_' + thesis_id).value;
    var form = document.getElementById('updateStudentForm_' + thesis_id);
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'student_id';
    input.value = (student_id === 'NULL') ? "" : student_id;
    form.appendChild(input);
    form.submit();
}


function filterStudentDropdown(thesis_id) {
    var input = document.getElementById("student_search_" + thesis_id);
    var filter = input.value.toUpperCase();
    var dropdown = document.getElementById("student_dropdown_" + thesis_id);
    var options = dropdown.getElementsByTagName("option");

    for (var i = 0; i < options.length; i++) {
        var option = options[i];
        var text = option.textContent || option.innerText;
        if (text.toUpperCase().indexOf(filter) > -1) {
            option.style.display = "";
        } else {
            option.style.display = "none";
        }
    }
}
let currentThesisId = null; // Track the current thesis ID

// Function to show the notes popup
function showNotesPopup(thesisId) {
    currentThesisId = thesisId; // Save the thesis ID
    document.getElementById('notes-popup').style.display = 'block';

    // Fetch existing notes for this thesis and display them in the textarea
    fetch(`fetch_notes.php?thesis_id=${thesisId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('notes-textarea').value = data; // Load notes into the textarea
        })
        .catch(error => console.error('Error fetching notes:', error));
}

// Function to close the notes popup
function closeNotesPopup() {
    document.getElementById('notes-popup').style.display = 'none';
}

function saveNotes() {
    const notes = document.getElementById('notes-textarea').value;

    // Create a FormData object to send data like a form submission
    const formData = new FormData();
    formData.append('thesis_id', currentThesisId); // Add thesis ID to the form data
    formData.append('notes', notes); // Add notes text to the form data

    // Log the data being sent to the server
    console.log('Sending data:', {
        thesis_id: currentThesisId,
        notes: notes
    });

    // Send the notes to the server to be saved
    fetch('save_notes.php', {
        method: 'POST',
        body: formData // Send the form data
    })
    .then(response => response.text()) // Expect plain text response
    .then(data => {
        console.log('Server response:', data); // Log the server response
        if (data.includes('Notes saved successfully!')) {
            alert('Notes saved successfully!');
            closeNotesPopup(); // Close the popup after saving
        } else {
            alert('Failed to save notes.');
        }
    })
    .catch(error => console.error('Error saving notes:', error));
}

function showThesisHistory(thesisId) {
    console.log('Show thesis history button clicked, thesisId:', thesisId);

    // Fetch history data
    fetch(`fetch_history.php?thesis_id=${thesisId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Build the history HTML
                let historyHtml = '<h3>Thesis History</h3>';
                historyHtml += '<table>';
                historyHtml += '<thead><tr><th>Date/Time</th><th>Action</th><th>Old Value</th><th>New Value</th><th>Field of Change</th></tr></thead>';
                historyHtml += '<tbody>';

                data.history.forEach(record => {
                    historyHtml += `<tr>
                        <td>${record.datetime}</td>
                        <td>${record.action}</td>
                        <td>${record.old_value}</td>
                        <td>${record.new_value}</td>
                        <td>${record.field_of_change}</td>
                    </tr>`;
                });

                historyHtml += '</tbody></table>';

                // Insert the history HTML into the thesis history popup content
                document.getElementById('thesis-history-popup-content').innerHTML = historyHtml;

                // Show the thesis history popup and overlay
                document.getElementById('thesis-history-popup').style.display = 'block';
                document.getElementById('thesis-history-popup-overlay').style.display = 'block';
            } else {
                alert('No history found for this thesis.');
            }
        })
        .catch(error => {
            console.error('Error fetching thesis history:', error);
            alert('Error fetching thesis history.');
        });
}

function closeThesisHistoryPopup() {
    console.log("thesis history close button pressed");
    // Hide the thesis history popup and overlay
    document.getElementById('thesis-history-popup').style.display = 'none';
    document.getElementById('thesis-history-popup-overlay').style.display = 'none';
}

function showPopup(thesisId) {
    console.log('Opening popup for thesis ID:', thesisId);

    fetch(`fetch_invitations.php?thesis_id=${thesisId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            console.log('Data received from server:', data); // Log the response data to inspect it
            
            const popupContent = document.getElementById('popup-content');
            if (popupContent) {
                popupContent.innerHTML = data;
                const popup = document.getElementById('popup');
                popup.style.display = 'block';
            } else {
                console.error('popup-content element not found');
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        });
}


function closePopup() {
    const popup = document.querySelector('.popup'); // Select by class
    const overlay = document.querySelector('.popup-overlay'); // Select by class
    console.log("close button pressed");
    if (popup) popup.style.display = 'none'; // Hide the popup
    if (overlay) overlay.style.display = 'none'; // Hide the overlay
}



function deleteThesis(thesis_id) {
    const confirmation = confirm("Are you sure you want to cancel this thesis?");
    if (!confirmation) return;

    const arithmosProtokolou = prompt("Enter Arithmos Protokolou:");
    if (!arithmosProtokolou || isNaN(arithmosProtokolou.trim())) {
        alert("Invalid input. Please enter a valid number.");
        return;
    }

    fetch('delete_thesis.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `thesis_id=${encodeURIComponent(thesis_id)}&ap=${encodeURIComponent(arithmosProtokolou.trim())}`,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = "index.php"; // Redirect on success
        } else {
            alert(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}




document.getElementById('addThesisButton').addEventListener('click', function () {
    const form = document.getElementById('addNewRowForm');
    const formData = new FormData(form);

    fetch('insert_thesis.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Optionally reload or update the page
            window.location.reload();
        } else {
            alert(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
});



function showDescPopup(thesisId) {
    currentThesisId = thesisId; 
    document.getElementById('desc-popup').style.display = 'block';

    fetch(`fetch_description.php?thesis_id=${thesisId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('desc-textarea').value = data;
        })
        .catch(error => console.error('Error fetching description:', error));
}

function closeDescPopup() {
    document.getElementById('desc-popup').style.display = 'none';
}

function saveDesc() {
    const desc = document.getElementById('desc-textarea').value;

    const formData = new FormData();
    formData.append('thesis_id', currentThesisId);
    formData.append('description', desc);

    console.log('Sending data:', {
        thesis_id: currentThesisId,
        description: desc
    });

    fetch('save_description.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Only show the server's response if it's an error message
        if (!data.includes('Description saved successfully!')) {
            alert(data);  // Display the error message returned from the server
        } else {
            alert('Description saved successfully!');
            closeDescPopup();
        }
    })
    .catch(() => {
        // If an error occurs during the fetch, silently ignore it
    });
}
