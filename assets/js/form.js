/**
 * PHO CONSO HFDP Form JavaScript
 */

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('record-form');
    form.addEventListener('submit', handleFormSubmit);
    
    // Initialize number-to-words converter for costing field
    const costingInput = document.getElementById('costing');
    if (costingInput) {
        costingInput.addEventListener('input', handleCostingInput);
        costingInput.addEventListener('blur', formatCostingOnBlur);
        // Initialize with current value - don't format immediately to allow natural typing
        updateCostingWords.call(costingInput);
    }
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
        costing: formatCostingForDB(document.getElementById('costing').value),
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
        if (response.status === 401) { window.location.href = 'login.php'; return; }
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
        if (!data) { submitBtn.disabled = false; return; }
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

// Update costing words display
function updateCostingWords() {
    const rawValue = this.value.replace(/,/g, '');
    const value = parseFloat(rawValue) || 0;
    const wordsElement = document.getElementById('costing-words');
    if (wordsElement) {
        if (value > 0) {
            wordsElement.textContent = numberToWords(value) + ' Pesos';
        } else {
            wordsElement.textContent = '';
        }
    }
}

// Handle costing input formatting
function handleCostingInput(e) {
    // Allow free typing - just validate and prevent invalid characters
    let value = e.target.value;
    
    // Remove any non-numeric characters except decimal point
    value = value.replace(/[^0-9.]/g, '');
    
    // Prevent multiple decimal points
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit to 2 decimal places
    if (parts[1] && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    // Update the value if it changed
    if (e.target.value !== value) {
        e.target.value = value;
    }
    
    // Update words with the raw numeric value
    const rawValue = value.replace(/,/g, '');
    const numericValue = parseFloat(rawValue) || 0;
    const wordsElement = document.getElementById('costing-words');
    if (wordsElement) {
        if (numericValue > 0) {
            wordsElement.textContent = numberToWords(numericValue) + ' Pesos';
        } else {
            wordsElement.textContent = '';
        }
    }
}

// Format costing on blur (ensure proper formatting)
function formatCostingOnBlur() {
    let value = this.value.trim();
    
    // If empty, set to 0.00
    if (!value) {
        this.value = '0.00';
        updateCostingWords.call(this);
        return;
    }
    
    // Parse and format the value
    const numericValue = parseFloat(value) || 0;
    this.value = numericValue.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    updateCostingWords.call(this);
}

// Strip commas and format for database
function formatCostingForDB(value) {
    return value.replace(/,/g, '');
}

// Convert number to words
function numberToWords(num) {
    if (num === 0) return 'Zero';
    
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
    const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    const scales = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];
    
    function convertHundreds(n) {
        let str = '';
        if (n >= 100) {
            str += ones[Math.floor(n / 100)] + ' Hundred ';
            n %= 100;
        }
        if (n >= 20) {
            str += tens[Math.floor(n / 10)] + ' ';
            n %= 10;
        } else if (n >= 10) {
            str += teens[n - 10] + ' ';
            return str.trim();
        }
        if (n > 0) {
            str += ones[n] + ' ';
        }
        return str.trim();
    }
    
    let result = '';
    let scaleIndex = 0;
    
    while (num > 0) {
        const chunk = num % 1000;
        if (chunk > 0) {
            const chunkWords = convertHundreds(chunk);
            result = chunkWords + ' ' + scales[scaleIndex] + ' ' + result;
        }
        num = Math.floor(num / 1000);
        scaleIndex++;
    }
    
    return result.trim();
}
