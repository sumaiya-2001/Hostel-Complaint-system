// Hostel Complaint System - JavaScript
// Getting all HTML elements we need
const adminModal = document.getElementById('adminModal');
const complaintIdInput = document.getElementById('complaintId');
const checkBtn = document.getElementById('checkStatusBtn');
const adminBtn = document.getElementById('adminBtn');
const closeAdminModal = document.getElementById('closeAdminModal');
const adminLoginForm = document.getElementById('adminLoginForm');
const complaintForm = document.getElementById('complaintForm');
const statusResult = document.getElementById('statusResult');

// Admin Login Modal Functions
function openAdminLogin() {
    if (adminModal) {
        adminModal.style.display = 'block';
        // Put cursor in password box after showing modal
        setTimeout(() => {
            const adminPassword = document.getElementById('adminPassword');
            if (adminPassword) adminPassword.focus();
        }, 100);
    }
}

function closeAdminLogin() {
    if (adminModal) {
        adminModal.style.display = 'none';
    }
}

// Form Validation
function validateForm(event) {
    event.preventDefault(); // Stop form from submitting
    
    // Get form values
    const roomNumber = document.getElementById('room_number').value.trim();
    const regNumber = document.getElementById('reg_number').value.trim();
    const name = document.getElementById('name').value.trim();
    const description = document.getElementById('description').value.trim();
    const complaintType = document.getElementById('complaint_type').value;
    
    // Check room number format
    const roomPattern = /^[A-Za-z]?[-\s]?\d+[A-Za-z]?$/;
    if (!roomPattern.test(roomNumber)) {
        alert('Enter valid room number (e.g., A101, B-201)');
        return false;
    }
    
    // Check registration number format
    const regPattern = /^[A-Za-z0-9-\/]{6,20}$/;
    if (!regPattern.test(regNumber)) {
        alert('Enter valid registration number');
        return false;
    }
    
    // Check name length
    if (name.length < 3) {
        alert('Name must be at least 3 characters long');
        return false;
    }
    
    // Check description length
    if (description.length < 10) {
        alert('Please provide a more detailed description (minimum 10 characters)');
        return false;
    }
    
    // Check complaint type selected
    if (!complaintType) {
        alert('Please select a complaint type');
        return false;
    }
    
    // If all checks pass, submit the form
    complaintForm.submit();
    return true;
}

function validateAdminPassword(event) {
    const password = document.getElementById('adminPassword').value;
    if (password === '') {
        alert('Please enter password');
        event.preventDefault();
        return false;
    }
    return true;
}

// Status Check Functions
function checkStatus() {
    // Check if elements exist
    if (!complaintIdInput || !checkBtn || !statusResult) return;
    
    const complaintId = complaintIdInput.value.trim();
    
    // Check if ID is entered
    if (!complaintId) {
        showStatusMessage('Please enter a Complaint ID', 'error');
        return;
    }
    
    // Show loading on button
    const originalText = checkBtn.textContent;
    checkBtn.textContent = 'Checking...';
    checkBtn.disabled = true;
    
    // Clear previous result
    statusResult.innerHTML = '';
    
    // Send request to server
    const request = new XMLHttpRequest();
    request.open('POST', 'check_status.php', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    // When server responds
    request.onload = function() {
        // Reset button
        checkBtn.textContent = originalText;
        checkBtn.disabled = false;
        
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    displayStatusResult(response);
                    complaintIdInput.value = ''; // Clear input
                } else {
                    showStatusMessage('Complaint not found. Please check the ID.', 'error');
                }
            } catch (e) {
                console.error('Error:', e);
                showStatusMessage('Error checking status. Please try again.', 'error');
            }
        } else {
            showStatusMessage('Server error. Please try again later.', 'error');
        }
    };
    
    // If network error
    request.onerror = function() {
        checkBtn.textContent = originalText;
        checkBtn.disabled = false;
        showStatusMessage('Network error. Please check your connection.', 'error');
    };
    
    // Send the complaint ID to server
    request.send('complaint_id=' + encodeURIComponent(complaintId));
}

function displayStatusResult(data) {
    if (!statusResult) return;
    
    // Get CSS classes for styling
    const statusClass = getStatusClass(data.status);
    const priorityClass = getPriorityClass(data.priority);
    
    // Format date nicely
    const date = new Date(data.complaint_date);
    const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    
    // Create HTML to show result
    const resultHTML = `
        <div class="status-details">
            <div class="status-header">
                <h4>📋 Complaint Status</h4>
                <span class="status-badge ${statusClass}">${data.status}</span>
            </div>
            <div class="status-info">
                <p><strong>Complaint ID:</strong> ${data.complaint_id}</p>
                <p><strong>Name:</strong> ${data.name}</p>
                <p><strong>Room:</strong> ${data.hostel} - ${data.room_number}</p>
                <p><strong>Type:</strong> ${data.complaint_type}</p>
                <p><strong>Date:</strong> ${formattedDate}</p>
                <p><strong>Priority:</strong> <span class="${priorityClass}">${data.priority}</span></p>
            </div>
            <button onclick="clearStatusResult()" class="clear-status-btn">Clear</button>
        </div>
    `;
    
    // Show result on page
    statusResult.innerHTML = resultHTML;
    statusResult.style.display = 'block';
}

function showStatusMessage(message, type) {
    if (!statusResult) return;
    
    const messageHTML = `
        <div class="status-message ${type}">
            <p>${message}</p>
        </div>
    `;
    
    statusResult.innerHTML = messageHTML;
    statusResult.style.display = 'block';
    
    // Hide error messages after 5 seconds
    if (type === 'error') {
        setTimeout(() => {
            if (statusResult) {
                statusResult.style.display = 'none';
                statusResult.innerHTML = '';
            }
        }, 5000);
    }
}

function clearStatusResult() {
    if (statusResult) {
        statusResult.style.display = 'none';
        statusResult.innerHTML = '';
    }
}

// Helper functions to get CSS classes
function getStatusClass(status) {
    const statusLower = status.toLowerCase();
    if (statusLower === 'pending') return 'status-pending';
    if (statusLower === 'in progress') return 'status-in-progress';
    if (statusLower === 'completed') return 'status-completed';
    return 'status-pending';
}

function getPriorityClass(priority) {
    const priorityLower = priority.toLowerCase();
    if (priorityLower === 'low') return 'priority-low';
    if (priorityLower === 'medium') return 'priority-medium';
    if (priorityLower === 'high') return 'priority-high';
    if (priorityLower === 'urgent') return 'priority-urgent';
    return 'priority-medium';
}

// Set up all click events when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Admin panel button
    if (adminBtn) {
        adminBtn.addEventListener('click', openAdminLogin);
    }
    
    // Close modal button (X)
    if (closeAdminModal) {
        closeAdminModal.addEventListener('click', closeAdminLogin);
    }
    
    // Click outside modal to close
    window.addEventListener('click', function(event) {
        if (adminModal && event.target == adminModal) {
            closeAdminLogin();
        }
    });
    
    // Complaint form submit
    if (complaintForm) {
        complaintForm.addEventListener('submit', validateForm);
    }
    
    // Admin login form submit
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', validateAdminPassword);
    }
    
    // Check status button
    if (checkBtn) {
        checkBtn.addEventListener('click', checkStatus);
    }
    
    // Press Enter in complaint ID field
    if (complaintIdInput) {
        complaintIdInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                checkStatus();
            }
        });
    }
});