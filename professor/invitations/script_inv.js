function updateApproval(inv_id, approval) {
    const confirmation = confirm(
        approval === 1 
        ? "Are you sure you want to approve this invitation?" 
        : "Are you sure you want to reject this invitation?"
    );
    if (!confirmation) return;

    // Debugging: Log payload being sent
    console.log(`Payload: inv_id=${inv_id}, approval=${approval}`);

    fetch('update_invitation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `inv_id=${encodeURIComponent(inv_id)}&approval=${encodeURIComponent(approval)}`,
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data); // Debugging: Log response
        if (data.success) {
            alert(data.message);
            // Optionally reload or update the table row
            document.querySelector(`#row-${inv_id}`).remove();
        } else {
            alert(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}
