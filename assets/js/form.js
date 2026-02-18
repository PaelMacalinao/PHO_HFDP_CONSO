/**
 * PHO CONSO HFDP Form JavaScript
 */

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('record-form');
    form.addEventListener('submit', handleFormSubmit);
});

// Handle form submission
function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = {
        year: document.getElementById('year').value,
        cluster: document.getElementById('cluster').value,
        concerned_office_facility: document.getElementById('concerned_office_facility').value,
        facility_level: document.getElementById('facility_level').value,
        category: document.getElementById('category').value,
        type_of_health_facility: document.getElementById('type_of_health_facility').value,
        number_of_units: document.getElementById('number_of_units').value,
        facilities: document.getElementById('facilities').value,
        target: document.getElementById('target').value,
        costing: document.getElementById('costing').value,
        fund_source: document.getElementById('fund_source').value,
        presence_in_existing_plans: document.getElementById('presence_in_existing_plans').value,
        remarks: document.getElementById('remarks').value
    };
    
    // Validate required fields
    if (!formData.year || !formData.cluster || !formData.concerned_office_facility || 
        !formData.facility_level || !formData.category || !formData.fund_source) {
        showMessage('Please fill in all required fields.', 'error');
        return;
    }
    
    // Submit to API
    fetch('api/create_record.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Record created successfully!', 'success');
            document.getElementById('record-form').reset();
            
            // Redirect to dashboard after 2 seconds
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            showMessage('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error creating record. Please try again.', 'error');
    });
}

// Show message
function showMessage(message, type) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
    
    // Scroll to message
    messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
