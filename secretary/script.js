function showDescPopup(thesisId) {
    currentThesisId = thesisId; 
    const popup = document.getElementById('desc-popup');
    
    if (popup) {
        popup.style.display = 'block';

        fetch(`fetch_description.php?thesis_id=${thesisId}`)
            .then(response => response.text())
            .then(data => {
                const descText = document.getElementById('desc-textarea');
                if (descText) {
                    descText.textContent = data; // Set the fetched description as text
                } else {
                    console.error('Description text element not found.');
                }
            })
            .catch(error => console.error('Error fetching description:', error));
    } else {
        console.error('Popup element not found.');
    }
}

function closeDescPopup() {
    const popup = document.getElementById('desc-popup');
    if (popup) {
        popup.style.display = 'none';
    } else {
        console.error('Popup element not found.');
    }
}


document.querySelectorAll('.delete-thesis-button').forEach(button => {
    button.addEventListener('click', () => {
        const thesisId = button.dataset.thesisId; // Assume each delete button has a data attribute for the thesis ID.

        // Confirm if the user really wants to delete
        const confirmDelete = confirm('Are you sure you want to delete this thesis?');
        if (!confirmDelete) return;

        // Ask for "Arithmos Protokolou"
        const arithmosProtokolou = prompt('Please enter the Arithmos Protokolou (AP):');
        if (!arithmosProtokolou || isNaN(arithmosProtokolou)) {
            alert('Invalid Arithmos Protokolou. Please try again.');
            return;
        }

        // Send the request to the backend
        fetch('delete_thesis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `thesis_id=${thesisId}&arithmos_protokolou=${arithmosProtokolou}`,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.href = 'secretary.php';
                } else {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            });
    });
});


document.addEventListener("DOMContentLoaded", () => {
    // Format time based on the elapsed duration
    const formatElapsedTime = (seconds) => {
        const years = Math.floor(seconds / (365 * 24 * 3600)); // Approximate years
        seconds %= 365 * 24 * 3600;

        const months = Math.floor(seconds / (30 * 24 * 3600)); // Approximate months
        seconds %= 30 * 24 * 3600;

        const days = Math.floor(seconds / (24 * 3600));
        seconds %= 24 * 3600;

        const hours = Math.floor(seconds / 3600);
        seconds %= 3600;

        const minutes = Math.floor(seconds / 60);
        seconds %= 60;

        let result = "";

        if (years > 0) {
            result += `${years} year${years > 1 ? "s" : ""}, `;
        }
        if (months > 0 || years > 0) {
            result += `${months} month${months > 1 ? "s" : ""}, `;
        }
        if (days > 0 || months > 0 || years > 0) {
            result += `${days} day${days > 1 ? "s" : ""}, `;
        }
        if (hours > 0 || days > 0 || months > 0 || years > 0) {
            result += `${hours} hour${hours > 1 ? "s" : ""}, `;
        }
        if (minutes > 0 || hours > 0 || days > 0 || months > 0 || years > 0) {
            result += `${minutes} minute${minutes > 1 ? "s" : ""}, `;
        }
        result += `${seconds} second${seconds > 1 ? "s" : ""}`;

        return result;
    };

    // Update runtime elements
    const updateRuntime = () => {
        const runtimeElements = document.querySelectorAll(".runtime");
        runtimeElements.forEach((el) => {
            const startTime = new Date(el.getAttribute("data-start")); // Parse yyyy-mm-dd hh:mm:ss
            const now = new Date();
            const diffSeconds = Math.floor((now - startTime) / 1000); // Difference in seconds
            if (diffSeconds >= 0) {
                el.textContent = formatElapsedTime(diffSeconds);
            } else {
                el.textContent = "N/A";
            }
        });
    };

    // Update runtimes every second
    updateRuntime();
    setInterval(updateRuntime, 1000);
});


// This will trigger the prompt and submit the form after user input
function submitForm(thesisId) {
    var ap = prompt('Enter Arithmos Protokolou:');
    if (ap !== null && ap.trim() !== '') {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'update_state.php';  // Ensure form action points to correct PHP file

        var inputThesisId = document.createElement('input');
        inputThesisId.type = 'hidden';
        inputThesisId.name = 'thesis_id';
        inputThesisId.value = thesisId;
        form.appendChild(inputThesisId);

        var inputAp = document.createElement('input');
        inputAp.type = 'hidden';
        inputAp.name = 'arithmos_protokolou';
        inputAp.value = ap;
        form.appendChild(inputAp);

        document.body.appendChild(form);
        form.submit();
    } else {
        alert('Arithmos Protokolou is required.');
    }
}
