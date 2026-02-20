/**
 * PHO CONSO HFDP Dashboard JavaScript
 */

// Global variables
let currentFilters = {};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecords();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Filter buttons
    document.getElementById('btn-apply-filters').addEventListener('click', applyFilters);
    document.getElementById('btn-clear-filters').addEventListener('click', clearFilters);

    // Export to Excel (uses current filters)
    var exportBtn = document.getElementById('btn-export-excel');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportToExcel);
    }

    // Modal close
    const modal = document.getElementById('edit-modal');
    const closeBtn = document.querySelector('.close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Load records with current filters
function loadRecords() {
    const params = new URLSearchParams();
    
    Object.keys(currentFilters).forEach(key => {
        if (currentFilters[key]) {
            params.append(key, currentFilters[key]);
        }
    });

    fetch(`api/get_records.php?${params.toString()}`)
        .then(response => {
            if (response.status === 401) { window.location.href = 'login.php'; return; }
            return response.json();
        })
        .then(data => {
            if (!data) return;
            if (data.success) {
                displayRecords(data.data);
                document.getElementById('record-count').textContent = data.count;
            } else {
                console.error('Error loading records:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('records-tbody').innerHTML = 
                '<tr><td colspan="12" class="no-data">Error loading data. Please try again.</td></tr>';
        });
}

// Display records in table
function displayRecords(records) {
    const tbody = document.getElementById('records-tbody');
    
    if (records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" class="no-data">No records found.</td></tr>';
        return;
    }

    tbody.innerHTML = records.map(record => `
        <tr>
            <td>${record.year}</td>
            <td>${record.cluster}</td>
            <td>${record.concerned_office_facility || '-'}</td>
            <td>${record.municipality || '-'}</td>
            <td>${record.facility_level}</td>
            <td>${record.category}</td>
            <td>${record.type_of_health_facility || '-'}</td>
            <td>${record.number_of_units || 0}</td>
            <td>${formatCurrency(record.costing)}</td>
            <td>${record.fund_source}</td>
            <td>${record.presence_in_existing_plans || '-'}</td>
            <td>
                <button class="btn btn-edit" onclick="editRecord(${record.id})">Edit</button>
                <button class="btn btn-danger" onclick="deleteRecord(${record.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

// Format currency
function formatCurrency(amount) {
    if (!amount) return '₱0.00';
    return '₱' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Format costing for input field (with commas)
function formatCostingForInput(amount) {
    if (!amount) return '0.00';
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Strip commas and format for database
function formatCostingForDB(value) {
    return value.replace(/,/g, '');
}

// Update edit costing words display
function updateEditCostingWords() {
    const rawValue = this.value.replace(/,/g, '');
    const value = parseFloat(rawValue) || 0;
    const wordsElement = document.getElementById('edit-costing-words');
    if (wordsElement) {
        if (value > 0) {
            wordsElement.textContent = numberToWords(value) + ' Pesos';
        } else {
            wordsElement.textContent = '';
        }
    }
}

// Handle edit costing input formatting
function handleEditCostingInput(e) {
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
    const wordsElement = document.getElementById('edit-costing-words');
    if (wordsElement) {
        if (numericValue > 0) {
            wordsElement.textContent = numberToWords(numericValue) + ' Pesos';
        } else {
            wordsElement.textContent = '';
        }
    }
}

// Format edit costing on blur (ensure proper formatting)
function formatEditCostingOnBlur() {
    let value = this.value.trim();
    
    // If empty, set to 0.00
    if (!value) {
        this.value = '0.00';
        updateEditCostingWords.call(this);
        return;
    }
    
    // Parse and format the value
    const numericValue = parseFloat(value) || 0;
    this.value = numericValue.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    updateEditCostingWords.call(this);
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

// Apply filters
function applyFilters() {
    currentFilters = {
        year: document.getElementById('filter-year').value,
        cluster: document.getElementById('filter-cluster').value,
        facility_level: document.getElementById('filter-facility-level').value,
        category: document.getElementById('filter-category').value,
        type_of_health_facility: document.getElementById('filter-type-of-health-facility') ? document.getElementById('filter-type-of-health-facility').value : '',
        fund_source: document.getElementById('filter-fund-source').value,
        presence_plans: document.getElementById('filter-presence-plans').value
    };
    
    loadRecords();
}

// Export to Excel: build URL from current filters and trigger download
function exportToExcel() {
    var params = new URLSearchParams();
    Object.keys(currentFilters).forEach(function(key) {
        if (currentFilters[key]) {
            params.append(key, currentFilters[key]);
        }
    });
    window.location.href = 'api/export_excel.php?' + params.toString();
}

// Clear filters
function clearFilters() {
    document.getElementById('filter-year').value = '';
    document.getElementById('filter-cluster').value = '';
    document.getElementById('filter-facility-level').value = '';
    document.getElementById('filter-category').value = '';
    if (document.getElementById('filter-type-of-health-facility')) {
        document.getElementById('filter-type-of-health-facility').value = '';
    }
    document.getElementById('filter-fund-source').value = '';
    document.getElementById('filter-presence-plans').value = '';
    
    currentFilters = {};
    loadRecords();
}

// Edit record
function editRecord(id) {
    // Fetch record data
    fetch(`api/get_records.php?id=${id}`)
        .then(response => {
            if (response.status === 401) { window.location.href = 'login.php'; return; }
            return response.json();
        })
        .then(data => {
            if (!data) return;
            if (data.success && data.data.length > 0) {
                const record = data.data[0];
                showEditModal(record);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading record for editing.');
        });
}

// Show edit modal
function showEditModal(record) {
    const modal = document.getElementById('edit-modal');
    const container = document.getElementById('edit-form-container');
    
    container.innerHTML = `
        <form id="edit-form" class="data-form">
            <input type="hidden" id="edit-id" value="${record.id}">
            <div class="form-grid">
                <div class="form-group">
                    <label for="edit-year">Year <span class="required">*</span></label>
                    <select id="edit-year" required>
                        ${Array.from({length: 2100 - 2024 + 1}, (_, i) => {
                            const year = 2024 + i;
                            return `<option value="${year}" ${record.year == year ? 'selected' : ''}>${year}</option>`;
                        }).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-cluster">Cluster <span class="required">*</span></label>
                    <select id="edit-cluster" required>
                        <option value="REDCATS" ${record.cluster == 'REDCATS' ? 'selected' : ''}>REDCATS</option>
                        <option value="BCCL" ${record.cluster == 'BCCL' ? 'selected' : ''}>BCCL</option>
                        <option value="CAM" ${record.cluster == 'CAM' ? 'selected' : ''}>CAM</option>
                        <option value="NABBrRBEQ-K" ${record.cluster == 'NABBrRBEQ-K' ? 'selected' : ''}>NABBrRBEQ-K</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="edit-concerned_office_facility">Concerned Office / Facility <span class="required">*</span></label>
                    <select id="edit-concerned_office_facility" required>
                        ${generateConcernedOfficeOptions(record.concerned_office_facility)}
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-municipality">Municipality</label>
                    <input type="text" id="edit-municipality" value="${record.municipality || ''}">
                </div>
                <div class="form-group">
                    <label for="edit-facility_level">Facility Level <span class="required">*</span></label>
                    <select id="edit-facility_level" required>
                        <option value="BHS" ${record.facility_level == 'BHS' ? 'selected' : ''}>BHS</option>
                        <option value="PCF" ${record.facility_level == 'PCF' ? 'selected' : ''}>PCF</option>
                        <option value="HOSP" ${record.facility_level == 'HOSP' ? 'selected' : ''}>HOSP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-category">Category <span class="required">*</span></label>
                    <select id="edit-category" required>
                        <option value="INFRASTRUCTURE" ${record.category == 'INFRASTRUCTURE' ? 'selected' : ''}>INFRASTRUCTURE</option>
                        <option value="EQUIPMENT" ${record.category == 'EQUIPMENT' ? 'selected' : ''}>EQUIPMENT</option>
                        <option value="HUMAN RESOURCE" ${record.category == 'HUMAN RESOURCE' ? 'selected' : ''}>HUMAN RESOURCE</option>
                    </select>
                </div>
                <div class="form-group full-width" id="edit-type-facility-group">
                    <label for="edit-type_of_health_facility">Requested Item/Human Resource(HR) <span class="required">*</span></label>
                    <select id="edit-type_of_health_facility" required>
                        <option value="">Select Type</option>
                    </select>
                    <input type="text" id="edit-type_specify" class="specify-input" placeholder="Please specify..." style="display:none;margin-top:6px" oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="form-group">
                    <label for="edit-number_of_units">Number of Units</label>
                    <input type="number" id="edit-number_of_units" value="${record.number_of_units || 0}" min="0" required>
                </div>
                <div class="form-group">
                    <label for="edit-costing">Costing</label>
                    <input type="text" id="edit-costing" value="${record.costing || '0.00'}" required>
                    <div id="edit-costing-words" class="number-to-words"></div>
                </div>
                <div class="form-group full-width">
                    <label for="edit-fund_source">Fund Source <span class="required">*</span></label>
                    <select id="edit-fund_source" required>
                        <option value="PLGU" ${record.fund_source == 'PLGU' ? 'selected' : ''}>PLGU</option>
                        <option value="MLGU" ${record.fund_source == 'MLGU' ? 'selected' : ''}>MLGU</option>
                        <option value="DOH" ${record.fund_source == 'DOH' ? 'selected' : ''}>DOH</option>
                        <option value="DPWH" ${record.fund_source == 'DPWH' ? 'selected' : ''}>DPWH</option>
                        <option value="NGO" ${record.fund_source == 'NGO' ? 'selected' : ''}>NGO</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-presence_in_existing_plans">Presence in Existing Plans</label>
                    <select id="edit-presence_in_existing_plans" required>
                        <option value="">Select Option</option>
                        <option value="LIPH" ${record.presence_in_existing_plans == 'LIPH' ? 'selected' : ''}>LIPH</option>
                        <option value="LIDP" ${record.presence_in_existing_plans == 'LIDP' ? 'selected' : ''}>LIDP</option>
                        <option value="AIP" ${record.presence_in_existing_plans == 'AIP' ? 'selected' : ''}>AIP</option>
                        <option value="CDP" ${record.presence_in_existing_plans == 'CDP' ? 'selected' : ''}>CDP</option>
                        <option value="DTP" ${record.presence_in_existing_plans == 'DTP' ? 'selected' : ''}>DTP</option>
                        <option value="AOP" ${record.presence_in_existing_plans == 'AOP' ? 'selected' : ''}>AOP</option>
                        <option value="NONE" ${record.presence_in_existing_plans == 'NONE' ? 'selected' : ''}>NONE</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Record</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-modal').style.display='none'">Cancel</button>
            </div>
        </form>
    `;
    
    // ── Cascading: Category → Type of Health Facility ──
    const EDIT_CATEGORY_TYPE_MAP = {
        'INFRASTRUCTURE': ['SPECIFY WHAT TO CONSTRUCT'],
        'EQUIPMENT':      ['MEDICAL EQUIPMENT','LABORATORY EQUIPMENT','OTHER EQUIPMENT'],
        'HUMAN RESOURCE': ['MEDICAL OFFICER','MEDICAL SPECIALIST','NURSE',
                           'NURSING ATTENDANT','MIDWIFE','RADIOLOGIC TECHNOLOGIST',
                           'PHARMACIST','DENTIST','DENTAL AIDE','MEDICAL TECHNOLOGIST',
                           'MEDICAL LABORATORY TECHNICIAN','LABORATORY ASSISTANT',
                           'ADMINISTRATIVE OFFICER','ADMINISTRATIVE ASSISTANT',
                           'ADMINISTRATIVE AIDE (UTILITY)','CIVIL ENGINEER',
                           'SANITARY ENGINEER','SANITATION INSPECTOR',
                           'NUTRITIONIST-DIETITIAN',
                           'HEALTH EDUCATION & PROMOTIONS OFFICER',
                           'STATISTICIAN','OTHERS']
    };
    const EDIT_SPECIFY_TRIGGERS = ['SPECIFY WHAT TO CONSTRUCT','MEDICAL EQUIPMENT','LABORATORY EQUIPMENT','OTHER EQUIPMENT','OTHERS'];

    function editPopulateTypeDropdown(categoryVal, preselectVal) {
        const sel   = document.getElementById('edit-type_of_health_facility');
        const spec  = document.getElementById('edit-type_specify');
        sel.innerHTML = '';
        spec.style.display = 'none';
        spec.value = '';

        const items = EDIT_CATEGORY_TYPE_MAP[categoryVal];
        if (!items) {
            sel.innerHTML = '<option value="">— Select a Category first —</option>';
            sel.disabled = true;
            return;
        }
        if (items.length === 0) {
            sel.innerHTML = '<option value="">N/A for this category</option>';
            sel.disabled = true;
            return;
        }
        sel.disabled = false;
        sel.innerHTML = '<option value="">Select Type</option>';
        items.forEach(function(t) {
            const o = document.createElement('option');
            o.value = t;
            o.textContent = t;
            sel.appendChild(o);
        });

        // Check if preselect value is in the list directly
        if (preselectVal) {
            let found = false;
            for (let i = 0; i < sel.options.length; i++) {
                if (sel.options[i].value === preselectVal) {
                    sel.options[i].selected = true;
                    found = true;
                    break;
                }
            }
            // If not found, it's a custom "specify" value — find best trigger
            if (!found) {
                for (let i = 0; i < EDIT_SPECIFY_TRIGGERS.length; i++) {
                    for (let j = 0; j < sel.options.length; j++) {
                        if (sel.options[j].value === EDIT_SPECIFY_TRIGGERS[i]) {
                            sel.options[j].selected = true;
                            break;
                        }
                    }
                }
                // Show specify field with the custom value
                spec.style.display = 'block';
                spec.value = preselectVal;
            }
        }
        // Toggle specify if a trigger is selected
        editToggleSpecifyInput();
        if (typeof refreshSearchableDropdown === 'function') {
            refreshSearchableDropdown('edit-type_of_health_facility');
        }
    }

    function editToggleSpecifyInput() {
        const sel  = document.getElementById('edit-type_of_health_facility');
        const spec = document.getElementById('edit-type_specify');
        if (EDIT_SPECIFY_TRIGGERS.includes(sel.value)) {
            spec.style.display = 'block';
            spec.required = true;
        } else {
            spec.style.display = 'none';
            spec.required = false;
            spec.value = '';
        }
    }

    // Bind category change
    document.getElementById('edit-category').addEventListener('change', function() {
        editPopulateTypeDropdown(this.value, '');
    });

    // Bind type change for specify toggle
    document.getElementById('edit-type_of_health_facility').addEventListener('change', function() {
        editToggleSpecifyInput();
    });

    // Initialize with existing record values
    editPopulateTypeDropdown(record.category, record.type_of_health_facility);

    // Add submit handler
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        updateRecord();
    });
    
    // Initialize number-to-words converter for edit costing field
    const editCostingInput = document.getElementById('edit-costing');
    if (editCostingInput) {
        editCostingInput.addEventListener('input', handleEditCostingInput);
        editCostingInput.addEventListener('blur', formatEditCostingOnBlur);
        // Initialize with current value - don't format immediately to allow natural typing
        updateEditCostingWords.call(editCostingInput);
    }
    
    modal.style.display = 'block';
}

// Update record
function updateRecord() {
    const data = {
        id: document.getElementById('edit-id').value,
        year: document.getElementById('edit-year').value,
        cluster: document.getElementById('edit-cluster').value,
        concerned_office_facility: document.getElementById('edit-concerned_office_facility').value,
        municipality: document.getElementById('edit-municipality').value,
        facility_level: document.getElementById('edit-facility_level').value,
        category: document.getElementById('edit-category').value,
        type_of_health_facility: (function() {
            var specEl = document.getElementById('edit-type_specify');
            if (specEl && specEl.style.display !== 'none' && specEl.value.trim()) {
                return specEl.value.trim().toUpperCase();
            }
            return document.getElementById('edit-type_of_health_facility').value;
        })(),
        number_of_units: document.getElementById('edit-number_of_units').value,
        facilities: document.getElementById('edit-concerned_office_facility').value,
        costing: formatCostingForDB(document.getElementById('edit-costing').value),
        fund_source: document.getElementById('edit-fund_source').value,
        presence_in_existing_plans: document.getElementById('edit-presence_in_existing_plans').value
    };
    
    fetch('api/update_record.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (response.status === 401) { window.location.href = 'login.php'; return; }
        return response.json();
    })
    .then(data => {
        if (!data) return;
        if (data.success) {
            alert('Record updated successfully!');
            document.getElementById('edit-modal').style.display = 'none';
            loadRecords();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating record.');
    });
}

// Delete record
function deleteRecord(id) {
    if (!confirm('Are you sure you want to delete this record?')) {
        return;
    }
    
    fetch('api/delete_record.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => {
        if (response.status === 401) { window.location.href = 'login.php'; return; }
        return response.json();
    })
    .then(data => {
        if (!data) return;
        if (data.success) {
            alert('Record deleted successfully!');
            loadRecords();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting record.');
    });
}

// Generate Concerned Office dropdown options
function generateConcernedOfficeOptions(selectedValue) {
    const options = [
        'ABORLAN MEDICARE HOSPITAL',
        'ABORLAN MUNICIPAL HEALTH OFFICE',
        'ARACELI-DUMARAN DISTRICT HOSPITAL',
        'BALABAC DISTRICT HOSPITAL',
        'BALABAC MUNICIPAL HEALTH OFFICE',
        'BATARAZA DISTRICT HOSPITAL',
        'BATARAZA MUNICIPAL HEALTH OFFICE',
        'BATARAZA MUNICIPAL HOSPITAL',
        'BROOKES POINT MUNICIPAL HEALTH OFFICE',
        'BROOKES POINT MUNICIPAL HOSPITAL',
        'BUSUANGA HEALTH OFFICE',
        'CORON DISTRICT HOSPITAL',
        'CORON MUNICIPAL HEALTH OFFICE',
        'CULION MUNICIPAL HEALTH OFFICE',
        'CUYO DISTRICT HOSPITAL',
        'DR JOSE RIZAL DISTRICT HOSPITAL',
        'EL NIDO COMMUNITY HOSPITAL',
        'KALAYAAN MUNICIPAL HEALTH OFFICE',
        'LINAPACAN MUNICIPAL HEALTH OFFICE',
        'MUNICIPALITY OF AGUTAYA',
        'MUNICIPALITY OF ARACELI',
        'MUNICIPALITY OF CAGAYANCILLO',
        'MUNICIPALITY OF DUMARAN',
        'MUNICIPALITY OF EL NIDO',
        'MUNICIPALITY OF MAGSAYSAY',
        'MUNICIPALITY OF ROXAS',
        'MUNICIPALITY OF SAN VICENTE',
        'MUNICIPALITY OF TAYTAY',
        'NARRA MUNICIPAL HEALTH OFFICE',
        'NARRA MUNICIPAL HOSPITAL',
        'NORTHERN PALAWAN PROVINCIAL HOSPITAL',
        'QUEZON MEDICARE HOSPITAL',
        'QUEZON MUNICIPAL HEALTH OFFICE',
        'RIZAL DISTRICT HOSPITAL',
        'RIZAL MUNICIPAL HEALTH OFFICE',
        'ROXAS MEDICARE HOSPITAL',
        'SAN VICENTE DISTRICT HOSPITAL',
        'SOFRONIO ESPAÑOLA DISTRICT HOSPITAL',
        'SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE',
        'SOUTHERN PALAWAN PROVINCIAL HOSPITAL'
    ];
    
    let html = '<option value="">Select Concerned Office</option>';
    options.forEach(option => {
        const selected = (selectedValue && selectedValue === option) ? 'selected' : '';
        html += `<option value="${escapeHtml(option)}" ${selected}>${escapeHtml(option)}</option>`;
    });
    return html;
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
