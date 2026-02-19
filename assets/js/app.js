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
                '<tr><td colspan="13" class="no-data">Error loading data. Please try again.</td></tr>';
        });
}

// Display records in table
function displayRecords(records) {
    const tbody = document.getElementById('records-tbody');
    
    if (records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="13" class="no-data">No records found.</td></tr>';
        return;
    }

    tbody.innerHTML = records.map(record => `
        <tr>
            <td>${record.year}</td>
            <td>${record.cluster}</td>
            <td>${record.concerned_office_facility || '-'}</td>
            <td>${record.facility_level}</td>
            <td>${record.category}</td>
            <td>${record.type_of_health_facility || '-'}</td>
            <td>${record.number_of_units || 0}</td>
            <td>${record.target || '-'}</td>
            <td>${formatCurrency(record.costing)}</td>
            <td>${record.fund_source}</td>
            <td>${record.presence_in_existing_plans || '-'}</td>
            <td>${record.remarks ? (record.remarks.length > 30 ? record.remarks.substring(0, 30) + '...' : record.remarks) : '-'}</td>
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
        presence_plans: document.getElementById('filter-presence-plans').value,
        target: document.getElementById('filter-target') ? document.getElementById('filter-target').value : ''
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
    if (document.getElementById('filter-target')) {
        document.getElementById('filter-target').value = '';
    }
    
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
                        <option value="TRANSPORTATION" ${record.category == 'TRANSPORTATION' ? 'selected' : ''}>TRANSPORTATION</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="edit-type_of_health_facility">Type of Health Facility</label>
                    <select id="edit-type_of_health_facility" required>
                        <option value="">Select Type (optional)</option>
                        <optgroup label="A–C">
                            <option value="ADMINISTRATIVE AIDE I" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE I' ? 'selected' : ''}>ADMINISTRATIVE AIDE I</option>
                            <option value="(ADMINISTRATIVE AIDE I)" ${record.type_of_health_facility === '(ADMINISTRATIVE AIDE I)' ? 'selected' : ''}>(ADMINISTRATIVE AIDE I)</option>
                            <option value="ACCOUNTANT I" ${record.type_of_health_facility === 'ACCOUNTANT I' ? 'selected' : ''}>ACCOUNTANT I</option>
                            <option value="ADMIN AIDE I" ${record.type_of_health_facility === 'ADMIN AIDE I' ? 'selected' : ''}>ADMIN AIDE I</option>
                            <option value="ADMIN ASSISTANT" ${record.type_of_health_facility === 'ADMIN ASSISTANT' ? 'selected' : ''}>ADMIN ASSISTANT</option>
                            <option value="ADMIN ASSISTANT II" ${record.type_of_health_facility === 'ADMIN ASSISTANT II' ? 'selected' : ''}>ADMIN ASSISTANT II</option>
                            <option value="ADMIN OFFICER" ${record.type_of_health_facility === 'ADMIN OFFICER' ? 'selected' : ''}>ADMIN OFFICER</option>
                            <option value="ADMIN OFFICER IV" ${record.type_of_health_facility === 'ADMIN OFFICER IV' ? 'selected' : ''}>ADMIN OFFICER IV</option>
                            <option value="ADMIN OFFICER V" ${record.type_of_health_facility === 'ADMIN OFFICER V' ? 'selected' : ''}>ADMIN OFFICER V</option>
                            <option value="ADMINISTRATIVE & TECHNICAL" ${record.type_of_health_facility === 'ADMINISTRATIVE & TECHNICAL' ? 'selected' : ''}>ADMINISTRATIVE & TECHNICAL</option>
                            <option value="ADMINISTRATIVE AIDE" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE' ? 'selected' : ''}>ADMINISTRATIVE AIDE</option>
                            <option value="ADMINISTRATIVE AIDE I (DRIVER I)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE I (DRIVER I)' ? 'selected' : ''}>ADMINISTRATIVE AIDE I (DRIVER I)</option>
                            <option value="ADMINISTRATIVE AIDE I (FOOD SERVICE WORKER)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE I (FOOD SERVICE WORKER)' ? 'selected' : ''}>ADMINISTRATIVE AIDE I (FOOD SERVICE WORKER)</option>
                            <option value="ADMINISTRATIVE AIDE I (MEDICAL RECORDS CLERK)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE I (MEDICAL RECORDS CLERK)' ? 'selected' : ''}>ADMINISTRATIVE AIDE I (MEDICAL RECORDS CLERK)</option>
                            <option value="ADMINISTRATIVE AIDE I (PHARMACY AIDE)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE I (PHARMACY AIDE)' ? 'selected' : ''}>ADMINISTRATIVE AIDE I (PHARMACY AIDE)</option>
                            <option value="ADMINISTRATIVE AIDE I (UTILITY)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE I (UTILITY)' ? 'selected' : ''}>ADMINISTRATIVE AIDE I (UTILITY)</option>
                            <option value="ADMINISTRATIVE AIDE I (WATCHMAN)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE I (WATCHMAN)' ? 'selected' : ''}>ADMINISTRATIVE AIDE I (WATCHMAN)</option>
                            <option value="ADMINISTRATIVE AIDE II" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE II' ? 'selected' : ''}>ADMINISTRATIVE AIDE II</option>
                            <option value="ADMINISTRATIVE AIDE II (BILLING)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE II (BILLING)' ? 'selected' : ''}>ADMINISTRATIVE AIDE II (BILLING)</option>
                            <option value="ADMINISTRATIVE AIDE II (CASH CLERK)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE II (CASH CLERK)' ? 'selected' : ''}>ADMINISTRATIVE AIDE II (CASH CLERK)</option>
                            <option value="ADMINISTRATIVE AIDE II (CLAIMS)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE II (CLAIMS)' ? 'selected' : ''}>ADMINISTRATIVE AIDE II (CLAIMS)</option>
                            <option value="ADMINISTRATIVE AIDE II (HEALTH" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE II (HEALTH' ? 'selected' : ''}>ADMINISTRATIVE AIDE II (HEALTH</option>
                            <option value="ADMINISTRATIVE AIDE II (SUPPLY AIDE)" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE II (SUPPLY AIDE)' ? 'selected' : ''}>ADMINISTRATIVE AIDE II (SUPPLY AIDE)</option>
                            <option value="ADMINISTRATIVE AIDE IV" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE IV' ? 'selected' : ''}>ADMINISTRATIVE AIDE IV</option>
                            <option value="ADMINISTRATIVE AIDE VI" ${record.type_of_health_facility === 'ADMINISTRATIVE AIDE VI' ? 'selected' : ''}>ADMINISTRATIVE AIDE VI</option>
                            <option value="ADMINISTRATIVE ASSISTANT I" ${record.type_of_health_facility === 'ADMINISTRATIVE ASSISTANT I' ? 'selected' : ''}>ADMINISTRATIVE ASSISTANT I</option>
                            <option value="ADMINISTRATIVE ASSISTANT II" ${record.type_of_health_facility === 'ADMINISTRATIVE ASSISTANT II' ? 'selected' : ''}>ADMINISTRATIVE ASSISTANT II</option>
                            <option value="ADMINISTRATIVE ASSISTANT III" ${record.type_of_health_facility === 'ADMINISTRATIVE ASSISTANT III' ? 'selected' : ''}>ADMINISTRATIVE ASSISTANT III</option>
                            <option value="ADMINISTRATIVE OFFICER II" ${record.type_of_health_facility === 'ADMINISTRATIVE OFFICER II' ? 'selected' : ''}>ADMINISTRATIVE OFFICER II</option>
                            <option value="ADMINISTRATIVE OFFICER III" ${record.type_of_health_facility === 'ADMINISTRATIVE OFFICER III' ? 'selected' : ''}>ADMINISTRATIVE OFFICER III</option>
                            <option value="ADMINISTRATIVE OFFICER III (HUMAN RESOURCE OFFICER)" ${record.type_of_health_facility === 'ADMINISTRATIVE OFFICER III (HUMAN RESOURCE OFFICER)' ? 'selected' : ''}>ADMINISTRATIVE OFFICER III (HUMAN RESOURCE OFFICER)</option>
                            <option value="ADMINISTRATIVE OFFICER IV" ${record.type_of_health_facility === 'ADMINISTRATIVE OFFICER IV' ? 'selected' : ''}>ADMINISTRATIVE OFFICER IV</option>
                            <option value="ADMINISTRATIVE OFFICER V" ${record.type_of_health_facility === 'ADMINISTRATIVE OFFICER V' ? 'selected' : ''}>ADMINISTRATIVE OFFICER V</option>
                            <option value="ADMINISTRATIVE STAFF" ${record.type_of_health_facility === 'ADMINISTRATIVE STAFF' ? 'selected' : ''}>ADMINISTRATIVE STAFF</option>
                            <option value="ADMINITRATIVE ASSISTANT II" ${record.type_of_health_facility === 'ADMINITRATIVE ASSISTANT II' ? 'selected' : ''}>ADMINITRATIVE ASSISTANT II</option>
                            <option value="ADMINSTRATIVE OFFICER" ${record.type_of_health_facility === 'ADMINSTRATIVE OFFICER' ? 'selected' : ''}>ADMINSTRATIVE OFFICER</option>
                            <option value="AMBULANCE/PTV DRIVER" ${record.type_of_health_facility === 'AMBULANCE/PTV DRIVER' ? 'selected' : ''}>AMBULANCE/PTV DRIVER</option>
                            <option value="ASSISTANT NUTRITIONIST" ${record.type_of_health_facility === 'ASSISTANT NUTRITIONIST' ? 'selected' : ''}>ASSISTANT NUTRITIONIST</option>
                            <option value="ASST MHO" ${record.type_of_health_facility === 'ASST MHO' ? 'selected' : ''}>ASST MHO</option>
                            <option value="BILLING CLERK" ${record.type_of_health_facility === 'BILLING CLERK' ? 'selected' : ''}>BILLING CLERK</option>
                            <option value="BOATSWAIN" ${record.type_of_health_facility === 'BOATSWAIN' ? 'selected' : ''}>BOATSWAIN</option>
                            <option value="CASH CLERK" ${record.type_of_health_facility === 'CASH CLERK' ? 'selected' : ''}>CASH CLERK</option>
                            <option value="CASHIER" ${record.type_of_health_facility === 'CASHIER' ? 'selected' : ''}>CASHIER</option>
                            <option value="CLAIMS PROCESSOR" ${record.type_of_health_facility === 'CLAIMS PROCESSOR' ? 'selected' : ''}>CLAIMS PROCESSOR</option>
                            <option value="CLERK" ${record.type_of_health_facility === 'CLERK' ? 'selected' : ''}>CLERK</option>
                            <option value="CLERK/UTILITY" ${record.type_of_health_facility === 'CLERK/UTILITY' ? 'selected' : ''}>CLERK/UTILITY</option>
                            <option value="COMPUTER FILE LIBRARIAN" ${record.type_of_health_facility === 'COMPUTER FILE LIBRARIAN' ? 'selected' : ''}>COMPUTER FILE LIBRARIAN</option>
                            <option value="COMPUTER MAINTENANCE TECHNICIAN I" ${record.type_of_health_facility === 'COMPUTER MAINTENANCE TECHNICIAN I' ? 'selected' : ''}>COMPUTER MAINTENANCE TECHNICIAN I</option>
                            <option value="COMPUTER MAINTENANCE TECHNOLOGIST" ${record.type_of_health_facility === 'COMPUTER MAINTENANCE TECHNOLOGIST' ? 'selected' : ''}>COMPUTER MAINTENANCE TECHNOLOGIST</option>
                            <option value="COMPUTER OPERATOR I" ${record.type_of_health_facility === 'COMPUTER OPERATOR I' ? 'selected' : ''}>COMPUTER OPERATOR I</option>
                            <option value="CONSTRCUTION OF PCF" ${record.type_of_health_facility === 'CONSTRCUTION OF PCF' ? 'selected' : ''}>CONSTRCUTION OF PCF</option>
                            <option value="CONSTRUCTION OF BHS" ${record.type_of_health_facility === 'CONSTRUCTION OF BHS' ? 'selected' : ''}>CONSTRUCTION OF BHS</option>
                            <option value="CONSTRUCTION OF HOSPITAL" ${record.type_of_health_facility === 'CONSTRUCTION OF HOSPITAL' ? 'selected' : ''}>CONSTRUCTION OF HOSPITAL</option>
                            <option value="CONSTRUCTION OF PCF" ${record.type_of_health_facility === 'CONSTRUCTION OF PCF' ? 'selected' : ''}>CONSTRUCTION OF PCF</option>
                            <option value="CONTACT TRACER" ${record.type_of_health_facility === 'CONTACT TRACER' ? 'selected' : ''}>CONTACT TRACER</option>
                            <option value="COOK" ${record.type_of_health_facility === 'COOK' ? 'selected' : ''}>COOK</option>
                            <option value="COOK II" ${record.type_of_health_facility === 'COOK II' ? 'selected' : ''}>COOK II</option>
                        </optgroup>
                        <optgroup label="D–L">
                            <option value="DATA CONTROLLER" ${record.type_of_health_facility === 'DATA CONTROLLER' ? 'selected' : ''}>DATA CONTROLLER</option>
                            <option value="DATA ENCODER" ${record.type_of_health_facility === 'DATA ENCODER' ? 'selected' : ''}>DATA ENCODER</option>
                            <option value="DENTAL AIDE" ${record.type_of_health_facility === 'DENTAL AIDE' ? 'selected' : ''}>DENTAL AIDE</option>
                            <option value="DENTIST" ${record.type_of_health_facility === 'DENTIST' ? 'selected' : ''}>DENTIST</option>
                            <option value="DENTIST I" ${record.type_of_health_facility === 'DENTIST I' ? 'selected' : ''}>DENTIST I</option>
                            <option value="DENTIST II" ${record.type_of_health_facility === 'DENTIST II' ? 'selected' : ''}>DENTIST II</option>
                            <option value="DESIGNATED REGULATORY COMPLIANCE" ${record.type_of_health_facility === 'DESIGNATED REGULATORY COMPLIANCE' ? 'selected' : ''}>DESIGNATED REGULATORY COMPLIANCE</option>
                            <option value="DIALYSIS TECH" ${record.type_of_health_facility === 'DIALYSIS TECH' ? 'selected' : ''}>DIALYSIS TECH</option>
                            <option value="DOCTOR" ${record.type_of_health_facility === 'DOCTOR' ? 'selected' : ''}>DOCTOR</option>
                            <option value="DRIVER" ${record.type_of_health_facility === 'DRIVER' ? 'selected' : ''}>DRIVER</option>
                            <option value="DRIVER II" ${record.type_of_health_facility === 'DRIVER II' ? 'selected' : ''}>DRIVER II</option>
                            <option value="DUMP DRIVER" ${record.type_of_health_facility === 'DUMP DRIVER' ? 'selected' : ''}>DUMP DRIVER</option>
                            <option value="ELECTRICIAN" ${record.type_of_health_facility === 'ELECTRICIAN' ? 'selected' : ''}>ELECTRICIAN</option>
                            <option value="EMERGENCY TRANSPORT DRIVER" ${record.type_of_health_facility === 'EMERGENCY TRANSPORT DRIVER' ? 'selected' : ''}>EMERGENCY TRANSPORT DRIVER</option>
                            <option value="ENCODER" ${record.type_of_health_facility === 'ENCODER' ? 'selected' : ''}>ENCODER</option>
                            <option value="ENCODER/ADMIN ASSISTANT" ${record.type_of_health_facility === 'ENCODER/ADMIN ASSISTANT' ? 'selected' : ''}>ENCODER/ADMIN ASSISTANT</option>
                            <option value="ENGINEER" ${record.type_of_health_facility === 'ENGINEER' ? 'selected' : ''}>ENGINEER</option>
                            <option value="ENGINEER II" ${record.type_of_health_facility === 'ENGINEER II' ? 'selected' : ''}>ENGINEER II</option>
                            <option value="ENGINEERI" ${record.type_of_health_facility === 'ENGINEERI' ? 'selected' : ''}>ENGINEERI</option>
                            <option value="HEALTH AIDE" ${record.type_of_health_facility === 'HEALTH AIDE' ? 'selected' : ''}>HEALTH AIDE</option>
                            <option value="HEALTH EDUCATION & PROMOTION OFFICER" ${record.type_of_health_facility === 'HEALTH EDUCATION & PROMOTION OFFICER' ? 'selected' : ''}>HEALTH EDUCATION & PROMOTION OFFICER</option>
                            <option value="HEALTH EQUIPMENT" ${record.type_of_health_facility === 'HEALTH EQUIPMENT' ? 'selected' : ''}>HEALTH EQUIPMENT</option>
                            <option value="HEALTH PROGRAM OFFICER I" ${record.type_of_health_facility === 'HEALTH PROGRAM OFFICER I' ? 'selected' : ''}>HEALTH PROGRAM OFFICER I</option>
                            <option value="HEPO I" ${record.type_of_health_facility === 'HEPO I' ? 'selected' : ''}>HEPO I</option>
                            <option value="HOSPITAL" ${record.type_of_health_facility === 'HOSPITAL' ? 'selected' : ''}>HOSPITAL</option>
                            <option value="HOSPITAL EQUIPMENT" ${record.type_of_health_facility === 'HOSPITAL EQUIPMENT' ? 'selected' : ''}>HOSPITAL EQUIPMENT</option>
                            <option value="HUMAN RESOURCE FOR HEALTH" ${record.type_of_health_facility === 'HUMAN RESOURCE FOR HEALTH' ? 'selected' : ''}>HUMAN RESOURCE FOR HEALTH</option>
                            <option value="HUMAN RESOURCE OFFICER" ${record.type_of_health_facility === 'HUMAN RESOURCE OFFICER' ? 'selected' : ''}>HUMAN RESOURCE OFFICER</option>
                            <option value="INFORMATION SYSTEM ANALYST" ${record.type_of_health_facility === 'INFORMATION SYSTEM ANALYST' ? 'selected' : ''}>INFORMATION SYSTEM ANALYST</option>
                            <option value="INFORMATION TECHNOLOGIST I" ${record.type_of_health_facility === 'INFORMATION TECHNOLOGIST I' ? 'selected' : ''}>INFORMATION TECHNOLOGIST I</option>
                            <option value="INFORMATION TECHNOLOGY OFFICER" ${record.type_of_health_facility === 'INFORMATION TECHNOLOGY OFFICER' ? 'selected' : ''}>INFORMATION TECHNOLOGY OFFICER</option>
                            <option value="IT" ${record.type_of_health_facility === 'IT' ? 'selected' : ''}>IT</option>
                            <option value="IT PERSONNEL" ${record.type_of_health_facility === 'IT PERSONNEL' ? 'selected' : ''}>IT PERSONNEL</option>
                            <option value="LAB AIDE" ${record.type_of_health_facility === 'LAB AIDE' ? 'selected' : ''}>LAB AIDE</option>
                            <option value="LABORATORY AIDE" ${record.type_of_health_facility === 'LABORATORY AIDE' ? 'selected' : ''}>LABORATORY AIDE</option>
                            <option value="LABORATORY PERSONNEL" ${record.type_of_health_facility === 'LABORATORY PERSONNEL' ? 'selected' : ''}>LABORATORY PERSONNEL</option>
                            <option value="LABORATORY TECHNICIAN" ${record.type_of_health_facility === 'LABORATORY TECHNICIAN' ? 'selected' : ''}>LABORATORY TECHNICIAN</option>
                            <option value="LABORATORY TECHNICIAN I" ${record.type_of_health_facility === 'LABORATORY TECHNICIAN I' ? 'selected' : ''}>LABORATORY TECHNICIAN I</option>
                            <option value="LAND VEHICLE DRIVER" ${record.type_of_health_facility === 'LAND VEHICLE DRIVER' ? 'selected' : ''}>LAND VEHICLE DRIVER</option>
                            <option value="LAUNDRY WORKER" ${record.type_of_health_facility === 'LAUNDRY WORKER' ? 'selected' : ''}>LAUNDRY WORKER</option>
                            <option value="LAUNDRY WORKER II" ${record.type_of_health_facility === 'LAUNDRY WORKER II' ? 'selected' : ''}>LAUNDRY WORKER II</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-number_of_units">Number of Units</label>
                    <input type="number" id="edit-number_of_units" value="${record.number_of_units || 0}" min="0" required>
                </div>
                <div class="form-group">
                    <label for="edit-target">Target <span class="required">*</span></label>
                    <select id="edit-target" required>
                        ${generateTargetOptions(record.target)}
                    </select>
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
                        <option value="MLGU/PLGU" ${record.fund_source == 'MLGU/PLGU' ? 'selected' : ''}>MLGU/PLGU</option>
                        <option value="MLGU/DOH" ${record.fund_source == 'MLGU/DOH' ? 'selected' : ''}>MLGU/DOH</option>
                        <option value="PLGU/DOH" ${record.fund_source == 'PLGU/DOH' ? 'selected' : ''}>PLGU/DOH</option>
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
                <div class="form-group full-width">
                    <label for="edit-remarks">Remarks</label>
                    <textarea id="edit-remarks" rows="4">${escapeHtml(record.remarks || '')}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Record</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-modal').style.display='none'">Cancel</button>
            </div>
        </form>
    `;
    
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
        facility_level: document.getElementById('edit-facility_level').value,
        category: document.getElementById('edit-category').value,
        type_of_health_facility: document.getElementById('edit-type_of_health_facility').value,
        number_of_units: document.getElementById('edit-number_of_units').value,
        facilities: document.getElementById('edit-concerned_office_facility').value,
        target: document.getElementById('edit-target').value,
        costing: formatCostingForDB(document.getElementById('edit-costing').value),
        fund_source: document.getElementById('edit-fund_source').value,
        presence_in_existing_plans: document.getElementById('edit-presence_in_existing_plans').value,
        remarks: document.getElementById('edit-remarks').value
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
