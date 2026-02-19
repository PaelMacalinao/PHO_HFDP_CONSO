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

    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn.disabled) return;
    submitBtn.disabled = true;

    const formData = {
        year: document.getElementById('year').value,
        cluster: document.getElementById('cluster').value,
        concerned_office_facility: document.getElementById('concerned_office_facility').value,
        facility_level: document.getElementById('facility_level').value,
        category: document.getElementById('category').value,
        type_of_health_facility: document.getElementById('type_of_health_facility').value,
        number_of_units: document.getElementById('number_of_units').value,
        target: document.getElementById('target').value,
        costing: document.getElementById('costing').value,
        fund_source: document.getElementById('fund_source').value,
        presence_in_existing_plans: document.getElementById('presence_in_existing_plans').value,
        remarks: document.getElementById('remarks').value
    };
    
    // Validate required fields
    if (
        !formData.year ||
        !formData.cluster ||
        !formData.concerned_office_facility ||
        !formData.facility_level ||
        !formData.category ||
        !formData.type_of_health_facility ||
        formData.number_of_units === '' ||
        !formData.target ||
        formData.costing === '' ||
        !formData.fund_source ||
        !formData.presence_in_existing_plans
    ) {
        showMessage('Please fill in all required fields.', 'error');
        submitBtn.disabled = false;
        return;
    }

    // Submit to API (path relative to current page, e.g. /PHO_HFDP_CONSO/api/create_record.php)
    const apiUrl = new URL('api/create_record.php', window.location.href).href;
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        return response.text().then(function(text) {
            const contentType = response.headers.get('content-type');
            if (!response.ok) {
                throw new Error('Server returned ' + response.status + (response.status === 404 ? '. Check that you open the app via http://localhost/... (not file://).' : ''));
            }
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(text || 'Server returned non-JSON response');
            }
            try {
                return JSON.parse(text);
            } catch (_) {
                throw new Error(text || 'Invalid server response');
            }
        });
    })
    .then(data => {
        if (data.success) {
            showMessage('Record created successfully!', 'success');
            document.getElementById('record-form').reset();
            
            // Redirect to dashboard after 2 seconds
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            showMessage('Error: ' + (data.message || 'Unknown error'), 'error');
        }
        submitBtn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error: ' + (error.message || 'Please try again.'), 'error');
        submitBtn.disabled = false;
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
