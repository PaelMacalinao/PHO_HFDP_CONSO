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
        .then(response => response.json())
        .then(data => {
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
                '<tr><td colspan="15" class="no-data">Error loading data. Please try again.</td></tr>';
        });
}

// Display records in table
function displayRecords(records) {
    const tbody = document.getElementById('records-tbody');
    
    if (records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="15" class="no-data">No records found.</td></tr>';
        return;
    }

    tbody.innerHTML = records.map(record => `
        <tr>
            <td>${record.id}</td>
            <td>${record.year}</td>
            <td>${record.cluster}</td>
            <td>${record.concerned_office_facility || '-'}</td>
            <td>${record.facility_level}</td>
            <td>${record.category}</td>
            <td>${record.type_of_health_facility || '-'}</td>
            <td>${record.number_of_units || 0}</td>
            <td>${record.facilities ? (record.facilities.length > 50 ? record.facilities.substring(0, 50) + '...' : record.facilities) : '-'}</td>
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

// Apply filters
function applyFilters() {
    currentFilters = {
        year: document.getElementById('filter-year').value,
        cluster: document.getElementById('filter-cluster').value,
        facility_level: document.getElementById('filter-facility-level').value,
        category: document.getElementById('filter-category').value,
        fund_source: document.getElementById('filter-fund-source').value,
        presence_plans: document.getElementById('filter-presence-plans').value
    };
    
    loadRecords();
}

// Clear filters
function clearFilters() {
    document.getElementById('filter-year').value = '';
    document.getElementById('filter-cluster').value = '';
    document.getElementById('filter-facility-level').value = '';
    document.getElementById('filter-category').value = '';
    document.getElementById('filter-fund-source').value = '';
    document.getElementById('filter-presence-plans').value = '';
    
    currentFilters = {};
    loadRecords();
}

// Edit record
function editRecord(id) {
    // Fetch record data
    fetch(`api/get_records.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
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
                        <option value="2024" ${record.year == 2024 ? 'selected' : ''}>2024</option>
                        <option value="2025" ${record.year == 2025 ? 'selected' : ''}>2025</option>
                        <option value="2026" ${record.year == 2026 ? 'selected' : ''}>2026</option>
                        <option value="2027" ${record.year == 2027 ? 'selected' : ''}>2027</option>
                        <option value="2028" ${record.year == 2028 ? 'selected' : ''}>2028</option>
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
                    <input type="text" id="edit-concerned_office_facility" value="${escapeHtml(record.concerned_office_facility || '')}" required>
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
                    <input type="text" id="edit-type_of_health_facility" value="${escapeHtml(record.type_of_health_facility || '')}">
                </div>
                <div class="form-group">
                    <label for="edit-number_of_units">Number of Units</label>
                    <input type="number" id="edit-number_of_units" value="${record.number_of_units || 0}" min="0">
                </div>
                <div class="form-group full-width">
                    <label for="edit-facilities">Facilities</label>
                    <textarea id="edit-facilities" rows="3">${escapeHtml(record.facilities || '')}</textarea>
                </div>
                <div class="form-group">
                    <label for="edit-target">Target</label>
                    <input type="text" id="edit-target" value="${escapeHtml(record.target || '')}">
                </div>
                <div class="form-group">
                    <label for="edit-costing">Costing</label>
                    <input type="number" id="edit-costing" value="${record.costing || 0}" step="0.01" min="0">
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
                    <select id="edit-presence_in_existing_plans">
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
        facilities: document.getElementById('edit-facilities').value,
        target: document.getElementById('edit-target').value,
        costing: document.getElementById('edit-costing').value,
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
    .then(response => response.json())
    .then(data => {
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
    .then(response => response.json())
    .then(data => {
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

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
