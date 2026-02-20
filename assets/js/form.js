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

    /**
     * Helper: resolve the effective value of a field.
     */
    function resolveFieldValue(id) {
        var el = document.getElementById(id);
        if (!el) return '';
        var val = el.value;
        if (val && val !== '__OTHER__') return val;
        var wrap = el.parentElement && el.parentElement.querySelector('.sd-wrap');
        if (!wrap) return val;
        var otherInput = wrap.querySelector('.sd-other-input');
        if (otherInput && otherInput.offsetParent !== null && otherInput.value.trim() !== '') {
            return otherInput.value.trim();
        }
        return val;
    }

    /* ---- Collect header (shared) fields ---- */
    var headerData = {
        year: resolveFieldValue('year'),
        cluster: resolveFieldValue('cluster'),
        concerned_office_facility: resolveFieldValue('concerned_office_facility'),
        municipality: resolveFieldValue('municipality'),
        facility_level: resolveFieldValue('facility_level'),
        presence_in_existing_plans: resolveFieldValue('presence_in_existing_plans')
    };

    // Validate header fields
    if (!headerData.year || !headerData.cluster || !headerData.concerned_office_facility ||
        !headerData.facility_level || !headerData.presence_in_existing_plans) {
        showMessage('Please fill in all required header fields.', 'error');
        submitBtn.disabled = false;
        return;
    }

    /* ---- Collect repeater items ---- */
    var rows = document.querySelectorAll('#repeater-container .repeater-row');
    var items = [];
    var itemError = null;

    rows.forEach(function(row, i) {
        var catSel   = row.querySelector('.r-category');
        var typSel   = row.querySelector('.r-type');
        var specInp  = row.querySelector('.r-specify');
        var unitsInp = row.querySelector('.r-units');
        var costInp  = row.querySelector('.r-costing');
        var fundSel  = row.querySelector('.r-fund');

        var category = catSel ? catSel.value : '';
        var typeVal  = '';
        if (specInp && specInp.style.display !== 'none' && specInp.value.trim()) {
            typeVal = specInp.value.trim().toUpperCase();
        } else if (typSel) {
            typeVal = typSel.value;
        }
        var units    = unitsInp ? unitsInp.value : '0';
        var costing  = costInp ? formatCostingForDB(costInp.value) : '0';
        var fund     = fundSel ? fundSel.value : '';

        if (!category || !typeVal || !fund) {
            itemError = 'Please fill in all required fields in Item #' + (i + 1) + '.';
        }

        items.push({
            category: category,
            type_of_health_facility: typeVal,
            number_of_units: units,
            costing: costing,
            fund_source: fund
        });
    });

    if (itemError) { showMessage(itemError, 'error'); submitBtn.disabled = false; return; }
    if (items.length === 0) { showMessage('Please add at least one item.', 'error'); submitBtn.disabled = false; return; }

    /* ---- Build payload ---- */
    var payload = {
        year: headerData.year,
        cluster: headerData.cluster,
        concerned_office_facility: headerData.concerned_office_facility,
        municipality: headerData.municipality,
        facility_level: headerData.facility_level,
        presence_in_existing_plans: headerData.presence_in_existing_plans,
        items: items
    };

    // Submit to API
    const apiUrl = new URL('api/create_record.php', window.location.href).href;
    fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
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
            try { return JSON.parse(text); } catch (_) { throw new Error(text || 'Invalid server response'); }
        });
    })
    .then(data => {
        if (!data) { submitBtn.disabled = false; return; }
        if (data.success) {
            showMessage(data.message || 'Record(s) created successfully!', 'success');
            document.getElementById('record-form').reset();

            // Remove extra repeater rows, keep only the first
            var allRows = document.querySelectorAll('#repeater-container .repeater-row');
            for (var i = allRows.length - 1; i > 0; i--) allRows[i].remove();

            // Reset first row type dropdown
            var firstRow = document.querySelector('#repeater-container .repeater-row');
            if (firstRow) {
                var tSel = firstRow.querySelector('.r-type');
                if (tSel) { tSel.innerHTML = '<option value="">\u2014 Select Category first \u2014</option>'; tSel.disabled = true; }
                var sp = firstRow.querySelector('.r-specify');
                if (sp) { sp.style.display = 'none'; sp.required = false; sp.value = ''; }
                firstRow.querySelectorAll('.r-costing-words').forEach(function(w) { w.textContent = ''; });
            }
            if (typeof updateRepeaterUI === 'function') updateRepeaterUI();
            if (typeof updatePreview === 'function') updatePreview();

            setTimeout(() => { window.location.href = 'index.php'; }, 2000);
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
