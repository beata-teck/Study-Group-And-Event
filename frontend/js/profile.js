// frontend/js/profile.js
document.addEventListener('DOMContentLoaded', async () => {
    if (!currentUser) {
        window.location.href = 'login.html';
        return;
    }

    const form = document.getElementById('profileForm');
    const deleteBtn = document.getElementById('deleteAccountBtn');

    // Load initial data
    try {
        const response = await fetch(`${API_BASE}/profile.php`, {
            headers: { 'x-user-id': currentUser.id }
        });
        const data = await response.json();

        if (data.success) {
            const user = data.data;
            document.getElementById('name').value = user.name || '';
            document.getElementById('department').value = user.department || '';
            document.getElementById('yearOfStudy').value = user.year_of_study || '';
            document.getElementById('bio').value = user.bio || '';
            document.getElementById('interests').value = user.interests || '';
        }
    } catch (error) {
        console.error('Error loading profile:', error);
    }

    // Handle Save
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const updatedData = {
            name: document.getElementById('name').value,
            department: document.getElementById('department').value,
            year_of_study: document.getElementById('yearOfStudy').value,
            bio: document.getElementById('bio').value,
            interests: document.getElementById('interests').value
        };

        try {
            const response = await fetch(`${API_BASE}/profile.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'x-user-id': currentUser.id
                },
                body: JSON.stringify(updatedData)
            });
            const data = await response.json();

            if (data.success) {
                alert('Profile updated successfully!');
                // Update local storage
                localStorage.setItem('user', JSON.stringify(data.data));
                currentUser = data.data;
                updateNavForUser(); // Refresh nav name if changed
            } else {
                alert('Failed to update: ' + data.message);
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            alert('An error occurred.');
        }
    });

    // Handle Delete
    deleteBtn.addEventListener('click', async () => {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            try {
                const response = await fetch(`${API_BASE}/profile.php`, {
                    method: 'DELETE',
                    headers: { 'x-user-id': currentUser.id }
                });
                const data = await response.json();

                if (data.success) {
                    alert('Account deleted.');
                    logout();
                } else {
                    alert('Failed to delete: ' + data.message);
                }
            } catch (error) {
                console.error('Error deleting account:', error);
                alert('An error occurred.');
            }
        }
    });
});
